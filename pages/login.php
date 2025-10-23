<?php
// Sempre inicie a sessão no topo
session_start();

// Inclui o arquivo de conexão com o banco de dados
require_once '../config/db.php';

$mensagem_erro = '';
$mensagem_sucesso = '';

// Mensagem de sucesso vinda do cadastro
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem_sucesso = '<div class="alert alert-success">' . $_SESSION['mensagem_sucesso'] . '</div>';
    unset($_SESSION['mensagem_sucesso']);
}

// Lista de tabelas e destinos (em ordem de prioridade)
// Admin é o primeiro a ser verificado para garantir prioridade de login
$tipos_usuarios = [
    'admin'     => ['tabela' => 'Administrador', 'destino' => '../admin/dashboard.php'],
    'prestador' => ['tabela' => 'Prestador', 'destino' => '../prestador/dashboard.php'],
    'cliente'   => ['tabela' => 'Cliente', 'destino' => '../cliente/dashboard.php'],
];


// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Apenas email e senha são necessários (o campo 'tipo' foi removido do formulário)
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    // 1. Validação básica
    if (empty($email) || empty($senha)) {
        $mensagem_erro = '<div class="alert alert-danger">Por favor, preencha o e-mail e a senha.</div>';
    } else {
        $usuario_encontrado = false;
        
        try {
            $pdo = obterConexaoPDO();

            // 2. Itera sobre as tabelas para encontrar o usuário
            foreach ($tipos_usuarios as $tipo => $dados) {
                $tabela = $dados['tabela'];
                $destino = $dados['destino'];

                // Ajusta a coluna do nome para a tabela Prestador
                $coluna_nome_db = ($tipo === 'prestador') ? 'nome_razão_social' : 'nome';
                
                // Busca o usuário pelo e-mail
                $stmt = $pdo->prepare("SELECT id, $coluna_nome_db AS nome, email, password FROM `$tabela` WHERE email = ?");
                $stmt->execute([$email]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                // Se o usuário for encontrado na tabela atual, verifica a senha
                if ($usuario) {
                    if (password_verify($senha, $usuario['password'])) {
                        // Login bem-sucedido!
                        $_SESSION['usuario_id']     = $usuario['id'];
                        $_SESSION['usuario_nome']   = $usuario['nome'];
                        $_SESSION['usuario_tipo']   = $tipo; // Armazena o tipo encontrado ('admin', 'prestador', ou 'cliente')
                        
                        $usuario_encontrado = true;
                        header("Location: " . $destino);
                        exit; // Termina o script e redireciona

                    } else {
                        // Usuário encontrado, mas senha incorreta. Interrompe a busca nas outras tabelas
                        $mensagem_erro = '<div class="alert alert-danger">E-mail ou senha inválidos.</div>';
                        $usuario_encontrado = true;
                        break; 
                    }
                }
            } // Fim do loop foreach

            // 3. Se o loop terminou e o usuário não foi encontrado em NENHUMA tabela
            if (!$usuario_encontrado) {
                 $mensagem_erro = '<div class="alert alert-danger">E-mail ou senha inválidos.</div>';
            }

        } catch (Exception $e) {
            // AQUI: Registra o erro real, mas exibe o genérico para o usuário
            error_log("ERRO CRÍTICO NO LOGIN (PDO): " . $e->getMessage() . " - SQLState: " . $e->getCode());
            $mensagem_erro = '<div class="alert alert-danger">Erro no sistema. Tente novamente.</div>';
        }
    }
}

// Layout e HTML
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card p-4 shadow-sm" style="width: 100%; max-width: 400px;">
        <h3 class="text-center mb-4">Login</h3>

        <?= $mensagem_erro ?>
        <?= $mensagem_sucesso ?>

        <form action="login.php" method="post">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail:</label>
                <input type="email" class="form-control" name="email" id="email" placeholder="Digite seu e-mail" required>
            </div>

            <div class="mb-3">
                <label for="senha" class="form-label">Senha:</label>
                <input type="password" class="form-control" name="senha" id="senha" placeholder="Digite sua senha" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>

        <div class="text-center mt-3">
            <a href="esqueci-senha.php" class="d-block">Esqueci minha senha</a>
            <span class="text-muted">Ainda não tem conta?</span>
            <a href="cadastro.php">Cadastre-se</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>