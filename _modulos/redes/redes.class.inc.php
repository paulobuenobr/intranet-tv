<?

class redes {

	var $bancodedados;
	var $permissoes;
	var $acao;
	var $conteudo;
	var $dados_item;
	var $dados_modulo;
	var $dados_acao;
	var $servico_id;

	function __construct($bancodedados, $permissoes, $executar = true) {
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		$this->servico_id =  12;
		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "topologia");
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
	}

	function geraTitulo() {
		$titulo  = '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
		$titulo .= '<tr align="center" class="txtBranco11">';
		$titulo .= '<td bgcolor="#505050"><b>';
		$titulo .= $this->dados_modulo["descricao"];
		$titulo .= '</b></td></tr></table>';
		return $titulo;
	}

	function carregaArquivo($arquivo) {
		if (file_exists(dirname(__FILE__)."/".$arquivo)) $arquivo = dirname(__FILE__)."/".$arquivo;
		$fp = fopen($arquivo, "r");
		$retorno = fread($fp, filesize($arquivo));
		fclose($fp);
		return $retorno;
	}

	function buscaDadosModulo($nome) {
		$query = "SELECT mod_id AS id, mod_nome AS nome, mod_descricao AS descricao FROM tab_modulos WHERE mod_nome = '$nome'";
		$query = $this->bancodedados->query($query);
		if ($this->bancodedados->linhas($query)) {
			$this->dados_modulo = $this->bancodedados->resultadoArray($query);
		}
	}

	function buscaDadosAcao($nome) {
		$query = "SELECT aco_id AS id, aco_nome AS nome, aco_descricao AS descricao, act_id AS tipo FROM tab_acoes WHERE aco_nome = '$nome' and mod_id = '{$this->dados_modulo["id"]}'";
		$query = $this->bancodedados->query($query);
		if ($this->bancodedados->linhas($query)) {
			$this->dados_acao = $this->bancodedados->resultadoArray($query);
			$this->acao = $this->dados_acao["nome"];
		}
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

	function regeraIPs($servidor_id = null, $interface_id = null) {
    $servidores = controle::instanciaObjeto("servidores", $this->bancodedados, $this->permissoes, false);
    $servidores->regerarIPs(null, $interface_id);
	}

	#################
	### Topologia ###
	#################

	function topologia() {
		$this->conteudo .= $this->carregaArquivo("redes.topologia.inc.php");
		$this->conteudo = str_replace('[topologia]', $this->buscaTopologia(), $this->conteudo);
	}

	function buscaTopologia($rede = "") {
		$dados[] = $this->buscaAdicionarRede($rede);
		$dados = funcoes::mescla_arrays_recursivo($dados, $this->buscaRedes());
		return $this->geraArvore($dados);
	}

	function buscaAdicionarRede($rede = "") {
		$conteudo[] = '<table cellpadding="0" cellspacing="0" border="0"><tr class="txtAzulEscuro11">';
		$conteudo[] = '<td><input name="nova_rede" id="nova_rede" type="text" class="inputBrancoAzulEscuro11" style="width:105px;height:15px;font-size:10px;padding-top:0px;padding-bottom:0px;padding-left:2px;padding-right:2px" size="50" maxlength="80" value="'.$rede.'" onkeypress="if ((ehNetscape() ? event.which : event.keyCode) == 13) novaRede()">';
		$conteudo[] = '<td>&nbsp;<a href="javascript:novaRede()" class="txtAzulEscuro11">[OK]</a>';
		$conteudo[] = '</tr></table>';
		return array("rede" => implode("", $conteudo));
	}

	function geraArvore(&$dados, $nivel = 0, $colunas = null, &$dark = false, &$total_pontos = null, &$total_redes = 0, &$total_redes_ocupadas = 0) {
		for ($i = 0; $i < count($dados); $i++) {
			if (array_key_exists(0, $dados[$i])) {
				if ($i == count($dados) - 1 && count($dados) > 1) {
					$colunas[$nivel] = "";
				} elseif ($i == count($dados) - 1) {
					$colunas[$nivel] = "L";
				} else {
					$colunas[$nivel] = "|";
				}
				$conteudo .= $this->geraArvore($dados[$i], $nivel + 1, $colunas, $dark, $total_pontos, $total_redes, $total_redes_ocupadas);
			} else {
				if (array_key_exists($i + 1, $dados) && array_key_exists(0, $dados[$i + 1])) {
					$ramos_livres = $this->possuiRamosLivres($dados[$i + 1]);
					$ramos_ocupados = $this->possuiRamosOcupados($dados[$i + 1]);
				} else {
					$ramos_livres = $this->possuiRamosLivres($dados[$i]);
					$ramos_ocupados = $this->possuiRamosOcupados($dados[$i]);
				}
				if ($i == 0 && $nivel == 0 && count($dados) > 1) {
					$colunas[$nivel] = "T";
				} elseif ($i == 0 && $nivel == 0) {
					$colunas[$nivel] = "L";
				} elseif (($i == count($dados) - 1) || ($i == count($dados) - 2 && array_key_exists(0, $dados[$i + 1]))) {
					$colunas[$nivel] = "L";
				} else {
					$colunas[$nivel] = "+";
				}
				$conteudo .= '<table cellspacing="0" cellpadding="0" border="0" width="100%">';
				$conteudo .= '<tr class="txtAzulEscuro11"'.($dark ? " style=\"background-color:#dae5f0\"" : "").'>';
				for ($j = 0; $j <= $nivel; $j++) {
					if ($dados[$i]["id"] && !$dados[$i]["redes_filhas"] && !$dados[$i]["interface_id"] && $dados[$i]["mascara"] < 30) {
						$juncao = "plus";
						$link = "expandeRede({$dados[$i]["id"]})";
					} elseif ($ramos_livres && !$ramos_ocupados && $dados[$i]["redes_filhas"]) {
						$juncao = "minus";
						$link = "reduzRede({$dados[$i]["id"]})";
					} else {
						$juncao = "join";
						$link = "";
					}
					if ($colunas[$j] == "|") {
						$conteudo .= "<td width=\"19\"><img src=\"imagens/line.gif\" width=\"19\" height=\"16\"></td>";
					} elseif ($colunas[$j] == "+" || $colunas[$j] == "T") {
						$conteudo .= "<td width=\"19\"><img src=\"imagens/{$juncao}bottom.gif\" width=\"19\" height=\"16\"".($link ? " onclick=\"$link\" style=\"cursor:pointer\"" : "")."></td>";
					} elseif ($colunas[$j] == "L") {
						$conteudo .= "<td width=\"19\"><img src=\"imagens/{$juncao}.gif\" width=\"19\" height=\"16\"".($link ? " onclick=\"$link\" style=\"cursor:pointer\"" : "")."></td>";
					} else {
						$conteudo .= "<td width=\"19\"></td>";
					}
				}
				if ($dados[$i]["id"] && !$dados[$i]["redes_filhas"]) {
					$total_redes += pow(2, 32 - $dados[$i]["mascara"]) - 3 - $dados[$i]["reserva_ips"];
					if ($dados[$i]["interface_id"]) {
						$total_pontos[$dados[$i]["interface_id"]] = $dados[$i]["quantidade_pontos"];
						$total_redes_ocupadas += pow(2, 32 - $dados[$i]["mascara"]) - 3 - $dados[$i]["reserva_ips"];
					}
				}
				$conteudo .= "<td style=\"padding-left:2px\"><span".($link ? " onclick=\"$link\" style=\"cursor:pointer" : " style=\"").(!$dados[$i]["redes_filhas"] ? ";color:#232323\"" : "\"").$this->geraHint($dados[$i]).">{$dados[$i]["rede"]}</span> {$dados[$i]["links"]}</td>";
				$conteudo .= "<td width=\"40\" align=\"center\" id=\"celula_dhcp_{$dados[$i]["id"]}\">".($dados[$i]["id"] ? (!$dados[$i]["redes_filhas"] ? "<input type=\"checkbox\" id=\"checkbox_dhcp_{$dados[$i]["id"]}\" style=\"width:12px;height:12px;margin:0px\"".($dados[$i]["publicar_dhcp"] ? " checked" : "")." onclick=\"defineDHCP({$dados[$i]["id"]}, this.checked)\">" : "") : "<b>DHCP</b>")."</td>";
				$conteudo .= "<td width=\"170\" align=\"left\" id=\"celula_servidor_{$dados[$i]["id"]}\">".($dados[$i]["id"] ? ($dados[$i]["servidor_id"] ? "<span style=\"cursor:pointer\" onclick=\"exibeInfo({$dados[$i]["id"]}, {$dados[$i]["servidor_id"]})\" class=\"txtAzulEscuro11\">{$dados[$i]["servidor_nome"]}</span>" : "").($dados[$i]["servidor_id"] && !$dados[$i]["interface_id"] && !$dados[$i]["rede_pai"]["servidor_id"] ? " ".$this->buscaLinkLimpaServidor($dados[$i]["id"]) : "").($ramos_livres && !$dados[$i]["interface_id"] ? " ".$this->buscaLinkDefineServidor($dados[$i]["id"], $dados[$i]["servidor_id"], $ramos_ocupados || $dados[$i]["redes_filhas"]) : "") : "<b>Servidor</b>")."</td>";
				$conteudo .= "<td width=\"85\" align=\"left\" id=\"celula_interface_{$dados[$i]["id"]}\">".($dados[$i]["id"] ? ($dados[$i]["interface_id"] ? $dados[$i]["interface_nome"]." ".$this->buscaLinkLimpaInterface($dados[$i]["id"]) : "").(!$dados[$i]["redes_filhas"] && $dados[$i]["servidor_id"] ? " ".$this->buscaLinkDefineInterface($dados[$i]["id"], $dados[$i]["servidor_id"], $dados[$i]["interface_id"]) : "") : "<b>Interface</b>")."</td>";
				$conteudo .= "<td width=\"65\" align=\"right\">".($dados[$i]["id"] ? $dados[$i]["pontos"] : "<b>Pontos</b>")."</td>";
				$conteudo .= "<td width=\"30\" align=\"right\">".($dados[$i]["ocupacao"] !== null ? $this->geraBarra($dados[$i]["ocupacao"]) : "")."</td>";
				$conteudo .= "<td width=\"5\"></td>";
				$conteudo .= "</tr>";
				$conteudo .= "</table>";
				$dark = !$dark;
			}
		}
		if ($nivel == 0) {
			$total_pontos = $this->somaTotal($total_pontos);
			$conteudo .= '<table cellspacing="0" cellpadding="0" border="0" width="100%">';
			$conteudo .= '<tr><td colspan="6" height="3"></td></tr>';
			$conteudo .= '<tr><td colspan="6" height="1" style="background-color:#505050"></td></tr>';
			$conteudo .= '<tr><td colspan="6" height="2"></td></tr>';
			$conteudo .= '<tr class="txtAzulEscuro11"'.($dark ? " style=\"background-color:#dae5f0\"" : "").'>';
			$conteudo .= "<td align=\"right\" style=\"color:#232323\">Alocado</td>";
			$conteudo .= "<td width=\"100\" align=\"right\">".number_format($total_redes_ocupadas, 0, "", ".")." / ".number_format($total_redes, 0, "", ".")."</td>";
			$conteudo .= "<td width=\"40\" align=\"right\">".$this->geraBarra($total_redes_ocupadas / $total_redes * 100)."</td>";
			$conteudo .= "<td width=\"5\"></td>";
			$conteudo .= "</tr>";
			$conteudo .= '<tr><td colspan="6" height="3"></td></tr>';
			$conteudo .= '<tr><td colspan="6" height="1" style="background-color:#505050"></td></tr>';
			$conteudo .= '<tr><td colspan="6" height="2"></td></tr>';
			$dark = !$dark;
			$conteudo .= '<tr class="txtAzulEscuro11"'.($dark ? " style=\"background-color:#dae5f0\"" : "").'>';
			$conteudo .= "<td align=\"right\" style=\"color:#232323\">Utilizado</td>";
			$conteudo .= "<td width=\"100\" align=\"right\">".number_format($total_pontos, 0, "", ".")." / ".number_format($total_redes, 0, "", ".")."</td>";
			$conteudo .= "<td width=\"40\" align=\"right\">".$this->geraBarra($total_pontos / $total_redes * 100)."</td>";
			$conteudo .= "<td width=\"5\"></td>";
			$conteudo .= "</tr>";
			$conteudo .= '<tr><td colspan="6" height="3"></td></tr>';
			$conteudo .= '<tr><td colspan="6" height="1" style="background-color:#505050"></td></tr>';
			$conteudo .= "</table>";
		}
		return $conteudo;
	}

	function possuiRamosLivres(&$dados) {
		if (!array_key_exists(0, $dados)) {
			return !$dados["servidor_id"] ? true : false;
		}
		for ($i = 0; $i < count($dados); $i++) {
			if (array_key_exists(0, $dados[$i])) {
				if ($this->possuiRamosLivres($dados[$i])) {
					return true;
				}
			} elseif (!$dados[$i]["servidor_id"] && (!array_key_exists($i + 1, $dados) || !array_key_exists(0, $dados[$i + 1]))) {
				return true;
			}
		}
		return false;
	}

	function possuiRamosOcupados(&$dados) {
		if (!array_key_exists(0, $dados)) {
			return $dados["servidor_id"] ? true : false;
		}
		for ($i = 0; $i < count($dados); $i++) {
			if (array_key_exists(0, $dados[$i])) {
				if ($this->possuiRamosOcupados($dados[$i])) {
					return true;
				}
			} elseif ($dados[$i]["servidor_id"] && (!array_key_exists($i + 1, $dados) || !array_key_exists(0, $dados[$i + 1]))) {
				return true;
			}
		}
		return false;
	}

	function somaTotal($soma) {
		if (!$soma) {
			return 0;
		}
		foreach ($soma as $valor) {
			$total += $valor;
		}
		return $total;
	}

	function geraBarra($ocupacao) {
		if ($ocupacao >= 75) {
			$cor = "e10202";
		} elseif ($ocupacao >= 50) {
			$cor = "ffca29";
		} else {
			$cor = "28c802";
		}
		$conteudo[] = "<div style=\"width:20px;height:6px;padding:1px;border:1px solid #AAAAAA;cursor:pointer;background-color:#FFFFFF\" align=\"left\" title=\"".number_format($ocupacao, 0, ".", "")."%\">";
		$conteudo[] = "<div style=\"height:6px;width:".ceil(20 * $ocupacao / 100)."px;background-color:#$cor;font-size:1px\"></div>";
		$conteudo[] = "</div>";
		return implode($conteudo);
	}

	function geraHint($dados) {
		if (!$dados["id"]) {
			return "";
		}
		$hint[] = '<table cellpadding=\\\'0\\\' cellspacing=\\\'0\\\' border=\\\'0\\\' width=\\\'100%\\\'>';
		$hint[] = '<tr class=\\\'txtBranco11\\\' width=\\\'40%\\\'><td>Host » Mínimo:</td><td>'.$this->geraIP($dados["endereco_ip"], 1, $dados["mascara"]).'</td></tr>';
		$hint[] = '<tr class=\\\'txtBranco11\\\'><td>Host » Máximo:</td><td>'.$this->geraIP($dados["endereco_ip"], pow(2, 32 - $dados["mascara"]) - 3, $dados["mascara"]).'</td></tr>';
		$hint[] = '<tr class=\\\'txtBranco11\\\'><td>Máscara de Rede:</td><td>'.$this->formataMascara($dados["mascara"]).'</td></tr>';
		$hint[] = '<tr class=\\\'txtBranco11\\\'><td>Número de hosts:</td><td>'.number_format(pow(2, 32 - $dados["mascara"]) - 2, 0, "", ".").'</td></tr>';
		$hint[] = '</table>';
		$hint = implode($hint);
		return " onmouseover=\"hintShow('$hint', 200)\" onmouseout=\"hintHide()\"";
	}

	function buscaDadosRede($rede_id) {
		$query = "
  	  SELECT
  	    tab_redes.red_id AS id,
  	    tab_redes.red_endereco_ip AS endereco_ip,
  	    tab_redes.red_mascara AS mascara,
  	    tab_redes.red_publicar_dhcp AS publicar_dhcp,
  	    tab_redes.red_pai AS rede_pai,
  	    tab_redes.ser_id AS servidor_id,
  	    tab_redes.sei_id AS interface_id,
  	    COUNT(redes_filhas.red_id) AS redes_filhas
  	  FROM
  	    tab_redes
  	    LEFT OUTER JOIN tab_redes redes_filhas ON (tab_redes.red_id = redes_filhas.red_pai)
  	  WHERE
  	    tab_redes.red_id = $rede_id
  	  GROUP BY
  	    tab_redes.red_id";
		$query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
			$dados["rede"] = "{$dados["endereco_ip"]}/{$dados["mascara"]}";
			return $dados;
		}
	}

	function buscaServidores() {
		$query = "
			SELECT
			  tab_servidores.ser_id AS id,
			  IF(tab_servidores_interfaces.sei_prefixo_ip IS NULL, tab_servidores.ser_nome, CONCAT(tab_servidores.ser_nome, ' (', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip, ')')) AS nome,
				COUNT(DISTINCT tab_clientes_pontos.clp_id) AS pontos_total,
				IF(COUNT(tab_servidores_servicos.ses_id) > 0, 'Com Publicação 2G', 'Sem Publicação 2G') AS grupo
			FROM
			  tab_servidores
			  LEFT OUTER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
			  LEFT OUTER JOIN tab_servidores_interfaces tab_interfaces_clientes ON (tab_servidores.ser_id = tab_interfaces_clientes.ser_id)
			  LEFT OUTER JOIN tab_servidores_servicos ON (tab_interfaces_clientes.sei_id = tab_servidores_servicos.sei_id AND tab_servidores_servicos.sst_id = {$this->servico_id})
			  LEFT OUTER JOIN tab_clientes_pontos ON (tab_interfaces_clientes.sei_id = tab_clientes_pontos.sei_id AND tab_clientes_pontos.cps_id = 1)
			GROUP BY
				tab_servidores.ser_id
			ORDER BY
				grupo,
			  nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["nome"] = "{$dados["nome"]} [{$dados["pontos_total"]}]";
			$servidores[] = $dados;
		}
		return $servidores;
	}

	function buscaInterfaces($servidor_id, $resumido = true, $incluir_servidor_servidor = true) {
	  /*
		$query = "
  		SELECT
  			tab_servidores_interfaces.sei_id AS id,
  			tab_servidores_interfaces.sei_nome AS interface_nome,
  			tab_servidores_interfaces.sit_id AS tipo_id,
  			tab_servidores_interfaces.sei_reserva_ips AS reserva_ips,
  			COUNT(tab_clientes_pontos.clp_id) AS pontos_total
  	  FROM
  	    tab_servidores_interfaces
  	    LEFT OUTER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
  	    LEFT OUTER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id AND tab_clientes_pontos.cps_id = 1)
  	    LEFT OUTER JOIN tab_redes ON (tab_servidores_interfaces.sei_id = tab_redes.sei_id)
  	  WHERE
  	  	tab_servidores_interfaces.ser_id = $servidor_id AND
  	  	tab_servidores_interfaces.sit_id IN (1".($incluir_servidor_servidor ? ", 3" : "").", 6) AND
        ((tab_servidores_interfaces.sit_id = 1 AND tab_servidores_interfaces.sei_pai IS NULL) OR
	  		 (tab_servidores_interfaces.sit_id = 6 AND tab_servidores_interfaces.sei_pai IS NOT NULL AND tab_redes.red_id IS NULL)".($incluir_servidor_servidor ? " OR
	  		 (tab_servidores_interfaces.sit_id = 3)" : "")." ) AND
  	    (tab_servidores_servicos.sst_id = {$this->servico_id}".($incluir_servidor_servidor ? " OR
  	     tab_servidores_interfaces.sit_id = 3" : "").")
  		GROUP BY
  		  tab_servidores_interfaces.sei_id
  	  ORDER BY
  	    tab_servidores_interfaces.sei_nome";
    */
		$query = "
  		SELECT
  			tab_servidores_interfaces.sei_id AS id,
  			tab_servidores_interfaces.sei_nome AS interface_nome,
  			tab_servidores_interfaces.sit_id AS tipo_id,
  			tab_servidores_interfaces.sei_reserva_ips AS reserva_ips,
  			COUNT(tab_clientes_pontos.clp_id) AS pontos_total
  	  FROM
  	    tab_servidores_interfaces
  	    LEFT OUTER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id AND tab_clientes_pontos.cps_id = 1)
  	    LEFT OUTER JOIN tab_redes ON (tab_servidores_interfaces.sei_id = tab_redes.sei_id)
  	  WHERE
  	  	tab_servidores_interfaces.ser_id = $servidor_id AND
  	  	tab_servidores_interfaces.sit_id IN (1".($incluir_servidor_servidor ? ", 3" : "").", 6) AND
        ((tab_servidores_interfaces.sit_id = 1 AND tab_servidores_interfaces.sei_pai IS NULL) OR
	  		 (tab_servidores_interfaces.sit_id = 6 AND tab_servidores_interfaces.sei_pai IS NOT NULL AND tab_redes.red_id IS NULL)".($incluir_servidor_servidor ? " OR
	  		 (tab_servidores_interfaces.sit_id = 3)" : "").")
  		GROUP BY
  		  tab_servidores_interfaces.sei_id
  	  ORDER BY
  	    tab_servidores_interfaces.sei_nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["pontos_livres"] = $this->buscaQuantidadeHostsInterface($dados["id"]);
			if ($resumido || (($dados["pontos_total"] || $dados["reserva_ips"]) && !$dados["pontos_livres"])) {
				$dados["pontos_total"] = $dados["pontos_total"] ? $dados["pontos_total"] : $dados["reserva_ips"];
				$dados["ordem"] = sprintf("% -15s", preg_replace("/[^a-zA-Z _]/", "", $dados["interface_nome"])).sprintf("%05s", preg_replace("/[^0-9]/", "", $dados["interface_nome"]));
				$dados["pontos"] = $dados["pontos_total"] - $dados["pontos_livres"];
				$dados["nome"] = "{$dados["interface_nome"]} ({$dados["pontos_total"]} / {$dados["pontos_livres"]})";
				$interfaces[] = $dados;
			}
		}
		if (!$resumido) {
			return $interfaces;
		}
		$interfaces = $this->ordena_multiarray($interfaces, array("ordem"));
		foreach ($interfaces as $dados) {
			$retorno[$dados["id"]] = $dados["nome"];
		}
		return $retorno;
	}

	function buscaQuantidadeHostsInterface($interface_id) {
		$redes = 0;
		$query = "
  	  SELECT
  	    tab_redes.red_id AS id,
  	    tab_redes.red_mascara AS mascara,
  	    tab_redes.red_reserva_ips AS reserva_ips
  	  FROM
  	    tab_redes
  	  WHERE
  	    tab_redes.sei_id = $interface_id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$redes += pow(2, 32 - $dados["mascara"]) - 3 - $dados["reserva_ips"];
		}
		return $redes;
	}

	function adicionarNovaRede($rede) {
		$rede = explode("/", $rede);
		$rede[0] = $this->geraIP($rede[0], 0, $rede[1]);
		$query = "INSERT INTO tab_redes (red_endereco_ip, red_mascara) VALUES ('$rede[0]', $rede[1])";
		$this->bancodedados->query($query);
		return true;
	}

	function expandeRede($rede_id) {
		if (($rede = $this->buscaDadosRede($rede_id)) && !$rede["redes_filhas"]) {
			$servidor_id = $rede["servidor_id"] ? $rede["servidor_id"] : "NULL";
			$quantidade_ips = pow(2, 32 - ($rede["mascara"] + 1));
			$mascara = $rede["mascara"] + 1;
			for ($i = 0; $i < 2; $i++) {
				$endereco_ip = $this->geraIP($rede["endereco_ip"], $i * $quantidade_ips, $mascara);
				$reserva_ips = $mascara >= 30 ? 0 : 1;
				$query = "INSERT INTO tab_redes (red_endereco_ip, red_mascara, red_publicar_dhcp, red_reserva_ips, ser_id, red_pai) VALUES ('$endereco_ip', $mascara, {$rede["publicar_dhcp"]}, $reserva_ips, $servidor_id, {$rede["id"]})";
				$this->bancodedados->query($query);
			}
			$query = "UPDATE tab_redes SET ser_id = NULL WHERE red_id = $rede_id";
			$this->bancodedados->query($query);
			return true;
		} else {
			return false;
		}
	}

	function reduzRede($rede_id, $manter_servidores = false) {
		$redes_filhas = $this->buscaRedes($rede_id, false);
		if ($redes_filhas) {
			$reduzido = true;
			for ($i = 0; $i < count($redes_filhas); $i++) {
				$reduzido = $this->reduzRede($redes_filhas[$i]["id"], $manter_servidores) && $reduzido;
				if ($redes_filhas[$i]["servidor_id"]) {
					$servidores[] = $redes_filhas[$i]["servidor_id"];
				}
				if ($redes_filhas[$i]["interface_id"]) {
					$interfaces[] = $redes_filhas[$i]["interface_id"];
				}
				$publicar_dhcp[] = $redes_filhas[$i]["publicar_dhcp"];
			}
		}
		if (!$interfaces && $redes_filhas && $reduzido && (!$servidores || !$manter_servidores)) {
			$publicar_dhcp = $publicar_dhcp[0] && $publicar_dhcp[1] ? 1 : 0;
			if ((count($servidores) == 2) && ($servidores[0] == $servidores[1]) && ($servidores[0])) {
				$query = "UPDATE tab_redes SET ser_id = $servidores[0], red_publicar_dhcp = $publicar_dhcp WHERE red_id = $rede_id";
				$this->bancodedados->query($query);
			} else {
				$query = "UPDATE tab_redes SET red_publicar_dhcp = $publicar_dhcp WHERE red_id = $rede_id";
				$this->bancodedados->query($query);
			}
			$query = "DELETE FROM tab_redes WHERE red_pai = $rede_id AND sei_id IS NULL";
			$this->bancodedados->query($query);
			return true;
		} else {
			return $redes_filhas ? false : true;
		}
	}

	function removerRede($rede_id) {
		if (($rede = $this->buscaDadosRede($rede_id)) && !$rede["redes_filhas"] && !$rede["rede_pai"]) {
			// Desatribui IP/Mascara Interface
			if ($rede["interface_id"]) {
				$query = "UPDATE tab_servidores_interfaces SET sei_prefixo_ip = NULL, sei_endereco_ip = NULL, sei_mascara_rede = NULL WHERE sei_id = {$rede["interface_id"]}";
				$this->bancodedados->query($query);
			}
			// Remove Rede
			$query = "DELETE FROM tab_redes WHERE red_id = $rede_id";
			$this->bancodedados->query($query);
			return $rede["rede"];
		} else {
			return false;
		}
	}

	function defineDHCP($rede_id, $publicar_dhcp) {
		$query = "UPDATE tab_redes SET red_publicar_dhcp = $publicar_dhcp WHERE red_id = $rede_id";
		$this->bancodedados->query($query);
	}

	function defineServidor($rede_id, $servidor_id) {
		if (($rede = $this->buscaDadosRede($rede_id)) && ($servidor = $this->buscaDadosServidor($servidor_id))) {
			if ($this->ramoAssociadoOutroServidor($servidor_id, $rede_id)) {
				return "Erro: Não é possível associar este servidor!";
			}
			if (!$rede["redes_filhas"]) {
				$query = "UPDATE tab_redes SET ser_id = $servidor_id WHERE red_id = $rede_id";
				$this->bancodedados->query($query);
			}
			$redes_filhas = $this->buscaRedes($rede["id"]);
			if ($redes_filhas) {
				for ($i = 0; $i < count($redes_filhas); $i++) {
					$this->defineServidor($redes_filhas[$i]["id"], $servidor_id);
				}
			}
		} else {
			return "Erro: Servidor não disponível!";
		}
	}

	function ramoAssociadoOutroServidor($servidor_id, $rede_id) {
		$query = "
  	  SELECT
  	    red_id AS id,
  	    ser_id AS servidor_id
  	  FROM
  	    tab_redes
  	  WHERE
  	    red_pai = $rede_id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["servidor_id"] && $dados["servidor_id"] != $servidor_id) {
				return true;
			}
			if (!$dados["servidor_id"] && $this->ramoAssociadoOutroServidor($servidor_id, $dados["id"])) {
				return true;
			}
		}
		return false;
	}

	function distribuirInterfaces($rede_id, $servidor_id) {
		if (($dados_rede = $this->buscaDadosRede($rede_id)) && ($dados_servidor = $this->buscaDadosServidor($servidor_id))) {
			if (!($lista_interfaces = $this->buscaInterfaces($servidor_id, false, false))) {
				return "Sem interfaces disponíveis!";
			}
			$this->reduzRede($rede_id, true);
			for ($i = 0; $i < count($lista_interfaces); $i++) {
				$distribuicao = $this->distribuiRedes($lista_interfaces[$i]["pontos"], $lista_interfaces[$i]["tipo_id"]);
				if ($distribuicao) {
					$redes[$lista_interfaces[$i]["id"]]["redes"] = $distribuicao;
				}
			}
			$distribuicao = $this->buscaDistribuicao($redes);
			if ($distribuicao) {
				$arvore = array();
				foreach ($distribuicao as $rede => $interfaces) {
					foreach ($interfaces as $interface_id) {
						$this->registraArvore($arvore, $rede, $interface_id);
					}
				}
				if ($this->associaArvore($arvore, $servidor_id, $rede_id)) {
					$this->regeraIPs($servidor_id);
					return false;
				} else {
					$this->reduzRede($rede_id, true);
					return "Não existem hosts suficientes nesta rede!";
				}
			} else {
				return "Nenhuma interface pendente encontrada!";
			}
		}
		return false;
	}

	function associaArvore(&$arvore, $servidor_id, $rede_pai_id = null, &$associacoes = array(), $nivel = 0) {
		for ($i = 0; $i < count($arvore); $i++) {
			$rede_id = $this->buscaRedeLivre($arvore[$i]["rede"], $rede_pai_id, array_keys($associacoes));
			if ($rede_id) {
				if ($arvore[$i]["interface"]) {
					$dados["rede_id"] = $rede_id;
					$dados["servidor_id"] = $servidor_id;
					$dados["interface_id"] = $arvore[$i]["interface"];
					$associacoes[$rede_id] = $dados;
				} elseif ($arvore[$i]["filhas"]) {
					if (!$this->associaArvore($arvore[$i]["filhas"], $servidor_id, $rede_pai_id, $associacoes, $nivel + 1)) {
						return false;
					}
				}
			} else {
				return false;
			}
		}
		if ($nivel == 0) {
			foreach ($associacoes as $associacao) {
				// Associa Rede/Interface
				$query = "UPDATE tab_redes SET ser_id = {$associacao["servidor_id"]}, sei_id = {$associacao["interface_id"]} WHERE red_id = {$associacao["rede_id"]}";
				$this->bancodedados->query($query);
				// Atribui IP/Máscara à Interface
				$dados_interface = $this->buscaDadosInterface($associacao["interface_id"]);
				if ($dados_interface["tipo_id"] == 6) {
					$dados_rede = $this->buscaDadosRede($associacao["rede_id"]);
					$ip = explode(".", $this->geraIP($dados_rede["endereco_ip"], 1, $dados_rede["mascara"]));
					$mascara_rede = $this->formataMascara($dados_rede["mascara"]);
					$query = "UPDATE tab_servidores_interfaces SET sei_prefixo_ip = '$ip[0].$ip[1].$ip[2]', sei_endereco_ip = '$ip[3]', sei_mascara_rede = '$mascara_rede' WHERE sei_id = {$associacao["interface_id"]}";
					$this->bancodedados->query($query);
				} elseif ($dados_interface["tipo_id"] == 1) {
					$query = "UPDATE tab_servidores_interfaces SET sei_prefixo_ip = NULL, sei_endereco_ip = NULL, sei_mascara_rede = NULL WHERE sei_id = {$associacao["interface_id"]}";
					$this->bancodedados->query($query);
				}
			}
		}
		return true;
	}

	function buscaDadosInterface($interface_id) {
 	  $query = "
 	    SELECT
 	      sei_id AS id,
 	      sei_nome AS nome,
 	      sit_id AS tipo_id
 	    FROM
 	      tab_servidores_interfaces
 	    WHERE
 	      sei_id = $interface_id";
		$query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
			return $dados;
		}
	}

	function buscaRedeLivre($rede, $rede_pai_id, $ocupados = null) {
		$dados_rede = $this->buscaDadosRede($rede_pai_id);
		if ($dados_rede) {
			if ($dados_rede["mascara"] == $rede) {
				if (in_array($rede_pai_id, $ocupados)) {
					return null;
				} else {
					if (!$dados_rede["servidor_id"]) {
						return $rede_pai_id;
					} else {
						return null;
					}
				}
			}
			if (!$dados_rede["redes_filhas"]) {
				if (!$dados_rede["servidor_id"]) {
					$this->expandeRede($rede_pai_id);
				} else {
					return null;
				}
			}
		} else {
			return null;
		}
		$query = "
  	  SELECT
  	    tab_redes.red_id AS id,
  	    tab_redes.red_mascara AS mascara,
  	    COUNT(redes_filhas.red_id) AS redes_filhas
  	  FROM
  	    tab_redes
  	    LEFT OUTER JOIN tab_redes redes_filhas ON (tab_redes.red_id = redes_filhas.red_pai)
  	  WHERE
  	    tab_redes.red_mascara <= $rede AND
  	    tab_redes.red_pai = $rede_pai_id AND
  	    tab_redes.ser_id IS NULL".($ocupados ? " AND
  	    tab_redes.red_id NOT IN (".implode(", ", $ocupados).")" : "")."
  	  GROUP BY
  	    tab_redes.red_id
  	  ORDER BY
		  	INET_ATON(tab_redes.red_endereco_ip) DESC";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["mascara"] == $rede) {
				if (!$dados["redes_filhas"]) {
					return $dados["id"];
				} else {
					continue;
				}
			} else {
				$rede_livre = $this->buscaRedeLivre($rede, $dados["id"], $ocupados);
				if ($rede_livre) {
					return $rede_livre;
				} else {
					continue;
				}
			}
		}
		return null;
	}

	function registraArvore(&$arvore, $rede, $interface_id, $nivel = 0) {
		for ($i = 0; $i < count($arvore); $i++) {
			if ($arvore[$i]["interface"]) {
				continue;
			}
			if ($arvore[$i]["rede"] < $rede) {
				if (!$arvore[$i]["filhas"]) {
					for ($j = 0; $j < 2; $j++) {
						$arvore[$i]["filhas"][$j]["rede"] = $arvore[$i]["rede"] + 1;
					}
				}
				if ($this->registraArvore($arvore[$i]["filhas"], $rede, $interface_id, $nivel + 1)) {
					return true;
				} else {
					continue;
				}
			}
			if ($arvore[$i]["rede"] == $rede) {
				if ($arvore[$i]["filhas"]) {
					continue;
				} else {
					$arvore[$i]["interface"] = $interface_id;
					return true;
				}
			}
		}
		if ($nivel == 0) {
			$indice = count($arvore);
			$arvore[$indice]["rede"] = $rede;
			return $this->registraArvore($arvore, $rede, $interface_id, $nivel);
		} else {
			return false;
		}
	}

	function buscaDistribuicao($redes) {
		if (!$redes) {
			return null;
		}
		foreach ($redes as $interface => $dados) {
			for ($i = 0; $i < count($dados["redes"]); $i++) {
				$classes[$dados["redes"][$i]][] = $interface;
			}
		}
		ksort($classes);
		return $classes;
	}

	function distribuiRedes($quantidade, $tipo_id, $reserva_ips = 6) {
		if ($quantidade + $reserva_ips <= 0) {
			return null;
		}
		$indice = intval(ceil(log($quantidade + 3, 2)));
		if ($tipo_id == 6) {
			if ((pow(2, $indice) - 4) < ($quantidade + $reserva_ips)) {
				$indice++;
			}
			return array(32 - $indice);
		}
		$quantidade_pendente = $quantidade;
		if ($indice > 5 && $quantidade < $this->somaIndices($indice)) {
			while ($quantidade_pendente > 0) {
				$indice--;
				if ($quantidade_pendente > pow(2, $indice - 1) - 4 || $indice == 4) {
					$quantidade_pendente -= pow(2, $indice) - 4;
					$mascaras[] = 32 - $indice;
				}
			}
		} else {
			$indice = ($indice < 4) ? 4 : $indice;
			$quantidade_pendente -= pow(2, $indice) - 4;
			$mascaras[] = 32 - $indice;
		}
		if ($quantidade_pendente + $reserva_ips > 0) {
			if (!in_array(28, $mascaras)) {
				$mascaras[] = 28;
			} elseif (in_array(28, $mascaras) && !in_array(27, $mascaras)) {
				for ($i = 0; $i < count($mascaras); $i++) {
					if ($mascaras[$i] == 28) {
						$mascaras[$i] = 27;
						break;
					}
				}
			} else {
				return $this->distribuiRedes($quantidade + 13, $tipo_id, $reserva_ips);
			}
		}
		return $mascaras;
	}

	function somaIndices($indice) {
		$indice--;
		for ($i = $indice; $i > 4; $i--) {
			$soma += pow(2, $i) - 4;
		}
		return $soma;
	}

	function buscaDadosServidor($servidor_id) {
		$query = "
  	  SELECT
  	    tab_servidores.ser_id AS servidor_id,
  			COUNT(tab_clientes_pontos.clp_id) AS pontos,
        CONCAT(tab_servidores.ser_nome, IF(tab_servidores.sei_id IS NULL, '', CONCAT(' (', interface_servidor.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip, ')'))) AS servidor_nome
  	  FROM
  	    tab_servidores
  	    LEFT OUTER JOIN tab_servidores_interfaces ON (tab_servidores.ser_id = tab_servidores_interfaces.ser_id)
  	    LEFT OUTER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id AND tab_clientes_pontos.cps_id = 1)
  	    LEFT OUTER JOIN tab_servidores_interfaces interface_servidor ON (tab_servidores.sei_id = interface_servidor.sei_id)
  	  WHERE
  	    tab_servidores.ser_id = $servidor_id
  	  GROUP BY
  	    tab_servidores.ser_id";
		$query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
			return $dados;
		}
	}

	function limpaServidor($rede_id = null, $servidor_id = null) {
		if ($servidor_id) {
			$redes = $this->buscaRedesSuperPaiServidor($servidor_id);
			if ($redes) {
				// Desatribui Servidor
				$query = "UPDATE tab_redes SET ser_id = NULL, sei_id = NULL WHERE ser_id = $servidor_id";
				$this->bancodedados->query($query);
				// Desatribui IP/Máscara das Interfaces
				$query = "UPDATE tab_servidores_interfaces SET sei_prefixo_ip = NULL, sei_endereco_ip = NULL, sei_mascara_rede = NULL WHERE ser_id = $servidor_id AND sit_id IN (1, 6)";
				$this->bancodedados->query($query);
				// Reduz Redes
				foreach ($redes as $rede_id) {
					$this->reduzRede($rede_id);
				}
				$this->regeraIPs($servidor_id);
			}
		}	elseif (($rede = $this->buscaDadosRede($rede_id)) != false) {
			// Desatribui Servidor/Interface
			$query = "UPDATE tab_redes SET ser_id = NULL, sei_id = NULL WHERE red_id = $rede_id";
			$this->bancodedados->query($query);
			// Desatribui IP/Máscara das Interfaces
			if ($rede["interface_id"]) {
				$query = "UPDATE tab_servidores_interfaces SET sei_prefixo_ip = NULL, sei_endereco_ip = NULL, sei_mascara_rede = NULL WHERE sei_id = {$rede["interface_id"]} AND sit_id IN (1, 6)";
				$this->bancodedados->query($query);
				// Regera IPs
				$this->regeraIPs($rede["interface_id"]);
			}
			// Limpa Servidor
			$redes_filhas = $this->buscaRedes($rede["id"]);
			if ($redes_filhas) {
				for ($i = 0; $i < count($redes_filhas); $i++) {
					$this->limpaServidor($redes_filhas[$i]["id"]);
				}
			}
		}
		return true;
	}

	function buscaRedesSuperPaiServidor($servidor_id) {
		$query = "
		  SELECT
		    red_id AS id
		  FROM
		    tab_redes
		  WHERE
		    ser_id = $servidor_id";
		$query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
			$redes[] = $this->buscaRedeSuperPai($dados["id"]);
		}
		array_unique($redes);
		return $redes;
	}

	function buscaRedeSuperPai($rede_id) {
		$query = "
		  SELECT
		    red_id AS rede_id,
		    red_pai AS rede_pai_id
		  FROM
		    tab_redes
		  WHERE
		    red_id = $rede_id";
		$query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
			if ($dados["rede_pai_id"]) {
				return $this->buscaRedeSuperPai($dados["rede_pai_id"]);
			} else {
				return $dados["rede_id"];
			}
		} else {
			return $rede_id;
		}
	}

	function defineInterface($rede_id, $interface_id) {
		$rede = $this->buscaDadosRede($rede_id);
		if ($rede) {
			// Associa Rede/Interface
			$query = "UPDATE tab_redes SET sei_id = $interface_id WHERE red_id = $rede_id";
			$this->bancodedados->query($query);
			// Desatribui IP/Mascara Interface Antiga
			if ($rede["interface_id"]) {
				$query = "UPDATE tab_servidores_interfaces SET sei_prefixo_ip = NULL, sei_endereco_ip = NULL, sei_mascara_rede = NULL WHERE sei_id = {$rede["interface_id"]} AND sit_id IN (1, 6)";
				$this->bancodedados->query($query);
			}
			// Atribui IP/Máscara à Interface
			$dados_interface = $this->buscaDadosInterface($interface_id);
			if ($dados_interface["tipo_id"] == 6) {
				$ip = explode(".", $this->geraIP($rede["endereco_ip"], 1, $rede["mascara"]));
				$mascara_rede = $this->formataMascara($rede["mascara"]);
				$query = "UPDATE tab_servidores_interfaces SET sei_prefixo_ip = '$ip[0].$ip[1].$ip[2]', sei_endereco_ip = '$ip[3]', sei_mascara_rede = '$mascara_rede' WHERE sei_id = $interface_id";
				$this->bancodedados->query($query);
			} elseif ($dados_interface["tipo_id"] == 1) {
				$query = "UPDATE tab_servidores_interfaces SET sei_prefixo_ip = NULL, sei_endereco_ip = NULL, sei_mascara_rede = NULL WHERE sei_id = $interface_id";
				$this->bancodedados->query($query);
			}
			$this->regeraIPs(null, $interface_id);
		} else {
			return "ERRO:\n\nInterface não encontrada!";
		}
	}

	function limpaInterface($rede_id) {
		$rede = $this->buscaDadosRede($rede_id);
		if ($rede) {
			// Desatribui Interface
			$query = "UPDATE tab_redes SET sei_id = NULL WHERE red_id = $rede_id";
			$this->bancodedados->query($query);
			// Desatribui IP/Mascara Interface
			if ($rede["interface_id"]) {
				$query = "UPDATE tab_servidores_interfaces SET sei_prefixo_ip = NULL, sei_endereco_ip = NULL, sei_mascara_rede = NULL WHERE sei_id = {$rede["interface_id"]} AND sit_id IN (1, 6)";
				$this->bancodedados->query($query);
			}
			return true;
		}
	}

	function limpaInterfaces($interfaces) {
		if ($interfaces) {
			$interfaces = implode(", ", $interfaces);
			// Desatribui Interface
			$query = "UPDATE tab_redes SET sei_id = NULL WHERE sei_id IN ($interfaces)";
			$this->bancodedados->query($query);
			// Desatribui IP/Mascara Interface
			$query = "UPDATE tab_servidores_interfaces SET sei_prefixo_ip = NULL, sei_endereco_ip = NULL, sei_mascara_rede = NULL WHERE sei_id IN ($interfaces) AND sit_id IN (1, 6)";
			$this->bancodedados->query($query);
			return true;
		} else {
			return false;
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

	function formataMascara($mascara) {
		$mascara = sprintf("%0-32s", str_repeat("1", $mascara));
		for ($i = 0; $i < 4; $i++) {
			$resultado[] = bindec(substr($mascara, $i * 8, 8));
		}
		return implode(".", $resultado);
	}

	function formataRede($rede) {
		$rede = preg_split('/[.\/]+/', $rede);
		$conteudo[] = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr class=\"txtAzulEscuro11\">";
		for ($i = 0; $i < count($rede); $i++) {
			if ($i > 0 && $i != 4) {
				$conteudo[] = "<td style=\"text-align:center;width:4px;\">.</td>";
			} elseif ($i == 4) {
				$conteudo[] = "<td style=\"text-align:center;width:8px;\">/</td>";
			}
			$conteudo[] = "<td style=\"text-align:center;width:18px;\">{$rede[$i]}</td>";
		}
		$conteudo[] = "</tr></table>";
		return implode($conteudo);
	}

	function buscaRedes($rede_pai = null, $buscar_filhas = true) {
		$query = "
  	  SELECT
  	    tab_redes.red_id AS id,
  	    tab_redes.red_endereco_ip AS endereco_ip,
  	    tab_redes.red_mascara AS mascara,
  	    tab_redes.red_publicar_dhcp AS publicar_dhcp,
  	    tab_redes.red_pai AS rede_pai,
  	    tab_redes.ser_id AS servidor_id,
  	    tab_redes.sei_id AS interface_id,
  	    tab_redes.red_reserva_ips AS reserva_ips,
  	    COUNT(redes_filhas.red_id) AS redes_filhas,
  	    tab_servidores.ser_id AS servidor_id,
        tab_servidores.ser_nome AS servidor_nome,
        CONCAT(interface_servidor.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) AS servidor_ip,
  	    tab_servidores_interfaces.sei_id AS interface_id,
  	    tab_servidores_interfaces.sei_nome AS interface_nome
  	  FROM
  	    tab_redes
  	    LEFT OUTER JOIN tab_redes redes_filhas ON (tab_redes.red_id = redes_filhas.red_pai)
  	    LEFT OUTER JOIN tab_servidores ON (tab_redes.ser_id = tab_servidores.ser_id)
  	    LEFT OUTER JOIN tab_servidores_interfaces interface_servidor ON (tab_servidores.sei_id = interface_servidor.sei_id)
  	    LEFT OUTER JOIN tab_servidores_interfaces ON (tab_redes.sei_id = tab_servidores_interfaces.sei_id)
  	  WHERE".($rede_pai ? "
  	    tab_redes.red_pai = $rede_pai" : "
  	    tab_redes.red_pai IS NULL")."
  	  GROUP BY
  	    tab_redes.red_id
  	  ORDER BY
  	    INET_ATON(tab_redes.red_endereco_ip)";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		  $dados["servidor_nome"] = strlen($dados["servidor_nome"]) > 8 ? substr($dados["servidor_nome"], 0, 8)."..." : $dados["servidor_nome"];
		  $dados["interface_nome"] = strlen($dados["interface_nome"]) > 8 ? substr($dados["interface_nome"], 0, 5)."..." : $dados["interface_nome"];
		  if ($dados["servidor_ip"]) {
		    $dados["servidor_nome"] = "{$dados["servidor_nome"]} ({$dados["servidor_ip"]})";
		  }
			if ($dados["interface_id"]) {
				$dados["quantidade_pontos"] = $this->buscaQuantidadePontos($dados["interface_id"]);
				$dados["quantidade_redes"] = $this->buscaQuantidadeRedes($dados["interface_id"]);
				if ($dados["quantidade_redes"] && $dados["quantidade_redes"] != pow(2, 32 - $dados["mascara"]) - 3 - $dados["reserva_ips"]) {
					$dados["pontos"] = number_format($dados["quantidade_pontos"], 0, "", ".")." / {$dados["quantidade_redes"]}";
				} else {
					$dados["pontos"] = number_format($dados["quantidade_pontos"], 0, "", ".")." / ".number_format(pow(2, 32 - $dados["mascara"]) - 3 - $dados["reserva_ips"], 0, "", ".");
				}
				$dados["ocupacao"] = (($dados["quantidade_pontos"] / $dados["quantidade_redes"]) * 100);
			} elseif (!$dados["redes_filhas"]) {
				$dados["pontos"] = "0"." / ".number_format(pow(2, 32 - $dados["mascara"]) - 3 - $dados["reserva_ips"], 0, "", ".");
				$dados["ocupacao"] = 0;
				$dados["ips_livres"] = 100 - $dados["ocupacao"];
			} else {
				$dados["pontos"] = "";
			}
			$dados["rede"] = "{$dados["endereco_ip"]}/{$dados["mascara"]}";
			if ($dados["rede_pai"]) {
				$dados["rede_pai"] = $this->buscaDadosRede($dados["rede_pai"]);
			}
			if (!$dados["rede_pai"] && !$dados["redes_filhas"]) {
				$dados["links"][] = $this->buscaLinkRemover($dados["id"]);
			}
			$dados["links"] = @implode(" ", $dados["links"]);
			$redes[] = $dados;
			if ($buscar_filhas && ($redes_filhas = $this->buscaRedes($dados["id"]))) {
				$redes[] = $redes_filhas;
			}
		}
		return $redes;
	}

	function buscaRedesInterface($interface_id) {
		$query = "
  	  SELECT
  	    tab_redes.red_id AS id,
  	    tab_redes.red_endereco_ip AS endereco_ip,
  	    tab_redes.red_mascara AS mascara,
  	    tab_redes.red_reserva_ips AS reserva_ips
  	  FROM
  	    tab_redes
  	  WHERE
  	    tab_redes.sei_id = $interface_id
  	  ORDER BY
  	    INET_ATON(tab_redes.red_endereco_ip)";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["hosts"] = pow(2, 32 - $dados["mascara"]) - 3 - $dados["reserva_ips"];
			$dados["rede"] = "{$dados["endereco_ip"]}/{$dados["mascara"]}";
			$redes[] = $dados;
		}
		return $redes;
	}

	function buscaQuantidadeRedes($interface_id) {
		$query = "
  	  SELECT
  	    tab_redes.red_mascara AS mascara,
  	    tab_redes.red_reserva_ips AS reserva_ips
  	  FROM
  	    tab_redes
  	  WHERE
  	    tab_redes.sei_id = $interface_id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$quantidade += pow(2, 32 - $dados["mascara"]) - 3 - $dados["reserva_ips"];
		}
		return $quantidade;
	}

	function buscaQuantidadePontos($interface_id) {
		$query = "
  	  SELECT
  	    COUNT(clp_id) AS pontos
  	  FROM
  	    tab_clientes_pontos
  	  WHERE
  	    sei_id = $interface_id AND
  	    cps_id = 1";
		$query = $this->bancodedados->query($query);
		return $this->bancodedados->resultado($query, 0, "pontos");
	}

	function buscaLinkRemover($rede_id) {
		return "<a href=\"javascript:removeRede($rede_id)\" class=\"txtAzulEscuro11\" title=\"Remover\">[X]</a>";
	}

	function buscaLinkLimpaServidor($rede_id) {
		return "<a href=\"javascript:limpaServidor($rede_id)\" class=\"txtAzulEscuro11\" title=\"Desatribuir\">[X]</a>";
	}

	function buscaLinkLimpaServidores($servidor_id) {
		return "<a href=\"javascript:limpaServidores($servidor_id)\" class=\"txtBranco11\" style=\"font-weight:bold\" title=\"Desatribuir\">[X]</a>";
	}

	function buscaLinkLimpaInterface($rede_id) {
		return "<a href=\"javascript:limpaInterface($rede_id)\" class=\"txtAzulEscuro11\" title=\"Desatribuir\">[X]</a>";
	}

	function buscaLinkLimpaInterfaces() {
		return "<a href=\"javascript:limpaInterfaces()\" class=\"txtAzulEscuro11\" title=\"Desatribuir\">[X]</a>";
	}

	function buscaLinkDefineServidor($rede_id, $servidor_id, $ramos_ocupados) {
		return "<a href=\"javascript:exibeServidores($rede_id, '$servidor_id')\" class=\"txtAzulEscuro11\" title=\"Associar\">".($ramos_ocupados ? "..." : "<span style=\"color:#232323\">[+]</span>")."</a>";
	}

	function buscaLinkDefineInterface($rede_id, $servidor_id, $interface_id) {
		return "<a href=\"javascript:exibeInterfaces($rede_id, $servidor_id, '$interface_id')\" class=\"txtAzulEscuro11\" title=\"Associar\">[...]</a>";
	}

	function buscaInformacoes($servidor_id) {
		$query = "
  	  SELECT
  	    tab_servidores_interfaces.sei_id AS id,
  	    tab_servidores_interfaces.sei_nome AS nome,
  	    tab_servidores_interfaces.sei_reserva_ips AS reserva_ips,
  			COUNT(tab_clientes_pontos.clp_id) AS pontos
  	  FROM
  	    tab_servidores_interfaces
  	    LEFT OUTER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id AND tab_clientes_pontos.cps_id = 1)
  	  WHERE
  	    tab_servidores_interfaces.ser_id = $servidor_id AND
  	  	tab_servidores_interfaces.sit_id IN (1, 3, 6) AND
        ((tab_servidores_interfaces.sit_id = 1 AND tab_servidores_interfaces.sei_pai IS NULL) OR
	  		 (tab_servidores_interfaces.sit_id = 6 AND tab_servidores_interfaces.sei_pai IS NOT NULL) OR
	  		 (tab_servidores_interfaces.sit_id = 3))
  		GROUP BY
  		  tab_servidores_interfaces.sei_id";
		$query = $this->bancodedados->query($query);
		$interfaces = array();
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["hosts"] = 0;
			$redes_interface = $this->buscaRedesInterface($dados["id"]);
			for ($i = 0; $i < count($redes_interface); $i++) {
				$dados["redes"][] = $this->formataRede($redes_interface[$i]["rede"]);
				$dados["hosts"] += $redes_interface[$i]["hosts"];
			}
			$dados["livres"] = $dados["hosts"] - $dados["pontos"];
			$total["hosts"] += $dados["hosts"];
			$total["pontos"] += $dados["pontos"];
			$total["livres"] += $dados["livres"];
			$total["reserva_ips"] += $dados["reserva_ips"];
			$dados["livres"] = $dados["livres"] > 0 ? $dados["livres"] : "<span style=\"color:#FF0000\">{$dados["livres"]}</span>";
			$dados["pontos"] = "{$dados["pontos"]}".($dados["reserva_ips"] ? " [{$dados["reserva_ips"]}]" : "")." / {$dados["hosts"]}";
			$dados["redes"] = implode($dados["redes"]);
			$dados["ordem"] = sprintf("% -15s", preg_replace("/[^a-zA-Z _]/", "", $dados["nome"])).sprintf("%05s", preg_replace("/[^0-9]/", "", $dados["nome"]));
			$dados["checkbox"] = "<input type=\"checkbox\" id=\"checkbox_interfaces_".count($interfaces)."\" value=\"{$dados["id"]}\" style=\"width:12px;height:12px;margin:0px\">";
			$interfaces[] = $dados;
		}
		$interfaces = $this->ordena_multiarray($interfaces, array("ordem"));
		$total["checkbox"] = $this->buscaLinkLimpaInterfaces();
		$total["nome"] = "Total";
		$total["cor_linha"] = "#dae5f0";
		$total["pontos"] = "{$total["pontos"]}".($total["reserva_ips"] ? " [{$total["reserva_ips"]}]" : "")." / {$total["hosts"]}";
		$total["livres"] = $total["livres"] > 0 ? $total["livres"] : "<span style=\"color:#FF0000\">{$total["livres"]}</span>";
		$interfaces[] = $total;
		$cabecalho = array ("checkbox" => "<input type=\"checkbox\" id=\"checkbox_interfaces_todos\" onclick=\"interfacesSelectAll(this.checked)\" style=\"width:12px;height:12px;margin:0px\">", "nome" => "<label for=\"checkbox_interfaces_todos\">Interface</label>", "redes" => "Redes", "pontos" => "Pontos", "livres" => "Livres");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $interfaces, 0, null, array(13, 60, 120, 70), null, array("left", "left", "center", "right", "right"));
		// Dados Servidor
		$dados_servidor = $this->buscaDadosServidor($servidor_id);
		if ($this->permissoes["servidores"]["ver_dc"]) $links[] = "<a href=\"./?m=servidores&a=ver_dc&i={$dados_servidor["servidor_id"]}\" title=\"Dados Cadastrais\" class=\"txtBranco11\">DC</a>";
		if ($this->permissoes["servidores"]["ver_di"]) $links[] = "<a href=\"./?m=servidores&a=ver_di&i={$dados_servidor["servidor_id"]}\" title=\"Interfaces\" class=\"txtBranco11\">DI</a>";
		if ($this->permissoes["servidores"]["ver_co"]) $links[] = "<a href=\"./?m=servidores&a=ver_co&i={$dados_servidor["servidor_id"]}\" title=\"Condomínios\" class=\"txtBranco11\">CO</a>";
		$links = implode(" ", $links);
		$conteudo[] = '<table cellspacing="1" cellpadding="2" border="0" width="100%"><tr class="txtBranco11" style="font-weight:bold;background-color:#505050">';
		$conteudo[] = '<td align="left" id="dragarea">&nbsp;'.$dados_servidor["servidor_nome"].' '.$this->buscaLinkLimpaServidores($dados_servidor["servidor_id"]).'</td>';
		$conteudo[] = '<td align="center" width="60">'.$links.'</td>';
		$conteudo[] = '<td align="center" width="15" style="cursor:pointer" onclick="document.getElementById(\\\'container_informacoes\\\').style.display = \\\'none\\\'">X</td>';
		$conteudo[] = '</tr></table>';
		$conteudo[] = $listagem->conteudo;
		return implode($conteudo);
	}

}
