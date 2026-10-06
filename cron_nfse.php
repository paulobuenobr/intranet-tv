<?

set_time_limit(0);
ignore_user_abort(1);

ini_alter("display_errors", "on");
ini_alter("include_path", "/usr/local/www/apache22/data/intranet");

require_once("global.inc.php");

global $bancodedados;

$cron = controle::instanciaObjeto("cron", $bancodedados);
$cron->executa(65);

?>
