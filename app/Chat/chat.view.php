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
<body class="p-2 relative flex flex-col min-h-screen">
<div
        id="chat"
        :hx-get="uri([ChatController::class, 'realtime'])"
        hx-trigger="load, every 2s"
        hx-swap="innerHTML"
        class="mt-auto"
>
    <x-chat />
</div>

<script>
    const chat = document.getElementById('chat-messages');
    chat.scrollTop = chat.scrollHeight;

    document.body.addEventListener('htmx:afterSwap', function() {
        const chat = document.getElementById('chat-messages');
        if (chat) chat.scrollTop = chat.scrollHeight;
    });
</script>
</body>
</html>