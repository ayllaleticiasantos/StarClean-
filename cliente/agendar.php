<?php
session_start();
require_once '../config/db.php';
require_once '../config/config.php';
require_once '../includes/log_helper.php'; // Inclui o helper de log

// Segurança: Apenas clientes podem aceder a esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    header("Location: ../pages/login.php");
    exit();
}

$mensagem = '';
$servico = null;
$enderecos_cliente = []; 
$id_cliente = $_SESSION['usuario_id'];
$remarcar_id = $_GET['remarcar_id'] ?? null; // <-- NOVO: Captura o ID do agendamento a ser remarcado

// Valida o servico_id
if (!isset($_GET['servico_id']) || !is_numeric($_GET['servico_id'])) {
    $mensagem = '<div class="alert alert-danger">ID do serviço não fornecido ou inválido.</div>';
} else {
    $servico_id = $_GET['servico_id'];
    try {
        $pdo = obterConexaoPDO();
        
        // 1. Buscar detalhes do Serviço e do Prestador
        $stmt = $pdo->prepare(
            "SELECT s.id, s.titulo, s.descricao, s.preco, s.prestador_id, p.nome AS nome_prestador
             FROM Servico s
             JOIN Prestador p ON s.prestador_id = p.id
             WHERE s.id = ?"
        );
        $stmt->execute([$servico_id]);
        $servico = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$servico) {
            $mensagem = '<div class="alert alert-danger">Serviço não encontrado.</div>';
        }

        // 2. Buscar todos os Endereços do Cliente Logado
        $stmt_endereco = $pdo->prepare("SELECT id, logradouro, numero, bairro, cidade, uf FROM Endereco WHERE Cliente_id = ?");
        $stmt_endereco->execute([$id_cliente]);
        $enderecos_cliente = $stmt_endereco->fetchAll(PDO::FETCH_ASSOC);

        // Se nenhum endereço for encontrado, redireciona o cliente
        if (empty($enderecos_cliente)) {
            $_SESSION['mensagem_erro'] = "Nenhum endereço cadastrado. Por favor, adicione um endereço para agendar.";
            header("Location: gerir_enderecos.php");
            exit();
        }

    } catch (PDOException $e) {
        $mensagem = '<div class="alert alert-danger">Ocorreu um erro ao buscar dados essenciais.</div>';
        error_log("Erro em agendar.php: " . $e->getMessage());
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $servico && !empty($enderecos_cliente)) {
    $prestador_id = $servico['prestador_id'];
    $endereco_id = $_POST['endereco_id'] ?? null; 
    $data = $_POST['data']; 
    $hora = $_POST['hora']; 
    $observacoes = $_POST['observacoes'];
    // --- NOVOS CAMPOS CAPTURADOS DO POST ---
    // O valor será '1' para Sim e '0' para Não
    $tem_pets = $_POST['tem_pets'] ?? '0';
    $tem_crianca = $_POST['tem_crianca'] ?? '0';
    $possui_aspirador = $_POST['possui_aspirador'] ?? '0';
    $status = 'pendente';
    
    if (empty($endereco_id)) {
        $mensagem = '<div class="alert alert-danger">Por favor, selecione um endereço para o serviço.</div>';
    } else {
        
        // >>> VALIDAÇÃO DE DATA/HORA APLICADA <<<
        $data_hora_agendada = $data . ' ' . $hora;
        $data_hora_atual = date('Y-m-d H:i:s');

        // Cria objetos DateTime para comparação precisa
        try {
            $dt_agendada = new DateTime($data_hora_agendada);
            $dt_atual = new DateTime($data_hora_atual);

            // Adiciona um buffer de 5 minutos
            $dt_atual->modify('+5 minutes'); 

            if ($dt_agendada < $dt_atual) {
                // A data e hora escolhidas estão no passado ou muito próximas do presente.
                $mensagem = '<div class="alert alert-danger">Não é possível agendar serviços para datas ou horários que já passaram. Escolha uma data e hora futura.</div>';
            } else {
                // Se a validação da data/hora passar, prossegue com o INSERT
                try {
                    // --- MUDANÇA: Inicia uma transação para garantir a integridade dos dados ---
                    $pdo = obterConexaoPDO();
                    $pdo->beginTransaction();
                    
                    $stmt = $pdo->prepare(
                        "INSERT INTO Agendamento (cliente_id, prestador_id, servico_id, endereco_id, data, hora, status, observacoes, tem_pets, tem_crianca, possui_aspirador)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([$id_cliente, $prestador_id, $servico['id'], $endereco_id, $data, $hora, $status, $observacoes, $tem_pets, $tem_crianca, $possui_aspirador]);

                    // --- NOVO: Se for uma remarcação, atualiza o status do agendamento antigo ---
                    if (!empty($remarcar_id)) {
                        $stmt_update = $pdo->prepare(
                            "UPDATE Agendamento SET status = 'remarcado' WHERE id = ? AND Cliente_id = ? AND status = 'cancelado'"
                        );
                        $stmt_update->execute([$remarcar_id, $id_cliente]);
                    }

                    $id_novo_agendamento = $pdo->lastInsertId(); // Pega o ID do agendamento recém-criado
                    $pdo->commit(); // Confirma as alterações no banco

                    // Registra a ação no log
                    registrar_log_usuario('cliente', $id_cliente, 'Criou um novo agendamento', ['agendamento_id' => $id_novo_agendamento, 'servico_id' => $servico['id']]);
                    // O código de envio de e-mail foi removido daqui.
                    $_SESSION['mensagem_sucesso'] = "Agendamento solicitado com sucesso! Aguarde a confirmação do prestador.";
                    header("Location: meus_agendamentos.php");
                    exit();
                } catch (PDOException $e) {
                    $pdo->rollBack(); // Desfaz as alterações em caso de erro
                    $mensagem = '<div class="alert alert-danger">Erro ao solicitar o agendamento. Verifique se a data e hora são válidas.</div>';
                    error_log("Erro no INSERT de Agendamento: " . $e->getMessage());
                }
            }

        } catch (Exception $e) {
             $mensagem = '<div class="alert alert-danger">Formato de data/hora inválido.</div>';
             error_log("Erro de data/hora: " . $e->getMessage());
        }
    }
}

// Obtém a data mínima de hoje no formato YYYY-MM-DD para o input HTML (Frontend)
$min_date = date('Y-m-d'); 

include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>

<button class="btn btn-primary d-md-none m-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"
    aria-controls="sidebarMenu">
    <i class="fas fa-bars"></i> Menu
</button>

<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarMenuLabel">Navegação</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <?php include '../includes/menu.php'; ?>
    </div>
</div>
<main class="d-flex">
    <?php include '../includes/sidebar.php'; ?>

    <div class="container-fluid p-4 flex-grow-1">
        <h1 class="mb-4">Agendar Serviço</h1>
        <hr>

        <?= $mensagem ?>

        <?php if ($servico && !empty($enderecos_cliente)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($servico['titulo']) ?></h5>
                    <p class="card-text text-muted">Prestador: <?= htmlspecialchars($servico['nome_prestador']) ?></p>
                    <p class="card-text">Preço: <span class="text-success fw-bold">R$ <?= number_format($servico['preco'], 2, ',', '.') ?></span></p>
                    <p class="card-text"><?= htmlspecialchars($servico['descricao']) ?></p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5>Detalhes do Agendamento</h5>
                </div>
                <div class="card-body">
                    <!-- MUDANÇA: Adiciona o remarcar_id na action do formulário -->
                    <form action="agendar.php?servico_id=<?= htmlspecialchars($servico['id']) ?>&remarcar_id=<?= htmlspecialchars($remarcar_id) ?>" method="post" onsubmit="return validarDisponibilidade(event)">
                        
                        <div class="mb-3">
                            <label for="endereco_id" class="form-label">Selecione o Endereço:</label>
                            <select class="form-select" id="endereco_id" name="endereco_id" required>
                                <option value="">--- Selecione um Endereço ---</option>
                                <?php foreach ($enderecos_cliente as $endereco): ?>
                                    <option value="<?= htmlspecialchars($endereco['id']) ?>">
                                        <?= htmlspecialchars($endereco['logradouro']) ?>, N° <?= htmlspecialchars($endereco['numero']) ?> - <?= htmlspecialchars($endereco['bairro']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- ================================================== -->
                        <!-- !! NOVOS CAMPOS ADICIONADOS AO FORMULÁRIO !! -->
                        <!-- ================================================== -->
                        <div class="mb-3">
                            <label class="form-label">Possui animais de estimação (pets)?</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tem_pets" id="pets_sim" value="1" required>
                                    <label class="form-check-label" for="pets_sim">Sim</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tem_pets" id="pets_nao" value="0" checked>
                                    <label class="form-check-label" for="pets_nao">Não</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Há crianças em casa?</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tem_crianca" id="crianca_sim" value="1" required>
                                    <label class="form-check-label" for="crianca_sim">Sim</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tem_crianca" id="crianca_nao" value="0" checked>
                                    <label class="form-check-label" for="crianca_nao">Não</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="possui_aspirador" class="form-label">Disponibiliza aspirador de pó?</label>
                            <select class="form-select" id="possui_aspirador" name="possui_aspirador" required>
                                <option value="0" selected>Não</option>
                                <option value="1">Sim</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="data" class="form-label">Data do Serviço:</label>
                            <input type="date" class="form-control" id="data" name="data" required min="<?= $min_date ?>">
                        </div>
                        <div class="mb-3">
                            <label for="hora" class="form-label">Hora do Serviço:</label>
                             <input type="time" class="form-control" id="hora" name="hora" required>
                        </div>
                        <div class="mb-3">
                            <label for="observacoes" class="form-label">Observações (opcional):</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Confirmar Agendamento</button>
                        <a href="buscar_servicos.php" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        <?php else: ?>
             <div class="alert alert-info">Não foi possível carregar os detalhes do serviço.</div>
        <?php endif; ?>
    </div>
</main>

<script>
// Função para verificar a disponibilidade ANTES de enviar o formulário
async function validarDisponibilidade(event) {
    event.preventDefault(); // Impede o envio imediato do formulário
    const form = event.target;
    const dataSelecionada = document.getElementById('data').value;
    const prestadorId = <?= $servico['prestador_id'] ?? 'null' ?>;

    if (!dataSelecionada || !prestadorId) {
        alert('Por favor, selecione uma data válida.');
        return false;
    }

    try {
        // Faz uma requisição para um novo script PHP que apenas verifica a data
        const response = await fetch(`../includes/verificar_disponibilidade_api.php?prestador_id=${prestadorId}&data=${dataSelecionada}`);
        const resultado = await response.json();

        if (resultado.disponivel) {
            // Se estiver disponível, envia o formulário
            form.submit();
        } else {
            // Se não, exibe um alerta
            alert('O prestador não está disponível na data selecionada. Por favor, escolha outra data.');
            return false;
        }
    } catch (error) {
        console.error('Erro ao verificar disponibilidade:', error);
        alert('Ocorreu um erro ao verificar a disponibilidade. Tente novamente.');
        return false;
    }
}
</script>

<?php include '../includes/footer.php'; ?>