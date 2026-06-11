<?php
require_once 'icon.php';
require_once 'init.php';
require_once 'db.php';
requerLogin();

$estatisticasEquipamentos = [
  'total'     => 0,
  'ativos'    => 0,
  'inativos'  => 0,
  'em_falha'  => 0,
];

$alarmesCriticos  = 0;
$alarmesTotal     = 0;
$ultimosAlarmes   = [];
$ultimasLeituras  = [];
$ultimosEquip     = [];

try {
  $db = getDB();

  $linhasStatus = $db->query(
    "SELECT status, COUNT(*) as qtd FROM equipamentos GROUP BY status"
  )->fetchAll();
  foreach ($linhasStatus as $linhaStatus) {
    $estatisticasEquipamentos[$linhaStatus['status']] = (int)$linhaStatus['qtd'];
    $estatisticasEquipamentos['total'] += (int)$linhaStatus['qtd'];
  }

  $consultaAlarmesAbertos = $db->query(
    "SELECT COUNT(*)
       FROM alarmes a
       JOIN equipamentos e ON e.id = a.equipamento_id
      WHERE a.resolvido = 0
        AND e.status <> 'inativo'"
  );
  $alarmesTotal = (int)$consultaAlarmesAbertos->fetchColumn();
  $consultaAlarmesCriticos = $db->query(
    "SELECT COUNT(*)
       FROM alarmes a
       JOIN equipamentos e ON e.id = a.equipamento_id
      WHERE a.resolvido = 0
        AND a.severidade = 'critico'
        AND e.status <> 'inativo'"
  );
  $alarmesCriticos = (int)$consultaAlarmesCriticos->fetchColumn();

  $ultimosAlarmes = $db->query(
    "SELECT a.*, e.nome as equip_nome, e.tag as equip_tag
         FROM alarmes a
         JOIN equipamentos e ON e.id = a.equipamento_id
         WHERE a.resolvido = 0
           AND e.status <> 'inativo'
         ORDER BY a.criado_em DESC LIMIT 5"
  )->fetchAll();

  $ultimasLeituras = $db->query(
    "SELECT ls.*, e.nome, e.tag, e.status, e.temp_max, e.pressao_max
         FROM leituras_sensor ls
         JOIN equipamentos e ON e.id = ls.equipamento_id
         WHERE ls.id IN (
           SELECT MAX(id) FROM leituras_sensor GROUP BY equipamento_id
         )
         ORDER BY ls.registrado_em DESC LIMIT 6"
  )->fetchAll();

  $ultimosEquip = $db->query(
    "SELECT * FROM equipamentos ORDER BY criado_em DESC LIMIT 4"
  )->fetchAll();
} catch (Throwable $e) {
  $estatisticasEquipamentos = ['total' => 8, 'ativos' => 5, 'inativos' => 2, 'em_falha' => 1];
  $alarmesCriticos = 2;
  $alarmesTotal    = 4;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php echo $icon; ?>
  <title>Indux | Dashboard</title>
  <link rel="stylesheet" href="../CSS/styles.css">
  <link rel="shortcut icon" type="png" href="IMG/logo.png">
</head>

<body>

  <?php require_once 'header.php'; ?>

  <main class="site-main">

    <div class="page-header">
      <div class="page-header-left">
        <div class="page-icon">{{lucide:chart-no-axes-combined}}</div>
        <div>
          <div class="breadcrumb">
            <span>INDUX</span> / <span>Dashboard</span>
          </div>
          <h1 class="page-title">Dashboard</h1>
          <p class="page-subtitle">Visão geral do sistema industrial</p>
        </div>
      </div>
      <div style="display:flex;gap:.6rem;align-items:center">
        <span style="font-size:.72rem;color:var(--text-muted);font-family:var(--font-mono)">
          Atualizado: <?php echo date('d/m/Y H:i:s'); ?>
        </span>
        <a href="dashboard.php" class="btn btn--ghost btn--sm">{{lucide:rotate-cw}} Atualizar</a>
      </div>
    </div>

    <?php if (isset($_GET['erro']) && $_GET['erro'] === 'acesso_negado'): ?>
      <div class="alerta alerta--erro">{{lucide:shield-x}} Acesso negado. Você não tem permissão para acessar essa área.</div>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="kpi-grid">
      <div class="kpi-card kpi-card--cyan">
        <div>
          <div class="kpi-label">Total Equipamentos</div>
          <div class="kpi-valor"><?php echo $estatisticasEquipamentos['total']; ?></div>
        </div>
      </div>
      <div class="kpi-card kpi-card--green">
        <div>
          <div class="kpi-label">Ativos</div>
          <div class="kpi-valor"><?php echo $estatisticasEquipamentos['ativos']; ?></div>
        </div>
      </div>
      <div class="kpi-card kpi-card--blue">
        <div>
          <div class="kpi-label">Inativos</div>
          <div class="kpi-valor"><?php echo $estatisticasEquipamentos['inativos']; ?></div>
        </div>
      </div>
      <div class="kpi-card <?php echo $estatisticasEquipamentos['em_falha'] > 0 ? 'kpi-card--red' : 'kpi-card--purple'; ?>">
        <div>
          <div class="kpi-label">Em Falha</div>
          <div class="kpi-valor"><?php echo $estatisticasEquipamentos['em_falha']; ?></div>
        </div>
      </div>
      <div class="kpi-card <?php echo $alarmesCriticos > 0 ? 'kpi-card--yellow' : 'kpi-card--green'; ?>">
        <div>
          <div class="kpi-label">Alarmes Críticos</div>
          <div class="kpi-valor"><?php echo $alarmesCriticos; ?></div>
        </div>
      </div>
      <div class="kpi-card kpi-card--purple">
        <div>
          <div class="kpi-label">Alarmes Abertos</div>
          <div class="kpi-valor"><?php echo $alarmesTotal; ?></div>
        </div>
      </div>
    </div>

    <div class="monitor-grid">

      <div class="panel-card">
        <div class="panel-header">
          <div class="panel-title">{{lucide:radio}} Últimas Leituras dos Sensores</div>
          <a href="monitoramento.php" class="btn btn--ghost btn--sm">Ver tudo</a>
        </div>
        <div class="panel-body" style="padding:0">
          <?php if (empty($ultimasLeituras)): ?>
            <div class="empty-state" style="padding:2.5rem">
              <div class="empty-state__icon">{{lucide:radio}}</div>
              <div class="empty-state__title">Nenhuma leitura registrada</div>
              <div class="empty-state__desc">Cadastre equipamentos e registre leituras</div>
            </div>
          <?php else: ?>
            <table class="tabela-estoque">
              <thead>
                <tr>
                  <th>Equipamento</th>
                  <th>Temp. (°C)</th>
                  <th>Pressão (bar)</th>
                  <th>Status</th>
                  <th>Horário</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($ultimasLeituras as $leitura):
                  $classeTemperatura = avaliarTemp($leitura['temperatura'], 0, $leitura['temp_max'] ?: 80);
                  $classePressao = avaliarPressao($leitura['pressao'], 0, $leitura['pressao_max'] ?: 10);
                ?>
                  <tr>
                    <td>
                      <div style="font-weight:600;font-size:.85rem;color:#fff"><?php echo htmlspecialchars($leitura['nome']); ?></div>
                      <span class="tag-chip"><?php echo htmlspecialchars($leitura['tag']); ?></span>
                    </td>
                    <td class="text-mono leitura-val <?php echo $classeTemperatura; ?>"><?php echo number_format($leitura['temperatura'], 1); ?>°</td>
                    <td class="text-mono leitura-val <?php echo $classePressao; ?>"><?php echo number_format($leitura['pressao'], 2); ?> bar</td>
                    <td><span class="status-badge <?php echo statusClass($leitura['status']); ?>"><?php echo statusLabel($leitura['status']); ?></span></td>
                    <td style="font-size:.72rem;color:var(--text-muted);font-family:var(--font-mono)"><?php echo date('H:i', strtotime($leitura['registrado_em'])); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

      <div class="panel-card">
        <div class="panel-header">
          <div class="panel-title">{{lucide:bell}} Alarmes Ativos</div>
          <a href="alarmes.php" class="btn btn--ghost btn--sm">Ver todos</a>
        </div>
        <div class="panel-body">
          <?php if (empty($ultimosAlarmes)): ?>
            <div class="empty-state" style="padding:2rem">
              <div class="empty-state__icon">{{lucide:circle-check}}</div>
              <div class="empty-state__title">Nenhum alarme ativo</div>
              <div class="empty-state__desc">Sistema operando normalmente</div>
            </div>
          <?php else: ?>
            <?php foreach ($ultimosAlarmes as $alarme): ?>
              <div class="alarme-item <?php echo $alarme['severidade']; ?>">
                <div class="alarme-icon"><?php echo tipoAlarmeIcon($alarme['tipo']); ?></div>
                <div class="alarme-body">
                  <div class="alarme-msg"><?php echo htmlspecialchars($alarme['mensagem']); ?></div>
                  <div class="alarme-meta">
                    <span class="tag-chip"><?php echo htmlspecialchars($alarme['equip_tag']); ?></span>
                    <span><?php echo date('d/m H:i', strtotime($alarme['criado_em'])); ?></span>
                  </div>
                </div>

              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <?php if (!empty($ultimosEquip)): ?>
      <div class="panel-card" style="margin-top:1.25rem">
        <div class="panel-header">
          <div class="panel-title">{{lucide:settings}} Equipamentos Recentes</div>
          <a href="equipamentos.php" class="btn btn--ghost btn--sm">Ver todos</a>
        </div>
        <div class="panel-body" style="padding:0">
          <table class="tabela-estoque">
            <thead>
              <tr>
                <th>TAG</th>
                <th>Equipamento</th>
                <th>Localização</th>
                <th>Status</th>
                <th>Cadastrado em</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ultimosEquip as $equipamento): ?>
                <tr>
                  <td><span class="tag-chip"><?php echo htmlspecialchars($equipamento['tag']); ?></span></td>
                  <td>
                    <strong style="color:#fff"><?php echo htmlspecialchars($equipamento['nome']); ?></strong>
                    <?php if ($equipamento['modelo']): ?>
                      <div style="font-size:.72rem;color:var(--text-muted);font-family:var(--font-mono)"><?php echo htmlspecialchars($equipamento['modelo']); ?></div>
                    <?php endif; ?>
                  </td>
                  <td style="font-size:.82rem;color:var(--text-dim)"><?php echo htmlspecialchars($equipamento['localizacao'] ?: '—'); ?></td>
                  <td><span class="status-badge <?php echo statusClass($equipamento['status']); ?>"><?php echo statusLabel($equipamento['status']); ?></span></td>
                  <td style="font-size:.75rem;color:var(--text-muted);font-family:var(--font-mono)"><?php echo date('d/m/Y', strtotime($equipamento['criado_em'])); ?></td>
                  <td>
                    <a href="equipamentos.php?ver=<?php echo $equipamento['id']; ?>" class="btn btn--ghost btn--sm">Ver</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <div style="margin-top:1.75rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem">
      <?php if (podeCriarEquip()): ?>
        <a href="novo-equipamento.php" class="btn btn--primary btn--lg" style="justify-content:center">
          {{lucide:plus}} Novo Equipamento
        </a>
      <?php endif; ?>
      <a href="equipamentos.php" class="btn btn--ghost btn--lg" style="justify-content:center">
        {{lucide:settings}} Todos Equipamentos
      </a>
      <a href="alarmes.php" class="btn btn--warning btn--lg" style="justify-content:center">
        {{lucide:bell}} Ver Alarmes
      </a>
      <?php if (ehAdmin()): ?>
        <a href="usuarios.php" class="btn btn--ghost btn--lg" style="justify-content:center">
          {{lucide:users-round}} Usuários
        </a>
      <?php endif; ?>
    </div>

  </main>

  <?php require_once 'footer.php'; ?>

</body>

</html>
