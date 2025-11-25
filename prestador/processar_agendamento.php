<?php
session_start();
require_once '../config/db.php';
require_once '../includes/log_helper.php';
 
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header("Location: ../pages/login.php");
    exit();
}

$id_prestador = $_SESSION['usuario_id'];

// Unifica a captura de dados de POST e GET para compatibilidade
$id_agendamento = $_REQUEST['agendamento_id'] ?? $_REQUEST['id'] ?? null;
$acao = $_REQUEST['acao'] ?? null;


if (!$id_agendamento || !$acao) {
    $_SESSION['mensagem_erro'] = "Requisição inválida.";
    header("Location: gerir_agendamentos.php");
    exit();
}

$novo_status = '';
$mensagem_sucesso = '';

try {
    $pdo = obterConexaoPDO();

    if ($acao === 'aceitar') {
        $stmt_info = $pdo->prepare("SELECT data, hora FROM Agendamento WHERE id = ?");
        $stmt_info->execute([$id_agendamento]);
        $agendamento_info = $stmt_info->fetch();

        if ($agendamento_info) {
            $stmt_check = $pdo->prepare(
                "SELECT COUNT(*) FROM Agendamento WHERE Prestador_id = ? AND data = ? AND hora = ? AND status = 'aceito' AND id != ?"
            );
            $stmt_check->execute([$id_prestador, $agendamento_info['data'], $agendamento_info['hora'], $id_agendamento]);
            $conflitos = $stmt_check->fetchColumn();

            if ($conflitos > 0) {
                $_SESSION['mensagem_erro'] = "Não foi possível aceitar. Você já possui outro serviço aceito neste mesmo dia e horário.";
                header("Location: gerir_agendamentos.php");
                exit();
            }
        }
    }
    
    switch ($acao) {
        case 'aceitar':
            $novo_status = 'aceito';
            $mensagem_sucesso = "Agendamento aceito com sucesso!";
            break;
        case 'recusar':
            $novo_status = 'cancelado';
            $mensagem_sucesso = "Agendamento recusado com sucesso.";
            break;
        case 'realizado':
            $novo_status = 'realizado';
            $mensagem_sucesso = "Serviço marcado como concluído!";
            break;
        case 'cancelado':
            $novo_status = 'cancelado';
            $mensagem_sucesso = "Agendamento cancelado com sucesso.";
            break;
    }

    if ($novo_status) {
        $stmt = $pdo->prepare("UPDATE Agendamento SET status = ? WHERE id = ? AND Prestador_id = ?");
        $stmt->execute([$novo_status, $id_agendamento, $id_prestador]);

        registrar_log_usuario('prestador', $id_prestador, "Alterou o status do agendamento para '$novo_status'", ['agendamento_id' => $id_agendamento]);
        
        $_SESSION['mensagem_sucesso'] = $mensagem_sucesso;

    } else {
        $_SESSION['mensagem_erro'] = "Ação desconhecida.";
    }

} catch (PDOException $e) {
    $_SESSION['mensagem_erro'] = "Erro ao atualizar o agendamento.";
    error_log($e->getMessage());
}

header("Location: gerir_agendamentos.php");
exit();
?>