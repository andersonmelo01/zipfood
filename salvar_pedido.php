<?php

require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método inválido');
}

if (is_loja_fechada()) {
    http_response_code(403);
    exit('A loja está fechada no momento.');
}

$nome = trim((string) ($_POST['nome'] ?? ''));
$telefone = trim((string) ($_POST['telefone'] ?? ''));
$endereco = trim((string) ($_POST['endereco'] ?? ''));
$referencia = trim((string) ($_POST['referencia'] ?? ''));
$observacao = trim((string) ($_POST['observacao'] ?? ''));
$pagamento = trim((string) ($_POST['pagamento'] ?? ''));
$taxa = (float) ($_POST['taxa_entrega'] ?? 0);
$total = (float) ($_POST['total'] ?? 0);

$produtos = $_POST['produto_nome'] ?? [];
$qtds = $_POST['quantidade'] ?? [];
$precos = $_POST['preco'] ?? [];

if ($nome === '' || $telefone === '' || $endereco === '' || empty($produtos)) {
    exit('Dados inválidos.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO pedidos
        (nome, telefone, endereco, referencia, observacao, pagamento, taxa_entrega, total)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $nome,
        $telefone,
        $endereco,
        $referencia,
        $observacao,
        $pagamento,
        $taxa,
        $total,
    ]);

    $pedidoId = (int) $pdo->lastInsertId();

    $stmtItem = $pdo->prepare("
        INSERT INTO pedido_itens
        (pedido_id, produto_nome, quantidade, preco, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($produtos as $index => $produto) {
        $produtoNome = trim((string) $produto);
        $quantidade = (int) ($qtds[$index] ?? 0);
        $preco = (float) ($precos[$index] ?? 0);

        if ($produtoNome === '' || $quantidade <= 0) {
            continue;
        }

        $stmtItem->execute([
            $pedidoId,
            $produtoNome,
            $quantidade,
            $preco,
            $quantidade * $preco,
        ]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    exit('Erro ao salvar pedido: ' . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedido Confirmado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="soft-panel p-4 text-center">
                    <div class="status-chip is-open mb-3">Pedido confirmado</div>
                    <h1 class="h4 fw-bold mb-2">Seu pedido foi realizado</h1>
                    <p class="text-muted mb-3">O número do seu pedido é:</p>
                    <div class="display-6 fw-bold text-primary mb-4">#<?= (int) $pedidoId ?></div>

                    <a id="btnAcompanhar" href="acompanhar.php?codigo=<?= (int) $pedidoId ?>" class="btn btn-brand w-100">
                        Acompanhar pedido
                    </a>
                    <script>
                        // Redireciona automaticamente após 2 segundos
                        setTimeout(function() {
                            document.getElementById('btnAcompanhar').click();
                        }, 2000);
                    </script>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
