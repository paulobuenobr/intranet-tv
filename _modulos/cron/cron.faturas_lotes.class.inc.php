<?

class cron_faturas_lotes {

	var $bancodedados;

	function __construct($bancodedados) {

		$this->bancodedados = $bancodedados;

	}
  
	function geraLote() {
		
		//$this->geraLoteModelo21();
		$this->geraLoteNotaFatura();
		
	}
	
	function geraLoteModelo21() {
	  
		$faturas_lotes = controle::instanciaObjeto("faturas_lotes", $this->bancodedados, null, false);

		$data = date("Y-m-d", strtotime("yesterday"));
		$sequencia = $this->buscaSequenciaCiclo($data, 2);
		
		// Gera Lote
		$ciclo = date("Ymd", strtotime($data)).sprintf("%03s", $sequencia);
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes (fal_ciclo, fal_ciclo_sequencia, fal_data_ciclo, fal_data_geracao, fap_id, fls_id) VALUES ('$ciclo', $sequencia, '$data', NOW(), 2, 1)");
		$id = $this->bancodedados->ultimoIdInserido();
		
		// Status Recebida
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=',  2, 2, $id)");
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=',  3, 2, $id)");
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=',  4, 2, $id)");
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=',  5, 2, $id)");
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=',  9, 2, $id)");
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=', 13, 2, $id)");
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=', 14, 2, $id)");
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=', 15, 2, $id)");
		
		// Data Processamento
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=', '".date("d/m/Y", strtotime($data))."', 4, $id)");
		
		// = SC
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=', 1, 7, $id)");

		// Gera Faturas
		$faturas_lotes->geraFaturas($id);
		
		$faturas_lotes->buscaDadosItem($id);
		
		// Processa Lote

		if ($faturas_lotes->processarLote($faturas_lotes->dados_item)) {
			$faturas_lotes->aprovarLote($faturas_lotes->dados_item);
		}
		
	}
		
	function geraLoteNotaFatura() {
	  
		$faturas_lotes = controle::instanciaObjeto("faturas_lotes", $this->bancodedados, null, false);

		$data = date("Y-m-d", strtotime("yesterday"));
		$sequencia = $this->buscaSequenciaCiclo($data, 1);
		
		// Gera Lote
		$ciclo = date("Ymd", strtotime($data)).sprintf("%03s", $sequencia);
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes (fal_ciclo, fal_ciclo_sequencia, fal_data_ciclo, fal_data_geracao, fap_id, fls_id) VALUES ('$ciclo', $sequencia, '$data', NOW(), 1, 1)");
		$id = $this->bancodedados->ultimoIdInserido();
		
		// Status Recebida
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=',  2, 2, $id)");
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=',  3, 2, $id)");
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=',  4, 2, $id)");
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=',  5, 2, $id)");
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=',  9, 2, $id)");
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=', 13, 2, $id)");
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=', 14, 2, $id)");
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=', 15, 2, $id)");
		
		// Data Processamento
		$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('=', '".date("d/m/Y", strtotime($data))."', 4, $id)");
		
		// <> SC
		//$this->bancodedados->query("INSERT INTO tab_faturas_lotes_filtros (flf_comparacao, flf_valor, faf_id, fal_id) VALUES ('<>', 1, 7, $id)");

		// Gera Faturas
		$faturas_lotes->geraFaturas($id);
		
		$faturas_lotes->buscaDadosItem($id);
		
		// Aprova Lote
		
		$this->bancodedados->query("
			UPDATE 
				tab_faturas_lotes 
			SET 
				fal_data_aprovacao = NOW(), 
				fls_id = 3 
			WHERE 
				fal_id = {$id}");
	
		$faturas_lotes->registraComentario($id, "<b>Ação:</b> Ciclo aprovado");
		
	}

	function buscaSequenciaCiclo($data, $tipo_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				MAX(fal_ciclo_sequencia)
			FROM
				tab_faturas_lotes
			WHERE
				fap_id = $tipo_id AND
				fal_data_ciclo LIKE '$data'");
				
		return $this->bancodedados->resultado($query) + 1;
		
	}
	
}

?>
