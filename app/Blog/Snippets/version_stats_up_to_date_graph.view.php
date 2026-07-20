<?php

use function Tempest\src_path;

$version ??= null;

if (! is_string($version) || $version === '') {
    return;
}

/** @var array<string, array<string, float|string|null>> $source */
$source = "Blog/VersionStats/Data/{$version}-up-to-date.json"
    |> src_path(...)
    |> file_get_contents(...)
    |> (fn ($x) => json_decode($x, associative: true, flags: JSON_THROW_ON_ERROR));

$dates = array_keys($source);
sort($dates);

$series = [
    'installs' => [
        'label' => 'Installs with supported PHP version',
        'color' => '#3F8758',
        'values' => [],
    ],
    //    'packages' => [
    //        'label' => 'Packages with only supported PHP versions',
    //        'color' => '#527DB4',
    //        'values' => [],
    //    ],
];

foreach ($dates as $date) {
    foreach ($series as $key => $data) {
        $value = $source[$date][$key] ?? null;

        if ($value === null || $value === '' || ! is_numeric($value)) {
            $series[$key]['values'][] = null;

            continue;
        }

        $series[$key]['values'][] = round((float) $value * 100, 2);
    }
}

$allValues = array_filter(
    array_merge(...array_column($series, 'values')),
    static fn (?float $value): bool => $value !== null,
);

$yAxisMin = max(0, min($allValues) - 5);

$chartData = [
    'labels' => $dates,
    'datasets' => array_values(
        array_map(
            static fn (array $data): array => [
                'label' => $data['label'],
                'data' => $data['values'],
                'borderColor' => $data['color'],
                'backgroundColor' => $data['color'],
                'borderWidth' => 2,
                'pointRadius' => 0,
                'pointHoverRadius' => 5,
                'tension' => 0.35,
                'spanGaps' => false,
            ],
            $series,
        ),
    ),
];

$chartId = 'version-stats-up-to-date-graph-' . bin2hex(random_bytes(4));
$chartDataJson = json_encode($chartData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
?>

<div id="<?= $chartId ?>" class="not-prose my-8 h-[420px] w-full">
    <canvas></canvas>
</div>

<script>
    (() => {
        const graph = document.getElementById('<?= $chartId ?>');

        if (!graph) {
            return;
        }

        const chartData = <?= $chartDataJson ?>;

        function loadChartJs() {
            if (window.Chart) {
                return Promise.resolve();
            }

            if (window.versionStatsGraphChartJsLoading) {
                return window.versionStatsGraphChartJsLoading;
            }

            window.versionStatsGraphChartJsLoading = new Promise((resolve, reject) => {
                const script = document.createElement('script');

                script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                script.onload = resolve;
                script.onerror = reject;

                document.head.appendChild(script);
            });

            return window.versionStatsGraphChartJsLoading;
        }

        loadChartJs().then(() => {
            const canvas = graph.querySelector('canvas');

            if (!canvas) {
                return;
            }

            new Chart(canvas, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.dataset.label}: ${context.parsed.y}%`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Year',
                            },
                            ticks: {
                                callback: function (value) {
                                    return String(this.getLabelForValue(value)).slice(0, 4);
                                },
                            },
                        },
                        y: {
                            min: <?= $yAxisMin ?>,
                            max: 100,
                            ticks: {
                                callback: (value) => `${value}%`,
                            },
                        },
                    },
                },
            });
        });
    })();
</script>
