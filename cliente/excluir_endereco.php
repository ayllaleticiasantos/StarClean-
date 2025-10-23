<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas clientes podem acessar esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    header("Location: ../pages/login.php");
    exit();
}

// 1. Verifica se o ID do endereço foi passado
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_endereco = $_GET['id'];
    $id_cliente = $_SESSION['usuario_id'];

    try {
        $pdo = obterConexaoPDO();
        
        // 2. Executa a exclusão. A cláusula WHERE Cliente_id = ? 
        // garante que apenas o próprio cliente possa excluir seus endereços.
        $stmt = $pdo->prepare("DELETE FROM Endereco WHERE id = ? AND Cliente_id = ?");
        $stmt->execute([$id_endereco, $id_cliente]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['mensagem_sucesso'] = "Endereço excluído com sucesso!";
        } else {
            // Se rowCount for 0, o endereço não existia ou não pertencia a este cliente
            $_SESSION['mensagem_erro'] = "Erro ao excluir o endereço. Endereço não encontrado ou acesso negado.";
        }

    } catch (PDOException $e) {
        $_SESSION['mensagem_erro'] = "Erro ao processar a exclusão: " . $e->getMessage();
        // Para depuração: error_log($e->getMessage());
    }

} else {
    $_SESSION['mensagem_erro'] = "ID de endereço inválido ou não fornecido.";
}

// 3. Redireciona de volta para a página de gerenciamento de endereços
header("Location: gerir_enderecos.php");
exit();
?>