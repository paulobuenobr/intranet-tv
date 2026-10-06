<?

require_once("../global.inc.php");

function validaCPF($value) {
	$value = sprintf("%011s", preg_replace("/[^0-9]/", "", $value));
	if (strlen($value) > 11 || preg_match("/0{11}|1{11}|2{11}|3{11}|4{11}|5{11}|6{11}|7{11}|8{11}|9{11}/", $value)) {
		return false;
	} else {
		$d1 = 0;
		$d2 = 0;
		for ($i = 0; $i < 9; $i++) {
			$d1 += $value[$i] * (10 - $i);
			$d2 += $value[$i] * (11 - $i);
		}
		$r1 = $d1 % 11;
		$d1 = $r1 > 1 ? 11 - $r1 : 0;
		$r2 = ($d2 + ($d1 * 2)) % 11;
		$d2 = $r2 > 1 ? 11 - $r2 : 0;
		return $value[9] == $d1 && $value[10] == $d2;
	}
}

function limpaCNPJAlfanumerico($value) {

	$value = strtoupper(preg_replace("/[^0-9A-Z]/", "", $value));
	$base = "";
	$dv = "";
	$len = strlen($value);

	for ($i = 0; $i < $len && strlen($base) < 12; $i++) {
		if (preg_match("/^[0-9A-Z]$/", $value[$i])) {
			$base .= $value[$i];
		}
	}

	for (; $i < $len && strlen($dv) < 2; $i++) {
		if (preg_match("/^[0-9]$/", $value[$i])) {
			$dv .= $value[$i];
		}
	}

	return $base . $dv;
}

function valorCNPJMod11($caractere) {
	return ord($caractere) - 48;
}

function validaCNPJ($value) {

	$value = limpaCNPJAlfanumerico($value);

	if (strlen($value) != 14 || preg_match("/^(.)\1{13}$/", $value)) {
		return false;
	}

	$mult1 = array(5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);
	$mult2 = array(6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);

	$d1 = 0;
	for ($i = 0; $i < 12; $i++) {
		$d1 += valorCNPJMod11($value[$i]) * $mult1[$i];
	}

	$r1 = $d1 % 11;
	$dv1 = $r1 > 1 ? 11 - $r1 : 0;

	if ((int) $value[12] !== $dv1) {
		return false;
	}

	$d2 = 0;
	for ($i = 0; $i < 13; $i++) {
		$d2 += valorCNPJMod11($value[$i]) * $mult2[$i];
	}

	$r2 = $d2 % 11;
	$dv2 = $r2 > 1 ? 11 - $r2 : 0;

	return (int) $value[13] === $dv2;
}

function formataCPF($value) {
	$value = sprintf("%011s", preg_replace("/[^0-9]/", "", $value));
	return substr($value, 0, 3) . "." . substr($value, 3, 3) . "." . substr($value, 6, 3) . "-" . substr($value, 9, 2);
}

function formataCNPJ($value) {

	$value = limpaCNPJAlfanumerico($value);

	if ($value == "") {
		return "";
	}

	return substr($value, 0, 2) . "." .
		substr($value, 2, 3) . "." .
		substr($value, 5, 3) . "/" .
		substr($value, 8, 4) . "-" .
		substr($value, 12, 2);
}

if (validaCPF($_POST["cpf_cnpj"])) {
	
	$_POST["cpf_cnpj"] = formataCPF($_POST["cpf_cnpj"]);
	
} elseif (validaCNPJ($_POST["cpf_cnpj"])) {
	
	$_POST["cpf_cnpj"] = formataCNPJ($_POST["cpf_cnpj"]);
	
} else {
	
	die();
	
}

print $bancodedados->resultado($bancodedados->query("SELECT cli_id FROM tab_clientes WHERE cli_cpf_cnpj = '{$_POST["cpf_cnpj"]}' ORDER BY IF(cls_id = 2, 0, cls_id) ASC LIMIT 1"));

?>
