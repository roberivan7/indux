<?php
require_once 'icon.php';
require_once 'init.php';
require_once 'db.php';
requerLogin();
if (!podeVerUsuarios()) { header('Location: dashboard.php?erro=acesso_negado'); exit; }

$msg     = '';
$msgTipo = 'info';
$erros   = [];
$modo    = $_GET['acao'] ?? 'listar'; // listar | novo | editar
$editId  = (int)($_GET['id'] ?? 0);
$usuario = null;

if (isset($_GET['excluir']) && ehAdmin()) {
    $usuarioExcluirId = (int)$_GET['excluir'];
    if ($usuarioExcluirId === ($_SESSION['usuario_id'] ?? 0)) {
        $msg = '{{lucide:triangle-alert}} Você não pode excluir seu próprio usuário.'; $msgTipo = 'aviso';
    } else {
        $usuarioExcluir = dbBuscarUsuario($usuarioExcluirId);
        if ($usuarioExcluir && dbExcluirUsuario($usuarioExcluirId)) {
            registrarLog('EXCLUIR_USUARIO','usuarios',$usuarioExcluirId,'Email:'.$usuarioExcluir['email']);
            $msg = 'Usuário "'.$usuarioExcluir['nome'].'" excluído.'; $msgTipo = 'aviso';
        } else { $msg = 'Erro ao excluir.'; $msgTipo = 'erro'; }
    }
    header('Location: usuarios.php?msg='.urlencode($msg).'&tipo='.$msgTipo); exit;
}

if (isset($_GET['toggle']) && ehAdmin()) {
    $usuarioToggleId = (int)$_GET['toggle'];
    $usuarioToggle   = dbBuscarUsuario($usuarioToggleId);
    if ($usuarioToggle) {
        $novoAtivo = $usuarioToggle['ativo'] ? 0 : 1;
        getDB()->prepare('UPDATE usuarios SET ativo=? WHERE id=?')->execute([$novoAtivo, $usuarioToggleId]);
        registrarLog('TOGGLE_USUARIO','usuarios',$usuarioToggleId,'Ativo:'.($novoAtivo?'sim':'não'));
        $msg = $novoAtivo ? 'Usuário ativado.' : 'Usuário desativado.'; $msgTipo = 'sucesso';
    }
    header('Location: usuarios.php?msg='.urlencode($msg).'&tipo='.$msgTipo); exit;
}

