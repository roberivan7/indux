<?php
require_once 'icon.php';
$totalEquipamentos = 0;
$totalAtivos       = 0;
$totalFalhas       = 0;
$alarmesCriticos   = 0;

try {
    require_once 'db.php';
    $db = getDB();
    $consultaTotal = $db->query("SELECT COUNT(*) FROM equipamentos");
    $totalEquipamentos = (int)$consultaTotal->fetchColumn();

    $consultaAtivos = $db->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'ativo'");
    $totalAtivos = (int)$consultaAtivos->fetchColumn();

    $consultaFalhas = $db->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'em_falha'");
    $totalFalhas = (int)$consultaFalhas->fetchColumn();

    $consultaAlarmesCriticos = $db->query(
        "SELECT COUNT(*)
           FROM alarmes a
           JOIN equipamentos e ON e.id = a.equipamento_id
          WHERE a.resolvido = 0
            AND a.severidade = 'critico'
            AND e.status <> 'inativo'"
    );
    $alarmesCriticos = (int)$consultaAlarmesCriticos->fetchColumn();
} catch (Throwable $e) {

}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo $icon; ?>
<title>Indux | Footer</title>
  <link rel="stylesheet" href="../CSS/styles.css">
</head>
<footer class="site-footer">
  <div class="site-footer__inner">

    <div style="display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap">
      <div class="footer-stat">
        <span class="footer-stat__label">⚙️ Equipamentos</span>
        <strong class="footer-stat__value text-cyan"><?php echo $totalEquipamentos; ?></strong>
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
      <?php if ($alarmesCriticos > 0): ?>
      <div class="footer-stat">
        <span class="footer-stat__label">🔔 Alarmes Críticos</span>
        <strong class="footer-stat__value footer-stat__value--yellow"><?php echo $alarmesCriticos; ?></strong>
      </div>
      <?php endif; ?>
    </div>

    <div class="footer-copy">
      &copy; <?php echo EMPRESA_ANO; ?> <?php echo SISTEMA_NOME; ?> — <?php echo SISTEMA_TAGLINE; ?>
    </div>

  </div>
</footer>
</html>
