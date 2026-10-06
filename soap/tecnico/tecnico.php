<?

require_once ("../../global.inc.php");

$server = new SoapServer(null, array('uri' => "tecnico"));
$server->setClass("tecnico", $bancodedados);
$server->handle();

class tecnico {

	private $bancodedados;

	function __construct($bancodedados) {

		$this->bancodedados = $bancodedados;

	}

	function login($usuario, $senha) {

		require_once ("config.php");

		$usuario = addslashes(trim($usuario));
		$senha = addslashes(trim($senha));

		$query = "
			SELECT
				COUNT(*)
			FROM
				tab_usuarios
			WHERE
				usu_login = '{$usuario}' AND
				usu_senha = '{$senha}' AND
				uss_id = 1";

		$query = $this->bancodedados->query($query);

		return $this->bancodedados->resultado($query) > 0;

	}

	function getContrato($hash) {

		$hash = addslashes(trim($hash));

		$query = "
			SELECT
				tab_contratos.cor_codigo AS codigo,
				tab_contratos.pls_id AS servico_id,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente,
				tab_clientes_autenticacoes.cau_usuario AS usuario,
				tab_clientes_autenticacoes.cau_senha AS senha,
				tab_servidores.ser_nome AS concentrador,
				tab_servidores_ssids.ssi_nome AS ssid
			FROM
				tab_contratos
				INNER JOIN tab_clientes ON (tab_contratos.cli_id = tab_clientes.cli_id)
				INNER JOIN rel_ass_cor_estacontido ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)
				LEFT OUTER JOIN tab_clientes_autenticacoes ON (rel_ass_cor_estacontido.ass_id = tab_clientes_autenticacoes.ass_id)
				LEFT OUTER JOIN tab_servidores ON (tab_servidores.ser_id = tab_clientes_autenticacoes.ser_id)
				LEFT OUTER JOIN tab_servidores_ssids ON (tab_servidores_ssids.ssi_id = tab_clientes_autenticacoes.ssi_id)
			WHERE
				tab_contratos.cor_hash = '{$hash}' AND
				tab_contratos.crs_id = 17";

		$query = $this->bancodedados->query($query);

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
			return $dados;			
		}

		return null;

	}
	
	function getAdicionar($hash) {
		
		$dados_contrato = getContrato($hash);
		
		if ($dados_contrato) {
			
			$dados_adicionar = array("servico_id" => $dados_contrato["servico_id"]);
			
			if ($dados_contrato["servico_id"] == 1) {
				
			} elseif ($dados_contrato["servico_id"] == 28) {
				
			} elseif ($dados_contrato["servico_id"] == 38) {
			}
			
			return $dados_adicionar;
			
		}
		
		return null;

	}

}

?>
