<?php
session_start();
require_once '../config/db.php';
require_once '../includes/log_helper.php'; // Inclui o helper de log
// Removido o include de agendar.php pois as funções necessárias estão aqui

// Segurança: Apenas clientes logados podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    header("Location: ../pages/login.php");
    exit();
}

$id_cliente_logado = $_SESSION['usuario_id'];
$agendamento_detalhes = null;
$enderecos_cliente = [];
$mensagem = '';

// 1. Validação do ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensagem_erro'] = "ID do agendamento não fornecido ou inválido.";
    header("Location: meus_agendamentos.php");
    exit();
}

$agendamento_id = $_GET['id'];

try {
    $pdo = obterConexaoPDO();
    
    // Buscar agendamento e prestador
    $stmt = $pdo->prepare(
        "SELECT a.id, a.servico_id, a.endereco_id, a.data, a.hora, a.status, a.observacoes, 
                s.titulo AS titulo_servico, p.nome AS nome_prestador
         FROM Agendamento a
         JOIN Servico s ON a.Servico_id = s.id
         JOIN Prestador p ON a.Prestador_id = p.id
         WHERE a.id = ? AND a.Cliente_id = ?"
    );
    $stmt->execute([$agendamento_id, $id_cliente_logado]);
    $agendamento_detalhes = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$agendamento_detalhes) {
        $_SESSION['mensagem_erro'] = "Agendamento não encontrado ou acesso não autorizado.";
        header("Location: meus_agendamentos.php");
        exit();
    }
    
    // CORREÇÃO APLICADA AQUI: Verificar se o agendamento pode ser editado (não cancelado, não realizado)
    if ($agendamento_detalhes['status'] === 'cancelado' || $agendamento_detalhes['status'] === 'realizado') {
        $_SESSION['mensagem_alerta'] = "Este agendamento não pode ser editado pois está com o status '{$agendamento_detalhes['status']}'.";
        header("Location: meus_agendamentos.php");
        exit();
    }
    
    // Buscar Endereços do Cliente Logado
    $stmt_endereco = $pdo->prepare("SELECT id, logradouro, numero, bairro, cidade, uf FROM Endereco WHERE Cliente_id = ?");
    $stmt_endereco->execute([$id_cliente_logado]);
    $enderecos_cliente = $stmt_endereco->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $mensagem = '<div class="alert alert-danger">Erro ao buscar dados para edição.</div>';
    error_log("Erro em editar_agendamento.php (GET): " . $e->getMessage());
}

// 3. Processamento do Formulário de Edição (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $agendamento_detalhes) {
    $endereco_id_novo = $_POST['endereco_id'] ?? $agendamento_detalhes['endereco_id']; 
    $data_nova = $_POST['data']; 
    $hora_nova = $_POST['hora']; 
    $observacoes_novas = $_POST['observacoes'];
    
    // =========================================================
    // Lógica de validação de data futura
    // =========================================================
    $data_hora_agendada = $data_nova . ' ' . $hora_nova;
    $dt_agendada = new DateTime($data_hora_agendada);
    $dt_atual = new DateTime(date('Y-m-d H:i:s'));
    $dt_atual->modify('+5 minutes'); // Buffer
    
    $validacao_ok = true;

    if ($dt_agendada < $dt_atual) {
        $mensagem = '<div class="alert alert-danger">Não é possível agendar para uma data ou horário que já passou.</div>';
        $validacao_ok = false;
    } 
    // Opcional: Impedir edição se a data for muito próxima (ex: menos de 4 horas)
     elseif ($dt_agendada < (new DateTime())->modify('+4 hours')) {
        $mensagem = '<div class="alert alert-danger">A data/hora escolhida deve ter no mínimo 4 horas de antecedência.</div>';
        $validacao_ok = false;
    }
    
    
    if ($validacao_ok) {
        try {
            $pdo = obterConexaoPDO();
            // 4. Executa a Atualização
            $stmt_update = $pdo->prepare(
                "UPDATE Agendamento 
                 SET endereco_id = ?, data = ?, hora = ?, observacoes = ?
                 WHERE id = ? AND Cliente_id = ?"
            );
            $stmt_update->execute([
                $endereco_id_novo, 
                $data_nova, 
                $hora_nova, 
                $observacoes_novas, 
                $agendamento_id, 
                $id_cliente_logado
            ]);

            // Registra a ação no log
            registrar_log_usuario('cliente', $id_cliente_logado, 'Editou o agendamento', ['agendamento_id' => $agendamento_id, 'nova_data' => $data_nova, 'nova_hora' => $hora_nova]);

            $_SESSION['mensagem_sucesso'] = "Agendamento #{$agendamento_id} atualizado com sucesso.";
            header("Location: meus_agendamentos.php");
            exit();
        } catch (PDOException $e) {
            $mensagem = '<div class="alert alert-danger">Erro ao atualizar o agendamento. Tente novamente.</div>';
            error_log("Erro no UPDATE de Agendamento: " . $e->getMessage());
        }
    }
}

// Obtém a data mínima de hoje no formato YYYY-MM-DD para o input HTML (Frontend)
$min_date = date('Y-m-d'); 

include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>

<button class="btn btn-primary d-md-none m-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
    <i class="fas fa-bars"></i> Menu
</button>
<main class="d-flex">
    <?php include '../includes/sidebar.php'; ?>

    <div class="container-fluid p-4 flex-grow-1">
        <h1 class="mb-4">Editar Agendamento</h1>
        <hr>

        <?= $mensagem ?>
        <a href="meus_agendamentos.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Voltar</a>

        <?php if ($agendamento_detalhes): ?>
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5>Editando Serviço: <?= htmlspecialchars($agendamento_detalhes['titulo_servico']) ?></h5>
                    <p class="text-muted mb-0">Prestador: <?= htmlspecialchars($agendamento_detalhes['nome_prestador']) ?></p>
                </div>
                <div class="card-body">
                    <form action="editar_agendamento.php?id=<?= htmlspecialchars($agendamento_id) ?>" method="post">
                        
                        <div class="mb-3">
                            <label for="endereco_id" class="form-label">Selecione o Endereço:</label>
                            <select class="form-select" id="endereco_id" name="endereco_id" required>
                                <?php foreach ($enderecos_cliente as $endereco): 
                                    $selected = ($endereco['id'] == $agendamento_detalhes['endereco_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= htmlspecialchars($endereco['id']) ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($endereco['logradouro']) ?>, N° <?= htmlspecialchars($endereco['numero']) ?> - <?= htmlspecialchars($endereco['bairro']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="data" class="form-label">Nova Data do Serviço:</label>
                            <input type="date" class="form-control" id="data" name="data" required 
                                value="<?= htmlspecialchars($agendamento_detalhes['data']) ?>" min="<?= $min_date ?>">
                        </div>
                        <div class="mb-3">
                            <label for="hora" class="form-label">Nova Hora do Serviço:</label>
                            <input type="time" class="form-control" id="hora" name="hora" required 
                                value="<?= htmlspecialchars($agendamento_detalhes['hora']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="observacoes" class="form-label">Observações (opcional):</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?= htmlspecialchars($agendamento_detalhes['observacoes']) ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning">Salvar Alterações</button>
                        <a href="meus_agendamentos.php" class="btn btn-secondary">Cancelar Edição</a>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>