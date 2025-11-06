<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas administradores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$servico = null;
$mensagem_erro = '';

// Verifica se o ID do serviço foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $servico_id = $_GET['id'];

    try {
        $pdo = obterConexaoPDO();
        
        // Busca os detalhes do serviço e o nome do prestador
        $stmt = $pdo->prepare("SELECT s.id, s.titulo, s.descricao, s.preco, p.nome AS nome_prestador, p.id AS prestador_id
                               FROM Servico s
                               JOIN Prestador p ON s.prestador_id = p.id
                               WHERE s.id = ?");
        $stmt->execute([$servico_id]);
        $servico = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$servico) {
            $_SESSION['mensagem_erro'] = "Serviço não encontrado.";
            header("Location: gerir_servicos.php");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Erro ao buscar serviço para visualização: " . $e->getMessage());
        $_SESSION['mensagem_erro'] = "Erro ao carregar os detalhes do serviço.";
        header("Location: gerir_servicos.php");
        exit();
    }
} else {
    $_SESSION['mensagem_erro'] = "ID do serviço não fornecido ou inválido.";
    header("Location: gerir_servicos.php");
    exit();
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
        <h1 class="mb-4">Detalhes do Serviço</h1>
        
        <div class="card shadow-sm" style="max-width: 700px;">
            <div class="card-header"><h3><?= htmlspecialchars($servico['titulo']) ?></h3></div>
            <div class="card-body">
                <p><strong>Descrição:</strong><br><?= nl2br(htmlspecialchars($servico['descricao'] ?: 'Nenhuma descrição fornecida.')) ?></p>
                <p><strong>Prestador Responsável:</strong> <?= htmlspecialchars($servico['nome_prestador']) ?> (ID: <?= htmlspecialchars($servico['prestador_id']) ?>)</p>
                <p><strong>Preço:</strong> <span class="fw-bold text-success">R$ <?= number_format($servico['preco'], 2, ',', '.') ?></span></p>
            </div>
            <div class="card-footer">
                <a href="gerir_servicos.php" class="btn btn-secondary">Voltar para a Lista</a>
                <a href="editar_servico.php?id=<?= $servico['id'] ?>" class="btn btn-warning">Editar Serviço</a>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>