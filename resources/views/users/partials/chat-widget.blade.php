<!-- Chat Widget -->
<div class="chat-widget">
    <button class="chat-button" onclick="toggleChat()">
        <i class="fas fa-comments"></i>
    </button>

    <div class="chat-popup" id="chatPopup">
        <div class="chat-header">
            <img src="https://via.placeholder.com/40" alt="Admin" class="admin-avatar">
            <div>
                <h6 class="m-0">Shop Support</h6>
                <div class="admin-status">
                    <i class="fas fa-circle text-success"></i> Online
                </div>
            </div>
        </div>

        <div class="chat-messages">
            @auth
                @forelse($messages as $message)
                    <div class="message {{ ($message->user->loai_nguoidung == 'admin') ? 'admin' : 'customer' }}">
                        <img src="{{ $message->user->avatar ?? 'https://via.placeholder.com/32' }}"
                                alt="{{ $message->user->hoten }}"
                                class="message-avatar">
                        <div class="message-content">
                            <div class="message-bubble">
                                {{ $message->noidung }}
                            </div>
                            <div class="message-info">
                                {{ $message->user->hoten }} • {{ $message->thoigian->format('g:i A') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="message admin">
                        <img src="https://via.placeholder.com/32" alt="Admin" class="message-avatar">
                        <div class="message-content">
                            <div class="message-bubble">
                                Xin chào! Tôi có thể giúp gì cho bạn?
                            </div>
                            <div class="message-info">
                                Admin • {{ now()->format('g:i A') }}
                            </div>
                        </div>
                    </div>
                @endforelse
            @else
                <div class="login-prompt text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-lock fa-3x text-muted"></i>
                    </div>
                    <h5>Vui lòng đăng nhập</h5>
                    <p class="text-muted">Để bắt đầu trò chuyện với shop</p>
                    <button onclick="openModal()" class="primary-btn no-border">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập
                    </button>
                </div>
            @endauth
        </div>

        @auth
        <div class="chat-input">
            <form onsubmit="sendMessage(event)">
                <input type="text" placeholder="Nhập tin nhắn..." id="messageInput">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
        @endauth
    </div>
</div>
