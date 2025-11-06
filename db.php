<?php

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "Tarefas";
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erro DB: " . $e->getMessage());
}


function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}


function ensure_logged_in() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}
?>