<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container m-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
            <img src="<?= BASE_URL ?>/img/logoPrimary.png" alt="StarClean" height="50" class="d-inline-block align-text-top my-1">
        </a>
        <!-- <i class="bi bi-star fs-3 me-2 bg-circle p-2 text-white">StarClean</i></a> -->
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto m-3">
                <li class="nav-item" id="nav-home">
                    <a class="nav-link" href="<?= BASE_URL ?>/index.php">Home</a>
                </li>
                <li class="nav-item" id="nav-sobre">
                    <a class="nav-link" href="<?= BASE_URL ?>/pages/sobre.php">Sobre Nós</a>
                </li>
                <li class="nav-item" id="nav-avaliacoes">
                    <a class="nav-link" href="<?= BASE_URL ?>/pages/avaliacoes.php">Avaliações</a>
                </li>
                <li class="nav-item" id="nav-servicos">
                    <a href="<?= BASE_URL ?>/pages/servicos.php" class="nav-link">Serviços</a>
                </li>
            </ul>

            <?php
            // Pega o caminho do script atual (ex: /star_clean/pages/login.php)
            $currentPage = $_SERVER['PHP_SELF'];

            // SÓ MOSTRA O BOTÃO SE A PÁGINA ATUAL NÃO FOR A DE LOGIN OU CADASTRO
            if (strpos($currentPage, 'login.php') === false && strpos($currentPage, 'cadastro.php') === false) :
            ?>
                <div class="d-flex">
                    <a href="<?= BASE_URL ?>/pages/login.php" class="btn btn-outline-light">Entrar no Sistema</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</nav>
