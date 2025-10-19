<?php
$small ??= false;
?>

<a :href="$href" class="text-center bg-primary rounded-full text-white font-bold  shadow-sm hover:shadow-lg" :class="$small ? 'text-sm p-2 px-4' : 'p-3 px-5'">
    <x-slot />
</a>