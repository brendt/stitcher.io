<?php

use function Tempest\Router\uri;
use App\Mail\MailController;

?>

<x-base>
    <x-container class="grid gap-4">
        <x-article>
            <div>
                <h1 class="text-4xl font-bold text-primary">
                    <a :href="uri([MailController::class, 'overview'])">{{ $mail->title }}</a></h1>
                <small class="pl-1">Written on {{ $mail->date->format('YYYY-MM-dd') }}</small>
            </div>

            <x-cta class="grid gap-2">
                <p>
                    Join over 14k subscribers on my mailing list. I write about PHP news, share programming content from across the web, keep you up to date about what's happening on this blog, my work on Tempest, and more.
                </p>

                <p class="font-bold">
                    You can subscribe by sending an email to
                    <a href="mailto:brendt@stitcher.io">brendt@stitcher.io</a>.
                </p>
            </x-cta>

            {!! $mail->cleanContent !!}
        </x-article>

        <x-footer class="mt-4">
            <x-button :href="uri([MailController::class, 'overview'])">Back</x-button>
        </x-footer>

        <x-card>
            <p>
                Join over 14k subscribers on my mailing list. I write about PHP news, share programming content from across the web, keep you up to date about what's happening on this blog, my work on Tempest, and more.
            </p>

            <p class="font-bold">
                You can subscribe by sending an email to
                <a href="mailto:brendt@stitcher.io">brendt@stitcher.io</a>.
            </p>
        </x-card>
    </x-container>
</x-base>