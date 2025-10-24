<?php
use function Tempest\Router\uri;
use App\PhpDocs\PhpDocsController;
?>

<x-php-base>
    <div class="flex flex-col gap-8 justify-center items-center min-h-[90vh]">
        <div class="
            md:w-[30%]
            flex flex-col gap-8 items-center
        ">
            <x-icon name="logos:php-alt" class="w-[100px] h-[100px] mb-[-2rem]" />
            <input
                type="text"
                name="search"
                class="
                    w-full
                    p-2 px-4 text-lg bg-white rounded-full shadow
                    border-2 border-transparent
                    focus:shadow-xl focus:bg-gray-100 focus:outline-0  focus:border-php
                "
                placeholder="Search the docs…"
                :hx-post="uri([PhpDocsController::class, 'search'])"
                hx-trigger="input changed delay:500ms, keyup[key=='Enter']"
                hx-target="#php-search-results"
            >
            <span class="text-gray-500 text-sm">Press <span class="mx-1 bg-php-light px-2 p-1 rounded text-white font-bold">cmd + k</span> to search anywhere</span>
        </div>

        <x-php-search-results :matches="[]"/>
    </div>
</x-php-base>`