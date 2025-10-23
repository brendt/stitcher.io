<x-php-base>
    <div class="flex flex-col gap-8 justify-center items-center min-h-[90vh]">
        <div class="
            md:w-[30%]
            flex flex-col gap-4 items-center
        ">
            <input type="text" class="
                w-full
                p-2 px-4 text-lg bg-white rounded-full shadow
                border-2 border-transparent
                focus:shadow-xl focus:bg-gray-100 focus:outline-0  focus:border-php
            " placeholder="Search the docs…">
            <span class="text-gray-500 text-sm">Press <span class="bg-php-light px-2 p-1 rounded text-white font-bold">cmd + k</span> anywhere to search</span>
        </div>

        <nav>
            <x-php-button>array_find</x-php-button>
        </nav>
    </div>
</x-php-base>`