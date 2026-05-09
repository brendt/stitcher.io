<x-base title="Analytics">
    <x-slot name="head">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.6/dist/htmx.min.js" integrity="sha384-Akqfrbj/HpNVo8k11SXBb6TlBWmXXlYQrCSqEWmyKJe+hDm3Z/B2WVG4smwBkRVm" crossorigin="anonymous"></script>
    </x-slot>

    <div class="w-full flex flex-col pb-8">
        <div class="w-full z-10 p-2 md:p-12 grid gap-4 md:gap-8">
            <a href="/analytics">Back</a><h1> {{ $uri }}</h1>
            <x-chart :chart="$visitsPerDay" label="Visits" title="Visits last 124 days"></x-chart>
        </div>
    </div>
</x-base>