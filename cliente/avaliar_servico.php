<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas clientes podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    header("Location: ../pages/login.php");
    exit();
}

$mensagem = '';
$id_cliente = $_SESSION['usuario_id'];
$agendamento = null;
$avaliacao_existente = null;

// 1. Validação e Busca Inicial
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensagem_erro'] = "ID do agendamento não fornecido ou inválido.";
    header("Location: meus_agendamentos.php");
    exit();
}

$id_agendamento = $_GET['id'];

try {
    $pdo = obterConexaoPDO();

    // Buscar detalhes do agendamento (incluindo Prestador_id para a avaliação)
    $stmt = $pdo->prepare(
        "SELECT a.id, a.Prestador_id, a.Servico_id, a.status, s.titulo AS titulo_servico, p.nome_razão_social AS nome_prestador
         FROM Agendamento a
         JOIN Servico s ON a.Servico_id = s.id
         JOIN Prestador p ON a.Prestador_id = p.id
         WHERE a.id = ? AND a.Cliente_id = ?"
    );
    $stmt->execute([$id_agendamento, $id_cliente]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$agendamento) {
        $_SESSION['mensagem_erro'] = "Agendamento não encontrado ou acesso não autorizado.";
        header("Location: meus_agendamentos.php");
        exit();
    }
    
    // Verificar se o agendamento está 'realizado'
    if ($agendamento['status'] !== 'realizado') {
        $_SESSION['mensagem_erro'] = "Este agendamento ainda não foi concluído e não pode ser avaliado.";
        header("Location: meus_agendamentos.php");
        exit();
    }

    // 2. Verificar se já existe uma avaliação para este prestador/cliente
    // Nota: O cliente avalia o prestador, não o agendamento em si, para fins de consistência.
    $stmt_check = $pdo->prepare(
        // Verifica se já existe uma avaliação do cliente para este prestador
        "SELECT nota, comentario FROM avaliacao_prestador 
         WHERE Cliente_id = ? AND Prestador_id = ? LIMIT 1"
    );
    $stmt_check->execute([$id_cliente, $agendamento['Prestador_id']]);
    $avaliacao_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($avaliacao_existente) {
        $mensagem = '<div class="alert alert-info">Você já avaliou o prestador deste serviço (Nota anterior: ' . $avaliacao_existente['nota'] . '). Sua nova avaliação irá substituí-la.</div>';
    }

} catch (PDOException $e) {
    $mensagem = '<div class="alert alert-danger">Erro ao carregar dados. Tente novamente.</div>';
    error_log("Erro em avaliar_servico.php (GET): " . $e->getMessage());
}

// 3. Processamento do Formulário (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $agendamento) {
    $nota = $_POST['nota'] ?? null;
    $comentario = trim($_POST['comentario'] ?? '');
    
    if (empty($nota) || $nota < 1 || $nota > 5) {
        $mensagem = '<div class="alert alert-danger">Por favor, selecione uma nota de 1 a 5.</div>';
    } else {
        try {
            $prestador_id = $agendamento['Prestador_id'];
            
            // Verifica se a avaliação já existe para decidir entre INSERT ou UPDATE
            if ($avaliacao_existente) {
                 // Atualiza a avaliação existente
                 $stmt = $pdo->prepare(
                    "UPDATE avaliacao_prestador SET nota = ?, comentario = ? 
                     WHERE Cliente_id = ? AND Prestador_id = ?"
                 );
                 $stmt->execute([$nota, $comentario, $id_cliente, $prestador_id]);
                 $_SESSION['mensagem_sucesso'] = "Sua avaliação foi atualizada com sucesso!";
            } else {
                // Insere nova avaliação
                $stmt = $pdo->prepare(
                    "INSERT INTO avaliacao_prestador (Cliente_id, Prestador_id, nota, comentario) 
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->execute([$id_cliente, $prestador_id, $nota, $comentario]);
                $_SESSION['mensagem_sucesso'] = "Sua avaliação foi enviada com sucesso!";
            }
            
            header("Location: meus_agendamentos.php");
            exit();

        } catch (PDOException $e) {
            $mensagem = '<div class="alert alert-danger">Erro ao salvar sua avaliação. Tente novamente.</div>';
            error_log("Erro em avaliar_servico.php (POST): " . $e->getMessage());
        }
    }
}

include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>

<button class="btn btn-primary d-md-none m-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"
    aria-controls="sidebarMenu">
    <i class="fas fa-bars"></i> Menu
</button>

<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarMenuLabel">Navegação</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <?php include '../includes/menu.php'; ?>
    </div>
</div>
<main class="d-flex">
    <?php include '../includes/sidebar.php'; ?>

    <div class="container-fluid p-4">
        <h1 class="mb-4">Avaliar Serviço</h1>
        <hr>

        <?= $mensagem ?>

        <?php if ($agendamento): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Avaliação do Agendamento ID: <?= htmlspecialchars($agendamento['id']) ?></h5>
                </div>
                <div class="card-body">
                    <p><strong>Serviço:</strong> <?= htmlspecialchars($agendamento['titulo_servico']) ?></p>
                    <p><strong>Prestador:</strong> <?= htmlspecialchars($agendamento['nome_prestador']) ?></p>

                    <form action="avaliar_servico.php?id=<?= htmlspecialchars($id_agendamento) ?>" method="post">
                        <div class="mb-3">
                            <label class="form-label">Sua Nota (1 a 5 Estrelas):</label>
                            <div class="rating-stars" id="ratingStars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="far fa-star fa-2x text-warning" data-value="<?= $i ?>"></i>
                                <?php endfor; ?>
                                <input type="hidden" name="nota" id="notaInput" value="<?= htmlspecialchars($avaliacao_existente['nota'] ?? '') ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="comentario" class="form-label">Comentário (Opcional):</label>
                            <textarea class="form-control" id="comentario" name="comentario" rows="3" placeholder="Compartilhe sua experiência..."><?= htmlspecialchars($avaliacao_existente['comentario'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
                        <a href="meus_agendamentos.php" class="btn btn-secondary">Voltar</a>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    /* Estilos para as estrelas de avaliação */
    .rating-stars .fa-star {
        cursor: pointer;
        transition: color 0.2s;
    }
    /* Estilo para estrelas preenchidas */
    .rating-stars .fas.fa-star {
        color: orange; 
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const starsContainer = document.getElementById('ratingStars');
        const stars = starsContainer.querySelectorAll('.fa-star');
        const notaInput = document.getElementById('notaInput');

        // Função para atualizar a cor das estrelas
        function updateStars(value) {
            stars.forEach((star, index) => {
                const starValue = parseInt(star.getAttribute('data-value'));
                if (starValue <= value) {
                    star.classList.remove('far'); // Outline
                    star.classList.add('fas'); // Solid
                } else {
                    star.classList.remove('fas');
                    star.classList.add('far');
                }
            });
        }

        // Evento de click: define a nota e a cor
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = parseInt(this.getAttribute('data-value'));
                notaInput.value = value;
                updateStars(value);
            });

            // Efeito hover: visualiza a nota antes de clicar
            star.addEventListener('mouseover', function() {
                const value = parseInt(this.getAttribute('data-value'));
                updateStars(value);
            });
            
            // Efeito mouseout: volta para a nota selecionada (ou nada)
            star.addEventListener('mouseout', function() {
                const selectedValue = parseInt(notaInput.value);
                updateStars(selectedValue);
            });
        });

        // Inicializa as estrelas com a nota existente (se houver)
        updateStars(parseInt(notaInput.value));
    });
</script>

<?php include '../includes/footer.php'; ?>