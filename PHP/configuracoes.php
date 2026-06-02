<?php
require_once 'icon.php';
require_once 'init.php';
require_once 'db.php';
requerLogin();

$msg = '';
$msgTipo = 'info';
$erros = [];
$fotoColunaDisponivel = false;

function garantirColunaFotoUsuario(PDO $db): bool {
    try {
        $consulta = $db->prepare(
            "SELECT COUNT(*)
               FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'usuarios'
                AND COLUMN_NAME = 'foto'"
        );
        $consulta->execute();
        if ((int)$consulta->fetchColumn() === 0) {
            $db->exec("ALTER TABLE usuarios ADD COLUMN foto VARCHAR(255) DEFAULT NULL AFTER perfil");
        }
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function removerFotoUsuarioLocal(?string $foto): void {
    if (!$foto || strncmp($foto, 'IMG/usuarios/', 13) !== 0) {
        return;
    }

    $caminho = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $foto);
    if (is_file($caminho)) {
        @unlink($caminho);
    }
}

$usuarioId = (int)($_SESSION['usuario_id'] ?? 0);

try {
    $db = getDB();
    $fotoColunaDisponivel = garantirColunaFotoUsuario($db);
} catch (Throwable $e) {
    $db = null;
}

$usuario = $usuarioId ? dbBuscarUsuario($usuarioId) : null;
if (!$usuario) {
    $usuario = [
        'id' => $usuarioId,
        'nome' => $_SESSION['usuario_nome'] ?? 'Usuario',
        'email' => $_SESSION['usuario_email'] ?? '',
        'senha' => '',
        'foto' => $_SESSION['usuario_foto'] ?? '',
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $senhaAtual = $_POST['senha_atual'] ?? '';
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';
    $fotoAtual = $usuario['foto'] ?? '';
    $novaFoto = $fotoAtual;
    $fotoParaRemover = null;
    $tentouAlterarFoto = (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) || isset($_POST['remover_foto']);

    if ($nome === '') {
        $erros[] = 'Nome e obrigatorio.';
    }

    $alterarSenha = $senhaAtual !== '' || $novaSenha !== '' || $confirmarSenha !== '';
    if ($alterarSenha) {
        if ($senhaAtual === '' || $novaSenha === '' || $confirmarSenha === '') {
            $erros[] = 'Preencha a senha atual, a nova senha e a confirmacao.';
        } elseif (!password_verify($senhaAtual, $usuario['senha'] ?? '')) {
            $erros[] = 'Senha atual incorreta.';
        } elseif (strlen($novaSenha) < 6) {
            $erros[] = 'A nova senha deve ter ao menos 6 caracteres.';
        } elseif ($novaSenha !== $confirmarSenha) {
            $erros[] = 'A confirmacao da nova senha nao confere.';
        }
    }

    if (!$fotoColunaDisponivel && $tentouAlterarFoto) {
        $erros[] = 'Nao foi possivel preparar o campo de foto no banco.';
    }

    if ($fotoColunaDisponivel && isset($_POST['remover_foto']) && $fotoAtual) {
        $novaFoto = null;
        $fotoParaRemover = $fotoAtual;
    }

    if ($fotoColunaDisponivel && isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $arquivo = $_FILES['foto'];

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            $erros[] = 'Erro ao receber a foto enviada.';
        } elseif ($arquivo['size'] > 2 * 1024 * 1024) {
            $erros[] = 'A foto deve ter no maximo 2 MB.';
        } else {
            $infoImagem = @getimagesize($arquivo['tmp_name']);
            $extensoes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ];
            $mime = $infoImagem['mime'] ?? '';

            if (!isset($extensoes[$mime])) {
                $erros[] = 'Envie uma imagem JPG, PNG, WEBP ou GIF.';
            } else {
                $pastaDestino = __DIR__ . DIRECTORY_SEPARATOR . 'IMG' . DIRECTORY_SEPARATOR . 'usuarios';
                if (!is_dir($pastaDestino)) {
                    mkdir($pastaDestino, 0775, true);
                }

                $nomeArquivo = 'usuario_' . $usuarioId . '_' . time() . '.' . $extensoes[$mime];
                $caminhoDestino = $pastaDestino . DIRECTORY_SEPARATOR . $nomeArquivo;
                if (move_uploaded_file($arquivo['tmp_name'], $caminhoDestino)) {
                    $novaFoto = 'IMG/usuarios/' . $nomeArquivo;
                    if ($fotoAtual) {
                        $fotoParaRemover = $fotoAtual;
                    }
                } else {
                    $erros[] = 'Nao foi possivel salvar a foto.';
                }
            }
        }
    }

    if (empty($erros) && (!$db instanceof PDO || $usuarioId <= 0)) {
        $erros[] = 'Nao foi possivel localizar seu usuario no banco.';
    }

    if (empty($erros)) {
        try {
            $campos = ['nome = ?'];
            $parametros = [$nome];

            if ($alterarSenha) {
                $campos[] = 'senha = ?';
                $parametros[] = password_hash($novaSenha, PASSWORD_DEFAULT);
            }

            if ($fotoColunaDisponivel) {
                $campos[] = 'foto = ?';
                $parametros[] = $novaFoto;
            }

            $parametros[] = $usuarioId;
            $db->prepare('UPDATE usuarios SET ' . implode(', ', $campos) . ' WHERE id = ?')
               ->execute($parametros);

            if ($fotoParaRemover && $fotoParaRemover !== $novaFoto) {
                removerFotoUsuarioLocal($fotoParaRemover);
            }

            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_foto'] = $fotoColunaDisponivel ? ($novaFoto ?? '') : ($_SESSION['usuario_foto'] ?? '');

            registrarLog('ATUALIZAR_CONFIGURACOES_USUARIO', 'usuarios', $usuarioId);
            header('Location: configuracoes.php?msg=' . urlencode('Configuracoes atualizadas!') . '&tipo=sucesso');
            exit;
        } catch (Throwable $e) {
            $erros[] = 'Erro ao salvar as configuracoes.';
        }
    }

    $usuario['nome'] = $nome;
    $usuario['foto'] = $novaFoto;
}

