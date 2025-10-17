<x-base>
    <x-container class="grid gap-4">
        <article class="bg-white p-4 md:pt-16 md:pb-8 border-sm shadow-md grid gap-4 rounded-xs">
            <div>
                <h1 class="text-4xl font-bold text-primary">{{ $post->title }}</h1>
                <small class="pl-1">Written on {{ $post->date->format('YYYY-MM-dd') }}</small>
            </div>
            {!! $post->content !!}
        </article>
        <footer class="py-4 hidden md:block">
            <x-button href="/">Back</x-button>
        </footer>
    </x-container>
</x-base>