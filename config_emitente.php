<?php
include 'emitente.php';

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'nome' => $_POST['nome'] ?? '',
        'cnpj' => $_POST['cnpj'] ?? '',
        'endereco' => $_POST['endereco'] ?? '',
        'telefone' => $_POST['telefone'] ?? '',
        'site' => $_POST['site'] ?? ''
    ];
    salvar_emitente($dados);
    $mensagem = 'Dados do emitente salvos com sucesso!';
}

$emitente = ler_emitente();
if (!isset($emitente['site'])) $emitente['site'] = '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Configuração do Emitente</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</head>
</form>
    </div>
<body class="app-bg" style="min-height:100vh;">
    <div class="container app-shell py-4">
        <div class="app-hero p-4 p-lg-5 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="brand-mark mb-3">🏢</div>
                    <h2 class="fw-bold mb-1">Configuração do Emitente</h2>
                    <div class="opacity-75">Dados do estabelecimento para relatórios e pedidos.</div>
                </div>
                <a href="dashboard.php" class="btn btn-outline-light">← Voltar ao Dashboard</a>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card-soft p-4 shadow-sm mb-4">
                    <?php if ($mensagem): ?>
                        <div class="alert alert-success py-2 mb-3"><?= $mensagem ?></div>
                    <?php endif; ?>
                    <form method="post" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label">Nome do Estabelecimento</label>
                            <input type="text" class="form-control" name="nome" value="<?= htmlspecialchars($emitente['nome']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">CNPJ/CPF</label>
                            <input type="text" class="form-control" id="cnpj" name="cnpj" value="<?= htmlspecialchars($emitente['cnpj']) ?>" required maxlength="18">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Endereço</label>
                            <input type="text" class="form-control" name="endereco" value="<?= htmlspecialchars($emitente['endereco']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($emitente['telefone']) ?>" maxlength="15">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Site</label>
                            <input type="text" class="form-control" name="site" value="<?= htmlspecialchars($emitente['site']) ?>" placeholder="https://">
                        </div>
                        <button type="submit" class="btn btn-brand w-100">Salvar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
    $(function(){
        function cnpjCpfMask(val) {
            return val.replace(/\D/g, '').length <= 11 ? '000.000.000-00' : '00.000.000/0000-00';
        }
        $('#cnpj').mask(cnpjCpfMask, {
            onKeyPress: function(val, e, field, options) {
                field.mask(cnpjCpfMask.apply({}, arguments), options);
            }
        });
        $('#telefone').mask('(00) 00000-0000');
    });
    </script>
</body>
</html>
