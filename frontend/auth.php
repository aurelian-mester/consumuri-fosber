<?php
session_start();
// Daca utilizatorul nu este logat, il trimitem la pagina de login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>

