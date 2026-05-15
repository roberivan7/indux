<?php
require_once 'init.php';
require_once 'db.php';
requerLogin();

$editarIdSolicitado = (int)($_GET['editar'] ?? ($_POST['editar_id'] ?? 0));
if ($editarIdSolicitado > 0) {
    if (!podeEditarEquip()) {
        header('Location: equipamentos.php?msg=' . urlencode('Sem permissao para editar equipamentos.') . '&tipo=erro');
        exit;
    }
} elseif (!podeCriarEquip()) {
    header('Location: equipamentos.php?msg=' . urlencode('Sem permissao para cadastrar equipamentos.') . '&tipo=erro');
    exit;
}

$equipamento = null;
$editando    = false;
$msg         = '';
$msgTipo     = 'info';
$erros       = [];

// ── Carregar para edição ───────────────────────────────────
if (isset($_GET['editar'])) {
    $equipamentoId = (int)$_GET['editar'];
    $editando = true;
    try {
        $consultaEquipamento = getDB()->prepare('SELECT * FROM equipamentos WHERE id = ?');
        $consultaEquipamento->execute([$equipamentoId]);
        $equipamento = $consultaEquipamento->fetch();
    } catch (Throwable $e) {}
}

// ── SALVAR ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dados vindos do formulario, ja tratados antes de salvar.
    $dadosEquipamento = [
        'tag'          => strtoupper(trim($_POST['tag'] ?? '')),
        'nome'         => trim($_POST['nome'] ?? ''),
        'modelo'       => trim($_POST['modelo'] ?? ''),
        'fabricante'   => trim($_POST['fabricante'] ?? ''),
        'localizacao'  => trim($_POST['localizacao'] ?? ''),
        'descricao'    => trim($_POST['descricao'] ?? ''),
        'temp_min'     => (float)($_POST['temp_min'] ?? 0),
        'temp_max'     => (float)($_POST['temp_max'] ?? 80),
        'pressao_min'  => (float)($_POST['pressao_min'] ?? 0),
        'pressao_max'  => (float)($_POST['pressao_max'] ?? 10),
        'status'       => $_POST['status'] ?? 'ativo',
    ];

    // Validações
    if ($dadosEquipamento['tag'] === '')  $erros[] = 'TAG é obrigatória.';
    if ($dadosEquipamento['nome'] === '') $erros[] = 'Nome é obrigatório.';
    if (!in_array($dadosEquipamento['status'], ['ativo','inativo','em_falha'])) $erros[] = 'Status inválido.';
    if ($dadosEquipamento['temp_max'] <= $dadosEquipamento['temp_min']) $erros[] = 'Temperatura máxima deve ser maior que a mínima.';
    if ($dadosEquipamento['pressao_max'] <= $dadosEquipamento['pressao_min']) $erros[] = 'Pressão máxima deve ser maior que a mínima.';

    if (empty($erros)) {
        try {
            $db = getDB();
            $equipamentoId = (int)($_POST['editar_id'] ?? 0);

            if ($equipamentoId) {
                // Verificar TAG duplicada (exceto o próprio)
                $consultaDuplicada = $db->prepare('SELECT id FROM equipamentos WHERE tag = ? AND id != ?');
                $consultaDuplicada->execute([$dadosEquipamento['tag'], $equipamentoId]);
                if ($consultaDuplicada->fetch()) { $erros[] = 'TAG já cadastrada para outro equipamento.'; }
                else {
                    $db->prepare(
                        'UPDATE equipamentos SET tag=?,nome=?,modelo=?,fabricante=?,localizacao=?,descricao=?,
                         temp_min=?,temp_max=?,pressao_min=?,pressao_max=?,status=? WHERE id=?'
                    )->execute(array_merge(array_values($dadosEquipamento), [$equipamentoId]));
                    registrarLog('EDITAR_EQUIPAMENTO','equipamentos',$equipamentoId,'TAG:'.$dadosEquipamento['tag']);
                    header('Location: equipamentos.php?msg='.urlencode('✅ Equipamento atualizado!').'&tipo=sucesso');
                    exit;
                }
            } else {
                $consultaDuplicada = $db->prepare('SELECT id FROM equipamentos WHERE tag = ?');
                $consultaDuplicada->execute([$dadosEquipamento['tag']]);
                if ($consultaDuplicada->fetch()) { $erros[] = 'TAG já cadastrada. Use outra identificação.'; }
                else {
                    $db->prepare(
                        'INSERT INTO equipamentos (tag,nome,modelo,fabricante,localizacao,descricao,temp_min,temp_max,pressao_min,pressao_max,status)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?)'
                    )->execute(array_values($dadosEquipamento));
                    $novoId = (int)$db->lastInsertId();
                    registrarLog('CRIAR_EQUIPAMENTO','equipamentos',$novoId,'TAG:'.$dadosEquipamento['tag']);
                    header('Location: equipamentos.php?msg='.urlencode('✅ Equipamento "'.$dadosEquipamento['nome'].'" cadastrado!').'&tipo=sucesso');
                    exit;
                }
            }
        } catch (Throwable $e) {
            $erros[] = 'Erro ao salvar: ' . $e->getMessage();
        }
    }

    // Repopula form em caso de erro
    $equipamento = $dadosEquipamento;
    $equipamentoId = (int)($_POST['editar_id'] ?? 0);
    if ($equipamentoId) { $equipamento['id'] = $equipamentoId; $editando = true; }
}

