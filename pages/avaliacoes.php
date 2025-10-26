<?php
session_start();
require_once '../config/db.php';

// Lógica para buscar avaliações
$avaliacoes = [];
$erro_banco = '';
try {
    $pdo = obterConexaoPDO();

    // Consulta SQL para buscar as avaliações mais recentes e o nome do cliente
    $stmt = $pdo->prepare(
        "SELECT ap.comentario, ap.nota, c.nome AS nome_cliente 
         FROM avaliacao_prestador ap
         JOIN cliente c ON ap.Cliente_id = c.id
         WHERE ap.comentario IS NOT NULL AND ap.nota IS NOT NULL
         ORDER BY ap.id DESC"
    );
    $stmt->execute();
    $avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro ao buscar avaliações: " . $e->getMessage());
    $erro_banco = '<div class="alert alert-danger text-center">Não foi possível carregar as avaliações no momento.</div>';
}

include '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';

?>

<header class="hero-section"
    style="background: url(<?= BASE_URL ?>/img/produtoslimp.png) center/cover no-repeat; height: 400px; display: flex; align-items: center; color: black; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">
    <div class="container">
        <h1 class="display-3 fw-bold text-center color-white">Avaliações</h1>
        <p>
        <h4 class="text-center color-white">Veja o que nossos clientes estão dizendo sobre nós.</h4>
        </p>
    </div> 
</header>

<main class="container my-5">
    
    <?= $erro_banco ?>

    <section class="mb-5">
        <div class="row text-center justify-content-center">
            
            <?php if (!empty($avaliacoes)): ?>
                <?php foreach ($avaliacoes as $avaliacao): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body d-flex flex-column">
                                
                                <div class="text-center mb-3">
                                    <?php 
                                        // Geração dinâmica das estrelas
                                        $nota = (int)$avaliacao['nota'];
                                        for ($i = 1; $i <= 5; $i++): 
                                    ?>
                                        <i class="fas fa-star fs-4 <?= ($i <= $nota) ? 'text-warning' : 'text-muted' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                
                                <p class="card-text flex-grow-1">"<?= nl2br(htmlspecialchars($avaliacao['comentario'])) ?>"</p>
                                
                                <h5 class="card-title mt-3 mb-0 text-primary">
                                    <?= htmlspecialchars($avaliacao['nome_cliente']) ?>
                                </h5>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                 <div class="col-12 text-center">
                    <div class="alert alert-info">Ainda não há avaliações para exibir.</div>
                </div>
            <?php endif; ?>

        </div>
    </section>

</main>

<?php
include '../includes/footer.php';
?>