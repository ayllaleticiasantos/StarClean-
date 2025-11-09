<?php
// Carrega a configuração da BASE_URL, se necessário
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}
// session_start() deve ser chamada na primeira linha de CADA ARQUIVO PHP que utiliza sessões.
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StarClean - Sistema de Limpeza</title>

    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/img/logoBranca.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/includes/star_clean.css">

    <script src="https://code.jquery.com/jquery-3.7.1.slim.js"
        integrity="sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc=" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css" />

    <!-- CSS do Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- JS do Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <!-- Biblioteca de Gráficos (Chart.js) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Biblioteca de Editor de Texto Rico (TinyMCE) -->
    <script src="https://cdn.tiny.cloud/1/s98nwfy460zya63abrfocp53rjiw5ydguwp2xtm3k7iv6jwu/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Pega o caminho completo da URL atual (ex: /star_clean/pages/sobre.php)
            const path = window.location.pathname;

            // Mapeamento simples de caminhos para IDs de LI
            // NOTE: Use a parte final do caminho ou o nome do arquivo para o mapeamento
            let activeId;

            if (path.endsWith('/index.php') || path.endsWith('/star_clean/')) {
                activeId = 'nav-home';
            } else if (path.includes('/pages/sobre.php')) {
                activeId = 'nav-sobre';
            } else if (path.includes('/pages/avaliacoes.php')) {
                activeId = 'nav-avaliacoes';
            } else if (path.includes('/pages/servicos.php')) {
                activeId = 'nav-servicos';
            }

            // Adiciona a classe 'active' ao item de navegação correto
            if (activeId) {
                const activeElement = document.getElementById(activeId);
                if (activeElement) {
                    // Remove a classe 'active' de todos os links para evitar duplicidade
                    document.querySelectorAll('.navbar-nav .nav-item').forEach(item => {
                        item.classList.remove('active');
                    });

                    // Adiciona a classe 'active' ao item da página atual
                    activeElement.classList.add('active');
                }
            }
        });
    </script>

    <style>
    #sidebar {
        width: 280px;     /* Largura fixa para o menu */
        flex-shrink: 0;   /* Impede que o menu seja "esmagado" */
    }

    /* Em telas pequenas, a sidebar está no offcanvas, então removemos a largura */
    @media (max-width: 767.98px) {
        #sidebar {
            width: auto;
        }
    }
  
        /* Estilos para a impressão */
        @media print {

            /* Oculta tudo que não deve aparecer na impressão */
            body {
                background-color: #fff !important;
                /* Fundo branco para economizar tinta */
            }

            .navbar,
            #sidebar,
            .offcanvas,
            .btn,
            footer,
            #btn-print,
            form,
            .no-print {
                display: none !important;
            }

            /* Garante que o conteúdo principal ocupe toda a página */
            main,
            .container-fluid {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
            }

            /* NOVO: Força a impressão de cores e fundos em todos os elementos */
            * {
                -webkit-print-color-adjust: exact !important; /* Chrome, Safari, Edge */
                print-color-adjust: exact !important; /* Firefox */
            }
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100" style="background-color: #acd0f5ff;">

    <?php
    // Inicia a sessão se ainda não tiver sido iniciada para verificar o login
    if (session_status() === PHP_SESSION_NONE) {
        // Nada aqui, session_start() está no topo de cada script
    }
    ?>