<?

require_once("_global/modulo.class.inc.php");

class clientes_consultas_credito extends modulo {

    function __construct($bancodedados, $permissoes, $executar = true) {
  	
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		
    if ($executar) {
    	
  		$this->buscaDadosModulo($_GET["m"]);
  		$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
  		$this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
      
    }
    
	}

	function fechar() {
		$this->conteudo	= '<script type="text/javascript">window.close();</script>';
	}
	
  ########################
  ### Busca Dados Item ###
  ########################

  function buscaDadosItem($id) {
  	
    $query = "
      SELECT
        tab_clientes_consultas_credito.ccc_id as id,
        DATE_FORMAT(tab_clientes_consultas_credito.ccc_data_hora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_clientes_consultas_credito_situacoes.ccs_id AS situacao_id,
        IF(tab_clientes_consultas_credito_situacoes.ccs_id = 1, ccs_nome, CONCAT(ccs_nome, ' (', ccc_restricoes, ')')) AS situacao,
        tab_clientes_consultas_credito.ccc_conteudo AS conteudo,
        tab_clientes_consultas_credito_tipos.cct_id AS tipo_id,
        tab_clientes_consultas_credito_tipos.cct_nome AS tipo_nome,
        tab_clientes.cli_id AS cliente_id,
        tab_clientes.cli_nome AS cliente_nome,
        tab_usuarios.usu_id AS usuario_id,
        tab_usuarios.usu_nome AS usuario_nome
      FROM
      	tab_clientes_consultas_credito
        INNER JOIN tab_clientes_consultas_credito_situacoes ON (tab_clientes_consultas_credito.ccs_id = tab_clientes_consultas_credito_situacoes.ccs_id)
        INNER JOIN tab_clientes_consultas_credito_tipos ON (tab_clientes_consultas_credito.cct_id = tab_clientes_consultas_credito_tipos.cct_id)
        INNER JOIN tab_clientes ON (tab_clientes_consultas_credito.cli_id = tab_clientes.cli_id)
        INNER JOIN tab_usuarios ON (tab_clientes_consultas_credito.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_clientes_consultas_credito.ccc_id = $id";
    
    $query = $this->bancodedados->query($query);
    
    $this->dados_item = $this->bancodedados->resultadoArray($query);
    
    if ($this->dados_item) {
    	
      $this->dados_item["conteudo"] = unserialize($this->dados_item["conteudo"]);
      $this->dados_item["conteudo_formatado"] = $this->formataConteudo($this->dados_item);
      
    }
    
  }
  
  function buscaDadosCliente($cliente_id) {
  	
  	$query = "
  		SELECT
  			cli_id AS id,
  			cli_cpf_cnpj AS cpf_cnpj,
  			cli_nome AS nome
  		FROM
  			tab_clientes
  		WHERE
  			cli_id = $cliente_id";
  	
  	$query = $this->bancodedados->query($query);
    
    if (($dados = $this->bancodedados->resultadoArray($query)) != null) {
    	
    	$dados["telefones"] = $this->buscaTelefonesCliente($cliente_id);
    	$dados["telefones_formatado"] = array();
    	
    	foreach ($dados["telefones"] as $telefone) {
    		$dados["telefones_formatado"][] = "({$telefone["ddd"]}) {$telefone["numero"]}";
    	}
    	
    	$dados["telefones_formatado"] = implode(", ", $dados["telefones_formatado"]);
    	
    	return $dados;
    	
    }
    
    return null;
  	
  }
  
  function buscaTelefonesCliente($cliente_id) {
  	
  	$telefones = array();
  	
  	$query = "
			SELECT
				ccl_ddd AS ddd,
				ccl_numero AS numero
			FROM
				tab_clientes_contatos_telefones
			WHERE
				cli_id = $cliente_id AND
				ccf_id > 0
			LIMIT
				2";
  	
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$telefones[] = $dados;
			
		}
		
		return $telefones;
  	
  }
  
  function buscaClientesConsultasCredito($cliente_id) {
  	  	
  	$consultas = array();
		
		$query = "
      SELECT
        tab_clientes_consultas_credito.ccc_id AS id,
        tab_clientes_consultas_credito.cli_id AS cliente_id,
        DATE_FORMAT(tab_clientes_consultas_credito.ccc_data_hora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_clientes_consultas_credito.ccc_restricoes AS restricoes,
        tab_clientes_consultas_credito_situacoes.ccs_id AS situacao_id,
        tab_clientes_consultas_credito_situacoes.ccs_nome AS situacao,
        tab_clientes_consultas_credito_tipos.cct_nome AS tipo,
        tab_usuarios.usu_nome AS usuario
      FROM
        tab_clientes_consultas_credito
        INNER JOIN tab_clientes_consultas_credito_situacoes ON (tab_clientes_consultas_credito.ccs_id = tab_clientes_consultas_credito_situacoes.ccs_id)
        INNER JOIN tab_clientes_consultas_credito_tipos ON (tab_clientes_consultas_credito.cct_id = tab_clientes_consultas_credito_tipos.cct_id)
        INNER JOIN tab_usuarios ON (tab_clientes_consultas_credito.usu_id = tab_usuarios.usu_id)
      WHERE
      	tab_clientes_consultas_credito.cli_id = $cliente_id OR
        tab_clientes_consultas_credito.ccc_cpf_cnpj = (
        	SELECT
						cli_cpf_cnpj
					FROM
						tab_clientes
					WHERE
						cli_id = $cliente_id
        )
      ORDER BY
       	tab_clientes_consultas_credito.ccc_data_hora DESC";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes["clientes_consultas_credito"]["ver"]) {
				$dados["link"] = "javascript:verConsulta('{$dados["id"]}');";
			}
			          	
      if ($this->permissoes["clientes"]["ver_df"]) {
        $dados["cliente_id"] = "<a href=\"?m=clientes&a=ver_df&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
      }
			
			if ($dados["situacao_id"] == "1") {
      	$dados["cor_linha"] = "#C0FFC0";
      } else {
      	$dados["cor_linha"] = "#FFC0C0";
      }
			
			$consultas[] = $dados;
			
		}
		
		return $consultas;
  	
  }
  
