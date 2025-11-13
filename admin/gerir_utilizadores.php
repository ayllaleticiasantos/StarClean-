<?php
session_start();
require_once '../config/db.php';

$tipo_admin = $_SESSION['admin_tipo'] ?? '';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin' || $tipo_admin !== 'adminmaster') {
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

$termo_pesquisa = $_GET['q'] ?? '';
$clientes = [];
$prestadores = [];

try {
    $pdo = obterConexaoPDO();
    $params = [];
    
    $sql_clientes = "SELECT id, nome, sobrenome, telefone, cpf, email, criado_em FROM Cliente";
    if (!empty($termo_pesquisa)) {
        $sql_clientes .= " WHERE nome LIKE ? OR sobrenome LIKE ? OR email LIKE ?";
        $params_clientes = ["%$termo_pesquisa%", "%$termo_pesquisa%", "%$termo_pesquisa%"];
    }
    $sql_clientes .= " ORDER BY nome ASC";
    $stmt_clientes = $pdo->prepare($sql_clientes);
    $stmt_clientes->execute($params_clientes ?? []);
    $clientes = $stmt_clientes->fetchAll();

    $sql_prestadores = "SELECT id, CONCAT(nome, ' ', sobrenome) AS nome_completo, cpf, email, telefone, criado_em FROM Prestador";
    if (!empty($termo_pesquisa)) {
        $sql_prestadores .= " WHERE nome LIKE ? OR sobrenome LIKE ? OR email LIKE ? OR cpf LIKE ?";
        $params_prestadores = ["%$termo_pesquisa%", "%$termo_pesquisa%", "%$termo_pesquisa%", "%$termo_pesquisa%"];
    }
    $sql_prestadores .= " ORDER BY nome ASC";
    $stmt_prestadores = $pdo->prepare($sql_prestadores);
    $stmt_prestadores->execute($params_prestadores ?? []);
    $prestadores = $stmt_prestadores->fetchAll();

} catch (PDOException $e) {
    error_log("Erro ao buscar utilizadores: " . $e->getMessage());
    $mensagem_erro = '<div class="alert alert-danger">Não foi possível carregar os dados dos utilizadores. Tente novamente mais tarde.</div>';
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

        <h1 class="mb-4">Gestão de Utilizadores</h1>

        <?= $mensagem_sucesso ?>
        <?= $mensagem_erro ?>

        <div class="mb-4 d-flex justify-content-end">
            <form action="gerir_utilizadores.php" method="GET" class="d-flex align-items-center">
                <input class="form-control me-2" type="search" name="q" placeholder="Pesquisar por nome, email, CPF/CNPJ..." value="<?= htmlspecialchars($termo_pesquisa) ?>" aria-label="Pesquisar">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($termo_pesquisa)): ?>
                    <a href="gerir_utilizadores.php" class="btn btn-outline-secondary ms-2" title="Limpar Pesquisa"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <h3 class="mt-5">Clientes</h3>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th style="width: 20%;">Nome Completo</th>
                                <th style="width: 15%; min-width: 120px;">CPF</th>
                                <th style="width: 25%;">Email</th>
                                <th style="width: 12%; min-width: 120px;">Telefone</th>
                                <th style="width: 13%; min-width: 150px;">Data de Criação</th>
                                <th style="width: 10%; min-width: 140px;">Ações</th> </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clientes)): ?>
                                <tr><td colspan="7" class="text-center">Nenhum cliente encontrado.</td></tr>
                            <?php else: ?>
                                <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cliente['id']) ?></td>
                                        <td><?= htmlspecialchars($cliente['nome'] . ' ' . $cliente['sobrenome']) ?></td>
                                        <td><?= htmlspecialchars($cliente['cpf']) ?></td>
                                        <td><?= htmlspecialchars($cliente['email']) ?></td>
                                        <td><?= htmlspecialchars($cliente['telefone']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($cliente['criado_em'])) ?></td>
                                        <td style="white-space: nowrap;">
                                            <a href="editar_utilizador.php?id=<?= $cliente['id'] ?>&tipo=cliente" class="btn btn-warning btn-sm">Editar</a>
                                            <a href="excluir_utilizador.php?id=<?= $cliente['id'] ?>&tipo=cliente" class="btn btn-danger btn-sm" onclick="return confirm('Tem a certeza?');">Excluir</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <h3 class="mt-5">Prestadores de Serviço</h3>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th style="width: 20%;">Nome Completo</th>
                                <th style="width: 15%; min-width: 120px;">CPF/CNPJ</th>
                                <th style="width: 25%;">Email</th>
                                <th style="width: 12%; min-width: 120px;">Telefone</th>
                                <th style="width: 13%; min-width: 150px;">Data de Criação</th>
                                <th style="width: 10%; min-width: 140px;">Ações</th> </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($prestadores)): ?>
                                <tr><td colspan="7" class="text-center">Nenhum prestador encontrado.</td></tr>
                            <?php else: ?>
                                <?php foreach ($prestadores as $prestador): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($prestador['id']) ?></td>
                                        <td><?= htmlspecialchars($prestador['nome_completo']) ?></td>
                                        <td><?= htmlspecialchars($prestador['cpf']) ?></td>
                                        <td><?= htmlspecialchars($prestador['email']) ?></td>
                                        <td><?= htmlspecialchars($prestador['telefone']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($prestador['criado_em'])) ?></td>
                                        <td style="white-space: nowrap;">
                                            <a href="editar_utilizador.php?id=<?= $prestador['id'] ?>&tipo=prestador" class="btn btn-warning btn-sm">Editar</a>
                                            <a href="excluir_utilizador.php?id=<?= $prestador['id'] ?>&tipo=prestador" class="btn btn-danger btn-sm" onclick="return confirm('Tem a certeza?');">Excluir</a>
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
<?php 
include '../includes/footer.php'; 
?>