<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas administradores podem executar esta ação
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    // Se não for admin, redireciona para o login
    header("Location: ../pages/login.php");
    exit();
}

// Verifica se os parâmetros necessários (id e tipo) foram passados na URL
if (isset($_GET['id']) && isset($_GET['tipo'])) {
    $id = $_GET['id'];
    $tipo = $_GET['tipo'];

    // Determina a tabela correta com base no tipo
    $tabela = '';
    if ($tipo === 'cliente') {
        $tabela = 'Cliente';
    } elseif ($tipo === 'prestador') {
        $tabela = 'Prestador';
    }

    // Se a tabela for válida, executa a exclusão
    if ($tabela) {
        try {
            $pdo = obterConexaoPDO();
            $stmt = $pdo->prepare("DELETE FROM `$tabela` WHERE id = ?");
            $stmt->execute([$id]);

            // Define uma mensagem de sucesso para ser exibida na página de gestão
            $_SESSION['mensagem_sucesso'] = "Utilizador excluído com sucesso!";

        } catch (PDOException $e) {
            // Em caso de erro, define uma mensagem de erro
            $_SESSION['mensagem_erro'] = "Erro ao excluir o utilizador.";
            // Para depuração: error_log($e->getMessage());
        }
    } else {
        $_SESSION['mensagem_erro'] = "Tipo de utilizador inválido.";
    }
} else {
    $_SESSION['mensagem_erro'] = "Parâmetros inválidos para exclusão.";
}

// Redireciona de volta para a página de gestão de utilizadores
header("Location: gerir_utilizadores.php");
exit();
?>