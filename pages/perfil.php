<?php
session_start();
require_once '../config/db.php';
require_once '../config/config.php';
require_once '../includes/log_helper.php';
require_once '../includes/validation_helper.php';

// 1. SEGURANÇA: VERIFICA SE O USUÁRIO ESTÁ LOGADO
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$tipo_usuario = $_SESSION['usuario_tipo'];
$tabela = '';
$id_coluna = 'id';

// Determina a tabela correta com base no tipo de usuário
switch ($tipo_usuario) {
    case 'cliente':
        $tabela = 'Cliente';
        break;
    case 'prestador':
        $tabela = 'Prestador';
        break;
    case 'admin':
        $tabela = 'Administrador';
        break;
    default:
        // Se o tipo de usuário for inválido, redireciona
        header("Location: login.php");
        exit();
}

$mensagem_sucesso = '';
$mensagem_erro = '';

// 2. PROCESSAMENTO DO FORMULÁRIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = obterConexaoPDO();
    
    // --- AÇÃO: ATUALIZAR DADOS PESSOAIS ---
    if (isset($_POST['atualizar_dados'])) {
        try {
            if ($tipo_usuario === 'cliente') {
                $nome = $_POST['nome'];
                $sobrenome = $_POST['sobrenome'];
                $telefone = $_POST['telefone'];
                $data_nascimento = $_POST['data_nascimento'];

                $stmt = $pdo->prepare("UPDATE Cliente SET nome = ?, sobrenome = ?, telefone = ?, data_nascimento = ? WHERE id = ?");
                $stmt->execute([$nome, $sobrenome, $telefone, $data_nascimento, $id_usuario]);

            } elseif ($tipo_usuario === 'prestador') {
                $nome = $_POST['nome'];
                $sobrenome = $_POST['sobrenome'];
                $telefone = $_POST['telefone'];
                $especialidade = $_POST['especialidade'];
                $descricao = $_POST['descricao'];

                $stmt = $pdo->prepare("UPDATE Prestador SET nome = ?, sobrenome = ?, telefone = ?, especialidade = ?, descricao = ? WHERE id = ?");
                $stmt->execute([$nome, $sobrenome, $telefone, $especialidade, $descricao, $id_usuario]);

            } elseif ($tipo_usuario === 'admin') {
                $nome = $_POST['nome'];
                $sobrenome = $_POST['sobrenome'];

                $stmt = $pdo->prepare("UPDATE Administrador SET nome = ?, sobrenome = ? WHERE id = ?");
                $stmt->execute([$nome, $sobrenome, $id_usuario]);
            }

            $_SESSION['usuario_nome'] = $nome; // Atualiza o nome na sessão
            $mensagem_sucesso = "Seus dados foram atualizados com sucesso!";

        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao atualizar os dados. Tente novamente.";
            error_log("Erro ao atualizar perfil ($tipo_usuario): " . $e->getMessage());
        }
    }

    // --- AÇÃO: ATUALIZAR SENHA ---
    if (isset($_POST['atualizar_senha'])) {
        $senha_atual = $_POST['senha_atual'];
        $nova_senha = $_POST['nova_senha'];
        $confirmar_senha = $_POST['confirmar_senha'];

        // Busca a senha atual no banco
        $stmt_pass = $pdo->prepare("SELECT password FROM $tabela WHERE id = ?");
        $stmt_pass->execute([$id_usuario]);
        $usuario_db = $stmt_pass->fetch();

        if (!$usuario_db || !password_verify($senha_atual, $usuario_db['password'])) {
            $mensagem_erro = "A senha atual está incorreta.";
        } elseif ($nova_senha !== $confirmar_senha) {
            $mensagem_erro = "A nova senha e a confirmação não correspondem.";
        } else {
            $erros_senha = validarSenhaForte($nova_senha);
            if (!empty($erros_senha)) {
                $mensagem_erro = "A nova senha não é forte o suficiente: <ul><li>" . implode("</li><li>", $erros_senha) . "</li></ul>";
            } else {
                // Se tudo estiver OK, atualiza a senha
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt_update = $pdo->prepare("UPDATE $tabela SET password = ? WHERE id = ?");
                $stmt_update->execute([$nova_senha_hash, $id_usuario]);
                $mensagem_sucesso = "Senha alterada com sucesso!";
            }
        }
    }
}

