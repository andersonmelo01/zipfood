<?php

$secret = 'AMS_SECRET_2026'; // mesmo segredo do sistema
$licencaGerada = null;
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $documento = preg_replace('/\D/', '', $_POST['documento'] ?? '');
    $validade = $_POST['validade'] ?? null;

    if (!$documento || !$validade) {
        $erro = "Informe o CPF/CNPJ e a data de validade.";
    } elseif (strlen($documento) != 11 && strlen($documento) != 14) {
        $erro = "Documento inválido. Informe CPF (11) ou CNPJ (14 dígitos).";
    } else {

        $tipo = strlen($documento) == 11 ? 'CPF' : 'CNPJ';

        $payload = [
            'documento' => $documento,
            'tipo' => $tipo,
            'validade' => $validade,
        ];

        $json = json_encode($payload);
        $assinatura = hash_hmac('sha256', $json, $secret);
        $licencaGerada = base64_encode($json . '|' . $assinatura);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerador de Licença</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #0d6efd, #20c997);
            height: 100vh;
        }

        .card {
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, .2);
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card p-4">
                    <h4 class="text-center mb-3">Gerador de Licença Offline</h4>

                    <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= $erro ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">CPF ou CNPJ</label>
                            <input type="text" name="documento" class="form-control" placeholder="Digite o CPF ou CNPJ" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Data de validade</label>
                            <input type="date" name="validade" class="form-control" required>
                        </div>

                        <button class="btn btn-primary w-100 fw-bold">
                            Gerar Licença
                        </button>
                    </form>

                    <?php if ($licencaGerada): ?>
                        <hr>
                        <label class="form-label fw-bold">Chave de Licença:</label>
                        <textarea class="form-control" rows="4" id="licenca"><?= $licencaGerada ?></textarea>
                        <button class="btn btn-success mt-2 w-100" onclick="copiarLicenca()">
                            Copiar Licença
                        </button>
                    <?php endif; ?>

                </div>

                <p class="text-center text-white mt-3 small">
                    © <?= date('Y') ?> - Gerador de Licenças
                </p>

            </div>
        </div>
    </div>

    <script>
        function copiarLicenca() {
            let texto = document.getElementById("licenca");
            texto.select();
            texto.setSelectionRange(0, 99999);
            document.execCommand("copy");
            alert("Licença copiada!");
        }
    </script>

</body>

</html>