<x-base title="Analytics">
    <x-slot name="head">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </x-slot>

    <div class="w-full flex flex-col pb-8">
        <div class="w-full z-10 p-2 md:p-12 grid gap-4 md:gap-8">
            <div class="w-full mx-auto grid md:grid-cols-2 xl:grid-cols-2 gap-12">
                <x-chart :chart="$visitsPerHour" label="Visits" title="Visits last 48 hours"></x-chart>
                <x-analytics-card title="Popular posts this week">
                    <table>
                        <tr class="table-head">
                            <td>Post</td>
                            <td class="text-right">Visits this week</td>
                        </tr>
                        <tr :foreach="$popularPosts as $post">
                            <td>
                                <a :href="$post->uri" class="underline hover:no-underline">{{ $post->uri }}</a>
                            </td>
                            <td class="text-right">{{ $post->count }}</td>
                        </tr>
                        <tr :forelse>
                            <td colspan="2" class="text-center">No data</td>
                        </tr>
                    </table>
                </x-analytics-card>
            </div>

            <x-chart :chart="$visitsPerMonth" label="Visits" title="Visits last 36 months"></x-chart>

            <div class="w-full mx-auto grid md:grid-cols-2 xl:grid-cols-2 gap-12">
                <x-chart :chart="$visitsPerDay" label="Visits" title="Visits last 62 days"></x-chart>
                <x-chart :chart="$visitsPerYear" label="Visits" title="Visits per year"></x-chart>
            </div>
        </div>
    </div>
</x-base>