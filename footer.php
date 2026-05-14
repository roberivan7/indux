<?php
// Estatísticas resumidas para o rodapé
$totalEquip    = 0;
$totalAtivos   = 0;
$totalFalhas   = 0;
$alarmesCrit   = 0;

try {
    require_once 'db.php';
    $db = getDB();
    $r1 = $db->query("SELECT COUNT(*) FROM equipamentos");
    $totalEquip = (int)$r1->fetchColumn();
    $r2 = $db->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'ativo'");
    $totalAtivos = (int)$r2->fetchColumn();
    $r3 = $db->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'em_falha'");
    $totalFalhas = (int)$r3->fetchColumn();
    $r4 = $db->query("SELECT COUNT(*) FROM alarmes WHERE resolvido = 0 AND severidade = 'critico'");
    $alarmesCrit = (int)$r4->fetchColumn();
} catch (Throwable $e) {
    // silencia
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — INDUX Monitoramento Industrial</title>
  <link rel="stylesheet" href="styles.css">
</head>
<footer class="site-footer">
  <div class="site-footer__inner">

    <div style="display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap">
      <div class="footer-stat">
        <span class="footer-stat__label">⚙️ Equipamentos</span>
        <strong class="footer-stat__value text-cyan"><?php echo $totalEquip; ?></strong>
      </div>
      <div class="footer-stat">
        <span class="footer-stat__label">✅ Ativos</span>
        <strong class="footer-stat__value footer-stat__value--green"><?php echo $totalAtivos; ?></strong>
      </div>
      <?php if ($totalFalhas > 0): ?>
      <div class="footer-stat">
        <span class="footer-stat__label">🔴 Em Falha</span>
        <strong class="footer-stat__value footer-stat__value--red"><?php echo $totalFalhas; ?></strong>
      </div>
      <?php endif; ?>
      <?php if ($alarmesCrit > 0): ?>
      <div class="footer-stat">
        <span class="footer-stat__label">🔔 Alarmes Críticos</span>
        <strong class="footer-stat__value footer-stat__value--yellow"><?php echo $alarmesCrit; ?></strong>
      </div>
      <?php endif; ?>
    </div>

    <div class="footer-copy">
      &copy; <?php echo EMPRESA_ANO; ?> <?php echo SISTEMA_NOME; ?> — <?php echo SISTEMA_TAGLINE; ?>
    </div>

  </div>
</footer>
</html>