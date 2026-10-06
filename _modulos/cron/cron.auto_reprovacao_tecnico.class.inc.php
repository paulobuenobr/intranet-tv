<?

class cron_auto_reprovacao_tecnico {

	var $bancodedados;

	function __construct($bancodedados) {
		$this->bancodedados = $bancodedados;
	}

	########################
	### Atualiza Ajustes ###
	########################

	function reprovaTecnico() {
		// busca tipos contratos auto reprovaveis
    $tipos_contratos = $this->buscaTiposContratosAutoReprovaveis();
	  foreach ($tipos_contratos as $tipo_contrato) {
	    $contratos = $this->buscaContratosAutoReprovaveis($tipo_contrato["id"]);
  	  foreach ($contratos as $contrato) {
  	    if (!$this->buscaRetornoContratosAutoReprovaveis($contrato["cliente_id"])) {
    	    $this->registraRetornoDiretorTecnico($contrato, $tipo_contrato["intervalo"]);
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
	    if ($dados["auto_reprovacao_tecnico"]) {
	  	  $tipos[] = array("id" => $dados["id"], "intervalo" => $dados["auto_reprovacao_tecnico"]);
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
        clr_comentario LIKE '%está aguardando aprovação técnica há mais de%' AND
        ces_id = 1";
	  $query = $this->bancodedados->query($query);
	  if ($this->bancodedados->resultadoArray($query)) {
	  	return true;
	  }
	}

	function buscaContratosAutoReprovaveis($tipo) {
	  $query = "
      SELECT
        tab_contratos.cor_id AS id,
        tab_contratos.cor_codigo AS codigo,
        tab_clientes.cli_id AS cliente_id
      FROM
        tab_contratos
        INNER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id)
        INNER JOIN tab_clientes ON (tab_visitas.cli_id = tab_clientes.cli_id)
        LEFT OUTER JOIN tab_contratos_comentarios comentarios_aprovacao ON (comentarios_aprovacao.cor_id = tab_contratos.cor_id AND comentarios_aprovacao.ccc_id IN (2, 4, 14))
        LEFT OUTER JOIN tab_contratos_comentarios comentarios_reprovacao ON (comentarios_reprovacao.cor_id = tab_contratos.cor_id AND comentarios_reprovacao.ccc_id IN (7))
      WHERE
        tab_contratos.crs_id = 15 AND
        tab_contratos.crt_id = {$tipo["id"]}
      GROUP BY
        id
      HAVING
        (MAX(comentarios_reprovacao.coo_datahora) IS NULL OR
         MAX(comentarios_aprovacao.coo_datahora) > MAX(comentarios_reprovacao.coo_datahora)) AND
        (NOW() > DATE_ADD(MAX(comentarios_aprovacao.coo_datahora), INTERVAL {$tipo["intervalo"]} HOUR))";
	  $query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	    $contratos[] = $dados;
	  }
	  return $contratos;
	}

	################################################
	#### Registra Retorno Aos Diretores Técnicos ###
	################################################

	function registraRetornoDiretorTecnico($contrato, $intervalo) {
	  $query = "
	    SELECT
	      tab_usuarios.usu_id AS id
	    FROM
	      tab_usuarios
	      INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
	    WHERE
	      rel_usu_gru_fazparte.gru_id = 13 AND
	      tab_usuarios.uss_id = 1";
	  $query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
  	  $comentario = "Contrato {$contrato["codigo"]} está aguardando aprovação técnica há mais de $intervalo hora(s)";
      $query = "INSERT INTO tab_clientes_retornos (cli_id, usu_id, clr_abertura, clr_data, clr_comentario, cet_id, ces_id) VALUES ({$contrato["cliente_id"]}, {$dados["id"]}, NOW(), CURDATE(), '$comentario', 2, 1)";
      $this->bancodedados->query($query);
	  }
	}

}

?>
