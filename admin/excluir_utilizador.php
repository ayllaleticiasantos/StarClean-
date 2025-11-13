<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['tipo'])) {
    $id = $_GET['id'];
    $tipo = $_GET['tipo'];

    $tabela = '';
    if ($tipo === 'cliente') {
        $tabela = 'Cliente';
    } elseif ($tipo === 'prestador') {
        $tabela = 'Prestador';
    }

    if ($tabela) {
        try {
            $pdo = obterConexaoPDO();
            $stmt = $pdo->prepare("DELETE FROM `$tabela` WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['mensagem_sucesso'] = "Utilizador excluído com sucesso!";

        } catch (PDOException $e) {
            $_SESSION['mensagem_erro'] = "Erro ao excluir o utilizador.";
        }
    } else {
        $_SESSION['mensagem_erro'] = "Tipo de utilizador inválido.";
    }
} else {
    $_SESSION['mensagem_erro'] = "Parâmetros inválidos para exclusão.";
}
    registrar_log_admin($id_admin_logado, "Excluiu um utilizador do tipo $tipo com ID $id.");
header("Location: gerir_utilizadores.php");
exit();
?>