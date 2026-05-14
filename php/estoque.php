<?php
require_once 'init.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

$msg     = '';
$msgTipo = '';

// ── MOVIMENTAÇÃO ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $id    = (int)($_POST['produto_id'] ?? 0);
    $delta = (int)($_POST['delta'] ?? 0);

    foreach ($_SESSION['produtos'] as &$p) {
        if ((int)$p['id'] === $id) {
            $novaQty = max(0, (int)$p['quantidade'] + $delta);
            $p['quantidade'] = $novaQty;
            $msg     = $delta > 0
                ? "✅ {$p['nome']}: +{$delta} unidade(s) adicionada(s)."
                : "📤 {$p['nome']}: " . abs($delta) . " unidade(s) removida(s).";
            $msgTipo = 'sucesso';
            break;
        }
    }
    unset($p);
    salvarProdutos($_SESSION['produtos']); // Persiste no JSON
    header("Location: estoque.php?msg=" . urlencode($msg) . "&tipo=" . $msgTipo);
    exit;
}

// ── EXCLUIR ──────────────────────────────────────────────────────────
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    $_SESSION['produtos'] = array_values(array_filter(
        $_SESSION['produtos'],
        fn($p) => (int)$p['id'] !== $id
    ));
    salvarProdutos($_SESSION['produtos']); // Persiste no JSON
    header("Location: estoque.php?msg=" . urlencode("🗑️ Produto removido do estoque.") . "&tipo=aviso");
    exit;
}

if (isset($_GET['msg'])) {
    $msg     = htmlspecialchars($_GET['msg']);
    $msgTipo = $_GET['tipo'] ?? 'sucesso';
}

$catFiltro  = $_GET['categoria'] ?? '';
$totalGeral = 0;
$totalItens = count($_SESSION['produtos']);
$alertas    = 0;

