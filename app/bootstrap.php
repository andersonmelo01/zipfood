<?php

require_once __DIR__ . '/helpers.php';

$timezone = (string) config_value('app.timezone', 'America/Sao_Paulo');
date_default_timezone_set($timezone);

$debug = (bool) config_value('app.debug', true);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');

if (session_status() === PHP_SESSION_NONE) {
    $https = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443)
    );

    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
