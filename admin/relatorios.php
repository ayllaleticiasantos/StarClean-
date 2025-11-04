<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas administradores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

// --- LÓGICA PARA BUSCAR DADOS FINANCEIROS ---
$mensagem_erro = '';
$ano_selecionado = $_GET['ano'] ?? date('Y'); // Pega o ano do filtro ou o ano atual
$dados_grafico = [
    'realizado' => array_fill(0, 12, 0), // Array com 12 meses, inicializados em 0
    'aceito' => array_fill(0, 12, 0)
];
$dados_status_grafico = [
    'realizado' => 0,
    'cancelado' => 0
];

try {
    $pdo = obterConexaoPDO();

    // Função para buscar dados e preencher o array do gráfico
    function buscarDadosPorStatus($pdo, $status, $ano) {
        $sql = "SELECT MONTH(a.data) as mes, SUM(s.preco) as total
                FROM Agendamento a
                JOIN Servico s ON a.Servico_id = s.id
                WHERE a.status = ? AND YEAR(a.data) = ?
                GROUP BY MONTH(a.data)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $ano]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Busca dados para serviços 'realizado'
    $resultados_realizado = buscarDadosPorStatus($pdo, 'realizado', $ano_selecionado);
    foreach ($resultados_realizado as $row) {
        $dados_grafico['realizado'][$row['mes'] - 1] = (float)$row['total'];
    }

    // Busca dados para serviços 'aceito'
    $resultados_aceito = buscarDadosPorStatus($pdo, 'aceito', $ano_selecionado);
    foreach ($resultados_aceito as $row) {
        $dados_grafico['aceito'][$row['mes'] - 1] = (float)$row['total'];
    }

    // Busca contagem de status (realizado vs cancelado) para o gráfico de pizza
    $sql_status = "SELECT status, COUNT(*) as total
                   FROM Agendamento
                   WHERE status IN ('realizado', 'cancelado') AND YEAR(data) = ?
                   GROUP BY status";
    $stmt_status = $pdo->prepare($sql_status);
    $stmt_status->execute([$ano_selecionado]);
    $resultados_status = $stmt_status->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados_status as $row) {
        $dados_status_grafico[$row['status']] = (int)$row['total'];
    }

} catch (PDOException $e) {
    $mensagem_erro = "Erro ao buscar dados financeiros: " . htmlspecialchars($e->getMessage());
    error_log("Erro no relatório financeiro do admin: " . $e->getMessage());
}
?>

<?php
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
        <h1 class="mb-4">Relatório Financeiro Anual</h1>
        <hr>

        <?php if ($mensagem_erro): ?>
            <div class="alert alert-danger"><?= $mensagem_erro ?></div>
        <?php else: ?>
            <!-- Card de Filtro -->
            <div class="card shadow-sm mb-4 no-print">
                <div class="card-body">
                    <form method="GET" action="relatorios.php" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="ano" class="form-label">Selecione o Ano:</label>
                            <select name="ano" id="ano" class="form-select">
                                <?php for ($ano = date('Y'); $ano >= 2023; $ano--): ?>
                                    <option value="<?= $ano ?>" <?= ($ano == $ano_selecionado) ? 'selected' : '' ?>><?= $ano ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-8 d-flex">
                            <button type="submit" class="btn btn-primary me-2"><i class="fas fa-eye me-1"></i>Visualizar</button>
                            <button type="button" onclick="window.print();" class="btn btn-info ms-auto" id="btn-print"><i class="fas fa-print me-1"></i>Imprimir Relatório</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card do Gráfico -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Faturamento Mensal vs. A Receber (Ano: <?= htmlspecialchars($ano_selecionado) ?>)</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoFinanceiro"></canvas>
                </div>
                <div class="card-footer text-muted">
                    <span class="me-3"><i class="fas fa-circle text-success"></i> Faturado (Realizado)</span>
                    <span><i class="fas fa-circle text-warning"></i> A Receber (Aceito)</span>
                </div>
            </div>
            <h1>Relatório de Serviços Realizados e Cancelados</h1>
            <hr>
            <!-- Card do Gráfico de Status -->
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Proporção de Serviços Realizados vs. Cancelados (Ano: <?= htmlspecialchars($ano_selecionado) ?>)</h5>
                </div>
                <div class="card-body d-flex justify-content-center">
                    <div style="max-width: 450px; width: 100%;">
                        <canvas id="graficoStatusServicos"></canvas>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <span class="me-3"><i class="fas fa-circle text-primary"></i> Serviços Realizados</span>
                    <span><i class="fas fa-circle text-danger"></i> Serviços Cancelados</span>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Verifica se o elemento canvas existe antes de criar o gráfico
    const ctx = document.getElementById('graficoFinanceiro');
    if (ctx) {
        // Converte os dados PHP para JSON para serem usados no JavaScript
        const dadosGrafico = <?= json_encode($dados_grafico); ?>;
        const dadosStatusGrafico = <?= json_encode($dados_status_grafico); ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                datasets: [
                    {
                        label: 'Faturado (R$)',
                        data: dadosGrafico.realizado,
                        backgroundColor: 'rgba(25, 135, 84, 0.7)', // Verde do Bootstrap (success)
                        borderColor: 'rgba(25, 135, 84, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'A Receber (R$)',
                        data: dadosGrafico.aceito,
                        backgroundColor: 'rgba(255, 193, 7, 0.7)', // Amarelo do Bootstrap (warning)
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });

        // Renderiza o segundo gráfico (pizza)
        const ctxStatus = document.getElementById('graficoStatusServicos');
        if (ctxStatus) {
            new Chart(ctxStatus, {
                type: 'doughnut', // Tipo pizza/rosca
                data: {
                    labels: ['Serviços Realizados', 'Serviços Cancelados'],
                    datasets: [{
                        label: 'Quantidade',
                        data: [dadosStatusGrafico.realizado, dadosStatusGrafico.cancelado],
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.7)', // Azul do Bootstrap (primary)
                            'rgba(220, 53, 69, 0.7)'  // Vermelho do Bootstrap (danger)
                        ],
                        borderColor: [
                            'rgba(13, 110, 253, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: { responsive: true }
            });
        }
    }
})
</script>

<?php include "../includes/footer.php"; ?>