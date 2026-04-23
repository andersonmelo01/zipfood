<?php

require_once __DIR__ . '/conexao.php';
require_admin();

$itens = $_POST['itens'] ?? [];

if (!is_array($itens) || empty($itens)) {
    exit('Nenhum item informado.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO pedidos
        (nome, telefone, endereco, referencia, pagamento, total, observacao, status, data_pedido)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'novo', NOW())
    ");

    $stmt->execute([
        trim((string) ($_POST['nome'] ?? '')),
        trim((string) ($_POST['telefone'] ?? '')),
        trim((string) ($_POST['endereco'] ?? '')),
        trim((string) ($_POST['referencia'] ?? '')),
        trim((string) ($_POST['pagamento'] ?? '')),
        (float) ($_POST['total'] ?? 0),
        trim((string) ($_POST['observacao'] ?? '')),
    ]);

    $pedidoId = (int) $pdo->lastInsertId();

    $stmtItem = $pdo->prepare("
        INSERT INTO pedido_itens
        (pedido_id, produto_nome, preco, quantidade, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($itens as $item) {
        $nome = trim((string) ($item['nome'] ?? ''));
        $preco = (float) ($item['preco'] ?? 0);
        $quantidade = (int) ($item['quantidade'] ?? 0);

        if ($nome === '' || $quantidade <= 0) {
            continue;
        }

        $stmtItem->execute([
            $pedidoId,
            $nome,
            $preco,
            $quantidade,
            $preco * $quantidade,
        ]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    exit('Erro ao salvar pedido manual: ' . $e->getMessage());
}

redirect('pedidos.php');
