<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas prestadores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header("Location: ../pages/login.php");
    exit();
}

$id_prestador_logado = $_SESSION['usuario_id'];
$agendamento_detalhes = null;

// 1. Validação do ID do Agendamento
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensagem_erro'] = "ID do agendamento não fornecido ou inválido.";
    header("Location: gerir_agendamentos.php");
    exit();
}

$agendamento_id = $_GET['id'];

try {
    $pdo = obterConexaoPDO();

    // 2. Busca os detalhes completos do agendamento
    $stmt = $pdo->prepare("
        SELECT a.id, s.titulo AS titulo_servico, s.descricao AS descricao_servico,
               a.data, a.hora, a.status, a.observacoes,
               c.nome AS nome_cliente, c.telefone AS telefone_cliente, e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.uf, e.cep,
               e.latitude, e.longitude
        FROM Agendamento a
        JOIN Servico s ON a.Servico_id = s.id
        JOIN Cliente c ON a.Cliente_id = c.id
        JOIN Endereco e ON a.Endereco_id = e.id
        WHERE a.id = ? AND a.Prestador_id = ?
    ");
    $stmt->execute([$agendamento_id, $id_prestador_logado]);
    $agendamento_detalhes = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Checa se o agendamento foi encontrado e pertence ao prestador
    if (!$agendamento_detalhes) {
        $_SESSION['mensagem_erro'] = "Agendamento não encontrado ou acesso não autorizado.";
        header("Location: gerir_agendamentos.php");
        exit();
    }

} catch (PDOException $e) {
    $_SESSION['mensagem_erro'] = "Erro ao buscar detalhes do agendamento.";
    error_log("Erro em visualizar_agendamento.php (prestador): " . $e->getMessage());
    header("Location: gerir_agendamentos.php");
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

        <a href="gerir_agendamentos.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Voltar para a Lista</a>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Serviço Agendado</h5>
            </div>
            <div class="card-body">
                <p><strong>Serviço:</strong> <?= htmlspecialchars($agendamento_detalhes['titulo_servico']) ?></p>
                <p><strong>Descrição do Serviço:</strong> <?= nl2br(htmlspecialchars($agendamento_detalhes['descricao_servico'])) ?></p>
                <p><strong>Cliente:</strong> <?= htmlspecialchars($agendamento_detalhes['nome_cliente']) ?></p>
                <p><strong>Contato do Cliente:</strong> <?= htmlspecialchars($agendamento_detalhes['telefone_cliente']) ?></p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Detalhes de Data e Status</h5>
            </div>
            <div class="card-body">
                <p><strong>Data do Serviço:</strong> <?= date('d/m/Y', strtotime($agendamento_detalhes['data'])) ?></p>
                <p><strong>Hora:</strong> <?= htmlspecialchars(substr($agendamento_detalhes['hora'], 0, 5)) ?></p>
                <p><strong>Status:</strong>
                    <?php
                        $badge_class = 'bg-secondary';
                        switch ($agendamento_detalhes['status']) {
                            case 'pendente':  $badge_class = 'bg-warning text-dark'; break;
                            case 'aceito':    $badge_class = 'bg-success'; break;
                            case 'realizado': $badge_class = 'bg-primary'; break;
                            case 'cancelado': $badge_class = 'bg-danger'; break;
                        }
                    ?>
                    <span class="badge <?= $badge_class ?>"><?= htmlspecialchars(ucfirst($agendamento_detalhes['status'])) ?></span>
                </p>
                <p><strong>Observações do Cliente:</strong> <?= nl2br(htmlspecialchars($agendamento_detalhes['observacoes'] ?: 'Nenhuma observação fornecida.')) ?></p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Local do Serviço</h5>
            </div>
            <div class="card-body">
                <p><strong>Endereço:</strong> <?= htmlspecialchars($agendamento_detalhes['logradouro']) ?>, N° <?= htmlspecialchars($agendamento_detalhes['numero']) ?></p>
                <p><strong>Bairro:</strong> <?= htmlspecialchars($agendamento_detalhes['bairro']) ?></p>
                <p><strong>Cidade/UF:</strong> <?= htmlspecialchars($agendamento_detalhes['cidade']) ?>/<?= htmlspecialchars($agendamento_detalhes['uf']) ?></p>
                <p><strong>CEP:</strong> <?= htmlspecialchars($agendamento_detalhes['cep']) ?></p>
                <?php if ($agendamento_detalhes['complemento']): ?>
                    <p><strong>Complemento:</strong> <?= htmlspecialchars($agendamento_detalhes['complemento']) ?></p>
                <?php endif; ?>
                
                <div id="map" style="height: 300px; width: 100%; border-radius: 8px;"></div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Passa os dados do PHP para o JavaScript de forma segura
    const lat = <?= json_encode($agendamento_detalhes['latitude'] ?? null) ?>;
    const lon = <?= json_encode($agendamento_detalhes['longitude'] ?? null) ?>;
    const enderecoCompleto = `<?= htmlspecialchars($agendamento_detalhes['logradouro'] . ', ' . $agendamento_detalhes['numero'] . ' - ' . $agendamento_detalhes['bairro']) ?>`;
    const mapContainer = document.getElementById('map');

    // Verifica se temos coordenadas válidas
    if (lat && lon) {
        // Inicializa o mapa com as coordenadas do endereço
        var map = L.map('map').setView([lat, lon], 16); // Zoom 16 para uma visão mais próxima

        // Adiciona a camada do OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        // Adiciona um marcador na localização exata
        L.marker([lat, lon]).addTo(map)
            .bindPopup(`<b>Local do Serviço</b><br>${enderecoCompleto}`)
            .openPopup();
    } else {
        // Se não houver coordenadas, exibe uma mensagem no lugar do mapa
        mapContainer.innerHTML = `
            <div class="alert alert-warning h-100 d-flex align-items-center justify-content-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Localização não disponível no mapa.
            </div>`;
        mapContainer.style.height = 'auto'; // Ajusta a altura do container
    }
});
</script>

<?php include '../includes/footer.php'; ?>
