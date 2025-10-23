<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas clientes podem aceder a esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
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

$id_cliente = $_SESSION['usuario_id'];
$enderecos = [];
try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->prepare("SELECT * FROM Endereco WHERE Cliente_id = ?");
    $stmt->execute([$id_cliente]);
    $enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao buscar endereços: " . $e->getMessage();
}

include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>

<main class="d-flex">
    <?php include '../includes/sidebar.php'; ?>

    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gerir Endereços</h1>
            <a href="adicionar_endereco.php" class="btn btn-primary">Adicionar Novo Endereço</a>
        </div>

        <?= $mensagem_sucesso ?>
        <?= $mensagem_erro ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>CEP</th>
                                <th>Logradouro</th>
                                <th>Número</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($enderecos)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Nenhum endereço cadastrado.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($enderecos as $endereco): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($endereco['cep']) ?></td>
                                        <td><?= htmlspecialchars($endereco['logradouro']) ?></td>
                                        <td><?= htmlspecialchars($endereco['numero']) ?></td>
                                        <td>
                                            <a href="editar_endereco.php?id=<?= $endereco['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                            <a href="excluir_endereco.php?id=<?= $endereco['id'] ?>" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Tem a certeza de que deseja excluir este endereço?');">Excluir</a>
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