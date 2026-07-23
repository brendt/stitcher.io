<?php

use function Tempest\src_path;

$version ??= null;

if (! $version) {
    return;
}

$source = "Blog/VersionStats/Data/{$version}-package-stats.json"
    |> src_path(...)
    |> file_get_contents(...)
    |> (fn ($x) => json_decode($x, associative: true, flags: JSON_THROW_ON_ERROR));

$dates = array_keys($source);
sort($dates);

$latestDateByYear = [];

foreach ($dates as $date) {
    $latestDateByYear[substr($date, 0, 4)] = $date;
}

$years = array_slice(array_keys($latestDateByYear), -6);
$latestYear = array_key_last($latestDateByYear);

$versions = [];

foreach ($source[$latestDateByYear[$latestYear]] as $phpVersion => $value) {
    if ($value === '') {
        continue;
    }

    $versions[$phpVersion] = $phpVersion;
}

uksort($versions, version_compare(...));
$versions = array_reverse($versions);

$colorsByMajorVersion = [
    '5' => ['#D89086', '#C76F66', '#B4504B', '#963B39'],
    '7' => ['#8FB0D8', '#6F97C9', '#527DB4', '#3F6396', '#314B78'],
    '8' => ['#8DCB99', '#6FB982', '#54A56D', '#3F8758', '#326C49', '#28553B'],
];

$firstMinorVersionByMajorVersion = [
    '5' => 2,
    '7' => 0,
    '8' => 0,
];

$colorForVersion = function (string $phpVersion) use ($colorsByMajorVersion, $firstMinorVersionByMajorVersion): string {
    [$majorVersion, $minorVersion] = array_pad(explode('.', $phpVersion, 2), 2, '0');

    $colorShades = $colorsByMajorVersion[$majorVersion] ?? ['#A3A3A3'];
    $colorIndex = max(0, (int) $minorVersion - ($firstMinorVersionByMajorVersion[$majorVersion] ?? 0));

    return $colorShades[min($colorIndex, count($colorShades) - 1)];
};

$datasets = [];

foreach (array_values($versions) as $phpVersion) {
    $values = [];

    foreach ($years as $year) {
        $value = $source[$latestDateByYear[$year]][$phpVersion] ?? null;

        $values[] =
            $value === null || $value === ''
                ? 0
                : (int) $value;
    }

    $color = $colorForVersion($phpVersion);

    $datasets[] = [
        'label' => $phpVersion,
        'data' => $values,
        'backgroundColor' => $color,
        'borderColor' => $color,
        'borderWidth' => 1,
    ];
}

$chartData = [
    'labels' => $years,
    'datasets' => $datasets,
];

$chartId = 'package-stats-usage-graph-' . preg_replace('/[^a-z0-9]+/i', '-', $version) . '-' . bin2hex(random_bytes(4));
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
                type: 'bar',
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
                            labels: {
                                sort: (a, b) => a.text.localeCompare(b.text, undefined, { numeric: true }),
                            },
                        },
                    },
                    scales: {
                        x: {
                            stacked: true,
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                        },
                    },
                },
            });
        });
    })();
</script>
