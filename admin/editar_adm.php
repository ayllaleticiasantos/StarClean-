<?php
session_start();
require_once '../includes/validation_helper.php'; 
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$pdo = obterConexaoPDO();
$mensagem_erro = '';
$mensagem_sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nome = trim($_POST['nome']);
    $sobrenome = trim($_POST['sobrenome']);
    $email = trim($_POST['email']);
    $tipo = $_POST['tipo'];
    $senha = $_POST['senha']; 

    if ($id && !empty($nome) && !empty($email) && !empty($tipo)) {
        try {
            if (!empty($senha)) {
                $erros_senha = validarSenhaForte($senha);
                if (!empty($erros_senha)) {
                    $_SESSION['mensagem_erro'] = "A nova senha não é forte o suficiente: <ul><li>" . implode("</li><li>", $erros_senha) . "</li></ul>";
                    header("Location: editar_adm.php?id=" . $id);
                    exit();
                }

                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    "UPDATE Administrador SET nome = ?, sobrenome = ?, email = ?, tipo = ?, password = ? WHERE id = ?"
                );
                $stmt->execute([$nome, $sobrenome, $email, $tipo, $senhaHash, $id]);
            } else {
                $stmt = $pdo->prepare(
                    "UPDATE Administrador SET nome = ?, sobrenome = ?, email = ?, tipo = ? WHERE id = ?"
                );
                $stmt->execute([$nome, $sobrenome, $email, $tipo, $id]);
            //     registrar_log_admin($id_admin_logado, "Excluiu um utilizador do tipo $tipo com ID $id.");
            }

            $_SESSION['mensagem_sucesso'] = "Administrador atualizado com sucesso!";
            header("Location: dashboard.php");
            exit();

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['mensagem_erro'] = "Erro: O e-mail informado já está em uso por outra conta.";
            } else {
                $_SESSION['mensagem_erro'] = "Erro ao atualizar o administrador.";
            }
            header("Location: editar_adm.php?id=" . $id);
            exit();
        }
    } else {
        $_SESSION['mensagem_erro'] = "Dados insuficientes para atualizar.";
        header("Location: dashboard.php");
        exit();
    }
}

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
    header("Location: dashboard.php");
    registrar_log_admin($id_admin_logado, "Editou um utilizador do tipo $tipo com ID $id.");

    exit();
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
                    <div class="input-group">
                        <input type="password" class="form-control" name="senha" id="senha" placeholder="Deixe em branco para não alterar" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}|" title="A senha deve conter no mínimo 8 caracteres, incluindo maiúsculas, minúsculas, números e um caractere especial, ou ser deixada em branco.">
                        <button class="btn btn-outline-secondary" type="button" id="toggleSenha"><i class="fas fa-eye" id="iconSenha"></i></button>
                    </div>
                    <small class="form-text text-muted">Preencha apenas se desejar alterar a senha atual. Se preenchido, deve ser uma senha forte.</small>
                    
                    <ul id="password-requirements" class="list-unstyled mt-2 text-muted small">
                        <li id="length" class="text-danger"><i class="fas fa-times-circle me-1"></i> Mínimo de 8 caracteres</li>
                        <li id="lowercase" class="text-danger"><i class="fas fa-times-circle me-1"></i> Uma letra minúscula</li>
                        <li id="uppercase" class="text-danger"><i class="fas fa-times-circle me-1"></i> Uma letra maiúscula</li>
                        <li id="number" class="text-danger"><i class="fas fa-times-circle me-1"></i> Um número</li>
                        <li id="special" class="text-danger"><i class="fas fa-times-circle me-1"></i> Um caractere especial (!@#$%)</li>
                    </ul>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const senhaInput = document.getElementById('senha');
    const requirementsList = document.getElementById('password-requirements');
    const requirements = {
        length: document.getElementById('length'),
        lowercase: document.getElementById('lowercase'),
        uppercase: document.getElementById('uppercase'),
        number: document.getElementById('number'),
        special: document.getElementById('special')
    };

    function validatePassword() {
        const value = senhaInput.value;

        if (value.length === 0) {
            requirementsList.style.display = 'none';
            return;
        }
        requirementsList.style.display = 'block';

        const updateRequirement = (req, isValid) => {
            req.className = isValid ? 'text-success' : 'text-danger';
            req.querySelector('i').className = isValid ? 'fas fa-check-circle me-1' : 'fas fa-times-circle me-1';
        };

        updateRequirement(requirements.length, value.length >= 8);
        updateRequirement(requirements.lowercase, /[a-z]/.test(value));
        updateRequirement(requirements.uppercase, /[A-Z]/.test(value));
        updateRequirement(requirements.number, /\d/.test(value));
        updateRequirement(requirements.special, /[\W_]/.test(value));
    }

    senhaInput.addEventListener('input', validatePassword);
    validatePassword(); // Executa uma vez ao carregar para esconder a lista se o campo estiver vazio

    const toggleButton = document.getElementById('toggleSenha');
    const icon = document.getElementById('iconSenha');

    if (toggleButton && senhaInput && icon) {
        toggleButton.addEventListener('click', function() {
            const type = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
            senhaInput.setAttribute('type', type);
            
            icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        });
    }
});
</script>