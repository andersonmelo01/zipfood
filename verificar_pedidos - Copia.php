<?php
header('Content-Type: application/json');

$pedidos = file_exists('pedidos.json')
    ? json_decode(file_get_contents('pedidos.json'), true)
    : [];

// Recebe filtro por GET
$dataInicio = $_GET['data_inicio'] ?? date('Y-m-d');
$dataFim    = $_GET['data_fim'] ?? date('Y-m-d');

// Converter para DateTime com horas
$dataInicioObj = DateTime::createFromFormat('Y-m-d H:i', $dataInicio . ' 00:00');
$dataFimObj    = DateTime::createFromFormat('Y-m-d H:i', $dataFim . ' 23:59');

$contagem = 0;
$ultimoId = null;

foreach ($pedidos as $index => $p) {
    $dataPedido = DateTime::createFromFormat('d/m/Y H:i', $p['data']);
    if (!$dataPedido) continue;

    if ($dataPedido >= $dataInicioObj && $dataPedido <= $dataFimObj) {
        $contagem++;
        $ultimoId = $index; // pega o índice do último pedido válido
    }
}

echo json_encode([
    'total' => $contagem,
    'ultimo_id' => $ultimoId
]);
