<?php
session_start();


$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "Tarefas";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    die("<div class='message error'>Você precisa estar logado para acessar esta página.</div>");
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = null;

$sectors = ['Financeiro','RH','TI','Comercial','Manutenção','Outros'];

$editing = false;
$task = null;


if (!empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();
    if ($task) $editing = true;
    else echo '<div class="message error">Tarefa não encontrada ou sem permissão.</div>';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $sector = trim($_POST['sector'] ?? '');
    $priority = $_POST['priority'] ?? 'baixa';
    $status = $_POST['status'] ?? 'a fazer';

    if ($description === '') $errors[] = "Descrição é obrigatória.";
    if ($sector === '') $errors[] = "Setor é obrigatório.";
    if (!in_array($priority, ['baixa','media','alta'])) $errors[] = "Prioridade inválida.";
    if (!in_array($status, ['a fazer','fazendo','pronto'])) $errors[] = "Status inválido.";

    if (empty($errors)) {
      
        if (!empty($_POST['task_id'])) {
            $id = (int)$_POST['task_id'];
            $stmt = $conn->prepare("UPDATE tasks SET description = ?, sector = ?, priority = ?, status = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssssii", $description, $sector, $priority, $status, $id, $user_id);
            $stmt->execute();
            $success = "Cadastro concluído com sucesso (tarefa atualizada).";
        } 
    
        else {
            $stmt = $conn->prepare("INSERT INTO tasks (user_id, description, sector, priority, status) VALUES (?,?,?,?,?)");
            $status = 'a fazer';
            $stmt->bind_param("issss", $user_id, $description, $sector, $priority, $status);
            $stmt->execute();
            $success = "Cadastro concluído com sucesso (tarefa criada).";
            $_POST = [];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title><?= $editing ? 'Editar Tarefa' : 'Cadastrar Tarefa' ?></title>
<link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<main>

<h2><?= $editing ? 'Editar Tarefa' : 'Cadastrar Tarefa' ?></h2>

<?php if ($success): ?>
<div class="message success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="message error">
    <?php foreach ($errors as $er) echo "<div>" . htmlspecialchars($er) . "</div>"; ?>
</div>
<?php endif; ?>

<form method="post" class="form" id="taskForm">
    <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['id'] ?? '') ?>">

    <label>Descrição *</label>
    <textarea name="description" id="description" rows="4" required><?= htmlspecialchars($_POST['description'] ?? $task['description'] ?? '') ?></textarea>

    <div class="small">
        Precisa de ajuda? 
        <button type="button" id="suggestBtn">Sugerir descrição (API)</button>
    </div>

    <label>Setor *</label>
    <input list="sectors" name="sector" value="<?= htmlspecialchars($_POST['sector'] ?? $task['sector'] ?? '') ?>" required>
    <datalist id="sectors">
        <?php foreach ($sectors as $s): ?><option value="<?= htmlspecialchars($s) ?>"><?php endforeach; ?>
    </datalist>

    <label>Prioridade *</label>
    <?php $sel = $_POST['priority'] ?? $task['priority'] ?? 'baixa'; ?>
    <select name="priority">
        <option value="baixa" <?= $sel==='baixa' ? 'selected' : '' ?>>Baixa</option>
        <option value="media" <?= $sel==='media' ? 'selected' : '' ?>>Média</option>
        <option value="alta" <?= $sel==='alta' ? 'selected' : '' ?>>Alta</option>
    </select>

    <?php if ($editing): ?>
    <label>Status</label>
    <?php $statusSel = $_POST['status'] ?? $task['status'] ?? 'a fazer'; ?>
    <select name="status">
        <option value="a fazer" <?= $statusSel==='a fazer' ? 'selected' : '' ?>>A Fazer</option>
        <option value="fazendo" <?= $statusSel==='fazendo' ? 'selected' : '' ?>>Fazendo</option>
        <option value="pronto" <?= $statusSel==='pronto' ? 'selected' : '' ?>>Pronto</option>
    </select>
    <?php endif; ?>

    <button type="submit"><?= $editing ? 'Salvar alterações' : 'Cadastrar tarefa' ?></button>
</form>

<script>
document.getElementById('suggestBtn').addEventListener('click', async function(){
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Buscando...';
    try {
        const res = await fetch('https://api.adviceslip.com/advice', {cache: "no-store"});
        const data = await res.json();
        if (data && data.slip && data.slip.advice) {
            const desc = document.getElementById('description');
            desc.value = (desc.value ? desc.value + "\n\n" : "") + data.slip.advice;
        } else {
            alert('Não foi possível obter sugestão da API.');
        }
    } catch (e) {
        alert('Erro ao acessar API: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Sugerir descrição (API)';
    }
});
</script>

</main>
</body>
</html>
