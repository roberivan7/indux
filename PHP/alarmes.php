<?php
require_once 'icon.php';
require_once 'init.php';
require_once 'db.php';
requerLogin();

$msg     = '';
$msgTipo = 'info';

if (isset($_GET['resolver']) && podeResolverAlarme()) {
  $alarmeId = (int)$_GET['resolver'];
  try {
    $db = getDB();
    $db->prepare(
      'UPDATE alarmes SET resolvido=1, resolvido_por=?, resolvido_em=NOW() WHERE id=?'
    )->execute([$_SESSION['usuario_id'], $alarmeId]);
    registrarLog('RESOLVER_ALARME', 'alarmes', $alarmeId);
    $msg = 'Alarme marcado como resolvido.';
    $msgTipo = 'sucesso';
  } catch (Throwable $e) {
    $msg = 'Erro ao resolver alarme.';
    $msgTipo = 'erro';
  }
  header('Location: alarmes.php?msg=' . urlencode($msg) . '&tipo=' . $msgTipo);
  exit;
}

if (isset($_GET['resolver_todos']) && podeResolverAlarme()) {
  try {
    $db = getDB();
    $db->prepare(
      'UPDATE alarmes SET resolvido=1, resolvido_por=?, resolvido_em=NOW() WHERE resolvido=0'
    )->execute([$_SESSION['usuario_id']]);
    $msg = 'Todos os alarmes ativos foram resolvidos.';
    $msgTipo = 'sucesso';
  } catch (Throwable $e) {
    $msg = 'Erro.';
    $msgTipo = 'erro';
  }
  header('Location: alarmes.php?msg=' . urlencode($msg) . '&tipo=' . $msgTipo);
  exit;
}

if (isset($_GET['msg'])) {
  $msg = htmlspecialchars($_GET['msg']);
  $msgTipo = $_GET['tipo'] ?? 'info';
}

$filtroSeveridade = $_GET['sev']    ?? '';
$filtroResolvido = $_GET['res']      ?? '0';
$filtroTipo     = $_GET['tipo_al']  ?? '';

$alarmes  = [];
$contagens = ['total' => 0, 'critico' => 0, 'alerta' => 0, 'informativo' => 0, 'resolvidos' => 0];

