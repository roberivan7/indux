<?php
// ============================================================
// INDUX — db.php  v2.0
// Conexão centralizada e funções de acesso ao banco de dados
// ============================================================

define('DB_HOST',    '127.0.0.1');
define('DB_NAME',    'db_teste');
define('DB_USER',    'Karla');
define('DB_PASS',    '123');
define('DB_CHARSET', 'utf8mb4');

// ── Conexão PDO (singleton) ──────────────────────────────
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

// ── Log de sistema ───────────────────────────────────────
function registrarLog(string $acao, ?string $tabela = null, ?int $registroId = null, ?string $detalhes = null): void {
    try {
        $db = getDB();
        $db->prepare('INSERT INTO log_sistema (usuario_id, acao, tabela_afetada, registro_id, detalhes, ip) VALUES (?,?,?,?,?,?)')
           ->execute([$_SESSION['usuario_id'] ?? null, $acao, $tabela, $registroId, $detalhes, $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (Throwable $e) {}
}

// ══════════════════════════════════════════════════════════
// QUERIES — USUÁRIOS
// ══════════════════════════════════════════════════════════

function dbListarUsuarios(string $busca = '', string $perfil = ''): array {
    try {
        $filtrosSql = []; $parametros = [];
        if ($busca !== '') { $filtrosSql[] = '(nome LIKE ? OR email LIKE ?)'; $buscaLike = '%'.$busca.'%'; $parametros[] = $buscaLike; $parametros[] = $buscaLike; }
        if ($perfil !== '') { $filtrosSql[] = 'perfil = ?'; $parametros[] = $perfil; }
        $sql = 'SELECT * FROM usuarios' . ($filtrosSql ? ' WHERE '.implode(' AND ',$filtrosSql) : '') . ' ORDER BY perfil, nome';
        $consultaUsuarios = getDB()->prepare($sql);
        $consultaUsuarios->execute($parametros);
        return $consultaUsuarios->fetchAll();
    } catch (Throwable $e) { return []; }
}

function dbBuscarUsuario(int $id): ?array {
    try {
        $consultaUsuario = getDB()->prepare('SELECT * FROM usuarios WHERE id = ?');
        $consultaUsuario->execute([$id]);
        return $consultaUsuario->fetch() ?: null;
    } catch (Throwable $e) { return null; }
}

function dbCriarUsuario(array $dados): int|false {
    try {
        $db = getDB();
        $db->prepare('INSERT INTO usuarios (nome,email,senha,perfil,ativo,perm_criar_equip,perm_editar_equip,perm_resolver_alarme,is_operador) VALUES (?,?,?,?,?,?,?,?,?)')
           ->execute([$dados['nome'],$dados['email'],password_hash($dados['senha'],PASSWORD_DEFAULT),$dados['perfil'],$dados['ativo'],$dados['perm_criar_equip']??0,$dados['perm_editar_equip']??0,$dados['perm_resolver_alarme']??0,$dados['is_operador']??0]);
        return (int)$db->lastInsertId();
    } catch (Throwable $e) { return false; }
}

function dbAtualizarUsuario(int $id, array $dados): bool {
    try {
        $db = getDB();
        if (!empty($dados['senha'])) {
            $db->prepare('UPDATE usuarios SET nome=?,email=?,senha=?,perfil=?,ativo=?,perm_criar_equip=?,perm_editar_equip=?,perm_resolver_alarme=?,is_operador=? WHERE id=?')
               ->execute([$dados['nome'],$dados['email'],password_hash($dados['senha'],PASSWORD_DEFAULT),$dados['perfil'],$dados['ativo'],$dados['perm_criar_equip']??0,$dados['perm_editar_equip']??0,$dados['perm_resolver_alarme']??0,$dados['is_operador']??0,$id]);
        } else {
            $db->prepare('UPDATE usuarios SET nome=?,email=?,perfil=?,ativo=?,perm_criar_equip=?,perm_editar_equip=?,perm_resolver_alarme=?,is_operador=? WHERE id=?')
               ->execute([$dados['nome'],$dados['email'],$dados['perfil'],$dados['ativo'],$dados['perm_criar_equip']??0,$dados['perm_editar_equip']??0,$dados['perm_resolver_alarme']??0,$dados['is_operador']??0,$id]);
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

// ══════════════════════════════════════════════════════════
// QUERIES — EQUIPAMENTOS
// ══════════════════════════════════════════════════════════

function dbListarEquipamentos(): array {
    try {
        return getDB()->query(
            "SELECT e.*,
                (SELECT ls.temperatura   FROM leituras_sensor ls WHERE ls.equipamento_id=e.id ORDER BY ls.id DESC LIMIT 1) AS ultima_temp,
                (SELECT ls.pressao       FROM leituras_sensor ls WHERE ls.equipamento_id=e.id ORDER BY ls.id DESC LIMIT 1) AS ultima_pressao,
                (SELECT ls.umidade       FROM leituras_sensor ls WHERE ls.equipamento_id=e.id ORDER BY ls.id DESC LIMIT 1) AS ultima_umidade,
                (SELECT ls.registrado_em FROM leituras_sensor ls WHERE ls.equipamento_id=e.id ORDER BY ls.id DESC LIMIT 1) AS ultima_leitura,
                (SELECT COUNT(*) FROM alarmes a WHERE a.equipamento_id=e.id AND a.resolvido=0) AS qtd_alarmes
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

// ══════════════════════════════════════════════════════════
// QUERIES — ALARMES
// ══════════════════════════════════════════════════════════

function dbContarAlarmes(): array {
    $contagem = ['total'=>0,'critico'=>0,'alerta'=>0,'informativo'=>0,'resolvidos'=>0];
    try {
        $db = getDB();
        $linhasSeveridade = $db->query("SELECT severidade, COUNT(*) as qtd FROM alarmes WHERE resolvido=0 GROUP BY severidade")->fetchAll();
        foreach ($linhasSeveridade as $linhaSeveridade) { $contagem[$linhaSeveridade['severidade']] = (int)$linhaSeveridade['qtd']; $contagem['total'] += (int)$linhaSeveridade['qtd']; }
        $contagem['resolvidos'] = (int)$db->query("SELECT COUNT(*) FROM alarmes WHERE resolvido=1")->fetchColumn();
    } catch (Throwable $e) {}
    return $contagem;
}

// ══════════════════════════════════════════════════════════
// QUERIES — RELATÓRIOS
// ══════════════════════════════════════════════════════════

function dbRelatorioAlarmesPorEquip(): array {
    try {
        return getDB()->query(
            "SELECT e.nome, e.tag, e.status,
                    COUNT(a.id) as total_alarmes,
                    SUM(CASE WHEN a.severidade='critico' THEN 1 ELSE 0 END) as criticos,
                    SUM(CASE WHEN a.severidade='alerta'  THEN 1 ELSE 0 END) as alertas,
                    SUM(CASE WHEN a.resolvido=0          THEN 1 ELSE 0 END) as pendentes
             FROM equipamentos e
             LEFT JOIN alarmes a ON a.equipamento_id = e.id
             GROUP BY e.id, e.nome, e.tag, e.status
             ORDER BY total_alarmes DESC"
        )->fetchAll();
    } catch (Throwable $e) { return []; }
}

function dbRelatorioLeiturasPorDia(int $dias = 7): array {
    try {
        return getDB()->query(
            "SELECT DATE(registrado_em) as data,
                    COUNT(*) as total_leituras,
                    ROUND(AVG(temperatura),2) as avg_temp,
                    ROUND(AVG(pressao),2)     as avg_pres,
                    ROUND(MAX(temperatura),2) as max_temp,
                    ROUND(MAX(pressao),2)     as max_pres
             FROM leituras_sensor
             WHERE registrado_em >= NOW() - INTERVAL {$dias} DAY
             GROUP BY DATE(registrado_em)
             ORDER BY data DESC"
        )->fetchAll();
    } catch (Throwable $e) { return []; }
}

function dbRelatorioLogSistema(int $limite = 50): array {
    try {
        return getDB()->query(
            "SELECT ls.*, u.nome as usuario_nome
             FROM log_sistema ls
             LEFT JOIN usuarios u ON u.id = ls.usuario_id
             ORDER BY ls.criado_em DESC
             LIMIT {$limite}"
        )->fetchAll();
    } catch (Throwable $e) { return []; }
}

function dbRelatorioUsuariosAtivos(): array {
    try {
        return getDB()->query(
            "SELECT perfil, COUNT(*) as total,
                    SUM(CASE WHEN ativo=1 THEN 1 ELSE 0 END) as ativos
             FROM usuarios GROUP BY perfil"
        )->fetchAll();
    } catch (Throwable $e) { return []; }
}

function dbRelatorioEquipSemLeitura(): array {
    try {
        return getDB()->query(
            "SELECT e.*, MAX(ls.registrado_em) as ultima_leitura
             FROM equipamentos e
             LEFT JOIN leituras_sensor ls ON ls.equipamento_id = e.id
             GROUP BY e.id
             HAVING ultima_leitura IS NULL OR ultima_leitura < NOW() - INTERVAL 24 HOUR
             ORDER BY ultima_leitura ASC"
        )->fetchAll();
    } catch (Throwable $e) { return []; }
}
// ── Funções adicionais ────────────────────────────────────
if (!function_exists('acoesLog')) {
    function acoesLog(string $acao): string {
        $m = [
            'LOGIN'               =>'🔑','LOGOUT'              =>'🚪',
            'REGISTRAR_LEITURA'   =>'📊','CRIAR_EQUIPAMENTO'   =>'➕',
            'EDITAR_EQUIPAMENTO'  =>'✏️','EXCLUIR_EQUIPAMENTO' =>'🗑️',
            'ALTERAR_STATUS'      =>'🔄','RESOLVER_ALARME'     =>'✅',
            'CRIAR_USUARIO'       =>'👤','EDITAR_USUARIO'      =>'✏️',
            'TOGGLE_USUARIO'      =>'🔄','EXCLUIR_USUARIO'     =>'🗑️',
        ];
        return ($m[$acao]??'📝').' '.ucwords(strtolower(str_replace('_',' ',$acao)));
    }
}
if (!function_exists('perfilBadgeHtml')) {
    function perfilBadgeHtml(string $perfil): string {
        $t=['admin'=>'👑 Admin','staff'=>'🛡️ Staff','funcionario'=>'👤 Funcionário'];
        $c=['admin'=>'perfil--admin','staff'=>'perfil--staff','funcionario'=>'perfil--funcionario'];
        return '<span class="perfil-badge '.($c[$perfil]??'').'">'.(htmlspecialchars($t[$perfil]??$perfil)).'</span>';
    }
}


