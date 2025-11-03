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
    if ($acao === 'aceito') {
        $novo_status = 'aceito';
        $_SESSION['mensagem_sucesso'] = "Agendamento aceito com sucesso!";
    } elseif ($acao === 'cancelado') {
        $novo_status = 'cancelado';
        $_SESSION['mensagem_sucesso'] = "Agendamento recusado com sucesso.";
    } elseif ($acao === 'realizado') {
        $novo_status = 'realizado';
        $_SESSION['mensagem_sucesso'] = "Serviço marcado como concluído!";
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