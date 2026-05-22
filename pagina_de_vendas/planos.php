<?php require_once 'header.php'; ?>
<?php require_once 'footer.php'; ?>
<style>
</style>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles.css">
  <title>Planos --- Indux</title>
</head>
<body>
  <header><?php echo $header;?></header>
<div class="plans-page" id="plansPage">
  <div class="page-header">
    <h1 class="main-title">Escolha o plano<br>ideal para sua <span>operação</span></h1>
    <p class="subtitle">Monitore equipamentos, leituras de sensores e alarmes com total controle. Sem surpresas na fatura.</p>
  </div>

  <div class="plans-grid">
    <!-- STARTER -->
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
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>Até 10 equipamentos</li>
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>50 leituras/dia por equip.</li>
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>Alarmes básicos</li>
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>Dashboard padrão</li>
        <li class="feature-item dim"><span class="fi no"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><line x1="3" y1="3" x2="9" y2="9"/><line x1="9" y1="3" x2="3" y2="9"/></svg></span>Relatórios avançados</li>
        <li class="feature-item dim"><span class="fi no"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><line x1="3" y1="3" x2="9" y2="9"/><line x1="9" y1="3" x2="3" y2="9"/></svg></span>API de integração</li>
        <li class="feature-item dim"><span class="fi no"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><line x1="3" y1="3" x2="9" y2="9"/><line x1="9" y1="3" x2="3" y2="9"/></svg></span>Suporte prioritário</li>
      </ul>
      <button class="plan-btn outline" onclick="goToPayment('Starter','97','77')">Começar agora</button>
    </div>

    <!-- PRO (featured) -->
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
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>Até 50 equipamentos</li>
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>Leituras ilimitadas</li>
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>Alarmes + notif. e-mail</li>
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>Relatórios avançados</li>
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>API de integração</li>
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>3 usuários admin</li>
        <li class="feature-item dim"><span class="fi no"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><line x1="3" y1="3" x2="9" y2="9"/><line x1="9" y1="3" x2="3" y2="9"/></svg></span>SLA 99,9%</li>
      </ul>
      <button class="plan-btn primary" onclick="goToPayment('Pro','247','197')">Assinar Pro</button>
    </div>

    <!-- ENTERPRISE -->
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
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>Equipamentos ilimitados</li>
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>Leituras ilimitadas</li>
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>Alarmes + SMS + WhatsApp</li>
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>Relatórios + exportação</li>
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>API avançada + webhooks</li>
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>Usuários ilimitados</li>
        <li class="feature-item"><span class="fi yes"><svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>SLA 99,9% garantido</li>
      </ul>
      <button class="plan-btn ghost" onclick="goToPayment('Enterprise','597','477')">Falar com vendas</button>
    </div>
  </div>

  <div class="trust-bar">
    <div class="trust-item"><svg viewBox="0 0 16 16" fill="none" stroke-width="1.8"><path d="M8 1L10 6h5L11 9l2 5-5-3-5 3 2-5L1 6h5z"/></svg>7 dias grátis, sem cartão</div>
    <div class="trust-item"><svg viewBox="0 0 16 16" fill="none" stroke-width="1.8"><rect x="2" y="6" width="12" height="9" rx="2"/><path d="M5 6V4a3 3 0 0 1 6 0v2"/></svg>Pagamento 100% seguro</div>
    <div class="trust-item"><svg viewBox="0 0 16 16" fill="none" stroke-width="1.8"><path d="M8 1l1.5 3h3.5l-2.5 2 1 3L8 7.5 4.5 9l1-3L3 4h3.5z"/></svg>Cancele quando quiser</div>
    <div class="trust-item"><svg viewBox="0 0 16 16" fill="none" stroke-width="1.8"><circle cx="8" cy="8" r="7"/><path d="M8 4v4l3 1.5"/></svg>Suporte em português</div>
  </div>
</div>

