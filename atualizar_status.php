<?php

require_once __DIR__ . '/conexao.php';
require_module_access('pedidos');

$id = (int) ($_POST['id'] ?? 0);
$status = trim((string) ($_POST['status'] ?? ''));

if ($id <= 0 || $status === '') {
    json_response(['ok' => false, 'message' => 'Dados inválidos.'], 422);
}

$allowed = ['novo', 'Preparando', 'Saiu', 'Entregue', 'Cancelado'];
if (!in_array($status, $allowed, true)) {
    json_response(['ok' => false, 'message' => 'Status inválido.'], 422);
}

$stmt = $pdo->prepare('UPDATE pedidos SET status = ? WHERE id = ?');
$stmt->execute([$status, $id]);

json_response(['ok' => true]);
