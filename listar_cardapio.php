<?php
require_once 'conexao.php';
header('Content-Type: application/json');

// Exemplo: tabela produtos (id, nome, descricao, preco)
$sql = "SELECT id, nome, descricao, preco FROM produtos";
$result = $conn->query($sql);

$cardapio = array();
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $cardapio[] = $row;
    }
}

echo json_encode($cardapio);
$conn->close();
