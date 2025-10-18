<x-base>
    <x-article>
        <div>
            <h1 class="text-4xl font-bold text-primary"><a href="/">{{ $post->title }}</a></h1>
            <small class="pl-1">Written on {{ $post->date->format('YYYY-MM-dd') }}</small>
        </div>
        {!! $post->content !!}
    </x-article>

    <x-footer class="mt-4">
        <x-button href="/">Back</x-button>
        <x-button :if="$post->next" :href="$post->next->uri">Up next: {{ $post->next->title }}</x-button>
    </x-footer>
</x-base>