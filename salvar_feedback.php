<?php

require_once __DIR__ . '/conexao.php';

$dados = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($dados)) {
    json_response(['status' => 'erro', 'message' => 'Payload inválido.'], 422);
}

$nome = trim((string) ($dados['nome'] ?? 'Cliente'));
$comentario = trim((string) ($dados['comentario'] ?? ($dados['mensagem'] ?? '')));
$estrelas = isset($dados['nota']) ? (int)$dados['nota'] : (isset($dados['estrelas']) ? (int)$dados['estrelas'] : 5);
$id = uniqid('fb_', true);

$sql = "INSERT INTO feedbacks (id, nome, comentario, estrelas, aprovado, data) VALUES (?, ?, ?, ?, 0, NOW())";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id, $nome, $comentario, $estrelas]);

json_response(['status' => 'ok']);

