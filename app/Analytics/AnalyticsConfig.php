<?php

declare(strict_types=1);

namespace App\Analytics;

final class AnalyticsConfig
{
    public function __construct(
        private(set) string $accessLogPath,
    ) {}
}
