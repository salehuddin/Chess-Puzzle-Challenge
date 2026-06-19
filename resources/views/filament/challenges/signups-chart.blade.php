@php
    $chartId = 'challenge-signups-chart-' . uniqid();
@endphp

<div class="rounded-xl border border-gray-200 bg-white p-4">
    <canvas id="{{ $chartId }}" class="h-72 w-full"></canvas>
</div>

<script>
    (function () {
        const chartId = @js($chartId);
        const chartData = @js($chart);

        const renderChart = async () => {
            if (! window.Chart) {
                await new Promise((resolve, reject) => {
                    const existing = document.querySelector('script[data-chartjs="true"]');

                    if (existing) {
                        existing.addEventListener('load', resolve, { once: true });
                        existing.addEventListener('error', reject, { once: true });
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js';
                    script.dataset.chartjs = 'true';
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            }

            window.challengeSignupCharts = window.challengeSignupCharts || {};

            if (window.challengeSignupCharts[chartId]) {
                window.challengeSignupCharts[chartId].destroy();
            }

            const canvas = document.getElementById(chartId);

            if (! canvas) {
                return;
            }

            const ctx = canvas.getContext('2d');

            window.challengeSignupCharts[chartId] = new window.Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                        },
                    },
                },
            });
        };

        renderChart();
    })();
</script>
