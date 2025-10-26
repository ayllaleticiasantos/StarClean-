<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas clientes podem aceder a esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    header("Location: ../pages/login.php");
    exit();
}

// Lógica para exibir mensagens de feedback
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
$mensagem_alerta = '';
if (isset($_SESSION['mensagem_alerta'])) {
    $mensagem_alerta = '<div class="alert alert-warning">' . $_SESSION['mensagem_alerta'] . '</div>';
    unset($_SESSION['mensagem_alerta']);
}


// Buscar agendamentos do cliente logado
$id_cliente_logado = $_SESSION['usuario_id'];
$agendamentos = [];
try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->prepare(
        "SELECT a.id, s.titulo AS titulo_servico, a.data, a.hora, a.status, p.nome_razão_social AS nome_prestador, s.descricao AS descricao_servico
         FROM Agendamento a
         JOIN Servico s ON a.Servico_id = s.id
         JOIN Prestador p ON a.Prestador_id = p.id
         WHERE a.Cliente_id = ?
         ORDER BY a.data, a.hora"
    );
    $stmt->execute([$id_cliente_logado]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // CORREÇÃO: Usando error_log e mensagem amigável
    error_log("Erro ao buscar agendamentos: " . $e->getMessage());
    $mensagem_erro = '<div class="alert alert-danger">Erro ao buscar agendamentos.</div>';
}

include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>

<button class="btn btn-primary d-md-none m-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"
    aria-controls="sidebarMenu">
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
        <h1 class="mb-4">Meus Agendamentos</h1>
        
        <?= $mensagem_sucesso ?>
        <?= $mensagem_erro ?>
        <?= $mensagem_alerta ?>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Serviço</th>
                                <th>Descrição</th>
                                <th>Prestador</th>
                                <th>Data</th>
                                <th>Hora</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($agendamentos)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Nenhum agendamento encontrado.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($agendamentos as $agendamento): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($agendamento['titulo_servico']) ?></td>
                                        <td><?= htmlspecialchars($agendamento['descricao_servico']) ?></td>
                                        <td><?= htmlspecialchars($agendamento['nome_prestador']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($agendamento['data'])) ?></td>
                                        <td><?= htmlspecialchars($agendamento['hora']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($agendamento['status']) ?></span></td>
                                        <td>
                                            <a href="cancelar_agendamento.php?id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-danger">Cancelar</a>
                                            <a href="visualizar_agendamento.php?id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-info">Visualizar</a>
                                            <a href="editar_agendamento.php?id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                            
                                            <?php 
                                            // CORREÇÃO APLICADA AQUI: Botão de avaliação
                                            if ($agendamento['status'] === 'realizado'): ?>
                                                <a href="avaliar_servico.php?id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-success mt-1">Avaliar</a>
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