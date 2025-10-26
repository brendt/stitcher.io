<x-php-base>
    <x-slot name="left">
        <span class="font-semibold text-(--ui-text)">
            Related
        </span>
        <nav class="grid mt-2 border-l-1 border-(--ui-php-light)">
            <a
                    :foreach="$related as $href => $name"
                    :href="$href"
                    class="
                   px-2.5 py-1.5
                  text-(--ui-text-muted)
                  hover:text-(--ui-php) hover:underline
                ">{{ $name }}</a>
        </nav>
    </x-slot>
    <article>
        {!! $content !!}
    </article>
</x-php-base>