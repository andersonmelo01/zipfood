<?php

function app_root(): string
{
    return dirname(__DIR__);
}

function runtime_config_path(): string
{
    return app_root() . DIRECTORY_SEPARATOR . 'config.json';
}

function app_config(): array
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $root = app_root();
    $projectConfig = [];
    $runtimeConfig = [];

    if (is_file($root . DIRECTORY_SEPARATOR . 'config.php')) {
        $loaded = require $root . DIRECTORY_SEPARATOR . 'config.php';
        if (is_array($loaded)) {
            $projectConfig = $loaded;
        }
    }

    if (is_file(runtime_config_path())) {
        $decoded = json_decode((string) file_get_contents(runtime_config_path()), true);
        if (is_array($decoded)) {
            $runtimeConfig = $decoded;
        }
    }

    $config = [
        'project' => $projectConfig,
        'delivery' => $runtimeConfig,
        'app' => $projectConfig['app'] ?? [],
        'database' => $projectConfig['database'] ?? [],
        'auth' => $projectConfig['auth'] ?? [],
        'integrations' => $projectConfig['integrations'] ?? [],
    ];

    return $config;
}

function config_value(string $path, mixed $default = null): mixed
{
    $value = app_config();

    foreach (explode('.', $path) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function write_json_file(string $path, array $data): bool
{
    return false !== file_put_contents(
        $path,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    );
}

function save_runtime_config(array $config): bool
{
    return write_json_file(runtime_config_path(), $config);
}

function config_set(string $section, array $value): bool
{
    if ($section === 'delivery') {
        return save_runtime_config($value);
    }

    $config = [];
    if (is_file(runtime_config_path())) {
        $decoded = json_decode((string) file_get_contents(runtime_config_path()), true);
        if (is_array($decoded)) {
            $config = $decoded;
        }
    }

    $config[$section] = $value;

    return save_runtime_config($config);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money(mixed $value): string
{
    return number_format((float) $value, 2, ',', '.');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function auth_session_key(): string
{
    return 'auth_user';
}

function role_options(): array
{
    return [
        'admin' => 'Administrador',
        'gerente' => 'Gerente',
        'vendedor' => 'Vendedor',
    ];
}

function role_label(string $role): string
{
    $roles = role_options();
    return $roles[$role] ?? ucfirst($role);
}

function normalize_role(?string $role): string
{
    $role = strtolower(trim((string) $role));
    return array_key_exists($role, role_options()) ? $role : 'vendedor';
}

function module_permissions(): array
{
    return [
        'admin' => ['*'],
        'gerente' => ['dashboard', 'produtos', 'pedidos', 'financeiro'],
        'vendedor' => ['dashboard', 'pedidos'],
    ];
}

function module_label(string $module): string
{
    $labels = [
        'dashboard' => 'dashboard',
        'produtos' => 'produtos',
        'pedidos' => 'pedidos',
        'financeiro' => 'financeiro',
        'usuarios' => 'usuarios',
        'feedbacks' => 'feedbacks',
        'emitente' => 'emitente',
        'configuracoes' => 'configuracoes',
        'licenca' => 'licenca',
    ];

    return $labels[$module] ?? $module;
}

function legacy_admin_session_user(): ?array
{
    if (empty($_SESSION['admin']) || !is_array($_SESSION['admin'])) {
        return null;
    }

    $usuario = trim((string) ($_SESSION['admin']['user'] ?? ''));
    if ($usuario === '') {
        return null;
    }

    return [
        'id' => 0,
        'nome' => 'Administrador',
        'usuario' => $usuario,
        'papel' => 'admin',
        'ativo' => 1,
    ];
}

function current_user(): ?array
{
    $sessionKey = auth_session_key();
    $user = $_SESSION[$sessionKey] ?? null;

    if (is_array($user) && !empty($user['usuario'])) {
        $user['papel'] = normalize_role((string) ($user['papel'] ?? 'vendedor'));
        $user['ativo'] = (int) ($user['ativo'] ?? 1);
        return $user;
    }

    return legacy_admin_session_user();
}

function is_user_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin_logged_in(): bool
{
    return is_user_logged_in();
}

function current_user_is_admin(): bool
{
    $user = current_user();
    return $user !== null && normalize_role((string) ($user['papel'] ?? '')) === 'admin';
}

function login_user(array $user, bool $regenerateSession = true): void
{
    if ($regenerateSession && session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    $payload = [
        'id' => (int) ($user['id'] ?? 0),
        'nome' => trim((string) ($user['nome'] ?? '')),
        'usuario' => trim((string) ($user['usuario'] ?? '')),
        'papel' => normalize_role((string) ($user['papel'] ?? 'vendedor')),
        'ativo' => (int) ($user['ativo'] ?? 1),
        'logged_at' => time(),
    ];

    $_SESSION[auth_session_key()] = $payload;
    $_SESSION['admin'] = [
        'user' => $payload['usuario'],
        'logged_at' => $payload['logged_at'],
    ];
}

function logout_user(): void
{
    unset($_SESSION[auth_session_key()], $_SESSION['admin'], $_SESSION['licenca_admin']);
}

function refresh_logged_user(PDO $pdo): ?array
{
    $sessionUser = current_user();
    if ($sessionUser === null) {
        return null;
    }

    $sessionUserId = (int) ($sessionUser['id'] ?? 0);
    $sessionUsername = trim((string) ($sessionUser['usuario'] ?? ''));

    if ($sessionUserId > 0) {
        $stmt = $pdo->prepare('SELECT id, nome, usuario, papel, ativo FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$sessionUserId]);
        $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dbUser || empty($dbUser['ativo'])) {
            logout_user();
            return null;
        }

        login_user($dbUser, false);
        return current_user();
    }

    if ($sessionUsername !== '') {
        $stmt = $pdo->prepare('SELECT id, nome, usuario, papel, ativo FROM usuarios WHERE usuario = ? LIMIT 1');
        $stmt->execute([$sessionUsername]);
        $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dbUser && !empty($dbUser['ativo'])) {
            login_user($dbUser, false);
            return current_user();
        }
    }

    return $sessionUser;
}

function user_can(string $module, ?array $user = null): bool
{
    $user ??= current_user();
    if ($user === null || empty($user['ativo'])) {
        return false;
    }

    $role = normalize_role((string) ($user['papel'] ?? ''));
    $permissions = module_permissions()[$role] ?? [];

    return in_array('*', $permissions, true) || in_array($module, $permissions, true);
}

function license_status(): array
{
    require_once app_root() . DIRECTORY_SEPARATOR . 'emitente.php';

    $emitente = ler_emitente();
    $diasRestantes = null;
    $expirada = false;

    if (!empty($emitente['validade'])) {
        try {
            $hoje = new DateTime('today');
            $validade = new DateTime((string) $emitente['validade']);
            $diasRestantes = (int) $hoje->diff($validade)->format('%r%a');
            $expirada = $diasRestantes < 0;
        } catch (Throwable) {
            $expirada = true;
        }
    }

    return [
        'emitente' => $emitente,
        'dias_restantes' => $diasRestantes,
        'expirada' => $expirada,
    ];
}

function license_is_expired(): bool
{
    return (bool) (license_status()['expirada'] ?? false);
}

function is_loja_fechada(): bool
{
    if (license_is_expired()) {
        return true;
    }

    return (bool) config_value('delivery.loja_fechada', false);
}

function forbidden_response(string $message = 'Acesso negado.'): never
{
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Acesso negado</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
        <div class="card shadow border-0" style="max-width: 480px;">
            <div class="card-body p-4 p-lg-5 text-center">
                <h1 class="h4 fw-bold mb-3">Permissao insuficiente</h1>
                <p class="text-muted mb-4"><?= e($message) ?></p>
                <?php if (is_user_logged_in()): ?>
                    <a href="dashboard.php" class="btn btn-primary">Voltar ao dashboard</a>
                <?php else: ?>
                    <a href="admin.php" class="btn btn-primary">Ir para o login</a>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

function enforce_license_for_module(string $module): void
{
    if (!license_is_expired()) {
        return;
    }

    $allowedForExpiredAdmin = ['dashboard', 'emitente', 'licenca'];

    if (current_user_is_admin() && in_array($module, $allowedForExpiredAdmin, true)) {
        return;
    }

    forbidden_response('A licenca esta expirada. Regularize o sistema para acessar este modulo.');
}

function require_login(): void
{
    if (!is_user_logged_in()) {
        redirect('admin.php');
    }
}

function require_module_access(string $module): void
{
    require_login();

    if (!user_can($module)) {
        forbidden_response('Seu perfil nao possui acesso ao modulo de ' . module_label($module) . '.');
    }

    enforce_license_for_module($module);
}

function require_admin(): void
{
    require_login();

    if (!current_user_is_admin()) {
        forbidden_response('Somente administradores podem acessar esta area.');
    }

    enforce_license_for_module('usuarios');
}

function pedidos_sync_file(): string
{
    return app_root() . DIRECTORY_SEPARATOR . 'pedidos.json';
}

function load_pedidos_sync(): array
{
    $file = pedidos_sync_file();

    if (!is_file($file)) {
        return [];
    }

    $decoded = json_decode((string) file_get_contents($file), true);
    return is_array($decoded) ? $decoded : [];
}

function save_pedidos_sync(array $pedidos): bool
{
    return write_json_file(pedidos_sync_file(), array_values($pedidos));
}

function append_pedido_sync(array $pedido): void
{
    $pedidos = load_pedidos_sync();
    $pedidos[] = $pedido;
    save_pedidos_sync($pedidos);
}
