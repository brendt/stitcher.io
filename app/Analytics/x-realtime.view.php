<?php

use App\Analytics\AnalyticsController;
use function Tempest\Router\uri;

?>

<div
        id="realtime"
        :hx-get="uri([AnalyticsController::class, 'realtime'])"
        hx-trigger="click, every 3s"
        hx-swap="outerHTML"
>
    <x-metric-card
        class="bg-blue-200"
        title="Active visitors"
        :metric="number_format($visits ?? 0)"
    />
</div>
