<?php
use App\Mail\MailController;

use function Tempest\Router\uri;

?>

<x-base title="stitcher mail">
    <x-container class="grid gap-4">
        <x-menu />
        <x-card :if="$uuid ?? null">
            <p>Click here to unsubsribe:</p>
            <x-form :action="uri([MailController::class, 'submitUnsubscribe'], uuid: $uuid)">
                <input type="submit" value="Unsubscribe" class="text-center bg-primary rounded-full text-white font-bold  shadow-sm underline hover:no-underline hover:shadow-lg text-sm p-2 px-4 cursor-pointer">
            </x-form>
        </x-card>
        <x-card :else>
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
