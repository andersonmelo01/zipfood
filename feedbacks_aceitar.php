<?php
// Marca um feedback como aprovado (visível para clientes)
require_once __DIR__ . '/conexao.php';
require_admin();

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? '';
if ($id) {
    $stmt = $pdo->prepare("UPDATE feedbacks SET aprovado = 1, sugestao = 0 WHERE id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['ok' => true]);
        exit;
    }
}
echo json_encode(['ok' => false]);
http_response_code(400);
