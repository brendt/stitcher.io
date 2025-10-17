<x-base>
    <x-article>
        <div>
            <h1 class="text-4xl font-bold text-primary">{{ $post->title }}</h1>
            <small class="pl-1">Written on {{ $post->date->format('YYYY-MM-dd') }}</small>
        </div>
        {!! $post->content !!}
    </x-article>

    <x-footer class="mt-4">
        <div>
            <x-button href="/">Back</x-button>
        </div>
    </x-footer>
</x-base>