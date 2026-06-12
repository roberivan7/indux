<?php
require_once 'icon.php';
require_once 'init.php';
require_once 'db.php';
requerLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_leitura']) && ehGestor()) {
    $equipamentoId = (int)$_POST['equip_id'];
    $temperatura   = (float)str_replace(',', '.', $_POST['temperatura'] ?? 0);
    $pressao       = (float)str_replace(',', '.', $_POST['pressao']     ?? 0);
    $umidade       = (isset($_POST['umidade']) && $_POST['umidade'] !== '')
                     ? (float)str_replace(',', '.', $_POST['umidade']) : null;

    try {
        $db = getDB();
        $db->prepare(
            'INSERT INTO leituras_sensor (equipamento_id, temperatura, pressao, umidade, registrado_em)
             VALUES (?, ?, ?, ?, NOW())'
        )->execute([$equipamentoId, $temperatura, $pressao, $umidade]);

        $equipamentoBase = dbBuscarEquipamento($equipamentoId);
        if ($equipamentoBase) {
            $alarmesGerados = [];
            if ($temperatura < $equipamentoBase['temp_min']) {
                $alarmesGerados[] = ['temperatura','critico',
                    "Temperatura abaixo do limite: {$temperatura}°C (mín: {$equipamentoBase['temp_min']}°C)",
                    $temperatura, $equipamentoBase['temp_min']];
            } elseif ($temperatura > $equipamentoBase['temp_max']) {
                $alarmesGerados[] = ['temperatura','critico',
                    "Temperatura acima do limite: {$temperatura}°C (máx: {$equipamentoBase['temp_max']}°C)",
                    $temperatura, $equipamentoBase['temp_max']];
            } elseif ($temperatura > ($equipamentoBase['temp_max'] * 0.85)) {
                $alarmesGerados[] = ['temperatura','alerta',
                    "Temperatura em zona de alerta: {$temperatura}°C",
                    $temperatura, $equipamentoBase['temp_max']];
            }
            if ($pressao < $equipamentoBase['pressao_min']) {
                $alarmesGerados[] = ['pressao','critico',
                    "Pressão abaixo do limite: {$pressao} bar (mín: {$equipamentoBase['pressao_min']} bar)",
                    $pressao, $equipamentoBase['pressao_min']];
            } elseif ($pressao > $equipamentoBase['pressao_max']) {
                $alarmesGerados[] = ['pressao','critico',
                    "Pressão acima do limite crítico: {$pressao} bar (máx: {$equipamentoBase['pressao_max']} bar)",
                    $pressao, $equipamentoBase['pressao_max']];
            } elseif ($pressao > ($equipamentoBase['pressao_max'] * 0.85)) {
                $alarmesGerados[] = ['pressao','alerta',
                    "Pressão em zona de alerta: {$pressao} bar",
                    $pressao, $equipamentoBase['pressao_max']];
            }
            foreach ($alarmesGerados as [$tipo,$sev,$mens,$valR,$valL]) {
                $db->prepare(
                    'INSERT INTO alarmes (equipamento_id,tipo,severidade,mensagem,valor_registrado,valor_limite)
                     VALUES (?,?,?,?,?,?)'
                )->execute([$equipamentoId,$tipo,$sev,$mens,$valR,$valL]);
            }
            $temCritico = count(array_filter($alarmesGerados, fn($a) => $a[1] === 'critico')) > 0;
            if ($temCritico) {
                $db->prepare("UPDATE equipamentos SET status='em_falha' WHERE id=?")
                   ->execute([$equipamentoId]);
            }
        }
        registrarLog('REGISTRAR_LEITURA', 'leituras_sensor', $equipamentoId, "T:{$temperatura}°C P:{$pressao}bar");
        header('Location: monitoramento.php?msg=leitura_ok&equip=' . $equipamentoId);
        exit;
    } catch (Throwable $e) {
        $erroForm = 'Erro ao registrar leitura: ' . $e->getMessage();
    }
}

$equipamentos = dbListarEquipamentos();

