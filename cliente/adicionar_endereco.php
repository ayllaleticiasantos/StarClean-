<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas clientes podem acessar esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    header("Location: ../pages/login.php");
    exit();
}

$mensagem = '';
$id_cliente = $_SESSION['usuario_id'];

// Inicializa as variáveis para manter os valores no formulário em caso de erro
$cep = $_POST['cep'] ?? '';
$logradouro = $_POST['logradouro'] ?? '';
$numero = $_POST['numero'] ?? '';
$complemento = $_POST['complemento'] ?? '';
$bairro = $_POST['bairro'] ?? '';
$cidade = $_POST['cidade'] ?? '';
$uf = $_POST['uf'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pega os valores do POST e remove espaços extras
    $cep = trim($_POST['cep']);
    $logradouro = trim($_POST['logradouro']);
    $numero = trim($_POST['numero']);
    $complemento = trim($_POST['complemento']);
    $bairro = trim($_POST['bairro']);
    $cidade = trim($_POST['cidade']);
    $uf = trim($_POST['uf']);

    // --- MELHORIA 1: VALIDAÇÃO NO LADO DO SERVIDOR ---
    $erros = [];
    if (empty($cep)) {
        $erros[] = "O campo CEP é obrigatório.";
    } elseif (!preg_match('/^[0-9]{8}$/', preg_replace('/\D/', '', $cep))) {
        $erros[] = "O formato do CEP é inválido.";
    }
    if (empty($logradouro)) {
        $erros[] = "O campo Logradouro é obrigatório.";
    }
    if (empty($numero)) {
        $erros[] = "O campo Número é obrigatório.";
    }
    if (empty($bairro)) {
        $erros[] = "O campo Bairro é obrigatório.";
    }
    if (empty($cidade)) {
        $erros[] = "O campo Cidade é obrigatório.";
    }
    if (empty($uf)) {
        $erros[] = "O campo UF é obrigatório.";
    } elseif (strlen($uf) !== 2) {
        $erros[] = "O campo UF deve ter exatamente 2 caracteres.";
    }

    if (!empty($erros)) {
        // Se houver erros, monta a mensagem para exibir
        $mensagem = '<div class="alert alert-danger"><strong>Por favor, corrija os seguintes erros:</strong><ul>';
        foreach ($erros as $erro) {
            $mensagem .= "<li>" . htmlspecialchars($erro) . "</li>";
        }
        $mensagem .= '</ul></div>';
    } else {
        // Se não houver erros, prossiga com a inserção no banco
        try {
            $pdo = obterConexaoPDO();
            $stmt = $pdo->prepare(
                "INSERT INTO Endereco (Cliente_id, cep, logradouro, numero, complemento, bairro, cidade, uf)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$id_cliente, $cep, $logradouro, $numero, $complemento, $bairro, $cidade, $uf]);

            $_SESSION['mensagem_sucesso'] = "Endereço adicionado com sucesso!";
            header("Location: gerir_enderecos.php");
            exit();
        } catch (PDOException $e) {
            // echo $stmt->debugDumpParams();
            $mensagem = '<div class="alert alert-danger">Erro ao adicionar o endereço. Tente novamente.</div>';
            // Para depuração (em ambiente de desenvolvimento): error_log($e->getMessage());
        }
    }
}

include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>

<main class="d-flex">
    <?php include '../includes/sidebar.php'; ?>

    <div class="container-fluid p-4">
        <h1 class="mb-4">Adicionar Endereço</h1>
        <hr>

        <?php if (!empty($mensagem)) { echo $mensagem; } ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="adicionar_endereco.php" method="post">
                    <div class="mb-3">
                        <label for="cep" class="form-label">CEP:</label>
                        <input type="text" class="form-control" id="cep" name="cep" value="<?= htmlspecialchars($cep) ?>" required>
                        <div id="cep-status" class="form-text"></div>
                    </div>
                    <div class="mb-3">
                        <label for="logradouro" class="form-label">Logradouro:</label>
                        <input type="text" class="form-control" id="logradouro" name="logradouro" value="<?= htmlspecialchars($logradouro) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="numero" class="form-label">Número:</label>
                        <input type="text" class="form-control" id="numero" name="numero" value="<?= htmlspecialchars($numero) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="complemento" class="form-label">Complemento (opcional):</label>
                        <input type="text" class="form-control" id="complemento" name="complemento" value="<?= htmlspecialchars($complemento) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="bairro" class="form-label">Bairro:</label>
                        <input type="text" class="form-control" id="bairro" name="bairro" value="<?= htmlspecialchars($bairro) ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-9 mb-3">
                            <label for="cidade" class="form-label">Cidade:</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" value="<?= htmlspecialchars($cidade) ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="uf" class="form-label">UF:</label>
                            <input type="text" class="form-control" id="uf" name="uf" value="<?= htmlspecialchars($uf) ?>" required maxlength="2">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar Endereço</button>
                    <a href="gerir_enderecos.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cepInput = document.getElementById('cep');
        const cepStatus = document.getElementById('cep-status');

        cepInput.addEventListener('blur', function() {
            let cep = cepInput.value.replace(/\D/g, ''); // Remove caracteres não-numéricos

            // Reseta o status
            cepStatus.textContent = '';
            cepStatus.className = 'form-text';

            if (cep.length === 8) {
                cepStatus.textContent = 'Buscando CEP...';
                cepStatus.classList.add('text-primary');

                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erro na rede ou na resposta da API.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        cepStatus.textContent = ''; // Limpa a mensagem
                        if (!data.erro) {
                            document.getElementById('logradouro').value = data.logradouro;
                            document.getElementById('bairro').value = data.bairro;
                            document.getElementById('cidade').value = data.localidade;
                            document.getElementById('uf').value = data.uf;
                            // Move o foco para o próximo campo a ser preenchido
                            document.getElementById('numero').focus();
                        } else {
                            cepStatus.textContent = 'CEP não encontrado.';
                            cepStatus.classList.add('text-danger');
                            limparCamposEndereco(false); // Limpa campos sem apagar o CEP
                        }
                    })
                    .catch(() => {
                        cepStatus.textContent = 'Ocorreu um erro ao buscar o CEP. Verifique sua conexão.';
                        cepStatus.classList.add('text-danger');
                        limparCamposEndereco(false);
                    });
            } else if (cep.length > 0) {
                cepStatus.textContent = 'Formato de CEP inválido. Digite 8 números.';
                cepStatus.classList.add('text-danger');
            }
        });

        function limparCamposEndereco(limparCep = true) {
            if (limparCep) {
                document.getElementById('cep').value = '';
            }
            document.getElementById('logradouro').value = '';
            document.getElementById('bairro').value = '';
            document.getElementById('cidade').value = '';
            document.getElementById('uf').value = '';
            document.getElementById('numero').value = '';
            document.getElementById('complemento').value = '';
        }
    });
</script>

<?php include '../includes/footer.php'; ?>