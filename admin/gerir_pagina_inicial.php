<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/log_helper.php';

$tipo_admin = $_SESSION['admin_tipo'] ?? '';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin' || !in_array($tipo_admin, ['adminmaster', 'admmoderador'])) {
    header("Location: ../pages/login.php");
    exit();
}

$id_admin_logado = $_SESSION['usuario_id'];
$mensagem_sucesso = '';
$mensagem_erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_bloco'])) {
    $pdo_blocos = obterConexaoPDO();

    if ($_POST['acao_bloco'] === 'salvar') {
        $bloco_id = $_POST['bloco_id'] ?? null;
        $titulo_admin = trim($_POST['bloco_titulo_admin']);
        $tipo_bloco = $_POST['bloco_tipo'];
        $ativo = isset($_POST['bloco_ativo']) ? 1 : 0;
        $ordem = (int)($_POST['bloco_ordem'] ?? 0);

        $conteudo_array = [];

        if ($tipo_bloco === 'texto_simples') {
            $conteudo_array['texto'] = $_POST['bloco_texto_simples'] ?? '';
        } elseif ($tipo_bloco === 'card_imagem_texto') {
            $conteudo_array['titulo'] = $_POST['bloco_card_titulo'] ?? '';
            $conteudo_array['texto'] = $_POST['bloco_card_texto'] ?? '';
            
            $imagem_url = $_POST['bloco_imagem_atual'] ?? '';
            if (isset($_FILES['bloco_card_imagem']) && $_FILES['bloco_card_imagem']['error'] == 0) {
                $upload_dir = '../img/blocos/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $nome_arquivo = uniqid() . '-' . basename($_FILES['bloco_card_imagem']['name']);
                $caminho_arquivo = $upload_dir . $nome_arquivo;
                if (move_uploaded_file($_FILES['bloco_card_imagem']['tmp_name'], $caminho_arquivo)) {
                    $imagem_url = 'img/blocos/' . $nome_arquivo;
                } else {
                    $mensagem_erro = "Falha ao mover o arquivo de imagem do bloco.";
                }
            }
            $conteudo_array['imagem_url'] = $imagem_url;
        }

        $conteudo_json = json_encode($conteudo_array);

        try {
            if ($bloco_id) {
                $stmt = $pdo_blocos->prepare("UPDATE blocos_conteudo SET titulo_admin = ?, tipo_bloco = ?, conteudo_json = ?, ordem = ?, ativo = ?, editado_por_admin_id = ? WHERE id = ?");
                $stmt->execute([$titulo_admin, $tipo_bloco, $conteudo_json, $ordem, $ativo, $id_admin_logado, $bloco_id]);
                $mensagem_sucesso = "Bloco de conteúdo atualizado com sucesso!";
            } else {
                $stmt = $pdo_blocos->prepare("INSERT INTO blocos_conteudo (pagina, titulo_admin, tipo_bloco, conteudo_json, ordem, ativo, editado_por_admin_id) VALUES ('index', ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$titulo_admin, $tipo_bloco, $conteudo_json, $ordem, $ativo, $id_admin_logado]);
                $mensagem_sucesso = "Novo bloco de conteúdo adicionado com sucesso!";
            }
        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao salvar o bloco de conteúdo: " . $e->getMessage();
            error_log("Erro Bloco Conteúdo: " . $e->getMessage());
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['conteudo_geral'])) {
        foreach ($_POST['conteudo_geral'] as $chave => $valor) {
            $conteudo = trim($valor['conteudo']);
            $oculto = isset($valor['oculto']) ? 1 : 0;
            $stmt = obterConexaoPDO()->prepare("UPDATE conteudo_geral SET conteudo = ?, oculto = ?, editado_por_admin_id = ? WHERE chave = ?");
            $stmt->execute([$conteudo, $oculto, $id_admin_logado, $chave]);
        }
        $mensagem_sucesso = "Conteúdo de texto atualizado com sucesso!";
    }

    if (isset($_POST['conteudo'])) {
        try {
            $pdo = obterConexaoPDO();
            $pdo->beginTransaction();

            foreach ($_POST['conteudo'] as $id => $dados) {
                $titulo = $dados['titulo'];
                $texto = $dados['texto'];
                $ativo = isset($dados['ativo']) ? 1 : 0;
                $oculto = isset($dados['oculto']) ? 1 : 0; // Novo campo: oculto

                // Lógica de upload de imagem
                $imagem_url = $dados['imagem_atual'];
                if (isset($_FILES['conteudo']['name'][$id]['imagem']) && $_FILES['conteudo']['error'][$id]['imagem'] == 0) {
                    $upload_dir = '../img/';
                    $nome_arquivo = basename($_FILES['conteudo']['name'][$id]['imagem']);
                    $caminho_arquivo = $upload_dir . $nome_arquivo;
                    
                    if (move_uploaded_file($_FILES['conteudo']['tmp_name'][$id]['imagem'], $caminho_arquivo)) {
                        $imagem_url = 'img/' . $nome_arquivo;
                    } else {
                        throw new Exception("Falha ao mover o arquivo de imagem.");
                    }
                }

                $stmt = $pdo->prepare(
                    "UPDATE conteudo_pagina_inicial SET titulo = ?, texto = ?, imagem_url = ?, ativo = ?, oculto = ?, editado_por_admin_id = ? WHERE id = ?"
                );
                $stmt->execute([$titulo, $texto, $imagem_url, $ativo, $oculto, $id_admin_logado, $id]);
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
    }

    registrar_log_admin($id_admin_logado, "Editou o conteúdo das páginas (inicial/sobre).");
}

$conteudos = [];
try {
    $pdo = obterConexaoPDO();
    $stmt = $pdo->query("SELECT * FROM conteudo_pagina_inicial ORDER BY tipo_conteudo, ordem");
    $conteudos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao carregar o conteúdo dinâmico da página.";
}

$conteudo_geral = [];
try {
    if (!isset($pdo)) {
        $pdo = obterConexaoPDO();
    }
    
    $stmt_geral = $pdo->query("SELECT * FROM conteudo_geral ORDER BY pagina, id");
    foreach ($stmt_geral->fetchAll(PDO::FETCH_ASSOC) as $item) {
        $conteudo_geral[$item['pagina']][] = $item;
    }
} catch (PDOException $e) {
    $mensagem_erro .= " Erro ao carregar os textos da página.";
}

$blocos_conteudo = [];
try {
    if (!isset($pdo)) $pdo = obterConexaoPDO();
    $stmt_blocos = $pdo->query("SELECT * FROM blocos_conteudo WHERE pagina = 'index' ORDER BY ordem ASC");
    $blocos_conteudo = $stmt_blocos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    if (empty($mensagem_erro)) {
        $mensagem_erro = "Erro ao carregar os blocos de conteúdo.";
    }
}

$servicos_por_prestador = [];
try {
    $stmt_servicos = $pdo->query(
        "SELECT s.id, s.titulo, s.descricao, s.preco, s.oculto, p.nome AS nome_prestador, p.id AS prestador_id
         FROM Servico s
         JOIN Prestador p ON s.prestador_id = p.id
         ORDER BY p.nome, s.titulo, s.oculto"
    );
    $todos_servicos = $stmt_servicos->fetchAll(PDO::FETCH_ASSOC);

    foreach ($todos_servicos as $servico) {
        $servicos_por_prestador[$servico['nome_prestador']][] = $servico;
    }

} catch (PDOException $e) {
    if (empty($mensagem_erro)) {
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
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="blocos-tab" data-bs-toggle="tab" data-bs-target="#blocos-content" type="button" role="tab">Gerenciar Conteúdo</button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="index-content" role="tabpanel">
                <form action="gerir_pagina_inicial.php" method="post" enctype="multipart/form-data">
                    <div class="card mt-3">
                        <div class="card-header"><h5>Textos da Página Inicial</h5></div>
                        <div class="card-body">
                            <?php foreach ($conteudo_geral['index'] ?? [] as $item): ?>
                                <div class="mb-3">
                                    <label for="cg-<?= $item['chave'] ?>" class="form-label"><strong><?= htmlspecialchars($item['titulo']) ?></strong></label>
                                    <?php if ($item['tipo'] === 'textarea'): ?>
                                        <textarea class="form-control" id="cg-<?= $item['chave'] ?>" name="conteudo_geral[<?= $item['chave'] ?>][conteudo]" rows="3" placeholder="Digite o texto para esta seção..."><?= htmlspecialchars($item['conteudo']) ?></textarea>
                                    <?php else: ?>
                                        <input type="text" class="form-control" id="cg-<?= $item['chave'] ?>" name="conteudo_geral[<?= $item['chave'] ?>][conteudo]" value="<?= htmlspecialchars($item['conteudo']) ?>" placeholder="Digite o título para esta seção...">
                                    <?php endif; ?>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="oculto-cg-<?= $item['chave'] ?>" name="conteudo_geral[<?= $item['chave'] ?>][oculto]" value="1" <?= $item['oculto'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="oculto-cg-<?= $item['chave'] ?>">Ocultar este item do site</label>
                                    </div>
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
                                        <input type="text" class="form-control" id="titulo-<?= $item['id'] ?>" name="conteudo[<?= $item['id'] ?>][titulo]" value="<?= htmlspecialchars($item['titulo']) ?>" placeholder="Ex: Bem-vindos à StarClean" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="texto-<?= $item['id'] ?>" class="form-label">Texto</label>
                                        <textarea class="form-control" id="texto-<?= $item['id'] ?>" name="conteudo[<?= $item['id'] ?>][texto]" rows="3" placeholder="Digite uma breve descrição para este item..."><?= htmlspecialchars($item['texto']) ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="imagem-<?= $item['id'] ?>" class="form-label">Imagem</label><br>
                                        <?php if ($item['imagem_url']): ?>
                                            <img src="<?= BASE_URL . '/' . htmlspecialchars($item['imagem_url']) ?>" alt="Imagem atual" style="max-width: 150px; height: auto; margin-bottom: 10px; border-radius: 5px;">
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="imagem-<?= $item['id'] ?>" name="conteudo[<?= $item['id'] ?>][imagem]" accept="image/png, image/jpeg, image/jpg">
                                        <small class="form-text text-muted">Envie uma nova imagem para substituir a atual.</small>
                                    </div>
                                    <!-- <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="ativo-<?= $item['id'] ?>" name="conteudo[<?= $item['id'] ?>][ativo]" value="1" <?= $item['ativo'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="ativo-<?= $item['id'] ?>">Exibir este item na página inicial</label>
                                    </div> -->
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="oculto-<?= $item['id'] ?>" name="conteudo[<?= $item['id'] ?>][oculto]" value="1" <?= $item['oculto'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="oculto-<?= $item['id'] ?>">Ocultar este item na página inicial</label>
                                        </div>
                                        <a href="excluir_conteudo_pagina.php?id=<?= $item['id'] ?>&tabela=conteudo_pagina_inicial" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir permanentemente este item? Esta ação não pode ser desfeita.');">Excluir</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; if (!empty($conteudos)) echo '</div>'; ?>
                    <div class="mt-4"><button type="submit" class="btn btn-primary btn-lg">Salvar Alterações da Página Inicial</button></div>
                </form>
            </div>

            <div class="tab-pane fade" id="sobre-content" role="tabpanel">
                <form action="gerir_pagina_inicial.php" method="post">
                    <div class="card mt-3">
                        <div class="card-header"><h5>Textos da Página Sobre</h5></div>
                        <div class="card-body">
                            <?php foreach ($conteudo_geral['sobre'] ?? [] as $item): ?>
                                <div class="mb-3">
                                    <label for="cg-<?= $item['chave'] ?>" class="form-label"><strong><?= htmlspecialchars($item['titulo']) ?></strong></label>
                                    <?php if ($item['tipo'] === 'textarea'): ?>
                                        <textarea class="form-control" id="cg-<?= $item['chave'] ?>" name="conteudo_geral[<?= $item['chave'] ?>][conteudo]" rows="4" placeholder="Digite o texto para esta seção..."><?= htmlspecialchars($item['conteudo']) ?></textarea>
                                    <?php else: ?>
                                        <input type="text" class="form-control" id="cg-<?= $item['chave'] ?>" name="conteudo_geral[<?= $item['chave'] ?>][conteudo]" value="<?= htmlspecialchars($item['conteudo']) ?>" placeholder="Digite o título ou informação para esta seção...">
                                    <?php endif; ?>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="oculto-cg-<?= $item['chave'] ?>" name="conteudo_geral[<?= $item['chave'] ?>][oculto]" value="1" <?= $item['oculto'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="oculto-cg-<?= $item['chave'] ?>">Ocultar este item do site</label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mt-4"><button type="submit" class="btn btn-primary btn-lg">Salvar Alterações da Página Sobre</button></div>
                </form>
            </div>

            <div class="tab-pane fade" id="termos-content" role="tabpanel">
                <form action="gerir_pagina_inicial.php" method="post">
                    <div class="card mt-3">
                        <div class="card-header"><h5>Editor dos Termos de Uso</h5></div>
                        <div class="card-body">
                            <p class="text-muted">Edite o conteúdo que aparece no pop-up de Termos de Uso. Você pode usar formatação como negrito, listas e links.</p>
                            <?php $item_termos = $conteudo_geral['termos_de_uso'][0] ?? ['chave' => 'termos_de_uso_conteudo', 'conteudo' => '']; ?>
                            <textarea id="editor-termos" name="conteudo_geral[<?= $item_termos['chave'] ?>][conteudo]">
                                <?= htmlspecialchars($item_termos['conteudo']) ?>
                            </textarea>
                        </div>
                    </div>
                    <div class="mt-4"><button type="submit" class="btn btn-primary btn-lg">Salvar Termos de Uso</button></div>
                </form>
            </div>

            <div class="tab-pane fade" id="servicos-content" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Serviços Cadastrados no Sistema</h5>
                        <div class="d-flex" style="width: 300px;">
                            <input type="text" id="filtroServicos" class="form-control form-control-sm" placeholder="Filtrar por serviço ou prestador...">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="accordion-servicos">
                            <?php if (empty($servicos_por_prestador)): ?>
                                <div class="alert alert-info">Nenhum serviço cadastrado para exibir.</div>
                            <?php else: ?>
                                <?php foreach ($servicos_por_prestador as $nome_prestador => $servicos): ?>
                                    <!-- Adicionado data-filter-text para o JS -->
                                    <div class="accordion-item mb-3 shadow-sm" data-filter-text="<?= strtolower(htmlspecialchars($nome_prestador) . ' ' . implode(' ', array_column($servicos, 'titulo')) . ' ' . implode(' ', array_column($servicos, 'descricao'))) ?>">
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
                                                                <?php if ($servico['oculto']): ?>
                                                                    <span class="badge bg-secondary me-2">Oculto</span>
                                                                    <a href="toggle_servico_visibilidade.php?id=<?= $servico['id'] ?>&ocultar=0" class="btn btn-sm btn-success" title="Tornar visível no site">Exibir</a>
                                                                <?php else: ?>
                                                                    <a href="toggle_servico_visibilidade.php?id=<?= $servico['id'] ?>&ocultar=1" class="btn btn-sm btn-secondary" title="Ocultar do site">Ocultar</a>
                                                                <?php endif; ?>
                                                                <a href="editar_servico.php?id=<?= $servico['id'] ?>" class="btn btn-sm btn-warning" title="Editar detalhes do serviço">Editar</a>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>    
                                <div id="nenhumServicoEncontrado" class="alert alert-warning text-center" style="display: none;">Nenhum serviço encontrado para o filtro informado.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="blocos-content" role="tabpanel">
                <div class="card mt-4 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Gerenciador de Conteúdo da Página Inicial</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBloco">
                            <i class="fas fa-plus me-2"></i>Adicionar Novo Conteúdo
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Adicione, edite e reordene os blocos de conteúdo que aparecem na sua página inicial. Arraste e solte para reordenar.</p>
                        
                        <?php if (empty($blocos_conteudo)): ?>
                            <div class="alert alert-light text-center">Nenhum bloco de conteúdo personalizado foi adicionado ainda.</div>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($blocos_conteudo as $bloco): ?>
                                    <?php $dados_bloco = json_decode($bloco['conteudo_json'], true); ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-secondary me-2">Ordem: <?= $bloco['ordem'] ?></span>
                                            <strong><?= htmlspecialchars($bloco['titulo_admin']) ?></strong>
                                            <small class="text-muted ms-2">(Tipo: <?= htmlspecialchars($bloco['tipo_bloco']) ?>)</small>
                                            <?= $bloco['ativo'] ? '<span class="badge bg-success ms-2">Visível</span>' : '<span class="badge bg-danger ms-2">Oculto</span>' ?>
                                        </div>
                                        <div>                                            
                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalBloco"
                                                data-bloco-id="<?= $bloco['id'] ?>"
                                                data-bloco-titulo-admin="<?= htmlspecialchars($bloco['titulo_admin']) ?>"
                                                data-bloco-tipo="<?= $bloco['tipo_bloco'] ?>"
                                                data-bloco-ordem="<?= $bloco['ordem'] ?>"
                                                data-bloco-ativo="<?= $bloco['ativo'] ?>"
                                                data-bloco-conteudo='<?= htmlspecialchars($bloco['conteudo_json'], ENT_QUOTES, 'UTF-8') ?>'>
                                                Editar
                                            </button>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="modalBloco" tabindex="-1" aria-labelledby="modalBlocoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="gerir_pagina_inicial.php" method="post" enctype="multipart/form-data" id="formBloco">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalBlocoLabel">Adicionar Novo Conteúdo Personalizado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="acao_bloco" value="salvar">
                    <input type="hidden" name="bloco_id" id="bloco_id">

                    <div class="mb-3">
                        <label for="bloco_titulo_admin" class="form-label">Título de Identificação (visível somente para administradores)</label>
                        <input type="text" class="form-control" id="bloco_titulo_admin" name="bloco_titulo_admin" placeholder="Ex: Seção de Boas-Vindas" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="bloco_tipo" class="form-label">Tipo de Bloco</label>
                            <select class="form-select" id="bloco_tipo" name="bloco_tipo">
                                <option value="texto_simples">Texto Simples</option>
                                <option value="card_imagem_texto">Card (Imagem + Texto)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="bloco_ordem" class="form-label">Ordem de Exibição</label>
                            <input type="number" class="form-control" id="bloco_ordem" name="bloco_ordem" value="0" placeholder="Escolha a ordem de exibição do bloco" required>
                        </div>
                    </div>

                    <hr>
                    <div id="campos_bloco_container">
                        <div class="campos-especificos" id="campos_texto_simples" style="display: none;">
                            <div class="mb-3">
                                <label for="bloco_texto_simples" class="form-label">Conteúdo do Texto</label>
                                <textarea class="form-control" name="bloco_texto_simples" id="bloco_texto_simples" rows="8" placeholder="Digite ou cole o seu texto aqui..."></textarea>
                            </div>
                        </div>

                        <div class="campos-especificos" id="campos_card_imagem_texto" style="display: none;">
                            <div class="mb-3">
                                <label for="bloco_card_titulo" class="form-label">Título do Card</label>
                                <input type="text" class="form-control" name="bloco_card_titulo" id="bloco_card_titulo" placeholder="Ex: Nossa Equipe Especializada">
                            </div>
                            <div class="mb-3">
                                <label for="bloco_card_texto" class="form-label">Texto do Card</label>
                                <textarea class="form-control" name="bloco_card_texto" id="bloco_card_texto" rows="4" placeholder="Descreva sobre o que se trata este conteúdo..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="bloco_card_imagem" class="form-label">Imagem do Card</label>
                                <input type="file" class="form-control" name="bloco_card_imagem" id="bloco_card_imagem" accept="image/*">
                                <input type="hidden" name="bloco_imagem_atual" id="bloco_imagem_atual">
                                <div id="imagem_preview_container" class="mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="bloco_ativo" name="bloco_ativo" value="1" checked>
                        <label class="form-check-label" for="bloco_ativo">Deixar este bloco visível no site</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Bloco</button>
                </div>
            </form>
        </div>
    </div>
</div>
</main>

<?php include '../includes/footer.php'; ?>

<script>
  tinymce.init({
    selector: '#editor-termos',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
  });

  const modalBloco = document.getElementById('modalBloco');
  const selectTipoBloco = document.getElementById('bloco_tipo');
  const camposContainer = document.getElementById('campos_bloco_container');

  function toggleCamposBloco() {
      camposContainer.querySelectorAll('.campos-especificos').forEach(div => {
          div.style.display = 'none';
      });
      const camposParaMostrar = document.getElementById('campos_' + selectTipoBloco.value);
      if (camposParaMostrar) {
          camposParaMostrar.style.display = 'block';
      }
  }

  selectTipoBloco.addEventListener('change', toggleCamposBloco);

  modalBloco.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const form = document.getElementById('formBloco');
      const modalTitle = modalBloco.querySelector('.modal-title');
      const imagemPreviewContainer = document.getElementById('imagem_preview_container');

      form.reset();
      imagemPreviewContainer.innerHTML = '';
      document.getElementById('bloco_id').value = '';

      const blocoId = button.getAttribute('data-bloco-id');
      
      if (blocoId) {
          modalTitle.textContent = 'Editar Bloco de Conteúdo';
          
          document.getElementById('bloco_id').value = blocoId;
          document.getElementById('bloco_titulo_admin').value = button.getAttribute('data-bloco-titulo-admin');
          document.getElementById('bloco_tipo').value = button.getAttribute('data-bloco-tipo');
          document.getElementById('bloco_ordem').value = button.getAttribute('data-bloco-ordem');
          document.getElementById('bloco_ativo').checked = button.getAttribute('data-bloco-ativo') == '1';

          const conteudo = JSON.parse(button.getAttribute('data-bloco-conteudo'));
          const tipo = button.getAttribute('data-bloco-tipo');

          if (tipo === 'texto_simples') {
              document.getElementById('bloco_texto_simples').value = conteudo.texto || '';
          } else if (tipo === 'card_imagem_texto') {
              document.getElementById('bloco_card_titulo').value = conteudo.titulo || '';
              document.getElementById('bloco_card_texto').value = conteudo.texto || '';
              document.getElementById('bloco_imagem_atual').value = conteudo.imagem_url || '';
              if (conteudo.imagem_url) {
                  imagemPreviewContainer.innerHTML = `<img src="../${conteudo.imagem_url}" class="img-thumbnail" style="max-width: 150px;" alt="Imagem atual">`;
              }
          }

      } else {
          modalTitle.textContent = 'Adicionar Novo Bloco';
          document.getElementById('bloco_ativo').checked = true;
      }

      toggleCamposBloco();
  });

  modalBloco.addEventListener('hidden.bs.modal', function () {
      const editor = tinymce.get('bloco_texto_simples');
      if (editor) {
          editor.remove();
      }
  });

  modalBloco.addEventListener('shown.bs.modal', function() {
      tinymce.init({
        selector: '#bloco_texto_simples',
        plugins: 'autolink lists link wordcount',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link',
        height: 300,
        menubar: false
      });
  });

  // Filtro dinâmico para a aba de serviços
  const filtroServicosInput = document.getElementById('filtroServicos');
  if (filtroServicosInput) {
      filtroServicosInput.addEventListener('keyup', function() {
          const termoBusca = this.value.toLowerCase();
          const itensAccordion = document.querySelectorAll('#accordion-servicos .accordion-item');
          const mensagemNenhumResultado = document.getElementById('nenhumServicoEncontrado');
          let resultadosEncontrados = 0;

          itensAccordion.forEach(function(item) {
              const textoItem = item.getAttribute('data-filter-text');
              if (textoItem.includes(termoBusca)) {
                  item.style.display = '';
                  resultadosEncontrados++;
              } else {
                  item.style.display = 'none';
              }
          });
          mensagemNenhumResultado.style.display = resultadosEncontrados > 0 ? 'none' : 'block';
      });
  }
</script>