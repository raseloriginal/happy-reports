<?php include 'includes/header.php'; ?>

<div class="h-[calc(100vh-4rem-3rem)] md:h-[calc(100vh-4rem)] flex flex-col max-w-5xl mx-auto bg-white dark:bg-dark-card border-x border-gray-100 dark:border-dark-border shadow-sm animate-fade-in" style="animation-delay: 0.1s;">
    
    <!-- Chat Header -->
    <div class="px-6 py-4 border-b border-gray-100 dark:border-dark-border flex items-center justify-between bg-white/80 dark:bg-dark-card/80 backdrop-blur-sm z-10">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center text-white shadow-md">
                <i class="ph ph-robot text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-800 dark:text-white leading-tight">Finance AI Assistant</h2>
                <div class="flex items-center text-xs text-emerald-500 font-medium mt-0.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Online · GPT-4o-mini
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <span class="text-[10px] text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-md" id="token-counter">Tokens: 0</span>
            <button onclick="clearChat()" class="text-xs text-gray-400 hover:text-rose-500 transition-colors flex items-center bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-md">
                <i class="ph ph-trash mr-1"></i> Clear
            </button>
        </div>
    </div>

    <!-- Chat Messages -->
    <div class="flex-1 overflow-y-auto p-6 space-y-6 scroll-smooth" id="chat-container">
        <!-- History will be loaded by JS, welcome shown if empty -->
        <div id="welcome-block" class="hidden">
            <div class="flex max-w-[85%]">
                <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white shrink-0 mt-1 shadow-sm">
                    <i class="ph ph-robot text-sm"></i>
                </div>
                <div class="ml-3">
                    <div class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 p-4 rounded-2xl rounded-tl-sm shadow-sm text-sm leading-relaxed chat-bubble">
                        <p>I'm your Happy Bangladesh finance assistant. I have access to your <strong>local database</strong> (deposits, withdrawals).</p>
                        <div class="mt-3 space-y-2">
                            <button class="block w-full text-left px-3 py-2 rounded-lg bg-white dark:bg-dark-card border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 transition-colors text-xs text-primary-600 dark:text-primary-400" onclick="sendSuggested('What is our net balance?')">
                                "What is our net balance?"
                            </button>
                            <button class="block w-full text-left px-3 py-2 rounded-lg bg-white dark:bg-dark-card border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 transition-colors text-xs text-primary-600 dark:text-primary-400" onclick="sendSuggested('Show me the latest deposits')">
                                "Show me the latest deposits"
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="history-loading" class="text-center py-8 text-gray-400"><i class="ph ph-spinner animate-spin text-2xl"></i></div>
    </div>

    <!-- Input -->
    <div class="p-4 bg-white dark:bg-dark-card border-t border-gray-100 dark:border-dark-border">
        <form id="chat-form" class="relative max-w-4xl mx-auto flex items-end bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 focus-within:border-primary-500 dark:focus-within:border-primary-500 focus-within:ring-1 focus-within:ring-primary-500 transition-all p-1 shadow-sm">
            <textarea id="chat-input" rows="1" class="w-full bg-transparent border-0 focus:ring-0 resize-none py-3 px-4 text-sm text-gray-800 dark:text-gray-200 placeholder-gray-400 max-h-32" placeholder="Ask about your financial data..."></textarea>
            <button type="submit" id="send-btn" class="m-1 p-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="ph ph-paper-plane-right font-bold text-lg"></i>
            </button>
        </form>
        <div class="text-center mt-2">
            <span class="text-[10px] text-gray-400 dark:text-gray-500">Strict mode: AI answers only from Happy Bangladesh data. Max 200 tokens per response.</span>
        </div>
    </div>
</div>