<!-- PAYMENT PAGE -->
<div class="payment-page" id="paymentPage">
  <div class="pay-header">
    <button class="back-btn" onclick="goBack()">
      <svg viewBox="0 0 16 16" fill="none" stroke-width="2"><polyline points="10,3 5,8 10,13"/></svg>
      Voltar aos planos
    </button>
    <div class="logo-bar" style="margin-bottom:1.5rem">
      <div class="logo-icon"><svg viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg"><path d="M3 3h7v7H3zm11 0h7v7h-7zM3 14h7v7H3zm11 3h2v-2h2v2h2v2h-2v2h-2v-2h-2z"/></svg></div>
      <span class="logo-text">INDUX</span>
    </div>
    <div class="badge-top" style="margin-bottom:1rem">Finalizar assinatura</div>
  </div>

  <div class="pay-wrap">
    <!-- Order summary -->
    <div class="order-card">
      <div class="order-title">Resumo do pedido</div>
      <div class="order-plan">
        <div>
          <div class="order-plan-name" id="summary-plan">Pro</div>
          <div class="order-plan-sub">Assinatura mensal · INDUX</div>
        </div>
        <div class="order-plan-price">
          <div class="big" id="summary-price">R$ 247</div>
          <div class="sm">/mês</div>
        </div>
      </div>
      <div class="order-features" id="summary-features"></div>
      <div class="order-total">
        <div class="total-row"><span>Subtotal</span><span id="sub-total">R$ 247,00</span></div>
        <div class="total-row"><span>Desconto</span><span id="discount-val" style="color:var(--indux-green)">R$ 0,00</span></div>
        <div class="total-row main"><span>Total mensal</span><span id="final-total">R$ 247,00</span></div>
      </div>
    </div>

    <!-- Payment form -->
    <div class="form-card">
      <div class="form-section">
        <div class="form-section-title">
          <svg viewBox="0 0 16 16" fill="none" stroke-width="1.8"><path d="M8 1l1 2.5h3l-2.5 1.8 1 2.7L8 6.5 5.5 8l1-2.7L4 3.5h3z"/></svg>
          Dados pessoais
        </div>
        <div class="form-row">
          <div class="form-group"><label>Nome completo</label><input type="text" placeholder="João Silva"></div>
          <div class="form-group"><label>Empresa</label><input type="text" placeholder="Indústria Ltda"></div>
        </div>
        <div class="form-group"><label>E-mail</label><input type="email" placeholder="joao@empresa.com.br"></div>
        <div class="form-group"><label>CPF / CNPJ</label><input type="text" placeholder="00.000.000/0001-00"></div>
      </div>

      <div class="form-section">
        <div class="form-section-title">
          <svg viewBox="0 0 16 16" fill="none" stroke-width="1.8"><rect x="1" y="4" width="14" height="10" rx="2"/><path d="M1 7h14"/></svg>
          Cartão de crédito
        </div>
        <div class="form-group">
          <label>Número do cartão</label>
          <div class="card-number-wrap">
            <input type="text" placeholder="0000 0000 0000 0000" maxlength="19" id="card-num" oninput="formatCard(this)">
            <div class="card-brand"><span>VISA</span><span>MC</span></div>
          </div>
        </div>
        <div class="form-group"><label>Nome no cartão</label><input type="text" placeholder="JOAO A SILVA"></div>
        <div class="form-row">
          <div class="form-group"><label>Validade</label><input type="text" placeholder="MM/AA" maxlength="5" oninput="formatExpiry(this)"></div>
          <div class="form-group"><label>CVV</label><input type="text" placeholder="•••" maxlength="4"></div>
        </div>
      </div>

      <button class="pay-submit" onclick="processPayment()">
        <svg viewBox="0 0 18 18" fill="none"><rect x="2" y="5" width="14" height="10" rx="2"/><path d="M2 8h14"/><circle cx="5.5" cy="12" r="1" fill="currentColor" stroke="none"/></svg>
        Assinar agora — <span id="btn-price">R$ 247/mês</span>
      </button>

      <div class="secure-note">
        <svg viewBox="0 0 16 16" fill="none" stroke-width="1.8"><path d="M8 1L3 4v4c0 3 2.5 5.5 5 7 2.5-1.5 5-4 5-7V4z"/></svg>
        Ambiente 100% seguro · SSL 256-bit
      </div>

      <div class="or-divider">ou pague com</div>
      <button class="pix-btn" onclick="processPayment()">
        <span class="pix-logo" style="color:var(--indux-green)">PIX</span>
        Pagar com PIX — receba acesso imediato
      </button>
    </div>
  </div>
</div>

