<?php
require_once __DIR__ . '/conexao.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT status FROM pedidos WHERE id = ?");
$stmt->execute([$id]);

json_response($stmt->fetch(PDO::FETCH_ASSOC) ?: ['status' => null]);
