<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chat\SearchChatParticipantRequest;
use App\Http\Requests\Chat\StoreChatConversationRequest;
use App\Http\Requests\Chat\StoreChatMessageRequest;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request, ChatService $chatService)
    {
        $isBusinessPanel = $request->routeIs('business.chat.*');
        $routePrefix = $isBusinessPanel ? 'business.chat.' : 'chat.';

        $actor = $chatService->resolveCurrentActor();
        $initialConversations = $chatService->conversationsForActor($actor)
            ->map(fn (ChatConversation $conversation) => $chatService->conversationSummary($conversation, $actor))
            ->values();

        return view('chat.index', [
            'layout' => $isBusinessPanel ? 'business.layouts.main' : 'front.layouts.main',
            'context' => $isBusinessPanel ? 'business' : 'front',
            'chatEndpoints' => [
                'conversations' => route($routePrefix . 'conversations.index'),
                'storeConversation' => route($routePrefix . 'conversations.store'),
                'searchParticipants' => route($routePrefix . 'participants.search'),
                'showConversation' => route($routePrefix . 'conversations.show', ['conversation' => '__ID__'], false),
                'storeMessage' => route($routePrefix . 'conversations.messages.store', ['conversation' => '__ID__'], false),
                'markRead' => route($routePrefix . 'conversations.read', ['conversation' => '__ID__'], false),
                'clear' => route($routePrefix . 'conversations.clear', ['conversation' => '__ID__'], false),
                'delete' => route($routePrefix . 'conversations.destroy', ['conversation' => '__ID__'], false),
                'leave' => route($routePrefix . 'conversations.leave', ['conversation' => '__ID__'], false),
                'block' => route($routePrefix . 'conversations.block', ['conversation' => '__ID__'], false),
                'update' => route($routePrefix . 'conversations.update', ['conversation' => '__ID__'], false),
                'addMember' => route($routePrefix . 'conversations.add_member', ['conversation' => '__ID__'], false),
                'removeMember' => route($routePrefix . 'conversations.remove_member', ['conversation' => '__ID__'], false),
                'unlock' => route($routePrefix . 'conversations.unlock', ['conversation' => '__ID__'], false),
            ],
            'selectedConversationId' => $request->integer('conversation_id'),
            'actor' => $actor,
            'initialConversations' => $initialConversations,
        ]);
    }

    public function conversations(Request $request, ChatService $chatService): JsonResponse
    {
        $actor = $chatService->resolveCurrentActor();
        $conversations = $chatService->conversationsForActor($actor)
            ->map(fn (ChatConversation $conversation) => $chatService->conversationSummary($conversation, $actor))
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'conversations' => $conversations,
            ],
        ]);
    }

    public function startConversation(Request $request, string $participantType, int $participantId, ChatService $chatService): RedirectResponse
    {
        $actor = $chatService->resolveCurrentActor();

        $conversation = $chatService->findOrCreateConversation(
            ChatService::CONVERSATION_DIRECT,
            [
                [
                    'type' => $participantType,
                    'id' => $participantId,
                ],
            ],
            $actor
        );

        $routePrefix = $request->routeIs('business.chat.*') ? 'business.chat.' : 'chat.';

        return redirect()->route($routePrefix . 'index', [
            'conversation_id' => $conversation->id,
        ]);
    }

    public function storeConversation(StoreChatConversationRequest $request, ChatService $chatService): JsonResponse
    {
        $actor = $chatService->resolveCurrentActor();

        $conversation = $chatService->findOrCreateConversation(
            $request->string('conversation_type')->toString(),
            $request->input('participants', []),
            $actor,
            $request->input('title')
        );

        return response()->json([
            'success' => true,
            'message' => 'Conversation ready.',
            'data' => [
                'conversation' => $chatService->conversationSummary(
                    $chatService->getConversationOrFail($conversation->id, $actor),
                    $actor
                ),
            ],
        ], 201);
    }

    public function show(Request $request, ChatConversation $conversation, ChatService $chatService): JsonResponse
    {
        $actor = $chatService->resolveCurrentActor();
        $conversation = $chatService->getConversationOrFail($conversation->id, $actor);

        $query = ChatMessage::query()
            ->select([
                'id',
                'conversation_id',
                'sender_type',
                'sender_id',
                'message_type',
                'action_type',
                'body',
                'metadata',
                'reply_to_message_id',
                'is_system',
                'edited_at',
                'created_at',
            ])
            ->with(['attachments'])
            ->where('conversation_id', $conversation->id)
            ->latest('id');

        if ($request->has('before_id')) {
            $query->where('id', '<', $request->integer('before_id'));
        }

        $messages = $query->limit(15)->get()->reverse()->values();

        if ($request->has('before_id')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'messages' => $messages->map(fn ($msg) => $chatService->messagePayload($msg, $actor)),
                    'has_more' => $messages->count() === 15,
                ],
            ]);
        }

        $conversation->setRelation('messages', $messages);
        
        $detail = $chatService->conversationDetail($conversation, $actor);
        $detail['has_more'] = $messages->count() === 15;

        return response()->json([
            'success' => true,
            'data' => $detail,
        ]);
    }

    public function storeMessage(StoreChatMessageRequest $request, ChatConversation $conversation, ChatService $chatService): JsonResponse
    {
        $actor = $chatService->resolveCurrentActor();
        $conversation = $chatService->getConversationOrFail($conversation->id, $actor);

        $uploadedFiles = $request->file('attachments', []);

        $message = $chatService->storeMessage($conversation, $actor, [
            'message_type' => $request->string('message_type')->toString(),
            'action_type' => in_array($request->string('message_type')->toString(), ['inquiry', 'place_order'], true)
                ? $request->string('message_type')->toString()
                : null,
            'body' => $request->input('body'),
            'metadata' => $request->input('metadata', []),
            'reply_to_message_id' => $request->input('reply_to_message_id'),
        ], is_array($uploadedFiles) ? $uploadedFiles : [$uploadedFiles]);

        $chatService->markConversationAsRead($conversation, $actor);

        $payload = $chatService->messagePayload($message, $actor);
        // broadcast(new \App\Events\MessageSent($message, $payload))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Message sent.',
            'data' => [
                'message' => $payload,
                'conversation_id' => $conversation->id,
            ],
        ], 201);
    }

    public function read(Request $request, ChatConversation $conversation, ChatService $chatService): JsonResponse
    {
        $actor = $chatService->resolveCurrentActor();
        $conversation = $chatService->getConversationOrFail($conversation->id, $actor);
        $chatService->markConversationAsRead($conversation, $actor);

        return response()->json([
            'success' => true,
            'message' => 'Conversation marked as read.',
        ]);
    }

    public function searchParticipants(SearchChatParticipantRequest $request, ChatService $chatService): JsonResponse
    {
        $actor = $chatService->resolveCurrentActor();
        $results = $chatService->searchParticipants($request->string('q')->toString(), $actor);

        return response()->json([
            'success' => true,
            'data' => [
                'results' => $results,
            ],
        ]);
    }

    public function clear(Request $request, ChatConversation $conversation, ChatService $chatService): JsonResponse
    {
        $actor = $chatService->resolveCurrentActor();
        $chatService->clearConversation($conversation, $actor);

        return response()->json([
            'success' => true,
            'message' => 'Chat cleared successfully.',
        ]);
    }

    public function destroy(Request $request, ChatConversation $conversation, ChatService $chatService): JsonResponse
    {
        $actor = $chatService->resolveCurrentActor();
        $chatService->deleteConversation($conversation, $actor);

        return response()->json([
            'success' => true,
            'message' => 'Chat deleted successfully.',
        ]);
    }

    public function leave(Request $request, ChatConversation $conversation, ChatService $chatService): JsonResponse
    {
        $actor = $chatService->resolveCurrentActor();
        $chatService->leaveConversation($conversation, $actor);

        return response()->json([
            'success' => true,
            'message' => 'Left group successfully.',
        ]);
    }

    public function block(Request $request, ChatConversation $conversation, ChatService $chatService): JsonResponse
    {
        $actor = $chatService->resolveCurrentActor();
        $chatService->blockConversation($conversation, $actor);

        return response()->json([
            'success' => true,
            'message' => 'User blocked successfully.',
        ]);
    }

    public function updateGroup(Request $request, ChatConversation $conversation, ChatService $chatService): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $actor = $chatService->resolveCurrentActor();
        $chatService->updateGroup($conversation, $actor, $validated, $request->file('image'));

        return response()->json([
            'success' => true,
            'message' => 'Group updated successfully.',
            'data' => [
                'conversation' => $chatService->conversationSummary($conversation, $actor)
            ]
        ]);
    }

    public function addGroupMember(Request $request, ChatConversation $conversation, ChatService $chatService): JsonResponse
    {
        $validated = $request->validate([
            'participant.type' => ['required', 'string', 'in:user,business'],
            'participant.id' => ['required', 'integer'],
        ]);

        $actor = $chatService->resolveCurrentActor();
        $chatService->addGroupMember($conversation, $actor, $validated['participant']);

        $updatedConversation = $chatService->getConversationOrFail($conversation->id, $actor);

        return response()->json([
            'success' => true,
            'message' => 'Member added successfully.',
            'data' => [
                'conversation' => $chatService->conversationSummary($updatedConversation, $actor)
            ]
        ]);
    }

    public function removeGroupMember(Request $request, ChatConversation $conversation, ChatService $chatService): JsonResponse
    {
        $validated = $request->validate([
            'participant.type' => ['required', 'string', 'in:user,business'],
            'participant.id' => ['required', 'integer'],
        ]);

        $actor = $chatService->resolveCurrentActor();
        $chatService->removeGroupMember($conversation, $actor, $validated['participant']);

        $updatedConversation = $chatService->getConversationOrFail($conversation->id, $actor);

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully.',
            'data' => [
                'conversation' => $chatService->conversationSummary($updatedConversation, $actor)
            ]
        ]);
    }

    public function showQuotation(Request $request, $id, ChatService $chatService)
    {
        $user = auth()->user();
        $actor = $chatService->resolveCurrentActor();
        
        $quotation = \App\Models\Quotation::with(['items', 'customer', 'creator', 'order'])->findOrFail($id);
        
        // Authorization check
        $isBusinessOwner = $user->business_id && ((int) $user->business_id === (int) $quotation->business_id);
        $isCustomer = (int) $user->id === (int) $quotation->customer_id;
        
        if (!$isBusinessOwner && !$isCustomer) {
            abort(403, 'Unauthorized action.');
        }
        
        if ($quotation->status === 'inprogress' && $quotation->valid_until && $quotation->valid_until < now()->toDateString()) {
            $quotation->update(['status' => 'expired']);
        }

        $canEdit = ($quotation->status === 'inprogress') && ($actor['type'] === 'business') && ($isBusinessOwner || ((int)$user->id === (int)$quotation->created_by_id));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('chat.partials.quotation_detail', compact('quotation', 'canEdit'))->render()
            ]);
        }

        abort(400, 'AJAX request required.');
    }

    public function unlock(Request $request, ChatConversation $conversation, ChatService $chatService): JsonResponse
    {
        $actor = $chatService->resolveCurrentActor();
        $conversation = $chatService->getConversationOrFail($conversation->id, $actor);

        if ($actor['type'] !== ChatService::PARTICIPANT_BUSINESS) {
            return response()->json([
                'success' => false,
                'message' => 'Only businesses can unlock chat sessions with credits.'
            ], 403);
        }

        if ($conversation->conversation_type !== ChatService::CONVERSATION_DIRECT) {
            return response()->json([
                'success' => false,
                'message' => 'Credit unlock is only required for direct customer conversations.'
            ], 400);
        }

        $businessId = $actor['id'];
        $creditService = app(\App\Services\CreditService::class);
        $creditCost = $creditService->getChatCreditDeductionAmount($businessId);
        $availableCredits = $creditService->getAvailableCredits($businessId);

        if ($creditCost > 0 && $availableCredits < $creditCost) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient credits. You need ' . number_format($creditCost, 2) . ' credits to unlock 24 hours of chat.'
            ], 400);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($creditService, $businessId, $conversation) {
            $creditService->deductChatCredit($businessId, $conversation->id, 'Unlock 24h Chat Session');
            $conversation->business_unlocked_until = now()->addHours(24);
            $conversation->save();
        });

        $updatedConversation = $chatService->getConversationOrFail($conversation->id, $actor);
        $summary = $chatService->conversationSummary($updatedConversation, $actor);

        return response()->json([
            'success' => true,
            'message' => 'Chat unlocked successfully for 24 hours!',
            'data' => [
                'conversation' => $summary,
                'unlocked_until' => $conversation->business_unlocked_until->toIso8601String(),
                'remaining_credits' => $creditService->getAvailableCredits($businessId),
            ]
        ]);
    }
}