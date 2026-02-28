<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Past Rolls</title>
    <link href="https://unpkg.com/tabulator-tables@5.5.2/dist/css/tabulator_bootstrap5.min.css" rel="stylesheet">
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; margin: 0; }
        .box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .filter-panel { background: #eef2f5; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 15px; align-items: center; }
        .filter-panel button { background: #0078D4; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
        .btn-back { background: #6c757d; color: white; text-decoration: none; padding: 8px 15px; border-radius: 5px; margin-right: auto; }
    </style>
</head>
<body>
<div class="box">
    <div class="filter-panel">
        <a href="index.php" class="btn-back">⬅️ Înapoi la Meniu</a>
        <label>De la:</label> <input type="date" id="start_date">
        <label>Până la:</label> <input type="date" id="end_date">
        <button onclick="applyFilter()">🔍 Adu Datele</button>
        <span id="loading" style="display:none; color:#0078D4;">⏳ Se încarcă...</span>
    </div>
    <div id="tabel"></div>
</div>
<script src="https://unpkg.com/tabulator-tables@5.5.2/dist/js/tabulator.min.js"></script>
<script>
    let table = new Tabulator("#tabel", {
        data: [], layout: "fitData", pagination: "local", paginationSize: 15, movableColumns: true,
        placeholder: "Alegeți perioada (după Data Descărcării)",
        columns: [
            { title: "ID DB", field: "id", frozen: true },
            { title: "Discharge Time", field: "roll_discharging_time", headerFilter: "input" },
            { title: "Charge Time", field: "roll_charging_time", headerFilter: "input" },
            { title: "Setup Code", field: "order_setup_code", headerFilter: "input" },
            { title: "Paper", field: "roll_paper", headerFilter: "input" },
            { title: "Grammage", field: "paper_grammage", headerFilter: "input" },
            { title: "Roll ID", field: "roll_id", headerFilter: "input" },
            { title: "Splicer", field: "splicer", headerFilter: "input" },
            { title: "Rem. Length", field: "roll_remaining_length", headerFilter: "input" },
            { title: "Rem. Diam", field: "roll_remaining_diameter", headerFilter: "input" }
        ]
    });
    async function applyFilter() {
        const s = document.getElementById('start_date').value, e = document.getElementById('end_date').value;
        if (!s || !e) return alert("Selectează ambele date!");
        document.getElementById('loading').style.display = "inline";
        table.clearData();
        const res = await fetch(`http://${window.location.hostname}:8080/api/past-rolls?start_date=${s}&end_date=${e}`);
        const json = await res.json();
        table.setData(json.data);
        document.getElementById('loading').style.display = "none";
    }
</script>
</body>
</html>

