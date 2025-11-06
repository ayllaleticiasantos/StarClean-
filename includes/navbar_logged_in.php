<?php
// Inclui o arquivo de conexão com o banco de dados
require_once __DIR__ . '/../config/db.php';

// Verifica se o usuário está logado
if (isset($_SESSION['usuario_id'])) {
    $id_usuario = $_SESSION['usuario_id'];
    $tipo_usuario = $_SESSION['usuario_tipo'];

    $pdo = obterConexaoPDO();
    $notifications = [];
    
    $link_destino = '#'; // Link padrão
    
    try {
        if ($tipo_usuario === 'prestador') {
            $link_destino = BASE_URL . '/prestador/gerir_agendamentos.php';
            // --- MODIFICADO: Busca apenas notificações não lidas ---
            $stmt = $pdo->prepare("SELECT a.data as data, c.nome AS nome_cliente FROM Agendamento a JOIN Cliente c ON a.Cliente_id = c.id WHERE a.Prestador_id = ? AND a.status = 'pendente' AND a.notificacao_prestador_lida = FALSE ORDER BY a.data DESC LIMIT 5");
            $stmt->execute([$id_usuario]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($tipo_usuario === 'cliente') {
            $link_destino = BASE_URL . '/cliente/meus_agendamentos.php';
            // --- MODIFICADO: Busca apenas notificações não lidas ---
            $stmt = $pdo->prepare("SELECT a.data as data, p.nome AS nome_prestador, s.titulo AS titulo_servico FROM Agendamento a JOIN Prestador p ON a.Prestador_id = p.id JOIN Servico s ON a.Servico_id = s.id WHERE a.Cliente_id = ? AND a.status = 'aceito' AND a.notificacao_cliente_lida = FALSE ORDER BY a.data DESC LIMIT 5");
            $stmt->execute([$id_usuario]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($tipo_usuario === 'admin') {
            $link_destino = BASE_URL . '/admin/gerir_agendamentos.php';
            // --- MODIFICADO: Busca apenas notificações não lidas ---
            $stmt = $pdo->prepare(
                "SELECT a.data, s.titulo, s.preco, p.nome AS nome_prestador
                 FROM Agendamento a
                 JOIN Servico s ON a.Servico_id = s.id
                 JOIN Prestador p ON a.Prestador_id = p.id
                 WHERE a.status = 'realizado' AND a.notificacao_admin_lida = FALSE ORDER BY a.data DESC LIMIT 5"
            );
            $stmt->execute();
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log("Erro ao buscar notificações: " . $e->getMessage());
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-primary shadow-sm fixed-top">
    <div class="container-fluid">
        
        <a href="<?= BASE_URL ?>/index.php">
            <img src="<?= BASE_URL ?>/img/LogoPrimary.png" alt="StarClean" height="60" class="d-inline-block align-text-top">
        </a>
        <div class="d-flex align-items-center">

            <div class="dropdown me-3">
                <a href="#" class="nav-link" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell fs-5 text-white"></i>
                    <?php if (!empty($notifications)): ?>
                        <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">
                            <?= count($notifications) ?>
                        </span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                    <li><h6 class="dropdown-header">Notificações</h6></li>
                    <?php if (empty($notifications)): ?>
                        <li><a class="dropdown-item" href="#">
                            <small class="text-muted">Nenhuma nova notificação.</small>
                        </a></li>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <li><a class="dropdown-item" href="<?= $link_destino ?>">
                                <small>
                                    <?php if ($tipo_usuario === 'prestador'): ?>
                                        <b>Novo agendamento!</b><br>
                                        <?= htmlspecialchars($notification['nome_cliente']) ?> agendou um serviço.
                                    <?php elseif ($tipo_usuario === 'cliente'): ?>
                                        <b>Agendamento Aceito!</b><br>
                                        Seu serviço com <?= htmlspecialchars($notification['nome_prestador']) ?> foi aceito.
                                    <?php elseif ($tipo_usuario === 'admin'): ?>
                                        <b>Serviço Concluído!</b><br>
                                        "<?= htmlspecialchars($notification['titulo']) ?>" por <?= htmlspecialchars($notification['nome_prestador']) ?>.
                                        <br>
                                        <span class="text-success fw-bold">Valor: R$ <?= number_format($notification['preco'], 2, ',', '.') ?></span>
                                    <?php endif; ?>
                                </small>
                                <br>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($notification['data'])) ?></small>
                            </a></li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <!-- Botão para marcar como lidas -->
                        <li><a class="dropdown-item text-center text-primary" href="<?= BASE_URL ?>/includes/marcar_notificacoes_lidas.php">
                            <i class="fas fa-check-double me-1"></i>Marcar todas como lidas
                        </a></li>
                    <?php endif; ?>
                    
                </ul>
            </div>

            <div class="dropdown">
                <a href="#" class="nav-link" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle fs-3 text-white"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><h6 class="dropdown-header">Olá, <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Convidado') ?>!</h6></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/pages/perfil.php"><i class="fas fa-user-edit me-2"></i>Meu Perfil</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/pages/configuracoes.php"><i class="fas fa-cog me-2"></i>Configurações</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/pages/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                </ul>
            </div>

        </div>
    </div>
</nav>