<!-- SUCCESS -->
<div class="success-overlay" id="successOverlay">
  <div class="success-circle"><svg viewBox="0 0 36 36" fill="none" stroke-width="2.5"><polyline points="8,18 15,25 28,11"/></svg></div>
  <div class="success-title">Assinatura confirmada! 🎉</div>
  <p class="success-sub">Seu acesso ao INDUX foi ativado. Verifique seu e-mail para as instruções de acesso.</p>
  <button class="plan-btn primary" style="max-width:200px" onclick="document.getElementById('successOverlay').classList.remove('show')">Acessar sistema</button>
</div>
<footer><?php echo $footer; ?></footer>
</body>
<script>
let isYearly = false;
let currentPlan = {name:'Pro', monthly:247, yearly:197};

const planFeatures = {
  'Starter': ['10 equipamentos','50 leituras/dia','Alarmes básicos','Dashboard padrão'],
  'Pro': ['50 equipamentos','Leituras ilimitadas','Alarmes + notificações','Relatórios avançados','API de integração'],
  'Enterprise': ['Equipamentos ilimitados','Leituras ilimitadas','SMS + WhatsApp','Relatórios + exportação','Usuários ilimitados','SLA 99,9%']
};

function toggleBilling(btn){
  isYearly = !isYearly;
  btn.classList.toggle('on', isYearly);
  document.getElementById('lbl-mensal').classList.toggle('active', !isYearly);
  document.getElementById('lbl-anual').classList.toggle('active', isYearly);
  document.querySelectorAll('.price-value').forEach(el=>{
    el.textContent = isYearly ? el.dataset.yearly : el.dataset.monthly;
  });
}

function goToPayment(planName, monthly, yearly){
  currentPlan = {name: planName, monthly: parseInt(monthly), yearly: parseInt(yearly)};
  const price = isYearly ? currentPlan.yearly : currentPlan.monthly;
  const discount = isYearly ? (currentPlan.monthly - currentPlan.yearly) : 0;

  document.getElementById('summary-plan').textContent = 'Plano ' + planName;
  document.getElementById('summary-price').textContent = 'R$ ' + price;
  document.getElementById('sub-total').textContent = 'R$ ' + currentPlan.monthly + ',00';
  document.getElementById('discount-val').textContent = isYearly ? '- R$ ' + discount + ',00' : 'R$ 0,00';
  document.getElementById('final-total').textContent = 'R$ ' + price + ',00';
  document.getElementById('btn-price').textContent = 'R$ ' + price + '/mês';

  const feats = planFeatures[planName] || [];
  document.getElementById('summary-features').innerHTML = feats.map(f=>`
    <div class="of-item">
      <div class="of-icon"><svg viewBox="0 0 10 10" fill="none" stroke-width="2.5"><polyline points="2,5 4,7 8,3"/></svg></div>
      ${f}
    </div>`).join('');

  document.getElementById('plansPage').style.display='none';
  document.getElementById('paymentPage').style.display='block';
  window.scrollTo(0,0);
}

function goBack(){
  document.getElementById('paymentPage').style.display='none';
  document.getElementById('plansPage').style.display='block';
  window.scrollTo(0,0);
}

function formatCard(el){
  let v = el.value.replace(/\D/g,'').substring(0,16);
  el.value = v.replace(/(.{4})/g,'$1 ').trim();
}

function formatExpiry(el){
  let v = el.value.replace(/\D/g,'').substring(0,4);
  if(v.length>=2) v = v.substring(0,2)+'/'+v.substring(2);
  el.value = v;
}

function processPayment(){
  const btn = document.querySelector('.pay-submit');
  btn.textContent = 'Processando...';
  btn.style.opacity = '0.7';
  setTimeout(()=>{
    document.getElementById('successOverlay').classList.add('show');
    btn.innerHTML = '<svg viewBox="0 0 18 18" fill="none"><rect x="2" y="5" width="14" height="10" rx="2"/><path d="M2 8h14"/><circle cx="5.5" cy="12" r="1" fill="currentColor" stroke="none"/></svg> Assinar agora — <span id="btn-price">R$ '+(isYearly?currentPlan.yearly:currentPlan.monthly)+'/mês</span>';
    btn.style.opacity = '1';
  }, 1800);
}
</script>