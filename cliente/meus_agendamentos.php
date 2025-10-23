<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas clientes podem aceder a esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    header("Location: ../pages/login.php");
    exit();
}

// Buscar agendamentos do cliente logado
$id_cliente_logado = $_SESSION['usuario_id'];
$agendamentos = [];
try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->prepare(
        // CORREÇÃO: Revertendo para os nomes de coluna 'data' e 'hora'
        "SELECT a.id, s.titulo AS titulo_servico, a.data, a.hora, a.status, p.nome_razão_social AS nome_prestador
         FROM Agendamento a
         JOIN Servico s ON a.Servico_id = s.id
         JOIN Prestador p ON a.Prestador_id = p.id
         WHERE a.Cliente_id = ?
         ORDER BY a.data, a.hora"
    );
    $stmt->execute([$id_cliente_logado]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar agendamentos: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>

<main class="d-flex">
    <?php include '../includes/sidebar.php'; ?>

    <div class="container-fluid p-4">
        <h1 class="mb-4">Meus Agendamentos</h1>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Serviço</th>
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
                                    <td colspan="6" class="text-center">Nenhum agendamento encontrado.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($agendamentos as $agendamento): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($agendamento['titulo_servico']) ?></td>
                                        <td><?= htmlspecialchars($agendamento['nome_prestador']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($agendamento['data'])) ?></td>
                                        <td><?= htmlspecialchars($agendamento['hora']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($agendamento['status']) ?></span></td>
                                        <td>
                                            <a href="cancelar_agendamento.php?id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-danger">Cancelar</a>
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