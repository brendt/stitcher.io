<?php
use function Tempest\Router\uri;
use App\Mail\MailController;
?>

<x-base title="stitcher mail">
    <x-container class="grid gap-4">
        <x-menu />
        <x-card>
            <p>
                Join over 14k subscribers on my mailing list. I write about PHP news, share programming content from across the web, keep you up to date about what's happening on this blog, my work on Tempest, and more.
            </p>

            <p class="font-bold">
                You can subscribe by sending an email to
                <a href="mailto:brendt@stitcher.io">brendt@stitcher.io</a>.
            </p>
        </x-card>
        <x-footer class="mt-4">
            <x-button :small :href="uri([MailController::class, 'overview'])">Read the archive</x-button>
        </x-footer>
    </x-container>
</x-base>