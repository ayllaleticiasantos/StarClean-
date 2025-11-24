<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header("Location: ../pages/login.php");
    exit();
}

$id_prestador = $_SESSION['usuario_id'];
$notificacoes_pendentes = [];
$contagem_status = ['pendente' => 0, 'aceito' => 0, 'realizado' => 0, 'cancelado' => 0];

try {
    $pdo = obterConexaoPDO();
    $stmt_pendentes = $pdo->prepare(
        "SELECT a.id, c.nome AS nome_cliente, s.titulo AS titulo_servico, a.data, a.hora
         FROM Agendamento a
         JOIN Cliente c ON a.Cliente_id = c.id
         JOIN Servico s ON a.Servico_id = s.id
         WHERE a.Prestador_id = ? AND a.status = 'pendente' AND a.notificacao_prestador_lida = FALSE
         ORDER BY a.data ASC, a.hora ASC LIMIT 5"
    );
    $stmt_pendentes->execute([$id_prestador]);
    $notificacoes_pendentes = $stmt_pendentes->fetchAll(PDO::FETCH_ASSOC);
    $stmt_contagem = $pdo->prepare(
        "SELECT status, COUNT(id) as total 
         FROM Agendamento 
         WHERE Prestador_id = ? AND status IN ('pendente', 'aceito', 'realizado', 'cancelado')
         GROUP BY status"
    );
    $stmt_contagem->execute([$id_prestador]);
    $resultados_contagem = $stmt_contagem->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados_contagem as $resultado) {
        $contagem_status[$resultado['status']] = $resultado['total'];
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar notificações do prestador: " . $e->getMessage());
}

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
        <h1 class="mb-4">Painel do Prestador</h1>
        <h3>Bem-vindo(a), <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h3>
        <hr>

        <?php foreach ($notificacoes_pendentes as $notificacao): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">Nova Solicitação de Agendamento!</h4>
                <p>O cliente <strong><?= htmlspecialchars($notificacao['nome_cliente']) ?></strong> solicitou o serviço "<strong><?= htmlspecialchars($notificacao['titulo_servico']) ?></strong>" para o dia <strong><?= date('d/m/Y', strtotime($notificacao['data'])) ?></strong>.</p>
                <hr>
                <p class="mb-0">Vá para <a href="meus_agendamentos.php" class="alert-link">Meus Agendamentos</a> para aceitar ou recusar.</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
        
        <div class="row mt-4">
            <div class="col-6 col-md-3 mb-4">
                <a href="gerir_agendamentos.php?status=pendente" class="text-decoration-none">
                    <div class="card shadow-sm border-start border-warning border-4 status-card status-card-warning">
                        <div class="card-body text-center p-3">
                            <h4 class="card-title text-warning">Total de Serviços Pendentes</h4>
                            <p class="card-text fs-2 fw-bold mb-0"><?= $contagem_status['pendente'] ?></p>
                            <p><h5>Aqui estão os seus serviços pendentes.</h5></p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-4">
                <a href="gerir_agendamentos.php?status=aceito" class="text-decoration-none">
                    <div class="card shadow-sm border-start border-primary border-4 status-card status-card-primary">
                        <div class="card-body text-center p-3">
                            <h4 class="card-title text-primary">Total de Serviços Aceitos</h4>
                            <p class="card-text fs-2 fw-bold mb-0"><?= $contagem_status['aceito'] ?></p>
                            <p><h5>Aqui estão os seus serviços aceitos.</h5></p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-4">
                <a href="gerir_agendamentos.php?status=realizado" class="text-decoration-none">
                    <div class="card shadow-sm border-start border-success border-4 status-card status-card-success">
                        <div class="card-body text-center p-3">
                            <h4 class="card-title text-success">Total de Serviços Realizados</h4>
                            <p class="card-text fs-2 fw-bold mb-0"><?= $contagem_status['realizado'] ?></p>
                            <p><h5>Aqui estão os seus serviços realizados.</h5></p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-4">
                <a href="gerir_agendamentos.php?status=cancelado" class="text-decoration-none">
                    <div class="card shadow-sm border-start border-danger border-4 status-card status-card-danger">
                        <div class="card-body text-center p-3">
                            <h4 class="card-title text-danger">Total de Serviços Cancelados</h4>
                            <p class="card-text fs-2 fw-bold mb-0"><?= $contagem_status['cancelado'] ?></p>
                            <p><h5>Aqui estão os seus serviços cancelados.</h5></p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-success">
                    <div class="card-body text-center d-flex flex-column justify-content-between">
                        <div>
                            <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Meus Agendamentos</h5>
                            <p class="card-text">Gerencie suas solicitações de serviço e seu calendário.</p>
                        </div>
                        <a href="gerir_agendamentos.php" class="btn btn-success">Gerenciar</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-secondary">
                    <div class="card-body text-center d-flex flex-column justify-content-between">
                        <div>
                            <i class="fas fa-briefcase fa-3x text-secondary mb-3"></i>
                            <h5 class="card-title">Meus Serviços</h5>
                            <p class="card-text">Visualize os serviços que você oferece através da plataforma.</p>
                        </div>
                        <a href="gerir_servicos.php" class="btn btn-secondary">Visualizar</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-danger">
                    <div class="card-body text-center d-flex flex-column justify-content-between">
                        <div>
                            <i class="fas fa-calendar-times fa-3x text-danger mb-3"></i>
                            <h5 class="card-title">Minha Disponibilidade</h5>
                            <p class="card-text">Marque os dias em que você não estará disponível para trabalhar.</p>
                        </div>
                        <a href="gerir_disponibilidade.php" class="btn btn-danger">Definir Datas</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-primary">
                    <div class="card-body text-center d-flex flex-column justify-content-between">
                        <div>
                            <i class="fas fa-dollar-sign fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Meu Financeiro</h5>
                            <p class="card-text">Acompanhe seus ganhos e histórico de serviços realizados.</p>
                        </div>
                        <a href="meu_financeiro.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-info">
                    <div class="card-body text-center d-flex flex-column justify-content-between">
                        <div>
                            <i class="fas fa-star fa-3x text-info mb-3"></i>
                            <h5 class="card-title">Minhas Avaliações</h5>
                            <p class="card-text">Veja o que os clientes estão dizendo sobre o seu trabalho.</p>
                        </div>
                        <a href="minhas_avaliacoes.php" class="btn btn-info">Visualizar</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-warning">
                    <div class="card-body text-center d-flex flex-column justify-content-between">
                        <div>
                            <i class="fas fa-user-edit fa-3x text-warning mb-3"></i>
                            <h5 class="card-title">Meu Perfil</h5>
                            <p class="card-text">Atualize suas informações pessoais, de contato e descrição.</p>
                        </div>
                        <a href="../pages/perfil.php" class="btn btn-warning">Acessar</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-dark">
                    <div class="card-body text-center d-flex flex-column justify-content-between">
                        <div>
                            <i class="fas fa-cog fa-3x text-dark mb-3"></i>
                            <h5 class="card-title">Configurações</h5>
                            <p class="card-text">Ajuste suas preferências de notificação e outras opções da conta.</p>
                        </div>
                        <a href="../pages/configuracoes.php" class="btn btn-dark">Acessar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .status-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .status-card-warning:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(255, 193, 7, 0.5) !important;
    }

    .status-card-primary:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(13, 110, 253, 0.4) !important;
    }

    .status-card-success:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(25, 135, 84, 0.4) !important;
    }

    .status-card-danger:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(220, 53, 69, 0.4) !important;
    }
</style>

<?php include '../includes/footer.php'; ?>