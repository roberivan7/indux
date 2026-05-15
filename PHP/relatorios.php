<?php
require_once 'init.php';
require_once 'db.php';
requerLogin();
if (!podeVerRelatorios()) {
    header('Location: dashboard.php?erro=acesso_negado'); exit;
}

// ── Parâmetros ────────────────────────────────────────────
$aba        = $_GET['aba']   ?? 'geral';
$diasFiltro = (int)($_GET['dias'] ?? 7);
if (!in_array($diasFiltro, [7,14,30,90])) $diasFiltro = 7;

// ── Dados Gerais (sempre carrega) ─────────────────────────
$statsEquip  = dbEstatisticasEquipamentos();
$statsAlarmes= dbContarAlarmes();

// ── Dados por aba ─────────────────────────────────────────
$relAlarmesPorEquip  = [];
$relLeiturasPorDia   = [];
$relLog              = [];
$relUsuarios         = [];
$relEquipSemLeitura  = [];

switch ($aba) {
    case 'alarmes':
        $relAlarmesPorEquip = dbRelatorioAlarmesPorEquip();
        break;
    case 'leituras':
        $relLeiturasPorDia = dbRelatorioLeiturasPorDia($diasFiltro);
        break;
    case 'usuarios':
        $relUsuarios       = dbRelatorioUsuariosAtivos();
        break;
    case 'manutencao':
        $relEquipSemLeitura = dbRelatorioEquipSemLeitura();
        break;
    case 'logs':
        $limLog = (int)($_GET['lim'] ?? 50);
        if (!in_array($limLog,[25,50,100,200])) $limLog = 50;
        $relLog = dbRelatorioLogSistema($limLog);
        // Filtro de ação
        $filtroAcao = trim($_GET['acao_f'] ?? '');
        if ($filtroAcao !== '') {
            $relLog = array_filter($relLog, fn($l) => stripos($l['acao'], $filtroAcao) !== false);
        }
        // Filtro de usuário
        $filtroUser = trim($_GET['user_f'] ?? '');
        if ($filtroUser !== '') {
            $relLog = array_filter($relLog, fn($l) => stripos($l['usuario_nome'] ?? '', $filtroUser) !== false);
        }
        break;
    default: // geral
        $relAlarmesPorEquip  = dbRelatorioAlarmesPorEquip();
        $relEquipSemLeitura  = dbRelatorioEquipSemLeitura();
        $relLeiturasPorDia   = dbRelatorioLeiturasPorDia(7);
        $relLog              = dbRelatorioLogSistema(10);
}

// ── Exportar CSV ──────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $tipoExport = $_GET['tipo'] ?? 'log';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="indux_'.$tipoExport.'_'.date('Ymd').'.csv"');
    echo "\xEF\xBB\xBF"; // BOM UTF-8
    $out = fopen('php://output','w');
    switch ($tipoExport) {
        case 'log':
            $rows = dbRelatorioLogSistema(500);
            fputcsv($out, ['ID','Data/Hora','Usuário','Ação','Tabela','Registro ID','Detalhes','IP']);
            foreach ($rows as $r) {
                fputcsv($out, [$r['id'],$r['criado_em'],$r['usuario_nome']??'Sistema',$r['acao'],$r['tabela_afetada']??'',$r['registro_id']??'',$r['detalhes']??'',$r['ip']??'']);
            }
            break;
        case 'alarmes':
            $rows = dbRelatorioAlarmesPorEquip();
            fputcsv($out, ['TAG','Equipamento','Status','Total Alarmes','Críticos','Alertas','Pendentes']);
            foreach ($rows as $r) {
                fputcsv($out, [$r['tag'],$r['nome'],$r['status'],$r['total_alarmes'],$r['criticos'],$r['alertas'],$r['pendentes']]);
            }
            break;
        case 'leituras':
            $rows = dbRelatorioLeiturasPorDia(30);
            fputcsv($out, ['Data','Total Leituras','Temp. Média (°C)','Temp. Máx (°C)','Pressão Média (bar)','Pressão Máx (bar)']);
            foreach ($rows as $r) {
                fputcsv($out, [$r['data'],$r['total_leituras'],$r['avg_temp'],$r['max_temp'],$r['avg_pres'],$r['max_pres']]);
            }
            break;
    }
    fclose($out); exit;
}

