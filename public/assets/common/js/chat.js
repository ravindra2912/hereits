$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    }
});

document.addEventListener('click', async (event) => {
    const trigger = event.target.closest('[data-chat-start]');
    if (!trigger) {
        return;
    }

    event.preventDefault();

    try {
        const response = await $.ajax({
            url: trigger.dataset.chatStoreUrl,
            type: 'POST',
            data: {
                conversation_type: 'direct',
                participants: [
                    {
                        type: trigger.dataset.chatTargetType,
                        id: Number(trigger.dataset.chatTargetId),
                    },
                ],
            }
        });

        const conversationId = response?.data?.conversation?.id;
        const chatIndexUrl = trigger.dataset.chatIndexUrl || '/chat';

        if (conversationId) {
            window.location.href = `${chatIndexUrl}?conversation_id=${conversationId}`;
        }
    } catch (error) {
        window.toastr?.error(error.responseJSON?.message || 'Unable to start chat.');
    }
});

const root = document.getElementById('chatPage');

if (root) {
    const endpoints = JSON.parse(root.dataset.chatEndpoints || '{}');
    const initialConversations = JSON.parse(root.dataset.initialConversations || '[]');
    let selectedConversationId = Number(root.dataset.selectedConversationId) || 0;
    let selectedFiles = [];

    const conversationList = document.getElementById('chatConversationList');
    const conversationSearch = document.getElementById('chatConversationSearch');
    const conversationTitle = document.getElementById('chatConversationTitle');
    const conversationSubtitle = document.getElementById('chatConversationSubtitle');
    const chatConversationAvatarWrapper = document.getElementById('chatConversationAvatarWrapper');
    const chatActionDropdown = document.getElementById('chatActionDropdown');
    const chatBoardHeader = document.getElementById('chatBoardHeader');
    const emptyNotice = document.getElementById('chatEmptyNotice');
    const messageWrapper = document.getElementById('chatMessageWrapper');
    const messageList = document.getElementById('chatMessageList');
    const composerShell = document.getElementById('chatComposerShell');
    const composerForm = document.getElementById('chatComposerForm');
    const composerHint = document.getElementById('chatComposerHint');
    const composerNote = document.getElementById('chatComposerNote');
    const messageBody = document.getElementById('chatMessageBody');
    const bodyWrapper = document.getElementById('chatBodyWrapper');
    const actionFields = document.getElementById('chatActionFields');
    const actionTitle = document.getElementById('chatActionTitle');
    const actionReference = document.getElementById('chatActionReference');
    const actionNotes = document.getElementById('chatActionNotes');
    const attachmentsInput = document.getElementById('chatAttachments');
    const attachmentButton = document.getElementById('chatAttachmentButton');
    const sendButton = document.getElementById('chatSendButton');
    const typeButtons = Array.from(document.querySelectorAll('.chat-type-btn'));
    const conversationTypeSelect = document.getElementById('chatConversationType');
    const groupTitleWrapper = document.getElementById('chatGroupTitleWrapper');
    const groupTitleInput = document.getElementById('chatGroupTitle');
    const participantSearch = document.getElementById('chatParticipantSearch');
    const participantResults = document.getElementById('chatParticipantResults');
    const selectedParticipantsWrap = document.getElementById('chatSelectedParticipants');
    const createConversationButton = document.getElementById('chatCreateConversationButton');

    // Emoji picker and image preview DOM elements
    const emojiButton = document.getElementById('chatEmojiButton');
    const emojiPicker = document.getElementById('chatEmojiPicker');
    const emojiList = document.getElementById('chatEmojiList');

    const imagePreviewModalElement = document.getElementById('chatImagePreviewModal');
    const imagePreviewModal = imagePreviewModalElement ? new bootstrap.Modal(imagePreviewModalElement) : null;
    const previewContainer = document.getElementById('chatPreviewContainer');
    const cancelImagesBtn = document.getElementById('chatCancelImagesBtn');
    const confirmImagesBtn = document.getElementById('chatConfirmImagesBtn');
    const imagePreviewCloseBtn = document.getElementById('chatImagePreviewCloseBtn');

    const composerAttachmentPreview = document.getElementById('chatComposerAttachmentPreview');
    const composerAttachmentList = document.getElementById('chatComposerAttachmentList');

    const state = {
        conversations: initialConversations,
        activeConversation: null,
        currentMessageType: 'text',
        participants: [],
        participantSearchResults: [],
        searchTimer: null,
        hasMoreMessages: false,
        isLoadingMessages: false,
        isLoadingConversation: false
    };

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

    const formatTime = (value) => {
        if (!value) return '';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return '';
        return date.toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
    };

    const routeTemplate = (template, id) => template.replace('__ID__', id);
    const allowedMessageTypesForConversation = (conversationType) => conversationType === 'group'
        ? ['text', 'image']
        : ['text', 'image', 'inquiry', 'place_order'];
    const getCurrentConversationType = () => state.activeConversation?.conversation_type || 'direct';

    const toggleSendButton = () => {
        const hasText = messageBody.value.trim().length > 0;
        const hasAttachments = selectedFiles.length > 0;
        const isActiveConv = !!state.activeConversation;
        sendButton.disabled = !isActiveConv || (!hasText && !hasAttachments);
    };

    const renderSelectedParticipants = () => {
        if (!state.participants.length) {
            selectedParticipantsWrap.innerHTML = '<span class="text-muted small">No participants selected.</span>';
            return;
        }

        selectedParticipantsWrap.innerHTML = state.participants.map((participant) => `
            <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center gap-2 px-3 py-2">
                <span>${escapeHtml(participant.name)}</span>
                <button type="button" class="btn-close btn-close-sm" aria-label="Remove" data-remove-participant="${participant.type}:${participant.id}"></button>
            </span>
        `).join('');
    };

    const updateComposerControls = () => {
        const allowedTypes = allowedMessageTypesForConversation(getCurrentConversationType());
        typeButtons.forEach((button) => {
            const type = button.dataset.messageType;
            const enabled = allowedTypes.includes(type);
            button.disabled = !enabled;
            button.classList.toggle('active', type === state.currentMessageType);
        });

        const actionTypeSelected = ['inquiry', 'place_order'].includes(state.currentMessageType);
        actionFields.classList.toggle('d-none', !actionTypeSelected);
        bodyWrapper.classList.toggle('d-none', state.currentMessageType === 'image');
        if (getCurrentConversationType() === 'group' && actionTypeSelected) {
            state.currentMessageType = 'text';
            updateComposerControls();
        }
    };

    const renderMessages = () => {
        if (!state.activeConversation) {
            chatBoardHeader?.classList.add('d-none');
            emptyNotice.classList.remove('d-none');
            messageWrapper.classList.add('d-none');
            composerShell.classList.add('d-none');
            messageList.innerHTML = '';
            conversationTitle.textContent = 'Select a conversation';
            conversationSubtitle.textContent = 'Your messages will appear here';
            chatConversationAvatarWrapper.innerHTML = '<i class="bi bi-person-circle fs-4"></i>';
            chatConversationAvatarWrapper.classList.add('bg-secondary', 'bg-opacity-10', 'text-secondary');
            chatActionDropdown.innerHTML = '';
            composerHint.textContent = 'Choose a conversation first';
            sendButton.disabled = true;
            return;
        }

        chatBoardHeader?.classList.remove('d-none');
        emptyNotice.classList.add('d-none');
        messageWrapper.classList.remove('d-none');
        composerShell.classList.remove('d-none');
        composerShell.classList.remove('d-none');
        
        if (state.isLoadingConversation) {
            messageList.innerHTML = `
                <div class="text-center py-5 w-100 h-100 d-flex justify-content-center align-items-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading chat...</span>
                    </div>
                </div>
            `;
        } else {
            messageList.innerHTML = state.activeConversation.messages?.length
                ? state.activeConversation.messages.map(renderMessage).join('')
                : '<div class="text-center text-muted py-5">No messages yet. Send the first one.</div>';
        }

        conversationTitle.textContent = state.activeConversation.display_name || 'Conversation';
        conversationSubtitle.textContent = state.activeConversation.display_subtitle || '';

        if (state.activeConversation.avatar) {
            chatConversationAvatarWrapper.innerHTML = `<img src="${escapeHtml(state.activeConversation.avatar)}" alt="" class="rounded-circle w-100 h-100" style="object-fit:cover;">`;
            chatConversationAvatarWrapper.classList.remove('bg-secondary', 'bg-opacity-10', 'text-secondary');
        } else {
            chatConversationAvatarWrapper.innerHTML = state.activeConversation.conversation_type === 'group'
                ? `<i class="bi bi-people fs-4"></i>`
                : `<i class="bi bi-person-circle fs-4"></i>`;
            chatConversationAvatarWrapper.classList.add('bg-secondary', 'bg-opacity-10', 'text-secondary');
        }

        let dropdownHtml = '';
        if (state.activeConversation.conversation_type === 'group') {
            const isAdmin = state.activeConversation.current_participant_role === 'admin';
            if (isAdmin) {
                dropdownHtml = `
                    <li><a class="dropdown-item text-danger chat-action-delete" href="#" data-id="${state.activeConversation.id}"><i class="bi bi-trash me-2"></i>Delete group</a></li>
                `;
            } else {
                dropdownHtml = `
                    <li><a class="dropdown-item text-danger chat-action-leave" href="#" data-id="${state.activeConversation.id}"><i class="bi bi-box-arrow-right me-2"></i>Leave group</a></li>
                `;
            }
        } else {
            dropdownHtml = `
                <li><a class="dropdown-item chat-action-clear" href="#" data-id="${state.activeConversation.id}"><i class="bi bi-eraser me-2"></i>Clear chat</a></li>
                <li><a class="dropdown-item text-danger chat-action-delete" href="#" data-id="${state.activeConversation.id}"><i class="bi bi-trash me-2"></i>Delete chat</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger chat-action-block" href="#" data-id="${state.activeConversation.id}"><i class="bi bi-slash-circle me-2"></i>Block user</a></li>
            `;
        }
        chatActionDropdown.innerHTML = dropdownHtml;

        composerHint.textContent = state.activeConversation.conversation_type === 'group'
            ? 'Group conversations allow text and image only.'
            : 'Direct chats allow text, image, inquiry, and order updates.';
        toggleSendButton();
        updateComposerControls();
    };

    const renderMessage = (message) => {
        if (message.is_system) {
            return `
                <div class="text-center">
                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2">${escapeHtml(message.body || message.action_type || 'System update')}</span>
                    <div class="small text-muted mt-1">${escapeHtml(formatTime(message.created_at))}</div>
                </div>
            `;
        }

        const isMine = message.is_mine;
        const bubbleClass = isMine ? 'chat-message-outgoing ms-auto' : 'chat-message-incoming me-auto';
        const textClass = isMine ? 'text-dark' : 'text-dark';
        const metaClass = isMine ? 'text-success-emphasis' : 'text-muted';
        const attachments = Array.isArray(message.attachments) ? message.attachments : [];
        const attachmentsMarkup = attachments.length
            ? `<div class="d-flex flex-wrap gap-2 mt-2">${attachments.map((attachment) => `<a href="${escapeHtml(attachment.url)}" target="_blank" rel="noopener" class="d-inline-block"><img src="${escapeHtml(attachment.url)}" alt="${escapeHtml(attachment.original_name)}" class="rounded-3 border chat-message-image"></a>`).join('')}</div>`
            : '';
        const actionBadge = message.action_type
            ? `<span class="badge ${isMine ? 'bg-light text-primary' : 'bg-primary-subtle text-primary'} rounded-pill mt-2">${escapeHtml(message.action_type.replace('_', ' '))}</span>`
            : '';

        return `
            <div class="d-flex ${isMine ? 'justify-content-end' : 'justify-content-start'}">
                <div class="rounded-4 p-3 chat-message-bubble ${bubbleClass}">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                        <strong class="small ${textClass}">${escapeHtml(message.sender?.name || 'User')}</strong>
                        <small class="${metaClass}">${escapeHtml(formatTime(message.created_at))}</small>
                    </div>
                    ${message.body ? `<div class="${textClass}">${escapeHtml(message.body)}</div>` : ''}
                    ${actionBadge}
                    ${attachmentsMarkup}
                </div>
            </div>
        `;
    };
    const renderConversationList = () => {
        if (!state.conversations.length) {
            conversationList.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-chat-dots fs-1 d-block mb-2"></i>
                    <div class="fw-semibold">No conversations yet</div>
                    <div class="small">Create a direct or group chat to get started.</div>
                </div>
            `;
            return;
        }

        conversationList.innerHTML = state.conversations.map((conversation) => {
            const activeClass = state.activeConversation?.id === conversation.id ? 'active' : '';
            const unreadBadge = conversation.is_unread ? '<span class="badge bg-danger rounded-pill">New</span>' : '';
            const avatarMarkup = conversation.avatar
                ? `<img src="${escapeHtml(conversation.avatar)}" alt="${escapeHtml(conversation.display_name)}" class="rounded-circle me-3 chat-avatar-media">`
                : `<div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-inline-flex align-items-center justify-content-center me-3 chat-avatar-circle"><i class="bi bi-people"></i></div>`;

            return `
                <button type="button" class="list-group-item list-group-item-action chat-conversation-item rounded-4 mb-2 ${activeClass}" data-conversation-id="${conversation.id}">
                    <div class="d-flex align-items-center">
                        ${avatarMarkup}
                        <div class="flex-grow-1 text-start">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div class="fw-semibold text-truncate">${escapeHtml(conversation.display_name)}</div>
                                ${unreadBadge}
                            </div>
                            <div class="small text-truncate ${state.activeConversation?.id === conversation.id ? 'text-success-emphasis' : 'text-muted'}">${escapeHtml(conversation.last_message_preview)}</div>
                            <div class="small ${state.activeConversation?.id === conversation.id ? 'text-success-emphasis' : 'text-muted'}">${escapeHtml(formatTime(conversation.last_message_at))}</div>
                        </div>
                    </div>
                </button>
            `;
        }).join('');
    };

    const setActiveConversation = (conversationId) => {
        state.activeConversation = state.conversations.find((item) => String(item.id) === String(conversationId)) || null;
        renderConversationList();
        renderMessages();
    };

    const openConversation = async (conversationId) => {
        try {
            state.isLoadingConversation = true;
            setActiveConversation(conversationId);

            const response = await $.ajax({
                url: routeTemplate(endpoints.showConversation, conversationId),
                type: 'GET'
            });
            state.activeConversation = response?.data?.conversation || null;
            if (state.activeConversation) {
                state.activeConversation.messages = response?.data?.messages || [];
            }
            state.hasMoreMessages = response?.data?.has_more || false;
            selectedConversationId = conversationId;
            if (window.history?.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.set('conversation_id', conversationId);
                window.history.replaceState({}, '', url.toString());
            }
            
            state.isLoadingConversation = false;
            renderConversationList();
            renderMessages();
            messageWrapper.scrollTop = messageWrapper.scrollHeight;
            
            await $.ajax({
                url: routeTemplate(endpoints.markRead, conversationId),
                type: 'POST'
            });
        } catch (error) {
            state.isLoadingConversation = false;
            window.toastr?.error(error.responseJSON?.message || 'Unable to open conversation.');
        }
    };

    const loadMoreMessages = async () => {
        if (!state.activeConversation || !state.activeConversation.messages.length || state.isLoadingMessages) return;

        state.isLoadingMessages = true;
        const beforeId = state.activeConversation.messages[0].id;
        const oldScrollHeight = messageWrapper.scrollHeight;

        const spinnerId = 'chatLoadingSpinner';
        messageList.insertAdjacentHTML('afterbegin', `
            <div id="${spinnerId}" class="text-center py-3 w-100">
                <div class="spinner-border text-primary" role="status" style="width: 1.5rem; height: 1.5rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);

        try {
            const response = await $.ajax({
                url: routeTemplate(endpoints.showConversation, state.activeConversation.id),
                type: 'GET',
                data: { before_id: beforeId }
            });

            const olderMessages = response?.data?.messages || [];
            if (olderMessages.length > 0) {
                state.activeConversation.messages = [...olderMessages, ...state.activeConversation.messages];

                messageList.innerHTML = state.activeConversation.messages.map(renderMessage).join('');

                const newScrollHeight = messageWrapper.scrollHeight;
                messageWrapper.scrollTop = newScrollHeight - oldScrollHeight;
            } else {
                const spinner = document.getElementById(spinnerId);
                if (spinner) spinner.remove();
            }
            state.hasMoreMessages = response?.data?.has_more || false;
        } catch (error) {
            console.error('Error loading older messages', error);
            const spinner = document.getElementById(spinnerId);
            if (spinner) spinner.remove();
        } finally {
            state.isLoadingMessages = false;
        }
    };

    messageWrapper.addEventListener('scroll', async () => {
        if (messageWrapper.scrollTop <= 50 && state.hasMoreMessages && !state.isLoadingMessages && state.activeConversation) {
            await loadMoreMessages();
        }
    });

    const fetchConversations = async () => {
        try {
            const response = await $.ajax({
                url: endpoints.conversations,
                type: 'GET'
            });
            state.conversations = response?.data?.conversations || [];
            renderConversationList();

            if (selectedConversationId > 0) {
                await openConversation(selectedConversationId);
                return;
            }

            if (!state.activeConversation) {
                renderMessages();
            }
        } catch (error) {
            conversationList.innerHTML = '<div class="text-danger small">Unable to load conversations.</div>';
        }
    };

    let searchParticipantsAjax = null;
    const searchParticipants = async (term) => {
        if (term.trim().length < 2) {
            participantResults.innerHTML = '<div class="p-3 text-muted small">Type at least 2 characters to search.</div>';
            return;
        }

        if (searchParticipantsAjax) {
            searchParticipantsAjax.abort();
        }

        try {
            searchParticipantsAjax = $.ajax({
                url: endpoints.searchParticipants,
                type: 'GET',
                data: { q: term }
            });
            const response = await searchParticipantsAjax;
            state.participantSearchResults = response?.data?.results || [];

            if (!state.participantSearchResults.length) {
                participantResults.innerHTML = '<div class="p-3 text-muted small">No participants found.</div>';
                return;
            }

            participantResults.innerHTML = state.participantSearchResults.map((participant) => {
                const selected = state.participants.some((item) => item.type === participant.type && String(item.id) === String(participant.id));
                return `
                    <button type="button" class="list-group-item list-group-item-action border-0 border-bottom py-3" data-add-participant="${participant.type}:${participant.id}">
                        <div class="d-flex align-items-center gap-3">
                            <img src="${escapeHtml(participant.avatar)}" alt="${escapeHtml(participant.name)}" class="rounded-circle" width="42" height="42">
                            <div class="flex-grow-1 text-start">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <div class="fw-semibold">${escapeHtml(participant.name)}</div>
                                    ${selected ? '<span class="badge bg-success rounded-pill">Selected</span>' : ''}
                                </div>
                                <div class="small text-muted">${escapeHtml(participant.subtitle)}</div>
                            </div>
                        </div>
                    </button>
                `;
            }).join('');
        } catch (error) {
            if (error.statusText === 'abort') return;
            participantResults.innerHTML = '<div class="p-3 text-danger small">Unable to search participants.</div>';
        }
    };

    const addParticipant = (participant) => {
        const exists = state.participants.some((item) => item.type === participant.type && String(item.id) === String(participant.id));
        if (exists) return;

        if (conversationTypeSelect.value === 'direct') {
            state.participants = [participant];
        } else {
            state.participants.push(participant);
        }

        renderSelectedParticipants();
        if (participantSearch.value.trim().length >= 2) {
            searchParticipants(participantSearch.value.trim());
        }
    };

    const removeParticipant = (key) => {
        state.participants = state.participants.filter((item) => `${item.type}:${item.id}` !== key);
        renderSelectedParticipants();
        if (participantSearch.value.trim().length >= 2) {
            searchParticipants(participantSearch.value.trim());
        }
    };

    conversationList.addEventListener('click', (event) => {
        const button = event.target.closest('[data-conversation-id]');
        if (!button) return;
        openConversation(button.dataset.conversationId);
    });

    conversationSearch.addEventListener('input', () => {
        const term = conversationSearch.value.trim().toLowerCase();
        Array.from(conversationList.querySelectorAll('[data-conversation-id]')).forEach((item) => {
            const visible = !term || item.textContent.toLowerCase().includes(term);
            item.classList.toggle('d-none', !visible);
        });
    });

    participantSearch.addEventListener('input', () => {
        clearTimeout(state.searchTimer);
        state.searchTimer = setTimeout(() => searchParticipants(participantSearch.value), 500);
    });

    participantResults.addEventListener('click', (event) => {
        const button = event.target.closest('[data-add-participant]');
        if (!button) return;
        const [type, id] = button.dataset.addParticipant.split(':');
        const found = state.participantSearchResults.find((participant) => participant.type === type && String(participant.id) === String(id));
        if (found) addParticipant(found);
    });

    selectedParticipantsWrap.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-participant]');
        if (!button) return;
        removeParticipant(button.dataset.removeParticipant);
    });

    conversationTypeSelect.addEventListener('change', () => {
        const isGroup = conversationTypeSelect.value === 'group';
        groupTitleWrapper.classList.toggle('d-none', !isGroup);
        if (!isGroup) {
            groupTitleInput.value = '';
            if (state.participants.length > 1) {
                state.participants = state.participants.slice(0, 1);
                renderSelectedParticipants();
            }
        }
    });

    typeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const type = button.dataset.messageType;
            const allowedTypes = allowedMessageTypesForConversation(getCurrentConversationType());
            if (!allowedTypes.includes(type)) return;
            state.currentMessageType = type;
            updateComposerControls();
        });
    });

    // Emoji Picker initialization and event handlers
    const popularEmojis = [
        '😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','😜','🤪','🤨','🧐','🤓','😎','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','☹️','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡','🤬','🤯','😳','🥵','🥶','😱','😨','😰','😥','😓','🤗','🤔','🤭','🤫','🤥','😶','😐','😑','😬','🙄','😯','😦','😧','😮','😲','🥱','😴','🤤','😪','😵','🤐','🥴','🤢','🤮','🤧','😷','🤒','🤕',
        '👋','🤚','🖐️','✋','🖖','👌','🤌','🤏','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','🖕','👇','☝️','👍','👎','✊','👊','🤛','🤜','👏','🙌','👐','🤲','🤝','🙏','✍️','💅','🤳','💪','🦾','🦿','🦵','🦶','👂','🦻','👃','🧠','🫀','🫁','🦷','🦴','👀','👁️','👅','👄',
        '❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❤️‍🔥','❤️‍🩹','❣️','💕','💞','💓','💗','💖','💘','💝','💟','🌟','⭐','✨','⚡','💥','🔥','🌈','☀️','🌤️','⛅','🌥️','☁️','🌦️','🌧️','⛈️','🌩️','🌨️','❄️','💨','💧','💦','🫧'
    ];

    if (emojiList) {
        emojiList.innerHTML = popularEmojis.map(emoji => `<span class="p-1 emoji-item" style="cursor: pointer; user-select: none;">${emoji}</span>`).join('');
    }

    if (emojiButton && emojiPicker) {
        emojiButton.addEventListener('click', (e) => {
            e.stopPropagation();
            emojiPicker.classList.toggle('d-none');
        });

        // Close emoji picker when clicking outside
        document.addEventListener('click', (e) => {
            if (!emojiPicker.classList.contains('d-none') && !emojiPicker.contains(e.target) && e.target !== emojiButton && !emojiButton.contains(e.target)) {
                emojiPicker.classList.add('d-none');
            }
        });
    }

    if (emojiList) {
        emojiList.addEventListener('click', (e) => {
            const emojiSpan = e.target.closest('.emoji-item');
            if (!emojiSpan) return;
            const emojiText = emojiSpan.textContent;
            
            const startPos = messageBody.selectionStart;
            const endPos = messageBody.selectionEnd;
            messageBody.value = messageBody.value.substring(0, startPos) + emojiText + messageBody.value.substring(endPos, messageBody.value.length);
            messageBody.focus();
            messageBody.selectionStart = startPos + emojiText.length;
            messageBody.selectionEnd = startPos + emojiText.length;

            toggleSendButton();
        });
    }

    if (messageBody) {
        messageBody.addEventListener('input', () => {
            toggleSendButton();
        });
    }

    // Selected image/attachment previews helpers
    const renderComposerPreviews = () => {
        if (!composerAttachmentPreview || !composerAttachmentList) return;
        if (selectedFiles.length === 0) {
            composerAttachmentPreview.classList.add('d-none');
            composerAttachmentList.innerHTML = '';
            toggleSendButton();
            return;
        }

        composerAttachmentPreview.classList.remove('d-none');
        composerAttachmentList.innerHTML = selectedFiles.map((file, index) => {
            const url = URL.createObjectURL(file);
            return `
                <div class="position-relative border rounded-3 p-1 bg-white shadow-sm" style="width: 70px; height: 70px;">
                    <img src="${url}" class="rounded-2 w-100 h-100" style="object-fit: cover;">
                    <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 d-flex align-items-center justify-content-center chat-remove-composer-image" style="width: 18px; height: 18px; padding: 0; transform: translate(30%, -30%);" data-index="${index}">
                        <i class="bi bi-x" style="font-size: 12px;"></i>
                    </button>
                </div>
            `;
        }).join('');

        toggleSendButton();
    };

    attachmentButton.addEventListener('click', () => attachmentsInput.click());

    if (attachmentsInput) {
        attachmentsInput.addEventListener('change', () => {
            if (attachmentsInput.files && attachmentsInput.files.length > 0) {
                Array.from(attachmentsInput.files).forEach(file => {
                    selectedFiles.push(file);
                });
                attachmentsInput.value = '';
                renderComposerPreviews();
            }
        });
    }

    if (composerAttachmentList) {
        composerAttachmentList.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.chat-remove-composer-image');
            if (!removeBtn) return;
            const index = parseInt(removeBtn.dataset.index);
            selectedFiles.splice(index, 1);
            renderComposerPreviews();
        });
    }

    composerForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!state.activeConversation) {
            window.toastr?.error('Select a conversation first.');
            return;
        }

        let determinedType = 'text';
        if (selectedFiles.length > 0) {
            determinedType = 'image';
        }

        const formData = new FormData();
        formData.append('message_type', determinedType);
        formData.append('body', messageBody.value || '');
        formData.append('metadata', JSON.stringify({
            title: actionTitle.value || null,
            reference: actionReference.value || null,
            notes: actionNotes.value || null,
        }));

        selectedFiles.forEach((file) => formData.append('attachments[]', file));

        try {
            const response = await $.ajax({
                url: routeTemplate(endpoints.storeMessage, state.activeConversation.id),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false
            });

            const message = response?.data?.message;
            if (message) {
                state.activeConversation.messages.push(message);
                state.conversations = state.conversations.map((conversation) => conversation.id === state.activeConversation.id ? {
                    ...conversation,
                    last_message: message,
                    last_message_preview: message.body || message.action_type || 'Image message',
                    last_message_at: message.created_at,
                    is_unread: false,
                } : conversation);
                messageBody.value = '';
                actionTitle.value = '';
                actionReference.value = '';
                actionNotes.value = '';
                attachmentsInput.value = '';
                selectedFiles = [];
                renderComposerPreviews();
                state.currentMessageType = 'text';
                renderConversationList();
                renderMessages();
                messageWrapper.scrollTop = messageWrapper.scrollHeight;
            }
        } catch (error) {
            window.toastr?.error(error.responseJSON?.message || 'Unable to send message.');
        }
    });

    createConversationButton.addEventListener('click', async () => {
        const conversationType = conversationTypeSelect.value;
        const participants = state.participants.map((participant) => ({ type: participant.type, id: participant.id }));
        const title = conversationType === 'group' ? groupTitleInput.value.trim() : null;

        if (!participants.length) {
            window.toastr?.error('Select at least one participant.');
            return;
        }

        if (conversationType === 'group' && participants.length < 2) {
            window.toastr?.error('Group chat needs at least two participants besides you.');
            return;
        }

        try {
            const response = await $.ajax({
                url: endpoints.storeConversation,
                type: 'POST',
                data: {
                    conversation_type: conversationType,
                    title,
                    participants,
                }
            });

            const conversation = response?.data?.conversation;
            const modal = bootstrap.Modal.getInstance(document.getElementById('chatConversationModal'));
            modal?.hide();
            state.participants = [];
            renderSelectedParticipants();
            await fetchConversations();
            await openConversation(conversation.id);
        } catch (error) {
            window.toastr?.error(error.responseJSON?.message || 'Unable to create conversation.');
        }
    });

    renderSelectedParticipants();
    renderConversationList();
    updateComposerControls();
    renderMessages();
    fetchConversations();

    // ── Chat action dropdown handler ────────────────────────────────────────
    const confirmModal = new bootstrap.Modal(document.getElementById('chatActionConfirmModal'));
    const confirmIcon = document.getElementById('chatActionConfirmIcon');
    const confirmTitle = document.getElementById('chatActionConfirmTitle');
    const confirmDesc = document.getElementById('chatActionConfirmDesc');
    const confirmBtn = document.getElementById('chatActionConfirmBtn');

    const ACTION_CONFIGS = {
        clear: {
            icon: '🗑️',
            title: 'Clear Chat?',
            desc: 'All messages in this conversation will be permanently deleted.',
            btnText: 'Clear',
            btnClass: 'btn-warning',
        },
        delete: {
            icon: '❌',
            title: 'Delete Chat?',
            desc: 'This conversation will be permanently deleted and removed from your list.',
            btnText: 'Delete',
            btnClass: 'btn-danger',
        },
        block: {
            icon: '🚫',
            title: 'Block User?',
            desc: 'This user will be blocked and the conversation will be closed.',
            btnText: 'Block',
            btnClass: 'btn-danger',
        },
        leave: {
            icon: '👋',
            title: 'Leave Group?',
            desc: 'You will no longer receive messages from this group.',
            btnText: 'Leave',
            btnClass: 'btn-warning',
        },
    };

    let pendingAction = null;

    document.addEventListener('click', (event) => {
        const clearTrigger = event.target.closest('.chat-action-clear');
        const deleteTrigger = event.target.closest('.chat-action-delete');
        const blockTrigger = event.target.closest('.chat-action-block');
        const leaveTrigger = event.target.closest('.chat-action-leave');

        const trigger = clearTrigger || deleteTrigger || blockTrigger || leaveTrigger;
        if (!trigger) return;

        event.preventDefault();
        const conversationId = trigger.dataset.id;
        let actionKey = '';
        if (clearTrigger) actionKey = 'clear';
        if (deleteTrigger) actionKey = 'delete';
        if (blockTrigger) actionKey = 'block';
        if (leaveTrigger) actionKey = 'leave';

        const config = ACTION_CONFIGS[actionKey];
        confirmIcon.textContent = config.icon;
        confirmTitle.textContent = config.title;
        confirmDesc.textContent = config.desc;
        confirmBtn.textContent = config.btnText;
        confirmBtn.className = `btn px-4 ${config.btnClass}`;

        pendingAction = { actionKey, conversationId };
        confirmModal.show();
    });

    confirmBtn.addEventListener('click', async () => {
        if (!pendingAction) return;
        const { actionKey, conversationId } = pendingAction;
        pendingAction = null;
        confirmModal.hide();

        const methodMap = {
            clear: { url: endpoints.clear, method: 'POST' },
            delete: { url: endpoints.delete, method: 'DELETE' },
            block: { url: endpoints.block, method: 'POST' },
            leave: { url: endpoints.leave, method: 'POST' },
        };

        const { url, method } = methodMap[actionKey];

        try {
            const response = await $.ajax({
                url: routeTemplate(url, conversationId),
                type: method,
            });

            window.toastr?.success(response.message || 'Done.');

            if (actionKey === 'clear') {
                if (state.activeConversation) {
                    state.activeConversation.messages = [];
                    renderMessages();
                }
            } else {
                state.conversations = state.conversations.filter((c) => String(c.id) !== String(conversationId));
                state.activeConversation = null;
                renderConversationList();
                renderMessages();
                chatBoardHeader?.classList.add('d-none');
            }
        } catch (error) {
            window.toastr?.error(error.responseJSON?.message || 'Action failed.');
        }
    });

    // ── Chat Profile Modal ────────────────────────────────────────
    const profileModal = new bootstrap.Modal(document.getElementById('chatProfileModal'));
    const profileAvatar = document.getElementById('chatProfileAvatar');
    const profileImageEditBtn = document.getElementById('chatProfileImageEditBtn');
    const profileImageInput = document.getElementById('chatProfileImageInput');
    const profileTitleWrapper = document.getElementById('chatProfileTitleWrapper');
    const profileTitle = document.getElementById('chatProfileTitle');
    const profileNameEditBtn = document.getElementById('chatProfileNameEditBtn');
    const profileNameEditSection = document.getElementById('chatProfileNameEditSection');
    const profileNameInput = document.getElementById('chatProfileNameInput');
    const profileNameSaveBtn = document.getElementById('chatProfileNameSaveBtn');
    const profileNameCancelBtn = document.getElementById('chatProfileNameCancelBtn');
    const profileSubtitle = document.getElementById('chatProfileSubtitle');
    const profileMembersSection = document.getElementById('chatProfileMembersSection');
    const profileMembersList = document.getElementById('chatProfileMembersList');
    const profileAddMemberBtn = document.getElementById('chatProfileAddMemberBtn');
    const profileAddMemberSection = document.getElementById('chatProfileAddMemberSection');
    const profileSearchInput = document.getElementById('chatProfileSearchInput');
    const profileSearchResults = document.getElementById('chatProfileSearchResults');

    chatConversationAvatarWrapper.parentElement.style.cursor = 'pointer';
    chatConversationAvatarWrapper.parentElement.addEventListener('click', () => {
        if (!state.activeConversation) return;

        const conv = state.activeConversation;
        const isAdmin = conv.current_participant_role === 'admin';
        const isGroup = conv.conversation_type === 'group';

        profileAvatar.innerHTML = conv.avatar
            ? `<img src="${conv.avatar}" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">`
            : `<i class="bi bi-person-circle"></i>`;

        profileTitle.textContent = conv.display_name;
        profileSubtitle.textContent = conv.display_subtitle;
        
        profileTitleWrapper.classList.remove('d-none');
        profileNameEditSection.classList.add('d-none');

        if (isGroup && isAdmin) {
            profileImageEditBtn.classList.remove('d-none');
            profileNameEditBtn.classList.remove('d-none');
        } else {
            profileImageEditBtn.classList.add('d-none');
            profileNameEditBtn.classList.add('d-none');
        }

        if (isGroup) {
            profileMembersSection.classList.remove('d-none');
            if (isAdmin) {
                profileAddMemberBtn.classList.remove('d-none');
            } else {
                profileAddMemberBtn.classList.add('d-none');
            }
            renderProfileMembers();
        } else {
            profileMembersSection.classList.add('d-none');
        }

        profileAddMemberSection.classList.add('d-none');
        profileModal.show();
    });

    function renderProfileMembers() {
        if (!state.activeConversation) return;
        const conv = state.activeConversation;
        const isAdmin = conv.current_participant_role === 'admin';

        profileMembersList.innerHTML = conv.participants.map(p => `
            <div class="d-flex align-items-center justify-content-between p-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                        ${p.avatar ? `<img src="${p.avatar}" class="rounded-circle w-100 h-100" style="object-fit: cover;">` : `<i class="bi bi-person"></i>`}
                    </div>
                    <div>
                        <div class="fw-medium small">${p.name} ${p.role === 'admin' ? '<span class="badge bg-primary ms-1">Admin</span>' : ''}</div>
                        <div class="text-muted" style="font-size: 0.7rem;">${p.subtitle}</div>
                    </div>
                </div>
                ${isAdmin && p.role !== 'admin' ? `
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 profile-remove-member" data-type="${p.type}" data-id="${p.id}">
                        <i class="bi bi-x-circle"></i>
                    </button>
                ` : ''}
            </div>
        `).join('');
    }

    profileImageEditBtn.addEventListener('click', () => {
        profileImageInput.click();
    });

    profileImageInput.addEventListener('change', async (e) => {
        if (!e.target.files[0]) return;
        const convId = state.activeConversation.id;
        const formData = new FormData();
        formData.append('image', e.target.files[0]);

        try {
            const response = await $.ajax({
                url: routeTemplate(endpoints.update, convId),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
            });
            window.toastr?.success('Image updated successfully.');
            state.activeConversation = response.data.conversation;
            state.conversations = state.conversations.map(c => String(c.id) === String(convId) ? response.data.conversation : c);
            
            profileAvatar.innerHTML = `<img src="${response.data.conversation.avatar}" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">`;
            renderConversationList();
            
            conversationTitle.textContent = response.data.conversation.display_name;
            conversationSubtitle.textContent = response.data.conversation.display_subtitle;
            chatConversationAvatarWrapper.innerHTML = response.data.conversation.avatar 
                ? `<img src="${response.data.conversation.avatar}" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">`
                : `<i class="bi bi-person-circle fs-4"></i>`;
        } catch (error) {
            window.toastr?.error('Failed to update group image.');
        }
        e.target.value = '';
    });

    profileNameEditBtn.addEventListener('click', () => {
        profileTitleWrapper.classList.add('d-none');
        profileNameEditSection.classList.remove('d-none');
        profileNameEditSection.classList.add('d-flex');
        profileNameInput.value = state.activeConversation.title || '';
        profileNameInput.focus();
    });

    profileNameCancelBtn.addEventListener('click', () => {
        profileNameEditSection.classList.remove('d-flex');
        profileNameEditSection.classList.add('d-none');
        profileTitleWrapper.classList.remove('d-none');
    });

    profileNameSaveBtn.addEventListener('click', async () => {
        const newName = profileNameInput.value.trim();
        if (!newName) return;
        
        const convId = state.activeConversation.id;
        const formData = new FormData();
        formData.append('title', newName);

        try {
            const response = await $.ajax({
                url: routeTemplate(endpoints.update, convId),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
            });
            window.toastr?.success('Name updated successfully.');
            state.activeConversation = response.data.conversation;
            state.conversations = state.conversations.map(c => String(c.id) === String(convId) ? response.data.conversation : c);
            
            profileTitle.textContent = response.data.conversation.display_name;
            profileNameCancelBtn.click();
            renderConversationList();
            
            conversationTitle.textContent = response.data.conversation.display_name;
            conversationSubtitle.textContent = response.data.conversation.display_subtitle;
            chatConversationAvatarWrapper.innerHTML = response.data.conversation.avatar 
                ? `<img src="${response.data.conversation.avatar}" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">`
                : `<i class="bi bi-person-circle fs-4"></i>`;
        } catch (error) {
            window.toastr?.error('Failed to update group name.');
        }
    });

    profileAddMemberBtn.addEventListener('click', () => {
        profileAddMemberSection.classList.toggle('d-none');
        if (!profileAddMemberSection.classList.contains('d-none')) {
            profileSearchInput.focus();
        }
    });

    let profileSearchTimer;
    let profileSearchAjax = null;
    profileSearchInput.addEventListener('input', (e) => {
        clearTimeout(profileSearchTimer);
        const q = e.target.value.trim();
        if (!q) {
            profileSearchResults.innerHTML = '';
            return;
        }
        profileSearchTimer = setTimeout(async () => {
            if (profileSearchAjax) {
                profileSearchAjax.abort();
            }
            try {
                profileSearchAjax = $.ajax({
                    url: endpoints.searchParticipants,
                    data: { q }
                });
                const response = await profileSearchAjax;

                const existingParticipantKeys = state.activeConversation.participants.map(p => `${p.type}_${p.id}`);

                profileSearchResults.innerHTML = (response.data.results || []).map(p => {
                    const isExisting = existingParticipantKeys.includes(`${p.type}_${p.id}`);
                    return `
                    <div class="p-2 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                ${p.avatar ? `<img src="${p.avatar}" class="rounded-circle w-100 h-100" style="object-fit: cover;">` : `<i class="bi bi-person"></i>`}
                            </div>
                            <div class="small fw-medium">${p.name}</div>
                        </div>
                        ${isExisting ? '<span class="text-muted small">Added</span>' : `
                        <button type="button" class="btn btn-sm btn-primary profile-add-participant-btn" data-type="${p.type}" data-id="${p.id}">
                            Add
                        </button>`}
                    </div>
                `}).join('');
            } catch (error) { }
        }, 500);
    });

    profileSearchResults.addEventListener('click', async (e) => {
        const btn = e.target.closest('.profile-add-participant-btn');
        if (!btn) return;

        const type = btn.dataset.type;
        const id = btn.dataset.id;
        const convId = state.activeConversation.id;

        try {
            const response = await $.ajax({
                url: routeTemplate(endpoints.addMember, convId),
                type: 'POST',
                data: { participant: { type, id } }
            });
            window.toastr?.success(response.message);
            const messages = state.activeConversation.messages;
            state.activeConversation = response.data.conversation;
            state.activeConversation.messages = messages;
            renderProfileMembers();
            profileSearchInput.value = '';
            profileSearchResults.innerHTML = '';
            profileAddMemberSection.classList.add('d-none');
            
            state.conversations = state.conversations.map(c => String(c.id) === String(convId) ? response.data.conversation : c);
            renderConversationList();

            const conv = state.activeConversation;
            conversationTitle.textContent = conv.display_name;
            conversationSubtitle.textContent = conv.display_subtitle;
            profileSubtitle.textContent = conv.display_subtitle;
            chatConversationAvatarWrapper.innerHTML = conv.avatar
                ? `<img src="${conv.avatar}" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">`
                : `<i class="bi bi-person-circle fs-4"></i>`;
        } catch (error) {
            window.toastr?.error(error.responseJSON?.message || 'Failed to add member.');
        }
    });

    profileMembersList.addEventListener('click', async (e) => {
        const btn = e.target.closest('.profile-remove-member');
        if (!btn) return;

        if (!confirm('Are you sure you want to remove this member?')) return;

        const type = btn.dataset.type;
        const id = btn.dataset.id;
        const convId = state.activeConversation.id;

        try {
            const response = await $.ajax({
                url: routeTemplate(endpoints.removeMember, convId),
                type: 'POST',
                data: { participant: { type, id } }
            });
            window.toastr?.success(response.message);
            const messages = state.activeConversation.messages;
            state.activeConversation = response.data.conversation;
            state.activeConversation.messages = messages;
            renderProfileMembers();
            state.conversations = state.conversations.map(c => String(c.id) === String(convId) ? response.data.conversation : c);
            renderConversationList();

            const conv = state.activeConversation;
            conversationTitle.textContent = conv.display_name;
            conversationSubtitle.textContent = conv.display_subtitle;
            profileSubtitle.textContent = conv.display_subtitle;
            chatConversationAvatarWrapper.innerHTML = conv.avatar
                ? `<img src="${conv.avatar}" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">`
                : `<i class="bi bi-person-circle fs-4"></i>`;
        } catch (error) {
            window.toastr?.error(error.responseJSON?.message || 'Failed to remove member.');
        }
    });
}
