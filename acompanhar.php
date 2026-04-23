<?php

require_once __DIR__ . '/conexao.php';


$pedido = null;
$codigoPedido = null;
$telefoneBusca = null;

if (isset($_GET['codigo'])) {
    $codigoPedido = (int) $_GET['codigo'];
} elseif (isset($_POST['codigo'])) {
    $codigoPedido = (int) $_POST['codigo'];
}
if (isset($_POST['telefone'])) {
    $telefoneBusca = preg_replace('/\D/', '', $_POST['telefone']);
}

if ($codigoPedido > 0) {
    $stmt = $pdo->prepare('SELECT * FROM pedidos WHERE id = ?');
    $stmt->execute([$codigoPedido]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($telefoneBusca) {
    // Busca ignorando máscara e espaços
    $stmt = $pdo->prepare('SELECT * FROM pedidos WHERE REPLACE(REPLACE(REPLACE(REPLACE(telefone, "(", ""), ")", ""), "-", ""), " ", "") = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$telefoneBusca]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pedido) {
        $codigoPedido = $pedido['id'];
    }
}

function corStatus(string $status): string
{
    return match ($status) {
        'Preparando' => 'bg-warning text-dark',
        'Saiu' => 'bg-info text-dark',
        'Entregue' => 'bg-success',
        default => 'bg-secondary',
    };
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acompanhar Pedido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="container" style="max-width: 480px;">
        <div class="app-hero text-center p-4 mb-4" style="border-radius: 28px;">
            <img src="img/logo-zipfood.svg" alt="ZipFood" style="height:54px;width:auto;">
            <h2 class="fw-bold mt-3 mb-1 page-title">Acompanhar Pedido</h2>
            <div class="page-subtitle mb-2" style="font-size:1.13em;">Consulte o status do seu pedido em tempo real</div>
        </div>
        <div class="card-soft p-4 shadow-sm mb-4" style="border-radius: 22px;">
            <form method="POST" class="mb-3">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Número do pedido</label>
                    <input type="number" name="codigo" class="form-control form-control-lg" placeholder="Digite o número do pedido">
                </div>
                <div class="text-center mb-2 text-muted">ou</div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Telefone</label>
                    <input type="text" name="telefone" id="telefone" class="form-control form-control-lg" placeholder="Digite seu telefone" maxlength="15" oninput="mascaraTelefone(this)">
                </div>
                <button class="btn btn-brand w-100 btn-lg">Buscar</button>
            </form>
            <script>
            function mascaraTelefone(input) {
                let v = input.value.replace(/\D/g, '');
                if (v.length > 10) {
                    v = v.replace(/(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
                } else if (v.length > 5) {
                    v = v.replace(/(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
                } else if (v.length > 2) {
                    v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
                }
                input.value = v;
            }
            </script>
    <script>
        // Bloqueia botão voltar e redireciona para cardápio
        history.pushState(null, '', location.href);
        window.onpopstate = function() {
            window.location.href = 'index.php';
        };
    </script>

            <?php if ($pedido): ?>
                <div class="text-center mb-4">
                    <span class="badge bg-dark px-3 py-2 fs-6 mb-2" style="font-size:1.1em;letter-spacing:0.03em;">Pedido #<?= (int) $pedido['id'] ?></span>
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-2" style="background: linear-gradient(135deg, #ff6b00 60%, #ff3d00 100%); width: 70px; height: 70px; box-shadow: 0 6px 24px rgba(255,107,0,0.13);">
                            <i class="bi bi-clock-history fs-1 text-white"></i>
                        </div>
                        <span id="statusBadge" class="badge px-4 py-2 fs-5 <?= corStatus((string) $pedido['status']) ?>" style="font-size:1.13em; letter-spacing:0.03em;">
                            <span id="statusPedido"><?= e($pedido['status']) ?></span>
                        </span>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <div class="soft-panel p-3">
                            <div class="d-flex align-items-center mb-2"><i class="bi bi-person-circle me-2 text-primary fs-5"></i> <strong>Nome:</strong> <span class="ms-2"><?= e($pedido['nome']) ?></span></div>
                            <div class="d-flex align-items-center mb-2"><i class="bi bi-telephone me-2 text-primary fs-5"></i> <strong>Telefone:</strong> <span class="ms-2"><?= e($pedido['telefone']) ?></span></div>
                            <div class="d-flex align-items-center mb-2"><i class="bi bi-geo-alt me-2 text-primary fs-5"></i> <strong>Endereço:</strong> <span class="ms-2"><?= e($pedido['endereco']) ?></span></div>
                            <div class="d-flex align-items-center mb-2"><i class="bi bi-credit-card me-2 text-primary fs-5"></i> <strong>Pagamento:</strong> <span class="ms-2"><?= e($pedido['pagamento']) ?></span></div>
                            <div class="d-flex align-items-center mb-2"><i class="bi bi-cash-coin me-2 text-primary fs-5"></i> <strong>Pagamento confirmado:</strong> <span class="ms-2"><?php if (in_array($pedido['pagamento'], ['Cartão Crédito', 'Cartão Débito'], true)): ?><span class="badge bg-success">Já pago</span><?php else: ?><span class="badge bg-danger">Pagar na entrega</span><?php endif; ?></span></div>
                        </div>
                    </div>
                </div>
            <?php elseif ($codigoPedido > 0): ?>
                <div class="alert alert-danger mt-3 text-center">Pedido não encontrado.</div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($pedido): ?>
        <script>
            let statusAtual = <?= json_encode($pedido['status'], JSON_UNESCAPED_UNICODE) ?>;
            let pedidoId = <?= (int) $pedido['id'] ?>;

            async function verificarStatus() {
                try {
                    const response = await fetch('status_pedido.php?id=' + pedidoId);
                    const data = await response.json();

                    if (data.status && data.status !== statusAtual) {
                        statusAtual = data.status;
                        document.getElementById('statusPedido').innerText = statusAtual;
                        const badge = document.getElementById('statusBadge');
                        badge.className = 'badge fs-5 ' + corStatus(statusAtual);
                    }
                } catch (e) {
                    console.log('Erro ao verificar status');
                }
            }

            function corStatus(status) {
                switch (status) {
                    case 'Preparando':
                        return 'bg-warning text-dark';
                    case 'Saiu':
                        return 'bg-info text-dark';
                    case 'Entregue':
                        return 'bg-success';
                    default:
                        return 'bg-secondary';
                }
            }

            setInterval(verificarStatus, 5000);
        </script>
    <?php endif; ?>
</body>

</html>
