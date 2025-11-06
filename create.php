<?php
require_once 'db.php';
require_once 'header.php';

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validações básicas
    if ($nome === '') $errors[] = "Nome é obrigatório.";
    if ($email === '') $errors[] = "E-mail é obrigatório.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "E-mail inválido.";

    // Verifica duplicidade de e-mail
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "E-mail já cadastrado.";
        }
    }

    // Insere novo usuário
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email) VALUES (?, ?)");
        $stmt->execute([$nome, $email]);
        $success = "Cadastro concluído com sucesso!";
    }
}
?>

<h2>Cadastro de Usuário</h2>

<?php if ($success): ?>
    <div class="message success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="message error">
        <?php foreach ($errors as $er): ?>
            <div><?= htmlspecialchars($er) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="form">
    <label>Nome *</label>
    <input type="text" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>

    <label>E-mail *</label>
    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

    <button type="submit">Cadastrar</button>
</form>

</main></body></html>
