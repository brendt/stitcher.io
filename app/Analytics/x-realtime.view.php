<?php

use App\Analytics\AnalyticsController;
use function Tempest\Router\uri;

?>

<div
        id="realtime"
        :hx-get="uri([AnalyticsController::class, 'realtime'])"
        hx-trigger="every 3s, load"
        hx-swap="outerHTML"
        class="grid grid-cols-4 gap-4"
>
    <div class="p-4 bg-blue-200 rounded-2xl flex flex-col shadow-lg">
        <span class="text-xl font-bold font-mono">{{ $visits ?? 0 }}</span>
        <span>Current visitor count</span>
    </div>
</div>
