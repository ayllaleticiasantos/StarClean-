<?php
include('../config/config.php');
include('../config/db.php');
include('../includes/validation_helper.php'); // Inclui o nosso helper

session_start();
require_once '../config/db.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $sobrenome = trim($_POST['sobrenome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $tipo = $_POST['tipo'];

    $erros_senha = validarSenhaForte($senha); 

    if (!empty($erros_senha)) {
        $mensagem = "A senha não é forte o suficiente: <ul><li>" . implode("</li><li>", $erros_senha) . "</li></ul>";
    } else {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $pdo = obterConexaoPDO();
            $stmt = $pdo->prepare('INSERT INTO Administrador (nome, sobrenome, email, password, tipo) VALUES (:nome, :sobrenome, :email, :password, :tipo)');
            $stmt->execute(['nome' => $nome, 'sobrenome' => $sobrenome, 'email' => $email, 'password' => $senhaHash, 'tipo' => $tipo]);
            $mensagem = 'Administrador cadastrado com sucesso!';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mensagem = 'Erro: Email já cadastrado.';
            } else {
                $mensagem = 'Erro ao cadastrar administrador: ' . $e->getMessage();
            }
        }
    }
       registrar_log_admin($id_admin_logado, "Cadastrou um administrador do tipo $tipo.");
 
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
       <div>
        <h1 class="text-dark mb-4"> Cadastrar Administrador</h1>
        <a href="gerenciar_adm.php" class="btn btn-secondary mb-4 align-bottom-end">Gerenciar Administradores</a>
       </div>

        <?php if ($mensagem): ?>
            <div class="alert <?= strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-danger' ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?> 
            

        <div class="card p-4 shadow-sm" style="width: 100%; max-width: 600px;">
            <form action="cadastraraadm.php" method="post">
                <div class="mb-3">
                    <label for="nome_admin" class="form-label" placeholder="Digite seu nome:">Nome:</label>
                    <input type="text" class="form-control" placeholder="Digite seu nome" name="nome" id="nome_admin">
                </div>
                <div class="mb-3">
                    <label for="sobrenome_admin" class="form-label" placeholder="Digite seu sobrenome:">Sobrenome:</label>
                    <input type="text" class="form-control" placeholder="Digite seu sobrenome" name="sobrenome" id="sobrenome_admin">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label" placeholder="Digite seu e-mail:">E-mail:</label>
                    <input type="email" class="form-control" placeholder="Digite seu e-mail" name="email" id="email" required>
                </div>

                <div class="mb-3"> 
                    <label for="senha" class="form-label" placeholder="Digite sua senha:">Senha:</label>
                    <div class="input-group">
                        <input type="password" class="form-control" placeholder="Digite sua senha" name="senha" id="senha" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}" title="A senha deve conter no mínimo 8 caracteres, incluindo maiúsculas, minúsculas, números e um caractere especial.">
                        <button class="btn btn-outline-secondary" type="button" id="toggleSenha"><i class="fas fa-eye" id="iconSenha"></i></button>
                        <div id="password-feedback" class="invalid-feedback">
                            A senha deve atender aos requisitos.
                        </div>
                    </div>
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
                        <option value="">Selecione o tipo</option>
                        <option value="adminmaster">Administrador Geral</option>
                        <option value="adminusuario">Administrador Usuário</option>
                        <option value="adminmoderador">Administrador Moderador</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-20">Cadastrar</button>
            </form>
            
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const senhaInput = document.getElementById('senha');
    const requirements = {
        length: document.getElementById('length'),
        lowercase: document.getElementById('lowercase'),
        uppercase: document.getElementById('uppercase'),
        number: document.getElementById('number'),
        special: document.getElementById('special')
    };

    function validatePassword() {
        const value = senhaInput.value;
        let allValid = true;

        const updateRequirement = (req, isValid) => {
            if (isValid) {
                req.classList.remove('text-danger');
                req.classList.add('text-success');
                req.querySelector('i').className = 'fas fa-check-circle me-1';
            } else {
                req.classList.remove('text-success');
                req.classList.add('text-danger');
                req.querySelector('i').className = 'fas fa-times-circle me-1';
                allValid = false;
            }
        };

        updateRequirement(requirements.length, value.length >= 8);
        updateRequirement(requirements.lowercase, /[a-z]/.test(value));
        updateRequirement(requirements.uppercase, /[A-Z]/.test(value));
        updateRequirement(requirements.number, /\d/.test(value));
        updateRequirement(requirements.special, /[\W_]/.test(value));
    }

    senhaInput.addEventListener('input', validatePassword);

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