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
$servico_atual = null;
$id_servico = $_GET['id'] ?? null;

try {
    $pdo = obterConexaoPDO();
    
    // 1. Buscar todos os prestadores para o campo de seleção
    // Nota: A coluna 'nome' é usada para o Prestador (conforme o schema do seu banco)
    $stmt_prestadores = $pdo->query("SELECT id, nome FROM Prestador ORDER BY nome ASC");
    $prestadores = $stmt_prestadores->fetchAll(PDO::FETCH_ASSOC);

    // 2. Lógica para buscar o serviço atual (GET)
    if (empty($id_servico) || !is_numeric($id_servico)) {
        $_SESSION['mensagem_erro'] = "ID do serviço não fornecido ou inválido.";
        header("Location: dashboard.php");
        exit();
    }

    $stmt_servico = $pdo->prepare("SELECT * FROM Servico WHERE id = ?");
    $stmt_servico->execute([$id_servico]);
    $servico_atual = $stmt_servico->fetch(PDO::FETCH_ASSOC);

    if (!$servico_atual) {
        $_SESSION['mensagem_erro'] = "Serviço não encontrado.";
        header("Location: dashboard.php");
        exit();
    }
    
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
    error_log("Erro ao buscar dados: " . $e->getMessage());
    $mensagem_erro = '<div class="alert alert-danger">Erro ao carregar dados essenciais.</div>';
}

// --- 3. Lógica de Atualização (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $servico_atual) {
    // Coleta os dados do formulário
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $preco = $_POST['preco'];
    $prestador_id_novo = $_POST['prestador_id'] ?? null; 

    // Validação
    if (empty($titulo) || empty($preco) || empty($prestador_id_novo)) {
        $_SESSION['mensagem_erro'] = "O título, o preço e o prestador são obrigatórios.";
    } else {
        try {
            $pdo = obterConexaoPDO();
            
            // Executa o UPDATE no banco de dados
            $stmt = $pdo->prepare(
                "UPDATE Servico SET prestador_id = ?, titulo = ?, descricao = ?, preco = ? WHERE id = ?"
            );
            $stmt->execute([$prestador_id_novo, $titulo, $descricao, $preco, $id_servico]);

            $_SESSION['mensagem_sucesso'] = "Serviço #{$id_servico} atualizado com sucesso!";
            // Redireciona para o painel de administração (ou para a página de listagem)
            header("Location: dashboard.php"); 
            exit();

        } catch (PDOException $e) {
            $_SESSION['mensagem_erro'] = "Erro ao atualizar o serviço. Detalhes: " . htmlspecialchars($e->getMessage());
        }
    }
    // Se houver erro, recarrega a página de edição para mostrar a mensagem
    header("Location: editar_servico.php?id=" . $id_servico);
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
    <?php include '../includes/sidebar.php'; ?>

    <div class="container-fluid p-4">
        <h1 class="mb-4">Editar Serviço</h1>
        <p class="lead text-muted">Preencha o formulário para atualizar os detalhes do serviço.</p>
        <hr>
        
        <?= $mensagem_sucesso ?>
        <?= $mensagem_erro ?>

        <?php if ($servico_atual): ?>
            <div class="card border-0 shadow-sm" style="max-width: 600px;">
                <div class="card-body">
                    <form action="editar_servico.php?id=<?= htmlspecialchars($id_servico) ?>" method="post">
                        <div class="mb-3">
                            <label for="prestador_id" class="form-label">Prestador de Serviço</label>
                            <select class="form-select" id="prestador_id" name="prestador_id" required>
                                <option value="">-- Selecione o Prestador --</option>
                                <?php foreach ($prestadores as $prestador): 
                                    // Marca o prestador atual como selecionado
                                    $selected = ($prestador['id'] == $servico_atual['prestador_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= htmlspecialchars($prestador['id']) ?>" <?= $selected ?>>
                                        [ID: <?= htmlspecialchars($prestador['id']) ?>] <?= htmlspecialchars($prestador['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título do Serviço</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" value="<?= htmlspecialchars($servico_atual['titulo']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3"><?= htmlspecialchars($servico_atual['descricao']) ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="preco" class="form-label">Preço (R$)</label>
                            <input type="number" class="form-control" id="preco" name="preco" step="0.01" min="0" value="<?= htmlspecialchars($servico_atual['preco']) ?>" required>
                        </div>

                        <button type="submit" class="btn btn-warning">Salvar Alterações</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Não foi possível carregar os detalhes do serviço para edição.</div>
        <?php endif; ?>
    </div>
</main>

<?php 
include '../includes/footer.php'; 
?>