<?php include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Invetich Fosber Hub</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 40px; text-align: center; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 900px; margin: auto; position: relative;}
        .logout { position: absolute; top: 20px; right: 20px; color: red; text-decoration: none; font-weight: bold; }
        h1 { color: #333; margin-bottom: 30px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn { display: block; background: #0078D4; color: white; text-decoration: none; padding: 20px; font-size: 16px; border-radius: 8px; font-weight: bold; transition: 0.3s; }
        .btn:hover { background: #005a9e; transform: translateY(-2px); }
        .btn-green { background: #28a745; } .btn-green:hover { background: #1e7e34; }
        .btn-red { background: #dc3545; } .btn-red:hover { background: #c82333; }
        .btn-purple { background: #6f42c1; } .btn-purple:hover { background: #59339d; }
    </style>
</head>
<body>
    <div class="box">
        <a href="logout.php" class="logout">🚪 Deconectare</a>
        <h1>🏭 Invetich - Fosber Hub</h1>
        <div class="grid">
            <a href="dashboard.php" class="btn btn-purple">📈 Dashboard Analytics<br><small>Grafice și Statistici</small></a>
            <a href="upload.php" class="btn btn-green">📤 Încărcare Rapoarte CSV<br><small>Past Rolls / Trace Rolls</small></a>
            
            <a href="past_rolls.php" class="btn">📜 Baza de Date: Past Rolls<br><small>Vizualizare & Export Excel</small></a>
            <a href="trace_rolls.php" class="btn">🔍 Baza de Date: Trace Rolls<br><small>Vizualizare & Export Excel</small></a>
            
            <a href="manage.php" class="btn btn-red">🗑️ Management Erori (Undo)<br><small>Șterge rapoarte încărcate greșit</small></a>
            <a href="combinations.php" class="btn" style="background:#6c757d">📊 Tabel: ss02.Combinations</a>
        </div>
    </div>
</body>
</html>

