<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header("Location: ../pages/login.php");
    exit();
}

$id_prestador_logado = $_SESSION['usuario_id'];
$total_realizado = 0;
$total_a_receber = 0;
$servicos_realizados = [];
$servicos_a_receber = [];
$mensagem_erro = '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

try {
    $pdo = obterConexaoPDO();
    $filtro_sql = "";
    $params_filtro = [];
    if (!empty($data_inicio)) {
        $filtro_sql .= " AND a.data >= ?";
        $params_filtro[] = $data_inicio;
    }
    if (!empty($data_fim)) {
        $filtro_sql .= " AND a.data <= ?";
        $params_filtro[] = $data_fim;
    }

    $sql_total_realizado = "SELECT SUM(s.preco) AS total 
                            FROM Agendamento a 
                            JOIN Servico s ON a.Servico_id = s.id 
                            WHERE a.Prestador_id = ? AND a.status = 'realizado'" . $filtro_sql;
    $stmt_realizado = $pdo->prepare($sql_total_realizado);
    $stmt_realizado->execute(array_merge([$id_prestador_logado], $params_filtro));
    $total_realizado = $stmt_realizado->fetchColumn() ?: 0;

    $sql_total_aceito = "SELECT SUM(s.preco) AS total 
                         FROM Agendamento a 
                         JOIN Servico s ON a.Servico_id = s.id 
                         WHERE a.Prestador_id = ? AND a.status = 'aceito'" . $filtro_sql;
    $stmt_aceito = $pdo->prepare($sql_total_aceito);
    $stmt_aceito->execute(array_merge([$id_prestador_logado], $params_filtro));
    $total_a_receber = $stmt_aceito->fetchColumn() ?: 0;

    $sql_base = "SELECT a.data, a.hora, c.nome AS nome_cliente, s.titulo, s.preco 
                 FROM Agendamento a
                 JOIN Cliente c ON a.Cliente_id = c.id
                 JOIN Servico s ON a.Servico_id = s.id
                 WHERE a.Prestador_id = ? AND a.status = ?" . $filtro_sql;

    $stmt_lista_realizados = $pdo->prepare($sql_base . " ORDER BY a.data DESC");
    $stmt_lista_realizados->execute(array_merge([$id_prestador_logado, 'realizado'], $params_filtro));
    $servicos_realizados = $stmt_lista_realizados->fetchAll(PDO::FETCH_ASSOC);

    $stmt_lista_aceitos = $pdo->prepare($sql_base . " ORDER BY a.data ASC");
    $stmt_lista_aceitos->execute(array_merge([$id_prestador_logado, 'aceito'], $params_filtro));
    $servicos_a_receber = $stmt_lista_aceitos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro na página financeira do prestador: " . $e->getMessage());
    $mensagem_erro = '<div class="alert alert-danger">Não foi possível carregar os dados financeiros. Tente novamente.</div>';
}

include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>

<style>
    @media print {
        .card.bg-success, .card.bg-warning {
            -webkit-print-color-adjust: exact !important; /* Chrome, Safari, Edge */
            print-color-adjust: exact !important; /* Firefox */
        }

        .card.bg-success, .card.bg-success .card-title, .card.bg-success .card-text {
            color: white !important;
        }
        .card.bg-warning, .card.bg-warning .card-title, .card.bg-warning .card-text {
            color: #212529 !important; /* Cor escura padrão do Bootstrap */
        }
    }
</style>


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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Meu Financeiro</h1>
            <button type="button" onclick="window.print();" class="btn btn-info no-print"><i class="fas fa-print me-1"></i>Imprimir Relatório</button>
        </div>
        <?= $mensagem_erro ?>

        <div class="mb-4 p-3 rounded no-print" style="background-color: #f8f9fa;">
            <form method="GET" action="meu_financeiro.php" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>">
                </div>
                <div class="col-md-5">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>">
                </div>
                <div class="col-md-2 d-flex">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i></button>
                    <a href="meu_financeiro.php" class="btn btn-outline-secondary ms-2" title="Limpar Filtro"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card text-white bg-success h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                        <h5 class="card-title">Total Faturado (Concluídos)</h5>
                        <p class="card-text display-4 fw-bold">R$ <?= number_format($total_realizado, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card text-dark bg-warning h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-hourglass-half fa-2x mb-2"></i>
                        <h5 class="card-title">A Receber (Aceitos)</h5>
                        <p class="card-text display-4 fw-bold">R$ <?= number_format($total_a_receber, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mt-5">Serviços a Realizar (A Receber)</h3>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <colgroup>
                            <col style="width: 20%;">
                            <col style="width: 35%;">
                            <col style="width: 30%;">
                            <col style="width: 15%;">
                        </colgroup>
                        <thead><tr><th>Data</th><th>Cliente</th><th>Serviço</th><th>Valor</th></tr></thead>
                        <tbody>
                            <?php if (empty($servicos_a_receber)): ?>
                                <tr><td colspan="4" class="text-center">Nenhum serviço a receber no momento.</td></tr>
                            <?php else: foreach ($servicos_a_receber as $servico): ?>
                                <tr><td><?= date('d/m/Y', strtotime($servico['data'])) ?></td><td><?= htmlspecialchars($servico['nome_cliente']) ?></td><td><?= htmlspecialchars($servico['titulo']) ?></td><td>R$ <?= number_format($servico['preco'], 2, ',', '.') ?></td></tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <h3 class="mt-5">Histórico de Serviços Concluídos</h3>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <colgroup>
                            <col style="width: 20%;">
                            <col style="width: 35%;">
                            <col style="width: 30%;">
                            <col style="width: 15%;">
                        </colgroup>
                        <thead><tr><th>Data</th><th>Cliente</th><th>Serviço</th><th>Valor</th></tr></thead>
                        <tbody>
                            <?php if (empty($servicos_realizados)): ?>
                                <tr><td colspan="4" class="text-center">Nenhum serviço concluído ainda.</td></tr>
                            <?php else: foreach ($servicos_realizados as $servico): ?>
                                <tr><td><?= date('d/m/Y', strtotime($servico['data'])) ?></td><td><?= htmlspecialchars($servico['nome_cliente']) ?></td><td><?= htmlspecialchars($servico['titulo']) ?></td><td>R$ <?= number_format($servico['preco'], 2, ',', '.') ?></td></tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>