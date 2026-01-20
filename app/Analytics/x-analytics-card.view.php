<div class="grid gap-4 bg-white rounded-2xl p-2 md:p-4 md:pt-2 shadow">
    <div class="flex gap-2 items-center justify-between flex-wrap flex-col md:flex-row">
        <h2 class="font-bold">
            {{ $title }}
        </h2>

        <span :if="$total ?? null" class="text-sm text-gray-800 bg-gray-200 rounded-md px-2 py-1">total: {{ number_format($total) }}</span>
    </div>
    <div>
        <x-slot/>
    </div>
</div>