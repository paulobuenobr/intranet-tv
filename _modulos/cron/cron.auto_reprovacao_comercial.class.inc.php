<?

class cron_auto_reprovacao_comercial {

	var $bancodedados;

	function __construct($bancodedados) {
		$this->bancodedados = $bancodedados;
	}

	########################
	### Atualiza Ajustes ###
	########################

	function reprovaComercial() {
		// busca tipos contratos auto reprovaveis
    $tipos_contratos = $this->buscaTiposContratosAutoReprovaveis();
	  foreach ($tipos_contratos as $tipo_contrato) {
	    $contratos = $this->buscaContratosAutoReprovaveis($tipo_contrato);
  	  foreach ($contratos as $contrato) {
  	    if (!$this->buscaRetornoContratosAutoReprovaveis($contrato["cliente_id"])) {
  	     $this->registraRetornoResponsavel($contrato, $tipo_contrato["intervalo"]);
  	    }
  	  }
	  }
	}

	########################
	### Funções de Busca ###
	########################

	function buscaTiposContratosAutoReprovaveis() {
	  $tipos = array();
	  $query = "SELECT crt_id AS id, crt_parametros AS parametros FROM tab_contratos_tipos";
	  $query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	    $dados["parametros"] = @unserialize($dados["parametros"]);
	    if ($dados["parametros"]["auto_reprovacao_comercial"]) {
	  	  $tipos[] = array("id" => $dados["id"], "intervalo" => $dados["parametros"]["auto_reprovacao_comercial"]);
	    }
	  }
	  return $tipos;
	}

	function buscaRetornoContratosAutoReprovaveis($cliente_id) {
	  $query = "
      SELECT
        clr_id AS id
      FROM
        tab_clientes_retornos
      WHERE
        cli_id = $cliente_id AND
        clr_comentario LIKE '%está aguardando aprovação do cliente há mais de%' AND
        ces_id = 1";
	  $query = $this->bancodedados->query($query);
	  if ($this->bancodedados->resultadoArray($query)) {
	  	return true;
	  }
	}

	function buscaContratosAutoReprovaveis($tipo) {
	  $contratos = array();
	  $query = "
      SELECT
        tab_contratos.cor_id AS id,
        tab_contratos.cor_codigo AS codigo,
        tab_clientes.cli_id AS cliente_id,
        IF(tab_contratos.crt_id IN (13, 14, 15), MIN(comentarios_gerado.usu_id), IF(tab_clientes.usu_id IS NULL, MIN(comentarios_gerado.usu_id), tab_clientes.usu_id)) AS responsavel_id
      FROM
        tab_contratos
        INNER JOIN tab_clientes ON (tab_contratos.cli_id = tab_clientes.cli_id)
        LEFT OUTER JOIN tab_contratos_comentarios comentarios_gerado ON (comentarios_gerado.cor_id = tab_contratos.cor_id AND comentarios_gerado.ccc_id = 1)
        LEFT OUTER JOIN tab_contratos_comentarios comentarios_enviado ON (comentarios_enviado.cor_id = tab_contratos.cor_id AND comentarios_enviado.ccc_id = 5)
        LEFT OUTER JOIN tab_contratos_comentarios comentarios_reprovado ON (comentarios_reprovado.cor_id = tab_contratos.cor_id AND comentarios_reprovado.ccc_id = 3)
      WHERE
        tab_contratos.crs_id = 13 AND
        tab_contratos.crt_id = {$tipo["id"]}
      GROUP BY
        id
      HAVING
        (MAX(comentarios_reprovado.coo_datahora) IS NULL OR
         MAX(comentarios_enviado.coo_datahora) > MAX(comentarios_reprovado.coo_datahora)) AND
        (NOW() > DATE_ADD(MAX(comentarios_enviado.coo_datahora), INTERVAL {$tipo["intervalo"]} HOUR))";
	  $query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	    $contratos[] = $dados;
	  }
	  return $contratos;
	}

	#########################
	#### Registra Retorno ###
	#########################

	function registraRetornoResponsavel($contrato, $intervalo) {
	  $comentario = "Contrato {$contrato["codigo"]} está aguardando aprovação do cliente há mais de $intervalo hora(s)";
    $query = "INSERT INTO tab_clientes_retornos (cli_id, usu_id, clr_abertura, clr_data, clr_comentario, cet_id, ces_id) VALUES ({$contrato["cliente_id"]}, {$contrato["responsavel_id"]}, NOW(), CURDATE(), '$comentario', 2, 1)";
    $this->bancodedados->query($query);
	}

}

?>
