<?php
/**
 * Registra uma ação de um administrador no log de atividades.
 *
 * @param int $admin_id O ID do administrador que realizou a ação.
 * @param string $acao A descrição da ação (ex: "Editou o conteúdo da página inicial").
 * @param array|null $detalhes Um array associativo com detalhes extras para armazenar como JSON.
 */
function registrar_log_admin(int $admin_id, string $acao, ?array $detalhes = null): void
{
    // Garante que a conexão PDO esteja disponível
    require_once __DIR__ . '/../config/db.php';

    try {
        $pdo = obterConexaoPDO();
        $stmt = $pdo->prepare(
            "INSERT INTO log_atividades (admin_id, acao, detalhes) VALUES (?, ?, ?)"
        );
        
        // Converte o array de detalhes para JSON, se houver
        $detalhes_json = $detalhes ? json_encode($detalhes, JSON_UNESCAPED_UNICODE) : null;
        
        $stmt->execute([$admin_id, $acao, $detalhes_json]);
    } catch (PDOException $e) {
        // Em caso de falha no log, apenas registra no log de erros do servidor para não quebrar a aplicação.
        error_log("Falha ao registrar log de admin: " . $e->getMessage());
    }
}