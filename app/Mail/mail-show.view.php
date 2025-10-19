<?php
use function Tempest\Router\uri;
use App\Mail\MailController;
?>

<x-base>
    <x-article>
        <div>
            <h1 class="text-4xl font-bold text-primary"><a :href="uri([MailController::class, 'overview'])">{{ $mail->title }}</a></h1>
            <small class="pl-1">Written on {{ $mail->date->format('YYYY-MM-dd') }}</small>
        </div>
        {!! $mail->cleanContent !!}
    </x-article>

    <x-footer class="mt-4">
        <x-button :href="uri([MailController::class, 'overview'])">Back</x-button>
    </x-footer>
</x-base>