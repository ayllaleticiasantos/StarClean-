<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header("Location: ../pages/login.php");
    exit();
}

$mensagem_sucesso = '';
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem_sucesso = '<div class="alert alert-success">' . $_SESSION['mensagem_sucesso'] . '</div>';
    unset($_SESSION['mensagem_sucesso']);
}

$mensagem_erro = '';
if (isset($_SESSION['mensagem_erro'])) {
    $mensagem_erro = '<div class="alert alert-danger">' . $_SESSION['mensagem_erro'] . '</div>';
    unset($_SESSION['mensagem_erro']);
}

$id_prestador_logado = $_SESSION['usuario_id'];
$servicos = [];
$termo_busca = $_GET['q'] ?? '';

try {
    $pdo = obterConexaoPDO();
    $params = [$id_prestador_logado];

    $sql = "SELECT * FROM Servico WHERE prestador_id = ?";

    if (!empty($termo_busca)) {
        $sql .= " AND (titulo LIKE ? OR descricao LIKE ?)";
        $like_term = "%" . $termo_busca . "%";
        $params[] = $like_term;
        $params[] = $like_term;
    }

    $sql .= " ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $servicos = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao buscar os serviços: " . $e->getMessage();
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Meus Serviços</h1>
            <form method="GET" action="gerir_servicos.php" class="d-flex">
                <input class="form-control me-2" type="search" name="q" placeholder="Buscar por título ou descrição..." value="<?= htmlspecialchars($termo_busca) ?>">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($termo_busca)): ?>
                    <a href="gerir_servicos.php" class="btn btn-outline-secondary ms-2">Limpar</a>
                <?php endif; ?>
            </form>
        </div>
        
        <?= $mensagem_sucesso ?>
        <?= $mensagem_erro ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Descrição</th>
                                <th>Preço</th>
                                </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($servicos)): ?>
                                <tr>
                                    <?php if (!empty($termo_busca)): ?>
                                        <td colspan="3" class="text-center">Nenhum serviço encontrado para "<?= htmlspecialchars($termo_busca) ?>".</td>
                                    <?php else: ?>
                                        <td colspan="3" class="text-center">Nenhum serviço cadastrado.</td>
                                    <?php endif; ?>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($servicos as $servico): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($servico['titulo']) ?></td>
                                        <td><?= htmlspecialchars($servico['descricao']) ?></td>
                                        <td>R$ <?= number_format($servico['preco'], 2, ',', '.') ?></td>
                                        </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>