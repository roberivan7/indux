<?php
// Calcula o resumo financeiro para exibir no rodapé
$totalValor    = 0;
$totalItens    = 0;
$totalAlertas  = 0;

if (isset($_SESSION['produtos']) && is_array($_SESSION['produtos'])) {
    foreach ($_SESSION['produtos'] as $p) {
        $qty = isset($p['quantidade']) ? (int)$p['quantidade'] : 0;
        $preco = isset($p['preco']) ? (float)$p['preco'] : 0;
        $totalValor += $qty * $preco;
        $totalItens += $qty;
        if ($qty <= ESTOQUE_MINIMO) $totalAlertas++;
    }
}
?>
<footer class="site-footer">
  <div class="site-footer__inner">
    <div class="footer-resumo">
      <div class="footer-stat">
        <span class="footer-stat__label">💰 Valor Total em Estoque</span>
        <strong class="footer-stat__value footer-stat__value--green">
          R$ <?php echo number_format($totalValor, 2, ',', '.'); ?>
        </strong>
      </div>
      <div class="footer-stat">
        <span class="footer-stat__label">📦 Unidades em Estoque</span>
        <strong class="footer-stat__value"><?php echo $totalItens; ?> un</strong>
      </div>
      <?php if ($totalAlertas > 0): ?>
      <div class="footer-stat">
        <span class="footer-stat__label">⚠️ Itens com Estoque Baixo</span>
        <strong class="footer-stat__value footer-stat__value--yellow"><?php echo $totalAlertas; ?> produto(s)</strong>
      </div>
      <?php endif; ?>
    </div>
    <nav aria-label="Rodapé">
      <small>&copy; <?php echo date('Y'); ?> <?php echo $nomeLoja; ?> — A solução para seus problemas</small>
      <small><br>Trabalho feito por Filipe Dupin 2MD, <b>Alisson</b> não compareceu as aulas, então decidi fazer sem duplas</small>
    </nav>
  </div>
</footer>
