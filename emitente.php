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
            'site' => ''
        ];
    }
    $json = file_get_contents($arquivo);
    return json_decode($json, true);
}

function salvar_emitente($dados) {
    $arquivo = 'emitente.json';
    file_put_contents($arquivo, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>
