<?php
/**
 * Busca coordenadas (latitude e longitude) para um ENDEREÇO COMPLETO.
 * É muito mais preciso do que buscar apenas pelo CEP.
 *
 * @param string $enderecoCompleto Ex: "Quadra QNQ 5, 17, Ceilândia, DF"
 * @return array|null Retorna um array com ['latitude', 'longitude'] ou null se não encontrar.
 */
function obterCoordenadasPorEndereco(string $enderecoCompleto): ?array
{
    // 1. Insira sua chave de API gratuita da OpenCage aqui
    // IMPORTANTE: Esta chave 'c245...' é do seu exemplo. Se ela for sua,
    // considere criar uma nova e guardá-la em segurança.
    $apiKey = 'c245633945894b24a123507f84263f38';

    // 2. Prepara a consulta para a API
    $query = urlencode($enderecoCompleto);
    // Adicionamos 'countrycode=br' para limitar a busca ao Brasil
    $url_api = "https://api.opencagedata.com/geocode/v1/json?q={$query}&key={$apiKey}&countrycode=br&limit=1";

    // 3. Faz a requisição cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Timeout de 5 segundos
    $resposta_json = curl_exec($ch);

    // Trata erros de cURL
    if (curl_errno($ch)) {
        error_log("Erro cURL na OpenCage API: " . curl_error($ch));
        return null;
    }
    curl_close($ch);

    // 4. Decodifica a resposta e extrai as coordenadas
    if ($resposta_json && ($dados = json_decode($resposta_json, true)) && !empty($dados['results'])) {
        $geometria = $dados['results'][0]['geometry'];
        
        // Verifica se a API encontrou com uma confiança razoável (ex: > 7)
        // Isso evita salvar coordenadas genéricas (do centro da cidade ou do CEP)
        if ($dados['results'][0]['confidence'] > 7) {
            return ['latitude' => $geometria['lat'], 'longitude' => $geometria['lng']];
        }
        
        error_log("OpenCage API: Baixa confiança para o endereço: " . $enderecoCompleto);
        return null;
    }
    
    error_log("OpenCage API: Endereço não encontrado: " . $enderecoCompleto);
    return null;
}

session_start();
require_once '../config/db.php'; // Garanta que este caminho está correto

// Segurança: Apenas clientes podem acessar
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Pega os valores do POST e limpa
    $cep = trim($_POST['cep']);
    $logradouro = trim($_POST['logradouro']);
    $numero = trim($_POST['numero']);
    $complemento = trim($_POST['complemento']);
    $bairro = trim($_POST['bairro']);
    $cidade = trim($_POST['cidade']);
    $uf = trim($_POST['uf']);

    // --- VALIDAÇÃO NO LADO DO SERVIDOR ---
    $erros = [];
    if (empty($cep)) { $erros[] = "O campo CEP é obrigatório."; }
    if (empty($logradouro)) { $erros[] = "O campo Logradouro é obrigatório."; }
    if (empty($numero)) { $erros[] = "O campo Número é obrigatório.";}
    if (empty($bairro)) { $erros[] = "O campo Bairro é obrigatório."; }
    if (empty($cidade)) { $erros[] = "O campo Cidade é obrigatório."; }
    if (empty($uf)) { $erros[] = "O campo UF é obrigatório."; }

    // *** CORREÇÃO DE SINTAXE (CHAVE FALTANDO) APLICADA AQUI ***
    if (!empty($erros)) {
        $mensagem = '<div class="alert alert-danger"><strong>Por favor, corrija os seguintes erros:</strong><ul>';
        foreach ($erros as $erro) {
            $mensagem .= "<li>" . htmlspecialchars($erro) . "</li>";
        } // <-- Esta chave estava faltando
        $mensagem .= '</ul></div>';
    } else {
        // Se não houver erros, prossiga com a inserção no banco
        try {
            
            // ==================================================================
            // !! ATUALIZAÇÃO APLICADA AQUI !! (Linha 118)
            // ==================================================================
            // Monta o endereço para busca precisa (Logradouro, Número, Cidade, UF)
            // Removemos bairro e cep para uma busca mais limpa e precisa.
            $endereco_para_api = "{$logradouro}, {$numero}, {$cidade}, {$uf}";
            // ==================================================================

            $coordenadas = obterCoordenadasPorEndereco($endereco_para_api);
            
            $latitude = $coordenadas['latitude'] ?? null;
            $longitude = $coordenadas['longitude'] ?? null;

            // --- CORREÇÃO LÓGICA (AVISO) APLICADA ---
            // Se não achou coordenadas, avisa o usuário
            if (!$latitude || !$longitude) {
                $mensagem = '<div class="alert alert-warning"><strong>Aviso:</strong> O endereço foi salvo, mas não foi possível encontrar a localização exata no mapa (latitude/longitude). Verifique se o número e o logradouro estão corretos.</div>';
                // Mesmo sem coordenadas, continuamos para salvar o endereço
            }

            $pdo = obterConexaoPDO();
            $stmt = $pdo->prepare(
                "INSERT INTO Endereco (Cliente_id, cep, logradouro, numero, complemento, bairro, cidade, uf, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([$id_cliente, $cep, $logradouro, $numero, $complemento, $bairro, $cidade, $uf, $latitude, $longitude]);

            // Se definimos uma mensagem de aviso, não redirecionamos
            if (empty($mensagem)) {
                $_SESSION['mensagem_sucesso'] = "Endereço adicionado com sucesso!";
                header("Location: gerir_enderecos.php");
                exit();
            } else {
                // Se houve aviso, adicionamos uma mensagem de sucesso parcial
                $mensagem = '<div class="alert alert-success">Endereço adicionado com sucesso!</div>' . $mensagem;
            }

        } catch (PDOException $e) {
            $mensagem = '<div class="alert alert-danger">Erro ao adicionar o endereço. Tente novamente. Se o erro persistir, contacte o suporte.</div>';
            error_log("Erro PDO em adicionar_endereco.php: " . $e->getMessage());
        }
    }
}

// Inclui o cabeçalho HTML
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
        <h1 class="mb-4">Adicionar Endereço</h1>
        <hr>

        <?php if (!empty($mensagem)) { echo $mensagem; } ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="adicionar_endereco.php" method="post">

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

        // --- FUNÇÃO DE BUSCA VIASEP ---
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