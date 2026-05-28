<?php
require_once 'header.php';
require_once 'footer.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
<header>
    <?php echo $header; ?>
</header>

<div class="conteiner">
    <div class="conteiner-f">
        <main class="form orcamento" id="orcamento">
            <div class="orcamento-grid">
                <article class="orcamento-card">
                    <p class="orcamento-label">Consulta inicial</p>
                    <h2>Diagnóstico rápido</h2>
                    <p>Entendemos suas necessidades e mapeamos a melhor solução para seu negócio.</p>
                </article>

                <article class="orcamento-card">
                    <p class="orcamento-label">Escopo</p>
                    <h2>Estrutura do projeto</h2>
                    <p>Definimos funcionalidades, prazos e prioridades para o lançamento sem surpresas.</p>
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

<<<<<<< HEAD
            <form id="formContato">
                <p class="Titulo-Orcamento">Entre em Contato</p>
                <div class="conteiner-f">
                    <div class="teste">
                       
=======
        <main class="form contato">
            <form>
                <h1>Entre em Contato</h1>
                <div class="form-linha">
                    <div class="input-group">
>>>>>>> ac4e1636b13af18baddfc2eae3a3d2a0d0976cfa
                        <label for="nome">Nome Completo:</label>
                        <input type="text" id="nome" name="nome" placeholder="Digite o seu nome ou o de sua empresa">
                    </div>

                    <div class="input-group">
                        <label for="numero">Número para Retorno:</label>
                        <input type="text" id="numero" name="numero" placeholder="Digite seu Número">
                    </div>
                </div>

                <div class="form-linha">
                    <div class="input-group">
                        <label for="email">Email Principal:</label>
                        <input type="email" id="email" name="email" placeholder="Digite seu Email">
                    </div>

                    <div class="input-group">
                        <label for="urgencia">Planos de serviço</label>

                        <select name="urgencia" id="urgencia">
                            <option>Starter</option>
                            <option>Pro</option>
                            <option>Enterprise</option>
                        </select>
                    </div>
                </div>
<<<<<<< HEAD
                    <div class="teste">
                           
                        <label for="mensagem">Fale Aqui:</label>
                        <textarea id="mensagem" name="mensagem" rows="2"  draggable="true"  ></textarea>
                        <button type="submit">Enviar --&gt;</button>
                    </div>
=======
>>>>>>> ac4e1636b13af18baddfc2eae3a3d2a0d0976cfa

                <div class="input-group">
                    <label for="mensagem">Fale Aqui:</label>
                    <textarea id="mensagem" name="mensagem" placeholder="Digite sua mensagem..."></textarea>

                </div>

                <button type="submit" class="btn-enviar">Enviar</button>
            </form>
        </main>
    </div>
</div>
<<<<<<< HEAD
<div class="contato-popup" id="popupContato" hidden>
    <div class="contato-popup__caixa" role="dialog" aria-modal="true" aria-labelledby="popupContatoTitulo">
        <div class="contato-popup__icone">OK</div>
        <h2 id="popupContatoTitulo">Mensagem enviada com sucesso!</h2>
        <button type="button" class="contato-popup__botao" id="fecharPopupContato">Fechar</button>
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
=======

<footer>
    <?php echo $footer; ?>
</footer>

>>>>>>> ac4e1636b13af18baddfc2eae3a3d2a0d0976cfa
</body>
</html>
