<?php

require_once __DIR__ . '/conexao.php';

if (is_admin_logged_in()) {
    redirect('dashboard.php');
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim((string) ($_POST['usuario'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');

    $auth = config_value('auth', []);
    $usuariosPermitidos = array_values(array_filter([
        (string) ($auth['admin_user'] ?? ''),
        'andersonmelo01@gmail.com',
        'admin',
    ]));
    $usuarioValido = false;
    foreach ($usuariosPermitidos as $usuarioPermitido) {
        if ($usuarioPermitido !== '' && hash_equals($usuarioPermitido, $usuario)) {
            $usuarioValido = true;
            break;
        }
    }
    $senhaHash = (string) ($auth['admin_password_hash'] ?? '');

    if ($senhaHash !== '') {
        $senhaValida = password_verify($senha, $senhaHash);
    } else {
        $senhaValida = hash_equals((string) ($auth['admin_password'] ?? '123456'), $senha);
    }

    if ($usuarioValido && $senhaValida) {
        $_SESSION['admin'] = [
            'user' => $usuario,
            'logged_at' => time(),
        ];

        redirect('dashboard.php');
    }

    $erro = 'Login inválido.';
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
            <div class="col-12 col-md-5 col-lg-4">
                <div class="soft-panel p-4 p-lg-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="img/logo-zipfood.svg" alt="ZipFood" style="height:40px;width:auto;">
                        <div>
                            <div class="page-title fs-4 mb-0">ZipFood</div>
                            <div class="page-subtitle">Painel administrativo</div>
                        </div>
                    </div>

                    <h1 class="h4 fw-bold mb-2">Entrar</h1>
                    <p class="text-muted mb-4">Acesse o back-office para gerenciar produtos, pedidos e operação.</p>

                    <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= e($erro) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="d-grid gap-3">
                        <div>
                            <label class="form-label">Usuário</label>
                            <input type="text" name="usuario" class="form-control" placeholder="Seu usuário" required>
                        </div>

                        <div>
                            <label class="form-label">Senha</label>
                            <input type="password" name="senha" class="form-control" placeholder="Sua senha" required>
                        </div>

                        <button class="btn btn-brand btn-lg">Entrar</button>
                    </form>

                    <div class="small text-muted mt-4">
                        Ambiente: <?= e((string) config_value('app.environment', 'local')) ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
