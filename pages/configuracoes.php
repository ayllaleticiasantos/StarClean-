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
switch ($tipo_usuario) {
    case 'cliente': $tabela = 'cliente'; break;
    case 'prestador': $tabela = 'prestador'; break;
    case 'admin': $tabela = 'administrador'; break;
}

// Lógica para processar o formulário quando for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tabela) {
    $pdo = obterConexaoPDO();

    // Se a checkbox estiver marcada, o valor é 1 (true), senão é 0 (false)
    $valor_notificacao = isset($_POST['notificacaoEmail']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE `$tabela` SET receber_notificacoes_email = ? WHERE id = ?");
    if ($stmt->execute([$valor_notificacao, $id_usuario])) {
        $_SESSION['mensagem_sucesso'] = "Preferências salvas com sucesso!";
    } else {
        $_SESSION['mensagem_erro'] = "Erro ao salvar as preferências.";
    }
    
    // Redireciona para a mesma página para evitar reenvio
    header("Location: configuracoes.php");
    exit();
}

// Busca a preferência atual do usuário no banco de dados para exibir na página
$prefere_email = 1; // Valor padrão
if ($tabela) {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->prepare("SELECT receber_notificacoes_email FROM `$tabela` WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $config_usuario = $stmt->fetch();
    if ($config_usuario) {
        $prefere_email = $config_usuario['receber_notificacoes_email'];
    }
}

// --- Inclusão dos ficheiros de layout ---
include '../includes/header.php';
include '../includes/navbar_logged_in.php';
include '../includes/sidebar.php'; // O sidebar já abre a coluna de conteúdo principal
?>

<!-- O conteúdo da página começa diretamente aqui -->
<h1>Configurações</h1>
<hr>

<?php 
if (isset($_SESSION['mensagem_sucesso'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['mensagem_sucesso'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensagem_sucesso']);
}
if (isset($_SESSION['mensagem_erro'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['mensagem_erro'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensagem_erro']);
}
?>
    
<div class="alert alert-info">
    <p class="mb-0">Aqui você pode configurar suas preferências de notificação, privacidade e outras opções da conta.</p>
</div>

<form action="configuracoes.php" method="POST">
    <div class="card mt-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Preferências de Notificação</h5>
            
            <div class="form-check form-switch">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    role="switch"
                    id="notificacaoEmail" 
                    name="notificacaoEmail"
                    <?php if ($prefere_email == 1) echo 'checked'; ?>
                >
                <label class="form-check-label" for="notificacaoEmail">Receber notificações por e-mail sobre agendamentos</label>
            </div>
            
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" role="switch" id="notificacaoPush" disabled>
                <label class="form-check-label text-muted" for="notificacaoPush">Receber notificações no app (em breve)</label>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Salvar Preferências</button>
        </div>
    </div>
</form>

<?php 
// O footer fechará a estrutura principal que foi aberta no header e sidebar
include '../includes/footer.php'; 
?>
