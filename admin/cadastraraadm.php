<?php
// cadastraadm.php

// Database connection (adjust credentials as needed)
include('../config/config.php');
include('../config/db.php');

session_start();
require_once '../config/db.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $sobrenome = trim($_POST['sobrenome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $tipo = $_POST['tipo'];

    try {
        $pdo = obterConexaoPDO();
        $stmt = $pdo->prepare('INSERT INTO Administrador (nome, sobrenome, email, password, tipo) VALUES (:nome, :sobrenome, :email, :password, :tipo)');
        $stmt->execute(['nome' => $nome, 'sobrenome' => $sobrenome, 'email' => $email, 'password' => $senhaHash, 'tipo' => $tipo]);
        $mensagem = 'Administrador cadastrado com sucesso!';
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Código de erro para violação de chave única
            $mensagem = 'Erro: Email já cadastrado.';
        } else {
            $mensagem = 'Erro ao cadastrar administrador: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>
<main class="d-flex">
    <?php include '../includes/sidebar.php'; ?>

    <div class="container-fluid p-4">
        <h1 class="text-center mb-4">Cadastrar Administrador</h1>
    
        <?php if ($mensagem): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <div class="card p-4 shadow-sm" style="width: 100%; max-width: 600px; margin: auto;">
            <form action="cadastraraadm.php" method="post">
                <div class="mb-3">
                    <label for="nome_admin" class="form-label">Nome:</label>
                    <input type="text" class="form-control" name="nome" id="nome_admin">
                </div>
                <div class="mb-3">
                    <label for="sobrenome_admin" class="form-label">Sobrenome:</label>
                    <input type="text" class="form-control" name="sobrenome" id="sobrenome_admin">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">E-mail:</label>
                    <input type="email" class="form-control" name="email" id="email" required>
                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label">Senha:</label>
                    <input type="password" class="form-control" name="senha" id="senha" required>
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
                <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
            </form>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>