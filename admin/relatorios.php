<?php
// Inclui o arquivo de conexão PDO
require_once '../config/db.php'; 

// Variáveis para armazenar os resultados
$counts = [
    'limpezas_realizadas' => 0,
    'limpezas_pendentes' => 0,
    'clientes_cadastrados' => 0,
    'prestadores_cadastrados' => 0,
    'prestadores_disponiveis' => 0,
];
$mensagem_erro = '';

try {
    $pdo = obterConexaoPDO();
    
    // --- 1. CONTAGEM DE LIMPEZAS REALIZADAS ---
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Agendamento WHERE status = 'realizado'");
    $counts['limpezas_realizadas'] = $stmt->fetchColumn();

    // --- 2. CONTAGEM DE LIMPEZAS PENDENTES ---
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Agendamento WHERE status = 'pendente'");
    $counts['limpezas_pendentes'] = $stmt->fetchColumn();

    // --- 3. CONTAGEM DE CLIENTES CADASTRADOS ---
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Cliente");
    $counts['clientes_cadastrados'] = $stmt->fetchColumn();

    // --- 4. CONTAGEM DE PRESTADORES CADASTRADOS ---
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Prestador");
    $counts['prestadores_cadastrados'] = $stmt->fetchColumn();
    
    // --- 5. CONTAGEM DE PRESTADORES DISPONÍVEIS ---
    // Conta quantos IDs de prestador têm o status 'livre' na tabela Disponibilidade
    $stmt = $pdo->query("SELECT COUNT(DISTINCT prestador_id) AS total FROM Disponibilidade WHERE status = 'livre'");
    $counts['prestadores_disponiveis'] = $stmt->fetchColumn();

} catch (PDOException $e) {
    // Em caso de erro, captura a mensagem e registra no log
    $mensagem_erro = "Erro ao buscar dados: Não foi possível conectar ao banco de dados ou a consulta falhou. Detalhes: " . htmlspecialchars($e->getMessage());
    error_log("Erro na consulta de estatísticas: " . $e->getMessage());
}
?>

<?php
include '../includes/header.php';
include '../includes/navbar_logged_in.php';
include '../includes/sidebar.php';
?>

    <div class="container">
        <h1 class="mb-4">Relatório de Estatísticas do StarClean</h1>
        <hr>

        <?php if ($mensagem_erro): ?>
            <div class="alert alert-danger"><?= $mensagem_erro ?></div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Limpezas Realizadas</h5>
                            <p class="card-text display-4"><?= $counts['limpezas_realizadas'] ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h5 class="card-title">Limpezas Pendentes</h5>
                            <p class="card-text display-4"><?= $counts['limpezas_pendentes'] ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Clientes Cadastrados</h5>
                            <p class="card-text display-4"><?= $counts['clientes_cadastrados'] ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card bg-info text-dark">
                        <div class="card-body">
                            <h5 class="card-title">Prestadores Cadastrados</h5>
                            <p class="card-text display-4"><?= $counts['prestadores_cadastrados'] ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Prestadores Disponíveis Hoje</h5>
                            <p class="card-text display-4"><?= $counts['prestadores_disponiveis'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php
include "../includes/footer.php";
?>