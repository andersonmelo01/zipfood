<?php

require_once __DIR__ . '/conexao.php';
require_admin();

$data = json_decode((string) file_get_contents('php://input'), true);
$index = isset($data['index']) ? (int) $data['index'] : null;
$arquivo = __DIR__ . '/feedbacks.json';

if ($index !== null && file_exists($arquivo)) {
    $feedbacks = json_decode((string) file_get_contents($arquivo), true);

    if (is_array($feedbacks) && isset($feedbacks[$index])) {
        unset($feedbacks[$index]);
        $feedbacks = array_values($feedbacks);
        file_put_contents($arquivo, json_encode($feedbacks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

json_response(['ok' => true]);

