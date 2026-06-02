<?php
require_once 'icon.php';
require_once 'init.php';
require_once 'db.php';
if (isset($_SESSION['usuario_id'])) {
    registrarLog('LOGOUT', 'usuarios', $_SESSION['usuario_id']);
}

$destino = $_GET['destino'] ?? '';
$redirect = 'login.php?msg=logout';
if ($destino === 'vendas') {
    $redirect = '../pagina_de_vendas/index.php';
}

session_unset();
session_destroy();

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

header('Location: ' . $redirect);
exit;
