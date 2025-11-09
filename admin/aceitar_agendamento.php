<?php
session_start();
require_once '../config/db.php';
require_once '../includes/log_helper.php';

// Segurança: Apenas administradores podem executar esta ação
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$id_admin_logado = $_SESSION['usuario_id'];

// Verifica se o ID do agendamento foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensagem_erro'] = "ID do agendamento não fornecido.";
    header("Location: gerir_agendamentos.php");
    exit();
}

$agendamento_id = $_GET['id'];

try {
    $pdo = obterConexaoPDO();
    // Atualiza o status do agendamento para 'aceito'
    $stmt = $pdo->prepare("UPDATE Agendamento SET status = 'aceito' WHERE id = ? AND status = 'pendente'");
    $stmt->execute([$agendamento_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['mensagem_sucesso'] = "Agendamento aceito com sucesso em nome do prestador.";
        // Registrar a ação no log de atividades
        registrar_log_admin($id_admin_logado, "Aceitou o agendamento ID: " . $agendamento_id, ['agendamento_id' => $agendamento_id]);
    } else {
        $_SESSION['mensagem_info'] = "O agendamento não estava pendente ou não foi encontrado.";
    }
} catch (PDOException $e) {
    $_SESSION['mensagem_erro'] = "Erro ao aceitar o agendamento.";
    error_log("Erro ao aceitar agendamento (admin): " . $e->getMessage());
}

// Redireciona de volta para a página de gestão de agendamentos
header("Location: gerir_agendamentos.php");
exit();
?>