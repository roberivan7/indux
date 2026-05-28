<?php
require_once 'init.php';

// ============================================================
// INDUX — API simples de solicitações de suporte
// O usuário escolhe o tipo de suporte, envia a mensagem e
// recebe uma confirmação automática na tela.
// As solicitações ficam salvas no arquivo suporte_mensagens.json
// ============================================================

header('Content-Type: application/json; charset=utf-8');

define('ARQUIVO_SUPORTE', __DIR__ . '/suporte_mensagens.json');

define('TIPOS_SUPORTE_PERMITIDOS', [
    'Erros diversos',
    'Bugs',
    'Suporte geral',
    'Outros'
]);

function responderJson(array $resposta, int $codigoHttp = 200): void
{
    http_response_code($codigoHttp);
    echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (isset($_SESSION['logado']) && $_SESSION['logado'] === true && !podeAcessarSuporte()) {
    responderJson(['ok' => false, 'erro' => 'Acesso negado ao suporte para usuários do perfil Funcionário.'], 403);
}

function pegarDadosEnviados(): array
{
    $jsonRecebido = json_decode(file_get_contents('php://input') ?: '', true);

    if (!is_array($jsonRecebido)) {
        $jsonRecebido = [];
    }

    return array_merge($_POST, $jsonRecebido);
}

function limparTexto(string $texto, int $limite = 1200): string
{
    $texto = trim(strip_tags($texto));

    if (function_exists('mb_substr')) {
        return mb_substr($texto, 0, $limite, 'UTF-8');
    }

    return substr($texto, 0, $limite);
}

function dataAtualSuporte(): string
{
    return date('Y-m-d H:i:s');
}

function criarIdSuporte(string $prefixo): string
{
    try {
        return $prefixo . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
    } catch (Throwable $erro) {
        return $prefixo . '_' . date('YmdHis') . '_' . uniqid();
    }
}

function lerArquivoSuporte(): array
{
    if (!file_exists(ARQUIVO_SUPORTE)) {
        file_put_contents(
            ARQUIVO_SUPORTE,
            json_encode(['solicitacoes' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    $conteudoArquivo = file_get_contents(ARQUIVO_SUPORTE);
    $dados = json_decode($conteudoArquivo ?: '', true);

    if (!is_array($dados) || !isset($dados['solicitacoes']) || !is_array($dados['solicitacoes'])) {
        $dados = ['solicitacoes' => []];
    }

    return $dados;
}

function salvarArquivoSuporte(array $dados): void
{
    file_put_contents(
        ARQUIVO_SUPORTE,
        json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    );
}

$dadosEnviados = pegarDadosEnviados();
$acao = strtolower(trim((string)($_GET['acao'] ?? $dadosEnviados['acao'] ?? '')));

try {
    switch ($acao) {
        case 'enviar_solicitacao': {
            $nomeUsuario = limparTexto((string)($dadosEnviados['nome_usuario'] ?? 'Visitante'), 100) ?: 'Visitante';
            $emailUsuario = limparTexto((string)($dadosEnviados['email_usuario'] ?? ''), 150);
            $tipoSuporte = limparTexto((string)($dadosEnviados['tipo_suporte'] ?? ''), 80);
            $mensagemUsuario = limparTexto((string)($dadosEnviados['mensagem'] ?? ''), 1400);

            if ($emailUsuario === '' || !filter_var($emailUsuario, FILTER_VALIDATE_EMAIL)) {
                responderJson(['ok' => false, 'erro' => 'Informe um e-mail válido para receber a resposta.'], 422);
            }

            if (!in_array($tipoSuporte, TIPOS_SUPORTE_PERMITIDOS, true)) {
                responderJson(['ok' => false, 'erro' => 'Escolha o tipo de suporte.'], 422);
            }

            if ($mensagemUsuario === '') {
                responderJson(['ok' => false, 'erro' => 'Digite uma mensagem antes de enviar.'], 422);
            }

            $dados = lerArquivoSuporte();

            $novaSolicitacao = [
                'id_solicitacao' => criarIdSuporte('solicitacao'),
                'nome_usuario' => $nomeUsuario,
                'email_usuario' => $emailUsuario,
                'tipo_suporte' => $tipoSuporte,
                'mensagem' => $mensagemUsuario,
                'status' => 'aguardando_resposta_email',
                'criada_em' => dataAtualSuporte(),
            ];

            $dados['solicitacoes'][] = $novaSolicitacao;
            salvarArquivoSuporte($dados);

            responderJson([
                'ok' => true,
                'mensagem_automatica' => 'Obrigado! mensagem enviada, aguarde resposta pelo Email.',
                'solicitacao' => $novaSolicitacao,
            ]);
        }

        default:
            responderJson(['ok' => false, 'erro' => 'Ação inválida no suporte.'], 400);
    }
} catch (Throwable $erro) {
    responderJson(['ok' => false, 'erro' => 'Erro interno no suporte: ' . $erro->getMessage()], 500);
}
