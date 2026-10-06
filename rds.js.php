<?

header('Content-Type: application/javascript; charset=iso-8859-1');

ini_alter("display_errors", "off");

require_once ("global.inc.php");

global $bancodedados;

$login = controle::instanciaObjeto("login", $bancodedados);

if (! $login->estaLogado()) die();

function atualizaSelect($campo, $dados = null, $ativo = null, $branco = true, $disabled = false, $atualizar = false, $focus = false) {
	
	if (! is_null($dados)) {
		$array = array ();
		foreach ($dados as $id => $nome) {
			$array[] = "new Array(\"$id\", \"$nome\")";
		}
		$ativo = is_null($ativo) ? "undefined" : $ativo;
		$branco = $branco ? "1" : "0";
		$disabled = $disabled ? "true" : "false";
		$atualizar = $atualizar ? "1" : "0";
		print "atualizaSelect(document.getElementById(\"$campo\"), new Array(" . @implode(", ", $array) . "), $ativo, $branco, $disabled, $atualizar);";
	} else {
		if (! is_null($ativo)) {
			print "document.getElementById(\"$campo\").value = \"$ativo\";";
		}
		print "document.getElementById(\"$campo\").disabled = " . ($disabled ? "true" : "false") . ";";
	}
	if ($focus) {
		print "document.getElementById(\"$campo\").focus();";
	}
	
}

function atualizaCampo($campo, $valor, $disabled = false, $atualizar = false, $focus = false) {
	
	print "document.getElementById(\"$campo\").value = \"$valor\";";
	print "document.getElementById(\"$campo\").disabled = " . ($disabled ? "true" : "false") . ";";
	if ($atualizar) {
		print "document.getElementById(\"$campo\").onchange();";
	}
	if ($focus) {
		print "document.getElementById(\"$campo\").focus();";
	}
	
}

function atualizaSpan($id, $conteudo) {
	
	print "if (document.getElementById('$id')) document.getElementById('$id').innerHTML = '$conteudo';";
	
}

if ($_GET["m"]) {
	$modulo = strpos($_GET["m"], ".") ? substr($_GET["m"], 0, strpos($_GET["m"], ".")) : $_GET["m"];
	if ($_GET["m"] == "painel") {
		$arquivo = DIR_PAINEL . "rds.painel.js.php";
	} else {
		$arquivo = DIR_MODULOS . "$modulo/rds.{$_GET["m"]}.js.php";
	}
	if (file_exists($arquivo)) {
		require_once ($arquivo);
	} else {
		print "alert('ERRO de RDS: Arquivo \"$arquivo\" não existe!');";
	}
} else {
	print "alert('ERRO de RDS: Módulo não informado!');";
}

?>
