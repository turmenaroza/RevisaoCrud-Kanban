<?php

require_once 'config.php';


$currentUser = null;
if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kanban - Sistema</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<nav class="menu">
    <div class="brand"><a href="tasks_manage.php">Kanban</a></div>
    <div class="links">
        <?php if ($currentUser): ?>
            <span class="user-welcome">Olá, <?= e($currentUser['name']) ?></span>
            <a href="users_create.php">Cadastrar usuário</a>
            <a href="tasks_create.php">Cadastrar tarefa</a>
            <a href="tasks_manage.php">Gerenciar tarefas</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="users_create.php">Cadastrar usuário</a>
        <?php endif; ?>
    </div>
</nav>
<main class="container">
    