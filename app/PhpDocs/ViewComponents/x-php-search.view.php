<div
        id="search-popup"
        class="
            backdrop-blur-sm
            bg-php-light/10
            fixed  z-[100] top-0 left-0 h-full w-full
            flex flex-col justify-center
            hidden
        "
>
    <div class="w-full md:w-[600px] grid mx-auto gap-2">
        <input
                type="text"
                name="search"
                id="search"
                class="
                           mx-5
                           md:mx-0
                            p-2 px-4 text-lg bg-gray-50
                            md:shadow-xl
                            border-2
                            focus:shadow-md focus:bg-white focus:outline-0
                            border-php-light
                        "
                :class="($matches ?? []) !== [] ? 'rounded-b-md' : 'rounded-md'"
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