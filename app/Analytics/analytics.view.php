<x-base title="Analytics">
    <x-slot name="head">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </x-slot>

    <div class="w-full flex flex-col pb-8">
        <div class="w-full z-10 p-12 grid gap-8">
            <x-chart :chart="$visitsPerMonth" label="Visits" chart-title="Visits last 36 months"></x-chart>

            <div class="w-full mx-auto grid md:grid-cols-2 xl:grid-cols-2 gap-12">
                <x-chart :chart="$visitsPerDay" label="Visits" chart-title="Visits last 62 days"></x-chart>
                <x-chart :chart="$visitsPerHour" label="Visits" chart-title="Visits last 24 hours"></x-chart>
            </div>
        </div>
    </div>
</x-base>