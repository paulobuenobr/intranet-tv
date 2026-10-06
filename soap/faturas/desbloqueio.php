<?
require_once("../../global.inc.php");

$server = new SoapServer(null, array('uri' => "desbloqueio"));
$server->setClass("callback", $bancodedados);
$server->handle();

class callback
{
	var $bancodedados;

	function __construct($bancodedados)
	{
		$this->bancodedados = $bancodedados;
	}
	
	function getDesbloquear($cpf_cnpj, $datanasc, $cliente_id)
	{
		require_once("config.php");

		$response = array();
		$response['nomecli'] 		= "";
		$response['success'] 		= 1;
		$response['message'] 		= TEXTERRORNOERROR;

		$cpf_cnpj = preg_replace("/[^0-9\\.\\-\\/]/", "", $cpf_cnpj);

		if (strlen($cpf_cnpj) == 0) {
			return $response;
		}

		$jaFoiDesbloqueado = $this->jaFoiDesbloqueado($cliente_id);
		if($jaFoiDesbloqueado) {

			$response['txt_jaFoiDesbloqueado'] = utf8_encode("<b style='font-size: 16px;line-height: 3;'>Você já realizou um desbloqueio em seu cadastro. Não é possivel realizar outro neste mês.</b>");
			$response['jaFoiDesbloqueado'] = true;
			$response['desbloqueado'] = false;

		} else {
			
			$this->removerBloqueio($cliente_id);
			$response['txt_jaFoiDesbloqueado'] = "";
			$response['txt_jaFoiDesbloqueado'] = utf8_encode("<b style='font-size: 16px;line-height: 1.4;padding: 10px 0;display: block;'>Você realizou o desbloqueio.</b>");
			$response['jaFoiDesbloqueado'] = false;
			$response['desbloqueado'] = true;

		}

		$response['cliente'] = $cliente_id;
		$response['datanasc'] = $datanasc;
		
		return $response;
	}


	function removerBloqueio($cliente)
	{

		$servicos_ajustes = controle::instanciaObjeto("servicos_ajustes", $this->bancodedados, null, false);
		$sql = "SELECT * FROM tab_servicos_ajustes WHERE cli_id = {$cliente} AND sas_id = 5";
		$query = $this->bancodedados->query($sql);
		
		$dados = $this->bancodedados->resultadoArrayTotal($query);
		for ($i = 0; $i < count($dados); $i++) {
			$servicos_ajustes->removeAjuste($dados[$i]["saj_id"]);
		}
	}

	function jaFoiDesbloqueado($cliente) {

		$query = "SELECT COUNT(*) desbloqueio FROM tab_servicos_ajustes WHERE cli_id = {$cliente} AND sas_id != 5 AND YEAR(saj_termino) = YEAR(CURDATE()) AND MONTH(saj_termino) = MONTH(CURDATE())";
		$result = $this->bancodedados->query($query);
		$retorno = $this->bancodedados->resultado($result);
		
		if($retorno > 0) {
			return true;
		}
		
		return false;
	}
}
