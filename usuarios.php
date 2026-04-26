<?php

require_once __DIR__ . '/conexao.php';
require_module_access('usuarios');

$mensagem = null;
$erro = null;
$edicaoId = isset($_GET['editar']) ? (int) $_GET['editar'] : 0;

if (isset($_GET['toggle'])) {
    $toggleId = (int) $_GET['toggle'];
    if ($toggleId > 0) {
        $stmt = $pdo->prepare('SELECT id, nome, papel, ativo FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$toggleId]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($targetUser) {
            $currentUser = current_user();
            $newStatus = empty($targetUser['ativo']) ? 1 : 0;

            if ((int) $targetUser['id'] === (int) ($currentUser['id'] ?? 0) && $newStatus === 0) {
                $erro = 'Voce nao pode desativar o proprio usuario.';
            } elseif ($targetUser['papel'] === 'admin' && $newStatus === 0) {
                $stmtAdmins = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE papel = 'admin' AND ativo = 1");
                if ((int) $stmtAdmins->fetchColumn() <= 1) {
                    $erro = 'Deve existir pelo menos um administrador ativo.';
                }
            }

            if ($erro === null) {
                $pdo->prepare('UPDATE usuarios SET ativo = ?, updated_at = NOW() WHERE id = ?')->execute([$newStatus, $toggleId]);
                redirect('usuarios.php');
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : 0;
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $usuario = trim((string) ($_POST['usuario'] ?? ''));
    $papel = normalize_role((string) ($_POST['papel'] ?? 'vendedor'));
    $senha = (string) ($_POST['senha'] ?? '');
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    if ($nome === '' || $usuario === '') {
        $erro = 'Informe nome e usuario.';
    } elseif ($id === 0 && strlen($senha) < 8) {
        $erro = 'A senha do novo usuario precisa ter pelo menos 8 caracteres.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = ? AND id <> ? LIMIT 1');
        $stmt->execute([$usuario, $id]);
        if ($stmt->fetchColumn()) {
            $erro = 'Ja existe um usuario cadastrado com esse login.';
        } else {
            if ($id > 0) {
                $stmtAtual = $pdo->prepare('SELECT id, papel FROM usuarios WHERE id = ? LIMIT 1');
                $stmtAtual->execute([$id]);
                $usuarioAtualizado = $stmtAtual->fetch(PDO::FETCH_ASSOC);

                if (!$usuarioAtualizado) {
                    $erro = 'Usuario nao encontrado.';
                } else {
                    $currentUser = current_user();
                    if ((int) $id === (int) ($currentUser['id'] ?? 0) && $ativo === 0) {
                        $erro = 'Voce nao pode desativar o proprio usuario.';
                    } elseif ($usuarioAtualizado['papel'] === 'admin' && $papel !== 'admin') {
                        $stmtAdmins = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE papel = 'admin' AND ativo = 1");
                        if ((int) $stmtAdmins->fetchColumn() <= 1) {
                            $erro = 'Deve existir pelo menos um administrador ativo.';
                        }
                    }
                }

                if ($erro === null) {
                    if ($senha !== '') {
                        $stmt = $pdo->prepare('
                            UPDATE usuarios
                            SET nome = ?, usuario = ?, papel = ?, ativo = ?, senha_hash = ?, updated_at = NOW()
                            WHERE id = ?
                        ');
                        $stmt->execute([$nome, $usuario, $papel, $ativo, password_hash($senha, PASSWORD_DEFAULT), $id]);
                    } else {
                        $stmt = $pdo->prepare('
                            UPDATE usuarios
                            SET nome = ?, usuario = ?, papel = ?, ativo = ?, updated_at = NOW()
                            WHERE id = ?
                        ');
                        $stmt->execute([$nome, $usuario, $papel, $ativo, $id]);
                    }

                    $mensagem = 'Usuario atualizado com sucesso.';
                    $edicaoId = $id;
                }
            } else {
                $stmt = $pdo->prepare('
                    INSERT INTO usuarios (nome, usuario, senha_hash, papel, ativo, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ');
                $stmt->execute([$nome, $usuario, password_hash($senha, PASSWORD_DEFAULT), $papel, $ativo]);
                $mensagem = 'Usuario cadastrado com sucesso.';
                $edicaoId = 0;
            }
        }
    }
}

$usuarios = $pdo->query('SELECT id, nome, usuario, papel, ativo, ultimo_login_em, created_at FROM usuarios ORDER BY nome ASC, usuario ASC')->fetchAll(PDO::FETCH_ASSOC);

$usuarioEdicao = [
    'id' => 0,
    'nome' => '',
    'usuario' => '',
    'papel' => 'vendedor',
    'ativo' => 1,
];

if ($edicaoId > 0) {
    foreach ($usuarios as $usuarioItem) {
        if ((int) $usuarioItem['id'] === $edicaoId) {
            $usuarioEdicao = $usuarioItem;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuarios do Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Usuarios e permissoes</h1>
                <div class="text-muted">Cadastre acessos por perfil para administrar o sistema.</div>
            </div>
            <a href="dashboard.php" class="btn btn-dark">Voltar ao dashboard</a>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= e($erro) ?></div>
        <?php endif; ?>

        <?php if ($mensagem): ?>
            <div class="alert alert-success"><?= e($mensagem) ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white fw-bold">
                        <?= $usuarioEdicao['id'] ? 'Editar usuario' : 'Novo usuario' ?>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="d-grid gap-3">
                            <input type="hidden" name="id" value="<?= (int) $usuarioEdicao['id'] ?>">

                            <div>
                                <label class="form-label">Nome</label>
                                <input type="text" name="nome" class="form-control" value="<?= e($usuarioEdicao['nome']) ?>" required>
                            </div>

                            <div>
                                <label class="form-label">Usuario</label>
                                <input type="text" name="usuario" class="form-control" value="<?= e($usuarioEdicao['usuario']) ?>" required>
                            </div>

                            <div>
                                <label class="form-label">Perfil</label>
                                <select name="papel" class="form-select" required>
                                    <?php foreach (role_options() as $role => $label): ?>
                                        <option value="<?= e($role) ?>" <?= $usuarioEdicao['papel'] === $role ? 'selected' : '' ?>>
                                            <?= e($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="form-label"><?= $usuarioEdicao['id'] ? 'Nova senha (opcional)' : 'Senha' ?></label>
                                <input type="password" name="senha" class="form-control" <?= $usuarioEdicao['id'] ? '' : 'required' ?> minlength="8">
                                <div class="form-text">Minimo de 8 caracteres.</div>
                            </div>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="ativo" id="ativoUsuario" <?= !empty($usuarioEdicao['ativo']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="ativoUsuario">Usuario ativo</label>
                            </div>

                            <button class="btn btn-primary"><?= $usuarioEdicao['id'] ? 'Salvar alteracoes' : 'Cadastrar usuario' ?></button>
                            <?php if ($usuarioEdicao['id']): ?>
                                <a href="usuarios.php" class="btn btn-outline-secondary">Cancelar edicao</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="card shadow border-0 mt-4">
                    <div class="card-header bg-dark text-white fw-bold">Perfis disponiveis</div>
                    <div class="card-body small">
                        <p class="mb-2"><strong>Administrador:</strong> acesso completo a todos os modulos.</p>
                        <p class="mb-2"><strong>Gerente:</strong> produtos, pedidos e financeiro.</p>
                        <p class="mb-0"><strong>Vendedor:</strong> acesso somente a pedidos.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-dark text-white fw-bold">Usuarios cadastrados</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nome</th>
                                        <th>Usuario</th>
                                        <th>Perfil</th>
                                        <th>Status</th>
                                        <th>Ultimo acesso</th>
                                        <th width="220">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuarioItem): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?= e($usuarioItem['nome']) ?></div>
                                                <div class="small text-muted">Criado em <?= date('d/m/Y H:i', strtotime((string) $usuarioItem['created_at'])) ?></div>
                                            </td>
                                            <td><?= e($usuarioItem['usuario']) ?></td>
                                            <td><span class="badge bg-primary-subtle text-primary"><?= e(role_label((string) $usuarioItem['papel'])) ?></span></td>
                                            <td>
                                                <?php if (!empty($usuarioItem['ativo'])): ?>
                                                    <span class="badge bg-success">Ativo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inativo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= !empty($usuarioItem['ultimo_login_em']) ? e(date('d/m/Y H:i', strtotime((string) $usuarioItem['ultimo_login_em']))) : '<span class="text-muted">Nunca acessou</span>' ?>
                                            </td>
                                            <td class="d-flex gap-2 flex-wrap">
                                                <a href="usuarios.php?editar=<?= (int) $usuarioItem['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                                <a href="usuarios.php?toggle=<?= (int) $usuarioItem['id'] ?>" class="btn btn-sm <?= !empty($usuarioItem['ativo']) ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                                    <?= !empty($usuarioItem['ativo']) ? 'Desativar' : 'Ativar' ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