if ($modo === 'editar' && $editId) {
    $usuario = dbBuscarUsuario($editId);
    if (!$usuario) { header('Location: usuarios.php'); exit; }
    if (!ehAdmin() && $usuario['perfil'] !== 'funcionario') {
        header('Location: usuarios.php'); exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioPostId = (int)($_POST['usuario_id'] ?? 0);
    $perfil = $_POST['perfil'] ?? 'funcionario';

    if (!ehAdmin() && $perfil !== 'funcionario') {
        $erros[] = 'Você só pode gerenciar usuários do perfil Funcionário.';
    }

    $dadosUsuario = [
        'nome'                 => trim($_POST['nome'] ?? ''),
        'email'                => strtolower(trim($_POST['email'] ?? '')),
        'senha'                => $_POST['senha'] ?? '',
        'perfil'               => $perfil,
        'ativo'                => isset($_POST['ativo']) ? 1 : 0,
        'is_operador'          => isset($_POST['is_operador']) ? 1 : 0,
        'perm_criar_equip'     => isset($_POST['perm_criar_equip']) ? 1 : 0,
        'perm_editar_equip'    => isset($_POST['perm_editar_equip']) ? 1 : 0,
        'perm_resolver_alarme' => isset($_POST['perm_resolver_alarme']) ? 1 : 0,
    ];

    if ($dadosUsuario['nome'] === '')  $erros[] = 'Nome é obrigatório.';
    if ($dadosUsuario['email'] === '' || !filter_var($dadosUsuario['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
    if (!in_array($dadosUsuario['perfil'], ['admin','funcionario'])) $erros[] = 'Perfil inválido.';
    if (!$usuarioPostId && $dadosUsuario['senha'] === '') $erros[] = 'Senha é obrigatória para novo usuário.';
    if ($dadosUsuario['senha'] !== '' && strlen($dadosUsuario['senha']) < 6) $erros[] = 'Senha deve ter ao menos 6 caracteres.';

    if (empty($erros)) {
        if (dbEmailExiste($dadosUsuario['email'], $usuarioPostId)) {
            $erros[] = 'Este e-mail já está cadastrado.';
        } else {
            if ($usuarioPostId) {
                if (!podeGerenciarPerfil($usuario['perfil'] ?? 'funcionario')) { $erros[] = 'Sem permissão.'; }
                else {
                    dbAtualizarUsuario($usuarioPostId, $dadosUsuario);
                    registrarLog('EDITAR_USUARIO','usuarios',$usuarioPostId,'Perfil:'.$dadosUsuario['perfil']);
                    header('Location: usuarios.php?msg='.urlencode('Usuário atualizado!').'&tipo=sucesso'); exit;
                }
            } else {
                $novoId = dbCriarUsuario($dadosUsuario);
                if ($novoId) {
                    registrarLog('CRIAR_USUARIO','usuarios',$novoId,'Perfil:'.$dadosUsuario['perfil']);
                    header('Location: usuarios.php?msg='.urlencode('Usuário "'.$dadosUsuario['nome'].'" criado!').'&tipo=sucesso'); exit;
                } else { $erros[] = 'Erro ao salvar no banco de dados.'; }
            }
        }
    }

    $usuario = $dadosUsuario;
    if ($usuarioPostId) { $usuario['id'] = $usuarioPostId; $modo = 'editar'; $editId = $usuarioPostId; }
    else { $modo = 'novo'; }
}

if (isset($_GET['msg'])) { $msg = htmlspecialchars($_GET['msg']); $msgTipo = $_GET['tipo'] ?? 'info'; }

$busca      = trim($_GET['busca'] ?? '');
$filtroPerfil = $_GET['perfil_f'] ?? '';
if (!in_array($filtroPerfil, ['', 'admin', 'funcionario'], true)) $filtroPerfil = '';
$listaUsuarios = ($modo === 'listar') ? dbListarUsuarios($busca, $filtroPerfil) : [];

$contagens = ['total'=>0,'admin'=>0,'funcionario'=>0,'ativos'=>0];
foreach ($listaUsuarios as $usuarioItem) {
    $contagens['total']++;
    $contagens[$usuarioItem['perfil']] = ($contagens[$usuarioItem['perfil']] ?? 0) + 1;
    if ($usuarioItem['ativo']) $contagens['ativos']++;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo $icon; ?>
<title>Indux | Usuarios</title>
  <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body>
<?php require_once 'header.php'; ?>
<main class="site-main">

  <div class="page-header">
    <div class="page-header-left">
      <div class="page-icon">{{lucide:users-round}}</div>
      <div>
        <div class="breadcrumb"><span>INDUX</span> / <span>Usuários</span><?php if ($modo!=='listar'): ?> / <span><?= $modo==='novo'?'Novo':'Editar' ?></span><?php endif; ?></div>
        <h1 class="page-title">Usuários do Sistema</h1>
        <p class="page-subtitle">Gerencie acesso, perfis e permissões de operação</p>
      </div>
    </div>
    <?php if ($modo==='listar' && ehAdmin()): ?>
    <a href="usuarios.php?acao=novo" class="btn btn--primary">{{lucide:plus}} Novo Usuário</a>
    <?php elseif ($modo!=='listar'): ?>
    <a href="usuarios.php" class="btn btn--ghost">← Voltar</a>
    <?php endif; ?>
  </div>

  <?php if ($msg): ?>
  <div class="alerta alerta--<?= $msgTipo ?>"><?= $msg ?></div>
  <?php endif; ?>

  <?php if (!empty($erros)): ?>
  <div class="alerta alerta--erro">
    <?php foreach ($erros as $erro): ?><div>{{lucide:triangle-alert}} <?= htmlspecialchars($erro) ?></div><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($modo === 'listar'): ?>

  <div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.25rem">
    <div class="kpi-card kpi-card--cyan" style="padding:.9rem 1rem">
      <div><div class="kpi-label">Total</div><div class="kpi-valor" style="font-size:1.4rem"><?= $contagens['total'] ?></div></div>
    </div>
    <div class="kpi-card kpi-card--purple" style="padding:.9rem 1rem">
      <div><div class="kpi-label">Admins</div><div class="kpi-valor" style="font-size:1.4rem"><?= $contagens['admin'] ?></div></div>
    </div>
    <div class="kpi-card kpi-card--cyan" style="padding:.9rem 1rem">
      <div><div class="kpi-label">Funcionários</div><div class="kpi-valor" style="font-size:1.4rem"><?= $contagens['funcionario'] ?></div></div>
    </div>
    <div class="kpi-card kpi-card--green" style="padding:.9rem 1rem">
      <div><div class="kpi-label">Ativos</div><div class="kpi-valor" style="font-size:1.4rem"><?= $contagens['ativos'] ?></div></div>
    </div>
  </div>

  <form method="GET" action="usuarios.php" style="display:flex;gap:.75rem;margin-bottom:1.25rem;flex-wrap:wrap">
    <div style="flex:1;min-width:220px;position:relative">
      <span class="input-icon">{{lucide:search}}</span>
      <input type="text" name="busca" class="form-control" placeholder="Buscar por nome ou e-mail..."
        value="<?= htmlspecialchars($busca) ?>" style="padding-left:2.25rem">
    </div>
    <select name="perfil_f" class="form-control" style="width:auto">
      <option value="">Todos os perfis</option>
      <option value="admin"       <?= $filtroPerfil==='admin'       ?'selected':'' ?>>Admin</option>
      <option value="funcionario" <?= $filtroPerfil==='funcionario' ?'selected':'' ?>>Funcionário</option>
    </select>
    <button type="submit" class="btn btn--primary">Buscar</button>
    <?php if ($busca || $filtroPerfil): ?><a href="usuarios.php" class="btn btn--ghost">{{lucide:x}} Limpar</a><?php endif; ?>
  </form>

  <div class="panel-card">
    <div class="panel-body" style="padding:0">
      <?php if (empty($listaUsuarios)): ?>
      <div class="empty-state" style="padding:3rem">
        <div class="empty-state__icon">{{lucide:users-round}}</div>
        <div class="empty-state__title">Nenhum usuário encontrado</div>
        <?php if (ehAdmin()): ?>
        <a href="usuarios.php?acao=novo" class="btn btn--primary" style="margin-top:1rem">{{lucide:plus}} Criar Primeiro Usuário</a>
        <?php endif; ?>
      </div>
      <?php else: ?>
      <table class="tabela-estoque">
        <thead><tr>
          <th>Usuário</th>
          <th>E-mail</th>
          <th>Perfil</th>
          <th>Permissões</th>
          <th>Status</th>
          <th>Último Acesso</th>
          <th>Ações</th>
        </tr></thead>
        <tbody>
        <?php foreach ($listaUsuarios as $usuarioItem): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:.75rem">
              <div class="user-avatar" style="width:34px;height:34px;font-size:.8rem;flex-shrink:0"><?= inicialNome($usuarioItem['nome']) ?></div>
              <div>
                <div style="font-weight:600;color:#fff;font-size:.88rem"><?= htmlspecialchars($usuarioItem['nome']) ?></div>
                <div style="font-size:.7rem;color:var(--text-muted);font-family:var(--font-mono)">#<?= $usuarioItem['id'] ?></div>
              </div>
            </div>
          </td>
          <td style="font-family:var(--font-mono);font-size:.78rem;color:var(--text-dim)"><?= htmlspecialchars($usuarioItem['email']) ?></td>
          <td><?= perfilBadgeHtml($usuarioItem['perfil']) ?></td>
          <td>
            <div style="display:flex;flex-wrap:wrap;gap:.3rem">
              <?php if ($usuarioItem['is_operador']): ?><span style="background:rgba(0,200,255,.1);color:var(--accent);border:1px solid rgba(0,200,255,.2);border-radius:4px;padding:1px 5px;font-size:.65rem;font-family:var(--font-mono)">Operador</span><?php endif; ?>
              <?php if ($usuarioItem['perm_criar_equip']): ?><span style="background:rgba(16,185,129,.1);color:var(--green);border:1px solid rgba(16,185,129,.2);border-radius:4px;padding:1px 5px;font-size:.65rem;font-family:var(--font-mono)">Criar Equip.</span><?php endif; ?>
              <?php if ($usuarioItem['perm_editar_equip']): ?><span style="background:rgba(245,158,11,.1);color:var(--yellow);border:1px solid rgba(245,158,11,.2);border-radius:4px;padding:1px 5px;font-size:.65rem;font-family:var(--font-mono)">Editar Equip.</span><?php endif; ?>
              <?php if ($usuarioItem['perm_resolver_alarme']): ?><span style="background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.2);border-radius:4px;padding:1px 5px;font-size:.65rem;font-family:var(--font-mono)">Resolver Alarme</span><?php endif; ?>
              <?php if (!$usuarioItem['is_operador']&&!$usuarioItem['perm_criar_equip']&&!$usuarioItem['perm_editar_equip']&&!$usuarioItem['perm_resolver_alarme']): ?>
              <span style="color:var(--text-muted);font-size:.7rem">Somente Visualização</span>
              <?php endif; ?>
            </div>
          </td>
          <td>
            <?php if ($usuarioItem['ativo']): ?>
            <span class="status-badge status-ativo">{{lucide:circle-check}} Ativo</span>
            <?php else: ?>
            <span class="status-badge status-inativo">{{lucide:pause}} Inativo</span>
            <?php endif; ?>
          </td>
          <td style="font-size:.72rem;color:var(--text-muted);font-family:var(--font-mono)">
            <?= $usuarioItem['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($usuarioItem['ultimo_acesso'])) : '—' ?>
          </td>
          <td>
            <div style="display:flex;gap:.4rem;flex-wrap:wrap">
              <?php if (podeGerenciarPerfil($usuarioItem['perfil'])): ?>
              <a href="usuarios.php?acao=editar&id=<?= $usuarioItem['id'] ?>" class="btn btn--ghost btn--sm">{{lucide:pencil}} Editar</a>
              <?php if (ehAdmin() && $usuarioItem['id'] !== ($_SESSION['usuario_id']??0)): ?>
              <a href="usuarios.php?toggle=<?= $usuarioItem['id'] ?>"
                 class="btn btn--ghost btn--sm"
                 onclick="return confirm('<?= $usuarioItem['ativo']?'Desativar':'Ativar' ?> este usuário?')"
                 style="color:<?= $usuarioItem['ativo']?'var(--red)':'var(--green)' ?>">
                <?= $usuarioItem['ativo']?'{{lucide:pause}}':'{{lucide:play}}' ?>
              </a>
              <a href="usuarios.php?excluir=<?= $usuarioItem['id'] ?>"
                 onclick="return confirm('Excluir usuário <?= htmlspecialchars(addslashes($usuarioItem['nome'])) ?>?')"
                 class="btn btn--danger btn--sm">{{lucide:trash-2}}</a>
              <?php endif; ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <div style="margin-top:1.25rem;background:var(--card);border:1px solid var(--border);border-radius:var(--radius-xl);padding:1.25rem">
    <div class="section-heading-icon" style="font-family:var(--font-display);font-size:.82rem;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem">{{lucide:book-open}} Hierarquia de Acesso</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
      <div>
        <?= perfilBadgeHtml('admin') ?>
        <p style="font-size:.78rem;color:var(--text-muted);margin-top:.5rem;line-height:1.5">Acesso total ao sistema. Gerencia todos os usuários e configurações.</p>
      </div>
      <div>
        <?= perfilBadgeHtml('funcionario') ?>
        <p style="font-size:.78rem;color:var(--text-muted);margin-top:.5rem;line-height:1.5">Acesso por permissão granular: criar equip., editar, resolver alarmes.</p>
      </div>
    </div>
  </div>

  <?php else: ?>

  <?php
    $formUsuario = $usuario ?? [];
    $editandoUsuario = ($modo === 'editar');
  ?>

  <form method="POST" action="usuarios.php" class="user-form">
    <?php if ($editandoUsuario): ?>
    <input type="hidden" name="usuario_id" value="<?= $editId ?>">
    <?php endif; ?>

    <div class="form-card">
      <div class="form-title">{{lucide:user-round}} Dados do Usuário</div>
      <div class="form-grid form-grid--user">

        <div class="form-group">
          <label class="form-label" for="nome">Nome Completo <span style="color:var(--red)">*</span></label>
          <input type="text" id="nome" name="nome" class="form-control"
            placeholder="Ex: João da Silva"
            value="<?= htmlspecialchars($formUsuario['nome'] ?? '') ?>" required maxlength="150">
          <span class="form-hint">&nbsp;</span>
        </div>

        <div class="form-group">
          <label class="form-label" for="email">E-mail <span style="color:var(--red)">*</span></label>
          <input type="email" id="email" name="email" class="form-control"
            placeholder="usuario@empresa.com"
            value="<?= htmlspecialchars($formUsuario['email'] ?? '') ?>" required maxlength="150">
          <span class="form-hint">&nbsp;</span>
        </div>

        <div class="form-group">
          <label class="form-label" for="senha">Senha <?= $editandoUsuario ? '' : '<span style="color:var(--red)">*</span>' ?></label>
          <input type="password" id="senha" name="senha" class="form-control"
            placeholder="<?= $editandoUsuario ? 'Nova senha (opcional)' : 'Mínimo 6 caracteres' ?>"
            minlength="6" autocomplete="new-password">
          <span class="form-hint"><?= $editandoUsuario ? 'Deixe em branco para manter a senha atual' : '&nbsp;' ?></span>
        </div>

        <div class="form-group">
          <label class="form-label" for="perfil">Perfil <span style="color:var(--red)">*</span></label>
          <select id="perfil" name="perfil" class="form-control" <?= !ehAdmin()?'disabled':'' ?>>
            <?php if (ehAdmin()): ?>
            <option value="admin"       <?= ($formUsuario['perfil']??'')==='admin'       ?'selected':'' ?>>Admin — Acesso Total</option>
            <?php endif; ?>
            <option value="funcionario" <?= ($formUsuario['perfil']??'funcionario')==='funcionario' ?'selected':'' ?>>Funcionário — Acesso Limitado</option>
          </select>
          <?php if (!ehAdmin()): ?><input type="hidden" name="perfil" value="funcionario"><?php endif; ?>
          <span class="form-hint">Define o nível de acesso base do usuário</span>
        </div>

        <div class="form-group" style="display:flex;align-items:center;gap:.75rem;padding-top:1.5rem">
          <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.88rem;color:var(--text-dim)">
            <input type="checkbox" name="ativo" value="1" <?= ($formUsuario['ativo']??1)?'checked':'' ?> style="width:16px;height:16px;accent-color:var(--green)">
            Usuário ativo (pode fazer login)
          </label>
        </div>

      </div>

      <div class="form-section" id="secao-permissoes">
        <div class="form-section-title">{{lucide:key-round}} Permissões Adicionais <span style="font-weight:400;color:var(--text-muted);font-size:.78rem">(para perfil Funcionário)</span></div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.75rem">

          <label style="display:flex;align-items:flex-start;gap:.75rem;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:.9rem;cursor:pointer">
            <input type="checkbox" name="is_operador" value="1" <?= ($formUsuario['is_operador']??0)?'checked':'' ?> style="margin-top:2px;accent-color:var(--accent)">
            <div>
              <div class="permission-title">{{lucide:settings}} Operador</div>
              <div style="font-size:.72rem;color:var(--text-muted);margin-top:.2rem">Acesso geral de operação (editar status, registrar leituras)</div>
            </div>
          </label>

          <label style="display:flex;align-items:flex-start;gap:.75rem;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:.9rem;cursor:pointer">
            <input type="checkbox" name="perm_criar_equip" value="1" <?= ($formUsuario['perm_criar_equip']??0)?'checked':'' ?> style="margin-top:2px;accent-color:var(--green)">
            <div>
              <div class="permission-title">{{lucide:plus}} Criar Equipamentos</div>
              <div style="font-size:.72rem;color:var(--text-muted);margin-top:.2rem">Pode cadastrar novos equipamentos</div>
            </div>
          </label>

          <label style="display:flex;align-items:flex-start;gap:.75rem;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:.9rem;cursor:pointer">
            <input type="checkbox" name="perm_editar_equip" value="1" <?= ($formUsuario['perm_editar_equip']??0)?'checked':'' ?> style="margin-top:2px;accent-color:var(--yellow)">
            <div>
              <div class="permission-title">{{lucide:pencil}} Editar Equipamentos</div>
              <div style="font-size:.72rem;color:var(--text-muted);margin-top:.2rem">Pode editar dados e status de equipamentos</div>
            </div>
          </label>

          <label style="display:flex;align-items:flex-start;gap:.75rem;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:.9rem;cursor:pointer">
            <input type="checkbox" name="perm_resolver_alarme" value="1" <?= ($formUsuario['perm_resolver_alarme']??0)?'checked':'' ?> style="margin-top:2px;accent-color:var(--red)">
            <div>
              <div class="permission-title">{{lucide:bell}} Resolver Alarmes</div>
              <div style="font-size:.72rem;color:var(--text-muted);margin-top:.2rem">Pode marcar alarmes como resolvidos</div>
            </div>
          </label>

        </div>
      </div>

      <div class="form-actions">
        <a href="usuarios.php" class="btn btn--ghost">Cancelar</a>
        <button type="submit" class="btn btn--primary btn--lg">
          <?= $editandoUsuario ? '{{lucide:save}} Salvar Alterações' : '{{lucide:plus}} Criar Usuário' ?>
        </button>
      </div>
    </div>
  </form>
  <?php endif; ?>

</main>
<?php require_once 'footer.php'; ?>

<script>
const perfSel = document.getElementById('perfil');
const secPerm = document.getElementById('secao-permissoes');
function togglePerms() {
    if (!perfSel || !secPerm) return;
    secPerm.style.display = perfSel.value === 'funcionario' ? '' : 'none';
}
if (perfSel) { perfSel.addEventListener('change', togglePerms); togglePerms(); }
</script>
</body>
</html>
