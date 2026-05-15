<?php
require_once 'init.php';
require_once 'db.php';
if (isset($_SESSION['usuario_id'])) {
    registrarLog('LOGOUT', 'usuarios', $_SESSION['usuario_id']);
}
session_unset();
session_destroy();
header('Location: login.php?msg=logout');
exit;