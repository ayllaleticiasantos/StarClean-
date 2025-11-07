<?php
/*
 * ==================================================================
 * PHP (Servidor)
 * Nenhuma alteração aqui. A lógica continua a mesma:
 * receber a latitude e longitude que o JavaScript enviar.
 * ==================================================================
 */

session_start();
require_once '../config/db.php'; // Garanta que este caminho está correto

// Segurança: Apenas clientes podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    header("Location: ../pages/login.php");
    exit();
}

$mensagem = '';
$id_cliente = $_SESSION['usuario_id'];

// Inicializa as variáveis para manter os valores no formulário
$cep = $_POST['cep'] ?? '';
$logradouro = $_POST['logradouro'] ?? '';
$numero = $_POST['numero'] ?? '';
$complemento = $_POST['complemento'] ?? '';
$bairro = $_POST['bairro'] ?? '';
$cidade = $_POST['cidade'] ?? '';
$uf = $_POST['uf'] ?? '';

// --- Campos ocultos que virão do mapa ---
$latitude = $_POST['latitude'] ?? '';
$longitude = $_POST['longitude'] ?? '';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // --- VALIDAÇÃO NO LADO DO SERVIDOR ---
    $erros = [];
    if (empty($cep)) { $erros[] = "O campo CEP é obrigatório."; }
    if (empty($logradouro)) { $erros[] = "O campo Logradouro é obrigatório."; }
    if (empty($numero)) { $erros[] = "O campo Número é obrigatório.";}
    if (empty($bairro)) { $erros[] = "O campo Bairro é obrigatório."; }
    if (empty($cidade)) { $erros[] = "O campo Cidade é obrigatório."; }
    if (empty($uf)) { $erros[] = "O campo UF é obrigatório."; }
    
    // --- NOVA VALIDAÇÃO ---
    // Verifica se o usuário marcou o pino no mapa
    if (empty($latitude) || empty($longitude)) {
        $erros[] = "Localização no mapa é obrigatória. Por favor, arraste o pino para seu endereço exato.";
    }

    
    if (!empty($erros)) {
        $mensagem = '<div class="alert alert-danger"><strong>Por favor, corrija os seguintes erros:</strong><ul>';
        foreach ($erros as $erro) {
            $mensagem .= "<li>" . htmlspecialchars($erro) . "</li>";
        }
        $mensagem .= '</ul></div>';
    } else {
        // Se não houver erros, prossiga com a inserção no banco
        try {
            // API de geocodificação não é mais necessária aqui no PHP
            
            $pdo = obterConexaoPDO();
            $stmt = $pdo->prepare(
                "INSERT INTO Endereco (Cliente_id, cep, logradouro, numero, complemento, bairro, cidade, uf, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            // Salva os dados, incluindo a latitude e longitude vindas do mapa
            $stmt->execute([
                $id_cliente, 
                $cep, 
                $logradouro, 
                $numero, 
                $complemento, 
                $bairro, 
                $cidade, 
                $uf, 
                $latitude, // Salva o dado do pino
                $longitude // Salva o dado do pino
            ]);

            $_SESSION['mensagem_sucesso'] = "Endereço adicionado com sucesso!";
            header("Location: gerir_enderecos.php");
            exit();

        } catch (PDOException $e) {
            // Verifica erro de chave duplicada (ex: cliente já tem endereço)
            if ($e->getCode() == 23000) { 
                 $mensagem = '<div class="alert alert-danger">Erro: Você já possui um endereço cadastrado. Para adicionar um novo, primeiro remova o antigo.</div>';
            } else {
                 $mensagem = '<div class="alert alert-danger">Erro ao adicionar o endereço. Tente novamente.</div>';
            }
            error_log("Erro PDO em adicionar_endereco.php: " . $e->getMessage());
        }
    }
}

// Inclui o cabeçalho HTML
include '../includes/header.php';
include '../includes/navbar_logged_in.php';

?>

<link rel="stylesheet" href="http://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
<style>
    /* Define a altura do mapa no formulário */
    #mapa_selecao {
        height: 350px; 
        width: 100%; 
        border-radius: 8px; 
        background-color: #f0f0f0;
        margin-top: 15px;
        margin-bottom: 15px;
    }
</style>


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

                    <div id="mapa_selecao">
                        <p style="padding: 20px; text-align: center; color: #666;">
                            Digite o CEP para carregar o mapa.
                        </p>
                    </div>

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
                    <button type="submit" class="btn btn-primary">Salvar Endereço</button>
                    <a href="gerir_enderecos.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="http://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>

