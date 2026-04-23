<?php

require_once __DIR__ . '/conexao.php';

$arquivo = __DIR__ . '/feedbacks.json';
$dados = json_decode((string) file_get_contents('php://input'), true);

if (!is_array($dados)) {
    json_response(['status' => 'erro', 'message' => 'Payload inválido.'], 422);
}

$feedbacks = file_exists($arquivo) ? json_decode((string) file_get_contents($arquivo), true) : [];
$feedbacks = is_array($feedbacks) ? $feedbacks : [];

$feedbacks[] = [
    'nome' => trim((string) ($dados['nome'] ?? 'Cliente')),
    'nota' => (int) ($dados['nota'] ?? 0),
    'comentario' => trim((string) ($dados['comentario'] ?? ($dados['mensagem'] ?? ''))),
    'data' => date('d/m/Y H:i'),
];

file_put_contents($arquivo, json_encode($feedbacks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

json_response(['status' => 'ok']);

