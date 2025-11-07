<?php

/**
 * Verifica se uma senha atende aos critérios de segurança definidos.
 *
 * @param string $senha A senha a ser validada.
 * @return array Retorna um array vazio se a senha for forte, ou um array de strings com as mensagens de erro.
 */
function validarSenhaForte(string $senha): array
{
    $erros = [];

    // 1. Verifica o comprimento mínimo
    if (strlen($senha) < 8) {
        $erros[] = "A senha deve ter no mínimo 8 caracteres.";
    }
    // 2. Verifica se contém pelo menos uma letra maiúscula
    if (!preg_match('/[A-Z]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos uma letra maiúscula.";
    }
    // 3. Verifica se contém pelo menos uma letra minúscula
    if (!preg_match('/[a-z]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos uma letra minúscula.";
    }
    // 4. Verifica se contém pelo menos um número
    if (!preg_match('/[0-9]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos um número.";
    }
    // 5. Verifica se contém pelo menos um caractere especial
    if (!preg_match('/[\W_]/', $senha)) { // \W corresponde a qualquer caractere que não seja letra ou número
        $erros[] = "A senha deve conter pelo menos um caractere especial (ex: !@#$%).";
    }

    return $erros;
}