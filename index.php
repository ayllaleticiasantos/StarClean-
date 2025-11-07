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
    <h2 class="text-center mb-4">Limpeza Completa e Personalizada</h2>
    <p class="text-center lead mb-4">
        Oferecemos serviços abrangentes de limpeza para empresas, condomínios e residências. 
        Trabalhamos com combos ou planos personalizados para atender sua necessidade.
    </p>
    
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Nossos Serviços Incluem:</h5>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Limpeza de chão e remoção de pó dos móveis </li>
                    <li class="list-group-item">Aspiração de tapetes e sofás </li>
                    <li class="list-group-item">Limpeza de banheiros </li>
                    <li class="list-group-item">Lavagem de portas e janelas </li>
                    <li class="list-group-item">Serviço de passadeira </li>
                    <li class="list-group-item">Encerar chão </li>
                    <li class="list-group-item">Lavar geladeira e micro-ondas </li>
                    <li class="list-group-item">Limpar parede de gordura </li>
                    <li class="list-group-item">Limpar lustres </li>
                    <li class="list-group-item">Separar lixo de reciclagem </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid bg-light py-5" id="diferenciais">
    <div class="container">
        <h2 class="text-center mb-4">Por que escolher a Star Clean?</h2>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 text-center shadow-sm card2">
                    <div class="card-body">
                        <h5 class="card-title">Equipe Qualificada</h5>
                        <p class="card-text">Nossos Prestadores de Serviços são capacitados e recebem cursos semestrais para garantir um serviço de excelência.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 text-center shadow-sm card2">
                    <div class="card-body">
                        <h5 class="card-title">Garantia de Qualidade</h5>
                        <p class="card-text">Nosso diferencial! Após o serviço, <strong>é feita uma vistoria do trabalho prestado</strong> para garantir sua total satisfação.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 text-center shadow-sm card2">
                    <div class="card-body">
                        <h5 class="card-title">Preços e Mimos</h5>
                        <p class="card-text">Oferecemos preços justos, <strong>5% de desconto na primeira compra</strong> e fragrâncias exclusivas para clientes fiéis.<br></p>
                        <p class="card-text"><a href="login.php">Faça seu login!</a> <br><a href="cadastro.php">Ainda não tem conta? Cadastre-se.</a></p>
                    </div>
                </div>
            </div>
        </div><
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

<div class="container my-5" id="processo">
    <h2 class="text-center mb-4">Nosso Processo Simplificado</h2>
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
                <p class="small mb-0">Realizamos o serviço e após você realiza a avaliação no sistema com feedback.</p>
            </div>
        </div>
    </div>
</div>

<div class="container my-5" id="planos">
    <h2 class="text-center mb-4">Planos que se adaptam à sua rotina</h2>
    <p class="text-center lead mb-4">Trabalhamos com combos diários, mensais e personalizados. Veja alguns dos nossos níveis de serviço:</p>
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Combo Básico</h5>
                    <p class="card-text small">Inclui: Limpar/lavar o chão, lavar banheiro, lavar louça/pia, limpar móveis, limpar espelhos, arrumar cama, limpar fogão, separar o lixo e organizar itens.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Combo Intermediário</h5>
                    <p class="card-text small">Tudo do Básico e mais: encerar piso, tirar teia dos móveis/teto, limpar micro-ondas, entre outros.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Combo Brilhante</h5>
                    <p class="card-text small">Tudo do Intermediário e mais: Lavar portas/janelas, limpar geladeira, limpar parede de gordura, limpar lustres e aspirar tapetes e sofás.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Combo Passadeira</h5>
                    <p class="card-text small">Serviço avulso ou combinado, focado em passar suas roupas.</p>
                </div>
            </div>
        </div>
    </div>
</div>    

<?php include 'includes/footer.php'; ?>