<?php
require_once 'config.php';
require_once 'header.php';

$errors = [];
$success = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    
    if ($name === '') $errors[] = "Nome é obrigatório.";
    if ($email === '') $errors[] = "E-mail é obrigatório.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "E-mail inválido.";
    if ($password === '') $errors[] = "Senha é obrigatória.";
    if ($password !== $password_confirm) $errors[] = "Senhas não conferem.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "E-mail já cadastrado.";
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name,email,password_hash) VALUES (?,?,?)");
        $stmt->execute([$name, $email, $hash]);
        $success = "Cadastro concluído com sucesso";
     
    }
}
?>

<h2>Cadastro de Usuário</h2>

<?php if ($success): ?>
    <div class="message success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="message error">
        <?php foreach ($errors as $er): ?>
            <div><?= e($er) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="form">
    <label>Nome *</label>
    <input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" required>

    <label>E-mail *</label>
    <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>

    <label>Senha *</label>
    <input type="password" name="password" required>

    <label>Confirmar senha *</label>
    <input type="password" name="password_confirm" required>

    <button type="submit">Cadastrar</button>
</form>

</main></body></html>
