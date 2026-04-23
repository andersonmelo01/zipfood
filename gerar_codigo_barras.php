<?php
require 'conexao.php';

// Calcula dígito verificador EAN-13
function calcularDigitoEAN13($codigo12)
{
    $soma = 0;
    for ($i = 0; $i < 12; $i++) {
        $num = intval($codigo12[$i]);
        $soma += ($i % 2 == 0) ? $num : $num * 3;
    }
    $resto = $soma % 10;
    return ($resto == 0) ? 0 : (10 - $resto);
}

function gerarCodigoBarrasUnico($pdo)
{
    do {
        // Prefixo brasileiro 789 + 9 dígitos aleatórios = 12 dígitos
        $base = '789';
        for ($i = 0; $i < 9; $i++) {
            $base .= rand(0, 9);
        }

        // Calcula dígito verificador EAN-13
        $digito = calcularDigitoEAN13($base);
        $codigo = $base . $digito;

        // Verifica se já existe no banco
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE codigo_barras = ?");
        $stmt->execute([$codigo]);
        $existe = $stmt->fetchColumn();
    } while ($existe > 0);

    return $codigo;
}

$codigo = gerarCodigoBarrasUnico($pdo);

echo json_encode([
    'codigo' => $codigo
]);
