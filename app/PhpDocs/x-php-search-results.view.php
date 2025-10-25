<?php
use function Tempest\Router\uri;
use App\PhpDocs\PhpDocsController;
?>

<div id="php-search-results">
    <nav  class="grid gap-1 bg-(--ui-php)/50 p-2 rounded-md shadow-md" :if="($keyword ?? null)">
        <a
            :foreach="$matches as $match"
            :href="uri([PhpDocsController::class, 'show'], slug: ltrim($match->uri, '/'))"
            class="
                search-result

                text-(--ui-white)
                hover:bg-(--ui-php) data-[selected]:bg-(--ui-php)

                p-2 px-3 rounded-md
                font-bold
            "
        >{{ $match->title }}</a>
        <div :forelse class="flex justify-center py-2 text-gray-500">
            No results
        </div>
    </nav>
</div>
