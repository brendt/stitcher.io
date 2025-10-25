<x-php-base>
    <nav class="grid gap-2">
        <a :foreach="$files as $href => $file" :href="$href">{{ $file }}</a>
    </nav>
</x-php-base>`