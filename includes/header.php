<?php
// Carrega a configuração da BASE_URL, se necessário
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StarClean - Sistema de Limpeza</title>

    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/img/Logo1-removebg-preview (1).png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/includes/star_clean.css">

</head>

<body class="d-flex flex-column min-vh-100" style="background-color: #acd0f5ff;">

    <?php
    // Inicia a sessão se ainda não tiver sido iniciada para verificar o login
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // // Inclui a navbar correta dependendo se o utilizador está logado ou não
    // if (isset($_SESSION['usuario_id'])) {
    //     include 'navbar_logged_in.php';
    // } else {
    //     include 'navbar.php';
    // }
    // ?>

    <div class="container-fluid flex-grow-1">
        <div class="row h-100">