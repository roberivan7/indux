<?php
require_once 'init.php';
require_once 'db.php';
requerLogin();

$msg     = '';
$msgTipo = 'info';

// ── EXCLUIR ──────────────────────────────────────────────
if (isset($_GET['excluir']) && ehGestor()) {
    $id = (int)$_GET['excluir'];
    try {
        $db   = getDB();
        $stmt = $db->prepare('SELECT nome FROM equipamentos WHERE id = ?');
        $stmt->execute([$id]);
        $equip = $stmt->fetch();
        if ($equip) {
            $db->prepare('DELETE FROM equipamentos WHERE id = ?')->execute([$id]);
            registrarLog('EXCLUIR_EQUIPAMENTO', 'equipamentos', $id, 'Nome: '.$equip['nome']);
            $msg     = '🗑️ Equipamento "' . $equip['nome'] . '" removido.';
            $msgTipo = 'aviso';
        }
    } catch (Throwable $e) {
        $msg = 'Erro ao remover equipamento.';
        $msgTipo = 'erro';
    }
    header('Location: equipamentos.php?msg=' . urlencode($msg) . '&tipo=' . $msgTipo);
    exit;
}

// ── ALTERAR STATUS ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_status']) && ehGestor()) {
    $id         = (int)$_POST['equip_id'];
    $novoStatus = $_POST['novo_status'] ?? '';
    if (in_array($novoStatus, ['ativo','inativo','em_falha'])) {
        try {
            $db = getDB();
            $db->prepare('UPDATE equipamentos SET status = ? WHERE id = ?')
               ->execute([$novoStatus, $id]);
            registrarLog('ALTERAR_STATUS', 'equipamentos', $id, 'Novo status: '.$novoStatus);
            $msg     = '✅ Status atualizado para: ' . statusLabel($novoStatus);
            $msgTipo = 'sucesso';
        } catch (Throwable $e) {
            $msg = 'Erro ao atualizar status.';
            $msgTipo = 'erro';
        }
    }
    header('Location: equipamentos.php?msg=' . urlencode($msg) . '&tipo=' . $msgTipo);
    exit;
}

if (isset($_GET['msg'])) {
    $msg     = htmlspecialchars($_GET['msg']);
    $msgTipo = $_GET['tipo'] ?? 'info';
}

// ── FILTRO DE STATUS ──────────────────────────────────────
$filtroStatus = $_GET['status'] ?? '';
$busca        = trim($_GET['busca'] ?? '');

// ── BUSCAR EQUIPAMENTOS ───────────────────────────────────
$equipamentos = [];
$contagens    = ['total' => 0, 'ativo' => 0, 'inativo' => 0, 'em_falha' => 0];

