<?php
// ============================================================
// INDUX — init.php  v2.0
// Hierarquia: admin > staff > funcionario
// ============================================================

session_start();

define('SISTEMA_NOME',    'INDUX');
define('SISTEMA_VERSAO',  '2.0');
define('SISTEMA_TAGLINE', 'Monitoramento Industrial Inteligente');
define('EMPRESA_ANO',     '2026');

define('TEMP_CRITICA_MAX',    85.0);
define('PRESSAO_CRITICA_MAX', 12.0);
define('TEMP_ALERTA_MAX',     70.0);
define('PRESSAO_ALERTA_MAX',   9.0);

$statusLabels = ['ativo'=>'Ativo','inativo'=>'Inativo','em_falha'=>'Em Falha'];
$tipoAlarmeLabels = ['temperatura'=>'Temperatura','pressao'=>'Pressão','falha'=>'Falha','conexao'=>'Conexão','manutencao'=>'Manutenção'];
$severidadeLabels = ['critico'=>'Crítico','alerta'=>'Alerta','informativo'=>'Informativo'];
$perfilLabels     = ['admin'=>'👑 Admin','staff'=>'🛡️ Staff','funcionario'=>'👤 Funcionário'];

// ── Auth ─────────────────────────────────────────────────
function requerLogin(): void {
    if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
        header('Location: login.php'); exit;
    }
}
function requerAdmin(): void { requerLogin(); if (!ehAdmin()) { header('Location: dashboard.php?erro=acesso_negado'); exit; } }
function requerStaff(): void { requerLogin(); if (!ehAdmin() && !ehStaff()) { header('Location: dashboard.php?erro=acesso_negado'); exit; } }

function ehAdmin(): bool      { return ($_SESSION['perfil'] ?? '') === 'admin'; }
function ehStaff(): bool      { return ($_SESSION['perfil'] ?? '') === 'staff'; }
function ehFuncionario(): bool{ return ($_SESSION['perfil'] ?? '') === 'funcionario'; }
function ehOperador(): bool   { return (bool)($_SESSION['is_operador'] ?? false); }
function ehGestor(): bool     { return ehAdmin() || ehStaff(); }

// Permissões granulares (funcionário)
function podeCriarEquip(): bool     { return ehGestor() || (bool)($_SESSION['perm_criar_equip']     ?? false); }
function podeEditarEquip(): bool    { return ehGestor() || (bool)($_SESSION['perm_editar_equip']    ?? false); }
function podeExcluirEquip(): bool   { return ehAdmin(); }
function podeResolverAlarme(): bool { return ehGestor() || (bool)($_SESSION['perm_resolver_alarme'] ?? false); }
function podeVerRelatorios(): bool  { return ehAdmin() || ehStaff(); }
function podeVerUsuarios(): bool    { return ehAdmin() || ehStaff(); }
function podeGerenciarPerfil(string $alvo): bool {
    if (ehAdmin()) return true;
    if (ehStaff() && $alvo === 'funcionario') return true;
    return false;
}

// ── Utilitários ──────────────────────────────────────────
function inicialNome(string $nome): string {
    $p = explode(' ', trim($nome));
    $i = strtoupper(substr($p[0], 0, 1));
    if (count($p) > 1) $i .= strtoupper(substr(end($p), 0, 1));
    return $i;
}
function statusClass(string $s): string { return match($s){ 'ativo'=>'status-ativo','inativo'=>'status-inativo','em_falha'=>'status-em_falha',default=>'' }; }
function statusLabel(string $s): string { global $statusLabels; return $statusLabels[$s] ?? $s; }
function severidadeIcon(string $s): string { return match($s){ 'critico'=>'🔴','alerta'=>'⚠️','informativo'=>'💡',default=>'❓' }; }
function tipoAlarmeIcon(string $t): string { return match($t){ 'temperatura'=>'🌡️','pressao'=>'⚙️','falha'=>'🔴','conexao'=>'📡','manutencao'=>'🔧',default=>'❓' }; }
function avaliarTemp(float $v, float $mn, float $mx): string { if($v>$mx||$v<$mn)return'danger'; if($v>($mx*0.85))return'warning'; return'ok'; }
function avaliarPressao(float $v, float $mn, float $mx): string { if($v>$mx||$v<$mn)return'danger'; if($v>($mx*0.85))return'warning'; return'ok'; }
function pctBar(float $v, float $mn, float $mx): float { if($mx<=$mn)return 0; return min(100,max(0,(($v-$mn)/($mx-$mn))*100)); }
function perfilBadgeHtml(string $perfil): string {
    $l=['admin'=>'👑 Admin','staff'=>'🛡️ Staff','funcionario'=>'👤 Funcionário'];
    $c=['admin'=>'perfil--admin','staff'=>'perfil--staff','funcionario'=>'perfil--funcionario'];
    return '<span class="perfil-badge '.($c[$perfil]??'').'">'.($l[$perfil]??$perfil).'</span>';
}