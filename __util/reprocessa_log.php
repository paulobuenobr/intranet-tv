<?php

require_once ("../global.inc.php");

$reprocessa_log = new reprocessa_log($_GET['limite']);

$reprocessa_log->reprocessar();

class reprocessa_log {
	
	private $bancodedados;
	private $limite;

	function reprocessa_log($limite) {
		
		$this->bancodedados = controle::instanciaObjeto("bancodedados", true, "10.0.100.250", "tvintranetuser", "WSinTr@net", "tvintranet");
		$this->limite = intval($limite);
		
		if ($this->limite <= 0) {
			$this->limite = 10;
		}

	}

	function reprocessar() {

		$query = $this->bancodedados->query("
				SELECT
					id,
					requestGet,
					requestPost
				FROM
					tab_clientes_origens_historicoapi_2 
				WHERE
					processado = 0 AND
					requestGet != '[\"clientes\",\"inserir\"]' AND
					requestGet != '[\"contratos\",\"inserir\"]'
				ORDER BY
					id ASC
				LIMIT ".$this->limite);

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$requestGet = json_decode($dados['requestGet'], true);

			$action = implode('/', $requestGet);

			$resultado = $this->chamadaAPI($action, $dados['requestPost']);

			print "ID: ".$dados['id']." - Ação: ".$action." - Resultado: ".($resultado ? 'SUCESSO' : 'FALHA')."<br>";

			$this->bancodedados->query("UPDATE tab_clientes_origens_historicoapi_2 SET processado = 1 WHERE id = ".$dados['id']);

		}

	}


	function chamadaAPI($action, $parameters = '' ) {

		$host = 'https://oletv.oletelecom.com.br/api';
		
		$ch = curl_init( $host.'/'.$action );	
		
		curl_setopt($ch, CURLOPT_ENCODING, "UTF-8" );
		curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		
		$result = curl_exec($ch);
		
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		
		curl_close($ch);
		
		if ($curl_errno > 0) {
			$objectResult['retorno_status'] = false;
			$objectResult['error'] = 'CURL Error ('.$curl_errno.'): '.$curl_error;
		}
		
		$objectResult = json_decode($result,true);

		if (!isset($objectResult['retorno_status'])) {
			$objectResult['retorno_status'] = false;
			$objectResult['error'] = 'Houve uma falha da comunicação com o servidor!';
		}
		
		return $objectResult['retorno_status'];

	}
	
}

?>
