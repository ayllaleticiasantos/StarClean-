<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$mensagem_erro = '';
$ano_selecionado = $_GET['ano'] ?? date('Y');
$dados_grafico = [
    'realizado' => array_fill(0, 12, 0),
    'aceito' => array_fill(0, 12, 0)
];
$servicos_realizados_detalhes = [];
$servicos_aceitos_detalhes = [];
$servicos_pendentes_detalhes = [];
$servicos_cancelados_detalhes = [];
$agendamentos_mapa = [];
$dados_status_grafico = [
    'realizado' => 0,
    'cancelado' => 0,
    'pendente' => 0
];

try {
    $pdo = obterConexaoPDO();

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

    $resultados_realizado = buscarDadosPorStatus($pdo, 'realizado', $ano_selecionado);
    foreach ($resultados_realizado as $row) {
        $dados_grafico['realizado'][$row['mes'] - 1] = (float)$row['total'];
    }

    $resultados_aceito = buscarDadosPorStatus($pdo, 'aceito', $ano_selecionado);
    foreach ($resultados_aceito as $row) {
        $dados_grafico['aceito'][$row['mes'] - 1] = (float)$row['total'];
    }

    $sql_status = "SELECT status, COUNT(*) as total
                   FROM Agendamento
                   WHERE status IN ('realizado', 'cancelado', 'pendente') AND YEAR(data) = ?
                   GROUP BY status";
    $stmt_status = $pdo->prepare($sql_status);
    $stmt_status->execute([$ano_selecionado]);
    $resultados_status = $stmt_status->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados_status as $row) {
        $dados_status_grafico[$row['status']] = (int)$row['total'];
    }

    $sql_detalhes_base = "SELECT a.data, c.nome as nome_cliente, p.nome as nome_prestador, s.titulo, s.preco, a.status
                          FROM Agendamento a
                          JOIN Cliente c ON a.Cliente_id = c.id
                          JOIN Prestador p ON a.Prestador_id = p.id
                          JOIN Servico s ON a.Servico_id = s.id
                          WHERE YEAR(a.data) = ?";

    $stmt_realizados = $pdo->prepare($sql_detalhes_base . " AND a.status = 'realizado' ORDER BY a.data DESC");
    $stmt_realizados->execute([$ano_selecionado]);
    $servicos_realizados_detalhes = $stmt_realizados->fetchAll(PDO::FETCH_ASSOC);

    $stmt_aceitos = $pdo->prepare($sql_detalhes_base . " AND a.status = 'aceito' ORDER BY a.data ASC");
    $stmt_aceitos->execute([$ano_selecionado]);
    $servicos_aceitos_detalhes = $stmt_aceitos->fetchAll(PDO::FETCH_ASSOC);

    $stmt_outros = $pdo->prepare($sql_detalhes_base . " AND a.status IN ('pendente', 'cancelado') ORDER BY a.data DESC");
    $stmt_outros->execute([$ano_selecionado]);
    foreach ($stmt_outros->fetchAll(PDO::FETCH_ASSOC) as $servico) {
        $servico['status'] === 'pendente' ? $servicos_pendentes_detalhes[] = $servico : $servicos_cancelados_detalhes[] = $servico;
    }

    $sql_mapa = "SELECT 
                    a.data, a.hora,
                    c.nome AS nome_cliente,
                    s.titulo AS titulo_servico,
                    e.latitude, e.longitude
                 FROM agendamento a
                 JOIN cliente c ON a.Cliente_id = c.id
                 JOIN servico s ON a.Servico_id = s.id
                 JOIN endereco e ON a.Endereco_id = e.id
                 WHERE e.latitude IS NOT NULL AND e.longitude IS NOT NULL";
    
    $stmt_mapa = $pdo->query($sql_mapa);
    $agendamentos_mapa = $stmt_mapa->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $mensagem_erro = "Erro ao buscar dados financeiros: " . htmlspecialchars($e->getMessage());
    error_log("Erro no relatório financeiro do admin: " . $e->getMessage());
}
?>

<?php
include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>
<style>
    .chart-container {
        position: relative;
        width: 100%;
        height: 400px;
    }
    .chart-container canvas {
        max-width: 100% !important;
        height: 100% !important;
    }

    @media print {
        .page-break-before {
            page-break-before: always;
        }
        .chart-container {
            height: 50vh;
        }
    }
