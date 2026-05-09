<x-base title="Analytics">
    <x-slot name="head">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.6/dist/htmx.min.js" integrity="sha384-Akqfrbj/HpNVo8k11SXBb6TlBWmXXlYQrCSqEWmyKJe+hDm3Z/B2WVG4smwBkRVm" crossorigin="anonymous"></script>
    </x-slot>

    <div class="w-full flex flex-col pb-8">
        <div class="w-full z-10 p-2 md:p-12 grid gap-4 md:gap-8">
            <div class="grid md:grid-cols-4 gap-4 md:gap-8">
                <x-realtime :visits="$realtimeVisitCount"/>

                <x-metric-card
                        class="bg-green-200"
                        title="Visits today"
                        :metric="number_format($visitsThisDay ?? 0)"
                ></x-metric-card>

                <x-metric-card
                        class="bg-yellow-200"
                        title="Visits this month"
                        :metric="number_format($visitsThisMonth ?? 0)"
                ></x-metric-card>

                <x-metric-card
                        class="bg-purple-200"
                        title="Most popular post today"
                        :metric="$mostPopularPostToday?->uri ?? '-'"
                ></x-metric-card>
            </div>

            <div class="w-full mx-auto grid md:grid-cols-2 xl:grid-cols-2 gap-4 md:gap-8">
                <x-chart :chart="$visitsPerHour" label="Visits" title="Visits last 48 hours"></x-chart>
                <x-analytics-card title="Popular posts">
                    <div class="grid rounded overflow-hidden">
                        <div class="flex justify-between px-3 py-2 font-bold bg-pastel">
                            <span>Post</span>
                            <span>Visits this month</span>
                        </div>
                        <a
                                :foreach="$popularPosts as $post"
                                :href="$post->detailUri"
                                class="flex justify-between w-full px-3 py-1 bg-pastel hover:bg-primary hover:text-white"
                        >
                            <span>{{ $post->uri }}</span>
                            <span>{{ number_format($post->count) }}</span>
                        </a>
                    </div>
                </x-analytics-card>
            </div>

            <x-chart :chart="$visitsPerDay" label="Visits" title="Visits last 100 days"></x-chart>

            <div class="w-full mx-auto grid md:grid-cols-2 xl:grid-cols-2 gap-8">
                <x-chart :chart="$visitsPerMonth" label="Visits" title="Visits last 36 months"></x-chart>
                <x-chart :chart="$visitsPerYear" label="Visits" title="Visits per year"></x-chart>
            </div>
        </div>
    </div>
</x-base>