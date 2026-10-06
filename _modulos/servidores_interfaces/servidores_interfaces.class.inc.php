<?

class servidores_interfaces {
	
	var $bancodedados;
	var $permissoes;
	var $acao;
	var $conteudo;
	var $dados_item;
	var $dados_modulo;
	var $dados_acao;
	
	function __construct($bancodedados, $permissoes, $executar = true) {
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			if ($_GET["a"] == "ver_dc" && $this->permissoes[$this->dados_modulo["nome"]]["editar_dc"])
				$_GET["a"] = "editar_dc";
			if ($_GET["a"] == "ver_ds" && $this->permissoes[$this->dados_modulo["nome"]]["editar_ds"])
				$_GET["a"] = "editar_ds";
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
	}
	
	function geraTitulo() {
		$titulo = '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
		$titulo .= '<tr align="center" class="txtBranco11">';
		$titulo .= '<td bgcolor="#505050"><b>';
		$titulo .= $this->dados_modulo["descricao"];
		$titulo .= '</b></td></tr></table>';
		return $titulo;
	}
	
	function carregaArquivo($arquivo) {
		if (file_exists(dirname(__FILE__) . "/" . $arquivo))
			$arquivo = dirname(__FILE__) . "/" . $arquivo;
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
	
	########################
	### Busca Dados Item ###
	########################
	

	function buscaDadosItem($id) {
		$this->dados_item = $this->buscaDadosInterface($id);
	}
	
	function buscaDadosInterface($id) {
		$query = "
			SELECT
	    	tab_servidores_interfaces.sei_id AS id,
    		tab_servidores_interfaces.sei_nome AS nome,
	  		tab_servidores_interfaces.ser_id AS servidor_id,
    		tab_servidores_interfaces.sei_endereco_ip AS endereco_ip,
    		tab_servidores_interfaces.sei_descricao AS descricao,
    		tab_servidores_interfaces.sip_id AS padrao,
  			tab_servidores_interfaces.sei_mascara_rede AS mascara_rede,
	  		tab_servidores_interfaces.sei_prefixo_ip AS prefixo,
    		tab_servidores_interfaces.sit_id AS tipo_id,
    		tab_servidores_interfaces.sei_reserva_ips AS reserva_ips,
    		tab_servidores_interfaces.sei_configuracao AS configuracao,
    		tab_servidores_interfaces_status.sia_id AS status_id,
    		tab_servidores_interfaces_status.sia_nome AS status_nome,
 	  		tab_servidores.ser_nome AS servidor_nome,
 	  		tab_servidores.sep_id AS servidor_tipo,
 	  		tab_servidores.sso_id AS sistema_operacional_id,
	  		tab_servidores_interfaces_tipos.sit_nome AS tipo_nome,
	  		tab_servidores_situacoes.seu_id AS servidor_situacao_id,
	  		tab_servidores_situacoes.seu_nome AS servidor_situacao_nome
    	FROM
	    	tab_servidores_interfaces
	  		INNER JOIN tab_servidores_interfaces_tipos ON (tab_servidores_interfaces_tipos.sit_id = tab_servidores_interfaces.sit_id)
	  		INNER JOIN tab_servidores_interfaces_status ON (tab_servidores_interfaces_status.sia_id = tab_servidores_interfaces.sia_id)
	  		INNER JOIN tab_servidores ON (tab_servidores.ser_id = tab_servidores_interfaces.ser_id)
	  		INNER JOIN tab_servidores_situacoes ON (tab_servidores_situacoes.seu_id = tab_servidores.seu_id)
    	WHERE
    		tab_servidores_interfaces.sei_id = $id";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			$dados["configuracao"] = @unserialize($dados["configuracao"]);
			$dados["prefixo_ip_1"] = $this->buscaPrefixoIP($dados["prefixo"], 0);
			$dados["prefixo_ip_2"] = $this->buscaPrefixoIP($dados["prefixo"], 1);
			$dados["prefixo_ip_3"] = $this->buscaPrefixoIP($dados["prefixo"], 2);
			if ($dados["tipo_id"] == 1) {
				$dados["redes"] = $this->buscaRedes($dados["id"]);
			} elseif ($dados["tipo_id"] == 6) {
				$dados["interfaces_carp"] = $this->buscaInterfacesCarp($dados["id"]);
			}
			if ($dados["sistema_operacional_id"] == 9 && $dados["tipo_id"] == 1) {
				$dados["ssids"] = $this->buscaSSIDs($dados["id"]);
			}
			
			return $dados;
		}
	}
	
	function buscaTipoServidor($id) {
		$query = "
			SELECT
				sep_id AS servidor_tipo
			FROM
				tab_servidores
			WHERE
				ser_id = $id";
		$query = $this->bancodedados->query($query);
		return $this->bancodedados->resultado($query, 0, 0);
	}
	
