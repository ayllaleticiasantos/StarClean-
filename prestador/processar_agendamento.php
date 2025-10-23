<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas prestadores podem aceder
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header("Location: ../pages/login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['acao'])) {
    $id_agendamento = $_GET['id'];
    $acao = $_GET['acao'];

    $novo_status = '';
    if ($acao === 'aceitar') {
        $novo_status = 'aceito'; // Corrigido para "aceito"
        $_SESSION['mensagem_sucesso'] = "Agendamento aceito com sucesso!";
    } elseif ($acao === 'recusar') {
        $novo_status = 'cancelado';
        $_SESSION['mensagem_erro'] = "Agendamento recusado.";
    }

    if ($novo_status !== '') {
        try {
            $pdo = obterConexaoPDO();
            $stmt = $pdo->prepare("UPDATE Agendamento SET status = ? WHERE id = ? AND Prestador_id = ?");
            $stmt->execute([$novo_status, $id_agendamento, $_SESSION['usuario_id']]);
        } catch (PDOException $e) {
            $_SESSION['mensagem_erro'] = "Erro ao atualizar o agendamento.";
            error_log($e->getMessage());
        }
    }
}

header("Location: gerir_agendamentos.php");
exit();
?>