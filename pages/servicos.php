<?php
include '../config/config.php';
require_once '../config/db.php';
include '../includes/header.php';
include '../includes/navbar.php';

$servicos = [];
$termo_busca = $_GET['q'] ?? '';
$mensagem_erro = '';

try {
    $pdo = obterConexaoPDO();
    
    $sql = "SELECT s.titulo, s.descricao, s.preco FROM Servico s WHERE s.oculto = 0";
    $params = [];

    if (!empty($termo_busca)) {
        $sql .= " AND (s.titulo LIKE ? OR s.descricao LIKE ?)";
        $like_term = "%" . $termo_busca . "%";
        $params = [$like_term, $like_term];
    }

    $sql .= " ORDER BY 
                CASE 
                    WHEN s.titulo LIKE '%básico%' OR s.descricao LIKE '%básico%' THEN 1
                    WHEN s.titulo LIKE '%intermediário%' OR s.descricao LIKE '%intermediário%' THEN 2
                    WHEN s.titulo LIKE '%brilhante%' OR s.descricao LIKE '%brilhante%' THEN 3
                    ELSE 4 
                END, s.preco ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $mensagem_erro = "Erro ao carregar os serviços. Tente novamente mais tarde.";
    error_log("Erro na página de serviços: " . $e->getMessage());
}

?>

<header class="hero-section"
    style="background: url(../img/cleaning.jpeg) center/cover no-repeat; height: 400px; display: flex; align-items: center; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">
    <div class="container">
        <h1 class="display-3 fw-bold text-center color-white">Conheça alguns dos nossos serviços</h1>
    </div>
</header>
<main>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">Serviços Disponíveis</h1> 
        <hr class="my-4">


        <?php if (!empty($mensagem_erro)): ?>
            <div class="alert alert-danger"><?= $mensagem_erro ?></div>
        <?php endif; ?>

        <div class="mb-4">
            <form action="servicos.php" method="GET" class="d-flex">
                <input class="form-control me-2" type="search" name="q" placeholder="Buscar por serviço..." value="<?= htmlspecialchars($termo_busca) ?>">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($termo_busca)): ?>
                    <a href="servicos.php" class="btn btn-outline-secondary ms-2">Limpar</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($termo_busca) && empty($servicos)): ?>
            <div class="alert alert-info text-center" role="alert">Nenhum serviço encontrado para "<?= htmlspecialchars($termo_busca) ?>". Tente uma busca diferente ou limpe o filtro.</div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th scope="col" style="width: 80%;">Serviço</th>
                        <th scope="col" style="width: 20%;" class="text-end">Preço a partir de</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($servicos)): ?>
                        <tr>
                            <td colspan="2" class="text-center">Nenhum serviço disponível no momento.</td> 
                        </tr>
                    <?php else: ?>
                        <?php foreach ($servicos as $servico): ?>
                            <tr>
                                <td>
                                    <h6 class="mb-1"><?= htmlspecialchars($servico['titulo']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($servico['descricao']) ?></small>
                                </td>
                                <td class="text-end text-primary fw-bold">
                                    R$ <?= number_format($servico['preco'], 2, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>