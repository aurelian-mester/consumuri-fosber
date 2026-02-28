<?php include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Management Date</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
        .box { background: white; padding: 30px; border-radius: 10px; max-width: 600px; margin: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        select, input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #dc3545; color: white; width: 100%; border: none; padding: 15px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; }
        a.btn-back { display: inline-block; background: #6c757d; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="box">
        <a href="index.php" class="btn-back">⬅️ Meniu</a>
        <h2 style="color: #dc3545;">🗑️ Ștergere / Undo Încărcări</h2>
        <p>Alege perioada și tipul de raport pe care vrei să îl anulezi din baza de date.</p>
        
        <label>Tip Raport:</label>
        <select id="type">
            <option value="past">Past Rolls</option>
            <option value="trace">Trace Rolls</option>
        </select>
        
        <label>De la data:</label>
        <input type="date" id="start">
        
        <label>Până la data:</label>
        <input type="date" id="end">
        
        <button onclick="deleteData()">⚠️ ȘTERGE DATELE DEFINITIV</button>
    </div>

    <script>
        async function deleteData() {
            const type = document.getElementById('type').value;
            const start = document.getElementById('start').value;
            const end = document.getElementById('end').value;

            if(!start || !end) return alert("Selectează datele!");
            if(!confirm(`Ești SIGUR că vrei să ștergi raportul ${type} din perioada selectată?`)) return;

            const res = await fetch(`http://${window.location.hostname}:8080/api/delete-records`, {
                method: 'POST',
                body: JSON.stringify({type: type, start_date: start, end_date: end})
            });
            const data = await res.json();
            if(data.status === 'success') {
                alert(`Succes! S-au șters ${data.deleted} rânduri din baza de date.`);
            } else {
                alert("Eroare la ștergere!");
            }
        }
    </script>
</body>
</html>

