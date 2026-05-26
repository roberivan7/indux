<?php
require_once 'icon.php';
require_once 'init.php';
require_once 'db.php';
requerLogin();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php echo $icon; ?>
  <title>Indux | Planos</title>
  <link rel="stylesheet" href="../CSS/styles.css">
  <link rel="shortcut icon" type="png" href="../IMG/logo.png">
</head>
<body>

  <?php require_once 'header.php'; ?>

  <main class="site-main site-main--upgrade">
    <div class="upgrade-plans plans-page" id="plansPage">
      <div class="upgrade-page-header">
        <h1 class="main-title">Escolha o plano<br>ideal para sua <span>operação</span></h1>
        <p class="subtitle">Monitore equipamentos, leituras de sensores e alarmes com total controle. Sem surpresas na fatura.</p>
      </div>

      <div class="plans-grid">
        <div class="plan-card">
          <div class="plan-name">Starter</div>
          <div class="plan-price">
            <span class="price-currency">R$</span>
            <span class="price-value" data-monthly="97" data-yearly="77">97</span>
            <span class="price-period">/mês</span>
          </div>
          <p class="plan-desc">Ideal para pequenas plantas com poucos equipamentos.</p>
          <hr class="divider">
          <ul class="feature-list">
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>Até 10 equipamentos</li>
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>50 leituras/dia por equip.</li>
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>Alarmes básicos</li>
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>Dashboard padrão</li>
            <li class="feature-item dim"><span class="fi no"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><line x1="3" y1="3" x2="9" y2="9" /><line x1="9" y1="3" x2="3" y2="9" /></svg></span>API de integração</li>
            <li class="feature-item dim"><span class="fi no"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><line x1="3" y1="3" x2="9" y2="9" /><line x1="9" y1="3" x2="3" y2="9" /></svg></span>Suporte prioritário</li>
          </ul>
          <button class="plan-btn ghost" type="button" onclick="goToPayment('Starter','97','77')">Assinar agora</button>
        </div>

        <div class="plan-card featured">
          <div class="plan-name featured">Pro</div>
          <div class="plan-price">
            <span class="price-currency">R$</span>
            <span class="price-value" data-monthly="247" data-yearly="197">247</span>
            <span class="price-period">/mês</span>
          </div>
          <p class="plan-desc">Para operações médias que precisam de monitoramento completo.</p>
          <hr class="divider">
          <ul class="feature-list">
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>Até 50 equipamentos</li>
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>Leituras ilimitadas</li>
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>Alarmes + notif. e-mail</li>
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>API de integração</li>
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>3 usuários admin</li>
            <li class="feature-item dim"><span class="fi no"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><line x1="3" y1="3" x2="9" y2="9" /><line x1="9" y1="3" x2="3" y2="9" /></svg></span>SLA 99,9%</li>
          </ul>
          <button class="plan-btn primary" type="button" onclick="goToPayment('Pro','247','197')">Assinar Pro</button>
        </div>

        <div class="plan-card">
          <div class="plan-name">Enterprise</div>
          <div class="plan-price">
            <span class="price-currency">R$</span>
            <span class="price-value" data-monthly="597" data-yearly="477">597</span>
            <span class="price-period">/mês</span>
          </div>
          <p class="plan-desc">Grandes plantas industriais com SLA garantido e suporte dedicado.</p>
          <hr class="divider">
          <ul class="feature-list">
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>Equipamentos ilimitados</li>
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>Leituras ilimitadas</li>
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>Alarmes + SMS + WhatsApp</li>
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>API avançada + webhooks</li>
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>Usuários ilimitados</li>
            <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>SLA 99,9% garantido</li>
          </ul>
          <button class="plan-btn ghost" type="button" onclick="goToPayment('Enterprise','597','477')">Assinar agora</button>
        </div>
      </div>

      <div class="trust-bar">
        <div class="trust-item"><svg viewBox="0 0 16 16" fill="none" stroke-width="1.8"><path d="M8 1L10 6h5L11 9l2 5-5-3-5 3 2-5L1 6h5z" /></svg>7 dias grátis, sem cartão</div>
        <div class="trust-item"><svg viewBox="0 0 16 16" fill="none" stroke-width="1.8"><rect x="2" y="6" width="12" height="9" rx="2" /><path d="M5 6V4a3 3 0 0 1 6 0v2" /></svg>Pagamento 100% seguro</div>
        <div class="trust-item"><svg viewBox="0 0 16 16" fill="none" stroke-width="1.8"><path d="M8 1l1.5 3h3.5l-2.5 2 1 3L8 7.5 4.5 9l1-3L3 4h3.5z" /></svg>Cancele quando quiser</div>
        <div class="trust-item"><svg viewBox="0 0 16 16" fill="none" stroke-width="1.8"><circle cx="8" cy="8" r="7" /><path d="M8 4v4l3 1.5" /></svg>Suporte em português</div>
      </div>
    </div>

    <div class="upgrade-payment payment-page" id="paymentPage">
      <div class="pay-wrap">
        <div class="order-card">
          <div class="order-title">Resumo do pedido</div>
          <div class="order-plan">
            <div>
              <div class="order-plan-name" id="summary-plan">Plano Pro</div>
              <div class="order-plan-sub">Assinatura mensal - INDUX</div>
            </div>
            <div class="order-plan-price">
              <div class="big" id="summary-price">R$ 247</div>
              <div class="sm">/mês</div>
            </div>
          </div>
          <div class="order-features" id="summary-features"></div>
          <div class="order-total">
            <div class="total-row"><span>Subtotal</span><span id="sub-total">R$ 247,00</span></div>
            <div class="total-row"><span>Desconto</span><span id="discount-val">R$ 0,00</span></div>
            <div class="total-row main"><span>Total mensal</span><span id="final-total">R$ 247,00</span></div>
          </div>
        </div>

        <div class="form-card">
          <div class="form-section">
            <div class="form-section-title">Dados pessoais</div>
            <div class="form-row">
              <div class="form-group"><label for="nome">Nome completo</label><input id="nome" type="text" placeholder="João Silva"></div>
              <div class="form-group"><label for="empresa">Empresa</label><input id="empresa" type="text" placeholder="Indústria Ltda"></div>
            </div>
            <div class="form-group"><label for="email">E-mail</label><input id="email" type="email" placeholder="joao@empresa.com.br"></div>
            <div class="form-group"><label for="documento">CPF / CNPJ</label><input id="documento" type="text" placeholder="00.000.000/0001-00"></div>
          </div>

          <div class="form-section">
            <div class="form-section-title">Cartão de crédito</div>
            <div class="form-group"><label for="card-num">Número do cartão</label><input id="card-num" type="text" placeholder="0000 0000 0000 0000" maxlength="19" oninput="formatCard(this)"></div>
            <div class="form-group"><label for="card-name">Nome no cartão</label><input id="card-name" type="text" placeholder="JOAO A SILVA"></div>
            <div class="form-row">
              <div class="form-group"><label for="expiry">Validade</label><input id="expiry" type="text" placeholder="MM/AA" maxlength="5" oninput="formatExpiry(this)"></div>
              <div class="form-group"><label for="cvv">CVV</label><input id="cvv" type="text" placeholder="000" maxlength="4"></div>
            </div>
          </div>

          <button id="paySubmit" class="pay-submit" type="button" onclick="processPayment()">Assinar agora - <span id="btn-price">R$ 247/mês</span></button>
          <div class="or-divider">ou pague com</div>
          <button class="pix-btn" type="button" onclick="processPayment()">Pagar com PIX - receba acesso imediato</button>
          <button class="plan-btn ghost back-plan-btn" type="button" onclick="goBack()">Voltar aos planos</button>
        </div>
      </div>
    </div>
  </main>

  <div id="successOverlay" class="success-overlay">
    <div class="success-circle">✓</div>
    <div class="success-title">Assinatura confirmada!</div>
    <p class="success-sub">Seu acesso ao INDUX foi ativado. Verifique seu e-mail para as instruções de acesso.</p>
    <button class="plan-btn primary" type="button" onclick="document.getElementById('successOverlay').classList.remove('show')">Acessar sistema</button>
  </div>

  <script>
    let isYearly = false;
    let currentPlan = {name: 'Pro', monthly: 247, yearly: 197};

    const planFeatures = {
      Starter: ['10 equipamentos', '50 leituras/dia', 'Alarmes básicos', 'Dashboard padrão'],
      Pro: ['50 equipamentos', 'Leituras ilimitadas', 'Alarmes + notificações', 'API de integração'],
      Enterprise: ['Equipamentos ilimitados', 'Leituras ilimitadas', 'SMS + WhatsApp', 'Usuários ilimitados', 'SLA 99,9%']
    };

    function goToPayment(planName, monthly, yearly) {
      currentPlan = {
        name: planName,
        monthly: parseInt(monthly, 10),
        yearly: parseInt(yearly, 10)
      };

      const price = isYearly ? currentPlan.yearly : currentPlan.monthly;
      const discount = isYearly ? (currentPlan.monthly - currentPlan.yearly) : 0;

      document.getElementById('summary-plan').textContent = 'Plano ' + planName;
      document.getElementById('summary-price').textContent = 'R$ ' + price;
      document.getElementById('sub-total').textContent = 'R$ ' + currentPlan.monthly + ',00';
      document.getElementById('discount-val').textContent = isYearly ? '- R$ ' + discount + ',00' : 'R$ 0,00';
      document.getElementById('final-total').textContent = 'R$ ' + price + ',00';
      document.getElementById('btn-price').textContent = 'R$ ' + price + '/mês';

      const feats = planFeatures[planName] || [];
      document.getElementById('summary-features').innerHTML = feats.map((feature) => `
        <div class="of-item">
          <span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3" /></svg></span>
          ${feature}
        </div>
      `).join('');

      document.getElementById('plansPage').style.display = 'none';
      document.getElementById('paymentPage').style.display = 'block';
      window.scrollTo(0, 0);
    }

    function goBack() {
      document.getElementById('paymentPage').style.display = 'none';
      document.getElementById('plansPage').style.display = 'block';
      window.scrollTo(0, 0);
    }

    function formatCard(el) {
      const value = el.value.replace(/\D/g, '').substring(0, 16);
      el.value = value.replace(/(.{4})/g, '$1 ').trim();
    }

    function formatExpiry(el) {
      let value = el.value.replace(/\D/g, '').substring(0, 4);
      if (value.length >= 2) value = value.substring(0, 2) + '/' + value.substring(2);
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
