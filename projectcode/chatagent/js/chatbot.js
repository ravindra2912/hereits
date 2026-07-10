/**
 * Ollama RAG Chatbot Widget
 * Self-contained JS file that injects chatbot UI into the host page.
 */
(function () {
    // Configuration - Change this URL to match your backend API address
    const API_URL = "http://localhost:8000";

    // Generate or retrieve chat session ID
    function getOrCreateSessionId() {
        try {
            let id = sessionStorage.getItem("hereits_chat_session_id");
            if (!id) {
                id = 'sess_' + Math.random().toString(36).substring(2, 15) + '_' + Date.now();
                sessionStorage.setItem("hereits_chat_session_id", id);
            }
            return id;
        } catch (e) {
            if (!window._hereitsChatSessionId) {
                window._hereitsChatSessionId = 'sess_' + Math.random().toString(36).substring(2, 15) + '_' + Date.now();
            }
            return window._hereitsChatSessionId;
        }
    }

    let sessionId = getOrCreateSessionId();
    let isSending = false;

    // Create and inject stylesheet
    const styles = `
        /* Font import for Outfit & Inter */
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap');

        :root {
            --rag-font-title: 'Outfit', sans-serif;
            --rag-font-body: 'Plus Jakarta Sans', sans-serif;
            --rag-color-primary: #7F00FF;
            --rag-color-secondary: #E100FF;
            --rag-color-bg-dark: rgba(15, 15, 23, 0.85);
            --rag-color-border: rgba(255, 255, 255, 0.08);
            --rag-color-glass: rgba(255, 255, 255, 0.04);
            --rag-color-text: #f3f4f6;
            --rag-color-text-muted: #9ca3af;
        }

        /* Pulsing Glow Animation */
        @keyframes rag-pulse-glow {
            0% { box-shadow: 0 0 0 0 rgba(225, 0, 255, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(225, 0, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(225, 0, 255, 0); }
        }

        /* Slide-up Scale Animation */
        @keyframes rag-slide-up {
            from { transform: translateY(30px) scale(0.92); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        /* Message Fade-in Animation */
        @keyframes rag-fade-in {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Floating Chat Button */
        #rag-chatbot-bubble {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--rag-color-primary), var(--rag-color-secondary));
            box-shadow: 0 8px 28px rgba(127, 0, 255, 0.4);
            cursor: pointer;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: rag-pulse-glow 2.5s infinite;
            border: none;
            outline: none;
        }

        #rag-chatbot-bubble:hover {
            transform: scale(1.08);
            box-shadow: 0 10px 32px rgba(127, 0, 255, 0.6);
        }

        #rag-chatbot-bubble:active {
            transform: scale(0.95);
        }

        #rag-chatbot-bubble svg {
            width: 28px;
            height: 28px;
            fill: white;
            transition: transform 0.3s ease;
        }

        #rag-chatbot-bubble.active svg {
            transform: rotate(90deg);
        }

        /* Chat Window Container */
        #rag-chatbot-window {
            position: fixed;
            bottom: 96px;
            right: 24px;
            width: 380px;
            height: 560px;
            max-height: calc(100vh - 120px);
            max-width: calc(100vw - 48px);
            border-radius: 24px;
            z-index: 999998;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--rag-color-bg-dark);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--rag-color-border);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            transform: translateY(30px) scale(0.92);
            opacity: 0;
            pointer-events: none;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
            font-family: var(--rag-font-body);
        }

        #rag-chatbot-window.active {
            transform: translateY(0) scale(1);
            opacity: 1;
            pointer-events: auto;
        }

        #rag-chatbot-window.fullscreen {
            width: 100vw;
            height: 100vh;
            max-width: 100vw;
            max-height: 100vh;
            bottom: 0;
            right: 0;
            border-radius: 0;
            border: none;
            transform: none !important;
            transition: none !important;
        }

        #rag-chatbot-window.fullscreen .exit-fullscreen-icon {
            display: block !important;
        }

        #rag-chatbot-window.fullscreen .fullscreen-icon {
            display: none !important;
        }

        /* Header Styling */
        .rag-chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid var(--rag-color-border);
        }

        .rag-header-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .rag-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--rag-color-primary), var(--rag-color-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--rag-font-title);
            font-weight: 700;
            color: white;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .rag-title-wrapper {
            display: flex;
            flex-direction: column;
        }

        .rag-chat-title {
            font-family: var(--rag-font-title);
            font-size: 16px;
            font-weight: 600;
            color: white;
            margin: 0;
        }

        .rag-status {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            color: var(--rag-color-text-muted);
        }

        .rag-status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
        }

        .rag-header-actions {
            display: flex;
            gap: 8px;
        }

        .rag-action-btn {
            background: transparent;
            border: none;
            color: var(--rag-color-text-muted);
            cursor: pointer;
            padding: 6px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .rag-action-btn:hover {
            color: white;
            background: rgba(255, 255, 255, 0.05);
        }

        .rag-action-btn svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        /* Message Area */
        .rag-chat-body {
            flex-grow: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            scroll-behavior: smooth;
        }

        /* Custom Scrollbar */
        .rag-chat-body::-webkit-scrollbar {
            width: 5px;
        }
        .rag-chat-body::-webkit-scrollbar-track {
            background: transparent;
        }
        .rag-chat-body::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .rag-chat-body::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* Message bubble wrapper */
        .rag-message-wrapper {
            display: flex;
            width: 100%;
            animation: rag-fade-in 0.3s ease forwards;
        }

        .rag-message-wrapper.user {
            justify-content: flex-end;
        }

        .rag-message-wrapper.bot {
            justify-content: flex-start;
        }

        /* Chat Message bubble */
        .rag-message-bubble {
            max-width: 80%;
            padding: 12px 16px;
            font-size: 14px;
            line-height: 1.5;
        }

        .user .rag-message-bubble {
            background: linear-gradient(135deg, var(--rag-color-primary), var(--rag-color-secondary));
            color: white;
            border-radius: 18px 18px 2px 18px;
            box-shadow: 0 4px 12px rgba(127, 0, 255, 0.15);
        }

        .bot .rag-message-bubble {
            background: var(--rag-color-glass);
            color: var(--rag-color-text);
            border: 1px solid var(--rag-color-border);
            border-radius: 18px 18px 18px 2px;
        }

        .rag-message-bubble p {
            margin: 0 0 8px 0;
        }

        .rag-message-bubble p:last-child {
            margin-bottom: 0;
        }

        .rag-message-bubble ul, .rag-message-bubble ol {
            margin: 4px 0;
            padding-left: 20px;
        }

        .rag-message-bubble code {
            font-family: monospace;
            background: rgba(255, 255, 255, 0.08);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 13px;
        }

        .rag-message-bubble pre {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--rag-color-border);
            padding: 10px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 8px 0;
        }

        .rag-message-bubble pre code {
            background: transparent;
            padding: 0;
            font-size: 12px;
        }

        .rag-context-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
            padding: 2px 8px;
            border-radius: 10px;
            margin-top: 8px;
            font-weight: 500;
        }

        /* Typing Indicator animation */
        .rag-typing-indicator {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
        }

        .rag-typing-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: var(--rag-color-text-muted);
            opacity: 0.4;
            animation: rag-typing-bounce 1.4s infinite ease-in-out both;
        }

        .rag-typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .rag-typing-dot:nth-child(2) { animation-delay: -0.16s; }

        @keyframes rag-typing-bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1.0); }
        }

        /* Input Area Styling */
        .rag-chat-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--rag-color-border);
            background: rgba(255, 255, 255, 0.01);
            display: flex;
            align-items: flex-end;
            gap: 12px;
        }

        .rag-input-container {
            flex-grow: 1;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--rag-color-border);
            border-radius: 18px;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .rag-input-container:focus-within {
            border-color: rgba(127, 0, 255, 0.5);
            box-shadow: 0 0 8px rgba(127, 0, 255, 0.2);
        }

        .rag-chat-input {
            flex-grow: 1;
            background: transparent;
            border: none;
            outline: none;
            color: white;
            font-family: var(--rag-font-body);
            font-size: 14px;
            resize: none;
            max-height: 80px;
            min-height: 20px;
            padding: 4px 0;
            line-height: 1.4;
        }

        .rag-chat-input::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .rag-send-btn {
            background: linear-gradient(135deg, var(--rag-color-primary), var(--rag-color-secondary));
            border: none;
            outline: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            flex-shrink: 0;
            color: white;
        }

        .rag-send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(127, 0, 255, 0.3);
        }

        .rag-send-btn:active {
            transform: scale(0.95);
        }

        .rag-send-btn svg {
            width: 18px;
            height: 18px;
            fill: white;
        }

        .rag-send-btn:disabled {
            background: rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.3);
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .rag-send-btn:disabled svg {
            fill: rgba(255, 255, 255, 0.3) !important;
        }



        .rag-error-msg {
            color: #f87171;
            font-size: 13px;
            margin-top: 5px;
            background: rgba(248, 113, 113, 0.1);
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid rgba(248, 113, 113, 0.2);
        }

        .rag-chat-quick-btn {
            background: rgba(127, 0, 255, 0.15);
            border: 1px solid rgba(127, 0, 255, 0.4);
            color: #f3f4f6;
            padding: 6px 12px;
            border-radius: 12px;
            cursor: pointer;
            margin: 4px;
            display: inline-block;
            font-size: 13px;
            font-family: var(--rag-font-body);
            transition: all 0.2s ease;
        }
        .rag-chat-quick-btn:hover {
            background: rgba(127, 0, 255, 0.35);
            border-color: rgba(225, 0, 255, 0.6);
            transform: translateY(-1px);
        }
        .rag-chat-quick-btn:active {
            transform: translateY(1px);
        }

        .rag-message-bubble li:has(.rag-chat-quick-btn) {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }
        .rag-message-bubble ul:has(.rag-chat-quick-btn) {
            list-style-type: none;
            padding-left: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
    `;

    // Function to parse basic Markdown tags
    function parseMarkdown(text) {
        if (!text) return "";
        let html = text;

        // Escape HTML to prevent XSS
        html = html
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

        // Parse custom buttons: [button:label|value] and [button:value]
        html = html.replace(/\[button:([^|\]]+)\|([^\]]+)\]/g, '<button class="rag-chat-quick-btn" data-value="$2">$1</button>');
        html = html.replace(/\[button:([^\]]+)\]/g, '<button class="rag-chat-quick-btn" data-value="$1">$1</button>');

        // Code blocks: ```code```
        html = html.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');

        // Inline code: `code`
        html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

        // Bold: **text**
        html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

        // Italic: *text*
        html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');

        // Bullet points: - item
        html = html.replace(/^\s*-\s+(.+)$/gm, '<li>$1</li>');
        html = html.replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>');

        // Newlines to break tags
        html = html.replace(/\n/g, '<br>');

        return html;
    }

    // Initialize Widget
    function init() {
        // Inject CSS Styles
        const styleSheet = document.createElement("style");
        styleSheet.innerText = styles;
        document.head.appendChild(styleSheet);

        // Inject Floating Button HTML
        const bubble = document.createElement("button");
        bubble.id = "rag-chatbot-bubble";
        bubble.innerHTML = `
            <svg viewBox="0 0 24 24">
                <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/>
            </svg>
        `;
        document.body.appendChild(bubble);

        // Inject Chat Window HTML
        const windowDiv = document.createElement("div");
        windowDiv.id = "rag-chatbot-window";
        windowDiv.innerHTML = `
            <div class="rag-chat-header">
                <div class="rag-header-info">
                    <div class="rag-avatar">AI</div>
                    <div class="rag-title-wrapper">
                        <span class="rag-chat-title">AI Assistant</span>
                        <div class="rag-status">
                            <span class="rag-status-dot"></span>
                            <span>Online</span>
                        </div>
                    </div>
                </div>
                <div class="rag-header-actions">
                    <button class="rag-action-btn" id="rag-clear-btn" title="Clear Chat">
                        <svg viewBox="0 0 24 24">
                            <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                        </svg>
                    </button>
                    <button class="rag-action-btn" id="rag-fullscreen-btn" title="Fullscreen">
                        <svg class="fullscreen-icon" viewBox="0 0 24 24">
                            <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/>
                        </svg>
                        <svg class="exit-fullscreen-icon" viewBox="0 0 24 24" style="display: none;">
                            <path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/>
                        </svg>
                    </button>
                    <button class="rag-action-btn" id="rag-close-btn" title="Close">
                        <svg viewBox="0 0 24 24">
                            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="rag-chat-body" id="rag-chat-body">
                <div class="rag-message-wrapper bot">
                    <div class="rag-message-bubble">
                        <p>Hello! I am your AI assistant powered by Qwen 3.5. How can I help you today?</p>
                    </div>
                </div>
            </div>
            <div class="rag-chat-footer">
                <div class="rag-input-container">
                    <textarea class="rag-chat-input" id="rag-chat-input" placeholder="Type a message..." rows="1"></textarea>
                </div>
                <button class="rag-send-btn" id="rag-send-btn">
                    <svg viewBox="0 0 24 24">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </div>
        `;
        document.body.appendChild(windowDiv);

        // DOM elements
        const chatInput = document.getElementById("rag-chat-input");
        const sendBtn = document.getElementById("rag-send-btn");
        const chatBody = document.getElementById("rag-chat-body");
        const clearBtn = document.getElementById("rag-clear-btn");
        const closeBtn = document.getElementById("rag-close-btn");
        const fullscreenBtn = document.getElementById("rag-fullscreen-btn");

        // Toggle chat window
        bubble.addEventListener("click", () => {
            bubble.classList.toggle("active");
            windowDiv.classList.toggle("active");
            if (windowDiv.classList.contains("active")) {
                chatInput.focus();
            }
        });

        // Toggle Fullscreen Mode
        fullscreenBtn.addEventListener("click", () => {
            windowDiv.classList.toggle("fullscreen");
            if (windowDiv.classList.contains("fullscreen")) {
                bubble.style.display = "none";
            } else {
                bubble.style.display = "flex";
            }
        });

        closeBtn.addEventListener("click", () => {
            bubble.classList.remove("active");
            windowDiv.classList.remove("active");
            windowDiv.classList.remove("fullscreen");
            bubble.style.display = "flex";
        });

        // Auto-expand textarea
        chatInput.addEventListener("input", function () {
            this.style.height = "auto";
            this.style.height = (this.scrollHeight - 8) + "px";
        });

        // Send message handlers
        sendBtn.addEventListener("click", sendMessage);
        chatInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Handle quick-response buttons click
        chatBody.addEventListener("click", (e) => {
            const btn = e.target.closest(".rag-chat-quick-btn");
            if (btn) {
                const value = btn.getAttribute("data-value");
                if (value && !isSending) {
                    chatInput.value = value;
                    sendMessage();
                }
            }
        });

        // Clear conversation
        clearBtn.addEventListener("click", () => {
            if (confirm("Are you sure you want to clear this conversation?")) {
                chatBody.innerHTML = `
                    <div class="rag-message-wrapper bot">
                        <div class="rag-message-bubble">
                            <p>Hello! I am your AI assistant powered by Qwen 3.5. How can I help you today?</p>
                        </div>
                    </div>
                `;
                // Generate a new session ID for a fresh backend history
                const newId = 'sess_' + Math.random().toString(36).substring(2, 15) + '_' + Date.now();
                try {
                    sessionStorage.setItem("hereits_chat_session_id", newId);
                    sessionStorage.removeItem("hereits_chat_messages");
                } catch (e) { }
                window._hereitsChatSessionId = newId;
                sessionId = newId;
            }
        });

        // Core Send function
        async function sendMessage() {
            if (isSending) return;
            const text = chatInput.value.trim();
            if (!text) return;

            // Fetch the history before appending the current query to sessionStorage
            let history = [];
            try {
                const stored = sessionStorage.getItem("hereits_chat_messages");
                if (stored) {
                    history = JSON.parse(stored);
                }
            } catch (e) {
                console.error("Failed to parse history from sessionStorage", e);
            }

            // Set sending state
            isSending = true;
            sendBtn.disabled = true;

            // Clear input & reset height
            chatInput.value = "";
            chatInput.style.height = "auto";

            // Add user message to UI
            appendMessage("user", text);
            scrollToBottom();

            // Add typing indicator
            const typingId = appendTypingIndicator();
            scrollToBottom();

            try {
                // Call API backend passing session_id, prompt, and history
                const response = await fetch(`${API_URL}/api/chat`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        session_id: sessionId,
                        prompt: text,
                        history: history
                    })
                });

                // Remove typing indicator
                removeTypingIndicator(typingId);

                if (!response.ok) {
                    const errData = await response.json();
                    throw new Error(errData.detail || "Server error occurred");
                }

                const data = await response.json();

                // Add assistant response to UI
                appendMessage("bot", data.response, data.has_context);
                scrollToBottom();

            } catch (err) {
                removeTypingIndicator(typingId);
                appendError(err.message);
                scrollToBottom();
            } finally {
                isSending = false;
                sendBtn.disabled = false;
                chatInput.focus();
            }
        }

        // UI Append helpers
        function appendMessage(role, content, hasContext = false, saveToStorage = true) {
            const wrapper = document.createElement("div");
            wrapper.className = `rag-message-wrapper ${role}`;

            const bubble = document.createElement("div");
            bubble.className = "rag-message-bubble";
            bubble.innerHTML = parseMarkdown(content);



            wrapper.appendChild(bubble);
            chatBody.appendChild(wrapper);

            if (saveToStorage) {
                saveMessageToStorage(role, content, hasContext);
            }
        }

        function saveMessageToStorage(role, content, hasContext) {
            try {
                let stored = sessionStorage.getItem("hereits_chat_messages");
                let messages = stored ? JSON.parse(stored) : [];
                messages.push({ role, content, hasContext });
                sessionStorage.setItem("hereits_chat_messages", JSON.stringify(messages));
            } catch (e) {
                console.error("Failed to save message to session storage", e);
            }
        }

        function restoreHistory() {
            try {
                const stored = sessionStorage.getItem("hereits_chat_messages");
                if (stored) {
                    const messages = JSON.parse(stored);
                    messages.forEach(msg => {
                        appendMessage(msg.role, msg.content, msg.hasContext, false);
                    });
                }
            } catch (e) {
                console.error("Failed to restore history", e);
            }
        }

        function appendTypingIndicator() {
            const id = "rag-typing-" + Date.now();
            const wrapper = document.createElement("div");
            wrapper.className = "rag-message-wrapper bot";
            wrapper.id = id;
            wrapper.innerHTML = `
                <div class="rag-message-bubble">
                    <div class="rag-typing-indicator">
                        <div class="rag-typing-dot"></div>
                        <div class="rag-typing-dot"></div>
                        <div class="rag-typing-dot"></div>
                    </div>
                </div>
            `;
            chatBody.appendChild(wrapper);
            return id;
        }

        function removeTypingIndicator(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
        }

        function appendError(message) {
            const wrapper = document.createElement("div");
            wrapper.className = "rag-message-wrapper bot";
            wrapper.innerHTML = `
                <div class="rag-message-bubble" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15);">
                    <div class="rag-error-msg">
                        <strong>Error:</strong> ${message}
                    </div>
                    <div style="font-size: 11px; margin-top: 5px; color: var(--rag-color-text-muted);">
                        Please make sure the Python server is running at <code style="font-size: 11px;">${API_URL}</code>.
                    </div>
                </div>
            `;
            chatBody.appendChild(wrapper);
        }

        function scrollToBottom() {
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        // Restore past chat history and auto-scroll to the bottom
        restoreHistory();
        scrollToBottom();
    }

    // Run after window is fully loaded
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
