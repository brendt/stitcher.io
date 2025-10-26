<!-- Header -->
<div class="flex flex-col inset-x-0 items-center lg:justify-center z-[1] h-(--ui-header-height)">
    <header
            class="
            group
            w-full lg:max-w-5xl xl:max-w-7xl 2xl:max-w-8xl
            fixed
            lg:rounded-md
            lg:-translate-y-2
             py-4 px-4
             flex items-center justify-between
             duration-200
             lg:data-[scrolling]:translate-y-2
             data-[scrolling]:ring-(--ui-php)/90
             data-[scrolling]:backdrop-blur
             bg-(--ui-php)/80
             data-[scrolling]:bg-(--ui-php)/60 z-[1]"
            id="header"
    >
        <!-- Left side -->
        <div class="flex items-center gap-2">
            <a href="/php" class="mx-4">
                <x-icon name="logos:php-alt" class="size-10 fill-white"/>
            </a>
            <x-template :foreach="$breadcrumbs->loop() as $href => $name">
                <span :if="$breadcrumbs->isLast" class="text-(--ui-white) border-(--ui-php) border-b-2 p-1 mx-1 text-sm cursor-default font-bold hidden md:block">{{ $name }}</span>
                <a :else :href="$href" class="text-(--ui-white) bg-(--ui-php) p-2 rounded-md text-sm hover:bg-(--ui-php-light) cursor-pointer font-bold">
                    {{ $name }}
                </a>
            </x-template>
        </div>

        <!-- Center -->
        <div class="flex items-center gap-4">
            <!--            <x-search/>-->
        </div>

        <!-- Right side -->
        <div class="flex items-center gap-2 font-medium">
            <div
                    id="search-trigger"
                    class="
                        flex items-center gap-2
                        text-(--ui-white) bg-(--ui-php) p-2 rounded-md text-sm hover:bg-(--ui-php-light) cursor-pointer font-bold
                    "
            >
                <span>cmd + k</span>
                <x-icon name="oui:search" class="size-3 fill-php"/>
            </div>

            <button id="toggle-theme" class="
                            overflow-hidden transition hover:text-(--ui-primary)
                            flex items-center gap-2
                            text-(--ui-white) bg-(--ui-php) p-2 rounded-md text-sm hover:bg-(--ui-php-light) cursor-pointer font-bold
                        ">
                <x-icon name="tabler:moon" class="size-5 dark:opacity-0 dark:translate-y-full duration-200"/>
            </button>
        </div>
    </header>
</div>
<script>
    const header = document.getElementById('header')
    window.addEventListener('scroll', () => {
        if (window.scrollY > 0) {
            header.dataset.scrolling = true
        } else {
            delete header.dataset.scrolling
        }
    })
</script>