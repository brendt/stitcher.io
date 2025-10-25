<x-php-base>
    <x-slot name="left">
        <h3>Related</h3>
        <nav class="grid gap-2 ml-2 px-4 border-l-2 border-(--ui-php-light)">
            <a :foreach="$related as $href => $name" :href="$href" class="hover:text-(--ui-php) hover:underline">{{ $name }}</a>
        </nav>
    </x-slot>
    <article>
        <x-markdown :content="$content"/>
    </article>
</x-php-base>