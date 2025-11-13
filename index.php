<?php
include 'includes/header.php';
include 'includes/navbar.php';
require_once 'config/config.php';
require_once 'config/db.php';

$carousel_slides = [];
$cards_destaque = [];
$blocos_conteudo = [];
$avisos_ativos = [];
$conteudo_pagina = [];

try {
    $pdo = obterConexaoPDO();

    $stmt_carousel = $pdo->query("SELECT * FROM conteudo_pagina_inicial WHERE tipo_conteudo = 'carousel' AND ativo = 1 AND oculto = 0 ORDER BY ordem ASC");
    $carousel_slides = $stmt_carousel->fetchAll(PDO::FETCH_ASSOC);

    $stmt_cards = $pdo->query("SELECT * FROM conteudo_pagina_inicial WHERE tipo_conteudo = 'card' AND ativo = 1 AND oculto = 0 ORDER BY ordem ASC");
    $cards_destaque = $stmt_cards->fetchAll(PDO::FETCH_ASSOC);

    $stmt_geral = $pdo->query("SELECT chave, conteudo FROM conteudo_geral WHERE pagina = 'index' AND oculto = 0");
    foreach ($stmt_geral->fetchAll(PDO::FETCH_ASSOC) as $item) {
        $conteudo_pagina[$item['chave']] = $item['conteudo'];
    }

    $stmt_blocos = $pdo->query("SELECT * FROM blocos_conteudo WHERE pagina = 'index' AND ativo = 1 ORDER BY ordem ASC");
    $blocos_conteudo = $stmt_blocos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro ao buscar conteúdo da página inicial: " . $e->getMessage());
}
?>
<main>
    <?php // include __DIR__ . '/includes/avisos_section.php'; ?>

    <div>
        <div id="carouselInicialSC" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php foreach ($carousel_slides as $index => $slide): ?>
                    <button type="button" data-bs-target="#carouselInicialSC" data-bs-slide-to="<?= $index ?>"
                        class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>"
                        aria-label="Slide <?= $index + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach ($carousel_slides as $index => $slide): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="<?= htmlspecialchars($slide['imagem_url']) ?>" class="d-block w-100"
                            style="max-height: 500px; object-fit: cover;" alt="<?= htmlspecialchars($slide['titulo']) ?>">
                        <div class="carousel-caption d-none d-md-block">
                            <h2 style="color: white; text-shadow: 1px 1px 2px black;">
                                <?= htmlspecialchars($slide['titulo']) ?>
                            </h2>
                            <p style="color: white; text-shadow: 1px 1px 2px black;">
                                <?= htmlspecialchars($slide['texto']) ?>
                            </p>
                            <?php if (!empty($slide['link_url']) && !empty($slide['texto_botao'])): ?>
                                <a href="<?= htmlspecialchars($slide['link_url']) ?>"
                                    class="btn btn-primary"><?= htmlspecialchars($slide['texto_botao']) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($carousel_slides) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselInicialSC"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselInicialSC"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Próximo</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="container my-5">
        <h1 class="text-center mb-4">
            <?= htmlspecialchars($conteudo_pagina['index_hero_titulo'] ?? 'Limpeza Completa e Personalizada') ?>
        </h1>
        <hr class="my-3">
        <h4 class="text-center mb-4">
            <?= nl2br(htmlspecialchars($conteudo_pagina['index_hero_subtitulo'] ?? 'Oferecemos serviços abrangentes...')) ?>
        </h4>
    </div>

    <div class="container-fluid bg-light py-5" id="diferenciais">
        <div class="container">
            <h2 class="text-center mb-4">
                <?= htmlspecialchars($conteudo_pagina['index_diferenciais_titulo'] ?? 'Por que escolher a Star Clean?') ?>
            </h2>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 text-center shadow-sm card2">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= htmlspecialchars($conteudo_pagina['index_diferenciais_card1_titulo'] ?? 'Equipe Qualificada') ?>
                            </h5>
                            <p class="card-text">
                                <?= $conteudo_pagina['index_diferenciais_card1_texto'] ?? 'Texto do diferencial 1.' ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 text-center shadow-sm card2">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= htmlspecialchars($conteudo_pagina['index_diferenciais_card2_titulo'] ?? 'Garantia de Qualidade') ?>
                            </h5>
                            <p class="card-text">
                                <?= $conteudo_pagina['index_diferenciais_card2_texto'] ?? 'Texto do diferencial 2.' ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 text-center shadow-sm card2">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= htmlspecialchars($conteudo_pagina['index_diferenciais_card3_titulo'] ?? 'Preços e Mimos') ?>
                            </h5>
                            <p class="card-text">
                                <?= $conteudo_pagina['index_diferenciais_card3_texto'] ?? 'Texto do diferencial 3.' ?>
                            </p>
                            <p class="card-text"><a href="<?= BASE_URL ?>/pages/login.php">Faça seu login!</a> <br><a
                                    href="<?= BASE_URL ?>/pages/cadastro.php">Ainda não tem conta? Cadastre-se.</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <h3 class="text-center ">Como cuidamos dos seus ambientes!</h3>
        <hr class="my-3">
        <div class="row">
            <?php if (empty($cards_destaque)): ?>
                <div class="col-12">
                    <p class="text-center">Nenhum serviço em destaque no momento.</p>
                </div>
            <?php else: ?>
                <?php foreach ($cards_destaque as $card): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100">
                            <img src="<?= htmlspecialchars($card['imagem_url']) ?>" class="card-img-top"
                                style="object-fit: cover; height: 200px;" alt="<?= htmlspecialchars($card['titulo']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($card['titulo']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($card['texto']) ?></p>
                                <?php if (!empty($card['link_url']) && !empty($card['texto_botao'])): ?>
                                    <a
                                        href="<?= BASE_URL . '/' . htmlspecialchars($card['link_url']) ?>"><?= htmlspecialchars($card['texto_botao']) ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="container my-5">
        <?php foreach ($blocos_conteudo as $bloco): ?>
            <?php 
                $dados = json_decode($bloco['conteudo_json'], true);
            ?>

            <?php if ($bloco['tipo_bloco'] === 'texto_simples'): ?>
                <div class="my-5">
                    <?= $dados['texto'] ?>
                </div>

            <?php elseif ($bloco['tipo_bloco'] === 'card_imagem_texto' && $dados): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($dados['imagem_url']) ?>" class="img-fluid rounded-start" alt="<?= htmlspecialchars($dados['titulo']) ?>" style="object-fit: cover; height: 100%;">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($dados['titulo']) ?></h5>
                                <p class="card-text"><?= nl2br(htmlspecialchars($dados['texto'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="container my-5" id="processo">
        <h2 class="text-center mb-4">Nosso Processo Simplificado</h2>
        <hr class="my-3">
        <div class="row">
            <div class="col-m-3 text-center m-3 bg-dark text-white">
                <div class="p-3 border rounded shadow-sm">
                    <strong>1. Contato</strong>
                    <p class="small mb-0">Via site da StarClean</p>
                </div>
            </div>
            <div class="col-m-3 text-center m-3 bg-dark text-white">
                <div class="p-3 border rounded shadow-sm">
                    <strong>2. Se cadastre</strong>
                    <p class="small mb-0">Crie uma conta para acessar nossos serviços.</p>
                </div>
            </div>
            <div class="col-m-3 text-center m-3 bg-dark text-white">
                <div class="p-3 border rounded shadow-sm">
                    <strong>3. Agendamento</strong>
                    <p class="small mb-0">Você escolhe o dia, o serviço e o horário.</p>
                </div>
            </div>
            <div class="col-m-3 text-center m-3 bg-dark text-white">
                <div class="p-3 border rounded shadow-sm">
                    <strong>4. Execução e Feedback</strong>
                    <p class="small mb-0">Realizamos o serviço e após você realiza a avaliação no sistema com feedback.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-3" id="planos">
        <h2 class="text-center mb-4">O que cada combo oferece</h2>
        <hr class="my-2">
        <p class="text-center lead mb-4">Trabalhamos com combos diários e mensais. Veja alguns dos
            nossos níveis de serviço:</p>
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-3 col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">Combo Básico</h5>
                        <p class="card-text small">Inclui: Limpar/lavar o chão, lavar banheiro, lavar louça/pia,
                            limpar
                            móveis, limpar espelhos, arrumar cama, limpar fogão, separar o lixo e organizar itens.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">Combo Intermediário</h5>
                        <p class="card-text small">Tudo do Básico e mais: encerar piso, tirar teia dos móveis/teto,
                            limpar micro-ondas, limpeza de fornos.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">Combo Brilhante</h5>
                        <p class="card-text small">Tudo do Intermediário e mais: Lavar portas/janelas, limpar
                            geladeira,
                            limpar parede de gordura, limpar lustres e aspirar tapetes e sofás.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

    <?php include 'includes/footer.php'; ?>