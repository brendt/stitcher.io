<x-php-base>
    <x-php-header/>

    <x-docs-container>
        <div class="py-10 pb-20 max-w-2xl">

            <nav>
                <a
                    :foreach="$files as $href => $file"
                    :href="$href"
                    class="php-dir-row"
                >
                    <span class="php-dir-row-name">
                        <?= ucwords(str_replace(['-', '_'], ' ', basename($file))) ?>
                    </span>
                    <span class="php-dir-row-arrow">→</span>
                </a>
            </nav>

        </div>
    </x-docs-container>
</x-php-base>
