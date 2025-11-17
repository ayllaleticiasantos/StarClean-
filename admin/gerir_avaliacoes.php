<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas administradores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$mensagem_sucesso = '';
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem_sucesso = '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['mensagem_sucesso'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensagem_sucesso']);
}

$mensagem_erro = '';
if (isset($_SESSION['mensagem_erro'])) {
    $mensagem_erro = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['mensagem_erro'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensagem_erro']);
}

$avaliacoes = [];
$prestadores = [];
$filtro_prestador = $_GET['prestador_id'] ?? '';
$filtro_nota = $_GET['nota'] ?? '';
$termo_busca = $_GET['q'] ?? '';

try {
    $pdo = obterConexaoPDO();

    // Busca prestadores para o dropdown de filtro
    $stmt_prestadores = $pdo->query("SELECT id, nome FROM prestador ORDER BY nome ASC");
    $prestadores = $stmt_prestadores->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT av.id, c.nome as nome_cliente, p.nome as nome_prestador, av.nota, av.comentario, av.oculto
         FROM avaliacao_prestador av
         JOIN cliente c ON av.Cliente_id = c.id
         JOIN prestador p ON av.Prestador_id = p.id";

    $where_clauses = [];
    $params = [];

    if (!empty($filtro_prestador)) {
        $where_clauses[] = "av.Prestador_id = ?";
        $params[] = $filtro_prestador;
    }
    if (!empty($filtro_nota)) {
        $where_clauses[] = "av.nota = ?";
        $params[] = $filtro_nota;
    }
    if (!empty($termo_busca)) {
        $where_clauses[] = "(c.nome LIKE ? OR p.nome LIKE ? OR av.comentario LIKE ?)";
        $like_term = "%" . $termo_busca . "%";
        array_push($params, $like_term, $like_term, $like_term);
    }

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }

    $sql .= " ORDER BY av.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao carregar as avaliações.";
    error_log("Erro em gerir_avaliacoes.php: " . $e->getMessage());
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
        <h1 class="mb-4">Gerenciar Avaliações</h1>

        <?= $mensagem_sucesso ?>
        <?= $mensagem_erro ?>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Todas as Avaliações Registradas</h5>
                <form method="GET" action="gerir_avaliacoes.php" class="d-flex align-items-center gap-2">
                    <select name="prestador_id" class="form-select" style="width: auto;">
                        <option value="">Todos os Prestadores</option>
                        <?php foreach ($prestadores as $prestador): ?>
                            <option value="<?= $prestador['id'] ?>" <?= ($filtro_prestador == $prestador['id']) ? 'selected' : '' ?>><?= htmlspecialchars($prestador['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="nota" class="form-select" style="width: auto;">
                        <option value="">Todas as Notas</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= ($filtro_nota == $i) ? 'selected' : '' ?>><?= $i ?> Estrela(s)</option>
                        <?php endfor; ?>
                    </select>
                    <input class="form-control" type="search" name="q" placeholder="Buscar..." value="<?= htmlspecialchars($termo_busca) ?>" style="width: 200px;">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if (!empty($termo_busca) || !empty($filtro_nota) || !empty($filtro_prestador)): ?>
                        <a href="gerir_avaliacoes.php" class="btn btn-outline-secondary" title="Limpar Filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Prestador Avaliado</th>
                                <th>Nota</th>
                                <th>Comentário</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($avaliacoes)): ?>
                                <?php if (!empty($termo_busca) || !empty($filtro_nota) || !empty($filtro_prestador)): ?>
                                    <tr><td colspan="5" class="text-center">Nenhuma avaliação encontrada para os filtros aplicados.</td></tr>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">Nenhuma avaliação encontrada.</td></tr>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php foreach ($avaliacoes as $avaliacao): ?>
                                    <tr>
                                        <td><?= $avaliacao['oculto'] ? htmlspecialchars($avaliacao['nome_cliente']) . ' (Anônimo)' : htmlspecialchars($avaliacao['nome_cliente']) ?></td>
                                        <td><?= htmlspecialchars($avaliacao['nome_prestador']) ?></td>
                                        <td>
                                            <span class="text-warning">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="<?= ($i <= $avaliacao['nota']) ? 'fas' : 'far' ?> fa-star"></i>
                                                <?php endfor; ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($avaliacao['comentario'] ?: 'N/A') ?></td>
                                        <td>
                                            <a href="excluir_avaliacao.php?id=<?= $avaliacao['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta avaliação? Esta ação não pode ser desfeita.');" title="Excluir Avaliação">
                                                <i class="fas fa-trash"></i>
                                            </a>
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