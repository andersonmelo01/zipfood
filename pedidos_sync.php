<?php

require_once __DIR__ . '/conexao.php';

$pedidos = load_pedidos_sync();

if (empty($pedidos)) {
    $row = $pdo->query("SELECT COUNT(*) AS total, COALESCE(MAX(id), 0) AS ultimo_id FROM pedidos")->fetch(PDO::FETCH_ASSOC);
    json_response([
        'total' => (int) ($row['total'] ?? 0),
        'ultimo_id' => (int) ($row['ultimo_id'] ?? 0),
    ]);
}

$ultimoId = 0;
foreach ($pedidos as $pedido) {
    $ultimoId = max($ultimoId, (int) ($pedido['id'] ?? 0));
}

json_response([
    'total' => count($pedidos),
    'ultimo_id' => $ultimoId,
]);