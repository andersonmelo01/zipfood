<?php

require_once __DIR__ . '/helpers.php';

$timezone = (string) config_value('app.timezone', 'America/Sao_Paulo');
date_default_timezone_set($timezone);

$debug = (bool) config_value('app.debug', true);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

