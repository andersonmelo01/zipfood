<?php

$senha = '123456';

// Gera o hash com bcrypt
$hash = password_hash($senha, PASSWORD_BCRYPT);

// Exibe o resultado
echo "Senha original: " . $senha . PHP_EOL;
echo "Hash gerado: " . $hash . PHP_EOL;
