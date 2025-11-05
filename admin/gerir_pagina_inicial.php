<?php
session_start();
require_once '../config/db.php';
require_once '../includes/log_helper.php'; // Inclui nossa nova função de log

// Segurança: Apenas administradores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$id_admin_logado = $_SESSION['usuario_id'];
$mensagem_sucesso = '';
$mensagem_erro = '';

// Lógica para processar o formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = obterConexaoPDO();
        $pdo->beginTransaction();

        // Itera sobre os dados enviados
        foreach ($_POST['conteudo'] as $id => $dados) {
            $titulo = $dados['titulo'];
            $texto = $dados['texto'];
            $ativo = isset($dados['ativo']) ? 1 : 0;

            // Lógica de upload de imagem
            $imagem_url = $dados['imagem_atual']; // Mantém a imagem atual por padrão
            if (isset($_FILES['conteudo']['name'][$id]['imagem']) && $_FILES['conteudo']['error'][$id]['imagem'] == 0) {
                $upload_dir = '../img/';
                $nome_arquivo = basename($_FILES['conteudo']['name'][$id]['imagem']);
                $caminho_arquivo = $upload_dir . $nome_arquivo;
                
                // Move o arquivo para a pasta de uploads
                if (move_uploaded_file($_FILES['conteudo']['tmp_name'][$id]['imagem'], $caminho_arquivo)) {
                    $imagem_url = 'img/' . $nome_arquivo; // Salva o caminho relativo
                } else {
                    throw new Exception("Falha ao mover o arquivo de imagem.");
                }
            }

            $stmt = $pdo->prepare(
                "UPDATE conteudo_pagina_inicial SET titulo = ?, texto = ?, imagem_url = ?, ativo = ?, editado_por_admin_id = ? WHERE id = ?"
            );
            // Adiciona o ID do admin logado na atualização
            $stmt->execute([$titulo, $texto, $imagem_url, $ativo, $id_admin_logado, $id]);
        }

        $pdo->commit();
        $mensagem_sucesso = "Conteúdo da página inicial atualizado com sucesso!";

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $mensagem_erro = "Erro ao atualizar o conteúdo: " . $e->getMessage();
        error_log("Erro em gerir_pagina_inicial.php: " . $e->getMessage());
    }

    // Registra a ação no log de atividades
    registrar_log_admin($id_admin_logado, "Editou o conteúdo da página inicial.");
}

// Buscar o conteúdo atual do banco de dados
$conteudos = [];
try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->query("SELECT * FROM conteudo_pagina_inicial ORDER BY tipo_conteudo, ordem");
    $conteudos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao carregar o conteúdo da página.";
}

include '../includes/header.php';
include '../includes/navbar_logged_in.php';
?>

<button class="btn btn-primary d-md-none m-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
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
        <h1 class="mb-4">Gerir Conteúdo da Página Inicial</h1>

        <?php if ($mensagem_sucesso): ?><div class="alert alert-success"><?= $mensagem_sucesso ?></div><?php endif; ?>
        <?php if ($mensagem_erro): ?><div class="alert alert-danger"><?= $mensagem_erro ?></div><?php endif; ?>

        <form action="gerir_pagina_inicial.php" method="post" enctype="multipart/form-data">
            <div class="accordion" id="accordionConteudo">
                <?php foreach ($conteudos as $item): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-<?= $item['id'] ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $item['id'] ?>" aria-expanded="false" aria-controls="collapse-<?= $item['id'] ?>">
                                <strong><?= ucfirst($item['tipo_conteudo']) ?> #<?= $item['ordem'] ?>:</strong>&nbsp;<?= htmlspecialchars($item['titulo']) ?>
                            </button>
                        </h2>
                        <div id="collapse-<?= $item['id'] ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?= $item['id'] ?>" data-bs-parent="#accordionConteudo">
                            <div class="card-body">
                                <input type="hidden" name="conteudo[<?= $item['id'] ?>][imagem_atual]" value="<?= htmlspecialchars($item['imagem_url']) ?>">
                                
                                <div class="mb-3">
                                    <label for="titulo-<?= $item['id'] ?>" class="form-label">Título</label>
                                    <input type="text" class="form-control" id="titulo-<?= $item['id'] ?>" name="conteudo[<?= $item['id'] ?>][titulo]" value="<?= htmlspecialchars($item['titulo']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="texto-<?= $item['id'] ?>" class="form-label">Texto</label>
                                    <textarea class="form-control" id="texto-<?= $item['id'] ?>" name="conteudo[<?= $item['id'] ?>][texto]" rows="3"><?= htmlspecialchars($item['texto']) ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="imagem-<?= $item['id'] ?>" class="form-label">Imagem</label><br>
                                    <?php if ($item['imagem_url']): ?>
                                        <img src="<?= BASE_URL . '/' . htmlspecialchars($item['imagem_url']) ?>" alt="Imagem atual" style="max-width: 150px; height: auto; margin-bottom: 10px;">
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="imagem-<?= $item['id'] ?>" name="conteudo[<?= $item['id'] ?>][imagem]" accept="image/png, image/jpeg, image/jpg">
                                    <small class="form-text text-muted">Envie uma nova imagem para substituir a atual. Tamanho recomendado: 1200x500 para carrossel, 400x200 para cards.</small>
                                </div>

                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="ativo-<?= $item['id'] ?>" name="conteudo[<?= $item['id'] ?>][ativo]" value="1" <?= $item['ativo'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="ativo-<?= $item['id'] ?>">Exibir este item na página inicial</label>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary btn-lg">Salvar Todas as Alterações</button>
            </div>
        </form>
    </div>
</main>

<?php include '../includes/footer.php'; ?>