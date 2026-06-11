<?php
require_once 'icon.php';
require_once 'header.php';
require_once 'footer.php';

$paginaAtiva = 'solucoes';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <?php echo $icon; ?>
<title>Indux | Soluções</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    

</head>
<body>
<header><?php echo $header;?></header>
<section class="hero-solucoes">
    <h1>
        Soluções completas para<br>
        <span class="destaque">indústrias inteligentes</span>
    </h1>
    <p>
        Do sensor ao dashboard — o INDUX centraliza equipamentos, leituras,
        alarmes e equipes em uma única plataforma industrial.
    </p>
    <span class="status-online">Sistema operacional 24/7</span>
</section>

<section style="padding: 80px 2rem; background: var(--azul-escuro);">
    <div style="max-width:1200px; margin:0 auto;">

        <div class="secao-titulo">
            <span class="label">O que oferecemos</span>
            <h2>Cada módulo, um problema resolvido</h2>
            <p>Ferramentas criadas para operadores, engenheiros e gestores industriais.</p>
        </div>

        <div class="grid-solucoes">

            <div class="card-solucao">
                <div class="card-icone">{{lucide:radio}}</div>
                <h3>Monitoramento em Tempo Real</h3>
                <p>
                    Acompanhe leituras de sensores — temperatura, pressão e mais —
                    de todos os equipamentos em uma tela unificada. Dados atualizados
                    continuamente sem necessidade de refresh manual.
                </p>
                <div class="card-tags">
                    <span class="tag">Leituras de sensor</span>
                    <span class="tag">Atualização contínua</span>
                    <span class="tag">Multi-equipamento</span>
                </div>
            </div>

            <div class="card-solucao amarelo">
                <div class="card-icone">{{lucide:bell}}</div>
                <h3>Gestão de Alarmes</h3>
                <p>
                    Alarmes gerados automaticamente quando leituras ultrapassam limites
                    configurados. Filtre por severidade e status, e resolva ocorrências
                    diretamente na plataforma com registro de histórico completo.
                </p>
                <div class="card-tags">
                    <span class="tag">Crítico / Aviso</span>
                    <span class="tag">Auto-geração</span>
                    <span class="tag">Histórico</span>
                </div>
            </div>

            <div class="card-solucao verde">
                <div class="card-icone">{{lucide:factory}}</div>
                <h3>Cadastro de Equipamentos</h3>
                <p>
                    Registre, edite e controle o status de cada equipamento do parque
                    industrial. Busque e filtre por setor, status ou tipo. Toda a
                    operação documentada e rastreável desde o cadastro.
                </p>
                <div class="card-tags">
                    <span class="tag">CRUD completo</span>
                    <span class="tag">Status ativo/inativo</span>
                    <span class="tag">Busca avançada</span>
                </div>
            </div>

            <div class="card-solucao roxo">
                <div class="card-icone">{{lucide:chart-no-axes-combined}}</div>
                <h3>Dashboard Analítico</h3>
                <p>
                    Visão executiva com KPIs consolidados: total de equipamentos,
                    leituras recentes, alarmes ativos e tendências. Tome decisões
                    baseadas em dados, não em suposições.
                </p>
                <div class="card-tags">
                    <span class="tag">KPIs em tempo real</span>
                    <span class="tag">Últimas leituras</span>
                    <span class="tag">Alarmes ativos</span>
                </div>
            </div>

            <div class="card-solucao laranja">
                <div class="card-icone">{{lucide:lock-keyhole}}</div>
                <h3>Controle de Acesso e Usuários</h3>
                <p>
                    Gerencie usuários com perfis e permissões diferenciadas. Defina
                    quem pode cadastrar equipamentos, resolver alarmes ou administrar
                    o sistema — com segurança por sessão PHP.
                </p>
                <div class="card-tags">
                    <span class="tag">Perfis e permissões</span>
                    <span class="tag">CRUD de usuários</span>
                    <span class="tag">Autenticação segura</span>
                </div>
            </div>

            <div class="card-solucao">
                <div class="card-icone">{{lucide:clock-3}}</div>
                <h3>Suporte 24 horas</h3>
                <p>Atendimento contínuo para monitoramento, suporte técnico e resposta rápida a falhas industriais, garantindo estabilidade e segurança operacional da planta.</p>
                <div class="card-tags">
                    <span class="tag">Atendimento contínuo</span>
                    <span class="tag">Resposta rápida a falhas</span>
                    <span class="tag">Monitoramento 24/7</span>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="secao-processo">
    <div class="inner">
        <div class="secao-titulo">
            <span class="label">Fluxo operacional</span>
            <h2>Como o INDUX funciona</h2>
            <p>Do cadastro ao alerta — tudo automatizado e integrado.</p>
        </div>

        <div class="steps">
            <div class="step">
                <div class="step-numero">1</div>
                <h4>Cadastre Equipamentos</h4>
                <p>Registre máquinas e sensores com seus limites operacionais.</p>
            </div>
            <div class="step">
                <div class="step-numero">2</div>
                <h4>Registre Leituras</h4>
                <p>Insira manualmente ou integre leituras de sensores via sistema.</p>
            </div>
            <div class="step">
                <div class="step-numero">3</div>
                <h4>Alarmes Automáticos</h4>
                <p>Se uma leitura ultrapassar o limite, o alarme é criado automaticamente.</p>
            </div>
            <div class="step">
                <div class="step-numero">4</div>
                <h4>Resolva e Documente</h4>
                <p>Equipes agem sobre alarmes e tudo fica registrado no histórico.</p>
            </div>
            <div class="step">
                <div class="step-numero">5</div>
                <h4>Analise no Dashboard</h4>
                <p>Gestores acompanham KPIs e tendências em tempo real.</p>
            </div>
        </div>
    </div>
</section>

<section class="secao-metricas">
    <div class="secao-titulo">
        <span class="label">Por que escolher o INDUX</span>
        <h2>Números que falam por si</h2>
    </div>
    <div class="grid-metricas">
        <div class="metrica-card">
            <div class="metrica-numero">100%</div>
            <div class="metrica-label">Web-based — sem instalação</div>
        </div>
        <div class="metrica-card">
            <div class="metrica-numero">5</div>
            <div class="metrica-label">Módulos integrados</div>
        </div>
        <div class="metrica-card">
            <div class="metrica-numero">24/7</div>
            <div class="metrica-label">Monitoramento contínuo</div>
        </div>
        <div class="metrica-card">
            <div class="metrica-numero">PHP</div>
            <div class="metrica-label">Stack simples e robusto</div>
        </div>
    </div>
</section>

<section class="secao-cta">
    <h2>Pronto para monitorar sua indústria?</h2>
    <p>
        Acesse o sistema agora, cadastre seus equipamentos e
        tenha controle total da sua operação em minutos.
    </p>
    <div class="cta-botoes">
        <a href="../PHP/login.php" class="btn-primario">
            {{lucide:zap}} Acessar o Sistema
        </a>
        <a href="sobre.php" class="btn-secundario">
            Saiba mais sobre nós →
        </a>
    </div>
</section>

<?php echo $footer; ?>
</body>
</html>