<?php
require_once 'icon.php';
define('DB_HOST',    '127.0.0.1');
define('DB_NAME',    'indux');
define('DB_USER',    'root');
define('DB_PASS',    '123');
define('DB_CHARSET', 'utf8mb4');    

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:monospace;background:#1a0000;color:#ff4444;padding:2rem;border-radius:8px;margin:2rem">
                <strong>❌ ERRO DE CONEXÃO COM O BANCO DE DADOS</strong><br><br>
                Verifique as configurações em <code>db.php</code>:<br>
                HOST: ' . DB_HOST . '<br>BANCO: ' . DB_NAME . '<br>USUÁRIO: ' . DB_USER . '<br><br>
                Detalhes: ' . htmlspecialchars($e->getMessage()) . '
            </div>');
        }
    }
    return $pdo;
}

function registrarLog(string $acao, ?string $tabela = null, ?int $registroId = null, ?string $detalhes = null): void {
    try {
        $db = getDB();
        $db->prepare('INSERT INTO log_sistema (usuario_id, acao, tabela_afetada, registro_id, detalhes, ip) VALUES (?,?,?,?,?,?)')
           ->execute([$_SESSION['usuario_id'] ?? null, $acao, $tabela, $registroId, $detalhes, $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (Throwable $e) {}
}

function dbListarUsuarios(string $busca = '', string $perfil = ''): array {
    try {
        $filtrosSql = []; $parametros = [];
        if ($busca !== '') { $filtrosSql[] = '(nome LIKE ? OR email LIKE ?)'; $buscaLike = '%'.$busca.'%'; $parametros[] = $buscaLike; $parametros[] = $buscaLike; }
        if ($perfil !== '') { $filtrosSql[] = 'perfil = ?'; $parametros[] = $perfil; }
        $sql = 'SELECT * FROM usuarios' . ($filtrosSql ? ' WHERE '.implode(' AND ',$filtrosSql) : '') . ' ORDER BY id ASC';
        $consultaUsuarios = getDB()->prepare($sql);
        $consultaUsuarios->execute($parametros);
        $usuarios = $consultaUsuarios->fetchAll();
        foreach ($usuarios as &$usuario) {
            $usuario['perfil'] = ($usuario['perfil'] ?? '') === 'admin' ? 'admin' : 'funcionario';
        }
        unset($usuario);
        return $usuarios;
    } catch (Throwable $e) { return []; }
}

function dbBuscarUsuario(int $id): ?array {
    try {
        $consultaUsuario = getDB()->prepare('SELECT * FROM usuarios WHERE id = ?');
        $consultaUsuario->execute([$id]);
        $usuario = $consultaUsuario->fetch() ?: null;
        if ($usuario) {
            $usuario['perfil'] = ($usuario['perfil'] ?? '') === 'admin' ? 'admin' : 'funcionario';
        }
        return $usuario;
    } catch (Throwable $e) { return null; }
}

function dbCriarUsuario(array $dados): int|false {
    try {
        $db = getDB();
        $perfil = ($dados['perfil'] ?? '') === 'admin' ? 'admin' : 'funcionario';
        $db->prepare('INSERT INTO usuarios (nome,email,senha,perfil,ativo,perm_criar_equip,perm_editar_equip,perm_resolver_alarme,is_operador) VALUES (?,?,?,?,?,?,?,?,?)')
           ->execute([$dados['nome'],$dados['email'],password_hash($dados['senha'],PASSWORD_DEFAULT),$perfil,$dados['ativo'],$dados['perm_criar_equip']??0,$dados['perm_editar_equip']??0,$dados['perm_resolver_alarme']??0,$dados['is_operador']??0]);
        return (int)$db->lastInsertId();
    } catch (Throwable $e) { return false; }
}

function dbAtualizarUsuario(int $id, array $dados): bool {
    try {
        $db = getDB();
        $perfil = ($dados['perfil'] ?? '') === 'admin' ? 'admin' : 'funcionario';
        if (!empty($dados['senha'])) {
            $db->prepare('UPDATE usuarios SET nome=?,email=?,senha=?,perfil=?,ativo=?,perm_criar_equip=?,perm_editar_equip=?,perm_resolver_alarme=?,is_operador=? WHERE id=?')
               ->execute([$dados['nome'],$dados['email'],password_hash($dados['senha'],PASSWORD_DEFAULT),$perfil,$dados['ativo'],$dados['perm_criar_equip']??0,$dados['perm_editar_equip']??0,$dados['perm_resolver_alarme']??0,$dados['is_operador']??0,$id]);
        } else {
            $db->prepare('UPDATE usuarios SET nome=?,email=?,perfil=?,ativo=?,perm_criar_equip=?,perm_editar_equip=?,perm_resolver_alarme=?,is_operador=? WHERE id=?')
               ->execute([$dados['nome'],$dados['email'],$perfil,$dados['ativo'],$dados['perm_criar_equip']??0,$dados['perm_editar_equip']??0,$dados['perm_resolver_alarme']??0,$dados['is_operador']??0,$id]);
        }
        return true;
    } catch (Throwable $e) { return false; }
}

function dbExcluirUsuario(int $id): bool {
    try { getDB()->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$id]); return true; }
    catch (Throwable $e) { return false; }
}

function dbEmailExiste(string $email, int $excluirId = 0): bool {
    try {
        $consultaEmail = getDB()->prepare('SELECT id FROM usuarios WHERE email = ? AND id != ?');
        $consultaEmail->execute([$email, $excluirId]);
        return (bool)$consultaEmail->fetch();
    } catch (Throwable $e) { return false; }
}

function dbListarEquipamentos(): array {
    try {
        return getDB()->query(
            "SELECT e.*,
                (SELECT ls.temperatura   FROM leituras_sensor ls WHERE ls.equipamento_id=e.id ORDER BY ls.id DESC LIMIT 1) AS ultima_temp,
                (SELECT ls.pressao       FROM leituras_sensor ls WHERE ls.equipamento_id=e.id ORDER BY ls.id DESC LIMIT 1) AS ultima_pressao,
                (SELECT ls.umidade       FROM leituras_sensor ls WHERE ls.equipamento_id=e.id ORDER BY ls.id DESC LIMIT 1) AS ultima_umidade,
                (SELECT ls.registrado_em FROM leituras_sensor ls WHERE ls.equipamento_id=e.id ORDER BY ls.id DESC LIMIT 1) AS ultima_leitura,
                (SELECT COUNT(*) FROM alarmes a WHERE a.equipamento_id=e.id AND a.resolvido=0 AND e.status <> 'inativo') AS qtd_alarmes
             FROM equipamentos e
             ORDER BY CASE e.status WHEN 'em_falha' THEN 0 WHEN 'ativo' THEN 1 ELSE 2 END, e.nome"
        )->fetchAll();
    } catch (Throwable $e) { return []; }
}

function dbBuscarEquipamento(int $id): ?array {
    try {
        $consultaEquipamento = getDB()->prepare('SELECT * FROM equipamentos WHERE id = ?');
        $consultaEquipamento->execute([$id]);
        return $consultaEquipamento->fetch() ?: null;
    } catch (Throwable $e) { return null; }
}

function dbEstatisticasEquipamentos(): array {
    $estatisticas = ['total'=>0,'ativos'=>0,'inativos'=>0,'em_falha'=>0];
    try {
        $linhasStatus = getDB()->query("SELECT status, COUNT(*) as qtd FROM equipamentos GROUP BY status")->fetchAll();
        foreach ($linhasStatus as $linhaStatus) {
            $chaveStatus = $linhaStatus['status']==='ativo' ? 'ativos' : ($linhaStatus['status']==='inativo' ? 'inativos' : 'em_falha');
            $estatisticas[$chaveStatus] = (int)$linhaStatus['qtd']; $estatisticas['total'] += (int)$linhaStatus['qtd'];
        }
    } catch (Throwable $e) {}
    return $estatisticas;
}

function dbContarAlarmes(): array {
    $contagem = ['total'=>0,'critico'=>0,'alerta'=>0,'informativo'=>0,'resolvidos'=>0];
    try {
        $db = getDB();
        $linhasSeveridade = $db->query(
            "SELECT a.severidade, COUNT(*) as qtd
               FROM alarmes a
               JOIN equipamentos e ON e.id = a.equipamento_id
              WHERE a.resolvido=0
                AND e.status <> 'inativo'
              GROUP BY a.severidade"
        )->fetchAll();
        foreach ($linhasSeveridade as $linhaSeveridade) { $contagem[$linhaSeveridade['severidade']] = (int)$linhaSeveridade['qtd']; $contagem['total'] += (int)$linhaSeveridade['qtd']; }
        $contagem['resolvidos'] = (int)$db->query(
            "SELECT COUNT(*)
               FROM alarmes a
               JOIN equipamentos e ON e.id = a.equipamento_id
              WHERE a.resolvido=1
                AND e.status <> 'inativo'"
        )->fetchColumn();
    } catch (Throwable $e) {}
    return $contagem;
}

if (!function_exists('perfilBadgeHtml')) {
    function perfilBadgeHtml(string $perfil): string {
        $perfil = $perfil === 'admin' ? 'admin' : 'funcionario';
        $t=['admin'=>'👑 Admin','funcionario'=>'👤 Funcionário'];
        $c=['admin'=>'perfil--admin','funcionario'=>'perfil--funcionario'];
        return '<span class="perfil-badge '.($c[$perfil]??'').'">'.(htmlspecialchars($t[$perfil]??$perfil)).'</span>';
    }
}
