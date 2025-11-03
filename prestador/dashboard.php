<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas prestadores podem aceder a esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header("Location: ../pages/login.php");
    exit();
}

// Lógica para buscar as contagens de agendamentos
$id_prestador_logado = $_SESSION['usuario_id'];
$counts = [
    'pendente' => 0,
    'aceito' => 0,
    'realizado' => 0,
    'cancelado' => 0
];

try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->prepare("SELECT status, COUNT(*) AS total FROM Agendamento WHERE Prestador_id = ? GROUP BY status");
    $stmt->execute([$id_prestador_logado]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $counts[$row['status']] = $row['total'];
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar contagens de agendamentos: " . $e->getMessage());
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

    <div class="container-fluid p-4">
        <h1 class="mb-4">Painel do Prestador</h1>
        <h3>Bem-vindo(a), <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h3>
        <hr>

        <div class="row mt-4">
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-list fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Meus Serviços</h5>
                        <p class="card-text">Visualize os serviços que você oferece.</p>
                        <a href="gerir_servicos.php" class="btn btn-primary">Ver Serviços</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Gerir Agendamentos</h5>
                        <p class="card-text">Veja seus agendamentos pendentes e aceitos.</p>
                        <a href="gerir_agendamentos.php" class="btn btn-success">Ver Agendamentos</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-user-edit fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Meu Perfil</h5>
                        <p class="card-text">Mantenha seus dados de contato e de acesso atualizados.</p>
                        <a href="../pages/perfil.php" class="btn btn-warning">Editar Perfil</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-currency-dollar fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Meu Financeiro</h5>
                        <p class="card-text">Veja os serviços concluídos, e prestes a concluir </p>
                        <a href="meu_financeiro.php" class="btn btn-warning">Ver Dados</a>
                    </div>
                </div>
            </div>

        </div>
        <div class="row text-center">
            <div class="col-12 col-sm-6 col-lg-4 mb-4 align-self-stretch">
                <div class="card h-100 shadow-sm border-warning">
                    <div class="card-body">
                        <h5 class="card-title text-warning">Agendamentos Pendentes</h5>
                        <h2 class="card-text display-4"><?= $counts['pendente'] ?></h2>
                        <p class="card-text">Acompanhe os agendamentos pendentes.</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 mb-4 align-self-stretch">
                <div class="card h-100 shadow-sm border-success">
                    <div class="card-body">
                        <h5 class="card-title text-success">Agendamentos Aceitos</h5>
                        <h2 class="card-text display-4"><?= $counts['aceito'] ?></h2>
                        <p class="card-text">Acompanhe os agendamentos aceitos.</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 mb-4 align-self-stretch">
                <div class="card h-100 shadow-sm border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Serviços Concluídos</h5>
                        <h2 class="card-text display-4"><?= $counts['realizado'] ?></h2>
                        <p class="card-text">Acompanhe os serviços concluídos.</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 mb-4 align-self-stretch">
                <div class="card h-100 shadow-sm border-danger">
                    <div class="card-body">
                        <h5 class="card-title text-danger">Agendamentos Cancelados</h5>
                        <h2 class="card-text display-4"><?= $counts['cancelado'] ?></h2>
                        <p class="card-text">Acompanhe os agendamentos cancelados.</p>
                    </div>
                </div>
            </div>
        </div>



    </div>
</main>

<?php include '../includes/footer.php'; ?>