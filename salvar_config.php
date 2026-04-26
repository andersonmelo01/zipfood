<?php

require_once __DIR__ . '/conexao.php';
// Permitir gerente ou admin acessar configurações
$user = current_user();
$role = normalize_role((string)($user['papel'] ?? ''));
if (!in_array($role, ['admin', 'gerente'], true)) {
    forbidden_response('Somente administradores ou gerentes podem acessar esta área.');
}

$config = [
    'taxa_entrega' => (float) ($_POST['taxa_entrega'] ?? 5),
    'taxa_ativa' => isset($_POST['taxa_ativa']),
    'loja_fechada' => isset($_POST['loja_fechada']),
];

save_runtime_config($config);

redirect('dashboard.php');