// 3. BUSCAR DADOS ATUAIS DO USUÁRIO PARA EXIBIR NO FORMULÁRIO
$usuario = null;
try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->prepare("SELECT * FROM $tabela WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        // Força o logout se o usuário não for encontrado no banco
        session_destroy();
        header("Location: login.php?mensagem=Sessão inválida.");
        exit();
    }
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao carregar os dados do perfil.";
    error_log("Erro ao buscar dados do perfil ($tipo_usuario): " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>

<button class="btn btn-primary d-md-none m-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
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
        <h1 class="mb-4">Meu Perfil</h1>

        <?php if ($mensagem_sucesso): ?><div class="alert alert-success"><?= $mensagem_sucesso ?></div><?php endif; ?>
        <?php if ($mensagem_erro): ?><div class="alert alert-danger"><?= $mensagem_erro ?></div><?php endif; ?>

        <?php if ($usuario): ?>
            <ul class="nav nav-tabs" id="perfilTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="dados-pessoais-tab" data-bs-toggle="tab" data-bs-target="#dados-pessoais" type="button" role="tab">Dados Pessoais</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="seguranca-tab" data-bs-toggle="tab" data-bs-target="#seguranca" type="button" role="tab">Segurança</button>
                </li>
            </ul>

            <div class="tab-content card shadow-sm" id="perfilTabContent">
                <!-- ABA DE DADOS PESSOAIS -->
                <div class="tab-pane fade show active" id="dados-pessoais" role="tabpanel">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Editar Informações Pessoais</h5>
                        <form action="perfil.php" method="post">
                            <input type="hidden" name="atualizar_dados" value="1">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nome" class="form-label">Nome:</label>
                                    <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required placeholder="Seu primeiro nome">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sobrenome" class="form-label">Sobrenome:</label>
                                    <input type="text" class="form-control" id="sobrenome" name="sobrenome" value="<?= htmlspecialchars($usuario['sobrenome']) ?>" required placeholder="Seu sobrenome">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" disabled readonly>
                                <small class="form-text text-muted">O e-mail não pode ser alterado.</small>
                            </div>

                            <?php if ($tipo_usuario === 'cliente'): ?>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="telefone" class="form-label">Telefone:</label>
                                        <input type="text" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($usuario['telefone']) ?>" required placeholder="(XX) XXXXX-XXXX">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="data_nascimento" class="form-label">Data de Nascimento:</label>
                                        <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($usuario['data_nascimento']) ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="cpf" class="form-label">CPF:</label>
                                    <input type="text" class="form-control" id="cpf" name="cpf" value="<?= htmlspecialchars($usuario['cpf']) ?>" disabled readonly>
                                    <small class="form-text text-muted">O CPF não pode ser alterado.</small>
                                </div>
                            <?php endif; ?>

                            <?php if ($tipo_usuario === 'prestador'): ?>
                                <div class="mb-3">
                                    <label for="telefone" class="form-label">Telefone:</label>
                                    <input type="text" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($usuario['telefone']) ?>" required placeholder="(XX) XXXXX-XXXX">
                                </div>
                                <div class="mb-3">
                                    <label for="especialidade" class="form-label">Especialidade:</label>
                                    <input type="text" class="form-control" id="especialidade" name="especialidade" value="<?= htmlspecialchars($usuario['especialidade']) ?>" required placeholder="Ex: Limpeza residencial, pós-obra...">
                                </div>
                                <div class="mb-3">
                                    <label for="descricao" class="form-label">Descrição:</label>
                                    <textarea class="form-control" id="descricao" name="descricao" rows="4" placeholder="Fale um pouco sobre você e seus serviços..."><?= htmlspecialchars($usuario['descricao']) ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="cpf" class="form-label">CPF:</label>
                                    <input type="text" class="form-control" id="cpf" name="cpf" value="<?= htmlspecialchars($usuario['cpf']) ?>" disabled readonly>
                                    <small class="form-text text-muted">O CPF não pode ser alterado.</small>
                                </div>
                            <?php endif; ?>

                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        </form>
                    </div>
                </div>

                <!-- ABA DE SEGURANÇA -->
                <div class="tab-pane fade" id="seguranca" role="tabpanel">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Alterar Senha</h5>
                        <form action="perfil.php" method="post">
                            <input type="hidden" name="atualizar_senha" value="1">
                            <div class="mb-3">
                                <label for="senha_atual" class="form-label">Senha Atual:</label>
                                <input type="password" class="form-control" id="senha_atual" name="senha_atual" required placeholder="Digite sua senha atual">
                            </div>
                            <div class="mb-3">
                                <label for="nova_senha" class="form-label">Nova Senha:</label>
                                <input type="password" class="form-control" id="nova_senha" name="nova_senha" required placeholder="Crie uma nova senha forte" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}" title="A senha deve atender a todos os requisitos de segurança.">
                            </div>
                            <!-- Requisitos da Senha (Feedback Visual) -->
                            <ul id="password-requirements" class="list-unstyled mt-2 text-muted small">
                                <li id="length" class="text-danger"><i class="fas fa-times-circle me-1"></i> Mínimo de 8 caracteres</li>
                                <li id="lowercase" class="text-danger"><i class="fas fa-times-circle me-1"></i> Uma letra minúscula</li>
                                <li id="uppercase" class="text-danger"><i class="fas fa-times-circle me-1"></i> Uma letra maiúscula</li>
                                <li id="number" class="text-danger"><i class="fas fa-times-circle me-1"></i> Um número</li>
                                <li id="special" class="text-danger"><i class="fas fa-times-circle me-1"></i> Um caractere especial (!@#$%)</li>
                            </ul>
                            <div class="mb-3">
                            </div>
                            <div class="mb-3">
                                <label for="confirmar_senha" class="form-label">Confirmar Nova Senha:</label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required placeholder="Confirme a nova senha">
                            </div>
                            <button type="submit" class="btn btn-primary">Alterar Senha</button>
                        </form>
                    </div>
                </div>

            </div>

        <?php else: ?>
            <div class="alert alert-danger">Não foi possível carregar os dados do seu perfil. Por favor, tente fazer login novamente.</div>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Lógica para manter a aba ativa após o post (recarregamento da página)
    const activeTab = localStorage.getItem('activePerfilTab');
    if (activeTab) {
        const tabElement = document.querySelector('#perfilTab button[data-bs-target="' + activeTab + '"]');
        if (tabElement) {
            new bootstrap.Tab(tabElement).show();
        }
    }

    // Salva a aba clicada no localStorage
    const tabButtons = document.querySelectorAll('#perfilTab button[data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function (event) {
            localStorage.setItem('activePerfilTab', event.target.getAttribute('data-bs-target'));
        });
    });

    // Limpa o localStorage ao sair da página para não interferir em outras sessões
    window.addEventListener('beforeunload', function() {
        // Se o formulário foi submetido, não limpa para poder mostrar a msg de sucesso na aba correta
        if (!document.querySelector('form').classList.contains('submitting')) {
             localStorage.removeItem('activePerfilTab');
        }
    });
    document.querySelector('form').addEventListener('submit', function() {
        this.classList.add('submitting');
    });

    // Máscara de telefone (exemplo simples)
    const phoneInput = document.getElementById('telefone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            e.target.value = value.slice(0, 15);
        });
    }

    // --- LÓGICA PARA VALIDAÇÃO DE SENHA FORTE ---
    const senhaInput = document.getElementById('nova_senha');
    const requirementsList = document.getElementById('password-requirements');
    const requirements = {
        length: document.getElementById('length'),
        lowercase: document.getElementById('lowercase'),
        uppercase: document.getElementById('uppercase'),
        number: document.getElementById('number'),
        special: document.getElementById('special')
    };

    if (senhaInput && requirementsList) {
        function validatePassword() {
            const value = senhaInput.value;

            // Esconde a lista se o campo estiver vazio
            requirementsList.style.display = value.length > 0 ? 'block' : 'none';

            const updateRequirement = (req, isValid) => {
                if (req) {
                    req.className = isValid ? 'text-success' : 'text-danger';
                    req.querySelector('i').className = isValid ? 'fas fa-check-circle me-1' : 'fas fa-times-circle me-1';
                }
            };

            updateRequirement(requirements.length, value.length >= 8);
            updateRequirement(requirements.lowercase, /[a-z]/.test(value));
            updateRequirement(requirements.uppercase, /[A-Z]/.test(value));
            updateRequirement(requirements.number, /\d/.test(value));
            updateRequirement(requirements.special, /[\W_]/.test(value));
        }

        senhaInput.addEventListener('input', validatePassword);
        validatePassword(); // Executa uma vez para definir o estado inicial (escondido)
    }
});
</script>

<?php include '../includes/footer.php'; ?>


<style>
/* Adiciona um espaçamento interno consistente para todas as abas */
.tab-content > .tab-pane {
    border: 1px solid #dee2e6;
    border-top: 0;
    border-radius: 0 0 0.375rem 0.375rem;
}

/* Remove a borda do card para que ele se integre com as abas */
.tab-content.card {
    border: none;
}
</style>