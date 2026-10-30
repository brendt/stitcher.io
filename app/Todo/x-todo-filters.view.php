<?php
/**
 * @var array<string, string> $links The active filters, each with the URL that shows the page without it
 */
?>

<div class="flex flex-wrap justify-center gap-x-4 gap-y-2">
    <a
        :foreach="$links as $label => $uri"
        :href="$uri"
        :title="'Stop filtering on ' . $label"
        class="font-bold px-1.5 py-0.5 rounded bg-gray-100 border border-dotted border-black"
    >{{ $label }}</a>
</div>
