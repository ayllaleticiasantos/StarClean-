<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';

// Segurança: Apenas usuários logados podem acessar
if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/pages/login.php");
    exit();
}

$id_notificacao = $_GET['id'] ?? null;
$id_usuario = $_SESSION['usuario_id'];
$tipo_usuario = $_SESSION['usuario_tipo'];

$link_destino_padrao = BASE_URL . '/' . $tipo_usuario . '/dashboard.php';

if (!$id_notificacao || !is_numeric($id_notificacao)) {
    header("Location: " . $link_destino_padrao);
    exit();
}

try {
    $pdo = obterConexaoPDO();

    // Busca o link de destino ANTES de marcar como lida
    $stmt_link = $pdo->prepare("SELECT link FROM notificacoes WHERE id = ? AND usuario_id = ?");
    $stmt_link->execute([$id_notificacao, $id_usuario]);
    $link = $stmt_link->fetchColumn();

    // Marca a notificação como lida
    $stmt_update = $pdo->prepare("UPDATE notificacoes SET lida = TRUE WHERE id = ? AND usuario_id = ?");
    $stmt_update->execute([$id_notificacao, $id_usuario]);

    // Redireciona para o link correto ou para o padrão se não encontrar
    header("Location: " . ($link ? BASE_URL . '/' . $link : $link_destino_padrao));
    exit();

} catch (PDOException $e) {
    error_log("Erro ao processar notificação: " . $e->getMessage());
    header("Location: " . $link_destino_padrao);
    exit();
}