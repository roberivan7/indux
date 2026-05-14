<?php require_once 'init.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Início — <?php echo $nomeLoja; ?></title>
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
  <?php require_once '../partials/header.php'; ?>

  <main class="site-main">
    <section class="hero" aria-labelledby="titulo-hero">
      <h1 id="titulo-hero">Controle de Estoque</h1>
      <p>Todas as informações necessárias para controlar seu lucro, vendas e estoque</p>
      <?php if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true): ?>
        <a href="login.php" class="btn btn--hero">Acessar o Sistema</a>
      <?php else: ?>
        <a href="estoque.php" class="btn btn--hero">Ver Estoque</a>
      <?php endif; ?>
    </section>

    <section aria-labelledby="titulo-destaques">
      <h2 id="titulo-destaques" class="secao-titulo">Produtos em Destaque</h2>
      <div class="cards-grid">
        <?php foreach (array_slice($_SESSION['produtos'], 0, 3) as $p): ?>
        <article class="card">
          <img class="card__img" src="<?php echo htmlspecialchars($p['imagem']); ?>" alt="<?php echo htmlspecialchars($p['nome']); ?>">
          <div class="card__body">
            <span class="badge badge--<?php echo $p['categoria']; ?>"><?php echo $categorias[$p['categoria']] ?? $p['categoria']; ?></span>
            <h3><?php echo htmlspecialchars($p['nome']); ?></h3>
            <p><?php echo htmlspecialchars($p['descricao']); ?></p>
            <p class="card__preco">R$ <?php echo number_format((float)$p['preco'], 2, ',', '.'); ?></p>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>
  </main>
  <?php require_once '../partials/footer.php'; ?>
</body>
</html>
