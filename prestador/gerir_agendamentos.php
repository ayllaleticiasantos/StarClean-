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

// Buscar apenas os agendamentos do prestador que está logado
$id_prestador_logado = $_SESSION['usuario_id'];
$agendamentos = [];
try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->prepare(
        // CORREÇÃO FINAL: Usando nomes de coluna simples (data e hora)
        "SELECT a.id, c.nome as nome_cliente, s.titulo as titulo_servico, a.data, a.hora, a.status 
         FROM Agendamento a
         JOIN Cliente c ON a.Cliente_id = c.id
         JOIN Servico s ON a.Servico_id = s.id
         WHERE a.Prestador_id = ?
         ORDER BY a.status DESC, a.data, a.hora"
    );
    $stmt->execute([$id_prestador_logado]);
    $agendamentos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar os agendamentos: " . $e->getMessage());
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Meus Agendamentos</h1>
        </div>
        
        <?= $mensagem_sucesso ?>
        <?= $mensagem_erro ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Serviço</th>
                                <th>Data</th>
                                <th>Hora</th>
                                <th>Estado</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($agendamentos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum agendamento encontrado.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($agendamentos as $agendamento): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($agendamento['nome_cliente']) ?></td>
                                        <td><?= htmlspecialchars($agendamento['titulo_servico']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($agendamento['data'])) ?></td>
                                        <td><?= htmlspecialchars($agendamento['hora']) ?></td>
                                        <td>
                                            <?php
                                                $badge_class = 'bg-secondary';
                                                switch ($agendamento['status']) {
                                                    case 'pendente':
                                                        $badge_class = 'bg-warning';
                                                        break;
                                                    case 'aceito':
                                                        $badge_class = 'bg-success';
                                                        break;
                                                    case 'realizado':
                                                        $badge_class = 'bg-primary';
                                                        break;
                                                    case 'cancelado':
                                                        $badge_class = 'bg-danger';
                                                        break;
                                                }
                                            ?>
                                            <span class="badge <?= $badge_class ?>"><?= htmlspecialchars(ucfirst($agendamento['status'])) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($agendamento['status'] === 'pendente'): ?>
                                                <a href="processar_agendamento.php?id=<?= $agendamento['id'] ?>&acao=aceitar" class="btn btn-sm btn-success">Aceitar</a>
                                                <a href="processar_agendamento.php?id=<?= $agendamento['id'] ?>&acao=recusar" class="btn btn-sm btn-danger">Recusar</a>
                                            <?php elseif ($agendamento['status'] === 'aceito'): ?>
                                                <a href="concluir_agendamento.php?id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-primary">Marcar como Concluído</a>
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