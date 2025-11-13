<?php
require_once '../config/config.php';
require_once '../config/db.php';

$conteudo_pagina = [];
try {
    $pdo = obterConexaoPDO();
    $stmt_geral = $pdo->query("SELECT chave, conteudo FROM conteudo_geral WHERE pagina = 'sobre' AND oculto = 0");
    foreach ($stmt_geral->fetchAll(PDO::FETCH_ASSOC) as $item) {
        $conteudo_pagina[$item['chave']] = $item['conteudo'];
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar conteúdo da página Sobre: " . $e->getMessage());
    $conteudo_pagina = [
        'sobre_hero_titulo' => 'Sobre a StarClean',
        'sobre_hero_subtitulo' => 'Conheça nossa jornada e nossos valores.',
        'sobre_historia_titulo' => 'Nossa História',
        'sobre_historia_texto' => 'Texto sobre a história da empresa.',
        'sobre_missao_titulo' => 'Nossa Missão',
        'sobre_missao_texto' => 'Texto sobre a missão da empresa.',
        'sobre_visao_titulo' => 'Nossa Visão',
        'sobre_visao_texto' => 'Texto sobre a visão da empresa.'
    ];
}

include '../includes/header.php';

include '../includes/navbar.php';
?>

<header class="hero-section" style="background: url('<?= BASE_URL ?>/img/cleaning.jpeg') center/cover no-repeat; height: 400px; display: flex; align-items: center; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">
    <div class="container">
        <h1 class="display-3 fw-bold text-center"><?= htmlspecialchars($conteudo_pagina['sobre_hero_titulo'] ?? '') ?></h1>
        <p class="lead col-lg-8 mx-auto text-center"><?= htmlspecialchars($conteudo_pagina['sobre_hero_subtitulo'] ?? '') ?></p>
    </div>
</header>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="mb-5">
                <h2 class="mb-3"><?= htmlspecialchars($conteudo_pagina['sobre_historia_titulo'] ?? '') ?></h2>
                <hr class="my-3">
                <p class="text-muted"><?= nl2br(htmlspecialchars($conteudo_pagina['sobre_historia_texto'] ?? '')) ?></p>
            </div>

            <div class="row mb-5">
                <div class="col-md-6">
                    <h3 class="mb-3"><?= htmlspecialchars($conteudo_pagina['sobre_missao_titulo'] ?? '') ?></h3>
                    <hr class="my-3">
                    <p class="text-muted"><?= nl2br(htmlspecialchars($conteudo_pagina['sobre_missao_texto'] ?? '')) ?></p>
                </div>
                <div class="col-md-6">
                    <h3 class="mb-3"><?= htmlspecialchars($conteudo_pagina['sobre_visao_titulo'] ?? '') ?></h3>
                    <hr class="my-3">
                    <p class="text-muted"><?= nl2br(htmlspecialchars($conteudo_pagina['sobre_visao_texto'] ?? '')) ?></p>
                </div>
            </div>

            <hr class="my-5">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4"><?= htmlspecialchars($conteudo_pagina['sobre_dados_titulo'] ?? '') ?></h2>
                    <ul class="list-unstyled text-center text-muted">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-primary"></i><strong>Endereço:</strong> <?= htmlspecialchars($conteudo_pagina['sobre_dados_endereco'] ?? '') ?></li>
                        <li class="mb-2"><i class="fas fa-phone me-2 text-primary"></i><strong>Telefones:</strong> <?= htmlspecialchars($conteudo_pagina['sobre_dados_telefones'] ?? '') ?></li>
                        <li class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i><strong>Email:</strong> <?= htmlspecialchars($conteudo_pagina['sobre_dados_email'] ?? '') ?></li>
                        <li class="mb-2"><i class="fas fa-clock me-2 text-primary"></i><strong>Horário:</strong> <?= htmlspecialchars($conteudo_pagina['sobre_dados_horario'] ?? '') ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>