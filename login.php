<?php
require_once 'config.php';
if (!empty($_SESSION['user_id'])) {
    header('Location: tasks_manage.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = "Preencha e-mail e senha.";
    } else {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password_hash'])) {
            
            $_SESSION['user_id'] = $user['id'];
            header('Location: tasks_manage.php');
            exit;
        } else {
            $errors[] = "E-mail ou senha inválidos.";
        }
    }
}

require_once 'header.php';
?>

<h2>Login</h2>

<?php if (!empty($errors)): ?>
    <div class="message error">
        <?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="form">
    <label>E-mail</label>
    <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>

    <label>Senha</label>
    <input type="password" name="password" required>

    <button type="submit">Entrar</button>
</form>

</main></body></html>
