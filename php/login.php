<?php
session_start();

if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: estoque.php");
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = $_POST['senha'] ?? '';

    if ($usuario === 'ADM' && $senha === '1234') {
        $_SESSION['logado'] = true;
        header("Location: estoque.php");
        exit;
    } else {
        $erro = 'Usuário ou senha incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — ConstruTech</title>
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="login-body">
  <main class="login-container">
    <img src="../imagem/logo.png" alt="Logo ConstruTech" class="login-logo">
    <h1 class="login-titulo">ConstruTech</h1>
    <p class="login-subtitulo">Gestão de Estoque &amp; Vendas</p>

    <?php if ($erro): ?>
      <div class="msg-erro">⚠️ <?php echo htmlspecialchars($erro); ?><br><small>Use <strong>ADM</strong> / <strong>1234</strong></small></div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="login-form">
      <div class="form-group">
        <label for="usuario">Usuário</label>
        <input type="text" id="usuario" name="usuario" placeholder="Escreva seu Usuario" required autocomplete="username">
      </div>
      <div class="form-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" placeholder="••••" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn-login">Entrar no Sistema</button>
    </form>
  </main>
</body>
</html>
