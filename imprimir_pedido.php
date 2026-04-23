<?php
// Gera impressão simples do pedido para o entregador
require_once __DIR__ . '/conexao.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('Pedido inválido.');
}

$stmt = $pdo->prepare('SELECT * FROM pedidos WHERE id = ?');
$stmt->execute([$id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$pedido) {
    die('Pedido não encontrado.');
}

$stmtItens = $pdo->prepare('SELECT produto_nome AS nome, preco, quantidade FROM pedido_itens WHERE pedido_id = ?');
$stmtItens->execute([$id]);
$itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Imprimir Pedido #<?= $pedido['id'] ?></title>
    <style>
        body {
            background: #fff;
            color: #000;
            font-family: 'monospace', 'Courier New', Courier, Arial, sans-serif;
            font-size: 13px;
        }
        .print-area {
            width: 260px;
            max-width: 100vw;
            margin: 0 auto;
            padding: 0;
        }
        .print-title {
            font-size: 1.1em;
            font-weight: bold;
            text-align: center;
            margin-bottom: 6px;
            margin-top: 2px;
        }
        .print-label {
            font-weight: bold;
        }
        .print-items {
            margin-bottom: 8px;
            padding-left: 0;
            list-style: none;
        }
        .print-items li {
            margin-bottom: 1px;
            display: flex;
            justify-content: space-between;
        }
        .print-total {
            font-size: 1.1em;
            font-weight: bold;
            text-align: right;
            margin-top: 6px;
            margin-bottom: 6px;
        }
        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .linha {
            border-top: 1px dashed #000;
            margin: 4px 0;
        }
        .center { text-align: center; }
        .no-print { display: block; margin-top: 10px; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-area">
        <div class="print-title">PEDIDO #<?= $pedido['id'] ?></div>
        <div class="center">Data: <?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></div>
        <hr>
        <div><span class="print-label">Cliente:</span> <?= htmlspecialchars($pedido['nome']) ?></div>
        <div><span class="print-label">Fone:</span> <?= htmlspecialchars($pedido['telefone']) ?></div>
        <div><span class="print-label">End:</span> <?= htmlspecialchars($pedido['endereco']) ?></div>
        <?php if (!empty($pedido['referencia'])): ?>
        <div><span class="print-label">Ref:</span> <?= htmlspecialchars($pedido['referencia']) ?></div>
        <?php endif; ?>
        <div><span class="print-label">Pagamento:</span> <?= htmlspecialchars($pedido['pagamento']) ?></div>
        <div><span class="print-label">Status:</span> <?= htmlspecialchars($pedido['status']) ?></div>
        <?php if (!empty($pedido['observacao'])): ?>
        <div><span class="print-label">Obs:</span> <?= htmlspecialchars($pedido['observacao']) ?></div>
        <?php endif; ?>
        <div class="linha"></div>
        <div class="print-label center">ITENS</div>
        <ul class="print-items">
            <?php foreach ($itens as $item): ?>
                <li><span><?= htmlspecialchars($item['nome']) ?> x<?= $item['quantidade'] ?></span><span>R$ <?= number_format($item['preco'], 2, ',', '.') ?></span></li>
            <?php endforeach; ?>
        </ul>
        <div class="print-total">Total: R$ <?= number_format($pedido['total'], 2, ',', '.') ?></div>
        <div class="linha"></div>
        <div class="center">Obrigado por escolher a ZipFood!</div>
        <button class="no-print" onclick="window.close()">Fechar</button>
    </div>
</body>
</html>
