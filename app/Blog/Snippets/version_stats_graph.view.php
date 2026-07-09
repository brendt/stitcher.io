<?php

use function Tempest\src_path;

$version ??= null;

if (! $version) {
    return;
}

$source = "Blog/VersionStats/Data/version-stats-{$version}.json"
    |> src_path(...)
    |> file_get_contents(...)
    |> (fn ($x) => json_decode($x, associative: true, flags: JSON_THROW_ON_ERROR));

$dates = array_keys($source);
sort($dates);

$versions = [];

foreach ($source as $stats) {
    foreach (array_keys($stats) as $phpVersion) {
        $versions[$phpVersion] = $phpVersion;
    }
}

uksort($versions, version_compare(...));

$colorsByMajorVersion = [
    '5' => ['#D89086', '#C76F66', '#B4504B', '#963B39'],
    '7' => ['#8FB0D8', '#6F97C9', '#527DB4', '#3F6396', '#314B78'],
    '8' => ['#8DCB99', '#6FB982', '#54A56D', '#3F8758', '#326C49', '#28553B'],
];

$firstMinorVersionByMajorVersion = [
    '5' => 3,
    '7' => 0,
    '8' => 0,
];

$colorForVersion = function (string $phpVersion) use ($colorsByMajorVersion, $firstMinorVersionByMajorVersion): string {
    [$majorVersion, $minorVersion] = array_pad(explode('.', $phpVersion, 2), 2, '0');

    $colorShades = $colorsByMajorVersion[$majorVersion] ?? ['#A3A3A3'];
    $colorIndex = max(0, ((int) $minorVersion) - ($firstMinorVersionByMajorVersion[$majorVersion] ?? 0));

    return $colorShades[min($colorIndex, count($colorShades) - 1)];
};

$datasets = [];
$maxUsagePercentage = 0;

foreach (array_values($versions) as $phpVersion) {
    $values = [];

    foreach ($dates as $date) {
        $value = $source[$date][$phpVersion] ?? null;

        if ($value === null || $value === '') {
            $values[] = null;

            continue;
        }

        $percentage = round((float) $value * 100, 2);
        $maxUsagePercentage = max($maxUsagePercentage, $percentage);

        $values[] = $percentage;
    }

    $color = $colorForVersion($phpVersion);

    $datasets[] = [
        'label' => $phpVersion,
        'data' => $values,
        'borderColor' => $color,
        'backgroundColor' => $color,
        'borderWidth' => 2,
        'pointRadius' => 0,
        'pointHoverRadius' => 5,
        'tension' => 0.35,
        'spanGaps' => false,
    ];
}

$chartData = [
    'labels' => $dates,
    'datasets' => $datasets,
];

$yAxisMax = ceil($maxUsagePercentage + 5);
$chartId = 'version-stats-graph-' . preg_replace('/[^a-z0-9]+/i', '-', $version) . '-' . bin2hex(random_bytes(4));
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
                            beginAtZero: true,
                            max: <?= $yAxisMax ?>,
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
