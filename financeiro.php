<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/emitente.php';
require_module_access('financeiro');


$dataInicio = (string) ($_GET['data_inicio'] ?? date('Y-m-d'));
$dataFim = (string) ($_GET['data_fim'] ?? date('Y-m-d'));
$filtroPagamento = isset($_GET['pagamento']) ? $_GET['pagamento'] : '';
$filtroCancelado = isset($_GET['cancelado']) ? $_GET['cancelado'] : '';


$sql = "SELECT p.id, p.nome, p.telefone, p.pagamento, p.total, p.status, p.data_pedido
        FROM pedidos p
        WHERE p.data_pedido BETWEEN ? AND ?";
$params = [$dataInicio . ' 00:00:00', $dataFim . ' 23:59:59'];
if ($filtroCancelado === '1') {
    $sql .= " AND p.status = 'Cancelado'";
} else {
    $sql .= " AND p.status = 'Entregue'";
}
if ($filtroPagamento !== '') {
    $sql .= " AND p.pagamento = ?";
    $params[] = $filtroPagamento;
}
$sql .= " ORDER BY p.data_pedido DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

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

        <div class="mb-4">
            <div class="alert alert-success" style="font-size:1.2em;">
                <strong>Total do faturamento filtrado:</strong> R$ <?= money($faturamentoTotal) ?>
            </div>
        </div>
        <form method="GET" class="row g-3 mb-4 soft-panel p-3">
            <div class="col-md-3">
                <label class="form-label">Data Inicial</label>
                <input type="date" name="data_inicio" value="<?= e($dataInicio) ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Data Final</label>
                <input type="date" name="data_fim" value="<?= e($dataFim) ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Pagamento</label>
                <select name="pagamento" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach (array_keys($pagamentos) as $pg): ?>
                        <option value="<?= e($pg) ?>" <?= ($filtroPagamento === $pg) ? 'selected' : '' ?>><?= e($pg) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="cancelado" value="1" id="canceladoCheck" <?= $filtroCancelado === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="canceladoCheck">Exibir apenas cancelados</label>
                </div>
            </div>
            <div class="col-md-12 d-flex align-items-end gap-2">
                <button class="btn btn-brand">Filtrar</button>
                <a href="financeiro.php" class="btn btn-outline-secondary">Limpar</a>
                <button type="button" class="btn btn-secondary" onclick="window.print()">Imprimir</button>
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

        <div class="d-print-block d-none" style="margin-bottom:24px;">
            <?php $emitente = ler_emitente(); ?>
            <div class="text-center">
                <h3 style="margin-bottom:2px; font-weight:bold;">Relatório Financeiro</h3>
                <?php if (!empty($emitente['nome'])): ?><div><?= htmlspecialchars($emitente['nome']) ?></div><?php endif; ?>
                <?php if (!empty($emitente['cnpj'])): ?><div>CNPJ/CPF: <?= htmlspecialchars($emitente['cnpj']) ?></div><?php endif; ?>
                <?php if (!empty($emitente['endereco'])): ?><div><?= htmlspecialchars($emitente['endereco']) ?></div><?php endif; ?>
                <?php if (!empty($emitente['telefone'])): ?><div>Fone: <?= htmlspecialchars($emitente['telefone']) ?></div><?php endif; ?>
                <?php if (!empty($emitente['email'])): ?><div><?= htmlspecialchars($emitente['email']) ?></div><?php endif; ?>
                <div>Período: <?= e($dataInicio) ?> a <?= e($dataFim) ?></div>
            </div>
            <hr>
        </div>

        <h4 class="mb-3">Pedidos entregues</h4>
        <?php if (empty($pedidosFiltrados)): ?>
            <div class="alert alert-info soft-panel border-0">Nenhum pedido entregue neste período.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" style="background:#fff;">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>Telefone</th>
                            <th>Pagamento</th>
                            <th>Itens</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidosFiltrados as $p): ?>
                            <tr>
                                <td><?= (int) $p['id'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($p['data_pedido'])) ?></td>
                                <td><?= e($p['nome']) ?></td>
                                <td><?= e($p['telefone']) ?></td>
                                <td><?= e($p['pagamento']) ?></td>
                                <td>
                                    <?php foreach ($p['itens'] as $item): ?>
                                        <?= e($item['produto_nome']) ?> x<?= (int)$item['quantidade'] ?><br>
                                    <?php endforeach; ?>
                                </td>
                                <td>R$ <?= money($p['total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { display: !window.matchMedia('print').matches }, y: { display: !window.matchMedia('print').matches } }
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
                maintainAspectRatio: false,
                plugins: { legend: { display: !window.matchMedia('print').matches } }
            }
        });
    </script>
    <style>
        @media print {
            .btn, .soft-panel, .app-hero, nav, .row.g-3.mb-4, .card-soft.p-4.mb-4, .chartjs-render-monitor, .form-label, .form-control, .d-print-none, .d-none:not(.d-print-block) { display: none !important; }
            .d-print-block { display: block !important; }
            .table { font-size: 12px; }
        }
    </style>
</body>

</html>
