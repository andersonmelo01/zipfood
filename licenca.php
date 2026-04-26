<?php

require_once __DIR__ . '/conexao.php';
require_module_access('licenca');
require_once __DIR__ . '/emitente.php';

$erro = null;
$sucesso = null;
$licencaGerada = '';
$emitente = ler_emitente();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['validade']) && !isset($_POST['documento'])) {
        $novaValidade = trim((string) ($_POST['validade'] ?? ''));

        if ($novaValidade === '') {
            $erro = 'Informe uma data de validade.';
        } else {
            $emitente['validade'] = $novaValidade;
            salvar_emitente($emitente);
            $emitente = ler_emitente();
            $sucesso = 'Licenca atualizada com sucesso.';
        }
    }

    if (isset($_POST['documento'], $_POST['validade'])) {
        $documento = trim((string) ($_POST['documento'] ?? ''));
        $validade = trim((string) ($_POST['validade'] ?? ''));

        if ($documento === '' || $validade === '') {
            $erro = 'Informe documento e validade para gerar a licenca.';
        } else {
            $licencaGerada = base64_encode(json_encode([
                'doc' => $documento,
                'validade' => $validade,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Painel de Licenca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Licenca do sistema</h1>
                <div class="text-muted">Atualize a validade ou gere um codigo de licenca.</div>
            </div>
            <a href="dashboard.php" class="btn btn-dark">Voltar ao dashboard</a>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= e($erro) ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= e($sucesso) ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow border-0">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Atualizar validade</h2>
                        <form method="POST">
                            <label class="form-label">Validade</label>
                            <input type="date" name="validade" class="form-control mb-3" value="<?= e((string) ($emitente['validade'] ?? '')) ?>" required>
                            <button class="btn btn-success w-100">Salvar validade</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow border-0">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Gerar licenca</h2>
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">CPF ou CNPJ</label>
                                    <input type="text" name="documento" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Validade</label>
                                    <input type="date" name="validade" class="form-control" required>
                                </div>
                            </div>
                            <button class="btn btn-primary w-100 mt-3">Gerar codigo</button>
                        </form>

                        <?php if ($licencaGerada !== ''): ?>
                            <hr>
                            <label class="form-label fw-bold">Codigo gerado</label>
                            <textarea id="licencaGerada" class="form-control" rows="4" readonly><?= e($licencaGerada) ?></textarea>
                            <button class="btn btn-outline-success mt-3" type="button" onclick="copiarLicenca()">Copiar codigo</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function copiarLicenca() {
            const campo = document.getElementById('licencaGerada');
            if (!campo) {
                return;
            }

            campo.select();
            await navigator.clipboard.writeText(campo.value);
            alert('Licenca copiada.');
        }
    </script>
</body>
</html>
