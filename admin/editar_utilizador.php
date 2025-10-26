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
                                <label for="nome" class="form-label" placeholder="Digite seu nome:">Nome:</label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($utilizador['nome']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sobrenome" class="form-label" placeholder="Digite seu sobrenome:">Sobrenome:</label>
                                <input type="text" class="form-control" id="sobrenome" name="sobrenome" value="<?= htmlspecialchars($utilizador['sobrenome']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label" placeholder="Digite seu e-mail:">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($utilizador['email']) ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label" placeholder="Digite seu telefone:">Telefone:</label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($utilizador['telefone']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cpf" class="form-label" placeholder="Digite seu CPF:">CPF:</label>
                                <input type="text" class="form-control" id="cpf" name="cpf" value="<?= htmlspecialchars($utilizador['cpf']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="data_nascimento" class="form-label" placeholder="Digite sua data de nascimento:">Data de Nascimento:</label>
                            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($utilizador['data_nascimento']) ?>" required>
                        </div>

                    <?php elseif ($tipo === 'prestador'): ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome_razão_social" class="form-label" placeholder="Digite seu nome:">Nome:</label>
                                <input type="text" class="form-control" id="nome_razão_social" name="nome_razão_social" value="<?= htmlspecialchars($utilizador['nome_razão_social']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sobrenome_nome_fantasia" class="form-label" placeholder="Digite seu sobrenome:">Sobrenome:</label>
                                <input type="text" class="form-control" id="sobrenome_nome_fantasia" name="sobrenome_nome_fantasia" value="<?= htmlspecialchars($utilizador['sobrenome_nome_fantasia']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label" placeholder="Digite seu e-mail:">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($utilizador['email']) ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label" placeholder="Digite seu telefone:">Telefone:</label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($utilizador['telefone']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cpf_cnpj" class="form-label" placeholder="Digite seu CPF:">CPF/CNPJ:</label>
                                <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" value="<?= htmlspecialchars($utilizador['cpf_cnpj']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="especialidade" class="form-label" placeholder="Digite sua especialidade:">Especialidade:</label>
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
<script>
 // CORREÇÃO APLICADA AQUI: O script foi ajustado para procurar o ID correto (telefone)
    // --- MÁSCARA DE TELEFONE ---
    function mascaraTelefone(evento) {
        if (evento.key === "Backspace") return;
        let valor = evento.target.value.replace(/\D/g, '');
        valor = valor.replace(/^(\d{2})(\d)/g, '($1) $2');
        valor = valor.replace(/(\d)(\d{4})$/, '$1-$2');
        evento.target.value = valor;
    }
    
    // Procura por todos os campos com ID 'telefone'
    const inputTelefone = document.getElementById('telefone');
    if (inputTelefone) inputTelefone.addEventListener('keyup', mascaraTelefone);
    

    function validaCPF(cpf) {
        cpf = cpf.replace(/[^\d]+/g, '');
        if (cpf === '' || cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        let soma = 0, resto;
        for (let i = 1; i <= 9; i++) soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
        resto = (soma * 10) % 11;
        if ((resto === 10) || (resto === 11)) resto = 0;
        if (resto !== parseInt(cpf.substring(9, 10))) return false;
        soma = 0;
        for (let i = 1; i <= 10; i++) soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
        resto = (soma * 10) % 11;
        if ((resto === 10) || (resto === 11)) resto = 0;
        if (resto !== parseInt(cpf.substring(10, 11))) return false;
        return true;
    }
</script>