<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Segurança: Apenas usuários logados podem acessar
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$tipo_usuario = $_SESSION['usuario_tipo'];

try {
    $pdo = obterConexaoPDO();
    $sql = "";

    // Define a coluna e o status a serem atualizados com base no tipo de usuário
    if ($tipo_usuario === 'prestador') {
        // Marca como lidas as notificações de agendamentos pendentes
        $sql = "UPDATE Agendamento SET notificacao_prestador_lida = TRUE WHERE Prestador_id = ? AND status = 'pendente'";
        $params = [$id_usuario];
    } elseif ($tipo_usuario === 'cliente') {
        // Marca como lidas as notificações de agendamentos aceitos
        $sql = "UPDATE Agendamento SET notificacao_cliente_lida = TRUE WHERE Cliente_id = ? AND status = 'aceito'";
        $params = [$id_usuario];
    } elseif ($tipo_usuario === 'admin') {
        // Marca como lidas as notificações de agendamentos realizados
        $sql = "UPDATE Agendamento SET notificacao_admin_lida = TRUE WHERE status = 'realizado'";
        $params = []; // Admin vê todas
    }

    if (!empty($sql)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

} catch (PDOException $e) {
    error_log("Erro ao marcar notificações como lidas: " . $e->getMessage());
    // Não redireciona com erro para não confundir o usuário, apenas registra o log.
}

// Redireciona de volta para a página anterior ou para o dashboard
$redirect_url = $_SERVER['HTTP_REFERER'] ?? '../index.php';
header("Location: " . $redirect_url);
exit();
