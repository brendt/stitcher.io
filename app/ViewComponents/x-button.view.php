<?php
$small ??= false;
?>

<a :href="$href"
        class="text-center bg-primary rounded-full text-white font-bold  shadow-sm underline hover:no-underline hover:shadow-lg"
        :class="($small ? 'text-sm p-2 px-4' : 'p-3 px-5') . ' ' . ($class ?? '')">
    <x-slot />
</a>