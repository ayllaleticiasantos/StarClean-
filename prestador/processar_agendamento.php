<?php
session_start();
require_once '../config/db.php';
require_once '../includes/log_helper.php'; // Passo 2: Inclui o helper

// Segurança: Apenas prestadores podem aceder
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header("Location: ../pages/login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['acao'])) {
    $id_agendamento = $_GET['id'];
    $acao = $_GET['acao'];
    $id_prestador = $_SESSION['usuario_id'];

    $novo_status = '';
    if ($acao === 'aceito') {
        try {
            $pdo = obterConexaoPDO();

            // --- NOVA VALIDAÇÃO: Impede aceitar agendamentos duplicados no mesmo horário ---
            if ($acao === 'aceito') {
                // 1. Busca a data e hora do agendamento que está sendo aceito
                $stmt_info = $pdo->prepare("SELECT data, hora FROM Agendamento WHERE id = ?");
                $stmt_info->execute([$id_agendamento]);
                $agendamento_info = $stmt_info->fetch();

                if ($agendamento_info) {
                    // 2. Verifica se já existe outro agendamento ACEITO no mesmo horário
                    $stmt_check = $pdo->prepare(
                        "SELECT COUNT(*) FROM Agendamento WHERE Prestador_id = ? AND data = ? AND hora = ? AND status = 'aceito'"
                    );
                    $stmt_check->execute([$id_prestador, $agendamento_info['data'], $agendamento_info['hora']]);
                    $conflitos = $stmt_check->fetchColumn();

                    if ($conflitos > 0) {
                        // Se houver conflito, cancela o agendamento atual e informa o prestador.
                        $stmt_cancel = $pdo->prepare("UPDATE Agendamento SET status = 'cancelado', motivo_cancelamento = 'conflito_horario' WHERE id = ? AND Prestador_id = ?");
                        $stmt_cancel->execute([$id_agendamento, $id_prestador]);

                        registrar_log_usuario('prestador', $id_prestador, "Tentativa de aceitar agendamento com conflito (cancelado automaticamente)", ['agendamento_id' => $id_agendamento, 'motivo' => 'conflito_horario']);

                        $_SESSION['mensagem_erro'] = "Não foi possível aceitar. Você já possui outro serviço aceito neste mesmo dia e horário.";
                        header("Location: gerir_agendamentos.php");
                        exit();
                    }
                }
            }
            
            // Se passou pela validação (ou não era 'aceito'), define o status e a mensagem de sucesso.
            if ($acao === 'aceito') {
                $novo_status = 'aceito';
                $_SESSION['mensagem_sucesso'] = "Agendamento aceito com sucesso!";
            } elseif ($acao === 'cancelado') {
                $novo_status = 'cancelado';
                $_SESSION['mensagem_sucesso'] = "Agendamento recusado com sucesso.";
            } elseif ($acao === 'realizado') {
                $novo_status = 'realizado';
                $_SESSION['mensagem_sucesso'] = "Serviço marcado como concluído!";
            }

            if ($novo_status) {
                $stmt = $pdo->prepare("UPDATE Agendamento SET status = ? WHERE id = ? AND Prestador_id = ?");
                $stmt->execute([$novo_status, $id_agendamento, $id_prestador]);

                // Passo 3: Registra a ação no log
                registrar_log_usuario('prestador', $id_prestador, "Alterou o status do agendamento para '$novo_status'", ['agendamento_id' => $id_agendamento]);

                // Se o serviço foi concluído, notifica os administradores
                if ($novo_status === 'realizado') {
                    // Busca o preço do serviço
                    $stmt_servico = $pdo->prepare(
                        "SELECT s.preco FROM Servico s JOIN Agendamento a ON s.id = a.Servico_id WHERE a.id = ?"
                    );
                    $stmt_servico->execute([$id_agendamento]);
                    $preco_servico = $stmt_servico->fetchColumn();

                    if ($preco_servico) {
                        // Busca todos os administradores
                        $stmt_admins = $pdo->query("SELECT id FROM administrador");
                        $admin_ids = $stmt_admins->fetchAll(PDO::FETCH_COLUMN);

                        if ($admin_ids) {
                            $stmt_notif = $pdo->prepare(
                                "INSERT INTO notificacoes (usuario_id, tipo_usuario, mensagem, link, lida) VALUES (?, 'admin', ?, ?, FALSE)"
                            );
                            $mensagem_notif = "Serviço concluído! Valor: R$ " . number_format($preco_servico, 2, ',', '.');
                            foreach ($admin_ids as $admin_id) {
                                $stmt_notif->execute([$admin_id, $mensagem_notif, 'admin/relatorios.php']);
                            }
                        }
                    }
                }
            }
         } catch (PDOException $e) {
             $_SESSION['mensagem_erro'] = "Erro ao atualizar o agendamento.";
             error_log($e->getMessage());
         }
     }
}

header("Location: gerir_agendamentos.php");
exit();
?>