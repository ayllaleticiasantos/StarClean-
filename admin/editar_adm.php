<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas administradores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$pdo = obterConexaoPDO();
$mensagem_erro = '';
$mensagem_sucesso = '';

// --- 1. LÓGICA DE ATUALIZAÇÃO (QUANDO O FORMULÁRIO É ENVIADO VIA POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nome = trim($_POST['nome']);
    $sobrenome = trim($_POST['sobrenome']);
    $email = trim($_POST['email']);
    $tipo = $_POST['tipo'];
    $senha = $_POST['senha']; // Nova senha (opcional)

    if ($id && !empty($nome) && !empty($email) && !empty($tipo)) {
        try {
            // Se uma nova senha foi fornecida, crie o hash.
            // Se não, o campo da senha não será atualizado.
            if (!empty($senha)) {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    "UPDATE Administrador SET nome = ?, sobrenome = ?, email = ?, tipo = ?, password = ? WHERE id = ?"
                );
                $stmt->execute([$nome, $sobrenome, $email, $tipo, $senhaHash, $id]);
            } else {
                // Atualiza sem alterar a senha
                $stmt = $pdo->prepare(
                    "UPDATE Administrador SET nome = ?, sobrenome = ?, email = ?, tipo = ? WHERE id = ?"
                );
                $stmt->execute([$nome, $sobrenome, $email, $tipo, $id]);
            }

            $_SESSION['mensagem_sucesso'] = "Administrador atualizado com sucesso!";
            header("Location: dashboard.php"); // Redireciona para o painel
            exit();

        } catch (PDOException $e) {
            // Código 23000 é para violação de chave única (email duplicado)
            if ($e->getCode() == 23000) {
                $_SESSION['mensagem_erro'] = "Erro: O e-mail informado já está em uso por outra conta.";
            } else {
                $_SESSION['mensagem_erro'] = "Erro ao atualizar o administrador.";
            }
            // Redireciona de volta para a página de edição para mostrar o erro
            header("Location: editar_adm.php?id=" . $id);
            exit();
        }
    } else {
        $_SESSION['mensagem_erro'] = "Dados insuficientes para atualizar.";
        header("Location: dashboard.php");
        exit();
    }
}

// --- 2. LÓGICA PARA BUSCAR DADOS (QUANDO A PÁGINA É CARREGADA VIA GET) ---
$admin_atual = null;
$id_admin_para_editar = $_GET['id'] ?? null;

if ($id_admin_para_editar && is_numeric($id_admin_para_editar)) {
    try {
        $stmt = $pdo->prepare("SELECT id, nome, sobrenome, email, tipo FROM Administrador WHERE id = ?");
        $stmt->execute([$id_admin_para_editar]);
        $admin_atual = $stmt->fetch();

        if (!$admin_atual) {
            $_SESSION['mensagem_erro'] = "Administrador não encontrado.";
            header("Location: dashboard.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Erro ao buscar dados do administrador: " . $e->getMessage());
    }
} else {
    // Se não houver ID, não há quem editar.
    header("Location: dashboard.php");
    exit();
}

// --- 3. HTML DA PÁGINA ---
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
        <h1 class="mb-4">Editar Administrador</h1>

        <?php if (isset($_SESSION['mensagem_erro'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['mensagem_erro'] ?></div>
            <?php unset($_SESSION['mensagem_erro']); ?>
        <?php endif; ?>

        <div class="card p-4 shadow-sm" style="max-width: 600px; margin: auto;">
            <form action="editar_adm.php" method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars($admin_atual['id']) ?>">

                <div class="mb-3">
                    <label for="nome" class="form-label">Nome:</label>
                    <input type="text" class="form-control" name="nome" id="nome" value="<?= htmlspecialchars($admin_atual['nome']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="sobrenome" class="form-label">Sobrenome:</label>
                    <input type="text" class="form-control" name="sobrenome" id="sobrenome" value="<?= htmlspecialchars($admin_atual['sobrenome']) ?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail:</label>
                    <input type="email" class="form-control" name="email" id="email" value="<?= htmlspecialchars($admin_atual['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="senha" class="form-label">Nova Senha:</label>
                    <input type="password" class="form-control" name="senha" id="senha" placeholder="Deixe em branco para não alterar">
                    <small class="form-text text-muted">Preencha apenas se desejar alterar a senha atual.</small>
                </div>
                <div class="mb-3">
                    <label for="tipo" class="form-label">Tipo:</label>
                    <select class="form-select" name="tipo" id="tipo" required>
                        <option value="adminmaster" <?= $admin_atual['tipo'] == 'adminmaster' ? 'selected' : '' ?>>Administrador Geral</option>
                        <option value="adminusuario" <?= $admin_atual['tipo'] == 'adminusuario' ? 'selected' : '' ?>>Administrador Usuário</option>
                        <option value="adminmoderador" <?= $admin_atual['tipo'] == 'adminmoderador' ? 'selected' : '' ?>>Administrador Moderador</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Salvar Alterações</button>
                <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">Cancelar</a>
            </form>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>