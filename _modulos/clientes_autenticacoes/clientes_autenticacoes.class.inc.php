<?

require_once("_global/modulo.class.inc.php");

class clientes_autenticacoes extends modulo {

	const MIN_CCQ = 70;
	const MIN_SIGNAL = -70;
	const MIN_SNR = 15;
	
	function __construct($bancodedados, $permissoes, $executar = true) {
		
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		$this->buscaDadosModulo(__CLASS__);
		
		if ($executar) {
			if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
			$this->buscaDadosAcao(empty($_GET["a"]) ? "listar" : $_GET["a"]);
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
		
	}
		
	function buscaDadosCliente($cliente_id) {
		
		$query = "SELECT cli_id AS id, cli_nome AS nome FROM tab_clientes WHERE cli_id = $cliente_id";
		$query = $this->bancodedados->query($query);
		$this->dados_cliente = $this->bancodedados->resultadoArray($query);
		
	}

	######################
	### BuscaDadosItem ###
	######################

	function buscaDadosItem($id) {
		
		$query = "
			SELECT
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes.clo_id AS origem_id,
				tab_condominios.con_id AS condominio_id,
				tab_condominios.cit_id AS condominio_instalacao_tipo_id,
				tab_clientes_autenticacoes.cau_id AS id,
				tab_clientes_autenticacoes.cau_senha AS senha,
				tab_clientes_autenticacoes.cau_usuario AS usuario,
				tab_clientes_autenticacoes.cau_autenticador AS autenticador,
				tab_clientes_autenticacoes.cau_endereco_ip AS endereco_ip,
				tab_clientes_autenticacoes.cau_endereco_mac AS endereco_mac,
				tab_clientes_autenticacoes.cau_velocidade_download AS velocidade_download,
				tab_clientes_autenticacoes.cau_velocidade_upload AS velocidade_upload,
				tab_clientes_autenticacoes.cau_ajuste_download AS ajuste_download,
				tab_clientes_autenticacoes.cau_ajuste_upload AS ajuste_upload,
				tab_clientes_autenticacoes.cau_pool AS pool,
				tab_clientes_autenticacoes.cau_ajustado AS ajustado,
				tab_clientes_autenticacoes.cau_publicado AS publicado,
				tab_clientes_autenticacoes.cau_comentario AS observacao,
				tab_clientes_autenticacoes.cau_equipamento_ip AS equipamento_ip,
				tab_clientes_autenticacoes.cau_equipamento_modelo AS equipamento_modelo,
				tab_clientes_autenticacoes.cau_equipamento_serie AS equipamento_serie,
				tab_clientes_autenticacoes.cau_equipamento_mac AS equipamento_mac,
				tab_clientes_autenticacoes.ser_id AS servidor_id,
				tab_clientes_autenticacoes.sei_id AS interface_id,
				tab_clientes_autenticacoes.ssi_id AS ssid_id,
				tab_clientes_autenticacoes_status.cas_id AS status_id,
				tab_clientes_autenticacoes_status.cas_nome AS status_nome,
				tab_servidores_ssids.ssi_nome AS ssid_nome,
				tab_servidores_ssids.ssi_ip AS ssid_ip,
				tab_assinaturas.ass_id AS assinatura_id,
	 			CONCAT(tab_planos_servicos.pls_nome, ' (', tab_planos.pla_nome, ')', IF(tab_assinaturas.ast_id = 1, ' - Prospect', '')) AS assinatura,
	 			tab_planos_servicos.pls_id AS servico_id,
				tab_servidores.ser_publicacao_versao AS servidor_publicacao_versao,
				tab_servidores.ser_nome AS concentrador_nome,
				CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) AS concentrador_ip
			FROM
				tab_clientes_autenticacoes
				INNER JOIN tab_clientes_autenticacoes_status ON (tab_clientes_autenticacoes.cas_id = tab_clientes_autenticacoes_status.cas_id)
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_autenticacoes.ass_id)
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
				INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
				LEFT OUTER JOIN tab_servidores_ssids ON (tab_clientes_autenticacoes.ssi_id = tab_servidores_ssids.ssi_id)
				LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
				LEFT OUTER JOIN tab_servidores ON (tab_clientes_autenticacoes.ser_id = tab_servidores.ser_id)
				LEFT OUTER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
			WHERE
				tab_clientes_autenticacoes.cau_id = $id";
				
		$query = $this->bancodedados->query($query);		
		$this->dados_item = $this->bancodedados->resultadoArray($query);
		
