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
$termo_busca = $_GET['q'] ?? ''; // NOVO: Captura o termo de busca
$status_filtro = $_GET['status'] ?? ''; // NOVO: Captura o status do filtro
$agendamentos = [];
try {
    $pdo = obterConexaoPDO();
    $params = [$id_cliente_logado];
    $where_clauses = [];

    $sql = "SELECT a.id, s.id AS servico_id, s.titulo AS titulo_servico, a.data, a.hora, a.status, p.nome AS nome_prestador, s.descricao AS descricao_servico
            FROM Agendamento a
            JOIN Servico s ON a.Servico_id = s.id
            JOIN Prestador p ON a.Prestador_id = p.id
            WHERE a.Cliente_id = ?";

    // Adiciona filtro de busca por texto
    if (!empty($termo_busca)) {
        $where_clauses[] = "(s.titulo LIKE ? OR s.descricao LIKE ? OR p.nome LIKE ?)";
        $like_term = "%" . $termo_busca . "%";
        array_push($params, $like_term, $like_term, $like_term);
    }

    if (!empty($status_filtro)) {
        $where_clauses[] = "a.status = ?";
        $params[] = $status_filtro;
    }

    if (!empty($where_clauses)) {
        $sql .= " AND " . implode(" AND ", $where_clauses);
    }

    $sql .= " ORDER BY a.data DESC, a.hora DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
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

    <div class="container-fluid p-4 flex-grow-1">
        <h1 class="mb-4">Meus Agendamentos</h1>
        
        <?= $mensagem_sucesso ?>
        <?= $mensagem_erro ?>
        <?= $mensagem_alerta ?>

        <!-- Formulário de Filtro e Busca (sem card) -->
        <div class="d-flex justify-content-end mb-4">
            <form method="GET" action="meus_agendamentos.php" class="d-flex align-items-center gap-2">
                <input class="form-control" type="search" name="q" placeholder="Buscar por serviço, prestador..." value="<?= htmlspecialchars($termo_busca) ?>">
                <select name="status" id="status" class="form-select" style="width: auto;">
                    <option value="">Todos os Status</option>
                    <option value="pendente" <?= ($status_filtro === 'pendente') ? 'selected' : '' ?>>Pendente</option>
                    <option value="aceito" <?= ($status_filtro === 'aceito') ? 'selected' : '' ?>>Aceito</option>
                    <option value="realizado" <?= ($status_filtro === 'realizado') ? 'selected' : '' ?>>Realizado</option>
                    <option value="cancelado" <?= ($status_filtro === 'cancelado') ? 'selected' : '' ?>>Cancelado</option>
                    <option value="remarcado" <?= ($status_filtro === 'remarcado') ? 'selected' : '' ?>>Remarcado</option>
                </select>
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                <!-- O botão de limpar aparece se qualquer filtro estiver ativo -->
                <?php if (!empty($status_filtro) || !empty($termo_busca)): ?><a href="meus_agendamentos.php" class="btn btn-outline-secondary">Limpar</a><?php endif; ?>
            </form>
        </div>
        
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
                                    <?php if (!empty($termo_busca) || !empty($status_filtro)): ?>
                                        <td colspan="7" class="text-center">Nenhum agendamento encontrado para os filtros aplicados.</td>
                                    <?php else: ?>
                                        <td colspan="7" class="text-center">Nenhum agendamento encontrado.</td>
                                    <?php endif; ?>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($agendamentos as $agendamento): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($agendamento['titulo_servico']) ?></td>
                                        <td><?= htmlspecialchars($agendamento['descricao_servico']) ?></td>
                                        <td><?= htmlspecialchars($agendamento['nome_prestador']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($agendamento['data'])) ?></td>
                                        <td><?= htmlspecialchars($agendamento['hora']) ?></td>
                                        <td>
                                            <?php
                                                $status = $agendamento['status'];
                                                $badge_class = 'bg-secondary';
                                                if ($status === 'pendente') $badge_class = 'bg-warning text-dark';
                                                elseif ($status === 'aceito') $badge_class = 'bg-success';
                                                elseif ($status === 'realizado') $badge_class = 'bg-primary';
                                                elseif ($status === 'cancelado') $badge_class = 'bg-danger';
                                                elseif ($status === 'remarcado') $badge_class = 'bg-light text-dark border';
                                            ?>
                                            <span class="badge <?= $badge_class ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
                                        </td>
                                        <td class="d-flex gap-1">
                                            <a href="visualizar_agendamento.php?id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-info" title="Visualizar Detalhes"><i class="fas fa-eye"></i></a>
                                            <?php if ($status === 'pendente' || $status === 'aceito'): ?>
                                                <a href="editar_agendamento.php?id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-warning" title="Editar Data/Hora"><i class="fas fa-edit"></i></a>
                                                <a href="cancelar_agendamento.php?id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-danger" title="Cancelar Agendamento" onclick="return confirm('Tem certeza que deseja cancelar este agendamento?');"><i class="fas fa-times"></i></a>
                                            <?php elseif ($status === 'realizado'): ?>
                                                <a href="avaliar_servico.php?id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-success" title="Avaliar Serviço"><i class="fas fa-star"></i></a>
                                            <?php elseif ($status === 'cancelado'): ?>
                                                <a href="agendar.php?servico_id=<?= $agendamento['servico_id'] ?>&remarcar_id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-primary" title="Remarcar Serviço">
                                                    <i class="fas fa-redo"></i>
                                                </a>
                                            <?php endif; // Nenhuma ação é exibida para status 'remarcado' ?>
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