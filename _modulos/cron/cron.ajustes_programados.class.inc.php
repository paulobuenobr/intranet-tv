<?

class cron_ajustes_programados {

	var $bancodedados;
	var $servicos_ajustes;

	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		$this->servicos_ajustes = controle::instanciaObjeto("servicos_ajustes", $bancodedados, null, false);
	
	}

	########################
	### Atualiza Ajustes ###
	########################

	function atualizaAjustes() {
		
		// Busca e finaliza ajustes expirados
		$ajustes = $this->buscaAjustesExpirados();
		foreach ($ajustes as $ajuste) {
			$this->servicos_ajustes->expirarAjuste($ajuste);
		}
		
		// Busca e aplica ajustes ativos
		$ajustes = $this->buscaAjustesAtivos();
		foreach ($ajustes as $ajuste) {
			$this->servicos_ajustes->aplicarAjuste($ajuste["ajuste_id"]);
		}
	
	}

	########################
	### Funções de Busca ###
	########################

	function buscaAjustesExpirados() {
		
		$ajustes = array();
		
		$query = "
			SELECT
				tab_servicos_ajustes.saj_id AS id,
				tab_servicos_ajustes_categorias.sac_nome AS categoria,
				tab_ajuste_pai.saj_id AS ajuste_pai
			FROM
				tab_servicos_ajustes
				INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes_categorias.sac_id = tab_servicos_ajustes.sac_id)
				LEFT OUTER JOIN tab_servicos_ajustes tab_ajuste_pai ON (tab_ajuste_pai.saj_id = tab_servicos_ajustes.saj_pai) AND (tab_ajuste_pai.sas_id = '5')
			WHERE
				tab_servicos_ajustes.sas_id IN (5, 9) AND
				tab_servicos_ajustes.saj_termino <= NOW()
			ORDER BY
				tab_servicos_ajustes.saj_pai DESC";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$ajustes[] = $dados;
		}
		
		return $ajustes;
	
	}

	function buscaAjustesAtivos() {
				
		$ajustes = array();

		$query = "
			SELECT
				saj_id AS ajuste_id,
				sac_id AS categoria_id,
				cli_id AS cliente_id
			FROM
				tab_servicos_ajustes
			WHERE
				sas_id IN (5, 9) AND
				saj_inicio <= NOW() AND
				saj_pai IS NULL
			ORDER BY
				ajuste_id DESC";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$ajustes[] = $dados;
			//if (!$ajustes[$dados["cliente_id"]] || $ajustes[$dados["cliente_id"]]["categoria_id"] != 1) {
			//	$ajustes[$dados["cliente_id"]] = $dados;
			//}
		}
		
		return $ajustes;
	
	}

}

?>
