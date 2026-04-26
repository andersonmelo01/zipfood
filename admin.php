<?php
require_once __DIR__ . '/conexao.php';

if (is_user_logged_in()) {
    redirect('dashboard.php');
}

$license = license_status();
$licencaExpirada = (bool) ($license['expirada'] ?? false);
$diasRestantes = $license['dias_restantes'] ?? null;

if ($licencaExpirada) {
    $mensagemLicenca = 'Licenca expirada em ' . date('d/m/Y', strtotime((string) ($license['emitente']['validade'] ?? 'now'))) . '.';
} elseif ($diasRestantes !== null && $diasRestantes <= 5) {
    $mensagemLicenca = 'Licenca vence em ' . $diasRestantes . ' dia(s).';
} else {
    $mensagemLicenca = null;
}

$erro = null;
$sucesso = null;
$abaAtiva = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = (string) ($_POST['acao'] ?? 'login');

    if ($acao === 'alterar_senha') {
        $abaAtiva = 'senha';

        $usuario = trim((string) ($_POST['usuario_alteracao'] ?? ''));
        $senhaAtual = (string) ($_POST['senha_atual'] ?? '');
        $novaSenha = (string) ($_POST['nova_senha'] ?? '');
        $confirmacao = (string) ($_POST['confirmar_nova_senha'] ?? '');

        if ($usuario === '' || $senhaAtual === '' || $novaSenha === '' || $confirmacao === '') {
            $erro = 'Preencha todos os campos para alterar a senha.';
        } elseif ($novaSenha !== $confirmacao) {
            $erro = 'A confirmacao da nova senha nao confere.';
        } elseif (strlen($novaSenha) < 8) {
            $erro = 'A nova senha precisa ter pelo menos 8 caracteres.';
        } else {
            $stmt = $pdo->prepare('SELECT id, senha_hash FROM usuarios WHERE usuario = ? AND ativo = 1 LIMIT 1');
            $stmt->execute([$usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($senhaAtual, (string) $user['senha_hash'])) {
                $erro = 'Usuario ou senha atual invalidos.';
            } else {
                $stmt = $pdo->prepare('UPDATE usuarios SET senha_hash = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([password_hash($novaSenha, PASSWORD_DEFAULT), (int) $user['id']]);
                $sucesso = 'Senha alterada com sucesso. Voce ja pode entrar com a nova senha.';
                $abaAtiva = 'login';
            }
        }
    } else {
        $usuario = trim((string) ($_POST['usuario'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');

        $stmt = $pdo->prepare('SELECT id, nome, usuario, senha_hash, papel, ativo FROM usuarios WHERE usuario = ? LIMIT 1');
        $stmt->execute([$usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || empty($user['ativo']) || !password_verify($senha, (string) $user['senha_hash'])) {
            $erro = 'Login invalido.';
        } elseif ($licencaExpirada && normalize_role((string) $user['papel']) !== 'admin') {
            $erro = 'Licenca expirada. Somente o administrador pode entrar para regularizacao.';
        } else {
            login_user($user);
            $pdo->prepare('UPDATE usuarios SET ultimo_login_em = NOW() WHERE id = ?')->execute([(int) $user['id']]);
            redirect('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <main class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="soft-panel p-4 p-lg-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="img/logo-zipfood.svg" alt="ZipFood" style="height:40px;width:auto;">
                        <div>
                            <div class="page-title fs-4 mb-0">ZipFood</div>
                            <div class="page-subtitle">Painel administrativo</div>
                        </div>
                    </div>

                    <h1 class="h4 fw-bold mb-2">Acesso ao sistema</h1>
                    <p class="text-muted mb-4">Entre com seu usuario ou altere a senha usando a senha atual.</p>

                    <?php if ($mensagemLicenca): ?>
                        <div class="alert <?= $licencaExpirada ? 'alert-danger' : 'alert-warning' ?> text-center fw-bold">
                            <?= e($mensagemLicenca) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= e($erro) ?></div>
                    <?php endif; ?>

                    <?php if ($sucesso): ?>
                        <div class="alert alert-success"><?= e($sucesso) ?></div>
                    <?php endif; ?>

                    <ul class="nav nav-pills nav-fill mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $abaAtiva === 'login' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#tab-login" type="button">Entrar</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $abaAtiva === 'senha' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#tab-senha" type="button">Alterar senha</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade <?= $abaAtiva === 'login' ? 'show active' : '' ?>" id="tab-login">
                            <form method="POST" class="d-grid gap-3">
                                <input type="hidden" name="acao" value="login">

                                <div>
                                    <label class="form-label">Usuario</label>
                                    <input type="text" name="usuario" class="form-control" required>
                                </div>

                                <div>
                                    <label class="form-label">Senha</label>
                                    <input type="password" name="senha" class="form-control" required>
                                </div>

                                <button class="btn btn-brand btn-lg">Entrar</button>
                            </form>
                        </div>

                        <div class="tab-pane fade <?= $abaAtiva === 'senha' ? 'show active' : '' ?>" id="tab-senha">
                            <form method="POST" class="d-grid gap-3">
                                <input type="hidden" name="acao" value="alterar_senha">

                                <div>
                                    <label class="form-label">Usuario</label>
                                    <input type="text" name="usuario_alteracao" class="form-control" required>
                                </div>

                                <div>
                                    <label class="form-label">Senha atual</label>
                                    <input type="password" name="senha_atual" class="form-control" required>
                                </div>

                                <div>
                                    <label class="form-label">Nova senha</label>
                                    <input type="password" name="nova_senha" class="form-control" minlength="8" required>
                                </div>

                                <div>
                                    <label class="form-label">Confirmar nova senha</label>
                                    <input type="password" name="confirmar_nova_senha" class="form-control" minlength="8" required>
                                </div>

                                <button class="btn btn-outline-primary btn-lg">Atualizar senha</button>
                            </form>
                        </div>
                    </div>

                    <div class="small text-muted mt-4">
                        Ambiente: <?= e((string) config_value('app.environment', 'local')) ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
