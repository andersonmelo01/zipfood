<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/schema.php';

$db = config_value('database', []);

$host = $db['host'] ?? 'localhost';
$name = $db['name'] ?? 'zipfood';
$user = $db['user'] ?? 'adv';
$pass = $db['pass'] ?? 'gigalele';

try {
    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    ensure_schema($pdo);
} catch (PDOException $e) {
    http_response_code(500);

    if ((bool) config_value('app.debug', true)) {
        die('Erro na conexão: ' . $e->getMessage());
    }

    die('Erro ao conectar ao banco de dados.');
}

