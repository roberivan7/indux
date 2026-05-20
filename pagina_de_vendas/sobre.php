<?php
require_once 'header.php';
require_once 'footer.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nós - INDUX</title>
    <link rel="stylesheet" href="styles.css">
    </head>

<body>
    <?php echo $header;?>

    <section class="hero-section">
        <div class="badge">Monitoramento Industrial</div>
        <h1 class="hero-title">Transformando o caos do chão de fábrica em <span>clareza absoluta</span>.</h1>
        <p class="hero-subtitle"> Do sensor ao dashboard — conheça a história por trás da plataforma inteligente que unifica a sua operação.</p>
    </section>

    <section class="history-section">
    <div class="container-grid">

        <div class="history-content">
            <h2>Como Nascemos</h2>
            <p>Fundada por <strong>engenheiros de software</strong> com a ajuda de especialistas em automação e sistemas críticos, a <strong>INDUX</strong> nasceu para resolver um problema concreto: indústrias operando às cegas, reféns de falhas inesperadas e monitoramentos complexos.</p>
            <p>Entendemos que a complexidade técnica muitas vezes afasta a eficiência. Por isso, nossa engenharia foi direcionada para unificar o que realmente importa, trazendo previsibilidade para ambientes de alta exigência.</p>
        </div>

        <div class="mission-box">
            <h3>A Nossa Solução</h3>
            <p> Unificamos a supervisão de processos, pressão e temperatura em uma única plataforma inteligente. Com um cadastro simplificado de equipamentos e alertas em tempo real, eliminamos o escuro operacional.</p>
        </div>

    </div>
    </section>

    <section class="pillars-section">
    <span class="section-tag">O que oferecemos</span>
        <div class="pillars-grid">

        <div class="pillar-card">
            <h4>Cadastro Simplificado</h4>
            <p>Centralize equipamentos, leituras e alarmes de forma rápida e intuitiva, desenhada para operadores e gestores.</p>
        </div>

        <div class="pillar-card">
            <h4>Alertas em Tempo Real</h4>
            <p>Monitore pressão e temperatura de sistemas críticos instantaneamente, mitigando falhas antes que elas aconteçam.</p>
        </div>

        <div class="pillar-card">
            <h4>Visibilidade de Ativos</h4>
            <p>Saiba com precisão cirúrgica e em tempo real qual ativo no seu ecossistema industrial está <span class="status-active">Ativo</span>, <span class="status-inactive">Inativo</span> ou <span class="status-failure">Em Falha</span>.</p>
        </div>

    </div>
    </section>

    <?php echo $footer; ?>
</body>
</html>