	function buscaServicosServidor($servidor_id) {
		$query = "
	    SELECT
	      tab_servidores_servicos.ses_parametros AS parametros,
	      tab_servidores_servicos.sst_id AS tipo_id,
	      tab_servidores_servicos.sss_id AS status_id
	    FROM
	      tab_servidores_servicos
	      INNER JOIN tab_servidores_interfaces ON (tab_servidores_servicos.sei_id = tab_servidores_interfaces.sei_id)
	    WHERE
	      tab_servidores_interfaces.ser_id = $servidor_id
	    GROUP BY
	      tab_servidores_servicos.sst_id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servicos[] = $dados;
		}
		return $servicos;
	}
	
	#######################
	### Interfaces CARP ###
	#######################
	

	function buscaInterfacesCarp($interface_id) {
		$query = "
 	    SELECT
 	      tab_servidores_interfaces.sei_id AS id,
 	      tab_servidores_interfaces.sei_nome AS nome,
  	    CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip) AS endereco_ip,
  	    tab_servidores_interfaces.sei_mascara_rede AS mascara_rede
 	    FROM
 	      tab_servidores_interfaces
 	    WHERE
 	      tab_servidores_interfaces.sei_pai = $interface_id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["ordem"] = sprintf("% -15s", preg_replace("/[^a-zA-Z _]/", "", $dados["nome"])) . sprintf("%05s", preg_replace("/[^0-9]/", "", $dados["nome"]));
			$interfaces[] = $dados;
		}
		return $this->ordena_multiarray($interfaces, array("ordem"));
	}
	
	function buscaCarpsAdicionar() {
		$adicionar["nome"] = '<input name="carp_nome" maxlength="15" id="carp_nome" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="8">';
		$adicionar["endereco_ip"] = '<input name="carp_endereco_ip_1" id="carp_endereco_ip_1" type="text" class="inputBrancoAzulEscuro11_c" style="width:29px" size="3" onKeyPress="return formataNumeroIP(this, event)">.<input name="carp_endereco_ip_2" id="carp_endereco_ip_2" type="text" class="inputBrancoAzulEscuro11_c" style="width:29px" size="3" onKeyPress="return formataNumeroIP(this, event)">.<input name="carp_endereco_ip_3" id="carp_endereco_ip_3" type="text" class="inputBrancoAzulEscuro11_c" style="width:29px" size="3" onKeyPress="return formataNumeroIP(this, event)">.<input name="carp_endereco_ip_4" id="carp_endereco_ip_4" type="text" class="inputBrancoAzulEscuro11_c" style="width:29px" size="3" onKeyPress="return formataNumeroIP(this, event)">';
		$adicionar["mascara_rede"] = '<input name="carp_mascara_rede_1" id="carp_mascara_rede_1" type="text" class="inputBrancoAzulEscuro11_c" style="width:29px" size="3" onKeyPress="return formataNumeroIP(this, event)">.<input name="carp_mascara_rede_2" id="carp_mascara_rede_2" type="text" class="inputBrancoAzulEscuro11_c" style="width:29px" size="3" onKeyPress="return formataNumeroIP(this, event)">.<input name="carp_mascara_rede_3" id="carp_mascara_rede_3" type="text" class="inputBrancoAzulEscuro11_c" style="width:29px" size="3" onKeyPress="return formataNumeroIP(this, event)">.<input name="carp_mascara_rede_4" id="carp_mascara_rede_4" type="text" class="inputBrancoAzulEscuro11_c" style="width:29px" size="3" onKeyPress="return formataNumeroIP(this, event)">';
		$adicionar["botoes"] = '<input type="button" name="adicionar_carp" id="adicionar_carp" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="adicionarCarp()">';
		return $adicionar;
	}
	
	function geraListagemCarp($dados = null, $editavel = false) {
		if ($dados) {
			for ($i = 0; $i < count($dados); $i++) {
				if ($editavel) {
					$dados[$i]["endereco_ip"] = $this->formataIP($dados[$i]["endereco_ip"]);
					$dados[$i]["mascara_rede"] = $this->formataIP($dados[$i]["mascara_rede"]);
					$dados[$i]["link"] = "javascript:selecionarCarp(this, event, " . ($i + 1) . ")";
					$dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerCarp(' . ($i + 1) . ')">';
				}
			}
		} else {
			$dados[] = array("mesclado" => "<table width=\"100%\" height=\"20\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>");
		}
		if ($editavel) {
			$dados = funcoes::mescla_arrays(array($this->buscaCarpsAdicionar()), $dados);
			$cabecalho = array("nome" => "Nome", "endereco_ip" => "Endereço IP", "mascara_rede" => "Másraca de Rede", "botoes" => "");
			$larguras = array(110, 130, 130, 70);
		} else {
			$cabecalho = array("nome" => "Nome", "endereco_ip" => "Endereço IP", "mascara_rede" => "Másraca de Rede");
			$larguras = null;
		}
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, $larguras);
		return $listagem->conteudo;
	}
	
	#######################
	### Interfaces Pool ###
	#######################
	

	function buscaPoolAdicionar() {
		$adicionar["classe"] = '<input name="classe_1" id="classe_1" type="text" class="inputBrancoAzulEscuro11_c" style="width:37px" size="3" onKeyPress="return formataNumeroIP(this, event)"> . <input name="classe_2" id="classe_2" type="text" class="inputBrancoAzulEscuro11_c" style="width:37px" size="3" onKeyPress="return formataNumeroIP(this, event)"> . <input name="classe_3" id="classe_3" type="text" class="inputBrancoAzulEscuro11_c" style="width:37px" size="3" onKeyPress="return formataNumeroIP(this, event)"> . <input name="classe_4" id="classe_4" type="text" class="inputBrancoAzulEscuro11_c" style="width:37px" size="3" onKeyPress="return formataNumeroIP(this, event)"> / <input name="classe_5" id="classe_5" type="text" class="inputBrancoAzulEscuro11_c" style="width:37px" size="3" onKeyPress="return formataNumeroIP(this, event)">';
		$adicionar["botoes"] = '<input type="button" name="adicionar_classe" id="adicionar_classe" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="adicionarClasse()">';
		return $adicionar;
	}
	
	function buscaSSIDAdicionar() {
		$adicionar["ssid"] = '<input name="ssid" id="ssid" type="text" class="inputBrancoAzulEscuro11" style="width:180px">';
		$adicionar["ip"] = '<div style="white-space:nowrap;"><input name="ip_1" id="ip_1" type="text" class="inputBrancoAzulEscuro11_c" style="width:30px" size="3" onKeyPress="return formataNumeroIP(this, event)">.<input name="ip_2" id="ip_2" type="text" class="inputBrancoAzulEscuro11_c" style="width:30px" size="3" onKeyPress="return formataNumeroIP(this, event)">.<input name="ip_3" id="ip_3" type="text" class="inputBrancoAzulEscuro11_c" style="width:30px" size="3" onKeyPress="return formataNumeroIP(this, event)">.<input name="ip_4" id="ip_4" type="text" class="inputBrancoAzulEscuro11_c" style="width:30px" size="3" onKeyPress="return formataNumeroIP(this, event)"></div>';
		$adicionar["tipo"] = '<select name="ssid_tipo" id="ssid_tipo" class="inputBrancoAzulEscuro11" style="width:50px">'.$this->geraSelect($this->getSSIDsTipos(), null, true).'</select>';
		$adicionar["botoes"] = '<input type="button" name="adicionar_ssid" id="adicionar_ssid" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="adicionarSSID()">';
		return $adicionar;
	}
	
	function geraListagemPool($dados = null, $editavel = false) {
		if ($dados) {
			for ($i = 0; $i < count($dados); $i++) {
				if ($editavel) {
					$dados[$i]["classe"] = $dados[$i]["classe"];
					$dados[$i]["link"] = "javascript:selecionarClasse(this, event, " . ($i + 1) . ")";
					$dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerClasse(' . ($i + 1) . ')">';
				}
			}
		} else {
			$dados[] = array("mesclado" => "<table width=\"100%\" height=\"20\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>");
		}
		if ($editavel) {
			$dados = funcoes::mescla_arrays(array($this->buscaPoolAdicionar()), $dados);
			$cabecalho = array("classe" => "Classe", "botoes" => "");
			$larguras = array(0, 33);
		} else {
			$cabecalho = array("classe" => "Classe");
			$larguras = null;
		}
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, $larguras);
		return $listagem->conteudo;
	}
	
	function geraListagemSSID($dados = null, $editavel = false) {
		if ($dados) {
			$ssids_tipos = $this->getSSIDsTipos();
			for ($i = 0; $i < count($dados); $i++) {
				if ($editavel) {
					$dados[$i]["ssids"] = $dados[$i]["ssids"];
					$dados[$i]["ips"] = $dados[$i]["ips"];
					$dados[$i]["link"] = "javascript:selecionarSSID(this, event, " . ($i + 1) . ")";
					$dados[$i]["tipo"] = $ssids_tipos[$dados[$i]["tipo"]];
					if ($dados[$i]["pontos"]) {
						$dados[$i]["ssid"] = "{$dados[$i]["ssid"]} ({$dados[$i]["pontos"]})";
						$dados[$i]["botoes"] = '<input type="button" name="remover_ssid" id="remover_ssid" value="X" style="width:25px" class="buttonAzulEscuroBranco11disabled" disabled>';
					} else {
						$dados[$i]["botoes"] = '<input type="button" name="remover_ssid" id="remover_ssid" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerSSID(' . ($i + 1) . ')">';
					}
				}
			}
		} else {
			$dados[] = array("mesclado" => "<table width=\"100%\" height=\"20\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>");
		}
		if ($editavel) {
			$dados = funcoes::mescla_arrays(array($this->buscaSSIDAdicionar()), $dados);
			$cabecalho = array("ssid" => "SSID", "ip" => "IP", "tipo" => "Tipo", "botoes" => "");
			$larguras = array(60, 80, 20);
		} else {
			$cabecalho = array("ssid" => "SSID", "ip" => "IP", "tipo" => "Tipo");
			$larguras = null;
		}
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, $larguras);
		return $listagem->conteudo;
	}
	
	function geraListagemRedes($dados) {
		$cabecalho = array("endereco_ip" => "Endereço IP", "mascara_rede" => "Máscara de Rede");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
		return $listagem->conteudo;
	}
		
	function getSSIDsTipos() {
		$query = "
			SELECT
				sst_id AS id,
				sst_nome AS nome
			FROM
				tab_servidores_ssids_tipos
			ORDER BY
				sst_nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$ssids_tipos[$dados["id"]] = $dados["nome"];
		}
		return $ssids_tipos;
	}
	
	function buscaSSIDs($id) {
		$query = "
			SELECT
				tab_servidores_ssids.ssi_id AS id,
				tab_servidores_ssids.ssi_nome AS ssid,
				tab_servidores_ssids.ssi_ip AS ip,
				(SELECT COUNT(*) FROM tab_clientes_autenticacoes WHERE tab_clientes_autenticacoes.ssi_id = tab_servidores_ssids.ssi_id AND tab_clientes_autenticacoes.cas_id IN (1, 2)) AS pontos,
				tab_servidores_ssids_tipos.sst_id AS tipo
			FROM
				tab_servidores_ssids
				INNER JOIN tab_servidores_ssids_tipos ON (tab_servidores_ssids.sst_id = tab_servidores_ssids_tipos.sst_id)
			WHERE
				tab_servidores_ssids.sei_id = $id
			ORDER BY
				tab_servidores_ssids.ssi_nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$ssids[] = $dados;
		}
		return $ssids;
	}
	
	
	function buscaRedes($interface_id) {
		$query = "
  	  SELECT
  	    red_id AS id,
  	    red_endereco_ip AS endereco_ip,
  	    red_mascara AS mascara_rede
  	  FROM
  	    tab_redes
  	  WHERE
  	    sei_id = $interface_id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["endereco_ip"] = $this->geraIP($dados["endereco_ip"], 1, $dados["mascara_rede"]);
			$dados["mascara_rede"] = $this->formataMascara($dados["mascara_rede"]);
			$redes[] = $dados;
		}
		return $redes;
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
	
	function corrigeClasse($classe) {
		$classe = explode("/", $classe);
		$mascara = sprintf("%0-32s", str_repeat("1", $classe[1]));
		$ip = explode(".", $classe[0]);
		for ($i = 0; $i < 4; $i++) {
			$ip[$i] = sprintf("%08s", decbin($ip[$i]));
		}
		$ip = implode($ip);
		$ip = sprintf("%0-32s", $ip & $mascara);
		for ($i = 0; $i < 4; $i++) {
			$resultado[] = bindec(substr($ip, $i * 8, 8));
		}
		return implode(".", $resultado) . "/" . $classe[1];
	}
	
	##############
	### Fechar ###
	##############
	

	function fechar() {
		$this->conteudo = $this->dados_item["servidor_id"] ? "<script type=\"text/javascript\">window.opener.buscaServidoresInterfaces({$this->dados_item["servidor_id"]})</script>" : "";
		$this->conteudo .= "<script type=\"text/javascript\">window.close()</script>";
	}
	
	#############
	### Geral ###
	#############
	

	function buscaPrefixoIP($prefixo, $posicao) {
		$prefixo = explode('.', $prefixo);
		return $prefixo[$posicao];
	}
	
	function geraSelect($itens, $selecionado = null, $item_nulo = false) {
		if ($item_nulo)
			$select = "<option></option>";
		foreach ($itens as $valor => $item) {
			$select .= "<option value=\"$valor\"" . (isset($selecionado) && $valor == $selecionado ? " selected" : "") . ">$item</option>";
		}
		return $select;
	}
	
	function formataIP($ip, $nome = "") {
		$conteudo[] = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr class=\"txtAzulEscuro11\">";
		if ($nome) {
			$conteudo[] = "<td style=\"text-align:left;width:40px;\">$nome</td>";
			$conteudo[] = "<td style=\"text-align:right;width:5px;\">(</td>";
		}
		$ip = explode(".", $ip);
		foreach ($ip as $valor) {
			if ((count($conteudo) > 1 && !$nome) || (count($conteudo) > 3 && $nome)) {
				$conteudo[] = "<td style=\"text-align:center;width:5px;\">.</td>";
			}
			if (substr($valor, 0, 1) == "(") {
				$valor = substr($valor, 1);
				$conteudo[] = "<td style=\"text-align:center;width:5px;\">(</td>";
			}
			$conteudo[] = "<td style=\"text-align:center;width:20px;\">" . preg_replace("/[^0-9]/", "", $valor) . "</td>";
			if (substr($valor, -1) == ")") {
				$valor = substr($valor, 0, -1);
				$conteudo[] = "<td style=\"text-align:center;width:5px;\">)</td>";
			}
		}
		if ($nome) {
			$conteudo[] = "<td style=\"text-align:left;width:5px;\">)</td>";
		}
		$conteudo[] = "</tr></table>";
		return implode($conteudo);
	}
	
	function buscaTipoInterfaces($tipo_id = null, $interface_usada = false) {
		$query = "
  		SELECT
  			tab_servidores_interfaces_tipos.sit_id AS id,
  			tab_servidores_interfaces_tipos.sit_nome as nome
  		FROM
  			tab_servidores_interfaces_tipos
  		ORDER BY
  			nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if (!$interface_usada || ($tipo_id == $dados["id"]) || (in_array($tipo_id, array(3, 5)) && in_array($dados["id"], array(3, 5)))) {
				$tipos[$dados["id"]] = $dados["nome"];
			}
		}
		return $tipos;
	}
	
	function buscaCanais() {
		$i = 1;
		do {
			$nomes[$i] = $i;
			$i += 1;
		} while ($i <= 11);
		return $nomes;
	}
	
	function buscaServidor($id) {
		$query = "
      SELECT
      	tab_servidores.ser_nome AS nome
      FROM
      	tab_servidores
      WHERE
      	tab_servidores.ser_id = $id
      ORDER BY
      	nome";
		$query = $this->bancodedados->query($query);
		$servidor = $this->bancodedados->resultadoArray($query);
		return $servidor["nome"];
	}
	
	function buscaPadroes() {
		$query = "
   	  SELECT DISTINCT
   	    tab_servidores_interfaces_padroes.sip_id AS id,
   	    tab_servidores_interfaces_padroes.sip_nome as nome
   	  FROM
   	    tab_servidores_interfaces_padroes
   	  ORDER BY
   	    nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$padroes[$dados["id"]] = $dados["nome"];
		}
		return $padroes;
	}
	
	function buscaServidoresServicos($interface_id) {
		$servidores_servicos = controle::instanciaObjeto("servidores_servicos", $this->bancodedados, $this->permissoes, false);
		return $servidores_servicos->geraServidoresServicosListagem($servidores_servicos->buscaServidoresServicos($interface_id));
	}
	
	function buscaServidoresServicosAdicionar($interface_id) {
		$servidores_servicos = controle::instanciaObjeto("servidores_servicos", $this->bancodedados, $this->permissoes, false);
		return $servidores_servicos->buscaServidoresServicosAdicionar($interface_id);
	}
	
	function buscaServico($sse_id) {
		$query = "
			SELECT
				tab_servidores_servicos_tipos.sst_nome AS nome,
				tab_servidores_servicos.sss_id AS status_id,
				tab_servidores_servicos_status.sss_nome AS status_nome
			FROM
				tab_servidores_servicos
				INNER JOIN tab_servidores_servicos_tipos ON (tab_servidores_servicos.sst_id = tab_servidores_servicos_tipos.sst_id)
				INNER JOIN tab_servidores_servicos_status ON (tab_servidores_servicos.sss_id = tab_servidores_servicos_status.sss_id)
			WHERE
				tab_servidores_servicos.ses_id = $sse_id";
		$query = $this->bancodedados->query($query);
		return $this->bancodedados->resultadoArray($query);
	}
	
	function buscaListaServicos($sit_id) {
		$query = "
			SELECT
				tab_servidores_servicos_tipos.sst_id as id,
				tab_servidores_servicos_tipos.sst_nome AS nome
			FROM
				rel_sit_sst_contem
				INNER JOIN tab_servidores_interfaces_tipos ON (rel_sit_sst_contem.sit_id = tab_servidores_interfaces_tipos.sit_id)
				INNER JOIN tab_servidores_servicos_tipos ON (rel_sit_sst_contem.sst_id = tab_servidores_servicos_tipos.sst_id)
			WHERE
				tab_servidores_interfaces_tipos.sit_id = $sit_id
			ORDER BY
				nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servicos[$dados["id"]] = $dados["nome"];
		}
		return $servicos;
	}
	
	function interfaceUsada($interface) {
		// Consulta Links
		$query = "
			SELECT
				tab_servidores_links.sel_id AS id
			FROM
				tab_servidores_links
			WHERE
				tab_servidores_links.sei_id = '$interface'";
		$query = $this->bancodedados->query($query);
		if ($this->bancodedados->linhas($query)) {
			return true;
		} else {
			// Consulta Servidores
			$query = "
				SELECT
					tab_servidores.ser_id AS id
				FROM
					tab_servidores
				WHERE
					tab_servidores.sei_id = '$interface'";
			$query = $this->bancodedados->query($query);
			if ($this->bancodedados->linhas($query)) {
				return true;
			} else {
				// Consulta Condomínios
				$query = "
					SELECT
						tab_condominios_blocos.con_id AS id
					FROM
						tab_condominios_blocos
					WHERE
						tab_condominios_blocos.sei_id = '$interface'";
				$query = $this->bancodedados->query($query);
				if ($this->bancodedados->linhas($query)) {
					return true;
				} else {
					// Consulta Servidores
					$query = "
						SELECT
							tab_servidores_servicos.ses_id AS id
						FROM
							tab_servidores_servicos
						WHERE
							tab_servidores_servicos.sei_id = '$interface'";
					$query = $this->bancodedados->query($query);
					if ($this->bancodedados->linhas($query)) {
						return true;
					}
				}
			}
		}
	}
	
	function buscaInterfaceAnterior($id) {
		$query = "
			SELECT
    		CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.',	tab_servidores_interfaces.sei_endereco_ip) AS ip,
				tab_servidores_interfaces.sei_mascara_rede as mascara_rede
    	FROM
	    	tab_servidores_interfaces
				INNER JOIN tab_servidores ON (tab_servidores_interfaces.ser_id = tab_servidores.ser_id)
    	WHERE
		  	tab_servidores_interfaces.ser_id = $id AND
		    tab_servidores.sep_id = 2";
		$query = $this->bancodedados->query($query);
		return $this->bancodedados->resultadoArray($query);
	}
	
	function buscaCondominiosInterface($interface_id, $carp = false) {
		$query = "
	    SELECT
	      tab_condominios.con_id AS condominio_id,
	      tab_servidores_interfaces.sei_nome AS interface_nome,
	      CONCAT(tab_condominios.con_nome, ' (', tab_cidades.cid_nome, ' / ', tab_bairros.bai_nome, ')') AS condominio_nome,
	      tab_condominios_blocos.cob_nome AS bloco_nome,
	      COUNT(DISTINCT tab_clientes_pontos.clp_id) + COUNT(DISTINCT tab_clientes_autenticacoes.cau_id) AS pontos_ativos
	    FROM
	      tab_condominios
	      INNER JOIN tab_condominios_blocos ON (tab_condominios.con_id = tab_condominios_blocos.con_id)
	      INNER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
	      INNER JOIN tab_bairros ON (tab_condominios.bai_id = tab_bairros.bai_id)
	      INNER JOIN tab_cidades ON (tab_condominios.cid_id = tab_cidades.cid_id)
	      LEFT OUTER JOIN tab_clientes ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
	      LEFT OUTER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
  			LEFT OUTER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id AND tab_clientes_pontos.ass_id = tab_assinaturas.ass_id AND tab_clientes_pontos.cps_id = 1)
  			LEFT OUTER JOIN tab_clientes_autenticacoes ON (tab_servidores_interfaces.sei_id = tab_clientes_autenticacoes.sei_id AND tab_clientes_autenticacoes.ass_id = tab_assinaturas.ass_id AND tab_clientes_autenticacoes.cas_id = 1)
  		WHERE
	      tab_servidores_interfaces." . ($carp ? "sei_pai" : "sei_id") . " = $interface_id
	    GROUP BY
	      tab_condominios_blocos.cob_id
	    ORDER BY" . ($carp ? "
	      tab_servidores_interfaces.sei_nome," : "") . "
	      tab_condominios.con_nome,
	      tab_bairros.bai_nome,
	      tab_condominios_blocos.cob_nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($this->permissoes["condominios"]["ver_dt"]) {
				$dados["link"] = "javascript:((window.opener) ? window.opener.location='?m=condominios&a=ver_dt&i={$dados["condominio_id"]}' : null)";
			}
			$dados["bloco_nome"] = $dados["bloco_nome"] == "Único" ? "-" : $dados["bloco_nome"];
			$condominios[] = $dados;
		}
		return $this->geraCondominiosInterface($condominios, $carp);
	}
	
	function geraCondominiosInterface($dados, $carp = false) {
		if ($carp) {
			$cabecalho = array("interface_nome" => "CARP", "condominio_nome" => "Condomínio", "bloco_nome" => "Bloco", "pontos_ativos" => "Ativos");
			$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, array("pontos_ativos" => "formataNumero"), null, null, array("left", "left"));
		} else {
			$cabecalho = array("condominio_nome" => "Condomínio", "bloco_nome" => "Bloco", "pontos_ativos" => "Ativos");
			$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, array("pontos_ativos" => "formataNumero"));
		}
		return $listagem->conteudo;
	}
	
	##################################
	### Adicionar Dados Cadastrais ###
	##################################
	

	function adicionar() {
		if (!$_POST) {
			$this->conteudo .= $this->carregaArquivo("servidores_interfaces.adicionar.inc.php");
			$acoes .= '<td bgcolor="#505050"><strong>&nbsp;Adicionar&nbsp;</strong></td>';
			$this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
			// Dados
			$dados = $this->buscaInterfaceAnterior($_GET["servidor_id"]);
			if ($dados) {
				$ip = explode(".", $dados["ip"]);
				$mascara_rede = explode(".", $dados["mascara_rede"]);
			} else {
				$mascara_rede[0] = "255";
				$mascara_rede[1] = "255";
				$mascara_rede[2] = "255";
			}
			$this->conteudo = str_replace('[interfaces_carp]', $this->geraListagemCarp(null, true), $this->conteudo);
			$this->conteudo = str_replace('[prefixo_ip_1]', $ip[0], $this->conteudo);
			$this->conteudo = str_replace('[prefixo_ip_2]', $ip[1], $this->conteudo);
			$this->conteudo = str_replace('[prefixo_ip_3]', $ip[2], $this->conteudo);
			$this->conteudo = str_replace('[endereco_ip]', $ip[3], $this->conteudo);
			$this->conteudo = str_replace('[mascara_rede_1]', $mascara_rede[0], $this->conteudo);
			$this->conteudo = str_replace('[mascara_rede_2]', $mascara_rede[1], $this->conteudo);
			$this->conteudo = str_replace('[mascara_rede_3]', $mascara_rede[2], $this->conteudo);
			$this->conteudo = str_replace('[mascara_rede_4]', $mascara_rede[3], $this->conteudo);
			$this->conteudo = str_replace('[servidor_nome]', $this->buscaServidor($_GET["servidor_id"]), $this->conteudo);
			$this->conteudo = str_replace('[servidor_id]', $_GET["servidor_id"], $this->conteudo);
			$this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipoInterfaces(), "", "n"), $this->conteudo);
			$this->conteudo = str_replace('[padrao]', $this->geraSelect($this->buscaPadroes(), $this->dados_item["wireless"], "n"), $this->conteudo);
			$this->conteudo = str_replace('[canal]', $this->geraSelect($this->buscaCanais()), $this->conteudo);
			// Botões
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar&servidor_id={$_GET["servidor_id"]}", $this->conteudo);
		} else {
			if ($_POST["padrao"] == 3) {
				$configuracao_parametros["essid"] = $_POST["essid"];
				$configuracao_parametros["chave_criptografia"] = $_POST["chave_criptografia"];
				$configuracao_parametros["canal"] = $_POST["canal"];
				$configuracao = addslashes(serialize($configuracao_parametros));
			} else {
				$configuracao = "";
			}
			if ($_POST["tipo"] == 6) {
				$prefixo = "NULL";
				$masca_rede = "NULL";
				$_POST["endereco_ip"] = "NULL";
			} else {
				$prefixo = $_POST["prefixo_ip_1"] ? "'{$_POST["prefixo_ip_1"]}.{$_POST["prefixo_ip_2"]}.{$_POST["prefixo_ip_3"]}'" : "NULL";
				$masca_rede = $_POST["mascara_rede_1"] ? "'{$_POST["mascara_rede_1"]}.{$_POST["mascara_rede_2"]}.{$_POST["mascara_rede_3"]}.{$_POST["mascara_rede_4"]}'" : "NULL";
				$_POST["endereco_ip"] = $_POST["endereco_ip"] ? $_POST["endereco_ip"] : "NULL";
			}
			$_POST["nome"] = trim($_POST["nome"]);
			$_POST["reserva_ips"] = $_POST["reserva_ips"] ? intval($_POST["reserva_ips"], 10) : "NULL";
			$query = "
				INSERT INTO
					tab_servidores_interfaces (ser_id, sei_prefixo_ip, sei_endereco_ip, sei_mascara_rede, sit_id, sei_nome, sei_descricao, sip_id, sia_id, sei_configuracao, sei_reserva_ips)
				VALUES ({$_GET["servidor_id"]}, $prefixo, {$_POST["endereco_ip"]}, $masca_rede, {$_POST["tipo"]}, '{$_POST["nome"]}', '{$_POST["descricao"]}', {$_POST["padrao"]}, 2, '$configuracao', {$_POST["reserva_ips"]})";
			$this->bancodedados->query($query);
			$id = $this->bancodedados->ultimoIdInserido();
			// Insere Serviços das outras Interfaces
			if (($_POST["tipo"] == 1 || $_POST["tipo"] == 6) && ($servicos = $this->buscaServicosServidor($_GET["servidor_id"]))) {
				foreach ($servicos as $servico) {
					$query = "INSERT INTO tab_servidores_servicos (sei_id, ses_parametros, sst_id, sss_id) VALUES ($id, '{$servico["parametros"]}', {$servico["tipo_id"]}, {$servico["status_id"]})";
					$this->bancodedados->query($query);
				}
			}
			// Atualiza Carps
			if ($_POST["tipo"] == 6) {
				$_POST["lista_carp"] = unserialize(stripslashes($_POST["lista_carp"]));
				foreach ($_POST["lista_carp"] as $carp) {
					if ($carp["endereco_ip"]) {
						$carp["endereco_ip"] = explode(".", $carp["endereco_ip"]);
						$carp["prefixo_ip"] = "'{$carp["endereco_ip"][0]}.{$carp["endereco_ip"][1]}.{$carp["endereco_ip"][2]}'";
						$carp["endereco_ip"] = $carp["endereco_ip"][4];
					} else {
						$carp["prefixo_ip"] = "NULL";
						$carp["endereco_ip"] = "NULL";
					}
					$carp["mascara_rede"] = $carp["mascara_rede"] ? "'{$carp["mascara_rede"]}'" : "NULL";
					$query = "INSERT INTO tab_servidores_interfaces (ser_id, sei_nome, sei_prefixo_ip, sei_endereco_ip, sei_mascara_rede, sei_pai, sit_id, sip_id, sia_id) VALUES ({$_GET["servidor_id"]}, '{$carp["nome"]}', {$carp["prefixo_ip"]}, {$carp["endereco_ip"]}, {$carp["mascara_rede"]}, $id, 6, {$_POST["padrao"]}, 2)";
					$this->bancodedados->query($query);
					$carp_id = $this->bancodedados->ultimoIdInserido();
					// Insere Serviços Existentes
					if ($servicos) {
						foreach ($servicos as $servico) {
							$query = "INSERT INTO tab_servidores_servicos (sei_id, ses_parametros, sst_id, sss_id) VALUES ($carp_id, '{$servico["parametros"]}', {$servico["tipo_id"]}, {$servico["status_id"]})";
							$this->bancodedados->query($query);
						}
					}
				}
			}
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
			header("Location: ./?m=" . $this->dados_modulo["nome"] . "&a=ver_dc&i=" . $id);
		}
	}
	
	############################
	### Ver Dados Cadastrais ###
	############################
	

	function ver_dc() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			$this->conteudo .= $this->carregaArquivo("servidores_interfaces.ver_dc.inc.php");
			// Ações
			$acoes = '<td bgcolor="#505050" width="33%"><strong>&nbsp;Dados Cadastrais&nbsp;</strong></td>';
			if (in_array($this->dados_item["tipo_id"], array(1, 6))) {
				if ($this->permissoes[$this->dados_modulo["nome"]]["ver_ds"])
					$acoes .= '<td bgcolor="#828282" width="33%"><strong>&nbsp;<a href="?m=' . $this->dados_modulo["nome"] . '&a=ver_ds&i=' . $this->dados_item["id"] . '" class="txtBranco11">Serviços</a>&nbsp;</strong></td>';
				if ($this->permissoes[$this->dados_modulo["nome"]]["ver_co"])
					$acoes .= '<td bgcolor="#828282" width="33%"><strong>&nbsp;<a href="?m=' . $this->dados_modulo["nome"] . '&a=ver_co&i=' . $this->dados_item["id"] . '" class="txtBranco11">Condomínios</a>&nbsp;</strong></td>';
			}
			$this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
			// Dados
			$padrao = $this->buscaPadroes();
			$tipo = $this->buscaTipoInterfaces();
			if ($this->dados_item["tipo_id"] == 6) {
				$this->conteudo = preg_replace('@\[nao_servidor_cliente_carp\].*?\[/nao_servidor_cliente_carp\]@si', "", $this->conteudo);
				$this->conteudo = preg_replace('@\[.?servidor_cliente_carp\]@si', "", $this->conteudo);
				$this->conteudo = str_replace('[interfaces_carp]', $this->geraListagemCarp($this->dados_item["interfaces_carp"], false), $this->conteudo);
			} else {
				$this->conteudo = preg_replace('@\[servidor_cliente_carp\].*?\[/servidor_cliente_carp\]@si', "", $this->conteudo);
				$this->conteudo = preg_replace('@\[.?nao_servidor_cliente_carp\]@si', "", $this->conteudo);
			}
			$this->conteudo = str_replace('[id]', $this->dados_item["id"], $this->conteudo);
			$this->conteudo = str_replace('[servidor_id]', $_GET["migrado"] ? $_GET["migrado"] : $this->dados_item["servidor_id"], $this->conteudo);
			$this->conteudo = str_replace('[servidor_nome]', $this->dados_item["servidor_nome"], $this->conteudo);
			$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace('[endereco_ip]', $this->formataIP($this->dados_item["prefixo_ip_1"] . "." . $this->dados_item["prefixo_ip_2"] . "." . $this->dados_item["prefixo_ip_3"] . "." . $this->dados_item["endereco_ip"]), $this->conteudo);
			$this->conteudo = str_replace('[mascara_rede]', $this->formataIP($this->dados_item["mascara_rede"]), $this->conteudo);
			$this->conteudo = str_replace('[reserva_ips]', $this->dados_item["reserva_ips"], $this->conteudo);
			$this->conteudo = str_replace('[padrao]', $padrao[$this->dados_item["padrao"]], $this->conteudo);
			$this->conteudo = str_replace('[essid]', $this->dados_item["configuracao"]["essid"], $this->conteudo);
			$this->conteudo = str_replace('[chave_criptografia]', $this->dados_item["configuracao"]["chave_criptografia"], $this->conteudo);
			$this->conteudo = str_replace('[canal]', $this->dados_item["configuracao"]["canal"], $this->conteudo);
			$this->conteudo = str_replace('[tipo]', $tipo[$this->dados_item["tipo_id"]], $this->conteudo);
			$this->conteudo = str_replace('[comentarios]', nl2br($this->dados_item["descricao"]), $this->conteudo);
			$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
			// Botões
			if ($this->permissoes[$this->dados_modulo["nome"]]["publicar"] && in_array($this->dados_item["tipo_id"], array(1, 6)))
				$botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:80" value="Publicar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=publicar&i=' . $_GET["i"] . '&retorno=' . $this->acao . '\';">';
			if ($this->dados_item["status_id"] != 1 && $this->permissoes[$this->dados_modulo["nome"]]["ativar"])
				$botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["desativar"])
				$botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=desativar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["remover"])
				$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"] && in_array($this->dados_item["tipo_id"], array(1, 3, 6)))
				$botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		} else {
			$this->fechar();
		}
	}
	
	###############################
	### Editar Dados Cadastrais ###
	###############################
	

	function editar_dc() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			if (!$_POST) {
				$mascara_rede = explode(".", $this->dados_item["mascara_rede"]);
				$this->conteudo .= $this->carregaArquivo("servidores_interfaces.editar_dc.inc.php");
				// Ações
				$acoes = '<td bgcolor="#505050" width="33%"><strong>&nbsp;Dados Cadastrais&nbsp;</strong></td>';
				if (in_array($this->dados_item["tipo_id"], array(1, 6))) {
					if ($this->permissoes[$this->dados_modulo["nome"]]["ver_ds"])
						$acoes .= '<td bgcolor="#828282" width="33%"><strong>&nbsp;<a href="?m=' . $this->dados_modulo["nome"] . '&a=ver_ds&i=' . $this->dados_item["id"] . '" class="txtBranco11">Serviços</a>&nbsp;</strong></td>';
					if ($this->permissoes[$this->dados_modulo["nome"]]["ver_co"])
						$acoes .= '<td bgcolor="#828282" width="33%"><strong>&nbsp;<a href="?m=' . $this->dados_modulo["nome"] . '&a=ver_co&i=' . $this->dados_item["id"] . '" class="txtBranco11">Condomínios</a>&nbsp;</strong></td>';
				}
				$this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
				// Dados
				if ($this->dados_item["sistema_operacional_id"] == 9) {
					$this->conteudo = str_replace('[listagem_pool]', $this->geraListagemPool($this->dados_item["configuracao"]["pool"], true), $this->conteudo);
					$this->conteudo = str_replace('[lista_pool]', @serialize($this->dados_item["configuracao"]["pool"]), $this->conteudo);
					$this->conteudo = preg_replace('@\[.?interface_pool\]@si', "", $this->conteudo);
				} else {
					$this->conteudo = preg_replace('@\[interface_pool\].*?\[/interface_pool\]@si', "", $this->conteudo);
				}
				if ($this->dados_item["sistema_operacional_id"] == 9 && $this->dados_item["tipo_id"] == 1) {
					$this->conteudo = str_replace('[listagem_ssid]', $this->geraListagemSSID($this->dados_item["ssids"], true), $this->conteudo);
					$this->conteudo = str_replace('[lista_ssid]', @serialize($this->dados_item["ssids"]), $this->conteudo);
					$this->conteudo = preg_replace('@\[.?interface_ssid\]@si', "", $this->conteudo);
				} else {
					$this->conteudo = preg_replace('@\[interface_ssid\].*?\[/interface_ssid\]@si', "", $this->conteudo);
				}
				
				if ($this->dados_item["tipo_id"] == 6) {
					$this->conteudo = preg_replace('@\[interface_clientes\].*?\[/interface_clientes\]@si', "", $this->conteudo);
					$this->conteudo = preg_replace('@\[interface_redes\].*?\[/interface_redes\]@si', "", $this->conteudo);
					$this->conteudo = preg_replace('@\[.?interface_carp\]@si', "", $this->conteudo);
					$this->conteudo = str_replace('[interfaces_carp]', $this->geraListagemCarp($this->dados_item["interfaces_carp"], true), $this->conteudo);
					$this->conteudo = str_replace('[lista_carp]', @serialize($this->dados_item["interfaces_carp"]), $this->conteudo);
				} elseif (($this->dados_item["tipo_id"] == 1) && $this->dados_item["redes"]) {
					$this->conteudo = preg_replace('@\[interface_clientes\].*?\[/interface_clientes\]@si', "", $this->conteudo);
					$this->conteudo = preg_replace('@\[interface_carp\].*?\[/interface_carp\]@si', "", $this->conteudo);
					$this->conteudo = preg_replace('@\[.?interface_redes\]@si', "", $this->conteudo);
					$this->conteudo = str_replace('[redes]', $this->geraListagemRedes($this->dados_item["redes"]), $this->conteudo);
				} else {
					$this->conteudo = preg_replace('@\[interface_redes\].*?\[/interface_redes\]@si', "", $this->conteudo);
					$this->conteudo = preg_replace('@\[interface_carp\].*?\[/interface_carp\]@si', "", $this->conteudo);
					$this->conteudo = preg_replace('@\[.?interface_clientes\]@si', "", $this->conteudo);
				}
				$this->conteudo = str_replace('[id]', $this->dados_item["id"], $this->conteudo);
				$this->conteudo = str_replace('[servidor_id]', $this->dados_item["servidor_id"], $this->conteudo);
				$this->conteudo = str_replace('[servidor_nome]', $this->dados_item["servidor_nome"], $this->conteudo);
				$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
				$this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipoInterfaces($this->dados_item["tipo_id"], true), $this->dados_item["tipo_id"], "n"), $this->conteudo);
				$this->conteudo = str_replace('[prefixo_ip_1]', $this->dados_item["prefixo_ip_1"], $this->conteudo);
				$this->conteudo = str_replace('[prefixo_ip_2]', $this->dados_item["prefixo_ip_2"], $this->conteudo);
				$this->conteudo = str_replace('[prefixo_ip_3]', $this->dados_item["prefixo_ip_3"], $this->conteudo);
				$this->conteudo = str_replace('[endereco_ip]', $this->dados_item["endereco_ip"], $this->conteudo);
				$this->conteudo = str_replace('[situacao]', ($this->dados_item["prefixo_ip_1"]) ? "enabled" : "disabled", $this->conteudo);
				$this->conteudo = str_replace('[mascara_rede_1]', $mascara_rede[0], $this->conteudo);
				$this->conteudo = str_replace('[mascara_rede_2]', $mascara_rede[1], $this->conteudo);
				$this->conteudo = str_replace('[mascara_rede_3]', $mascara_rede[2], $this->conteudo);
				$this->conteudo = str_replace('[mascara_rede_4]', $mascara_rede[3], $this->conteudo);
				$this->conteudo = str_replace('[reserva_ips]', $this->dados_item["reserva_ips"], $this->conteudo);
				$this->conteudo = str_replace('[padrao]', $this->geraSelect($this->buscaPadroes(), $this->dados_item["padrao"], ""), $this->conteudo);
				$this->conteudo = str_replace('[essid]', $this->dados_item["configuracao"]["essid"], $this->conteudo);
				$this->conteudo = str_replace('[chave_criptografia]', ($this->dados_item["configuracao"]["chave_criptografia"]), $this->conteudo);
				$this->conteudo = str_replace('[canal]', $this->geraSelect($this->buscaCanais(), $this->dados_item["configuracao"]["canal"], "n"), $this->conteudo);
				$this->conteudo = str_replace('[situacao_parametros]', ($this->dados_item["padrao"] == 3) ? " enabled" : " disabled", $this->conteudo);
				$this->conteudo = str_replace('[descricao]', $this->dados_item["descricao"], $this->conteudo);
				$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
				// Botões
				if ($this->permissoes[$this->dados_modulo["nome"]]["publicar"] && in_array($this->dados_item["tipo_id"], array(1, 6)))
					$botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:80" value="Publicar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=publicar&i=' . $_GET["i"] . '&retorno=' . $this->acao . '\';">';
				if ($this->dados_item["status_id"] != 1 && $this->permissoes[$this->dados_modulo["nome"]]["ativar"])
					$botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" style="width:65" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["desativar"])
					$botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" style="width:65" value="Desativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=desativar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["remover"])
					$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" style="width:65" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"] && in_array($this->dados_item["tipo_id"], array(1, 3, 6)))
					$botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" style="width:45" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" style="width:55"  value="Limpar">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" style="width:55" value="Fechar" onClick="window.close()">';
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar_dc&i={$this->dados_item["id"]}", $this->conteudo);
			} else {
				// Configuração
				if ($_POST["padrao"] == 3) {
					$configuracao["essid"] = $_POST["essid"];
					$configuracao["chave_criptografia"] = $_POST["chave_criptografia"];
					$configuracao["canal"] = $_POST["canal"];
				}
				if ($_POST["lista_pool"]) {
					$configuracao["pool"] = unserialize(stripslashes($_POST["lista_pool"]));
				}
				$configuracao = $configuracao ? addslashes(serialize($configuracao)) : "";
				if (($this->dados_item["tipo_id"] == 6) || (($this->dados_item["tipo_id"] == 1) && $this->dados_item["redes"])) {
					$_POST["endereco_ip"] = "NULL";
					$prefixo = "NULL";
					$masca_rede = "NULL";
					$endereco_ip = "NULL";
				} else {
					$prefixo = $_POST["prefixo_ip_1"] ? "'{$_POST["prefixo_ip_1"]}.{$_POST["prefixo_ip_2"]}.{$_POST["prefixo_ip_3"]}'" : "NULL";
					$masca_rede = $_POST["mascara_rede_1"] ? "'{$_POST["mascara_rede_1"]}.{$_POST["mascara_rede_2"]}.{$_POST["mascara_rede_3"]}.{$_POST["mascara_rede_4"]}'" : "NULL";
					$endereco_ip = $_POST["prefixo_ip_1"] && $_POST["endereco_ip"] ? $_POST["endereco_ip"] : "NULL";
				}
				$_POST["nome"] = trim($_POST["nome"]);
				$_POST["reserva_ips"] = $_POST["reserva_ips"] ? intval($_POST["reserva_ips"], 10) : "NULL";
				$query = "
			  	UPDATE
			  		tab_servidores_interfaces
			  	SET
			  		sei_nome = '{$_POST["nome"]}',
			  		sei_prefixo_ip = $prefixo,
			  		sei_endereco_ip = $endereco_ip,
			  		sei_mascara_rede = $masca_rede,
			  		sei_descricao = '{$_POST["descricao"]}',
			  		sip_id = {$_POST["padrao"]},
			  		sei_configuracao = '$configuracao',
			  		sei_reserva_ips = {$_POST["reserva_ips"]}
			  	WHERE
			  		sei_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				if ($this->buscaTipoServidor($this->dados_item["servidor_id"]) == 2) {
					$query = "
				  	UPDATE
				  		tab_servidores_interfaces
				  	SET
				  		sei_prefixo_ip = '$prefixo',
				  		sei_endereco_ip = '{$_POST["endereco_ip"]}',
				  		sei_mascara_rede = '$masca_rede'
				  	WHERE
				  		ser_id = {$this->dados_item["servidor_id"]}";
					$this->bancodedados->query($query);
				}
				// SSIDs
				if ($this->dados_item["sistema_operacional_id"] == 9 && $this->dados_item["tipo_id"] == 1) {
					$_POST["lista_ssid"] = unserialize(stripslashes($_POST["lista_ssid"]));
					$_POST["lista_ip"] = unserialize(stripslashes($_POST["lista_ip"]));
					for ($i = 0; $i < count($_POST["lista_ssid"]); $i++) {
						if ($_POST["lista_ssid"][$i]["id"]) {
							$query = "UPDATE tab_servidores_ssids SET ssi_nome = '{$_POST["lista_ssid"][$i]["ssid"]}', ssi_ip = '{$_POST["lista_ssid"][$i]["ip"]}', sst_id = '{$_POST["lista_ssid"][$i]["tipo"]}' WHERE ssi_id = {$_POST["lista_ssid"][$i]["id"]}";
							$this->bancodedados->query($query);
							$ssids_mantidos[] = $_POST["lista_ssid"][$i]["id"];
						} else {
							$query = "INSERT INTO tab_servidores_ssids (ssi_nome, sei_id, ssi_ip, sst_id) VALUES ('{$_POST["lista_ssid"][$i]["ssid"]}', {$_GET["i"]}, '{$_POST["lista_ssid"][$i]["ip"]}', '{$_POST["lista_ssid"][$i]["tipo"]}')";
							$this->bancodedados->query($query);
							$ssids_mantidos[] = $this->bancodedados->ultimoIdInserido();
						}
					}

					$query = "UPDATE tab_clientes_autenticacoes SET ssi_id = null WHERE cas_id = 3 AND sei_id = {$_GET["i"]}" . ($ssids_mantidos ? " AND ssi_id NOT IN (" . implode(", ", $ssids_mantidos) . ")" : "");
					$this->bancodedados->query($query);

					$query = "DELETE FROM tab_servidores_ssids WHERE sei_id = {$_GET["i"]}" . ($ssids_mantidos ? " AND ssi_id NOT IN (" . implode(", ", $ssids_mantidos) . ")" : "");
					$this->bancodedados->query($query);
				}
				// Atualiza Carps
				if ($this->dados_item["tipo_id"] == 6) {
					$_POST["lista_carp"] = unserialize(stripslashes($_POST["lista_carp"]));
					// Busca Serviços
					$servicos = $this->buscaServicosServidor($this->dados_item["servidor_id"]);
					foreach ($_POST["lista_carp"] as $carp) {
						if ($carp["endereco_ip"]) {
							$carp["endereco_ip"] = explode(".", $carp["endereco_ip"]);
							$carp["prefixo_ip"] = "'{$carp["endereco_ip"][0]}.{$carp["endereco_ip"][1]}.{$carp["endereco_ip"][2]}'";
							$carp["endereco_ip"] = $carp["endereco_ip"][4];
						} else {
							$carp["prefixo_ip"] = "NULL";
							$carp["endereco_ip"] = "NULL";
						}
						$carp["mascara_rede"] = $carp["mascara_rede"] ? "'{$carp["mascara_rede"]}'" : "NULL";
						if ($carp["id"]) {
							// Atualiza Alterados
							$query = "UPDATE tab_servidores_interfaces SET sei_nome = '{$carp["nome"]}', sei_prefixo_ip = {$carp["prefixo_ip"]}, sei_endereco_ip = {$carp["endereco_ip"]}, sei_mascara_rede = {$carp["mascara_rede"]}, sei_reserva_ips = {$_POST["reserva_ips"]} WHERE sei_id = {$carp["id"]}";
							$this->bancodedados->query($query);
							$atualizados[] = $carp["id"];
						} else {
							// Insere Novos
							$query = "INSERT INTO tab_servidores_interfaces (ser_id, sei_nome, sei_prefixo_ip, sei_endereco_ip, sei_mascara_rede, sei_pai, sit_id, sip_id, sia_id, sei_reserva_ips) VALUES ({$this->dados_item["servidor_id"]}, '{$carp["nome"]}', {$carp["prefixo_ip"]}, {$carp["endereco_ip"]}, {$carp["mascara_rede"]}, {$this->dados_item["id"]}, 6, {$this->dados_item["padrao"]}, {$this->dados_item["status_id"]}, {$_POST["reserva_ips"]})";
							$this->bancodedados->query($query);
							$carp_id = $this->bancodedados->ultimoIdInserido();
							// Insere Serviços Existentes
							if ($servicos) {
								foreach ($servicos as $servico) {
									$query = "INSERT INTO tab_servidores_servicos (sei_id, ses_parametros, sst_id, sss_id) VALUES ($carp_id, '{$servico["parametros"]}', {$servico["tipo_id"]}, {$servico["status_id"]})";
									$this->bancodedados->query($query);
								}
							}
						}
					}
					// Apaga Removidos
					foreach ($this->dados_item["interfaces_carp"] as $carp) {
						if (!$atualizados || !in_array($carp["id"], $atualizados)) {
							// Remove Vínculo c/ Condomínios / Blocos
							$query = "UPDATE tab_condominios, tab_condominios_blocos SET sei_id = NULL WHERE sei_id = {$carp["id"]}";
							$this->bancodedados->query($query);
							// Remove Vínculo c/ Pontos Cancelados
							$query = "UPDATE tab_clientes_pontos SET sei_id = NULL WHERE sei_id = {$carp["id"]}";
							$this->bancodedados->query($query);
							// Remove Rede
							$query = "UPDATE tab_redes SET sei_id = NULL WHERE sei_id = {$carp["id"]}";
							$this->bancodedados->query($query);
							// Remove Rede
							$query = "DELETE FROM tab_servidores_servicos WHERE sei_id = {$carp["id"]}";
							$this->bancodedados->query($query);
							// Remove Interface
							$query = "DELETE FROM tab_servidores_interfaces WHERE sei_id = {$carp["id"]}";
							$this->bancodedados->query($query);
						}
					}
				}
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
			}
		} else {
			$this->fechar();
		}
	}
	
	####################
	### Ver Serviços ###
	####################
	

	function ver_ds() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			$this->conteudo .= $this->carregaArquivo("servidores_interfaces.ver_ds.inc.php");
			// Ações
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver_dc"])
				$acoes = '<td bgcolor="#828282" width="33%"><strong>&nbsp;<a href="?m=' . $this->dados_modulo["nome"] . '&a=ver_dc&i=' . $this->dados_item["id"] . '" class="txtBranco11">Dados Cadastrais</a>&nbsp;</strong></td>';
			$acoes .= '<td bgcolor="#505050" width="33%"><strong>&nbsp;Serviços&nbsp;</strong></td>';
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver_co"])
				$acoes .= '<td bgcolor="#828282" width="33%"><strong>&nbsp;<a href="?m=' . $this->dados_modulo["nome"] . '&a=ver_co&i=' . $this->dados_item["id"] . '" class="txtBranco11">Condomínios</a>&nbsp;</strong></td>';
			$this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
			// Dados
			$this->conteudo = str_replace('[id]', $this->dados_item["id"], $this->conteudo);
			$this->conteudo = str_replace('[servidor_id]', $this->dados_item["servidor_id"], $this->conteudo);
			$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace('[servidor_nome]', $this->dados_item["servidor_nome"], $this->conteudo);
			$this->conteudo = str_replace('[servidores_servicos]', $this->buscaServidoresServicos($_GET["i"]), $this->conteudo);
			$this->conteudo = str_replace('[servidores_servicos_adicionar]', $this->buscaServidoresServicosAdicionar($_GET["i"]), $this->conteudo);
			// Botões
			if ($this->permissoes[$this->dados_modulo["nome"]]["publicar"] && in_array($this->dados_item["tipo_id"], array(1, 6)))
				$botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:80" value="Publicar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=publicar&i=' . $_GET["i"] . '&retorno=' . $this->acao . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["remover"])
				$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"] && in_array($this->dados_item["tipo_id"], array(1, 3, 6)))
				$botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar_dc&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		} else {
			$this->fechar();
		}
	}
	
	#######################
	### Editar Serviços ###
	#######################
	

	function editar_ds() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			$this->conteudo .= $this->carregaArquivo("servidores_interfaces.ver_ds.inc.php");
			// Ações
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver_dc"])
				$acoes = '<td bgcolor="#828282" width="33%"><strong>&nbsp;<a href="?m=' . $this->dados_modulo["nome"] . '&a=ver_dc&i=' . $this->dados_item["id"] . '" class="txtBranco11">Dados Cadastrais</a>&nbsp;</strong></td>';
			$acoes .= '<td bgcolor="#505050" width="33%"><strong>&nbsp;Serviços&nbsp;</strong></td>';
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver_co"])
				$acoes .= '<td bgcolor="#828282" width="33%"><strong>&nbsp;<a href="?m=' . $this->dados_modulo["nome"] . '&a=ver_co&i=' . $this->dados_item["id"] . '" class="txtBranco11">Condomínios</a>&nbsp;</strong></td>';
			$this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
			// Dados
			$this->conteudo = str_replace('[id]', $this->dados_item["id"], $this->conteudo);
			$this->conteudo = str_replace('[servidor_id]', $this->dados_item["servidor_id"], $this->conteudo);
			$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace('[servidor_nome]', $this->dados_item["servidor_nome"], $this->conteudo);
			$this->conteudo = str_replace('[servidores_servicos]', $this->buscaServidoresServicos($_GET["i"]), $this->conteudo);
			$this->conteudo = str_replace('[servidores_servicos_adicionar]', $this->buscaServidoresServicosAdicionar($_GET["i"]), $this->conteudo);
			// Botões
			if ($this->permissoes[$this->dados_modulo["nome"]]["publicar"] && in_array($this->dados_item["tipo_id"], array(1, 6)))
				$botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:80" value="Publicar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=publicar&i=' . $_GET["i"] . '&retorno=' . $this->acao . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["remover"])
				$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"] && in_array($this->dados_item["tipo_id"], array(1, 3, 6)))
				$botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar_dc&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		} else {
			$this->fechar();
		}
	}
	
	#######################
	### Ver Condomínios ###
	#######################
	

	function ver_co() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			$this->conteudo .= $this->carregaArquivo("servidores_interfaces.ver_co.inc.php");
			// Ações
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver_dc"])
				$acoes = '<td bgcolor="#828282" width="33%"><strong>&nbsp;<a href="?m=' . $this->dados_modulo["nome"] . '&a=ver_dc&i=' . $this->dados_item["id"] . '" class="txtBranco11">Dados Cadastrais</a>&nbsp;</strong></td>';
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver_ds"])
				$acoes .= '<td bgcolor="#828282" width="33%"><strong>&nbsp;<a href="?m=' . $this->dados_modulo["nome"] . '&a=ver_ds&i=' . $this->dados_item["id"] . '" class="txtBranco11">Serviços</a>&nbsp;</strong></td>';
			$acoes .= '<td bgcolor="#505050" width="33%"><strong>&nbsp;Condomínios&nbsp;</strong></td>';
			$this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
			// Dados
			$this->conteudo = str_replace('[id]', $this->dados_item["id"], $this->conteudo);
			$this->conteudo = str_replace('[servidor_id]', $this->dados_item["servidor_id"], $this->conteudo);
			$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace('[servidor_nome]', $this->dados_item["servidor_nome"], $this->conteudo);
			$this->conteudo = str_replace('[condominios]', $this->buscaCondominiosInterface($_GET["i"], $this->dados_item["tipo_id"] == 6), $this->conteudo);
			// Botões
			if ($this->permissoes[$this->dados_modulo["nome"]]["publicar"] && in_array($this->dados_item["tipo_id"], array(1, 6)))
				$botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:80" value="Publicar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=publicar&i=' . $_GET["i"] . '&retorno=' . $this->acao . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["remover"])
				$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"] && in_array($this->dados_item["tipo_id"], array(1, 3, 6)))
				$botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar_dc&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		} else {
			$this->fechar();
		}
	}
	
	##############
	### Migrar ###
	##############
	

	function migrar() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("servidores_interfaces.migrar.inc.php");
				$acoes .= '<td bgcolor="#505050"><strong>&nbsp;Migrar&nbsp;</strong></td>';
				$this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
				// Dados
				$this->conteudo = str_replace('[servidor_nome]', $this->dados_item["servidor_nome"], $this->conteudo);
				$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
				$this->conteudo = str_replace('[servidor]', $this->geraSelect($this->buscaServidores($this->dados_item["tipo_id"]), null, true), $this->conteudo);
				$this->conteudo = str_replace('[interface_tipo_id]', $this->dados_item["tipo_id"], $this->conteudo);
				// Botões
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=migrar&i={$_GET["i"]}", $this->conteudo);
			} else {
				if ($_POST["servidor"] && !$_POST["interface"]) {
					// Migra Interface
					$this->bancodedados->query("UPDATE tab_servidores_interfaces SET ser_id = {$_POST["servidor"]} WHERE sei_id = {$_GET["i"]}");
					// Atualiza servidor autenticacoes
					$this->bancodedados->query("UPDATE tab_clientes_autenticacoes SET ser_id = {$_POST["servidor"]}, cau_publicado = 0 WHERE sei_id = {$_GET["i"]}");
					// Agenda publicação servidores
					$this->bancodedados->query("UPDATE tab_servidores SET ser_publicado = 0 WHERE ser_id IN ({$_POST["servidor"]}, {$this->dados_item["servidor_id"]})");
					// Migra redes
					$this->bancodedados->query("UPDATE tab_redes SET ser_id = {$_POST["servidor"]} WHERE sei_id = {$_GET["i"]}");
					// Redireciona
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}&migrado={$this->dados_item["servidor_id"]}");
				} elseif ($_POST["interface"]) {
					$this->migrarInterface($_GET["i"], $_POST["interface"]);
					// Agenda publicação servidores
					$this->bancodedados->query("UPDATE tab_servidores SET ser_publicado = 0 WHERE ser_id IN ({$_POST["servidor"]}, {$this->dados_item["servidor_id"]})");
					// Redireciona
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
				} else {
					$this->fechar();
				}
			}
		} else {
			$this->fechar();
		}
	}
	
	function migrarInterface($interface_atual_id, $interface_nova_id) {
		$dados_interface_atual = $this->buscaDadosInterface($interface_atual_id);
		$dados_interface_nova = $this->buscaDadosInterface($interface_nova_id);
		if ($dados_interface_atual["tipo_id"] == 1) {
			// Migra Pontos
			$query = "UPDATE tab_clientes_pontos SET sei_id = $interface_nova_id, clp_publicado = 0 WHERE sei_id = $interface_atual_id";
			$this->bancodedados->query($query);
			// Migra Autenticações
			$query = "UPDATE tab_clientes_autenticacoes SET sei_id = $interface_nova_id, ser_id = {$dados_interface_nova["servidor_id"]}, cau_publicado = 0 WHERE sei_id = $interface_atual_id";
			$this->bancodedados->query($query);
			// Migra SSIDs
			$query = "UPDATE tab_servidores_ssids SET sei_id = $interface_nova_id WHERE sei_id = $interface_atual_id";
			$this->bancodedados->query($query);
			// Migra Pool IPs
			if ($dados_interface_atual["configuracao"]["pool"]) {
				foreach ($dados_interface_atual["configuracao"]["pool"] as $pool_atual) {
					$existe = false;
					foreach ($dados_interface_nova["configuracao"]["pool"] as $pool_nova) {
						if ($pool_atual["classe"] == $pool_nova["classe"]) {
							$existe = true;
						}
					}
					if (!$existe) {
						$dados_interface_nova["configuracao"]["pool"][] = $pool_atual;
					}
				}
				$dados_interface_atual["configuracao"]["pool"] = null;
				// Atualiza pool atual
				$dados_interface_atual["configuracao"] = serialize($dados_interface_atual["configuracao"]);
				$query = "UPDATE tab_servidores_interfaces SET sei_configuracao = '{$dados_interface_atual["configuracao"]}' WHERE sei_id = $interface_atual_id";
				$this->bancodedados->query($query);
				// Atualiza pool nova
				$dados_interface_nova["configuracao"] = serialize($dados_interface_nova["configuracao"]);
				$query = "UPDATE tab_servidores_interfaces SET sei_configuracao = '{$dados_interface_nova["configuracao"]}' WHERE sei_id = $interface_nova_id";
				$this->bancodedados->query($query);
			}
			// Migra Condomínios
			$query = "UPDATE tab_condominios_blocos SET sei_id = $interface_nova_id WHERE sei_id = $interface_atual_id";
			$this->bancodedados->query($query);
		} elseif ($dados_interface_atual["tipo_id"] == 3) {
			// Migra Servidores
			$query = "UPDATE tab_servidores SET sei_id = $interface_nova_id WHERE sei_id = $interface_atual_id";
			$this->bancodedados->query($query);
		} else {
			if ($dados_interface_atual["interfaces_carp"]) {
				foreach ($dados_interface_atual["interfaces_carp"] as $dados) {
					// Migra Pontos
					$query = "UPDATE tab_clientes_pontos SET sei_id = $interface_nova_id, clp_publicado = 0 WHERE sei_id = {$dados["id"]}";
					$this->bancodedados->query($query);
					// Migra Condomínios
					$query = "UPDATE tab_condominios_blocos SET sei_id = $interface_nova_id WHERE sei_id = {$dados["id"]}";
					$this->bancodedados->query($query);
					// Migra Autenticações
				}
			}
		}
		// Migra Redes
		$query = "UPDATE tab_redes SET ser_id = {$dados_interface_nova["servidor_id"]}, sei_id = $interface_nova_id WHERE sei_id = $interface_atual_id";
		$this->bancodedados->query($query);
	}
	
	function buscaServidores() {
		$query = "
  		SELECT
  	    tab_servidores.ser_id AS id,
        CONCAT(tab_servidores.ser_nome, ' (', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip, ')') AS nome,
  			COUNT(DISTINCT tab_clientes_pontos.clp_id) + COUNT(DISTINCT tab_clientes_autenticacoes.cau_id) AS pontos_total
  	  FROM
  	    tab_servidores
  	    INNER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
  	    INNER JOIN tab_servidores_interfaces tab_interfaces_clientes ON (tab_servidores.ser_id = tab_interfaces_clientes.ser_id)
  	    LEFT OUTER JOIN tab_clientes_pontos ON (tab_interfaces_clientes.sei_id = tab_clientes_pontos.sei_id AND tab_clientes_pontos.cps_id = 1)
  	    LEFT OUTER JOIN tab_clientes_autenticacoes ON (tab_clientes_autenticacoes.sei_id = tab_clientes_pontos.sei_id AND tab_clientes_autenticacoes.cas_id = 1)
  	  GROUP BY
  	  	tab_servidores.ser_id
  	  ORDER BY
  	    tab_servidores.ser_nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servidores[$dados["id"]] = "{$dados["nome"]} [{$dados["pontos_total"]}]";
		}
		return $servidores;
	}
	
	function buscaInterfaces($servidor_id, $interface_tipo_id) {
		$query = "
  		SELECT
  			tab_servidores_interfaces.sei_id AS id,
  			tab_servidores_interfaces.sei_nome AS interface_nome,
  			COUNT(DISTINCT tab_clientes_pontos.clp_id) + COUNT(DISTINCT tab_clientes_autenticacoes.cau_id) AS pontos_total
  		FROM
  	    tab_servidores_interfaces
  	    LEFT OUTER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id AND tab_clientes_pontos.cps_id = 1)
  	    LEFT OUTER JOIN tab_clientes_autenticacoes ON (tab_clientes_autenticacoes.sei_id = tab_clientes_pontos.sei_id AND tab_clientes_autenticacoes.cas_id = 1)
  	  WHERE
  	  	tab_servidores_interfaces.ser_id = $servidor_id AND" . ($interface_tipo_id == 3 ? "
  	  	tab_servidores_interfaces.sit_id IN (3)" : "
  	  	tab_servidores_interfaces.sit_id IN (1, 6) AND
        ((tab_servidores_interfaces.sit_id = 1 AND tab_servidores_interfaces.sei_pai IS NULL) OR
	  		 (tab_servidores_interfaces.sit_id = 6 AND tab_servidores_interfaces.sei_pai IS NOT NULL))") . "
  		GROUP BY
  		  tab_servidores_interfaces.sei_id
  	  ORDER BY
  	    tab_servidores_interfaces.sei_nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["ordem"] = sprintf("% -15s", preg_replace("/[^a-zA-Z _]/", "", $dados["interface_nome"])) . sprintf("%05s", preg_replace("/[^0-9]/", "", $dados["interface_nome"]));
			$dados["nome"] = "{$dados["interface_nome"]} [{$dados["pontos_total"]}]";
			$interfaces[] = $dados;
		}
		return $this->ordena_multiarray($interfaces, array("ordem"));
	}
	
	##############
	### Ativar ###
	##############
	

	function ativar() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("servidores_interfaces.ativar.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=ativar&i={$_GET["i"]}", $this->conteudo);
				if ($this->permissoes[$this->dados_modulo["nome"]]["publicar"] && in_array($this->dados_item["tipo_id"], array(1, 6))) {
					$this->conteudo = str_replace('[publicar]', '<input type="checkbox" name="publicar" id="publicar" value="1"><label for="publicar"> Publicar alterações no servidor agora</label><br>', $this->conteudo);
				} else {
					$this->conteudo = str_replace('[publicar]', '', $this->conteudo);
				}
			} else {
				$query = "UPDATE tab_servidores_interfaces SET sia_id = 1 WHERE sei_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				$query = "UPDATE tab_servidores_interfaces SET sia_id = 1 WHERE sei_pai = {$_GET["i"]}";
				$this->bancodedados->query($query);
				if ($_POST["publicar"]) {
					$this->publicar();
				}
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
			}
		} else {
			$this->fechar();
		}
	}
	
	#################
	### Desativar ###
	#################
	

	function desativar() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("servidores_interfaces.desativar.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=desativar&i={$_GET["i"]}", $this->conteudo);
			} else {
				$query = "UPDATE tab_servidores_interfaces SET sia_id = 2 WHERE sei_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				$query = "UPDATE tab_servidores_interfaces SET sia_id = 2 WHERE sei_pai = {$_GET["i"]}";
				$this->bancodedados->query($query);
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, 'id=' . $this->dados_item["id"]);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
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
			if (($erros = $this->buscaErros($_GET["i"], $this->dados_item["tipo_id"] == 6)) || !$_POST) {
				$this->conteudo .= $this->carregaArquivo("servidores_interfaces.remover.inc.php");
				$this->conteudo = str_replace('[mensagem]', $erros ? "Foram encontratos os seguintes vínculos à esta interface:<br><br>$erros<br><br>Não é possível continuar com a remoção." : "Tem certeza que deseja continuar com a remoção desta interface?", $this->conteudo);
				// Botões
				if (!$erros)
					$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}", $this->conteudo);
			} else {
				// Remove Vínculo c/ Blocos
				$query = "UPDATE tab_condominios_blocos SET sei_id = NULL WHERE sei_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// Remove Vínculo c/ Pontos Cancelados
				$query = "UPDATE tab_clientes_pontos SET sei_id = NULL WHERE sei_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				$query = "UPDATE tab_clientes_autenticacoes SET sei_id = NULL WHERE sei_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// Remove Serviços
				$query = "DELETE FROM tab_servidores_servicos WHERE sei_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// Remove Carps
				$query = "DELETE FROM tab_servidores_interfaces WHERE sei_pai = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// Remove Interface
				$query = "DELETE FROM tab_servidores_interfaces WHERE sei_id = {$_GET["i"]}";
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
	
	function buscaErros($interface_id, $carp = false) {
		$erros = array();
		$query = "
      SELECT
        CONCAT(red_endereco_ip, '/', red_mascara) AS rede
      FROM
        tab_redes
      WHERE
        sei_id = $interface_id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$erros[] = "&raquo;&nbsp; Rede <b>{$dados["rede"]}</b>";
		}
		$query = "
      SELECT
        tab_servidores.ser_id AS id,
        tab_servidores.ser_nome AS nome
      FROM
        tab_servidores
        INNER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
      WHERE
        tab_servidores_interfaces." . ($carp ? "sei_pai" : "sei_id") . " = $interface_id
      ORDER BY
        nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$erros[] = "&raquo;&nbsp; Servidor <b><a href=\"javascript:verServidor({$dados["id"]})\" class=\"txtAzulEscuro11\" style=\"text-decoration:underline\">{$dados["nome"]}</a></b>";
		}
		$query = "
      SELECT
        tab_servidores_links.sel_nome AS nome
      FROM
        tab_servidores_links
        INNER JOIN tab_servidores_interfaces ON (tab_servidores_links.sei_id = tab_servidores_interfaces.sei_id)
      WHERE
        tab_servidores_interfaces." . ($carp ? "sei_pai" : "sei_id") . " = $interface_id
      ORDER BY
        nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$erros[] = "&raquo;&nbsp; Link <b>{$dados["nome"]}</b>";
		}
		$query = "
      SELECT
        tab_condominios.con_nome AS condominio,
        tab_condominios_blocos.cob_nome AS bloco,
        COUNT(tab_clientes_pontos.clp_id) + COUNT(tab_clientes_autenticacoes.cau_id) as quantidade
      FROM
        tab_clientes
        INNER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
        INNER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
        LEFT OUTER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id AND tab_clientes_pontos.cps_id != 3)
        LEFT OUTER JOIN tab_clientes_autenticacoes ON (tab_assinaturas.ass_id = tab_clientes_autenticacoes.ass_id AND tab_clientes_autenticacoes.cas_id != 3)
      WHERE
        tab_servidores_interfaces." . ($carp ? "sei_pai" : "sei_id") . " = $interface_id AND
        tab_planos.pls_id IN (1, 2)
      GROUP BY
        tab_condominios_blocos.cob_id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$erros[] = "&raquo;&nbsp; <b>{$dados["quantidade"]}</b> ponto(s) no condomínio <b>{$dados["condominio"]} &raquo; Bloco {$dados["bloco"]}</b>";
		}
		$query = "
      SELECT
        tab_clientes.cli_id AS cliente_id,
        tab_clientes.cli_nome AS cliente_nome,
        COUNT(tab_clientes_pontos.clp_id) + COUNT(tab_clientes_autenticacoes.cau_id) as quantidade
      FROM
        tab_clientes
        INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        LEFT OUTER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id AND tab_clientes_pontos.cps_id != 3)
        LEFT OUTER JOIN tab_clientes_autenticacoes ON (tab_assinaturas.ass_id = tab_clientes_autenticacoes.ass_id AND tab_clientes_autenticacoes.cas_id != 3)
        INNER JOIN tab_servidores_interfaces ON (tab_servidores_interfaces.sei_id = IF(tab_clientes_pontos.sei_id IS NOT NULL, tab_clientes_pontos.sei_id, tab_clientes_autenticacoes.sei_id))
      WHERE
        tab_servidores_interfaces." . ($carp ? "sei_pai" : "sei_id") . " = $interface_id AND
        tab_planos.pls_id IN (3, 5, 36)
      GROUP BY
        tab_clientes.cli_id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$erros[] = "&raquo;&nbsp; <b>{$dados["quantidade"]}</b> ponto(s) no cliente <b>[{$dados["cliente_id"]}] {$dados["cliente_nome"]}</b>";
		}
		return implode("<br>", $erros);
	}
	
	################
	### Publicar ###
	################

	function publicar() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item["servidor_situacao_id"] != 1 && !$_POST["confirmar"] && !$_GET["sucesso"] && !$_GET["erro"]) {
			$this->conteudo .= $this->carregaArquivo("servidores_interfaces.publicar.inc.php");
			$acoes = '<td bgcolor="#505050"><strong>&nbsp;Publicar&nbsp;</strong></td>';
			$this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
			$mensagem[] = "Pode não ser possível publicar este servidor no momento!";
			$mensagem[] = "Deseja continuar com a publicação assim mesmo?";
			$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onclick="window.close()">';
			$this->conteudo = str_replace('[mensagem]', implode("<br><br>", $mensagem), $this->conteudo);
			$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&retorno={$_GET["retorno"]}", $this->conteudo);
		} elseif (!$_POST) {
			if ($_GET["sucesso"]) {
				$this->conteudo .= "<script type=\"text/javascript\">alert('Ponto publicado com sucesso!')</script>";
				$this->conteudo .= "<script type=\"text/javascript\">window.location = './?m={$this->dados_modulo["nome"]}&a={$_GET["retorno"]}&i={$_GET["i"]}'</script>";
			} else {
				$this->conteudo .= $this->carregaArquivo("servidores_interfaces.publicar.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				if ($_GET["erro"]) {
					$mensagem = "{$_GET["erro"]}<br><br>Clique em Confirmar para tentar novamente.";
				} else {
					$mensagem = "Você deseja realmente <b>publicar</b> este servidor?";
				}
				$this->conteudo = str_replace('[mensagem]', $mensagem, $this->conteudo);
				$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&retorno={$_GET["retorno"]}", $this->conteudo);
			}
		} else {
			$erro = $this->publicarServicos($this->dados_item["servidor_id"]);
			if ($erro) {
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&erro=$erro&retorno={$_GET["retorno"]}");
			} else {
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], "publicar", $_GET["i"]);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&retorno={$_GET["retorno"]}&sucesso=1");
			}
		}
	}
	
	function publicarServicos($servidor_id) {
		$servidores = controle::instanciaObjeto("servidores", $this->bancodedados, $this->permissoes, false);
		return $servidores->publicarServicos($servidor_id);
	}
	
	###########
	### RDS ###
	###########
	

	function buscaServidoresInterfaces($servidor_id) {
		$query = "
  		SELECT
  			tab_servidores_interfaces.sei_id AS id,
  			tab_servidores_interfaces.sei_nome AS nome,
  			tab_servidores_interfaces.ser_id AS servidor_id,
  			CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip) AS ip,
  			tab_servidores_interfaces.sei_mascara_rede AS mascara,
  			tab_servidores_interfaces.sei_reserva_ips AS reserva_ips,
  			tab_servidores_interfaces_tipos.sit_id AS tipo_id,
  			tab_servidores_interfaces_tipos.sit_nome AS tipo_nome,
  			tab_servidores_interfaces_status.sia_nome AS status
  		FROM
  			tab_servidores_interfaces
  			INNER JOIN tab_servidores_interfaces_tipos ON (tab_servidores_interfaces_tipos.sit_id = tab_servidores_interfaces.sit_id)
  			INNER JOIN tab_servidores_interfaces_status ON (tab_servidores_interfaces_status.sia_id = tab_servidores_interfaces.sia_id)
  		WHERE
  			tab_servidores_interfaces.ser_id = $servidor_id AND
  			tab_servidores_interfaces.sei_pai IS NULL
      GROUP BY
        tab_servidores_interfaces.sei_id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["ordem"] = sprintf("%05s", 99999 - ($dados["tipo_id"] == 6 ? 1 : $dados["tipo_id"])) . sprintf("% -15s", preg_replace("/[^a-zA-Z _]/", "", $dados["nome"])) . sprintf("%05s", preg_replace("/[^0-9]/", "", $dados["nome"]));
			if ($this->permissoes["servidores_interfaces"]["ver_dc"])
				$dados["link"] = "javascript:verServidorInterface({$dados["id"]})";
			if ($this->permissoes["servidores_interfaces"]["ver_dc"])
				$dados["links"][] = "<a href=\"javascript:verServidorInterface({$dados["id"]})\" class=\"txtAzulEscuro11\">DC</a>";
			if ($this->permissoes["servidores_interfaces"]["ver_ds"] && ($dados["tipo_id"] == 1 || $dados["tipo_id"] == 6))
				$dados["links"][] = "<a href=\"javascript:verServidorInterface({$dados["id"]}, 'ds')\" class=\"txtAzulEscuro11\">DS</a>";
			if ($this->permissoes["servidores_interfaces"]["ver_co"] && ($dados["tipo_id"] == 1 || $dados["tipo_id"] == 6))
				$dados["links"][] = "<a href=\"javascript:verServidorInterface({$dados["id"]}, 'co')\" class=\"txtAzulEscuro11\">CO</a>";
			if ($dados["tipo_id"] != 1 && $dados["tipo_id"] != 6) {
				$dados["pontos_ativos"] = "-";
			} else {
				$dados["pontos_ativos"] = $this->buscaPontosAtivos($dados["id"], $dados["tipo_id"] == 6);
				$dados["disponiveis"] = $this->buscaPontosDisponiveis($dados["id"], $dados["tipo_id"]);
				if ($dados["disponiveis"]) {
					$dados["pontos_ativos"] .= " / {$dados["disponiveis"]}";
				}
				if ($dados["reserva_ips"]) {
					$dados["pontos_ativos"] .= " [{$dados["reserva_ips"]}]";
				}
			}
			if ($dados["tipo_id"] == 6) {
				$carps = $this->buscaInterfacesCarp($dados["id"]);
				if ($carps) {
					foreach ($carps as $carp) {
						$dados["ip"][] = $carp["endereco_ip"] ? "<span style=\"color:#232323\">{$carp["nome"]}</span>: {$carp["endereco_ip"]} ({$carp["mascara_rede"]})" : $carp["nome"];
					}
					$dados["ip"] = implode("<br>", $dados["ip"]);
				} else {
					$dados["ip"] = "-";
				}
				$dados["mascara"] = "-";
			} elseif (($dados["tipo_id"] == 1) && ($redes = $this->buscaRedes($dados["id"]))) {
				$dados["ip"] = array();
				foreach ($redes as $rede) {
					$dados["ip"][] = "{$rede["endereco_ip"]} ({$rede["mascara_rede"]})";
				}
				$dados["ip"] = implode("<br>", $dados["ip"]);
			} else {
				$dados["ip"] = $dados["ip"] . ($dados["mascara"] ? " ({$dados["mascara"]})" : "");
			}
			$dados["links"] = implode(" ", $dados["links"]);
			$dados["mascara"] = $this->formataIP($dados["mascara"]);
			$interfaces[] = $dados;
		}
		return $interfaces;
	}
	
	function buscaPontosDisponiveis($interface_id, $tipo_id) {
		$query = "
  	  SELECT
  	    tab_redes.red_mascara AS mascara,
  	    tab_redes.red_reserva_ips AS reserva_ips
  	  FROM
  	    tab_servidores_interfaces
  	    INNER JOIN tab_redes ON (tab_servidores_interfaces.sei_id = tab_redes.sei_id)
  	  WHERE
  	    tab_servidores_interfaces." . ($tipo_id == 6 ? "sei_pai" : "sei_id") . " = $interface_id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$pontos += pow(2, 32 - $dados["mascara"]) - 3 - $dados["reserva_ips"];
		}
		return $pontos;
	}
	
	function buscaPontosAtivos($interface_id, $carp = false) {
		$query = "
	    SELECT
	      (SELECT COUNT(tab_clientes_pontos.clp_id) FROM tab_clientes_pontos WHERE tab_clientes_pontos.sei_id = tab_servidores_interfaces.sei_id AND tab_clientes_pontos.cps_id = 1) +
	      (SELECT COUNT(tab_clientes_autenticacoes.cau_id) FROM tab_clientes_autenticacoes WHERE tab_clientes_autenticacoes.sei_id = tab_servidores_interfaces.sei_id AND tab_clientes_autenticacoes.cas_id = 1) AS pontos
	    FROM
  			tab_servidores_interfaces
	    WHERE
  			tab_servidores_interfaces." . ($carp ? "sei_pai" : "sei_id") . " = $interface_id
  	  GROUP BY
  	    tab_servidores_interfaces." . ($carp ? "sei_pai" : "sei_id");
		$query = $this->bancodedados->query($query);
		return $this->bancodedados->resultado($query, 0, "pontos");
	}
	
	function ordena_multiarray($array, $campos) {
		if (is_array($array) && sizeof($array)) {
			$tamanho_chave = 0;
			reset($array);
			while (($array_ordenacao_atual = current($array)) != false) {
				$chave = array();
				for ($i = 0; $i < sizeof($campos); $i++) {
					$chave[] = $array_ordenacao_atual[$campos[$i]];
				}
				$chave = implode(",", $chave);
				$tamanho_chave = strlen($chave) > $tamanho_chave ? strlen($chave) : $tamanho_chave;
				unset($chave);
				next($array);
			}
			reset($array);
			$array_ordenacao = array();
			while (($array_ordenacao_atual = current($array)) != false) {
				$chave = array();
				for ($i = 0; $i < sizeof($campos); $i++) {
					$chave[] = $array_ordenacao_atual[$campos[$i]];
				}
				$chave = implode(",", $chave);
				if (!$array_ordenacao[$chave]) {
					$array_ordenacao[sprintf("% -{$tamanho_chave}s", $chave) . "_" . key($array)] = key($array);
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
	
	function geraServidoresInterfacesListagem($dados) {
		$dados = $this->ordena_multiarray($dados, array("ordem"));
		for ($i = 1; $i <= count($dados); $i++) {
			$dados[$i - 1]["id"] = $i;
		}
		$cabecalho = array("id" => "#", "nome" => "Nome", "tipo_nome" => "Tipo", "ip" => "IP (Máscara)", "pontos_ativos" => "Pontos Ativos", "status" => "Status", "links" => "Links");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, array("pontos_ativos" => "formataNumero"), array(13), null, array("right"));
		return $listagem->conteudo;
	}
	
	function buscaServidorInterfaceNome($servidor_id, $interface_nome) {
		$interface_nome = trim($interface_nome);
		$query = "
	    SELECT
	      sei_pai AS id
	    FROM
	      tab_servidores_interfaces
	    WHERE
	      ser_id = $servidor_id AND
	      sei_nome LIKE '$interface_nome'
	    LIMIT
	      0, 1";
		$query = $this->bancodedados->query($query);
		return $this->bancodedados->resultado($query, 0, "id");
	}
	
	function buscaServidorInterfaceIP($servidor_id, $prefixo_ip, $endereco_ip) {
		$query = "
	    SELECT
	      sei_pai AS id
	    FROM
	      tab_servidores_interfaces
	    WHERE
	      ser_id = $servidor_id AND
	      sei_prefixo_ip = '$prefixo_ip' AND
	      sei_endereco_ip = $endereco_ip
	    LIMIT
	      0, 1";
		$query = $this->bancodedados->query($query);
		return $this->bancodedados->resultado($query, 0, "id");
	}

}

?>
