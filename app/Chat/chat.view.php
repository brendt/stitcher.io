<?php
use function Tempest\Router\uri;
use App\Chat\ChatController;
?>
<html>
<head>
    <title>Chat</title>
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.6/dist/htmx.min.js" integrity="sha384-Akqfrbj/HpNVo8k11SXBb6TlBWmXXlYQrCSqEWmyKJe+hDm3Z/B2WVG4smwBkRVm" crossorigin="anonymous"></script>
    <x-vite-tags entrypoint="app/main.entrypoint.css"/>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
</head>
<body :class="$modal ? 'p-2 relative flex flex-col' : 'bg-[#1b1429]'" class="min-h-screen">
<div
        id="chat"
        :hx-get="$modal ? uri([ChatController::class, 'realtime'], modal: true) : uri([ChatController::class, 'realtime'])"
        hx-trigger="load, every 2s"
        hx-swap="innerHTML"
        :class="$modal ? 'mt-auto' : 'h-full'"
>
    <x-chat :modal="$modal"/>
</div>

<script>
    const scrollChatToBottom = () => {
        const chat = document.getElementById('chat-messages');
        if (!chat) return;
        chat.scrollTop = chat.scrollHeight;
    };

    document.addEventListener('DOMContentLoaded', function () {
        scrollChatToBottom();
    });

    document.body.addEventListener('htmx:afterSwap', function (event) {
        if (event.target.id !== 'chat') return;
        scrollChatToBottom();
    });
</script>
</body>
</html>