try {
    $db = getDB();

    // Contagens para as tabs
    $rows = $db->query("SELECT status, COUNT(*) as qtd FROM equipamentos GROUP BY status")->fetchAll();
    foreach ($rows as $r) {
        $contagens[$r['status']] = (int)$r['qtd'];
        $contagens['total'] += (int)$r['qtd'];
    }

    // Query principal
    $where  = [];
    $params = [];

    if ($filtroStatus !== '') {
        $where[]  = 'e.status = ?';
        $params[] = $filtroStatus;
    }

    if ($busca !== '') {
        $where[]  = '(e.nome LIKE ? OR e.tag LIKE ? OR e.localizacao LIKE ? OR e.modelo LIKE ?)';
        $like     = '%' . $busca . '%';
        $params   = array_merge($params, [$like, $like, $like, $like]);
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $sql = "SELECT e.*,
                (SELECT ls.temperatura FROM leituras_sensor ls WHERE ls.equipamento_id = e.id ORDER BY ls.id DESC LIMIT 1) as ultima_temp,
                (SELECT ls.pressao FROM leituras_sensor ls WHERE ls.equipamento_id = e.id ORDER BY ls.id DESC LIMIT 1) as ultima_pressao,
                (SELECT ls.registrado_em FROM leituras_sensor ls WHERE ls.equipamento_id = e.id ORDER BY ls.id DESC LIMIT 1) as ultima_leitura,
                (SELECT COUNT(*) FROM alarmes a WHERE a.equipamento_id = e.id AND a.resolvido = 0) as qtd_alarmes
            FROM equipamentos e
            $whereSql
            ORDER BY
                CASE e.status WHEN 'em_falha' THEN 0 WHEN 'ativo' THEN 1 ELSE 2 END,
                e.nome";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $equipamentos = $stmt->fetchAll();

} catch (Throwable $e) {
    // Dados demo
    $equipamentos = [
        ['id'=>1,'tag'=>'CLD-001','nome'=>'Caldeira Principal','modelo'=>'CBR-5000','fabricante'=>'ThermoTec','localizacao'=>'Sala 01','status'=>'ativo','temp_min'=>20,'temp_max'=>95,'pressao_min'=>0,'pressao_max'=>15,'ultima_temp'=>72.4,'ultima_pressao'=>8.1,'ultima_leitura'=>date('Y-m-d H:i:s',time()-120),'qtd_alarmes'=>0,'criado_em'=>date('Y-m-d'),'descricao'=>''],
        ['id'=>2,'tag'=>'CMP-002','nome'=>'Compressor Industrial','modelo'=>'AIR-2400','fabricante'=>'PneumaCorp','localizacao'=>'Área B','status'=>'em_falha','temp_min'=>15,'temp_max'=>70,'pressao_min'=>0,'pressao_max'=>12,'ultima_temp'=>88.9,'ultima_pressao'=>13.2,'ultima_leitura'=>date('Y-m-d H:i:s',time()-60),'qtd_alarmes'=>2,'criado_em'=>date('Y-m-d'),'descricao'=>''],
        ['id'=>3,'tag'=>'BBA-003','nome'=>'Bomba Hidráulica','modelo'=>'HYD-800','fabricante'=>'FluidTech','localizacao'=>'Subsolo','status'=>'ativo','temp_min'=>10,'temp_max'=>65,'pressao_min'=>1,'pressao_max'=>10,'ultima_temp'=>45.0,'ultima_pressao'=>6.5,'ultima_leitura'=>date('Y-m-d H:i:s',time()-300),'qtd_alarmes'=>0,'criado_em'=>date('Y-m-d'),'descricao'=>''],
        ['id'=>4,'tag'=>'TRF-004','nome'=>'Transformador Elétrico','modelo'=>'TRF-500KVA','fabricante'=>'ElectraInd','localizacao'=>'Subestação','status'=>'inativo','temp_min'=>0,'temp_max'=>85,'pressao_min'=>0,'pressao_max'=>5,'ultima_temp'=>null,'ultima_pressao'=>null,'ultima_leitura'=>null,'qtd_alarmes'=>0,'criado_em'=>date('Y-m-d'),'descricao'=>''],
    ];
    $contagens = ['total'=>4,'ativo'=>2,'inativo'=>1,'em_falha'=>1];
}

// Filtra demo local se necessário
if (!empty($busca) && isset($equipamentos[0]['tag'])) {
    $equipamentos = array_filter($equipamentos, fn($e) =>
        stripos($e['nome'],$busca)!==false || stripos($e['tag'],$busca)!==false
    );
}
if ($filtroStatus !== '') {
    $equipamentos = array_filter($equipamentos, fn($e) => $e['status'] === $filtroStatus);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Equipamentos — <?php echo SISTEMA_NOME; ?></title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <?php require_once 'header.php'; ?>

  <main class="site-main">

    <!-- Page header -->
    <div class="page-header">
      <div class="page-header-left">
        <div class="page-icon">⚙️</div>
        <div>
          <div class="breadcrumb"><span>INDUX</span> / <span>Equipamentos</span></div>
          <h1 class="page-title">Equipamentos</h1>
          <p class="page-subtitle">Cadastre, monitore e controle o funcionamento de cada equipamento</p>
        </div>
      </div>
      <?php if (ehOperador()): ?>
      <a href="novo-equipamento.php" class="btn btn--primary">➕ Novo Equipamento</a>
      <?php endif; ?>
    </div>

    <?php if ($msg): ?>
    <div class="alerta alerta--<?php echo $msgTipo; ?>"><?php echo $msg; ?></div>
    <?php endif; ?>

    <!-- KPIs rápidos -->
    <div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.25rem">
      <div class="kpi-card kpi-card--cyan" style="padding:.9rem 1rem">
        <div class="kpi-icon" style="font-size:1.2rem;width:36px;height:36px">⚙️</div>
        <div><div class="kpi-label">Total</div><div class="kpi-valor" style="font-size:1.4rem"><?php echo $contagens['total']; ?></div></div>
      </div>
      <div class="kpi-card kpi-card--green" style="padding:.9rem 1rem">
        <div class="kpi-icon" style="font-size:1.2rem;width:36px;height:36px">✅</div>
        <div><div class="kpi-label">Ativos</div><div class="kpi-valor" style="font-size:1.4rem"><?php echo $contagens['ativo']; ?></div></div>
      </div>
      <div class="kpi-card kpi-card--blue" style="padding:.9rem 1rem">
        <div class="kpi-icon" style="font-size:1.2rem;width:36px;height:36px">⏸️</div>
        <div><div class="kpi-label">Inativos</div><div class="kpi-valor" style="font-size:1.4rem"><?php echo $contagens['inativo']; ?></div></div>
      </div>
      <div class="kpi-card <?php echo $contagens['em_falha'] > 0 ? 'kpi-card--red' : 'kpi-card--purple'; ?>" style="padding:.9rem 1rem">
        <div class="kpi-icon" style="font-size:1.2rem;width:36px;height:36px">🔴</div>
        <div><div class="kpi-label">Em Falha</div><div class="kpi-valor" style="font-size:1.4rem"><?php echo $contagens['em_falha']; ?></div></div>
      </div>
    </div>

    <!-- Busca -->
    <form method="GET" action="equipamentos.php" style="display:flex;gap:.75rem;margin-bottom:1.25rem;flex-wrap:wrap">
      <?php if ($filtroStatus): ?>
      <input type="hidden" name="status" value="<?php echo htmlspecialchars($filtroStatus); ?>">
      <?php endif; ?>
      <div style="flex:1;min-width:250px;position:relative">
        <span style="position:absolute;left:.8rem;top:50%;transform:translateY(-50%);font-size:.9rem">🔍</span>
        <input
          type="text"
          name="busca"
          class="form-control"
          placeholder="Buscar por nome, TAG, local, modelo..."
          value="<?php echo htmlspecialchars($busca); ?>"
          style="padding-left:2.25rem"
        >
      </div>
      <button type="submit" class="btn btn--primary">Buscar</button>
      <?php if ($busca || $filtroStatus): ?>
      <a href="equipamentos.php" class="btn btn--ghost">✕ Limpar</a>
      <?php endif; ?>
    </form>

    <!-- Tabs de status (como no Lovable) -->
    <div class="tabs">
      <a href="equipamentos.php<?php echo $busca ? '?busca='.urlencode($busca) : ''; ?>"
         class="tab-btn <?php echo $filtroStatus === '' ? 'ativo' : ''; ?>">
        Todos <span class="tab-badge"><?php echo $contagens['total']; ?></span>
      </a>
      <a href="equipamentos.php?status=ativo<?php echo $busca ? '&busca='.urlencode($busca) : ''; ?>"
         class="tab-btn <?php echo $filtroStatus === 'ativo' ? 'ativo' : ''; ?>">
        ✅ Ativos <span class="tab-badge tab-badge--green"><?php echo $contagens['ativo']; ?></span>
      </a>
      <a href="equipamentos.php?status=inativo<?php echo $busca ? '&busca='.urlencode($busca) : ''; ?>"
         class="tab-btn <?php echo $filtroStatus === 'inativo' ? 'ativo' : ''; ?>">
        ⏸️ Inativos <span class="tab-badge"><?php echo $contagens['inativo']; ?></span>
      </a>
      <a href="equipamentos.php?status=em_falha<?php echo $busca ? '&busca='.urlencode($busca) : ''; ?>"
         class="tab-btn <?php echo $filtroStatus === 'em_falha' ? 'ativo' : ''; ?>">
        🔴 Em Falha <span class="tab-badge tab-badge--red"><?php echo $contagens['em_falha']; ?></span>
      </a>
    </div>

    <!-- Lista de equipamentos em cards (estilo Lovable) -->
    <?php if (empty($equipamentos)): ?>
    <div class="empty-state">
      <div class="empty-state__icon">⚙️</div>
      <div class="empty-state__title">Nenhum equipamento encontrado</div>
      <div class="empty-state__desc">
        <?php if ($busca || $filtroStatus): ?>
          Tente alterar os filtros de busca.
        <?php elseif (ehOperador()): ?>
          <a href="novo-equipamento.php" class="btn btn--primary" style="margin-top:1rem">➕ Cadastrar primeiro equipamento</a>
        <?php else: ?>
          Nenhum equipamento cadastrado ainda.
        <?php endif; ?>
      </div>
    </div>
    <?php else: ?>
    <div class="equip-grid">
      <?php foreach ($equipamentos as $e):
        $tClass  = ($e['ultima_temp'] !== null)    ? avaliarTemp($e['ultima_temp'], $e['temp_min'], $e['temp_max'])        : 'ok';
        $pClass  = ($e['ultima_pressao'] !== null)  ? avaliarPressao($e['ultima_pressao'], $e['pressao_min'], $e['pressao_max']) : 'ok';
        $tPct    = ($e['ultima_temp'] !== null)    ? pctBar($e['ultima_temp'], $e['temp_min'], $e['temp_max'])              : 0;
        $pPct    = ($e['ultima_pressao'] !== null)  ? pctBar($e['ultima_pressao'], $e['pressao_min'], $e['pressao_max'])    : 0;
      ?>
      <div class="equip-card <?php echo $e['status']; ?>">

        <div class="equip-card__header">
          <div>
            <div class="equip-card__title"><?php echo htmlspecialchars($e['nome']); ?></div>
            <div class="equip-card__sub">
              <span class="tag-chip"><?php echo htmlspecialchars($e['tag']); ?></span>
              <?php if ($e['localizacao']): ?>
              <span style="margin-left:.4rem;color:var(--text-muted);font-size:.72rem">📍 <?php echo htmlspecialchars($e['localizacao']); ?></span>
              <?php endif; ?>
            </div>
          </div>
          <span class="status-badge <?php echo statusClass($e['status']); ?>"><?php echo statusLabel($e['status']); ?></span>
        </div>

        <!-- Métricas -->
        <?php if ($e['ultima_temp'] !== null): ?>
        <div class="equip-card__metrics">
          <div class="metric-item">
            <div class="metric-label">🌡️ Temperatura</div>
            <div class="metric-value <?php echo $tClass; ?>"><?php echo number_format($e['ultima_temp'],1); ?>°C</div>
          </div>
          <div class="metric-item">
            <div class="metric-label">⚙️ Pressão</div>
            <div class="metric-value <?php echo $pClass; ?>"><?php echo number_format($e['ultima_pressao'],2); ?> bar</div>
          </div>
        </div>

        <!-- Gauges -->
        <div class="gauge-wrap">
          <div class="gauge-label"><span>Temperatura</span><span><?php echo number_format($tPct,0); ?>%</span></div>
          <div class="gauge-bar"><div class="gauge-fill <?php echo $tClass; ?>" style="width:<?php echo $tPct; ?>%"></div></div>
        </div>
        <div class="gauge-wrap" style="margin-top:.4rem">
          <div class="gauge-label"><span>Pressão</span><span><?php echo number_format($pPct,0); ?>%</span></div>
          <div class="gauge-bar"><div class="gauge-fill <?php echo $pClass; ?>" style="width:<?php echo $pPct; ?>%"></div></div>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:1rem 0;color:var(--text-muted);font-size:.8rem;font-family:var(--font-mono)">
          — Sem leituras registradas —
        </div>
        <?php endif; ?>

        <!-- Info adicional -->
        <div style="display:flex;gap:.75rem;font-size:.72rem;color:var(--text-muted);font-family:var(--font-mono);margin-top:.5rem">
          <?php if ($e['modelo']): ?><span>📦 <?php echo htmlspecialchars($e['modelo']); ?></span><?php endif; ?>
          <?php if ($e['qtd_alarmes'] > 0): ?>
          <span style="color:var(--red)">🔔 <?php echo $e['qtd_alarmes']; ?> alarme(s)</span>
          <?php endif; ?>
          <?php if ($e['ultima_leitura']): ?>
          <span>🕐 <?php echo date('H:i', strtotime($e['ultima_leitura'])); ?></span>
          <?php endif; ?>
        </div>

        <!-- Ações -->
        <div class="equip-card__actions">
          <a href="monitoramento.php?equip=<?php echo $e['id']; ?>" class="btn btn--ghost btn--sm" style="flex:1;justify-content:center">
            📡 Monitorar
          </a>

          <?php if (ehOperador()): ?>
          <!-- Dropdown de status -->
          <div style="position:relative">
            <button
              class="btn btn--ghost btn--sm"
              onclick="this.nextElementSibling.classList.toggle('open')"
              type="button"
            >⚡ Status ▾</button>
            <div style="position:absolute;bottom:calc(100% + 4px);right:0;background:var(--card);border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:.4rem;min-width:150px;z-index:10;display:none" class="status-dropdown">
              <?php foreach (['ativo'=>'✅ Ativo','inativo'=>'⏸️ Inativo','em_falha'=>'🔴 Em Falha'] as $sv => $sl): ?>
              <?php if ($sv !== $e['status']): ?>
              <form method="POST">
                <input type="hidden" name="equip_id" value="<?php echo $e['id']; ?>">
                <input type="hidden" name="novo_status" value="<?php echo $sv; ?>">
                <button type="submit" name="alterar_status" value="1"
                  class="btn btn--ghost btn--sm"
                  style="width:100%;justify-content:flex-start;border:none;margin:.15rem 0">
                  <?php echo $sl; ?>
                </button>
              </form>
              <?php endif; ?>
              <?php endforeach; ?>
              <hr style="border:none;border-top:1px solid var(--border);margin:.3rem 0">
              <a href="equipamentos.php?excluir=<?php echo $e['id']; ?>"
                 onclick="return confirm('Excluir <?php echo htmlspecialchars(addslashes($e['nome'])); ?>?')"
                 class="btn btn--danger btn--sm"
                 style="width:100%;justify-content:flex-start">🗑️ Excluir</a>
            </div>
          </div>
          <?php endif; ?>

          <?php if (ehOperador()): ?>
          <a href="novo-equipamento.php?editar=<?php echo $e['id']; ?>" class="btn btn--ghost btn--sm">✏️</a>
          <?php endif; ?>
        </div>

      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </main>

  <?php require_once 'footer.php'; ?>

  <script>
  // Fecha dropdowns ao clicar fora
  document.addEventListener('click', function(e) {
    if (!e.target.closest('[onclick]')) {
      document.querySelectorAll('.status-dropdown.open').forEach(d => d.classList.remove('open'));
    }
  });
  // CSS para .open
  document.head.insertAdjacentHTML('beforeend', '<style>.status-dropdown.open{display:block!important}</style>');
  </script>

</body>
</html>
