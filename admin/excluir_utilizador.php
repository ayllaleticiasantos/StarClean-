<?php
session_start();
require_once '../config/db.php';
require_once '../includes/log_helper.php';
require_once '../config/enviar_email.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$id_admin_logado = $_SESSION['usuario_id'];

if (isset($_GET['id']) && isset($_GET['tipo'])) {
    $usuario_id = $_GET['id'];
    $tipo = $_GET['tipo'];

    $tabela = '';
    if ($tipo === 'cliente') {
        $tabela = 'Cliente';
    } elseif ($tipo === 'prestador') {
        $tabela = 'prestador'; // Corrigido para minúsculas para consistência
    }

    if ($tabela) {
        try {
            $pdo = obterConexaoPDO();

            $stmt_select = $pdo->prepare("SELECT id, nome, email FROM `$tabela` WHERE id = ?");
            $stmt_select->execute([$usuario_id]);
            $usuario_para_deletar = $stmt_select->fetch(PDO::FETCH_ASSOC);

            if ($usuario_para_deletar) {
                if ($tipo === 'prestador') {
                    $stmt_servicos = $pdo->prepare("SELECT COUNT(*) FROM servico WHERE prestador_id = ?");
                    $stmt_servicos->execute([$usuario_id]);
                    $num_servicos = $stmt_servicos->fetchColumn();

                    if ($num_servicos == 0) {
                        enviarEmailExclusaoPrestador($usuario_para_deletar['email'], $usuario_para_deletar['nome']);
                    }
                }

                $stmt_delete = $pdo->prepare("DELETE FROM `$tabela` WHERE id = ?");
                $stmt_delete->execute([$usuario_id]);

                registrar_log_admin($id_admin_logado, "Excluiu um utilizador do tipo $tipo com ID $usuario_id.");
                $_SESSION['mensagem_sucesso'] = "Utilizador excluído com sucesso!";
            } else {
                $_SESSION['mensagem_erro'] = "Utilizador não encontrado.";
            }

        } catch (PDOException $e) {
            $_SESSION['mensagem_erro'] = "Erro ao excluir o utilizador. Verifique se ele possui agendamentos ou outros dados vinculados.";
            error_log("Erro ao excluir utilizador: " . $e->getMessage());
        }
    } else {
        $_SESSION['mensagem_erro'] = "Tipo de utilizador inválido.";
    }
} else {
    $_SESSION['mensagem_erro'] = "Parâmetros inválidos para exclusão.";
}
header("Location: gerir_utilizadores.php");
exit();
?>