<?php
// Carrega a configuração da BASE_URL, se necessário
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}
// session_start() deve ser chamada na primeira linha de CADA ARQUIVO PHP que utiliza sessões.
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StarClean - Sistema de Limpeza</title>

    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/img/favcon_starclean.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/includes/star_clean.css">

    <script src="https://code.jquery.com/jquery-3.7.1.slim.js" integrity="sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc=" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css" />

    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>


</head>

<body class="d-flex flex-column min-vh-100" style="background-color: #acd0f5ff;">

    <?php
    // Inicia a sessão se ainda não tiver sido iniciada para verificar o login
    if (session_status() === PHP_SESSION_NONE) {
        // Nada aqui, session_start() está no topo de cada script
    }
    ?>