<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas clientes podem aceder a esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    header("Location: ../pages/login.php");
    exit();
}

// --- LÓGICA DE BUSCA E FILTRO ---
$servicos = [];
$termo_busca = $_GET['q'] ?? ''; // Pega o termo da URL
$mensagem_erro = '';

try {
    $pdo = obterConexaoPDO();
    $params = [];

    $sql = "SELECT s.id, s.titulo, s.descricao, s.preco, p.nome AS nome_prestador
            FROM Servico s
            JOIN Prestador p ON s.prestador_id = p.id";

    // Se houver um termo de busca, adiciona o filtro
    if (!empty($termo_busca)) {
        $sql .= " WHERE s.titulo LIKE ? OR s.descricao LIKE ? OR p.nome LIKE ?";
        $like_term = "%" . $termo_busca . "%";
        $params = [$like_term, $like_term, $like_term];
    }

    $sql .= " ORDER BY s.titulo";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao buscar os serviços: " . $e->getMessage();
    error_log($mensagem_erro);
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

    <div class="container-fluid p-4 flex-grow-1">
        <h1 class="mb-4">Buscar Serviços</h1>

        <!-- Formulário de Filtro -->
        <div class="mb-4">
            <form action="buscar_servicos.php" method="GET" class="d-flex">
                <input class="form-control me-2" type="search" name="q" placeholder="Buscar por serviço ou prestador..." value="<?= htmlspecialchars($termo_busca) ?>">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($termo_busca)): ?>
                    <a href="buscar_servicos.php" class="btn btn-outline-secondary ms-2">Limpar</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="row">
            <?php if (empty($servicos)): ?>
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        Nenhum serviço encontrado para "<?= htmlspecialchars($termo_busca) ?>". Tente uma busca diferente ou limpe o filtro.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($servicos as $servico): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($servico['titulo']) ?></h5>
                                <p class="card-text text-muted">Por: <?= htmlspecialchars($servico['nome_prestador']) ?></p>
                                <p class="card-text flex-grow-1"><?= htmlspecialchars($servico['descricao']) ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-3">
                                    <h4 class="text-success mb-0">R$ <?= number_format($servico['preco'], 2, ',', '.') ?></h4>
                                    <a href="agendar.php?servico_id=<?= htmlspecialchars($servico['id']) ?>" class="btn btn-primary">Agendar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>