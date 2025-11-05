<?php
require_once 'config.php';
ensure_logged_in();
require_once 'header.php';

$user_id = $_SESSION['user_id'];
$msg = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = $_POST['action'];
    $task_id = (int)($_POST['task_id'] ?? 0);

    if ($action === 'delete') {
       
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        $msg = "Tarefa excluída com sucesso.";
    } elseif ($action === 'change_status') {
        $newStatus = $_POST['new_status'] ?? '';
        if (!in_array($newStatus, ['a fazer','fazendo','pronto'])) {
            $msg = "Status inválido.";
        } else {
            $stmt = $pdo->prepare("UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
            $stmt->execute([$newStatus, $task_id, $user_id]);
            $msg = "Status atualizado.";
        }
    }
}


$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY 
  FIELD(status, 'a fazer','fazendo','pronto'), 
  FIELD(priority,'alta','media','baixa'), created_at DESC");
$stmt->execute([$user_id]);
$allTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);


$cols = ['a fazer' => [], 'fazendo' => [], 'pronto' => []];
foreach ($allTasks as $t) {
    $cols[$t['status']][] = $t;
}
?>

<h2>Gerenciar Tarefas</h2>

<?php if ($msg): ?><div class="message success"><?= e($msg) ?></div><?php endif; ?>

<div class="kanban">
    <?php foreach ($cols as $status => $tasks): ?>
        <div class="column">
            <h3><?= $status === 'a fazer' ? 'A Fazer' : ($status === 'fazendo' ? 'Fazendo' : 'Pronto') ?></h3>

            <?php if (empty($tasks)): ?><div class="small">Nenhuma tarefa.</div><?php endif; ?>

            <?php foreach ($tasks as $task): ?>
                <div class="card">
                    <div class="desc"><?= nl2br(e($task['description'])) ?></div>
                    <div class="meta">
                        <strong>Setor:</strong> <?= e($task['sector']) ?> |
                        <strong>Prioridade:</strong> <?= e($task['priority']) ?> |
                        <strong>Criado:</strong> <?= e($task['created_at']) ?>
                    </div>
                    <div class="actions">
                        <a href="tasks_create.php?id=<?= e($task['id']) ?>"><button class="btn-edit">Editar</button></a>

                        <form method="post" style="display:inline" onsubmit="return confirm('Confirma exclusão desta tarefa?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="task_id" value="<?= e($task['id']) ?>">
                            <button type="submit" class="btn-delete">Excluir</button>
                        </form>

                     
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="change_status">
                            <input type="hidden" name="task_id" value="<?= e($task['id']) ?>">
                            <?php if ($task['status'] !== 'a fazer'): ?>
                                <input type="hidden" name="new_status" value="a fazer">
                                <button class="btn-status" type="submit">Mover para A Fazer</button>
                            <?php endif; ?>
                        </form>

                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="change_status">
                            <input type="hidden" name="task_id" value="<?= e($task['id']) ?>">
                            <?php if ($task['status'] !== 'fazendo'): ?>
                                <input type="hidden" name="new_status" value="fazendo">
                                <button class="btn-status" type="submit">Mover para Fazendo</button>
                            <?php endif; ?>
                        </form>

                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="change_status">
                            <input type="hidden" name="task_id" value="<?= e($task['id']) ?>">
                            <?php if ($task['status'] !== 'pronto'): ?>
                                <input type="hidden" name="new_status" value="pronto">
                                <button class="btn-status" type="submit">Mover para Pronto</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    <?php endforeach; ?>
</div>

</main></body></html>
