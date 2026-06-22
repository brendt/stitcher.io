<?php
$targetHours = 30.4;
$dayOfWeek = (int) date('N');
$workDaysElapsed = min($dayOfWeek, 5);
$expectedHours = $workDaysElapsed * ($targetHours / 5);

$currentWeek = null;
$pastWeeks = [];
foreach ($perWeek as $w) {
    if ($w->isCurrent) {
        $currentWeek = $w;
    } else {
        $pastWeeks[] = $w;
    }
}

$currentHours = round($currentWeek?->totalHours ?? 0, 1);
$progressPct = min(100, ($currentHours / $targetHours) * 100);
$expectedPct = min(100, ($expectedHours / $targetHours) * 100);
$hoursDiff = round(abs($currentHours - $expectedHours), 1);
$isOnTrack = $currentHours >= $expectedHours;
$statusLabel = $hoursDiff < 0.1 ? 'On track' : ($isOnTrack ? "+{$hoursDiff}h ahead" : "-{$hoursDiff}h behind");
$statusColor = $hoursDiff < 0.1 ? 'bg-green-100 text-green-700' : ($isOnTrack ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700');
$barColor = $isOnTrack ? 'bg-green-500' : 'bg-orange-400';
?>

<div id="time-entries" class="grid gap-3">

    {{-- Current week hero card --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">

        {{-- Hours + status --}}
        <div class="px-5 pt-5 pb-4">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">This week</div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-4xl font-bold tabular-nums">{{ $currentHours }}</span>
                        <span class="text-gray-400 text-lg">/ {{ $targetHours }}h</span>
                    </div>
                </div>

                <span class="text-xs font-semibold px-2.5 py-1 rounded-full mt-1 {{ $statusColor }}">
                    {{ $statusLabel }}
                </span>
            </div>

            {{-- Progress bar --}}
            <div class="relative h-2.5 bg-gray-100 rounded-full overflow-visible mb-2">
                {{-- Expected-pace marker --}}
                <div
                    class="absolute top-1/2 -translate-y-1/2 w-0.5 h-4 bg-gray-300 rounded-full z-10"
                    style="left: {{ $expectedPct }}%"
                ></div>
                {{-- Actual progress --}}
                <div
                    class="h-full rounded-full transition-all {{ $barColor }}"
                    style="width: {{ $progressPct }}%"
                ></div>
            </div>
            <div class="flex justify-between text-xs text-gray-300 select-none">
                <span>0h</span>
                <span>{{ $targetHours }}h</span>
            </div>
        </div>

        {{-- Start / stop button --}}
        <div class="px-5 pb-5">
            <button
                :if="!$isRunning"
                hx-post="/time/start"
                hx-target="#time-entries"
                hx-swap="outerHTML"
                class="w-full bg-primary text-white font-bold py-3 rounded-xl shadow-sm hover:opacity-90 active:scale-95 transition text-center"
            >Start</button>
            <button
                :if="$isRunning"
                hx-post="/time/stop"
                hx-target="#time-entries"
                hx-swap="outerHTML"
                class="w-full bg-red-500 text-white font-bold py-3 rounded-xl shadow-sm hover:opacity-90 active:scale-95 transition flex items-center justify-center gap-2"
            >
                <span class="w-2 h-2 rounded-full bg-white/80 animate-pulse"></span>
                Stop
            </button>
        </div>

        {{-- This week's entries --}}
        <div :if="$currentWeek" class="border-t border-gray-100 divide-y divide-gray-50">
            <div
                :foreach="$currentWeek->timeEntries as $timeEntry"
                class="flex items-center justify-between px-5 py-2.5 text-sm"
            >
                <span class="text-gray-400 font-mono">
                    {{ $timeEntry->start->format('EEEE HH:mm') }} — {{ $timeEntry->end?->format('HH:mm') ?? '…' }}
                </span>
                <span class="font-mono text-gray-600">{{ round($timeEntry->totalHours, 1) }}h</span>
            </div>
        </div>
    </div>

    {{-- Past weeks --}}
    <div :if="count($pastWeeks) > 0" class="grid gap-1.5">
        <div
            :foreach="$pastWeeks as $weekEntry"
            class="bg-white rounded-xl shadow-sm flex items-center justify-between px-4 py-3"
        >
            <span class="text-sm text-gray-500">Week {{ $weekEntry->week }}</span>
            <span class="font-mono text-sm {{ $weekEntry->totalHours >= $targetHours ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                {{ round($weekEntry->totalHours, 1) }}h
            </span>
        </div>
    </div>

</div>
