<?php
// URL-ul backend-ului nostru Golang
$apiUrl = "http://127.0.0.1:8080/api/test-db";

// Facem request către Go
$response = @file_get_contents($apiUrl);

$data = null;
if ($response !== false) {
    $data = json_decode($response, true);
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Consumuri Fosber</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f2f5; text-align: center; padding-top: 50px; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: inline-block; min-width: 400px; }
        .success { color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; border: 1px solid #c3e6cb; }
        .error { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; border: 1px solid #f5c6cb; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🛠️ Dashboard Consumuri Fosber</h1>
        <p>Frontend: <strong>PHP</strong> | Backend: <strong>Golang</strong></p>
        <hr>
        
        <?php if ($data && $data['status'] === 'success'): ?>
            <div class="success">
                <p>✅ <strong>Conexiune reușită!</strong></p>
                <p><?php echo htmlspecialchars($data['message']); ?></p>
                <p><small>Ora serverului DB: <?php echo htmlspecialchars($data['db_time']); ?></small></p>
            </div>
        <?php else: ?>
            <div class="error">
                <p>❌ <strong>Eroare de comunicare!</strong></p>
                <p>Backend-ul de Golang nu răspunde sau nu se poate conecta la PostgreSQL.</p>
                <?php if ($data && isset($data['details'])): ?>
                    <p><small><?php echo htmlspecialchars($data['details']); ?></small></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
