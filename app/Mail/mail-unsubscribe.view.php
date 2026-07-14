<?php
use App\Mail\MailController;

use function Tempest\Router\uri;

?>

<x-base title="stitcher mail">
    <x-container class="grid gap-4">
        <x-menu />
        <x-card>
            <p>
                You've been unsubscribed!
            </p>

            <p class="font-bold">
                If this was by accident, you can re-subscribe by sending an email to
                <a href="mailto:brendt@stitcher.io">brendt@stitcher.io</a>.
            </p>
        </x-card>
        <x-footer class="mt-4">
            <x-button :small :href="uri([MailController::class, 'overview'])">Read the archive</x-button>
        </x-footer>
    </x-container>
</x-base>
