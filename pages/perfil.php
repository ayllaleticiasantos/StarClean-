<?php
session_start();
require_once '../config/db.php';

// Segurança: Se não estiver logado, redireciona para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$tipo_usuario = $_SESSION['usuario_tipo'];

// Determina a tabela correta com base no tipo de usuário
$tabela = '';
$coluna_nome = '';
switch ($tipo_usuario) {
    case 'cliente':
        $tabela = 'Cliente';
        $coluna_nome = 'nome';
        break;
    case 'prestador':
        $tabela = 'Prestador';
        $coluna_nome = 'nome_razão_social';
        break;
    case 'admin':
        $tabela = 'Administrador';
        $coluna_nome = 'nome';
        break;
}

// Lógica para processar os formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = obterConexaoPDO();

    // Formulário de atualização de dados
    if (isset($_POST['atualizar_dados'])) {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);

        $stmt = $pdo->prepare("UPDATE `$tabela` SET `$coluna_nome` = ?, email = ? WHERE id = ?");
        if ($stmt->execute([$nome, $email, $id_usuario])) {
            $_SESSION['usuario_nome'] = $nome; // Atualiza o nome na sessão também
            $_SESSION['mensagem_sucesso'] = "Dados atualizados com sucesso!";
        } else {
            $_SESSION['mensagem_erro'] = "Erro ao atualizar os dados.";
        }
    }
    
    // Formulário de alteração de senha
    if (isset($_POST['alterar_senha'])) {
        $senha_atual = $_POST['senha_atual'];
        $nova_senha = $_POST['nova_senha'];
        $confirmar_nova_senha = $_POST['confirmar_nova_senha'];

        if ($nova_senha !== $confirmar_nova_senha) {
            $_SESSION['mensagem_erro'] = "As novas senhas não correspondem.";
        } else {
            // Busca a senha atual no banco de dados para verificação
            $stmt = $pdo->prepare("SELECT password FROM `$tabela` WHERE id = ?");
            $stmt->execute([$id_usuario]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha_atual, $usuario['password'])) {
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE `$tabela` SET password = ? WHERE id = ?");
                if ($stmt->execute([$nova_senha_hash, $id_usuario])) {
                    $_SESSION['mensagem_sucesso'] = "Senha alterada com sucesso!";
                } else {
                    $_SESSION['mensagem_erro'] = "Erro ao alterar a senha.";
                }
            } else {
                $_SESSION['mensagem_erro'] = "A senha atual está incorreta.";
            }
        }
    }
    
    // Redireciona para a mesma página para evitar reenvio do formulário
    header("Location: perfil.php");
    exit();
}

// Busca os dados atuais do usuário para exibir no formulário
$pdo = obterConexaoPDO();
$stmt = $pdo->prepare("SELECT `$coluna_nome` as nome, email FROM `$tabela` WHERE id = ?");
$stmt->execute([$id_usuario]);
$usuario_atual = $stmt->fetch();


include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>

<div class="container mt-5" style="max-width: 800px; padding: botton 20px;">
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
        <div class="card-header">
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
        <div class="card-header">
            <h5>Alterar Senha</h5>
        </div>
        <div class="card-body">
            <form action="perfil.php" method="POST">
                <div class="mb-3">
                    <label for="senha_atual" class="form-label">Senha Atual</label>
                    <input type="password" class="form-control" id="senha_atual" placeholder="Digite sua senha atual" name="senha_atual" required>
                </div>
                <div class="mb-3">
                    <label for="nova_senha" class="form-label">Nova Senha</label>
                    <input type="password" class="form-control" id="nova_senha" placeholder="Digite sua nova senha" name="nova_senha" required>
                </div>
                <div class="mb-3">
                    <label for="confirmar_nova_senha" class="form-label">Confirmar Nova Senha</label>
                    <input type="password" class="form-control" id="confirmar_nova_senha" placeholder="Confirme sua nova senha" name="confirmar_nova_senha" required>
                </div>
                <button type="submit" name="alterar_senha" class="btn btn-primary">Alterar Senha</button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>