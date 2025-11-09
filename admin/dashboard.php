<?php
session_start();
require_once '../config/db.php';

// 1. VERIFICAÇÃO DE SEGURANÇA (CORRIGIDA)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}
$tipo_admin = $_SESSION['admin_tipo'] ?? ''; // Pega o tipo específico do admin

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
            (SELECT COUNT(*) FROM Agendamento) AS total_agendamentos,
            (SELECT COUNT(*) FROM Servico) AS total_servicos
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $counts['clientes'] = $result['total_clientes'];
    $counts['prestadores'] = $result['total_prestadores'];
    $counts['agendamentos'] = $result['total_agendamentos'];
    $counts['servicos'] = $result['total_servicos'];
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

    <div class="container-fluid p-4 flex-grow-1">
        <h1>Painel de Controle</h1>
        <hr>
        <h3>Bem-vindo(a), <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h3>
        <p>Este é o seu painel de controle. A partir daqui, você poderá gerir todas as funcionalidades do sistema.
        </p>
        <p>O seu tipo de utilizador é: <strong><?= htmlspecialchars($_SESSION['usuario_tipo']) ?></strong>.</p>

        <div class="row mt-4">
            <!-- Cards de Ação -->
            <!-- CONDIÇÃO DE VISIBILIDADE PARA O CARD "GERIR UTILIZADORES" -->
            <?php if ($tipo_admin === 'adminmaster'): ?>
                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-primary">
                        <div class="card-body text-center d-flex flex-column"><i class="fas fa-users fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Gerir Utilizadores</h5>
                            <p class="card-text">Gerir clientes e prestadores de serviço.</p>
                            <a href="gerir_utilizadores.php" class="btn btn-primary mt-auto">Acessar</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-success">
                    <div class="card-body text-center d-flex flex-column"><i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Gerir Agendamentos</h5>
                        <p class="card-text">Ver e gerir todos os agendamentos.</p>
                        <a href="gerir_agendamentos.php" class="btn btn-success mt-auto">Acessar</a>
                    </div>
                </div>
            </div>
             <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-warning">
                    <div class="card-body text-center d-flex flex-column"><i class="fas fa-briefcase fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Gerir Serviços</h5>
                        <p class="card-text">Editar, remover e visualizar todos os serviços.</p>
                        <a href="gerir_servicos.php" class="btn btn-warning mt-auto">Acessar</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-dark">
                    <div class="card-body text-center d-flex flex-column"><i class="fas bi bi-plus-square-fill fa-3x text-dark mb-3"></i>
                        <h5 class="card-title">Cadastrar Serviço</h5>
                        <p class="card-text">Adicionar um novo serviço e atribuir a um prestador.</p>
                        <a href="adicionar_servico.php" class="btn btn-dark mt-auto">Acessar</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-info">
                    <div class="card-body text-center d-flex flex-column"><i class="fas fa-user-shield fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Gerir Administradores</h5>
                        <p class="card-text">Adicionar e gerir contas de admin.</p>
                        <a href="gerenciar_adm.php" class="btn btn-info mt-auto">Acessar</a>
                    </div>
                </div>
            </div>
            
            <!-- CONDIÇÃO DE VISIBILIDADE PARA O CARD "GERENCIAR SITE" -->
            <?php if ($tipo_admin === 'adminmaster' || $tipo_admin === 'admmoderador'): ?>
                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-secondary">
                        <div class="card-body text-center d-flex flex-column"><i class="fas fa-desktop fa-3x text-secondary mb-3"></i>
                            <h5 class="card-title">Gerenciar Site</h5>
                            <p class="card-text">Gerencie o conteúdo da página inicial.</p>
                            <a href="gerir_pagina_inicial.php" class="btn btn-secondary mt-auto">Acessar</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-danger">
                    <div class="card-body text-center d-flex flex-column"><i class="fas fa-chart-pie fa-3x text-danger mb-3"></i>
                        <h5 class="card-title">Relatórios</h5>
                        <p class="card-text">Visualize relatórios financeiros e de serviços.</p>
                        <a href="relatorios.php" class="btn btn-danger mt-auto">Acessar</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-light">
                    <div class="card-body text-center d-flex flex-column"><i class="fas fa-history fa-3x text-dark mb-3"></i>
                        <h5 class="card-title">Logs de Atividades</h5>
                        <p class="card-text">Monitore as ações realizadas pelos administradores.</p>
                        <a href="visualizar_logs.php" class="btn btn-light mt-auto">Acessar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <!-- Cards de Informação -->
            <div class="col-12 col-sm-6 col-lg-3 mb-4 align-self-stretch">
                <div class="card h-100 shadow-sm border-primary text-center">
                    <div class="card-body">
                        <h2 class="card-title text-primary">Total de Clientes cadastrados</h2>
                        <h2 class="card-text display-4"><?= $counts['clientes'] ?></h2>
                        <p>
                        <h4>Clientes registados no sistema.</h4>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3 mb-4 align-self-stretch">
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
            <div class="col-12 col-sm-6 col-lg-3 mb-4 align-self-stretch">
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
            <div class="col-12 col-sm-6 col-lg-3 mb-4 align-self-stretch">
                <div class="card h-100 shadow-sm border-warning text-center">
                    <div class="card-body">
                        <h2 class="card-title text-warning">Total de serviços cadastrados</h2>
                        <h2 class="card-text display-4"><?= $counts['servicos'] ?></h2>
                        <p>
                        <h4>Serviços registados no sistema.</h4>
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