<?php
include 'includes/header.php';
include 'includes/navbar.php';
require_once 'config/db.php';

// Buscar conteúdo dinâmico do banco de dados
$carousel_slides = [];
$cards_destaque = [];
try {
    $pdo = obterConexaoPDO();
    
    // Busca slides do carrossel ativos
    $stmt_carousel = $pdo->prepare("SELECT * FROM conteudo_pagina_inicial WHERE tipo_conteudo = 'carousel' AND ativo = 1 ORDER BY ordem ASC");
    $stmt_carousel->execute();
    $carousel_slides = $stmt_carousel->fetchAll(PDO::FETCH_ASSOC);

    // Busca cards de destaque ativos
    $stmt_cards = $pdo->prepare("SELECT * FROM conteudo_pagina_inicial WHERE tipo_conteudo = 'card' AND ativo = 1 ORDER BY ordem ASC");
    $stmt_cards->execute();
    $cards_destaque = $stmt_cards->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Em caso de erro, a página pode ficar em branco, mas não quebrará. O erro será logado.
    error_log("Erro ao buscar conteúdo da página inicial: " . $e->getMessage());
}
?>
<div>
    <div id="carouselInicialSC" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach ($carousel_slides as $index => $slide): ?>
                <button type="button" data-bs-target="#carouselInicialSC" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($carousel_slides as $index => $slide): ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <img src="<?= htmlspecialchars($slide['imagem_url']) ?>" class="d-block w-100" style="max-height: 500px; object-fit: cover;" alt="<?= htmlspecialchars($slide['titulo']) ?>">
                    <div class="carousel-caption d-none d-md-block">
                        <h2 style="color: white; text-shadow: 1px 1px 2px black;"><?= htmlspecialchars($slide['titulo']) ?></h2>
                        <p style="color: white; text-shadow: 1px 1px 2px black;"><?= htmlspecialchars($slide['texto']) ?></p>
                        <?php if (!empty($slide['link_url']) && !empty($slide['texto_botao'])): ?>
                            <a href="<?= htmlspecialchars($slide['link_url']) ?>" class="btn btn-primary"><?= htmlspecialchars($slide['texto_botao']) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($carousel_slides) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselInicialSC" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselInicialSC" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Próximo</span>
            </button>
        <?php endif; ?>
    </div>
</div>


<div class="container my-5">
    <div class="row">
        <?php if (empty($cards_destaque)): ?>
            <div class="col-12">
                <p class="text-center">Nenhum serviço em destaque no momento.</p>
            </div>
        <?php else: ?>
            <?php foreach ($cards_destaque as $card): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <img src="<?= htmlspecialchars($card['imagem_url']) ?>" class="card-img-top" style="object-fit: cover; height: 200px;" alt="<?= htmlspecialchars($card['titulo']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($card['titulo']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($card['texto']) ?></p>
                            <?php if (!empty($card['link_url']) && !empty($card['texto_botao'])): ?>
                                <a href="<?= BASE_URL . '/' . htmlspecialchars($card['link_url']) ?>"><?= htmlspecialchars($card['texto_botao']) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>