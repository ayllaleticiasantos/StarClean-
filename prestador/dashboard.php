<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas prestadores podem aceder a esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header("Location: ../pages/login.php");
    exit();
}

$id_prestador_logado = $_SESSION['usuario_id'];

// Lógica para buscar as contagens de agendamentos
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

<main class="d-flex">
    <?php include '../includes/sidebar.php'; ?>

    <div class="container-fluid p-4">
        <h1 class="mb-4">Painel do Prestador</h1>
        <h3>Bem-vindo(a), <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h3>
        <hr>

        <div class="row text-center">
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-warning">
                    <div class="card-body">
                        <h5 class="card-title text-warning">Agendamentos Pendentes</h5>
                        <h2 class="card-text display-4"><?= $counts['pendente'] ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-success">
                    <div class="card-body">
                        <h5 class="card-title text-success">Agendamentos Aceites</h5>
                        <h2 class="card-text display-4"><?= $counts['aceito'] ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Serviços Concluídos</h5>
                        <h2 class="card-text display-4"><?= $counts['realizado'] ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-danger">
                    <div class="card-body">
                        <h5 class="card-title text-danger">Agendamentos Cancelados</h5>
                        <h2 class="card-text display-4"><?= $counts['cancelado'] ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center">
                        <i class="fas fa-list fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Meus Serviços</h5>
                        <p class="card-text">Visualize os serviços que você oferece.</p>
                        <a href="gerir_servicos.php" class="btn btn-primary">Ver Serviços</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Gerir Agendamentos</h5>
                        <p class="card-text">Veja seus agendamentos pendentes e aceitos.</p>
                        <a href="gerir_agendamentos.php" class="btn btn-success">Ver Agendamentos</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center">
                        <i class="fas fa-user-edit fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Meu Perfil</h5>
                        <p class="card-text">Mantenha seus dados de contato e de acesso atualizados.</p>
                        <a href="../pages/perfil.php" class="btn btn-warning">Editar Perfil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>