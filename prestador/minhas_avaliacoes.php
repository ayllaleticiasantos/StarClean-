<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header("Location: ../pages/login.php");
    exit();
}

$id_prestador = $_SESSION['usuario_id'];
$avaliacoes = [];
$stats = ['total' => 0, 'media' => 0];
$mensagem_erro = '';
$termo_busca = $_GET['q'] ?? ''; 
$filtro_nota = $_GET['nota'] ?? ''; 

try {
    $pdo = obterConexaoPDO();
    $sql_avaliacoes = "SELECT ap.nota, ap.comentario, ap.oculto, c.nome AS nome_cliente
         FROM avaliacao_prestador ap
         JOIN cliente c ON ap.Cliente_id = c.id
         WHERE ap.Prestador_id = ? AND ap.comentario IS NOT NULL AND ap.nota IS NOT NULL";

    $params_avaliacoes = [$id_prestador];

    if (!empty($termo_busca)) {
        $sql_avaliacoes .= " AND (c.nome LIKE ? OR ap.comentario LIKE ?)";
        $like_term = "%" . $termo_busca . "%";
        $params_avaliacoes[] = $like_term;
        $params_avaliacoes[] = $like_term;
    }

    if (!empty($filtro_nota) && is_numeric($filtro_nota)) {
        $sql_avaliacoes .= " AND ap.nota = ?";
        $params_avaliacoes[] = $filtro_nota;
    }

    $sql_avaliacoes .= " ORDER BY ap.id DESC";

    $stmt_avaliacoes = $pdo->prepare($sql_avaliacoes);
    $stmt_avaliacoes->execute($params_avaliacoes);
    $avaliacoes = $stmt_avaliacoes->fetchAll(PDO::FETCH_ASSOC);

    $stmt_stats = $pdo->prepare(
        "SELECT COUNT(id) as total, AVG(nota) as media
         FROM avaliacao_prestador
         WHERE Prestador_id = ? AND nota IS NOT NULL"
    );
    $stmt_stats->execute([$id_prestador]);
    $stats_result = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    if ($stats_result) {
        $stats['total'] = (int) $stats_result['total'];
        $stats['media'] = $stats_result['media'] ? round($stats_result['media'], 1) : 0;
    }

} catch (PDOException $e) {
    $mensagem_erro = "Erro ao carregar suas avaliações.";
    error_log("Erro em minhas_avaliacoes.php: " . $e->getMessage());
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

    <div class="container-fluid p-4 flex-grow-1">
        <h1 class="mb-4">Minhas Avaliações</h1>
        <?php if ($mensagem_erro): ?><div class="alert alert-danger"><?= $mensagem_erro ?></div><?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card text-white bg-primary h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-star-half-alt fa-2x mb-2"></i>
                        <h5 class="card-title">Nota Média</h5>
                        <p class="card-text display-4 fw-bold"><?= number_format($stats['media'], 1, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card text-dark bg-light h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-comment-dots fa-2x mb-2"></i>
                        <h5 class="card-title">Total de Avaliações</h5>
                        <p class="card-text display-4 fw-bold"><?= $stats['total'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Comentários Recebidos</h5>
                <form method="GET" action="minhas_avaliacoes.php" class="d-flex align-items-center gap-2">
                    <select name="nota" class="form-select" style="width: auto;">
                        <option value="">Todas as Notas</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= ($filtro_nota == $i) ? 'selected' : '' ?>><?= $i ?> Estrela(s)</option>
                        <?php endfor; ?>
                    </select>
                    <input class="form-control" type="search" name="q" placeholder="Buscar por cliente ou comentário..." value="<?= htmlspecialchars($termo_busca) ?>" style="width: 250px;">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if (!empty($termo_busca) || !empty($filtro_nota)): ?>
                        <a href="minhas_avaliacoes.php" class="btn btn-outline-secondary" title="Limpar Filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card-body">
                <?php if (empty($avaliacoes)): ?>
                    <?php if (!empty($termo_busca) || !empty($filtro_nota)): ?>
                        <p class="text-muted text-center">Nenhuma avaliação encontrada para os filtros aplicados.</p>
                    <?php else: ?>
                        <p class="text-muted text-center">Você ainda não recebeu nenhuma avaliação com comentário.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <?php foreach ($avaliacoes as $avaliacao): ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-primary">
                                    <?= $avaliacao['oculto'] ? 'Cliente Anônimo' : htmlspecialchars($avaliacao['nome_cliente']) ?>
                                </h6>
                                <div class="text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="<?= ($i <= $avaliacao['nota']) ? 'fas' : 'far' ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="mt-2 mb-0">"<?= nl2br(htmlspecialchars($avaliacao['comentario'])) ?>"</p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>