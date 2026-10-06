<?

class cron_cobrancas_geracao {

	var $bancodedados;

	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}

	########################
	### Atualiza Ajustes ###
	########################

	function cobrancasGerar() {

		$this->desativaCron();

		$cobranca = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);

		while (($lotes = $this->buscaCobrancasLotes()) != false) {
			foreach ($lotes as $lote) {
			  if (!in_array($lote["formato_id"], array(2, 3))) {
				  $cobranca->geraCobrancasLote($lote["id"]);
			  }
			  $this->defineLoteGerado($lote["id"]);
			}
		}
		
	}

	function defineLoteGerado($lote_id) {
		
		$query = "UPDATE tab_cobrancas_lotes SET col_gerado = 1, col_sincronizado = 1, cla_id = 1 WHERE col_id = $lote_id";
		$this->bancodedados->query($query);
		
		// Registra Comentário
		$comentario = "<b>Ação:</b> Geração Concluída";
		$this->registraComentario($lote_id, $comentario);
		
	}

	function desativaCron() {
		
		$query = "UPDATE tab_cron SET cro_ativo = 0 WHERE cro_classe = 'cobrancas_geracao' AND cro_acao = 'cobrancasGerar'";
		$this->bancodedados->query($query);
		
	}

	########################
	### Funções de Busca ###
	########################

	function buscaCobrancasLotes() {
		
		$query = "
			SELECT
				col_id AS id,
				cof_id AS formato_id
			FROM
				tab_cobrancas_lotes
			WHERE
				col_gerado = 0
			ORDER BY
				col_vencimento,
				IF(cof_id IN (2, 3), 1, 0),
				cof_id";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lotes[] = $dados;
		}
		
		return $lotes;
		
	}

	###################
	### Comentários ###
	###################

	function registraComentario($id, $conteudo) {
		
		$query = "INSERT INTO tab_cobrancas_lotes_comentarios (clo_datahora, col_id, clo_conteudo, usu_id) VALUES (NOW(), $id, '$conteudo', NULL)";
		$this->bancodedados->query($query);
		
	}

}

?>
