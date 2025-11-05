<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas administradores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$logs = [];
try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->query(
        "SELECT l.id, l.acao, l.detalhes, l.data_ocorrencia, a.nome as admin_nome, a.email as admin_email
         FROM log_atividades l
         JOIN administrador a ON l.admin_id = a.id
         ORDER BY l.data_ocorrencia DESC"
    );
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao carregar os logs de atividades.";
    error_log($e->getMessage());
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
        <h1 class="mb-4">Log de Atividades dos Administradores</h1>
        <p class="lead">Aqui são registradas todas as ações importantes realizadas no painel administrativo.</p>

        <?php if (isset($mensagem_erro)): ?><div class="alert alert-danger"><?= $mensagem_erro ?></div><?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Administrador</th>
                                <th>Ação Realizada</th>
                                <th>Detalhes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr><td colspan="4" class="text-center">Nenhuma atividade registrada.</td></tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i:s', strtotime($log['data_ocorrencia'])) ?></td>
                                        <td><?= htmlspecialchars($log['admin_nome']) ?><br><small class="text-muted"><?= htmlspecialchars($log['admin_email']) ?></small></td>
                                        <td><?= htmlspecialchars($log['acao']) ?></td>
                                        <td>
                                            <?php if (!empty($log['detalhes'])): ?>
                                                <pre style="white-space: pre-wrap; word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 5px;"><?= htmlspecialchars(json_encode(json_decode($log['detalhes']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
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