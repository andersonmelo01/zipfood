<?php

require_once __DIR__ . '/conexao.php';
require_admin();


// Busca feedbacks do banco
$feedbacks = [];
$stmt = $pdo->query("SELECT * FROM feedbacks ORDER BY data DESC");
while ($f = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $feedbacks[] = $f;
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feedbacks dos Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">⭐ Feedbacks dos Clientes</h2>
                <div class="text-muted">Visão consolidada das avaliações publicadas.</div>
            </div>
            <a href="dashboard.php" class="btn btn-dark">⬅ Voltar ao Dashboard</a>
        </div>

        <?php
        // Filtro de status
        $status = $_GET['status'] ?? 'pendente';
        $statusOptions = [
            'pendente' => 'Pendentes',
            'aprovado' => 'Aprovados',
            'sugestao' => 'Sugestões',
            'todos' => 'Todos'
        ];
        $filtrados = [];
        foreach ($feedbacks as $f) {
            if ($status === 'pendente' && (empty($f['aprovado']) && empty($f['sugestao']))) {
                $filtrados[] = $f;
            } elseif ($status === 'aprovado' && !empty($f['aprovado'])) {
                $filtrados[] = $f;
            } elseif ($status === 'sugestao' && !empty($f['sugestao'])) {
                $filtrados[] = $f;
            } elseif ($status === 'todos') {
                $filtrados[] = $f;
            }
        }
        ?>
        <form method="get" class="mb-4 d-flex gap-2 align-items-center">
            <label class="fw-bold text-muted">Filtrar:</label>
            <?php foreach ($statusOptions as $key => $label): ?>
                <button type="submit" name="status" value="<?= $key ?>" class="btn btn-sm <?= $status === $key ? 'btn-primary' : 'btn-outline-primary' ?>"> <?= $label ?> </button>
            <?php endforeach; ?>
        </form>
        <?php if (empty($filtrados)): ?>
            <div class="alert alert-info text-center soft-panel border-0">Nenhum feedback encontrado para este filtro.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($filtrados as $f): ?>
                    <?php
                    $nome = (string) ($f['nome'] ?? 'Cliente');
                    $nota = isset($f['nota']) ? (int) $f['nota'] : (isset($f['estrelas']) ? (int)$f['estrelas'] : 0);
                    $comentario = (string) ($f['comentario'] ?? ($f['mensagem'] ?? 'Sem comentário'));
                    $data = (string) ($f['data'] ?? '');
                    $aprovado = !empty($f['aprovado']);
                    $sugestao = !empty($f['sugestao']);
                    $id = $f['id'] ?? '';
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="soft-panel p-4 h-100 border shadow-sm <?php if($aprovado) echo 'border-success'; elseif($sugestao) echo 'border-warning'; else echo 'border-secondary'; ?>">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold" style="font-family:'Playfair Display',serif; color:#ff6b00;"><?= e($nome) ?></span>
                                <span class="badge bg-success"><?= $nota > 0 ? str_repeat('⭐', min($nota, 5)) : 'Sem nota' ?></span>
                            </div>
                            <div class="small text-muted mb-2">📅 <?= e($data) ?></div>
                            <div class="fst-italic text-secondary mb-3">“<?= e($comentario) ?>”</div>
                            <?php if ($aprovado): ?>
                                <div class="alert alert-success py-1 px-2 mb-2">Aprovado e visível para clientes</div>
                            <?php elseif ($sugestao): ?>
                                <div class="alert alert-warning py-1 px-2 mb-2">Marcado como sugestão interna</div>
                            <?php else: ?>
                                <div class="alert alert-secondary py-1 px-2 mb-2">Aguardando ação do administrador</div>
                            <?php endif; ?>
                            <div class="d-flex gap-2">
                                <?php if (!$aprovado && !$sugestao): ?>
                                    <button class="btn btn-sm btn-success flex-fill" onclick="aprovarFeedback('<?= $id ?>')">✔ Aceitar</button>
                                    <button class="btn btn-sm btn-warning flex-fill" onclick="sugestaoFeedback('<?= $id ?>')">💡 Sugestão</button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary flex-fill" disabled>✔ Aceito</button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-danger flex-fill" onclick="excluirFeedback('<?= $id ?>')">🗑 Excluir</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        async function excluirFeedback(id) {
            if (!confirm('Deseja excluir este feedback?')) return;
            await fetch('excluir_feedback.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            location.reload();
        }
        async function aprovarFeedback(id) {
            if (!confirm('Aceitar este feedback e exibir para os clientes?')) return;
            await fetch('feedbacks_aceitar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            location.reload();
        }
        async function sugestaoFeedback(id) {
            if (!confirm('Marcar este feedback como sugestão interna?')) return;
            await fetch('feedbacks_sugestao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            location.reload();
        }
    </script>
</body>

</html>
