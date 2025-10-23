<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas prestadores podem aceder
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header("Location: ../pages/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_agendamento = $_GET['id'];
    $novo_status = 'realizado';

    try {
        $pdo = obterConexaoPDO();
        $stmt = $pdo->prepare("UPDATE Agendamento SET status = ? WHERE id = ? AND Prestador_id = ?");
        $stmt->execute([$novo_status, $id_agendamento, $_SESSION['usuario_id']]);

        $_SESSION['mensagem_sucesso'] = "Agendamento concluído com sucesso!";
    } catch (PDOException $e) {
        $_SESSION['mensagem_erro'] = "Erro ao concluir o agendamento.";
        error_log($e->getMessage());
    }
}

header("Location: gerir_agendamentos.php");
exit();
?>