try {
  $db = getDB();

  $linhasSeveridade = $db->query(
    "SELECT severidade, COUNT(*) as qtd FROM alarmes WHERE resolvido=0 GROUP BY severidade"
  )->fetchAll();
  foreach ($linhasSeveridade as $linhaSeveridade) {
    $contagens[$linhaSeveridade['severidade']] = (int)$linhaSeveridade['qtd'];
    $contagens['total'] += (int)$linhaSeveridade['qtd'];
  }
  $totalResolvidos = $db->query("SELECT COUNT(*) FROM alarmes WHERE resolvido=1")->fetchColumn();
  $contagens['resolvidos'] = (int)$totalResolvidos;

  $filtrosSql = [];
  $parametros = [];

  if ($filtroResolvido !== '') {
    $filtrosSql[] = 'a.resolvido = ?';
    $parametros[] = (int)$filtroResolvido;
  }
  if ($filtroSeveridade !== '') {
    $filtrosSql[] = 'a.severidade = ?';
    $parametros[] = $filtroSeveridade;
  }
  if ($filtroTipo !== '') {
    $filtrosSql[] = 'a.tipo = ?';
    $parametros[] = $filtroTipo;
  }

  $filtroSql = $filtrosSql ? 'WHERE ' . implode(' AND ', $filtrosSql) : '';

  $consultaAlarmes = $db->prepare(
    "SELECT a.*, e.nome as equip_nome, e.tag as equip_tag,
                u.nome as resolvido_nome
         FROM alarmes a
         JOIN equipamentos e ON e.id = a.equipamento_id
         LEFT JOIN usuarios u ON u.id = a.resolvido_por
         $filtroSql
         ORDER BY
           a.resolvido ASC,
           CASE a.severidade WHEN 'critico' THEN 0 WHEN 'alerta' THEN 1 ELSE 2 END,
           a.criado_em DESC
         LIMIT 100"
  );
  $consultaAlarmes->execute($parametros);
  $alarmes = $consultaAlarmes->fetchAll();
} catch (Throwable $e) {
  // Demo
  $alarmes = [
    ['id' => 1, 'tipo' => 'temperatura', 'severidade' => 'critico', 'mensagem' => 'Temperatura acima do limite: 88.9°C (máx: 70°C)', 'valor_registrado' => 88.9, 'valor_limite' => 70, 'resolvido' => 0, 'resolvido_por' => null, 'resolvido_em' => null, 'criado_em' => date('Y-m-d H:i:s', time() - 120), 'equip_nome' => 'Compressor Industrial', 'equip_tag' => 'CMP-002', 'resolvido_nome' => null],
    ['id' => 2, 'tipo' => 'pressao', 'severidade' => 'critico', 'mensagem' => 'Pressão excede limite crítico: 13.2 bar (máx: 12 bar)', 'valor_registrado' => 13.2, 'valor_limite' => 12, 'resolvido' => 0, 'resolvido_por' => null, 'resolvido_em' => null, 'criado_em' => date('Y-m-d H:i:s', time() - 180), 'equip_nome' => 'Compressor Industrial', 'equip_tag' => 'CMP-002', 'resolvido_nome' => null],
    ['id' => 3, 'tipo' => 'manutencao', 'severidade' => 'alerta', 'mensagem' => 'Manutenção preventiva programada para esta semana', 'valor_registrado' => null, 'valor_limite' => null, 'resolvido' => 0, 'resolvido_por' => null, 'resolvido_em' => null, 'criado_em' => date('Y-m-d H:i:s', time() - 3600), 'equip_nome' => 'Caldeira Principal', 'equip_tag' => 'CLD-001', 'resolvido_nome' => null],
    ['id' => 4, 'tipo' => 'conexao', 'severidade' => 'informativo', 'mensagem' => 'Sensor de temperatura reiniciado com sucesso', 'valor_registrado' => null, 'valor_limite' => null, 'resolvido' => 1, 'resolvido_por' => 1, 'resolvido_em' => date('Y-m-d H:i:s', time() - 1800), 'criado_em' => date('Y-m-d H:i:s', time() - 7200), 'equip_nome' => 'Bomba Hidráulica', 'equip_tag' => 'BBA-003', 'resolvido_nome' => 'Administrador INDUX'],
  ];
  $contagens = ['total' => 3, 'critico' => 2, 'alerta' => 1, 'informativo' => 0, 'resolvidos' => 1];

  if ($filtroResolvido !== '') {
    $alarmes = array_filter($alarmes, fn($alarme) => (int)$alarme['resolvido'] === (int)$filtroResolvido);
  }
  if ($filtroSeveridade !== '') {
    $alarmes = array_filter($alarmes, fn($alarme) => $alarme['severidade'] === $filtroSeveridade);
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php echo $icon; ?>
  <title>Indux | Alarmes</title>
  <link rel="stylesheet" href="../CSS/styles.css">
</head>

<body>

  <?php require_once 'header.php'; ?>

  <main class="site-main">

    <div class="page-header">
      <div class="page-header-left">
        <div class="page-icon">🔔</div>
        <div>
          <div class="breadcrumb"><span>INDUX</span> / <span>Alarmes</span></div>
          <h1 class="page-title">Alarmes Industriais</h1>
          <p class="page-subtitle">Monitoramento de eventos críticos e notificações do sistema</p>
        </div>
      </div>
      <?php if (ehOperador() && $contagens['total'] > 0): ?>
        <a href="alarmes.php?resolver_todos=1"
          onclick="return confirm('Resolver todos os alarmes ativos?')"
          class="btn btn--warning">
          Resolver Todos
        </a>
      <?php endif; ?>
    </div>

    <?php if ($msg): ?>
      <div class="alerta alerta--<?php echo $msgTipo; ?>"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="kpi-grid" style="grid-template-columns:repeat(5,1fr);margin-bottom:1.25rem">
      <div class="kpi-card <?php echo $contagens['total'] > 0 ? 'kpi-card--yellow' : 'kpi-card--green'; ?>" style="padding:.9rem 1rem">
        <div>
          <div class="kpi-label">Ativos</div>
          <div class="kpi-valor" style="font-size:1.4rem"><?php echo $contagens['total']; ?></div>
        </div>
      </div>
      <div class="kpi-card kpi-card--red" style="padding:.9rem 1rem">
        <div>
          <div class="kpi-label">Críticos</div>
          <div class="kpi-valor" style="font-size:1.4rem"><?php echo $contagens['critico']; ?></div>
        </div>
      </div>
      <div class="kpi-card kpi-card--yellow" style="padding:.9rem 1rem">
        <div>
          <div class="kpi-label">Alertas</div>
          <div class="kpi-valor" style="font-size:1.4rem"><?php echo $contagens['alerta']; ?></div>
        </div>
      </div>
      <div class="kpi-card kpi-card--cyan" style="padding:.9rem 1rem">
        <div>
          <div class="kpi-label">Informativos</div>
          <div class="kpi-valor" style="font-size:1.4rem"><?php echo $contagens['informativo']; ?></div>
        </div>
      </div>
      <div class="kpi-card kpi-card--green" style="padding:.9rem 1rem">
        <div>
          <div class="kpi-label">Resolvidos</div>
          <div class="kpi-valor" style="font-size:1.4rem"><?php echo $contagens['resolvidos']; ?></div>
        </div>
      </div>
    </div>

    <div class="tabs">
      <a href="alarmes.php?res=0" class="tab-btn <?php echo $filtroResolvido === '0' && $filtroSeveridade === '' ? 'ativo' : ''; ?>">
        Pendentes <span class="tab-badge tab-badge--red"><?php echo $contagens['total']; ?></span>
      </a>
      <a href="alarmes.php?res=0&sev=critico" class="tab-btn <?php echo $filtroSeveridade === 'critico' ? 'ativo' : ''; ?>">
        Críticos <span class="tab-badge tab-badge--red"><?php echo $contagens['critico']; ?></span>
      </a>
      <a href="alarmes.php?res=0&sev=alerta" class="tab-btn <?php echo $filtroSeveridade === 'alerta' ? 'ativo' : ''; ?>">
        Alertas <span class="tab-badge tab-badge--yellow"><?php echo $contagens['alerta']; ?></span>
      </a>
      <a href="alarmes.php?res=1" class="tab-btn <?php echo $filtroResolvido === '1' ? 'ativo' : ''; ?>">
        Resolvidos <span class="tab-badge"><?php echo $contagens['resolvidos']; ?></span>
      </a>
      <a href="alarmes.php" class="tab-btn <?php echo $filtroResolvido === '' ? 'ativo' : ''; ?>">
        Todos
      </a>
    </div>

    <?php if (empty($alarmes)): ?>
      <div class="empty-state">
        <div class="empty-state__icon"><?php echo $filtroResolvido === '0' ? '✅' : '📋'; ?></div>
        <div class="empty-state__title">
          <?php echo $filtroResolvido === '0' ? 'Nenhum alarme ativo' : 'Nenhum alarme encontrado'; ?>
        </div>
        <div class="empty-state__desc">
          <?php echo $filtroResolvido === '0' ? 'Todos os sistemas operando normalmente.' : 'Tente ajustar os filtros.'; ?>
        </div>
      </div>
    <?php else: ?>
      <div>
        <?php foreach ($alarmes as $alarme): ?>
          <div class="alarme-item <?php echo $alarme['severidade']; ?> <?php echo $alarme['resolvido'] ? 'resolvido' : ''; ?>">

            <div class="alarme-icon"><?php echo tipoAlarmeIcon($alarme['tipo']); ?></div>

            <div class="alarme-body">
              <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:.3rem">
                <span class="severidade-badge sev--<?php echo $alarme['severidade']; ?>">
                  <?php echo ucfirst($alarme['severidade']); ?>
                </span>
                <span class="tag-chip"><?php echo htmlspecialchars($alarme['equip_tag']); ?></span>
                <span style="font-size:.78rem;font-weight:600;color:var(--text-dim)"><?php echo htmlspecialchars($alarme['equip_nome']); ?></span>
              </div>
              <div class="alarme-msg"><?php echo htmlspecialchars($alarme['mensagem']); ?></div>
              <div class="alarme-meta" style="margin-top:.3rem">
                <?php if ($alarme['valor_registrado'] !== null): ?>
                  <span>📊 Valor: <strong><?php echo number_format($alarme['valor_registrado'], 2); ?></strong></span>
                <?php endif; ?>
                <?php if ($alarme['valor_limite'] !== null): ?>
                  <span>🚧 Limite: <strong><?php echo number_format($alarme['valor_limite'], 2); ?></strong></span>
                <?php endif; ?>
                <span>🕐 <?php echo date('d/m/Y H:i', strtotime($alarme['criado_em'])); ?></span>
                <?php if ($alarme['resolvido'] && $alarme['resolvido_nome']): ?>
                  <span style="color:var(--green)">✅ Resolvido por <?php echo htmlspecialchars($alarme['resolvido_nome']); ?></span>
                <?php endif; ?>
              </div>
            </div>

            <?php if (!$alarme['resolvido'] && ehOperador()): ?>
              <a href="alarmes.php?resolver=<?php echo $alarme['id']; ?>&res=0"
                class="btn btn--success btn--sm"
                onclick="return confirm('Marcar alarme como resolvido?')">
                Resolver
              </a>
            <?php endif; ?>

          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </main>

  <?php require_once '../PHP/footer.php'; ?>

</body>

</html>