</style>

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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Relatório Financeiro Anual</h1>
            <button type="button" onclick="window.print();" class="btn btn-info no-print"><i class="fas fa-print me-1"></i>Imprimir Relatório</button>
        </div>
        <hr>

        <?php if ($mensagem_erro): ?>
            <div class="alert alert-danger"><?= $mensagem_erro ?></div>
        <?php else: ?>
            <div class="d-flex justify-content-end mb-4 no-print">
                <form method="GET" action="relatorios.php" class="d-flex align-items-center gap-2">
                    <label for="ano" class="form-label mb-0">Ano:</label>
                    <select name="ano" id="ano" class="form-select" style="width: auto;">
                        <?php for ($ano = date('Y'); $ano >= 2023; $ano--): ?>
                            <option value="<?= $ano ?>" <?= ($ano == $ano_selecionado) ? 'selected' : '' ?>><?= $ano ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button>
                </form>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Faturamento Mensal vs. A Receber (Ano: <?= htmlspecialchars($ano_selecionado) ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container"><canvas id="graficoFinanceiro"></canvas></div>
                </div>
                <div class="card-footer text-muted">
                    <span class="me-3"><i class="fas fa-circle text-success"></i> Faturado (Realizado)</span>
                    <span><i class="fas fa-circle text-warning"></i> A Receber (Aceito)</span>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-success text-white"><h5 class="mb-0">Detalhes dos Serviços Faturados (Concluídos)</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead><tr><th>Data</th><th>Cliente</th><th>Prestador</th><th>Serviço</th><th class="text-end">Valor (R$)</th></tr></thead>
                            <tbody>
                                <?php if(empty($servicos_realizados_detalhes)): ?>
                                    <tr><td colspan="5" class="text-center">Nenhum serviço faturado no período.</td></tr>
                                <?php else: foreach($servicos_realizados_detalhes as $servico): ?>
                                    <tr><td><?= date('d/m/Y', strtotime($servico['data'])) ?></td><td><?= htmlspecialchars($servico['nome_cliente']) ?></td><td><?= htmlspecialchars($servico['nome_prestador']) ?></td><td><?= htmlspecialchars($servico['titulo']) ?></td><td class="text-end"><?= number_format($servico['preco'], 2, ',', '.') ?></td></tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-warning text-dark"><h5 class="mb-0">Detalhes dos Serviços a Receber (Aceitos)</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead><tr><th>Data</th><th>Cliente</th><th>Prestador</th><th>Serviço</th><th class="text-end">Valor (R$)</th></tr></thead>
                            <tbody>
                                <?php if(empty($servicos_aceitos_detalhes)): ?>
                                    <tr><td colspan="5" class="text-center">Nenhum serviço a receber no período.</td></tr>
                                <?php else: foreach($servicos_aceitos_detalhes as $servico): ?>
                                    <tr><td><?= date('d/m/Y', strtotime($servico['data'])) ?></td><td><?= htmlspecialchars($servico['nome_cliente']) ?></td><td><?= htmlspecialchars($servico['nome_prestador']) ?></td><td><?= htmlspecialchars($servico['titulo']) ?></td><td class="text-end"><?= number_format($servico['preco'], 2, ',', '.') ?></td></tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <h1 class="mt-5 page-break-before">Relatório de Serviços Realizados e Cancelados</h1>
            <hr class="no-print">
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Proporção de Serviços Realizados vs. Cancelados (Ano: <?= htmlspecialchars($ano_selecionado) ?>)</h5>
                </div>
                <div class="card-body d-flex justify-content-center">
                    <div class="chart-container" style="max-width: 450px; width: 100%;">
                        <canvas id="graficoStatusServicos"></canvas>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <span class="me-3"><i class="fas fa-circle text-primary"></i> Serviços Realizados</span>
                    <span class="me-3"><i class="fas fa-circle text-danger"></i> Serviços Cancelados</span>
                    <span><i class="fas fa-circle text-warning"></i> Serviços Pendentes</span>
                </div>
            </div>
            
            <div class="card shadow-sm mt-4">
                <div class="card-header"><h5 class="mb-0">Detalhes por Status do Agendamento</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead><tr><th>Data</th><th>Cliente</th><th>Prestador</th><th>Serviço</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if(!empty($servicos_pendentes_detalhes)): ?>
                                    <tr><td colspan="5" class="table-warning fw-bold">Serviços Pendentes</td></tr>
                                    <?php foreach($servicos_pendentes_detalhes as $servico): ?>
                                        <tr><td><?= date('d/m/Y', strtotime($servico['data'])) ?></td><td><?= htmlspecialchars($servico['nome_cliente']) ?></td><td><?= htmlspecialchars($servico['nome_prestador']) ?></td><td><?= htmlspecialchars($servico['titulo']) ?></td><td><span class="badge bg-warning text-dark">Pendente</span></td></tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if(!empty($servicos_realizados_detalhes)): ?>
                                    <tr><td colspan="5" class="table-primary fw-bold">Serviços Concluídos</td></tr>
                                    <?php foreach($servicos_realizados_detalhes as $servico): ?>
                                        <tr><td><?= date('d/m/Y', strtotime($servico['data'])) ?></td><td><?= htmlspecialchars($servico['nome_cliente']) ?></td><td><?= htmlspecialchars($servico['nome_prestador']) ?></td><td><?= htmlspecialchars($servico['titulo']) ?></td><td><span class="badge bg-primary">Realizado</span></td></tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if(!empty($servicos_cancelados_detalhes)): ?>
                                    <tr><td colspan="5" class="table-danger fw-bold">Serviços Cancelados</td></tr>
                                    <?php foreach($servicos_cancelados_detalhes as $servico): ?>
                                        <tr><td><?= date('d/m/Y', strtotime($servico['data'])) ?></td><td><?= htmlspecialchars($servico['nome_cliente']) ?></td><td><?= htmlspecialchars($servico['nome_prestador']) ?></td><td><?= htmlspecialchars($servico['titulo']) ?></td><td><span class="badge bg-danger">Cancelado</span></td></tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php 
                                    if (empty($servicos_pendentes_detalhes) && empty($servicos_realizados_detalhes) && empty($servicos_cancelados_detalhes)) {
                                        echo '<tr><td colspan="5" class="text-center">Nenhum agendamento encontrado para os status selecionados no período.</td></tr>';
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <h1 class="mt-5 page-break-before">Mapa de Agendamentos</h1>
            <hr class="no-print">
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Localização Geográfica dos Serviços</h5>
                </div>
                <div class="card-body">
                    <div id="mapaAgendamentos" style="height: 500px; width: 100%; border-radius: 8px;"></div>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const agendamentosParaMapa = <?= json_encode($agendamentos_mapa); ?>;

        const dadosGrafico = <?= json_encode($dados_grafico); ?>;
        const dadosStatusGrafico = <?= json_encode($dados_status_grafico); ?>;

    const ctx = document.getElementById('graficoFinanceiro');
    if (ctx) {
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
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                            }
                        }
                    },
                }
            }
        });
    }

    const ctxStatus = document.getElementById('graficoStatusServicos');
    if (ctxStatus) {
        new Chart(ctxStatus, {
            type: 'doughnut',
                data: {
                    labels: ['Serviços Realizados', 'Serviços Cancelados', 'Serviços Pendentes'],
                    datasets: [{
                        label: 'Quantidade',
                        data: [dadosStatusGrafico.realizado, dadosStatusGrafico.cancelado, dadosStatusGrafico.pendente],
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.7)', // Azul do Bootstrap (primary)
                            'rgba(220, 53, 69, 0.7)',  // Vermelho do Bootstrap (danger)
                            'rgba(255, 193, 7, 0.7)'   // Amarelo do Bootstrap (warning)
                        ],
                        borderColor: [
                            'rgba(13, 110, 253, 1)',
                            'rgba(220, 53, 69, 1)',
                            'rgba(255, 193, 7, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: { 
                    maintainAspectRatio: false,
                    responsive: true
                }
        });
    }

    const mapaContainer = document.getElementById('mapaAgendamentos');
    if (mapaContainer) {
        const map = L.map('mapaAgendamentos').setView([-15.793889, -47.882778], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        if (agendamentosParaMapa && agendamentosParaMapa.length > 0) {
            const locations = {};

            agendamentosParaMapa.forEach(agendamento => {
                if (agendamento.latitude && agendamento.longitude) {
                    const key = `${agendamento.latitude},${agendamento.longitude}`;
                    if (!locations[key]) {
                        locations[key] = {
                            lat: parseFloat(agendamento.latitude),
                            lon: parseFloat(agendamento.longitude),
                            cliente: agendamento.nome_cliente,
                            agendamentos: []
                        };
                    }
                    locations[key].agendamentos.push(agendamento);
                }
            });

            for (const key in locations) {
                const locationData = locations[key];
                const totalAgendamentos = locationData.agendamentos.length;

                let popupContent = `<b>Cliente:</b> ${locationData.cliente}<br>`;
                popupContent += `<b>Total de Agendamentos:</b> ${totalAgendamentos}<hr>`;
                
                locationData.agendamentos.forEach(ag => {
                    const dataFormatada = new Date(ag.data + 'T00:00:00').toLocaleDateString('pt-BR');
                    popupContent += `
                        <div style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 5px;">
                            <b>Serviço:</b> ${ag.titulo_servico}<br>
                            <b>Data:</b> ${dataFormatada}
                        </div>
                    `;
                });

                L.marker([locationData.lat, locationData.lon]).addTo(map).bindPopup(popupContent);
            }
        } else {
            mapaContainer.innerHTML = '<div class="alert alert-info">Nenhum agendamento com coordenadas válidas para exibir no mapa.</div>';
        }
    }
})
</script>

<?php include "../includes/footer.php"; ?>