<div
        id="search-popup"
        class="
            backdrop-blur-sm
            md:bg-(--ui-php-light)/15
            fixed  z-[100] top-0 left-0 h-full w-full
            flex flex-col justify-center
            pt-4 md:pt-0
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
                            p-4 text-lg
                            bg-(--ui-php)/60
                            text-(--ui-white)
                            md:shadow-xl
                            focus:shadow-md focus:outline-0
                            rounded-md
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