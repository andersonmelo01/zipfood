<?php

require_once __DIR__ . '/conexao.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['remover'])) {
    $idRemover = (int) $_GET['remover'];

    if ($idRemover > 0) {
        $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = ?');
        $stmt->execute([$idRemover]);
    }

    redirect('painel.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método inválido');
}

$id = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;

$codigo_produto = trim((string) ($_POST['codigo_produto'] ?? ''));
$codigo_barras = trim((string) ($_POST['codigo_barras'] ?? ''));
$nome = trim((string) ($_POST['nome'] ?? ''));
$categoria = trim((string) ($_POST['categoria'] ?? 'Sem Categoria'));
$un = strtoupper(trim((string) ($_POST['un'] ?? 'UN')));
$preco = (float) ($_POST['preco'] ?? 0);
$preco_custo = (float) ($_POST['preco_custo'] ?? 0);
$imagem = trim((string) ($_POST['imagem'] ?? ''));
$preco_promocional = (float) ($_POST['preco_promocional'] ?? 0);
$promo_ativa = isset($_POST['promo_ativa']) ? 1 : 0;
if ($promo_ativa && ($preco_promocional <= 0 || $preco_promocional >= $preco)) {
    $preco_promocional = round($preco * 0.90, 2);
}

$ncm = trim((string) ($_POST['ncm'] ?? ''));
$cfop = trim((string) ($_POST['cfop'] ?? ''));
$cst_icms = trim((string) ($_POST['cst_icms'] ?? ''));
$aliquota_icms = (float) ($_POST['aliquota_icms'] ?? 0);
$aliquota_pis = (float) ($_POST['aliquota_pis'] ?? 0);
$aliquota_cofins = (float) ($_POST['aliquota_cofins'] ?? 0);
$disponivel = isset($_POST['disponivel']) ? 1 : 0;

if ($nome === '' || $codigo_produto === '' || $preco <= 0) {
    exit('Dados do produto inválidos.');
}

if ($id) {
    $sql = "UPDATE produtos SET
        codigo_produto = ?,
        codigo_barras = ?,
        nome = ?,
        categoria = ?,
        un = ?,
        preco = ?,
        preco_promocional = ?,
        preco_custo = ?,
        imagem = ?,
        disponivel = ?,
        promo_ativa = ?,
        ncm = ?,
        cfop = ?,
        cst_icms = ?,
        aliquota_icms = ?,
        aliquota_pis = ?,
        aliquota_cofins = ?
        WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $codigo_produto,
        $codigo_barras,
        $nome,
        $categoria,
        $un,
        $preco,
        $preco_promocional,
        $preco_custo,
        $imagem,
        $disponivel,
        $promo_ativa,
        $ncm,
        $cfop,
        $cst_icms,
        $aliquota_icms,
        $aliquota_pis,
        $aliquota_cofins,
        $id,
    ]);
} else {
    $sql = "INSERT INTO produtos
        (codigo_produto, codigo_barras, nome, categoria, un, preco, preco_promocional, preco_custo, imagem, disponivel, promo_ativa,
         ncm, cfop, cst_icms, aliquota_icms, aliquota_pis, aliquota_cofins)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $codigo_produto,
        $codigo_barras,
        $nome,
        $categoria,
        $un,
        $preco,
        $preco_promocional,
        $preco_custo,
        $imagem,
        $disponivel,
        $promo_ativa,
        $ncm,
        $cfop,
        $cst_icms,
        $aliquota_icms,
        $aliquota_pis,
        $aliquota_cofins,
    ]);
}

redirect('painel.php');
