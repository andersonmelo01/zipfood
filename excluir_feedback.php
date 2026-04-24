<?php

require_once __DIR__ . '/conexao.php';
require_admin();

$data = json_decode((string) file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM feedbacks WHERE id = ?");
    $stmt->execute([$id]);
}
json_response(['ok' => true]);

