<?php
session_start();
require_once '../includes/validation_helper.php'; // Inclui o nosso helper
require_once '../config/db.php';

$mensagem = '';
$token_valido = false;

// 1. Verifica se o token foi passado na URL
if (!isset($_GET['token'])) {
    die("Token não fornecido.");
}

$token = $_GET['token'];

try {
    $pdo = obterConexaoPDO();

    // 2. Busca o token no banco de dados
    $stmt = $pdo->prepare("SELECT * FROM redefinicao_senha WHERE token = ?");
    $stmt->execute([$token]);
    $pedido = $stmt->fetch();

    // 3. Verifica se o token existe e não expirou
    if ($pedido && new DateTime() < new DateTime($pedido['data_expiracao'])) {
        $token_valido = true;
        $email_usuario = $pedido['email'];
    } else {
        $mensagem = '<div class="alert alert-danger">Token inválido ou expirado. Por favor, solicite a redefinição novamente.</div>';
    }

} catch (Exception $e) {
    $mensagem = '<div class="alert alert-danger">Ocorreu um erro no sistema.</div>';
}


// 4. Se o formulário de nova senha foi enviado e o token é válido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Valida a força da nova senha
    $erros_senha = validarSenhaForte($nova_senha);

    if (!empty($erros_senha)) {
        $mensagem = '<div class="alert alert-danger">A nova senha não é forte o suficiente: <ul><li>' . implode("</li><li>", $erros_senha) . "</li></ul></div>";
    }
    elseif ($nova_senha !== $confirmar_senha) {
        $mensagem = '<div class="alert alert-danger">As senhas não correspondem.</div>';
    } else {
        // Criptografa a nova senha
        $senhaHash = password_hash($nova_senha, PASSWORD_DEFAULT);
        
        // Atualiza a senha na tabela correta (clientes, prestadores ou administradores)
        $tabela_atualizada = false;
        foreach (['Cliente', 'Prestador', 'Administrador'] as $tabela) {
            $stmt = $pdo->prepare("UPDATE `$tabela` SET password = ? WHERE email = ?");
            if ($stmt->execute([$senhaHash, $email_usuario])) {
                if ($stmt->rowCount() > 0) {
                    $tabela_atualizada = true;
                    break;
                }
            }
        }
        
        if ($tabela_atualizada) {
            // Exclui o token do banco para que não possa ser usado novamente
            $stmt = $pdo->prepare("DELETE FROM redefinicao_senha WHERE email = ?");
            $stmt->execute([$email_usuario]);

            $_SESSION['mensagem_sucesso'] = "Senha redefinida com sucesso! Pode fazer o login.";
            header("Location: login.php");
            exit();
        } else {
            $mensagem = '<div class="alert alert-danger">Erro ao atualizar a senha.</div>';
        }
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card p-4 shadow-sm" style="width: 100%; max-width: 400px;">
        <h3 class="text-center mb-4">Criar Nova Senha</h3>
        
        <?= $mensagem ?>

        <?php if ($token_valido): ?>
            <form action="redefinir-senha.php?token=<?= htmlspecialchars($token) ?>" method="post">
                <div class="mb-3">
                    <label for="nova_senha" class="form-label">Nova Senha:</label>
                    <div class="input-group">
                        <input type="password" class="form-control" placeholder="Digite sua nova senha" name="nova_senha" id="nova_senha" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}" title="A senha deve conter no mínimo 8 caracteres, incluindo maiúsculas, minúsculas, números e um caractere especial.">
                        <button class="btn btn-outline-secondary" type="button" id="toggleSenha"><i class="fas fa-eye" id="iconSenha"></i></button>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="confirmar_senha" class="form-label">Confirme a Nova Senha:</label>
                    <div class="input-group">
                        <input type="password" class="form-control" placeholder="Confirme sua nova senha" name="confirmar_senha" id="confirmar_senha" required>
                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmarSenha"><i class="fas fa-eye" id="iconConfirmarSenha"></i></button>
                        <div class="invalid-feedback">As senhas não correspondem.</div>
                    </div>
                </div>

                <!-- Requisitos da Senha (Feedback Visual) -->
                <ul id="password-requirements" class="list-unstyled mt-2 text-muted small">
                    <li id="length" class="text-danger"><i class="fas fa-times-circle me-1"></i> Mínimo de 8 caracteres</li>
                    <li id="lowercase" class="text-danger"><i class="fas fa-times-circle me-1"></i> Uma letra minúscula</li>
                    <li id="uppercase" class="text-danger"><i class="fas fa-times-circle me-1"></i> Uma letra maiúscula</li>
                    <li id="number" class="text-danger"><i class="fas fa-times-circle me-1"></i> Um número</li>
                    <li id="special" class="text-danger"><i class="fas fa-times-circle me-1"></i> Um caractere especial (!@#$%)</li>
                </ul>

                <button type="submit" class="btn btn-primary w-100">Redefinir Senha</button>
            </form>
        <?php else: ?>
            <div class="text-center">
                <a href="esqueci-senha.php" class="btn btn-primary">Solicitar Nova Redefinição</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const senhaInput = document.getElementById('nova_senha');
    const confirmarSenhaInput = document.getElementById('confirmar_senha');
    const requirements = {
        length: document.getElementById('length'),
        lowercase: document.getElementById('lowercase'),
        uppercase: document.getElementById('uppercase'),
        number: document.getElementById('number'),
        special: document.getElementById('special')
    };

    if (senhaInput) {
        function validatePassword() {
            const value = senhaInput.value;

            // Função para atualizar o requisito na UI
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
    }

    if (senhaInput && confirmarSenhaInput) {
        function checkPasswordMatch() {
            if (senhaInput.value !== confirmarSenhaInput.value && confirmarSenhaInput.value.length > 0) {
                confirmarSenhaInput.classList.add('is-invalid');
            } else {
                confirmarSenhaInput.classList.remove('is-invalid');
            }
        }

        senhaInput.addEventListener('input', checkPasswordMatch);
        confirmarSenhaInput.addEventListener('input', checkPasswordMatch);
    }

    // --- LÓGICA PARA MOSTRAR/OCULTAR SENHA ---
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

    setupTogglePassword('nova_senha', 'toggleSenha', 'iconSenha');
    setupTogglePassword('confirmar_senha', 'toggleConfirmarSenha', 'iconConfirmarSenha');
});
</script>