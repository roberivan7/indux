<?php
// Página de Soluções - INDUX Monitoramento Industrial
$paginaAtiva = 'solucoes';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soluções | INDUX - Monitoramento Industrial</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ===== RESET E BASE ===== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --azul-escuro:   #0a1628;
            --azul-medio:    #0d2244;
            --azul-card:     #0f2a52;
            --azul-borda:    #1a3a6e;
            --ciano:         #00c8ff;
            --ciano-hover:   #00a8d8;
            --verde:         #00e676;
            --amarelo:       #ffd600;
            --vermelho:      #ff5252;
            --texto-claro:   #e8eaf6;
            --texto-muted:   #90a4b8;
            --branco:        #ffffff;
            --gradiente:     linear-gradient(135deg, #0a1628 0%, #0d2244 100%);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--azul-escuro);
            color: var(--texto-claro);
            line-height: 1.6;
            min-height: 100vh;
        }

        a { text-decoration: none; color: inherit; }

        /* ===== NAVBAR ===== */
        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            background: rgba(10, 22, 40, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--azul-borda);
            padding: 0 2rem;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .navbar-logo img {
            height: 36px;
            object-fit: contain;
        }

        .navbar-logo .marca {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: 3px;
            color: var(--ciano);
            text-transform: uppercase;
        }

        .navbar-logo .marca span {
            color: var(--branco);
        }

        .navbar-links {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            list-style: none;
        }

        .navbar-links a {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--texto-muted);
            transition: all 0.2s;
        }

        .navbar-links a:hover,
        .navbar-links a.ativo {
            color: var(--ciano);
            background: rgba(0, 200, 255, 0.08);
        }

        .navbar-links a.ativo {
            border-bottom: 2px solid var(--ciano);
            border-radius: 0;
        }

        .btn-demo {
            padding: 0.5rem 1.25rem;
            background: var(--ciano);
            color: var(--azul-escuro) !important;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.2s !important;
        }

        .btn-demo:hover {
            background: var(--ciano-hover) !important;
            color: var(--azul-escuro) !important;
        }

        /* ===== HERO SOLUÇÕES ===== */
        .hero-solucoes {
            padding: 140px 2rem 80px;
            text-align: center;
            background: var(--gradiente);
            position: relative;
            overflow: hidden;
        }

        .hero-solucoes::before {
            content: '';
            position: absolute;
            top: -50%;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(0,200,255,0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-solucoes .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(0, 200, 255, 0.1);
            border: 1px solid rgba(0, 200, 255, 0.3);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--ciano);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .hero-solucoes h1 {
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 1.25rem;
            color: var(--branco);
        }

        .hero-solucoes h1 .destaque {
            color: var(--ciano);
        }

        .hero-solucoes p {
            font-size: 1.1rem;
            color: var(--texto-muted);
            max-width: 640px;
            margin: 0 auto 2rem;
        }

        /* ===== SEÇÃO: GRID DE SOLUÇÕES ===== */
        .secao {
            padding: 80px 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .secao-titulo {
            text-align: center;
            margin-bottom: 3rem;
        }

        .secao-titulo .label {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--ciano);
            margin-bottom: 0.75rem;
        }

        .secao-titulo h2 {
            font-size: clamp(1.6rem, 3vw, 2.4rem);
            font-weight: 700;
            color: var(--branco);
            margin-bottom: 0.75rem;
        }

        .secao-titulo p {
            color: var(--texto-muted);
            font-size: 1rem;
            max-width: 560px;
            margin: 0 auto;
        }

        /* ===== CARDS DE SOLUÇÃO ===== */
        .grid-solucoes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .card-solucao {
            background: var(--azul-card);
            border: 1px solid var(--azul-borda);
            border-radius: 16px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card-solucao::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--cor-acento, var(--ciano));
            opacity: 0;
            transition: opacity 0.3s;
        }

        .card-solucao:hover {
            transform: translateY(-4px);
            border-color: var(--cor-acento, var(--ciano));
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4);
        }

        .card-solucao:hover::before {
            opacity: 1;
        }

        .card-icone {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: rgba(0, 200, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 1.25rem;
            border: 1px solid rgba(0, 200, 255, 0.15);
        }

        .card-solucao h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--branco);
            margin-bottom: 0.75rem;
        }

        .card-solucao p {
            font-size: 0.9rem;
            color: var(--texto-muted);
            line-height: 1.7;
            margin-bottom: 1.25rem;
        }

        .card-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .tag {
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 600;
            background: rgba(0, 200, 255, 0.1);
            color: var(--ciano);
            border: 1px solid rgba(0, 200, 255, 0.2);
        }

        /* Variações de cor por card */
        .card-solucao.verde  { --cor-acento: var(--verde); }
        .card-solucao.verde  .card-icone { background: rgba(0,230,118,0.1); border-color: rgba(0,230,118,0.15); }
        .card-solucao.verde  .tag { background: rgba(0,230,118,0.1); color: var(--verde); border-color: rgba(0,230,118,0.2); }

        .card-solucao.amarelo .card-icone { background: rgba(255,214,0,0.1); border-color: rgba(255,214,0,0.15); }
        .card-solucao.amarelo { --cor-acento: var(--amarelo); }
        .card-solucao.amarelo .tag { background: rgba(255,214,0,0.1); color: var(--amarelo); border-color: rgba(255,214,0,0.2); }

        .card-solucao.roxo   { --cor-acento: #b388ff; }
        .card-solucao.roxo   .card-icone { background: rgba(179,136,255,0.1); border-color: rgba(179,136,255,0.15); }
        .card-solucao.roxo   .tag { background: rgba(179,136,255,0.1); color: #b388ff; border-color: rgba(179,136,255,0.2); }

        .card-solucao.laranja { --cor-acento: #ff9100; }
        .card-solucao.laranja .card-icone { background: rgba(255,145,0,0.1); border-color: rgba(255,145,0,0.15); }
        .card-solucao.laranja .tag { background: rgba(255,145,0,0.1); color: #ff9100; border-color: rgba(255,145,0,0.2); }

        /* ===== SEÇÃO: COMO FUNCIONA ===== */
        .secao-processo {
            padding: 80px 2rem;
            background: rgba(13, 34, 68, 0.5);
            border-top: 1px solid var(--azul-borda);
            border-bottom: 1px solid var(--azul-borda);
        }

        .secao-processo .inner {
            max-width: 1200px;
            margin: 0 auto;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 3rem;
            position: relative;
        }

        .step {
            text-align: center;
            padding: 2rem 1.5rem;
            background: var(--azul-card);
            border: 1px solid var(--azul-borda);
            border-radius: 12px;
            position: relative;
        }

        .step-numero {
            width: 48px;
            height: 48px;
            background: var(--ciano);
            color: var(--azul-escuro);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 800;
            margin: 0 auto 1rem;
        }

        .step h4 {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--branco);
            margin-bottom: 0.5rem;
        }

        .step p {
            font-size: 0.85rem;
            color: var(--texto-muted);
        }

        /* ===== SEÇÃO: MÉTRICAS ===== */
        .secao-metricas {
            padding: 80px 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .grid-metricas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .metrica-card {
            background: var(--azul-card);
            border: 1px solid var(--azul-borda);
            border-radius: 12px;
            padding: 1.75rem;
            text-align: center;
        }

        .metrica-numero {
            font-size: 2.4rem;
            font-weight: 800;
            color: var(--ciano);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .metrica-label {
            font-size: 0.85rem;
            color: var(--texto-muted);
        }

        /* ===== CTA FINAL ===== */
        .secao-cta {
            padding: 80px 2rem;
            background: linear-gradient(135deg, rgba(0,200,255,0.06) 0%, rgba(13,34,68,0.8) 100%);
            border-top: 1px solid var(--azul-borda);
            text-align: center;
        }

        .secao-cta h2 {
            font-size: clamp(1.6rem, 3vw, 2.4rem);
            font-weight: 800;
            color: var(--branco);
            margin-bottom: 1rem;
        }

        .secao-cta p {
            font-size: 1rem;
            color: var(--texto-muted);
            max-width: 520px;
            margin: 0 auto 2.5rem;
        }

        .cta-botoes {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-primario {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.85rem 2rem;
            background: var(--ciano);
            color: var(--azul-escuro);
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .btn-primario:hover {
            background: var(--ciano-hover);
            transform: translateY(-2px);
        }

        .btn-secundario {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.85rem 2rem;
            background: transparent;
            color: var(--texto-claro);
            border: 1px solid var(--azul-borda);
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .btn-secundario:hover {
            border-color: var(--ciano);
            color: var(--ciano);
            transform: translateY(-2px);
        }

        /* ===== FOOTER ===== */
        .footer {
            background: rgba(5, 12, 22, 0.95);
            border-top: 1px solid var(--azul-borda);
            padding: 2rem;
            text-align: center;
        }

        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-marca {
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 3px;
            color: var(--ciano);
        }

        .footer-texto {
            font-size: 0.82rem;
            color: var(--texto-muted);
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
            list-style: none;
        }

        .footer-links a {
            font-size: 0.85rem;
            color: var(--texto-muted);
            transition: color 0.2s;
        }

        .footer-links a:hover { color: var(--ciano); }

        /* ===== STATUS BADGE ===== */
        .status-online {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            color: var(--verde);
            font-weight: 600;
        }

        .status-online::before {
            content: '';
            width: 7px;
            height: 7px;
            background: var(--verde);
            border-radius: 50%;
            animation: pulso 1.5s infinite;
        }

        @keyframes pulso {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(0,230,118,0.4); }
            50% { opacity: 0.8; box-shadow: 0 0 0 6px rgba(0,230,118,0); }
        }

        /* ===== RESPONSIVO ===== */
        @media (max-width: 768px) {
            .navbar-links { display: none; }
            .hero-solucoes { padding: 100px 1.25rem 60px; }
            .secao { padding: 60px 1.25rem; }
            .grid-solucoes { grid-template-columns: 1fr; }
            .footer-inner { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
    <a href="index.php" class="navbar-logo">
        <img src="../IMG/logo.png" alt="INDUX Logo" onerror="this.style.display='none'">
        <span class="marca">IND<span>UX</span></span>
    </a>

    <ul class="navbar-links">
        <li><a href="index.php">Início</a></li>
        <li><a href="solucoes.php" class="ativo">Soluções</a></li>
        <li><a href="sobre.php">Sobre</a></li>
        <li><a href="../PHP/login.php" class="btn-demo">Acessar Sistema</a></li>
    </ul>
</nav>

<!-- ===== HERO ===== -->
<section class="hero-solucoes">
    <div class="badge">
        ⚡ Monitoramento Industrial
    </div>
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

<!-- ===== CARDS DE SOLUÇÕES ===== -->
<section style="padding: 80px 2rem; background: var(--azul-escuro);">
    <div style="max-width:1200px; margin:0 auto;">

        <div class="secao-titulo">
            <span class="label">O que oferecemos</span>
            <h2>Cada módulo, um problema resolvido</h2>
            <p>Ferramentas criadas para operadores, engenheiros e gestores industriais.</p>
        </div>

        <div class="grid-solucoes">

            <!-- MONITORAMENTO EM TEMPO REAL -->
            <div class="card-solucao">
                <div class="card-icone">📡</div>
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

            <!-- GESTÃO DE ALARMES -->
            <div class="card-solucao amarelo">
                <div class="card-icone">🔔</div>
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

            <!-- CADASTRO DE EQUIPAMENTOS -->
            <div class="card-solucao verde">
                <div class="card-icone">🏭</div>
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

            <!-- DASHBOARD ANALÍTICO -->
            <div class="card-solucao roxo">
                <div class="card-icone">📊</div>
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

            <!-- CONTROLE DE ACESSO -->
            <div class="card-solucao laranja">
                <div class="card-icone">🔐</div>
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

            <!-- LOGS DO SISTEMA -->
            <div class="card-solucao">
                <div class="card-icone">📋</div>
                <h3>Log e Rastreabilidade</h3>
                <p>
                    Cada ação relevante é registrada no log do sistema: quem resolveu
                    um alarme, quando um equipamento foi alterado, novas leituras
                    inseridas. Auditoria completa para conformidade industrial.
                </p>
                <div class="card-tags">
                    <span class="tag">Log de eventos</span>
                    <span class="tag">Auditoria</span>
                    <span class="tag">Conformidade</span>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ===== COMO FUNCIONA ===== -->
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

<!-- ===== MÉTRICAS ===== -->
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

<!-- ===== CTA ===== -->
<section class="secao-cta">
    <h2>Pronto para monitorar sua indústria?</h2>
    <p>
        Acesse o sistema agora, cadastre seus equipamentos e
        tenha controle total da sua operação em minutos.
    </p>
    <div class="cta-botoes">
        <a href="../PHP/login.php" class="btn-primario">
            ⚡ Acessar o Sistema
        </a>
        <a href="sobre.php" class="btn-secundario">
            Saiba mais sobre nós →
        </a>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="footer">
    <div class="footer-inner">
        <span class="footer-marca">INDUX</span>
        <span class="footer-texto">© <?php echo date('Y'); ?> INDUX — Monitoramento Industrial. Todos os direitos reservados.</span>
        <ul class="footer-links">
            <li><a href="index.php">Início</a></li>
            <li><a href="solucoes.php">Soluções</a></li>
            <li><a href="sobre.php">Sobre</a></li>
            <li><a href="../PHP/login.php">Login</a></li>
        </ul>
    </div>
</footer>

</body>
</html>