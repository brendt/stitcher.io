<x-base title="Time" :footer="false">
    <x-slot name="favicon">
        <link rel="apple-touch-icon" sizes="180x180" href="/favicon/time/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon/time/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon/time/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
    </x-slot>

    <x-time-entries :perWeek="$perWeek" :isRunning="$isRunning" />
</x-base>
