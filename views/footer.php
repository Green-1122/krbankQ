</div><!-- /page-content -->
</div><!-- /main-content -->
</div><!-- /app-layout -->

<!-- Live Chat Button -->
<div id="livechat-button" class="livechat-button" onclick="openChat()">
    <i class="fas fa-comments"></i>
</div>

<!-- Live Chat Modal -->
<div id="livechat-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content chat-modal">
        <div class="modal-header">
            <h3>Live Chat Support</h3>
            <button class="modal-close" onclick="closeChat()">&times;</button>
        </div>
        <div class="chat-messages" id="chat-messages">
            <div class="message support">Hello! How can we help you today?</div>
        </div>
        <div class="chat-input">
            <input type="text" id="chat-input" placeholder="Type your message..." onkeypress="handleKeyPress(event)">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>
</div>

<script>
    // Global JS helpers
    function showModal(id) { document.getElementById(id).classList.add('active') }
    function hideModal(id) { document.getElementById(id).classList.remove('active') }
    document.querySelectorAll('.modal-overlay').forEach(m => { m.addEventListener('click', e => { if (e.target === m) m.classList.remove('active') }) });

    // Live Chat Functions
    function openChat() {
        document.getElementById('livechat-modal').style.display = 'flex';
    }

    function closeChat() {
        document.getElementById('livechat-modal').style.display = 'none';
    }

    function sendMessage() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();
        if (message) {
            const messages = document.getElementById('chat-messages');
            const userMessage = document.createElement('div');
            userMessage.className = 'message user';
            userMessage.textContent = message;
            messages.appendChild(userMessage);
            input.value = '';
            messages.scrollTop = messages.scrollHeight;

            // Simulate support response
            setTimeout(() => {
                const supportMessage = document.createElement('div');
                supportMessage.className = 'message support';
                supportMessage.textContent = 'Thank you for your message. A support agent will respond shortly.';
                messages.appendChild(supportMessage);
                messages.scrollTop = messages.scrollHeight;
            }, 1000);
        }
    }

    function handleKeyPress(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }
</script>
</body>

</html>