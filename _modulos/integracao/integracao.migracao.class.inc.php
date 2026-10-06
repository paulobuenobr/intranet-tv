<?

class integracao_migracao {

  var $bancodedados;

  function integracao_migracao($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->acao = $_GET["a"] ? $_GET["a"] : "migrar";
    if (method_exists($this, $this->acao)) {
			$this->{$this->acao}();
    } else {
			$this->erro = 3;
    }
  }

  #############
  ### Geral ###
  #############

	function carregaArquivo($arquivo) {
		if (file_exists(dirname(__FILE__)."/".$arquivo)) $arquivo = dirname(__FILE__)."/".$arquivo;
		$fp = fopen($arquivo, "r");
		$retorno = fread($fp, filesize($arquivo));
		fclose($fp);
		return $retorno;
	}

	function transicao($url) {
		die("<script type=\"text/javascript\">window.location = '$url';</script>");
	}
	
	##############
	### Buscas ###
	##############

  function buscaDadosMigracao($codigo) {
    $query = "
      SELECT
        migracao_planos.cli_id,
        migracao_planos.codigo,
        migracao_planos.plano_atual,
        migracao_planos.valor_atual,
        migracao_planos.plano_1,
        migracao_planos.valor_1,
        migracao_planos.plano_2,
        migracao_planos.valor_2,
        migracao_planos.migracao_plano,
        migracao_planos.migracao_contrato,
        DATE_FORMAT(migracao_planos.migracao_datahora, '%d/%m/%Y') AS data,
        DATE_FORMAT(migracao_planos.migracao_datahora, '%H:%i') AS hora,
        migracao_planos.migracao_ip,
        migracao_planos.envio_email,
        tab_clientes.cli_nome AS cliente_nome
      FROM
        migracao_planos
        INNER JOIN tab_clientes ON (migracao_planos.cli_id = tab_clientes.cli_id)
      WHERE
        migracao_planos.codigo = '$codigo' AND
        migracao_planos.erro IS NULL";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	$contrato_id = $this->buscaContratoCliente($dados["cli_id"]);
      if ($contrato_id) {
        $dados["contrato_id"] = $contrato_id;
        return $dados;
      }
    }
  }
  
  function buscaContratoCliente($cliente_id) {
    $query = "
      SELECT
        cor_id AS id
      FROM
        tab_contratos
      WHERE
        cli_id = $cliente_id AND
        crs_id IN (19, 20) AND
        crt_id = 11 AND
        pls_id = 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["id"];
    }
  }
  
  function buscaPlano($velocidade) {
    $planos[ 409600] = 315; // 400.0 Kbps
    $planos[ 614400] = 292; // 600.0 Kbps
    $planos[ 819200] = 316; // 800.0 Kbps
    $planos[1048576] = 330; //   1.0 Mbps
    $planos[1258291] =   0; //   1.2 Mbps
    $planos[1572864] = 331; //   1.5 Mbps
    $planos[2097152] = 332; //   2.0 Mbps
    $planos[2306867] =   0; //   2.2 Mbps
    $planos[2621440] = 333; //   2.5 Mbps
    $planos[3145728] = 334; //   3.0 Mbps
    $planos[3355443] =   0; //   3.2 Mbps
    $planos[3670016] =   0; //   3.5 Mbps
    return $planos[$velocidade];
  }
  
  function formataDinheiro($valor, $moeda = 'R$') {
    $valor = preg_replace("/[^0-9]/", "", intval($valor));
    $valor = number_format($valor / 100, 2, ",", ".");
    if ($moeda) $valor = $moeda." ".$valor;
    return $valor;
  }
  
  function formataVelocidade($download, $upload = 0, $unidade = "bps") {
    // Unidades
  	$unidades[1] = "K";
	  $unidades[2] = "M";
	  $unidades[3] = "G";
	  $unidades[4] = "T";
	  // Calcula Unidade
 	  $valor_unidade = floor(log($download, 1024));
 	  $valor_unidade = $valor_unidade < 1 ? 1 : $valor_unidade;
 	  // Formata Velocidade
 	  $valor_formatado[] = round($download / pow(1024, $valor_unidade), 1);
 	  if ($upload) {
 	    $valor_formatado[] = round($upload / pow(1024, $valor_unidade), 1);
 	  }
 	  return implode(" / ", $valor_formatado)." ".$unidades[$valor_unidade].$unidade;
  }
  
  function buscaAssinaturaMigrada($contrato_id) {
	  $query = "
	    SELECT
	      tab_assinaturas.ass_id AS id
	    FROM
	      tab_assinaturas
	      INNER JOIN rel_ass_cor_estacontido USING (ass_id)
	    WHERE
	      rel_ass_cor_estacontido.cor_id = $contrato_id AND
	      tab_assinaturas.ast_id = 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["id"];
    }
  }

  ##############
  ### Buscar ###
  ##############

  function migrar() {
	  if ($_GET["i"] && ($dados_migracao = $this->buscaDadosMigracao($_GET["i"]))) {
	    if ($dados_migracao["migracao_plano"]) {
	      // Exibe dados plano migrado
        $this->conteudo = $this->carregaArquivo("integracao.migracao.ver.inc.php");
        $this->conteudo = str_replace('[mensalidade_atual]', $this->formataDinheiro($dados_migracao["valor_atual"]), $this->conteudo);
        $this->conteudo = str_replace('[velocidade_atual]', $this->formataVelocidade($dados_migracao["plano_atual"]), $this->conteudo);
        $this->conteudo = str_replace('[mensalidade_final]', $this->formataDinheiro($dados_migracao["valor_{$dados_migracao["migracao_plano"]}"]), $this->conteudo);
        $this->conteudo = str_replace('[velocidade_final]', $this->formataVelocidade($dados_migracao["plano_{$dados_migracao["migracao_plano"]}"]), $this->conteudo);
        $this->conteudo = str_replace('[cliente]', $dados_migracao["cliente_nome"], $this->conteudo);
        $this->conteudo = str_replace('[data]', $dados_migracao["data"], $this->conteudo);
        $this->conteudo = str_replace('[hora]', $dados_migracao["hora"], $this->conteudo);
        $this->conteudo = str_replace('[migracao_ip]', $dados_migracao["migracao_ip"], $this->conteudo);
	    } else {
	      if (!$_POST["plano"]) {
	        // Exibe opções de migração
          $this->conteudo = $this->carregaArquivo("integracao.migracao.migrar.inc.php");
          $this->conteudo = str_replace('[codigo]', $_GET["i"], $this->conteudo);
          $this->conteudo = str_replace('[cliente]', $dados_migracao["cliente_nome"], $this->conteudo);
          $this->conteudo = str_replace('[mensalidade_atual]', $this->formataDinheiro($dados_migracao["valor_atual"]), $this->conteudo);
          $this->conteudo = str_replace('[velocidade_atual]', $this->formataVelocidade($dados_migracao["plano_atual"]), $this->conteudo);
          $this->conteudo = str_replace('[mensalidade_1]', $this->formataDinheiro($dados_migracao["valor_1"]), $this->conteudo);
          $this->conteudo = str_replace('[velocidade_1]', $this->formataVelocidade($dados_migracao["plano_1"]), $this->conteudo);
          $this->conteudo = str_replace('[mensalidade_2]', $this->formataDinheiro($dados_migracao["valor_2"]), $this->conteudo);
          $this->conteudo = str_replace('[velocidade_2]', $this->formataVelocidade($dados_migracao["plano_2"]), $this->conteudo);
	      } else {
	        $this->migrarPlano($dados_migracao, $_POST["plano"]);
			    $this->transicao("?m=migracao&i={$_GET["i"]}");
	      }
	    }
	  } else {
	    // Erro
			$this->erro = 31;
	  }
  }

  function migrarPlano($dados_migracao, $plano) {
    $contratos = controle::instanciaObjeto("contratos", $this->bancodedados, "", false);
    // Dados Migração
    $dados_cliente = $contratos->buscaDadosCliente($dados_migracao["cli_id"]);
    $dados_contrato_tipo = $contratos->buscaDadosTipo(13); // Migração
    $servico_id = 1; // Olé Condomínios
    $dados_contrato = $contratos->buscaDadosContrato($dados_migracao["contrato_id"]);
    $assinaturas[$dados_contrato["assinaturas"][0]["id"]] = $dados_contrato["assinaturas"][0]["id"];
    $novo_plano[$dados_contrato["assinaturas"][0]["id"]] = $this->buscaPlano($dados_migracao["plano_{$plano}"]);
    // Gera Contrato
    $contrato_id = $contratos->geraContrato($dados_cliente, $dados_contrato_tipo, $servico_id, $assinaturas, $dados_contrato, $novo_plano);
    // Adiciona Desconto 30 dias de desconto
    $valor_desconto = $dados_migracao["valor_{$plano}"] - $dados_migracao["valor_atual"];
    $assinatura_migrada_id = $this->buscaAssinaturaMigrada($contrato_id);
    $query = "INSERT INTO tab_descontos_concessoes (dec_data_cadastro, dec_validade, dec_mensalidade, des_id, det_id, ass_id, dcs_id) VALUES (CURDATE(), 1, $valor_desconto, 234, 1, $assinatura_migrada_id, 2)";
    $this->bancodedados->query($query);
    $desconto_id = $this->bancodedados->ultimoIdInserido();
    $query = "INSERT INTO rel_dec_cor_estacontido (dec_id, cor_id) VALUES ($desconto_id, $contrato_id)";
    $this->bancodedados->query($query);
    // Sincroniza contrato
    $dados_contrato = $contratos->buscaDadosContrato($contrato_id);
    $contratos->sincronizarContrato($dados_contrato);
    // Dados Ativação
    $dados_contrato = $contratos->buscaDadosContrato($contrato_id);
    // Ativa Contrato
    $data_ativacao = date("Y-m-d");
    $contrato_status = 20;
    $contratos->ativar_contrato_aditivo_migracao($dados_contrato, $data_ativacao, $contrato_status);
    // Atualiza dia vencimento do cliente - 5
    $query = "UPDATE tab_clientes SET cli_ccd_vencimento = 5 WHERE cli_id = {$dados_migracao["cli_id"]}";
    $this->bancodedados->query($query);
    // Registra Migração
    $query = "UPDATE migracao_planos SET migracao_plano = $plano, migracao_contrato = $contrato_id, migracao_datahora = NOW(), migracao_ip = '{$_GET["ip_cliente"]}' WHERE codigo = '{$dados_migracao["codigo"]}'";
    $this->bancodedados->query($query);
    // Retorna Contrato Migração
    return $contrato_id;
  }

}

?>
