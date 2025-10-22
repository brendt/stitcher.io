<x-base :meta="$post->meta ?? null">
    <x-container class="grid gap-4">
        <x-article>
            <div>
                <h1 class="text-4xl font-bold text-primary"><a href="/">{{ $post->title }}</a></h1>
                <small class="pl-1">Written on {{ $post->date->format('YYYY-MM-dd') }}</small>
            </div>

            {!! $post->content !!}
        </x-article>

        <x-footer class="mt-4">
            <x-button href="/">Back</x-button>
            <x-button :if="$post->next ?? null" :href="$post->next->uri">Up next: {{ $post->next->title }}</x-button>
        </x-footer>

        <x-book-card title="Things I wish I knew when I started programming" img="/img/static/books/things-cover.png" href="https://things-i-wish-i-knew.com/">
            <p>
                This is my newest book aimed at programmers of any skill level. This book isn't about patterns, principles, or best practices; there's actually barely any code in it. It's about the many things I've learned along the way being a professional programmer, and about the many, many mistakes I made along that way as well. It's what I wish someone would have told me years ago, and I hope it might inspire you.
            </p>
        </x-book-card>

        <x-card :post="$post">
            <h2 hx-trigger="load" :hx-get="'/blog/' . $post->slug . '/comments'" hx-target="#comments">Comments</h2>
            <x-comments :post="$post" :comments="$comments ?? []" :user="$user ?? null"/>
        </x-card>
    </x-container>
</x-base>