$form = $equipamento ?? [];
$equipamentoId = $editando && isset($form['id']) ? $form['id'] : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $editando ? 'Editar' : 'Novo'; ?> Equipamento — <?php echo SISTEMA_NOME; ?></title>
  <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body>

  <?php require_once 'header.php'; ?>

  <main class="site-main">

    <div class="page-header">
      <div class="page-header-left">
        <div class="page-icon"><?php echo $editando ? '✏️' : '➕'; ?></div>
        <div>
          <div class="breadcrumb"><span>INDUX</span> / <a href="equipamentos.php">Equipamentos</a> / <span><?php echo $editando ? 'Editar' : 'Novo'; ?></span></div>
          <h1 class="page-title"><?php echo $editando ? 'Editar Equipamento' : 'Novo Equipamento'; ?></h1>
          <p class="page-subtitle">Preencha os dados do equipamento monitorado</p>
        </div>
      </div>
      <a href="equipamentos.php" class="btn btn--ghost">← Voltar</a>
    </div>

    <?php if (!empty($erros)): ?>
    <div class="alerta alerta--erro">
      <div>
        <?php foreach ($erros as $erro): ?>
        <div>⚠️ <?php echo htmlspecialchars($erro); ?></div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <form method="POST" action="novo-equipamento.php">
      <?php if ($editando): ?>
      <input type="hidden" name="editar_id" value="<?php echo $equipamentoId; ?>">
      <?php endif; ?>

      <div class="form-card">

        <!-- Identificação -->
        <div class="form-title">📋 Identificação do Equipamento</div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="tag">TAG <span style="color:var(--red)">*</span></label>
            <input type="text" id="tag" name="tag" class="form-control"
              placeholder="Ex: CLD-001, CMP-002"
              value="<?php echo htmlspecialchars($form['tag'] ?? ''); ?>"
              required maxlength="50"
              style="font-family:var(--font-mono);text-transform:uppercase;letter-spacing:.05em">
            <span class="form-hint">Código único de identificação (ex: TAG-001)</span>
          </div>

          <div class="form-group">
            <label class="form-label" for="nome">Nome <span style="color:var(--red)">*</span></label>
            <input type="text" id="nome" name="nome" class="form-control"
              placeholder="Ex: Caldeira Principal"
              value="<?php echo htmlspecialchars($form['nome'] ?? ''); ?>"
              required maxlength="150">
          </div>

          <div class="form-group">
            <label class="form-label" for="modelo">Modelo</label>
            <input type="text" id="modelo" name="modelo" class="form-control"
              placeholder="Ex: CBR-5000"
              value="<?php echo htmlspecialchars($form['modelo'] ?? ''); ?>" maxlength="100">
          </div>

          <div class="form-group">
            <label class="form-label" for="fabricante">Fabricante</label>
            <input type="text" id="fabricante" name="fabricante" class="form-control"
              placeholder="Ex: ThermoTec Ind."
              value="<?php echo htmlspecialchars($form['fabricante'] ?? ''); ?>" maxlength="100">
          </div>

          <div class="form-group">
            <label class="form-label" for="localizacao">Localização</label>
            <input type="text" id="localizacao" name="localizacao" class="form-control"
              placeholder="Ex: Sala 01 / Área B / Subsolo"
              value="<?php echo htmlspecialchars($form['localizacao'] ?? ''); ?>" maxlength="200">
          </div>

          <div class="form-group">
            <label class="form-label" for="status">Status Inicial</label>
            <select id="status" name="status" class="form-control">
              <option value="ativo"    <?php echo ($form['status']??'ativo')==='ativo'    ? 'selected' : ''; ?>>✅ Ativo</option>
              <option value="inativo"  <?php echo ($form['status']??'')==='inativo'       ? 'selected' : ''; ?>>⏸️ Inativo</option>
              <option value="em_falha" <?php echo ($form['status']??'')==='em_falha'      ? 'selected' : ''; ?>>🔴 Em Falha</option>
            </select>
          </div>

          <div class="form-group full">
            <label class="form-label" for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" class="form-control" rows="3"
              placeholder="Descrição técnica, observações, procedimentos especiais..."><?php echo htmlspecialchars($form['descricao'] ?? ''); ?></textarea>
          </div>
        </div>

        <!-- Limites de operação -->
        <div class="form-section">
          <div class="form-section-title">🌡️ Limites de Temperatura (°C)</div>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="temp_min">Mínima (°C)</label>
              <input type="number" id="temp_min" name="temp_min" class="form-control"
                step="0.1" min="-50" max="500"
                value="<?php echo $form['temp_min'] ?? 0; ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="temp_max">Máxima aceitável (°C)</label>
              <input type="number" id="temp_max" name="temp_max" class="form-control"
                step="0.1" min="-50" max="500"
                value="<?php echo $form['temp_max'] ?? 80; ?>">
              <span class="form-hint">Acima deste valor → alarme de temperatura</span>
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="form-section-title">⚙️ Limites de Pressão (bar)</div>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="pressao_min">Mínima (bar)</label>
              <input type="number" id="pressao_min" name="pressao_min" class="form-control"
                step="0.01" min="0" max="1000"
                value="<?php echo $form['pressao_min'] ?? 0; ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="pressao_max">Máxima aceitável (bar)</label>
              <input type="number" id="pressao_max" name="pressao_max" class="form-control"
                step="0.01" min="0" max="1000"
                value="<?php echo $form['pressao_max'] ?? 10; ?>">
              <span class="form-hint">Acima deste valor → alarme de pressão</span>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <a href="equipamentos.php" class="btn btn--ghost">Cancelar</a>
          <button type="submit" class="btn btn--primary btn--lg">
            <?php echo $editando ? '💾 Salvar Alterações' : '➕ Cadastrar Equipamento'; ?>
          </button>
        </div>

      </div>
    </form>

  </main>

  <?php require_once 'footer.php'; ?>

  <script>
  // Auto-maiúsculo na TAG
  document.getElementById('tag').addEventListener('input', function() {
    let valorTag = this.value.toUpperCase().replace(/[^A-Z0-9\-_]/g,'');
    this.value = valorTag;
  });
  </script>

</body>
</html>
