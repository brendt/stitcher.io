<?php

namespace App\Chat;

use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final class ChatController
{
    public function __construct(
        private ChatStorage $chatStorage,
    ) {}

    #[Get('/chat')]
    public function index(): View
    {
        return view('chat.view.php');
    }

    #[Get('/chat/realtime')]
    public function realtime(): View
    {
        $messages = $this->chatStorage->getMessages();

        $messages = array_slice(array_reverse($messages), 0, 6);

        return view('x-chat.view.php', messages: $messages);
    }
}