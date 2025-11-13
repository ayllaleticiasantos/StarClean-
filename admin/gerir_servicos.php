<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$mensagem_sucesso = '';
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem_sucesso = '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['mensagem_sucesso'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensagem_sucesso']);
}

$mensagem_erro = '';
if (isset($_SESSION['mensagem_erro'])) {
    $mensagem_erro = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['mensagem_erro'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensagem_erro']);
}

$servicos = [];
$termo_busca = $_GET['q'] ?? '';

try {
    $pdo = obterConexaoPDO();
    $params = [];
    
    $sql = "SELECT s.id, s.titulo, s.descricao, s.preco, p.nome AS nome_prestador, p.id AS prestador_id
         FROM Servico s
         JOIN Prestador p ON s.prestador_id = p.id";

    if (!empty($termo_busca)) {
        $sql .= " WHERE s.titulo LIKE ? OR s.descricao LIKE ? OR p.nome LIKE ?";
        $like_term = "%" . $termo_busca . "%";
        $params = [$like_term, $like_term, $like_term];
    }

    $sql .= " ORDER BY p.nome, s.titulo ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro ao buscar os serviços do Admin: " . $e->getMessage());
    $mensagem_erro = '<div class="alert alert-danger">Erro ao carregar a lista de serviços.</div>';
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
            <div>
                <h1>Gestão de Serviços</h1>
                <p class="lead">Visualize, filtre e gerencie todos os serviços cadastrados.</p>
            </div>
            <form method="GET" action="gerir_servicos.php" class="d-flex align-items-center">
                <input class="form-control me-2" type="search" name="q" placeholder="Buscar por serviço ou prestador..." value="<?= htmlspecialchars($termo_busca) ?>" style="width: 300px;">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($termo_busca)): ?><a href="gerir_servicos.php" class="btn btn-outline-secondary ms-2">Limpar</a><?php endif; ?>
            </form>
            <a href="adicionar_servico.php" class="btn btn-primary ms-3">Adicionar Novo</a>
        </div>
        
        <?= $mensagem_sucesso ?>
        <?= $mensagem_erro ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Descrição</th>
                                <th>Prestador</th>
                                <th>Preço</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($servicos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum serviço cadastrado no sistema.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($servicos as $servico): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($servico['id']) ?></td>
                                        <td><?= htmlspecialchars($servico['titulo']) ?></td>
                                        <td><?= htmlspecialchars($servico['descricao']) ?></td>
                                        <td>[ID: <?= htmlspecialchars($servico['prestador_id']) ?>] <?= htmlspecialchars($servico['nome_prestador']) ?></td>
                                        <td>R$ <?= number_format($servico['preco'], 2, ',', '.') ?></td>
                                        <td>
                                            <a href="editar_servico.php?id=<?= $servico['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                            <a href="excluir_servico.php?id=<?= $servico['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja EXCLUIR este serviço? Esta ação é permanente.');">Excluir</a>
                                            <a href="vizualizar_servico.php?id=<?= $servico['id'] ?>" class="btn btn-sm btn-info">Visualizar</a>
                                        </td>
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