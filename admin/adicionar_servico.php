<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas administradores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$mensagem_sucesso = '';
$mensagem_erro = '';
$prestadores = [];

try {
    $pdo = obterConexaoPDO();
    
    // Buscar todos os prestadores para o campo de seleção
    $stmt = $pdo->query("SELECT id, nome_razão_social FROM Prestador ORDER BY nome_razão_social ASC");
    $prestadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lógica para exibir mensagens de feedback
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

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados do formulário
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $preco = $_POST['preco'];
    $prestador_id = $_POST['prestador_id'] ?? null; // ID do prestador selecionado

    // Validação
    if (empty($titulo) || empty($preco) || empty($prestador_id)) {
        $_SESSION['mensagem_erro'] = "O título, o preço e o prestador são obrigatórios.";
    } else {
        try {
            $pdo = obterConexaoPDO();
            
            // Insere o serviço no banco de dados, ATRELADO ao Prestador selecionado
            $stmt = $pdo->prepare("INSERT INTO Servico (prestador_id, titulo, descricao, preco) VALUES (?, ?, ?, ?)");
            $stmt->execute([$prestador_id, $titulo, $descricao, $preco]);

            $_SESSION['mensagem_sucesso'] = "Serviço adicionado com sucesso e atrelado ao Prestador ID: " . $prestador_id;
            header("Location: adicionar_servico.php"); // Redireciona para evitar reenvio do formulário
            exit();

        } catch (PDOException $e) {
            $_SESSION['mensagem_erro'] = "Erro ao adicionar o serviço. Detalhes: " . htmlspecialchars($e->getMessage());
        }
    }
    // Se houver erro, redireciona de volta para o formulário para exibir a mensagem
    header("Location: adicionar_servico.php");
    exit();
}

include '../includes/header.php';
include '../includes/navbar_logged_in.php';

/* REMOVIDA A INCLUSÃO DE SIDEBAR AQUI
include '../includes/sidebar.php'; 
*/
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
    // CORREÇÃO: A inclusão correta da sidebar e o início da div de conteúdo
    // é feita dentro da tag <main>
    include '../includes/sidebar.php'; 
    ?>

    <div class="container-fluid p-4">
        <h1 class="mb-4">Cadastrar Novo Serviço</h1>
        <?= $mensagem_sucesso ?>
        <?= $mensagem_erro ?>

        <div class="card border-0 shadow-sm" style="max-width: 600px;">
            <div class="card-body">
                <form action="adicionar_servico.php" method="post">
                    
                    <div class="mb-3">
                        <label for="prestador_id" class="form-label">Prestador de Serviço</label>
                        <select class="form-select" id="prestador_id" name="prestador_id" required>
                            <option value="">-- Selecione o Prestador --</option>
                            <?php foreach ($prestadores as $prestador): ?>
                                <option value="<?= htmlspecialchars($prestador['id']) ?>">
                                    [ID: <?= htmlspecialchars($prestador['id']) ?>] <?= htmlspecialchars($prestador['nome_razão_social']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="titulo" class="form-label" placeholder="Titulo do Serviço">Título do Serviço</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descricao" class="form-label" placeholder="Descrição do Serviço">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="preco" class="form-label" placeholder="Digite o preço do Serviço">Preço (R$)</label>
                        <input type="number" class="form-control" id="preco" name="preco" step="0.01" min="0" required placeholder="Ex: 50.00">
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar Serviço</button>
                    <a href="dashboard.php" class="btn btn-secondary">Voltar ao Painel</a>
                </form>
            </div>
        </div>
    </div>
</main>

<?php 
include '../includes/footer.php'; 
?>