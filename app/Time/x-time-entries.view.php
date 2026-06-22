<?php

use Tempest\DateTime\DateTime;
use Tempest\DateTime\Timezone;

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
$hoursDiff = round(abs($currentHours - $expectedHours), 1);
$isOnTrack = $currentHours >= $expectedHours;
$passedColor = $isOnTrack ? 'bg-green-700' : 'bg-orange-600';
$dayMarkers = array_map(
    fn($pct) => ['pct' => $pct, 'color' => $progressPct >= $pct ? $passedColor : 'bg-gray-300'],
    [20, 40, 60, 80],
);
$onTrackThreshold = $targetHours / 5;
$statusLabel = ($isOnTrack || $hoursDiff < $onTrackThreshold) ? 'On track' : "-{$hoursDiff}h behind";
$statusColor = ($isOnTrack || $hoursDiff < $onTrackThreshold) ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700';
$barColor = $isOnTrack ? 'bg-green-500' : 'bg-orange-400';
$todayPrefill = DateTime::now(Timezone::EUROPE_BRUSSELS)->format('yyyy-MM-dd HH:mm');
?>

<div id="time-entries" class="grid gap-3">

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

    {{-- Fixed bottom card --}}
    <div class="fixed bottom-0 left-0 right-0 z-20 p-3" style="padding-bottom: max(12px, env(safe-area-inset-bottom))">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

            {{-- Hours + status + progress --}}
            <div class="px-5 pt-4 pb-3">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl font-bold tabular-nums">{{ $currentHours }}</span>
                        <span class="text-gray-400">/ {{ $targetHours }}h</span>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusColor }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="relative h-2 bg-gray-100 rounded-full overflow-visible">
                    <div
                        :foreach="$dayMarkers as $marker"
                        class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 w-3 h-3 rounded-full z-10 {{ $marker['color'] }}"
                        style="left: {{ $marker['pct'] }}%"
                    ></div>
                    <div
                        class="h-full rounded-full transition-all {{ $barColor }}"
                        style="width: {{ $progressPct }}%"
                    ></div>
                </div>
            </div>

            {{-- This week's entries --}}
            <div :if="$currentWeek" class="border-t border-gray-100 divide-y divide-gray-50">
                <details
                    :foreach="$currentWeek->timeEntries as $timeEntry"
                    class="group"
                >
                    <summary class="flex items-center justify-between px-5 py-2.5 text-sm cursor-pointer list-none">
                        <span class="text-gray-400 font-mono">
                            {{ $timeEntry->start->format('EEEE HH:mm') }} — {{ $timeEntry->end?->format('HH:mm') ?? '…' }}
                        </span>
                        <span class="font-mono text-gray-600">{{ round($timeEntry->totalHours, 1) }}h</span>
                    </summary>
                    <div class="px-5 pb-3">
                        <button
                            hx-post="/time/remove/{{ $timeEntry->id }}"
                            hx-target="#time-entries"
                            hx-swap="outerHTML"
                            hx-confirm="Delete this entry?"
                            class="w-full bg-red-500 text-white font-bold py-2.5 rounded-xl text-sm"
                        >Delete entry</button>
                    </div>
                </details>
            </div>

            {{-- Start / stop button --}}
            <div class="px-5 pb-4 pt-3 grid gap-2">
                <button
                    :if="!$isRunning"
                    hx-post="/time/start"
                    hx-target="#time-entries"
                    hx-swap="outerHTML"
                    class="w-full bg-green-700/80 text-white font-bold py-3 rounded-xl shadow-sm active:scale-95 transition text-center"
                >Start</button>
                <button
                    :if="$isRunning"
                    hx-post="/time/stop"
                    hx-target="#time-entries"
                    hx-swap="outerHTML"
                    class="w-full bg-red-700/80 text-white font-bold py-3 rounded-xl shadow-sm active:scale-95 transition flex items-center justify-center gap-2"
                >
                    <span class="w-2 h-2 rounded-full bg-white/80 animate-pulse"></span>
                    Stop
                </button>

                {{-- Manual entry --}}
                <details class="group">
                    <summary class="cursor-pointer text-sm text-center text-gray-400 transition list-none py-1 select-none">
                        + Manual entry
                    </summary>
                    <form
                        hx-post="/time/manual"
                        hx-target="#time-entries"
                        hx-swap="outerHTML"
                        class="mt-3 grid gap-3"
                    >
                        <div class="grid gap-1">
                            <label class="text-xs text-gray-500 font-medium">Start</label>
                            <input
                                type="datetime-local"
                                name="start"
                                required
                                value="{{ $todayPrefill }}"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                            >
                        </div>
                        <div class="grid gap-1">
                            <label class="text-xs text-gray-500 font-medium">End <span class="text-gray-300">(optional)</span></label>
                            <input
                                type="datetime-local"
                                name="end"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                            >
                        </div>
                        <button
                            type="submit"
                            class="w-full bg-gray-800 text-white font-bold py-2.5 rounded-xl shadow-sm active:scale-95 transition"
                        >Save entry</button>
                    </form>
                </details>
            </div>
        </div>
    </div>

</div>