<script>
    // --- Variáveis Globais para o Mapa ---
    let map;
    let marker;
    const mapaDiv = document.getElementById('mapa_selecao');
    const inputLat = document.getElementById('latitude');
    const inputLon = document.getElementById('longitude');

    // ==================================================================
    // !! ATUALIZAÇÃO APLICADA AQUI !!
    // Substituída a função 'buscarCoordenadas' pela API OPENCAGE
    // ==================================================================
    async function buscarCoordenadas(endereco) {
        mapaDiv.innerHTML = '<p style="text-align:center; padding: 20px;">Buscando localização...</p>';
        
        // Use a sua chave da API OpenCage
        const apiKey = 'c245633945894b24a123507f84263f38'; // A chave que você usou
        const url = `https://api.opencagedata.com/geocode/v1/json?q=${encodeURIComponent(endereco)}&key=${apiKey}&countrycode=br&limit=1`;

        try {
            const response = await fetch(url);
            const data = await response.json();
            
            // Verifica se a API OpenCage encontrou resultados
            if (data && data.results && data.results.length > 0) {
                const coords = data.results[0].geometry;
                return { lat: coords.lat, lon: coords.lng }; // ATENÇÃO: é .lng
            }
            return null; // Não encontrou
        } catch (error) {
            console.error('Erro na API OpenCage:', error);
            return null;
        }
    }

    // --- Função para iniciar o mapa (Sem alterações) ---
    function iniciarMapa(lat, lon) {
        mapaDiv.innerHTML = ''; // Limpa o "Carregando..."
        
        if (map) {
            map.setView([lat, lon], 17);
        } else {
            map = L.map('mapa_selecao').setView([lat, lon], 17);
            L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);
        }

        if (marker) {
            marker.setLatLng([lat, lon]);
        } else {
            marker = L.marker([lat, lon], {
                draggable: true 
            }).addTo(map);

            marker.on('dragend', function(event) {
                const position = marker.getLatLng();
                console.log(`Pino movido para: ${position.lat}, ${position.lng}`);
                inputLat.value = position.lat;
                inputLon.value = position.lng;
            });
        }

        inputLat.value = lat;
        inputLon.value = lon;
        
        // Adiciona um aviso amigável
        mapaDiv.insertAdjacentHTML('afterbegin', '<p style="text-align:center; background-color:#fff3cd; padding: 5px; margin-bottom: 5px; border-radius: 5px; z-index: 1000; position: relative;">Arraste o pino para o local exato!</p>');
    }

    // --- Função para exibir erro no mapa (Sem alterações) ---
    function exibirErroMapa() {
        mapaDiv.innerHTML = 
            '<div class="alert alert-danger" role="alert">Não foi possível carregar o mapa. Tente um CEP próximo.</div>';
    }


    // --- SCRIPT EXISTENTE DO ViaCEP (Sem alterações) ---
    document.addEventListener('DOMContentLoaded', function() {
        const cepInput = document.getElementById('cep');
        const cepStatus = document.getElementById('cep-status');

        function mascaraCEP(evento) {
            if (evento.key === "Backspace") return;
            let valor = evento.target.value.replace(/\D/g, '');
            valor = valor.replace(/^(\d{5})(\d)/, '$1-$2');
            evento.target.value = valor;
        }
        cepInput.addEventListener('keyup', mascaraCEP);

        // --- FUNÇÃO DE BUSCA ViaCEP (MODIFICADA) ---
        cepInput.addEventListener('blur', function() {
            let cep = cepInput.value.replace(/\D/g, '');
            cepStatus.textContent = '';
            cepStatus.className = 'form-text';

            if (cep.length === 8) {
                cepStatus.textContent = 'Buscando CEP...';
                cepStatus.classList.add('text-primary');

                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(async data => { // <-- 'async'
                        cepStatus.textContent = '';
                        if (!data.erro) {
                            document.getElementById('logradouro').value = data.logradouro;
                            document.getElementById('bairro').value = data.bairro;
                            document.getElementById('cidade').value = data.localidade;
                            document.getElementById('uf').value = data.uf;
                            document.getElementById('numero').focus();

                            // --- NOVO: INICIA O MAPA ---
                            // Cria uma string de endereço para a OpenCage buscar
                            // (Logradouro, Cidade, UF é mais preciso que só o CEP)
                            const enderecoParaBusca = `${data.logradouro}, ${data.localidade}, ${data.uf}`;
                            const coords = await buscarCoordenadas(enderecoParaBusca); // AGORA CHAMA A OPENCAGE

                            if (coords) {
                                iniciarMapa(coords.lat, coords.lon);
                            } else {
                                // Se falhar (raro), centraliza no Brasil
                                iniciarMapa(-15.793889, -47.882778); 
                            }
                            
                        } else {
                            cepStatus.textContent = 'CEP não encontrado.';
                            cepStatus.classList.add('text-danger');
                            limparCamposEndereco(false);
                            exibirErroMapa();
                        }
                    })
                    .catch(() => {
                        cepStatus.textContent = 'Ocorreu um erro ao buscar o CEP.';
                        cepStatus.classList.add('text-danger');
                        limparCamposEndereco(false);
                        exibirErroMapa();
                    });
            } else if (cep.length > 0) {
                cepStatus.textContent = 'Formato de CEP inválido.';
                cepStatus.classList.add('text-danger');
            }
        });

        function limparCamposEndereco(limparCep = true) {
            if (limparCep) { document.getElementById('cep').value = ''; }
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