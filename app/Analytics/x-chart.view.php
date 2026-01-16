<?php

/**
 * @var \App\Analytics\Chart $chart
 * @var string $label
 * @var string $title
 */

use Symfony\Component\Uid\Uuid;

$uuid = Uuid::v4()->toString();
?>

<div class="grid gap-4 bg-white rounded-2xl p-2 md:p-4 md:pt-2 shadow">
    <div class="px-1 flex gap-2 items-center justify-between flex-wrap flex-col md:flex-row">
        <h2 class="font-bold">
            {{ $chartTitle }}
        </h2>
        <span class="text-sm text-gray-800 bg-gray-200 rounded-md px-2 py-1">total: {{ number_format($chart->total) }}</span>
    </div>
    <div>
        <canvas id="<?= $uuid ?>" class="h-[300px] w-[100px]"></canvas>
    </div>

    <script>
        var ctx = document.getElementById('<?= $uuid ?>');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart->labels->values()->toArray()) ?>,
                datasets: [{
                    label: '<?= $label ?>',
                    data: <?= json_encode($chart->values->values()->toArray()) ?>,
                    borderColor: '#fe2977',
                    borderWidth: 2
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled: false
                    },
                },
                maintainAspectRatio: false,
                elements: {
                    line: {
                        tension: 0.4
                    }
                },
                scales: {
                    y: {
                        <?php if ($chart->min !== null): ?>
                            min: <?= $chart->min - 100 ?>,
                        <?php endif; ?>
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</div>
