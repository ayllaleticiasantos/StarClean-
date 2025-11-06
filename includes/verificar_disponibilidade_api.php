<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$prestador_id = $_GET['prestador_id'] ?? null;
$data = $_GET['data'] ?? null;

$response = ['disponivel' => false];

if (!$prestador_id || !$data) {
    echo json_encode($response);
    exit();
}

try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM indisponibilidade_prestador WHERE prestador_id = ? AND data_indisponivel = ?"
    );
    $stmt->execute([$prestador_id, $data]);
    $count = $stmt->fetchColumn();

    // Se a contagem for 0, significa que não há registro de indisponibilidade,
    // então o prestador ESTÁ disponível.
    if ($count == 0) {
        $response['disponivel'] = true;
    }

} catch (PDOException $e) {
    // Em caso de erro, assume-se que não está disponível para segurança
    error_log("API Error: " . $e->getMessage());
}

echo json_encode($response);
?>