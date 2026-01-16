<?php

/**
 * @var \App\Analytics\Chart $chart
 * @var string $label
 * @var string $title
 */

use Symfony\Component\Uid\Uuid;

$uuid = Uuid::v4()->toString();
?>

<x-analytics-card :title="$title" :total="$chart->total" :uuid="$uuid" :chart="$chart" :label="$label">
    <canvas id="<?= $uuid ?>" class="h-[300px] w-[100px]"></canvas>
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
</x-analytics-card>
