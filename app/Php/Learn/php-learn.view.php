<x-php-base>
    <x-php-header />
    <div class="php-learn-layout">

        {{-- --------------------------------
             SIDEBAR — chapter list
        -------------------------------- --}}
        <aside class="php-learn-sidebar px-2">
            <p class="php-learn-sidebar-label">Learn PHP</p>
            <nav class="grid gap-1">
                <a
                    :foreach="$chapters as $item"
                    :href="$item->uri"
                    :class="$item->slug === $chapter->slug ? 'php-learn-nav-item active' : 'php-learn-nav-item'"
                >
                    <span class="php-learn-nav-number">{{ (int) $item->index }}</span>
                    {{ $item->title }}
                </a>
            </nav>
        </aside>

        {{-- --------------------------------
             MAIN — chapter content
        -------------------------------- --}}
        <div class="php-learn-content php-content px-4">
            {!! $chapter->content !!}
        </div>

    </div>
</x-php-base>
