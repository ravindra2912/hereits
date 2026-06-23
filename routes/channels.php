<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    $chatService = app(App\Services\ChatService::class);
    try {
        // Try the direct resolved actor first
        $actor = $chatService->resolveCurrentActor();
        try {
            $chatService->getConversationOrFail((int) $conversationId, $actor);
            return true;
        } catch (\Exception $e) {
            // Fall through if direct resolve failed (e.g. business route check on generic broadcasting route)
        }

        // Try as a user actor
        $userActor = [
            'type' => \App\Services\ChatService::PARTICIPANT_USER,
            'id' => $user->id,
        ];
        try {
            $chatService->getConversationOrFail((int) $conversationId, $userActor);
            return true;
        } catch (\Exception $e) {}

        // Try as a business actor using session
        $sessionBusinessId = data_get(session('currentBusiness'), 'id');
        if ($sessionBusinessId) {
            $businessActor = [
                'type' => \App\Services\ChatService::PARTICIPANT_BUSINESS,
                'id' => (int) $sessionBusinessId,
            ];
            try {
                $chatService->getConversationOrFail((int) $conversationId, $businessActor);
                return true;
            } catch (\Exception $e) {}
        }

        // Try as a business actor using user's direct business_id
        if ($user->business_id) {
            $businessActor = [
                'type' => \App\Services\ChatService::PARTICIPANT_BUSINESS,
                'id' => (int) $user->business_id,
            ];
            try {
                $chatService->getConversationOrFail((int) $conversationId, $businessActor);
                return true;
            } catch (\Exception $e) {}
        }

        return false;
    } catch (\Exception $e) {
        return false;
    }
});
