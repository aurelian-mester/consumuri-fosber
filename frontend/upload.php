<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Upload Multiplu Fișiere Fosber</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 40px; }
        .container { max-width: 700px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        p.desc { text-align: center; color: #666; font-size: 14px; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #555; }
        select, input[type="file"] { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 16px; background-color: #fff; }
        button { width: 100%; background-color: #0078D4; color: white; border: none; padding: 14px; font-size: 18px; border-radius: 5px; cursor: pointer; margin-top: 10px; font-weight: bold; transition: background 0.3s;}
        button:hover { background-color: #005a9e; }
        
        #file-count { margin-top: 5px; font-size: 13px; color: #0078D4; font-weight: bold; display: block; }

        .progress-container { width: 100%; background-color: #e0e0e0; border-radius: 5px; margin-top: 20px; display: none; overflow: hidden; position: relative; height: 25px; }
        .progress-bar { height: 100%; background-color: #28a745; width: 0%; transition: width 0.3s ease; }
        .progress-text { position: absolute; top: 3px; left: 50%; transform: translateX(-50%); color: #000; font-weight: bold; font-size: 14px; }

        .console-container { background-color: #1e1e1e; color: #4af626; font-family: 'Courier New', Courier, monospace; font-size: 13px; padding: 15px; border-radius: 5px; margin-top: 20px; height: 200px; overflow-y: auto; display: none; box-shadow: inset 0 0 10px #000; }
        .console-line { margin: 3px 0; }
        
        .status-badge { text-align: center; font-weight: bold; margin-top: 15px; padding: 10px; border-radius: 5px; display: none; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <h2>📂 Încărcare Rapoarte Fosber</h2>
    <p class="desc">Selectează fișierele și urmărește consola pentru procesarea în timp real.</p>

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

        <button type="button" onclick="submitUpload()">Începe Încărcarea</button>
    </form>

    <div class="progress-container" id="progress-container">
        <div class="progress-bar" id="progress-bar"></div>
        <div class="progress-text" id="progress-text">0%</div>
    </div>

    <div class="console-container" id="console"></div>

    <div class="status-badge" id="status-message"></div>
</div>

<script>
    function updateFileCount() {
        const fileInput = document.getElementById('csv_files');
        const fileCountSpan = document.getElementById('file-count');
        const count = fileInput.files.length;
        if (count === 0) fileCountSpan.textContent = "Niciun fișier selectat.";
        else if (count === 1) fileCountSpan.textContent = "1 fișier selectat.";
        else fileCountSpan.textContent = count + " fișiere selectate.";
    }

    function addLogToConsole(text) {
        const consoleDiv = document.getElementById('console');
        const p = document.createElement('div');
        p.className = 'console-line';
        p.textContent = text;
        consoleDiv.appendChild(p);
        consoleDiv.scrollTop = consoleDiv.scrollHeight; 
    }

    function submitUpload() {
        const fileInput = document.getElementById('csv_files');
        const fileType = document.getElementById('file_type').value;
        const msgBox = document.getElementById('status-message');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const progressContainer = document.getElementById('progress-container');
        const consoleDiv = document.getElementById('console');

        if (fileInput.files.length === 0) {
            alert("Vă rugăm să selectați cel puțin un fișier CSV!");
            return;
        }

        msgBox.style.display = "none";
        consoleDiv.innerHTML = "";
        consoleDiv.style.display = "block";
        progressContainer.style.display = "block";
        progressBar.style.width = "0%";
        progressText.textContent = "0%";

        addLogToConsole("> Se preia comanda. Vă rugăm nu închideți pagina...");

        const formData = new FormData();
        formData.append("file_type", fileType);
        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append("csv_files", fileInput.files[i]);
        }

        const xhr = new XMLHttpRequest();
        const backendUrl = `http://${window.location.hostname}:8080/api/upload-csv`;

        xhr.upload.addEventListener("progress", function(evt) {
            if (evt.lengthComputable) {
                let percentComplete = Math.round((evt.loaded / evt.total) * 100);
                progressBar.style.width = percentComplete + "%";
                progressText.textContent = percentComplete + "%";
                
                if (percentComplete === 100) {
                    progressText.textContent = "Golang inserează datele...";
                    addLogToConsole("\n> Încărcare în rețea completă (100%).");
                    addLogToConsole("> ATENȚIE: Inserarea a 50+ fișiere (zeci de mii de rânduri) în baza de date poate dura de la 30 secunde până la câteva minute. Vă rugăm așteptați!");
                }
            }
        });

        xhr.addEventListener("load", function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === "success") {
                        response.logs.forEach(log => { addLogToConsole(log); });
                        msgBox.className = "status-badge success";
                        msgBox.innerHTML = "✅ " + response.message;
                        msgBox.style.display = "block";
                        fileInput.value = ""; 
                        updateFileCount();
                    } else {
                        throw new Error(response.message);
                    }
                } catch (e) {
                    addLogToConsole(`\n[EROARE SERVER] ${e.message}`);
                    msgBox.className = "status-badge error";
                    msgBox.innerHTML = "❌ Eroare: " + e.message;
                    msgBox.style.display = "block";
                }
            } else {
                addLogToConsole(`\n[EROARE HTTP] Cod ${xhr.status}`);
                msgBox.className = "status-badge error";
                msgBox.innerHTML = "❌ Eroare rețea sau backend oprit.";
                msgBox.style.display = "block";
            }
        });

        xhr.addEventListener("error", function() {
            addLogToConsole("\n[CRITICAL] Nu s-a putut conecta la serverul backend.");
            msgBox.className = "status-badge error";
            msgBox.innerHTML = "❌ Conexiune eșuată.";
            msgBox.style.display = "block";
        });

        xhr.open("POST", backendUrl);
        xhr.send(formData);
    }
</script>

</body>
</html>

