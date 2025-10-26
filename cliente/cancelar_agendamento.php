<?php
session_start();
require_once '../config/db.php';

// 1. Segurança: Apenas clientes logados podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    $_SESSION['mensagem_erro'] = "Acesso negado. Faça login como cliente.";
    header("Location: ../pages/login.php");
    exit();
}

// 2. Validação do ID do Agendamento
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensagem_erro'] = "ID do agendamento não fornecido ou inválido.";
    header("Location: meus_agendamentos.php");
    exit();
}

$agendamento_id = $_GET['id'];
$cliente_id = $_SESSION['usuario_id'];

try {
    $pdo = obterConexaoPDO();
    
    // 3. Verificação de Propriedade e Status Atual
    // Verifica se o agendamento existe, pertence ao cliente logado
    // E se o status NÃO É "cancelado" ou "concluído" (não faz sentido cancelar algo que já passou ou já está cancelado)
    $stmt_check = $pdo->prepare(
        "SELECT status, data, hora FROM Agendamento 
         WHERE id = ? AND Cliente_id = ?"
    );
    $stmt_check->execute([$agendamento_id, $cliente_id]);
    $agendamento = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$agendamento) {
        $_SESSION['mensagem_erro'] = "Agendamento não encontrado ou não pertence a você.";
        header("Location: meus_agendamentos.php");
        exit();
    }
    
    // 4. Lógica de Cancelamento
    if ($agendamento['status'] === 'cancelado') {
         $_SESSION['mensagem_alerta'] = "Este agendamento já está cancelado.";
    } 
    // Opcional: Impedir cancelamento se estiver muito próximo da data
    // Exemplo: Bloquear cancelamento 24 horas antes do serviço.
    
    $data_servico = new DateTime($agendamento['data'] . ' ' . $agendamento['hora']);
    $agora = new DateTime();
    $limite_cancelamento = $data_servico->modify('-24 hours');
    
    if ($limite_cancelamento < $agora) {
        $_SESSION['mensagem_erro'] = "Não é possível cancelar. O prazo de 24 horas antes do serviço já passou.";
        header("Location: meus_agendamentos.php");
        exit();
    }
    
    
    else {
        // Executa a atualização do status
        $stmt_update = $pdo->prepare(
            "UPDATE Agendamento SET status = 'cancelado' WHERE id = ? AND Cliente_id = ?"
        );
        $stmt_update->execute([$agendamento_id, $cliente_id]);
        
        $_SESSION['mensagem_sucesso'] = "Agendamento cancelado com sucesso.";
    }

} catch (PDOException $e) {
    $_SESSION['mensagem_erro'] = "Erro ao tentar cancelar o agendamento: " . $e->getMessage();
    error_log("Erro ao cancelar agendamento: " . $e->getMessage());
}

// 5. Redirecionar de volta para a lista
header("Location: meus_agendamentos.php");
exit();
?>