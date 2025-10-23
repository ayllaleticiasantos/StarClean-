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
    $tipo = $_POST['tipo'] ?? null;

    if ($id && $tipo) {
        try {
            if ($tipo === 'cliente') {
                // Coleta todos os dados do formulário de cliente
                $nome = trim($_POST['nome']);
                $sobrenome = trim($_POST['sobrenome']);
                $email = trim($_POST['email']);
                $telefone = trim($_POST['telefone']);
                $cpf = trim($_POST['cpf']);
                $data_nascimento = trim($_POST['data_nascimento']);
                
                $stmt = $pdo->prepare("UPDATE cliente SET nome = ?, sobrenome = ?, email = ?, telefone = ?, cpf = ?, data_nascimento = ? WHERE id = ?");
                $stmt->execute([$nome, $sobrenome, $email, $telefone, $cpf, $data_nascimento, $id]);

            } elseif ($tipo === 'prestador') {
                // Coleta todos os dados do formulário de prestador
                $nome_razao_social = trim($_POST['nome_razão_social']);
                $sobrenome_nome_fantasia = trim($_POST['sobrenome_nome_fantasia']);
                $email = trim($_POST['email']);
                $telefone = trim($_POST['telefone']);
                $cpf_cnpj = trim($_POST['cpf_cnpj']);
                $especialidade = trim($_POST['especialidade']);

                $stmt = $pdo->prepare("UPDATE prestador SET nome_razão_social = ?, sobrenome_nome_fantasia = ?, email = ?, telefone = ?, cpf_cnpj = ?, especialidade = ? WHERE id = ?");
                $stmt->execute([$nome_razao_social, $sobrenome_nome_fantasia, $email, $telefone, $cpf_cnpj, $especialidade, $id]);
            }

            $_SESSION['mensagem_sucesso'] = "Utilizador atualizado com sucesso!";
            header("Location: gerir_utilizadores.php");
            exit();

        } catch (PDOException $e) {
            $_SESSION['mensagem_erro'] = "Erro ao atualizar o utilizador. Verifique se o e-mail, CPF ou CNPJ já existem.";
            header("Location: gerir_utilizadores.php");
            exit();
        }
    } else {
        $_SESSION['mensagem_erro'] = "Dados insuficientes para atualizar.";
        header("Location: gerir_utilizadores.php");
        exit();
    }
}


// --- 2. LÓGICA PARA BUSCAR DADOS (QUANDO A PÁGINA É CARREGADA VIA GET) ---
$utilizador = null;
if (isset($_GET['id']) && isset($_GET['tipo'])) {
    $id = $_GET['id'];
    $tipo = $_GET['tipo'];
    
    try {
        if ($tipo === 'cliente') {
            $stmt = $pdo->prepare("SELECT * FROM cliente WHERE id = ?");
        } elseif ($tipo === 'prestador') {
            $stmt = $pdo->prepare("SELECT * FROM prestador WHERE id = ?");
        } else {
            die("Tipo de utilizador inválido.");
        }
        $stmt->execute([$id]);
        $utilizador = $stmt->fetch();

    } catch (PDOException $e) {
        die("Erro ao buscar dados do utilizador: " . $e->getMessage());
    }

    if (!$utilizador) {
        die("Utilizador não encontrado.");
    }
} else {
    die("Parâmetros inválidos para edição.");
}

// --- 3. HTML DA PÁGINA ---
include '../includes/header.php';
include '../includes/navbar_logged_in.php';   
?>

<main class="d-flex">
    <?php include '../includes/sidebar.php'; ?>

    <div class="container-fluid p-4">
        <h1 class="mb-4">Editar Utilizador</h1>

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">A editar: <?= htmlspecialchars($tipo === 'cliente' ? $utilizador['nome'] . ' ' . $utilizador['sobrenome'] : $utilizador['nome_razão_social']) ?></h5>
            </div>
            <div class="card-body">
                <form action="editar_utilizador.php" method="post">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($utilizador['id']) ?>">
                    <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">

                    <?php if ($tipo === 'cliente'): ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome:</label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($utilizador['nome']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sobrenome" class="form-label">Sobrenome:</label>
                                <input type="text" class="form-control" id="sobrenome" name="sobrenome" value="<?= htmlspecialchars($utilizador['sobrenome']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($utilizador['email']) ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label">Telefone:</label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($utilizador['telefone']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cpf" class="form-label">CPF:</label>
                                <input type="text" class="form-control" id="cpf" name="cpf" value="<?= htmlspecialchars($utilizador['cpf']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="data_nascimento" class="form-label">Data de Nascimento:</label>
                            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($utilizador['data_nascimento']) ?>" required>
                        </div>

                    <?php elseif ($tipo === 'prestador'): ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome_razão_social" class="form-label">Nome / Razão Social:</label>
                                <input type="text" class="form-control" id="nome_razão_social" name="nome_razão_social" value="<?= htmlspecialchars($utilizador['nome_razão_social']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sobrenome_nome_fantasia" class="form-label">Sobrenome / Nome Fantasia:</label>
                                <input type="text" class="form-control" id="sobrenome_nome_fantasia" name="sobrenome_nome_fantasia" value="<?= htmlspecialchars($utilizador['sobrenome_nome_fantasia']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($utilizador['email']) ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label">Telefone:</label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($utilizador['telefone']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cpf_cnpj" class="form-label">CPF / CNPJ:</label>
                                <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" value="<?= htmlspecialchars($utilizador['cpf_cnpj']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="especialidade" class="form-label">Especialidade:</label>
                            <input type="text" class="form-control" id="especialidade" name="especialidade" value="<?= htmlspecialchars($utilizador['especialidade']) ?>" required>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    <a href="gerir_utilizadores.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>