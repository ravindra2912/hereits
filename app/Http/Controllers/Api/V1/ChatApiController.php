<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class ChatApiController extends Controller
{
    public function conversations(Request $request)
    {
        try {
            $user = $request->user();
            $conversations = ChatConversation::with(['lastMessage', 'participants'])
                ->whereHas('participants', function ($q) use ($user) {
                    $q->where('participant_type', 'App\\Models\\User')
                      ->where('participant_id', $user->id);
                })
                ->latest('updated_at')
                ->get();

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
            $messages = ChatMessage::where('conversation_id', $conversationId)
                ->latest()
                ->paginate(30);

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
                'message' => 'required|string',
            ]);

            $user = $request->user();

            $msg = ChatMessage::create([
                'conversation_id' => $conversationId,
                'sender_type' => 'App\\Models\\User',
                'sender_id' => $user->id,
                'message' => $request->message,
                'message_type' => 'text',
            ]);

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

            // Check if direct conversation already exists between this user and business
            $conversation = ChatConversation::where('conversation_type', 'direct')
                ->whereHas('participants', function ($q) use ($user) {
                    $q->where('participant_type', 'App\\Models\\User')
                      ->where('participant_id', $user->id);
                })
                ->whereHas('participants', function ($q) use ($businessId) {
                    $q->where('participant_type', 'App\\Models\\Business')
                      ->where('participant_id', $businessId);
                })
                ->first();

            if (!$conversation) {
                // Create a new conversation
                $business = \App\Models\Business::find($businessId);
                $conversation = ChatConversation::create([
                    'conversation_type' => 'direct',
                    'title' => $business->name,
                ]);

                // Create participants
                $conversation->participants()->create([
                    'participant_type' => 'App\\Models\\User',
                    'participant_id' => $user->id,
                ]);

                $conversation->participants()->create([
                    'participant_type' => 'App\\Models\\Business',
                    'participant_id' => $businessId,
                ]);
            }

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
