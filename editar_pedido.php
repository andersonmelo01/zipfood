<?php
require_once __DIR__ . '/conexao.php';
require_admin();

$id = $_POST['id'];

$pdo->beginTransaction();

try {

    // 1️⃣ Atualiza dados principais
    $stmt = $pdo->prepare("UPDATE pedidos SET 
        nome=?, telefone=?, endereco=?, referencia=?, pagamento=?, observacao=? 
        WHERE id=?");

    $stmt->execute([
        $_POST['nome'],
        $_POST['telefone'],
        $_POST['endereco'],
        $_POST['referencia'],
        $_POST['pagamento'],
        $_POST['observacao'],
        $id
    ]);

    // 2️⃣ Apaga todos os itens antigos
    $pdo->prepare("DELETE FROM pedido_itens WHERE pedido_id=?")
        ->execute([$id]);

    $total = 0;

    // 3️⃣ Insere novamente os itens enviados
    if (!empty($_POST['itens'])) {

        foreach ($_POST['itens'] as $item) {

            if (empty($item['nome'])) continue;

            $preco = floatval($item['preco']);
            $quantidade = intval($item['quantidade']);
            $subtotal = $preco * $quantidade;

            $total += $subtotal;

            $stmtItem = $pdo->prepare("INSERT INTO pedido_itens 
            (pedido_id, produto_nome, preco, quantidade, subtotal) 
            VALUES (?, ?, ?, ?, ?)");

            $stmtItem->execute([
                $id,
                $item['nome'],
                $preco,
                $quantidade,
                $subtotal
            ]);
        }
    }

    // 4️⃣ Atualiza total do pedido
    $stmtTotal = $pdo->prepare("UPDATE pedidos SET total=? WHERE id=?");
    $stmtTotal->execute([$total, $id]);

    $pdo->commit();
} catch (Exception $e) {

    $pdo->rollBack();
    die("Erro ao atualizar pedido: " . $e->getMessage());
}

header("Location: pedidos.php");
exit;
