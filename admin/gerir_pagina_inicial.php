<?php
session_start();
require_once '../config/config.php'; // Adicionado para BASE_URL
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
    // --- LÓGICA PARA ATUALIZAR CONTEÚDO GERAL (TEXTOS) ---
    if (isset($_POST['conteudo_geral'])) {
        foreach ($_POST['conteudo_geral'] as $chave => $valor) {
            $stmt = obterConexaoPDO()->prepare("UPDATE conteudo_geral SET conteudo = ? WHERE chave = ?");
            $stmt->execute([trim($valor), $chave]);
        }
        $mensagem_sucesso = "Conteúdo de texto atualizado com sucesso!"; // Mensagem provisória
    }

    // --- CORREÇÃO AQUI ---
    // Só executa esta lógica se os dados de 'conteudo' (acordeão) forem enviados
    if (isset($_POST['conteudo'])) {
        try {
            $pdo = obterConexaoPDO();
            $pdo->beginTransaction();

            // Itera sobre os dados enviados
            // A linha 31 original agora está segura dentro do 'if'
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
            // Sobrescreve a mensagem se esta parte também rodar
            $mensagem_sucesso = "Conteúdo da página inicial (dinâmico) atualizado com sucesso!"; 

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $mensagem_erro = "Erro ao atualizar o conteúdo: " . $e->getMessage();
            error_log("Erro em gerir_pagina_inicial.php: " . $e->getMessage());
        }
    } // --- FIM DA CORREÇÃO (fechamento do 'if' adicionado) ---

    // Registra a ação no log de atividades (só registra se algo foi postado)
    registrar_log_admin($id_admin_logado, "Editou o conteúdo das páginas (inicial/sobre).");
}

// Buscar o conteúdo atual do banco de dados
$conteudos = [];
try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->query("SELECT * FROM conteudo_pagina_inicial ORDER BY tipo_conteudo, ordem");
    $conteudos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao carregar o conteúdo dinâmico da página.";
}

// --- NOVA LÓGICA: Buscar conteúdo geral (textos) ---
$conteudo_geral = [];
try {
    // Garante que $pdo está definido mesmo se o POST falhar
    if (!isset($pdo)) {
        $pdo = obterConexaoPDO();
    }
    
    $stmt_geral = $pdo->query("SELECT * FROM conteudo_geral ORDER BY pagina, id");
    foreach ($stmt_geral->fetchAll(PDO::FETCH_ASSOC) as $item) {
        $conteudo_geral[$item['pagina']][] = $item; // Agrupa por página
    }
} catch (PDOException $e) {
    $mensagem_erro .= " Erro ao carregar os textos da página.";
}

