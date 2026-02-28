<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Upload Multiplu Fișiere Fosber</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 40px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        p.desc { text-align: center; color: #666; font-size: 14px; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #555; }
        select, input[type="file"] { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 16px; background-color: #fff; }
        button { width: 100%; background-color: #0078D4; color: white; border: none; padding: 14px; font-size: 18px; border-radius: 5px; cursor: pointer; margin-top: 10px; font-weight: bold; transition: background 0.3s;}
        button:hover { background-color: #005a9e; }
        
        #file-count { margin-top: 5px; font-size: 13px; color: #0078D4; font-weight: bold; display: block; }

        #status-message { margin-top: 20px; padding: 15px; border-radius: 5px; text-align: center; font-weight: bold; display: none; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .loading { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    </style>
</head>
<body>

<div class="container">
    <h2>📂 Încărcare Rapoarte Fosber</h2>
    <p class="desc">Selectează unul sau mai multe fișiere CSV (folosind Ctrl sau Shift).</p>
    
    <div id="status-message"></div>

    <form id="uploadForm">
        <div class="form-group">
            <label>1. Alege Tipul Rapoartelor:</label>
            <select name="file_type" id="file_type" required>
                <option value="past">Past Rolls (Role Anterioare)</option>
                <option value="trace">Trace Rolls in Past Orders</option>
            </select>
        </div>

        <div class="form-group">
            <label>2. Selectează fișierele (.csv):</label>
            <input type="file" name="csv_files" id="csv_files" accept=".csv" multiple required onchange="updateFileCount()">
            <span id="file-count">Niciun fișier selectat.</span>
        </div>

        <button type="button" onclick="submitUpload()">Încărcare Fișiere</button>
    </form>
</div>

<script>
    // Functie mica pentru a arata utilizatorului cate fisiere a selectat in fereastra
    function updateFileCount() {
        const fileInput = document.getElementById('csv_files');
        const fileCountSpan = document.getElementById('file-count');
        const count = fileInput.files.length;
        
        if (count === 0) {
            fileCountSpan.textContent = "Niciun fișier selectat.";
        } else if (count === 1) {
            fileCountSpan.textContent = "1 fișier selectat.";
        } else {
            fileCountSpan.textContent = count + " fișiere selectate.";
        }
    }

    async function submitUpload() {
        const fileInput = document.getElementById('csv_files');
        const fileType = document.getElementById('file_type').value;
        const msgBox = document.getElementById('status-message');

        if (fileInput.files.length === 0) {
            alert("Vă rugăm să selectați cel puțin un fișier CSV!");
            return;
        }

        msgBox.className = "loading";
        msgBox.innerHTML = "⏳ Se procesează fișierele... Așteptați...";
        msgBox.style.display = "block";

        const formData = new FormData();
        formData.append("file_type", fileType);
        
        // Atasam TOATE fisierele selectate la FormData
        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append("csv_files", fileInput.files[i]);
        }

        try {
            const backendUrl = `http://${window.location.hostname}:8080/api/upload-csv`;
            const response = await fetch(backendUrl, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                msgBox.className = "success";
                msgBox.innerHTML = "✅ " + result.message;
                fileInput.value = ""; 
                updateFileCount(); // Resetam textul
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            msgBox.className = "error";
            msgBox.innerHTML = "❌ Eroare la încărcare: " + error.message;
        }
    }
</script>

</body>
</html>

