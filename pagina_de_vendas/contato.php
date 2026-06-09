<?php
require_once 'header.php';
require_once 'footer.php';
require_once 'icon.php';
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indux | Contato</title>
    <?php echo $icon; ?>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header><?php echo $header;?></header>
<section class="hero-solucoes">
    <h1>
        Entre em contato<br>
        <span class="destaque">estamos prontos para atendê-lo</span>
    </h1>
    <p>
        Fale com nossa equipe e descubra como nossas soluções podem <br> 
        transformar sua indústria.
    </p>
    <span class="status-online">Resposta rápida em até 24h úteis</span>
</section>
<div class="conteiner contato-painel">
    <aside class="conteiner-left contato-info" aria-label="Etapas do atendimento">
        <div class="contato-info-cabecalho">
            <span class="contato-eyebrow"><i></i> Atendimento consultivo</span>
            <h2>Da primeira conversa à evolução da sua operação.</h2>
            <p>Um processo claro, próximo e orientado às necessidades reais da sua indústria.</p>
        </div>

        <div class="orcamento-grid">
            <article class="orcamento-card">
                <div class="orcamento-card-topo">
                    <span class="orcamento-numero">01</span>
                    <span class="orcamento-icone" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M11 4a7 7 0 1 0 4.9 12L20 20.1" />
                            <path d="M15 15.1 20 20" />
                        </svg>
                    </span>
                </div>
                <p class="orcamento-label">Consulta inicial</p>
                <h3>Diagnóstico rápido</h3>
                <p>Entendemos seus desafios e identificamos a solução mais adequada para o seu negócio.</p>
            </article>

            <article class="orcamento-card">
                <div class="orcamento-card-topo">
                    <span class="orcamento-numero">02</span>
                    <span class="orcamento-icone" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="m12 3 2.1 4.3L19 8l-3.5 3.4.8 4.8-4.3-2.3-4.3 2.3.8-4.8L5 8l4.9-.7L12 3Z" />
                            <path d="M5 20h14" />
                        </svg>
                    </span>
                </div>
                <p class="orcamento-label">Entrega</p>
                <h3>Implementação acompanhada</h3>
                <p>Desenvolvemos, testamos e ajustamos cada etapa com acompanhamento próximo da sua equipe.</p>
            </article>

            <article class="orcamento-card">
                <div class="orcamento-card-topo">
                    <span class="orcamento-numero">03</span>
                    <span class="orcamento-icone" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M4 12a8 8 0 0 1 14.9-4" />
                            <path d="M20 4v5h-5" />
                            <path d="M20 12a8 8 0 0 1-14.9 4" />
                            <path d="M4 20v-5h5" />
                        </svg>
                    </span>
                </div>
                <p class="orcamento-label">Suporte</p>
                <h3>Monitoramento contínuo</h3>
                <p>Garantimos evolução constante com suporte técnico e melhorias orientadas por resultados.</p>
            </article>
        </div>

        <div class="contato-compromisso">
            <span aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M12 3 5 6v5c0 4.6 2.9 8.2 7 10 4.1-1.8 7-5.4 7-10V6l-7-3Z" />
                    <path d="m9 12 2 2 4-4" />
                </svg>
            </span>
            <p><strong>Seus dados estão protegidos.</strong> Usamos suas informações apenas para este atendimento.</p>
        </div>
    </aside>

    <div class="conteiner-right">
        <main class="form contato-form-card">
            <form id="formContato" autocomplete="on">
                <div class="contato-form-cabecalho">
                    <div>
                        <span class="contato-eyebrow">Solicite uma conversa</span>
                        <h2 id="contato-form-title">Conte um pouco sobre a sua necessidade</h2>
                    </div>
                    <span class="tempo-resposta">
                        <i></i>
                        Resposta em até 24h úteis
                    </span>
                </div>

                <p class="contato-form-intro">Preencha os dados abaixo e um de nossos especialistas entrará em contato.</p>

                <div class="contato-form-grid">
                    <div class="contato-campo">
                        <label for="nome">Nome completo</label>
                        <input type="text" id="nome" name="nome" placeholder="Como podemos chamar você?" autocomplete="name">
                    </div>

                    <div class="contato-campo">
                        <label for="documento">CPF ou CNPJ <span>*</span></label>
                        <input type="text" id="documento" name="documento" placeholder="00.000.000/0000-00" required>
                    </div>

                    <div class="contato-campo">
                        <label for="email">E-mail principal</label>
                        <input type="email" id="email" name="email" placeholder="voce@empresa.com.br" autocomplete="email">
                    </div>

                    <div class="contato-campo">
                        <label for="numero">Telefone para retorno</label>
                        <input type="tel" id="numero" name="numero" placeholder="(00) 00000-0000" autocomplete="tel">
                    </div>

                    <div class="contato-campo">
                        <label for="setor">Setor da indústria <span>*</span></label>
                        <input type="text" id="setor" name="setor" placeholder="Ex.: metalúrgico, alimentício" required>
                    </div>

                    <div class="contato-campo">
                        <label for="cargo">Cargo <span>*</span></label>
                        <input type="text" id="cargo" name="cargo" placeholder="Seu cargo na empresa" autocomplete="organization-title" required>
                    </div>

                    <div class="contato-campo">
                        <label for="endereco">Endereço <span>*</span></label>
                        <input type="text" id="endereco" name="endereco" placeholder="Cidade e estado" autocomplete="street-address" required>
                    </div>

                    <div class="contato-campo">
                        <label for="urgencia">Plano de interesse</label>
                        <select name="urgencia" id="urgencia">
                            <option value="" selected disabled>Selecione um plano</option>
                            <option value="Starter">Starter</option>
                            <option value="Pro">Pro</option>
                            <option value="Enterprise">Enterprise</option>
                        </select>
                    </div>

                    <div class="contato-campo contato-campo-mensagem">
                        <label for="mensagem">Como podemos ajudar?</label>
                        <textarea id="mensagem" name="mensagem" rows="4" placeholder="Descreva brevemente o cenário da sua operação e o que você busca melhorar."></textarea>
                    </div>
                </div>

                <div class="contato-form-acoes">
                    <p><span>*</span> Campos obrigatórios</p>
                    <button type="button" onclick="window.location.href='../index.html'">
                        Enviar solicitação
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14" />
                            <path d="m14 7 5 5-5 5" />
                        </svg>
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>
<footer><?php echo $footer; ?></footer>
</body>
</html>
