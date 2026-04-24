<?php
require_once 'conexao.php';
header('Content-Type: application/json');

// Exemplo: tabela pedidos (id, cliente, status, valor)
$sql = "SELECT id, cliente, status, valor FROM pedidos ORDER BY id DESC LIMIT 20";
$result = $conn->query($sql);

$pedidos = array();
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
}

echo json_encode($pedidos);
$conn->close();
