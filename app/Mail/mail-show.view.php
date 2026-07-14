<?php

use App\Mail\MailController;

use function Tempest\Router\uri;

?>

<x-base>
    <x-container class="grid gap-4">
        <x-menu />
        <x-article>
            <div>
                <h1>{{ $mail->title }}</h1>
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

            <button
                :if="! $mail->isSent"
                :hx-post="uri([MailController::class, 'send'], slug: $mail->slug)"
                hx-confirm="Are you sure you want to send this mail?"
                hx-on::after-request="if (event.detail.successful) { this.textContent = 'Sent!'; this.disabled = true; this.classList.remove('bg-primary'); this.classList.add('bg-green-600', 'cursor-not-allowed', 'no-underline'); }"
                class="text-center bg-primary rounded-full text-white font-bold shadow-sm underline hover:no-underline hover:shadow-lg p-3 px-5 cursor-pointer"
            >Send</button>
            <span :else class="text-green-600 font-bold">
                Sent!
            </span>
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
