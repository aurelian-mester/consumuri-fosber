<?php
session_start();
$eroare = "";

// Parola setata hardcodat pentru simplitate (o poti schimba)
$parola_corecta = "Fosber2026!"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['parola'] === $parola_corecta) {
        $_SESSION['logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $eroare = "Parolă incorectă!";
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Login - Invetich</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; width: 300px; }
        input { width: 100%; padding: 10px; margin: 15px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; background: #0078D4; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .err { color: red; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔒 Autentificare</h2>
        <?php if($eroare) echo "<p class='err'>$eroare</p>"; ?>
        <form method="POST">
            <input type="password" name="parola" placeholder="Introduceți parola" required>
            <button type="submit">Intră în Hub</button>
        </form>
    </div>
</body>
</html>

