<?php include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Fosber</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; text-align: center; }
        .box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 1000px; margin: auto; }
        .charts { display: flex; justify-content: space-between; flex-wrap: wrap; margin-top: 30px; }
        .chart-container { width: 48%; min-width: 400px; }
        a.btn-back { display: inline-block; background: #6c757d; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="box">
        <div style="text-align: left;"><a href="index.php" class="btn-back">⬅️ Înapoi la Meniu</a></div>
        <h2>📈 Dashboard Analytics</h2>
        
        <div class="charts">
            <div class="chart-container">
                <h3>Top 5 Tipuri Hârtie Utilizate</h3>
                <canvas id="pieChart"></canvas>
            </div>
            <div class="chart-container">
                <h3>Evoluție Metri Produși (Ultimele 7 Zile)</h3>
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const host = `http://${window.location.hostname}:8080`;

        // Incarcam Top Hartie (Pie Chart)
        fetch(`${host}/api/stats/top-paper`).then(res => res.json()).then(data => {
            new Chart(document.getElementById('pieChart'), {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{ data: data.data, backgroundColor: ['#0078D4', '#28a745', '#dc3545', '#ffc107', '#17a2b8'] }]
                }
            });
        });

        // Incarcam Metri pe Zi (Bar Chart)
        fetch(`${host}/api/stats/meters`).then(res => res.json()).then(data => {
            new Chart(document.getElementById('barChart'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{ label: 'Metri Liniari Produși', data: data.data, backgroundColor: '#6f42c1' }]
                }
            });
        });
    </script>
</body>
</html>

