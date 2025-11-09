<?php
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
// --- NOVO: Captura as coordenadas existentes ---
$latitude = $endereco_existente['latitude'];
$longitude = $endereco_existente['longitude'];


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
    // --- NOVO: Captura as coordenadas do formulário ---
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    

    // --- MELHORIA 1: VALIDAÇÃO NO LADO DO SERVIDOR ---
    $erros = [];
    if (empty($cep)) { $erros[] = "O campo CEP é obrigatório.";}
    if (empty($logradouro)) { $erros[] = "O campo Logradouro é obrigatório."; }
    if (empty($numero)) { $erros[] = "O campo Número é obrigatório.";}
    if (empty($bairro)) { $erros[] = "O campo Bairro é obrigatório."; }
    if (empty($cidade)) { $erros[] = "O campo Cidade é obrigatório."; }
    if (empty($uf) || strlen($uf) !== 2) { $erros[] = "O campo UF deve ter 2 caracteres."; }
    // --- NOVO: Validação das coordenadas ---
    if (empty($latitude) || empty($longitude)) {
        $erros[] = "Localização no mapa é obrigatória. Por favor, arraste o pino para seu endereço exato.";
    }

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
            // Prepara o comando UPDATE com os campos de latitude e longitude
            $stmt = $pdo->prepare(
                "UPDATE Endereco 
                 SET cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, uf = ?, latitude = ?, longitude = ?
                 WHERE id = ? AND Cliente_id = ?"
            );
            
            // Executa o comando, passando as novas variáveis $latitude e $longitude
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

<style>
    #mapa_selecao { height: 350px; width: 100%; border-radius: 8px; background-color: #f0f0f0; margin-top: 15px; margin-bottom: 15px; }
</style>

<?php
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

                    <!-- NOVO: Container do Mapa -->
                    <div id="mapa_selecao"></div>

                    <!-- NOVO: Campos ocultos para as coordenadas -->
                    <input type="hidden" id="latitude" name="latitude" value="<?= htmlspecialchars($latitude) ?>">
                    <input type="hidden" id="longitude" name="longitude" value="<?= htmlspecialchars($longitude) ?>">

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


<!-- NOVO: Script completo do mapa, adaptado para a edição -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Variáveis Globais para o Mapa ---
        let map;
        let marker;
        const mapaDiv = document.getElementById('mapa_selecao');
        const inputLat = document.getElementById('latitude');
        const inputLon = document.getElementById('longitude');

        // --- Pega as coordenadas iniciais do PHP ---
        const initialLat = <?= json_encode($latitude) ?>;
        const initialLon = <?= json_encode($longitude) ?>;

        // --- Funções do Mapa (copiadas de adicionar_endereco.php) ---
        async function buscarCoordenadas(endereco) {
            mapaDiv.innerHTML = '<p style="text-align:center; padding: 20px;">Buscando localização...</p>';
            const apiKey = 'c245633945894b24a123507f84263f38'; // Sua chave OpenCage
            const url = `https://api.opencagedata.com/geocode/v1/json?q=${encodeURIComponent(endereco)}&key=${apiKey}&countrycode=br&limit=1`;
            try {
                const response = await fetch(url);
                const data = await response.json();
                if (data && data.results && data.results.length > 0) {
                    const coords = data.results[0].geometry;
                    return { lat: coords.lat, lon: coords.lng };
                }
                return null;
            } catch (error) {
                console.error('Erro na API OpenCage:', error);
                return null;
            }
        }

        function iniciarMapa(lat, lon) {
            mapaDiv.innerHTML = '';
            if (map) {
                map.setView([lat, lon], 17);
            } else {
                map = L.map('mapa_selecao').setView([lat, lon], 17);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);
            }
            if (marker) {
                marker.setLatLng([lat, lon]);
            } else {
                marker = L.marker([lat, lon], { draggable: true }).addTo(map);
                marker.on('dragend', function(event) {
                    const position = marker.getLatLng();
                    inputLat.value = position.lat;
                    inputLon.value = position.lng;
                });
            }
            inputLat.value = lat;
            inputLon.value = lon;
            mapaDiv.insertAdjacentHTML('afterbegin', '<p style="text-align:center; background-color:#fff3cd; padding: 5px; margin-bottom: 5px; border-radius: 5px; z-index: 1000; position: relative;">Arraste o pino para o local exato!</p>');
        }

        function exibirErroMapa() {
            mapaDiv.innerHTML = '<div class="alert alert-danger" role="alert">Não foi possível carregar o mapa. Tente um CEP próximo.</div>';
        }

        // --- Lógica do ViaCEP (copiada e adaptada) ---
        const cepInput = document.getElementById('cep');
        const cepStatus = document.getElementById('cep-status');

        function mascaraCEP(evento) {
            if (evento.key === "Backspace") return;
            let valor = evento.target.value.replace(/\D/g, '');
            valor = valor.replace(/^(\d{5})(\d)/, '$1-$2');
            evento.target.value = valor;
        }
        cepInput.addEventListener('keyup', mascaraCEP);

        async function handleCepBlur() {
            let cep = cepInput.value.replace(/\D/g, '');
            cepStatus.textContent = '';
            cepStatus.className = 'form-text';

            if (cep.length === 8) {
                cepStatus.textContent = 'Buscando CEP...';
                cepStatus.classList.add('text-primary');
                try {
                    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                    const data = await response.json();
                    cepStatus.textContent = '';
                    if (!data.erro) {
                        document.getElementById('logradouro').value = data.logradouro;
                        document.getElementById('bairro').value = data.bairro;
                        document.getElementById('cidade').value = data.localidade;
                        document.getElementById('uf').value = data.uf;
                        document.getElementById('numero').focus();

                        const enderecoParaBusca = `${data.logradouro}, ${data.localidade}, ${data.uf}`;
                        const coords = await buscarCoordenadas(enderecoParaBusca);
                        if (coords) {
                            iniciarMapa(coords.lat, coords.lon);
                        } else {
                            exibirErroMapa();
                        }
                    } else {
                        cepStatus.textContent = 'CEP não encontrado.';
                        cepStatus.classList.add('text-danger');
                        exibirErroMapa();
                    }
                } catch (error) {
                    cepStatus.textContent = 'Ocorreu um erro ao buscar o CEP.';
                    cepStatus.classList.add('text-danger');
                    exibirErroMapa();
                }
            } else if (cep.length > 0) {
                cepStatus.textContent = 'Formato de CEP inválido. Digite 8 números.';
                cepStatus.classList.add('text-danger');
            }
        }
        cepInput.addEventListener('blur', handleCepBlur);

        // --- NOVO: Inicializa o mapa com os dados existentes ao carregar a página ---
        if (initialLat && initialLon) {
            iniciarMapa(initialLat, initialLon);
        } else {
            mapaDiv.innerHTML = '<p style="padding: 20px; text-align: center; color: #666;">Localização não definida. Digite o CEP para carregar o mapa.</p>';
        }
    });
</script>

<?php include '../includes/footer.php'; ?>