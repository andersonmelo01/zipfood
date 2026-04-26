<?php
// Página de configuração do estabelecimento
require_once __DIR__ . '/conexao.php';
require_module_access('configuracoes');

// Caminho do arquivo de configuração
$configFile = __DIR__ . '/config_estabelecimento.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'nome' => trim($_POST['nome'] ?? ''),
        'cnpj' => trim($_POST['cnpj'] ?? ''),
        'endereco' => trim($_POST['endereco'] ?? ''),
        'telefone' => trim($_POST['telefone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'site' => trim($_POST['site'] ?? ''),
        'mensagem_cupom' => trim($_POST['mensagem_cupom'] ?? ''),
    ];
    file_put_contents($configFile, json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    $msg = 'Configurações salvas com sucesso!';
}

$dados = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];

?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Configuração do Estabelecimento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 520px;">
    <div class="card shadow p-4">
        <h3 class="mb-3">Configuração do Estabelecimento</h3>
        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nome do Estabelecimento</label>
                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($dados['nome'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">CNPJ</label>
                <input type="text" name="cnpj" class="form-control" value="<?= htmlspecialchars($dados['cnpj'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Endereço</label>
                <input type="text" name="endereco" class="form-control" value="<?= htmlspecialchars($dados['endereco'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($dados['telefone'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($dados['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Site</label>
                <input type="text" name="site" class="form-control" value="<?= htmlspecialchars($dados['site'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Mensagem no Cupom</label>
                <textarea name="mensagem_cupom" class="form-control" rows="2"><?= htmlspecialchars($dados['mensagem_cupom'] ?? '') ?></textarea>
            </div>
            <button class="btn btn-primary">Salvar</button>
        </form>
    </div>
</div>
</body>
</html>
