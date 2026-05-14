<?php
$nomeLoja = "ConstruTech";

$categorias = [
    'bruto'       => 'Bruto',
    'ferramentas' => 'Ferramentas',
    'acabamento'  => 'Acabamento',
];

define('ESTOQUE_MINIMO', 5);
define('JSON_PATH', __DIR__ . '/produtos.json');

// Lê os produtos do arquivo JSON
function lerProdutos(): array {
    if (!file_exists(JSON_PATH)) {
        return [];
    }
    $json = file_get_contents(JSON_PATH);
    $dados = json_decode($json, true);
    return is_array($dados) ? $dados : [];
}

// Salva o array de produtos no JSON
function salvarProdutos(array $produtos): void {
    file_put_contents(
        JSON_PATH,
        json_encode(array_values($produtos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}
