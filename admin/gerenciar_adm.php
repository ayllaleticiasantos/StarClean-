<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas administradores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$pdo = obterConexaoPDO();
$mensagem_sucesso = '';
$mensagem_erro = '';

// Lógica para exibir mensagens de feedback
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem_sucesso = '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['mensagem_sucesso'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensagem_sucesso']);
}
if (isset($_SESSION['mensagem_erro'])) {
    $mensagem_erro = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['mensagem_erro'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensagem_erro']);
}

// --- LÓGICA DE BUSCA E FILTRO ---
$administradores = [];
$termo_busca = $_GET['search'] ?? '';

try {
    $sql = "SELECT id, nome, sobrenome, email, tipo FROM Administrador";
    $params = [];

    if (!empty($termo_busca)) {
        // Adiciona a cláusula WHERE para filtrar por nome ou email
        $sql .= " WHERE nome LIKE ? OR email LIKE ?";
        $params[] = "%" . $termo_busca . "%";
        $params[] = "%" . $termo_busca . "%";
    }

    $sql .= " ORDER BY nome ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $administradores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro ao buscar administradores: " . $e->getMessage());
    $mensagem_erro = '<div class="alert alert-danger">Erro ao carregar a lista de administradores.</div>';
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

    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gerenciar Administradores</h1>
            <form method="GET" action="gerenciar_adm.php" class="d-flex align-items-center">
                <input class="form-control me-2" type="search" name="search" placeholder="Pesquisar por nome ou email" value="<?= htmlspecialchars($termo_busca) ?>">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
            </form>
            <div class="ms-3">
                <a href="cadastraraadm.php" class="btn btn-primary">Cadastrar Novo</a>
                <a href="dashboard.php" class="btn btn-secondary">Voltar ao Painel</a>
            </div>
        </div>

        <?= $mensagem_sucesso ?>
        <?= $mensagem_erro ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome Completo</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($administradores)): ?>
                                <tr><td colspan="5" class="text-center">Nenhum administrador encontrado.</td></tr>
                            <?php else: ?>
                                <?php foreach ($administradores as $admin): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($admin['id']) ?></td>
                                        <td><?= htmlspecialchars($admin['nome'] . ' ' . $admin['sobrenome']) ?></td>
                                        <td><?= htmlspecialchars($admin['email']) ?></td>
                                        <td><?= htmlspecialchars($admin['tipo']) ?></td>
                                        <td>
                                            <a href="editar_adm.php?id=<?= $admin['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                            <?php if ($admin['id'] != $_SESSION['usuario_id']): // Impede que o admin se auto-exclua ?>
                                                <a href="excluir_adm.php?id=<?= $admin['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este administrador?');">Excluir</a>
                                            <?php endif; ?>
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