		if ($this->dados_item) {
			
			// Gera dados velocidade
	 		$unidades = $this->buscaUnidadesVelocidade();
	 		$unidade = floor(log($this->dados_item["velocidade_download"], 1024));
	 		$unidade = $unidade < 1 ? 1 : $unidade;
	 		$velocidade = round($this->dados_item["velocidade_download"] / pow(1024, $unidade), 1)." / ".round($this->dados_item["velocidade_upload"] / pow(1024, $unidade), 1)." ".$unidades[$unidade];
			// Atribui velocidade ao campo
	 		$this->dados_item["velocidade"] = $velocidade;
	 		// Verifica se tem ajuste e mostra os valores ajustados
			if ($this->dados_item["ajustado"] && $this->dados_item["ajuste_download"] && $this->dados_item["ajuste_upload"]) {
		 		$unidade = floor(log($this->dados_item["ajuste_download"], 1024));
		 		if ($unidade < 1) $unidade = 1;
		 		$velocidade = round($this->dados_item["ajuste_download"] / pow(1024, $unidade), 1)." / ".round($this->dados_item["ajuste_upload"] / pow(1024, $unidade), 1)." ".$unidades[$unidade];
	 			$this->dados_item["velocidade"] .= " ($velocidade)";
	 			// Velocidade Final
	 			$this->dados_item["final_download"] = intval(round($this->dados_item["ajuste_download"] / 1024), 10);
	 			$this->dados_item["final_upload"] = intval(round($this->dados_item["ajuste_upload"] / 1024), 10);
			} else {
	 			// Velocidade Final
	 			$this->dados_item["final_download"] = intval(round($this->dados_item["velocidade_download"] / 1024), 10);
	 			$this->dados_item["final_upload"] = intval(round($this->dados_item["velocidade_upload"] / 1024), 10);
			}
			if ($this->dados_item["endereco_ip"]) {
				$this->dados_item["endereco_ip_separado"] = explode(".", $this->dados_item["endereco_ip"]);
			} else {
				$this->dados_item["endereco_ip_separado"] = array("", "", "", "");
			}
			$this->dados_item["bloqueado"] = (!$this->dados_item["ajustado"] && ($this->dados_item["velocidade_download"] <= 0 || $this->dados_item["velocidade_upload"] <= 0)) || ($this->dados_item["ajustado"] && ($this->dados_item["ajuste_download"] <= 0 || $this->dados_item["ajuste_upload"] <= 0));
			// Trata Status
			if ($this->dados_item["status_id"] != 3) {
				if ($this->dados_item["bloqueado"]) {
					$this->dados_item["status_nome"] .= " (Bloqueado)";
				}
				if ($this->dados_item["publicado"] && $this->dados_item["status_id"] != 2 && !$this->dados_item["bloqueado"]) {
					$this->dados_item["status_nome"] .= " (Publicado)";
				} elseif (!$this->dados_item["publicado"] && !$this->dados_item["bloqueado"]) {
					$this->dados_item["status_nome"] .= " (Não Publicado)";
				}
			}
			if ($this->dados_item["status_id"] == 1 && $this->dados_item["publicado"] && !$this->dados_item["bloqueado"]) {
				$this->dados_item["status_nome"] = "<span style=\"color:green\">{$this->dados_item["status_nome"]}<span>";
			} else {
				$this->dados_item["status_nome"] = "<span style=\"color:red\">{$this->dados_item["status_nome"]}<span>";
			}
			$this->dados_item["assinatura"] = (!$this->dados_item["assinatura"]) ? "Sem Assinatura" : $this->dados_item["assinatura"];
			
		}
		
	}
	
	function geraNomeEquipamento($cliente_id) {
		
		$query = "
			SELECT
				cli_id AS cliente_id,
				cli_nome AS cliente_nome
			FROM
				tab_clientes
			WHERE
				cli_id = $cliente_id";
				
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			$nome = explode(" ", $dados["cliente_nome"]);	
			return $this->limpaNome(strtolower($nome[0])).$cliente_id;
		}
		
	}
		
	function limpaNome($nome) {
		 
		$nome = strtr($nome, "ñÑçÇáàâãäªÁÀÂÃÄéèêëÉÈÊËíìîïÍÌÎÏóòôõö°ºÓÒÔÕÖúùûüÚÙÛÜ",
							 "nncCaaaaaaAAAAAeeeeEEEEiiiiIIIIoooooooOOOOOuuuuUUUU");
		$nome = preg_replace('/[^a-zA-Z0-9\-_\.\/\(\),\[\] ]/', '', $nome);

		return trim($nome);

	}

	function buscaUnidadesVelocidade() {
		
		$unidadecapacidade["1"] = "Kbps";
		$unidadecapacidade["2"] = "Mbps";
		$unidadecapacidade["3"] = "Gbps";
		$unidadecapacidade["4"] = "Tbps";
		
		return $unidadecapacidade;
		
	}
	
	function possuiPublicacaoPPPoE($cliente_id) {
		
		$query = "
			SELECT
				COUNT(tab_servidores_servicos.ses_id) AS servicos_pppoe
			FROM
				tab_clientes
				INNER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				INNER JOIN tab_servidores_servicos ON (tab_condominios_blocos.sei_id = tab_servidores_servicos.sei_id)
			WHERE
				tab_clientes.cli_id = $cliente_id AND
				tab_servidores_servicos.sst_id = 13";
				
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function buscaDadosAssinatura($assinatura_id) {
		
		$query = "
			SELECT
				ass_id AS id,
				cli_id AS cliente_id,
				pls_id AS servico_id
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				ass_id = $assinatura_id";
				
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultadoArray($query);
		
	}

	function buscaAssinaturas($cliente_id, $total = false) {
		
		$query = "
			SELECT
				tab_assinaturas.ass_id AS id,
				tab_planos_servicos.pls_id AS servico_id,
				CONCAT(tab_planos_servicos.pls_nome, ' - ', tab_planos.pla_nome, IF(tab_assinaturas.ast_id = 1, ' - Prospect', ''), IF(tab_clientes_pontos.clp_endereco_mac IS NULL, '', CONCAT(' [', tab_clientes_pontos.clp_endereco_mac, ']'))) AS nome,
 				tab_assinaturas.ass_parametros AS parametros,
				COUNT(DISTINCT tab_clientes_pontos.clp_id) AS quantidade_pontos,
				COUNT(DISTINCT tab_clientes_autenticacoes.cau_id) AS quantidade_autenticacoes
			FROM
				tab_planos_servicos
				INNER JOIN tab_planos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
				INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)
				INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
				INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id)
				LEFT OUTER JOIN tab_clientes_autenticacoes ON (tab_clientes_autenticacoes.ass_id = tab_assinaturas.ass_id AND tab_clientes_autenticacoes.cas_id != 3)
				LEFT OUTER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id AND tab_clientes_pontos.cps_id != 3)
			WHERE
				(tab_contratos.crs_id IN (19, 20, 21) OR ((SELECT tab_visitas.vis_data FROM tab_visitas WHERE tab_visitas.cor_id = tab_contratos.cor_id AND tab_visitas.via_id = 2 LIMIT 1) <= CURDATE())) AND
				tab_assinaturas.ast_id IN (1, 2, 5) AND
				tab_assinaturas.cli_id = $cliente_id
			GROUP BY
				tab_assinaturas.ass_id
			ORDER BY
				nome";
				
		$query = $this->bancodedados->query($query);
		
		while (($resultado = $this->bancodedados->resultadoArray($query)) != false) {
			
			$parametros = @unserialize($resultado["parametros"]);
			$disponivel = $parametros["pontos_quantidade"] - $resultado["quantidade_pontos"] - $resultado["quantidade_autenticacoes"];
			if ($total) {
				$autenticacoes_disponiveis += $disponivel;
			} else {
				if ($disponivel > 0) {
		 			$dados[$resultado["id"]] = $resultado["nome"]." ($disponivel disponíve".(($disponivel > 1) ? "is" : "l").")";
				}
			}
			
		}
		
		if ($total) {
			return $autenticacoes_disponiveis;
		} else {
			return $dados;
		}
		
	}

	function buscaDominiosPorAssinatura($assinatura_id = '%', $busca = false) {
		
		if ($assinatura_id && $assinatura_id != '%') {
			$servico_id = $this->buscaServicoAssinatura($assinatura_id);
		}
		
		$query = "
			SELECT DISTINCT
				tab_dominios.dom_id AS id,
				tab_dominios.dom_dominio AS nome
			FROM
				tab_dominios
			WHERE".($busca ? "" : "
				(tab_dominios.dos_id IN (3, 4)) AND")."
				(tab_dominios.dom_dominio <> '-') AND
				(tab_dominios.dom_dominio <> '') AND
				(tab_dominios.ass_id LIKE '$assinatura_id'".($servico_id != 11 ? " OR tab_dominios.dom_global = 1)" : ")")."
			ORDER BY
				nome";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dominios[$dados["id"]] = $dados["nome"];
		}
		
		return $dominios;
		
	}

	function buscaServicoAssinatura($assinatura_id) {
		
		$query = "
			SELECT
				tab_planos.pls_id AS id
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				tab_assinaturas.ass_id = $assinatura_id";
				
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);

	}

	function buscaParametros($ass_id) {
		
		$query = "
			SELECT
				ass_parametros AS parametros
			FROM
		 		tab_assinaturas
			WHERE
				ass_id = $ass_id";
				
		$query = $this->bancodedados->query($query);
		$parametros = $this->bancodedados->resultado($query);
		
		if ($parametros) {
			return unserialize($parametros);
		}
		
	}

	function buscaCliente($cli_id) {
		
		$query = "
			SELECT
				tab_clientes.cli_id AS id,
				tab_clientes.cli_nome AS nome,
				tab_clientes.con_id AS condominio_id,
				tab_clientes.clo_id AS origem_id,
				tab_condominios.cit_id AS condominio_instalacao_tipo_id,
				tab_servidores_interfaces.sei_id AS interface_id,
				tab_servidores_interfaces.ser_id AS servidor_id,
				IF(tab_ceps.bai_id IN (142,149,170,738,849), 1, 0) AS rwt
			FROM
				tab_clientes
				LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
				LEFT OUTER JOIN tab_ceps ON (tab_clientes.cep_id = tab_ceps.cep_id)
			WHERE
				tab_clientes.cli_id = $cli_id";
				
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultadoArray($query);
		
	}

	function buscaAssinaturasComAjuste($assinatura) {
		
		$lista = array();
		
		$query = "
			SELECT
				rel_saj_ass_contem.saj_id AS id
			FROM
				rel_saj_ass_contem
				INNER JOIN tab_servicos_ajustes ON (rel_saj_ass_contem.saj_id = tab_servicos_ajustes.saj_id)
			WHERE
				rel_saj_ass_contem.ass_id = $assinatura";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[] = $dados["id"];
		}
		
		return $lista;
		
	}

	function buscaAjustes($autenticacao_id) {
		
		$lista = array();

		$query = "
			SELECT DISTINCT
				tab_servicos_ajustes.saj_id AS id,
 				DATE_FORMAT(tab_servicos_ajustes.saj_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
				DATE_FORMAT(tab_servicos_ajustes.saj_termino, '%d/%m/%Y (%H:%i)') AS termino,
 				tab_servicos_ajustes_categorias.sac_nome AS categoria,
				tab_servicos_ajustes_status.sas_id AS status_id,
				tab_servicos_ajustes_status.sas_nome AS status,
				DATE_FORMAT(tab_clientes_autenticacoes_ajustes.caa_inicio, '%d/%m/%Y (%H:%i)') AS aplicado_deste
			FROM
				tab_servicos_ajustes
				INNER JOIN tab_servicos_ajustes_status ON (tab_servicos_ajustes_status.sas_id = tab_servicos_ajustes.sas_id)
				INNER JOIN rel_saj_cau_contem ON (rel_saj_cau_contem.saj_id = tab_servicos_ajustes.saj_id)
				INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes.sac_id = tab_servicos_ajustes_categorias.sac_id)
				LEFT OUTER JOIN tab_clientes_autenticacoes_ajustes ON (tab_clientes_autenticacoes_ajustes.saj_id = tab_servicos_ajustes.saj_id AND tab_clientes_autenticacoes_ajustes.caa_termino IS NULL)
			WHERE
				rel_saj_cau_contem.cau_id = $autenticacao_id AND
				tab_servicos_ajustes_status.sas_id IN (5, 9)
			ORDER BY
				id";
				
		$query = $this->bancodedados->query($query);
		
		while (($resultado = $this->bancodedados->resultadoArray($query)) != false) {
			
			$regras = '&raquo; Início: '.$resultado["inicio"];
			if ($resultado["termino"]) $regras .= '&nbsp; &nbsp; &raquo; Término: '.$resultado["termino"];
			$regras .= $this->buscaRegras($resultado["id"]);
			$regras = str_replace("\n", " ", $regras);
			$regras = str_replace("\"", "'", $regras);
			$resultado["codigo"] = "<div align=\"center\">".sprintf("%06s", $resultado["id"])."</div>";
			$resultado["categoria"] = "<div align=\"left\" style=\"cursor:pointer\"onmouseover=\"hintShow('$regras', 390)\" onmouseout=\"hintHide()\">".$resultado["categoria"]."</div>";
			if ($resultado["status_id"] == 5) {
				if ($resultado["aplicado_deste"]) {
					$resultado["status"] .= " / Aplicado desde {$resultado["aplicado_deste"]}";
				} else {
					$resultado["status"] .= " / Não Aplicado";
				}
			}
			$resultado["categoria"] = "<div align=\"left\">".$resultado["categoria"]."</div>";
			if (!$resultado["inicio"]) $resultado["inicio"] = "-";
			if (!$resultado["termino"]) $resultado["termino"] = "-";
			
			$lista[] = $resultado;
			
		}
		
		$cabecalho = array ("codigo" => "Código", "categoria" => "Categoria", "status" => "Status");
		$larguras = array("40", "", "220");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $lista, 0, false, $larguras);
		
		return $listagem->conteudo;
		
	}

	function buscaRegras($ajuste_id) {
		
		$tabela = "";
		
		$query = "
			SELECT
				tab_servicos_ajustes_regras.sar_ajuste AS ajuste,
				tab_servicos_ajustes_regras.sar_dia_semana AS dia_semana,
				DATE_FORMAT(tab_servicos_ajustes_regras.sar_hora_inicio, '%H:%i') AS inicio,
				DATE_FORMAT(tab_servicos_ajustes_regras.sar_hora_termino, '%H:%i') AS termino,
				tab_servicos_ajustes_tipos.sat_id AS tipo_id,
				tab_servicos_ajustes_tipos.sat_nome AS tipo
			FROM
				tab_servicos_ajustes_regras
				LEFT OUTER JOIN tab_servicos_ajustes_tipos ON (tab_servicos_ajustes_tipos.sat_id = tab_servicos_ajustes_regras.sat_id)
			 WHERE
			 	saj_id = $ajuste_id
			ORDER BY
				sar_id ASC";
				
		$query = $this->bancodedados->query($query);
		
		if ($this->bancodedados->linhas($query)) {
			
			$tabela .= '<table width="100%" cellpadding="1" cellspacing="1" border="0" bgcolor="#FFFFFF">';
			$tabela .= '<tr>';
			$tabela .= '<td bgcolor="#505050" class="txtBranco10" colspan="5" align="center"><b>Regras</b></td>';
			$tabela .= '</tr>';
			$tabela .= '<tr>';
			$tabela .= '<td bgcolor="#505050" class="txtBranco10"><b>Tipo</b></td>';
			$tabela .= '<td bgcolor="#505050" class="txtBranco10" align="center" width="50"><b>Ajuste</b></td>';
			$tabela .= '<td bgcolor="#505050" class="txtBranco10" align="center" width="70"><b>Dia</b></td>';
			$tabela .= '<td bgcolor="#505050" class="txtBranco10" align="center" width="45"><b>Início</b></td>';
			$tabela .= '<td bgcolor="#505050" class="txtBranco10" align="center" width="45"><b>Término</b></td>';
			$tabela .= '</tr>';
			
			while (($regra_ajuste = $this->bancodedados->resultadoArray($query)) != false) {
				$regra_ajuste["ajuste_tipo"] = $regra_ajuste["ajuste"] <= 1 ? 1 : 2;
				$regra_ajuste["ajuste_unidade"] = $regra_ajuste["ajuste_tipo"] == 2 ? floor(log($regra_ajuste["ajuste"], 1024)) : "";
				$regra_ajuste["ajuste_valor"] = $regra_ajuste["ajuste_tipo"] == 1 ? $regra_ajuste["ajuste"] * 100 : floor($regra_ajuste["ajuste"] / pow(1024, $regra_ajuste["ajuste_unidade"]));
				$regra_ajuste["ajuste_valor"] .= $regra_ajuste["ajuste_unidade"] ? " ".$this->buscaAjusteUnidade($regra_ajuste["tipo_id"], $regra_ajuste["ajuste_unidade"]) : "%";
				if (!$regra_ajuste["tipo"]) $regra_ajuste["tipo"] = "Todos";
				$regra_ajuste["dia_semana"] = (!$regra_ajuste["dia_semana"])? "Todos" : $this->buscaDiaSemana($regra_ajuste["dia_semana"]);
				if (!$regra_ajuste["inicio"]) $regra_ajuste["inicio"] = "00:00";
				if (!$regra_ajuste["termino"]) $regra_ajuste["termino"] = "24:00";
				$tabela .= '<tr>';
				$tabela .= "<td bgcolor=\"#505050\" class=\"txtBranco10\">{$regra_ajuste["tipo"]}</td>";
				$tabela .= "<td bgcolor=\"#505050\" class=\"txtBranco10\" align=\"center\">{$regra_ajuste["ajuste_valor"]}</td>";
				$tabela .= "<td bgcolor=\"#505050\" class=\"txtBranco10\" align=\"center\">{$regra_ajuste["dia_semana"]}</td>";
				$tabela .= "<td bgcolor=\"#505050\" class=\"txtBranco10\" align=\"center\">{$regra_ajuste["inicio"]}</td>";
				$tabela .= "<td bgcolor=\"#505050\" class=\"txtBranco10\" align=\"center\">{$regra_ajuste["termino"]}</td>";
				$tabela .= '</tr>';
			}
			
			$tabela .= '</table>';

		}
		
		return $tabela;
		
	}

	function buscaDiaSemana($dia) {
		
		$dias[1] = "Segunda";
		$dias[2] = "Terça";
		$dias[3] = "Quarta";
		$dias[4] = "Quinta";
		$dias[5] = "Sexta";
		$dias[6] = "Sábado";
		$dias[7] = "Domingo";
		
		return $dias[$dia];
		
	}

	function buscaAjusteUnidade($ajuste_tipo_id, $unidade_id) {
		
		$multiplicadores = array(0 => "", 1 => "K", 2 => "M", 3 => "G");
		$unidades = array(1 => "bps", 2 => "B", 4 => "B", 5 => "B / mês", 7 => "B", 8 => "B / mês", 9 => "bps");
		
		return $multiplicadores[$unidade_id].$unidades[$ajuste_tipo_id];
		
	}

	function buscaVelocidadeAssinatura($assinatura_id) {
		
		$query = "
			SELECT
				ass_parametros AS parametros
			FROM
				tab_assinaturas
			WHERE
				ass_id = $assinatura_id";
				
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			
			$dados_assinatura = $this->buscaDadosAssinatura($assinatura_id);
			$dados = @unserialize($dados["parametros"]);
			$lista_unidades = array ("1" => "Kbps", "2" => "Mbps", "3" => "Gbps", "4" => "Tbps");
 			$unidade_download = floor(log($dados["pontos_velocidade_download"], 1024));
			
 			return $dados["pontos_velocidade_download"] / pow(1024, $unidade_download)." / ".round($dados["pontos_velocidade_upload"] / pow(1024, $unidade_download), 1)." ".$lista_unidades[$unidade_download];
		
		}
		
	}

	function ehPublicavel() {
		
		if (!$this->permissoes[$this->dados_modulo["nome"]]["publicar"]) return false;
		if ($this->permissoes[$this->dados_modulo["nome"]]["publicar_plus"]) return true;
		if ($this->dados_item["publicado"]) return false;
		
		return true;
		
	}
	
	function ehVerificavel() {
		
		if (!$this->permissoes[$this->dados_modulo["nome"]]["verificar"]) return false;
		if (!$this->dados_item["ssid_ip"]) return false;
		
		return true;
		
	}
	
	/*
	function buscaCapacidadeMaxima($assinatura_id) {
		
		$query = "
			SELECT
				tab_assinaturas.ass_id AS id,
				tab_assinaturas.ass_parametros AS parametros,
				SUM(IF(tab_clientes_emails.ces_id IN (1, 2), tab_clientes_emails.cle_capacidade, 0)) AS emails_capacidade
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				LEFT OUTER JOIN tab_clientes_emails ON (tab_assinaturas.ass_id = tab_clientes_emails.ass_id)
			WHERE
				tab_assinaturas.ast_id IN (1, 2, 5) AND
				tab_assinaturas.ass_id = $assinatura_id
			GROUP BY
				tab_assinaturas.ass_id
			ORDER BY
				id";
		
		$query = $this->bancodedados->query($query);
		
		while (($resultado = $this->bancodedados->resultadoArray($query)) != false) {
			$resultado["parametros"] = unserialize($resultado["parametros"]);
			$capacidade_maxima += ($resultado["parametros"]["emails_capacidade"] * $resultado["parametros"]["emails_quantidade"]) - $resultado["emails_capacidade"];
		}
		
		return $capacidade_maxima > 0 ? $capacidade_maxima : 0;
		
	}

	function buscaCapacidadePadrao($assinatura_id) {
		
		$query = "
			SELECT
				ass_parametros
			FROM
				tab_assinaturas
			WHERE
				ass_id = $assinatura_id";
				
		$query = $this->bancodedados->query($query);
		$parametros = $this->bancodedados->resultado($query);
		
		if ($parametros) {
			$parametros = unserialize($parametros);
			return $parametros["emails_capacidade"];
		} else {
			return 0;
		}
		
	}

	function buscaCapacidades($capacidade_maxima) {
		
		for ($i = 10; $i <= $capacidade_maxima / (1024 * 1024); $i += 10) {
			$capacidades[$i * 1024 * 1024] = $this->formataCapacidade($i * 1024 * 1024);
			$maior_capacidade = $i * 1024 * 1024;
		}
		
		if ($maior_capacidade < $capacidade_maxima) {
			$capacidades[$maior_capacidade] = $this->formataCapacidade($maior_capacidade);
		}
		
		return $capacidades;
		
	}

	function formataCapacidade($capacidade) {
		
		if ($capacidade <= 0) return "0 MB";
		
		$unidades["1"] = "KB";
		$unidades["2"] = "MB";
		$unidades["3"] = "GB";
		$unidades["4"] = "TB";
		
		$unidade_capacidade = !$capacidade ? 2 : floor(log($capacidade, 1024));
		
		return round($capacidade / pow(1024, $unidade_capacidade), 2)." ".$unidades[$unidade_capacidade];
		
	}

	function buscaEmails($assinatura_id) {
		
		$emails = array();
		
		$query = "
			SELECT
				tab_clientes_emails.cle_id AS id,
				CONCAT(tab_clientes_emails.cle_usuario, '@', tab_dominios.dom_dominio) AS nome
			FROM
				tab_clientes_emails
				INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
			WHERE
				tab_clientes_emails.ass_id = $assinatura_id AND
				tab_clientes_emails.dom_id = 2062 AND
				tab_clientes_emails.ces_id = 1
			ORDER BY
				tab_dominios.dom_dominio,
				tab_clientes_emails.cle_usuario";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$emails[$dados["id"]] = $dados["nome"];
		}
		
		return $emails;
		
	}
	*/

	function verificaMAC($mac, $autenticacao_id) {
		
		$query = "
		 	SELECT
			 	tab_clientes.cli_id AS cliente_id,
			 	tab_clientes.cli_nome AS cliente_nome
		 	FROM
				tab_clientes_autenticacoes
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_autenticacoes.ass_id)
				INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
		 	WHERE
				tab_clientes_autenticacoes.cau_equipamento_mac = '$mac' AND
				tab_clientes_autenticacoes.cau_id != '$autenticacao_id' AND
				tab_clientes_autenticacoes.cas_id != 3";
				
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			return "Este MAC já está sendo utilizado pelo cliente [{$dados["cliente_id"]}] {$dados["cliente_nome"]}";
	 	}
		
	}
	
	function buscaInterfaces($interface_id = null, $profundidade = 0) {
		
		$interfaces = array();
		
		$query = "
			SELECT
				tab_servidores_interfaces.sei_id AS id,
				CONCAT(tab_servidores.ser_nome, ' (', tab_servidores_interfaces.sei_nome, ')') AS nome,
				tab_servidores.ser_id AS servidor_id
			FROM
				tab_servidores
				INNER JOIN tab_servidores_interfaces ON (tab_servidores.ser_id = tab_servidores_interfaces.ser_id)
					WHERE".($interface_id ? "
				tab_servidores.sei_id = $interface_id" : "
				tab_servidores.sso_id = 9")." AND
				tab_servidores.set_id = 1 AND
				tab_servidores_interfaces.sia_id = 1 AND
				tab_servidores_interfaces.sit_id IN (1, 6)
		 	ORDER BY
				tab_servidores.ser_nome,
				LENGTH(tab_servidores_interfaces.sei_nome),
				tab_servidores_interfaces.sei_nome";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$interfaces[$dados["id"]] = str_repeat("-", $profundidade).$dados["nome"];
		}
		
		return $interfaces;
		
	}
	
	function buscaInterface($interface_id) {
		
		if (!$interface_id) return null;
		
		$query = "
			SELECT
				CONCAT(tab_servidores.ser_nome, ' (', ip_servidor.sei_prefixo_ip , '.', tab_servidores.ser_endereco_ip,'): ', interface.sei_nome) AS nome
			FROM
				tab_servidores
				INNER JOIN tab_servidores_interfaces AS interface ON (tab_servidores.ser_id = interface.ser_id)
				INNER JOIN tab_servidores_interfaces AS ip_servidor ON (tab_servidores.sei_id = ip_servidor.sei_id)
			WHERE
				interface.sei_id = $interface_id";
				
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function buscaInterfacesServidor($servidor_id) {
		
		$interfaces = array();
		
		$query = "
			SELECT
				sei_id AS id
			FROM
				tab_servidores_interfaces
			WHERE
				ser_id = $servidor_id AND
				sia_id = 1 AND
				sit_id = 3";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$interfaces[] = $dados["id"];
		}
		
		return $interfaces;
		
	}
	
	function buscaIpSSID($ssid_id){
		
		$query = "
			SELECT
				ssi_ip
			FROM
				tab_servidores_ssids
			WHERE
				ssi_id = $ssid_id";
				
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function buscaIpPoolInterface($interface_id, $ip_atual = null) {
		
		$query = "
			SELECT
				sei_configuracao
			FROM
				tab_servidores_interfaces
			WHERE
				sei_id = $interface_id";
				
		$query = $this->bancodedados->query($query);
		$configuracao = $this->bancodedados->resultado($query);
		
		if ($configuracao) {
			$configuracao = unserialize($configuracao);
			if (array_key_exists("pool", $configuracao)) {
				foreach ($configuracao["pool"] as $classe) {
					
					$classe["classe"] = split("/", $classe["classe"]);
					$rede = $classe["classe"][0];
					$classe = $classe["classe"][1];
					
					if ($ip_atual && $this->ehIPdaRede($ip_atual, $rede, $classe)) {
						return array("ip" => $ip_atual, "mascara" => $this->calculaMascara($classe), "gateway" => $this->calculaGateway($rede, $classe));
					} else {
						$ip = $this->buscaIpLivre($interface_id, $rede, $classe);
						if ($ip) {
							return array("ip" => $ip, "mascara" => $this->calculaMascara($classe), "gateway" => $this->calculaGateway($rede, $classe));
						}
					}
					
				}
			}
		}
		
	}
	
	function calculaMascara($classe) {
		
		if ($classe >= 0 && $classe <= 32) {
		
			$mask = str_repeat("1", $classe).str_repeat("0", 32 - $classe);
					
			return bindec(substr($mask, 0, 8)).".".bindec(substr($mask, 8, 8)).".".bindec(substr($mask, 16, 8)).".".bindec(substr($mask, 24, 8));
	
		}
		
		return "";
	
	}
	
	function calculaGateway($rede, $classe) {
	
		$ip = split("[.]", $rede);
		$ip = sprintf('%08b', $ip[0]).sprintf('%08b', $ip[1]).sprintf('%08b', $ip[2]).sprintf('%08b', $ip[3]);
		
		$mascara = str_repeat("1", $classe).str_repeat("0", 32 - $classe);
		
		$ip = sprintf('%032b', (bindec($ip) & bindec($mascara)) + 1);
		
		return bindec(substr($ip, 0, 8)).".".bindec(substr($ip, 8, 8)).".".bindec(substr($ip, 16, 8)).".".bindec(substr($ip, 24, 8));
	
	}
	
	function ehIpPoolInterface($interface_id, $ip) {
		
		$query = "
			SELECT
				sei_configuracao AS configuracao
			FROM
				tab_servidores_interfaces
			WHERE
				sei_id = $interface_id";
				
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			if ($dados["configuracao"]) {
				$dados["configuracao"] = unserialize($dados["configuracao"]);
				if (array_key_exists("pool", $dados["configuracao"])) {
					foreach ($dados["configuracao"]["pool"] as $pool) {
						$classe = explode("/", $pool["classe"]);
						if ($this->ehIPdaRede($ip, $classe[0], $classe[1])) {
							return true;
						}
					}
				}
			}
		}
		
		return false;
		
	}
	
	function ehIPdaRede($ip, $rede, $classe) {
		$ip = ip2long($ip);
		$ip_inicial = ip2long($rede);
		$ip_final = $ip_inicial + pow(2, 32 - $classe);
		return $ip >= $ip_inicial && $ip <= $ip_final;
	}
	
	function buscaIpLivre($interface_id, $rede, $classe) {
		
		$ip_inicial = ip2long($rede);
		$quantidade_ips = pow(2, 32 - $classe);
		$pool = array();
		for ($i = 2; $i < $quantidade_ips - 1; $i++) {
			$pool[long2ip($ip_inicial + $i)] = long2ip($ip_inicial + $i);
		}
		$ips = "'".implode("', '", $pool)."'";
		
		$query = "
			SELECT
				cau_equipamento_ip AS ip
			FROM
				tab_clientes_autenticacoes
			WHERE
				cau_equipamento_ip IN ($ips)";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if (array_key_exists($dados["ip"], $pool)) {
				unset($pool[$dados["ip"]]);
			}
		}
		
		if (count($pool) > 0) {
			return current($pool);
		}
		
	}
	
	function buscaTiposAutenticadores($tipo = null) {
		
		$tipos = array();
		
		$tipos["ATA"] = "ATA";
		$tipos["PC"] = "PC";
		$tipos["Rádio 2.4Ghz"] = "Rádio 2.4Ghz";
		$tipos["Rádio 5.8Ghz"] = "Rádio 5.8Ghz";
		$tipos["Roteador"] = "Roteador";
		$tipos["Telefone IP"] = "Telefone IP";
		
		if ($tipo && !in_array($tipo, $tipos)) {
			$tipos[$tipo] = $tipo;
		}
		
		ksort($tipos);
		
		return $tipos;
		
	}

	function buscaTecnicos() {

		$usuarios = array();
	
		$query = "
			SELECT
				tab_usuarios.usu_id AS id,
				tab_usuarios.usu_nome AS nome
			FROM
				tab_usuarios
				INNER JOIN rel_usu_orf_ors_exerce ON (tab_usuarios.usu_id = rel_usu_orf_ors_exerce.usu_id)
			WHERE
				tab_usuarios.uss_id = 1 AND
				rel_usu_orf_ors_exerce.ors_id = 10
			ORDER BY
				tab_usuarios.usu_nome";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$usuarios[$dados["nome"]] = $dados["nome"];
		}
		
		return $usuarios;

	}
	
	##############
	### Fechar ###
	##############

	function fechar() {
		
		if ($_GET["id"]) {
			$dados_item = $this->buscaCliente($_GET["id"]);
			$this->conteudo = "
				<script type=\"text/javascript\">
					if (!window.opener.closed && window.opener.buscaAutenticacoes) {
						window.opener.buscaAutenticacoes({$_GET["id"]});
					}
					window.close();
				</script>";
		} else {
			$this->conteudo = '<script type="text/javascript">window.close();</script>';
		}
		
	}
	
	function buscaInterfaceSSIDs($ssid_id = null){
		
		$ssids = array();
		
		$query="
			SELECT
				tab_servidores_ssids.ssi_id AS id,
				CONCAT(tab_servidores_ssids.ssi_nome, ' (', tab_servidores.ser_nome, ' / ', tab_servidores_interfaces.sei_nome, ')') AS nome
			FROM
				tab_servidores
				INNER JOIN tab_servidores_interfaces ON (tab_servidores.ser_id = tab_servidores_interfaces.ser_id)
				INNER JOIN tab_servidores_ssids ON (tab_servidores_interfaces.sei_id = tab_servidores_ssids.sei_id)
			WHERE
				(tab_servidores.sso_id = 9 AND
				 tab_servidores.set_id = 1 AND
				 tab_servidores_interfaces.sia_id = 1 AND
				 tab_servidores_interfaces.sit_id = 1 AND
				 tab_servidores_ssids.ssi_instalavel = 1)".($ssid_id ? " OR
				(tab_servidores_ssids.ssi_id = $ssid_id)" : "")."
		 	ORDER BY
				tab_servidores_ssids.ssi_nome ASC";
				
	 	$query = $this->bancodedados->query($query);
		
	 	while(($dados = $this->bancodedados->resultadoArray($query)) != false){
	 		$ssids[$dados["id"]] = $dados["nome"];
	 	}
		
	 	return $ssids;
		
	}
	
	function buscaInterfaceSSID($ssid_id){
		
		$query = "
			SELECT
				sei_id 
			FROM
				tab_servidores_ssids
			WHERE
				ssi_id = $ssid_id";
				
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);
		
	}

	#################
	### Adicionar ###
	#################

	function adicionar() {
		
		$cliente_assinaturas = $this->buscaAssinaturas($_GET["i"]);
		
		if ($cliente_assinaturas) {
			
			$dados_cliente = $this->buscaCliente($_GET["i"]);
			
			if (!$_POST) {
				
				if (count($cliente_assinaturas) == 1) {
					$dados_assinatura = $this->buscaDadosAssinatura(key($cliente_assinaturas));
				}
				
				$this->conteudo .= $this->carregaArquivo("clientes_autenticacoes.adicionar.inc.php");
				
				$this->conteudo = str_replace('[cliente_id]', $_GET["i"], $this->conteudo);
				$this->conteudo = str_replace('[cliente_nome]', $dados_cliente["nome"], $this->conteudo);
				$this->conteudo = str_replace('[assinatura]', $this->geraSelect($cliente_assinaturas, $dados_assinatura ? $dados_assinatura["id"] : null, true, "assinatura", "100%", "mudaAssinatura(this.value)"), $this->conteudo);
				$this->conteudo = str_replace('[velocidade]', $dados_assinatura ? $this->buscaVelocidadeAssinatura($dados_assinatura["id"]) : "", $this->conteudo);
				$this->conteudo = str_replace('[autenticador]', $this->geraSelect($this->buscaTiposAutenticadores(), null, true, "autenticador", "100%"), $this->conteudo);
				
				if ($dados_assinatura) {
					
					if ($dados_cliente["condominio_id"] && $dados_cliente["condominio_instalacao_tipo_id"] == 2) {
						
						// Olé Condomínios
						$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
						$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[linha_concentrador_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[concentrador]', '', $this->conteudo);
						$this->conteudo = str_replace('[linha_endereco_ip_visivel]', in_array($dados_assinatura["servico_id"], array(3, 4, 9, 12, 30, 34, 35, 36, 39)) ? 'table-row' : 'none', $this->conteudo);
						$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[aguardando_verificacao]', 'false', $this->conteudo);
						
					} else if (in_array($dados_assinatura["servico_id"], array(3, 4, 9, 12, 30, 34, 35, 36, 39))) {
						
						// Olé Empresas/PME/Monitoramento
						$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
						$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[linha_concentrador_visivel]', 'table-row', $this->conteudo);
						$this->conteudo = str_replace('[concentrador]', $this->geraSelect($this->buscaConcentradores(), null, true, "concentrador", "100%"), $this->conteudo);
						$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'table-row', $this->conteudo);
						$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[aguardando_verificacao]', 'false', $this->conteudo);
						
					} else if (in_array($dados_assinatura["servico_id"], array(5))) {
						
						// Olé Casa
						$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
						$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[linha_concentrador_visivel]', 'table-row', $this->conteudo);
						$this->conteudo = str_replace('[concentrador]', $this->geraSelect($this->buscaConcentradores(), null, true, "concentrador", "100%"), $this->conteudo);
						$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[aguardando_verificacao]', 'false', $this->conteudo);
						
					} else if (in_array($dados_assinatura["servico_id"], array(28))) {
						
						// Olé Residencial
						$this->conteudo = str_replace('[linha_ssid_visivel]', 'table-row', $this->conteudo);
						$this->conteudo = str_replace('[ssid]', $this->geraSelect($this->buscaInterfaceSSIDs(), null, true, "ssid", "100%", "mudaSSID(this.value)"), $this->conteudo);
						$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'table-row', $this->conteudo);
						$this->conteudo = str_replace('[linha_concentrador_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[concentrador]', '', $this->conteudo);
						$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[equipamento_visivel]', 'table-row-group', $this->conteudo);
						$this->conteudo = str_replace('[aguardando_verificacao]', 'true', $this->conteudo);
						
					} else if (in_array($dados_assinatura["servico_id"], array(38))) {
						
						// Olé Fibra
						$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
						$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[linha_concentrador_visivel]', 'table-row', $this->conteudo);
						$this->conteudo = str_replace('[concentrador]', $this->geraSelect($this->buscaConcentradores(), null, true, "concentrador", "100%"), $this->conteudo);
						$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
						$this->conteudo = str_replace('[aguardando_verificacao]', 'false', $this->conteudo);
						
					}
					
				} else {
					
					$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
					$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[linha_concentrador_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[concentrador]', '', $this->conteudo);
					$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[aguardando_verificacao]', 'false', $this->conteudo);
					
				}
				
				// Botões
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar" onclick="mudaAssinatura(0)">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onclick="window.close()">';

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar&i={$_GET["i"]}&device={$_GET["device"]}", $this->conteudo);
				
			} else {
								
				// Cadastra Autenticação
				$dados_assinatura = $this->buscaDadosAssinatura($_POST["assinatura"]);
			 	$parametros = $this->buscaParametros($_POST["assinatura"]);
 				$velocidade_download = $parametros["pontos_velocidade_download"] ? $parametros["pontos_velocidade_download"] : 0;
 				$velocidade_upload = $parametros["pontos_velocidade_upload"] ? $parametros["pontos_velocidade_upload"] : 0;
				
				if ($dados_cliente["condominio_id"] && $dados_cliente["condominio_instalacao_tipo_id"] == 2) {
					
					// Olé Condomínios
					if (in_array($dados_assinatura["servico_id"], array(3, 4, 9, 12, 30, 34, 35, 36, 39)) && $_POST["endereco_ip_1"] && $_POST["endereco_ip_2"] && $_POST["endereco_ip_3"] && $_POST["endereco_ip_4"]) {
						$_POST["endereco_ip"] = "'{$_POST["endereco_ip_1"]}.{$_POST["endereco_ip_2"]}.{$_POST["endereco_ip_3"]}.{$_POST["endereco_ip_4"]}'";
					} else {
						$_POST["endereco_ip"] = "NULL";
					}
 					$_POST["ssid"] = "NULL";
 					$_POST["interface"] = $dados_cliente["interface_id"] ? $dados_cliente["interface_id"] : "NULL";
 					$_POST["servidor"] = $dados_cliente["servidor_id"] ? $dados_cliente["servidor_id"] : "NULL";
				
					// Dados Equipamento
					$_POST["equipamento_ip"] = "NULL";
					$_POST["equipamento_modelo"] = "NULL";
					$_POST["equipamento_mac"] = "NULL";
					$_POST["equipamento_serie"] = "NULL";

				} else if (in_array($dados_assinatura["servico_id"], array(3, 4, 9, 12, 30, 34, 35, 36, 39))) {
					
					// Olé Empresas/PME/Monitoramento
					if ($_POST["endereco_ip_1"] && $_POST["endereco_ip_2"] && $_POST["endereco_ip_3"] && $_POST["endereco_ip_4"]) {
						$_POST["endereco_ip"] = "'{$_POST["endereco_ip_1"]}.{$_POST["endereco_ip_2"]}.{$_POST["endereco_ip_3"]}.{$_POST["endereco_ip_4"]}'";
					} else {
						$_POST["endereco_ip"] = "NULL";
					}
 					$_POST["ssid"] = "NULL";
 					$_POST["interface"] = "NULL";
 					$_POST["servidor"] = $_POST["concentrador"] ? $_POST["concentrador"] : "NULL";
				
					// Dados Equipamento
					$_POST["equipamento_ip"] = "NULL";
					$_POST["equipamento_modelo"] = "NULL";
					$_POST["equipamento_mac"] = "NULL";
					$_POST["equipamento_serie"] = "NULL";

				} else if (in_array($dados_assinatura["servico_id"], array(5))) {
					
					// Olé Casa
					$_POST["endereco_ip"] = "NULL";
 					$_POST["ssid"] = "NULL";
 					$_POST["interface"] = "NULL";
 					$_POST["servidor"] = $_POST["concentrador"] ? $_POST["concentrador"] : "NULL";
									
					// Dados Equipamento
					$_POST["equipamento_ip"] = "NULL";
					$_POST["equipamento_modelo"] = "NULL";
					$_POST["equipamento_mac"] = "NULL";
					$_POST["equipamento_serie"] = "NULL";

				} else if (in_array($dados_assinatura["servico_id"], array(28))) {
					
					// Olé Residencial
 					$ssid = $this->buscaDadosSSID($_POST["ssid"]);
					$_POST["endereco_ip"] = "NULL";
					$_POST["ssid"] = $_POST["ssid"] ? $_POST["ssid"] : "NULL";
 					$_POST["interface"] = $ssid ? $ssid["interface_id"] : "NULL";
 					$_POST["servidor"] = $ssid ? $ssid["servidor_id"] : "NULL";
						
					// Dados Equipamento
					if ($_SESSION["equipamento"] && $_POST["verificacao_id"] && array_key_exists($_POST["verificacao_id"], $_SESSION["equipamento"])) {
						$_POST["equipamento_ip"] = $_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_ip"] ? "'{$_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_ip"]}'" : "NULL";
						$_POST["equipamento_modelo"] = $_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_modelo"] ? "'{$_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_modelo"]}'" : "NULL";
						$_POST["equipamento_mac"] = $_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_mac"] ? "'{$_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_mac"]}'" : "NULL";
					} else {
						$_POST["equipamento_ip"] = "NULL";
						$_POST["equipamento_modelo"] = "NULL";
						$_POST["equipamento_mac"] = "NULL";
					}
					$_POST["equipamento_serie"] = $_POST["equipamento_serie"] ? "'{$_POST["equipamento_serie"]}'" : "NULL";

				} else if (in_array($dados_assinatura["servico_id"], array(38))) {
					
					// Olé Fibra
					$_POST["endereco_ip"] = "NULL";
 					$_POST["ssid"] = "NULL";
 					$_POST["interface"] = "NULL";
 					$_POST["servidor"] = $_POST["concentrador"] ? $_POST["concentrador"] : "NULL";
									
					// Dados Equipamento
					$_POST["equipamento_ip"] = "NULL";
					$_POST["equipamento_modelo"] = "NULL";
					$_POST["equipamento_mac"] = "NULL";
					$_POST["equipamento_serie"] = "NULL";

				} else {
					
					$_POST["endereco_ip"] = "NULL";
 					$_POST["ssid"] = "NULL";
 					$_POST["interface"] = "NULL";
 					$_POST["servidor"] = "NULL";
									
					// Dados Equipamento
					$_POST["equipamento_ip"] = "NULL";
					$_POST["equipamento_modelo"] = "NULL";
					$_POST["equipamento_mac"] = "NULL";
					$_POST["equipamento_serie"] = "NULL";

				}
												
 				$_POST["usuario"] = $this->geraUsuario($dados_cliente);
 				$_POST["senha"] = $this->geraSenha();
						
 				// Insere registro
				$query = "
					INSERT INTO
						tab_clientes_autenticacoes
					SET
						cau_usuario = '{$_POST["usuario"]}',
						cau_senha = '{$_POST["senha"]}', 
						cau_velocidade_download = $velocidade_download, 
						cau_velocidade_upload = $velocidade_upload, 
						cau_autenticador = '{$_POST["autenticador"]}', 
						cau_equipamento_ip = {$_POST["equipamento_ip"]}, 
						cau_equipamento_modelo = {$_POST["equipamento_modelo"]}, 
						cau_equipamento_serie = {$_POST["equipamento_serie"]}, 
						cau_equipamento_mac = {$_POST["equipamento_mac"]}, 
						cau_endereco_ip = {$_POST["endereco_ip"]}, 
						ass_id = {$_POST["assinatura"]}, 
						ser_id = {$_POST["servidor"]}, 
						sei_id = {$_POST["interface"]}, 
						ssi_id = {$_POST["ssid"]}, 
						cas_id = 1, 
						cau_data_alteracao = CURDATE()";
			 	
				$this->bancodedados->query($query);
				
				$id = $this->bancodedados->ultimoIdInserido();
				
				// Publica Autenticação
				$this->publicarAutenticacao($id);
				
				// Verifica se assinatura tem ajuste e adiciona ajuste a autenticação
				$ajuste = $this->buscaAssinaturasComAjuste($_POST["assinatura"]);
				if ($ajuste) {
					foreach ($ajuste as $id_ajuste) {
						$query = "
					 		INSERT INTO
					 			rel_saj_cau_contem (saj_id, cau_id)
					 		VALUES ($id_ajuste, $id)";
					 	$this->bancodedados->query($query);
					}
				}
				
		 		// Cancela Ponto
				$query = "UPDATE tab_clientes_pontos SET cps_id = 3, clp_publicado = 0 WHERE ass_id = {$_POST["assinatura"]} AND cps_id != 3";
		 		$this->bancodedados->query($query);
				
				// Registra comentário
				$this->registraComentario($id, "<b>Ação</b>: Adicionado", $_SESSION["usuario"]);
				if ($_POST["comentarios"]) {
					$this->registraComentario($id, $_POST["comentarios"], $_SESSION["usuario"]);
				}
				
				if (in_array($dados_assinatura["servico_id"], array(38))) {

					// Gera Comentario Verificação
					if ($_SESSION["qualidade"] && $_GET["verificacao_id"] && array_key_exists($_GET["verificacao_id"], $_SESSION["qualidade"])) {
						for ($i = 0; $i < count($_SESSION[$_GET["verificacao_id"]]); $i++) {
							$signal_formatado[] = $_SESSION[$_GET["verificacao_id"]][$i]["signal"] >= clientes_autenticacoes::MIN_SIGNAL ? "<span style=\"color:green\">{$_SESSION[$_GET["device"]][$i]["signal"]}</span>" : "<span style=\"color:red\">{$_SESSION[$_GET["device"]][$i]["signal"]}</span>";
							$ccq_formatado[] = $_SESSION[$_GET["verificacao_id"]][$i]["ccq"] >= clientes_autenticacoes::MIN_CCQ ? "<span style=\"color:green\">{$_SESSION[$_GET["device"]][$i]["ccq"]}</span>" : "<span style=\"color:red\">{$_SESSION[$_GET["device"]][$i]["ccq"]}</span>";
							$apsignal_formatado[] = $_SESSION[$_GET["verificacao_id"]][$i]["apsignal"] >= clientes_autenticacoes::MIN_SIGNAL ? "<span style=\"color:green\">{$_SESSION[$_GET["device"]][$i]["apsignal"]}</span>" : "<span style=\"color:red\">{$_SESSION[$_GET["device"]][$i]["apsignal"]}</span>";
						}
					
						$signal_formatado = implode(", ", $signal_formatado);
						$apsignal_formatado = implode(", ", $apsignal_formatado);
						$ccq_formatado = implode(", ", $ccq_formatado);
					
						if ($signal_formatado) {
							$comentario_verificacao[] = "<b>Signal</b>: $signal_formatado";
						}
						if ($apsignal_formatado) {
							$comentario_verificacao[] = "<b>AP Signal</b>: $apsignal_formatado";
						}
						if ($ccq_formatado) {
							$comentario_verificacao[] = "<b>CCQ</b>: $ccq_formatado";
						}
						if ($comentario_verificacao) {
							$comentario_verificacao = "<b>Ação</b>: Verificação<br>".implode("<br>", $comentario_verificacao);
							$this->registraComentario($id, $comentario_verificacao, $_SESSION["usuario"]);
						}
						
					}
					
				}
				
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
				
				// Redireciona
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
				
			}
			
		} else {
			
			$this->fechar();
			
		}
		
	}
	
	function geraSenha($comprimento = 8) {
		$caracteres = "abcdefghijklmnopqrstuvwxyz0123456789";
		$senha = "";
		for ($i = 0; $i < $comprimento; $i++) {
		  $senha .= substr($caracteres, rand(0, strlen($caracteres) - 1), 1);
		}
		return $senha;
	}

		
	function geraUsuario($dados_cliente) {
		
		$usuario = $this->limpaUsuario(strtok($dados_cliente["nome"], " ")).".".$dados_cliente["id"];
 	
		$query = "
			SELECT
				MAX(SUBSTRING_INDEX(cau_usuario, '-', -1) + 1)
			FROM
				tab_clientes_autenticacoes
			WHERE
				cau_usuario REGEXP '^$usuario(\-[0-9]+)?$'";
	 	$query = $this->bancodedados->query($query);
	 	$digito = $this->bancodedados->resultado($query, 0, 0);
 		return $usuario.($digito > 0 ? "-".sprintf("%02s", $digito) : "");
	}
	
	function limpaUsuario($nome) {
		
		$nome = strtr(mb_strtolower($nome, "iso-8859-1"),
			"ñÑçÇáàâãäªÁÀÂÃÄéèêëÉÈÊËíìîïÍÌÎÏóòôõö°ºÓÒÔÕÖúùûüÚÙÛÜ",
			"nnccaaaaaaaaaaaeeeeeeeeiiiiiiiioooooooooooouuuuuuuu");
		
		return preg_replace("/[^a-z0-9]/", "", $nome);
		
	}

	function buscaDadosSSID($ssid_id) {
		
		$query = "
			SELECT
				tab_servidores_ssids.ssi_id AS id,
				tab_servidores_ssids.ssi_nome AS ssid,
				tab_servidores_ssids.sst_id AS tipo_id,
				tab_servidores_interfaces.sei_id AS interface_id,
				tab_servidores_interfaces.ser_id AS servidor_id,
				(SELECT COUNT(*) FROM tab_clientes_autenticacoes WHERE tab_clientes_autenticacoes.ssi_id = tab_servidores_ssids.ssi_id AND tab_clientes_autenticacoes.cas_id = 1) AS clientes
			FROM
				tab_servidores_ssids
				INNER JOIN tab_servidores_interfaces ON (tab_servidores_ssids.sei_id = tab_servidores_interfaces.sei_id) 
			WHERE
				ssi_id = $ssid_id";
				
	 	$query = $this->bancodedados->query($query);
		
	 	return $this->bancodedados->resultadoArray($query);
		
	}

	function buscaServidorInterface($interface_id) {
		
		$query = "
			SELECT
				ser_id
			FROM
				tab_servidores_interfaces 
			WHERE
				sei_id = $interface_id";
				
	 	$query = $this->bancodedados->query($query);
		
	 	return $this->bancodedados->resultado($query);
		
	}
	
	###################
	### Alterar POP ###
	###################

	function alterar_pop() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			if (!$_GET["device"] || !$_SESSION[$_GET["device"]]) {
				$this->conteudo .= $this->carregaArquivo("clientes_autenticacoes.adicionar.verificacao.inc.php");
				$acoes = '<td bgcolor="#505050"><strong>&nbsp;Alterar POP&nbsp;</strong></td>';
				$this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
			 	$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
			 	$this->conteudo = str_replace('[autenticacao_id]', $this->dados_item["id"], $this->conteudo);
			 	$this->conteudo = str_replace('[cliente_nome]', $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace('[ssid]', $this->geraSelect($this->buscaInterfaceSSIDs(), $this->dados_item["ssid_id"], true, "ssid", "100%", "mudaSSID(this.value)"), $this->conteudo);
				$this->conteudo = str_replace('[ssid_ip]', $this->dados_item["ssid_ip"] ? "<a href='http://{$this->dados_item["ssid_ip"]}' target='_blank' >{$this->dados_item["ssid_ip"]}</a>" : "", $this->conteudo);

				$dados_ssid = $this->buscaDadosSSID($this->dados_item["ssid_id"]);
			 	$this->conteudo = str_replace('[clientes_count]', $dados_ssid["clientes"], $this->conteudo);

				if ($this->dados_item["interface_id"] && (!$this->dados_item["equipamento_ip"] || !$this->ehIpPoolInterface($this->dados_item["interface_id"], $this->dados_item["equipamento_ip"]))) {
					$this->dados_item["equipamento_ip"] = $this->buscaIpPoolInterface($this->dados_item["interface_id"]);
				}
				$this->conteudo = str_replace('[ip]', $this->dados_item["interface_id"] ? "<a href='http://{$this->dados_item["equipamento_ip"]}' target='_blank' id='ip_link' >{$this->dados_item["equipamento_ip"]}</a>" : "", $this->conteudo);
				
				$botoes[] = '<input type="button" name="verificar" id="verificar" class="buttonAzulEscuroBranco11'.($this->dados_item["interface_id"] ? '' : 'disabled').'" onclick="verificarConexao()" value="Verificar Conexão"'.($this->dados_item["interface_id"] ? '' : ' disabled').'>';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
			} else {
				$autorizado = true;
				
	  		for ($i = 0; $i < count($_SESSION[$_GET["device"]]); $i++) {
	  			// Cliente
  			  if (array_key_exists("signal", $_SESSION[$_GET["device"]][$i])) {
	  				$autorizado = $autorizado && $_SESSION[$_GET["device"]][$i]["signal"] >= clientes_autenticacoes::MIN_SIGNAL;
		  			$signal_formatado[] = $_SESSION[$_GET["device"]][$i]["signal"] >= clientes_autenticacoes::MIN_SIGNAL ? "[verde]{$_SESSION[$_GET["device"]][$i]["signal"]}[/verde]" : "[vermelho]{$_SESSION[$_GET["device"]][$i]["signal"]}[/vermelho]";
  			  }
  			  if (array_key_exists("snr", $_SESSION[$_GET["device"]][$i])) {
	  				$autorizado = $autorizado && $_SESSION[$_GET["device"]][$i]["snr"] >= clientes_autenticacoes::MIN_SNR;
		  			$snr_formatado[] = $_SESSION[$_GET["device"]][$i]["snr"] >= clientes_autenticacoes::MIN_SNR ? "[verde]{$_SESSION[$_GET["device"]][$i]["snr"]}[/verde]" : "[vermelho]{$_SESSION[$_GET["device"]][$i]["snr"]}[/vermelho]";
  			  }
  			  if (array_key_exists("ccq", $_SESSION[$_GET["device"]][$i])) {
	  				$autorizado = $autorizado && $_SESSION[$_GET["device"]][$i]["ccq"] >= clientes_autenticacoes::MIN_CCQ;
		  			$ccq_formatado[] = $_SESSION[$_GET["device"]][$i]["ccq"] >= clientes_autenticacoes::MIN_CCQ ? "[verde]{$_SESSION[$_GET["device"]][$i]["ccq"]}[/verde]" : "[vermelho]{$_SESSION[$_GET["device"]][$i]["ccq"]}[/vermelho]";
  			  }
					// AP
  			  if (array_key_exists("apsignal", $_SESSION[$_GET["device"]][$i])) {
	  				$autorizado = $autorizado && $_SESSION[$_GET["device"]][$i]["apsignal"] >= clientes_autenticacoes::MIN_SIGNAL;
	  				$apsignal_formatado[] = $_SESSION[$_GET["device"]][$i]["apsignal"] >= clientes_autenticacoes::MIN_SIGNAL ? "[verde]{$_SESSION[$_GET["device"]][$i]["apsignal"]}[/verde]" : "[vermelho]{$_SESSION[$_GET["device"]][$i]["apsignal"]}[/vermelho]";
  			  } elseif (array_key_exists("apsignal1", $_SESSION[$_GET["device"]][$i]) && array_key_exists("apsignal2", $_SESSION[$_GET["device"]][$i])) {
	  				$autorizado = $autorizado && $_SESSION[$_GET["device"]][$i]["apsignal1"] >= clientes_autenticacoes::MIN_SIGNAL && $_SESSION[$_GET["device"]][$i]["apsignal2"] >= clientes_autenticacoes::MIN_SIGNAL;
	  				$apsignal_formatado[] = ($_SESSION[$_GET["device"]][$i]["apsignal1"] >= clientes_autenticacoes::MIN_SIGNAL ? "[verde]{$_SESSION[$_GET["device"]][$i]["apsignal1"]}[/verde]" : "[vermelho]{$_SESSION[$_GET["device"]][$i]["apsignal1"]}[/vermelho]")."/".($_SESSION[$_GET["device"]][$i]["apsignal2"] >= clientes_autenticacoes::MIN_SIGNAL ? "[verde]{$_SESSION[$_GET["device"]][$i]["apsignal2"]}[/verde]" : "[vermelho]{$_SESSION[$_GET["device"]][$i]["apsignal2"]}[/vermelho]");
  			  }
	  			if (array_key_exists("apsnr", $_SESSION[$_GET["device"]][$i])) {
	  		  	$autorizado = $autorizado && $_SESSION[$_GET["device"]][$i]["apsnr"] >= clientes_autenticacoes::MIN_SNR;
		  			$apsnr_formatado[] = ($_SESSION[$_GET["device"]][$i]["apsnr"] >= clientes_autenticacoes::MIN_SNR ? "<span style=\"color:green\">{$_SESSION[$_GET["device"]][$i]["apsnr"]}</span>" : "<span style=\"color:red\">{$_SESSION[$_GET["device"]][$i]["apsnr"]}</span>");
  		  	} elseif (array_key_exists("apsnr1", $_SESSION[$_GET["device"]][$i]) && array_key_exists("apsnr2", $_SESSION[$_GET["device"]][$i])) {
	  				$autorizado = $autorizado && $_SESSION[$_GET["device"]][$i]["apsnr1"] >= clientes_autenticacoes::MIN_SNR && $_SESSION[$_GET["device"]][$i]["apsnr2"] >= clientes_autenticacoes::MIN_SNR;
	  				$apsnr_formatado[] = ($_SESSION[$_GET["device"]][$i]["apsnr1"] >= clientes_autenticacoes::MIN_SNR ? "[verde]{$_SESSION[$_GET["device"]][$i]["apsnr1"]}[/verde]" : "[vermelho]{$_SESSION[$_GET["device"]][$i]["apsnr1"]}[/vermelho]")."/".($_SESSION[$_GET["device"]][$i]["apsnr2"] >= clientes_autenticacoes::MIN_SNR ? "[verde]{$_SESSION[$_GET["device"]][$i]["apsnr2"]}[/verde]" : "[vermelho]{$_SESSION[$_GET["device"]][$i]["apsnr2"]}[/vermelho]");
  			  }
  			  if (array_key_exists("apccq", $_SESSION[$_GET["device"]][$i])) {
	  				$autorizado = $autorizado && $_SESSION[$_GET["device"]][$i]["apccq"] >= clientes_autenticacoes::MIN_CCQ;
	  				$apccq_formatado[] = $_SESSION[$_GET["device"]][$i]["apccq"] >= clientes_autenticacoes::MIN_CCQ ? "[verde]{$_SESSION[$_GET["device"]][$i]["apccq"]}[/verde]" : "[vermelho]{$_SESSION[$_GET["device"]][$i]["apccq"]}[/vermelho]";
  			  }
	  		}
  			// Cliente
	  		if ($signal_formatado) {
		  		$signal_formatado = implode(", ", $signal_formatado);
	  		}
	  		if ($snr_formatado) {
		  		$snr_formatado = implode(", ", $snr_formatado);
	  		}
	  		if ($ccq_formatado) {
		  		$ccq_formatado = implode(", ", $ccq_formatado);
	  		}
	  		// AP
	  		if ($apsignal_formatado) {
		  		$apsignal_formatado = implode(", ", $apsignal_formatado);
	  		}
	  		if ($apsnr_formatado) {
		  		$apsnr_formatado = implode(", ", $apsnr_formatado);
	  		}
	  		if ($apccq_formatado) {
		  		$apccq_formatado = implode(", ", $apccq_formatado);
	  		}
	  		
  			if ($_POST || $autorizado) {
	  			$dados_ssid = $this->buscaDadosSSID($_SESSION[$_GET["device"]][0]["ssid"]);
  				// Atualiza dados autenticação
					$query = "
						UPDATE
							tab_clientes_autenticacoes 
						SET
							ssi_id = {$dados_ssid["id"]},
							sei_id = {$dados_ssid["interface_id"]},
							ser_id = {$dados_ssid["servidor_id"]},
							cau_data_alteracao = CURDATE(),
							cau_equipamento_ip = '{$_SESSION[$_GET["device"]][0]["ip"]}',
							cau_equipamento_modelo = '{$_SESSION[$_GET["device"]][0]["platform"]}',
							cau_equipamento_mac = '{$_SESSION[$_GET["device"]][0]["apmac"]}',
						WHERE
							cau_id = {$_GET["i"]}";
				 	$this->bancodedados->query($query);
				 	// Abre chamado Técnica
				 	if ($_POST) {
			    	$chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
			    	// Gera Dados Chamado
		    		$dados_chamado["solicitante"] = "Sistema (Intranet)";
				    $dados_chamado["categoria"] = 1052454; // Técnico (Diretor)
				    $dados_chamado["prioridade"] = 3;
				    $dados_chamado["tipo"] = 5;
	    			$dados_chamado["sistema"] = true;
				    $dados_chamado["conteudo"] = "Ponto instalado sem padrões mínimos de qualidade.\n\n";
				    $dados_chamado["conteudo"] .= "Dados do equipamento:";
	    			$dados_chamado["conteudo"] .= "[d]";
				    $dados_chamado["conteudo"] .= "[n]Modelo:[/n] {$_SESSION[$_GET["device"]][0]["platform"]}\n";
				    $dados_chamado["conteudo"] .= "[n]Nome:[/n] {$_SESSION[$_GET["device"]][0]["devicename"]}\n";
				    $dados_chamado["conteudo"] .= "[n]MAC:[/n] {$_SESSION[$_GET["device"]][0]["apmac"]}\n";
				    $dados_chamado["conteudo"] .= "[n]SSID:[/n] {$_SESSION[$_GET["device"]][0]["essid"]}";
	    			$dados_chamado["conteudo"] .= "[/d]";
				    $dados_chamado["conteudo"] .= "Qualidade do sinal:";
				    $dados_chamado["conteudo"] .= "[d]";
				    if ($signal_formatado) $dados_chamado["conteudo"] .= "[n]Signal:[/n] $signal_formatado\n";
				    if ($snr_formatado) $dados_chamado["conteudo"] .= "[n]SNR:[/n] $snr_formatado\n";
				    if ($ccq_formatado) $dados_chamado["conteudo"] .= "[n]CCQ:[/n] $ccq_formatado";
				    if ($apsignal_formatado) $dados_chamado["conteudo"] .= "[n]AP Signal:[/n] $apsignal_formatado\n";
				    if ($apsnr_formatado) $dados_chamado["conteudo"] .= "[n]AP SNR:[/n] $apsnr_formatado\n";
				    if ($apccq_formatado) $dados_chamado["conteudo"] .= "[n]AP CCQ:[/n] $apccq_formatado\n";
				    $dados_chamado["conteudo"] .= "[/d]";
				    $dados_chamado["conteudo"] .= "Técnico:";
				    $dados_chamado["conteudo"] .= "[d]{$_POST["tecnico"]}[/d]";
				    $dados_chamado["conteudo"] .= "Questionário:\n\n";
				    $dados_chamado["conteudo"] .= "[n]Pergunta 1 - Testou com um equipamento de maior potência?[/n]";
				    $dados_chamado["conteudo"] .= "[d]{$_POST["pergunta_1"]}[/d]";
				    $dados_chamado["conteudo"] .= "[n]Pergunta 2 - Se não testou com um equipamento melhor, por que não?[/n]";
				    $dados_chamado["conteudo"] .= "[d]{$_POST["pergunta_2"]}[/d]";
				    $dados_chamado["conteudo"] .= "[n]Pergunta 3 - Que tipo de equipamento será necessário para este cliente?[/n]";
				    $dados_chamado["conteudo"] .= "[d]{$_POST["pergunta_3"]}[/d]";
				    $dados_chamado["conteudo"] .= "[n]Pergunta 4 - Há algum tipo de problema com a visada para o POP?[/n]";
				    $dados_chamado["conteudo"] .= "[d]{$_POST["pergunta_4"]}[/d]";
				    $dados_chamado["conteudo"] .= "[n]Pergunta 5 - Tem visada para algum outro POP?[/n]";
				    $dados_chamado["conteudo"] .= "[d]{$_POST["pergunta_5"]}[/d]";
	    			// Adiciona Chamado
				    $chamado_id = $chamados->adicionarChamado($this->dados_item["cliente_id"], null, $dados_chamado);
				    $dados["responsavel_fechamento"] = "4,4,12";
				    $chamados->registraEtapa($chamados->buscaDadosChamado($chamado_id, true), 16, $dados);
				 	}
					// Registra Atividade
					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
					// Redireciona
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
  			} else {
					$this->conteudo .= $this->carregaArquivo("clientes_autenticacoes.erro.verificacao.inc.php");
					$this->conteudo = str_replace('[cliente_nome]', $this->dados_item["cliente_nome"], $this->conteudo);
					$this->conteudo = str_replace('[tecnico]', $this->geraSelect($this->buscaTecnicos(), null, false, "tecnico", "100%"), $this->conteudo);
					$botoes[] = '<input type="submit" name="continuar" class="buttonAzulEscuroBranco11" value="Continuar">';
					$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
					$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
					$this->conteudo = str_replace('[action]', "?m=clientes_autenticacoes&a=alterar_pop&i={$_GET["i"]}&device={$_GET["device"]}", $this->conteudo);
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
			
			$this->conteudo .= $this->carregaArquivo("clientes_autenticacoes.ver.inc.php");
				
			$this->conteudo = str_replace("[autenticacao_id]", $this->dados_item["id"], $this->conteudo);
			$this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
			$this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
			$this->conteudo = str_replace("[assinatura]", $this->dados_item["assinatura"], $this->conteudo);
			$this->conteudo = str_replace("[velocidade]", $this->dados_item["velocidade"], $this->conteudo);
			$this->conteudo = str_replace("[observacao]", $this->dados_item["observacao"], $this->conteudo);
			$this->conteudo = str_replace('[autenticador]', $this->dados_item["autenticador"], $this->conteudo);
			$this->conteudo = str_replace("[situacao]", $this->buscaStatusAutenticacao($this->dados_item["id"]), $this->conteudo);
			
			$this->conteudo = str_replace("[usuario]", $this->dados_item["usuario"], $this->conteudo);
			$this->conteudo = str_replace("[senha]", $this->dados_item["senha"], $this->conteudo);
			
			if ($this->dados_item["condominio_id"] && $this->dados_item["condominio_instalacao_tipo_id"] == 2) {
				
				// Olé Condomínios
				$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
				$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[linha_concentrador_visivel]', 'table-row', $this->conteudo);
				$this->conteudo = str_replace('[concentrador]', $this->buscaServidor($this->dados_item["servidor_id"]), $this->conteudo);
				$this->conteudo = str_replace('[linha_endereco_ip_visivel]', in_array($this->dados_item["servico_id"], array(3, 4, 9, 12, 30, 34, 35, 36, 39)) ? 'table-row' : 'none', $this->conteudo);
				$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
				
			} else if ($this->dados_item["cliente_id"] == 107681 || in_array($this->dados_item["servico_id"], array(3, 4, 9, 12, 30, 34, 35, 36, 39))) {
				
				// Olé Empresas/PME/Monitoramento
				$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
				$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[linha_concentrador_visivel]', 'table-row', $this->conteudo);
				$this->conteudo = str_replace('[concentrador]', $this->buscaServidor($this->dados_item["servidor_id"]), $this->conteudo);
				$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'table-row', $this->conteudo);
				$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
				
			} else if (in_array($this->dados_item["servico_id"], array(5))) {
				
				// Olé Casa
				$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
				$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[linha_concentrador_visivel]', 'table-row', $this->conteudo);
				$this->conteudo = str_replace('[concentrador]', $this->buscaServidor($this->dados_item["servidor_id"]), $this->conteudo);
				$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
				
			} else if (in_array($this->dados_item["servico_id"], array(28))) {
				
				// Olé Residencial
				$this->conteudo = str_replace('[linha_ssid_visivel]', 'table-row', $this->conteudo);
				$this->conteudo = str_replace('[ssid]', $this->buscaSSID($this->dados_item["ssid_id"]), $this->conteudo);
				$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'table-row', $this->conteudo);
				$this->conteudo = str_replace('[ap_ip]', "<a href=\"http://{$this->dados_item["ssid_ip"]}\" target=\"_blank\">{$this->dados_item["ssid_ip"]}</a>", $this->conteudo);
				$this->conteudo = str_replace('[linha_concentrador_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[concentrador]', '', $this->conteudo);
				$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[equipamento_visivel]', 'table-row-group', $this->conteudo);
				
				$this->conteudo = str_replace("[equipamento_modelo]", $this->dados_item["equipamento_modelo"], $this->conteudo);
				$this->conteudo = str_replace("[equipamento_mac]", $this->dados_item["equipamento_mac"], $this->conteudo);
				$this->conteudo = str_replace("[equipamento_ip]", $this->dados_item["equipamento_ip"] ? "<a href='http://{$this->dados_item["equipamento_ip"]}' target='_blank' >{$this->dados_item["equipamento_ip"]}</a>" : "", $this->conteudo);
	
	
	
	
	
				$this->conteudo = str_replace("[equipamento_serie]", $this->dados_item["equipamento_serie"], $this->conteudo);
			} else if (in_array($this->dados_item["servico_id"], array(38))) {
				
				// Olé Fibra
				$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
				$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[linha_concentrador_visivel]', 'table-row', $this->conteudo);
				$this->conteudo = str_replace('[concentrador]', $this->buscaServidor($this->dados_item["servidor_id"]), $this->conteudo);
				$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
				
			} else {
				
				$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
				$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[linha_concentrador_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[concentrador]', '', $this->conteudo);
				$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'none', $this->conteudo);
				$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
				
			}
			
			$this->conteudo = str_replace("[endereco_ip]", $this->dados_item["endereco_ip"], $this->conteudo);

			$this->conteudo = str_replace('[pool]', $this->dados_item["pool"], $this->conteudo);
			$this->conteudo = str_replace("[linha_pool_visivel]", in_array($this->dados_item["servico_id"], array(3, 36)) ? "none" : "table-row", $this->conteudo);

			$this->conteudo = str_replace("[ajustes_programados]", $this->buscaAjustes($_GET["i"]), $this->conteudo);
			$this->conteudo = str_replace("[lista_comentarios]", $this->geraComentariosListagem($_GET["i"]), $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);

			// Botões
			if ($this->ehPublicavel()) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:57" value="Publicar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&i='.$_GET["i"].'\';">';
			if ($this->dados_item["status_id"] == 2 && $this->permissoes[$this->dados_modulo["nome"]]["ativar"]) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" style="width:65px" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$_GET["i"].'\';">';
			if ($this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["desativar"]) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" style="width:65px" value="Desativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$_GET["i"].'\';">';
			if ($this->dados_item["status_id"] == 3 && $this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" style="width:65px" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$_GET["i"].'\';">';
			if ($this->dados_item["status_id"] != 3 && $this->permissoes[$this->dados_modulo["nome"]]["cancelar"]) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" style="width:60px" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$_GET["i"].'\';">';
			if ($this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["liberar_tecnico"]) $botoes[] = "<input type=\"button\" name=\"liberar_tecnico\" class=\"buttonAzulEscuroBranco11\" style=\"width:100px\" value=\"Liberar Técnico\" onClick=\"window.location='?m={$this->dados_modulo["nome"]}&a=liberar_tecnico&i={$_GET["i"]}';\">";
			
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" style="width:50px" value="Fechar" onclick="window.close()">';
			
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].$this->janela, $this->conteudo);
			
		} else {
			
			$this->fechar();
			
		}
		
	}

	##############
	### Editar ###
	##############

	function editar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item["status_id"] == 3) {
			
			$this->ver();
			
		} elseif ($this->dados_item) {
			
			if (!$_POST) {
				
				$this->conteudo .= $this->carregaArquivo("clientes_autenticacoes.editar.inc.php");
				
				$this->conteudo = str_replace("[autenticacao_id]", $this->dados_item["id"], $this->conteudo);
				$this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
				$this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace("[assinatura]", $this->dados_item["assinatura"], $this->conteudo);
				$this->conteudo = str_replace("[velocidade]", $this->dados_item["velocidade"], $this->conteudo);
				$this->conteudo = str_replace("[observacao]", $this->dados_item["observacao"], $this->conteudo);
				$this->conteudo = str_replace('[autenticador]', $this->geraSelect($this->buscaTiposAutenticadores($this->dados_item["autenticador"] ? $this->dados_item["autenticador"] : "Rádio"), $this->dados_item["autenticador"], true, "autenticador", "100%"), $this->conteudo);
				$this->conteudo = str_replace("[situacao]", $this->buscaStatusAutenticacao($this->dados_item["id"]), $this->conteudo);
				$this->conteudo = str_replace('[tecnico]', $this->geraSelect($this->buscaTecnicos(), null, true, "tecnico", "100%"), $this->conteudo);
				
				$this->conteudo = str_replace("[usuario]", $this->dados_item["usuario"], $this->conteudo);
				$this->conteudo = str_replace("[senha]", $this->dados_item["senha"], $this->conteudo);

				
				if ($this->dados_item["condominio_id"] && $this->dados_item["condominio_instalacao_tipo_id"] == 2) {
					
					// Olé Condomínios
					$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
					$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[linha_concentrador_visivel]', 'table-row', $this->conteudo);
					$this->conteudo = str_replace('[concentrador]', $this->buscaServidor($this->dados_item["servidor_id"]), $this->conteudo);
					$this->conteudo = str_replace('[linha_endereco_ip_visivel]', in_array($this->dados_item["servico_id"], array(3, 4, 9, 12, 30, 34, 35, 36, 39)) ? 'table-row' : 'none', $this->conteudo);
					$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
					
				} else if ($this->dados_item["cliente_id"] == 107681 || in_array($this->dados_item["servico_id"], array(3, 4, 9, 12, 30, 34, 35, 36, 39))) {
					
					// Olé Empresas/PME/Monitoramento
					$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
					$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[linha_concentrador_visivel]', 'table-row', $this->conteudo);
					$this->conteudo = str_replace('[concentrador]', $this->geraSelect($this->buscaConcentradores($this->dados_item["servidor_id"]), $this->dados_item["servidor_id"], true, "concentrador", "100%"), $this->conteudo);
					$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'table-row', $this->conteudo);
					$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
					
				} else if (in_array($this->dados_item["servico_id"], array(5))) {
					
					// Olé Casa
					$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
					$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[linha_concentrador_visivel]', 'table-row', $this->conteudo);
					$this->conteudo = str_replace('[concentrador]', $this->geraSelect($this->buscaConcentradores($this->dados_item["servidor_id"]), $this->dados_item["servidor_id"], true, "concentrador", "100%"), $this->conteudo);
					$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
					
				} else if (in_array($this->dados_item["servico_id"], array(28))) {
					
					// Olé Residencial
					$this->conteudo = str_replace('[linha_ssid_visivel]', 'table-row', $this->conteudo);
					$this->conteudo = str_replace('[ssid]', $this->geraSelect($this->buscaInterfaceSSIDs($this->dados_item["ssid_id"]), $this->dados_item["ssid_id"], true, "ssid", "100%", "mudaSSID(this.value)"), $this->conteudo);
					$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'table-row', $this->conteudo);
					$this->conteudo = str_replace('[ap_ip]', "<a href=\"http://{$this->dados_item["ssid_ip"]}\" target=\"_blank\">{$this->dados_item["ssid_ip"]}</a>", $this->conteudo);
					$this->conteudo = str_replace('[linha_concentrador_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[concentrador]', '', $this->conteudo);
					$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[equipamento_visivel]', 'table-row-group', $this->conteudo);
									
					$this->conteudo = str_replace("[equipamento_modelo]", $this->dados_item["equipamento_modelo"], $this->conteudo);
					$this->conteudo = str_replace("[equipamento_mac]", $this->dados_item["equipamento_mac"], $this->conteudo);
					if ($this->dados_item["equipamento_ip"]) {
						$dados_ip = $this->buscaIpPoolInterface($this->dados_item["interface_id"], $this->dados_item["equipamento_ip"]);
						$this->conteudo = str_replace("[equipamento_ip]", "<a href='http://{$dados_ip["ip"]}' id='ip_link' target='_blank' >{$dados_ip["ip"]}</a> (Máscara: {$dados_ip["mascara"]}, Gateway: {$dados_ip["gateway"]})", $this->conteudo);
					} else {
						$this->conteudo = str_replace("[equipamento_ip]", "", $this->conteudo);
					}
					$this->conteudo = str_replace("[equipamento_serie]", $this->dados_item["equipamento_serie"], $this->conteudo);

				} else if (in_array($this->dados_item["servico_id"], array(38))) {
					
					// Olé Fibra
					$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
					$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[linha_concentrador_visivel]', 'table-row', $this->conteudo);
					$this->conteudo = str_replace('[concentrador]', $this->geraSelect($this->buscaConcentradores($this->dados_item["servidor_id"]), $this->dados_item["servidor_id"], true, "concentrador", "100%"), $this->conteudo);
					$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
					
				} else {
					
					$this->conteudo = str_replace('[linha_ssid_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[ssid]', '', $this->conteudo);
					$this->conteudo = str_replace('[linha_ap_ip_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[linha_concentrador_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[concentrador]', '', $this->conteudo);
					$this->conteudo = str_replace('[linha_endereco_ip_visivel]', 'none', $this->conteudo);
					$this->conteudo = str_replace('[equipamento_visivel]', 'none', $this->conteudo);
					
				}

				$this->conteudo = str_replace("[endereco_ip_1]", $this->dados_item["endereco_ip_separado"][0], $this->conteudo);
				$this->conteudo = str_replace("[endereco_ip_2]", $this->dados_item["endereco_ip_separado"][1], $this->conteudo);
				$this->conteudo = str_replace("[endereco_ip_3]", $this->dados_item["endereco_ip_separado"][2], $this->conteudo);
				$this->conteudo = str_replace("[endereco_ip_4]", $this->dados_item["endereco_ip_separado"][3], $this->conteudo);

				if ($this->permissoes[$this->dados_modulo["nome"]]["editar_pool"]) {
					$this->conteudo = str_replace('[pool]', $this->geraSelect(array("pppoe" => "pppoe", "liberados" => "liberados"), $this->dados_item["pool"], true, "pool"), $this->conteudo);
				} else {
					$this->conteudo = str_replace('[pool]', $this->dados_item["pool"], $this->conteudo);
				}
				//$this->conteudo = str_replace("[linha_pool_visivel]", in_array($this->dados_item["servico_id"], array(3, 36)) ? "none" : "table-row", $this->conteudo);
				$this->conteudo = str_replace("[linha_pool_visivel]", "table-row", $this->conteudo);

				$this->conteudo = str_replace("[ajustes_programados]", $this->buscaAjustes($_GET["i"]), $this->conteudo);
				$this->conteudo = str_replace("[lista_comentarios]", $this->geraComentariosListagem($_GET["i"]), $this->conteudo);
				$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
	
				// Botões
				if ($this->ehPublicavel()) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:57" value="Publicar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&i='.$_GET["i"].'\';">';
				if ($this->dados_item["status_id"] == 2 && $this->permissoes[$this->dados_modulo["nome"]]["ativar"]) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" style="width:65px" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$_GET["i"].'\';">';
				if ($this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["desativar"]) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" style="width:65px" value="Desativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$_GET["i"].'\';">';
				if ($this->dados_item["status_id"] == 3 && $this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" style="width:65px" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$_GET["i"].'\';">';
				if ($this->dados_item["status_id"] != 3 && $this->permissoes[$this->dados_modulo["nome"]]["cancelar"]) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" style="width:60px" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$_GET["i"].'\';">';
				if ($this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["liberar_tecnico"]) $botoes[] = "<input type=\"button\" name=\"liberar_tecnico\" class=\"buttonAzulEscuroBranco11\" style=\"width:100px\" value=\"Liberar Técnico\" onClick=\"window.location='?m={$this->dados_modulo["nome"]}&a=liberar_tecnico&i={$_GET["i"]}';\">";
				
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" style="width:45px" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" style="width:55px" value="Limpar" onclick="mudaAssinatura(0)">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" style="width:50px" value="Fechar" onclick="window.close()">';
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].$this->janela, $this->conteudo);

			} else {

				$alteracoes = array();
				$publicado = 1;

				if ($this->dados_item["condominio_id"] && $this->dados_item["condominio_instalacao_tipo_id"] == 2) {
					
					// Olé Condomínios
					if (in_array($this->dados_item["servico_id"], array(3, 4, 9, 12, 30, 34, 35, 36, 39)) && $_POST["endereco_ip_1"] && $_POST["endereco_ip_2"] && $_POST["endereco_ip_3"] && $_POST["endereco_ip_4"]) {
						$_POST["endereco_ip"] = "{$_POST["endereco_ip_1"]}.{$_POST["endereco_ip_2"]}.{$_POST["endereco_ip_3"]}.{$_POST["endereco_ip_4"]}";
					} else {
						$_POST["endereco_ip"] = null;
					}
 					$_POST["ssid"] = null;
 					$_POST["interface"] = $this->dados_item["interface_id"] ? $this->dados_item["interface_id"] : null;
 					$_POST["servidor"] = $this->dados_item["servidor_id"] ? $this->dados_item["servidor_id"] : null;
				
					// Dados Equipamento
					$_POST["equipamento_ip"] = null;
					$_POST["equipamento_modelo"] = null;
					$_POST["equipamento_mac"] = null;
					$_POST["equipamento_serie"] = null;

				} else if ($this->dados_item["cliente_id"] == 107681 || in_array($this->dados_item["servico_id"], array(3, 4, 9, 12, 30, 34, 35, 36, 39))) {
					
					// Olé Empresas/PME/Monitoramento
					if ($_POST["endereco_ip_1"] && $_POST["endereco_ip_2"] && $_POST["endereco_ip_3"] && $_POST["endereco_ip_4"]) {
						$_POST["endereco_ip"] = "{$_POST["endereco_ip_1"]}.{$_POST["endereco_ip_2"]}.{$_POST["endereco_ip_3"]}.{$_POST["endereco_ip_4"]}";
					} else {
						$_POST["endereco_ip"] = null;
					}
 					$_POST["ssid"] = null;
 					$_POST["interface"] = null;
 					$_POST["servidor"] = $_POST["concentrador"] ? $_POST["concentrador"] : null;
				
					// Dados Equipamento
					$_POST["equipamento_ip"] = null;
					$_POST["equipamento_modelo"] = null;
					$_POST["equipamento_mac"] = null;
					$_POST["equipamento_serie"] = null;

				} else if (in_array($this->dados_item["servico_id"], array(5))) {
					
					// Olé Casa
					$_POST["endereco_ip"] = null;
 					$_POST["ssid"] = null;
 					$_POST["interface"] = null;
 					$_POST["servidor"] = $_POST["concentrador"] ? $_POST["concentrador"] : null;
									
					// Dados Equipamento
					$_POST["equipamento_ip"] = null;
					$_POST["equipamento_modelo"] = null;
					$_POST["equipamento_mac"] = null;
					$_POST["equipamento_serie"] = null;

				} else if (in_array($this->dados_item["servico_id"], array(28))) {
					
					// Olé Residencial
 					$ssid = $this->buscaDadosSSID($_POST["ssid"]);
					$_POST["endereco_ip"] = null;
					$_POST["ssid"] = $_POST["ssid"] ? $_POST["ssid"] : null;
 					$_POST["interface"] = $ssid ? $ssid["interface_id"] : null;
 					$_POST["servidor"] = $ssid ? $ssid["servidor_id"] : null;						
						
					// Dados Equipamento
					if ($_SESSION["equipamento"] && $_POST["verificacao_id"] && array_key_exists($_POST["verificacao_id"], $_SESSION["equipamento"])) {
						$_POST["equipamento_modelo"] = $_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_modelo"] ? $_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_modelo"] : null;
						$_POST["equipamento_ip"] = $_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_ip"] ? $_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_ip"] : null;
						$_POST["equipamento_mac"] = $_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_mac"] ? $_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_mac"] : null;
					} else {
						$_POST["equipamento_modelo"] = $this->dados_item["equipamento_modelo"];
						$_POST["equipamento_ip"] = $this->dados_item["equipamento_ip"];
						$_POST["equipamento_mac"] = $this->dados_item["equipamento_mac"];
					}
					$_POST["equipamento_serie"] = $_POST["equipamento_serie"] ? $_POST["equipamento_serie"] : null;

				} else if (in_array($this->dados_item["servico_id"], array(38))) {
					
					// Olé Fibra
					$_POST["endereco_ip"] = null;
 					$_POST["ssid"] = null;
 					$_POST["interface"] = null;
 					$_POST["servidor"] = $_POST["concentrador"] ? $_POST["concentrador"] : null;
									
					// Dados Equipamento
					$_POST["equipamento_modelo"] = null;
					$_POST["equipamento_ip"] = null;
					$_POST["equipamento_mac"] = null;
					$_POST["equipamento_serie"] = null;

				} else {
					
					$_POST["endereco_ip"] = null;
 					$_POST["ssid"] = null;
 					$_POST["interface"] = null;
 					$_POST["servidor"] = null;
									
					// Dados Equipamento
					$_POST["equipamento_ip"] = null;
					$_POST["equipamento_modelo"] = null;
					$_POST["equipamento_mac"] = null;
					$_POST["equipamento_serie"] = null;

				}
				
				if ($_POST["senha"] != $this->dados_item["senha"]) {
					$publicado = 0;
					$alteracoes[] = "<b>Alteração de senha:</b> de '{$this->dados_item["senha"]}' para '{$_POST["senha"]}'.";
				}
				
				if (!$_POST["pool"] || !$this->permissoes[$this->dados_modulo["nome"]]["editar_pool"]) {
					$_POST["pool"] = $this->dados_item["pool"];
				}				
				if ($_POST["pool"] != $this->dados_item["pool"]) {
					$publicado = 0;
					$alteracoes[] = "<b>Alteração de pool:</b> de '{$this->dados_item["pool"]}' para '{$_POST["pool"]}'.";
				}
				
				$_POST["observacao"] = trim($_POST["observacao"]);
				if ($_POST["observacao"] != $this->dados_item["observacao"]) {
					$alteracoes[] = "<b>Alteração de observação:</b> de '{$this->dados_item["observacao"]}' para '{$_POST["observacao"]}'.";
				}
				
				if ($_POST["autenticador"] != $this->dados_item["autenticador"]) {
					$alteracoes[] = "<b>Alteração de autenticador:</b> de '{$this->dados_item["autenticador"]}' para '{$_POST["autenticador"]}'.";
				}
				
				if ($_POST["endereco_ip"] != $this->dados_item["endereco_ip"]) {
					$publicado = 0;
					$alteracoes[] = "<b>Alteração de IP:</b> de '{$this->dados_item["endereco_ip"]}' para '{$_POST["endereco_ip"]}'.";
				}
				
				if ($_POST["ssid"] != $this->dados_item["ssid_id"]) {
					$publicado = 0;
					$alteracoes[] = "<b>Alteração de SSID:</b> de '{$this->buscaSSID($this->dados_item["ssid_id"])}' para '{$this->buscaSSID($_POST["ssid"])}'.";
				}
				
				if ($_POST["interface"] != $this->dados_item["interface_id"]) {
					$publicado = 0;
					$alteracoes[] = "<b>Alteração de interface:</b> de '{$this->buscaInterface($this->dados_item["interface_id"])}' para '{$this->buscaInterface($_POST["interface"])}'.";
				}
				
				if ($_POST["servidor"] != $this->dados_item["servidor_id"]) {
					$publicado = 0;
					$alteracoes[] = "<b>Alteração de concentrador:</b> de '{$this->buscaServidor($this->dados_item["servidor_id"])}' para '{$this->buscaServidor($_POST["servidor"])}'.";
				}
									
				if ($_POST["equipamento_modelo"] != $this->dados_item["equipamento_modelo"]) {
					$alteracoes[] = "<b>Alteração de modelo do equipamento:</b> de '{$this->dados_item["equipamento_modelo"]}' para '{$_POST["equipamento_modelo"]}'.";
				}
				
				if ($_POST["equipamento_ip"] != $this->dados_item["equipamento_ip"]) {
					$alteracoes[] = "<b>Alteração de IP do equipamento:</b> de '{$this->dados_item["equipamento_ip"]}' para '{$_POST["equipamento_ip"]}'.";
				}
				
				if ($_POST["equipamento_mac"] != $this->dados_item["equipamento_mac"]) {
					$publicado = 0;
					$alteracoes[] = "<b>Alteração de MAC do equipamento:</b> de '{$this->dados_item["equipamento_mac"]}' para '{$_POST["equipamento_mac"]}'.";
				}
				
				if ($_POST["equipamento_serie"] != $this->dados_item["equipamento_serie"]) {
					$alteracoes[] = "<b>Alteração de número de série:</b> de '{$this->dados_item["equipamento_serie"]}' para '{$_POST["equipamento_serie"]}'.";
				}

				$_POST["endereco_ip"] = $_POST["endereco_ip"] ? "'{$_POST["endereco_ip"]}'" : "NULL";
				$_POST["ssid"] = $_POST["ssid"] ? "'{$_POST["ssid"]}'" : "NULL";
				$_POST["interface"] = $_POST["interface"] ? "'{$_POST["interface"]}'" : "NULL";
				$_POST["servidor"] = $_POST["servidor"] ? "'{$_POST["servidor"]}'" : "NULL";
				$_POST["equipamento_modelo"] = $_POST["equipamento_modelo"] ? "'{$_POST["equipamento_modelo"]}'" : "NULL";
				$_POST["equipamento_ip"] = $_POST["equipamento_ip"] ? "'{$_POST["equipamento_ip"]}'" : "NULL";
				$_POST["equipamento_mac"] = $_POST["equipamento_mac"] ? "'{$_POST["equipamento_mac"]}'" : "NULL";
				$_POST["equipamento_serie"] = $_POST["equipamento_serie"] ? "'{$_POST["equipamento_serie"]}'" : "NULL";
				
				$query = "
					UPDATE
						tab_clientes_autenticacoes
					SET
						cau_senha = '{$_POST["senha"]}',
						cau_autenticador = '{$_POST["autenticador"]}',
						cau_pool = '{$_POST["pool"]}',
						cau_publicado = $publicado,
						cau_comentario = '{$_POST["observacao"]}',
						cau_equipamento_modelo = {$_POST["equipamento_modelo"]},
						cau_equipamento_ip = {$_POST["equipamento_ip"]},
						cau_equipamento_mac = {$_POST["equipamento_mac"]},
						cau_equipamento_serie = {$_POST["equipamento_serie"]},
						cau_endereco_ip = {$_POST["endereco_ip"]},
						ssi_id = {$_POST["ssid"]},
						sei_id = {$_POST["interface"]},
						ser_id = {$_POST["servidor"]}
					WHERE
						cau_id = {$_GET["i"]}";
											
				$this->bancodedados->query($query);
				
				// Publica Autenticação
				if (!$publicado) {
					$this->publicarAutenticacao($_GET["i"]);
				}
				
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
				
				// Adicionar comentário
				if ($_POST["comentarios"]) {
					$this->registraComentario($_GET["i"], $_POST["comentarios"], $_SESSION["usuario"]);
				}
								
				if ($alteracoes) {
					$this->registraComentario($_GET["i"], implode("<br>", $alteracoes), $_SESSION["usuario"]);
				}
								
				if ($_POST["reprovado"] && $_POST["verificacao_id"] && array_key_exists($_POST["verificacao_id"], $_SESSION["qualidade"])) {

					$signal_formatado = array();
					$snr_formatado = array();
					$ccq_formatado = array();
					
					$apsignal_formatado = array();
					$apsnr_formatado = array();
					$apccq_formatado = array();
				
					for ($i = 0; $i < count($_SESSION["qualidade"][$_POST["verificacao_id"]]); $i++) {
						// Cliente
						if (array_key_exists("signal", $_SESSION["qualidade"][$_POST["verificacao_id"]][$i])) {
							$signal_formatado[] = $_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["signal"] >= clientes_autenticacoes::MIN_SIGNAL ? "[verde]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["signal"]}[/verde]" : "[vermelho]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["signal"]}[/vermelho]";
						}
						if (array_key_exists("snr", $_SESSION["qualidade"][$_POST["verificacao_id"]][$i])) {
							$snr_formatado[] = $_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["snr"] >= clientes_autenticacoes::MIN_SNR ? "[verde]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["snr"]}[/verde]" : "[vermelho]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["snr"]}[/vermelho]";
						}
						if (array_key_exists("ccq", $_SESSION["qualidade"][$_POST["verificacao_id"]][$i])) {
							$ccq_formatado[] = $_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["ccq"] >= clientes_autenticacoes::MIN_CCQ ? "[verde]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["ccq"]}[/verde]" : "[vermelho]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["ccq"]}[/vermelho]";
						}
						// AP
						if (array_key_exists("apsignal", $_SESSION["qualidade"][$_POST["verificacao_id"]][$i])) {
							$apsignal_formatado[] = $_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsignal"] >= clientes_autenticacoes::MIN_SIGNAL ? "[verde]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsignal"]}[/verde]" : "[vermelho]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsignal"]}[/vermelho]";
						} elseif (array_key_exists("apsignal1", $_SESSION["qualidade"][$_POST["verificacao_id"]][$i]) && array_key_exists("apsignal2", $_SESSION["qualidade"][$_POST["verificacao_id"]][$i])) {
							$apsignal_formatado[] = ($_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsignal1"] >= clientes_autenticacoes::MIN_SIGNAL ? "[verde]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsignal1"]}[/verde]" : "[vermelho]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsignal1"]}[/vermelho]")."/".($_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsignal2"] >= clientes_autenticacoes::MIN_SIGNAL ? "[verde]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsignal2"]}[/verde]" : "[vermelho]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsignal2"]}[/vermelho]");
						}
						if (array_key_exists("apsnr", $_SESSION["qualidade"][$_POST["verificacao_id"]][$i])) {
							$apsnr_formatado[] = ($_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsnr"] >= clientes_autenticacoes::MIN_SNR ? "<span style=\"color:green\">{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsnr"]}</span>" : "<span style=\"color:red\">{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsnr"]}</span>");
						} elseif (array_key_exists("apsnr1", $_SESSION["qualidade"][$_POST["verificacao_id"]][$i]) && array_key_exists("apsnr2", $_SESSION["qualidade"][$_POST["verificacao_id"]][$i])) {
							$apsnr_formatado[] = ($_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsnr1"] >= clientes_autenticacoes::MIN_SNR ? "[verde]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsnr1"]}[/verde]" : "[vermelho]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsnr1"]}[/vermelho]")."/".($_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsnr2"] >= clientes_autenticacoes::MIN_SNR ? "[verde]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsnr2"]}[/verde]" : "[vermelho]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apsnr2"]}[/vermelho]");
						}
						if (array_key_exists("apccq", $_SESSION["qualidade"][$_POST["verificacao_id"]][$i])) {
							$apccq_formatado[] = $_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apccq"] >= clientes_autenticacoes::MIN_CCQ ? "[verde]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apccq"]}[/verde]" : "[vermelho]{$_SESSION["qualidade"][$_POST["verificacao_id"]][$i]["apccq"]}[/vermelho]";
						}
					}
					
					// Cliente
					if ($signal_formatado) {
						$signal_formatado = implode(", ", $signal_formatado);
					}
					if ($snr_formatado) {
						$snr_formatado = implode(", ", $snr_formatado);
					}
					if ($ccq_formatado) {
						$ccq_formatado = implode(", ", $ccq_formatado);
					}
					
					// AP
					if ($apsignal_formatado) {
						$apsignal_formatado = implode(", ", $apsignal_formatado);
					}
					if ($apsnr_formatado) {
						$apsnr_formatado = implode(", ", $apsnr_formatado);
					}
					if ($apccq_formatado) {
						$apccq_formatado = implode(", ", $apccq_formatado);
					}
					
			    	$chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
					
			    	// Gera Dados Chamado
		    		$dados_chamado["solicitante"] = "Sistema (Intranet)";
				    $dados_chamado["categoria"] = 1052454; // Técnico (Diretor)
				    $dados_chamado["prioridade"] = 3;
				    $dados_chamado["tipo"] = 5;
	    			$dados_chamado["sistema"] = true;
				    $dados_chamado["conteudo"] = "Ponto instalado sem padrões mínimos de qualidade.\n\n";
				    $dados_chamado["conteudo"] .= "Dados do equipamento:";
	    			$dados_chamado["conteudo"] .= "[d]";
				    $dados_chamado["conteudo"] .= "[n]Modelo:[/n] {$_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_modelo"]}\n";
				    $dados_chamado["conteudo"] .= "[n]MAC:[/n] {$_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_mac"]}\n";
				    $dados_chamado["conteudo"] .= "[n]IP:[/n] {$_SESSION["equipamento"][$_POST["verificacao_id"]]["equipamento_ip"]}\n";
				    $dados_chamado["conteudo"] .= "[n]SSID:[/n] {$_SESSION[$_GET["device"]][0]["essid"]}";
	    			$dados_chamado["conteudo"] .= "[/d]";
				    $dados_chamado["conteudo"] .= "Qualidade do sinal:";
				    $dados_chamado["conteudo"] .= "[d]";
					
				    if ($signal_formatado) $dados_chamado["conteudo"] .= "[n]Signal:[/n] $signal_formatado\n";
				    if ($snr_formatado) $dados_chamado["conteudo"] .= "[n]SNR:[/n] $snr_formatado\n";
				    if ($ccq_formatado) $dados_chamado["conteudo"] .= "[n]CCQ:[/n] $ccq_formatado";
				    if ($apsignal_formatado) $dados_chamado["conteudo"] .= "[n]AP Signal:[/n] $apsignal_formatado\n";
				    if ($apsnr_formatado) $dados_chamado["conteudo"] .= "[n]AP SNR:[/n] $apsnr_formatado\n";
				    if ($apccq_formatado) $dados_chamado["conteudo"] .= "[n]AP CCQ:[/n] $apccq_formatado\n";
					
				    $dados_chamado["conteudo"] .= "[/d]";
				    $dados_chamado["conteudo"] .= "Técnico:";
				    $dados_chamado["conteudo"] .= "[d]{$_POST["tecnico"]}[/d]";
				    $dados_chamado["conteudo"] .= "Questionário:\n\n";
				    $dados_chamado["conteudo"] .= "[n]Pergunta 1 - Testou com um equipamento de maior potência?[/n]";
				    $dados_chamado["conteudo"] .= "[d]{$_POST["pergunta_1"]}[/d]";
				    $dados_chamado["conteudo"] .= "[n]Pergunta 2 - Se não testou com um equipamento melhor, por que não?[/n]";
				    $dados_chamado["conteudo"] .= "[d]{$_POST["pergunta_2"]}[/d]";
				    $dados_chamado["conteudo"] .= "[n]Pergunta 3 - Que tipo de equipamento será necessário para este cliente?[/n]";
				    $dados_chamado["conteudo"] .= "[d]{$_POST["pergunta_3"]}[/d]";
				    $dados_chamado["conteudo"] .= "[n]Pergunta 4 - Há algum tipo de problema com a visada para o POP?[/n]";
				    $dados_chamado["conteudo"] .= "[d]{$_POST["pergunta_4"]}[/d]";
				    $dados_chamado["conteudo"] .= "[n]Pergunta 5 - Tem visada para algum outro POP?[/n]";
				    $dados_chamado["conteudo"] .= "[d]{$_POST["pergunta_5"]}[/d]";
					
	    			// Adiciona Chamado
				    $chamado_id = $chamados->adicionarChamado($this->dados_item["cliente_id"], null, $dados_chamado);
					
				    $dados["responsavel_fechamento"] = "4,4,12";
				    $chamados->registraEtapa($chamados->buscaDadosChamado($chamado_id, true), 16, $dados);

				}

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
				
		} else {
			
			$this->fechar();
			
		}
		
	}
	
	function buscaSSID($ssid_id) {
		
		$query = "
			SELECT
				ssi_nome
			FROM
				tab_servidores_ssids
			WHERE
				ssi_id = $ssid_id";
				
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function buscaServidor($servidor_id) {
		
		$query = "
			SELECT
				CONCAT(tab_servidores.ser_nome, ' <span style=\"color:#232323\">(', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip, ')</span>')
			FROM
				tab_servidores
				INNER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
			WHERE
				tab_servidores.ser_id = $servidor_id";
				
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);
		
	}

	##############
	### Ativar ###
	##############

	function ativar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("clientes_autenticacoes.ativar.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=ativar&i={$_GET["i"]}", $this->conteudo);
				if ($this->ehPublicavel()) {
					$this->conteudo = str_replace('[publicar]', '<input type="checkbox" name="publicar" id="publicar" value="1" checked><label for="publicar"> Publicar alterações no servidor agora</label><br><br>', $this->conteudo);
				} else {
					$this->conteudo = str_replace('[publicar]', '', $this->conteudo);
				}
			} else {
				// Ativa Autenticação
				$query = "UPDATE tab_clientes_autenticacoes SET cas_id = 1, cau_publicado = 0 WHERE cau_id = {$_GET["i"]}";
		 		$this->bancodedados->query($query);
		 		// Cancela Ponto
				$query = "UPDATE tab_clientes_pontos SET cps_id = 3, clp_publicado = 0 WHERE ass_id = {$this->dados_item["assinatura_id"]} AND cps_id != 3";
		 		$this->bancodedados->query($query);
				// Registra Comentário
				$this->registraComentario($_GET["i"], "<b>Ação</b>: Ativado", $_SESSION["usuario"]);
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				// Publica serviço
		 		if ($_POST["publicar"]) {
		 			$this->publicar();
		 		} else {
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
		 		}
			}
			
		} else {
			
			$this->fechar();
			
		}
		
	}

	#################
	### Desativar ###
	#################

	function desativar() {
		
		if ($_GET["i"]) {
			
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("clientes_autenticacoes.desativar.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$_GET["i"], $this->conteudo);
				if ($this->ehPublicavel()) {
					$this->conteudo = str_replace('[publicar]', '<input type="checkbox" name="publicar" id="publicar" value="1" checked><label for="publicar"> Publicar alterações no servidor agora</label><br><br>', $this->conteudo);
				} else {
					$this->conteudo = str_replace('[publicar]', '', $this->conteudo);
				}
			} else {
				$query = "UPDATE tab_clientes_autenticacoes SET cas_id = 2, cau_publicado = 0 WHERE cau_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// Altera data de alteração do serviço
				$this->buscaDadosItem($_GET["i"]);
				// Registra Comentário
				$this->registraComentario($_GET["i"], "<b>Ação</b>: Desativado", $_SESSION["usuario"]);
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				// Publica serviço
		 		if ($_POST["publicar"]) {
		 			$this->publicar();
		 		} else {
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
		 		}
			}
			
		} else {
			
			$this->fechar();
			
		}
		
	}

	###############
	### Remover ###
	###############

	function remover() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("clientes_autenticacoes.remover.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}&id={$this->dados_item["cliente_id"]}", $this->conteudo);
				if ($this->ehPublicavel()) {
					$this->conteudo = str_replace('[publicar]', '<input type="checkbox" name="publicar" id="publicar" value="1" checked><label for="publicar"> Publicar alterações no servidor agora</label><br><br>', $this->conteudo);
				} else {
					$this->conteudo = str_replace('[publicar]', '', $this->conteudo);
				}
			} else {
				// deleta comentários
				$query = "DELETE FROM tab_clientes_autenticacoes_comentarios WHERE cau_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// deleta links c/ ajuste
				$query = "DELETE FROM tab_clientes_autenticacoes_ajustes WHERE cau_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// deleta links c/ ajuste
				$query = "DELETE FROM rel_saj_cau_contem WHERE cau_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// deleta Autenticação
				$query = "DELETE FROM tab_clientes_autenticacoes WHERE cau_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
				$this->fechar();
			}
			
		} else {
			
			$this->fechar();
			
		}
		
	}

	################
	### Cancelar ###
	################

	function cancelar() {
		
		if ($_GET["i"]) {
			
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("clientes_autenticacoes.cancelar.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$_GET["i"], $this->conteudo);
				if ($this->ehPublicavel()) {
					$this->conteudo = str_replace('[publicar]', '<input type="checkbox" name="publicar" id="publicar" value="1" checked><label for="publicar"> Publicar alterações no servidor agora</label><br><br>', $this->conteudo);
				} else {
					$this->conteudo = str_replace('[publicar]', '', $this->conteudo);
				}
			} else {
				// Atualiza Status
				$query = "UPDATE tab_clientes_autenticacoes SET cas_id = 3, cau_publicado = 0, cau_ajuste_download = NULL, cau_ajuste_upload = NULL, cau_ajustado = 0 WHERE cau_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// Altera data de alteração do serviço
				$this->buscaDadosItem($_GET["i"]);
				// Registra Comentário
				$this->registraComentario($_GET["i"], "<b>Ação</b>: Cancelado", $_SESSION["usuario"]);
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
				// Publica serviço
		 		if ($_POST["publicar"]) {
		 			$this->publicar();
		 		} else {
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
		 		}
			}
			
		} else {
			
			$this->fechar();
			
		}
		
	}

	################
	### Publicar ###
	################

	function publicar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if (!$_POST) {
			
			if ($_GET["sucesso"]) {
				$this->conteudo .= "<script type=\"text/javascript\">alert('Autenticação publicada com sucesso!')</script>";
				$this->conteudo .= "<script type=\"text/javascript\">window.location = './?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}'</script>";
			} else {
				$this->conteudo .= $this->carregaArquivo("clientes_autenticacoes.publicar.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				if ($_GET["erro"]) {
					$mensagem = "{$_GET["erro"]}<br><br>Clique em Confirmar para tentar novamente.";
				} else {
					$mensagem = "Você deseja realmente <b>publicar</b> esta autenticação?";
				}
				$this->conteudo = str_replace('[mensagem]', $mensagem, $this->conteudo);
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}", $this->conteudo);
			}
			
		} else {
			
			$erro = $this->publicarAutenticacao($this->dados_item["id"]);
			if ($erro) {
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&erro=$erro");
			} else {
				$this->registraComentario($_GET["i"], "<b>Ação</b>: Publicado", $_SESSION["usuario"]);
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], "publicar", $_GET["i"]);
		 		header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&sucesso=1");
			}
			
		}
		
	}

	function publicarAutenticacao($autenticacao_id) {
		
		$publicacao = controle::instanciaObjeto("clientes_autenticacoes.publicacao", $this->bancodedados);
		
		return $publicacao->publicarAutenticacao($autenticacao_id);
		
	}
	
	function buscaStatusAutenticacao($autenticacao_id, $icon = false) {
		
		$publicacao = controle::instanciaObjeto("clientes_autenticacoes.publicacao", $this->bancodedados);
		
		return $publicacao->verificarStatus($autenticacao_id, $icon);
		
	}
	
	function buscaServicos() {
				
		$servicos = array();
		
		$query = "
			SELECT
				pls_id AS id,
				pls_nome AS nome
			FROM
				tab_planos_servicos
		 	ORDER BY
				nome";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servicos[] = $dados;
		}
		
		return $servicos;

	}

	##############
	### Buscar ###
	##############

	function buscar() {
		
		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;
		
		$this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=clientes_autenticacoes" id="clientes_autenticacoes.js"></script>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
		$this->conteudo .= $filtro->formulario;
		
		if ($_GET["f"] || $_GET["p"]) {
			
			$itens = $this->buscaItens($filtro, $pagina);
			$paginacao = $this->geraPaginacao($filtro, $itens["linhas"], $pagina);
			$listagem = $this->geraListagem($itens["itens"]);
			$this->conteudo .= '</td></tr></table>';
			$this->conteudo .= $paginacao->conteudo;
			$this->conteudo .= $listagem->conteudo;
			$this->conteudo .= $paginacao->conteudo;
			
		} else {
			
			$this->conteudo .= '</td></tr></table>';
			
		}
		
	}

	function inicializaFiltro($valores = "") {
		
		// Cliente
		$itens[] = array ("tipo" => "input", "nome" => "cliente", "descricao" => "Cliente", "valor" => "", "where" => "(tab_clientes.cli_nome LIKE '%[valor]%')");
		// Usuário
		$itens[] = array ("tipo" => "input", "nome" => "usuario", "descricao" => "Usuário", "valor" => "", "where" => "(tab_clientes_autenticacoes.cau_usuario LIKE '%[valor]%')");
		// MAC
		$itens[] = array ("tipo" => "input", "nome" => "mac", "descricao" => "MAC", "valor" => "", "where" => "(tab_clientes_autenticacoes.cau_equipamento_mac LIKE '%[valor]%')");
		// IP do ponto
		$itens[] = array ("tipo" => "input", "nome" => "ip_ponto", "descricao" => "IP do Ponto", "valor" => "", "where" => "(tab_clientes_autenticacoes.cau_endereco_ip LIKE '%[valor]%')");
		// IP do rádio
		$itens[] = array ("tipo" => "input", "nome" => "ip_equipamento", "descricao" => "IP do Equipamento", "valor" => "", "where" => "(tab_clientes_autenticacoes.cau_equipamento_ip LIKE '%[valor]%')");
		// Equipamento
		$itens[] = array ("tipo" => "input", "nome" => "equipamento", "descricao" => "Equipamento", "valor" => "", "where" => "(tab_clientes_autenticacoes.cau_equipamento_modelo LIKE '%[valor]%')");
		// Serie/Patrimonio
		$itens[] = array ("tipo" => "input", "nome" => "serie", "descricao" => "Série/Patrimônio", "valor" => "", "where" => "(tab_clientes_autenticacoes.cau_equipamento_serie LIKE '%[valor]%')");
		//Servicos
		$servicos = $this->buscaServicos();
		$itens[] = array ("tipo" => "select", "nome" => "servico", "descricao" => "Serviço", "valor" => $servicos, "where" => "tab_planos_servicos.pls_id = '[valor]'");
		// Concentrador
		$concentradores = $this->buscaConcentradores();
		$select_concentradores = array();
		foreach ($concentradores as $id => $nome) {
			$select_concentradores[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "concentrador", "descricao" => "Concentrador", "valor" => $select_concentradores, "where" => "tab_clientes_autenticacoes.ser_id = '[valor]'");
		// SSID
		$ssids = $this->buscaInterfaceSSIDs();
		$select_ssids = array();
		foreach ($ssids as $id => $nome) {
			$select_ssids[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "ssid", "descricao" => "SSID", "valor" => $select_ssids, "where" => "tab_clientes_autenticacoes.ssi_id = '[valor]'");
		// Status
		$status = $this->buscaStatus();
		$status[-1] = "Ativo (Bloqueado)";
		$status[-2] = "Ativo (Não Bloqueado)";
		asort($status);
		foreach ($status as $id => $nome) {
			$select_status[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "IF([valor] = -1, tab_clientes_autenticacoes.cau_ajustado = 1 AND tab_clientes_autenticacoes.cau_ajuste_download = 0 AND tab_clientes_autenticacoes.cas_id = 1, IF([valor] = -2, tab_clientes_autenticacoes.cau_ajustado = 0 AND tab_clientes_autenticacoes.cas_id = 1, tab_clientes_autenticacoes.cas_id = [valor]))");
		// Filtro
		$filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		
		return $filtro;
		
	}

	function geraPaginacao($filtro, $total, $pagina) {
		
		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
		
	}

	function buscaItens($filtro, $pagina) {
		
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		
		$query = "
			SELECT SQL_CALC_FOUND_ROWS
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes_autenticacoes.cau_id AS id,
				tab_clientes_autenticacoes.cau_senha AS senha,
				tab_clientes_autenticacoes.cau_usuario AS usuario,
				tab_clientes_autenticacoes.cau_endereco_ip AS endereco_ip,
				tab_clientes_autenticacoes.cau_velocidade_download AS velocidade_download,
				tab_clientes_autenticacoes.cau_velocidade_upload AS velocidade_upload,
				tab_clientes_autenticacoes.cau_ajuste_download AS ajuste_download,
				tab_clientes_autenticacoes.cau_ajuste_upload AS ajuste_upload,
				tab_clientes_autenticacoes.cau_ajustado AS ajustado,
				tab_clientes_autenticacoes.cau_publicado AS publicado,
				tab_clientes_autenticacoes.cau_equipamento_mac AS equipamento_mac,
				tab_clientes_autenticacoes.cau_comentario AS observacao,
				tab_clientes_autenticacoes_status.cas_id AS status_id,
				tab_clientes_autenticacoes_status.cas_nome AS status_nome,
				tab_assinaturas.ass_id AS assinatura_id,
	 			CONCAT(tab_planos_servicos.pls_nome, '<br>', tab_planos.pla_nome, IF(tab_assinaturas.ast_id = 1, ' - Prospect', '')) AS assinatura,
				IF(tab_clientes_autenticacoes.ser_id IS NULL, 'Não<br>definido', CONCAT(tab_servidores.ser_nome, '<br><span style=\"color:#232323\">(', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip, ')</span>')) AS concentrador
			FROM
				tab_clientes_autenticacoes
				INNER JOIN tab_clientes_autenticacoes_status ON (tab_clientes_autenticacoes.cas_id = tab_clientes_autenticacoes_status.cas_id)
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_autenticacoes.ass_id)
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
				INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
				LEFT OUTER JOIN tab_servidores ON (tab_clientes_autenticacoes.ser_id = tab_servidores.ser_id)
				LEFT OUTER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
			WHERE
				$filtro->parametros_sql
			ORDER BY
				tab_clientes.cli_nome
			LIMIT
				$limit_inicio,
				$limit_fim";
				
		$query = $this->bancodedados->query($query);
		$lista_unidades = $this->buscaUnidadesVelocidade();
		$linhas = $this->bancodedados->linhasTotal($query);
		
		$itens = array();
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
				$dados["link"] = "javascript:verAutenticacao('{$dados["id"]}');";
			}
			// Dados Cliente
			if ($this->permissoes["clientes"]["ver_dt"]) {
				$dados["cliente_id"] = "<a href=\"./?m=clientes&a=ver_dt&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
			}
			// Usuário
			if ($dados["endereco_ip"]) {
				$dados["usuario"] .= "<br>({$dados["endereco_ip"]})";
			}
			// Velocidade
			$unidade_download = floor(log($dados["velocidade_download"], 1024));
			$unidade_download = $unidade_download < 1 ? 1 : $unidade_download;
			$dados["velocidade"] = round($dados["velocidade_download"] / pow(1024, $unidade_download), 1)." / ".round($dados["velocidade_upload"] / pow(1024, $unidade_download), 1)." ".$lista_unidades[$unidade_download];
			if ($dados["ajustado"]) {
				$unidade = floor(log($dados["ajuste_download"], 1024));
				$unidade = $unidade < 1 ? 1 : $unidade;
				$velocidade = round($dados["ajuste_download"] / pow(1024, $unidade), 1)." / ".round($dados["ajuste_upload"] / pow(1024, $unidade), 1)." ".$lista_unidades[$unidade];
				$dados["velocidade"] .= "<br>($velocidade)";
			}
			$dados["bloqueado"] = (!$dados["ajustado"] && ($dados["velocidade_download"] <= 0 || $dados["velocidade_upload"] <= 0)) || ($dados["ajustado"] && ($dados["ajuste_download"] <= 0 || $dados["ajuste_upload"] <= 0));
		 	// trata Status
			if ($dados["status_id"] != 3) {
				if ($dados["bloqueado"]) {
					$dados["status_nome"] .= "<br>(Bloqueado)";
				}
				if ($dados["publicado"] && $dados["status_id"] != 2 && !$dados["bloqueado"]) {
					$dados["status_nome"] .= "<br>(Publicado)";
				} elseif (!$dados["publicado"] && !$dados["bloqueado"]) {
					$dados["status_nome"] .= "<br>(Não Publicado)";
				}
			}
			if ($dados["status_id"] == 1 && $dados["publicado"] && !$dados["bloqueado"]) {
				$dados["status_nome"] = "<span style=\"color:green\">{$dados["status_nome"]}<span>";
			} else {
				$dados["status_nome"] = "<span style=\"color:red\">{$dados["status_nome"]}<span>";
			}
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			if ($dados["observacao"]) {
				$dados["rowspan"] = array("contador" => 3);
			}

			$itens[] = $dados;
			if ($dados["observacao"]) {
				$itens[] = array("contador" => "<div style=\"color:#232323;text-align:left\">{$dados["observacao"]}</div>", "colspan" => array("contador" => 7));
			}
			
		}
		
		return array("linhas" => $linhas, "itens" => $itens);
		
	}

	function geraListagem($itens) {
		
		$cabecalho = array ("contador" => "#", "cliente_id" => "ID", "cliente_nome" => "Cliente", "usuario" => "Usuário / MAC", "concentrador" => "Concentrador", "velocidade" => "Velocidade", "status_nome" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20", "35", "", "", "", "95", "85"), null, array("right", "center", "left", "center", "center"));
		
		return $listagem;
		
	}

	function buscaStatus() {
		
		$status = array();
		
		$query = "SELECT cas_id AS id, cas_nome AS nome FROM tab_clientes_autenticacoes_status ORDER BY nome";
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$status[$dados["id"]] = $dados["nome"];
		}
		
		return $status;
		
	}
	
	function buscaConcentradores($servidor_id = null) {
		
		$concentradores = array();
		
		$query = "
			SELECT 
				ser_id AS id, 
				ser_nome AS nome 
			FROM 
				tab_servidores 
			WHERE 
				(sso_id = 9 AND set_id = 1)".($servidor_id ? " OR
				(ser_id = $servidor_id)
				" : "")."
			ORDER BY 
				ser_nome";
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$concentradores[$dados["id"]] = $dados["nome"];
		}
		
		return $concentradores;
		
	}
	
	###################
	### Comentários ###
	###################

	function registraComentario($autenticacao_id, $conteudo, $responsavel_id) {
		
		$responsavel_id = $responsavel_id ? $responsavel_id : "NULL";
		$conteudo = addslashes($conteudo);
		
		$query = "
			INSERT INTO tab_clientes_autenticacoes_comentarios
				(cac_datahora, cac_conteudo, cau_id, usu_id)
			VALUES
				(NOW(), '$conteudo', $autenticacao_id, $responsavel_id)";
				
		$this->bancodedados->query($query);
		
	}

	function buscaComentarios($autenticacao_id) {
		
		$query = "
			SELECT
				IF(tab_usuarios.usu_id IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel,
				DATE_FORMAT(tab_clientes_autenticacoes_comentarios.cac_datahora, '%d/%m/%Y (%H:%i)') AS data,
				tab_clientes_autenticacoes_comentarios.cac_conteudo AS conteudo
			FROM
				tab_clientes_autenticacoes_comentarios
				LEFT OUTER JOIN tab_usuarios ON (tab_clientes_autenticacoes_comentarios.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_clientes_autenticacoes_comentarios.cau_id = $autenticacao_id";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["detalhamento"][] = "<b>{$dados["responsavel"]}</b> em <b>{$dados["data"]}</b>";
			if ($dados["conteudo"]) $dados["detalhamento"][] = substr($dados["conteudo"], 0, 3) == "<b>" ? $dados["conteudo"] : "<b>Conteúdo:</b> {$dados["conteudo"]}";
			$dados["detalhamento"] = implode("<br>", $dados["detalhamento"]);
			$comentarios[] = $dados;
		}
		
		return $comentarios;
		
	}

	function geraComentariosListagem($id) {
		
		$comentarios = $this->buscaComentarios($id);
		$cabecalho = array ("detalhamento" => "Detalhamento");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
		
		return $listagem->conteudo;
		
	}

	###########
	### RDS ###
	###########

	function buscaClientesAutenticacoes($cliente_id) {
		
		$autenticacoes = array();
		$lista_unidades = $this->buscaUnidadesVelocidade();
		
		$query = "
			SELECT
				tab_clientes_autenticacoes.cau_id AS id,
				tab_clientes_autenticacoes.cau_senha AS senha,
				tab_clientes_autenticacoes.cau_usuario AS usuario,
				tab_clientes_autenticacoes.cau_endereco_ip AS endereco_ip,
				tab_clientes_autenticacoes.cau_equipamento_mac AS equipamento_mac,
				tab_clientes_autenticacoes.cau_velocidade_download AS velocidade_download,
				tab_clientes_autenticacoes.cau_velocidade_upload AS velocidade_upload,
				tab_clientes_autenticacoes.cau_ajuste_download AS ajuste_download,
				tab_clientes_autenticacoes.cau_ajuste_upload AS ajuste_upload,
				tab_clientes_autenticacoes.cau_ajustado AS ajustado,
				tab_clientes_autenticacoes.cau_publicado AS publicado,
				IF(tab_clientes_autenticacoes.ser_id IS NULL, 'Não<br>definido', CONCAT(tab_servidores.ser_nome, '<br><span style=\"color:#232323\">(', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip, ')</span>')) AS concentrador,
				tab_clientes_autenticacoes.cau_comentario AS observacao,
				tab_clientes_autenticacoes_status.cas_id AS status_id,
				tab_clientes_autenticacoes_status.cas_nome AS status_nome,
	 			CONCAT(tab_planos_servicos.pls_nome, '<br>', tab_planos.pla_nome, IF(tab_assinaturas.ast_id = 1, ' - Prospect', '')) AS assinatura
			FROM
				tab_clientes_autenticacoes
				INNER JOIN tab_clientes_autenticacoes_status ON (tab_clientes_autenticacoes.cas_id = tab_clientes_autenticacoes_status.cas_id)
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_autenticacoes.ass_id)
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
				INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
				LEFT OUTER JOIN tab_servidores ON (tab_clientes_autenticacoes.ser_id = tab_servidores.ser_id)
				LEFT OUTER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
			WHERE
				tab_assinaturas.cli_id = $cliente_id
			ORDER BY
				tab_clientes_autenticacoes_status.cas_id,
				tab_planos.plt_id,
				tab_clientes_autenticacoes.cau_id";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$unidade_download = floor(log($dados["velocidade_download"], 1024));
			$unidade_download = $unidade_download < 1 ? 1 : $unidade_download;
			if ($this->permissoes["clientes_autenticacoes"]["ver"]) $dados["link"] = "javascript:verAutenticacao({$dados["id"]})";
			$dados["velocidade"] = round($dados["velocidade_download"] / pow(1024, $unidade_download), 1)." / ".round($dados["velocidade_upload"] / pow(1024, $unidade_download), 1)." ".$lista_unidades[$unidade_download];
			if ($dados["ajustado"]) {
				$unidade = floor(log($dados["ajuste_download"], 1024));
				$unidade = $unidade < 1 ? 1 : $unidade;
				$velocidade = round($dados["ajuste_download"] / pow(1024, $unidade), 1)." / ".round($dados["ajuste_upload"] / pow(1024, $unidade), 1)." ".$lista_unidades[$unidade];
				$dados["velocidade"] .= "<br>($velocidade)";
			}
			$bloqueado = $dados["velocidade_download"] <= 0 || ($dados["ajustado"] && $dados["ajuste_download"] <= 0);
			// Usuário
			if ($dados["endereco_ip"]) {
				$dados["usuario"] .= "<br>({$dados["endereco_ip"]})";
			}
			
			// trata Status
			if ($dados["status_id"] != 3) {
				if ($bloqueado) {
					$dados["status_nome"] .= "<br>(Bloqueado)";
				}
				if ($dados["publicado"] && $dados["status_id"] != 2 && !$bloqueado) {
					$dados["status_nome"] .= "<br>(Publicado)";
				} elseif (!$dados["publicado"] && !$bloqueado) {
					$dados["status_nome"] .= "<br>(Não Publicado)";
				}
			}
			if ($dados["status_id"] == 1 && $dados["publicado"] && !$bloqueado) {
				$dados["status_nome"] = "<span style=\"color:green\">{$dados["status_nome"]}<span>";
			} else {
				$dados["status_nome"] = "<span style=\"color:red\">{$dados["status_nome"]}<span>";
			}
			if ($dados["observacao"]) {
				$dados["rowspan"] = array("assinatura" => 2);
			}
			
			$autenticacoes[] = $dados;
			
			if ($dados["observacao"]) {
				$autenticacoes[] = array("assinatura" => "<div style=\"color:#232323;text-align:left\">{$dados["observacao"]}</div>", "colspan" => array("assinatura" => 5));
			}
			
		}
		
		return $autenticacoes;
		
	}

	function geraClientesAutenticacoesListagem($dados) {
		
		$cabecalho = array ("assinatura" => "Assinatura", "usuario" => "Usuário / IP", "concentrador" => "Concentrador", "velocidade" => "Velocidade", "status_nome" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array("180", "", "100", "90"), null, array("center"));
		
		return $listagem->conteudo;
		
	}

	function geraBotaoAdicionar($cliente_id) {
		
		if (($autenticacoes_disponiveis = $this->buscaAssinaturas($cliente_id, true)) > 0) {
			if ($this->permissoes["clientes_autenticacoes"]["adicionar"]) {
				$botao_adicionar = '<input type="button" class="buttonAzulEscuroBranco11" name="autenticacoes_adicionar" value="Adicionar" onClick="adicionarAutenticacao('.$cliente_id.')">';
			}
			$botao_adicionar .= " $autenticacoes_disponiveis ponto(s) disponível(is)";
			return $botao_adicionar;
		}
		
		return "Nenhum ponto disponível";
		
	}
	
	function verificarConexao($ip, $ap_ip, $ap_tipo_id, $nome_equipamento) {
				
		$verificacao = controle::instanciaObjeto("clientes_autenticacoes.verificacao", $this->bancodedados);
		
		return $verificacao->verificarConexao($ip, $ap_ip, $ap_tipo_id, $nome_equipamento);
		
	}
	
	#######################
	### Liberar Técnico ###
	#######################
	
	function liberar_tecnico() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item["concentrador_ip"]) {
			$publicacao = controle::instanciaObjeto("clientes_autenticacoes.publicacao", $this->bancodedados);
			$erro = $publicacao->liberar_tecnico($this->dados_item["concentrador_ip"]);
			if ($erro) {
				print "<script type=\"text/javascript\">alert('$erro'); window.location = './?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}';</script>";
			} else {
				$this->registraComentario($_GET["i"], "Liberar técnico ({$this->dados_item["concentrador_ip"]})", $_SESSION["usuario"]);
				print "<script type=\"text/javascript\">alert('Liberado técnico para o usuário hotspot@oletelecom.com.br'); window.location = './?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}';</script>";
			}
		} else {
				print "<script type=\"text/javascript\">alert('Concentrador não definido.'); window.location = './?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}';</script>";
		}
		
		die();
		
	}
	
	#################
	### Verificar ###
	#################
	
	function verificar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if (!$_POST) {
			
			$this->conteudo .= $this->carregaArquivo("clientes_autenticacoes.verificar.inc.php");
			$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			$this->conteudo = str_replace('[mensagem]', "Você deseja realmente <b>verificar</b> esta autenticação?", $this->conteudo);
			$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=verificar&i={$_GET["i"]}", $this->conteudo);
			
		} else {
			
			$ssid = $this->buscaDadosSSID($this->dados_item["ssid_id"]);
			$this->verificarConexao($this->dados_item["ip"], $this->dados_item["ssid_ip"], $ssid["tipo_id"], $this->geraNomeEquipamento($this->dados_item["cliente_id"]));
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
		}
		
	}

}

?>
