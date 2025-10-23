<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas prestadores podem aceder a esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header("Location: ../pages/login.php");
    exit();
}

// Lógica para exibir mensagens de sucesso ou erro
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

// Buscar apenas os serviços do prestador que está logado
$id_prestador_logado = $_SESSION['usuario_id'];
$servicos = [];
try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->prepare("SELECT * FROM Servico WHERE prestador_id = ? ORDER BY id DESC");
    $stmt->execute([$id_prestador_logado]);
    $servicos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar os serviços: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>

<main class="d-flex">
    <?php include '../includes/sidebar.php'; ?>

    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Meus Serviços</h1>
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
                                    <td colspan="3" class="text-center">Nenhum serviço cadastrado.</td>
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