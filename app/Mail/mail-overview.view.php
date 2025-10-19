<x-base>
    <x-container>
        <div class="my-4 sm:my-8 grid gap-2">
            <h1 class="text-4xl font-bold text-primary text-center sm:text-left">stitcher.io - newsletter</h1>
            <div class="flex flex-wrap gap-2 justify-center sm:justify-start">
                <x-button :small href="/">Blog</x-button>
                <x-button :small href="/rss">Feed</x-button>
                <x-button :small href="/">Books</x-button>
                <x-button :small href="https://tempestphp.com">Tempest</x-button>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-8 border-sm shadow-md grid gap-4 rounded-xs">
            <p>
                Join over 14k subscribers on my mailing list. I write about PHP news, share programming content from across the web, keep you up to date about what's happening on this blog, my work on Tempest, and more.
            </p>

            <p class="font-bold text-primary">
                You can subscribe by sending an email to
                <a href="mailto:brendt@stitcher.io">brendt@stitcher.io</a>.
            </p>
        </div>


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