  function geraListagemClientesConsultasCredito($dados) {
		
		$cabecalho = array("data_hora" => "Data/Hora", "cliente_id" => "ID", "tipo" => "Tipo", "usuario" => "Usuário", "restricoes" => "Restrições", "situacao" => "Situação");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array("100", "45", "100", "0", "80", "100"), null, array("center", "center", "left", "left", "right", "left"));
		
		return $listagem->conteudo;
  	
  }
  
  #################
  ### Consultar ###
  #################
  
  function consultar() {
  	
  	if ($_GET["i"] && ($dados_cliente = $this->buscaDadosCliente($_GET["i"])) != null) {
  		
  		if (!$_POST) {
  			
	      $this->conteudo .= $this->carregaArquivo("clientes_consultas_credito.consultar.inc.php");
	      
	      $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=consultar&i={$dados_cliente["id"]}", $this->conteudo);
	      $this->conteudo = str_replace("[cliente_nome]", $dados_cliente["nome"], $this->conteudo);
	      $this->conteudo = str_replace("[cliente_cpf_cnpj]", $dados_cliente["cpf_cnpj"], $this->conteudo);
	      $this->conteudo = str_replace("[cliente_telefones]", $dados_cliente["telefones_formatado"], $this->conteudo);
	      $this->conteudo = str_replace("[erro]", $_GET["erro"], $this->conteudo);
	      
	      $botoes[] = '<input type="submit" name="continuar" class="buttonAzulEscuroBranco11" value="Continuar">';
	      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
	      
	      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
  			
  		} else {
  			
				$check_check = controle::instanciaObjeto("clientes_consultas_credito.checkcheck");
				
				$check_check->setCpfCnpj($dados_cliente["cpf_cnpj"]);
				
				for ($i = 0; $i < count($dados_cliente["telefones"]) && $i < 2; $i++) {
					$check_check->addTelefone($dados_cliente["telefones"][$i]["ddd"], $dados_cliente["telefones"][$i]["numero"]);
				}
				
				if ($check_check->consultar()) {
					
					$conteudo = $check_check->getConteudo();
					
					$conteudo_serializado = serialize($conteudo);
					$restricoes = (int) $check_check->getRestricoes();
					$situacao_id = $restricoes == 0 ? 1 : 2;
					
					$query = "
						INSERT INTO
							tab_clientes_consultas_credito
						SET
							ccc_cpf_cnpj = '{$dados_cliente["cpf_cnpj"]}',
							ccc_data_hora = NOW(),
							ccc_conteudo = '{$conteudo_serializado}',
							ccc_restricoes = {$restricoes},
							cli_id = {$dados_cliente["id"]},
							usu_id = {$_SESSION["usuario"]},
							ccs_id = {$situacao_id},
							cct_id = 1";
					$this->bancodedados->query($query);
					
					$id = $this->bancodedados->ultimoIdInserido();
					
	        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
					
				} else {
					
	        header("Location: ./?m={$this->dados_modulo["nome"]}&a=consultar&i={$_GET["i"]}&erro={$check_check->getErro()}");
					
				}
  			
  		}
  		
  	} else {
  		
  		$this->fechar();
  		
  	}
  	
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
  	
    $this->buscaDadosItem($_GET["i"]);
    
    if ($this->dados_item) {
    	
      $this->conteudo .= $this->carregaArquivo("clientes_consultas_credito.ver.inc.php");
      
      $this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
      $this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
      $this->conteudo = str_replace("[data_hora]", $this->dados_item["data_hora"], $this->conteudo);
      $this->conteudo = str_replace("[usuario_nome]", $this->dados_item["usuario_nome"], $this->conteudo);
      $this->conteudo = str_replace("[situacao]", $this->dados_item["situacao"], $this->conteudo);
      $this->conteudo = str_replace("[conteudo]", $this->dados_item["conteudo_formatado"], $this->conteudo);
      
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      
    } else {
    	
      $this->fechar();
      
    }
    
  }
  
  ##################
  ### Formatação ###
  ##################
  
  function formataConteudo($consulta_credito) {
  	
  	$conteudo = $consulta_credito["conteudo"];
  	
  	unset($conteudo["CRE"]);
  	unset($conteudo["DHC"]);
  	unset($conteudo["NC0"]);
  	unset($conteudo["NC1"]);
  	unset($conteudo["NC2"]);
  	
  	if (array_key_exists("NOM", $conteudo) && array_key_exists("nomes", $conteudo["NOM"])) {
  		$conteudo["NOM"]["nomes"] = implode(", ", $conteudo["NOM"]["nomes"]);
  	}
  	
    $retorno  = "<div style=\"font-weight:bold;padding:3px;margin-bottom:6px;".($consulta_credito["situacao_id"] == 1 ? "background-color:#c0FFc0;color:#009900;border:1px solid #009900" : "background-color:#FFc0c0;color:#990000;border: 1px solid #990000")."\" align=\"center\" class=\"txtBranco11\"><b>{$consulta_credito["situacao"]}</div>";
    
    $retorno .= "<table cellspacing=\"1\" cellpadding=\"3\" width=\"100%\" bgcolor=\"#252525\">";
    
    foreach ($conteudo as $grupo => $dados_grupo) {
  		
  		if ($dados_grupo) {
  			
  			if (is_array($dados_grupo) && count($dados_grupo) > 0) {
	  			
  				$retorno .= "<tr><td colspan=\"2\" bgcolor=\"#505050\" align=\"left\" class=\"txtBranco11\">&nbsp;<b>{$this->buscaDescricaoGrupo($grupo)}".(array_key_exists(0, $dados_grupo) ? " (".count($dados_grupo).")" : "")."</b></td></tr>";
	  			
	  			foreach ($dados_grupo as $campo => $valor) {
	  				
	  				if ($valor) {
		  				
		  				if (is_int($campo)) {
	  				
		  						$retorno .= "<tr><td colspan=\"2\"  bgcolor=\"#E8E8E8\" style=\"padding:6px\"><table cellspacing=\"1\" cellpadding=\"3\" bgcolor=\"#252525\" width=\"100%\">";
		  						
		  						foreach ($valor as $tabela_campo => $tabela_valor) {
		  							
		  							if ($tabela_valor) {
			  							
			                $retorno .= "<tr><td  bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\" width=\"30%\"><b>&nbsp;&raquo; {$this->buscaDescricaoCampo($tabela_campo)}</b></td>";
			                $retorno .= "<td bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">{$this->formataDescricaoValor($grupo, $tabela_campo, $tabela_valor)}</td></tr>";
			                
		  							}
		                
		  						}
		  						
		  						$retorno .= "</table></td></tr>";
	  				
	  					} elseif (is_array($valor)) {
		  					
		  					foreach ($valor as $indice => $tabela) {
		  						
		  						$retorno .= "<tr><td colspan=\"2\"  bgcolor=\"#E8E8E8\" style=\"padding:6px\"><table cellspacing=\"1\" cellpadding=\"3\" bgcolor=\"#252525\" width=\"100%\">";
		  						
		  						foreach ($tabela as $tabela_campo => $tabela_valor) {
		  							
		  							if ($tabela_valor) {
			  							
			                $retorno .= "<tr><td  bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\" width=\"30%\"><b>&nbsp;&raquo; {$this->buscaDescricaoCampo($tabela_campo)}</b></td>";
			                $retorno .= "<td bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">{$this->formataDescricaoValor($grupo, $tabela_campo, $tabela_valor)}</td></tr>";
			                
		  							}
		                
		  						}
		  						
		  						$retorno .= "</table></td></tr>";
		  						
		  					}
		  					
		  				} else {
		  					
	                $retorno .= "<tr><td  bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\" width=\"30%\"><b>&nbsp;&raquo; {$this->buscaDescricaoCampo($campo)}</b></td>";
	                $retorno .= "<td bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">{$this->formataDescricaoValor($grupo, $campo, $valor)}</td></tr>";
		  					
		  				}
		  				
	  				}
	  				
	  			}
	  			
	  		}
	  		
  		}
  		
  	}
  	
  	$retorno .= "</table>";
  	
  	return $retorno;
  	
  }
  
  function buscaDescricaoGrupo($grupo) {
  	
    $respostas["quadroEntrada"] = "Dados Consultados";
    $respostas["grafia"] = "Grafia";
    $respostas["obito"] = "Confirmação de Óbito";
    $respostas["socio"] = "Quadro Societário";
    $respostas["participacao"] = "Participações Societárias";
    $respostas["rastro"] = "Rastro";
    $respostas["telefoneReferencia"] = "Telefones Referência";
    $respostas["telefoneProprietario"] = "Telefones do Proprietário";
    $respostas["telefoneConsultado"] = "Telefones Consultados";
    $respostas["chequeSemFundoVarejo"] = "Cheques sem Fundos no Varejo";
    $respostas["pendenciasInternasVarejo"] = "Pendências Internas no Varejo";
    $respostas["pendenciasFinanceirasComposto"] = "Pendências Financeiras (Composto)";
    $respostas["protestoEstadual"] = "Protestos Estaduais";
    
    $respostas["DCS"] = "Dados Consultados";
    $respostas["NOM"] = "Confirmação de Nome";
    $respostas["LOI"] = "Login";
    $respostas["MSG"] = "Mensagem";
    $respostas["FRN"] = "Franquia";
    $respostas["LAS"] = "Lista de Serviços";
    $respostas["ERR"] = "Erros";
    $respostas["CFM"] = "Nomes Confirmados na SRF";
    $respostas["BCO"] = "Situação do Banco Perante o CCO";
    $respostas["AGE"] = "Endereço de Agência Bancária";
    $respostas["RCH"] = "Recheque Online";
    $respostas["CFV"] = "Cheques Sem Fundos Varejo";
    $respostas["PVJ"] = "Pendências Internas no Varejo";
    $respostas["NC0"] = "COM RESTRIÇÃO";
    $respostas["NC1"] = "NADA CONSTA";
    $respostas["NC2"] = "ATENÇÃO - Constam apenas informações complementares, não restritivas";
    $respostas["CCF"] = "Cheques Sem Fundos";
    $respostas["RCO"] = "Restrições Comerciais Check Check";
    $respostas["TLB"] = "Telebloqueios - Cheques Consultados";
    $respostas["TBC"] = "Telebloqueios - Outros Cheques Não Consultados";
    $respostas["PLU"] = "Cheques Contra-Ordenados";
    $respostas["CTM"] = "Cheques Contra-Ordenados, Contumácia";
    $respostas["SEM"] = "Cheques Contra-Ordenados, Semelhantes";
    $respostas["PFN"] = "Pendências Financeiras";
    $respostas["RFN"] = "Refinanciamentos";
    $respostas["CHI"] = "Cheques Irregulares";
    $respostas["INS"] = "Insucessos Empresariais";
    $respostas["PRT"] = "Protestos";
    $respostas["ACO"] = "Ações";
    $respostas["FAL"] = "Falências";
    $respostas["TEL"] = "Telefones Confirmados";
    $respostas["VCL"] = "Veículos";
    $respostas["RST"] = "Rastro de Consultas";
    $respostas["CRE"] = "Código de Resposta";
    
    return array_key_exists($grupo, $respostas) ? $respostas[$grupo] : $grupo;
    
  }

  function buscaDescricaoCampo($campo) {
  	
  	$campos["cpfCnpj"] = "CPF/CNPJ";
  	$campos["tipoPessoa"] = "Tipo de Pessoa";
  	$campos["nomeServico"] = "Serviço";
  	$campos["dataConsulta"] = "Data da Consulta";
  	$campos["codigoCliente"] = "Código do Cliente";
  	$campos["nomeCliente"] = "Nome do Cliente";
  	$campos["dataVencimento"] = "Data Vencimento";
  	$campos["quantidadeCheques"] = "Quantidade de Cheques";
  	$campos["quadroSocietario"] = "Quadro Societário";
  	$campos["participacaoSocietaria"] = "Participação Societária";
  	$campos["ddd1"] = "DDD";
  	$campos["telefone1"] = "Telefone";
  	$campos["ddd2"] = "DDD";
  	$campos["telefone2"] = "Telefone";
  	$campos["nsu"] = "NSU";
  	$campos["dataSituacao"] = "Data Situação";
  	$campos["situacaoCPFCNPJ"] = "Situação CPF/CNPJ";
  	$campos["dataNascimentoFundacao"] = "Data Nascimento/Fundação";
  	$campos["idade"] = "Idade";
  	$campos["nomeMae"] = "Nome da Mãe";
  	$campos["grauInstrucao"] = "Grau de Instrução";
  	$campos["endereco"] = "Endereço";
  	$campos["razaoSocial"] = "Razão Social";
  	$campos["livro"] = "Livro";
  	$campos["folha"] = "Folha";
  	$campos["termo"] = "Termo";
  	$campos["tipoIdentificador"] = "Tipo de Identificador";
  	$campos["identificador"] = "Identificador";
  	$campos["participacao"] = "Participação";
  	$campos["cargo"] = "Cargo";
  	$campos["dataEntrada"] = "Data Entrada";
  	$campos["mensagemStatus"] = "Status";
  	$campos["telefone"] = "Telefone";
  	$campos["dddTelefone"] = "DDD";
  	$campos["codigoStatus"] = "Código Status";
  	$campos["dataInstalacao"] = "Data Instalação";
  	$campos["IdEndereco"] = "ID Endereço";
  	$campos["numeroBancoOcorrenciaMaisRecente"] = "Nr. Banco Mais Recente";
  	$campos["nomeBancoOcorrenciaMaisRecente"] = "Banco Mais Recente";
  	$campos["codigoAgenciaOcorrenciaMaisRecente"] = "Agência Mais Recente";
  	$campos["origemOcorrenciaMaisRecente"] = "Origem Mais Recente";
  	$campos["numeroLoja"] = "Número da Loja";
  	$campos["modalidade"] = "Modalidade";
  	$campos["avalista"] = "Avalista";
  	$campos["contrato"] = "Contrato";
  	$campos["cartorio"] = "Cartório";
  	
    // Cabeçalho
    $campos["cod_resp"] = "Código de Resposta";
    $campos["nomes"] = "Nome(s)";
    $campos["data_hora_consulta"] = "Data e Hora da Consulta";
    
    // Dados Consultados
    $campos["telefones"] = "Telefone(s)";
    $campos["banco"] = "Banco";
    $campos["cheque_inicial"] = "Cheque &raquo; Inicial";
    $campos["cheque_final"] = "Cheque &raquo; Final";
    $campos["cheque_valor"] = "Cheque &raquo; Valor";
    $campos["veiculo_uf"] = "Veículo &raquo; UF";
    $campos["veiculo_placa"] = "Veículo &raquo; Placa";
    $campos["veiculo_chassi"] = "Veículo &raquo; Chassi";
    $campos["veiculo_renavam"] = "Veículo &raquo; Renavam";
    $campos["veiculo_motor"] = "Veículo &raquo; Motor";
    $campos["veiculo_cambio"] = "Veículo &raquo; Câmbio";
    $campos["cmc7"] = "CMC7";
    $campos["cmc7_quantidade"] = "Quantidade de CMC7's";
    
    // Detalhamento
    $campos["status_login"] = "Status";
    $campos["mens"] = "Mensagem de Resposta";
    $campos["cnpj"] = "CNPJ";
    $campos["nome"] = "Nome";
    $campos["razao"] = "Razão Social";
    $campos["data_nasc"] = "Data de Nascimento";
    $campos["sit_rec"] = "Situação CPF/CNPJ";
    $campos["data_at"] = "Última Atualização";
    $campos["nome_mae"] = "Nome da Mãe";
    $campos["end"] = "Endereço";
    $campos["bairro"] = "Bairro";
    $campos["cidade"] = "Cidade";
    $campos["uf"] = "Estado";
    $campos["cep"] = "CEP";
    $campos["email"] = "E-mail";
    $campos["fone"] = "Telefone";
    $campos["franq"] = "Código da Franquia";
    $campos["perfil"] = "Número do Perfil";
    $campos["uso_cmc7"] = "Uso CMC7";
    $campos["time_cons"] = "Timeout da Consulta";
    $campos["time_login"] = "Timeout do Login";
    $campos["tipo_msg"] = "Tipo Mensagem";
    $campos["mensagem"] = "Mensagem";
    $campos["frn_cod"] = "Código";
    $campos["frn_rso"] = "Razão Social";
    $campos["frn_tel"] = "Telefone";
    $campos["frn_fax"] = "Fax";
    $campos["frn_end"] = "Endereço";
    $campos["frn_bai"] = "Bairro";
    $campos["frn_cid"] = "Cidade";
    $campos["frn_uf"] = "Estado";
    $campos["frn_cep"] = "CEP";
    $campos["frn_email"] = "E-mail";
    $campos["pfc_faixa"] = "Perfil";
    $campos["cod_srv"] = "Código";
    $campos["srv_def"] = "Default";
    $campos["val_min"] = "Valor Mínimo";
    $campos["val_max"] = "Valor Máximo";
    $campos["niv_err"] = "Nível";
    $campos["cod_err"] = "Código";
    $campos["msg_err"] = "Mensagem";
    $campos["quant"] = "Quantidade";
    $campos["descricao"] = "Descrição";
    $campos["data_atualizacao"] = "Última Atualização";
    $campos["ddd"] = "DDD";
    $campos["ddd_fax"] = "DDD";
    $campos["fax"] = "Fax";
    $campos["sit"] = "Situação";
    $campos["data_abert_encerr"] = "Data Abertura/Encerramento";
    $campos["tipo_pessoa"] = "Tipo de Pessoa";
    $campos["cpf_cnpj"] = "CPF/CNPJ";
    $campos["tipo_doc"] = "Tïpo de Documento";
    $campos["doc"] = "Número do Documento";
    $campos["emissor"] = "Órgão Emissor";
    $campos["data_emissão"] = "Data de Emissão";
    $campos["data_rec"] = "Data";
    $campos["cod_comp"] = "Código de Compensação";
    $campos["num_oc"] = "Código da Ocorrência";
    $campos["origem"] = "Origem";
    $campos["sigla"] = "Sigla";
    $campos["num_lj"] = "Número da Loja";
    $campos["data_ant"] = "Prmeira Ocorrência";
    $campos["data_rec"] = "Última Ocorrência";
    $campos["data_oc"] = "Data";
    $campos["mod"] = "Modalidade";
    $campos["aval"] = "Avalista";
    $campos["moeda"] = "Moeda";
    $campos["valor"] = "Valor";
    $campos["contr"] = "Contrato";
    $campos["quantidade"] = "Quantidade";
    $campos["quantidade_filiais"] = "Quantidade nas Filiais";
    $campos["dat_ant"] = "Cheque mais Antigo";
    $campos["dat_rec"] = "Cheque mais Recente";
    $campos["cod_bnc"] = "Código do Banco";
    $campos["nom_bnc"] = "Nome do Banco";
    $campos["cod_age"] = "Código da Agência";
    $campos["nom_age"] = "Nome da Agência";
    $campos["dat_inc_rec"] = "Inclusão mais Recente";
    $campos["tot_ccf"] = "Total de Cheques";
    $campos["dat_proc"] = "Processamento";
    $campos["dat_inc_ant"] = "Inclusão mais Antiga";
    $campos["dat_proc_pri_inc"] = "Proc. Primeira Inclusão";
    $campos["cpf_cnpj_assoc"] = "CPF/CNPJ";
    $campos["tip_pes_assoc"] = "Tipo de Pessoa";
    $campos["aln_bnc"] = "Alínea Bancária";
    $campos["emp_nom"] = "Empresa que Incluiu";
    $campos["emp_tel"] = "Telefone";
    $campos["dat_deb"] = "Data da Ocorrência";
    $campos["dat_inc"] = "Data de Inclusão";
    $campos["cod_mot_inc"] = "Código do Motivo";
    $campos["ori_inc"] = "Código da Origem";
    $campos["cod_bnc"] = "Código do Banco";
    $campos["cod_age"] = "Código da Agência";
    $campos["num_chq"] = "Número do Cheque";
    $campos["aln"] = "Alínea";
    $campos["num_parc"] = "Número de Parcelas";
    $campos["tot_parc"] = "Valor Total";
    $campos["num_men"] = "Número de Mensalidades";
    $campos["num_dup"] = "Número de Duplicatas";
    $campos["emp_uf"] = "Estado";
    $campos["mot_inc_cli"] = "Motivo de Inclusão";
    $campos["emp_raz"] = "Razão Social";
    $campos["tip_pes"] = "Tipo de Pessoa";
    $campos["ocr_val"] = "Valor da Ocorrência";
    $campos["cnt_cor"] = "Conta Corrente";
    $campos["chq_ini"] = "Cheque Inicial";
    $campos["chq_fin"] = "Cheque Final";
    $campos["motiv"] = "Motivo";
    $campos["dat_reg"] = "Data de Registro";
    $campos["hora"] = "Hora de Cadastramento";
    $campos["dat_cco"] = "Data da Contra Ordem";
    $campos["tip_cco"] = "Tipo da Contra Ordem";
    $campos["fonte"] = "Código da Fonte";
    $campos["doc_ori"] = "Documento Original";
    $campos["pfn_dat"] = "Data";
    $campos["pfn_mod"] = "Modalidade";
    $campos["tip_moeda"] = "Moeda";
    $campos["pfn_val"] = "Valor";
    $campos["pfn_ori"] = "Origem";
    $campos["rfn_dat"] = "Data";
    $campos["rfn_nat"] = "Natureza";
    $campos["rfn_val"] = "Valor";
    $campos["cid"] = "Cidade";
    $campos["cod_bcn_conv"] = "Banco/Participante";
    $campos["chi_dat"] = "Data";
    $campos["chq_val"] = "Valor";
    $campos["ins_dat"] = "Data";
    $campos["ins_nat"] = "Natureza";
    $campos["ins_qlf"] = "Qualificação";
    $campos["ins_var"] = "Vara de Registro";
    $campos["nom_pes"] = "Nome";
    $campos["prt_dat"] = "Data";
    $campos["cod_cart"] = "Código do Cartório";
    $campos["prt_nat"] = "Natureza";
    $campos["prt_val"] = "Valor";
    $campos["aco_dat"] = "Data";
    $campos["cod_var"] = "Código da Vara";
    $campos["cod_dist"] = "Código do Distribuidor";
    $campos["aco_nat"] = "Natureza";
    $campos["aco_val"] = "Valor";
    $campos["pri_coo"] = "Tipo";
    $campos["fal_dat"] = "Data";
    $campos["fal_nat"] = "Natureza";
    $campos["fal_var"] = "Vara";
    $campos["info"] = "Informação";
    $campos["bai"] = "Bairro";
    $campos["renavam"] = "Renavam";
    $campos["placa"] = "Placa";
    $campos["chassi"] = "Chassi";
    $campos["tip_mont"] = "Tipo de Montagem";
    $campos["tip_veic"] = "Tipo de Veículo";
    $campos["marca"] = "Marca";
    $campos["especie"] = "Espécie";
    $campos["situacao"] = "Situação";
    $campos["cor"] = "Cor";
    $campos["ano_mod"] = "Ano";
    $campos["combust"] = "Combustível";
    $campos["proced"] = "Procedência";
    $campos["tip_doc_prop"] = "Tipo de Documento";
    $campos["doc_prop"] = "Número do Documento";
    $campos["nome_prop"] = "Proprietário";
    $campos["dat_ult_atu"] = "Última Atualização";
    $campos["cilind"] = "Cilindradas";
    $campos["potencia"] = "Portência";
    $campos["motor"] = "Motor";
    $campos["qtd_eixos"] = "Eixos";
    $campos["rest1"] = "Restrição 1";
    $campos["rest2"] = "Restrição 2";
    $campos["rest3"] = "Restrição 3";
    $campos["rest4"] = "Restrição 4";
    $campos["max_pass"] = "Passageiros";
    $campos["peso_bruto"] = "Peso Bruto";
    $campos["tip_doc_fat"] = "Documento de Faturamento";
    $campos["doc_fat"] = "Número do Documento";
    $campos["uf_fat"] = "Estado";
    $campos["ano_fab"] = "Ano de Fabricação";
    $campos["data"] = "Data";
    
    return array_key_exists($campo, $campos) ? $campos[$campo] : $campo;

  }
  
  function formataDescricaoValor($grupo, $campo, $valor) {
  	
  	$valores["quadroEntrada"]["tipoPessoa"]["F"] = "Pessoa Física";
  	$valores["quadroEntrada"]["tipoPessoa"]["J"] = "Pessoa Jurídica";
  	$valores["quadroEntrada"]["quadroSocietario"]["S"] = "Sim";
  	$valores["quadroEntrada"]["quadroSocietario"]["N"] = "Não";
  	$valores["quadroEntrada"]["participacaoSocietaria"]["S"] = "Sim";
  	$valores["quadroEntrada"]["participacaoSocietaria"]["N"] = "Não";
  	$valores["telefoneReferencia"]["tipoPessoa"]["F"] = "Pessoa Física";
  	$valores["telefoneReferencia"]["tipoPessoa"]["J"] = "Pessoa Jurídica";
  	
    $valores["MSG"]["tipo_msg"]["0001"] = "Mensagem específica";
    $valores["MSG"]["tipo_msg"]["0000"] = "Mensagem genérica";
    $valores["LAS"]["pfc_faixa"]["0001"] = "Perfil por faixa";
    $valores["LAS"]["pfc_faixa"]["0000"] = "Perfil comum";
    $valores["LAS"]["srv_def"]["1"] = "Sim";
    $valores["LAS"]["srv_def"]["0"] = "Não";
    $valores["ERR"]["niv_err"]["1"] = "Cliente precisa logar novamente";
    $valores["ERR"]["niv_err"]["0"] = "Cliente não precisa logar novamente";
    $valores["RCH"]["tipo_doc"]["0"] = "RG";
    $valores["RCH"]["tipo_doc"]["1"] = "Outros";
    $valores["PVJ"]["aval"]["S"] = "Sim";
    $valores["CCF"]["tip_pes_assoc"]["1"] = "Pessoa Física";
    $valores["CCF"]["tip_pes_assoc"]["2"] = "Pessoa Jurídica";
    $valores["RCO"]["tip_pes"]["F"] = "Pessoa Física";
    $valores["RCO"]["tip_pes"]["J"] = "Pessoa Jurídica";
    $valores["TLB"]["motiv"]["E"] = "Extraviado";
    $valores["TLB"]["motiv"]["S"] = "Sustado";
    $valores["TLB"]["motiv"]["R"] = "Roubado";
    $valores["TLB"]["motiv"]["F"] = "Furtado";
    $valores["TBC"]["motiv"]["E"] = "Extraviado";
    $valores["TBC"]["motiv"]["S"] = "Sustado";
    $valores["TBC"]["motiv"]["R"] = "Roubado";
    $valores["TBC"]["motiv"]["F"] = "Furtado";
    $valores["TBC"]["motiv"]["C"] = "Cancelado";
    $valores["PLU"]["tip_cco"]["E"] = "Extraviado";
    $valores["PLU"]["tip_cco"]["S"] = "Sustado";
    $valores["PLU"]["tip_cco"]["R"] = "Roubado";
    $valores["PLU"]["tip_cco"]["T"] = "Cancelado";
    $valores["PLU"]["tip_cco"]["C"] = "Cancelado";
    $valores["CTM"]["tip_cco"]["E"] = "Extraviado";
    $valores["CTM"]["tip_cco"]["S"] = "Sustado";
    $valores["CTM"]["tip_cco"]["R"] = "Roubado";
    $valores["CTM"]["tip_cco"]["T"] = "Cancelado";
    $valores["CTM"]["tip_cco"]["C"] = "Cancelado";
    $valores["SEM"]["tip_cco"]["E"] = "Extraviado";
    $valores["SEM"]["tip_cco"]["S"] = "Sustado";
    $valores["SEM"]["tip_cco"]["R"] = "Roubado";
    $valores["SEM"]["tip_cco"]["T"] = "Cancelado";
    $valores["SEM"]["tip_cco"]["C"] = "Cancelado";
    $valores["PFN"]["aval"]["S"] = "Sim";
    $valores["PFN"]["aval"]["N"] = "Não";
    $valores["RFN"]["aval"]["S"] = "Sim";
    $valores["RFN"]["aval"]["N"] = "Não";
    $valores["INS"]["ins_qlf"]["GER"] = "Gerente";
    $valores["INS"]["ins_qlf"]["TIT"] = "Titular";
    $valores["INS"]["ins_qlf"]["SOC"] = "Sócio";
    $valores["INS"]["ins_qlf"]["DIR"] = "Diretor";
    $valores["INS"]["ins_qlf"]["XXX"] = "Outra";
    $valores["PRT"]["prt_nat"]["FPG"] = "Falta de Pagamento";
    $valores["PRT"]["prt_nat"]["XXX"] = "Outra";
    $valores["ACO"]["aco_nat"]["EXE"] = "Execução";
    $valores["ACO"]["aco_nat"]["BA"] = "Busca e Apreensão";
    $valores["ACO"]["aco_nat"]["JF"] = "Justiça Federal";
    $valores["ACO"]["aco_nat"]["JE"] = "Juizado Especial";
    $valores["ACO"]["aco_nat"]["XXX"] = "Outra";
    $valores["ACO"]["pri_coo"]["PRI"] = "Principal";
    $valores["ACO"]["pri_coo"]["COO"] = "Coobrigado";
    $valores["FAL"]["fal_nat"]["FAR"] = "Falência Requerida";
    $valores["FAL"]["fal_nat"]["FD"] = "Falência Decretada";
    $valores["FAL"]["fal_nat"]["AF"] = "Auto Falência";
    $valores["FAL"]["fal_nat"]["CR"] = "Concordata Requerida";
    $valores["FAL"]["fal_nat"]["CD"] = "Concordata Deferida";
    $valores["FAL"]["fal_nat"]["CS"] = "Concordata Suspensiva";
    $valores["FAL"]["fal_nat"]["XXX"] = "Outra";
    $valores["TEL"]["tip_pes"]["F"] = "Pessoa Física";
    $valores["TEL"]["tip_pes"]["J"] = "Pessoa Jurídica";
    
    if (array_key_exists($grupo, $valores) && array_key_exists($campo, $valores[$grupo]) && array_key_exists($valor, $valores[$grupo][$campo])) {
      
    	return mb_strtoupper($valores[$grupo][$campo][$valor]);
    
    } else {
    	
      return mb_strtoupper($valor);
      
    }
    
  }
  
}

?>
