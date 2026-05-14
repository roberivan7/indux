<?php
session_start();
require_once 'data.php';

// Sempre carrega do JSON — persiste mesmo após reload ou reinício do servidor
$_SESSION['produtos'] = lerProdutos();
