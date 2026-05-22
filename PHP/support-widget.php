<?php
if (!function_exists('renderSupportWidget')) {
    function renderSupportWidget(): string {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $isSalesPage = strpos($scriptName, '/pagina_de_vendas/') !== false;
        $apiPath = $isSalesPage ? '../PHP/support-api.php' : 'support-api.php';
        $logoPath = $isSalesPage ? 'img/Monitoramento industrial (2).png' : '../IMG/logo.png';
        $source = $isSalesPage ? 'Pagina de vendas' : 'Sistema';

        ob_start();
        ?>
<div class="indux-support" data-api="<?php echo htmlspecialchars($apiPath); ?>" data-source="<?php echo htmlspecialchars($source); ?>">
  <button class="indux-support__button" type="button" aria-label="Abrir suporte INDUX">
    <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="INDUX">
  </button>

  <section class="indux-support__panel" aria-live="polite">
    <div class="indux-support__header">
      <div>
        <strong>Suporte INDUX</strong>
        <span>Atendimento tecnico</span>
      </div>
      <button type="button" class="indux-support__close" aria-label="Fechar suporte">x</button>
    </div>

    <div class="indux-support__messages"></div>

    <div class="indux-support__identity">
      <input type="text" class="indux-support__name" placeholder="Seu nome">
      <input type="email" class="indux-support__email" placeholder="E-mail para retorno">
    </div>
    <form class="indux-support__form">
      <textarea class="indux-support__text" rows="3" placeholder="Digite sua mensagem para o suporte"></textarea>
      <button type="submit">Enviar</button>
    </form>
  </section>

  <section class="indux-support__admin" aria-live="polite">
    <div class="indux-support__header">
      <div>
        <strong>Central Admin</strong>
        <span>F9 abre/fecha esta area</span>
      </div>
      <button type="button" class="indux-support__admin-close" aria-label="Fechar admin">x</button>
    </div>

    <form class="indux-support__admin-login">
      <input type="password" class="indux-support__admin-pass" placeholder="Senha do suporte">
      <button type="submit">Entrar</button>
    </form>

    <div class="indux-support__admin-area">
      <div class="indux-support__tickets"></div>
      <div class="indux-support__conversation"></div>
      <form class="indux-support__reply-form">
        <textarea class="indux-support__reply" rows="3" placeholder="Responder usuario selecionado"></textarea>
        <button type="submit">Responder</button>
      </form>
    </div>
  </section>
</div>

<style>
.indux-support{position:fixed;right:22px;bottom:22px;z-index:10000;font-family:Arial,system-ui,sans-serif;color:#e2e8f0}
.indux-support *{box-sizing:border-box}
.indux-support__button{width:64px;height:64px;border-radius:18px;border:1px solid rgba(0,200,255,.35);background:#07111f;box-shadow:0 16px 40px rgba(0,0,0,.45),0 0 24px rgba(0,200,255,.25);display:flex;align-items:center;justify-content:center;cursor:pointer;padding:8px}
.indux-support__button img{max-width:100%;max-height:100%;object-fit:contain}
.indux-support__panel,.indux-support__admin{position:absolute;right:0;bottom:78px;width:min(360px,calc(100vw - 32px));background:#0d1526;border:1px solid #243d66;border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.55);display:none;overflow:hidden}
.indux-support__admin{width:min(720px,calc(100vw - 32px));bottom:0;right:78px}
.indux-support.is-open .indux-support__panel,.indux-support.is-admin-open .indux-support__admin{display:block}
.indux-support__header{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;background:#050b18;border-bottom:1px solid #1e3354}
.indux-support__header strong{display:block;color:#fff;font-size:14px}
.indux-support__header span{display:block;color:#94a3b8;font-size:11px;margin-top:2px}
.indux-support__close,.indux-support__admin-close{background:transparent;border:0;color:#94a3b8;font-size:18px;cursor:pointer}
.indux-support__messages,.indux-support__conversation{height:220px;overflow:auto;padding:14px;display:flex;flex-direction:column;gap:8px}
.indux-support__conversation{height:260px;background:#091224;border-top:1px solid #1e3354;border-bottom:1px solid #1e3354}
.indux-support__bubble{max-width:86%;padding:8px 10px;border-radius:10px;font-size:13px;line-height:1.35;background:#111d33;border:1px solid #1e3354;color:#e2e8f0}
.indux-support__bubble small{display:block;color:#64748b;margin-top:4px;font-size:10px}
.indux-support__bubble--user{align-self:flex-end;background:rgba(0,200,255,.12);border-color:rgba(0,200,255,.25)}
.indux-support__bubble--admin{align-self:flex-start;background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.25)}
.indux-support__identity{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:12px 12px 0}
.indux-support input,.indux-support textarea{width:100%;background:#070d1a;border:1px solid #1e3354;border-radius:8px;color:#e2e8f0;padding:9px 10px;font:inherit;font-size:13px;outline:none}
.indux-support textarea{resize:vertical}
.indux-support input:focus,.indux-support textarea:focus{border-color:#00c8ff;box-shadow:0 0 0 3px rgba(0,200,255,.1)}
.indux-support__form,.indux-support__reply-form,.indux-support__admin-login{display:flex;gap:8px;padding:12px}
.indux-support__form{align-items:flex-end}
.indux-support button[type=submit]{border:0;border-radius:8px;background:#00c8ff;color:#06101d;font-weight:700;padding:10px 12px;cursor:pointer;white-space:nowrap}
.indux-support__admin-area{display:none}
.indux-support.admin-auth .indux-support__admin-login{display:none}
.indux-support.admin-auth .indux-support__admin-area{display:grid;grid-template-columns:230px 1fr}
.indux-support.admin-auth .indux-support__reply-form{grid-column:1 / -1}
.indux-support__tickets{max-height:260px;overflow:auto;border-right:1px solid #1e3354}
.indux-support__ticket{display:block;width:100%;text-align:left;background:transparent;border:0;border-bottom:1px solid #1e3354;color:#e2e8f0;padding:10px 12px;cursor:pointer}
.indux-support__ticket:hover,.indux-support__ticket.active{background:rgba(0,200,255,.1)}
.indux-support__ticket strong{display:block;font-size:12px;color:#fff}
.indux-support__ticket span{display:block;font-size:10px;color:#94a3b8;margin-top:2px}
@media(max-width:768px){.indux-support{right:14px;bottom:14px}.indux-support__admin{right:0;bottom:78px}.indux-support.admin-auth .indux-support__admin-area{display:block}.indux-support__tickets{border-right:0;border-bottom:1px solid #1e3354;max-height:160px}.indux-support__identity{grid-template-columns:1fr}}
</style>

<script>
(function(){
  const root = document.currentScript.previousElementSibling.previousElementSibling;
  if (!root || root.dataset.ready) return;
  root.dataset.ready = 'true';

  const api = root.dataset.api;
  const source = root.dataset.source || 'Sistema';
  const storageKey = 'indux_support_conversation';
  let conversationId = localStorage.getItem(storageKey) || '';
  let selectedTicket = '';

  const qs = (selector) => root.querySelector(selector);
  const post = (body) => fetch(api, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams(body)}).then(r=>r.json());
  const escapeHtml = (text) => String(text || '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch]));

  function renderMessages(target, conversation) {
    const messages = conversation && Array.isArray(conversation.messages) ? conversation.messages : [];
    target.innerHTML = messages.length ? messages.map(item => `
      <div class="indux-support__bubble indux-support__bubble--${item.from === 'admin' ? 'admin' : 'user'}">
        ${escapeHtml(item.message)}
        <small>${item.from === 'admin' ? 'Admin' : 'Usuario'} - ${escapeHtml(item.created_at || '')}</small>
      </div>
    `).join('') : '<div class="indux-support__bubble">Ola! Envie sua duvida e nosso suporte responde por aqui.</div>';
    target.scrollTop = target.scrollHeight;
  }

  function loadConversation() {
    if (!conversationId) {
      renderMessages(qs('.indux-support__messages'), null);
      return;
    }
    fetch(api + '?action=conversation&conversation_id=' + encodeURIComponent(conversationId))
      .then(r => r.json())
      .then(data => renderMessages(qs('.indux-support__messages'), data.conversation));
  }

  function loadAdminTickets() {
    post({action:'admin_list'}).then(data => {
      if (!data.ok) return;
      root.classList.add('admin-auth');
      const tickets = data.conversations || [];
      qs('.indux-support__tickets').innerHTML = tickets.length ? tickets.map(ticket => `
        <button type="button" class="indux-support__ticket ${ticket.id === selectedTicket ? 'active' : ''}" data-id="${escapeHtml(ticket.id)}">
          <strong>${escapeHtml(ticket.name || 'Visitante')}</strong>
          <span>${escapeHtml(ticket.source || '')} - ${escapeHtml(ticket.status || 'aberto')}</span>
          <span>${escapeHtml(ticket.updated_at || '')}</span>
        </button>
      `).join('') : '<div style="padding:14px;color:#94a3b8;font-size:13px">Nenhuma mensagem recebida.</div>';

      if (!selectedTicket && tickets[0]) selectedTicket = tickets[0].id;
      const current = tickets.find(ticket => ticket.id === selectedTicket);
      renderMessages(qs('.indux-support__conversation'), current);
    });
  }

  root.addEventListener('click', event => {
    if (event.target.closest('.indux-support__button')) {
      root.classList.toggle('is-open');
      loadConversation();
    }
    if (event.target.closest('.indux-support__close')) root.classList.remove('is-open');
    if (event.target.closest('.indux-support__admin-close')) root.classList.remove('is-admin-open');
    const ticket = event.target.closest('.indux-support__ticket');
    if (ticket) {
      selectedTicket = ticket.dataset.id;
      loadAdminTickets();
    }
  });

  qs('.indux-support__form').addEventListener('submit', event => {
    event.preventDefault();
    const text = qs('.indux-support__text');
    const message = text.value.trim();
    if (!message) return;
    post({
      action:'send',
      conversation_id: conversationId,
      source,
      name: qs('.indux-support__name').value,
      email: qs('.indux-support__email').value,
      message
    }).then(data => {
      if (!data.ok) return alert(data.error || 'Erro ao enviar mensagem.');
      conversationId = data.conversation_id;
      localStorage.setItem(storageKey, conversationId);
      text.value = '';
      renderMessages(qs('.indux-support__messages'), data.conversation);
    });
  });

  qs('.indux-support__admin-login').addEventListener('submit', event => {
    event.preventDefault();
    post({action:'admin_login', password: qs('.indux-support__admin-pass').value}).then(data => {
      if (!data.ok) return alert(data.error || 'Acesso negado.');
      loadAdminTickets();
    });
  });

  qs('.indux-support__reply-form').addEventListener('submit', event => {
    event.preventDefault();
    const reply = qs('.indux-support__reply');
    const message = reply.value.trim();
    if (!selectedTicket || !message) return;
    post({action:'admin_reply', conversation_id:selectedTicket, message}).then(data => {
      if (!data.ok) return alert(data.error || 'Erro ao responder.');
      reply.value = '';
      loadAdminTickets();
      loadConversation();
    });
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'F9') {
      event.preventDefault();
      root.classList.toggle('is-admin-open');
      if (root.classList.contains('is-admin-open')) loadAdminTickets();
    }
  });

  loadConversation();
  setInterval(() => {
    if (root.classList.contains('is-open')) loadConversation();
    if (root.classList.contains('is-admin-open') && root.classList.contains('admin-auth')) loadAdminTickets();
  }, 8000);
})();
</script>
        <?php
        return ob_get_clean();
    }
}
