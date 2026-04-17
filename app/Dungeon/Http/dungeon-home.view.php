<?php

use App\Dungeon\Http\DungeonGameController;
use function Tempest\Router\uri;

?>

<x-dungeon>
    <x-slot name="head">
        <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.6/dist/htmx.min.js" integrity="sha384-Akqfrbj/HpNVo8k11SXBb6TlBWmXXlYQrCSqEWmyKJe+hDm3Z/B2WVG4smwBkRVm" crossorigin="anonymous"></script>
    </x-slot>

    <div class="grid gap-8 pb-8">
        <x-deck-builder :deck="$deck" :shop="$shop" :stats="$stats" />
    </div>
</x-dungeon>