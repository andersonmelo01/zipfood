<?php
header('Content-Type: application/json');

$total = $_GET['total'] ?? 0;

if ($total <= 0) {
    echo json_encode(['erro' => 'Valor inválido']);
    exit;
}

$token = "SEU_TOKEN_INFINITEPAY";

$data = [
    "amount" => floatval($total),
    "description" => "Pedido Verdadeiro X-Tudo",
    "payment_method" => "pix"
];

$ch = curl_init("https://api.infinitepay.io/v1/charges");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
