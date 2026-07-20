document.addEventListener('click', async (event) => {
    const trigger = event.target.closest('[data-chat-start]');
    if (!trigger) {
        return;
    }

    event.preventDefault();

    try {
        const response = await axios.post(trigger.dataset.chatStoreUrl, {
            conversation_type: 'direct',
            participants: [
                {
                    type: trigger.dataset.chatTargetType,
                    id: Number(trigger.dataset.chatTargetId),
                },
            ],
        });

        const conversationId = response.data?.data?.conversation?.id;
        const chatIndexUrl = trigger.dataset.chatIndexUrl || '/chat';

        if (conversationId) {
            window.location.href = `${chatIndexUrl}?conversation_id=${conversationId}`;
        }
    } catch (error) {
        window.toastr?.error(error.response?.data?.message || 'Unable to start chat.');
    }
});

const root = document.getElementById('chatPage');

if (root) {
    const endpoints = JSON.parse(root.dataset.chatEndpoints || '{}');
    const initialConversations = JSON.parse(root.dataset.initialConversations || '[]');
    let selectedConversationId = root.dataset.selectedConversationId || '';
    const actorType = root.dataset.actorType || '';
    const actorId = Number(root.dataset.actorId) || 0;

    const conversationList = document.getElementById('chatConversationList');
    const conversationSearch = document.getElementById('chatConversationSearch');
    const conversationTitle = document.getElementById('chatConversationTitle');
    const conversationSubtitle = document.getElementById('chatConversationSubtitle');
    const conversationTypeBadge = document.getElementById('chatConversationTypeBadge');
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

    const state = {
        conversations: initialConversations,
        activeConversation: null,
        currentMessageType: 'text',
        participants: [],
        participantSearchResults: [],
        searchTimer: null,
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
            emptyNotice.classList.remove('d-none');
            messageWrapper.classList.add('d-none');
            composerShell.classList.add('d-none');
            messageList.innerHTML = '';
            conversationTitle.textContent = 'Select a conversation';
            conversationSubtitle.textContent = 'Your messages will appear here';
            conversationTypeBadge.textContent = 'Text / Image / Inquiry / Order';
            composerHint.textContent = 'Choose a conversation first';
            sendButton.disabled = true;
            return;
        }

        emptyNotice.classList.add('d-none');
        messageWrapper.classList.remove('d-none');
        composerShell.classList.remove('d-none');
        messageList.innerHTML = state.activeConversation.messages.length
            ? state.activeConversation.messages.map(renderMessage).join('')
            : '<div class="text-center text-muted py-5">No messages yet. Send the first one.</div>';

        conversationTitle.textContent = state.activeConversation.display_name || 'Conversation';
        conversationSubtitle.textContent = state.activeConversation.display_subtitle || '';
        conversationTypeBadge.textContent = state.activeConversation.conversation_type === 'group' ? 'Group chat' : 'Direct chat';
        composerHint.textContent = state.activeConversation.conversation_type === 'group'
            ? 'Group conversations allow text and image only.'
            : 'Direct chats allow text, image, inquiry, and order updates.';
        sendButton.disabled = false;
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

        let quotationBtn = '';
        if (message.message_type === 'quotation' && message.metadata && message.metadata.quotation_id) {
            if (actorType === 'business') {
                quotationBtn = `
                    <div class="mt-2 text-start">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 chat-view-quotation-btn" data-quotation-id="${message.metadata.quotation_id}">
                            <i class="bi bi-file-earmark-text me-1"></i> View Quotation
                        </button>
                    </div>
                `;
            } else {
                const quoteNoMatch = message.body ? message.body.match(/#[A-Z0-9-]+/) : null;
                const quotationNo = quoteNoMatch ? quoteNoMatch[0] : '';
                quotationBtn = `
                    <div class="mt-2 text-start d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 chat-view-quotation-btn" data-quotation-id="${message.metadata.quotation_id}">
                            <i class="bi bi-file-earmark-text me-1"></i> View
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 chat-reply-quotation-btn" data-quotation-no="${quotationNo}">
                            <i class="bi bi-reply me-1"></i> Reply
                        </button>
                    </div>
                `;
            }
        }

        return `
            <div class="d-flex ${isMine ? 'justify-content-end' : 'justify-content-start'}">
                <div class="rounded-4 p-3 chat-message-bubble ${bubbleClass}">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                        <strong class="small ${textClass}">${escapeHtml(message.sender?.name || 'User')}</strong>
                        <small class="${metaClass}">${escapeHtml(formatTime(message.created_at))}</small>
                    </div>
                    ${message.body ? `<div class="${textClass}">${escapeHtml(message.body)}</div>` : ''}
                    ${actionBadge}
                    ${quotationBtn}
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
            const response = await axios.get(routeTemplate(endpoints.showConversation, conversationId));
            state.activeConversation = response.data?.data?.conversation || null;
            state.activeConversation.messages = response.data?.data?.messages || [];
            selectedConversationId = conversationId;
            if (window.history?.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.set('conversation_id', conversationId);
                window.history.replaceState({}, '', url.toString());
            }
            renderConversationList();
            renderMessages();
            messageWrapper.scrollTop = messageWrapper.scrollHeight;
            await axios.post(routeTemplate(endpoints.markRead, conversationId));
        } catch (error) {
            window.toastr?.error(error.response?.data?.message || 'Unable to open conversation.');
        }
    };

    const fetchConversations = async () => {
        try {
            const response = await axios.get(endpoints.conversations);
            state.conversations = response.data?.data?.conversations || [];
            renderConversationList();

            if (selectedConversationId) {
                await openConversation(selectedConversationId);
                return;
            }

            if (!state.activeConversation && state.conversations.length) {
                await openConversation(state.conversations[0].id);
            }
        } catch (error) {
            conversationList.innerHTML = '<div class="text-danger small">Unable to load conversations.</div>';
        }
    };

    const searchParticipants = async (term) => {
        if (term.trim().length < 2) {
            participantResults.innerHTML = '<div class="p-3 text-muted small">Type at least 2 characters to search.</div>';
            return;
        }

        try {
            const response = await axios.get(endpoints.searchParticipants, { params: { q: term } });
            state.participantSearchResults = response.data?.data?.results || [];

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
        state.searchTimer = setTimeout(() => searchParticipants(participantSearch.value), 300);
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

    attachmentButton.addEventListener('click', () => attachmentsInput.click());

    composerForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!state.activeConversation) {
            window.toastr?.error('Select a conversation first.');
            return;
        }

        const formData = new FormData();
        formData.append('message_type', state.currentMessageType);
        formData.append('body', messageBody.value || '');
        formData.append('metadata', JSON.stringify({
            title: actionTitle.value || null,
            reference: actionReference.value || null,
            notes: actionNotes.value || null,
        }));

        Array.from(attachmentsInput.files || []).forEach((file) => formData.append('attachments[]', file));

        try {
            const response = await axios.post(routeTemplate(endpoints.storeMessage, state.activeConversation.id), formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            const message = response.data?.data?.message;
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
                state.currentMessageType = 'text';
                renderConversationList();
                renderMessages();
                messageWrapper.scrollTop = messageWrapper.scrollHeight;
            }
        } catch (error) {
            window.toastr?.error(error.response?.data?.message || 'Unable to send message.');
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
            const response = await axios.post(endpoints.storeConversation, {
                conversation_type: conversationType,
                title,
                participants,
            });

            const conversation = response.data?.data?.conversation;
            const modal = bootstrap.Modal.getInstance(document.getElementById('chatConversationModal'));
            modal?.hide();
            state.participants = [];
            renderSelectedParticipants();
            await fetchConversations();
            await openConversation(conversation.id);
        } catch (error) {
            window.toastr?.error(error.response?.data?.message || 'Unable to create conversation.');
        }
    });

    document.addEventListener('click', async (event) => {
        const viewBtn = event.target.closest('.chat-view-quotation-btn');
        if (viewBtn) {
            const quotationId = viewBtn.dataset.quotationId;
            const modalBody = document.getElementById('quotation_modal_body');
            modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            
            const modalEl = document.getElementById('quotationLargeModal');
            let modal = bootstrap.Modal.getInstance(modalEl);
            if (!modal) {
                modal = new bootstrap.Modal(modalEl);
            }
            modal.show();

            try {
                const response = await axios.get(`/chat/quotation/details/${quotationId}`);
                if (response.data.success) {
                    modalBody.innerHTML = response.data.html;
                } else {
                    window.toastr?.error("Failed to load quotation details.");
                }
            } catch (error) {
                window.toastr?.error("Error loading quotation details.");
            }
            return;
        }

        const printBtn = event.target.closest('#print-modal-quote-btn');
        if (printBtn) {
            const printContents = document.getElementById('quotation_modal_body').innerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Quotation</title>');
            printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
            printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">');
            printWindow.document.write('</head><body onload="window.print(); window.close();">');
            printWindow.document.write(printContents);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            return;
        }

        const replyBtn = event.target.closest('.chat-reply-quotation-btn');
        if (replyBtn) {
            const quotationNo = replyBtn.dataset.quotationNo || '';
            const replyText = "Regarding quotation " + quotationNo + ": ";
            const inputField = document.getElementById('chatMessageBody');
            if (inputField) {
                inputField.value = replyText;
                inputField.focus();
                const tempVal = inputField.value;
                inputField.value = '';
                inputField.value = tempVal;
            }
        }
    });

    renderSelectedParticipants();
    renderConversationList();
    updateComposerControls();
    renderMessages();
    fetchConversations();
}