foreach ($_SESSION['produtos'] as $p) {
    $qty   = (int)($p['quantidade'] ?? 0);
    $preco = (float)($p['preco'] ?? 0);
    $totalGeral += $qty * $preco;
    if ($qty <= ESTOQUE_MINIMO) $alertas++;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Estoque — <?php echo $nomeLoja; ?></title>
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
  <?php require_once '../partials/header.php'; ?>

  <main class="site-main">

    <div class="page-header">
      <div>
        <h1 class="page-title">📦 Gestão de Estoque</h1>
        <p class="page-subtitle">Gerencie produtos, quantidades e alertas em tempo real</p>
      </div>
      <a href="cadastro-produto.php" class="btn btn--primary">+ Cadastrar Produto</a>
    </div>

    <?php if ($msg): ?>
    <div class="alerta alerta--<?php echo $msgTipo; ?>"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="kpi-grid">
      <div class="kpi-card kpi-card--green">
        <span class="kpi-icon">💰</span>
        <div>
          <div class="kpi-label">Valor Total em Estoque</div>
          <div class="kpi-valor">R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?></div>
        </div>
      </div>
      <div class="kpi-card kpi-card--blue">
        <span class="kpi-icon">📋</span>
        <div>
          <div class="kpi-label">Tipos de Produtos</div>
          <div class="kpi-valor"><?php echo $totalItens; ?></div>
        </div>
      </div>
      <div class="kpi-card <?php echo $alertas > 0 ? 'kpi-card--yellow' : 'kpi-card--gray'; ?>">
        <span class="kpi-icon">⚠️</span>
        <div>
          <div class="kpi-label">Alertas de Estoque Baixo</div>
          <div class="kpi-valor"><?php echo $alertas; ?> produto(s)</div>
        </div>
      </div>
      <div class="kpi-card kpi-card--orange">
        <span class="kpi-icon">📏</span>
        <div>
          <div class="kpi-label">Limite Mínimo</div>
          <div class="kpi-valor"><?php echo ESTOQUE_MINIMO; ?> unidades</div>
        </div>
      </div>
    </div>

    <nav class="filtros-categoria" aria-label="Filtrar por categoria">
      <a href="estoque.php" class="<?php echo $catFiltro === '' ? 'ativo' : ''; ?>">Todas</a>
      <?php foreach ($categorias as $key => $nome): ?>
      <a href="estoque.php?categoria=<?php echo $key; ?>" class="<?php echo $catFiltro === $key ? 'ativo' : ''; ?> filtro--<?php echo $key; ?>">
        <?php echo $nome; ?>
      </a>
      <?php endforeach; ?>
    </nav>

    <div class="tabela-wrap">
      <table class="tabela-estoque">
        <thead>
          <tr>
            <th>#</th>
            <th>Produto</th>
            <th>Categoria</th>
            <th>Qtd.</th>
            <th>Preço Unit.</th>
            <th>Valor Total</th>
            <th>Status</th>
            <th>Movimentação</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $produtosFiltrados = array_filter(
              $_SESSION['produtos'],
              fn($p) => $catFiltro === '' || $p['categoria'] === $catFiltro
          );

          if (empty($produtosFiltrados)):
          ?>
          <tr><td colspan="9" class="tabela-vazia">Nenhum produto encontrado.</td></tr>
          <?php else: ?>
          <?php foreach ($produtosFiltrados as $p):
            $qty        = (int)($p['quantidade'] ?? 0);
            $preco      = (float)($p['preco'] ?? 0);
            $valorTotal = $qty * $preco;
            $isLow      = $qty <= ESTOQUE_MINIMO;
          ?>
          <tr class="<?php echo $isLow ? 'linha-alerta' : ''; ?>">
            <td class="td-id"><?php echo (int)$p['id']; ?></td>
            <td class="td-nome">
              <strong><?php echo htmlspecialchars($p['nome']); ?></strong>
              <?php if ($isLow): ?>
                <span class="badge-alerta">⚠️ Baixo</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge badge--<?php echo $p['categoria']; ?>">
                <?php echo $categorias[$p['categoria']] ?? $p['categoria']; ?>
              </span>
            </td>
            <td class="td-qty <?php echo $isLow ? 'td-qty--alerta' : ''; ?>"><?php echo $qty; ?></td>
            <td class="td-preco">R$ <?php echo number_format($preco, 2, ',', '.'); ?></td>
            <td class="td-total">R$ <?php echo number_format($valorTotal, 2, ',', '.'); ?></td>
            <td>
              <?php if ($qty === 0): ?>
                <span class="status status--danger">Esgotado</span>
              <?php elseif ($isLow): ?>
                <span class="status status--warning">⚠️ Crítico</span>
              <?php else: ?>
                <span class="status status--ok">✅ Normal</span>
              <?php endif; ?>
            </td>
            <td class="td-mov">
              <form method="POST" action="estoque.php" style="display:inline">
                <input type="hidden" name="produto_id" value="<?php echo $p['id']; ?>">
                <input type="hidden" name="delta" value="-1">
                <input type="hidden" name="acao" value="mover">
                <button type="submit" class="btn-mov btn-mov--remover" <?php echo $qty === 0 ? 'disabled' : ''; ?>>−</button>
              </form>
              <span class="mov-qty"><?php echo $qty; ?></span>
              <form method="POST" action="estoque.php" style="display:inline">
                <input type="hidden" name="produto_id" value="<?php echo $p['id']; ?>">
                <input type="hidden" name="delta" value="1">
                <input type="hidden" name="acao" value="mover">
                <button type="submit" class="btn-mov btn-mov--adicionar">+</button>
              </form>
            </td>
            <td>
              <a href="estoque.php?excluir=<?php echo $p['id']; ?>"
                 class="btn-excluir"
                 onclick="return confirm('Remover <?php echo htmlspecialchars($p['nome']); ?> do estoque?')">🗑️</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr class="tabela-rodape">
            <td colspan="5"><strong>Total Geral do Estoque</strong></td>
            <td class="td-total td-total--grande" colspan="4">
              R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

  </main>

  <?php require_once '../partials/footer.php'; ?>
</body>
</html>
