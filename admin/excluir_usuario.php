<?php
session_start();
require_once '../config/db.php';
require_once '../includes/log_helper.php';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$id_admin_logado = $_SESSION['usuario_id'];
$usuario_id = $_GET['id'] ?? null;
$tipo = $_GET['tipo'] ?? null;

if (!$usuario_id || !$tipo || !in_array($tipo, ['cliente', 'prestador'])) {
    $_SESSION['mensagem_erro'] = "Parâmetros inválidos para exclusão.";
    header("Location: gerir_utilizadores.php");
    exit();
}

$tabela = $tipo;

try {
    $pdo = obterConexaoPDO();

    $stmt_select = $pdo->prepare("SELECT id, nome, email FROM `$tabela` WHERE id = ?");
    $stmt_select->execute([$usuario_id]);
    $usuario_para_deletar = $stmt_select->fetch(PDO::FETCH_ASSOC);

    if ($usuario_para_deletar) {
        $stmt_delete = $pdo->prepare(query: "DELETE FROM `$tabela` WHERE id = ?");
        $stmt_delete->execute([$usuario_id]);

        $detalhes_log = [
            'usuario_deletado_id' => $usuario_para_deletar['id'],
            'nome' => $usuario_para_deletar['nome'],
            'email' => $usuario_para_deletar['email'],
            'tipo' => $tipo
        ];

        registrar_log_admin($id_admin_logado, "Deletou um usuário do tipo '$tipo'", $detalhes_log);

        $_SESSION['mensagem_sucesso'] = "Usuário deletado com sucesso!";
    } else {
        $_SESSION['mensagem_erro'] = "Usuário não encontrado para exclusão.";
    }

} catch (PDOException $e) {
    $_SESSION['mensagem_erro'] = "Erro ao deletar o usuário. Pode haver agendamentos ou outros dados vinculados a ele.";
    error_log("Erro ao deletar usuário: " . $e->getMessage());
}

header("Location: gerir_utilizadores.php");
exit();