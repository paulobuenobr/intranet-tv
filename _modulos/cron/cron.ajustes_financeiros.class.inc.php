<?

class cron_ajustes_financeiros {

	var $bancodedados;

	function __construct($bancodedados) {
		$this->bancodedados = $bancodedados;
	}

	########################
	### Atualiza Ajustes ###
	########################

	function atualizaAjustesFinanceiros() {

		// busca ajustes ativos que estão expirados
		$ajustes_expirados = $this->buscaAjustesAtivosExpirados();

		for ($i = 0; $i < sizeof($ajustes_expirados); $i++) {
		  $this->atualizaStatus($ajustes_expirados[$i]["id"]);
		  $this->registraComentario($ajustes_expirados[$i]["id"]);
		}

	}

	########################
	### Funções de Busca ###
	########################

	function buscaAjustesAtivosExpirados() {
		$query = "
			SELECT
			  tab_ajustes_financeiros.ajf_id AS id
			FROM
			  tab_ajustes_financeiros
			WHERE
			 	tab_ajustes_financeiros.afs_id = '5' AND
				DATE_FORMAT(tab_ajustes_financeiros.ajf_competencia_termino, '%Y%m') < DATE_FORMAT(NOW(), '%Y%m')
			ORDER BY
				id";
	  $query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	  	$ajustes[] = $dados;
	  }
	  return $ajustes;
	}

	########################
	#### Atualiza Status ###
	########################

	function atualizaStatus($id) {
	  $query = "UPDATE tab_ajustes_financeiros SET afs_id = '7' WHERE ajf_id = '$id'";
	  $query = $this->bancodedados->query($query);
	}

	########################
	##### Comentários ######
	########################

	function registraComentario($id) {
    $query = "INSERT INTO tab_ajustes_financeiros_comentarios (afm_datahora, ajf_id, afm_conteudo) VALUES (NOW(), '$id', '<b>Ação:</b> Expirado')";
    $this->bancodedados->query($query);
	}

}

?>
