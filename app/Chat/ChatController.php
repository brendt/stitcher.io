<?php

namespace App\Chat;

use Tempest\Http\Request;
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final class ChatController
{
    public function __construct(
        private ChatStorage $chatStorage,
    ) {}

    #[Get('/chat')]
    public function index(Request $request): View
    {
        $modal = $request->has('modal');

        return view('chat.view.php', modal: $modal);
    }

    #[Get('/chat/realtime')]
    public function realtime(Request $request): View
    {
        $modal = $request->has('modal');

        $messages = $this->chatStorage->getMessages();

        return view('x-chat.view.php', messages: $messages, modal: $modal);
    }
}