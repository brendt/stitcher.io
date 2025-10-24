<?php
use function Tempest\Router\uri;
use App\PhpDocs\PhpDocsController;
?>

<div id="php-search-results">
    <nav  class="grid gap-1 bg-white p-2 mx-5 rounded-b-md shadow-md" :if="($keyword ?? null)">
        <a
            :foreach="$matches as $match"
            :href="uri([PhpDocsController::class, 'show'], slug: ltrim($match->uri, '/'))"
            class="
                search-result
                bg-gray-50 p-2 px-3 rounded-md
                data-[selected]:bg-php-light data-[selected]:text-white
                hover:bg-php-light hover:text-white
                font-bold
            "
        >{{ $match->title }}</a>
        <div :forelse class="flex justify-center py-2 text-gray-500">
            No results
        </div>
    </nav>
</div>
