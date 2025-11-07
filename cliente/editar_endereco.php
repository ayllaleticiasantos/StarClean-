<?php
/**
 * Busca coordenadas (latitude e longitude) para um CEP usando a API Nominatim.
 *
 * @param string $cep O CEP a ser buscado.
 * @return array|null Retorna um array com ['latitude', 'longitude'] ou null se não encontrar.
 */

function obterCoordenadasPorCEP(string $cep): ?array
{
    // Limpa o CEP para enviar apenas números e adiciona ", Brasil" para precisão
    $cep_limpo = preg_replace('/[^0-9]/', '', $cep);
    $endereco_completo = urlencode($cep_limpo . ", Brasil");
    $url_api = "https://nominatim.openstreetmap.org/search?q={$endereco_completo}&format=json&limit=1";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // User-Agent com e-mail válido (IMPORTANTE)
    curl_setopt($ch, CURLOPT_USERAGENT, 'StarCleanApp/1.0 (starclean.prest.servicos@gmail.com)');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $resposta_json = curl_exec($ch);

    // Trata erros de cURL
    if (curl_errno($ch)) {

        error_log("Erro cURL: " . curl_error($ch));
        return null;
    }

    curl_close($ch);

    if ($resposta_json && ($dados = json_decode($resposta_json, true)) && !empty($dados)) {
        return ['latitude' => $dados['lat'], 'longitude' => $dados['lon'],];
    }

    return null;
}


session_start();
require_once '../config/db.php';

// Segurança: Apenas clientes podem acessar esta página

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    header("Location: ../pages/login.php");
    exit();
}

$mensagem = "";
$id_cliente = $_SESSION['usuario_id'];
$id_endereco = $_GET['id'] ?? null;

$endereco_existente = null;

// Redireciona se o ID do endereço não foi fornecido
if (empty($id_endereco) || !is_numeric($id_endereco)) {
    $_SESSION['mensagem_erro'] = "ID do endereço inválido ou não fornecido.";
    header("Location: gerir_enderecos.php");
    exit();
}

