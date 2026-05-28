<?php 
require_once 'header.php';
require_once 'footer.php';
require_once 'icon.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indux</title>
    <link rel="stylesheet" href="styles.css">
    <?php echo $icon; ?>
</head>
<body>
    <header><?php echo $header;?></header>
    <main>
        <section class="inicial">
            <div class="container_inicial">
                <h1 class="titulo_site">Controle total da sua <span class="text_destaque">operação industrial</span></h1>
                <p class="texto_inicial">Controle toda a fábrica com monitoramento em tempo real, análise de equipamentos e sensores industriais integrados.</p>    
                <div class="container_btn">
                    <a href="planos.php" class="btn_inicial">Ver Planos <img src="img/arrow-right.png" alt="" class="seta"></a>
                    <a href="contato.php" class="btn_inicial btn_especialista">Falar com um especialista</a></div>
            </div>
            <img src="img/hero-industrial.jpg" alt="" class="img_principal">
        </section>
        <hr>
        <section class="secundario">
            <div><h1>100%</h1><p>SLA de Disponibilidade</p></div>
            <div><h1>87%</h1><p>Eficiência Operacional</p></div>
            <div><h1>+ 180</h1><p>Previsão de Empresas atendias</p> </div>
            <div><h1>24/7</h1><p>Suporte dedicado</p></div>
        </section>
        <hr>
        <section class="terciario">
            <h4 class="subtitulo_terciario">CAPACIDADE</h4>
            <h1 class="titulo_terciario">Uma plataforma. Toda a planta industrial.</h1>
            <p class="texto_teciario">Da aquisição de dados de campo até dashboards executivos, com governança e segurança industrial.</p>
            <div class="container_cards">
                <div class="cards">
                    <div class="img_card">
                        <img src="img/gauge.png" class="img_icon_card" alt="">
                    </div>
                    <h2>Telemetria em tempo real</h2><p>Coleta de dados de PLCs, CLPs e sensores com latência sub-segundo.</p>
                </div>
                <div class="cards">
                    <div class="img_card">
                        <img src="img/zap.png" class="img_icon_card" alt="">
                    </div>
                    <h2>Automação por regras</h2><p>Acione atuadores, alertas e workflows com base em condições da planta.</p>
                </div>
                <div class="cards">
                    <div class="img_card">
                        <img src="img/chart-line.png" class="img_icon_card" alt="">
                    </div>
                    <h2>Analytics Preditivo</h2><p>Modelos de manutenção preditiva e detecção de anomalias com IA.</p>
                </div>            
                <div class="cards">
                    <div class="img_card">
                        <img src="img/shield-check.png" class="img_icon_card" alt="">
                    </div>
                    <h2>Conformidade IEC 62443</h2>
                    <p>Segurança OT/IT em conformidade com normas internacionais de cibersegurança industrial.</p>
                </div>
                <div class="cards">
                    <div class="img_card">
                        <img src="img/cpu.png" class="img_icon_card" alt="">
                    </div>
                    <h2>Edge computing</h2><p>Processamento na borda com gateway certificado para ambientes industriais.</p>
                </div>
                <div class="cards">
                    <div class="img_card">
                        <img src="img/factory.png" class="img_icon_card" alt="">
                    </div>
                    <h2>Multi Setores</h2><p>Gerencie múltiplas setores fabris em um único painel consolidado.</p>
                </div>
        </section>
        <section class="quaternario">
            <div class="container_quaternario">
                <h1 class="titulo_quaternario">Pronto para industrializar seus dados?</h1>
                <p class="texto_quaternario">Agende uma sessão técnica com nossos engenheiros e veja a plataforma rodando com dados da sua operação.</p>
            </div>
            <div class="items_quaternario"><a href="contato.php" class="btn_quaternario">Solicitação Demonstração</a></div>
        </section>
        <hr>
        <footer><?php echo $footer; ?></footer>
</main>
</body>
</html>