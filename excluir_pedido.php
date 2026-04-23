<?php

require_once __DIR__ . '/conexao.php';
require_admin();

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    exit('ID não informado');
}

try {
    $pdo->beginTransaction();

    $pdo->prepare('DELETE FROM pedido_itens WHERE pedido_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM pedidos WHERE id = ?')->execute([$id]);

    $pdo->commit();

    echo 'OK';
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo 'Erro: ' . $e->getMessage();
}

