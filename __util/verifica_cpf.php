<?

ini_set("memory_limit", "128M");
set_time_limit(0);

require_once("../global.inc.php");

$verifica_cpf = new verifica_cpf();
$verifica_cpf->verificar("79.916.789/0001-85");

class verifica_cpf {
	
	function verificar($cpf) {
	
		$check_check = controle::instanciaObjeto("clientes_consultas_credito.checkcheck");
		
		$check_check->setCpfCnpj($cpf);
		$check_check->setValor(100);
				
		if ($check_check->consultar()) {
			$conteudo = $check_check->getConteudo();
		}
		
		var_dump($conteudo);

	}

}

?>
