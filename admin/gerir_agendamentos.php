<?php
session_start();
require_once '../config/db.php';

// 1. LÓGICA PHP DA PÁGINA
// Segurança: Apenas administradores podem aceder a esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$agendamentos = [];
$termo_busca = $_GET['q'] ?? '';
$mensagem_erro = '';

try {
    $pdo = obterConexaoPDO();
    $params = [];

    $sql = "SELECT a.id, c.nome AS nome_cliente, p.nome AS nome_prestador, 
          s.titulo AS titulo_servico, a.data, a.hora, a.status, s.descricao AS descricao_servico
         FROM Agendamento a
         JOIN Cliente c ON a.Cliente_id = c.id
         JOIN Prestador p ON a.Prestador_id = p.id
         JOIN Servico s ON a.Servico_id = s.id";

    if (!empty($termo_busca)) {
        $sql .= " WHERE c.nome LIKE ? OR p.nome LIKE ? OR s.titulo LIKE ? OR a.status LIKE ?";
        $like_term = "%" . $termo_busca . "%";
        $params = [$like_term, $like_term, $like_term, $like_term];
    }

    $sql .= " ORDER BY a.data DESC, a.hora DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Melhor prática: logar o erro e mostrar uma mensagem amigável
    error_log("Erro ao buscar agendamentos: " . $e->getMessage());
    $mensagem_erro = '<div class="alert alert-danger">Não foi possível carregar os agendamentos. Tente novamente mais tarde.</div>';
}

// 2. INCLUSÃO DO CABEÇALHO E NAVBAR
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>Gestão de Agendamentos</h1>
                <p class="lead">Visualize e filtre todos os agendamentos do sistema.</p>
            </div>
            <form method="GET" action="gerir_agendamentos.php" class="d-flex align-items-center">
                <input class="form-control me-2" type="search" name="q" placeholder="Buscar por cliente, prestador, serviço..." value="<?= htmlspecialchars($termo_busca) ?>" style="width: 300px;">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($termo_busca)): ?>
                    <a href="gerir_agendamentos.php" class="btn btn-outline-secondary ms-2">Limpar</a>
                <?php endif; ?>
            </form>
        </div>

        <?= $mensagem_erro ?>

        <div class="table-responsive mt-4">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Prestador</th>
                        <th>Serviço</th>
                        <th>Descrição</th>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($agendamentos)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Nenhum agendamento encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($agendamentos as $agendamento): ?>
                            <tr>
                                <td><?= htmlspecialchars($agendamento['id']) ?></td>
                                <td><?= htmlspecialchars($agendamento['nome_cliente']) ?></td>
                                <td><?= htmlspecialchars($agendamento['nome_prestador']) ?></td>
                                <td><?= htmlspecialchars($agendamento['titulo_servico']) ?></td>
                                <td><?= htmlspecialchars($agendamento['descricao_servico']) ?></td>
                                <td><?= date('d/m/Y', strtotime($agendamento['data'])) ?></td>
                                <td><?= htmlspecialchars(substr($agendamento['hora'], 0, 5)) ?></td>
                                <td>
                                    <?php
                                        
                                        $badge_class = 'bg-secondary';
                                        switch ($agendamento['status']) {
                                            case 'pendente':  $badge_class = 'bg-warning text-dark'; break; 
                                            case 'aceito':    $badge_class = 'bg-success'; break;
                                            case 'realizado': $badge_class = 'bg-primary'; break;
                                            case 'cancelado': $badge_class = 'bg-danger'; break;
                                        }
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= htmlspecialchars(ucfirst($agendamento['status'])) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php 
// 5. INCLUSÃO DO RODAPÉ
include '../includes/footer.php';
?>