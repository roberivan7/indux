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
function ehGestor(): bool     { return ehAdmin() || ehStaff(); }
function ehOperador(): bool   { return ehGestor() || (bool)($_SESSION['is_operador'] ?? false); }

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
    $partesNome = explode(' ', trim($nome));
    $iniciais = strtoupper(substr($partesNome[0], 0, 1));
    if (count($partesNome) > 1) $iniciais .= strtoupper(substr(end($partesNome), 0, 1));
    return $iniciais;
}
function statusClass(string $status): string { return match($status){ 'ativo'=>'status-ativo','inativo'=>'status-inativo','em_falha'=>'status-em_falha',default=>'' }; }
function statusLabel(string $status): string { global $statusLabels; return $statusLabels[$status] ?? $status; }
function severidadeIcon(string $severidade): string {
    $gifCritico = 'https://media0.giphy.com/media/v1.Y2lkPTc5MGI3NjExdXRudXl3eWR3bXNlaWticm0xY3Z5amVwa204Mm5xNW9rZHJwaXc4aCZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9cw/f4z6vCWrGcMw6xRlOA/giphy.gif';
    return match($severidade){
        'critico'=>'<img src="'.$gifCritico.'" alt="Alerta" style="width:22px;height:22px;object-fit:contain;display:inline-block;vertical-align:middle" />',
        'alerta'=>'⚠️',
        'informativo'=>'💡',
        default=>'❓'
    };
}
function tipoAlarmeIcon(string $tipoAlarme): string { return match($tipoAlarme){ 'temperatura'=>'🌡️','pressao'=>'⚙️','falha'=>'🔴','conexao'=>'📡','manutencao'=>'🔧',default=>'❓' }; }
function avaliarTemp(float $valor, float $minimo, float $maximo): string { if($valor>$maximo||$valor<$minimo)return'danger'; if($valor>($maximo*0.85))return'warning'; return'ok'; }
function avaliarPressao(float $valor, float $minimo, float $maximo): string { if($valor>$maximo||$valor<$minimo)return'danger'; if($valor>($maximo*0.85))return'warning'; return'ok'; }
function pctBar(float $valor, float $minimo, float $maximo): float { if($maximo<=$minimo)return 0; return min(100,max(0,(($valor-$minimo)/($maximo-$minimo))*100)); }
function perfilBadgeHtml(string $perfil): string {
    $perfilTexto=['admin'=>'👑 Admin','staff'=>'🛡️ Staff','funcionario'=>'👤 Funcionário'];
    $perfilClasse=['admin'=>'perfil--admin','staff'=>'perfil--staff','funcionario'=>'perfil--funcionario'];
    return '<span class="perfil-badge '.($perfilClasse[$perfil]??'').'">'.($perfilTexto[$perfil]??$perfil).'</span>';
}
