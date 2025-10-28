<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas clientes logados podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') { header("Location: ../pages/login.php");exit();
}

$id_cliente_logado = $_SESSION['usuario_id'];
$agendamento_detalhes = null;

// 1. Validação do ID do Agendamento (Essa seção está CORRETA)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
$_SESSION['mensagem_erro'] = "ID do agendamento não fornecido ou inválido.";
header("Location: meus_agendamentos.php");
exit();
}

$agendamento_id = $_GET['id'];

try {
$pdo = obterConexaoPDO();

// 2. BUSCA CORRIGIDA: REMOVIDA A COLUNA 'a.data_criacao'
$stmt = $pdo->prepare("SELECT a.id, s.titulo AS titulo_servico, s.descricao AS descricao_servico,
           a.data, a.hora, a.status, a.observacoes,
           p.nome AS nome_prestador, p.email AS email_prestador,
           e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.uf, e.cep
    FROM agendamento a
    JOIN servico s ON a.Servico_id = s.id
    JOIN prestador p ON a.Prestador_id = p.id
    JOIN endereco e ON a.Endereco_id = e.id
    WHERE a.id = ? AND a.Cliente_id = ?
");
$stmt->execute([$agendamento_id, $id_cliente_logado]);
$agendamento_detalhes = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Checar se o agendamento foi encontrado e pertence ao cliente
if (!$agendamento_detalhes) {
$_SESSION['mensagem_erro'] = "Agendamento não encontrado ou acesso não autorizado.";
header("Location: meus_agendamentos.php");
exit();
}

} catch (PDOException $e) {
// Mensagem de erro para o usuário e log para debug
$_SESSION['mensagem_erro'] = "Erro ao buscar detalhes do agendamento. (ERRO SQL)";
error_log("Erro no SQL em visualizar_agendamento.php: " . $e->getMessage());
header("Location: meus_agendamentos.php");
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

    <div class="container-fluid p-4">
        <h1 class="mb-4">Detalhes do Agendamento</h1>
        <hr>

        <a href="meus_agendamentos.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Voltar</a>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Serviço Agendado</h5>
            </div>
            <div class="card-body">
                <p><strong>Serviço:</strong> <?= htmlspecialchars($agendamento_detalhes['titulo_servico']) ?></p>
                <p><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($agendamento_detalhes['descricao_servico'])) ?></p>
                <p><strong>Prestador:</strong> <?= htmlspecialchars($agendamento_detalhes['nome_prestador']) ?></p>
                <p><strong>Contato do Prestador:</strong> <?= htmlspecialchars($agendamento_detalhes['email_prestador']) ?></p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Detalhes de Data e Status</h5>
            </div>
            <div class="card-body">
                <p><strong>Data do Serviço:</strong> <?= date('d/m/Y', strtotime($agendamento_detalhes['data'])) ?></p>
                <p><strong>Hora:</strong> <?= htmlspecialchars($agendamento_detalhes['hora']) ?></p>
                <p><strong>Status:</strong> <span class="badge bg-<?= ($agendamento_detalhes['status'] == 'pendente' ? 'warning' : ($agendamento_detalhes['status'] == 'cancelado' ? 'danger' : 'success')) ?>"><?= htmlspecialchars(ucfirst($agendamento_detalhes['status'])) ?></span></p>
                <p><strong>Observações:</strong> <?= nl2br(htmlspecialchars($agendamento_detalhes['observacoes'] ?: 'Nenhuma observação.')) ?></p>
            </div>
        </div>
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Local do Serviço</h5>
            </div>
            <div class="card-body">
                <p><strong>Logradouro:</strong> <?= htmlspecialchars($agendamento_detalhes['logradouro']) ?>, N° <?= htmlspecialchars($agendamento_detalhes['numero']) ?></p>
                <p><strong>Bairro:</strong> <?= htmlspecialchars($agendamento_detalhes['bairro']) ?></p>
                <p><strong>Cidade/UF:</strong> <?= htmlspecialchars($agendamento_detalhes['cidade']) ?>/<?= htmlspecialchars($agendamento_detalhes['uf']) ?></p>
                <p><strong>CEP:</strong> <?= htmlspecialchars($agendamento_detalhes['cep']) ?></p>
                <?php if ($agendamento_detalhes['complemento']): ?>
                    <p><strong>Complemento:</strong> <?= htmlspecialchars($agendamento_detalhes['complemento']) ?></p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>