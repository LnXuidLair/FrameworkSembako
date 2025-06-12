<x-filament-panels::page>
    <!-- Tambahan visualisasi -->
    <canvas id="clusterChart" width="400" height="200"></canvas>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const data = @json($dataPoints);

        const grouped = {};
        data.forEach(point => {
            if (!grouped[point.cluster]) grouped[point.cluster] = [];
            grouped[point.cluster].push({ x: point.x, y: point.y, name: point.name });
        });

        const datasets = Object.keys(grouped).map((cluster, index) => ({
            label: cluster,
            data: grouped[cluster].map(point => ({
                x: point.x,
                y: point.y,
                name: point.name  // Menambahkan nama titik ke data
            })),
            backgroundColor: ['red', 'blue', 'green'][index % 3],
        }));

        new Chart(document.getElementById('clusterChart'), {
            type: 'scatter',
            data: { datasets },
            options: {
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            title: function(tooltipItem) {
                                return tooltipItem[0].raw.name; // Menampilkan nama titik saat hover
                            },
                            label: function(tooltipItem) {
                                return `X: ${tooltipItem.raw.x}, Y: ${tooltipItem.raw.y}`; // Menampilkan X dan Y saat hover
                            }
                        }
                    }
                },
                scales: {
                    x: { title: { display: true, text: 'X' } },
                    y: { title: { display: true, text: 'Y' } }
                }
            }
        });
    </script>
    <!-- Akhir tambahan visualisasi -->
</x-filament-panels::page>
