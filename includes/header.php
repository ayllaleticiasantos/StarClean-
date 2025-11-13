<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://cdn.tiny.cloud/1/s98nwfy460zya63abrfocp53rjiw5ydguwp2xtm3k7iv6jwu/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const path = window.location.pathname;

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

            if (activeId) {
                const activeElement = document.getElementById(activeId);
                if (activeElement) {
                    document.querySelectorAll('.navbar-nav .nav-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    activeElement.classList.add('active');
                }
            }
        });
    </script>

    <style>
    #sidebar {
        width: 280px;
        flex-shrink: 0;
    }

    @media (max-width: 767.98px) {
        #sidebar {
            width: auto;
        }
    }
  
        @media print {

            body {
                background-color: #fff !important;
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

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100" style="background-color: #acd0f5ff;">

    <?php
    if (session_status() === PHP_SESSION_NONE) {
    }
    ?>