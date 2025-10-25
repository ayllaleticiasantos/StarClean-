<?php
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div>
    <div id="carouselInicialSC" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselInicialSC" data-bs-slide-to="0" class="active" aria-current="true"
                aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselInicialSC" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselInicialSC" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="img/sliderbar_1.png" class="d-block w-100" style="max-height: 500px; object-fit: cover;"
                    alt="Seja bem-vindo à StarClean">
                <div class="carousel-caption d-none d-md-block">
                    <h2 class="display-3 fw-bold text-white text-shadow 
                                    align-self-center">Bem-vindos à StarClean</h2>
                    <p class="lead col-lg-8 mx-auto text-white">A sua plataforma para agendar serviços de limpeza com qualidade e
                        confiança.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="img/sliderbar_1.png" class="d-block w-100" style="max-height: 500px; object-fit: cover;"
                    alt="Seja um Cliente">
                <div class="carousel-caption d-none d-md-block">
                    <h2 style="color: white; text-shadow: 1px 1px 2px black;">Seja um dos nossos Clientes</h2>
                    <p style="color: white; text-shadow: 1px 1px 2px black ;">Encontre os melhores prestadores de
                        serviços de limpeza.</p>
                    <a href="pages/cadastro.php" class="btn btn-primary">Cadastre-se</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="img/sliderbar_1.png" class="d-block w-100" style="max-height: 500px; object-fit: cover;"
                    alt="Seja um Prestador de Serviços">
                <div class="carousel-caption d-none d-md-block">
                    <h2 style="color: white; text-shadow: 1px 1px 2px black;">Seja um Prestador de Serviços</h2>
                    <p style="color: white; text-shadow: 1px 1px 2px black;">Junte-se a nós e ofereça seus serviços de limpeza.
                    </p>
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


<div class="container my-5">
    <div class="row">
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <img src="img/escritorio2.png" class="card-img-top" style="object-fit: cover; height: 200px;" alt="Higienização do escritório">
                <div class="card-body">
                    <h5 class="card-title">Higienização do Escritório</h5>
                    <p class="card-text">Este é um serviço de limpeza especializado para escritórios, garantindo um ambiente de
                        trabalho limpo e saudável.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <img src="img/cozinha.png" class="card-img-top" style="object-fit: cover; height: 200px;" alt="Higienização da Sua Casa">
                <div class="card-body">
                    <h5 class="card-title">Limpeza completa da sua casa.</h5>
                    <p class="card-text">Este é um serviço de limpeza especializado para residencias, garantindo que a sua casa
                        permaneça limpa e bem cuidada.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <img src="img/biblioteca.png" class="card-img-top" style="object-fit: cover; height: 200px;" alt="Higienização da Biblioteca">
                <div class="card-body">
                    <h5 class="card-title">Higienização da Biblioteca</h5>
                    <p class="card-text">Este é um serviço de limpeza especializado para bibliotecas, garantindo um ambiente de
                        leitura limpo e organizado para sua familia.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <img src="img/sala.png" class="card-img-top" style="object-fit: cover; height: 200px;" alt="Higienização da Sala">
                <div class="card-body">
                    <h5 class="card-title">Higienização da Sala</h5>
                    <p class="card-text">Este é um serviço de limpeza especializado para salas de estar, garantindo um ambiente
                        aconchegante limpo e bem cuidado.</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <img src="img/quarto.png" class="card-img-top" style="object-fit: cover; height: 200px;" alt="Higienização do Quarto">
                <div class="card-body">
                    <h5 class="card-title">Higienização do Quarto</h5>
                    <p class="card-text">Este é um serviço de limpeza especializado para casas no geral você pode contratar na
                        diária, mensalmente e por pacotes.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <img src="img/limpando.jpg" class="card-img-top" style="object-fit: cover; height: 200px;" alt="Outro Serviço">
                <div class="card-body">
                    <h5 class="card-title">Outros Serviços</h5>
                    <p class="card-text">Na Star Clean, oferecemos uma variedade de serviços adicionais para atender às suas necessidades específicas.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>