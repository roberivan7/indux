<?php
require_once 'icon.php';
require_once 'init.php';

$usuarioLogado = isset($_SESSION['logado']) && $_SESSION['logado'] === true;
if ($usuarioLogado && !podeAcessarSuporte()) {
    header('Location: dashboard.php?erro=acesso_negado');
    exit;
}

$nomeUsuario = $_SESSION['usuario_nome'] ?? '';
$emailUsuario = $_SESSION['usuario_email'] ?? '';
$classeBody = $usuarioLogado ? 'pagina-suporte' : 'pagina-suporte pagina-suporte-publica';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php echo $icon; ?>
  <title>Indux | Suporte</title>
  <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body class="<?php echo $classeBody; ?>">

  <?php if ($usuarioLogado): ?>
    <?php require_once 'header.php'; ?>
  <?php else: ?>
    <header class="suporte-topo-publico">
      <a href="../pagina_de_vendas/index.php" class="suporte-logo-publico">
        <img src="IMG/Monitoramento industrial (2).png" alt="INDUX">
      </a>
      <div class="suporte-topo-publico__links">
        <a href="../pagina_de_vendas/index.php">← Página inicial</a>
        <a href="login.php">Acessar sistema</a>
      </div>
    </header>
  <?php endif; ?>

  <main class="site-main suporte-main">
    <div class="page-header suporte-page-header">
      <div class="page-header-left">
        <div class="page-icon">💬</div>
        <div>
          <div class="breadcrumb">
            <span>INDUX</span> / <span>Suporte</span>
          </div>
          <h1 class="page-title">Suporte INDUX</h1>
          <p class="page-subtitle">Envie sua solicitação e aguarde o retorno da equipe pelo seu e-mail.</p>
        </div>
      </div>

      <div class="suporte-acoes-topo">
        <a href="../pagina_de_vendas/index.php" class="btn btn--ghost btn--sm">← Voltar para vendas</a>
        <?php if ($usuarioLogado): ?>
          <a href="dashboard.php" class="btn btn--ghost btn--sm">📊 Dashboard</a>
        <?php endif; ?>
      </div>
    </div>

    <section class="suporte-layout suporte-layout-simples">
      <div class="suporte-card suporte-card-chat">
        <div class="suporte-card__header">
          <div>
            <span class="suporte-etiqueta">Atendimento ao usuário</span>
            <h2>Enviar solicitação de suporte</h2>
          </div>
          <span class="suporte-status"><i></i> Online</span>
        </div>

        <div id="caixaRespostaSuporte" class="suporte-mensagens suporte-caixa-resposta">
          <div class="suporte-vazio">Preencha os dados abaixo para enviar sua solicitação.</div>
        </div>

        <form id="formularioSuporte" class="suporte-formulario">
          <div class="suporte-grid-formulario">
            <div class="form-group">
              <label class="form-label" for="nomeUsuarioSuporte">Nome</label>
              <input id="nomeUsuarioSuporte" name="nome_usuario" class="form-control" type="text" placeholder="Seu nome" value="<?php echo htmlspecialchars($nomeUsuario); ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="emailUsuarioSuporte">E-mail</label>
              <input id="emailUsuarioSuporte" name="email_usuario" class="form-control" type="email" placeholder="seu@email.com" value="<?php echo htmlspecialchars($emailUsuario); ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="tipoSuporte">Escolha o tipo de suporte</label>
            <select id="tipoSuporte" name="tipo_suporte" class="form-control" required>
              <option value="">Escolha o tipo de suporte</option>
              <option value="Erros diversos">Erros diversos</option>
              <option value="Bugs">Bugs</option>
              <option value="Suporte geral">Suporte geral</option>
              <option value="Outros">Outros</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="mensagemSuporte">Mensagem</label>
            <textarea id="mensagemSuporte" name="mensagem" class="form-control" rows="4" placeholder="Digite sua mensagem para o suporte..." required></textarea>
          </div>

          <div class="suporte-formulario__acoes">
            <span id="retornoSuporte" class="suporte-retorno"></span>
            <button type="submit" class="btn btn--primary">Enviar mensagem</button>
          </div>
        </form>
      </div>
    </section>
  </main>

  <script>
  const formularioSuporte = document.getElementById('formularioSuporte');
  const caixaRespostaSuporte = document.getElementById('caixaRespostaSuporte');
  const retornoSuporte = document.getElementById('retornoSuporte');

  function mostrarRetornoSuporte(texto, tipo) {
    retornoSuporte.textContent = texto || '';
    retornoSuporte.className = 'suporte-retorno' + (tipo ? ' ' + tipo : '');
  }

  function mostrarMensagemAutomatica() {
    caixaRespostaSuporte.innerHTML = `
      <div class="suporte-mensagem suporte-mensagem-automatica suporte-confirmacao">
        <div class="suporte-mensagem__topo">
          <strong>Suporte INDUX</strong>
          <small>Automático</small>
        </div>
        <p>Obrigado! mensagem enviada, aguarde resposta pelo Email.</p>
      </div>
    `;
  }

  function enviarSolicitacaoSuporte(dadosFormulario) {
    return fetch('suporte_api.php?acao=enviar_solicitacao', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(dadosFormulario)
    }).then(function(resposta) {
      return resposta.json().then(function(dados) {
        if (!resposta.ok || !dados.ok) {
          throw new Error(dados.erro || 'Não foi possível enviar a solicitação.');
        }

        return dados;
      });
    });
  }

  formularioSuporte.addEventListener('submit', function(evento) {
    evento.preventDefault();

    const dadosFormulario = Object.fromEntries(new FormData(formularioSuporte).entries());
    const botaoEnviar = formularioSuporte.querySelector('button[type="submit"]');

    botaoEnviar.disabled = true;
    botaoEnviar.textContent = 'Enviando...';
    mostrarRetornoSuporte('', '');

    enviarSolicitacaoSuporte(dadosFormulario)
      .then(function() {
        formularioSuporte.reset();
        mostrarMensagemAutomatica();
        mostrarRetornoSuporte('Solicitação enviada com sucesso.', 'sucesso');
      })
      .catch(function(erro) {
        mostrarRetornoSuporte(erro.message, 'erro');
      })
      .finally(function() {
        botaoEnviar.disabled = false;
        botaoEnviar.textContent = 'Enviar mensagem';
      });
  });
  </script>
</body>
</html>