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

function mensagemErroUploadFoto(int $codigo): string {
    return match ($codigo) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'A imagem excede o limite permitido.',
        UPLOAD_ERR_PARTIAL => 'O envio da imagem foi interrompido. Tente novamente.',
        UPLOAD_ERR_NO_TMP_DIR => 'A pasta temporaria de upload nao esta disponivel.',
        UPLOAD_ERR_CANT_WRITE => 'O servidor nao conseguiu gravar a imagem.',
        UPLOAD_ERR_EXTENSION => 'O upload foi bloqueado por uma extensao do servidor.',
        default => 'Nao foi possivel receber a imagem enviada.',
    };
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
    $fotoNovaSalva = null;
    $extensaoNovaFoto = null;
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
            $erros[] = mensagemErroUploadFoto((int)$arquivo['error']);
        } elseif ((int)$arquivo['size'] <= 0) {
            $erros[] = 'O arquivo selecionado esta vazio.';
        } elseif ($arquivo['size'] > 2 * 1024 * 1024) {
            $erros[] = 'A foto deve ter no maximo 2 MB.';
        } elseif (!is_uploaded_file($arquivo['tmp_name'])) {
            $erros[] = 'O arquivo recebido nao e um upload valido.';
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
                $extensaoNovaFoto = $extensoes[$mime];
            }
        }
    }

    if (empty($erros) && (!$db instanceof PDO || $usuarioId <= 0)) {
        $erros[] = 'Nao foi possivel localizar seu usuario no banco.';
    }

    if (empty($erros) && $extensaoNovaFoto !== null) {
        $pastaDestino = __DIR__ . DIRECTORY_SEPARATOR . 'IMG' . DIRECTORY_SEPARATOR . 'usuarios';

        if (!is_dir($pastaDestino) && !mkdir($pastaDestino, 0775, true)) {
            $erros[] = 'Nao foi possivel preparar a pasta das fotos.';
        } elseif (!is_writable($pastaDestino)) {
            $erros[] = 'A pasta das fotos nao possui permissao de gravacao.';
        } else {
            try {
                $nomeArquivo = sprintf(
                    'usuario_%d_%s.%s',
                    $usuarioId,
                    bin2hex(random_bytes(8)),
                    $extensaoNovaFoto
                );
                $caminhoDestino = $pastaDestino . DIRECTORY_SEPARATOR . $nomeArquivo;

                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $caminhoDestino)) {
                    $erros[] = 'Nao foi possivel salvar a foto no servidor.';
                } else {
                    @chmod($caminhoDestino, 0644);
                    $novaFoto = 'IMG/usuarios/' . $nomeArquivo;
                    $fotoNovaSalva = $novaFoto;
                    if ($fotoAtual) {
                        $fotoParaRemover = $fotoAtual;
                    }
                }
            } catch (Throwable $e) {
                $erros[] = 'Nao foi possivel gerar o arquivo da nova foto.';
            }
        }
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
            $db->beginTransaction();
            $db->prepare('UPDATE usuarios SET ' . implode(', ', $campos) . ' WHERE id = ?')
               ->execute($parametros);
            $db->commit();

            if ($fotoParaRemover && $fotoParaRemover !== $novaFoto) {
                removerFotoUsuarioLocal($fotoParaRemover);
            }

            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_foto'] = $fotoColunaDisponivel ? ($novaFoto ?? '') : ($_SESSION['usuario_foto'] ?? '');

            registrarLog('ATUALIZAR_CONFIGURACOES_USUARIO', 'usuarios', $usuarioId);
            header('Location: configuracoes.php?msg=' . urlencode('Configuracoes atualizadas!') . '&tipo=sucesso');
            exit;
        } catch (Throwable $e) {
            if ($db instanceof PDO && $db->inTransaction()) {
                $db->rollBack();
            }
            if ($fotoNovaSalva) {
                removerFotoUsuarioLocal($fotoNovaSalva);
                $novaFoto = $fotoAtual;
            }
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
        <div class="page-icon">{{lucide:settings}}</div>
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
      <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
      <div class="settings-layout">
        <div class="form-card settings-photo-card">
          <div class="settings-card-heading">
            <span class="settings-card-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M4 8.5A2.5 2.5 0 0 1 6.5 6H8l1.2-2h5.6L16 6h1.5A2.5 2.5 0 0 1 20 8.5v8A2.5 2.5 0 0 1 17.5 19h-11A2.5 2.5 0 0 1 4 16.5v-8Z"/>
                <circle cx="12" cy="12.5" r="3.5"/>
              </svg>
            </span>
            <div>
              <div class="form-title">Foto do Usuário</div>
              <p>Personalize como você aparece no sistema.</p>
            </div>
          </div>

          <div class="profile-photo-stage">
            <input type="file" id="foto" name="foto" class="profile-file-input" accept="image/png,image/jpeg,image/webp,image/gif">
            <input type="checkbox" id="remover_foto" name="remover_foto" value="1" class="profile-remove-input">

            <label class="profile-photo-picker" for="foto" id="profilePhotoPicker" tabindex="0">
              <span class="profile-photo-preview" id="profilePhotoPreview">
                <img
                  id="profilePhotoImage"
                  src="<?php echo $fotoUsuario ? htmlspecialchars($fotoUsuario) : ''; ?>"
                  alt="<?php echo htmlspecialchars($usuario['nome'] ?? 'Usuário'); ?>"
                  <?php echo $fotoUsuario ? '' : 'hidden'; ?>
                >
                <span id="profilePhotoInitials" <?php echo $fotoUsuario ? 'hidden' : ''; ?>>
                  <?php echo inicialNome($usuario['nome'] ?? 'U'); ?>
                </span>
                <span class="profile-photo-overlay" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none">
                    <path d="M4 8.5A2.5 2.5 0 0 1 6.5 6H8l1.2-2h5.6L16 6h1.5A2.5 2.5 0 0 1 20 8.5v8A2.5 2.5 0 0 1 17.5 19h-11A2.5 2.5 0 0 1 4 16.5v-8Z"/>
                    <circle cx="12" cy="12.5" r="3.5"/>
                  </svg>
                  <strong>Alterar foto</strong>
                  <small>Clique ou arraste aqui</small>
                </span>
              </span>
              <span class="profile-camera-badge" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                  <path d="M4 8.5A2.5 2.5 0 0 1 6.5 6H8l1.2-2h5.6L16 6h1.5A2.5 2.5 0 0 1 20 8.5v8A2.5 2.5 0 0 1 17.5 19h-11A2.5 2.5 0 0 1 4 16.5v-8Z"/>
                  <circle cx="12" cy="12.5" r="3.5"/>
                </svg>
              </span>
            </label>
          </div>

          <div class="profile-file-status" id="profileFileStatus" aria-live="polite">
            <span class="profile-file-status__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M12 16V4"/>
                <path d="m7 9 5-5 5 5"/>
                <path d="M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"/>
              </svg>
            </span>
            <span>
              <strong id="profileFileName">Selecione uma nova imagem</strong>
              <small id="profileFileDetails">JPG, PNG, WEBP ou GIF, até 2 MB</small>
            </span>
          </div>

          <div class="profile-photo-actions">
            <label for="foto" class="btn btn--ghost profile-select-button">
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 16V4"/>
                <path d="m7 9 5-5 5 5"/>
                <path d="M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"/>
              </svg>
              Escolher foto
            </label>
            <button
              type="button"
              class="btn profile-remove-photo"
              id="removePhotoButton"
              <?php echo $fotoUsuario ? '' : 'hidden'; ?>
            >
              Remover
            </button>
          </div>

          <p class="profile-photo-tip">
            A imagem será recortada em formato circular. Prefira uma foto quadrada e bem iluminada.
          </p>
        </div>

        <div class="form-card settings-data-card">
          <div class="settings-card-heading settings-card-heading--data">
            <span class="settings-card-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M12 3 5 6v5c0 4.6 2.9 8.2 7 10 4.1-1.8 7-5.4 7-10V6l-7-3Z"/>
                <path d="M9 11a3 3 0 1 1 6 0"/>
                <path d="M8 17c.8-2 2.1-3 4-3s3.2 1 4 3"/>
              </svg>
            </span>
            <div>
              <div class="form-title">Dados e Segurança</div>
              <p>Gerencie sua identificação e credenciais de acesso.</p>
            </div>
          </div>

          <div class="form-grid form-grid--settings">
            <div class="form-group">
              <label class="form-label" for="nome">Nome <span style="color:var(--red)">*</span></label>
              <input type="text" id="nome" name="nome" class="form-control" required maxlength="150" autocomplete="name" value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>">
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
                <div class="password-field">
                  <input type="password" id="senha_atual" name="senha_atual" class="form-control" autocomplete="current-password">
                  <button type="button" class="password-toggle" data-password-toggle="senha_atual" aria-label="Mostrar senha atual">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                      <circle cx="12" cy="12" r="2.5"/>
                    </svg>
                  </button>
                </div>
                <span class="form-hint">&nbsp;</span>
              </div>
              <div class="form-group">
                <label class="form-label" for="nova_senha">Nova senha</label>
                <div class="password-field">
                  <input type="password" id="nova_senha" name="nova_senha" class="form-control" minlength="6" autocomplete="new-password">
                  <button type="button" class="password-toggle" data-password-toggle="nova_senha" aria-label="Mostrar nova senha">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                      <circle cx="12" cy="12" r="2.5"/>
                    </svg>
                  </button>
                </div>
                <span class="form-hint" id="passwordStrengthText">Mínimo de 6 caracteres.</span>
                <span class="password-strength" aria-hidden="true"><i id="passwordStrengthBar"></i></span>
              </div>
              <div class="form-group">
                <label class="form-label" for="confirmar_senha">Confirmar senha</label>
                <div class="password-field">
                  <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" minlength="6" autocomplete="new-password">
                  <button type="button" class="password-toggle" data-password-toggle="confirmar_senha" aria-label="Mostrar confirmação da senha">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                      <circle cx="12" cy="12" r="2.5"/>
                    </svg>
                  </button>
                </div>
                <span class="form-hint" id="passwordMatchText">&nbsp;</span>
              </div>
            </div>
          </div>

          <div class="form-actions">
            <a href="dashboard.php" class="btn btn--ghost">Cancelar</a>
            <button type="submit" class="btn btn--primary btn--lg">
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 4h12l2 2v14H5V4Z"/>
                <path d="M8 4v6h8V4"/>
                <path d="M8 20v-6h8v6"/>
              </svg>
              Salvar Configurações
            </button>
          </div>
        </div>
      </div>
    </form>
  </main>

  <script>
  (() => {
    const input = document.getElementById('foto');
    const picker = document.getElementById('profilePhotoPicker');
    const previewImage = document.getElementById('profilePhotoImage');
    const previewInitials = document.getElementById('profilePhotoInitials');
    const removeInput = document.getElementById('remover_foto');
    const removeButton = document.getElementById('removePhotoButton');
    const fileStatus = document.getElementById('profileFileStatus');
    const fileName = document.getElementById('profileFileName');
    const fileDetails = document.getElementById('profileFileDetails');
    const initialPhoto = <?php echo json_encode($fotoUsuario ?: null, JSON_UNESCAPED_SLASHES); ?>;
    const maxSize = 2 * 1024 * 1024;
    const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    let previewUrl = null;

    const releasePreviewUrl = () => {
      if (previewUrl) {
        URL.revokeObjectURL(previewUrl);
        previewUrl = null;
      }
    };

    const showInitials = () => {
      releasePreviewUrl();
      previewImage.hidden = true;
      previewImage.removeAttribute('src');
      previewInitials.hidden = false;
    };

    const showImage = (source) => {
      previewImage.src = source;
      previewImage.hidden = false;
      previewInitials.hidden = true;
    };

    const setStatus = (title, detail, state = '') => {
      fileName.textContent = title;
      fileDetails.textContent = detail;
      fileStatus.dataset.state = state;
    };

    const validateAndPreview = (file) => {
      if (!file) return false;

      if (!validTypes.includes(file.type)) {
        input.value = '';
        setStatus('Formato não suportado', 'Use JPG, PNG, WEBP ou GIF.', 'error');
        return false;
      }

      if (file.size > maxSize) {
        input.value = '';
        setStatus('Imagem muito grande', 'Escolha um arquivo com no máximo 2 MB.', 'error');
        return false;
      }

      releasePreviewUrl();
      previewUrl = URL.createObjectURL(file);
      showImage(previewUrl);
      removeInput.checked = false;
      removeButton.hidden = false;
      setStatus(
        file.name,
        `${(file.size / 1024).toFixed(0)} KB · pronta para salvar`,
        'ready'
      );
      return true;
    };

    input.addEventListener('change', () => validateAndPreview(input.files[0]));

    picker.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        input.click();
      }
    });

    ['dragenter', 'dragover'].forEach((eventName) => {
      picker.addEventListener(eventName, (event) => {
        event.preventDefault();
        picker.classList.add('is-dragging');
      });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
      picker.addEventListener(eventName, (event) => {
        event.preventDefault();
        picker.classList.remove('is-dragging');
      });
    });

    picker.addEventListener('drop', (event) => {
      const file = event.dataTransfer.files[0];
      if (!file) return;

      const transfer = new DataTransfer();
      transfer.items.add(file);
      input.files = transfer.files;
      validateAndPreview(file);
    });

    removeButton.addEventListener('click', () => {
      input.value = '';
      removeInput.checked = true;
      removeButton.hidden = true;
      showInitials();
      setStatus('Foto atual será removida', 'Clique em salvar para confirmar.', 'removed');
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
      button.addEventListener('click', () => {
        const field = document.getElementById(button.dataset.passwordToggle);
        const showing = field.type === 'text';
        field.type = showing ? 'password' : 'text';
        button.classList.toggle('is-visible', !showing);
        button.setAttribute('aria-label', showing ? 'Mostrar senha' : 'Ocultar senha');
      });
    });

    const newPassword = document.getElementById('nova_senha');
    const confirmPassword = document.getElementById('confirmar_senha');
    const strengthBar = document.getElementById('passwordStrengthBar');
    const strengthText = document.getElementById('passwordStrengthText');
    const matchText = document.getElementById('passwordMatchText');

    const updatePasswordFeedback = () => {
      const value = newPassword.value;
      let score = 0;
      if (value.length >= 6) score++;
      if (value.length >= 10) score++;
      if (/[A-Z]/.test(value) && /[a-z]/.test(value)) score++;
      if (/\d/.test(value) && /[^A-Za-z0-9]/.test(value)) score++;

      strengthBar.style.width = value ? `${score * 25}%` : '0';
      strengthBar.dataset.score = String(score);
      strengthText.textContent = value
        ? ['Senha muito curta', 'Senha básica', 'Senha razoável', 'Senha forte'][Math.max(0, score - 1)]
        : 'Mínimo de 6 caracteres.';

      if (!confirmPassword.value) {
        matchText.innerHTML = '&nbsp;';
        matchText.dataset.state = '';
      } else {
        const matches = value === confirmPassword.value;
        matchText.textContent = matches ? 'As senhas coincidem.' : 'As senhas não coincidem.';
        matchText.dataset.state = matches ? 'success' : 'error';
      }
    };

    newPassword.addEventListener('input', updatePasswordFeedback);
    confirmPassword.addEventListener('input', updatePasswordFeedback);

    window.addEventListener('beforeunload', releasePreviewUrl);

    if (!initialPhoto) {
      showInitials();
    }
  })();
  </script>

  <?php require_once 'footer.php'; ?>
</body>
</html>
