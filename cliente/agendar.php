<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas clientes podem aceder a esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    header("Location: ../pages/login.php");
    exit();
}

$mensagem = '';
$servico = null;
$enderecos_cliente = []; 
$id_cliente = $_SESSION['usuario_id'];

// Valida o servico_id
if (!isset($_GET['servico_id']) || !is_numeric($_GET['servico_id'])) {
    $mensagem = '<div class="alert alert-danger">ID do serviço não fornecido ou inválido.</div>';
} else {
    $servico_id = $_GET['servico_id'];
    try {
        $pdo = obterConexaoPDO();
        
        // 1. Buscar detalhes do Serviço e do Prestador
        $stmt = $pdo->prepare(
            "SELECT s.id, s.titulo, s.descricao, s.preco, s.prestador_id, p.nome_razão_social AS nome_prestador
             FROM Servico s
             JOIN Prestador p ON s.prestador_id = p.id
             WHERE s.id = ?"
        );
        $stmt->execute([$servico_id]);
        $servico = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$servico) {
            $mensagem = '<div class="alert alert-danger">Serviço não encontrado.</div>';
        }

        // 2. Buscar todos os Endereços do Cliente Logado
        $stmt_endereco = $pdo->prepare("SELECT id, logradouro, numero, bairro, cidade, uf FROM Endereco WHERE Cliente_id = ?");
        $stmt_endereco->execute([$id_cliente]);
        $enderecos_cliente = $stmt_endereco->fetchAll(PDO::FETCH_ASSOC);

        // Se nenhum endereço for encontrado, redireciona o cliente
        if (empty($enderecos_cliente)) {
            $_SESSION['mensagem_erro'] = "Nenhum endereço cadastrado. Por favor, adicione um endereço para agendar.";
            header("Location: gerir_enderecos.php");
            exit();
        }

    } catch (PDOException $e) {
        $mensagem = '<div class="alert alert-danger">Ocorreu um erro ao buscar dados essenciais.</div>';
        error_log("Erro em agendar.php: " . $e->getMessage());
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $servico && !empty($enderecos_cliente)) {
    $prestador_id = $servico['prestador_id'];
    $endereco_id = $_POST['endereco_id'] ?? null; 
    $data = $_POST['data']; 
    $hora = $_POST['hora']; 
    $observacoes = $_POST['observacoes'];
    $status = 'pendente';
    
    if (empty($endereco_id)) {
        $mensagem = '<div class="alert alert-danger">Por favor, selecione um endereço para o serviço.</div>';
    } else {
        try {
            $pdo = obterConexaoPDO();
            // CORREÇÃO FINAL: Usando nomes de coluna simples (data e hora) para o INSERT
            $stmt = $pdo->prepare(
                "INSERT INTO Agendamento (cliente_id, prestador_id, servico_id, endereco_id, data, hora, status, observacoes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$id_cliente, $prestador_id, $servico['id'], $endereco_id, $data, $hora, $status, $observacoes]);

            $_SESSION['mensagem_sucesso'] = "Agendamento solicitado com sucesso! Aguarde a confirmação do prestador.";
            header("Location: meus_agendamentos.php");
            exit();
        } catch (PDOException $e) {
            $mensagem = '<div class="alert alert-danger">Erro ao solicitar o agendamento. Verifique se a data e hora são válidas.</div>';
            error_log("Erro no INSERT de Agendamento: " . $e->getMessage());
        }
    }
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
        <h1 class="mb-4">Agendar Serviço</h1>
        <hr>

        <?= $mensagem ?>

        <?php if ($servico && !empty($enderecos_cliente)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($servico['titulo']) ?></h5>
                    <p class="card-text text-muted">Prestador: <?= htmlspecialchars($servico['nome_prestador']) ?></p>
                    <p class="card-text">Preço: <span class="text-success fw-bold">R$ <?= number_format($servico['preco'], 2, ',', '.') ?></span></p>
                    <p class="card-text"><?= htmlspecialchars($servico['descricao']) ?></p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5>Detalhes do Agendamento</h5>
                </div>
                <div class="card-body">
                    <form action="agendar.php?servico_id=<?= htmlspecialchars($servico['id']) ?>" method="post">
                        
                        <div class="mb-3">
                            <label for="endereco_id" class="form-label">Selecione o Endereço:</label>
                            <select class="form-select" id="endereco_id" name="endereco_id" required>
                                <option value="">--- Selecione um Endereço ---</option>
                                <?php foreach ($enderecos_cliente as $endereco): ?>
                                    <option value="<?= htmlspecialchars($endereco['id']) ?>">
                                        <?= htmlspecialchars($endereco['logradouro']) ?>, N° <?= htmlspecialchars($endereco['numero']) ?> - <?= htmlspecialchars($endereco['bairro']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="data" class="form-label">Data do Serviço:</label>
                            <input type="date" class="form-control" id="data" name="data" required>
                        </div>
                        <div class="mb-3">
                            <label for="hora" class="form-label">Hora do Serviço:</label>
                             <input type="time" class="form-control" id="hora" name="hora" required>
                        </div>
                        <div class="mb-3">
                            <label for="observacoes" class="form-label">Observações (opcional):</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Confirmar Agendamento</button>
                        <a href="buscar_servicos.php" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        <?php else: ?>
             <div class="alert alert-info">Não foi possível carregar os detalhes do serviço.</div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>