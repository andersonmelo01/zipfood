<?php
require_once __DIR__ . '/conexao.php';
require_module_access('produtos');

header('Content-Type: application/json; charset=utf-8');

$pasta = __DIR__ . '/img/';
$publicPrefix = 'img/';

if (!is_dir($pasta)) {
    mkdir($pasta, 0755, true);
}

if (isset($_FILES['imagem'])) {
    $tmpPath = (string) ($_FILES['imagem']['tmp_name'] ?? '');
    $fileSize = (int) ($_FILES['imagem']['size'] ?? 0);
    $originalName = (string) ($_FILES['imagem']['name'] ?? '');
    $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
    $mime = $tmpPath !== '' ? (string) mime_content_type($tmpPath) : '';

    if (!in_array($extension, $permitidas, true) || !str_starts_with($mime, 'image/') || $fileSize > 5 * 1024 * 1024) {
        echo json_encode(['erro' => true, 'mensagem' => 'Arquivo invalido.']);
        exit;
    }

    $baseName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($originalName));
    $nome = time() . '_' . $baseName;
    $destino = $pasta . $nome;

    if (move_uploaded_file($tmpPath, $destino)) {
        echo json_encode(['url' => $publicPrefix . $nome]);
        exit;
    }
}

echo json_encode(['erro' => true]);
