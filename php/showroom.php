<?php
require_once 'init.php';

$catFiltro = $_GET['categoria'] ?? '';

// Separa produtos disponíveis (qty > 0) e esgotados
$disponiveis = array_filter($_SESSION['produtos'], fn($p) => (int)$p['quantidade'] > 0);
$esgotados   = array_filter($_SESSION['produtos'], fn($p) => (int)$p['quantidade'] === 0);

// Aplica filtro de categoria se selecionado
if ($catFiltro !== '') {
    $disponiveis = array_filter($disponiveis, fn($p) => $p['categoria'] === $catFiltro);
    $esgotados   = array_filter($esgotados,   fn($p) => $p['categoria'] === $catFiltro);
}

$totalProdutos  = count($_SESSION['produtos']);
$totalDisp      = count(array_filter($_SESSION['produtos'], fn($p) => (int)$p['quantidade'] > 0));

// Define categorias baseadas nos produtos existentes
$categoriasUnicas = array_unique(array_column($_SESSION['produtos'], 'categoria'));
$categorias = array_combine($categoriasUnicas, $categoriasUnicas);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Produtos — <?php echo $nomeLoja; ?></title>
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
  <?php require_once '../partials/header.php'; ?>

  <main class="site-main">

    <!-- Banner do Showroom -->
    <section class="showroom-hero">
      <div class="showroom-hero__texto">
        <span class="showroom-tag">🏪 Catálogo de Produtos</span>
        <h1>Nosso Estoque</h1>
        <p>Confira todos os materiais disponíveis na <strong><?php echo $nomeLoja; ?></strong> — preços, quantidades e categorias atualizados em tempo real.</p>
      </div>
      <div class="showroom-hero__stats">
        <div class="showroom-stat">
          <span class="showroom-stat__num"><?php echo $totalProdutos; ?></span>
          <span class="showroom-stat__label">Produtos cadastrados</span>
        </div>
        <div class="showroom-stat">
          <span class="showroom-stat__num showroom-stat__num--green"><?php echo $totalDisp; ?></span>
          <span class="showroom-stat__label">Disponíveis agora</span>
        </div>
      </div>
    </section>

    <!-- Filtros -->
    <nav class="filtros-categoria" aria-label="Filtrar por categoria">
      <a href="showroom.php" class="<?php echo $catFiltro === '' ? 'ativo' : ''; ?>">🔍 Todos</a>
      <?php foreach ($categorias as $key => $nome): ?>
      <a href="showroom.php?categoria=<?php echo $key; ?>"
         class="<?php echo $catFiltro === $key ? 'ativo' : ''; ?> filtro--<?php echo $key; ?>">
        <?php echo $nome; ?>
      </a>
      <?php endforeach; ?>
    </nav>

    <!-- Produtos Disponíveis -->
    <?php if (!empty($disponiveis)): ?>
    <section>
      <h2 class="secao-titulo">✅ Disponíveis (<?php echo count($disponiveis); ?>)</h2>
      <div class="showroom-grid">
        <?php foreach ($disponiveis as $p):
          $qty   = (int)$p['quantidade'];
          $preco = (float)$p['preco'];
          $isLow = $qty <= ESTOQUE_MINIMO;
        ?>
        <article class="showroom-card">

          <!-- Badges no topo da imagem -->
          <div class="showroom-card__img-wrap">
            <img
              src="<?php echo htmlspecialchars($p['imagem']); ?>"
              alt="<?php echo htmlspecialchars($p['nome']); ?>"
              class="showroom-card__img"
              onerror="this.src='../imagem/Cimento.jpg'"
            >
            <span class="showroom-card__cat badge badge--<?php echo $p['categoria']; ?>">
              <?php echo $categorias[$p['categoria']] ?? $p['categoria']; ?>
            </span>
            <?php if ($isLow): ?>
            <span class="showroom-card__badge showroom-card__badge--low">⚠️ Últimas unidades</span>
            <?php endif; ?>
          </div>

          <!-- Corpo do card -->
          <div class="showroom-card__body">
            <h3 class="showroom-card__nome"><?php echo htmlspecialchars($p['nome']); ?></h3>

            <?php if (!empty($p['descricao'])): ?>
            <p class="showroom-card__desc"><?php echo htmlspecialchars($p['descricao']); ?></p>
            <?php endif; ?>

            <!-- Infos -->
            <div class="showroom-card__infos">
              <div class="showroom-info">
                <span class="showroom-info__label">💰 Preço unitário</span>
                <span class="showroom-info__valor showroom-info__valor--preco">
                  R$ <?php echo number_format($preco, 2, ',', '.'); ?>
                </span>
              </div>
              <div class="showroom-info">
                <span class="showroom-info__label">📦 Em estoque</span>
                <span class="showroom-info__valor <?php echo $isLow ? 'showroom-info__valor--low' : 'showroom-info__valor--ok'; ?>">
                  <?php echo $qty; ?> unidade<?php echo $qty > 1 ? 's' : ''; ?>
                </span>
              </div>
            </div>

            <!-- Barra visual de estoque -->
            <?php
              $maxRef  = 50; // referência visual para a barra
              $percent = min(100, round(($qty / $maxRef) * 100));
              $barColor = $isLow ? 'bar--low' : 'bar--ok';
            ?>
            <div class="estoque-bar">
              <div class="estoque-bar__track">
                <div class="estoque-bar__fill <?php echo $barColor; ?>" style="width: <?php echo $percent; ?>%"></div>
              </div>
              <span class="estoque-bar__label"><?php echo $isLow ? 'Estoque baixo' : 'Estoque normal'; ?></span>
            </div>

          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php else: ?>
    <div class="showroom-vazio">
      <span>📭</span>
      <p>Nenhum produto disponível nesta categoria no momento.</p>
    </div>
    <?php endif; ?>

    <!-- Produtos Esgotados -->
    <?php if (!empty($esgotados)): ?>
    <section style="margin-top: 2.5rem;">
      <h2 class="secao-titulo" style="color: #94a3b8;">🚫 Esgotados (<?php echo count($esgotados); ?>)</h2>
      <div class="showroom-grid showroom-grid--esgotado">
        <?php foreach ($esgotados as $p): ?>
        <article class="showroom-card showroom-card--esgotado">
          <div class="showroom-card__img-wrap">
            <img
              src="<?php echo htmlspecialchars($p['imagem']); ?>"
              alt="<?php echo htmlspecialchars($p['nome']); ?>"
              class="showroom-card__img"
              onerror="this.src='../imagem/Cimento.jpg'"
            >
            <span class="showroom-card__badge showroom-card__badge--esgotado">🚫 Esgotado</span>
            <span class="showroom-card__cat badge badge--<?php echo $p['categoria']; ?>">
              <?php echo $categorias[$p['categoria']] ?? $p['categoria']; ?>
            </span>
          </div>
          <div class="showroom-card__body">
            <h3 class="showroom-card__nome"><?php echo htmlspecialchars($p['nome']); ?></h3>
            <?php if (!empty($p['descricao'])): ?>
            <p class="showroom-card__desc"><?php echo htmlspecialchars($p['descricao']); ?></p>
            <?php endif; ?>
            <div class="showroom-card__infos">
              <div class="showroom-info">
                <span class="showroom-info__label">💰 Preço unitário</span>
                <span class="showroom-info__valor">R$ <?php echo number_format((float)$p['preco'], 2, ',', '.'); ?></span>
              </div>
              <div class="showroom-info">
                <span class="showroom-info__label">📦 Em estoque</span>
                <span class="showroom-info__valor showroom-info__valor--esgotado">Sem estoque</span>
              </div>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

  </main>

  <?php require_once '../partials/footer.php'; ?>
</body>
</html>
