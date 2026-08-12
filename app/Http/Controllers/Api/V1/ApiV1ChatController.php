<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatMessageAttachment;
use Illuminate\Http\Request;

class ApiV1ChatController extends Controller
{
    public function conversations(Request $request)
    {
        try {
            $user = $request->user();
            $conversations = ChatConversation::with(['lastMessage', 'participants'])
                ->whereHas('participants', function ($q) use ($user) {
                    $q->whereIn('participant_type', ['user', 'App\\Models\\User'])
                      ->where('participant_id', $user->id);
                })
                ->latest('updated_at')
                ->get()
                ->map(function ($conv) use ($user) {
                    $otherParticipant = $conv->participants->first(function ($p) use ($user) {
                        return !in_array($p->participant_type, ['user', 'App\\Models\\User']) || $p->participant_id != $user->id;
                    });

                    if ($otherParticipant) {
                        if (in_array($otherParticipant->participant_type, ['business', 'App\\Models\\Business'])) {
                            $biz = \App\Models\Business::find($otherParticipant->participant_id);
                            if ($biz) {
                                $conv->title = $biz->name;
                                $conv->image = getImage($biz->business_image, 'business');
                            }
                        } elseif (in_array($otherParticipant->participant_type, ['user', 'App\\Models\\User'])) {
                            $otherUser = \App\Models\User::find($otherParticipant->participant_id);
                            if ($otherUser) {
                                $conv->title = trim($otherUser->first_name . ' ' . $otherUser->last_name);
                                $conv->image = getImage($otherUser->profile, 'user');
                            }
                        }
                    }

                    if (empty($conv->title)) {
                        $conv->title = 'Direct Chat';
                    }

                    return $conv;
                });

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Conversations retrieved',
                'data' => $conversations
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function messages($conversationId)
    {
        try {
            $messages = ChatMessage::with(['attachments'])
                ->where('conversation_id', $conversationId)
                ->latest()
                ->paginate(30);

            $messages->through(function ($msg) {
                $msg->message = $msg->body;
                if ($msg->attachments) {
                    foreach ($msg->attachments as $att) {
                        $att->url = getImage($att->path);
                    }
                }
                if ($msg->message_type === 'image') {
                    if (!empty($msg->body) && (str_contains($msg->body, '/') || preg_match('/\.(jpeg|jpg|gif|png|webp)/i', $msg->body))) {
                        $msg->image_url = getImage($msg->body);
                    } elseif ($msg->attachments && $msg->attachments->count() > 0) {
                        $msg->image_url = $msg->attachments->first()->url;
                    }
                }
                return $msg;
            });

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Messages retrieved',
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendMessage(Request $request, $conversationId)
    {
        try {
            $request->validate([
                'message' => 'nullable|string',
                'image'   => 'nullable|file|image|max:10240',
            ]);

            $uploadedFiles = [];
            if ($request->hasFile('images')) {
                $uploadedFiles = is_array($request->file('images')) ? $request->file('images') : [$request->file('images')];
            } elseif ($request->hasFile('image')) {
                $uploadedFiles = [$request->file('image')];
            } elseif ($request->hasFile('file')) {
                $uploadedFiles = [$request->file('file')];
            } elseif ($request->hasFile('attachment')) {
                $uploadedFiles = [$request->file('attachment')];
            }

            if (empty($request->message) && empty($uploadedFiles)) {
                return response()->json(['status_code' => 422, 'success' => false, 'message' => 'Message text or image is required'], 422);
            }

            $user = $request->user();
            $textMessage = trim($request->message ?? '');
            $messageType = !empty($uploadedFiles) ? 'image' : 'text';

            $msg = ChatMessage::create([
                'conversation_id' => $conversationId,
                'sender_type' => 'user',
                'sender_id' => $user->id,
                'body' => $textMessage,
                'message_type' => $messageType,
            ]);

            foreach ($uploadedFiles as $file) {
                if ($file) {
                    $path = fileUploadStorage($file, 'chat/' . $conversationId, 800, 800);
                    $disk = env('IMAGE_STORAGE_DISK', 'r2');

                    ChatMessageAttachment::create([
                        'message_id'    => $msg->id,
                        'disk'          => $disk,
                        'path'          => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type'     => $file->getMimeType(),
                        'file_size'     => $file->getSize(),
                    ]);
                }
            }

            $msg->load(['attachments']);
            $msg->message = $msg->body;
            $msg->image_url = $msg->attachments->first() ? $msg->attachments->first()->url : null;

            $conversation = ChatConversation::find($conversationId);
            if ($conversation) {
                $conversation->last_message_id = $msg->id;
                $conversation->last_message_at = now();
                $conversation->save();
            }

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Message sent',
                'data' => $msg
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function startConversation(Request $request)
    {
        try {
            $request->validate([
                'business_id' => 'required|exists:businesses,id',
            ]);

            $user = $request->user();
            $businessId = $request->business_id;

            $chatService = app(\App\Services\ChatService::class);
            $actor = [
                'type' => \App\Services\ChatService::PARTICIPANT_USER,
                'id' => $user->id,
            ];
            $target = [
                [
                    'type' => \App\Services\ChatService::PARTICIPANT_BUSINESS,
                    'id' => (int) $businessId,
                ]
            ];

            $conversation = $chatService->findOrCreateConversation(
                \App\Services\ChatService::CONVERSATION_DIRECT,
                $target,
                $actor
            );

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Conversation started',
                'data' => $conversation
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
