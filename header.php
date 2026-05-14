<?php
// Determina a página atual para destacar link ativo
$paginaAtual = basename($_SERVER['PHP_SELF'], '.php');
$paginaAtual = ($paginaAtual === 'index') ? 'dashboard' : $paginaAtual;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — INDUX Monitoramento Industrial</title>
  <link rel="stylesheet" href="styles.css">
</head>
<header class="site-header">
  <div class="site-header__inner">

    <!-- Logo -->
    <a class="logo" href="dashboard.php">
      <div class="logo-icon">
        <img src="logo.png">
          <rect width="42" height="42" rx="10" fill="rgba(0,200,255,0.08)"/>
          <!-- Hexágono industrial -->
          <polygon points="21,4 34,11.5 34,26.5 21,34 8,26.5 8,11.5" fill="none" stroke="#00c8ff" stroke-width="1.5" stroke-linejoin="round"/>
          <!-- Cruz central -->
          <line x1="21" y1="11" x2="21" y2="27" stroke="#00c8ff" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="13" y1="19" x2="29" y2="19" stroke="#00c8ff" stroke-width="1.5" stroke-linecap="round"/>
          <!-- Círculo central -->
          <circle cx="21" cy="19" r="3" fill="#00c8ff" opacity=".9"/>
          <!-- Pontos nos cantos do hex -->
          <circle cx="21" cy="5.5" r="1.5" fill="#f59e0b"/>
          <circle cx="32.5" cy="12" r="1.5" fill="#f59e0b" opacity=".6"/>
          <circle cx="32.5" cy="26" r="1.5" fill="#f59e0b" opacity=".6"/>
        </svg>
      </div>
      <div class="logo-text">
        <!-- <span class="logo-name">INDUX</span> -->
        <!-- <span class="logo-tagline">Monitoramento Industrial</span> -->
      </div>
    </a>

    <!-- Status do sistema -->
    <div class="system-status">
      <div class="status-dot"></div>
      SISTEMA ONLINE — v1.0
    </div>

    <!-- Navegação principal -->
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
              // Badge de alarmes não resolvidos
              try {
                  require_once 'db.php';
                  $stmt = getDB()->query("SELECT COUNT(*) FROM alarmes WHERE resolvido = 0 AND severidade = 'critico'");
                  $qtdCriticos = $stmt->fetchColumn();
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
          <li>
            <a href="relatorios.php" class="<?php echo $paginaAtual === 'relatorios' ? 'active' : ''; ?>">
              <span class="nav-icon">📋</span> Relatórios
            </a>
          </li>
        </ul>
      </div>

    </nav>

    <!-- Footer do sidebar -->
    <div class="sidebar-footer">
      <?php if (isset($_SESSION['logado']) && $_SESSION['logado']): ?>
      <div class="sidebar-user">
        <div class="user-avatar"><?php echo inicialNome($_SESSION['usuario_nome'] ?? 'U'); ?></div>
        <div class="user-info">
          <div class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></div>
          <div class="user-role"><?php echo $_SESSION['perfil'] ?? 'visualizador'; ?></div>
        </div>
      </div>
      <a href="logout.php" class="btn-logout">
        <span>⏻</span> Sair do Sistema
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