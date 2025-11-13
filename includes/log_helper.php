<?php
function registrar_log_usuario(string $tipo_usuario, int $usuario_id, string $acao, ?array $detalhes = null): void
{
    require_once __DIR__ . '/../config/db.php';

    $tabela_log = "log_{$tipo_usuario}_atividades";
    $coluna_id = "{$tipo_usuario}_id";

    try {
        $pdo = obterConexaoPDO();
        $stmt = $pdo->prepare(
            "INSERT INTO `$tabela_log` ($coluna_id, acao, detalhes) VALUES (?, ?, ?)"
        );
        
        $detalhes_json = $detalhes ? json_encode($detalhes, JSON_UNESCAPED_UNICODE) : null;
        $stmt->execute([$usuario_id, $acao, $detalhes_json]);
    } catch (PDOException $e) {
        error_log("Falha ao registrar log para o tipo '$tipo_usuario': " . $e->getMessage());
    }
}

function registrar_log_admin(int $admin_id, string $acao, ?array $detalhes = null): void
{
    require_once __DIR__ . '/../config/db.php';

    try {
        $pdo = obterConexaoPDO();
        $stmt = $pdo->prepare(
            "INSERT INTO log_atividades (admin_id, acao, detalhes) VALUES (?, ?, ?)"
        );
        
        $detalhes_json = $detalhes ? json_encode($detalhes, JSON_UNESCAPED_UNICODE) : null;
        
        $stmt->execute([$admin_id, $acao, $detalhes_json]);
    } catch (PDOException $e) {
        error_log("Falha ao registrar log de admin: " . $e->getMessage());
    }
}