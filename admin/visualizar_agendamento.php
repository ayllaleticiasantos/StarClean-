<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas administradores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$agendamento = null;
$mensagem_erro = '';

// Verifica se o ID do agendamento foi passado na URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensagem_erro'] = "ID do agendamento não fornecido ou inválido.";
    header("Location: gerir_agendamentos.php");
    exit();
}

$agendamento_id = $_GET['id'];

try {
    $pdo = obterConexaoPDO();
    
    // Query completa para buscar todos os detalhes do agendamento
    $stmt = $pdo->prepare("
        SELECT 
            a.id, a.data, a.hora, a.status, a.observacoes, a.tem_pets, a.tem_crianca, a.possui_aspirador,
            c.nome AS nome_cliente, c.sobrenome AS sobrenome_cliente, c.email AS email_cliente, c.telefone AS telefone_cliente,
            p.nome AS nome_prestador,
            s.titulo AS titulo_servico, s.descricao AS descricao_servico, s.preco,
            e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.uf, e.cep
        FROM Agendamento a
        JOIN Cliente c ON a.Cliente_id = c.id
        JOIN Prestador p ON a.Prestador_id = p.id
        JOIN Servico s ON a.Servico_id = s.id
        JOIN Endereco e ON a.Endereco_id = e.id
        WHERE a.id = ?
    ");
    $stmt->execute([$agendamento_id]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$agendamento) {
        $_SESSION['mensagem_erro'] = "Agendamento não encontrado.";
        header("Location: gerir_agendamentos.php");
        exit();
    }

} catch (PDOException $e) {
    error_log("Erro ao buscar detalhes do agendamento (admin): " . $e->getMessage());
    $_SESSION['mensagem_erro'] = "Erro ao carregar os detalhes do agendamento.";
    header("Location: gerir_agendamentos.php");
    exit();
}

include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>

<button class="btn btn-primary d-md-none m-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
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
        <h1 class="mb-4">Detalhes do Agendamento</h1>
        
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Resumo do Agendamento</h5>
                <a href="gerir_agendamentos.php" class="btn btn-secondary">Voltar para a Lista</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Detalhes do Serviço</h5>
                        <p><strong>Serviço:</strong> <?= htmlspecialchars($agendamento['titulo_servico']) ?></p>
                        <p><strong>Descrição:</strong> <?= htmlspecialchars($agendamento['descricao_servico']) ?></p>
                        <p><strong>Prestador:</strong> <?= htmlspecialchars($agendamento['nome_prestador']) ?></p>
                        <p><strong>Preço:</strong> <span class="fw-bold text-success">R$ <?= number_format($agendamento['preco'], 2, ',', '.') ?></span></p>
                        <p><strong>Data e Hora:</strong> <?= date('d/m/Y', strtotime($agendamento['data'])) ?> às <?= htmlspecialchars(substr($agendamento['hora'], 0, 5)) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Detalhes do Cliente</h5>
                        <p><strong>Cliente:</strong> <?= htmlspecialchars($agendamento['nome_cliente'] . ' ' . $agendamento['sobrenome_cliente']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($agendamento['email_cliente']) ?></p>
                        <p><strong>Telefone:</strong> <?= htmlspecialchars($agendamento['telefone_cliente']) ?></p>
                        <p><strong>Endereço:</strong> <?= htmlspecialchars($agendamento['logradouro'] . ', ' . $agendamento['numero'] . ($agendamento['complemento'] ? ' - ' . $agendamento['complemento'] : '')) ?>, <?= htmlspecialchars($agendamento['bairro'] . ', ' . $agendamento['cidade'] . ' - ' . $agendamento['uf']) ?></p>
                    </div>
                </div>
                <hr>
                <h5>Informações Adicionais</h5>
                <p><strong>Observações do Cliente:</strong> <?= htmlspecialchars($agendamento['observacoes'] ?: 'Nenhuma') ?></p>
                <p><strong>Possui Pets?</strong> <?= $agendamento['tem_pets'] ? 'Sim' : 'Não' ?></p>
                <p><strong>Há Crianças?</strong> <?= $agendamento['tem_crianca'] ? 'Sim' : 'Não' ?></p>
                <p><strong>Disponibiliza Aspirador?</strong> <?= $agendamento['possui_aspirador'] ? 'Sim' : 'Não' ?></p>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>