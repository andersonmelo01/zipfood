<?php
// Funções para ler e salvar dados do emitente
function ler_emitente() {
    $arquivo = 'emitente.json';
    if (!file_exists($arquivo)) {
        return [
            'nome' => '',
            'cnpj' => '',
            'endereco' => '',
            'telefone' => '',
            'site' => '',
            'validade' => ''
        ];
    }
    $json = file_get_contents($arquivo);
    $dados = json_decode($json, true);
    if (!isset($dados['validade'])) {
        $dados['validade'] = '';
    }
    return $dados;
}

function salvar_emitente($dados) {
    $arquivo = 'emitente.json';
    if (!isset($dados['validade'])) {
        $dados['validade'] = '';
    }
    file_put_contents($arquivo, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>
