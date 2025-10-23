<?php
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div>
    <div id="carouselInicialSC" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselInicialSC" data-bs-slide-to="0" class="active"
                aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselInicialSC" data-bs-slide-to="1"
                aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselInicialSC" data-bs-slide-to="2"
                aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="img/sliderbar_1.png" class="d-block w-100" style="max-height: 500px; object-fit: cover;"
                    alt="Seja bem-vindo à StarClean">
                <div class="carousel-caption d-none d-md-block">
                    <h2 class="display-3 fw-bold text-white align-self-center">Bem-vindos à StarClean</h2>
                    <p class="lead col-lg-8 mx-auto text-white">A sua plataforma para agendar serviços de limpeza com qualidade e
                        confiança.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="img/sliderbar_1.png" class="d-block w-100" style="max-height: 500px; object-fit: cover;"
                    alt="Seja um Cliente">
                <div class="carousel-caption d-none d-md-block">
                    <h2 style="color: white; text-shadow: 1px 1px 2px black;">Seja um dos nossos Clientes</h2>
                    <p style="color: white; text-shadow: 1px 1px 2px black  ;">Encontre os melhores prestadores de
                        serviços de limpeza.</p>
                    <a href="pages/cadastro.php" class="btn btn-primary">Cadastre-se</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="img/sliderbar_1.png" class="d-block w-100" style="max-height: 500px; object-fit: cover;"
                    alt="Seja um Prestador de Serviços">
                <div class="carousel-caption d-none d-md-block">
                    <h2 style="color: white; text-shadow: 1px 1px 2px black;">Seja um Prestador de Serviços</h2>
                    <p style="color: white; text-shadow: 1px 1px 2px black;">Junte-se a nós e ofereça seus serviços de limpeza.</p>
                    <a href="pages/cadastro.php" class="btn btn-primary">Cadastre-se</a>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselInicialSC" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselInicialSC" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Próximo</span>
        </button>
    </div>
</div>


<div class="container text-center my-5 py-5">
    <h1 class="display-3 fw-bold">Conheça alguns dos nossos serviços disponíveis</h1>
    <p class="lead col-lg-8 mx-auto">Na StarClean, oferecemos uma variedade de serviços de limpeza para atender às suas necessidades. Confira alguns deles:</p>
</div>

<?php include 'includes/footer.php'; ?>