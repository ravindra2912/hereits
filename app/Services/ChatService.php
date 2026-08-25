<?php

namespace App\Services;

use App\Models\Business;
use App\Models\ChatConversation;
use App\Models\ChatConversationParticipant;
use App\Models\ChatMessage;
use App\Models\ChatMessageAttachment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatService
{
    public const PARTICIPANT_USER = 'user';
    public const PARTICIPANT_BUSINESS = 'business';

    public const CONVERSATION_DIRECT = 'direct';
    public const CONVERSATION_GROUP = 'group';

    public const MESSAGE_TEXT = 'text';
    public const MESSAGE_IMAGE = 'image';
    public const MESSAGE_INQUIRY = 'inquiry';
    public const MESSAGE_PLACE_ORDER = 'place_order';
    public const MESSAGE_QUOTATION = 'quotation';
    public const MESSAGE_ORDER = 'order';
    public const MESSAGE_SYSTEM = 'system';

    public function resolveCurrentActor(): array
    {
        $user = auth()->user();

        if (request()->routeIs('business.*')) {
            $businessId = data_get(session('currentBusiness'), 'id') ?? $user?->business_id;

            $business = Business::query()
                ->select(['id', 'name', 'slug', 'business_logo', 'contact', 'status'])
                ->findOrFail($businessId);

            return [
                'type' => self::PARTICIPANT_BUSINESS,
                'id' => $business->id,
                'model' => $business,
            ];
        }

        $frontUser = User::query()
            ->select(['id', 'first_name', 'last_name', 'profile', 'email', 'contact', 'role', 'status'])
            ->findOrFail($user->id);

        return [
            'type' => self::PARTICIPANT_USER,
            'id' => $frontUser->id,
            'model' => $frontUser,
        ];
    }

    public function normalizeParticipantType(string $type): string
    {
        return match ($type) {
            'user', 'App\\Models\\User', User::class => self::PARTICIPANT_USER,
            'business', 'App\\Models\\Business', Business::class => self::PARTICIPANT_BUSINESS,
            default => $type,
        };
    }

    public function participantKey(string $participantType, int $participantId): string
    {
        return $this->normalizeParticipantType($participantType) . ':' . $participantId;
    }

    public function participantModel(string $participantType, int $participantId): Model
    {
        $normalizedType = $this->normalizeParticipantType($participantType);

        return match ($normalizedType) {
            self::PARTICIPANT_USER => User::query()
                ->select(['id', 'first_name', 'last_name', 'profile', 'email', 'contact', 'role', 'status'])
                ->findOrFail($participantId),
            self::PARTICIPANT_BUSINESS => Business::query()
                ->select(['id', 'name', 'slug', 'business_logo', 'contact', 'status'])
                ->findOrFail($participantId),
            default => throw new AuthorizationException('Unsupported participant type.'),
        };
    }

    public function participantPayload(string $participantType, int $participantId): array
    {
        $normalizedType = $this->normalizeParticipantType($participantType);
        $participant = $this->participantModel($normalizedType, $participantId);

        return [
            'type' => $normalizedType,
            'id' => $participant->id,
            'name' => $normalizedType === self::PARTICIPANT_BUSINESS
                ? $participant->name
                : trim($participant->first_name . ' ' . $participant->last_name),
            'avatar' => $normalizedType === self::PARTICIPANT_BUSINESS
                ? getImage($participant->business_logo)
                : getImage($participant->profile),
            'subtitle' => $normalizedType === self::PARTICIPANT_BUSINESS
                ? 'Business'
                : 'User',
        ];
    }

    public function conversationKey(string $conversationType, array $participants): string
    {
        $keys = collect($participants)
            ->map(fn(array $participant) => $this->participantKey($participant['type'], (int) $participant['id']))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return sha1($conversationType . '|' . implode('|', $keys));
    }

    public function normalizeParticipants(array $participants): array
    {
        return collect($participants)
            ->map(function (array $participant) {
                return [
                    'type' => $participant['type'],
                    'id' => (int) $participant['id'],
                ];
            })
            ->unique(fn(array $participant) => $this->participantKey($participant['type'], $participant['id']))
            ->values()
            ->all();
    }

    public function allowedMessageTypes(string $conversationType): array
    {
        return $conversationType === self::CONVERSATION_GROUP
            ? [self::MESSAGE_TEXT, self::MESSAGE_IMAGE]
            : [self::MESSAGE_TEXT, self::MESSAGE_IMAGE, self::MESSAGE_INQUIRY, self::MESSAGE_PLACE_ORDER, self::MESSAGE_QUOTATION, self::MESSAGE_ORDER];
    }

    public function getConversationOrFail(int $conversationId, array $actor): ChatConversation
    {
        $conversation = ChatConversation::query()
            ->select([
                'id',
                'conversation_key',
                'conversation_type',
                'title',
                'image',
                'created_by_type',
                'created_by_id',
                'last_message_id',
                'last_message_at',
                'is_active',
                'business_unlocked_until',
                'created_at',
            ])
            ->with([
                'participants' => function ($query) {
                    $query->select([
                        'id',
                        'conversation_id',
                        'participant_type',
                        'participant_id',
                        'role',
                        'joined_at',
                        'left_at',
                        'is_muted',
                        'is_archived',
                        'last_read_message_id',
                        'last_read_at',
                    ])->whereNull('left_at')->with('participant');
                },
                'lastMessage' => function ($query) {
                    $query->select([
                        'id',
                        'conversation_id',
                        'sender_type',
                        'sender_id',
                        'message_type',
                        'action_type',
                        'body',
                        'metadata',
                        'is_system',
                        'created_at',
                    ])->with('attachments');
                },
            ])
            ->active()
            ->findOrFail($conversationId);

        $this->ensureActorCanAccessConversation($conversation, $actor);

        return $conversation;
    }

    public function ensureActorCanAccessConversation(ChatConversation $conversation, array $actor): void
    {
        $actorType = $this->normalizeParticipantType($actor['type']);

        $hasAccess = $conversation->participants
            ->contains(function (ChatConversationParticipant $participant) use ($actorType, $actor) {
                return $this->normalizeParticipantType($participant->participant_type) === $actorType &&
                       (int) $participant->participant_id === (int) $actor['id'];
            });

        if (!$hasAccess) {
            throw new AuthorizationException('You do not have access to this conversation.');
        }
    }

    public function conversationsForActor(array $actor): Collection
    {
        return ChatConversation::query()
            ->select([
                'id',
                'conversation_key',
                'conversation_type',
                'title',
                'image',
                'created_by_type',
                'created_by_id',
                'last_message_id',
                'last_message_at',
                'is_active',
                'business_unlocked_until',
                'created_at',
            ])
            ->with([
                'participants' => function ($query) use ($actor) {
                    $query->select([
                        'id',
                        'conversation_id',
                        'participant_type',
                        'participant_id',
                        'role',
                        'joined_at',
                        'left_at',
                        'is_muted',
                        'is_archived',
                        'last_read_message_id',
                        'last_read_at',
                    ])->whereNull('left_at')->with('participant');
                },
                'lastMessage' => function ($query) {
                    $query->select([
                        'id',
                        'conversation_id',
                        'sender_type',
                        'sender_id',
                        'message_type',
                        'action_type',
                        'body',
                        'metadata',
                        'is_system',
                        'created_at',
                    ])->with('attachments');
                },
            ])
            ->active()
            ->whereHas('participants', function ($query) use ($actor) {
                $query->whereNull('left_at')
                    ->where('participant_type', $actor['type'])
                    ->where('participant_id', $actor['id']);
            })
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
            ->limit(50)
            ->get();
    }

    public function findOrCreateConversation(string $conversationType, array $selectedParticipants, array $actor, ?string $title = null): ChatConversation
    {
        $normalizedParticipants = $this->normalizeParticipants($selectedParticipants);
        $normalizedParticipants[] = [
            'type' => $actor['type'],
            'id' => (int) $actor['id'],
        ];
        $normalizedParticipants = $this->normalizeParticipants($normalizedParticipants);

        if ($conversationType === self::CONVERSATION_DIRECT && count($normalizedParticipants) !== 2) {
            throw new AuthorizationException('Direct chat needs exactly one target participant.');
        }

        if ($conversationType === self::CONVERSATION_GROUP && count($normalizedParticipants) < 3) {
            throw new AuthorizationException('Group chat needs at least two other participants.');
        }

        $conversationKey = $this->conversationKey($conversationType, $normalizedParticipants);

        return DB::transaction(function () use ($conversationKey, $conversationType, $normalizedParticipants, $actor, $title) {
            $conversation = ChatConversation::query()->firstOrCreate(
                ['conversation_key' => $conversationKey],
                [
                    'conversation_type' => $conversationType,
                    'title' => $conversationType === self::CONVERSATION_GROUP ? $title : null,
                    'created_by_type' => $actor['type'],
                    'created_by_id' => $actor['id'],
                    'is_active' => true,
                ]
            );

            foreach ($normalizedParticipants as $participant) {
                $role = 'member';
                if ($conversationType === self::CONVERSATION_GROUP && $participant['type'] === $actor['type'] && (int)$participant['id'] === (int)$actor['id']) {
                    $role = 'admin';
                }

                ChatConversationParticipant::query()->updateOrCreate(
                    [
                        'conversation_id' => $conversation->id,
                        'participant_type' => $participant['type'],
                        'participant_id' => $participant['id'],
                    ],
                    [
                        'role' => $role,
                        'left_at' => null,
                        'joined_at' => now(),
                    ]
                );
            }

            return $conversation->fresh();
        });
    }

    public function storeMessage(ChatConversation $conversation, array $actor, array $payload, array $uploadedFiles = []): ChatMessage
    {
        $allowedTypes = $this->allowedMessageTypes($conversation->conversation_type);

        if (!in_array($payload['message_type'], $allowedTypes, true) && $payload['message_type'] !== self::MESSAGE_SYSTEM) {
            throw new AuthorizationException('Message type is not allowed in this conversation.');
        }

        if ($actor['type'] === self::PARTICIPANT_BUSINESS && $conversation->conversation_type === self::CONVERSATION_DIRECT) {
            if (!$conversation->business_unlocked_until || $conversation->business_unlocked_until->isPast()) {
                throw new AuthorizationException('Chat session is locked or has expired (24h limit). Please unlock chat using credits.');
            }
        }

        return DB::transaction(function () use ($conversation, $actor, $payload, $uploadedFiles) {
            $message = ChatMessage::query()->create([
                'conversation_id' => $conversation->id,
                'sender_type' => $actor['type'],
                'sender_id' => $actor['id'],
                'message_type' => $payload['message_type'],
                'action_type' => $payload['action_type'] ?? null,
                'body' => $payload['body'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
                'reply_to_message_id' => $payload['reply_to_message_id'] ?? null,
                'is_system' => $payload['is_system'] ?? false,
            ]);

            foreach ($uploadedFiles as $index => $uploadedFile) {
                /** @var UploadedFile $uploadedFile */
                $path = fileUploadStorage($uploadedFile, 'chat/' . $conversation->id, 500, 500);

                $disk = env('IMAGE_STORAGE_DISK', 'r2');
                ChatMessageAttachment::query()->create([
                    'message_id'    => $message->id,
                    'disk'          => $disk,
                    'path'          => $path,
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'mime_type'     => $uploadedFile->getMimeType(),
                    'file_size'     => $uploadedFile->getSize(),
                    'sort_order'    => $index,
                ]);
            }

            $conversation->forceFill([
                'last_message_id' => $message->id,
                'last_message_at' => $message->created_at,
            ])->save();

            return $message->load(['attachments', 'replyTo']);
        });
    }

    public function markConversationAsRead(ChatConversation $conversation, array $actor): void
    {
        ChatConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('participant_type', $actor['type'])
            ->where('participant_id', $actor['id'])
            ->update([
                'last_read_message_id' => $conversation->last_message_id,
                'last_read_at' => now(),
            ]);
    }

    public function conversationSummary(ChatConversation $conversation, array $actor): array
    {
        $participants = $conversation->participants
            ->map(function (ChatConversationParticipant $participant) {
                $payload = $this->participantPayload($participant->participant_type, (int) $participant->participant_id);
                $payload['role'] = $participant->role;
                return $payload;
            })
            ->values();

        $currentParticipant = $conversation->participants->first(function (ChatConversationParticipant $participant) use ($actor) {
            return $participant->participant_type === $actor['type'] && (int) $participant->participant_id === (int) $actor['id'];
        });

        $display = $this->displayNameForConversation($conversation, $participants, $actor);
        $preview = $this->messagePreview($conversation->lastMessage);

        $isUnlocked = true;
        $unlockedUntil = null;
        $remainingSeconds = 0;
        $chatCreditCost = 1.0;
        $availableCredits = 0.0;

        if ($actor['type'] === self::PARTICIPANT_BUSINESS && $conversation->conversation_type === self::CONVERSATION_DIRECT) {
            $chatCreditCost = getChatCreditDeductionAmount($actor['id']);
            $bizSetting = \App\Models\BusinessSetting::where('business_id', $actor['id'])->first();
            $availableCredits = (float)($bizSetting->credit ?? 0);

            if ($conversation->business_unlocked_until && $conversation->business_unlocked_until->isFuture()) {
                $isUnlocked = true;
                $unlockedUntil = $conversation->business_unlocked_until->toIso8601String();
                $remainingSeconds = max(0, now()->diffInSeconds($conversation->business_unlocked_until, false));
            } else {
                $isUnlocked = false;
            }
        }

        return [
            'id' => $conversation->id,
            'conversation_key' => $conversation->conversation_key,
            'conversation_type' => $conversation->conversation_type,
            'title' => $conversation->title,
            'display_name' => $display['name'],
            'display_subtitle' => $display['subtitle'],
            'avatar' => $display['avatar'],
            'participants' => $participants,
            'last_message' => $conversation->lastMessage ? $this->messagePayload($conversation->lastMessage, $actor) : null,
            'last_message_preview' => $preview,
            'last_message_at' => optional($conversation->last_message_at ?? $conversation->created_at)?->toIso8601String(),
            'created_at' => optional($conversation->created_at)?->toIso8601String(),
            'is_unread' => filled($conversation->last_message_id)
                && (int) $conversation->last_message_id > (int) ($currentParticipant?->last_read_message_id ?? 0),
            'current_participant_role' => $currentParticipant?->role ?? 'member',
            'is_unlocked' => $isUnlocked,
            'unlocked_until' => $unlockedUntil,
            'remaining_seconds' => $remainingSeconds,
            'chat_credit_cost' => $chatCreditCost,
            'available_credits' => $availableCredits,
            'selected' => false,
        ];
    }

    public function conversationDetail(ChatConversation $conversation, array $actor): array
    {
        return [
            'conversation' => $this->conversationSummary($conversation, $actor),
            'messages' => $conversation->messages
                ->map(fn(ChatMessage $message) => $this->messagePayload($message, $actor))
                ->values(),
        ];
    }

    public function messagePayload(ChatMessage $message, array $actor): array
    {
        $sender = $this->participantPayload($message->sender_type, (int) $message->sender_id);

        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'message_type' => $message->message_type,
            'action_type' => $message->action_type,
            'body' => $message->body,
            'metadata' => $message->metadata ?? [],
            'is_system' => $message->is_system,
            'is_mine' => $message->sender_type === $actor['type'] && (int) $message->sender_id === (int) $actor['id'],
            'sender_type' => $message->sender_type,
            'sender_id' => (int) $message->sender_id,
            'sender' => $sender,
            'attachments' => $message->attachments->map(function (ChatMessageAttachment $attachment) {
                return [
                    'id' => $attachment->id,
                    'disk' => $attachment->disk,
                    'path' => $attachment->path,
                    'url' => $attachment->url,
                    'original_name' => $attachment->original_name,
                    'mime_type' => $attachment->mime_type,
                    'file_size' => $attachment->file_size,
                ];
            })->values(),
            'created_at' => optional($message->created_at)?->toIso8601String(),
        ];
    }

    public function messagePreview(?ChatMessage $message): string
    {
        if (!$message) {
            return 'No messages yet';
        }

        return match ($message->message_type) {
            self::MESSAGE_IMAGE => 'Image message',
            self::MESSAGE_INQUIRY => 'Inquiry',
            self::MESSAGE_PLACE_ORDER => 'Place order',
            self::MESSAGE_SYSTEM => $message->body ?: 'System update',
            default => Str::limit((string) $message->body, 70),
        };
    }

    public function displayNameForConversation(ChatConversation $conversation, Collection $participants, array $actor): array
    {
        if ($conversation->conversation_type === self::CONVERSATION_GROUP) {
            $avatar = $conversation->image ? Storage::disk('public')->url($conversation->image) : null;
            if (filled($conversation->title)) {
                return [
                    'name' => $conversation->title,
                    'subtitle' => $participants->count() . ' members',
                    'avatar' => $avatar,
                ];
            }

            $names = $participants->pluck('name')->filter()->take(3)->all();

            return [
                'name' => implode(', ', $names),
                'subtitle' => $participants->count() . ' members',
                'avatar' => $avatar,
            ];
        }

        $otherParticipant = $participants
            ->first(fn(array $participant) => !($participant['type'] === $actor['type'] && (int) $participant['id'] === (int) $actor['id']));

        if ($otherParticipant) {
            return [
                'name' => $otherParticipant['name'],
                'subtitle' => $otherParticipant['subtitle'],
                'avatar' => $otherParticipant['avatar'],
            ];
        }

        $firstParticipant = $participants->first();

        return [
            'name' => $firstParticipant['name'] ?? 'Conversation',
            'subtitle' => $firstParticipant['subtitle'] ?? 'Direct chat',
            'avatar' => $firstParticipant['avatar'] ?? null,
        ];
    }

    public function searchParticipants(string $term, array $actor): Collection
    {
        $term = trim($term);

        $users = User::query()
            ->select(['id', 'first_name', 'last_name', 'profile', 'email', 'contact', 'status'])
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->where(function ($query) use ($term) {
                $query->where('first_name', 'like', '%' . $term . '%')
                    ->orWhere('last_name', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%')
                    ->orWhere('contact', 'like', '%' . $term . '%');
            })
            ->limit(8)
            ->get()
            ->map(function (User $user) use ($actor) {
                if ($actor['type'] === self::PARTICIPANT_USER && (int) $actor['id'] === (int) $user->id) {
                    return null;
                }

                return [
                    'type' => self::PARTICIPANT_USER,
                    'id' => $user->id,
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                    'subtitle' => $user->email ?: 'User',
                    'avatar' => getImage($user->profile),
                ];
            })
            ->filter()
            ->values();

        $businesses = Business::query()
            ->select(['id', 'name', 'slug', 'business_logo', 'contact', 'status'])
            ->whereNull('deleted_at')
            ->whereIn('status', ['active', 'pending'])
            ->when($actor['type'] === self::PARTICIPANT_BUSINESS, fn($query) => $query->where('id', '!=', $actor['id']))
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', '%' . $term . '%')
                    ->orWhere('contact', 'like', '%' . $term . '%');
            })
            ->limit(8)
            ->get()
            ->map(function (Business $business) use ($actor) {
                if ($actor['type'] === self::PARTICIPANT_BUSINESS && (int) $actor['id'] === (int) $business->id) {
                    return null;
                }

                return [
                    'type' => self::PARTICIPANT_BUSINESS,
                    'id' => $business->id,
                    'name' => $business->name,
                    'subtitle' => $business->contact ?: 'Business',
                    'avatar' => getImage($business->business_logo),
                ];
            })
            ->filter()
            ->values();

        return $users->concat($businesses)->sortBy('name')->values();
    }

    public function clearConversation(ChatConversation $conversation, array $actor): void
    {
        $this->ensureActorCanAccessConversation($conversation, $actor);

        DB::transaction(function () use ($conversation) {
            ChatMessage::query()
                ->where('conversation_id', $conversation->id)
                ->delete();

            $conversation->forceFill([
                'last_message_id' => null,
                'last_message_at' => null,
            ])->save();
        });
    }

    public function deleteConversation(ChatConversation $conversation, array $actor): void
    {
        $this->ensureActorCanAccessConversation($conversation, $actor);

        $conversation->forceFill(['is_active' => false])->save();
    }

    public function leaveConversation(ChatConversation $conversation, array $actor): void
    {
        if ($conversation->conversation_type !== self::CONVERSATION_GROUP) {
            throw new AuthorizationException('You can only leave group conversations.');
        }

        $this->ensureActorCanAccessConversation($conversation, $actor);

        ChatConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('participant_type', $actor['type'])
            ->where('participant_id', $actor['id'])
            ->update(['left_at' => now()]);
    }

    public function blockConversation(ChatConversation $conversation, array $actor): void
    {
        if ($conversation->conversation_type !== self::CONVERSATION_DIRECT) {
            throw new AuthorizationException('Block is only available for direct conversations.');
        }

        $this->ensureActorCanAccessConversation($conversation, $actor);

        $conversation->forceFill(['is_active' => false])->save();
    }
    public function updateGroup(ChatConversation $conversation, array $actor, array $data, $image = null): ChatConversation
    {
        if ($conversation->conversation_type !== self::CONVERSATION_GROUP) {
            throw new AuthorizationException('Cannot update non-group conversations.');
        }

        $this->ensureActorCanAccessConversation($conversation, $actor);

        $currentParticipant = $conversation->participants->first(function ($participant) use ($actor) {
            return $participant->participant_type === $actor['type'] && (int)$participant->participant_id === (int)$actor['id'];
        });

        if ($currentParticipant?->role !== 'admin') {
            throw new AuthorizationException('Only group admins can update the group.');
        }

        $updates = [];
        if (isset($data['title'])) {
            $updates['title'] = $data['title'];
        }

        if ($image) {
            if ($conversation->image) {
                fileRemoveStorage($conversation->image);
            }
            $updates['image'] = fileUploadStorage($image, 'chat/groups', 500, 500);
        }

        if (!empty($updates)) {
            $conversation->forceFill($updates)->save();
        }

        return $conversation;
    }

    public function addGroupMember(ChatConversation $conversation, array $actor, array $participantData): void
    {
        if ($conversation->conversation_type !== self::CONVERSATION_GROUP) {
            throw new AuthorizationException('Cannot add members to non-group conversations.');
        }

        $this->ensureActorCanAccessConversation($conversation, $actor);

        $currentParticipant = $conversation->participants->first(function ($participant) use ($actor) {
            return $participant->participant_type === $actor['type'] && (int)$participant->participant_id === (int)$actor['id'];
        });

        if ($currentParticipant?->role !== 'admin') {
            throw new AuthorizationException('Only group admins can add members.');
        }

        ChatConversationParticipant::query()->updateOrCreate(
            [
                'conversation_id' => $conversation->id,
                'participant_type' => $participantData['type'],
                'participant_id' => $participantData['id'],
            ],
            [
                'role' => 'member',
                'left_at' => null,
                'joined_at' => now(),
            ]
        );
    }

    public function removeGroupMember(ChatConversation $conversation, array $actor, array $participantData): void
    {
        if ($conversation->conversation_type !== self::CONVERSATION_GROUP) {
            throw new AuthorizationException('Cannot remove members from non-group conversations.');
        }

        $this->ensureActorCanAccessConversation($conversation, $actor);

        $currentParticipant = $conversation->participants->first(function ($participant) use ($actor) {
            return $participant->participant_type === $actor['type'] && (int)$participant->participant_id === (int)$actor['id'];
        });

        if ($currentParticipant?->role !== 'admin') {
            throw new AuthorizationException('Only group admins can remove members.');
        }

        ChatConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('participant_type', $participantData['type'])
            ->where('participant_id', $participantData['id'])
            ->update(['left_at' => now()]);
    }
}
