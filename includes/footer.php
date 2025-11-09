<footer class="text-center bg-white text-dark py-3 mt-auto bottom">
    <p class="mb-0">&copy; <?= date('Y') ?> StarClean. Todos os direitos reservados.</p>
</footer>

<?php
// --- LÓGICA PARA BUSCAR O CONTEÚDO DOS TERMOS DE USO ---
$conteudo_termos = '<p>O conteúdo dos Termos de Uso não pôde ser carregado.</p>';
try {
    // Garante que a conexão PDO esteja disponível
    if (!function_exists('obterConexaoPDO')) {
        require_once __DIR__ . '/../config/db.php';
    }
    $stmt = obterConexaoPDO()->query("SELECT conteudo FROM conteudo_geral WHERE chave = 'termos_de_uso_conteudo' LIMIT 1");
    $resultado = $stmt->fetchColumn();
    if ($resultado) {
        $conteudo_termos = $resultado;
    }
} catch (Exception $e) {
    error_log("Erro ao buscar Termos de Uso para o modal: " . $e->getMessage());
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<div class="modal fade" id="modalTermosDeUso" tabindex="-1" aria-labelledby="modalTermosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTermosLabel">Termos e Condições de Uso - StarClean</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- O conteúdo dos termos é inserido aqui pelo código PHP -->
                <?= $conteudo_termos ?>
            </div>
        </div>
    </div>
    </body>

    </html>