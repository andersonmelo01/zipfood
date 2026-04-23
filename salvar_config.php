<?php

require_once __DIR__ . '/conexao.php';
require_admin();

$config = [
    'taxa_entrega' => (float) ($_POST['taxa_entrega'] ?? 5),
    'taxa_ativa' => isset($_POST['taxa_ativa']),
    'loja_fechada' => isset($_POST['loja_fechada']),
];

file_put_contents(__DIR__ . '/config.json', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

redirect('dashboard.php');

