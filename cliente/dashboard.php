<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas clientes podem acessar a esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    header("Location: ../pages/login.php");
    exit();
}

$id_cliente = $_SESSION['usuario_id'];
$notificacoes_aceitas = [];
$notificacoes_canceladas = [];

try {
    $pdo = obterConexaoPDO();

    // Busca agendamentos ACEITOS não lidos
    $stmt_aceitos = $pdo->prepare(
        "SELECT a.id, p.nome AS nome_prestador, s.titulo AS titulo_servico, a.data, a.hora
         FROM Agendamento a
         JOIN Prestador p ON a.Prestador_id = p.id
         JOIN Servico s ON a.Servico_id = s.id
         WHERE a.Cliente_id = ? AND a.status = 'aceito' AND a.notificacao_cliente_lida = FALSE
         ORDER BY a.data DESC, a.hora DESC LIMIT 5"
    );
    $stmt_aceitos->execute([$id_cliente]);
    $notificacoes_aceitas = $stmt_aceitos->fetchAll(PDO::FETCH_ASSOC);

    // Busca agendamentos CANCELADOS não lidos
    $stmt_cancelados = $pdo->prepare(
        "SELECT a.id, p.nome AS nome_prestador, s.titulo AS titulo_servico, a.data, a.hora, a.motivo_cancelamento
         FROM Agendamento a
         JOIN Prestador p ON a.Prestador_id = p.id
         JOIN Servico s ON a.Servico_id = s.id
         WHERE a.Cliente_id = ? AND a.status = 'cancelado' AND a.notificacao_cliente_lida = FALSE
         ORDER BY a.data DESC, a.hora DESC LIMIT 5"
    );
    $stmt_cancelados->execute([$id_cliente]);
    $notificacoes_canceladas = $stmt_cancelados->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro ao buscar notificações do cliente: " . $e->getMessage());
}

// --- Inclusão dos ficheiros de layout ---
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

    <div class="container-fluid p-4 flex-grow-1"> <h1 class="mb-4">Painel do Cliente</h1>
        <h3>Bem-vindo(a), <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h3>
        <hr>

        <?php foreach ($notificacoes_canceladas as $notificacao): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php if ($notificacao['motivo_cancelamento'] === 'conflito_horario'): ?>
                    <h4 class="alert-heading">Horário Indisponível!</h4>
                    <p>O prestador <strong><?= htmlspecialchars($notificacao['nome_prestador']) ?></strong> já possui outro serviço no horário solicitado para "<strong><?= htmlspecialchars($notificacao['titulo_servico']) ?></strong>".</p>
                <?php else: ?>
                    <h4 class="alert-heading">Agendamento Cancelado!</h4>
                    <p>O prestador <strong><?= htmlspecialchars($notificacao['nome_prestador']) ?></strong> cancelou o serviço "<strong><?= htmlspecialchars($notificacao['titulo_servico']) ?></strong>" que estava agendado para o dia <strong><?= date('d/m/Y', strtotime($notificacao['data'])) ?></strong>.</p>
                <?php endif; ?>
                <hr>
                <p class="mb-0">Você pode ir para a página <a href="meus_agendamentos.php" class="alert-link">Meus Agendamentos</a> para remarcar o serviço.</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>

        <?php foreach ($notificacoes_aceitas as $notificacao): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">Novos Agendamentos Aceitos!</h4>
                <p>O prestador <strong><?= htmlspecialchars($notificacao['nome_prestador']) ?></strong> aceitou o seu agendamento para <strong><?= htmlspecialchars($notificacao['titulo_servico']) ?></strong> no dia <strong><?= date('d/m/Y', strtotime($notificacao['data'])) ?></strong> às <strong><?= htmlspecialchars(substr($notificacao['hora'], 0, 5)) ?></strong>.</p>
                <hr>
                <p class="mb-0">Pode ver todos os seus agendamentos na página "Meus Agendamentos".</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>

        <div class="row mt-4">
            <div class="col-12 col-sm-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-primary">
                    <div class="card-body text-center d-flex flex-column">
                        <i class="fas fa-search fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Buscar Serviços</h5>
                        <p class="card-text">Encontre os melhores prestadores para o que você precisa.</p>
                        <a href="buscar_servicos.php" class="btn btn-primary mt-auto">Buscar Agora</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-success">
                    <div class="card-body text-center d-flex flex-column">
                        <i class="fas fa-calendar-alt fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Meus Agendamentos</h5>
                        <p class="card-text">Veja o histórico e os seus próximos serviços agendados.</p>
                        <a href="meus_agendamentos.php" class="btn btn-success mt-auto">Acessar</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-danger">
                    <div class="card-body text-center d-flex flex-column">
                        <i class="fas fa-map-marker-alt fa-3x text-danger mb-3"></i>
                        <h5 class="card-title">Gerir Endereços</h5>
                        <p class="card-text">Adicione ou edite seus endereços para os serviços.</p>
                        <a href="gerir_enderecos.php" class="btn btn-danger mt-auto">Acessar</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-warning">
                    <div class="card-body text-center d-flex flex-column">
                        <i class="fas fa-user-edit fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Meu Perfil</h5>
                        <p class="card-text">Mantenha seus dados de contato, senha e endereços atualizados.</p>
                        <a href="../pages/perfil.php" class="btn btn-warning mt-auto">Acessar</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<?php 
// O footer fechará a estrutura principal que foi aberta no header e sidebar
include '../includes/footer.php'; 
?>