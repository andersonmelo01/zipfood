<?php
require_once __DIR__ . '/conexao.php';
require_module_access('produtos');

$dir = __DIR__ . '/img/';
$publicPrefix = 'img/';
$arquivos = [];

if (is_dir($dir)) {
    foreach (scandir($dir) as $file) {
        if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'webp'])) {
            $arquivos[] = $publicPrefix . $file;
        }
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($arquivos);
