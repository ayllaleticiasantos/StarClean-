<?php
session_start();
require_once '../config/db.php';
require_once '../config/enviar_email.php'; // 1. Incluir o arquivo de e-mail

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
    } elseif ($agendamento['status'] === 'realizado') {
         $_SESSION['mensagem_alerta'] = "Não é possível cancelar um agendamento que já foi realizado.";
    } else {
        // Verifica o prazo de 24 horas
        $data_servico = new DateTime($agendamento['data'] . ' ' . $agendamento['hora']);
        $agora = new DateTime();
        $limite_cancelamento = $data_servico->modify('-24 hours');
        
        if ($limite_cancelamento < $agora) {
            $_SESSION['mensagem_erro'] = "Não é possível cancelar. O prazo de 24 horas antes do serviço já passou.";
        } else {
            // Executa a atualização do status
            $stmt_update = $pdo->prepare(
                "UPDATE Agendamento SET status = 'cancelado' WHERE id = ? AND Cliente_id = ?"
            );
            $stmt_update->execute([$agendamento_id, $cliente_id]);

            // 2. Buscar dados e enviar e-mail de notificação para o prestador
            $sql_dados_email = "
                SELECT 
                    a.data, a.hora,
                    c.nome AS nome_cliente,
                    p.nome AS nome_prestador, p.email AS email_prestador, p.receber_notificacoes_email AS notificacao_prestador,
                    s.titulo AS titulo_servico,
                    e.logradouro, e.numero, e.bairro, e.cidade, e.uf
                FROM agendamento a
                JOIN cliente c ON a.Cliente_id = c.id
                JOIN prestador p ON a.Prestador_id = p.id
                JOIN servico s ON a.Servico_id = s.id
                JOIN endereco e ON a.Endereco_id = e.id
                WHERE a.id = ?
            ";
            $stmt_email = $pdo->prepare($sql_dados_email);
            $stmt_email->execute([$agendamento_id]);
            $dadosAgendamento = $stmt_email->fetch(PDO::FETCH_ASSOC);

            if ($dadosAgendamento && $dadosAgendamento['notificacao_prestador'] == 1) {
                enviarEmailAgendamento('agendamento_cancelado_prestador', $dadosAgendamento);
            }
            
            $_SESSION['mensagem_sucesso'] = "Agendamento cancelado com sucesso.";
        }
    }

} catch (PDOException $e) {
    $_SESSION['mensagem_erro'] = "Erro ao tentar cancelar o agendamento: " . $e->getMessage();
    error_log("Erro ao cancelar agendamento: " . $e->getMessage());
}

// 5. Redirecionar de volta para a lista
header("Location: meus_agendamentos.php");
exit();
?>