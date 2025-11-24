<?php
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['usuario_id'])) {
    $id_usuario = $_SESSION['usuario_id'];
    $tipo_usuario = $_SESSION['usuario_tipo'];

    $pdo = obterConexaoPDO();
    $notifications = [];
    
    $link_destino = '#';
    
    try {
        if ($tipo_usuario === 'prestador') {
            $link_destino = BASE_URL . '/prestador/gerir_agendamentos.php';
            $stmt = $pdo->prepare("SELECT a.data as data, c.nome AS nome_cliente FROM Agendamento a JOIN Cliente c ON a.Cliente_id = c.id WHERE a.Prestador_id = ? AND a.status = 'pendente' AND a.notificacao_prestador_lida = FALSE ORDER BY a.data DESC LIMIT 15");
            $stmt->execute([$id_usuario]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($tipo_usuario === 'cliente') {
            $link_destino = BASE_URL . '/cliente/meus_agendamentos.php';
            $stmt = $pdo->prepare(
                "SELECT a.data, a.status, p.nome AS nome_prestador, s.titulo AS titulo_servico 
                 FROM Agendamento a 
                 JOIN Prestador p ON a.Prestador_id = p.id 
                 JOIN Servico s ON a.Servico_id = s.id 
                 WHERE a.Cliente_id = ? AND a.status IN ('aceito', 'cancelado') AND a.notificacao_cliente_lida = FALSE 
                 ORDER BY a.data DESC LIMIT 15"
            );
            $stmt->execute([$id_usuario]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($tipo_usuario === 'admin') {
            $link_destino = '#';
            $stmt = $pdo->prepare("
                SELECT id, mensagem, link, criado_em 
                FROM notificacoes 
                WHERE usuario_id = ? AND tipo_usuario = 'admin' AND lida = FALSE 
                ORDER BY criado_em DESC LIMIT 15");
            $stmt->execute([$id_usuario]); 
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
                            <?php if ($tipo_usuario === 'admin'): ?>
                                <li> 
                                    <a class="dropdown-item" href="<?= BASE_URL ?>/admin/processar_notificacao.php?id=<?= $notification['id'] ?>">
                                        <small>
                                            <i class="fas fa-user-plus me-2 text-info"></i><?= htmlspecialchars($notification['mensagem']) ?>
                                            <br>
                                            <small class="text-muted d-block"><?= date('d/m/Y H:i', strtotime($notification['criado_em'])) ?></small>
                                        </small>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li>
                                    <a class="dropdown-item" href="<?= $link_destino ?>">
                                        <small>
                                            <?php if ($tipo_usuario === 'prestador'): ?>
                                                <i class="fas fa-calendar-plus me-2 text-info"></i>
                                                Novo agendamento de <strong><?= htmlspecialchars($notification['nome_cliente']) ?></strong>
                                                <br>
                                                <small class="text-muted d-block">Para <?= date('d/m/Y', strtotime($notification['data'])) ?></small>
                                            <?php elseif ($tipo_usuario === 'cliente'): ?>
                                                <i class="fas fa-info-circle me-2 <?= $notification['status'] === 'aceito' ? 'text-success' : 'text-danger' ?>"></i>
                                                Agendamento de <strong><?= htmlspecialchars($notification['titulo_servico']) ?></strong> foi <strong><?= htmlspecialchars($notification['status']) ?></strong>.
                                            <?php endif; ?>
                                        </small>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
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