if (isset($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
    $msgTipo = $_GET['tipo'] ?? 'info';
}

$fotoUsuario = $usuario['foto'] ?? ($_SESSION['usuario_foto'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php echo $icon; ?>
  <title>Indux | Configuracoes</title>
  <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body>
  <?php require_once 'header.php'; ?>

  <main class="site-main">
    <div class="page-header">
      <div class="page-header-left">
        <div class="page-icon">⚙️</div>
        <div>
          <div class="breadcrumb"><span>INDUX</span> / <span>Configurações</span></div>
          <h1 class="page-title">Configurações do Usuário</h1>
          <p class="page-subtitle">Atualize seus dados de acesso e a foto exibida no sistema</p>
        </div>
      </div>
    </div>

    <?php if ($msg): ?>
      <div class="alerta alerta--<?php echo htmlspecialchars($msgTipo); ?>"><?php echo $msg; ?></div>
    <?php endif; ?>

    <?php foreach ($erros as $erro): ?>
      <div class="alerta alerta--erro"><?php echo htmlspecialchars($erro); ?></div>
    <?php endforeach; ?>

    <form method="POST" action="configuracoes.php" enctype="multipart/form-data" class="settings-form">
      <div class="settings-layout">
        <div class="form-card settings-photo-card">
          <div class="form-title">Foto do Usuário</div>
          <div class="profile-photo-preview">
            <?php if ($fotoUsuario): ?>
              <img src="<?php echo htmlspecialchars($fotoUsuario); ?>" alt="<?php echo htmlspecialchars($usuario['nome'] ?? 'Usuário'); ?>">
            <?php else: ?>
              <span><?php echo inicialNome($usuario['nome'] ?? 'U'); ?></span>
            <?php endif; ?>
          </div>

          <label class="form-label" for="foto">Nova foto</label>
          <input type="file" id="foto" name="foto" class="form-control profile-file-input" accept="image/png,image/jpeg,image/webp,image/gif">
          <span class="form-hint">JPG, PNG, WEBP ou GIF ate 2 MB.</span>

          <?php if ($fotoUsuario): ?>
          <label class="profile-remove-photo">
            <input type="checkbox" name="remover_foto" value="1">
            Remover foto atual
          </label>
          <?php endif; ?>
        </div>

        <div class="form-card settings-data-card">
          <div class="form-title">Dados e Segurança</div>
          <div class="form-grid form-grid--settings">
            <div class="form-group">
              <label class="form-label" for="nome">Nome <span style="color:var(--red)">*</span></label>
              <input type="text" id="nome" name="nome" class="form-control" required maxlength="150" value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>">
              <span class="form-hint">&nbsp;</span>
            </div>

            <div class="form-group">
              <label class="form-label" for="email">E-mail</label>
              <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" disabled>
              <span class="form-hint">O e-mail de login não é alterado aqui.</span>
            </div>
          </div>

          <div class="form-section">
            <div class="form-section-title">Troca de Senha</div>
            <div class="form-grid form-grid--password">
              <div class="form-group">
                <label class="form-label" for="senha_atual">Senha atual</label>
                <input type="password" id="senha_atual" name="senha_atual" class="form-control" autocomplete="current-password">
                <span class="form-hint">&nbsp;</span>
              </div>
              <div class="form-group">
                <label class="form-label" for="nova_senha">Nova senha</label>
                <input type="password" id="nova_senha" name="nova_senha" class="form-control" minlength="6" autocomplete="new-password">
                <span class="form-hint">Mínimo de 6 caracteres.</span>
              </div>
              <div class="form-group">
                <label class="form-label" for="confirmar_senha">Confirmar senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" minlength="6" autocomplete="new-password">
                <span class="form-hint">&nbsp;</span>
              </div>
            </div>
          </div>

          <div class="form-actions">
            <a href="dashboard.php" class="btn btn--ghost">Cancelar</a>
            <button type="submit" class="btn btn--primary btn--lg">💾 Salvar Configurações</button>
          </div>
        </div>
      </div>
    </form>
  </main>

  <?php require_once 'footer.php'; ?>
</body>
</html>