if (empty($equipamentos)) {
    $equipamentos = [
        ['id'=>1,'tag'=>'CLD-001','nome'=>'Caldeira Principal','status'=>'ativo',
         'localizacao'=>'Sala 01','temp_min'=>20,'temp_max'=>95,'pressao_min'=>0,'pressao_max'=>15,
         'ultima_temp'=>72.4,'ultima_pressao'=>8.1,'ultima_umidade'=>55.0,
         'ultima_leitura'=>date('Y-m-d H:i:s',time()-120),'qtd_alarmes'=>0,'modelo'=>'CBR-5000'],
        ['id'=>2,'tag'=>'CMP-002','nome'=>'Compressor Industrial','status'=>'em_falha',
         'localizacao'=>'Área B','temp_min'=>15,'temp_max'=>70,'pressao_min'=>0,'pressao_max'=>12,
         'ultima_temp'=>88.9,'ultima_pressao'=>13.2,'ultima_umidade'=>null,
         'ultima_leitura'=>date('Y-m-d H:i:s',time()-60),'qtd_alarmes'=>2,'modelo'=>'AIR-2400'],
        ['id'=>3,'tag'=>'BBA-003','nome'=>'Bomba Hidráulica','status'=>'ativo',
         'localizacao'=>'Subsolo','temp_min'=>10,'temp_max'=>65,'pressao_min'=>1,'pressao_max'=>10,
         'ultima_temp'=>45.0,'ultima_pressao'=>6.5,'ultima_umidade'=>null,
         'ultima_leitura'=>date('Y-m-d H:i:s',time()-300),'qtd_alarmes'=>0,'modelo'=>'HYD-800'],
        ['id'=>4,'tag'=>'TRF-004','nome'=>'Transformador Elétrico','status'=>'inativo',
         'localizacao'=>'Subestação','temp_min'=>0,'temp_max'=>85,'pressao_min'=>0,'pressao_max'=>5,
         'ultima_temp'=>null,'ultima_pressao'=>null,'ultima_umidade'=>null,
         'ultima_leitura'=>null,'qtd_alarmes'=>0,'modelo'=>'TRF-500KVA'],
    ];
}


$equipFiltro      = (int)($_GET['equip'] ?? 0);
$msg              = $_GET['msg'] ?? '';
$equipSelecionado = null;
$leituras         = [];
$historicoHoras   = [];