$nomeDias = ['7'=>'Últimos 7 dias','14'=>'Últimos 14 dias','30'=>'Últimos 30 dias','90'=>'Últimos 90 dias'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Relatórios — <?= SISTEMA_NOME ?></title>
  <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body>
<?php require_once 'header.php'; ?>

<main class="site-main">

  <!-- Page header -->
  <div class="page-header">
    <div class="page-header-left">
      <div class="page-icon">📋</div>
      <div>
        <div class="breadcrumb"><span>INDUX</span> / <span>Relatórios</span></div>
        <h1 class="page-title">Relatórios</h1>
        <p class="page-subtitle">Análise de dados, logs de sistema e indicadores operacionais</p>
      </div>
    </div>
    <!-- Exportar -->
    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
      <a href="relatorios.php?export=csv&tipo=log" class="btn btn--ghost btn--sm">⬇️ Log CSV</a>
      <a href="relatorios.php?export=csv&tipo=alarmes" class="btn btn--ghost btn--sm">⬇️ Alarmes CSV</a>
      <a href="relatorios.php?export=csv&tipo=leituras" class="btn btn--ghost btn--sm">⬇️ Leituras CSV</a>
    </div>
  </div>

  <!-- KPIs gerais -->
  <div class="kpi-grid" style="margin-bottom:1.5rem">
    <div class="kpi-card kpi-card--cyan" style="padding:.9rem 1rem">
      <div class="kpi-icon" style="font-size:1.2rem;width:36px;height:36px">⚙️</div>
      <div><div class="kpi-label">Total Equip.</div><div class="kpi-valor" style="font-size:1.4rem"><?= $statsEquip['total'] ?></div></div>
    </div>
    <div class="kpi-card kpi-card--green" style="padding:.9rem 1rem">
      <div class="kpi-icon" style="font-size:1.2rem;width:36px;height:36px">✅</div>
      <div><div class="kpi-label">Ativos</div><div class="kpi-valor" style="font-size:1.4rem"><?= $statsEquip['ativos'] ?></div></div>
    </div>
    <div class="kpi-card kpi-card--blue" style="padding:.9rem 1rem">
      <div class="kpi-icon" style="font-size:1.2rem;width:36px;height:36px">⏸️</div>
      <div><div class="kpi-label">Inativos</div><div class="kpi-valor" style="font-size:1.4rem"><?= $statsEquip['inativos'] ?></div></div>
    </div>
    <div class="kpi-card <?= $statsEquip['em_falha']>0?'kpi-card--red':'kpi-card--purple' ?>" style="padding:.9rem 1rem">
      <div class="kpi-icon" style="font-size:1.2rem;width:36px;height:36px">🔴</div>
      <div><div class="kpi-label">Em Falha</div><div class="kpi-valor" style="font-size:1.4rem"><?= $statsEquip['em_falha'] ?></div></div>
    </div>
    <div class="kpi-card <?= $statsAlarmes['critico']>0?'kpi-card--red':'kpi-card--green' ?>" style="padding:.9rem 1rem">
      <div class="kpi-icon" style="font-size:1.2rem;width:36px;height:36px">🔴</div>
      <div><div class="kpi-label">Alarmes Críticos</div><div class="kpi-valor" style="font-size:1.4rem"><?= $statsAlarmes['critico'] ?></div></div>
    </div>
    <div class="kpi-card <?= $statsAlarmes['total']>0?'kpi-card--yellow':'kpi-card--green' ?>" style="padding:.9rem 1rem">
      <div class="kpi-icon" style="font-size:1.2rem;width:36px;height:36px">🔔</div>
      <div><div class="kpi-label">Alarmes Abertos</div><div class="kpi-valor" style="font-size:1.4rem"><?= $statsAlarmes['total'] ?></div></div>
    </div>
  </div>

  <!-- Abas de relatórios -->
  <div class="tabs">
    <?php $abas = [
      'geral'      => ['📊','Visão Geral'],
      'alarmes'    => ['🔔','Alarmes p/ Equip.'],
      'leituras'   => ['📈','Leituras Diárias'],
      'manutencao' => ['🔧','Manutenção'],
      'usuarios'   => ['👥','Usuários'],
      'logs'       => ['🗒️','Log do Sistema'],
    ];
    foreach ($abas as $slug => [$ico,$label]): ?>
    <a href="relatorios.php?aba=<?= $slug ?>" class="tab-btn <?= $aba===$slug?'ativo':'' ?>">
      <?= $ico ?> <?= $label ?>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if ($aba === 'geral'): ?>
  <!-- ═══════════════════════════════════ GERAL ═══ -->

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.25rem">

    <!-- Alarmes por equipamento -->
    <div class="panel-card">
      <div class="panel-header">
        <div class="panel-title">🔔 Alarmes por Equipamento</div>
        <a href="relatorios.php?aba=alarmes" class="btn btn--ghost btn--sm">Ver completo</a>
      </div>
      <div style="padding:0">
        <?php if (empty($relAlarmesPorEquip)): ?>
        <div class="empty-state" style="padding:2rem"><div class="empty-state__icon">✅</div><div class="empty-state__title">Nenhum alarme registrado</div></div>
        <?php else: ?>
        <table class="tabela-estoque">
          <thead><tr><th>Equipamento</th><th>Status</th><th>Total</th><th>Críticos</th><th>Alertas</th><th>Pendentes</th></tr></thead>
          <tbody>
          <?php foreach (array_slice($relAlarmesPorEquip,0,6) as $r): ?>
          <tr>
            <td><strong style="color:#fff;font-size:.85rem"><?= htmlspecialchars($r['nome']) ?></strong><br><span class="tag-chip"><?= htmlspecialchars($r['tag']) ?></span></td>
            <td><span class="status-badge <?= statusClass($r['status']) ?>"><?= statusLabel($r['status']) ?></span></td>
            <td style="font-family:var(--font-mono);font-weight:700;color:var(--accent)"><?= $r['total_alarmes'] ?></td>
            <td style="font-family:var(--font-mono);color:<?= $r['criticos']>0?'var(--red)':'var(--text-muted)' ?>"><?= $r['criticos'] ?: '—' ?></td>
            <td style="font-family:var(--font-mono);color:<?= $r['alertas']>0?'var(--yellow)':'var(--text-muted)' ?>"><?= $r['alertas'] ?: '—' ?></td>
            <td>
              <?php if ($r['pendentes'] > 0): ?>
              <span style="background:var(--red-dim);color:var(--red);padding:2px 7px;border-radius:4px;font-size:.7rem;font-family:var(--font-mono)"><?= $r['pendentes'] ?></span>
              <?php else: ?>
              <span style="color:var(--text-muted);font-size:.75rem">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>

    <!-- Equipamentos sem leitura recente -->
    <div class="panel-card">
      <div class="panel-header">
        <div class="panel-title">⚠️ Sem Leitura Recente</div>
        <span style="font-size:.7rem;color:var(--text-muted);font-family:var(--font-mono)">+24h</span>
      </div>
      <div class="panel-body">
        <?php if (empty($relEquipSemLeitura)): ?>
        <div class="empty-state" style="padding:1.5rem">
          <div class="empty-state__icon">✅</div>
          <div class="empty-state__title" style="font-size:.9rem">Todos com leituras recentes</div>
        </div>
        <?php else: ?>
        <?php foreach (array_slice($relEquipSemLeitura,0,5) as $r): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid var(--border)">
          <div>
            <div style="font-size:.85rem;font-weight:600;color:#fff"><?= htmlspecialchars($r['nome']) ?></div>
            <span class="tag-chip"><?= htmlspecialchars($r['tag']) ?></span>
          </div>
          <div style="text-align:right">
            <span class="status-badge <?= statusClass($r['status']) ?>" style="font-size:.65rem"><?= statusLabel($r['status']) ?></span>
            <div style="font-size:.68rem;color:var(--red);font-family:var(--font-mono);margin-top:.2rem">
              <?= $r['ultima_leitura'] ? date('d/m H:i', strtotime($r['ultima_leitura'])) : '— Nunca —' ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- Leituras últimos 7 dias -->
  <div class="panel-card" style="margin-top:1.25rem">
    <div class="panel-header">
      <div class="panel-title">📈 Leituras — Últimos 7 Dias</div>
      <a href="relatorios.php?aba=leituras" class="btn btn--ghost btn--sm">Ver completo</a>
    </div>
    <div style="padding:0">
      <?php if (empty($relLeiturasPorDia)): ?>
      <div class="empty-state" style="padding:2rem"><div class="empty-state__icon">📊</div><div class="empty-state__title">Sem leituras no período</div></div>
      <?php else: ?>
      <table class="tabela-estoque">
        <thead><tr><th>Data</th><th>Leituras</th><th>Temp. Média</th><th>Temp. Máx.</th><th>Pressão Média</th><th>Pressão Máx.</th><th>Bar</th></tr></thead>
        <tbody>
        <?php foreach ($relLeiturasPorDia as $r):
          $tClass = $r['max_temp'] > 80 ? 'danger' : ($r['avg_temp'] > 65 ? 'warning' : 'ok');
          $pClass = $r['max_pres'] > 10 ? 'danger' : ($r['avg_pres'] > 8  ? 'warning' : 'ok');
        ?>
        <tr>
          <td style="font-family:var(--font-mono);font-size:.82rem;color:var(--text-dim)"><?= date('d/m/Y (D)', strtotime($r['data'])) ?></td>
          <td style="font-family:var(--font-mono);font-weight:700;color:var(--accent)"><?= $r['total_leituras'] ?></td>
          <td class="text-mono leitura-val <?= $tClass ?>"><?= $r['avg_temp'] ?>°C</td>
          <td class="text-mono leitura-val <?= $tClass ?>"><?= $r['max_temp'] ?>°C</td>
          <td class="text-mono leitura-val <?= $pClass ?>"><?= $r['avg_pres'] ?> bar</td>
          <td class="text-mono leitura-val <?= $pClass ?>"><?= $r['max_pres'] ?> bar</td>
          <td style="width:140px">
            <div class="gauge-bar" style="height:7px">
              <div class="gauge-fill <?= $tClass ?>" style="width:<?= min(100,$r['avg_temp']) ?>%"></div>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Últimas 10 ações do log -->
  <div class="panel-card" style="margin-top:1.25rem">
    <div class="panel-header">
      <div class="panel-title">🗒️ Últimas Ações do Sistema</div>
      <a href="relatorios.php?aba=logs" class="btn btn--ghost btn--sm">Ver log completo</a>
    </div>
    <div style="padding:0">
      <?php if (empty($relLog)): ?>
      <div class="empty-state" style="padding:2rem"><div class="empty-state__icon">📝</div><div class="empty-state__title">Sem registros</div></div>
      <?php else: ?>
      <table class="tabela-estoque">
        <thead><tr><th>Data/Hora</th><th>Usuário</th><th>Ação</th><th>Detalhes</th><th>IP</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($relLog,0,10) as $log): ?>
        <tr>
          <td style="font-family:var(--font-mono);font-size:.72rem;color:var(--text-muted)"><?= date('d/m H:i:s',strtotime($log['criado_em'])) ?></td>
          <td>
            <div style="font-size:.82rem;font-weight:600;color:#fff"><?= htmlspecialchars($log['usuario_nome'] ?? 'Sistema') ?></div>
          </td>
          <td style="font-size:.8rem;font-family:var(--font-mono)"><?= acoesLog($log['acao']) ?></td>
          <td style="font-size:.75rem;color:var(--text-muted);font-family:var(--font-mono);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <?= htmlspecialchars($log['detalhes'] ?? '—') ?>
          </td>
          <td style="font-size:.7rem;color:var(--text-muted);font-family:var(--font-mono)"><?= htmlspecialchars($log['ip'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <?php elseif ($aba === 'alarmes'): ?>
  <!-- ═══════════════════════════════════ ALARMES ═══ -->

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
    <div style="font-size:.8rem;color:var(--text-muted)">
      <?= count($relAlarmesPorEquip) ?> equipamento(s) com histórico de alarmes
    </div>
    <a href="relatorios.php?export=csv&tipo=alarmes" class="btn btn--ghost btn--sm">⬇️ Exportar CSV</a>
  </div>

  <?php if (empty($relAlarmesPorEquip)): ?>
  <div class="empty-state"><div class="empty-state__icon">✅</div><div class="empty-state__title">Nenhum alarme registrado</div></div>
  <?php else: ?>
  <div class="tabela-wrap">
    <table class="tabela-estoque">
      <thead>
        <tr>
          <th>TAG</th><th>Equipamento</th><th>Status</th>
          <th>Total Alarmes</th><th>🔴 Críticos</th><th>⚠️ Alertas</th><th>🔔 Pendentes</th>
          <th>Criticidade</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($relAlarmesPorEquip as $r):
        $maxAlarmes = max(1, array_reduce($relAlarmesPorEquip, fn($c,$x) => max($c,$x['total_alarmes']), 1));
        $pctBar = ($r['total_alarmes'] / $maxAlarmes) * 100;
      ?>
      <tr>
        <td><span class="tag-chip"><?= htmlspecialchars($r['tag']) ?></span></td>
        <td><strong style="color:#fff"><?= htmlspecialchars($r['nome']) ?></strong></td>
        <td><span class="status-badge <?= statusClass($r['status']) ?>"><?= statusLabel($r['status']) ?></span></td>
        <td style="font-family:var(--font-mono);font-weight:700;font-size:1rem;color:var(--accent)"><?= $r['total_alarmes'] ?></td>
        <td style="font-family:var(--font-mono);color:<?= $r['criticos']>0?'var(--red)':'var(--text-muted)' ?>;font-weight:<?= $r['criticos']>0?700:400 ?>">
          <?= $r['criticos'] > 0 ? '🔴 '.$r['criticos'] : '—' ?>
        </td>
        <td style="font-family:var(--font-mono);color:<?= $r['alertas']>0?'var(--yellow)':'var(--text-muted)' ?>;font-weight:<?= $r['alertas']>0?700:400 ?>">
          <?= $r['alertas'] > 0 ? '⚠️ '.$r['alertas'] : '—' ?>
        </td>
        <td>
          <?php if ($r['pendentes'] > 0): ?>
          <span style="background:var(--red-dim);color:var(--red);padding:2px 8px;border-radius:4px;font-size:.75rem;font-family:var(--font-mono);font-weight:700">🔔 <?= $r['pendentes'] ?> pendente(s)</span>
          <?php else: ?>
          <span style="background:var(--green-dim);color:var(--green);padding:2px 8px;border-radius:4px;font-size:.7rem;font-family:var(--font-mono)">✅ Resolvidos</span>
          <?php endif; ?>
        </td>
        <td style="width:120px">
          <div style="display:flex;align-items:center;gap:.5rem">
            <div class="gauge-bar" style="flex:1;height:7px">
              <div class="gauge-fill <?= $r['criticos']>0?'danger':($r['alertas']>0?'warning':'ok') ?>" style="width:<?= $pctBar ?>%"></div>
            </div>
            <span style="font-size:.68rem;color:var(--text-muted);font-family:var(--font-mono);min-width:28px"><?= number_format($pctBar,0) ?>%</span>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <?php elseif ($aba === 'leituras'): ?>
  <!-- ═══════════════════════════════════ LEITURAS ═══ -->

  <div style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap;align-items:center">
    <?php foreach ([7,14,30,90] as $d): ?>
    <a href="relatorios.php?aba=leituras&dias=<?= $d ?>"
       class="btn btn--<?= $diasFiltro===$d?'primary':'ghost' ?> btn--sm">
      <?= $d ?> dias
    </a>
    <?php endforeach; ?>
    <span style="margin-left:auto">
      <a href="relatorios.php?export=csv&tipo=leituras" class="btn btn--ghost btn--sm">⬇️ Exportar CSV</a>
    </span>
  </div>

  <?php if (empty($relLeiturasPorDia)): ?>
  <div class="empty-state"><div class="empty-state__icon">📊</div><div class="empty-state__title">Sem leituras no período de <?= $diasFiltro ?> dias</div></div>
  <?php else: ?>

  <!-- Mini-gráfico de barras para temperatura média -->
  <?php
    $maxTemp = max(1, array_reduce($relLeiturasPorDia, fn($c,$r) => max($c,(float)$r['avg_temp']), 0));
    $maxPres = max(1, array_reduce($relLeiturasPorDia, fn($c,$r) => max($c,(float)$r['avg_pres']), 0));
    $dados   = array_reverse($relLeiturasPorDia);
  ?>
  <div class="panel-card" style="margin-bottom:1.25rem">
    <div class="panel-header">
      <div class="panel-title">📈 Temperatura Média por Dia</div>
      <span style="font-size:.7rem;color:var(--text-muted);font-family:var(--font-mono)"><?= $nomeDias[$diasFiltro] ?></span>
    </div>
    <div class="panel-body">
      <div style="display:flex;align-items:flex-end;gap:4px;height:100px;padding-bottom:.5rem;border-bottom:1px solid var(--border)">
        <?php foreach ($dados as $r):
          $h = max(4, ($r['avg_temp'] / max(1,$maxTemp)) * 100);
          $cls = $r['avg_temp'] > 70 ? 'danger' : ($r['avg_temp'] > 50 ? 'warning' : 'ok');
        ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;min-width:20px" title="<?= $r['data'] ?>: <?= $r['avg_temp'] ?>°C">
          <span style="font-size:.58rem;color:var(--text-muted);font-family:var(--font-mono)"><?= number_format($r['avg_temp'],0) ?>°</span>
          <div class="gauge-fill <?= $cls ?>" style="width:100%;height:<?= $h ?>%;border-radius:3px 3px 0 0;min-height:4px"></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;gap:4px;padding-top:.4rem">
        <?php foreach ($dados as $r): ?>
        <div style="flex:1;text-align:center;font-size:.58rem;color:var(--text-muted);font-family:var(--font-mono)">
          <?= date('d/m', strtotime($r['data'])) ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="tabela-wrap">
    <table class="tabela-estoque">
      <thead><tr>
        <th>Data</th><th>Leituras</th>
        <th>Temp. Média</th><th>Temp. Máxima</th>
        <th>Pressão Média</th><th>Pressão Máxima</th>
      </tr></thead>
      <tbody>
      <?php foreach ($relLeiturasPorDia as $r):
        $tC = $r['max_temp'] > 80 ? 'danger' : ($r['avg_temp'] > 60 ? 'warning' : 'ok');
        $pC = $r['max_pres'] > 11 ? 'danger' : ($r['avg_pres'] > 8  ? 'warning' : 'ok');
      ?>
      <tr>
        <td style="font-family:var(--font-mono);font-size:.82rem"><?= date('d/m/Y (D)', strtotime($r['data'])) ?></td>
        <td>
          <span style="font-family:var(--font-mono);font-size:.9rem;font-weight:700;color:var(--accent)"><?= $r['total_leituras'] ?></span>
          <span style="font-size:.68rem;color:var(--text-muted)"> leituras</span>
        </td>
        <td class="text-mono leitura-val <?= $tC ?>"><?= $r['avg_temp'] ?>°C</td>
        <td>
          <span class="text-mono leitura-val <?= $tC ?>"><?= $r['max_temp'] ?>°C</span>
          <?php if ($r['max_temp'] > 80): ?>
          <span style="margin-left:.3rem;font-size:.65rem;color:var(--red)">⚠️ Acima do limite</span>
          <?php endif; ?>
        </td>
        <td class="text-mono leitura-val <?= $pC ?>"><?= $r['avg_pres'] ?> bar</td>
        <td>
          <span class="text-mono leitura-val <?= $pC ?>"><?= $r['max_pres'] ?> bar</span>
          <?php if ($r['max_pres'] > 11): ?>
          <span style="margin-left:.3rem;font-size:.65rem;color:var(--red)">⚠️ Acima do limite</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <?php elseif ($aba === 'manutencao'): ?>
  <!-- ═══════════════════════════════════ MANUTENÇÃO ═══ -->

  <div style="margin-bottom:1rem">
    <div class="alerta alerta--aviso">
      ⚠️ Equipamentos abaixo não receberam leituras nas últimas <strong>24 horas</strong> ou nunca foram monitorados.
    </div>
  </div>

  <?php if (empty($relEquipSemLeitura)): ?>
  <div class="empty-state"><div class="empty-state__icon">✅</div><div class="empty-state__title">Todos os equipamentos com leituras recentes</div><div class="empty-state__desc">Sistema operando normalmente nas últimas 24h</div></div>
  <?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem">
    <?php foreach ($relEquipSemLeitura as $r): ?>
    <div class="panel-card" style="border-color:rgba(245,158,11,.25)">
      <div class="panel-body">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.75rem">
          <div>
            <div style="font-family:var(--font-display);font-size:.95rem;font-weight:700;color:#fff;text-transform:uppercase"><?= htmlspecialchars($r['nome']) ?></div>
            <span class="tag-chip"><?= htmlspecialchars($r['tag']) ?></span>
          </div>
          <span class="status-badge <?= statusClass($r['status']) ?>"><?= statusLabel($r['status']) ?></span>
        </div>
        <?php if ($r['localizacao']): ?>
        <div style="font-size:.75rem;color:var(--text-muted);font-family:var(--font-mono);margin-bottom:.5rem">📍 <?= htmlspecialchars($r['localizacao']) ?></div>
        <?php endif; ?>
        <div style="font-size:.72rem;font-family:var(--font-mono)">
          <span style="color:var(--text-muted)">Última leitura: </span>
          <span style="color:var(--yellow);font-weight:600">
            <?= $r['ultima_leitura'] ? date('d/m/Y H:i', strtotime($r['ultima_leitura'])) : '— Nunca registrada —' ?>
          </span>
        </div>
        <div style="margin-top:.75rem;display:flex;gap:.5rem">
          <a href="monitoramento.php?equip=<?= $r['id'] ?>" class="btn btn--warning btn--sm" style="flex:1;justify-content:center">📡 Verificar</a>
          <?php if (ehGestor()): ?>
          <a href="monitoramento.php?equip=<?= $r['id'] ?>" class="btn btn--ghost btn--sm">➕ Leitura</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php elseif ($aba === 'usuarios'): ?>
  <!-- ═══════════════════════════════════ USUÁRIOS ═══ -->

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;margin-bottom:1.5rem">
    <?php foreach ($relUsuarios as $r):
      $configs = [
        'admin'       => ['👑','kpi-card--purple','Admins'],
        'staff'       => ['🛡️','kpi-card--blue','Staff'],
        'funcionario' => ['👤','kpi-card--cyan','Funcionários'],
      ];
      [$ico,$cc,$lbl] = $configs[$r['perfil']] ?? ['👤','kpi-card--cyan','Outros'];
    ?>
    <div class="kpi-card <?= $cc ?>">
      <div class="kpi-icon"><?= $ico ?></div>
      <div>
        <div class="kpi-label"><?= $lbl ?></div>
        <div class="kpi-valor"><?= $r['total'] ?></div>
        <div style="font-size:.72rem;color:var(--text-muted);font-family:var(--font-mono);margin-top:.2rem">
          <?= $r['ativos'] ?> ativo(s)
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($relUsuarios)): ?>
    <div class="panel-card"><div class="panel-body"><div class="empty-state" style="padding:2rem"><div class="empty-state__icon">👥</div><div class="empty-state__title">Sem dados de usuários</div></div></div></div>
    <?php endif; ?>
  </div>

  <!-- Tabela de usuários -->
  <?php
    $todosUsers = dbListarUsuarios();
  ?>
  <div class="panel-card">
    <div class="panel-header">
      <div class="panel-title">👥 Lista de Usuários</div>
      <span style="font-size:.72rem;color:var(--text-muted);font-family:var(--font-mono)"><?= count($todosUsers) ?> usuários</span>
    </div>
    <div style="padding:0">
      <?php if (empty($todosUsers)): ?>
      <div class="empty-state" style="padding:2rem"><div class="empty-state__icon">👥</div><div class="empty-state__title">Nenhum usuário cadastrado</div></div>
      <?php else: ?>
      <table class="tabela-estoque">
        <thead><tr><th>Usuário</th><th>E-mail</th><th>Perfil</th><th>Status</th><th>Último Acesso</th><th>Cadastro</th></tr></thead>
        <tbody>
        <?php foreach ($todosUsers as $u): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:.6rem">
              <div class="user-avatar" style="width:30px;height:30px;font-size:.72rem;flex-shrink:0"><?= inicialNome($u['nome']) ?></div>
              <strong style="color:#fff;font-size:.85rem"><?= htmlspecialchars($u['nome']) ?></strong>
            </div>
          </td>
          <td style="font-size:.78rem;font-family:var(--font-mono);color:var(--text-dim)"><?= htmlspecialchars($u['email']) ?></td>
          <td><?= perfilBadgeHtml($u['perfil']) ?></td>
          <td>
            <?php if ($u['ativo']): ?>
            <span class="status-badge status-ativo">✅ Ativo</span>
            <?php else: ?>
            <span class="status-badge status-inativo">⏸️ Inativo</span>
            <?php endif; ?>
          </td>
          <td style="font-size:.72rem;color:var(--text-muted);font-family:var(--font-mono)">
            <?= $u['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($u['ultimo_acesso'])) : '—' ?>
          </td>
          <td style="font-size:.72rem;color:var(--text-muted);font-family:var(--font-mono)">
            <?= date('d/m/Y', strtotime($u['criado_em'])) ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <?php elseif ($aba === 'logs'): ?>
  <!-- ═══════════════════════════════════ LOG DO SISTEMA ═══ -->

  <?php if (!ehAdmin()): ?>
  <div class="alerta alerta--aviso">🛡️ Staff tem acesso de leitura ao log. Apenas o Admin pode ver todos os registros.</div>
  <?php endif; ?>

  <!-- Filtros -->
  <form method="GET" action="relatorios.php" style="display:flex;gap:.75rem;margin-bottom:1.25rem;flex-wrap:wrap;align-items:flex-end">
    <input type="hidden" name="aba" value="logs">
    <div style="flex:1;min-width:180px">
      <label class="form-label" style="display:block;margin-bottom:.3rem">Filtrar por ação</label>
      <input type="text" name="acao_f" class="form-control" placeholder="Ex: LOGIN, CRIAR..." value="<?= htmlspecialchars($_GET['acao_f'] ?? '') ?>">
    </div>
    <div style="flex:1;min-width:180px">
      <label class="form-label" style="display:block;margin-bottom:.3rem">Filtrar por usuário</label>
      <input type="text" name="user_f" class="form-control" placeholder="Nome do usuário..." value="<?= htmlspecialchars($_GET['user_f'] ?? '') ?>">
    </div>
    <div>
      <label class="form-label" style="display:block;margin-bottom:.3rem">Exibir</label>
      <select name="lim" class="form-control" style="width:auto">
        <?php foreach ([25=>25,50=>50,100=>100,200=>200] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= ($limLog??50)===$v?'selected':'' ?>><?= $l ?> registros</option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn--primary">Filtrar</button>
    <a href="relatorios.php?aba=logs" class="btn btn--ghost">✕ Limpar</a>
    <a href="relatorios.php?export=csv&tipo=log" class="btn btn--ghost btn--sm" style="align-self:flex-end">⬇️ CSV</a>
  </form>

  <!-- Atalhos rápidos de ação -->
  <div style="display:flex;gap:.4rem;margin-bottom:1rem;flex-wrap:wrap">
    <?php foreach (['LOGIN','LOGOUT','CRIAR_EQUIPAMENTO','EDITAR_EQUIPAMENTO','EXCLUIR_EQUIPAMENTO','ALTERAR_STATUS','RESOLVER_ALARME','CRIAR_USUARIO','EDITAR_USUARIO','EXCLUIR_USUARIO'] as $acaoBtn): ?>
    <a href="relatorios.php?aba=logs&acao_f=<?= urlencode($acaoBtn) ?>"
       class="btn btn--ghost btn--sm <?= (($_GET['acao_f']??'') === $acaoBtn)?'btn--primary':'' ?>"
       style="font-family:var(--font-mono);font-size:.68rem;letter-spacing:.03em">
      <?= acoesLog($acaoBtn) ?>
    </a>
    <?php endforeach; ?>
  </div>

  <?php
    $logExibir = array_values($relLog);
  ?>

  <?php if (empty($logExibir)): ?>
  <div class="empty-state"><div class="empty-state__icon">📝</div><div class="empty-state__title">Nenhum registro encontrado</div><div class="empty-state__desc">Tente remover os filtros.</div></div>
  <?php else: ?>
  <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem;font-family:var(--font-mono)">
    Exibindo <?= count($logExibir) ?> registro(s)
  </div>
  <div class="tabela-wrap">
    <table class="tabela-estoque">
      <thead>
        <tr>
          <th>#</th>
          <th>Data/Hora</th>
          <th>Usuário</th>
          <th>Perfil</th>
          <th>Ação</th>
          <th>Tabela</th>
          <th>Detalhes</th>
          <th>IP</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($logExibir as $log):
        $corAcao = match(true) {
          str_contains($log['acao'],'EXCLUIR') => 'var(--red)',
          str_contains($log['acao'],'CRIAR')   => 'var(--green)',
          str_contains($log['acao'],'EDITAR')  => 'var(--yellow)',
          str_contains($log['acao'],'LOGIN')   => 'var(--accent)',
          str_contains($log['acao'],'LOGOUT')  => 'var(--text-muted)',
          default => 'var(--text)',
        };
      ?>
      <tr>
        <td style="font-family:var(--font-mono);font-size:.7rem;color:var(--text-muted)"><?= $log['id'] ?></td>
        <td style="font-family:var(--font-mono);font-size:.72rem;color:var(--text-muted);white-space:nowrap">
          <?= date('d/m/Y', strtotime($log['criado_em'])) ?><br>
          <strong style="color:var(--text-dim)"><?= date('H:i:s', strtotime($log['criado_em'])) ?></strong>
        </td>
        <td>
          <div style="font-size:.82rem;font-weight:600;color:#fff"><?= htmlspecialchars($log['usuario_nome'] ?? 'Sistema') ?></div>
        </td>
        <td>
          <?php
            try {
              $perfLog = dbBuscarUsuario((int)($log['usuario_id']??0));
              if ($perfLog) echo perfilBadgeHtml($perfLog['perfil']);
              else echo '<span style="color:var(--text-muted);font-size:.7rem">—</span>';
            } catch(Throwable $e) { echo '—'; }
          ?>
        </td>
        <td>
          <span style="font-family:var(--font-mono);font-size:.78rem;font-weight:600;color:<?= $corAcao ?>">
            <?= acoesLog($log['acao']) ?>
          </span>
        </td>
        <td style="font-size:.72rem;color:var(--text-muted);font-family:var(--font-mono)"><?= htmlspecialchars($log['tabela_afetada'] ?? '—') ?></td>
        <td style="font-size:.73rem;color:var(--text-muted);font-family:var(--font-mono);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($log['detalhes'] ?? '') ?>">
          <?= htmlspecialchars($log['detalhes'] ?? '—') ?>
        </td>
        <td style="font-size:.68rem;color:var(--text-muted);font-family:var(--font-mono)"><?= htmlspecialchars($log['ip'] ?? '—') ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <?php endif; ?><!-- fim abas -->

</main>
<?php require_once 'footer.php'; ?>
</body>
</html>