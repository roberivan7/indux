<?php
require_once 'init.php';
require_once 'db.php';
requerLogin();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Planos - <?php echo SISTEMA_NOME; ?></title>
  <link rel="stylesheet" href="../CSS/styles.css">
  <link rel="shortcut icon" type="png" href="../IMG/logo.png">
</head>
<body>

  <?php require_once 'header.php'; ?>

  <main class="site-main">
    <div id="plansPage" class="plans-page plans-shell">
      <div class="page-header">
        <div class="page-header-left">
          <div class="page-icon">UP</div>
          <div>
            <div class="breadcrumb">
              <span>INDUX</span> / <span>Planos</span>
            </div>
            <h1 class="page-title">Upgrade de plano</h1>
            <p class="page-subtitle">Escolha o plano ideal para sua operacao industrial.</p>
          </div>
        </div>
      </div>

      <div class="plans-grid">
        <section class="plan-card">
          <div class="plan-name">Starter</div>
          <div class="plan-price">
            <span class="price-currency">R$</span>
            <span class="price-value">97</span>
            <span class="price-period">/mes</span>
          </div>
          <p class="plan-desc">Ideal para pequenas plantas com poucos equipamentos.</p>
          <hr class="plan-divider">
          <ul class="feature-list">
            <li class="feature-item"><span class="feature-icon yes">✓</span>Ate 10 equipamentos</li>
            <li class="feature-item"><span class="feature-icon yes">✓</span>50 leituras/dia por equipamento</li>
            <li class="feature-item"><span class="feature-icon yes">✓</span>Alarmes basicos</li>
            <li class="feature-item"><span class="feature-icon yes">✓</span>Dashboard padrao</li>
            <li class="feature-item dim"><span class="feature-icon no">x</span>Relatorios avancados</li>
            <li class="feature-item dim"><span class="feature-icon no">x</span>API de integracao</li>
          </ul>
          <button class="btn btn--ghost plan-action" type="button" onclick="goToPayment('Starter', '97')">Comecar agora</button>
        </section>

        <section class="plan-card plan-card--featured">
          <div class="plan-name">Pro</div>
          <div class="plan-price">
            <span class="price-currency">R$</span>
            <span class="price-value">247</span>
            <span class="price-period">/mes</span>
          </div>
          <p class="plan-desc">Para operacoes medias que precisam de monitoramento completo.</p>
          <hr class="plan-divider">
          <ul class="feature-list">
            <li class="feature-item"><span class="feature-icon yes">✓</span>Ate 50 equipamentos</li>
            <li class="feature-item"><span class="feature-icon yes">✓</span>Leituras ilimitadas</li>
            <li class="feature-item"><span class="feature-icon yes">✓</span>Alarmes + notificacoes por e-mail</li>
            <li class="feature-item"><span class="feature-icon yes">✓</span>Relatorios avancados</li>
            <li class="feature-item"><span class="feature-icon yes">✓</span>API de integracao</li>
            <li class="feature-item"><span class="feature-icon yes">✓</span>3 usuarios admin</li>
          </ul>
          <button class="btn btn--primary plan-action" type="button" onclick="goToPayment('Pro', '247')">Assinar Pro</button>
        </section>

        <section class="plan-card">
          <div class="plan-name">Enterprise</div>
          <div class="plan-price">
            <span class="price-currency">R$</span>
            <span class="price-value">597</span>
            <span class="price-period">/mes</span>
          </div>
          <p class="plan-desc">Grandes plantas industriais com SLA garantido e suporte dedicado.</p>
          <hr class="plan-divider">
          <ul class="feature-list">
            <li class="feature-item"><span class="feature-icon yes">✓</span>Equipamentos ilimitados</li>
            <li class="feature-item"><span class="feature-icon yes">✓</span>Leituras ilimitadas</li>
            <li class="feature-item"><span class="feature-icon yes">✓</span>Alarmes + SMS + WhatsApp</li>
            <li class="feature-item"><span class="feature-icon yes">✓</span>Relatorios + exportacao</li>
            <li class="feature-item"><span class="feature-icon yes">✓</span>API avancada + webhooks</li>
            <li class="feature-item"><span class="feature-icon yes">✓</span>Usuarios ilimitados</li>
          </ul>
          <button class="btn btn--warning plan-action" type="button" onclick="goToPayment('Enterprise', '597')">Falar com vendas</button>
        </section>
      </div>

      <div class="trust-bar">
        <span>7 dias gratis, sem cartao</span>
        <span>Pagamento seguro</span>
        <span>Cancele quando quiser</span>
        <span>Suporte em portugues</span>
      </div>
    </div>

    <div id="paymentPage" class="payment-page plans-shell">
      <div class="page-header">
        <div class="page-header-left">
          <div class="page-icon">$</div>
          <div>
            <div class="breadcrumb">
              <span>INDUX</span> / <span>Planos</span> / <span>Pagamento</span>
            </div>
            <h1 class="page-title">Finalizar assinatura</h1>
            <p class="page-subtitle">Revise os dados do plano e preencha as informacoes de pagamento.</p>
          </div>
        </div>
        <button class="btn btn--ghost btn--sm" type="button" onclick="goBack()">Voltar aos planos</button>
      </div>

      <div class="pay-wrap">
        <aside class="order-card">
          <div class="order-title">Resumo do pedido</div>
          <div class="order-plan">
            <div>
              <div id="summary-plan" class="order-plan-name">Plano Pro</div>
              <div class="order-plan-sub">Assinatura mensal - INDUX</div>
            </div>
            <div class="order-plan-price">
              <div id="summary-price" class="big">R$ 247</div>
              <div class="order-plan-sub">/mes</div>
            </div>
          </div>
          <div id="summary-features" class="order-features"></div>
          <div class="order-total">
            <div class="total-row"><span>Subtotal</span><span id="sub-total">R$ 247,00</span></div>
            <div class="total-row main"><span>Total mensal</span><span id="final-total">R$ 247,00</span></div>
          </div>
        </aside>

        <section class="form-card">
          <div class="form-section">
            <div class="form-section-title">Dados pessoais</div>
            <div class="form-row">
              <div class="form-group">
                <label for="nome">Nome completo</label>
                <input id="nome" type="text" placeholder="Joao Silva">
              </div>
              <div class="form-group">
                <label for="empresa">Empresa</label>
                <input id="empresa" type="text" placeholder="Industria Ltda">
              </div>
            </div>
            <div class="form-group">
              <label for="email">E-mail</label>
              <input id="email" type="email" placeholder="joao@empresa.com.br">
            </div>
            <div class="form-group">
              <label for="documento">CPF / CNPJ</label>
              <input id="documento" type="text" placeholder="00.000.000/0001-00">
            </div>
          </div>

          <div class="form-section">
            <div class="form-section-title">Cartao de credito</div>
            <div class="form-group">
              <label for="card-num">Numero do cartao</label>
              <input id="card-num" type="text" placeholder="0000 0000 0000 0000" maxlength="19" oninput="formatCard(this)">
            </div>
            <div class="form-group">
              <label for="card-name">Nome no cartao</label>
              <input id="card-name" type="text" placeholder="JOAO A SILVA">
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="expiry">Validade</label>
                <input id="expiry" type="text" placeholder="MM/AA" maxlength="5" oninput="formatExpiry(this)">
              </div>
              <div class="form-group">
                <label for="cvv">CVV</label>
                <input id="cvv" type="text" placeholder="000" maxlength="4">
              </div>
            </div>
          </div>

          <button id="paySubmit" class="btn btn--primary btn--lg plan-action" type="button" onclick="processPayment()">
            Assinar agora - <span id="btn-price">R$ 247/mes</span>
          </button>
          <div class="secure-note">Ambiente seguro - SSL 256-bit</div>
          <div class="or-divider">ou pague com</div>
          <button class="btn btn--ghost pix-btn" type="button" onclick="processPayment()">PIX - acesso imediato</button>
        </section>
      </div>
    </div>
  </main>

  <div id="successOverlay" class="success-overlay">
    <div class="success-circle">✓</div>
    <div class="success-title">Assinatura confirmada!</div>
    <p class="success-sub">Seu acesso ao INDUX foi ativado. Verifique seu e-mail para as instrucoes de acesso.</p>
    <button class="btn btn--primary" type="button" onclick="document.getElementById('successOverlay').classList.remove('show')">Acessar sistema</button>
  </div>

  <script>
    let currentPlan = {name: 'Pro', price: 247};

    const planFeatures = {
      Starter: ['10 equipamentos', '50 leituras/dia', 'Alarmes basicos', 'Dashboard padrao'],
      Pro: ['50 equipamentos', 'Leituras ilimitadas', 'Alarmes + notificacoes', 'Relatorios avancados', 'API de integracao'],
      Enterprise: ['Equipamentos ilimitados', 'Leituras ilimitadas', 'SMS + WhatsApp', 'Relatorios + exportacao', 'Usuarios ilimitados', 'SLA 99,9%']
    };

    function goToPayment(planName, priceValue) {
      const price = parseInt(priceValue, 10);
      currentPlan = {name: planName, price};

      document.getElementById('summary-plan').textContent = 'Plano ' + planName;
      document.getElementById('summary-price').textContent = 'R$ ' + price;
      document.getElementById('sub-total').textContent = 'R$ ' + price + ',00';
      document.getElementById('final-total').textContent = 'R$ ' + price + ',00';
      document.getElementById('btn-price').textContent = 'R$ ' + price + '/mes';

      const feats = planFeatures[planName] || [];
      document.getElementById('summary-features').innerHTML = feats
        .map((feature) => '<div><span class="feature-icon yes">✓</span> ' + feature + '</div>')
        .join('');

      document.getElementById('plansPage').style.display = 'none';
      document.getElementById('paymentPage').style.display = 'grid';
      window.scrollTo(0, 0);
    }

    function goBack() {
      document.getElementById('paymentPage').style.display = 'none';
      document.getElementById('plansPage').style.display = 'grid';
      window.scrollTo(0, 0);
    }

    function formatCard(el) {
      const value = el.value.replace(/\D/g, '').substring(0, 16);
      el.value = value.replace(/(.{4})/g, '$1 ').trim();
    }

    function formatExpiry(el) {
      let value = el.value.replace(/\D/g, '').substring(0, 4);
      if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2);
      }
      el.value = value;
    }

    function processPayment() {
      const btn = document.getElementById('paySubmit');
      const original = btn.innerHTML;
      btn.textContent = 'Processando...';
      btn.disabled = true;

      setTimeout(() => {
        document.getElementById('successOverlay').classList.add('show');
        btn.innerHTML = original;
        btn.disabled = false;
      }, 900);
    }
  </script>
</body>
</html>
