<?php
session_start();


$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "Tarefas";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}


if (!empty($_SESSION['usuario_id'])) {
    header('Location: tasks_manage.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $errors[] = "Por favor, preencha o e-mail.";
    } else {
       
        $stmt = $conn->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];

            header('Location: tasks_manage.php');
            exit;
        } else {
            $errors[] = "E-mail não encontrado. Verifique ou cadastre-se.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema de Tarefas</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<main>

<h2>Login</h2>

<?php if (!empty($errors)): ?>
    <div class="message error">
        <?php foreach ($errors as $er): ?>
            <div><?= htmlspecialchars($er) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="form">
    <label for="email">E-mail:</label>
    <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

    <button type="submit">Entrar</button>
</form>

<p>Não tem cadastro? <a href="register.php">Crie uma conta</a>.</p>

</main>
</body>
</html>