// --- NOVA LÓGICA: Buscar todos os serviços para exibir ---
$servicos_por_prestador = [];
try {
    $stmt_servicos = $pdo->query(
        "SELECT s.id, s.titulo, s.descricao, s.preco, p.nome AS nome_prestador, p.id AS prestador_id
         FROM Servico s
         JOIN Prestador p ON s.prestador_id = p.id
         ORDER BY p.nome, s.titulo"
    );
    $todos_servicos = $stmt_servicos->fetchAll(PDO::FETCH_ASSOC);

    // Agrupa os serviços pelo nome do prestador
    foreach ($todos_servicos as $servico) {
        $servicos_por_prestador[$servico['nome_prestador']][] = $servico;
    }

} catch (PDOException $e) {
    if (empty($mensagem_erro)) { // Evita sobrepor a mensagem de erro anterior
        $mensagem_erro = "Erro ao carregar a lista de serviços.";
    }
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

    <div class="container-fluid p-4 flex-grow-1">
        <h1 class="mb-4">Gerir Conteúdo da Página Inicial</h1>

        <?php if ($mensagem_sucesso): ?><div class="alert alert-success"><?= $mensagem_sucesso ?></div><?php endif; ?>
        <?php if ($mensagem_erro): ?><div class="alert alert-danger"><?= $mensagem_erro ?></div><?php endif; ?>

        <!-- Abas de Navegação -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="index-tab" data-bs-toggle="tab" data-bs-target="#index-content" type="button" role="tab">Página Inicial</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sobre-tab" data-bs-toggle="tab" data-bs-target="#sobre-content" type="button" role="tab">Página Sobre</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="servicos-tab" data-bs-toggle="tab" data-bs-target="#servicos-content" type="button" role="tab">Serviços Cadastrados</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="termos-tab" data-bs-toggle="tab" data-bs-target="#termos-content" type="button" role="tab">Termos de Uso</button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <!-- Conteúdo da Aba Página Inicial -->
            <div class="tab-pane fade show active" id="index-content" role="tabpanel">
                <!-- O formulário da página inicial agora envia TUDO (geral e dinâmico) -->
                <form action="gerir_pagina_inicial.php" method="post" enctype="multipart/form-data">
                    <div class="card mt-3">
                        <div class="card-header"><h5>Textos da Página Inicial</h5></div>
                        <div class="card-body">
                            <?php foreach ($conteudo_geral['index'] ?? [] as $item): ?>
                                <div class="mb-3">
                                    <label for="cg-<?= $item['chave'] ?>" class="form-label"><strong><?= htmlspecialchars($item['titulo']) ?></strong></label>
                                    <?php if ($item['tipo'] === 'textarea'): ?>
                                        <textarea class="form-control" id="cg-<?= $item['chave'] ?>" name="conteudo_geral[<?= $item['chave'] ?>]" rows="3"><?= htmlspecialchars($item['conteudo']) ?></textarea>
                                    <?php else: ?>
                                        <input type="text" class="form-control" id="cg-<?= $item['chave'] ?>" name="conteudo_geral[<?= $item['chave'] ?>]" value="<?= htmlspecialchars($item['conteudo']) ?>">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php
                    $tipo_atual = '';
                    foreach ($conteudos as $item):
                        if ($item['tipo_conteudo'] !== $tipo_atual):
                            if ($tipo_atual !== '') echo '</div>';
                            $tipo_atual = $item['tipo_conteudo'];
                            ?>
                            <div class="mt-4">
                                <h3 class="text-secondary"><i class="fas <?= $tipo_atual === 'carousel' ? 'fa-images' : 'fa-th-large' ?> me-2"></i>Itens do <?= ucfirst($tipo_atual) ?></h3><hr>
                            </div>
                            <div class="accordion" id="accordion-<?= $tipo_atual ?>">
                        <?php endif; ?>
                        <div class="accordion-item mb-3 shadow-sm">
                            <h2 class="accordion-header" id="heading-<?= $item['id'] ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $item['id'] ?>">
                                    <strong>Item #<?= $item['ordem'] ?>:</strong>&nbsp;<?= htmlspecialchars($item['titulo']) ?>
                                </button>
                            </h2>
                            <div id="collapse-<?= $item['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#accordion-<?= $tipo_atual ?>">
                                <div class="accordion-body p-4">
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
                                            <img src="<?= BASE_URL . '/' . htmlspecialchars($item['imagem_url']) ?>" alt="Imagem atual" style="max-width: 150px; height: auto; margin-bottom: 10px; border-radius: 5px;">
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="imagem-<?= $item['id'] ?>" name="conteudo[<?= $item['id'] ?>][imagem]" accept="image/png, image/jpeg, image/jpg">
                                        <small class="form-text text-muted">Envie uma nova imagem para substituir a atual.</small>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="ativo-<?= $item['id'] ?>" name="conteudo[<?= $item['id'] ?>][ativo]" value="1" <?= $item['ativo'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="ativo-<?= $item['id'] ?>">Exibir este item na página inicial</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; if (!empty($conteudos)) echo '</div>'; ?>
                    <div class="mt-4"><button type="submit" class="btn btn-primary btn-lg">Salvar Alterações da Página Inicial</button></div>
                </form>
            </div>

            <!-- Conteúdo da Aba Página Sobre -->
            <div class="tab-pane fade" id="sobre-content" role="tabpanel">
                <!-- Este formulário envia APENAS o 'conteudo_geral' -->
                <form action="gerir_pagina_inicial.php" method="post">
                    <div class="card mt-3">
                        <div class="card-header"><h5>Textos da Página Sobre</h5></div>
                        <div class="card-body">
                            <?php foreach ($conteudo_geral['sobre'] ?? [] as $item): ?>
                                <div class="mb-3">
                                    <label for="cg-<?= $item['chave'] ?>" class="form-label"><strong><?= htmlspecialchars($item['titulo']) ?></strong></label>
                                    <?php if ($item['tipo'] === 'textarea'): ?>
                                        <textarea class="form-control" id="cg-<?= $item['chave'] ?>" name="conteudo_geral[<?= $item['chave'] ?>]" rows="4"><?= htmlspecialchars($item['conteudo']) ?></textarea>
                                    <?php else: ?>
                                        <input type="text" class="form-control" id="cg-<?= $item['chave'] ?>" name="conteudo_geral[<?= $item['chave'] ?>]" value="<?= htmlspecialchars($item['conteudo']) ?>">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mt-4"><button type="submit" class="btn btn-primary btn-lg">Salvar Alterações da Página Sobre</button></div>
                </form>
            </div>

            <!-- Conteúdo da Aba Termos de Uso -->
            <div class="tab-pane fade" id="termos-content" role="tabpanel">
                <form action="gerir_pagina_inicial.php" method="post">
                    <div class="card mt-3">
                        <div class="card-header"><h5>Editor dos Termos de Uso</h5></div>
                        <div class="card-body">
                            <p class="text-muted">Edite o conteúdo que aparece no pop-up de Termos de Uso. Você pode usar formatação como negrito, listas e links.</p>
                            <?php $item_termos = $conteudo_geral['termos_de_uso'][0] ?? ['chave' => 'termos_de_uso_conteudo', 'conteudo' => '']; ?>
                            <textarea id="editor-termos" name="conteudo_geral[<?= $item_termos['chave'] ?>]">
                                <?= htmlspecialchars($item_termos['conteudo']) ?>
                            </textarea>
                        </div>
                    </div>
                    <div class="mt-4"><button type="submit" class="btn btn-primary btn-lg">Salvar Termos de Uso</button></div>
                </form>
            </div>

            <!-- Conteúdo da Aba Serviços -->
            <div class="tab-pane fade" id="servicos-content" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header"><h5>Serviços Cadastrados no Sistema</h5></div>
                    <div class="card-body">
                        <div class="accordion" id="accordion-servicos">
                            <?php if (empty($servicos_por_prestador)): ?>
                                <div class="alert alert-info">Nenhum serviço cadastrado para exibir.</div>
                            <?php else: ?>
                                <?php foreach ($servicos_por_prestador as $nome_prestador => $servicos): ?>
                                    <div class="accordion-item mb-3 shadow-sm">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-prestador-<?= htmlspecialchars($servicos[0]['prestador_id']) ?>">
                                                <strong>Prestador:</strong>&nbsp;<?= htmlspecialchars($nome_prestador) ?> (<?= count($servicos) ?> serviço(s))
                                            </button>
                                        </h2>
                                        <div id="collapse-prestador-<?= htmlspecialchars($servicos[0]['prestador_id']) ?>" class="accordion-collapse collapse" data-bs-parent="#accordion-servicos">
                                            <div class="accordion-body p-4">
                                                <ul class="list-group">
                                                    <?php foreach ($servicos as $servico): ?>
                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <h6 class="mb-1"><?= htmlspecialchars($servico['titulo']) ?></h6>
                                                                <small class="text-muted"><?= htmlspecialchars($servico['descricao']) ?></small>
                                                            </div>
                                                            <div class="text-end">
                                                                <span class="fw-bold text-success me-3">R$ <?= number_format($servico['preco'], 2, ',', '.') ?></span>
                                                                <a href="editar_servico.php?id=<?= $servico['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<script>
  // Inicializa o editor de texto rico TinyMCE
  tinymce.init({
    selector: '#editor-termos',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
  });
</script>