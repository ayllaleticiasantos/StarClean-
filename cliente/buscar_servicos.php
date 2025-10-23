<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas clientes podem aceder a esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    header("Location: ../pages/login.php");
    exit();
}

// Buscar todos os serviços ativos no banco de dados
$servicos = [];
try {
    $pdo = obterConexaoPDO();
    // Consulta corrigida para buscar todos os serviços e os seus prestadores
    $stmt = $pdo->prepare(
        "SELECT s.id, s.titulo, s.descricao, s.preco, p.nome_razão_social AS nome_prestador
         FROM Servico s
         JOIN Prestador p ON s.prestador_id = p.id
         ORDER BY s.titulo"
    );
    $stmt->execute();
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Em caso de erro, termina a execução e exibe uma mensagem amigável
    die("Erro ao buscar os serviços: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>

<main class="d-flex">
    <?php include '../includes/sidebar.php'; ?>

    <div class="container-fluid p-4">
        <h1 class="mb-4">Buscar Serviços</h1>
        
        <div class="row">
            <?php if (empty($servicos)): ?>
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        Nenhum serviço disponível no momento.
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