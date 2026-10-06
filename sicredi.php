<?php

set_time_limit(0);
ignore_user_abort(1);
ini_alter("display_errors", "on");
ini_alter("include_path", "/usr/local/www/apache22/data/intranet");

$content = file_get_contents('php://input');
$json = json_decode($content, true);

require_once("global.inc.php");

global $bancodedados;
$sicredi = controle::instanciaObjeto("sicredi", $bancodedados);

if ($_GET["i"] == "sicredi_teste") {

    $ret = $sicredi->sicredi($json, true);
    echo $ret;
}
if ($_GET["i"] == "sicredi") {

    $ret = $sicredi->sicredi($json);
    echo $ret;
}
?>

