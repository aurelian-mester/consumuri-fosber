<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Combinations - ss02</title>
    <link href="https://unpkg.com/tabulator-tables@5.5.2/dist/css/tabulator_bootstrap5.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; box-sizing: border-box; }
        h2 { color: #333; margin-top: 0; }
        
        /* Stiluri pentru zona de filtrare */
        .filter-panel { background: #eef2f5; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        .filter-panel label { font-weight: bold; color: #555; }
        .filter-panel input[type="date"] { padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 15px; }
        .filter-panel button { background-color: #0078D4; color: white; border: none; padding: 10px 20px; font-size: 15px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .filter-panel button:hover { background-color: #005a9e; }
        
        #combinations-table { margin-top: 20px; font-size: 13px; }
        #loading { color: #0078D4; font-weight: bold; display: none; }
    </style>
</head>
<body>

<div class="box">
    <h2>📊 Tabela: ss02.combinations</h2>
    
    <div class="filter-panel">
        <label for="start_date">De la data (StartRun):</label>
        <input type="date" id="start_date">
        
        <label for="end_date">Până la data:</label>
        <input type="date" id="end_date">
        
        <button onclick="applyDateFilter()">🔍 Adu Datele</button>
        <span id="loading">⏳ Interogăm baza de date...</span>
    </div>
    
    <div id="combinations-table"></div>
</div>

<script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.2/dist/js/tabulator.min.js"></script>

<script>
    let table;

    // Când pagina se încarcă, desenăm doar "scheletul" tabelului (gol)
    document.addEventListener("DOMContentLoaded", function() {
        table = new Tabulator("#combinations-table", {
            data: [], // Începe gol
            layout: "fitData",      
            pagination: "local",
            paginationSize: 15,     
            movableColumns: true,
            placeholder: "Alegeți un interval de date și apăsați 'Adu Datele'", // Mesajul când e gol
            columns: [
                { title: "ID", field: "ID", headerFilter: "input", frozen: true },
                { title: "MachineID", field: "MachineID", headerFilter: "input" },
                { title: "ShiftID", field: "ShiftID", headerFilter: "input" },
                { title: "BoardGradeID", field: "BoardGradeID", headerFilter: "input" },
                { title: "ProgNumber", field: "ProgramNumber", headerFilter: "input" },
                { title: "RunNumber", field: "RunNumber", headerFilter: "input" },
                { title: "Status", field: "Status", headerFilter: "input" },
                { title: "SchedDate", field: "ScheduleDate", headerFilter: "input" },
                { title: "SchedMeters", field: "ScheduleMeters", headerFilter: "input" },
                { title: "Rollsize", field: "Rollsize", headerFilter: "input" },
                { title: "StartRun", field: "StartRun", headerFilter: "input" },
                { title: "EndRun", field: "EndRun", headerFilter: "input" },
                { title: "RunMeters", field: "RunMeters", headerFilter: "input" },
                { title: "SchedStatus", field: "ScheduleStatus", headerFilter: "input" },
                { title: "GivenSpeed", field: "GivenSpeed", headerFilter: "input" },
                { title: "IsConfirmed", field: "IsConfirmed", formatter: "tickCross", hozAlign: "center", headerFilter: "tickCross", headerFilterParams: { tristate: true } },
                { title: "EstimSpeed", field: "EstimSpeed", headerFilter: "input" },
                { title: "EstimTargetSpd", field: "EstimTargetSpeed", headerFilter: "input" },
                { title: "WasteArea", field: "WasteArea", headerFilter: "input" },
                { title: "NumScoreShafts", field: "NumScoreShafts", headerFilter: "input" },
                { title: "CombLinkID", field: "CombLinkID", headerFilter: "input" },
                { title: "CorrRunID", field: "CorrRunID", headerFilter: "input" },
                { title: "FCRParentID", field: "FCRParentID", headerFilter: "input" }
            ],
        });
    });

    // Funcția apelată când apăsăm butonul "Adu Datele"
    async function applyDateFilter() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        if (!startDate || !endDate) {
            alert("⚠️ Te rog selectează ambele date (De la și Până la)!");
            return;
        }

        const loading = document.getElementById('loading');
        loading.style.display = "inline-block"; // Arătăm mesajul de încărcare
        
        // Golește tabelul în timp ce așteptăm datele noi
        table.clearData(); 

        try {
            // Trimitem parametrii în URL către Golang
            const backendUrl = `http://${window.location.hostname}:8080/api/combinations?start_date=${startDate}&end_date=${endDate}`;
            const response = await fetch(backendUrl);
            const jsonResponse = await response.json();
            
            if (jsonResponse.status !== 'success') {
                throw new Error(jsonResponse.message);
            }

            // Inserăm datele proaspete în tabel
            table.setData(jsonResponse.data);
            
            if (jsonResponse.data.length === 0) {
                alert("Nu s-au găsit înregistrări pentru perioada selectată.");
            }
            
        } catch (error) {
            alert(`❌ Eroare la preluarea datelor: ${error.message}`);
        } finally {
            loading.style.display = "none"; // Ascundem mesajul
        }
    }
</script>

</body>
</html>

