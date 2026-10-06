<?

function formataTelefone($value) {
	if (strlen($value) == 10) {
		return "(".substr($value, 0, 2).") ".substr($value, 2, 4)."-".substr($value, 6, 4);
	} elseif (strlen($value) == 11) {
		return "(".substr($value, 0, 2).") ".substr($value, 2, 5)."-".substr($value, 7, 4);
	}
	return $value;
}
	
require_once("../global.inc.php");

$chamados = controle::instanciaObjeto("chamados", $bancodedados, null, false);

$_POST["callerid"] = formataTelefone($_POST["callerid"]);

$setores = array();

$setores["999"] = 102;

$setores["Comercial"] = 103;
$setores["Financeiro"] = 104;
$setores["Suporte"] = 105;
$setores["Ouvidoria"] = 101;
$setores["Ouvidoria2"] = 101;
$setores["Agendamento"] = 109;

$dados = array();

$dados["conteudo"] = "Mensagem deixada na central telefônica para o setor {$_POST["setor"]}:[d]Número: {$_POST["callerid"]}[/d][d]<audio controls><source src=\"http://asterisk.oletelecom.com.br/monitor/{$_POST["gravacao"]}\"></audio>[/d]";
$dados["sistema"] = true;
$dados["solicitante"] = "Sistema (Central Telefônica)";
$dados["categoria"] = array_key_exists($_POST["setor"], $setores) ? $setores[$_POST["setor"]] : $setores["Suporte"];
$dados["prioridade"] = 1;
$dados["tipo"] = 5;

print $chamados->adicionarChamado($_POST["cliente_id"], null, $dados);

?>
