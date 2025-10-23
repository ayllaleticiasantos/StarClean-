<?php
session_start();
require_once '../config/db.php';
require_once '../config/config.php'; // Garante que BASE_URL está definida

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $mensagem = '<div class="alert alert-danger">Por favor, insira o seu e-mail.</div>';
    } else {
        try {
            $pdo = obterConexaoPDO();
            $usuario_encontrado = false;
            
            // CORREÇÃO: Usando os nomes corretos das tabelas (singular)
            foreach (['Cliente', 'Prestador', 'Administrador'] as $tabela) {
                $stmt = $pdo->prepare("SELECT id FROM `$tabela` WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $usuario_encontrado = true;
                    break;
                }
            }

            if ($usuario_encontrado) {
                // Antes de inserir um novo token, remove os antigos para o mesmo email
                $stmt = $pdo->prepare("DELETE FROM redefinicao_senha WHERE email = ?");
                $stmt->execute([$email]);

                // Gera um token seguro
                $token = bin2hex(random_bytes(50));
                
                // Define o tempo de expiração (1 hora a partir de agora)
                $data_expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Salva o novo token na base de dados
                $stmt = $pdo->prepare("INSERT INTO redefinicao_senha (email, token, data_expiracao) VALUES (?, ?, ?)");
                $stmt->execute([$email, $token, $data_expiracao]);

                // --- SIMULAÇÃO DO ENVIO DE E-MAIL ---
                $link_redefinicao = BASE_URL . "/pages/redefinir-senha.php?token=" . $token;
                
                $mensagem = '<div class="alert alert-success">Se o e-mail estiver registado, um link para redefinir a senha foi gerado.</div>';
                // Apenas para fins de teste, mostra o link no ecrã
                $mensagem .= '<div class="alert alert-info mt-3"><strong>Link para Teste:</strong><br><a href="' . $link_redefinicao . '" class="text-break">' . $link_redefinicao . '</a></div>';
                
            } else {
                // Mensagem genérica para segurança (não revela se um e-mail existe no sistema)
                $mensagem = '<div class="alert alert-success">Se o e-mail estiver registado, receberá as instruções para redefinir a sua senha.</div>';
            }

        } catch (PDOException $e) {
            // Verifica se o erro é "tabela não encontrada" e dá uma instrução clara
            if ($e->getCode() == '42S02') {
                 $mensagem = '<div class="alert alert-danger">Erro: A tabela `redefinicao_senha` não foi encontrada. Por favor, execute o script SQL para criar a tabela.</div>';
            } else {
                $mensagem = '<div class="alert alert-danger">Ocorreu um erro no sistema. Tente novamente.</div>';
            }
            error_log("Erro em esqueci-senha.php: " . $e->getMessage());
        }
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card p-4 shadow-sm" style="width: 100%; max-width: 450px;">
        <h3 class="text-center mb-4">Recuperar Senha</h3>
        <p class="text-center text-muted mb-4">Insira seu e-mail e nós enviaremos um link para você voltar a aceder à sua conta.</p>

        <?php if($mensagem) { echo $mensagem; } ?>

        <form action="esqueci-senha.php" method="post">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail:</label>
                <input type="email" class="form-control" name="email" placeholder="Digite seu e-mail" id="email" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Enviar Link de Recuperação</button>
        </form>

        <div class="text-center mt-3">
            <a href="login.php">Voltar para o Login</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>