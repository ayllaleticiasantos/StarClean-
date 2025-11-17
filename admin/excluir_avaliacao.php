<?php
session_start();
require_once '../config/db.php';
require_once '../includes/log_helper.php';

// Segurança: Apenas administradores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$id_avaliacao = $_GET['id'] ?? null;

if (!$id_avaliacao || !is_numeric($id_avaliacao)) {
    $_SESSION['mensagem_erro'] = "ID da avaliação inválido.";
    header("Location: gerir_avaliacoes.php");
    exit();
}

try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->prepare("DELETE FROM avaliacao_prestador WHERE id = ?");
    $stmt->execute([$id_avaliacao]);

    if ($stmt->rowCount() > 0) {
        registrar_log_admin($_SESSION['usuario_id'], "Excluiu a avaliação ID: $id_avaliacao", ['avaliacao_id' => $id_avaliacao]);
        $_SESSION['mensagem_sucesso'] = "Avaliação excluída com sucesso.";
    } else {
        $_SESSION['mensagem_erro'] = "Avaliação não encontrada ou já foi excluída.";
    }
} catch (PDOException $e) {
    $_SESSION['mensagem_erro'] = "Erro ao excluir a avaliação.";
    error_log("Erro ao excluir avaliação: " . $e->getMessage());
}

header("Location: gerir_avaliacoes.php");
exit();