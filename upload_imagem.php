<?php
$pasta = 'img/';

if (!is_dir($pasta)) {
    mkdir($pasta, 0777, true);
}

if (isset($_FILES['imagem'])) {
    $nome = time() . '_' . basename($_FILES['imagem']['name']);
    $destino = $pasta . $nome;

    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
        echo json_encode(['url' => $destino]);
        exit;
    }
}

echo json_encode(['erro' => true]);
