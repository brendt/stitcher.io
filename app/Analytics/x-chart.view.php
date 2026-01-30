<?php

/**
 * @var \App\Analytics\Chart $chart
 * @var string $label
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
                        // min: 100,
                        beginAtZero: true,
                        display: true,
                        position: 'left',
                    },

                    <x-template :if="$chart->twoScales">
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false, // only want the grid lines for one axis to show up
                            },
                        },
                    </x-template>
                }
            },
            data: {
                labels: {!! json_encode($chart->labels->values()->toArray()) !!},
                datasets: [
                    <x-template :foreach="$chart->datasets as $dataset">
                        {!! $dataset->render() !!},
                    </x-template>
                ]
            }
        });
    </script>
</x-analytics-card>
