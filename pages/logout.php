<?php
// 1. Inicia a sessão para poder acedê-la.
session_start();

// 2. Remove todas as variáveis da sessão.
$_SESSION = array();

// 3. Destrói a sessão por completo.
session_destroy();

// 4. Redireciona o utilizador para a página de login.
// O caminho '../pages/login.php' leva o utilizador de volta à página de login.
header("Location: login.php");

// 5. Garante que nenhum código adicional é executado após o redirecionamento.
exit();
?>