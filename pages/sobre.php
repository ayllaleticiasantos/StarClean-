<?php

include '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';

?>

<header class="hero-section" style="background: url(../img/cleaning.jpeg) center/cover no-repeat; height: 400px; display: flex; align-items: start; color: black; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">
    <div class="container">
        <h1 class="display-4 fw-bold">Conheça a StarClean</h1>
        <p class="lead">Eficiência na prestação de serviços de limpeza para empresas, escritórios e
            residências.</p>
    </div>
</header>

<main class="container my-5">

    <section class="mb-5">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <i class="bi bi-bullseye fs-1 text-primary"></i>
                        <h3 class="card-title mt-3">Missão</h3>
                        <p class="card-text">Prestar serviços de qualidade aos clientes, com excelência, rapidez,
                            segurança, ética e eficiência, utilizando produtos de alto padrão e profissionais
                            capacitados.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <i class="bi bi-binoculars-fill fs-1 text-primary"></i>
                        <h3 class="card-title mt-3">Visão</h3>
                        <p class="card-text">Ser referência no Distrito Federal e região, expandindo nossos serviços
                            para atender tanto órgãos públicos, quanto privados e cultivar uma base de clientes
                            fidelizada.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <i class="bi bi-gem fs-1 text-primary"></i>
                        <h3 class="card-title mt-3">Valores</h3>
                        <ul class="list-unstyled align-center">
                            <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Ética</li>
                            <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Integridade</li>
                            <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Profissionalismo</li>
                            <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Transparência</li>
                            <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Pontualidade</li>
                            <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Eficiência</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light rounded">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-6">Nossa Liderança</h2>
                    <p class="lead">Conheça as profissionais que guiam a StarClean ao sucesso.</p>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-6 mb-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body p-4">
                            <!-- <img src="https://via.placeholder.com/150" alt="Foto de Gislene Aparecida"
                                class="rounded-circle team-member mb-3"> -->
                            <h4 class="mb-1">Gislene Aparecida Oliveira da Silva</h4>
                            <h6 class="text-primary mb-3">Diretora e Advogada</h6>
                            <p class="text-muted">Graduada em Direito, Técnica em Logística e Técnica em Administração e cursando Técnico em
                                Informática. Responsável pela direção da empresa, visão estratégica, relacionamento
                                com clientes e fornecedores, além da área jurídica.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 col-md-6 mb-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body p-4">
                            <!-- <img src="https://via.placeholder.com/150" alt="Foto de Ana Júlia Brito"
                                class="rounded-circle team-member mb-3"> -->
                            <h4 class="mb-1">Ana Júlia Brito de Souza</h4>
                            <h6 class="text-primary mb-3">Gerente</h6>
                            <p class="text-muted">Técnico em Administração. Atua na gerência da empresa, contribuindo
                                com a visão estratégica, orientação de equipes e relacionamento com clientes e
                                fornecedores.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-5">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <h2 class="mb-3">Dados do Empreendimento</h2>
                <div class="card">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Nome da Empresa:</strong> Star Clean</li>
                        <li class="list-group-item"><strong>Razão Social:</strong> Star Clean Serviços de Limpeza</li>
                        <li class="list-group-item"><strong>CNPJ:</strong> 01.123.567/0001-00</li>
                        <li class="list-group-item"><strong>Endereço:</strong> Taguatinga Centro - Quadra C 11, loja 2
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <h2 class="mb-3">Indicadores de Viabilidade</h2>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Indicador</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Lucratividade</td>
                                <td>13,15% ao ano</td>
                            </tr>
                            <tr>
                                <td>Rentabilidade</td>
                                <td>81,70% ao ano</td>
                            </tr>
                            <tr>
                                <td>Prazo de Retorno do Investimento</td>
                                <td>1 ano, 2 meses e 19 dias</td>
                            </tr>
                            <tr>
                                <td>Ponto de Equilíbrio (PE)</td>
                                <td>R$ 1.275.222,52 ao ano</td>
                            </tr>
                        </tbody>
                    </table>
                    <small class="text-muted">Capital inicial investido: R$ 340.800,00 | Faturamento mensal projetado:
                        R$ 176.120,00</small>
                </div>
            </div>
        </div>
    </section>

</main>

<? include '../includes/footer.php'; ?>