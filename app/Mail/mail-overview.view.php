<x-base title="Newsletter">
    <x-container>
        <div class="my-4 sm:my-8 grid gap-2">
            <h1 class="text-4xl font-bold text-primary text-center sm:text-left">stitcher.io - newsletter</h1>
            <x-menu />
        </div>

        <x-card>
            <p>
                Join over 14k subscribers on my mailing list. I write about PHP news, share programming content from across the web, keep you up to date about what's happening on this blog, my work on Tempest, and more.
            </p>

            <p class="font-bold">
                You can subscribe by sending an email to
                <a href="mailto:brendt@stitcher.io">brendt@stitcher.io</a>.
            </p>
        </x-card>

        <nav class="grid gap-2 mt-4">
            <a :href="$mail->uri" :foreach="$mails as $mail" class="p-3 px-4 bg-white shadow-md hover:shadow-lg rounded-sm grid hover:text-primary hover:underline">
                <span class="font-bold">
                    {{ $mail->title }}
                </span>
                <span class="text-sm  hover:text-inherit">
                    {{ $mail->date->format('YYYY-MM-dd') }}
                </span>
            </a>
        </nav>
    </x-container>
</x-base>