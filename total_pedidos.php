<?php

require_once __DIR__ . '/conexao.php';

json_response([
    'total' => (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn(),
]);

