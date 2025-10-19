<x-base>
    <x-container>
        <div class="my-4 sm:my-8 grid gap-2">
            <h1 class="text-4xl font-bold text-primary text-center sm:text-left">Newsletter</h1>
        </div>

        <nav class="grid gap-2">
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