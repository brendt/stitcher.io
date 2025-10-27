<!-- Header -->
<div class="flex flex-col inset-x-0 items-center lg:justify-center z-[1] h-(--ui-header-height) mt-7">
    <header
            class="
            group
            w-full lg:max-w-5xl xl:max-w-7xl 2xl:max-w-8xl
            fixed
            lg:rounded-md
            lg:-translate-y-2
             py-4 px-4
             flex items-center justify-between gap-2
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
                <span :if="$breadcrumbs->isLast" class="text-(--ui-white) border-(--ui-php-light) border-b-2 p-1 mx-1 text-sm cursor-default font-bold hidden md:block">{{ $name }}</span>
                <a :else :href="$href" class="text-(--ui-white) bg-(--ui-php) p-2 rounded-md text-sm hover:bg-(--ui-php-light) cursor-pointer font-bold">
                    {{ $name }}
                </a>
            </x-template>
        </div>

        <!-- Center -->
        <div class="flex items-center gap-4"></div>

        <!-- Right side -->
        <div class="flex items-center gap-2 font-medium">
            <div
                    id="search-trigger"
                    class="
                        flex items-center gap-2
                        text-(--ui-white) bg-(--ui-php) p-2 rounded-md text-sm hover:bg-(--ui-php-light) cursor-pointer font-bold
                    "
            >
                <span class="hidden sm:inline">cmd + k</span>
                <div class="size-5 flex justify-center items-center">
                    <x-icon name="oui:search" class="size-4 fill-php"/>
                </div>
            </div>

            <button id="toggle-theme" class="
                hidden sm:flex
                overflow-hidden transition hover:text-(--ui-primary)
                items-center gap-2
                text-(--ui-white) bg-(--ui-php) p-2 rounded-md text-sm hover:bg-(--ui-php-light) cursor-pointer font-bold
            ">
                <div class="size-5 flex justify-center items-center">
                    <x-icon name="tabler:moon" class="size-4 dark:opacity-0 dark:translate-y-full duration-200"/>
                </div>
            </button>

            <a :if="($originalUri ?? null)"
                :href="$originalUri"
                target="_blank" rel="noopener noreferrer"
                class="
                    hidden sm:flex
                    overflow-hidden transition hover:text-(--ui-primary)
                    items-center gap-2
                    text-(--ui-white) bg-(--ui-php) p-2 rounded-md text-sm hover:bg-(--ui-php-light) cursor-pointer font-bold
                "
                title="View original source"
            >
                <div class="size-5 flex justify-center items-center">
                    <x-icon name="simple-icons:php" class="size-6 dark:opacity-0 dark:translate-y-full duration-200"/>
                </div>
            </a>
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