try {

    $pdo = obterConexaoPDO();
    // 1. Lógica para buscar os dados do endereço existente
    $stmt = $pdo->prepare("SELECT * FROM Endereco WHERE id = ? AND Cliente_id = ?");
    $stmt->execute([$id_endereco, $id_cliente]);
    $endereco_existente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se o endereço existe e pertence ao cliente logado
    if (!$endereco_existente) {
        $_SESSION['mensagem_erro'] = "Endereço não encontrado ou você não tem permissão para editá-lo.";
        header("Location: gerir_enderecos.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar endereço para edição: " . $e->getMessage());
    $_SESSION['mensagem_erro'] = "Erro ao carregar dados do endereço.";
    header("Location: gerir_enderecos.php");

    exit();
}


// Inicializa as variáveis do formulário com os dados existentes
$cep = $endereco_existente['cep'];
$logradouro = $endereco_existente['logradouro'];
$numero = $endereco_existente['numero'];
$complemento = $endereco_existente['complemento'];
$bairro = $endereco_existente['bairro'];
$cidade = $endereco_existente['cidade'];
$uf = $endereco_existente['uf'];


// --- 2. LÓGICA DE ATUALIZAÇÃO (QUANDO O FORMULÁRIO É ENVIADO VIA POST) ---
if ($_SERVER["REQUEST_METHOD"]==="POST") {
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
    if (empty($cep)) { $erros[] = "O campo CEP é obrigatório.";}
    if (empty($logradouro)) { $erros[] = "O campo Logradouro é obrigatório."; }
    if (empty($numero)) { $erros[] = "O campo Número é obrigatório.";}
    if (empty($bairro)) { $erros[] = "O campo Bairro é obrigatório."; }
    if (empty($cidade)) { $erros[] = "O campo Cidade é obrigatório."; }
    if (empty($uf) || strlen($uf) !== 2) { $erros[] = "O campo UF deve ter 2 caracteres."; }

    if (!empty($erros)) {
        // Se houver erros, monta a mensagem para exibir
        $mensagem = '<div class="alert alert-danger"><strong>Por favor, corrija os seguintes erros:</strong><ul>';

        foreach ($erros as $erro) {
            $mensagem .= "<li>" . htmlspecialchars($erro) . "</li>";
        }
        $mensagem .= '</ul></div>';
        
    }else{
        // Se não houver erros, prossiga com a ATUALIZAÇÃO no banco
        try {
            
            // --- MODIFICAÇÃO APLICADA AQUI ---
            // 1. Busca as coordenadas novamente ao editar, usando APENAS o CEP e previne SQL Injection
            $coordenadas = obterCoordenadasPorCEP($cep);
            $latitude = $coordenadas['latitude'] ?? null;
            $longitude = $coordenadas['longitude'] ?? null;
            // ************************************

            // 2. Prepara o comando UPDATE com os campos de latitude e longitude
            $stmt = $pdo->prepare(
                "UPDATE Endereco 
                 SET cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, uf = ?, latitude = ?, longitude = ?

                 WHERE id = ? AND Cliente_id = ?"
            );
            
            // 3. Executa o comando, passando as novas variáveis $latitude e $longitude
            $stmt->execute([
                $cep, 
                $logradouro, 
                $numero, 
                $complemento, 
                $bairro, 
                $cidade, 
                $uf, 
                $latitude,  // Nova variável
                $longitude, // Nova variável
                $id_endereco, 
                $id_cliente
            ]);

            $_SESSION['mensagem_sucesso'] = "Endereço atualizado com sucesso!";
            header("Location: gerir_enderecos.php");
            exit();
            
        } catch (PDOException $e) {
            $mensagem = '<div class="alert alert-danger">Erro ao atualizar o endereço. Tente novamente. Se o erro persistir, contacte o suporte.</div>';
            error_log("Erro no UPDATE de Endereco: " . $e->getMessage());
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
        <h1 class="mb-4">Editar Endereço</h1>
        <hr>

        <?php if (!empty($mensagem)) { echo $mensagem; } ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="editar_endereco.php?id=<?= htmlspecialchars($id_endereco) ?>" method="post">

                    <div class="mb-3">
                        <label for="cep" class="form-label">CEP:</label>
                        <input type="text" class="form-control" id="cep" name="cep" value="<?= htmlspecialchars($cep) ?>" placeholder="00000-000" required>
                        <div id="cep-status" class="form-text"></div>
                    </div>
                    <div class="mb-3">
                        <label for="logradouro" class="form-label">Logradouro:</label>
                        <input type="text" class="form-control" id="logradouro" name="logradouro" value="<?= htmlspecialchars($logradouro) ?>" placeholder="Ex: Rua das Flores" required>
                    </div>
                    <div class="mb-3">
                        <label for="numero" class="form-label">Número:</label>
                        <input type="text" class="form-control" id="numero" name="numero" value="<?= htmlspecialchars($numero) ?>" placeholder="Ex: 123" required>
                    </div>
                    <div class="mb-3">
                        <label for="complemento" class="form-label">Complemento (opcional):</label>
                        <input type="text" class="form-control" id="complemento" name="complemento" value="<?= htmlspecialchars($complemento) ?>" placeholder="Ex: Apto 101, Bloco B">
                    </div>
                    <div class="mb-3">
                        <label for="bairro" class="form-label">Bairro:</label>
                        <input type="text" class="form-control" id="bairro" name="bairro" value="<?= htmlspecialchars($bairro) ?>" placeholder="Ex: Centro" required>
                    </div>
                    <div class="row">
                        <div class="col-md-9 mb-3">
                            <label for="cidade" class="form-label">Cidade:</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" value="<?= htmlspecialchars($cidade) ?>" placeholder="Ex: São Paulo" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="uf" class="form-label">UF:</label>
                            <input type="text" class="form-control" id="uf" name="uf" value="<?= htmlspecialchars($uf) ?>" placeholder="Ex: SP" required maxlength="2">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    <a href="gerir_enderecos.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</main>


<script>
    // Script do ViaCEP (para preenchimento automático)
    document.addEventListener('DOMContentLoaded', function() {
        const cepInput = document.getElementById('cep');
        const cepStatus = document.getElementById('cep-status');

        // --- FUNÇÃO DE MÁSCARA PARA O CEP ---
        function mascaraCEP(evento) {
            // Impede a máscara de ser aplicada ao apagar
            if (evento.key === "Backspace") return;
            // Pega o valor atual e remove tudo que não for número
            let valor = evento.target.value.replace(/\D/g, '');
            // Aplica a máscara XXXXX-XXX
            valor = valor.replace(/^(\d{5})(\d)/, '$1-$2');
            evento.target.value = valor;
        }
        cepInput.addEventListener('keyup', mascaraCEP);

        cepInput.addEventListener('blur', function() {
            let cep = cepInput.value.replace(/\D/g, ''); // Remove caracteres não-numéricos

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
                        cepStatus.textContent = ''; 
                        if (!data.erro) {
                            document.getElementById('logradouro').value = data.logradouro;
                            document.getElementById('bairro').value = data.bairro;
                            document.getElementById('cidade').value = data.localidade;
                            document.getElementById('uf').value = data.uf;
                            document.getElementById('numero').focus();
                        } else {
                            cepStatus.textContent = 'CEP não encontrado.';
                            cepStatus.classList.add('text-danger');
                            limparCamposEndereco(false); 
                        }
                    })
                    .catch(() => {
                        cepStatus.textContent = 'Ocorreu um erro ao buscar o CEP.';
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