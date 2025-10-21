<x-base :meta="$post->meta ?? null">
    <x-container class="grid gap-4">
        <x-article>
            <div>
                <h1 class="text-4xl font-bold text-primary"><a href="/">{{ $post->title }}</a></h1>
                <small class="pl-1">Written on {{ $post->date->format('YYYY-MM-dd') }}</small>
            </div>

            {!! $post->content !!}

            <x-cta class="font-bold">
                My new book
                <a href="https://things-i-wish-i-knew.com/">Things I wish I knew when I started programming</a> is now available in ebook and print!
            </x-cta>
        </x-article>

        <x-footer class="mt-4">
            <x-button href="/">Back</x-button>
            <x-button :if="$post->next ?? null" :href="$post->next->uri">Up next: {{ $post->next->title }}</x-button>
        </x-footer>

        <x-card :post="$post">
            <h2 hx-trigger="load" :hx-get="'/blog/' . $post->slug . '/comments'" hx-target="#comments">Comments</h2>
            <x-comments :post="$post" :comments="$comments ?? []" :user="$user ?? null"/>
        </x-card>
    </x-container>
</x-base>