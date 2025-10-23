<?php
session_start();
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

    if ($nova_senha !== $confirmar_senha) {
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
                    <input type="password" class="form-control" placeholder="Digite sua nova senha" name="nova_senha" id="nova_senha" required>
                </div>
                <div class="mb-3">
                    <label for="confirmar_senha" class="form-label">Confirme a Nova Senha:</label>
                    <input type="password" class="form-control" placeholder="Confirme sua nova senha" name="confirmar_senha" id="confirmar_senha" required>
                </div>
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