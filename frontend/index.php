<?php
$mesaj = "";
$tipMesaj = "";

// Dacă formularul a fost trimis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Pregătim datele pentru a fi trimise la Go
    $dateConsum = [
        "schimb" => (int)$_POST['schimb'],
        "tip_hartie" => $_POST['tip_hartie'],
        "latime_rola" => (int)$_POST['latime_rola'],
        "numar_rola" => $_POST['numar_rola'],
        "greutate_kg" => (float)$_POST['greutate_kg'],
        "metri_liniari" => (int)$_POST['metri_liniari'],
        "operator" => $_POST['operator']
    ];

    $payload = json_encode($dateConsum);

    // 2. Setăm opțiunile pentru cererea cURL (POST) către Golang
    $ch = curl_init('http://127.0.0.1:8080/api/add-consum');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);

    // 3. Executăm cererea și primim răspunsul
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 4. Afișăm rezultatul
    if ($httpCode == 200) {
        $mesaj = "✅ Rolă înregistrată cu succes!";
        $tipMesaj = "success";
    } else {
        $mesaj = "❌ Eroare la salvare. Verificați conexiunea.";
        $tipMesaj = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Introducere Consum Fosber</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 40px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; background-color: #0078D4; color: white; border: none; padding: 12px; font-size: 18px; border-radius: 5px; cursor: pointer; margin-top: 10px; }
        button:hover { background-color: #005a9e; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center; font-weight: bold; }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <h2>🏭 Adăugare Consum Fosber</h2>
    
    <?php if ($mesaj != ""): ?>
        <div class="alert <?php echo $tipMesaj; ?>">
            <?php echo $mesaj; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <div class="form-group">
            <label>Schimb / Tura</label>
            <select name="schimb" required>
                <option value="1">Schimbul 1</option>
                <option value="2">Schimbul 2</option>
                <option value="3">Schimbul 3</option>
            </select>
        </div>

        <div class="form-group">
            <label>Tip Hârtie</label>
            <input type="text" name="tip_hartie" placeholder="ex: Testliner" required>
        </div>

        <div class="form-group">
            <label>Lățime Rolă (mm)</label>
            <input type="number" name="latime_rola" placeholder="ex: 2100" required>
        </div>

        <div class="form-group">
            <label>Număr Rolă / Lot</label>
            <input type="text" name="numar_rola" placeholder="Scanați sau introduceți manual" required>
        </div>

        <div class="form-group">
            <label>Greutate (Kg)</label>
            <input type="number" step="0.01" name="greutate_kg" placeholder="ex: 1550.50" required>
        </div>

        <div class="form-group">
            <label>Metri Liniari</label>
            <input type="number" name="metri_liniari" placeholder="ex: 4500" required>
        </div>

        <div class="form-group">
            <label>Nume Operator</label>
            <input type="text" name="operator" placeholder="Numele dvs." required>
        </div>

        <button type="submit">💾 Salvează Consumul</button>
    </form>
</div>

</body>
</html>

