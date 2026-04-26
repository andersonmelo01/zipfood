<?php

require_once __DIR__ . '/conexao.php';
require_module_access('pedidos');

json_response([
    'total' => (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn(),
]);
