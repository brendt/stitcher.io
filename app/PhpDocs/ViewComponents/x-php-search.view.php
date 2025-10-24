<div
        id="search-popup"
        class="
            backdrop-blur-lg
            fixed  z-[100] top-0 left-0 h-full w-full
            flex flex-col justify-center
            hidden
        "
>
    <div class="w-full md:w-[600px] grid mx-auto">
        <input
                type="text"
                name="search"
                id="search"
                class="
                           mx-5
                           md:mx-0
                           rounded-t-md
                            p-2 px-4 text-lg bg-gray-50
                            md:rounded-full md:shadow-xl
                            border-2 border-transparent
                            focus:shadow-md focus:bg-white focus:outline-0  focus:border-php
                        "
                autocomplete="off"
                placeholder="Search the docs…"
                :hx-post="uri([PhpDocsController::class, 'search'])"
                hx-trigger="input changed delay:50ms, load"
                hx-target="#php-search-results"
                :value="$keyword"
                autofocus
        >
        <x-php-search-results :matches="$matches ?? []" :keyword="$keyword ?? null"/>
    </div>
</div>