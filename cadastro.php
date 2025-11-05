<?php
require_once 'config.php';
ensure_logged_in();
require_once 'header.php';

$user_id = $_SESSION['user_id'];
$errors = [];
$success = null;


$sectors = ['Financeiro','RH','TI','Comercial','Manutenção','Outros'];


$editing = false;
$task = null;
if (!empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($task) $editing = true;
    else {
        echo '<div class="message error">Tarefa não encontrada ou sem permissão.</div>';
    }
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
           
            $stmt = $pdo->prepare("UPDATE tasks SET description = ?, sector = ?, priority = ?, status = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
            $stmt->execute([$description, $sector, $priority, $status, (int)$_POST['task_id'], $user_id]);
            $success = "Cadastro concluído com sucesso (tarefa atualizada).";
        } else {
          
            $stmt = $pdo->prepare("INSERT INTO tasks (user_id, description, sector, priority, status) VALUES (?,?,?,?,?)");
            $stmt->execute([$user_id, $description, $sector, $priority, 'a fazer']);
            $success = "Cadastro concluído com sucesso (tarefa criada).";
          
            $_POST = [];
        }
        
        if (!empty($_POST['task_id'])) {
            $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
            $stmt->execute([$_POST['task_id'], $user_id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}
?>

<h2><?= $editing ? 'Editar Tarefa' : 'Cadastrar Tarefa' ?></h2>

<?php if ($success): ?><div class="message success"><?= e($success) ?></div><?php endif; ?>
<?php if (!empty($errors)): ?><div class="message error"><?php foreach ($errors as $er) echo "<div>".e($er)."</div>"; ?></div><?php endif; ?>

<form method="post" class="form" id="taskForm">
    <input type="hidden" name="task_id" value="<?= e($task['id'] ?? '') ?>">

    <label>Descrição *</label>
    <textarea name="description" id="description" rows="4" required><?= e($_POST['description'] ?? $task['description'] ?? '') ?></textarea>

    <div class="small">Precisa de ajuda? <button type="button" id="suggestBtn">Sugerir descrição (API)</button></div>

    <label>Setor *</label>
    <input list="sectors" name="sector" value="<?= e($_POST['sector'] ?? $task['sector'] ?? '') ?>" required>
    <datalist id="sectors">
        <?php foreach ($sectors as $s): ?><option value="<?= e($s) ?>"><?php endforeach; ?>
    </datalist>

    <label>Prioridade *</label>
    <select name="priority">
        <?php $sel = $_POST['priority'] ?? $task['priority'] ?? 'baixa'; ?>
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

</main></body></html>
