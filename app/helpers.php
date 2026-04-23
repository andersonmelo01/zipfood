<?php

function app_root(): string
{
    return dirname(__DIR__);
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

    if (is_file($root . DIRECTORY_SEPARATOR . 'config.json')) {
        $decoded = json_decode((string) file_get_contents($root . DIRECTORY_SEPARATOR . 'config.json'), true);
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

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money(mixed $value): string
{
    return number_format((float) $value, 2, ',', '.');
}

function redirect(string $path): void
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

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin']);
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        redirect('admin.php');
    }
}

function is_loja_fechada(): bool
{
    return (bool) config_value('delivery.loja_fechada', false);
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
    return (bool) file_put_contents(
        pedidos_sync_file(),
        json_encode(array_values($pedidos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
}

function append_pedido_sync(array $pedido): void
{
    $pedidos = load_pedidos_sync();
    $pedidos[] = $pedido;
    save_pedidos_sync($pedidos);
}
