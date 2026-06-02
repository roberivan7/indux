<?php
require_once 'icon.php';
require_once 'init.php';
require_once 'db.php';
requerLogin();

$msg     = '';
$msgTipo = 'info';

if (isset($_GET['excluir']) && podeExcluirEquip()) {
    $equipamentoId = (int)$_GET['excluir'];
    try {
        $db   = getDB();
        $consultaEquipamento = $db->prepare('SELECT nome FROM equipamentos WHERE id = ?');
        $consultaEquipamento->execute([$equipamentoId]);
        $equipamentoRemovido = $consultaEquipamento->fetch();
        if ($equipamentoRemovido) {
            $db->prepare('DELETE FROM equipamentos WHERE id = ?')->execute([$equipamentoId]);
            registrarLog('EXCLUIR_EQUIPAMENTO', 'equipamentos', $equipamentoId, 'Nome: '.$equipamentoRemovido['nome']);
            $msg     = '🗑️ Equipamento "' . $equipamentoRemovido['nome'] . '" removido.';
            $msgTipo = 'aviso';
        }
    } catch (Throwable $e) {
        $msg = 'Erro ao remover equipamento.';
        $msgTipo = 'erro';
    }
    header('Location: equipamentos.php?msg=' . urlencode($msg) . '&tipo=' . $msgTipo);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_status']) && (ehOperador() || podeEditarEquip())) {
    $equipamentoId = (int)$_POST['equip_id'];
    $novoStatus = $_POST['novo_status'] ?? '';
    if (in_array($novoStatus, ['ativo','inativo','em_falha'])) {
        try {
            $db = getDB();
            $db->prepare('UPDATE equipamentos SET status = ? WHERE id = ?')
               ->execute([$novoStatus, $equipamentoId]);
            registrarLog('ALTERAR_STATUS', 'equipamentos', $equipamentoId, 'Novo status: '.$novoStatus);
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

$filtroStatus = $_GET['status'] ?? '';
$busca        = trim($_GET['busca'] ?? '');

$equipamentos = [];
$contagens    = ['total' => 0, 'ativo' => 0, 'inativo' => 0, 'em_falha' => 0];

try {
    $db = getDB();

    $linhasStatus = $db->query("SELECT status, COUNT(*) as qtd FROM equipamentos GROUP BY status")->fetchAll();
    foreach ($linhasStatus as $linhaStatus) {
        $contagens[$linhaStatus['status']] = (int)$linhaStatus['qtd'];
        $contagens['total'] += (int)$linhaStatus['qtd'];
    }

    $filtrosSql = [];
    $parametros = [];

    if ($filtroStatus !== '') {
        $filtrosSql[] = 'e.status = ?';
        $parametros[] = $filtroStatus;
    }

    if ($busca !== '') {
        $filtrosSql[] = '(e.nome LIKE ? OR e.tag LIKE ? OR e.localizacao LIKE ? OR e.modelo LIKE ?)';
        $buscaLike    = '%' . $busca . '%';
        $parametros   = array_merge($parametros, [$buscaLike, $buscaLike, $buscaLike, $buscaLike]);
    }

    $filtroSql = $filtrosSql ? ('WHERE ' . implode(' AND ', $filtrosSql)) : '';

    $sql = "SELECT e.*,
                (SELECT ls.temperatura FROM leituras_sensor ls WHERE ls.equipamento_id = e.id ORDER BY ls.id DESC LIMIT 1) as ultima_temp,
                (SELECT ls.pressao FROM leituras_sensor ls WHERE ls.equipamento_id = e.id ORDER BY ls.id DESC LIMIT 1) as ultima_pressao,
                (SELECT ls.registrado_em FROM leituras_sensor ls WHERE ls.equipamento_id = e.id ORDER BY ls.id DESC LIMIT 1) as ultima_leitura,
                (SELECT COUNT(*) FROM alarmes a WHERE a.equipamento_id = e.id AND a.resolvido = 0 AND e.status <> 'inativo') as qtd_alarmes
            FROM equipamentos e
            $filtroSql
            ORDER BY
                CASE e.status WHEN 'em_falha' THEN 0 WHEN 'ativo' THEN 1 ELSE 2 END,
                e.nome";

    $consultaEquipamentos = $db->prepare($sql);
    $consultaEquipamentos->execute($parametros);
    $equipamentos = $consultaEquipamentos->fetchAll();

} catch (Throwable $e) {

    $equipamentos = [
        ['id'=>1,'tag'=>'CLD-001','nome'=>'Caldeira Principal','modelo'=>'CBR-5000','fabricante'=>'ThermoTec','localizacao'=>'Sala 01','status'=>'ativo','temp_min'=>20,'temp_max'=>95,'pressao_min'=>0,'pressao_max'=>15,'ultima_temp'=>72.4,'ultima_pressao'=>8.1,'ultima_leitura'=>date('Y-m-d H:i:s',time()-120),'qtd_alarmes'=>0,'criado_em'=>date('Y-m-d'),'descricao'=>''],
        ['id'=>2,'tag'=>'CMP-002','nome'=>'Compressor Industrial','modelo'=>'AIR-2400','fabricante'=>'PneumaCorp','localizacao'=>'Área B','status'=>'em_falha','temp_min'=>15,'temp_max'=>70,'pressao_min'=>0,'pressao_max'=>12,'ultima_temp'=>88.9,'ultima_pressao'=>13.2,'ultima_leitura'=>date('Y-m-d H:i:s',time()-60),'qtd_alarmes'=>2,'criado_em'=>date('Y-m-d'),'descricao'=>''],
        ['id'=>3,'tag'=>'BBA-003','nome'=>'Bomba Hidráulica','modelo'=>'HYD-800','fabricante'=>'FluidTech','localizacao'=>'Subsolo','status'=>'ativo','temp_min'=>10,'temp_max'=>65,'pressao_min'=>1,'pressao_max'=>10,'ultima_temp'=>45.0,'ultima_pressao'=>6.5,'ultima_leitura'=>date('Y-m-d H:i:s',time()-300),'qtd_alarmes'=>0,'criado_em'=>date('Y-m-d'),'descricao'=>''],
        ['id'=>4,'tag'=>'TRF-004','nome'=>'Transformador Elétrico','modelo'=>'TRF-500KVA','fabricante'=>'ElectraInd','localizacao'=>'Subestação','status'=>'inativo','temp_min'=>0,'temp_max'=>85,'pressao_min'=>0,'pressao_max'=>5,'ultima_temp'=>null,'ultima_pressao'=>null,'ultima_leitura'=>null,'qtd_alarmes'=>0,'criado_em'=>date('Y-m-d'),'descricao'=>''],
    ];
    $contagens = ['total'=>4,'ativo'=>2,'inativo'=>1,'em_falha'=>1];
}

if (!empty($busca) && isset($equipamentos[0]['tag'])) {
    $equipamentos = array_filter($equipamentos, fn($equipamento) =>
        stripos($equipamento['nome'],$busca)!==false || stripos($equipamento['tag'],$busca)!==false
    );
}
if ($filtroStatus !== '') {
    $equipamentos = array_filter($equipamentos, fn($equipamento) => $equipamento['status'] === $filtroStatus);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo $icon; ?>
<title>Indux | Equipamentos</title>
  <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body>

  <?php require_once 'header.php'; ?>

  <main class="site-main">

    <div class="page-header">
      <div class="page-header-left">
        <div class="page-icon">⚙️</div>
        <div>
          <div class="breadcrumb"><span>INDUX</span> / <span>Equipamentos</span></div>
          <h1 class="page-title">Equipamentos</h1>
          <p class="page-subtitle">Cadastre, monitore e controle o funcionamento de cada equipamento</p>
        </div>
      </div>
      <?php if (podeCriarEquip()): ?>
      <a href="novo-equipamento.php" class="btn btn--primary">➕ Novo Equipamento</a>
      <?php endif; ?>
    </div>

    <?php if ($msg): ?>
    <div class="alerta alerta--<?php echo $msgTipo; ?>"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.25rem">
      <div class="kpi-card kpi-card--cyan" style="padding:.9rem 1rem">
        <div><div class="kpi-label">Total</div><div class="kpi-valor" style="font-size:1.4rem"><?php echo $contagens['total']; ?></div></div>
      </div>
      <div class="kpi-card kpi-card--green" style="padding:.9rem 1rem">
        <div><div class="kpi-label">Ativos</div><div class="kpi-valor" style="font-size:1.4rem"><?php echo $contagens['ativo']; ?></div></div>
      </div>
      <div class="kpi-card kpi-card--blue" style="padding:.9rem 1rem">
        <div><div class="kpi-label">Inativos</div><div class="kpi-valor" style="font-size:1.4rem"><?php echo $contagens['inativo']; ?></div></div>
      </div>
      <div class="kpi-card <?php echo $contagens['em_falha'] > 0 ? 'kpi-card--red' : 'kpi-card--purple'; ?>" style="padding:.9rem 1rem">
        <div><div class="kpi-label">Em Falha</div><div class="kpi-valor" style="font-size:1.4rem"><?php echo $contagens['em_falha']; ?></div></div>
      </div>
    </div>

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

    <div class="tabs">
      <a href="equipamentos.php<?php echo $busca ? '?busca='.urlencode($busca) : ''; ?>"
         class="tab-btn <?php echo $filtroStatus === '' ? 'ativo' : ''; ?>">
        Todos <span class="tab-badge"><?php echo $contagens['total']; ?></span>
      </a>
      <a href="equipamentos.php?status=ativo<?php echo $busca ? '&busca='.urlencode($busca) : ''; ?>"
         class="tab-btn <?php echo $filtroStatus === 'ativo' ? 'ativo' : ''; ?>">
        Ativos <span class="tab-badge tab-badge--green"><?php echo $contagens['ativo']; ?></span>
      </a>
      <a href="equipamentos.php?status=inativo<?php echo $busca ? '&busca='.urlencode($busca) : ''; ?>"
         class="tab-btn <?php echo $filtroStatus === 'inativo' ? 'ativo' : ''; ?>">
        Inativos <span class="tab-badge"><?php echo $contagens['inativo']; ?></span>
      </a>
      <a href="equipamentos.php?status=em_falha<?php echo $busca ? '&busca='.urlencode($busca) : ''; ?>"
         class="tab-btn <?php echo $filtroStatus === 'em_falha' ? 'ativo' : ''; ?>">
        Em Falha <span class="tab-badge tab-badge--red"><?php echo $contagens['em_falha']; ?></span>
      </a>
    </div>

    <?php if (empty($equipamentos)): ?>
    <div class="empty-state">
      <div class="empty-state__icon">⚙️</div>
      <div class="empty-state__title">Nenhum equipamento encontrado</div>
      <div class="empty-state__desc">
        <?php if ($busca || $filtroStatus): ?>
          Tente alterar os filtros de busca.
        <?php elseif (podeCriarEquip()): ?>
          <a href="novo-equipamento.php" class="btn btn--primary" style="margin-top:1rem">➕ Cadastrar primeiro equipamento</a>
        <?php else: ?>
          Nenhum equipamento cadastrado ainda.
        <?php endif; ?>
      </div>
    </div>
    <?php else: ?>
    <div class="equip-grid">
      <?php foreach ($equipamentos as $equipamento):
        $classeTemperatura  = ($equipamento['ultima_temp'] !== null)    ? avaliarTemp($equipamento['ultima_temp'], $equipamento['temp_min'], $equipamento['temp_max'])        : 'ok';
        $classePressao      = ($equipamento['ultima_pressao'] !== null)  ? avaliarPressao($equipamento['ultima_pressao'], $equipamento['pressao_min'], $equipamento['pressao_max']) : 'ok';
        $percentualTemp     = ($equipamento['ultima_temp'] !== null)    ? pctBar($equipamento['ultima_temp'], $equipamento['temp_min'], $equipamento['temp_max'])              : 0;
        $percentualPressao  = ($equipamento['ultima_pressao'] !== null)  ? pctBar($equipamento['ultima_pressao'], $equipamento['pressao_min'], $equipamento['pressao_max'])    : 0;
      ?>
      <div class="equip-card <?php echo $equipamento['status']; ?>">

        <div class="equip-card__header">
          <div>
            <div class="equip-card__title"><?php echo htmlspecialchars($equipamento['nome']); ?></div>
            <div class="equip-card__sub">
              <span class="tag-chip"><?php echo htmlspecialchars($equipamento['tag']); ?></span>
              <?php if ($equipamento['localizacao']): ?>
              <span style="margin-left:.4rem;color:var(--text-muted);font-size:.72rem">📍 <?php echo htmlspecialchars($equipamento['localizacao']); ?></span>
              <?php endif; ?>
            </div>
          </div>
          <span class="status-badge <?php echo statusClass($equipamento['status']); ?>"><?php echo statusLabel($equipamento['status']); ?></span>
        </div>

        <?php if ($equipamento['ultima_temp'] !== null): ?>
        <div class="equip-card__metrics">
          <div class="metric-item">
            <div class="metric-label">🌡️ Temperatura</div>
            <div class="metric-value <?php echo $classeTemperatura; ?>"><?php echo number_format($equipamento['ultima_temp'],1); ?>°C</div>
          </div>
          <div class="metric-item">
            <div class="metric-label">⚙️ Pressão</div>
            <div class="metric-value <?php echo $classePressao; ?>"><?php echo number_format($equipamento['ultima_pressao'],2); ?> bar</div>
          </div>
        </div>

        <div class="gauge-wrap">
          <div class="gauge-label"><span>Temperatura</span><span><?php echo number_format($percentualTemp,0); ?>%</span></div>
          <div class="gauge-bar"><div class="gauge-fill <?php echo $classeTemperatura; ?>" style="width:<?php echo $percentualTemp; ?>%"></div></div>
        </div>
        <div class="gauge-wrap" style="margin-top:.4rem">
          <div class="gauge-label"><span>Pressão</span><span><?php echo number_format($percentualPressao,0); ?>%</span></div>
          <div class="gauge-bar"><div class="gauge-fill <?php echo $classePressao; ?>" style="width:<?php echo $percentualPressao; ?>%"></div></div>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:1rem 0;color:var(--text-muted);font-size:.8rem;font-family:var(--font-mono)">
          — Sem leituras registradas —
        </div>
        <?php endif; ?>

        <div style="display:flex;gap:.75rem;font-size:.72rem;color:var(--text-muted);font-family:var(--font-mono);margin-top:.5rem">
          <?php if ($equipamento['modelo']): ?><span>📦 <?php echo htmlspecialchars($equipamento['modelo']); ?></span><?php endif; ?>
          <?php if ($equipamento['status'] !== 'inativo' && $equipamento['qtd_alarmes'] > 0): ?>
          <span style="color:var(--red)">🔔 <?php echo $equipamento['qtd_alarmes']; ?> alarme(s)</span>
          <?php endif; ?>
          <?php if ($equipamento['ultima_leitura']): ?>
          <span>🕐 <?php echo date('H:i', strtotime($equipamento['ultima_leitura'])); ?></span>
          <?php endif; ?>
        </div>

        <div class="equip-card__actions">
          <a href="monitoramento.php?equip=<?php echo $equipamento['id']; ?>" class="btn btn--ghost btn--sm" style="flex:1;justify-content:center">
            Monitorar
          </a>

          <?php if (ehOperador() || podeEditarEquip()): ?>

          <div style="position:relative">
            <button
              class="btn btn--ghost btn--sm"
              onclick="this.nextElementSibling.classList.toggle('open')"
              type="button"
            >Status ▾</button>
            <div style="position:absolute;bottom:calc(100% + 4px);right:0;background:var(--card);border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:.4rem;min-width:150px;z-index:10;display:none" class="status-dropdown">
              <?php foreach (['ativo'=>'Ativo','inativo'=>'Inativo','em_falha'=>'Em Falha'] as $statusValor => $statusTexto): ?>
              <?php if ($statusValor !== $equipamento['status']): ?>
              <form method="POST">
                <input type="hidden" name="equip_id" value="<?php echo $equipamento['id']; ?>">
                <input type="hidden" name="novo_status" value="<?php echo $statusValor; ?>">
                <button type="submit" name="alterar_status" value="1"
                  class="btn btn--ghost btn--sm"
                  style="width:100%;justify-content:flex-start;border:none;margin:.15rem 0">
                  <?php echo $statusTexto; ?>
                </button>
              </form>
              <?php endif; ?>
              <?php endforeach; ?>
              <?php if (podeExcluirEquip()): ?>
              <hr style="border:none;border-top:1px solid var(--border);margin:.3rem 0">
              <a href="equipamentos.php?excluir=<?php echo $equipamento['id']; ?>"
                 onclick="return confirm('Excluir <?php echo htmlspecialchars(addslashes($equipamento['nome'])); ?>?')"
                 class="btn btn--danger btn--sm"
                 style="width:100%;justify-content:flex-start">Excluir</a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php if (podeEditarEquip()): ?>
          <a href="novo-equipamento.php?editar=<?php echo $equipamento['id']; ?>" class="btn btn--ghost btn--sm">✏️</a>
          <?php endif; ?>
        </div>

      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </main>

  <?php require_once 'footer.php'; ?>

  <script>

  document.addEventListener('click', function(e) {
    if (!e.target.closest('[onclick]')) {
      document.querySelectorAll('.status-dropdown.open').forEach(d => d.classList.remove('open'));
    }
  });

  document.head.insertAdjacentHTML('beforeend', '<style>.status-dropdown.open{display:block!important}</style>');
  </script>

</body>
</html>
