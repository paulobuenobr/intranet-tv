<?

require_once("_global/modulo.class.inc.php");

class clientes_pontos extends modulo {

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

	function geraIPs() {
		for ($i = 26; $i < 255; $i++) {
			$ips[$i] = $i;
		}
		return $ips;
	}

	function listaIPs($interface_id, $ponto_id = null) {
		$ips_existentes = array();
		// Pontos existentes
		$query = "
			SELECT
				clp_ip AS ip
			FROM
				tab_clientes_pontos
			WHERE
				sei_id = $interface_id AND
				cps_id != 3".($ponto_id ? " AND
				clp_id != $ponto_id" : "");
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["ip"] = explode(".", $dados["ip"]);
			$ips_existentes[$dados["ip"][3]] = $dados["ip"][3];
		}
		$query = "
			SELECT
				sei_endereco_ip AS ip
			FROM
				tab_servidores_interfaces
			WHERE
				sei_id = $interface_id";
 		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			$ips_existentes[$dados["ip"]] = $dados["ip"];
 		}
 		return array_diff_assoc($this->geraIPs(), $ips_existentes);
	}

	function buscaDadosCliente($id) {
		$query = "SELECT cli_id AS id, cli_nome AS nome FROM tab_clientes WHERE cli_id = '$id'";
		$query = $this->bancodedados->query($query);
		if ($this->bancodedados->linhas($query)) {
			$this->dados_cliente = $this->bancodedados->resultadoArray($query);
		} else {
			unset($this->dados_cliente);
		}
	}

	function buscaDadosInterface($interface_id){
		if ($interface_id) {
			$query = "
				SELECT
					tab_servidores_interfaces.ser_id AS servidor_id,
					tab_servidores_interfaces.sei_prefixo_ip AS prefixo,
					CONCAT(tab_servidores.ser_nome, ' (', tab_servidores_interfaces.sei_nome, IF(interface_pai.sei_id IS NULL, '', CONCAT(' [', interface_pai.sei_nome, ']')), IF(tab_servidores_interfaces.sei_prefixo_ip IS NULL, '', CONCAT(', ', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip)), ')') AS interface_nome
				FROM
					tab_servidores_interfaces
					INNER JOIN tab_servidores ON (tab_servidores_interfaces.ser_id = tab_servidores.ser_id)
					LEFT OUTER JOIN tab_servidores_interfaces interface_pai ON (tab_servidores_interfaces.sei_pai = interface_pai.sei_id)
				WHERE
					tab_servidores_interfaces.sei_id = $interface_id";
			$query = $this->bancodedados->query($query);
			return $this->bancodedados->resultadoArray($query);
		}
	}

	function buscaDadosItem($id) {
		$query = "
			SELECT
				CONCAT(tab_planos_servicos.pls_nome, ' - ', tab_planos.pla_nome, IF(tab_assinaturas.ast_id = 1, ' - Prospect', '')) AS assinatura,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes.con_id AS id_condominio,
				tab_clientes_pontos.clp_id AS id,
				tab_clientes_pontos.clp_regra AS regra,
				tab_clientes_pontos.clp_ip AS ip,
				tab_clientes_pontos.clp_endereco_mac AS mac,
				tab_clientes_pontos.clp_velocidade_download AS download,
				tab_clientes_pontos.clp_velocidade_upload AS upload,
				tab_clientes_pontos.clp_publicado AS publicado,
				tab_clientes_pontos.clp_ajustado AS ajustado,
				tab_clientes_pontos.clp_parametros_ajuste AS parametros_ajuste,
				tab_clientes_pontos.sei_id AS interface_id,
				tab_clientes_pontos.clp_comentario AS comentario,
				tab_clientes_pontos_status.cps_id AS status_id,
				tab_clientes_pontos_status.cps_nome AS status_nome,
				tab_servidores_interfaces.ser_id AS servidor_id,
				tab_assinaturas.ast_id AS assinatura_status,
				tab_assinaturas.ass_id AS assinatura_id,
				tab_planos.pls_id AS servico_id
			FROM
				tab_clientes_pontos_status
				INNER JOIN tab_clientes_pontos ON (tab_clientes_pontos_status.cps_id = tab_clientes_pontos.cps_id)
				INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
				LEFT OUTER JOIN tab_servidores_interfaces ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
			WHERE
				tab_clientes_pontos.clp_id = $id";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			$this->dados_item = $dados;
			// Gera dados velocidade
	 		$unidades = $this->buscaUnidadesVelocidade();
	 		$unidade = floor(log($dados["download"], 1024));
	 		$unidade = $unidade < 1 ? 1 : $unidade;
	 		$velocidade = round($dados["download"] / pow(1024, $unidade), 1)." / ".
	 									round($dados["upload"] / pow(1024, $unidade), 1)." ".
	 									$unidades[$unidade];
			// Atribui velocidade ao campo
	 		$this->dados_item["velocidade"] = $velocidade;
	 		// Verifica se tem ajuste e mostra os valores ajustados
			if ($dados["parametros_ajuste"] && $dados["ajustado"]) {
				$parametros = unserialize($dados["parametros_ajuste"]);
		 		$unidade = floor(log($parametros["download"], 1024));
		 		if ($unidade < 1) $unidade = 1;
		 		$velocidade = round($parametros["download"] / pow(1024, $unidade), 1)." / ".
		 									round($parametros["upload"] / pow(1024, $unidade), 1)." ".
		 									$unidades[$unidade];
	 			$this->dados_item["velocidade"] .= " ($velocidade)";
			}
			$bloqueado = $dados["download"] <= 0 || ($dados["ajustado"] && $parametros["download"] <= 0);
			$dados["bloqueado"] = $bloqueado;
			// Trata Status
			if ($dados["status_id"] != 3) {
				if ($dados["ajustado"] && !$bloqueado) {
					$this->dados_item["status_nome"] .= " (Ajustado)";
				} elseif ($bloqueado) {
					$this->dados_item["status_nome"] .= " (Bloqueado)";
				}
				if ($dados["publicado"] && $dados["status_id"] != 2 && !$bloqueado) {
					$this->dados_item["status_nome"] .= " (Publicado)";
				} elseif (!$dados["publicado"]) {
					$this->dados_item["status_nome"] .= " (Não Publicado)";
				}
			}
			if ($dados["status_id"] == 1 && $dados["publicado"] && !$bloqueado) {
				$this->dados_item["status_nome"] = "<span style=\"color:green\">{$this->dados_item["status_nome"]}<span>";
			} else {
				$this->dados_item["status_nome"] = "<span style=\"color:red\">{$this->dados_item["status_nome"]}<span>";
			}
			$this->dados_item["assinatura"] = (!$dados["assinatura"]) ? "Sem Assinatura" : $dados["assinatura"];
			// Dados Servidor
			$servidor = $this->buscaDadosInterface($dados["interface_id"]);
			$this->dados_item["interface_nome"] = $servidor["interface_nome"];
			$this->dados_item["servidor_situacao_id"] = $servidor["situacao_id"];
			$this->dados_item["servidor_situacao_nome"] = $servidor["situacao_nome"];
			$this->dados_item["prefixo"] = $servidor["prefixo"];
		} else {
			unset($this->dados_item);
		}
	}

	function buscaUnidadesVelocidade() {
		$unidadecapacidade["1"] = "Kbps";
		$unidadecapacidade["2"] = "Mbps";
		$unidadecapacidade["3"] = "Gbps";
		$unidadecapacidade["4"] = "Tbps";
		return $unidadecapacidade;
	}
	
	function buscaAssinaturas($cliente_id, $total = false) {
		$query = "
			SELECT
				tab_assinaturas.ass_id AS id,
				tab_planos_servicos.pls_id AS servico_id,
				CONCAT(tab_planos_servicos.pls_nome, ' - ', tab_planos.pla_nome, IF(tab_assinaturas.ast_id = 1, ' - Prospect', '')) AS nome,
 				tab_assinaturas.ass_parametros AS parametros,
				COUNT(DISTINCT tab_clientes_pontos.clp_id) AS quantidade_pontos,
				COUNT(DISTINCT tab_clientes_autenticacoes.cau_id) AS quantidade_autenticacoes
			FROM
				tab_planos_servicos
				INNER JOIN tab_planos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
				INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)
				INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
				INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id)
				LEFT OUTER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id)
				LEFT OUTER JOIN tab_clientes_pontos ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id AND tab_clientes_pontos.cps_id != 3)
				LEFT OUTER JOIN tab_clientes_autenticacoes ON (tab_clientes_autenticacoes.ass_id = tab_assinaturas.ass_id AND tab_clientes_autenticacoes.cas_id != 3)
			WHERE
				(tab_assinaturas.ast_id IN (1, 2, 5)) AND
				(tab_contratos.crt_id IN (11, 12)) AND
				(tab_contratos.crs_id IN (19, 20, 21) OR (tab_visitas.vis_id IS NULL OR tab_visitas.vis_data <= CURDATE())) AND
				(tab_assinaturas.cli_id = $cliente_id)
			GROUP BY
				tab_assinaturas.ass_id
			ORDER BY
				id";
		$query = $this->bancodedados->query($query);
		$pontos_disponiveis = 0;
		$assinaturas = array();
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$parametros = @unserialize($dados["parametros"]);
			$disponivel = $parametros["pontos_quantidade"] - $dados["quantidade_pontos"] - $dados["quantidade_autenticacoes"];
			if ($total) {
				$pontos_disponiveis += $disponivel;
			} else {
				if ($disponivel > 0) {
		 			$assinaturas[$dados["id"]] = $dados["nome"]." ($disponivel disponíve".(($disponivel > 1) ? "is" : "l").")";
				}
			}
		}
		if ($total) {
			return $pontos_disponiveis;
		} else {
			return $assinaturas;
		}
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
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			$dados = unserialize($dados["parametros"]);
			return $dados;
		}
	}

	function buscaCliente($cli_id) {
		$query = "
			SELECT
				tab_clientes.cli_nome AS nome,
				tab_clientes.con_id AS condominio_id,
				tab_servidores_interfaces.sei_prefixo_ip AS prefixo,
				tab_servidores_interfaces.sei_id AS sei_id
			FROM
				tab_clientes
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
				LEFT OUTER JOIN tab_servidores_interfaces ON (tab_servidores_interfaces.sei_id = tab_condominios_blocos.sei_id)
			WHERE
				tab_clientes.cli_id = $cli_id";
		$query = $this->bancodedados->query($query);
		return $this->bancodedados->resultadoArray($query);
	}

	function buscaAssinaturasComAjuste($assinatura) {
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

	function buscaAjustes($ponto_id) {
		$query = "
			SELECT DISTINCT
				tab_servicos_ajustes.saj_id AS id,
 				DATE_FORMAT(tab_servicos_ajustes.saj_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
				DATE_FORMAT(tab_servicos_ajustes.saj_termino, '%d/%m/%Y (%H:%i)') AS termino,
 				tab_servicos_ajustes_categorias.sac_nome AS categoria,
				tab_servicos_ajustes_status.sas_id AS status_id,
				tab_servicos_ajustes_status.sas_nome AS status,
				DATE_FORMAT(tab_clientes_pontos_ajustes.cpa_inicio, '%d/%m/%Y (%H:%i)') AS aplicado_deste
			FROM
				tab_servicos_ajustes
				INNER JOIN tab_servicos_ajustes_status ON (tab_servicos_ajustes_status.sas_id = tab_servicos_ajustes.sas_id)
				INNER JOIN rel_saj_clp_contem ON (rel_saj_clp_contem.saj_id = tab_servicos_ajustes.saj_id)
				INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes.sac_id = tab_servicos_ajustes_categorias.sac_id)
				LEFT OUTER JOIN tab_clientes_pontos_ajustes ON (tab_clientes_pontos_ajustes.saj_id = tab_servicos_ajustes.saj_id AND tab_clientes_pontos_ajustes.cpa_termino IS NULL)
			WHERE
				rel_saj_clp_contem.clp_id = $ponto_id AND
				tab_servicos_ajustes_status.sas_id IN (5, 9)
			ORDER BY
				id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$regras = '&raquo; Início: '.$dados["inicio"];
			if ($dados["termino"]) {
				$regras .= '&nbsp; &nbsp; &raquo; Término: '.$dados["termino"];
			}
			$regras .= $this->buscaRegras($dados["id"]);
			$regras = str_replace("\n", " ", $regras);
			$regras = str_replace("\"", "'", $regras);
			$dados["codigo"] = "<div align=\"center\">".sprintf("%06s", $dados["id"])."</div>";
			$dados["categoria"] = "<div align=\"left\" style=\"cursor:pointer\"onmouseover=\"hintShow('$regras', 390)\" onmouseout=\"hintHide()\">{$dados["categoria"]}</div>";
			if ($dados["status_id"] == 5) {
				if ($dados["aplicado_deste"]) {
					$dados["status"] .= " / Aplicado desde {$dados["aplicado_deste"]}";
				} else {
					$dados["status"] .= " / Não Aplicado";
				}
			}
			$dados["categoria"] = "<div align=\"left\">".$dados["categoria"]."</div>";
			if (!$dados["inicio"]) $dados["inicio"] = "-";
			if (!$dados["termino"]) $dados["termino"] = "-";
			$lista[] = $dados;
		}
		$cabecalho = array ("codigo" => "Código", "categoria" => "Categoria", "status" => "Status");
		$larguras = array("40", "", "220");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $lista, 0, false, $larguras);
		return $listagem->conteudo;
	}

	function buscaRegras($ajuste_id) {
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

	function buscaAjusteUnidade($servico_id, $unidade_id) {
		$multiplicadores = array(0 => "", 1 => "K", 2 => "M", 3 => "G");
		$unidades = array(1 => "bps", 2 => "B", 4 => "B", 5 => "B / mês", 7 => "B", 8 => "B / mês", 9 => "bps");
		return $multiplicadores[$unidade_id].$unidades[$servico_id];
	}

	function buscaCobrancasAtrasadas($cliente_id, $quantidade_maxima = 0) {
		$query = "
			SELECT
				count(coa_id) AS quantidade
			FROM
				tab_cobrancas
			WHERE
				cli_id = $cliente_id AND
				cot_id = 1 AND
				coa_data_vencimento < DATE_SUB(CURDATE(), INTERVAL ".COBRANCA_BLOQUEIO_GERENCIAMENTO_DO_CLIENTE." DAY)";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			return ($dados["quantidade"] > $quantidade_maxima) ? $dados["quantidade"] : false;
		} else {
			return false;
		}
	}

	function buscaClientesPontos($cliente_id) {
		$lista_unidades = $this->buscaUnidadesVelocidade();
		$query = "
			SELECT
				tab_clientes_pontos.clp_id AS id,
				tab_clientes_pontos.clp_endereco_mac AS mac,
				tab_clientes_pontos.clp_ip AS ip,
				tab_clientes_pontos.clp_velocidade_download AS download,
				tab_clientes_pontos.clp_velocidade_upload AS upload,
				tab_clientes_pontos_status.cps_id AS status_id,
				tab_clientes_pontos_status.cps_nome AS status_nome,
		 		tab_clientes_pontos.clp_publicado AS publicado,
		 		tab_clientes_pontos.clp_ajustado AS ajustado,
		 		tab_clientes_pontos.clp_parametros_ajuste AS parametros_ajuste,
		 		tab_clientes_pontos.clp_comentario AS comentario,
				tab_assinaturas.ass_parametros AS parametros,
	 			CONCAT(tab_planos_servicos.pls_nome, '<br>', tab_planos.pla_nome, IF(tab_assinaturas.ast_id = 1, ' - Prospect', '')) AS assinatura,
				CONCAT(tab_servidores.ser_nome, '<br>(', tab_servidores_interfaces.sei_nome, IF(interface_pai.sei_id IS NULL, '', CONCAT(' [', interface_pai.sei_nome, ']')), IF(tab_servidores_interfaces.sei_prefixo_ip IS NULL, '', CONCAT(', ', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip)), ')') AS interface
			FROM
				tab_clientes_pontos
				INNER JOIN tab_clientes_pontos_status ON (tab_clientes_pontos_status.cps_id = tab_clientes_pontos.cps_id)
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
				INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
				LEFT OUTER JOIN tab_servidores_interfaces ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
				LEFT OUTER JOIN tab_servidores_interfaces interface_pai ON (tab_servidores_interfaces.sei_pai = interface_pai.sei_id)
				LEFT OUTER JOIN tab_servidores ON (tab_servidores_interfaces.ser_id = tab_servidores.ser_id)
			WHERE
				tab_assinaturas.cli_id = $cliente_id
			ORDER BY
				tab_clientes_pontos_status.cps_id,
				tab_planos.plt_id,
				tab_clientes_pontos.clp_id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$unidade_download = floor(log($dados["download"], 1024));
			$unidade_download = $unidade_download < 1 ? 1 : $unidade_download;
			if ($this->permissoes["clientes_pontos"]["ver"]) {
				$dados["link"] = "javascript:verPonto({$dados["id"]})";
			}
			$dados["status"] = $dados["status_nome"];
			$dados["velocidade"] = round($dados["download"] / pow(1024, $unidade_download), 1)." / ".
			round($dados["upload"] / pow(1024, $unidade_download), 1)." ".$lista_unidades[$unidade_download];
			if ($dados["parametros_ajuste"] && $dados["ajustado"]) {
				$parametros = unserialize($dados["parametros_ajuste"]);
				$unidade = floor(log($parametros["download"], 1024));
				if ($unidade < 1) $unidade = 1;
				$velocidade = round($parametros["download"] / pow(1024, $unidade), 1)." / ".round($parametros["upload"] / pow(1024, $unidade), 1)." ".
				$lista_unidades[$unidade];
				$dados["velocidade"] .= "<br>($velocidade)";
			}
			$bloqueado = $dados["download"] <= 0 || ($dados["ajustado"] && $parametros["download"] <= 0);
			// MAC (IP)
			$dados["mac_ip"] = $dados["mac"];
			if ($dados["ip"]) $dados["mac_ip"] .= "<br>({$dados["ip"]})";
			// trata Status
			if ($dados["status_id"] != 3) {
				if ($dados["ajustado"] && !$bloqueado) {
					$dados["status"] .= " (Ajustado)";
				} elseif ($bloqueado) {
					$dados["status"] .= "<br>(Bloqueado)";
				}
				if ($dados["publicado"] && $dados["status_id"] != 2 && !$bloqueado) {
					$dados["status"] .= "<br>(Publicado)";
				} elseif (!$dados["publicado"]) {
					$dados["status"] .= "<br>(Não Publicado)";
				}
			}
			if ($dados["status_id"] == 1 && $dados["publicado"] && !$bloqueado) {
				$dados["status"] = "<span style=\"color:green\">{$dados["status"]}<span>";
			} else {
				$dados["status"] = "<span style=\"color:red\">{$dados["status"]}<span>";
			}
			if ($dados["comentario"]) {
				$dados["rowspan"] = array("assinatura" => 2);
			}
			$pontos[] = $dados;
			if ($dados["comentario"]) {
				$pontos[] = array("assinatura" => "<div style=\"color:#232323;text-align:left\">{$dados["comentario"]}</div>", "colspan" => array("assinatura" => 5));
			}
		}
		return $pontos;
	}

	function geraClientesPontosListagem($dados) {
		$cabecalho = array ("assinatura" => "Assinatura", "interface" => "Servidor (Interface)", "mac_ip" => "MAC (IP)", "velocidade" => "Velocidade", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array("180", "", "100", "90", "90"), null, array("center"));
		return $listagem->conteudo;
	}

	function geraBotaoAdicionar($cliente_id) {
		if (($pontos_disponiveis = $this->buscaAssinaturas($cliente_id, true)) > 0) {
			if ($this->permissoes["clientes_pontos"]["adicionar"]) {
				$botao_adicionar = '<input type="button" class="buttonAzulEscuroBranco11" name="pontos_adicionar" value="Adicionar" onClick="adicionarPonto('.$cliente_id.')">';
			}
			$botao_adicionar .= " $pontos_disponiveis ponto(s) disponível(is)";
			return $botao_adicionar;
		}
		return "Nenhum ponto disponível";
	}

	function verificaServidorCondominio($cliente_id) {
		$query = "
			SELECT
				tab_clientes.cli_id AS id
			FROM
				tab_clientes
				INNER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
			WHERE
				tab_condominios_blocos.sei_id IS NOT NULL AND
				tab_clientes.cli_id = $cliente_id";
		$query = $this->bancodedados->query($query);
		if ($this->bancodedados->resultadoArray($query)) {
			return true;
		}
	}

	function servicoFreeBSD($interface_id) {
		$query = "
			SELECT
				sst_id AS tipo_id
			FROM
				tab_servidores_servicos
			WHERE
				sei_id = $interface_id AND
				sst_id IN (9, 10, 12) AND
				sss_id = 1";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			return $dados["tipo_id"];
		}
	}
	
	function buscaBadList() {
		$bad_list[] = "00:e0:06:09:55:66";
		$bad_list[] = "ff:ff:ff:ff:ff:ff";
		$bad_list[] = "aa:aa:aa:aa:aa:aa";
		return $bad_list;
	}

	function ehMacInvalido($mac, $ponto_id = null) {
		if ($mac == "00:00:00:00:00:00") {
			return true;
		}
		if (in_array($mac, $this->buscaBadList())) {
			return true;
		}
		if ($this->buscaPontoMAC($mac, $ponto_id)) {
			return true;
		}
		if ($this->ehMacEmpresa($mac)) {
			return true;
		}
		return false;
	}
	
	function ehMacEmpresa($mac) {
		if (in_array($mac, unserialize(PUBLICACAO_PONTOS_TECNICA))) {
			return true;
		}
		return false;
	}
	
	function buscaPontoMAC($mac, $ponto_id = null) {
		$query = "
			SELECT
				tab_clientes_pontos.clp_id AS id
			FROM
				tab_clientes_pontos
			WHERE".($ponto_id ? "
				tab_clientes_pontos.clp_id != $ponto_id AND" : "")."
				tab_clientes_pontos.clp_endereco_mac LIKE '$mac' AND
				tab_clientes_pontos.cps_id IN (1, 2)";
		$query = $this->bancodedados->query($query);		
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			return $dados["id"];
		}
	}
	
	function buscaProximaRegra($interface_id) {
		$dados_interface = $this->buscaDadosInterface($interface_id);
		$regra = $this->buscaRegraLivre($dados_interface["servidor_id"]);
		if ($regra) {
			return $regra;
		} else {
			return $this->buscaRegraMaxima($dados_interface["servidor_id"]) + 1;
		}
	}
	
	function buscaRegraLivre($servidor_id) {
		$query = "
			SELECT
				tab_clientes_pontos.clp_id AS id,
				tab_clientes_pontos.clp_regra AS regra
			FROM
				tab_servidores_interfaces
				INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
			WHERE
				tab_servidores_interfaces.ser_id = $servidor_id AND
				tab_clientes_pontos.clp_regra IS NOT NULL AND
				tab_clientes_pontos.cps_id IN (3)
			ORDER BY
				tab_clientes_pontos.clp_regra
			LIMIT
				0, 1";
		$query = $this->bancodedados->query($query);		
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			$this->bancodedados->query("UPDATE tab_clientes_pontos SET clp_regra = NULL WHERE clp_id = {$dados["id"]}");		
			return $dados["regra"];
		}
	}
 
	function buscaRegraMaxima($servidor_id) {
		$query = "
			SELECT
				MAX(tab_clientes_pontos.clp_regra) AS regra
			FROM
				tab_servidores_interfaces
				INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
			WHERE
				tab_servidores_interfaces.ser_id = $servidor_id AND
				tab_clientes_pontos.clp_regra IS NOT NULL AND
				tab_clientes_pontos.cps_id IN (1, 2)";
		$query = $this->bancodedados->query($query);		
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			return $dados["regra"] ? $dados["regra"] : 10000;
		} else {
			return 10000;
		}
	}

	function buscaPontoSemRegra($servidor_id) {
		$query = "
			SELECT
				tab_clientes_pontos.clp_id AS id
			FROM
				tab_servidores_interfaces
				INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
			WHERE
				tab_servidores_interfaces.ser_id = $servidor_id AND
				tab_clientes_pontos.clp_regra IS NULL AND
				tab_clientes_pontos.cps_id IN (3)
			LIMIT
				0, 1";
		$query = $this->bancodedados->query($query);		
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			return $dados["id"];
		}
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
			$dados = @unserialize($dados["parametros"]);
			$lista_unidades = array ("1" => "Kbps", "2" => "Mbps", "3" => "Gbps", "4" => "Tbps");
			$unidade_download = floor(log($dados["pontos_velocidade_download"], 1024));
			$unidade_download = $unidade_download < 1 ? 1 : $unidade_download;
			return round($dados["pontos_velocidade_download"] / pow(1024, $unidade_download), 1)." / ".round($dados["pontos_velocidade_upload"] / pow(1024, $unidade_download), 1)." ".$lista_unidades[$unidade_download];
		}
	}
	
	function atualizaPontoIP($ponto_id) {
		$query = "
			SELECT
				tab_clientes_pontos.clp_id AS id,
				tab_clientes_pontos.ass_id AS assinatura_id,
				tab_clientes_pontos.sei_id AS interface_id
			FROM
				tab_clientes_pontos
			WHERE
				tab_clientes_pontos.clp_id = $ponto_id";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			$ip = $this->buscaIPLivre($dados["interface_id"], $dados["assinatura_id"], $dados["id"]);
			$ip = $ip ? "'$ip'" : "NULL";
			// Atualiza Registro
			$query = "UPDATE tab_clientes_pontos SET clp_ip = $ip WHERE clp_id = {$dados["id"]}";
			$this->bancodedados->query($query);
		}
	}
	
	function buscaIPLivre($interface_id, $assinatura_id, $ponto_id = null, $explodir = false) {
		$servico_id = $this->servicoFreeBSD($interface_id);
		if ($servico_id) {
			if ($servico_id == 12) {
				$dados_assinatura = $this->buscaDadosAssinatura($assinatura_id);
				if (in_array($dados_assinatura["servico_id"], array(3, 9))) {
					if ($ponto_id && ($ip = $this->buscaIPAtual($ponto_id, $interface_id))) {
						return $explodir ? explode(".", $ip) : $ip;
					}
					$ip = $this->buscaRedeIpLivre($interface_id);
					if ($ip) {
						return $explodir ? explode(".", $ip) : $ip;
					} else {
						return $explodir ? array("", "", "", "") : "";
					}
				} else {
					return $explodir ? array("", "", "", "") : "";
				}
			} else {
				return $explodir ? array("", "", "", "") : "";
			}
		} else {
			if ($ponto_id && ($ip = $this->buscaIPAtual($ponto_id, $interface_id))) {
				return $explodir ? explode(".", $ip) : $ip;
			}
			$dados_interface = $this->buscaDadosInterface($interface_id);
			if ($dados_interface["prefixo"]) {
				$ips_livres = $this->listaIPs($interface_id, $ponto_id);
				$ip = $dados_interface["prefixo"].".".current($ips_livres);
				return $explodir ? explode(".", $ip) : $ip;
			} else {
				return "";
			}
		}
	}
	
	function buscaRedeIpLivre($interface_id) {
		$query = "
			SELECT
				tab_redes.red_id AS id,
				tab_redes.red_endereco_ip AS rede,
				tab_redes.red_mascara AS mascara,
				tab_redes.red_reserva_ips AS reserva_ips
			FROM
				tab_redes
			WHERE
				tab_redes.sei_id = $interface_id AND
				(tab_redes.red_reserva_ips > 0 || tab_redes.red_mascara = 30)
			ORDER BY
				INET_ATON(tab_redes.red_endereco_ip) DESC
			LIMIT
				0, 1";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			$pontos_ip_fixo = $this->buscaQuantidadePontosIpFixo($dados["id"]);
			if (pow(2, 32 - $dados["mascara"]) - 3 - $dados["reserva_ips"] - $pontos_ip_fixo) {
				return $this->geraIP($dados["rede"], 1 + $dados["reserva_ips"] + $pontos_ip_fixo + 1, $dados["mascara"]);
			}
		}
	}

	function geraIP($prefixo, $sub_rede, $mascara) {
		$sub_rede = decbin($sub_rede);
		$prefixo = explode(".", $prefixo);
		for ($i = 0; $i < count($prefixo); $i++) {
			$prefixo[$i] = sprintf("%08s", decbin($prefixo[$i]));
		}
		$prefixo = implode($prefixo);
		$mascara = sprintf("%0-32s", str_repeat("1", $mascara));
		$endereco_ip = sprintf("%0-32s", decbin(bindec($prefixo & $mascara) + bindec($sub_rede)));
		for ($i = 0; $i < 4; $i++) {
			$resultado[] = bindec(substr($endereco_ip, $i * 8, 8));
		}
		return implode(".", $resultado);
	}

	function buscaQuantidadePontosIpFixo($rede_id) {
		$query = "
			SELECT
				COUNT(DISTINCT tab_clientes_pontos.clp_id) AS pontos
			FROM
				tab_clientes_pontos
				INNER JOIN tab_redes ON (tab_clientes_pontos.sei_id = tab_redes.sei_id)
			WHERE
				tab_redes.red_id = $rede_id AND
				INET_ATON(tab_clientes_pontos.clp_ip) - INET_ATON(tab_redes.red_endereco_ip) < POW(2, 32 - tab_redes.red_mascara) AND
				tab_clientes_pontos.clp_endereco_mac IS NOT NULL AND
				tab_clientes_pontos.clp_ip IS NOT NULL AND
				tab_clientes_pontos.cps_id IN (1, 2)";
		$query = $this->bancodedados->query($query);
		return $this->bancodedados->resultado($query, 0, "pontos");
	}

	function buscaIPAtual($ponto_id, $interface_id) {
		$query = "
			SELECT
				clp_ip AS ip
			FROM
				tab_clientes_pontos
			WHERE
				clp_id = $ponto_id AND
				sei_id = $interface_id";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			return $dados["ip"];
		}
	}
	
	function ehPublicavel() {
		if (!$this->permissoes[$this->dados_modulo["nome"]]["publicar"]) return false;
		if (!$this->dados_item["interface_id"] || !$this->dados_item["mac"]) return false;
		if ($this->permissoes[$this->dados_modulo["nome"]]["publicar_plus"]) return true;
		if ($this->dados_item["publicado"]) return false;
		return true;
	}

	##############
	### Fechar ###
	##############

	function fechar() {
		unset($this->conteudo);
		if (!empty($_GET["id"])) {
			$dados_item = $this->buscaCliente($_GET["id"]);
			$this->conteudo .= "
				<script type=\"text/javascript\">
					if (!window.opener.closed && window.opener.buscaPontos) {
						window.opener.buscaPontos({$_GET["id"]});
					} else if (!window.opener.closed && window.opener.buscaCondominiosPontos) {
						window.opener.buscaCondominiosPontos({$dados_item["condominio_id"]});
					}
				</script>";
		}
		$this->conteudo .= '<script type="text/javascript">window.close();</script>';
	}

	#################
	### Adicionar ###
	#################

	function adicionar() {
		$cliente_assinaturas = $this->buscaAssinaturas($_GET["i"]);
		if ($cliente_assinaturas) {
			$dados_cliente = $this->buscaCliente($_GET["i"]);
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("clientes_pontos.adicionar.inc.php");
				$this->conteudo = str_replace('[cliente_nome]', $dados_cliente["nome"], $this->conteudo);
				$this->conteudo = str_replace('[assinatura]', $this->geraSelect($cliente_assinaturas, count($cliente_assinaturas) == 1 ? key($cliente_assinaturas) : null, true, "assinatura", "100%", "mudaAssinatura(this.value)"), $this->conteudo);
				$this->conteudo = str_replace('[interface]', $this->geraSelect($interfaces = count($cliente_assinaturas) == 1 ? ($this->buscaInterfacesAssinatura($dados_cliente["condominio_id"] && in_array($this->buscaServicoAssinatura(key($cliente_assinaturas)), array(1, 32)) ? true : false, $dados_cliente["sei_id"])) : null, count($interfaces) == 1 ? key($interfaces) : null, true, "interface", "100%", "", count($cliente_assinaturas) == 1 && $this->permissoes["clientes_pontos"]["alterar_interface"]), $this->conteudo);
				if (count($cliente_assinaturas) == 1) {
			 		$this->conteudo = str_replace('[velocidade]', $this->buscaVelocidadeAssinatura(key($cliente_assinaturas)), $this->conteudo);
				} else {
			 		$this->conteudo = str_replace('[velocidade]', "", $this->conteudo);
				}
		 		//$ip = $interfaces ? $this->buscaIPLivre(key($interfaces), key($cliente_assinaturas), null, true) : array("", "", "", "");
				$this->conteudo = str_replace("[prefixo_ip_1]", "", $this->conteudo);
				$this->conteudo = str_replace("[prefixo_ip_2]", "", $this->conteudo);
				$this->conteudo = str_replace("[prefixo_ip_3]", "", $this->conteudo);
				$this->conteudo = str_replace("[endereco_ip]", "", $this->conteudo);
			 	$this->conteudo = str_replace('[cliente_id]', "", $this->conteudo);
				$this->conteudo = str_replace('[interface_id]', $dados_cliente["sei_id"], $this->conteudo);
				$this->conteudo = str_replace('[capturar]', $this->permissoes[$this->dados_modulo["nome"]]["capturar_mac"] ? '<input type="button" name="capturar" value="Capturar" class="buttonAzulEscuroBranco11" style="width:60" onClick="capturarMAC('.$dados_cliente["servidor_id"].')">' : '', $this->conteudo);
			 	$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			 	$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar" onclick="mudaAssinatura(0)">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onclick="window.close()">';
				$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar&i={$_GET["i"]}", $this->conteudo);
			} else {
			 	$parametros = $this->buscaParametros($_POST["assinatura"]);
 				$velocidade_download = $parametros["pontos_velocidade_download"];
 				if (!$velocidade_download) $velocidade_download = 0;
 				$velocidade_upload = $parametros["pontos_velocidade_upload"];
 				if (!$velocidade_upload) $velocidade_upload = 0;
				$_POST["mac"] = $this->ehMacInvalido($_POST["mac"]) ? "NULL" : "'".strtolower($_POST["mac"])."'";
				$_POST["interface"] = $_POST["interface"] ? $_POST["interface"] : ($dados_cliente["sei_id"] ? $dados_cliente["sei_id"] : "NULL");
				
				/*
				if ($_POST["interface"]) {
					$_POST["ip"] = $this->buscaIPLivre($_POST["interface"], $_POST["assinatura"]);
					$_POST["ip"] = $_POST["ip"] ? "'{$_POST["ip"]}'" : "NULL";
				} else {
					$_POST["ip"] = "NULL";
				}
				*/
				
				if ($_POST["prefixo_ip_1"] !== "" && $_POST["prefixo_ip_2"] !== "" && $_POST["prefixo_ip_3"] !== "" && $_POST["endereco_ip"] !== "") {
					$_POST["ip"] = "'{$_POST["prefixo_ip_1"]}.{$_POST["prefixo_ip_2"]}.{$_POST["prefixo_ip_3"]}.{$_POST["endereco_ip"]}'";
				} else {
					$_POST["ip"] = "NULL";
				}
				
				$status_id = $_POST["mac"] == "NULL" ? 2 : 1;
				$regra = $this->buscaProximaRegra($_POST["interface"]);
				$query = "
					INSERT INTO
						tab_clientes_pontos (ass_id, cps_id, sei_id, clp_regra, clp_ip, clp_endereco_mac, clp_velocidade_download, clp_velocidade_upload)
					VALUES ({$_POST["assinatura"]}, $status_id, {$_POST["interface"]}, $regra, {$_POST["ip"]}, {$_POST["mac"]}, $velocidade_download, $velocidade_upload)";
			 	$this->bancodedados->query($query);
				$id = $this->bancodedados->ultimoIdInserido();
				// Verifica se assinatura tem ajuste e adiciona ajuste ao ponto
				$ajustes = $this->buscaAssinaturasComAjuste($_POST["assinatura"]);
				if ($ajustes) {
					foreach ($ajustes as $ajuste_id) {
						$query = "
					 		INSERT INTO
					 			rel_saj_clp_contem (saj_id, clp_id)
					 		VALUES
					 			($ajuste_id, $id)";
					 	$this->bancodedados->query($query);
					}
				}
			 	// Cancela PPPoE
				$query = "UPDATE tab_clientes_autenticacoes SET cas_id = 3, cau_publicado = 0 WHERE ass_id = {$_POST["assinatura"]} AND cas_id != 3";
			 	$this->bancodedados->query($query);
				// Adicionar comentário
				$this->registraComentario($id, "<b>Ação</b>: Adicionado", $_SESSION["usuario"]);
				if ($_POST["comentarios"]) {
					$this->registraComentario($id, $_POST["comentarios"], $_SESSION["usuario"]);
				}
				// Altera data de alteração do serviço
				$this->buscaDadosItem($id);
				$this->atualizaServicos($this->dados_item["servidor_id"]);
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
			}
		} else {
			$this->fechar();
		}
	}
	
	function buscaServicoAssinatura($assinatura_id) {
		$query = "
			SELECT
				tab_planos.pls_id AS servico_id
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
		 	WHERE
		 	  tab_assinaturas.ass_id = $assinatura_id";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			return $dados["servico_id"];
		}
	}

	###########
	### Ver ###
	###########

	function ver() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			$this->conteudo .= $this->carregaArquivo("clientes_pontos.ver.inc.php");
			$this->conteudo = str_replace("[assinatura]", $this->dados_item["assinatura"], $this->conteudo);
			$this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
		 	$this->conteudo = str_replace("[id_condominio]", $this->dados_item["id_condominio"], $this->conteudo);
		 	$this->conteudo = str_replace("[id]", $this->dados_item["cliente_id"], $this->conteudo);
			$this->conteudo = str_replace("[endereco_ip]", $this->dados_item["ip"], $this->conteudo);
			$this->conteudo = str_replace("[mac]", $this->dados_item["mac"], $this->conteudo);
			$this->conteudo = str_replace("[download]", $this->dados_item["download"], $this->conteudo);
			$this->conteudo = str_replace("[upload]", $this->dados_item["upload"], $this->conteudo);
			$this->conteudo = str_replace("[servidor]", $this->dados_item["interface_nome"], $this->conteudo);
			$this->conteudo = str_replace("[velocidade]", $this->dados_item["velocidade"], $this->conteudo);
		 	$this->conteudo = str_replace("[ajustes_programados]", $this->buscaAjustes($_GET["i"]), $this->conteudo);
		 	$this->conteudo = str_replace("[lista_comentarios]", $this->geraComentariosListagem($_GET["i"]), $this->conteudo);
			$this->conteudo = str_replace("[status_nome]", $this->dados_item["status_nome"], $this->conteudo);
		 	if ($this->ehPublicavel()) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:57" value="Publicar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&i='.$_GET["i"].'\';">';
		 	if ($this->dados_item["assinatura_status"] <= 2 && $this->dados_item["status_id"] == 2 && $this->permissoes[$this->dados_modulo["nome"]]["ativar"]) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" style="width:65" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$_GET["i"].'\';">';
		 	if ($this->dados_item["assinatura_status"] <= 2 && $this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["desativar"]) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" style="width:65" value="Desativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$_GET["i"].'\';">';
		 	if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" style="width:65" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$_GET["i"].'\';">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
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
			return;
		}
		if ($this->dados_item) {
			if (!$_POST) {
					$this->conteudo .= $this->carregaArquivo("clientes_pontos.editar.inc.php");
					$this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
					$this->conteudo = str_replace("[condominio_id]", $this->dados_item["id_condominio"], $this->conteudo);
					$this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
					$this->conteudo = str_replace("[assinatura]", $this->dados_item["assinatura"], $this->conteudo);
					$this->dados_item["ip"] = $this->dados_item["ip"] ? explode(".", $this->dados_item["ip"]) : array("", "", "", "");
					$this->conteudo = str_replace("[prefixo_ip_1]", $this->geraInput("text", "prefixo_ip_1", "53px", $this->dados_item["ip"][0], $this->permissoes["clientes_pontos"]["alterar_interface"], "3", "return formataNumeroIP(this, event)"), $this->conteudo);
					$this->conteudo = str_replace("[prefixo_ip_2]", $this->geraInput("text", "prefixo_ip_2", "53px", $this->dados_item["ip"][1], $this->permissoes["clientes_pontos"]["alterar_interface"], "3", "return formataNumeroIP(this, event)"), $this->conteudo);
					$this->conteudo = str_replace("[prefixo_ip_3]", $this->geraInput("text", "prefixo_ip_3", "53px", $this->dados_item["ip"][2], $this->permissoes["clientes_pontos"]["alterar_interface"], "3", "return formataNumeroIP(this, event)"), $this->conteudo);
					$this->conteudo = str_replace("[endereco_ip]", $this->geraInput("text", "endereco_ip", "53px", $this->dados_item["ip"][3], $this->permissoes["clientes_pontos"]["alterar_interface"], "3", "return formataNumeroIP(this, event)"), $this->conteudo);
					$this->conteudo = str_replace("[endereco_ip_classe]", $this->geraInput("text", "endereco_ip_classe", "53px", $this->dados_item["ip"][3], $this->permissoes["clientes_pontos"]["alterar_interface"], "3", "return formataNumeroIP(this, event)"), $this->conteudo);
					$this->conteudo = str_replace("[ponto_id]", $this->dados_item["id"], $this->conteudo);
					$this->conteudo = str_replace("[mac]", $this->dados_item["mac"], $this->conteudo);
					if ($this->permissoes[$this->dados_modulo["nome"]]["capturar_mac"]) {
						$this->conteudo = str_replace("[capturar]", '<input type="button" name="capturar" value="Capturar" class="buttonAzulEscuroBranco11" style="width:60" onClick="capturarMAC('.$this->dados_item["servidor_id"].')">', $this->conteudo);
					} else {
						$this->conteudo = str_replace("[capturar]", '', $this->conteudo);
					}
					if (in_array($this->dados_item["servico_id"], array(3, 5, 36))) {
						$this->conteudo = str_replace("[interface]", $this->geraSelect($this->buscaInterfaces(true, $this->dados_item["interface_id"]), $this->dados_item["interface_id"], true, "interface", "100%", "mudaInterface(this.value)", $this->permissoes["clientes_pontos"]["alterar_interface"]), $this->conteudo);
					} else {
						$this->conteudo = str_replace("[interface]", $this->geraSelect(array($this->dados_item["interface_id"] => $this->dados_item["interface_nome"]), $this->dados_item["interface_id"], true, "interface", "100%", "mudaInterface(this.value)", false), $this->conteudo);
					}
					$this->conteudo = str_replace("[velocidade]", $this->dados_item["velocidade"], $this->conteudo);
					$this->conteudo = str_replace("[comentario]", $this->dados_item["comentario"], $this->conteudo);
					$this->conteudo = str_replace("[ajustes_programados]", $this->buscaAjustes($_GET["i"]), $this->conteudo);
				 	$this->conteudo = str_replace("[lista_comentarios]", $this->geraComentariosListagem($_GET["i"]), $this->conteudo);
				 	$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
				 	// Botões
				 	if ($this->ehPublicavel()) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:57" value="Publicar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&i='.$_GET["i"].'\';">';
				 	if ($this->dados_item["status_id"] == 2 && $this->permissoes[$this->dados_modulo["nome"]]["ativar"]) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" style="width:65" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$_GET["i"].'\';">';
				 	if ($this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["desativar"]) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" style="width:65" value="Desativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$_GET["i"].'\';">';
					$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" style="width:45" value="Salvar">';
					$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" style="width:55" value="Limpar" onclick="mudaAssinatura(0)">';
				 	if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" style="width:65" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$_GET["i"].'\';">';
				 	if ($this->permissoes[$this->dados_modulo["nome"]]["cancelar"]) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" style="width:60" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$_GET["i"].'\';">';
					$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" style="width:50" value="Fechar" onclick="window.close()">';
					$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
					$this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].$this->janela, $this->conteudo);
				} else {
					$publicar_servidor = false;
					$_POST["mac"] = $this->ehMacInvalido($_POST["mac"], $this->dados_item["id"]) ? "'{$this->dados_item["mac"]}'" : "'".strtolower($_POST["mac"])."'";
					$_POST["interface"] = $_POST["interface"] ? $_POST["interface"] : ($this->dados_item["interface_id"] ? $this->dados_item["interface_id"] : "NULL");
					
					
					/*
					if ($this->permissoes["clientes_pontos"]["alterar_interface"]) {
						if ($_POST["prefixo_ip_1"] && $_POST["prefixo_ip_2"] && $_POST["prefixo_ip_3"] && $_POST["endereco_ip"]) {
					 		$_POST["ip"] = "{$_POST["prefixo_ip_1"]}.{$_POST["prefixo_ip_2"]}.{$_POST["prefixo_ip_3"]}.{$_POST["endereco_ip"]}";
					 		if ($_POST["endereco_ip_classe"]) {
					 			$_POST["ip"] .= "/{$_POST["endereco_ip_classe"]}";
					 		}
					 		$_POST["ip"] = "'{$_POST["ip"]}'";
						} else {
					 		$_POST["ip"] = "NULL";
						}
					} elseif ($_POST["interface"] != "NULL" && $_POST["interface"] != $this->dados_item["interface_id"]) {
						$_POST["ip"] = $this->buscaIPLivre($_POST["interface"], $this->dados_item["assinatura_id"], $this->dados_item["id"]);
						$_POST["ip"] = $_POST["ip"] ? "'{$_POST["ip"]}'" : "NULL";
					} else {
						$_POST["ip"] = $this->dados_item["ip"] ? "'{$this->dados_item["ip"]}'" : "NULL";
					}
					*/
					
					if ($this->permissoes["clientes_pontos"]["alterar_interface"]) {
						if ($_POST["prefixo_ip_1"] !== "" && $_POST["prefixo_ip_2"] !== "" && $_POST["prefixo_ip_3"] !== "" && $_POST["endereco_ip"] !== "") {
							$_POST["ip"] = "'{$_POST["prefixo_ip_1"]}.{$_POST["prefixo_ip_2"]}.{$_POST["prefixo_ip_3"]}.{$_POST["endereco_ip"]}'";
						} else {
							$_POST["ip"] = "NULL";
						}
					} else {
						$_POST["ip"] = $this->dados_item["ip"] ? "'{$this->dados_item["ip"]}'" : "NULL";
					}


					
					if ($_POST["interface"] != $this->dados_item["interface_id"]) {
						$publicar_servidor = true;
					}
					
					
					
					$_POST["status"] = !$this->dados_item["mac"] && $_POST["mac"] != "''" && $_POST["mac"] != "NULL" && $this->dados_item["status_id"] == 2 ? 1 : $this->dados_item["status_id"];
					$_POST["comentario"] = trim($_POST["comentario"]);
					$_POST["comentario"] = $_POST["comentario"] ? "'{$_POST["comentario"]}'" : "NULL";
					if (($this->dados_item["interface_id"] && $this->dados_item["interface_id"] != $_POST["interface"]) || (!$this->dados_item["interface_id"] && $_POST["interface"] != "NULL") || ("'{$this->dados_item["mac"]}'" != $_POST["mac"]) || ($this->dados_item["ip"] && "'{$this->dados_item["ip"]}'" != $_POST["ip"]) || (!$this->dados_item["ip"] && $_POST["ip"] != "NULL") || ($this->dados_item["comentario"] != $_POST["comentario"])) {
						$publicado = $this->dados_item["interface_id"] == $_POST["interface"] && "'{$this->dados_item["mac"]}'" == $_POST["mac"] && "'{$this->dados_item["ip"]}'" == $_POST["ip"] ? "1" : "0";
						$query = "
							UPDATE
								tab_clientes_pontos
							SET
								clp_ip = {$_POST["ip"]},
								clp_endereco_mac = {$_POST["mac"]},
								sei_id = {$_POST["interface"]},
								clp_publicado = $publicado,
								cps_id = {$_POST["status"]},
								clp_comentario = {$_POST["comentario"]}
							WHERE
								clp_id = {$_GET["i"]}";
						$this->bancodedados->query($query);
						// Atualiza Serviços
						if ($this->dados_item["servidor_id"]) {
							$this->atualizaServicos($this->dados_item["servidor_id"]);
						}
						// Registra Alteração MAC
						if ($this->dados_item["mac"] != substr($_POST["mac"], 1, -1)) {
							$this->dados_item["mac"] = $this->dados_item["mac"] ? $this->dados_item["mac"] : "Vazio";
							$this->registraComentario($_GET["i"], "<b>Ação</b>: Alteração de MAC: <b>{$this->dados_item["mac"]}</b> para <b>".substr($_POST["mac"], 1, -1)."</b>", $_SESSION["usuario"]);
							$publicar_servidor = true;
						}
						// Registra Alteração IP
						if ($_POST["ip"] != "NULL" && "'{$this->dados_item["ip"]}'" != $_POST["ip"]) {
							$this->dados_item["ip"] = $this->dados_item["ip"] ? $this->dados_item["ip"] : "Vazio";
							$this->registraComentario($_GET["i"], "<b>Ação</b>: Alteração de IP: <b>'{$this->dados_item["ip"]}'</b> para <b>{$_POST["ip"]}</b>", $_SESSION["usuario"]);
							$publicar_servidor = true;
						}
						// Agenda publicação servidor
						if ($publicar_servidor) {
							$query = "UPDATE tab_servidores SET ser_publicado = 0 WHERE ser_id = {$this->dados_item["servidor_id"]}";
							$this->bancodedados->query($query);
						}
						// Registra atividade
						$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
						$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
					}
					// Adicionar comentário
					if ($_POST["comentarios"]) {
						$this->registraComentario($_GET["i"], $_POST["comentarios"], $_SESSION["usuario"]);
					}
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				}
		} else {
			$this->fechar();
		}
	}

	##############
	### Ativar ###
	##############

	function ativar() {
		$this->buscaDadosItem($_GET["i"]);
		if (($cobrancas = $this->buscaCobrancasAtrasadas($this->dados_item["cliente_id"])) && (!$this->permissoes["clientes_pontos"]["gerenciar_inadimplentes"] || !$_POST)) {
			$this->conteudo .= $this->carregaArquivo("clientes_pontos.erros.inc.php");
			$this->conteudo = str_replace("[erro]", "Este cliente possui $cobrancas cobrança(s) em atraso.", $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&i={$_GET["i"]}", $this->conteudo);
			// Botões
			if ($this->permissoes["clientes_pontos"]["gerenciar_inadimplentes"]) {
				$this->conteudo = str_replace("[mensagem]", "Você deseja continuar com a ativação deste ponto?", $this->conteudo);
				$botoes[] = '<input type="submit" name="sim" class="buttonAzulEscuroBranco11" value="Sim">';
			} else {
				$this->conteudo = str_replace("[mensagem]", "Não é possível continuar com a ativação deste ponto!<br>Entre em contato com o Setor Financeiro!", $this->conteudo);
			}
			if (intval($_GET["cor"])) $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
		} else {
			if ($_GET["i"]) {
				if (!$_POST) {
					$this->conteudo .= $this->carregaArquivo("clientes_pontos.ativar.inc.php");
					$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
					$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
					$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
					$this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$_GET["i"], $this->conteudo);
					if ($this->ehPublicavel()) {
						$this->conteudo = str_replace('[publicar]', '<input type="checkbox" name="publicar" id="publicar" value="1"><label for="publicar"> Publicar alterações no servidor agora</label><br><br>', $this->conteudo);
					} else {
						$this->conteudo = str_replace('[publicar]', '', $this->conteudo);
					}
				} else {
					// Ativa Ponto
					$query = "UPDATE tab_clientes_pontos SET cps_id = 1, clp_publicado = 0 WHERE clp_id = {$_GET["i"]}";
			 		$this->bancodedados->query($query);
			 		// Cancela PPPoE
					$query = "UPDATE tab_clientes_autenticacoes SET cas_id = 3, cau_publicado = 0 WHERE ass_id = {$this->dados_item["assinatura_id"]} AND cas_id != 3";
			 		$this->bancodedados->query($query);
					// Altera data de alteração do serviço
					$this->atualizaServicos($this->dados_item["servidor_id"]);
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
	}

	#################
	### Desativar ###
	#################

	function desativar() {
		if ($_GET["i"]) {
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("clientes_pontos.desativar.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$_GET["i"], $this->conteudo);
				if ($this->ehPublicavel()) {
					$this->conteudo = str_replace('[publicar]', '<input type="checkbox" name="publicar" id="publicar" value="1"><label for="publicar"> Publicar alterações no servidor agora</label><br><br>', $this->conteudo);
				} else {
					$this->conteudo = str_replace('[publicar]', '', $this->conteudo);
				}
			} else {
				$query = "UPDATE tab_clientes_pontos SET cps_id = '2', clp_publicado = 0 WHERE clp_id = '{$_GET["i"]}'";
				$this->bancodedados->query($query);
				// Altera data de alteração do serviço
				$this->buscaDadosItem($_GET["i"]);
				$this->atualizaServicos($this->dados_item["servidor_id"]);
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
				$this->conteudo .= $this->carregaArquivo("clientes_pontos.remover.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'&id='.$this->dados_item["cliente_id"], $this->conteudo);
				if ($this->ehPublicavel()) {
					$this->conteudo = str_replace('[publicar]', '<input type="checkbox" name="publicar" id="publicar" value="1"><label for="publicar"> Publicar alterações no servidor agora</label><br><br>', $this->conteudo);
				} else {
					$this->conteudo = str_replace('[publicar]', '', $this->conteudo);
				}
			} else {
				// Copia Regra p/ Ponto Cancelado s/ Regra
				if ($this->dados_item["regra"] && ($ponto_id = $this->buscaPontoSemRegra($this->dados_item["servidor_id"]))) {
					$this->bancodedados->query("UPDATE tab_clientes_pontos SET clp_regra = {$this->dados_item["regra"]} WHERE clp_id = $ponto_id");		
				}
				// Agenda publicação servidor
				$query = "UPDATE tab_servidores SET ser_publicado = 0 WHERE ser_id = {$this->dados_item["servidor_id"]}";
				$this->bancodedados->query($query);
				// deleta comentários
				$query = "DELETE FROM tab_clientes_pontos_comentarios WHERE clp_id = '{$_GET["i"]}'";
				$this->bancodedados->query($query);
				// deleta links c/ ajuste
				$query = "DELETE FROM tab_clientes_pontos_ajustes WHERE clp_id = '{$_GET["i"]}'";
				$this->bancodedados->query($query);
				// deleta links c/ ajuste
				$query = "DELETE FROM rel_saj_clp_contem WHERE clp_id = '{$_GET["i"]}'";
				$this->bancodedados->query($query);
				// deleta ponto
				$query = "DELETE FROM tab_clientes_pontos WHERE clp_id = '{$_GET["i"]}'";
				$this->bancodedados->query($query);
				// Altera data de alteração do serviço
				$this->atualizaServicos($this->dados_item["servidor_id"]);
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
				$this->conteudo .= $this->carregaArquivo("clientes_pontos.cancelar.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=cancelar&i={$_GET["i"]}", $this->conteudo);
				if ($this->ehPublicavel()) {
					$this->conteudo = str_replace('[publicar]', '<input type="checkbox" name="publicar" id="publicar" value="1"><label for="publicar"> Publicar alterações no servidor agora</label><br><br>', $this->conteudo);
				} else {
					$this->conteudo = str_replace('[publicar]', '', $this->conteudo);
				}
			} else {
				// Atualiza Status
				$query = "UPDATE tab_clientes_pontos SET cps_id = 3, clp_publicado = 0, clp_parametros_ajuste = '' WHERE clp_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// Altera data de alteração do serviço
				$this->buscaDadosItem($_GET["i"]);
				$this->atualizaServicos($this->dados_item["servidor_id"]);
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

	#########################
	### Atualiza Servicos ###
	#########################

	function atualizaServicos($servidor_id) {
		$query = "
			UPDATE
				tab_servidores_servicos
				INNER JOIN tab_servidores_interfaces ON (tab_servidores_servicos.sei_id = tab_servidores_interfaces.sei_id)
			SET
				tab_servidores_servicos.ses_data_alteracao = NOW()
			WHERE
				tab_servidores_servicos.sst_id IN (1, 2, 3, 8) AND
				tab_servidores_interfaces.ser_id = $servidor_id";
		$this->bancodedados->query($query);
	}

	################
	### Publicar ###
	################

	function publicar() {
		$this->buscaDadosItem($_GET["i"]);
		if (!$_POST) {
			if ($_GET["sucesso"]) {
				$this->conteudo .= "<script type=\"text/javascript\">alert('Ponto publicado com sucesso!')</script>";
				$this->conteudo .= "<script type=\"text/javascript\">window.location = './?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}'</script>";
			} else {
				$this->conteudo .= $this->carregaArquivo("clientes_pontos.publicar.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				if ($_GET["erro"]) {
					$mensagem = "{$_GET["erro"]}<br><br>Clique em Confirmar para tentar novamente.";
				} else {
					$mensagem = "Você deseja realmente <b>publicar</b> este ponto?";
				}
				$this->conteudo = str_replace('[mensagem]', $mensagem, $this->conteudo);
				$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}".(array_key_exists("debug", $_GET) ? "&debug" : ""), $this->conteudo);
			}
		} else {
			$erro = $this->publicarServicos($this->dados_item["servidor_id"], $this->dados_item["id"]);
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

	function publicarServicos($servidor_id, $ponto_id) {
		$servidores = controle::instanciaObjeto("servidores", $this->bancodedados, $this->permissoes, false);
		return $servidores->publicarServicos($servidor_id, null, $ponto_id);
	}

	####################
	### Capturar MAC ###
	####################

	function capturar_mac() {
		$servidor = $this->buscaDadosServidor($_GET["i"]);
		if ($servidor) {
			$lista_mac = $this->capturaMacServidor($servidor["ip"], $servidor["sistema_operacional"]);
			$lista_mac = $this->trataListaMAC($lista_mac);
			$this->conteudo .= $this->carregaArquivo("clientes_pontos.capturar_mac.inc.php");
		 	$this->conteudo = str_replace('[lista_macs]', $this->geraSelect($lista_mac, null, true, "lista_mac", "220px"), $this->conteudo);
		 	$botoes[] = '<input type="button" name="confirmar" class="buttonAzulEscuroBranco11" style="width:75" value="Confirmar" onclick="enviarMAC()">';
		 	$botoes[] = '<input type="button" name="atualizar" class="buttonAzulEscuroBranco11" style="width:65" value="Atualizar" onclick="window.location=\'?m=clientes_pontos&a=capturar_mac&i='.$_GET["i"].'\'">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onclick="window.close()">';
			$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
		} else {
			$this->fechar();
		}
	}

	function buscaDadosServidor($servidor_id) {
		$query = "
			SELECT
				tab_servidores.ser_configuracao AS configuracao,
				CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) AS ip
			FROM
				tab_servidores
				INNER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
			WHERE
				tab_servidores.ser_id = '{$servidor_id}'";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			$configuracao = @unserialize($dados["configuracao"]);
			if ($configuracao["sistema_operacional"] == 1 || $configuracao["sistema_operacional"] == 2) {
				$configuracao["sistema_operacional"] = 1;
			} elseif ($configuracao["sistema_operacional"] == 3 || $configuracao["sistema_operacional"] == 4) {
				$configuracao["sistema_operacional"] = 2;
			}
			$dados["sistema_operacional"] = $configuracao["sistema_operacional"];
			return $dados;
		}
	}

	function capturaMacServidor($ip, $sistema_operacional) {
		if ($sistema_operacional == 1) { // Debian
			$comando = "tail -5000 /var/log/daemon.log | grep -i 'unknown client'";
			return $this->executar($ip, $comando);
		} elseif ($sistema_operacional == 2) { // Red Hat
			$comando = "tail -5000 /var/log/messages | grep -i 'unknown client'";
			return $this->executar($ip, $comando);
		}
	}

	function executar($ip, $comando) {
		$comando = "ssh -i ".SSH_CHAVE." ".SSH_USUARIO."@$ip sudo \"$comando\"";
		exec($comando, $saida);
		return $saida;
	}

	function trataListaMAC($lista) {
		$lista = @implode("\n", $lista);
		preg_match_all('/[a-zA-Z\d]{2}:[a-zA-Z\d]{2}:[a-zA-Z\d]{2}:[a-zA-Z\d]{2}:[a-zA-Z\d]{2}:[a-zA-Z\d]{2}/U', $lista, $lista);
		$lista = array_unique($lista[0]);
		foreach ($lista as $mac) {
			$macs[$mac] = $mac;
		}
		return $macs;
	}

	function formataListaMAC($lista) {
		if (count($lista)) {
			foreach ($lista as $mac) {
				$lista_formatada[] = "<a href=\"javascript:enviarMAC(\'$mac\')\" class=\"txtAzulEscuro11\">$mac</a>";
			}
			return implode("<br>", $lista_formatada);
		}
		return "";
	}

	##############
	### Listar ###
	##############

	function listar() {
		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;
		$this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=clientes_pontos" id="clientes_pontos.js"></script>';
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
		// Condomínio
		$condominios = $this->buscaCondominios();
		foreach ($condominios as $id => $nome) {
			$select_condominios[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "condominio", "descricao" => "Condomínio", "valor" => $select_condominios, "where" => "(tab_clientes.con_id = [valor])");
		// Interface
		$interfaces = $this->buscaInterfaces();
		foreach ($interfaces as $id => $nome) { 
			$select_interfaces[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "interface", "descricao" => "Interface", "valor" => $select_interfaces, "where" => "(tab_clientes_pontos.sei_id = [valor])");
		// IP
		$itens[] = array ("tipo" => "input", "nome" => "ip", "descricao" => "Endereço IP", "valor" => "", "where" => "(tab_clientes_pontos.clp_ip LIKE '%[valor]%')");
		// MAC
		$itens[] = array ("tipo" => "input", "nome" => "mac", "descricao" => "MAC", "valor" => "", "where" => "(tab_clientes_pontos.clp_endereco_mac LIKE '%[valor]%')");
		// Status
		$status = $this->buscaStatus();
		foreach ($status as $id => $nome) {
			$select_status[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_clientes_pontos.cps_id = [valor])");
		// Filtro
		$filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		return $filtro;
	}

	function geraPaginacao($filtro, $total, $pagina) {
		$paginacao = controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
		return $paginacao;
	}

	function buscaItens($filtro, $pagina) {
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		$query = "
			SELECT SQL_CALC_FOUND_ROWS
				tab_clientes_pontos.clp_id AS id,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				CONCAT(tab_condominios.con_nome, ', ', SUBSTRING(tab_clientes.cli_complemento, 2), IF(tab_condominios_blocos.cob_nome <> 'Único', CONCAT('&nbsp;/&nbsp;', tab_condominios_blocos.cob_nome), ''))	AS condominio,
				tab_clientes_pontos.clp_ip AS ip,
				CONCAT(tab_servidores.ser_nome, '<br>(', tab_servidores_interfaces.sei_nome, IF(interface_pai.sei_id IS NULL, '', CONCAT(' [', interface_pai.sei_nome, ']')), IF(tab_servidores_interfaces.sei_prefixo_ip IS NULL, '', CONCAT(', ', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip)), ')') AS interface,
				tab_clientes_pontos.clp_velocidade_download AS download,
				tab_clientes_pontos.clp_velocidade_upload AS upload,
		 		tab_clientes_pontos.clp_parametros_ajuste AS parametros_ajuste,
				tab_clientes_pontos.clp_endereco_mac AS mac,
				tab_clientes_pontos_status.cps_id AS status_id,
				tab_clientes_pontos_status.cps_nome AS status,
		 		tab_clientes_pontos.clp_publicado AS publicado,
	 			tab_clientes_pontos.clp_ajustado AS ajustado,
	 			tab_clientes_pontos.clp_comentario AS comentario,
	 			tab_clientes_origens.clo_nome AS origem
			FROM
				tab_clientes_pontos
				LEFT OUTER JOIN tab_servidores_interfaces ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
				LEFT OUTER JOIN tab_servidores_interfaces interface_pai ON (tab_servidores_interfaces.sei_pai = interface_pai.sei_id)
				LEFT OUTER JOIN tab_servidores ON (tab_servidores_interfaces.ser_id = tab_servidores.ser_id)
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
				INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
				INNER JOIN tab_clientes_pontos_status ON (tab_clientes_pontos_status.cps_id = tab_clientes_pontos.cps_id)
				INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
				LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
			WHERE
				$filtro->parametros_sql
			ORDER BY
				tab_condominios.con_nome,
				tab_condominios_blocos.cob_nome,
				SUBSTRING(tab_clientes.cli_complemento, 2) ASC
			LIMIT
				$limit_inicio,
				$limit_fim";
		$query = $this->bancodedados->query($query);
		$lista_unidades = $this->buscaUnidadesVelocidade();
		$linhas = $this->bancodedados->linhasTotal($query);
		$itens = array();
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
				$dados["link"] = "javascript:verPonto('".$dados["id"]."');";
			}
			$dados["status"] = str_replace(" (", "<br>(", $dados["status"]);
			// Dados Cliente
			$dados["cliente_nome"] = strlen($dados["cliente_nome"]) > 25 ? substr($dados["cliente_nome"], 0, 22)."..." : $dados["cliente_nome"];
			if ($this->permissoes["clientes"]["ver_dt"]) {
				$dados["cliente_id"] = "<a href=\"./?m=clientes&a=ver_dt&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
			}
			$dados["dados_cliente"][] = $dados["cliente_nome"];
			if ($dados["condominio"]) $dados["dados_cliente"][] = "({$dados["condominio"]})";
			$dados["dados_cliente"] = implode("<br>", $dados["dados_cliente"]);
			// Velocidade
			$unidade_download = floor(log($dados["download"], 1024));
			if ($unidade_download < 1) $unidade_download = 1;
			$dados["velocidade"] = round($dados["download"] / pow(1024, $unidade_download), 1)." / ".
			round($dados["upload"] / pow(1024, $unidade_download), 1)." ".$lista_unidades[$unidade_download];
			if ($dados["parametros_ajuste"] && $dados["ajustado"]) {
				$parametros = unserialize($dados["parametros_ajuste"]);
				$unidade = floor(log($parametros["download"], 1024));
				if ($unidade < 1) $unidade = 1;
				$velocidade = round($parametros["download"] / pow(1024, $unidade), 1)." / ".round($parametros["upload"] / pow(1024, $unidade), 1)." ".
				$lista_unidades[$unidade];
				$dados["velocidade"] .= "<br>($velocidade)";
			}
			// MAC (IP)
			$dados["mac_ip"] = $dados["mac"];
			if ($dados["ip"]) $dados["mac_ip"] .= "<br>({$dados["ip"]})";
		 	// trata Status
			if ($dados["status_id"] != 3) {
			 	if ($dados["ajustado"]) {
					$dados["status"] .= " (Ajustado)";
				}
				if ($dados["publicado"]) {
					$dados["status"] .= "<br>Publicado";
				} else {
					$dados["status"] .= "<br>Não Publicado";
				}
			}
//			if ($dados["comentario"]) {
//				$dados["dados_cliente"] .= "<br><span style=\"color:#232323\">[{$dados["comentario"]}]</span>";
//			}
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");

			if ($dados["comentario"]) {
				$dados["rowspan"] = array("contador" => 2);
			}
			$itens[] = $dados;
			if ($dados["comentario"]) {
				$itens[] = array("contador" => "<div style=\"color:#232323;text-align:left\">{$dados["comentario"]}</div>", "colspan" => array("contador" => 7));
			}
		}
		return array("linhas" => $linhas, "itens" => $itens);
	}

	function geraListagem ($itens) {
		$cabecalho = array ("contador" => "#", "cliente_id" => "ID", "dados_cliente" => "Cliente (Condomínio)", "interface" => "Servidor (Interface)", "mac_ip" => "MAC (IP)", "velocidade" => "Velocidade", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20", "35", "", "180", "95", "95", "85"), null, array("right", "center", "center", "center", "center"));
		return $listagem;
	}

	function buscaInterfaces($somente_ativos = false, $interface_atual = null) {
		$query = "
			SELECT
				tab_servidores_interfaces.sei_id AS id,
				tab_servidores.ser_nome AS servidor_nome,
				tab_servidores_interfaces.sei_nome AS interface_nome,
				CONCAT(tab_servidores.ser_nome, ' (', tab_servidores_interfaces.sei_nome, IF(interface_pai.sei_id IS NULL, '', CONCAT(' [', interface_pai.sei_nome, ']')), IF(tab_servidores_interfaces.sei_prefixo_ip IS NULL OR tab_servidores_interfaces.sei_endereco_ip IS NULL, '', CONCAT(', ', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip)), ')') AS nome
			FROM
				tab_servidores_interfaces
				INNER JOIN tab_servidores ON (tab_servidores_interfaces.ser_id = tab_servidores.ser_id)
				LEFT OUTER JOIN tab_servidores_interfaces interface_pai ON (tab_servidores_interfaces.sei_pai = interface_pai.sei_id)
			WHERE
				(tab_servidores_interfaces.sit_id IN (1, 6)) AND
				((tab_servidores_interfaces.sit_id = 1 AND tab_servidores_interfaces.sei_pai IS NULL) OR
				 (tab_servidores_interfaces.sit_id = 6 AND tab_servidores_interfaces.sei_pai IS NOT NULL))".($somente_ativos ? " AND
				((tab_servidores_interfaces.sia_id = 1 AND tab_servidores.set_id = 1)".($interface_atual ? " OR
				 (tab_servidores_interfaces.sei_id = $interface_atual))" : ")") : "")."
			ORDER BY
				tab_servidores.ser_nome,
				tab_servidores_interfaces.sei_nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["ordem"] = sprintf("% -80s", $dados["servidor_nome"]).sprintf("% -15s", preg_replace("/[^a-zA-Z _]/", "", $dados["interface_nome"])).sprintf("%05s", preg_replace("/[^0-9]/", "", $dados["interface_nome"]));
			$interfaces[] = $dados;
		}
		// $interfaces = $this->ordena_multiarray($interfaces, array("ordem"));
		foreach ($interfaces as $dados) {
			$retorno[$dados["id"]] = $dados["nome"];
		}
		return $retorno;
	}

	function ordena_multiarray($array, $campos) {
		if (is_array($array) && sizeof($array)) {
			$tamanho_chave = 0;
			reset($array);
			while (($array_ordenacao_atual = current($array)) != false) {
				for ($i = 0; $i < sizeof($campos); $i++) {
					$chave[] = $array_ordenacao_atual[$campos[$i]];
				}
				$chave = @implode(",", $chave);
				$tamanho_chave = strlen($chave) > $tamanho_chave ? strlen($chave) : $tamanho_chave;
				unset($chave);
				next($array);
			}
			reset($array);
			$array_ordenacao = array();
			while (($array_ordenacao_atual = current($array)) != false) {
				for ($i = 0; $i < sizeof($campos); $i++) {
					$chave[] = $array_ordenacao_atual[$campos[$i]];
				}
				$chave = @implode(",", $chave);
				if (!$array_ordenacao[$chave]) {
					$array_ordenacao[sprintf("% -{$tamanho_chave}s", $chave)."_".key($array)] = key($array);
				}
				unset($chave);
				next($array);
			}
			ksort($array_ordenacao);
			while (is_int($array_id = current($array_ordenacao))) {
				$array_final[] = $array[$array_id];
				next($array_ordenacao);
			}
			return $array_final;
		}
	}

	function buscaInterfacesAssinatura($condominio, $interface_id) {
		if (!$condominio) {
			return $this->buscaInterfaces(true);
		} elseif ($interface_id) {
			$dados_interface = $this->buscaDadosInterface($interface_id);
			if ($dados_interface) {
				return array($interface_id => $dados_interface["interface_nome"]);
			}
		}
	}

	function buscaDadosAssinatura($assinatura_id) {
		$query = "
			SELECT
				tab_assinaturas.ass_id AS id,
				tab_planos.pla_id AS plano_id,
				tab_planos.pls_id AS servico_id
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				tab_assinaturas.ass_id = $assinatura_id";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			return $dados;
		}
	}

	function buscaCondominios() {
		$query = "
			SELECT
				con_id AS id,
				CONCAT(tab_condominios.con_nome,' (',tab_cidades.cid_nome,'/',tab_bairros.bai_nome,')') AS nome
			FROM
				tab_condominios
				INNER JOIN tab_logradouros ON (tab_condominios.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_condominios.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_condominios.cid_id = tab_cidades.cid_id)
			WHERE
				cos_id = 2
			ORDER BY
				nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$condominios[$dados["id"]] = $dados["nome"];
		}
		return $condominios;
	}

	function buscaStatus () {
		$query = "SELECT cps_id AS id, cps_nome AS nome FROM tab_clientes_pontos_status ORDER BY nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$status[$dados["id"]] = $dados["nome"];
		}
		return $status;
	}

	###################
	### Comentários ###
	###################

	function registraComentario($id, $conteudo, $responsavel) {
		$responsavel = ($responsavel) ? "'$responsavel'" : "NULL";
		$conteudo = addslashes($conteudo);
		$query = "
			INSERT INTO tab_clientes_pontos_comentarios
				(cpc_datahora, clp_id, cpc_conteudo, usu_id)
			VALUES
				(NOW(), '$id', '$conteudo', $responsavel)";
		$this->bancodedados->query($query);
	}

	function buscaComentarios($id) {
		$query = "
			SELECT
				IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel,
				DATE_FORMAT(tab_clientes_pontos_comentarios.cpc_datahora, '%d/%m/%Y (%H:%i)') AS data,
				tab_clientes_pontos_comentarios.cpc_conteudo AS conteudo
			FROM
				tab_clientes_pontos_comentarios
				LEFT OUTER JOIN tab_usuarios ON (tab_clientes_pontos_comentarios.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_clientes_pontos_comentarios.clp_id = $id";
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

}

?>
