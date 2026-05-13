<header class="site-header">
  <div class="site-header__inner">
    <a class="logo" href="index.php">
      <img src="../imagem/logo.png" alt="Logo ConstruTech">
      <span><?php echo $nomeLoja; ?></span>
    </a>

    <nav class="nav-principal" aria-label="Principal">
      <ul>
        <li><a href="index.php"><span class="nav-icon">🏠</span> Início</a></li>
        <li><a href="showroom.php"><span class="nav-icon">🏪</span> Showroom</a></li>
        <li><a href="estoque.php"><span class="nav-icon">📦</span> Estoque</a></li>
        <li><a href="cadastro-produto.php"><span class="nav-icon">➕</span> Cadastrar Produto</a></li>
      </ul>
    </nav>

    <?php if (isset($_SESSION['logado']) && $_SESSION['logado'] === true): ?>
    <a href="logout.php" class="btn-logout">Sair do Sistema</a>
    <?php else: ?>
    <a href="login.php" class="btn-logout btn-entrar">Entrar</a>
    <?php endif; ?>
  </div>
</header>
