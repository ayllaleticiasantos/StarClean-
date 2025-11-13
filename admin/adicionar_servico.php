<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$mensagem_sucesso = '';
$mensagem_erro = '';
$prestadores = [];

try {
    $pdo = obterConexaoPDO();
    
    $stmt = $pdo->query("SELECT id, nome FROM Prestador ORDER BY nome ASC");
    $prestadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_SESSION['mensagem_sucesso'])) {
        $mensagem_sucesso = '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['mensagem_sucesso'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        unset($_SESSION['mensagem_sucesso']);
    }
    if (isset($_SESSION['mensagem_erro'])) {
        $mensagem_erro = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['mensagem_erro'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        unset($_SESSION['mensagem_erro']);
    }

} catch (PDOException $e) {
    error_log("Erro ao buscar prestadores: " . $e->getMessage());
    $mensagem_erro = '<div class="alert alert-danger">Erro ao carregar a lista de prestadores.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $preco = $_POST['preco'];
    $prestador_id = $_POST['prestador_id'] ?? null;

    if (empty($titulo) || empty($preco) || empty($prestador_id)) {
        $_SESSION['mensagem_erro'] = "O título, o preço e o prestador são obrigatórios.";
    } else {
        try {
            $pdo = obterConexaoPDO();
            
            $stmt = $pdo->prepare("INSERT INTO Servico (prestador_id, titulo, descricao, preco) VALUES (?, ?, ?, ?)");
            $stmt->execute([$prestador_id, $titulo, $descricao, $preco]);

            $_SESSION['mensagem_sucesso'] = "Serviço adicionado com sucesso e atrelado ao Prestador ID: " . $prestador_id;
            header("Location: adicionar_servico.php");
            exit();

        } catch (PDOException $e) {
            $_SESSION['mensagem_erro'] = "Erro ao adicionar o serviço. Detalhes: " . htmlspecialchars($e->getMessage());
        }
    }    registrar_log_admin($id_admin_logado, " Adicionou um serviço ao prestador com ID $prestador_id.");

    header("Location: adicionar_servico.php");
    exit();
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
    <?php 
    include '../includes/sidebar.php'; 
    ?>

    <div class="container-fluid p-4 flex-grow-1">
        <h1 class="mb-4">Cadastrar Novo Serviço</h1>
        <?= $mensagem_sucesso ?>
        <?= $mensagem_erro ?>

        <div class="card border-0 shadow-sm" style="max-width: 600px;">
            <div class="card-body">
                <form action="adicionar_servico.php" method="post">
                    <a href="dashboard.php" class="btn btn-secondary m-1">Voltar ao Painel</a>

                    <div class="mb-3">
                        <label for="prestador_id" class="form-label">Prestador de Serviço</label>
                        <select class="form-select" id="prestador_id" name="prestador_id" required>
                            <option value="">-- Selecione o Prestador --</option>
                            <?php foreach ($prestadores as $prestador): ?>
                                <option value="<?= htmlspecialchars($prestador['id']) ?>">
                                    [ID: <?= htmlspecialchars($prestador['id']) ?>] <?= htmlspecialchars($prestador['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título do Serviço</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ex: Limpeza de que tipo...">
                    </div>
                    
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" placeholder="Descreva os detalhes do serviço, o que está incluso, etc."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="preco" class="form-label">Preço (R$)</label>
                        <input type="text" class="form-control" id="preco" name="preco" required placeholder="Ex: R$ 50,00">
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar Serviço</button>
                    <a href="gerir_servicos.php" class="btn btn-info">Gerir Serviços</a>

                </form>
            </div>
        </div>      
    </div>
</main>

<?php 
include '../includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const precoInput = document.getElementById('preco');

    function formatarMoeda(e) {
        let valor = e.target.value.replace(/\D/g, '');

        valor = (parseFloat(valor) / 100).toFixed(2);

        e.target.value = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor);
    }

    precoInput.addEventListener('input', formatarMoeda);
});
</script>