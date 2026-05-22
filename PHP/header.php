<?php

$paginaAtual = basename($_SERVER['PHP_SELF'], '.php');
$paginaAtual = ($paginaAtual === 'index') ? 'dashboard' : $paginaAtual;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — INDUX Monitoramento Industrial</title>
  <link rel="stylesheet" href="../CSS/styles.css">
</head>
<header class="site-header">
  <div class="site-header__inner">

    <a class="logo" href="dashboard.php">
      <div class="logo-icon">
        <img src="IMG/Monitoramento industrial (2).png">
      </div>
    </a>

    <div class="system-status">
      <div class="status-dot"></div>
      SISTEMA ONLINE — v1.0
    </div>

    <nav class="nav-principal" aria-label="Principal">

      <div>
        <div class="nav-label">Painel</div>
        <ul>
          <li>
            <a href="dashboard.php" class="<?php echo $paginaAtual === 'dashboard' ? 'active' : ''; ?>">
              <span class="nav-icon">📊</span> Dashboard
            </a>
          </li>
          <li>
            <a href="monitoramento.php" class="<?php echo $paginaAtual === 'monitoramento' ? 'active' : ''; ?>">
              <span class="nav-icon">📡</span> Monitoramento
            </a>
          </li>
        </ul>
      </div>

      <div>
        <div class="nav-label">Industrial</div>
        <ul>
          <li>
            <a href="equipamentos.php" class="<?php echo in_array($paginaAtual, ['equipamentos','novo-equipamento']) ? 'active' : ''; ?>">
              <span class="nav-icon">⚙️</span> Equipamentos
            </a>
          </li>
          <li>
            <a href="alarmes.php" class="<?php echo $paginaAtual === 'alarmes' ? 'active' : ''; ?>">
              <span class="nav-icon">🔔</span> Alarmes
              <?php
              try {
                  require_once 'db.php';
                  $consultaCriticos = getDB()->query("SELECT COUNT(*) FROM alarmes WHERE resolvido = 0 AND severidade = 'critico'");
                  $qtdCriticos = $consultaCriticos->fetchColumn();
                  if ($qtdCriticos > 0):
              ?><span style="background:var(--red);color:#fff;font-size:.6rem;padding:1px 5px;border-radius:3px;margin-left:.3rem;font-family:var(--font-mono)"><?php echo $qtdCriticos; ?></span><?php
                  endif;
              } catch(Throwable $e) {}
              ?>
            </a>
          </li>
        </ul>
      </div>

      <div>
        <div class="nav-label">Administração</div>
        <ul>
          <?php if (ehAdmin()): ?>
          <li>
            <a href="usuarios.php" class="<?php echo in_array($paginaAtual, ['usuarios','novo-usuario']) ? 'active' : ''; ?>">
              <span class="nav-icon">👥</span> Usuários
            </a>
          </li>
          <?php endif; ?>
        </ul>
      </div>

    </nav>

    <div class="sidebar-footer">
      <?php if (isset($_SESSION['logado']) && $_SESSION['logado']): ?>
      <a href="planos.php" class="btn-upgrade">
        <span>UP</span> Upgrade de plano
      </a>
      <div class="sidebar-user">
        <div class="user-avatar"><?php echo inicialNome($_SESSION['usuario_nome'] ?? 'U'); ?></div>
        <div class="user-info">
          <div class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></div>
          <div class="user-role"><?php echo $_SESSION['perfil'] ?? 'visualizador'; ?></div>
        </div>
      </div>
      <a href="logout.php" class="btn-logout">
        <span><img src="IMG/trash-2.png" alt=""></span> Sair do Sistema
      </a>
      <?php else: ?>
      <a href="login.php" class="btn-logout" style="color:var(--green);border-color:rgba(16,185,129,.3)">
        <span>🔑</span> Entrar
      </a>
      <?php endif; ?>
    </div>

  </div>
</header>
</html>