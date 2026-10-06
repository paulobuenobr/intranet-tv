<?php

// http://localhost/intranet/intranet-tv/__util/registrar_cliente_credito_saldo.php
// https://oletv.oletelecom.com.br/__util/registrar_cliente_credito_saldo.php
 
require_once(dirname(__DIR__) . "/global.inc.php");

global $bancodedados;

$modulo = 'cliente_credito_saldo';

$query = $bancodedados->query("SELECT mod_id FROM tab_modulos WHERE mod_nome = '{$modulo}' LIMIT 1");
$mod_id = $bancodedados->resultado($query);

if (!$mod_id) {
    $bancodedados->query("INSERT INTO tab_modulos (mod_nome, mod_descricao, mos_id) VALUES ('{$modulo}', 'Cliente Credito Saldo', 1)");
    $mod_id = $bancodedados->ultimoIdInserido();
    echo "Modulo criado: {$mod_id}\n";
} else {
    echo "Modulo existe: {$mod_id}\n";
}

$acoes = array(
    'listar' => 'Listar',
    'ver' => 'Ver',
    'ver_clientes' => 'Ver Clientes',
    'ver_plus' => 'Ver Plus',
    'exportar' => 'Exportar'
);

foreach ($acoes as $nome => $desc) {
    $query = $bancodedados->query("SELECT aco_id FROM tab_acoes WHERE mod_id = {$mod_id} AND aco_nome = '{$nome}' LIMIT 1");
    if (!$bancodedados->resultado($query)) {
        $desc = addslashes($desc);
        $bancodedados->query("INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id) VALUES ('{$nome}', '{$desc}', 1, {$mod_id})");
        echo "Acao criada: {$nome}\n";
    } else {
        echo "Acao existe: {$nome}\n";
    }
}

?>
