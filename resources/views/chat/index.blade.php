@extends($layout)

@section('content')
<div class="container-fluid chat-page" id="chatPage" data-chat-context="{{ $context }}" data-chat-endpoints='@json($chatEndpoints)' data-selected-conversation-id="{{ $selectedConversationId }}" data-initial-conversations='@json($initialConversations ?? [])'>
    <div class="row chat-layout">
        <div class="col-12 col-lg-4 mt-0">
            <div class="card border-0 shadow-sm chat-sidebar-card h-100">
                <div class="card-body p-3 p-md-4 d-flex flex-column gap-3 h-100">
                    <div class="d-flex align-items-start justify-content-between gap-3 conversation-list-header">
                        <div>
                            <h4 class="mb-1 fw-bold text-dark">Chats</h4>
                            <p class="text-muted small mb-0">Direct, business, and group conversations</p>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#chatConversationModal">
                            <i class="bi bi-plus-lg me-1"></i>New chat
                        </button>
                    </div>

                    <div class="input-group chat-search">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="search" class="form-control border-start-0 shadow-none" placeholder="Search conversations" id="chatConversationSearch" aria-label="Search conversations">
                    </div>

                    <div class="chat-conversation-list list-group list-group-flush flex-grow-1 overflow-auto" id="chatConversationList">
                        @forelse(($initialConversations ?? collect()) as $conversation)
                        <button type="button" class="list-group-item list-group-item-action chat-conversation-item rounded-4 mb-2 {{ (int) $selectedConversationId === (int) $conversation['id'] ? 'active' : '' }}" data-conversation-id="{{ $conversation['id'] }}">
                            <div class="d-flex align-items-center gap-3">
                                @if(!empty($conversation['avatar']))
                                <img src="{{ $conversation['avatar'] }}" alt="{{ $conversation['display_name'] }}" class="rounded-circle chat-avatar-media">
                                @else
                                <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-inline-flex align-items-center justify-content-center chat-avatar-circle"><i class="bi bi-people"></i></div>
                                @endif
                                <div class="flex-grow-1 text-start min-w-0">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div class="fw-semibold text-truncate">{{ $conversation['display_name'] }}</div>
                                        @if(!empty($conversation['is_unread']))
                                        <span class="badge bg-success rounded-pill">New</span>
                                        @endif
                                    </div>
                                    <div class="small text-truncate {{ (int) $selectedConversationId === (int) $conversation['id'] ? 'text-success-emphasis' : 'text-muted' }}">{{ $conversation['last_message_preview'] }}</div>
                                    <div class="small {{ (int) $selectedConversationId === (int) $conversation['id'] ? 'text-success-emphasis' : 'text-muted' }}">{{ \Illuminate\Support\Carbon::parse($conversation['last_message_at'] ?? $conversation['created_at'])->format('M j, g:i A') }}</div>
                                </div>
                            </div>
                        </button>
                        @empty
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-chat-dots fs-1 d-block mb-2"></i>
                            <div class="fw-semibold">No conversations yet</div>
                            <div class="small">Create a direct or group chat to get started.</div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8 mt-0">
            <div class="card border-0 shadow-sm chat-board-card h-100">
                <div class="card-header bg-white border-0 py-3 chat-board-header" id="chatBoardHeader">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div id="chatConversationAvatarWrapper" class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-inline-flex align-items-center justify-content-center chat-avatar-circle">
                                <i class="bi bi-person-circle fs-4"></i>
                            </div>
                            <div class="min-w-0">
                                <h5 class="mb-0 fw-bold text-truncate" id="chatConversationTitle">Select a conversation</h5>
                                <small class="text-muted" id="chatConversationSubtitle">Your messages will appear here</small>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-light rounded-circle border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 36px; height: 36px; padding: 0;">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" id="chatActionDropdown">
                                <!-- Dynamic options injected via JS -->
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card-body bg-white d-flex flex-column p-0 chat-board-body">
                    <div class="chat-empty-state flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center p-5" id="chatEmptyNotice" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);">
                        <div class="mb-4 position-relative">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center chat-bg-primary" style="width: 100px; height: 100px;">
                                <i class="bi bi-chat-left-text text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <div class="position-absolute bottom-0 end-0 bg-success border border-white border-3 rounded-circle" style="width: 24px; height: 24px; transform: translate(-10%, -10%);"></div>
                        </div>
                        <h4 class="fw-bold text-dark mb-2" style="letter-spacing: -0.5px;">Welcome to Messages</h4>
                        <p class="text-muted mb-4" style="max-width: 320px;">Connect with your network. Select an existing conversation or start a new one to begin chatting.</p>
                        <!-- <button class="btn btn-primary px-4 py-2 rounded-pill shadow-sm fw-medium d-inline-flex align-items-center gap-2 transition-all hover-lift" data-bs-toggle="modal" data-bs-target="#chatConversationModal">
                            <i class="bi bi-plus-lg"></i> Start a new chat
                        </button> -->
                    </div>

                    <div class="chat-message-wrapper d-none flex-grow-1" id="chatMessageWrapper">
                        <div class="chat-message-list d-flex flex-column gap-3" id="chatMessageList"></div>
                    </div>

                    <div class="chat-composer-shell d-none p-3 border-top" id="chatComposerShell" style="background-color: #f0f2f5;">
                        <form id="chatComposerForm" enctype="multipart/form-data" class="chat-composer-form mb-0">
                            <!-- Keep these hidden so JS doesn't break if it looks for them -->
                            <div class="d-none">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Message type">
                                    <button type="button" class="btn btn-outline-primary active chat-type-btn" data-message-type="text">Text</button>
                                    <button type="button" class="btn btn-outline-primary chat-type-btn" data-message-type="image">Image</button>
                                    <button type="button" class="btn btn-outline-primary chat-type-btn" data-message-type="inquiry">Inquiry</button>
                                    <button type="button" class="btn btn-outline-primary chat-type-btn" data-message-type="place_order">Order</button>
                                </div>
                                <small class="text-muted" id="chatComposerHint">Choose a conversation first</small>
                                <div class="text-muted small" id="chatComposerNote">Group conversations allow text and image only.</div>
                            </div>

                            <!-- Hidden action fields -->
                            <div class="row g-3 d-none mb-3 bg-white p-3 rounded-3 shadow-sm" id="chatActionFields">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="chatActionTitle" placeholder="Subject / Order title">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="chatActionReference" placeholder="Reference / Order number">
                                </div>
                                <div class="col-12">
                                    <textarea class="form-control" rows="3" id="chatActionNotes" placeholder="Extra details"></textarea>
                                </div>
                            </div>

                            <input type="file" class="d-none" id="chatAttachments" accept="image/*" multiple>

                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-link text-muted p-2 text-decoration-none shadow-none">
                                    <i class="bi bi-emoji-smile fs-4"></i>
                                </button>

                                <button type="button" class="btn btn-link text-muted p-2 text-decoration-none shadow-none" id="chatAttachmentButton">
                                    <i class="bi bi-plus-lg fs-4"></i>
                                </button>

                                <div class="flex-grow-1" id="chatBodyWrapper">
                                    <textarea class="form-control rounded-pill border-0 shadow-sm px-4 py-2" rows="1" id="chatMessageBody" placeholder="Type a message" style="resize: none; overflow-y: hidden; line-height: 1.5;"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm" id="chatSendButton" style="width: 44px; height: 44px; flex-shrink: 0;">
                                    <i class="bi bi-send-fill fs-5"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="chatConversationModal" tabindex="-1" aria-labelledby="chatConversationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="chatConversationModalLabel">Start a chat</h5>
                    <p class="text-muted small mb-0">Search users or businesses and create a direct or group conversation.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Conversation type</label>
                        <select class="form-select" id="chatConversationType">
                            <option value="direct">Direct</option>
                            <option value="group">Group</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-none" id="chatGroupTitleWrapper">
                        <label class="form-label fw-semibold">Group title</label>
                        <input type="text" class="form-control" id="chatGroupTitle" placeholder="Enter group title">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Search participants</label>
                    <input type="search" class="form-control" id="chatParticipantSearch" placeholder="Search by name, email, contact, or business name">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Selected participants</label>
                    <div class="border rounded-3 p-3 bg-light d-flex flex-wrap gap-2" id="chatSelectedParticipants">
                        <span class="text-muted small">No participants selected.</span>
                    </div>
                </div>

                <div class="border rounded-3 overflow-auto chat-participant-results" id="chatParticipantResults"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="chatCreateConversationButton">Create conversation</button>
            </div>
        </div>
    </div>
