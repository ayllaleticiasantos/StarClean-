<?php 

include('../config/config.php');
include '../includes/header.php';
include '../includes/navbar.php';

?>

<header class="hero-section" style="background: url(../img/Avaliacoes.jpg) center/cover no-repeat; height: 400px; display: flex; align-items: center; color: black; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">
    <div class="container">
        <h1 class="display-4 fw-bold">Avaliações</h1>
        <p class="lead">Veja o que nossos clientes estão dizendo sobre nós.</p>
    </div>
</header>

<main class="container my-5">

    <section class="mb-5">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mt-3">Avaliação 1</h3>
                        <p class="card-text">"A Star Clean fez um excelente trabalho na limpeza do meu escritório. Recomendo!"</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mt-3">Avaliação 2</h3>
                        <p class="card-text">"Profissionais atenciosos e serviço de qualidade. Estou muito satisfeito!"</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mt-3">Avaliação 3</h3>
                        <p class="card-text">"Ótima experiência! A equipe foi pontual e eficiente."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php

include '../includes/footer.php';

?>