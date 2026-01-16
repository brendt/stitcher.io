<?php

/**
 * @var \App\Analytics\Chart $chart
 * @var string $label
 * @var string $title
 */

use Symfony\Component\Uid\Uuid;

$uuid = Uuid::v4()->toString();
?>

<div class="grid gap-2 bg-white rounded-2xl p-4 pt-2 shadow">
    <h2 class="ml-4 font-bold">{{ $chartTitle }}</h2>
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
                    borderWidth: 2
                }]
            },
            options: {
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
