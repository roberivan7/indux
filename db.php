<?php
// ============================================================
// INDUX — db.php
// Conexão com banco de dados MySQL (PDO)
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'db_teste');
define('DB_USER', 'dev');           // altere conforme ambiente
define('DB_PASS', '123');               // altere conforme ambiente
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
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
                Verifique as configurações em <code>php/db.php</code>:<br>
                HOST: ' . DB_HOST . '<br>
                BANCO: ' . DB_NAME . '<br>
                USUÁRIO: ' . DB_USER . '<br><br>
                Detalhes: ' . htmlspecialchars($e->getMessage()) . '
            </div>');
        }
    }
    return $pdo;
}

// ── Log de sistema ────────────────────────────────────────
function registrarLog(string $acao, ?string $tabela = null, ?int $registroId = null, ?string $detalhes = null): void {
    try {
        $db = getDB();
        $stmt = $db->prepare(
            'INSERT INTO log_sistema (usuario_id, acao, tabela_afetada, registro_id, detalhes, ip)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $_SESSION['usuario_id'] ?? null,
            $acao,
            $tabela,
            $registroId,
            $detalhes,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $e) {
        // silencia erros de log para não quebrar o fluxo principal
    }
}
