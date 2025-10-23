<?php
// Defina a senha que você quer usar
$senha_texto_puro = 'admin123';

// Gera o hash da senha
$hash_da_senha = password_hash($senha_texto_puro, PASSWORD_DEFAULT);

// Exibe o hash
echo "A sua senha em texto puro é: " . $senha_texto_puro . "<br>";
echo "Copie este hash para o banco de dados: " . $hash_da_senha;
?>