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
            <div class="alert alert-info">
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
                    <input type="password" class="form-control" placeholder="Digite sua senha" name="senha" id="senha" required>
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