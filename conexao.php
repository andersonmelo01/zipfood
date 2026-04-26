<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/schema.php';

$db = config_value('database', []);

$host = (string) ($db['host'] ?? 'localhost');
$name = (string) ($db['name'] ?? 'zipfood');
$user = (string) ($db['user'] ?? 'adv');
$pass = (string) ($db['pass'] ?? 'gigalele');

if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
    http_response_code(500);
    die('Nome do banco de dados invalido.');
}

try {
    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        $unknownDatabase = str_contains(strtolower($e->getMessage()), 'unknown database');

        if (!$unknownDatabase) {
            throw $e;
        }

        $serverDsn = "mysql:host={$host};charset=utf8mb4";
        $serverPdo = new PDO($serverDsn, $user, $pass, $options);
        $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo = new PDO($dsn, $user, $pass, $options);
    }

    ensure_schema($pdo);
    refresh_logged_user($pdo);
} catch (PDOException $e) {
    http_response_code(500);

    if ((bool) config_value('app.debug', true)) {
        die('Erro na conexão: ' . $e->getMessage());
    }

    die('Erro ao conectar ao banco de dados.');
}
