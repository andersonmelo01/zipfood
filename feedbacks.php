<?php

require_once __DIR__ . '/conexao.php';
require_admin();

$arquivo = __DIR__ . '/feedbacks.json';
$feedbacks = file_exists($arquivo) ? json_decode((string) file_get_contents($arquivo), true) : [];
$feedbacks = is_array($feedbacks) ? array_reverse($feedbacks) : [];

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

        <?php if (empty($feedbacks)): ?>
            <div class="alert alert-info text-center soft-panel border-0">Nenhum feedback recebido ainda.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($feedbacks as $i => $f): ?>
                    <?php
                    $nome = (string) ($f['nome'] ?? 'Cliente');
                    $nota = isset($f['nota']) ? (int) $f['nota'] : 0;
                    $comentario = (string) ($f['comentario'] ?? ($f['mensagem'] ?? 'Sem comentário'));
                    $data = (string) ($f['data'] ?? '');
                    ?>
                    <div class="col-md-4">
                        <div class="card-soft p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong><?= e($nome) ?></strong>
                                <span class="badge bg-success"><?= $nota > 0 ? str_repeat('⭐', min($nota, 5)) : 'Sem nota' ?></span>
                            </div>

                            <div class="small text-muted mb-2">📅 <?= e($data) ?></div>
                            <div class="fst-italic text-secondary mb-3">“<?= e($comentario) ?>”</div>

                            <button class="btn btn-sm btn-danger w-100" onclick="excluirFeedback(<?= (int) $i ?>)">
                                🗑 Excluir Feedback
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        async function excluirFeedback(index) {
            if (!confirm('Deseja excluir este feedback?')) return;

            await fetch('excluir_feedback.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ index })
            });

            location.reload();
        }
    </script>
</body>

</html>