<script>
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatContainer = document.getElementById('chat-container');
    const sendBtn = document.getElementById('send-btn');
    let totalTokens = 0;

    chatInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        if(this.value === '') this.style.height = 'auto';
    });

    chatInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); chatForm.dispatchEvent(new Event('submit')); }
    });

    function appendUserMessage(text) {
        chatContainer.insertAdjacentHTML('beforeend', `
            <div class="flex max-w-[85%] ml-auto justify-end chat-bubble">
                <div class="mr-3 flex flex-col items-end">
                    <div class="bg-primary-600 text-white p-4 rounded-2xl rounded-tr-sm shadow-sm text-sm leading-relaxed whitespace-pre-wrap">${escapeHtml(text)}</div>
                </div>
                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 shrink-0 mt-1 shadow-sm font-bold text-xs">U</div>
            </div>`);
        scrollToBottom();
    }

    function appendAIMessage(text) {
        chatContainer.insertAdjacentHTML('beforeend', `
            <div class="flex max-w-[85%] chat-bubble">
                <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white shrink-0 mt-1 shadow-sm"><i class="ph ph-robot text-sm"></i></div>
                <div class="ml-3">
                    <div class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 p-4 rounded-2xl rounded-tl-sm shadow-sm text-sm leading-relaxed whitespace-pre-wrap">${escapeHtml(text)}</div>
                </div>
            </div>`);
        scrollToBottom();
    }

    function appendErrorMessage(text) {
        chatContainer.insertAdjacentHTML('beforeend', `
            <div class="flex max-w-[85%] chat-bubble">
                <div class="w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center text-white shrink-0 mt-1 shadow-sm"><i class="ph ph-warning text-sm"></i></div>
                <div class="ml-3">
                    <div class="bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-300 p-4 rounded-2xl rounded-tl-sm shadow-sm text-sm">${escapeHtml(text)}</div>
                </div>
            </div>`);
        scrollToBottom();
    }

    function showTypingIndicator() {
        const id = 'typing-' + Date.now();
        chatContainer.insertAdjacentHTML('beforeend', `
            <div class="flex max-w-[85%]" id="${id}">
                <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white shrink-0 mt-1 shadow-sm"><i class="ph ph-robot text-sm"></i></div>
                <div class="ml-3">
                    <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-2xl rounded-tl-sm shadow-sm flex space-x-1.5 items-center h-[52px]">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></div>
                    </div>
                </div>
            </div>`);
        scrollToBottom();
        return id;
    }

    function removeTypingIndicator(id) { const el = document.getElementById(id); if (el) el.remove(); }
    function scrollToBottom() { chatContainer.scrollTop = chatContainer.scrollHeight; }
    function escapeHtml(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

    window.sendSuggested = function(text) { chatInput.value = text; chatForm.dispatchEvent(new Event('submit')); }

    // Load chat history from DB
    async function loadHistory() {
        try {
            const res = await fetch('api/ai_chat.php?limit=50');
            const data = await res.json();
            document.getElementById('history-loading').remove();

            if (data.status === 'success' && data.data && data.data.length > 0) {
                // Has history — render it
                data.data.forEach(msg => {
                    if (msg.role === 'user') appendUserMessage(msg.message);
                    else appendAIMessage(msg.message);
                });
                // Sum up tokens from history
                data.data.forEach(msg => { totalTokens += (msg.tokens_used || 0); });
                document.getElementById('token-counter').textContent = `Tokens: ${totalTokens.toLocaleString()}`;
            } else {
                // No history — show welcome
                document.getElementById('welcome-block').classList.remove('hidden');
            }
            scrollToBottom();
        } catch (e) {
            document.getElementById('history-loading').remove();
            document.getElementById('welcome-block').classList.remove('hidden');
        }
    }

    // Clear chat
    window.clearChat = async function() {
        if (!confirm('Clear all chat history?')) return;
        await fetch('api/ai_chat.php', { method: 'DELETE' });
        // Reset UI
        chatContainer.innerHTML = '';
        chatContainer.appendChild(document.getElementById('welcome-block') || document.createElement('div'));
        totalTokens = 0;
        document.getElementById('token-counter').textContent = 'Tokens: 0';
        location.reload();
    }

    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = chatInput.value.trim();
        if (!msg) return;

        // Hide welcome block if visible
        const wb = document.getElementById('welcome-block');
        if (wb) wb.classList.add('hidden');

        chatInput.value = '';
        chatInput.style.height = 'auto';
        sendBtn.disabled = true;

        appendUserMessage(msg);
        const typingId = showTypingIndicator();

        try {
            const res = await fetch('api/ai_chat.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ message: msg })
            });
            const data = await res.json();
            removeTypingIndicator(typingId);

            if (data.status === 'success') {
                appendAIMessage(data.reply);
                if (data.tokens) {
                    totalTokens += data.tokens.total;
                    document.getElementById('token-counter').textContent = `Tokens: ${totalTokens.toLocaleString()} (last: ${data.tokens.total})`;
                }
            } else {
                appendErrorMessage(data.message || 'Something went wrong.');
            }
        } catch (err) {
            removeTypingIndicator(typingId);
            appendErrorMessage('Network error. Check if your server is running.');
        }

        sendBtn.disabled = false;
    });

    // Init
    loadHistory();
</script>

<?php include 'includes/footer.php'; ?>
