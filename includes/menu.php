<?php
// Garante que a BASE_URL está definida
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}
$tipo_usuario = $_SESSION['usuario_tipo'] ?? '';
?>

<ul class="nav flex-column mt-2h100">
    <?php // Menu para Admin ?>
    <?php if ($tipo_usuario === 'admin'): ?>
        <li class="nav-item">
            <a class="nav-link text-dark" href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-chart-line fa-fw me-2"></i>Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark" href="<?= BASE_URL ?>/admin/gerir_utilizadores.php"><i class="fas fa-users fa-fw me-2"></i>Gerir Utilizadores</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark" href="<?= BASE_URL ?>/admin/gerir_agendamentos.php"><i class="fas fa-calendar-check fa-fw me-2"></i>Gerir Agendamentos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark" href="<?= BASE_URL ?>/admin/adicionar_servico.php"><i class="fas fa-briefcase fa-fw me-2"></i>Cadastrar Serviço</a>
        </li>

    <?php // Menu para Prestador ?>
    <?php elseif ($tipo_usuario === 'prestador'): ?>
        <li class="nav-item">
            <a class="nav-link text-dark" href="<?= BASE_URL ?>/prestador/dashboard.php"><i class="fas fa-chart-line fa-fw me-2"></i>Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark" href="<?= BASE_URL ?>/prestador/gerir_servicos.php"><i class="fas fa-briefcase fa-fw me-2"></i>Meus Serviços</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark" href="<?= BASE_URL ?>/prestador/gerir_agendamentos.php"><i class="fas fa-calendar-alt fa-fw me-2"></i>Agendamentos</a>
        </li>

    <?php // Menu para Cliente ?>
    <?php elseif ($tipo_usuario === 'cliente'): ?>
        <li class="nav-item">
            <a class="nav-link text-dark" href="<?= BASE_URL ?>/cliente/dashboard.php"><i class="fas fa-tachometer-alt fa-fw me-2"></i>Meu Painel</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark" href="<?= BASE_URL ?>/cliente/buscar_servicos.php"><i class="fas fa-search fa-fw me-2"></i>Buscar Serviços</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark" href="<?= BASE_URL ?>/cliente/meus_agendamentos.php"><i class="fas fa-calendar-check fa-fw me-2"></i>Meus Agendamentos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark" href="<?= BASE_URL ?>/cliente/gerir_enderecos.php"><i class="fas fa-map-marker-alt fa-fw me-2"></i>Gerir Endereços</a>
        </li>
    <?php endif; ?>
</ul>