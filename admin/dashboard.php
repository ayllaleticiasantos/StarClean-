<?php
session_start();
require_once '../config/db.php';

// 1. VERIFICAÇÃO DE SEGURANÇA
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

// 2. LÓGICA DO DASHBOARD (BUSCA DE DADOS)
$counts = [
    'clientes' => 0,
    'prestadores' => 0,
    'agendamentos' => 0
];
try {
    $pdo = obterConexaoPDO();
    // Consulta otimizada para buscar todos os dados de uma vez
    $stmt = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM Cliente) AS total_clientes,
            (SELECT COUNT(*) FROM Prestador) AS total_prestadores,
            (SELECT COUNT(*) FROM Agendamento) AS total_agendamentos
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $counts['clientes'] = $result['total_clientes'];
    $counts['prestadores'] = $result['total_prestadores'];
    $counts['agendamentos'] = $result['total_agendamentos'];
} catch (PDOException $e) {
    error_log("Erro ao buscar dados do dashboard do admin: " . $e->getMessage());
}

// 3. INCLUSÃO DO CABEÇALHO E NAVBAR
include '../includes/header.php';
include '../includes/navbar_logged_in.php';

// =========================================================================
// 4. ESTRUTURA DA SIDEBAR RESPONSIVA COMEÇA AQUI
// =========================================================================
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
        <h1>Painel de Controle</h1>
        <hr>
        <h3>Bem-vindo(a), <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h3>
        <p>Este é o seu painel de controle. A partir daqui, você poderá gerir todas as funcionalidades do sistema.
        </p>
        <p>O seu tipo de utilizador é: <strong><?= htmlspecialchars($_SESSION['usuario_tipo']) ?></strong>.</p>

        <div class="row mt-4">
            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-primary">
                    <div class="card-body text-center"><i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Gerir Utilizadores</h5>
                        <p class="card-text">Gerir clientes e prestadores de serviço.</p><a
                            href="gerir_utilizadores.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-success">
                    <div class="card-body text-center"><i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Gerir Agendamentos</h5>
                        <p class="card-text">Ver e gerir todos os agendamentos.</p><a href="gerir_agendamentos.php"
                            class="btn btn-success">Acessar</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-info">
                    <div class="card-body text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor"
                            class="bi bi-person-fill-add text-info" viewBox="0 0 16 16">
                            <path
                                d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                            <path
                                d="M2 13c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4" />
                        </svg>
                        <h5 class="card-title">Cadastrar Administrador</h5>
                        <p class="card-text">Adicione um novo administrador.</p>
                        <a href="cadastraraadm.php" class="btn btn-info">Cadastrar Agora</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-warning">
                    <div class="card-body text-center"><i
                            class="fa-3x bi bi-plus-square-fill text-warning mb-3"></i>
                        <h5 class="card-title">Adicionar Serviços</h5>
                        <p class="card-text">Adicione um novo serviço.</p><a href="adicionar_servico.php"
                            class="btn btn-warning">Cadastrar Agora</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-danger">
                    <div class="card-body text-center"><i class="fa-3x bi bi-file-earmark-bar-graph-fill text-danger"></i>
                        <h5 class="card-title">Relatórios</h5>
                        <p class="card-text">Visualize e gere relatórios.</p><a href="relatorios.php"
                            class="btn btn-danger">Acessar</a>
                    </div>
                </div>
            </div>

        </div>

        <div class="row mt-4">
            <div class="col-12 col-sm-6 col-lg-4 mb-4 align-self-stretch">
                <div class="card h-100 shadow-sm border-primary text-center">
                    <div class="card-body">
                        <h2 class="card-title text-primary">Total de Clientes</h2>
                        <h2 class="card-text display-4"><?= $counts['clientes'] ?></h2>
                        <p>
                        <h4>Clientes registados no sistema.</h4>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 mb-4 align-self-stretch">
                <div class="card h-100 shadow-sm border-success text-center">
                    <div class="card-body">
                        <h2 class="card-title text-success">Total de Prestadores</h2>
                        <h2 class="card-text display-4"><?= $counts['prestadores'] ?></h2>
                        <p>
                        <h4>Prestadores registados no sistema.</h4>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 mb-4 align-self-stretch">
                <div class="card h-100 shadow-sm border-info text-center">
                    <div class="card-body">
                        <h2 class="card-title text-info">Total de Agendamentos</h2>
                        <h2 class="card-text display-4"><?= $counts['agendamentos'] ?></h2>
                        <p>
                        <h4>Agendamentos registados no sistema.</h4>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php
// 6. INCLUSÃO DO RODAPÉ
include '../includes/footer.php';
?>