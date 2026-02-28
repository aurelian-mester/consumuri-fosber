<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Invetich Fosber Hub</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 50px; text-align: center; }
        .box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 800px; margin: auto; }
        h1 { color: #333; margin-bottom: 30px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn { display: block; background-color: #0078D4; color: white; text-decoration: none; padding: 20px; font-size: 18px; border-radius: 8px; font-weight: bold; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .btn:hover { background-color: #005a9e; transform: translateY(-2px); }
        .btn-green { background-color: #28a745; }
        .btn-green:hover { background-color: #1e7e34; }
        .btn-full { grid-column: span 2; background-color: #6c757d; }
        .btn-full:hover { background-color: #5a6268; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🏭 Invetich - Fosber Dashboard</h1>
        <div class="grid">
            <a href="upload.php" class="btn btn-green">📤 Încărcare Rapoarte CSV<br><small>(Past Rolls / Trace Rolls)</small></a>
            <a href="manual_consum.php" class="btn">✍️ Adăugare Consum Manual<br><small>(Operator)</small></a>
            
            <a href="past_rolls.php" class="btn">📜 Tabel: Past Rolls</a>
            <a href="trace_rolls.php" class="btn">🔍 Tabel: Trace Rolls</a>
            
            <a href="combinations.php" class="btn btn-full">📊 Tabel: ss02.Combinations</a>
        </div>
    </div>
</body>
</html>

