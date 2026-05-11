<!-- Spacer so fixed header doesn't overlap content -->
<div class="h-(--ui-header-height)"></div>

<header
    id="header"
    class="
        fixed top-0 inset-x-0 z-50
        flex items-center justify-between gap-4
        px-4 md:px-8 h-(--ui-header-height)
        bg-(--ui-bg)/20 backdrop-blur-md
        transition-shadow duration-200
    "
>
    <!-- Left: logo + breadcrumbs -->
    <div class="flex items-center gap-2">
        <a href="/php" class="php-header-logo shrink-0 mr-2">
            <x-icon name="logos:php-alt" class="size-9"/>
        </a>

        <x-template :isset="$breadcrumbs">
            <x-template :foreach="$breadcrumbs->loop() as $href => $name">
                <span class="text-(--ui-text-muted) text-sm select-none">·</span>
                <span :if="$breadcrumbs->isLast" class="text-(--ui-text) text-sm font-medium truncate max-w-[18rem] hidden md:block">
                    {{ $name }}
                </span>
                <a :else :href="$href" class="text-(--ui-text-muted) text-sm hover:text-(--ui-php) transition-colors hidden md:block">
                    {{ $name }}
                </a>
            </x-template>
        </x-template>
    </div>

    <!-- Right: actions -->
    <div class="flex items-center gap-0.5">

        <!-- Search -->
        <button id="search-trigger" class="php-header-btn gap-2">
            <x-icon name="oui:search" class="size-4"/>
            <span class="hidden sm:inline">Search</span>
            <kbd class="php-header-kbd hidden sm:inline">⌘K</kbd>
        </button>

        <!-- Dark mode -->
        <button id="toggle-theme" onclick="toggleDarkMode()" class="php-header-btn">
            <x-icon name="tabler:moon" class="size-4"/>
        </button>

        <!-- php.net source link -->
        <a
            :if="($originalUri ?? null)"
            :href="$originalUri"
            target="_blank"
            rel="noopener noreferrer"
            title="View on php.net"
            class="php-header-btn"
        >
            <x-icon name="simple-icons:php" class="size-5"/>
        </a>

    </div>
</header>

<script>
    const header = document.getElementById('header');
    window.addEventListener('scroll', () => {
        header.classList.toggle('shadow-sm', window.scrollY > 0);
    });
</script>
