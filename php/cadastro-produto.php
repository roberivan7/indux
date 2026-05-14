<?php
require_once 'init.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids    = array_column($_SESSION['produtos'], 'id');
    $novoId = $ids ? max($ids) + 1 : 1;

    $nome      = trim($_POST['nome'] ?? '');
    $preco     = str_replace(',', '.', $_POST['preco'] ?? '0');
    $categoria = $_POST['categoria'] ?? '';
    $quantidade = max(0, (int)($_POST['quantidade'] ?? 0));
    $descricao = trim($_POST['descricao'] ?? '');
    $imagem    = trim($_POST['imagem'] ?? '../imagem/Cimento.jpg');

    if ($nome && $preco && $categoria) {
        $_SESSION['produtos'][] = [
            'id'         => $novoId,
            'nome'       => $nome,
            'preco'      => (float)$preco,
            'categoria'  => $categoria,
            'quantidade' => $quantidade,
            'descricao'  => $descricao,
            'imagem'     => $imagem ?: '../imagem/Cimento.jpg',
        ];
        // Salva no JSON permanentemente
        salvarProdutos($_SESSION['produtos']);
        header('Location: estoque.php?msg=' . urlencode("✅ Produto \"{$nome}\" cadastrado com sucesso!") . '&tipo=sucesso');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastrar Produto — <?php echo $nomeLoja; ?></title>
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
  <?php require_once '../partials/header.php'; ?>

  <main class="site-main">
    <div class="page-header">
      <div>
        <h1 class="page-title">➕ Cadastrar Produto</h1>
        <p class="page-subtitle">Adicione um novo item ao estoque da <?php echo $nomeLoja; ?></p>
      </div>
      <a href="estoque.php" class="btn btn--outline">← Voltar ao Estoque</a>
    </div>

    <form class="formulario" action="cadastro-produto.php" method="post">

      <div class="form-row">
        <div class="form-group">
          <label for="nome">Nome do Produto</label>
          <input type="text" id="nome" name="nome" placeholder="Ex: Cimento CP II 50kg" required>
        </div>
        <div class="form-group">
          <label for="categoria">Categoria</label>
          <select id="categoria" name="categoria" required>
            <option value="">Selecione</option>
            <option value="bruto">🪨 Bruto (cimento, areia)</option>
            <option value="ferramentas">🔧 Ferramentas (martelos, furadeiras)</option>
            <option value="acabamento">🏠 Acabamento (pisos, torneiras)</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="preco">Preço Unitário (R$)</label>
          <input type="text" id="preco" name="preco" placeholder="Ex: 42,90" required>
        </div>
        <div class="form-group">
          <label for="quantidade">Quantidade em Estoque</label>
          <input type="number" id="quantidade" name="quantidade" placeholder="Ex: 50" min="0" required>
        </div>
      </div>

      <div class="form-group">
        <label for="descricao">Descrição</label>
        <textarea id="descricao" name="descricao" placeholder="Breve descrição do produto"></textarea>
      </div>

      <div class="form-group">
        <label for="imagem">URL ou Caminho da Imagem</label>
        <input type="text" id="imagem" name="imagem" placeholder="https://... ou ../imagem/foto.jpg">
        <small class="form-hint">Deixe em branco para usar imagem padrão. Para imagens locais use: <code>../imagem/nome.jpg</code></small>
      </div>

      <div class="form-acoes">
        <a href="estoque.php" class="btn btn--outline">Cancelar</a>
        <button type="submit" class="btn btn--primary">Cadastrar Produto</button>
      </div>
    </form>
  </main>

  <?php require_once '../partials/footer.php'; ?>
</body>
</html>
