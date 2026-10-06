<?php

set_time_limit(0);
ignore_user_abort(1);
ini_alter("display_errors", "on");
ini_alter("include_path", "/usr/local/www/apache22/data/intranet");

$content = file_get_contents('php://input');
$json = json_decode($content, true);

require_once("global.inc.php");

global $bancodedados;
$youcast = controle::instanciaObjeto("sva_youcast", $bancodedados);

if ($_GET["action"] == "login") {

    $json = [
        "usuario" => $_GET["usuario"],
        "senha" => $_GET["senha"],
    ];
    $ret = $youcast->login($json);
    
    if (is_string($ret)) {
        $ret = json_decode($ret, true);
    }
    echo json_encode($ret, JSON_UNESCAPED_UNICODE);
}

if ($_GET["action"] == "autorizacao") {

    $json = [
        "cliente" => $_GET["cliente"],
        "produto" => $_GET["produto"],
    ];
    
    $ret = $youcast->autorizacao($json);
    
    if (is_string($ret)) {
        $ret = json_decode($ret, true);
    }
    
    $retorno = [
        "access" => $ret["access"],
        "rating" => $ret["rating"],
        "ttl" => $ret["ttl"],
        "error" => $ret["error"],
    ];
    
    $json = json_encode($retorno);

    echo $json;
}
?>

