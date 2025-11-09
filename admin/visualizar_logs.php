<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas administradores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$filtro_tipo = $_GET['tipo'] ?? ''; // Captura o filtro da URL
$logs = [];
try {
    $pdo = obterConexaoPDO();

    // --- LÓGICA DE FILTRO ---
    $queries = [];

    // Query para logs de admin
    if (empty($filtro_tipo) || $filtro_tipo === 'admin') {
        $queries[] = "(SELECT 'admin' as tipo_usuario, a.nome, a.email, l.acao, l.detalhes, l.data_ocorrencia
                      FROM log_atividades l JOIN administrador a ON l.admin_id = a.id)";
    }
    // Query para logs de cliente
    if (empty($filtro_tipo) || $filtro_tipo === 'cliente') {
        $queries[] = "(SELECT 'cliente' as tipo_usuario, c.nome, c.email, l.acao, l.detalhes, l.data_ocorrencia
                      FROM log_cliente_atividades l JOIN cliente c ON l.cliente_id = c.id)";
    }
    // Query para logs de prestador
    if (empty($filtro_tipo) || $filtro_tipo === 'prestador') {
        $queries[] = "(SELECT 'prestador' as tipo_usuario, p.nome, p.email, l.acao, l.detalhes, l.data_ocorrencia
                      FROM log_prestador_atividades l JOIN prestador p ON l.prestador_id = p.id)";
    }

    // Monta a query final unindo as partes necessárias
    $sql = implode(' UNION ALL ', $queries) . " ORDER BY data_ocorrencia DESC";

    $stmt = $pdo->query($sql);
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

    <div class="container-fluid p-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-0">Log de Atividades</h1>
                <p class="lead text-muted">Ações importantes realizadas por todos os usuários no sistema.</p>
            </div>
            <!-- Formulário de Filtro -->
            <form method="GET" action="visualizar_logs.php" class="d-flex align-items-center gap-2">
                <label for="tipo" class="form-label mb-0">Filtrar por:</label>
                <select name="tipo" id="tipo" class="form-select" style="width: auto;">
                    <option value="">Todos os Usuários</option>
                    <option value="admin" <?= ($filtro_tipo === 'admin') ? 'selected' : '' ?>>Administradores</option>
                    <option value="cliente" <?= ($filtro_tipo === 'cliente') ? 'selected' : '' ?>>Clientes</option>
                    <option value="prestador" <?= ($filtro_tipo === 'prestador') ? 'selected' : '' ?>>Prestadores</option>
                </select>
                <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i></button>
                <?php if (!empty($filtro_tipo)): ?><a href="visualizar_logs.php" class="btn btn-outline-secondary">Limpar</a><?php endif; ?>
            </form>
        </div>

        <?php if (isset($mensagem_erro)): ?><div class="alert alert-danger"><?= $mensagem_erro ?></div><?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Usuário</th>
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
                                        <td>
                                            <?php
                                                $badge_class = 'bg-secondary';
                                                if ($log['tipo_usuario'] === 'admin') $badge_class = 'bg-danger';
                                                elseif ($log['tipo_usuario'] === 'prestador') $badge_class = 'bg-info text-dark';
                                                elseif ($log['tipo_usuario'] === 'cliente') $badge_class = 'bg-success';
                                            ?>
                                            <?= htmlspecialchars($log['nome']) ?><br>
                                            <small class="text-muted"><?= htmlspecialchars($log['email']) ?></small><br>
                                            <span class="badge <?= $badge_class ?> mt-1"><?= ucfirst($log['tipo_usuario']) ?></span>
                                        </td>
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