<?php
session_start();
require_once '../config/db.php';
require_once '../includes/log_helper.php';

$tipo_admin = $_SESSION['admin_tipo'] ?? '';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin' || !in_array($tipo_admin, ['adminmaster', 'admmoderador'])) {
    $_SESSION['mensagem_erro'] = "Acesso negado.";
    header("Location: ../pages/login.php");
    exit();
}

$servico_id = $_GET['id'] ?? null;
$novo_status = isset($_GET['ocultar']) ? (int)$_GET['ocultar'] : null;

if ($servico_id === null || $novo_status === null || !in_array($novo_status, [0, 1])) {
    $_SESSION['mensagem_erro'] = "Parâmetros inválidos para alterar a visibilidade.";
    header("Location: gerir_pagina_inicial.php");
    exit();
}

try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->prepare("UPDATE Servico SET oculto = ? WHERE id = ?");
    $stmt->execute([$novo_status, $servico_id]);

    $acao_log = $novo_status == 1 ? 'Ocultou' : 'Tornou visível';
    registrar_log_admin($_SESSION['usuario_id'], "$acao_log o serviço ID: $servico_id", ['servico_id' => $servico_id]);

    $_SESSION['mensagem_sucesso'] = "Visibilidade do serviço alterada com sucesso!";

} catch (PDOException $e) {
    $_SESSION['mensagem_erro'] = "Erro ao alterar a visibilidade do serviço.";
    error_log("Erro em toggle_servico_visibilidade.php: " . $e->getMessage());
}

header("Location: gerir_pagina_inicial.php#servicos-tab");
exit();