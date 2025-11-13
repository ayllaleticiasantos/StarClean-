<?php
session_start();
require_once '../includes/validation_helper.php'; // Inclui o nosso helper
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$tipo_usuario = $_SESSION['usuario_tipo'];

$tabela = '';
$coluna_nome = '';
switch ($tipo_usuario) {
    case 'cliente':
        $tabela = 'Cliente';
        $coluna_nome = 'nome';
        break;
    case 'prestador':
        $tabela = 'Prestador';
        $coluna_nome = 'nome';
        break;
    case 'admin':
        $tabela = 'Administrador';
        $coluna_nome = 'nome';
        break;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = obterConexaoPDO();

    if (isset($_POST['atualizar_dados'])) {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);

        $stmt = $pdo->prepare("UPDATE `$tabela` SET `$coluna_nome` = ?, email = ? WHERE id = ?");
        if ($stmt->execute([$nome, $email, $id_usuario])) {
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['mensagem_sucesso'] = "Dados atualizados com sucesso!";
        } else {
            $_SESSION['mensagem_erro'] = "Erro ao atualizar os dados.";
        }
    }
    
    if (isset($_POST['alterar_senha'])) {
        $senha_atual = $_POST['senha_atual'];
        $nova_senha = $_POST['nova_senha'];
        $confirmar_nova_senha = $_POST['confirmar_nova_senha'];

        $erros_senha = validarSenhaForte($nova_senha);

        if (!empty($erros_senha)) {
            $_SESSION['mensagem_erro'] = "A nova senha não é forte o suficiente: <ul><li>" . implode("</li><li>", $erros_senha) . "</li></ul>";
        } elseif ($nova_senha !== $confirmar_nova_senha) {
            $_SESSION['mensagem_erro'] = "As novas senhas não correspondem.";
        } else {
            $stmt = $pdo->prepare("SELECT password FROM `$tabela` WHERE id = ?");
            $stmt->execute([$id_usuario]);
            $usuario = $stmt->fetch();

            if (!$usuario || !password_verify($senha_atual, $usuario['password'])) {
                $_SESSION['mensagem_erro'] = "A senha atual está incorreta.";
            } else {
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt_update = $pdo->prepare("UPDATE `$tabela` SET password = ? WHERE id = ?");
                if ($stmt_update->execute([$nova_senha_hash, $id_usuario])) {
                    $_SESSION['mensagem_sucesso'] = "Senha alterada com sucesso!";
                } else {
                    $_SESSION['mensagem_erro'] = "Ocorreu um erro ao alterar a senha.";
                }
            }
        }
    }
    
    header("Location: perfil.php");
    exit();
}
$pdo = obterConexaoPDO();
$stmt = $pdo->prepare("SELECT `$coluna_nome` as nome, email FROM `$tabela` WHERE id = ?");
$stmt->execute([$id_usuario]);
$usuario_atual = $stmt->fetch();


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
    
    <h1>Meu Perfil</h1>
    <hr>

    <?php 
    if (isset($_SESSION['mensagem_sucesso'])) {
        echo '<div class="alert alert-success">' . $_SESSION['mensagem_sucesso'] . '</div>';
        unset($_SESSION['mensagem_sucesso']);
    }
    if (isset($_SESSION['mensagem_erro'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['mensagem_erro'] . '</div>';
        unset($_SESSION['mensagem_erro']);
    }
    ?>

    <div class="card shadow-sm mb-5">
        <div class="card-header responsive">
            <h5>Dados Pessoais</h5>
        </div>
        <div class="card-body">
            <form action="perfil.php" method="POST">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome Completo</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($usuario_atual['nome']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($usuario_atual['email']) ?>" required>
                </div>
                <button type="submit" name="atualizar_dados" class="btn btn-primary">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-5">
        <div class="card-header responsive">
            <h5>Alterar Senha</h5>
        </div>
        <div class="card-body">
            <form action="perfil.php" method="POST">
                <div class="mb-3">
                    <label for="senha_atual" class="form-label">Senha Atual</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="senha_atual" placeholder="Digite sua senha atual" name="senha_atual" required>
                        <button class="btn btn-outline-secondary" type="button" id="toggleSenhaAtual"><i class="fas fa-eye" id="iconSenhaAtual"></i></button>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="nova_senha" class="form-label">Nova Senha</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="nova_senha" placeholder="Digite sua nova senha" name="nova_senha" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}" title="A senha deve conter no mínimo 8 caracteres, incluindo maiúsculas, minúsculas, números e um caractere especial.">
                        <button class="btn btn-outline-secondary" type="button" id="toggleNovaSenha"><i class="fas fa-eye" id="iconNovaSenha"></i></button>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="confirmar_nova_senha" class="form-label">Confirmar Nova Senha</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirmar_nova_senha" placeholder="Confirme sua nova senha" name="confirmar_nova_senha" required>
                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmarNovaSenha"><i class="fas fa-eye" id="iconConfirmarNovaSenha"></i></button>
                    </div>
                </div>

                <ul id="password-requirements" class="list-unstyled mt-2 text-muted small">
                    <li id="length" class="text-danger"><i class="fas fa-times-circle me-1"></i> Mínimo de 8 caracteres</li>
                    <li id="lowercase" class="text-danger"><i class="fas fa-times-circle me-1"></i> Uma letra minúscula</li>
                    <li id="uppercase" class="text-danger"><i class="fas fa-times-circle me-1"></i> Uma letra maiúscula</li>
                    <li id="number" class="text-danger"><i class="fas fa-times-circle me-1"></i> Um número</li>
                    <li id="special" class="text-danger"><i class="fas fa-times-circle me-1"></i> Um caractere especial (!@#$%)</li>
                </ul>

                <button type="submit" name="alterar_senha" class="btn btn-primary">Alterar Senha</button>
            </form>
        </div>
    </div>
</div>
</main>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const senhaInput = document.getElementById('nova_senha');
    const requirements = {
        length: document.getElementById('length'),
        lowercase: document.getElementById('lowercase'),
        uppercase: document.getElementById('uppercase'),
        number: document.getElementById('number'),
        special: document.getElementById('special')
    };

    function validatePassword() {
        const value = senhaInput.value;

        const updateRequirement = (req, isValid) => {
            if (isValid) {
                req.classList.remove('text-danger');
                req.classList.add('text-success');
                req.querySelector('i').className = 'fas fa-check-circle me-1';
            } else {
                req.classList.remove('text-success');
                req.classList.add('text-danger');
                req.querySelector('i').className = 'fas fa-times-circle me-1';
            }
        };

        updateRequirement(requirements.length, value.length >= 8);
        updateRequirement(requirements.lowercase, /[a-z]/.test(value));
        updateRequirement(requirements.uppercase, /[A-Z]/.test(value));
        updateRequirement(requirements.number, /\d/.test(value));
        updateRequirement(requirements.special, /[\W_]/.test(value));
    }

    senhaInput.addEventListener('input', validatePassword);

    function setupTogglePassword(inputId, buttonId, iconId) {
        const input = document.getElementById(inputId);
        const button = document.getElementById(buttonId);
        const icon = document.getElementById(iconId);

        if (input && button && icon) {
            button.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
            });
        }
    }
    setupTogglePassword('senha_atual', 'toggleSenhaAtual', 'iconSenhaAtual');
    setupTogglePassword('nova_senha', 'toggleNovaSenha', 'iconNovaSenha');
    setupTogglePassword('confirmar_nova_senha', 'toggleConfirmarNovaSenha', 'iconConfirmarNovaSenha');
});
</script>