</div>

{{-- Chat Profile Modal --}}
<div class="modal fade" id="chatProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <h5 class="modal-title fw-bold">Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="position-relative d-inline-block">
                        <div id="chatProfileAvatar" class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-3 overflow-hidden" style="width: 100px; height: 100px; font-size: 3rem;">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 d-none" id="chatProfileImageEditBtn" style="transform: translate(0, -10px); width: 32px; height: 32px;">
                            <i class="bi bi-pencil" style="font-size: 14px;"></i>
                        </button>
                        <input type="file" id="chatProfileImageInput" accept="image/*" class="d-none">
                    </div>

                    <div class="d-flex justify-content-center align-items-center mb-1 gap-2" id="chatProfileTitleWrapper">
                        <h5 class="fw-bold mb-0" id="chatProfileTitle">Name</h5>
                        <button type="button" class="btn btn-sm btn-link text-muted p-0 d-none" id="chatProfileNameEditBtn">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    </div>
                    <div id="chatProfileNameEditSection" class="d-none mb-2 justify-content-center gap-1">
                        <input type="text" class="form-control form-control-sm" id="chatProfileNameInput" style="max-width: 200px;">
                        <button type="button" class="btn btn-sm btn-success" id="chatProfileNameSaveBtn"><i class="bi bi-check2"></i></button>
                        <button type="button" class="btn btn-sm btn-light" id="chatProfileNameCancelBtn"><i class="bi bi-x-lg"></i></button>
                    </div>

                    <p class="text-muted small mb-0" id="chatProfileSubtitle">Subtitle</p>
                </div>

                <div id="chatProfileMembersSection" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Members</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary d-none" id="chatProfileAddMemberBtn">
                            <i class="bi bi-plus"></i> Add
                        </button>
                    </div>
                    <div class="chat-participant-list" id="chatProfileMembersList" style="max-height: 200px; overflow-y: auto;">
                        <!-- Members list injected here -->
                    </div>

                    <div id="chatProfileAddMemberSection" class="d-none mt-3 p-3 border rounded bg-light">
                        <label class="form-label small text-muted">Search to Add Member</label>
                        <input type="text" class="form-control mb-2" id="chatProfileSearchInput" placeholder="Search users/businesses...">
                        <div class="border rounded bg-white" id="chatProfileSearchResults" style="max-height: 150px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Chat Action Confirmation Modal --}}
<div class="modal fade" id="chatActionConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div id="chatActionConfirmIcon" class="mb-3 fs-1"></div>
                <h5 class="fw-bold mb-1" id="chatActionConfirmTitle"></h5>
                <p class="text-muted small mb-0" id="chatActionConfirmDesc"></p>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn px-4" id="chatActionConfirmBtn"></button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script src="{{ asset('assets/common/js/chat.js') }}?v={{ filemtime(public_path('assets/common/js/chat.js')) }}"></script>
@endpush

@push('style')
<link rel="stylesheet" href="{{ asset('assets/common/css/chat.css') }}?v={{ filemtime(public_path('assets/common/css/chat.css')) }}">
@endpush

@push('css')
<link rel="stylesheet" href="{{ asset('assets/common/css/chat.css') }}?v={{ filemtime(public_path('assets/common/css/chat.css')) }}">
@if($context === 'front')
<style>
    .conversation-list-header {
        display: none !important;
    }

    .chat-bg-primary {
        background-color: rgba(var(--bs-primary-rgb), var(--bs-bg-opacity)) !important;
    }
</style>
@endif
@endpush