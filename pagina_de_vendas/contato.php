<?php
require_once 'header.php';
require_once 'footer.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
<div class="conteiner">
    <div class="conteiner-left">
        <main class="form orcamento" id="orcamento">
            <div class="orcamento-grid">
                <article class="orcamento-card">
                    <p class="orcamento-label">Consulta inicial</p>
                    <h2>Diagnóstico rápido</h2>
                    <p>Entendemos suas necessidades e mapeamos a melhor solução para seu negócio.</p>
                </article>
                <article class="orcamento-card">
                    <p class="orcamento-label">Entrega</p>
                    <h2>Implementação</h2>
                    <p>Desenvolvemos, testamos e ajustamos o fluxo com acompanhamento próximo ao cliente.</p>
                </article>
                <article class="orcamento-card">
                    <p class="orcamento-label">Suporte</p>
                    <h2>Monitoramento contínuo</h2>
                    <p>Garantimos evolução constante com suporte técnico e melhorias contínuas.</p>
                </article>
            </div>
        </main>
    </div>
    <div class="conteiner-right">
        <main class="form">
            <form>
                <p class="Titulo-Orcamento"></p>
                <div class="conteiner-f">
                    <div class="inpt l">
                        <label for="nome">Nome Completo:</label>
                        <input type="text" id="nome" name="nome" placeholder="Digite o seu nome ou o de sua empresa">
                           
                        <label for="email">Email Principal:</label>
                        <input type="email" id="email" name="email" placeholder="Digite seu Email">


                        <label for="setor">Setor da indústria*</label>
                        <input type="text" id="setor" name="setor" placeholder="Digite o setor da indústria">


                        <label for="cargo">Cargo*</label>
                        <input type="text" id="cargo" name="cargo" placeholder="Digite seu cargo">
                    </div>
                    <div class="inpt r">
                        <label for="documento">CNPJ ou CPF*</label>
                        <input type="text" id="documento" name="documento" placeholder="CNPJ ou CPF">


                        <label for="endereco">ENDEREÇO*</label>
                        <input type="text" id="endereco" name="endereco" placeholder="Digite o endereço">


                        <label for="numero">Número para Retorno:</label>
                        <input type="number" id="numero" name="numero" placeholder="Digite seu Número">


                        <label for="urgencia">Planos de serviço</label>
                        <select name="urgencia" id="urgencia">
                            <option>1</option>
                            <option>2</option>
                            <option>3</option>
                        </select>
                    </div>
                </div>
                    <div class="inpt">
                           
                        <label for="mensagem">Fale Aqui:</label>
                        <textarea id="mensagem" name="mensagem" rows="2"  draggable="true"  ></textarea>
                        <button><a href="../index.html">Enviar</a></button>
                    </div>
            </form>
        </main>
    </div>
</div>
<footer><?php echo $footer; ?></footer>

<script>
const formContato = document.getElementById('formContato');
const popupContato = document.getElementById('popupContato');
const fecharPopupContato = document.getElementById('fecharPopupContato');

function abrirPopupContato() {
    popupContato.hidden = false;
    popupContato.classList.add('ativo');
    fecharPopupContato.focus();
}

function fecharPopup() {
    popupContato.classList.remove('ativo');
    popupContato.hidden = true;
}

formContato.addEventListener('submit', function(evento) {
    evento.preventDefault();

    if (!formContato.checkValidity()) {
        formContato.reportValidity();
        return;
    }

    formContato.reset();
    abrirPopupContato();
});

fecharPopupContato.addEventListener('click', fecharPopup);
popupContato.addEventListener('click', function(evento) {
    if (evento.target === popupContato) {
        fecharPopup();
    }
});
document.addEventListener('keydown', function(evento) {
    if (evento.key === 'Escape' && !popupContato.hidden) {
        fecharPopup();
    }
});
</script>
</body>
</html>
