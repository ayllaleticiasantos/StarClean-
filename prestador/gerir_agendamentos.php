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
    // SQL CORRIGIDO: Usando as tabelas e colunas corretas (Cliente, data, hora) e buscando as coordenadas.
    $stmt = $pdo->prepare(
        "SELECT a.id, c.nome as nome_cliente, s.titulo as titulo_servico, s.descricao as descricao_servico,
                a.data, a.hora, a.status, e.logradouro, e.numero, e.bairro
         FROM Agendamento a
         JOIN Cliente c ON a.Cliente_id = c.id
         JOIN Servico s ON a.Servico_id = s.id
         JOIN Endereco e ON a.Endereco_id = e.id
         WHERE a.Prestador_id = ?
         ORDER BY a.data, a.hora"
    );
    $stmt->execute([$id_prestador_logado]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro ao buscar os agendamentos: " . $e->getMessage());
    $mensagem_erro = '<div class="alert alert-danger">Erro ao carregar agendamentos. Tente novamente.</div>';
}// Adicione esta linha para depuração:
// var_dump($_SESSION);

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
                                <th>Descrição</th>
                                <th>Endereço</th>
                                <th>Data</th>
                                <th>Hora</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($agendamentos)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Nenhum agendamento encontrado.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($agendamentos as $agendamento):
                                    $status = strtolower($agendamento['status']);
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($agendamento['nome_cliente']) ?></td>
                                        <td><?= htmlspecialchars($agendamento['titulo_servico']) ?></td>
                                        <td><?= htmlspecialchars($agendamento['descricao_servico']) ?></td>
                                        <td><?= htmlspecialchars($agendamento['logradouro']) ?>,
                                            <?= htmlspecialchars($agendamento['numero']) ?>
                                            (<?= htmlspecialchars($agendamento['bairro']) ?>)</td>
                                        <td><?= date('d/m/Y', strtotime($agendamento['data'])) ?></td>
                                        <td><?= htmlspecialchars(substr($agendamento['hora'], 0, 5)) ?></td>
                                        <td>
                                            <?php
                                            $badge_class = 'bg-secondary';
                                            switch ($status) {
                                                case 'pendente':
                                                    $badge_class = 'bg-warning text-dark';
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
                                            <span
                                                class="badge <?= $badge_class ?>"><?= htmlspecialchars(ucfirst($agendamento['status'])) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($status === 'pendente'): ?>
                                                <a href="processar_agendamento.php?id=<?= $agendamento['id'] ?>&acao=aceito"
                                                    class="btn btn-sm btn-success">Aceitar</a>
                                                <a href="processar_agendamento.php?id=<?= $agendamento['id'] ?>&acao=cancelado"
                                                    class="btn btn-sm btn-danger">Recusar</a>
                                            <?php elseif ($status === 'aceito'): ?>
                                                <a href="processar_agendamento.php?id=<?= $agendamento['id'] ?>&acao=realizado"
                                                    class="btn btn-sm btn-primary">Concluído</a>
                                            <?php endif; ?>
                                            <a href="visualizar_agendamento.php?id=<?= $agendamento['id'] ?>"
                                                class="btn btn-sm btn-info">Visualizar</a>
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