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

<div class="container">
    <h1 class="mb-4 text-center">Tabela de Preços de Serviços StarClean</h1>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-primary">
                <tr>
                    <th scope="col" style="width: 70%;">Serviço Detalhado</th>
                    <th scope="col" style="width: 30%;" class="text-end">Preço</th>
                </tr>
            </thead>
            <tbody>
        <tr>
            <td colspan="2" class="table-group-divider text-primary">Combo 1 Nível Básico</td>
        </tr>
    
        <tr>
            <td>Combo 1 nível básico: para casa de 1 quarto diária</td>
            <td class="text-end text-primary fw-bold">R$180,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para casa de 1 quarto mensal</td>
            <td class="text-end text-primary fw-bold">R$720,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para casa de 3 quartos diária</td>
            <td class="text-end text-primary fw-bold">R$270,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para casa de 3 quartos mensal</td>
            <td class="text-end text-primary fw-bold">R$1.080,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para casa de 4 quartos diária</td>
            <td class="text-end text-primary fw-bold">R$ 320,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para casa de 4 quartos mensal</td>
            <td class="text-end text-primary fw-bold">R$1.280,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para casa de +4 quartos diária</td>
            <td class="text-end text-primary fw-bold">R$ 450,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para casa de +4 quartos mensal</td>
            <td class="text-end text-primary fw-bold">R$1.800,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para escritório de 1 sala diária</td>
            <td class="text-end text-primary fw-bold">R$190,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para escritório de 1 sala mensal</td>
            <td class="text-end text-primary fw-bold">R$760,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para escritório de 3 salas diária</td>
            <td class="text-end text-primary fw-bold">R$260,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para escritório de 3 salas mensal</td>
            <td class="text-end text-primary fw-bold">R$1.040,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para escritório de 4 salas diária</td>
            <td class="text-end text-primary fw-bold">R$300,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para escritório de 4 salas mensal</td>
            <td class="text-end text-primary fw-bold">R$1.200,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para escritório de +4 salas diária</td>
            <td class="text-end text-primary fw-bold">R$400,00</td>
        </tr>
        
        <tr>
            <td>Combo 1 nível básico: para escritório de +4 salas mensal</td>
            <td class="text-end text-primary fw-bold">R$1.600,00</td>
        </tr>
        
        <tr>
            <td colspan="2" class="table-group-divider text-primary">Combo 2 Nível Intermediário</td>
        </tr>
    
        <tr>
            <td>Combo 2 nível intermediário: para casa de 2 quartos diária</td>
            <td class="text-end text-primary fw-bold">R$270,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para casa de 2 quartos mensal</td>
            <td class="text-end text-primary fw-bold">R$1.000,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para casa de 3 quartos diária</td>
            <td class="text-end text-primary fw-bold">R$300,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para casa de 3 quartos mensal</td>
            <td class="text-end text-primary fw-bold">R$1.200,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para casa de 4 quartos diária</td>
            <td class="text-end text-primary fw-bold">R$350,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para casa de 4 quartos mensal</td>
            <td class="text-end text-primary fw-bold">R$1.400,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para casa de +4 quartos diária</td>
            <td class="text-end text-primary fw-bold">R$500,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para casa de +4 quartos mensal</td>
            <td class="text-end text-primary fw-bold">R$2.000,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para escritório de 2 salas diária</td>
            <td class="text-end text-primary fw-bold">R$260,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para escritório de 2 salas mensal</td>
            <td class="text-end text-primary fw-bold">R$1.040,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para escritório de 3 salas diária</td>
            <td class="text-end text-primary fw-bold">R$320,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para escritório de 3 salas mensal</td>
            <td class="text-end text-primary fw-bold">R$1.280,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para escritório de 4 salas diária</td>
            <td class="text-end text-primary fw-bold">R$380,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para escritório de 4 salas mensal</td>
            <td class="text-end text-primary fw-bold">R$1.520,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para escritório de +4 salas diária</td>
            <td class="text-end text-primary fw-bold">R$480,00</td>
        </tr>
        
        <tr>
            <td>Combo 2 nível intermediário: para escritório de +4 salas mensal</td>
            <td class="text-end text-primary fw-bold">R$1.920,00</td>
        </tr>
        
        <tr>
            <td colspan="2" class="table-group-divider text-primary">Combo 3 Nível Brilhante</td>
        </tr>
    
        <tr>
            <td>Combo 3 nível brilhante: para casa de 1 quarto diária</td>
            <td class="text-end text-primary fw-bold">R$360,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para casa de 1 quarto mensal</td>
            <td class="text-end text-primary fw-bold">R$1.440,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para casa de 2 quartos diária</td>
            <td class="text-end text-primary fw-bold">R$430,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para casa de 2 quartos mensal</td>
            <td class="text-end text-primary fw-bold">R$1.720,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para casa de 3 quartos diária</td>
            <td class="text-end text-primary fw-bold">R$520,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para casa de 3 quartos mensal</td>
            <td class="text-end text-primary fw-bold">R$2.080,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para casa de +4 quartos diária</td>
            <td class="text-end text-primary fw-bold">R$620,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para casa de +4 quartos mensal</td>
            <td class="text-end text-primary fw-bold">R$2.480,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para escritório de 1 sala diária</td>
            <td class="text-end text-primary fw-bold">R$440,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para escritório de 1 sala mensal</td>
            <td class="text-end text-primary fw-bold">R$1.760,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para escritório de 2 salas diária</td>
            <td class="text-end text-primary fw-bold">R$490,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para escritório de 2 salas mensal</td>
            <td class="text-end text-primary fw-bold">R$1.960,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para escritório de 3 salas diária</td>
            <td class="text-end text-primary fw-bold">R$580,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para escritório de 3 salas mensal</td>
            <td class="text-end text-primary fw-bold">R$2.320,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para escritório de +4 salas diária</td>
            <td class="text-end text-primary fw-bold">R$680,00</td>
        </tr>
        
        <tr>
            <td>Combo 3 nível brilhante: para escritório de +4 salas mensal</td>
            <td class="text-end text-primary fw-bold">R$2.720,00</td>
        </tr>
        
            </tbody>
        </table>
    </div>

<?php include 'includes/footer.php'; ?>