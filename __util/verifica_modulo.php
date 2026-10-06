<?php

// http://localhost/intranet/intranet_tv/__util/verifica_modulo.php?modulo=cidades
// http://localhost/intranet/intranet_tv/__util/verifica_modulo.php?modulo=cidades&menu=sim
// http://localhost/intranet/intranet-tv/__util/verifica_modulo.php?modulo=cidades&menu=sim&menu_nome=Cidades&menu_pai=1
// http://localhost/intranet/intranet-tv/__util/verifica_modulo.php?modulo=menus&menu=sim&menu_nome=Menus&menu_pai=5
// http://localhost/intranet/intranet-tv/__util/verifica_modulo.php?modulo=cidade_dataprev&menu=sim&menu_nome=Cidade_DataPrev&menu_pai=1&exportar_csv=sim

// https://oletv.oletelecom.com.br/__util/verifica_modulo.php?modulo=cidades
// https://oletv.oletelecom.com.br/__util/verifica_modulo.php?modulo=cidades&menu=sim
// https://oletv.oletelecom.com.br/__util/verifica_modulo.php?modulo=cidades&menu=sim&menu_nome=Cidades&menu_pai=1
// https://oletv.oletelecom.com.br/__util/verifica_modulo.php?modulo=menus&menu=sim&menu_nome=Menus&menu_pai=5
// https://oletv.oletelecom.com.br/__util/verifica_modulo.php?modulo=cidade_dataprev&menu=sim&menu_nome=Cidade_DataPrev&menu_pai=1&exportar_csv=sim

require_once("../global.inc.php");

global $bancodedados;

$modulo = empty($_GET["modulo"]) ? "crons" : trim($_GET["modulo"]);
$menu = empty($_GET["menu"]) ? "" : trim($_GET["menu"]);
$menuNome = empty($_GET["menu_nome"]) ? "" : trim($_GET["menu_nome"]);
$menuPai = empty($_GET["menu_pai"]) ? "" : trim($_GET["menu_pai"]);
$modulo = addslashes($modulo);
$menuNormalizado = strtolower($menu);
$gerarMenu = in_array($menuNormalizado, array("sim", "s", "y", "yes", "true", "1"), true);

$exportarCsv = empty($_GET["exportar_csv"]) ? "" : trim($_GET["exportar_csv"]);
$exportarCsvNormalizado = strtolower($exportarCsv);
$exportarCsv = in_array($exportarCsvNormalizado, array("sim", "s", "y", "yes", "true", "1"), true);

function inserirMenu($bancodedados, $menuNome, $menuPai, $modulo, $exportarCsv) {
    $menuNomeInserir = $menuNome !== "" ? $menuNome : $modulo;
    $menuNomeInserir = str_replace("_", " ", $menuNomeInserir);
    $menuNomeInserir = addslashes($menuNomeInserir);
    $menuPaiInserir = is_numeric($menuPai) ? intval($menuPai) : "NULL";

    $query = $bancodedados->query("
        SELECT
            men_id
        FROM
            tab_menus
        WHERE
            men_nome = '{$menuNomeInserir}' AND " . ($menuPaiInserir === "NULL" ? "men_pai IS NULL" : "men_pai = {$menuPaiInserir}") . "
        LIMIT 1");

    if ($bancodedados->resultado($query)) {
        return false;
    }

    $query = $bancodedados->query("
        SELECT
            IFNULL(MAX(men_ordem), 0) + 1
        FROM
            tab_menus
        WHERE
            " . ($menuPaiInserir === "NULL" ? "men_pai IS NULL" : "men_pai = {$menuPaiInserir}"));
    $menuOrdem = intval($bancodedados->resultado($query));

    $bancodedados->query("
        INSERT INTO tab_menus (men_nome, men_modulo, men_acao, men_pai, men_ordem)
        VALUES ('{$menuNomeInserir}', NULL, NULL, {$menuPaiInserir}, {$menuOrdem})");
    $menuPaiFilho = intval($bancodedados->ultimoIdInserido());

    if ($exportarCsv) {
        $bancodedados->query("
            INSERT INTO tab_menus (men_nome, men_modulo, men_acao, men_pai, men_ordem)
            VALUES ('Gerar', '{$modulo}', 'gerar', {$menuPaiFilho}, 1)");
    } else {
        $bancodedados->query("
            INSERT INTO tab_menus (men_nome, men_modulo, men_acao, men_pai, men_ordem)
            VALUES ('Listar', '{$modulo}', 'listar', {$menuPaiFilho}, 1)");
    }

    return true;
}

$query = $bancodedados->query("SELECT mod_id, mod_nome, mod_descricao, mos_id FROM tab_modulos WHERE mod_nome = '{$modulo}' LIMIT 1");
$dados = $bancodedados->resultadoArray($query);

header('Content-Type: application/json; charset=utf-8');

if ($dados) {

    if ($gerarMenu) {
        $menuInserido = inserirMenu($bancodedados, $menuNome, $menuPai, $modulo, $exportarCsv);   
        if (!$menuInserido) {
            $dados["mensagem"] = "Menu já existe.";
        }
    }
    
    echo json_encode(array(
        "existe" => true,
        "mensagem" => "Módulo encontrado.",
        "dados" => $dados
    ));

} else {

    $descricao = ucwords(str_replace("_", " ", $modulo));
    $descricao = addslashes($descricao);

    $bancodedados->query("INSERT INTO tab_modulos (mod_nome, mod_descricao, mos_id) VALUES ('{$modulo}', '{$descricao}', 1)");
    if ($exportarCsv) {
        $bancodedados->query("INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT 'gerar', 'Gerar', 1, mod_id FROM tab_modulos WHERE mod_nome LIKE '{$modulo}' LIMIT 1");
    } else {
        $bancodedados->query("INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT 'listar', 'Listar', 1, mod_id FROM tab_modulos WHERE mod_nome LIKE '{$modulo}' LIMIT 1");
        $bancodedados->query("INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT 'adicionar', 'Adicionar', 1, mod_id FROM tab_modulos WHERE mod_nome LIKE '{$modulo}' LIMIT 1");
        $bancodedados->query("INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT 'editar', 'Editar', 1, mod_id FROM tab_modulos WHERE mod_nome LIKE '{$modulo}' LIMIT 1");
        $bancodedados->query("INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT 'ver', 'Ver', 1, mod_id FROM tab_modulos WHERE mod_nome LIKE '{$modulo}' LIMIT 1");
        $bancodedados->query("INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT 'remover', 'Remover', 1, mod_id FROM tab_modulos WHERE mod_nome LIKE '{$modulo}' LIMIT 1");
    }

    if ($gerarMenu) {
        $menuInserido = inserirMenu($bancodedados, $menuNome, $menuPai, $modulo, $exportarCsv);
        if (!$menuInserido) {
            $dados["mensagem"] = "Menu já existe.";
        }
    }

    echo json_encode(array(
        "existe" => true,
        "mensagem" => "Módulo não encontrado e inserido com sucesso.",
        "dados" => $dados
    ));
}
