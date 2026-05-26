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
<div class="conteiner">
    <div class="conteiner-centralizar">
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
    </div>
    <div class="conteiner-centralizar">


        <main class="form">

            <form>
                <p class="Titulo-Orcamento">Entre em Contato</p>
                <div class="conteiner-f">
                    <div class="teste">
                       
                        <label for="nome">Nome Completo:</label>
                        <input type="text" id="nome" name="nome" placeholder="Digite o seu nome ou o de sua empresa">
                           
                        <label for="email">Email Principal:</label>
                        <input type="email" id="email" name="email" placeholder="Digite seu Email">
                    </div>
                    <div class="teste">
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
                    <div class="teste">
                           
                        <label for="mensagem">Fale Aqui:</label>
                        <textarea id="mensagem" name="mensagem" rows="2"  draggable="true"  ></textarea>
                        <button><a href="../index.html">Enviar  --></a></button>
                    </div>


            </form>
        </main>
    </div>
</div>
<footer><?php echo $footer; ?></footer>
</body>
</html>