<?php
session_start();
require_once '../config/db.php';
require_once '../includes/log_helper.php';

// Proteção: Apenas administradores com permissão podem acessar
$tipo_admin = $_SESSION['admin_tipo'] ?? '';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin' || !in_array($tipo_admin, ['adminmaster', 'admmoderador'])) {
    $_SESSION['mensagem_erro'] = "Acesso negado.";
    header("Location: ../pages/login.php");
    exit();
}

$id = $_GET['id'] ?? null;
$tabela = $_GET['tabela'] ?? null;

// Validação dos parâmetros
$tabelas_permitidas = ['conteudo_pagina_inicial']; // Removido 'blocos_conteudo'
$tabelas_permitidas = ['conteudo_pagina_inicial', 'blocos_conteudo'];
if (!is_numeric($id) || !$tabela || !in_array($tabela, $tabelas_permitidas)) {
    $_SESSION['mensagem_erro'] = "Parâmetros inválidos para exclusão.";
    header("Location: gerir_pagina_inicial.php");
    exit();
}

try {
    $pdo = obterConexaoPDO();

    // Pega o título para o log antes de deletar
    $coluna_titulo = ($tabela === 'blocos_conteudo') ? 'titulo_admin' : 'titulo';
    $stmt_info = $pdo->prepare("SELECT $coluna_titulo FROM `$tabela` WHERE id = ?");
    $stmt_info->execute([$id]);
    $titulo_item = $stmt_info->fetchColumn();

    // Deleta o item
    $stmt = $pdo->prepare("DELETE FROM `$tabela` WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        registrar_log_admin($_SESSION['usuario_id'], "Excluiu o item de conteúdo '$titulo_item' (ID: $id) da tabela '$tabela'.", ['item_id' => $id, 'tabela' => $tabela]);
        $_SESSION['mensagem_sucesso'] = "Item de conteúdo excluído com sucesso!";
    } else {
        $_SESSION['mensagem_erro'] = "Item não encontrado ou já foi excluído.";
    }

} catch (PDOException $e) {
    $_SESSION['mensagem_erro'] = "Erro ao excluir o item de conteúdo.";
    error_log("Erro em excluir_conteudo_pagina.php: " . $e->getMessage());
}

header("Location: gerir_pagina_inicial.php");
exit();