if ($equipFiltro > 0) {

    try {
        $db = getDB();
        $stmtEquip = $db->prepare(
            "SELECT e.*,
                (SELECT ls.temperatura   FROM leituras_sensor ls WHERE ls.equipamento_id=e.id ORDER BY ls.id DESC LIMIT 1) AS ultima_temp,
                (SELECT ls.pressao       FROM leituras_sensor ls WHERE ls.equipamento_id=e.id ORDER BY ls.id DESC LIMIT 1) AS ultima_pressao,
                (SELECT ls.umidade       FROM leituras_sensor ls WHERE ls.equipamento_id=e.id ORDER BY ls.id DESC LIMIT 1) AS ultima_umidade,
                (SELECT ls.registrado_em FROM leituras_sensor ls WHERE ls.equipamento_id=e.id ORDER BY ls.id DESC LIMIT 1) AS ultima_leitura,
                (SELECT COUNT(*) FROM alarmes a WHERE a.equipamento_id=e.id AND a.resolvido=0 AND e.status <> 'inativo') AS qtd_alarmes
             FROM equipamentos e
             WHERE e.id = ?"
        );
        $stmtEquip->execute([$equipFiltro]);
        $equipSelecionado = $stmtEquip->fetch() ?: null;
    } catch (Throwable $e) {
        // Busca no array de demo pelo ID correto
        foreach ($equipamentos as $eq) {
            if ((int)$eq['id'] === $equipFiltro) {
                $equipSelecionado = $eq;
                break;
            }
        }
    }

    try {
        $stmtLeituras = getDB()->prepare(
            'SELECT * FROM leituras_sensor WHERE equipamento_id = ?
             ORDER BY registrado_em DESC LIMIT 20'
        );
        $stmtLeituras->execute([$equipFiltro]);
        $leituras = $stmtLeituras->fetchAll();
    } catch (Throwable $e) {
        // Demo: gera leituras simuladas para o equipamento correto
        if ($equipSelecionado) {
            $baseTemp = (float)($equipSelecionado['ultima_temp'] ?? 50);
            $basePres = (float)($equipSelecionado['ultima_pressao'] ?? 5);
            for ($i = 0; $i < 5; $i++) {
                $leituras[] = [
                    'id'           => $i + 1,
                    'temperatura'  => round($baseTemp - $i * 2.3 + rand(-5,5)*0.1, 1),
                    'pressao'      => round($basePres - $i * 0.2 + rand(-3,3)*0.1, 2),
                    'umidade'      => $equipSelecionado['ultima_umidade'] !== null
                                      ? round((float)$equipSelecionado['ultima_umidade'] - $i, 1) : null,
                    'registrado_em'=> date('Y-m-d H:i:s', time() - $i * 300),
                ];
            }
        }
    }

    try {
        $stmtHist = getDB()->prepare(
            "SELECT DATE_FORMAT(registrado_em,'%H:00') as hora,
                    ROUND(AVG(temperatura),2) as avg_temp,
                    ROUND(AVG(pressao),2)     as avg_pres,
                    COUNT(*) as qtd
             FROM leituras_sensor
             WHERE equipamento_id = ?
               AND registrado_em >= NOW() - INTERVAL 24 HOUR
             GROUP BY hora ORDER BY hora"
        );
        $stmtHist->execute([$equipFiltro]);
        $historicoHoras = $stmtHist->fetchAll();
    } catch (Throwable $e) {
        $historicoHoras = [];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo $icon; ?>
<title>Indux | Monitoramento</title>
  <link rel="stylesheet" href="../CSS/styles.css">
  <?php if ($equipFiltro): ?>
  <meta http-equiv="refresh" content="30">
  <?php endif; ?>
</head>
<body>
<?php require_once 'header.php'; ?>

<main class="site-main">
  <div class="page-header">
    <div class="page-header-left">
      <div class="page-icon">{{lucide:radio}}</div>
      <div>
        <div class="breadcrumb">
          <span>INDUX</span> /
          <?php if ($equipFiltro && $equipSelecionado): ?>
          <a href="monitoramento.php">Monitoramento</a> /
          <span><?= htmlspecialchars($equipSelecionado['nome']) ?></span>
          <?php else: ?>
          <span>Monitoramento</span>
          <?php endif; ?>
        </div>
        <h1 class="page-title">Monitoramento</h1>
        <p class="page-subtitle">Supervisão em tempo real de temperatura, pressão e status</p>
      </div>
    </div>
    <?php if ($equipFiltro): ?>
    <div style="display:flex;gap:.5rem;align-items:center">
      <span style="font-size:.7rem;color:var(--green);font-family:var(--font-mono);animation:pulse 2s infinite">● AUTO-REFRESH 30s</span>
      <a href="monitoramento.php" class="btn btn--ghost btn--sm">← Todos</a>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($msg === 'leitura_ok'): ?>
  <div class="alerta alerta--sucesso">Leitura registrada com sucesso. Alarmes verificados automaticamente.</div>
  <?php elseif ($msg === 'alarme_resolvido'): ?>
  <div class="alerta alerta--sucesso">Alarme resolvido. Uma leitura estavel foi registrada para este equipamento.</div>
  <?php endif; ?>
  <?php if (isset($erroForm)): ?>
  <div class="alerta alerta--erro"><?= htmlspecialchars($erroForm) ?></div>
  <?php endif; ?>

  <?php if (!$equipFiltro): ?>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem">
    <?php foreach ($equipamentos as $equipamento):
      $cT  = $equipamento['ultima_temp']    !== null ? avaliarTemp($equipamento['ultima_temp'],    $equipamento['temp_min'],    $equipamento['temp_max'])    : null;
      $cP  = $equipamento['ultima_pressao'] !== null ? avaliarPressao($equipamento['ultima_pressao'],$equipamento['pressao_min'],$equipamento['pressao_max']) : null;
      $pT  = $equipamento['ultima_temp']    !== null ? pctBar($equipamento['ultima_temp'],    $equipamento['temp_min'],    $equipamento['temp_max'])    : 0;
      $pP  = $equipamento['ultima_pressao'] !== null ? pctBar($equipamento['ultima_pressao'],$equipamento['pressao_min'],$equipamento['pressao_max']) : 0;
    ?>
<div class="equip-card <?= $equipamento['status'] ?> <?= ($equipamento['status'] === 'inativo') ? 'card-inativo' : '' ?>">
      <div class="equip-card__header">
        <div>
          <div class="equip-card__title"><?= htmlspecialchars($equipamento['nome']) ?></div>
          <div style="display:flex;gap:.5rem;margin-top:.2rem;flex-wrap:wrap">
            <span class="tag-chip"><?= htmlspecialchars($equipamento['tag']) ?></span>
            <?php if ($equipamento['localizacao']): ?>
            <span class="inline-icon-text" style="font-size:.7rem;color:var(--text-muted)">{{lucide:map-pin}} <?= htmlspecialchars($equipamento['localizacao']) ?></span>
            <?php endif; ?>
          </div>
        </div>
        <span class="status-badge <?= statusClass($equipamento['status']) ?>"><?= statusLabel($equipamento['status']) ?></span>
      </div>

      <?php if ($equipamento['ultima_temp'] !== null): ?>
      <div class="equip-card__metrics">
        <div class="metric-item">
          <div class="metric-label">{{lucide:thermometer}} Temperatura</div>
          <div class="metric-value <?= $cT ?>"><?= number_format($equipamento['ultima_temp'],1) ?>°C</div>
        </div>
        <div class="metric-item">
          <div class="metric-label">{{lucide:settings}} Pressão</div>
          <div class="metric-value <?= $cP ?>"><?= number_format($equipamento['ultima_pressao'],2) ?> bar</div>
        </div>
        <?php if ($equipamento['ultima_umidade'] !== null): ?>
        <div class="metric-item">
          <div class="metric-label">{{lucide:droplet}} Umidade</div>
          <div class="metric-value ok"><?= number_format($equipamento['ultima_umidade'],1) ?>%</div>
        </div>
        <?php endif; ?>
        <?php if ($equipamento['status'] !== 'inativo' && $equipamento['qtd_alarmes'] > 0): ?>
        <div class="metric-item" style="border-color:rgba(239,68,68,.3)">
          <div class="metric-label">{{lucide:bell}} Alarmes</div>
          <div class="metric-value danger"><?= $equipamento['qtd_alarmes'] ?> ativo(s)</div>
        </div>
        <?php endif; ?>
      </div>
      <div class="gauge-wrap">
        <div class="gauge-label"><span>Temperatura</span><span><?= number_format($pT,0) ?>% do limite</span></div>
        <div class="gauge-bar"><div class="gauge-fill <?= $cT ?>" style="width:<?= $pT ?>%"></div></div>
      </div>
      <div class="gauge-wrap" style="margin-top:.5rem">
        <div class="gauge-label"><span>Pressão</span><span><?= number_format($pP,0) ?>% do limite</span></div>
        <div class="gauge-bar"><div class="gauge-fill <?= $cP ?>" style="width:<?= $pP ?>%"></div></div>
      </div>
      <div class="inline-icon-text" style="font-size:.68rem;color:var(--text-muted);font-family:var(--font-mono);margin-top:.6rem">
        {{lucide:clock-3}} Última leitura: <?= $equipamento['ultima_leitura'] ? date('d/m H:i:s', strtotime($equipamento['ultima_leitura'])) : '—' ?>
      </div>
      <?php else: ?>
      <div style="text-align:center;padding:1.5rem 0;color:var(--text-muted);font-size:.82rem;font-family:var(--font-mono)">
        — Sem leituras registradas —
      </div>
      <?php endif; ?>

      <div class="equip-card__actions">
        <a href="monitoramento.php?equip=<?= $equipamento['id'] ?>" class="btn btn--primary btn--sm" style="flex:1;justify-content:center">
          Detalhes
        </a>
        <?php if ($equipamento['status'] !== 'inativo' && $equipamento['qtd_alarmes'] > 0): ?>
        <a href="alarmes.php" class="btn btn--danger btn--sm">{{lucide:bell}} <?= $equipamento['qtd_alarmes'] ?></a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($equipamentos)): ?>
    <div class="empty-state" style="grid-column:1/-1">
      <div class="empty-state__icon">{{lucide:radio}}</div>
      <div class="empty-state__title">Nenhum equipamento cadastrado</div>
      <?php if (podeCriarEquip()): ?>
      <a href="novo-equipamento.php" class="btn btn--primary" style="margin-top:1rem">{{lucide:plus}} Cadastrar Equipamento</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <?php else: ?>


  <?php if (!$equipSelecionado): ?>
    <div class="alerta alerta--erro">{{lucide:triangle-alert}} Equipamento não encontrado (ID: <?= $equipFiltro ?>).</div>
    <a href="monitoramento.php" class="btn btn--ghost" style="margin-top:1rem">← Voltar para todos</a>
  <?php else:
    $eq       = $equipSelecionado;
    $cTA      = $eq['ultima_temp']    !== null ? avaliarTemp($eq['ultima_temp'],       $eq['temp_min'],    $eq['temp_max'])    : 'ok';
    $cPA      = $eq['ultima_pressao'] !== null ? avaliarPressao($eq['ultima_pressao'], $eq['pressao_min'], $eq['pressao_max']) : 'ok';
    $pTA      = $eq['ultima_temp']    !== null ? pctBar($eq['ultima_temp'],    $eq['temp_min'], $eq['temp_max'])    : 0;
    $pPA      = $eq['ultima_pressao'] !== null ? pctBar($eq['ultima_pressao'], $eq['pressao_min'], $eq['pressao_max']) : 0;
  ?>

  <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-xl);padding:1.5rem;margin-bottom:1.25rem;display:flex;gap:1.25rem;align-items:center;flex-wrap:wrap">
    <div style="flex:1;min-width:200px">
      <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem">
        <span class="tag-chip" style="font-size:.85rem"><?= htmlspecialchars($eq['tag']) ?></span>
        <span class="status-badge <?= statusClass($eq['status']) ?>"><?= statusLabel($eq['status']) ?></span>
      </div>
      <h2 style="font-family:var(--font-display);font-size:1.4rem;font-weight:800;color:#fff;text-transform:uppercase">
        <?= htmlspecialchars($eq['nome']) ?>
      </h2>
      <div class="inline-icon-text" style="font-size:.78rem;color:var(--text-muted);font-family:var(--font-mono);margin-top:.3rem">
        <?php if ($eq['modelo']): ?>{{lucide:box}} <?= htmlspecialchars($eq['modelo']) ?> &nbsp;<?php endif; ?>
        <?php if ($eq['localizacao']): ?>{{lucide:map-pin}} <?= htmlspecialchars($eq['localizacao']) ?><?php endif; ?>
      </div>
    </div>

    <div style="display:flex;gap:1rem;flex-wrap:wrap">
      <?php foreach ([
        ['Temperatura', $eq['ultima_temp']!==null ? number_format($eq['ultima_temp'],1).'°C' : '—', $cTA, 'Lim: '.$eq['temp_max'].'°C'],
        ['Pressão',     $eq['ultima_pressao']!==null ? number_format($eq['ultima_pressao'],2).' bar' : '—', $cPA, 'Lim: '.$eq['pressao_max'].' bar'],
      ] as [$kLabel,$kVal,$kClass,$kSub]):
        $cor = $kClass==='ok'?'green':($kClass==='warning'?'yellow':'red');
      ?>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:.75rem 1.25rem;text-align:center;min-width:110px">
        <div style="font-size:.65rem;color:var(--text-muted);font-family:var(--font-mono);text-transform:uppercase;margin-bottom:.25rem"><?= $kLabel ?></div>
        <div style="font-family:var(--font-display);font-size:1.6rem;font-weight:800;color:var(--<?= $cor ?>)"><?= $kVal ?></div>
        <div style="font-size:.65rem;color:var(--text-muted);font-family:var(--font-mono)"><?= $kSub ?></div>
      </div>
      <?php endforeach; ?>
      <?php if (!empty($leituras)): ?>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:.75rem 1.25rem;text-align:center;min-width:110px">
        <div style="font-size:.65rem;color:var(--text-muted);font-family:var(--font-mono);text-transform:uppercase;margin-bottom:.25rem">Leituras</div>
        <div style="font-family:var(--font-display);font-size:1.6rem;font-weight:800;color:var(--accent)"><?= count($leituras) ?></div>
        <div style="font-size:.65rem;color:var(--text-muted);font-family:var(--font-mono)">últimas 20</div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem">
    <?php foreach ([
      ['{{lucide:thermometer}} TEMPERATURA','°C',$pTA,$cTA,$eq['ultima_temp'],$eq['temp_min'],$eq['temp_max']],
      ['{{lucide:settings}} PRESSÃO',    'bar',$pPA,$cPA,$eq['ultima_pressao'],$eq['pressao_min'],$eq['pressao_max']],
    ] as [$titulo,$unidade,$pct,$classe,$valor,$vMin,$vMax]):
      $angulo = min(270, ($pct / 100) * 270);
      $raio = 54; $cx = 70; $cy = 70;
      $ir = deg2rad(135); $fr = deg2rad(135 + $angulo);
      $ix = $cx + $raio * cos($ir); $iy = $cy + $raio * sin($ir);
      $fx = $cx + $raio * cos($fr); $fy = $cy + $raio * sin($fr);
      $grande = $angulo > 180 ? 1 : 0;
      $cor    = $classe==='ok' ? '#10b981' : ($classe==='warning' ? '#f59e0b' : '#ef4444');
    ?>
    <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-xl);padding:1.5rem">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
        <span class="gauge-card-title" style="font-family:var(--font-display);font-size:.85rem;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:.05em"><?= $titulo ?></span>
        <span style="font-family:var(--font-mono);font-size:.75rem;color:var(--text-muted)"><?= number_format($vMin,1) ?> – <?= number_format($vMax,1) ?> <?= $unidade ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:1.5rem">
        <svg width="140" height="120" viewBox="0 0 140 120">
          <path d="M <?= number_format($cx+$raio*cos(deg2rad(135)),2) ?> <?= number_format($cy+$raio*sin(deg2rad(135)),2) ?> A <?= $raio ?> <?= $raio ?> 0 1 1 <?= number_format($cx+$raio*cos(deg2rad(45)),2) ?> <?= number_format($cy+$raio*sin(deg2rad(45)),2) ?>"
            fill="none" stroke="var(--border)" stroke-width="8" stroke-linecap="round"/>
          <?php if ($pct > 0): ?>
          <path d="M <?= number_format($ix,2) ?> <?= number_format($iy,2) ?> A <?= $raio ?> <?= $raio ?> 0 <?= $grande ?> 1 <?= number_format($fx,2) ?> <?= number_format($fy,2) ?>"
            fill="none" stroke="<?= $cor ?>" stroke-width="8" stroke-linecap="round"
            style="filter:drop-shadow(0 0 6px <?= $cor ?>)"/>
          <?php endif; ?>
          <text x="<?= $cx ?>" y="<?= $cy - 5 ?>" text-anchor="middle"
            font-family="IBM Plex Mono,monospace" font-size="16" font-weight="700" fill="<?= $cor ?>">
            <?= $valor !== null ? number_format($valor, 1) : '—' ?>
          </text>
          <text x="<?= $cx ?>" y="<?= $cy + 14 ?>" text-anchor="middle"
            font-family="IBM Plex Mono,monospace" font-size="10" fill="#64748b"><?= $unidade ?></text>
        </svg>
        <div>
          <div style="font-family:var(--font-display);font-size:2.5rem;font-weight:900;color:<?= $cor ?>;line-height:1">
            <?= number_format($pct,0) ?><span style="font-size:1rem;opacity:.7">%</span>
          </div>
          <div style="font-size:.72rem;color:var(--text-muted);font-family:var(--font-mono);margin-top:.3rem">do limite operacional</div>
          <div style="margin-top:.75rem">
            <?php if ($classe==='ok'): ?>
            <span class="status-badge status-ativo">Normal</span>
            <?php elseif ($classe==='warning'): ?>
            <span class="status-badge" style="background:var(--yellow-dim);color:var(--yellow);border:1px solid rgba(245,158,11,.3)">{{lucide:triangle-alert}} Alerta</span>
            <?php else: ?>
            <span class="status-badge status-em_falha" style="gap:.35rem">
              <?= severidadeIcon('critico') ?> Crítico
            </span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="monitor-grid">

    <?php if (ehGestor()): ?>
    <div class="panel-card">
      <div class="panel-header">
        <div class="panel-title">{{lucide:plus}} Registrar Nova Leitura</div>
      </div>
      <div class="panel-body">
        <form method="POST">
          <input type="hidden" name="equip_id" value="<?= $eq['id'] ?>">
          <div class="form-grid" style="grid-template-columns:1fr 1fr">
            <div class="form-group">
              <label class="form-label">{{lucide:thermometer}} Temperatura (°C) *</label>
              <input type="number" name="temperatura" class="form-control"
                step="0.1" required placeholder="Ex: <?= $eq['ultima_temp'] ?? '72.5' ?>">
              <span class="form-hint">Faixa: <?= $eq['temp_min'] ?> – <?= $eq['temp_max'] ?>°C</span>
            </div>
            <div class="form-group">
              <label class="form-label">{{lucide:settings}} Pressão (bar) *</label>
              <input type="number" name="pressao" class="form-control"
                step="0.01" required placeholder="Ex: <?= $eq['ultima_pressao'] ?? '8.1' ?>">
              <span class="form-hint">Faixa: <?= $eq['pressao_min'] ?> – <?= $eq['pressao_max'] ?> bar</span>
            </div>
            <div class="form-group">
              <label class="form-label">{{lucide:droplet}} Umidade (%) <span style="color:var(--text-muted)">opcional</span></label>
              <input type="number" name="umidade" class="form-control" step="0.1" min="0" max="100" placeholder="Ex: 55.0">
            </div>
          </div>
          <div class="form-actions" style="margin-top:1rem;padding-top:1rem">
            <button type="submit" name="registrar_leitura" value="1" class="btn btn--primary">
              {{lucide:chart-no-axes-combined}} Registrar Leitura
            </button>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <div class="panel-card">
      <div class="panel-header">
        <div class="panel-title">{{lucide:clipboard-list}} Histórico de Leituras</div>
        <span style="font-size:.7rem;color:var(--text-muted);font-family:var(--font-mono)">Últimas 20</span>
      </div>
      <div class="panel-body" style="padding:0;max-height:400px;overflow-y:auto">
        <?php if (empty($leituras)): ?>
        <div class="empty-state" style="padding:2rem">
          <div class="empty-state__icon">{{lucide:chart-no-axes-combined}}</div>
          <div class="empty-state__title">Sem leituras registradas</div>
        </div>
        <?php else: ?>
        <table class="tabela-estoque">
          <thead><tr>
            <th>Horário</th><th>Temp.</th><th>Pressão</th><th>Umidade</th><th>Status</th>
          </tr></thead>
          <tbody>
          <?php foreach ($leituras as $leitura):
            $lT = avaliarTemp($leitura['temperatura'], $eq['temp_min'], $eq['temp_max']);
            $lP = avaliarPressao($leitura['pressao'],  $eq['pressao_min'], $eq['pressao_max']);
            $pior = ($lT==='danger'||$lP==='danger') ? 'danger' : (($lT==='warning'||$lP==='warning') ? 'warning' : 'ok');
          ?>
          <tr>
            <td style="font-family:var(--font-mono);font-size:.75rem;color:var(--text-muted)">
              <?= date('d/m H:i:s', strtotime($leitura['registrado_em'])) ?>
            </td>
            <td class="text-mono leitura-val <?= $lT ?>"><?= number_format($leitura['temperatura'],1) ?>°C</td>
            <td class="text-mono leitura-val <?= $lP ?>"><?= number_format($leitura['pressao'],2) ?> bar</td>
            <td style="color:var(--text-dim);font-family:var(--font-mono);font-size:.8rem">
              <?= $leitura['umidade'] !== null ? number_format($leitura['umidade'],1).'%' : '—' ?>
            </td>
            <td>
              <?php if ($pior==='danger'): ?>
              <span class="status-badge status-em_falha" style="font-size:.65rem;gap:.35rem">
                <?= severidadeIcon('critico') ?> Crítico
              </span>
              <?php elseif ($pior==='warning'): ?>
              <span class="status-badge" style="background:var(--yellow-dim);color:var(--yellow);border:1px solid rgba(245,158,11,.3);font-size:.65rem">{{lucide:triangle-alert}} Alerta</span>
              <?php else: ?>
              <span class="status-badge status-ativo" style="font-size:.65rem">Normal</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>

  </div>
  <?php endif; ?>
  <?php endif; ?>

</main>
<?php require_once 'footer.php'; ?>
</body>
</html>
