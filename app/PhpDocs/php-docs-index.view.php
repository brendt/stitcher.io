<?php

use function Tempest\Router\uri;
use App\PhpDocs\PhpDocsController;

?>

<x-php-base>
    <div class="flex flex-col gap-8 justify-center items-center min-h-[90vh]">
        <div class="
            md:max-w-[600px]
            w-full
            flex flex-col gap-8 items-center
        ">
            <x-icon name="logos:php-alt" class="w-[100px] h-[100px] mb-[-2rem]"/>
            <span class="text-gray-500 text-sm">Press <span class="mx-1 bg-php-light px-2 p-1 rounded text-white font-bold">cmd + k</span> to search anywhere</span>
        </div>

    </div>
</x-php-base>