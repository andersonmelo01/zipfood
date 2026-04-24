<?php
session_start();

// ================= CONFIG =================
$usuario_correto = 'admin';
$senha_correta   = 'And95079@'; // ideal depois usar hash

$caminho_json = __DIR__ . '/emitente.json';

// ================= FUNÇÕES =================
function ler_emitente($caminho) {
    if (!file_exists($caminho)) {
        return [];
    }

    $conteudo = file_get_contents($caminho);
    return json_decode($conteudo, true);
}

function salvar_emitente($caminho, $dados) {
    file_put_contents($caminho, json_encode($dados, JSON_PRETTY_PRINT));
}

// ================= VARIÁVEIS =================
$erro = '';
$sucesso = '';
$licencaGerada = '';

// ================= LOGIN =================
if (!isset($_SESSION['licenca_admin'])) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'], $_POST['senha'])) {

        if ($_POST['usuario'] === $usuario_correto && $_POST['senha'] === $senha_correta) {
            $_SESSION['licenca_admin'] = true;
            header('Location: licenca.php');
            exit;
        } else {
            $erro = 'Usuário ou senha inválidos!';
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>Login Licença</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="d-flex align-items-center justify-content-center min-vh-100 bg-light">

        <div class="card p-4 shadow" style="width: 350px;">
            <h4 class="text-center mb-3">Acesso Licença</h4>

            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= $erro ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="text" name="usuario" class="form-control mb-2" placeholder="Usuário" required>
                <input type="password" name="senha" class="form-control mb-3" placeholder="Senha" required>
                <button class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>

    </body>
    </html>
    <?php
    exit;
}

// ================= PROCESSAMENTO =================
$emitente = ler_emitente($caminho_json);

// Atualizar validade
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validade']) && !isset($_POST['documento'])) {

    $emitente['validade'] = $_POST['validade'];
    salvar_emitente($caminho_json, $emitente);

    $sucesso = 'Licença atualizada com sucesso!';
}

// Gerar licença
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['documento'], $_POST['validade'])) {

    $dados = [
        'doc' => $_POST['documento'],
        'validade' => $_POST['validade']
    ];

    $licencaGerada = base64_encode(json_encode($dados));
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel de Licença</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">

        <!-- Atualizar Licença -->
        <div class="col-md-5">
            <div class="card p-4 shadow">
                <h4 class="mb-3">Atualizar Licença</h4>

                <?php if ($sucesso): ?>
                    <div class="alert alert-success"><?= $sucesso ?></div>
                <?php endif; ?>

                <form method="post">
                    <label>Validade</label>
                    <input type="date" name="validade" class="form-control mb-3"
                        value="<?= htmlspecialchars($emitente['validade'] ?? '') ?>" required>

                    <button class="btn btn-success w-100">Salvar</button>
                </form>
            </div>
        </div>

        <!-- Gerador -->
        <div class="col-md-5">
            <div class="card p-4 shadow">
                <h4 class="mb-3">Gerador de Licença</h4>

                <form method="post">
                    <input type="text" name="documento" class="form-control mb-2" placeholder="CPF ou CNPJ" required>

                    <input type="date" name="validade" class="form-control mb-3" required>

                    <button class="btn btn-primary w-100">Gerar Licença</button>
                </form>

                <?php if ($licencaGerada): ?>
                    <hr>
                    <label class="fw-bold">Licença:</label>
                    <textarea id="licenca" class="form-control"><?= $licencaGerada ?></textarea>

                    <button class="btn btn-success mt-2 w-100" onclick="copiar()">Copiar</button>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
function copiar() {
    let texto = document.getElementById("licenca");
    texto.select();
    document.execCommand("copy");
    alert("Licença copiada!");
}
</script>

</body>
</html>
