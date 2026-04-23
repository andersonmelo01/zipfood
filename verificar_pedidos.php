<?php

require_once __DIR__ . '/conexao.php';

$row = $pdo->query("SELECT COUNT(*) AS total, COALESCE(MAX(id), 0) AS ultimo_id FROM pedidos")->fetch(PDO::FETCH_ASSOC);

json_response([
    'total' => (int) ($row['total'] ?? 0),
    'ultimo_id' => (int) ($row['ultimo_id'] ?? 0),
]);
