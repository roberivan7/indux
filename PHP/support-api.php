<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

$storePath = __DIR__ . '/support_messages.json';

function supportJsonResponse(array $payload): void {
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function supportLoad(string $path): array {
    if (!file_exists($path)) {
        return ['conversations' => []];
    }

    $json = file_get_contents($path);
    $data = json_decode($json ?: '', true);
    if (!is_array($data) || !isset($data['conversations']) || !is_array($data['conversations'])) {
        return ['conversations' => []];
    }

    return $data;
}

function supportSave(string $path, array $data): bool {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents($path, $json, LOCK_EX) !== false;
}

function supportAdmin(): bool {
    return ($_SESSION['perfil'] ?? '') === 'admin' || ($_SESSION['support_admin'] ?? false) === true;
}

function supportCleanMessage(string $message): string {
    $message = trim($message);
    return mb_substr($message, 0, 1200);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$data = supportLoad($storePath);

if ($action === 'send') {
    $message = supportCleanMessage((string)($_POST['message'] ?? ''));
    if ($message === '') {
        supportJsonResponse(['ok' => false, 'error' => 'Mensagem vazia.']);
    }

    $conversationId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_POST['conversation_id'] ?? ''));
    $source = trim((string)($_POST['source'] ?? 'Sistema'));
    $name = trim((string)($_POST['name'] ?? ($_SESSION['usuario_nome'] ?? 'Visitante')));
    $email = trim((string)($_POST['email'] ?? ($_SESSION['usuario_email'] ?? '')));
    $now = date('Y-m-d H:i:s');

    $found = null;
    foreach ($data['conversations'] as $index => $conversation) {
        if (($conversation['id'] ?? '') === $conversationId) {
            $found = $index;
            break;
        }
    }

    if ($found === null) {
        $conversationId = 'sup_' . date('YmdHis') . '_' . bin2hex(random_bytes(3));
        $data['conversations'][] = [
            'id' => $conversationId,
            'name' => $name !== '' ? $name : 'Visitante',
            'email' => $email,
            'source' => $source !== '' ? $source : 'Sistema',
            'status' => 'aberto',
            'created_at' => $now,
            'updated_at' => $now,
            'messages' => [],
        ];
        $found = count($data['conversations']) - 1;
    }

    $data['conversations'][$found]['messages'][] = [
        'from' => supportAdmin() ? 'admin' : 'user',
        'message' => $message,
        'created_at' => $now,
    ];
    $data['conversations'][$found]['updated_at'] = $now;
    if (!supportAdmin()) {
        $data['conversations'][$found]['status'] = 'aberto';
    }

    if (!supportSave($storePath, $data)) {
        supportJsonResponse(['ok' => false, 'error' => 'Nao foi possivel salvar a mensagem.']);
    }

    supportJsonResponse([
        'ok' => true,
        'conversation_id' => $conversationId,
        'conversation' => $data['conversations'][$found],
    ]);
}

if ($action === 'conversation') {
    $conversationId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_GET['conversation_id'] ?? ''));
    foreach ($data['conversations'] as $conversation) {
        if (($conversation['id'] ?? '') === $conversationId) {
            supportJsonResponse(['ok' => true, 'conversation' => $conversation]);
        }
    }
    supportJsonResponse(['ok' => true, 'conversation' => null]);
}

if ($action === 'admin_login') {
    $password = (string)($_POST['password'] ?? '');
    if ($password === 'admin123' || ($_SESSION['perfil'] ?? '') === 'admin') {
        $_SESSION['support_admin'] = true;
        supportJsonResponse(['ok' => true, 'admin' => true]);
    }
    supportJsonResponse(['ok' => false, 'error' => 'Senha do suporte incorreta.']);
}

if ($action === 'admin_list') {
    if (!supportAdmin()) {
        supportJsonResponse(['ok' => false, 'error' => 'Acesso negado.']);
    }

    usort($data['conversations'], fn($a, $b) => strcmp((string)($b['updated_at'] ?? ''), (string)($a['updated_at'] ?? '')));
    supportJsonResponse(['ok' => true, 'conversations' => $data['conversations']]);
}

if ($action === 'admin_reply') {
    if (!supportAdmin()) {
        supportJsonResponse(['ok' => false, 'error' => 'Acesso negado.']);
    }

    $conversationId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_POST['conversation_id'] ?? ''));
    $message = supportCleanMessage((string)($_POST['message'] ?? ''));
    if ($conversationId === '' || $message === '') {
        supportJsonResponse(['ok' => false, 'error' => 'Selecione uma conversa e escreva uma resposta.']);
    }

    foreach ($data['conversations'] as $index => $conversation) {
        if (($conversation['id'] ?? '') === $conversationId) {
            $now = date('Y-m-d H:i:s');
            $data['conversations'][$index]['messages'][] = [
                'from' => 'admin',
                'message' => $message,
                'created_at' => $now,
            ];
            $data['conversations'][$index]['status'] = 'respondido';
            $data['conversations'][$index]['updated_at'] = $now;
            supportSave($storePath, $data);
            supportJsonResponse(['ok' => true, 'conversation' => $data['conversations'][$index]]);
        }
    }

    supportJsonResponse(['ok' => false, 'error' => 'Conversa nao encontrada.']);
}

supportJsonResponse(['ok' => false, 'error' => 'Acao invalida.']);
