<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas prestadores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'prestador') {
    header("Location: ../pages/login.php");
    exit();
}

$id_prestador = $_SESSION['usuario_id'];
$mensagem_sucesso = '';
$mensagem_erro = '';

// --- LÓGICA PARA ADICIONAR UMA DATA DE INDISPONIBILIDADE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data_indisponivel'])) {
    $data = $_POST['data_indisponivel'];

    if (empty($data)) {
        $mensagem_erro = "Por favor, selecione uma data.";
    } else {
        try {
            $pdo = obterConexaoPDO();
            $stmt = $pdo->prepare("INSERT INTO indisponibilidade_prestador (prestador_id, data_indisponivel) VALUES (?, ?)");
            $stmt->execute([$id_prestador, $data]);
            $mensagem_sucesso = "Data marcada como indisponível com sucesso!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Código para violação de chave única
                $mensagem_erro = "Esta data já está marcada como indisponível.";
            } else {
                $mensagem_erro = "Erro ao marcar a data. Tente novamente.";
                error_log("Erro ao adicionar indisponibilidade: " . $e->getMessage());
            }
        }
    }
}

// --- LÓGICA PARA REMOVER UMA DATA DE INDISPONIBILIDADE ---
if (isset($_GET['remover_id'])) {
    $id_para_remover = $_GET['remover_id'];
    try {
        $pdo = obterConexaoPDO();
        // A cláusula `prestador_id` garante que um prestador só pode remover suas próprias datas
        $stmt = $pdo->prepare("DELETE FROM indisponibilidade_prestador WHERE id = ? AND prestador_id = ?");
        $stmt->execute([$id_para_remover, $id_prestador]);
        $mensagem_sucesso = "A data foi liberada e está disponível novamente.";
    } catch (PDOException $e) {
        $mensagem_erro = "Erro ao remover a data.";
        error_log("Erro ao remover indisponibilidade: " . $e->getMessage());
    }
}

// --- BUSCAR DATAS JÁ MARCADAS ---
$datas_indisponiveis = [];
try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->prepare("SELECT id, data_indisponivel FROM indisponibilidade_prestador WHERE prestador_id = ? ORDER BY data_indisponivel ASC");
    $stmt->execute([$id_prestador]);
    $datas_indisponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao carregar suas datas indisponíveis.";
    error_log("Erro ao buscar indisponibilidades: " . $e->getMessage());
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

    <div class="container-fluid p-4">
        <h1 class="mb-4">Gerir Minha Disponibilidade</h1>
        <p class="lead">Marque as datas em que você <strong>não</strong> estará disponível para receber novos agendamentos.</p>

        <?php if ($mensagem_sucesso): ?><div class="alert alert-success"><?= $mensagem_sucesso ?></div><?php endif; ?>
        <?php if ($mensagem_erro): ?><div class="alert alert-danger"><?= $mensagem_erro ?></div><?php endif; ?>

        <div class="row">
            <!-- Formulário para adicionar data -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5>Marcar Data como Indisponível</h5>
                    </div>
                    <div class="card-body">
                        <form action="gerir_disponibilidade.php" method="post">
                            <div class="mb-3">
                                <label for="data_indisponivel" class="form-label">Selecione a Data:</label>
                                <input type="date" class="form-control" id="data_indisponivel" name="data_indisponivel" required min="<?= date('Y-m-d') ?>">
                            </div>
                            <button type="submit" class="btn btn-danger"><i class="fas fa-ban me-2"></i>Marcar como Indisponível</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Lista de datas já marcadas -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5>Datas Já Bloqueadas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($datas_indisponiveis)): ?>
                            <p class="text-muted">Nenhuma data bloqueada. Você está disponível todos os dias.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($datas_indisponiveis as $data): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong><?= date('d/m/Y', strtotime($data['data_indisponivel'])) ?></strong>
                                        <a href="gerir_disponibilidade.php?remover_id=<?= $data['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Tem certeza que deseja liberar esta data?');"><i class="fas fa-check me-1"></i>Tornar Disponível</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>