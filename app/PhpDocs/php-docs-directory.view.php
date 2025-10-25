<x-php-base>
    <nav class="grid border-l-2 border-(--ui-php) pl-4 mb-16">
        <a
                :foreach="$files as $href => $file"
                :href="$href"
                class="
                    p-2 py-3
                    font-bold
                    hover:underline
                "
        >{{ $file }}</a>
    </nav>
</x-php-base>