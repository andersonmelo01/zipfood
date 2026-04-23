<?php

require_once __DIR__ . '/conexao.php';
require_admin();

$dataInicio = (string) ($_GET['data_inicio'] ?? date('Y-m-d'));
$dataFim = (string) ($_GET['data_fim'] ?? date('Y-m-d'));

$stmt = $pdo->prepare("
    SELECT p.id, p.nome, p.telefone, p.pagamento, p.total, p.status, p.data_pedido
    FROM pedidos p
    WHERE p.data_pedido BETWEEN ? AND ?
      AND p.status = 'Entregue'
    ORDER BY p.data_pedido DESC
");
$stmt->execute([
    $dataInicio . ' 00:00:00',
    $dataFim . ' 23:59:59',
]);

$pedidosFiltrados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$faturamentoTotal = array_sum(array_map(static fn ($p) => (float) $p['total'], $pedidosFiltrados));
$pedidosPorId = array_column($pedidosFiltrados, 'id');

$faturamentoDiario = [];
$pagamentos = [];

$periodo = new DatePeriod(
    new DateTime($dataInicio),
    new DateInterval('P1D'),
    (new DateTime($dataFim))->modify('+1 day')
);

foreach ($periodo as $d) {
    $faturamentoDiario[$d->format('Y-m-d')] = 0;
}

if ($pedidosFiltrados) {
    $stmtItens = $pdo->prepare('SELECT pedido_id, produto_nome, preco, quantidade, subtotal FROM pedido_itens WHERE pedido_id IN (' . implode(',', array_fill(0, count($pedidosPorId), '?')) . ')');
    $stmtItens->execute($pedidosPorId);
    $itensPorPedido = [];

    foreach ($stmtItens->fetchAll(PDO::FETCH_ASSOC) as $item) {
        $itensPorPedido[(int) $item['pedido_id']][] = $item;
    }

    foreach ($pedidosFiltrados as &$pedido) {
        $pedido['itens'] = $itensPorPedido[(int) $pedido['id']] ?? [];

        $dataPedido = (new DateTime($pedido['data_pedido']))->format('Y-m-d');
        $faturamentoDiario[$dataPedido] = ($faturamentoDiario[$dataPedido] ?? 0) + (float) $pedido['total'];

        $pagamento = (string) ($pedido['pagamento'] ?: 'Não informado');
        $pagamentos[$pagamento] = ($pagamentos[$pagamento] ?? 0) + (float) $pedido['total'];
    }
    unset($pedido);
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Financeiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="assets/css/app.css" rel="stylesheet">
</head>

<body>
    <div class="container py-4">
        <div class="app-hero p-4 p-lg-5 mb-4">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <div class="brand-mark mb-3">F</div>
                    <h2 class="fw-bold mb-1">Financeiro</h2>
                    <div class="opacity-75">Visão consolidada de vendas e recebimentos.</div>
                </div>
                <a href="dashboard.php" class="btn btn-outline-light">← Voltar ao Dashboard</a>
            </div>
        </div>

        <form method="GET" class="row g-3 mb-4 soft-panel p-3">
            <div class="col-md-4">
                <label class="form-label">Data Inicial</label>
                <input type="date" name="data_inicio" value="<?= e($dataInicio) ?>" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Data Final</label>
                <input type="date" name="data_fim" value="<?= e($dataFim) ?>" class="form-control">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button class="btn btn-brand w-100">Filtrar</button>
                <a href="financeiro.php" class="btn btn-outline-secondary w-100">Hoje</a>
            </div>
        </form>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card-soft p-4">
                    <div class="text-muted">Faturamento total</div>
                    <div class="h2 fw-bold text-success mb-0">R$ <?= money($faturamentoTotal) ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-soft p-4">
                    <div class="text-muted">Pedidos entregues</div>
                    <div class="h2 fw-bold mb-0"><?= count($pedidosFiltrados) ?></div>
                </div>
            </div>
        </div>

        <div class="card-soft p-4 mb-4">
            <h5 class="fw-bold">Faturamento diário</h5>
            <div style="height:220px;">
                <canvas id="graficoFaturamento"></canvas>
            </div>
        </div>

        <div class="card-soft p-4 mb-4">
            <h5 class="fw-bold">Distribuição por forma de pagamento</h5>
            <div style="height:220px;">
                <canvas id="graficoPagamento"></canvas>
            </div>
        </div>

        <h4 class="mb-3">Pedidos entregues</h4>
        <?php if (empty($pedidosFiltrados)): ?>
            <div class="alert alert-info soft-panel border-0">Nenhum pedido entregue neste período.</div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($pedidosFiltrados as $p): ?>
                    <div class="col-md-4">
                        <div class="card-soft p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0">Pedido #<?= (int) $p['id'] ?></h5>
                                <span class="badge bg-success"><?= e($p['status']) ?></span>
                            </div>
                            <p><strong>Cliente:</strong> <?= e($p['nome']) ?></p>
                            <p><strong>Telefone:</strong> <?= e($p['telefone']) ?></p>
                            <p><strong>Pagamento:</strong> <?= e($p['pagamento']) ?></p>
                            <ul class="list-unstyled mb-2">
                                <?php foreach ($p['itens'] as $item): ?>
                                    <li>• <?= e($item['produto_nome']) ?> - R$ <?= money($item['preco']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <h6 class="text-success">Total: R$ <?= money($p['total']) ?></h6>
                            <small class="text-muted"><?= e($p['data_pedido']) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        new Chart(document.getElementById('graficoFaturamento'), {
            type: 'line',
            data: {
                labels: <?= json_encode(array_keys($faturamentoDiario)) ?>,
                datasets: [{
                    label: 'Faturamento (R$)',
                    data: <?= json_encode(array_values($faturamentoDiario)) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.15)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        new Chart(document.getElementById('graficoPagamento'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($pagamentos)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($pagamentos)) ?>,
                    backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>

</html>
