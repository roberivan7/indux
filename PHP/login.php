<?php
require_once 'init.php';
require_once 'db.php';

if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha todos os campos.';
    } else {
        try {
            $db   = getDB();
            $consultaUsuario = $db->prepare('SELECT * FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1');
            $consultaUsuario->execute([$email]);
            $usuario = $consultaUsuario->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {

                $db->prepare('UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?')
                   ->execute([$usuario['id']]);

                $_SESSION['logado']       = true;
                $_SESSION['usuario_id']   = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email']= $usuario['email'];
                $_SESSION['perfil']       = $usuario['perfil'];
                $_SESSION['is_operador']  = (bool)($usuario['is_operador'] ?? false);
                $_SESSION['perm_criar_equip']     = (bool)($usuario['perm_criar_equip'] ?? false);
                $_SESSION['perm_editar_equip']    = (bool)($usuario['perm_editar_equip'] ?? false);
                $_SESSION['perm_resolver_alarme'] = (bool)($usuario['perm_resolver_alarme'] ?? false);

                registrarLog('LOGIN', 'usuarios', $usuario['id']);

                header('Location: dashboard.php');
                exit;
            } else {
                $erro = 'E-mail ou senha incorretos.';
                // Demo: aceita admin@indux.com.br / admin123
                if ($email === 'admin@indux.com.br' && $senha === 'admin123') {
                    $_SESSION['logado']       = true;
                    $_SESSION['usuario_id']   = 1;
                    $_SESSION['usuario_nome'] = 'Administrador INDUX';
                    $_SESSION['usuario_email']= 'admin@indux.com.br';
                    $_SESSION['perfil']       = 'admin';
                    $_SESSION['is_operador']  = false;
                    $_SESSION['perm_criar_equip']     = false;
                    $_SESSION['perm_editar_equip']    = false;
                    $_SESSION['perm_resolver_alarme'] = false;
                    header('Location: dashboard.php');
                    exit;
                }
            }
        } catch (Throwable $e) {

            if ($email === 'admin@indux.com.br' && $senha === 'admin123') {
                $_SESSION['logado']       = true;
                $_SESSION['usuario_id']   = 1;
                $_SESSION['usuario_nome'] = 'Administrador INDUX';
                $_SESSION['usuario_email']= 'admin@indux.com.br';
                $_SESSION['perfil']       = 'admin';
                $_SESSION['is_operador']  = false;
                $_SESSION['perm_criar_equip']     = false;
                $_SESSION['perm_editar_equip']    = false;
                $_SESSION['perm_resolver_alarme'] = false;
                header('Location: dashboard.php');
                exit;
            }
            $erro = 'Erro de conexão. Use as credenciais demo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — INDUX Monitoramento Industrial</title>
  <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body class="login-body">

  <div class="login-bg-pattern"></div>

  <div style="position:fixed;top:10%;left:5%;opacity:.06;font-size:8rem;pointer-events:none;color:var(--accent);font-family:var(--font-mono)">01010</div>
  <div style="position:fixed;bottom:10%;right:5%;opacity:.06;font-size:6rem;pointer-events:none;color:var(--accent2);font-family:var(--font-mono)">SCADA</div>

  <main class="login-container">

    <div class="login-brand">
      <svg width="52" height="52" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="42" height="42" rx="10" fill="rgba(0,200,255,0.1)" stroke="rgba(0,200,255,0.3)" stroke-width="1"/>
        <polygon points="21,4 34,11.5 34,26.5 21,34 8,26.5 8,11.5" fill="none" stroke="#00c8ff" stroke-width="1.5"/>
        <line x1="21" y1="11" x2="21" y2="27" stroke="#00c8ff" stroke-width="1.5" stroke-linecap="round"/>
        <line x1="13" y1="19" x2="29" y2="19" stroke="#00c8ff" stroke-width="1.5" stroke-linecap="round"/>
        <circle cx="21" cy="19" r="3" fill="#00c8ff"/>
        <circle cx="21" cy="5.5" r="1.5" fill="#f59e0b"/>
      </svg>
    </div>

    <h1 class="login-titulo">INDUX</h1>
    <p class="login-subtitulo">Monitoramento Industrial Inteligente</p>

    <div class="login-divider"></div>

    <?php if ($erro): ?>
    <div class="alerta alerta--erro" style="margin-bottom:1rem">
      ⚠️ <?php echo htmlspecialchars($erro); ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="login-form">

      <div class="form-group">
        <label class="form-label" for="email">E-mail</label>
        <input
          type="email"
          id="email"
          name="email"
          class="form-control"
          placeholder="seu@email.com.br"
          value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
          required
          autocomplete="username"
        >
      </div>

      <div class="form-group">
        <label class="form-label" for="senha">Senha</label>
        <input
          type="password"
          id="senha"
          name="senha"
          class="form-control"
          placeholder="••••••••"
          required
          autocomplete="current-password"
        >
      </div>

      <button type="submit" class="btn btn--primary btn--lg">
        🔑 Acessar o Sistema
      </button>

    </form>

    <div class="login-hint">
      <strong style="color:var(--accent)">Demo:</strong>
      admin@indux.com.br / admin123
    </div>

  </main>

</body>
</html>
