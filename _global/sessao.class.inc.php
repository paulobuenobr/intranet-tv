<?

class sessao {

	var $bancodedados;
	var $tempo_expiracao = 3600;

	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
		if ($_GET && array_key_exists("m", $_GET)) {
			if (array_key_exists("a", $_GET)) {
				if ($_GET["m"] == "servidores_servicos" && in_array($_GET["a"], array("exportar"))) session_cache_limiter('no_cache');
				if ($_GET["m"] == "contratos" && in_array($_GET["a"], array("imprimir", "visualizar", "visualizar_contrato", "visualizar_vistoria"))) session_cache_limiter('no_cache');
				if ($_GET["m"] == "cobrancas_lotes" && in_array($_GET["a"], array("exportar"))) session_cache_limiter('no_cache');
				if ($_GET["m"] == "boletos" && in_array($_GET["a"], array("gerar"))) session_cache_limiter('no_cache');
				if ($_GET["m"] == "cobrancas" && in_array($_GET["a"], array("gerar_recibo", "exportar"))) session_cache_limiter('no_cache');
				if ($_GET["m"] == "tarefas" && in_array($_GET["a"], array("abrir_anexo"))) session_cache_limiter('no_cache');
				if ($_GET["m"] == "documentos" && in_array($_GET["a"], array("pegar_arquivo"))) session_cache_limiter('no_cache');
			} elseif ($_GET["m"] == "relatorios") {
				if (($_POST && array_key_exists("exportar", $_POST)) || (array_key_exists("i", $_GET))) session_cache_limiter('no_cache');
			}
		}
		
		session_start();
		
		$_SESSION["id"] = session_id();
		
		if (array_key_exists("usuario", $_SESSION)) {
			if ($this->existeSessao()) {
				if (strtotime($_SESSION["datahora_termino"]) > strtotime(date("Y-m-d H:i:s"))) {
					$this->atualizaSessao();
				} else {
					$this->removeSessao();
				}
			} else {
				$this->registraSessao();
			}
		}
	
	}

	function existeSessao() {
		
		$query = "SELECT ses_id FROM tab_sessoes WHERE ses_id = '{$_SESSION["id"]}' AND ses_datahora_termino > NOW()";
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->linhas($query) > 0;
		
	}

	function registraSessao() {
			
		$_SESSION["datahora_inicio"] = date("Y-m-d H:i:s");
		$_SESSION["datahora_termino"] = date("Y-m-d H:i:s", strtotime("+{$this->tempo_expiracao} seconds"));
		$_SESSION["url"] = strlen($_SERVER["REQUEST_URI"]) > 250 ? "" : $_SERVER["REQUEST_URI"];
		$_SESSION["ip"] = $_SERVER["REMOTE_ADDR"];
		
		$query = "DELETE FROM tab_sessoes WHERE ses_id = '{$_SESSION["id"]}'";
		$this->bancodedados->query($query);
		
		$query = "INSERT INTO tab_sessoes (ses_id, ses_datahora_inicio, ses_datahora_termino, ses_url, ses_ip, usu_id) VALUES ('{$_SESSION["id"]}', '{$_SESSION["datahora_inicio"]}', '{$_SESSION["datahora_termino"]}', '".addslashes($_SESSION["url"])."', '{$_SESSION["ip"]}', {$_SESSION["usuario"]})";
		$this->bancodedados->query($query);
	
	}

	function atualizaSessao() {
		
		$_SESSION["datahora_termino"] = date("Y-m-d H:i:s", strtotime("+{$this->tempo_expiracao} seconds"));
		$_SESSION["url"] = strlen($_SERVER["REQUEST_URI"]) > 250 ? "" : $_SERVER["REQUEST_URI"];
		
		$query = "UPDATE tab_sessoes SET ses_datahora_termino = '{$_SESSION["datahora_termino"]}', ses_url = '".addslashes($_SESSION["url"])."' WHERE ses_id = '{$_SESSION["id"]}'";
		$this->bancodedados->query($query);
	
	}

	function removeSessao() {
		
		$query = "DELETE FROM tab_sessoes WHERE ses_id = '{$_SESSION["id"]}'";
		$this->bancodedados->query($query);
		
		header("Location: ./logout.php");
	
	}

}

?>
