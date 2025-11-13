<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if ($id == $_SESSION['usuario_id']) {
        $_SESSION['mensagem_erro'] = "Você não pode excluir sua própria conta de administrador.";
        header("Location: gerenciar_adm.php");
        exit();
    }

    try {
        $pdo = obterConexaoPDO();
        $stmt = $pdo->prepare("DELETE FROM Administrador WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['mensagem_sucesso'] = "Administrador excluído com sucesso!";
        } else {
            $_SESSION['mensagem_erro'] = "Administrador não encontrado ou já foi excluído.";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem_erro'] = "Erro ao excluir o administrador.";
        error_log("Erro ao excluir admin: " . $e->getMessage());
    }
} else {
    $_SESSION['mensagem_erro'] = "ID do administrador não fornecido.";
}

header("Location: gerenciar_adm.php");
exit();
?>