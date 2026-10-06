<?

class descontos_concessoes {

	var $bancodedados;
	var $permissoes;
	var $acao;
	var $conteudo;
	var $dados_item;
	var $dados_modulo;
	var $dados_acao;

	var $valor_tipos = array(
		"1" => "Dinheiro",
		"2" => "Percentual"
	);

	function __construct($bancodedados, $permissoes, $executar = true) {
		
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		
		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
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

		$query = $this->bancodedados->query("
			SELECT 
				mod_id AS id, 
				mod_nome AS nome, 
				mod_descricao AS descricao 
			FROM 
				tab_modulos 
			WHERE 
				mod_nome = '$nome'");
		
		$this->dados_modulo = $this->bancodedados->resultadoArray($query);
		
	}

	function buscaDadosAcao($nome) {
		
		$query = $this->bancodedados->query("
			SELECT 
				aco_id AS id, 
				aco_nome AS nome, 
				aco_descricao AS descricao, 
				act_id AS tipo 
			FROM 
				tab_acoes 
			WHERE 
				aco_nome = '$nome' AND 
				mod_id = '{$this->dados_modulo["id"]}'");
				
		if (($this->dados_acao = $this->bancodedados->resultadoArray($query)) != false) {
			$this->acao = $this->dados_acao["nome"];
		}
		
	}

	function buscaDescricaoAcao($acao) {
		
		$query = $this->bancodedados->query("
			SELECT 
				aco_descricao
			FROM 
				tab_acoes 
			WHERE 
				aco_nome = '$acao'");
		
		return $this->bancodedados->resultado($query);
		
	}

	function buscaDadosCondominio($id) {
		
		$query = $this->bancodedados->query("
			SELECT 
				con_id AS id, 
				con_nome AS nome, 
				cos_id AS status 
			FROM 
				tab_condominios 
			WHERE 
				con_id = $id");
		
		return $this->bancodedados->resultadoArray($query);
		
	}

	function buscaDadosCliente($id) {

		$query = $this->bancodedados->query("
			SELECT 
				cli_id AS id, 
				cli_nome AS nome 
			FROM 
				tab_clientes 
			WHERE 
				cli_id = $id");

		return $this->bancodedados->resultadoArray($query);

	}

	function buscaDadosPlano($id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_planos.pla_id AS id,
				tab_planos.pla_nome AS nome,
				IF(tab_planos.plt_id = 1, 'pla', 'pld') AS tipo,
				tab_planos.pli_id AS sistema_id,
				tab_planos_servicos.pls_id AS servico_id,
				tab_planos_servicos.pls_nome AS servico_nome
			FROM
				tab_planos
				INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
			WHERE
				pla_id = $id");
		
		return $this->bancodedados->resultadoArray($query);
		
	}

	function buscaDadosAssinatura($id) {
		
		$query = $this->bancodedados->query("
			SELECT
				ass_id AS id,
				cli_id AS cliente,
				pla_id AS plano,
				ass_instalacao AS instalacao,
				ass_mensalidade AS mensalidade,
				ass_pai AS assinatura,
				ast_id AS status
			FROM
				tab_assinaturas
			WHERE
				ass_id = $id");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if (($dados_cliente = $this->buscaDadosCliente($dados["cliente"])) != false) {
				$dados["cliente_id"] = $dados_cliente["id"];
				$dados["cliente_nome"] = $dados_cliente["nome"];
			}
			
			if (($dados_plano = $this->buscaDadosPlano($dados["plano"])) != false) {
				$dados["nome"] = "{$dados_plano["servico_nome"]} &raquo; {$dados_plano["nome"]}";
				$dados["servico"] = $dados_plano["servico_id"];
				$dados["tipo"] = $dados_plano["tipo"];
			}
			
			$dados["contrato"] = $this->buscaContratoAssinatura($dados["id"]);
			
			return $dados;
			
		}
	
	}

	function buscaDadosDesconto($id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_descontos.des_id AS id,
				tab_descontos.des_nome AS nome,
				tab_descontos.des_parametros AS parametros,
				tab_descontos.des_fidelidade AS fidelidade,
				tab_descontos_tipos.det_id AS tipo_id,
				tab_descontos_tipos.det_nome AS tipo_nome,
				tab_planos_servicos.pls_id AS servico_id,
				tab_planos_servicos.pls_nome AS servico_nome
			FROM
				tab_descontos
				INNER JOIN tab_descontos_tipos ON (tab_descontos.det_id = tab_descontos_tipos.det_id)
				LEFT OUTER JOIN tab_planos_servicos ON (tab_descontos.pls_id = tab_planos_servicos.pls_id)
			WHERE
				des_id = $id");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		
			$parametros = unserialize($dados["parametros"]);
			unset($parametros["fidelidade"]);
			
			$dados = funcoes::mescla_arrays($dados, $parametros);
			
			$dados["instalacao_valor"] = $this->formataDesconto($dados["instalacao"]);
			$dados["mensalidade_valor"] = $this->formataDesconto($dados["mensalidade"]);
			$dados["instalacao_tipo"] = ($dados["instalacao"] > 1 || $dados["instalacao"] == 0) ? 1 : 2;
			$dados["mensalidade_tipo"] = ($dados["mensalidade"] > 1 || $dados["mensalidade"] == 0) ? 1 : 2;
			
			return $dados;
		
		}
	
	}

	function buscaDadosItem($id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_descontos_concessoes.dec_id AS id,
				tab_descontos_concessoes.des_id AS desconto,
				tab_descontos_concessoes.ass_id AS assinatura,
				tab_descontos_concessoes.con_id AS condominio,
				tab_descontos_concessoes.pla_id AS plano,
				tab_descontos_concessoes.dec_instalacao AS instalacao,
				tab_descontos_concessoes.dec_mensalidade AS mensalidade,
				tab_descontos_concessoes.dec_validade AS validade,
				DATE_FORMAT(tab_descontos_concessoes.dec_data_inicio, '%d/%m/%Y') AS data_inicio,
				DATE_FORMAT(tab_descontos_concessoes.dec_data_termino, '%d/%m/%Y') AS data_termino,
				tab_descontos_concessoes_status.dcs_id AS status_id,
				tab_descontos_concessoes_status.dcs_nome AS status_nome,
				tab_descontos_tipos.det_id AS tipo_id,
				tab_descontos_tipos.det_nome AS tipo_nome
			FROM
				tab_descontos_concessoes
				INNER JOIN tab_descontos_concessoes_status ON (tab_descontos_concessoes.dcs_id = tab_descontos_concessoes_status.dcs_id)
				INNER JOIN tab_descontos_tipos ON (tab_descontos_tipos.det_id = tab_descontos_concessoes.det_id)
				LEFT JOIN tab_assinaturas ON (tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id)
				LEFT JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
			WHERE
				tab_descontos_concessoes.dec_id = $id".($_SESSION["origem"] ? " AND
				(tab_descontos_concessoes.ass_id IS NULL OR tab_clientes.clo_id = {$_SESSION["origem"]})" : ""));

		if (($this->dados_item = $this->bancodedados->resultadoArray($query)) != false) {

			$validades = $this->buscaValidades();

			$this->dados_item["instalacao_valor"] = $this->formataDesconto($this->dados_item["instalacao"]);
			$this->dados_item["mensalidade_valor"] = $this->formataDesconto($this->dados_item["mensalidade"]);
			$this->dados_item["instalacao_tipo"] = $this->dados_item["instalacao"] > 1 || $this->dados_item["instalacao"] == 0 ? 1 : 2;
			$this->dados_item["mensalidade_tipo"] = $this->dados_item["mensalidade"] > 1 || $this->dados_item["mensalidade"] == 0 ? 1 : 2;
			$this->dados_item["validade_nome"] = !$this->dados_item["validade"] ? "Indeterminada" : $validades[$this->dados_item["validade"]];
			$this->dados_item["desconto"] = $this->buscaDadosDesconto($this->dados_item["desconto"]);

			if ($this->dados_item["assinatura"]) {
				$this->dados_item["assinatura"] = $this->buscaDadosAssinatura($this->dados_item["assinatura"]);
				$this->dados_item["tipo"] = "ass";
			} elseif ($this->dados_item["condominio"]) {
				$this->dados_item["condominio"] = $this->buscaDadosCondominio($this->dados_item["condominio"]);
				$this->dados_item["tipo"] = "con";
			} elseif ($this->dados_item["plano"]) {
				$this->dados_item["plano"] = $this->buscaDadosPlano($this->dados_item["plano"]);
				$this->dados_item["tipo"] = "pla";
			}
		
		}

	}

	function buscaDataCancelamento($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(tab_contratos.cor_data_cancelamento, '%Y%m%d')
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
				INNER JOIN tab_contratos ON (tab_assinaturas.cli_id = tab_contratos.cli_id AND tab_planos.pls_id = tab_contratos.pls_id)
			WHERE
				tab_assinaturas.ass_id = $assinatura_id
			ORDER BY
				tab_contratos.cor_data_cancelamento DESC");
		
		return $this->bancodedados->resultado($query);
		
	}

	function buscaSistemaAssinatura($assinatura_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				pli_id
			FROM
				tab_assinaturas
			WHERE
				ass_id = $assinatura_id");
		
		return $this->bancodedados->resultado($query);
		
	}

	function buscaDescontosAssinatura($assinatura_id) {
		
		$descontos = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				des_id AS id
			FROM
				tab_descontos_concessoes
			WHERE
				ass_id = $assinatura_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$descontos[] = $dados["id"];
		}
		
		return $descontos;
	
	}

	function buscaOrigemAssinatura($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes.clo_id
			FROM
				tab_clientes
				INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
			WHERE
				tab_assinaturas.ass_id = $assinatura_id");
		
		return $this->bancodedados->resultado($query);
	
	}

	function buscaTotalDescontosCondominio($assinatura_id) {
		
		$descontos = array();
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				dec_pai AS id
			FROM
				tab_descontos_concessoes
			WHERE
				ass_id = $assinatura_id AND
				dec_validade IS NULL AND
				dec_mensalidade > 1 AND
				dec_pai IS NOT NULL");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$descontos[] = $dados["id"];
		}
		
		$descontos = implode(", ", $descontos);
		
		if ($descontos) {
			
			$query = $this->bancodedados->query("
				SELECT
					SUM(dec_mensalidade)
				FROM
					tab_descontos_concessoes
				WHERE
					dec_id IN ({$descontos}) AND
					dec_validade IS NULL AND
					con_id IS NOT NULL AND
					dec_mensalidade > 1");
			
			return $this->bancodedados->resultado($query);
			
		}
	
	}

	function buscaDescontoMaiorValidade($assinatura_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				dec_id AS id,
				des_parametros AS parametros
			FROM
				tab_descontos_concessoes
				INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
			WHERE
				ass_id = $assinatura_id AND
				dec_validade IS NOT NULL AND
				dec_mensalidade > 1 AND
				dec_pai IS NULL
			ORDER BY
				dec_validade DESC");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["parametros"] = unserialize($dados["parametros"]);
			
			if ($dados["parametros"]["mensalidade_fixa"]) {
				return $dados["id"];
			}
			
		}
	
	}

	function buscaContratoAssinatura($assinatura) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_contratos.cor_id AS id,
				tab_contratos.cor_codigo AS codigo,
				cor_parametros AS parametros,
				crs_id AS status_id
			FROM
				rel_ass_cor_estacontido
				INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id)
			WHERE
				rel_ass_cor_estacontido.ass_id = $assinatura AND
				tab_contratos.cor_pai IS NULL");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		} else {
			
			$query = $this->bancodedados->query("
				SELECT
					tab_contratos.cor_id AS id,
					tab_contratos.cor_codigo AS codigo,
					cor_parametros AS parametros,
					crs_id AS status_id
				FROM
					rel_ass_cor_estacontido
					INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id)
				WHERE
					rel_ass_cor_estacontido.ass_id = $assinatura");
			
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$dados["parametros"] = unserialize($dados["parametros"]);
				return $dados;
			}
		
		}
	
	}

	function buscaDescontos($destino_id, $servico_id, $sistema_id, $tipo) {
		
		$descontos = array();
		
		if (in_array($tipo, array("plano"))) {
			$desconto_tipo = "pla";
		} elseif (in_array($tipo, array("condominio"))) {
			$desconto_tipo = "con";
		} elseif (in_array($tipo, array("plano_principal", "plano_adicional"))) {
			$desconto_tipo = "ass";
		}
		
		if ($desconto_tipo == "ass") {
			$data_cancelamento = $this->buscaDataCancelamento($destino_id);
		} else {
			$data_cancelamento = "";
		}
		
		$query = $this->bancodedados->query("
			SELECT
				tab_descontos.des_id AS id,
				tab_descontos.des_nome AS nome,
				tab_descontos.des_parametros AS parametros
			FROM
				tab_descontos
				LEFT OUTER JOIN tab_descontos_concessoes ON (tab_descontos_concessoes.des_id = tab_descontos.des_id AND tab_descontos_concessoes.dcs_id <= 5 AND tab_descontos_concessoes.{$desconto_tipo}_id = $destino_id)
			WHERE".($sistema_id ? "
				(pli_id IS NULL OR pli_id LIKE '$sistema_id') AND" : "")."
				pls_id = $servico_id AND
				dec_id IS NULL AND
				dea_id = 1
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$parametros = unserialize($dados["parametros"]);
			if ($desconto_tipo == "ass" && $parametros["carencia"]) {
				$data_carencia_minima = date('Ymd', strtotime("{$parametros["carencia"]} days ago"));
			}
			if ($parametros["aplica_$tipo"] && 
					($tipo == "condominio" || $tipo == "plano" || !$data_carencia_minima || $data_carencia_minima >= $data_cancelamento) &&
					($tipo != "plano" || !$parametros["planos"] || in_array($destino_id, $parametros["planos"]))) {
				$descontos[$dados["id"]] = $dados["nome"];
			}
		}
		
		return $descontos;
		
	}

	###################
	### Busca Itens ###
	###################

	function buscaStatus() {
		
		$status = array();
		
		$query = $this->bancodedados->query("
			SELECT
				dcs_id AS id,
				dcs_nome AS nome
			FROM
				tab_descontos_concessoes_status
			WHERE
				dcs_id != 1");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$status[$dados["id"]] = $dados["nome"];
		}
		
		return $status;
	
	}

	function buscaDescontosServicos() {
		
		$descontos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_descontos.des_id AS id,
				IF(tab_descontos.pls_id IS NULL, tab_descontos.des_nome, CONCAT(tab_planos_servicos.pls_nome, ' » ', tab_descontos.des_nome)) AS nome
			FROM
				tab_descontos
				LEFT OUTER JOIN tab_planos_servicos ON (tab_descontos.pls_id = tab_planos_servicos.pls_id)
			ORDER BY
				tab_planos_servicos.pls_id,
				tab_descontos.des_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$descontos[$dados["id"]] = $dados["nome"];
		}
		
		return $descontos;
	
	}

	function buscaDescontosTipos() {
		
		$tipos = array();
		
		$query = $this->bancodedados->query("
			SELECT 
				det_id AS id, 
				det_nome AS nome
			FROM 
				tab_descontos_tipos 
			ORDER BY 
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$tipos[$dados["id"]] = $dados["nome"];
		}
		
		return $tipos;
		
	}

	function buscaCondominiosResponsaveis() {
		
		$responsaveis = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_usuarios.usu_id AS id,
				tab_usuarios.usu_nome AS nome
			FROM
				tab_condominios
				INNER JOIN tab_usuarios ON (tab_condominios.usu_id = tab_usuarios.usu_id)
				INNER JOIN tab_descontos_concessoes ON (tab_condominios.con_id = tab_descontos_concessoes.con_id)
			WHERE
				tab_condominios.cos_id IS NOT NULL
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$responsaveis[$dados["id"]] = $dados["nome"];
		}
		
		return $responsaveis;
	
	}

	function buscaClientesResponsaveis() {
		
		$responsaveis = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_usuarios.usu_id AS id,
				tab_usuarios.usu_nome AS nome
			FROM
				tab_clientes
				LEFT OUTER JOIN tab_usuarios ON (tab_clientes.usu_id = tab_usuarios.usu_id)
				INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
				INNER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
			WHERE
				tab_clientes.cls_id IS NOT NULL
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$responsaveis[$dados["id"]] = $dados["nome"];
		}
		
		return $responsaveis;
		
	}

	function buscaCondominios() {
		
		$condominios = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_condominios.con_id AS id,
				CONCAT(tab_condominios.con_nome, ' (', tab_cidades.cid_nome, ' / ', tab_bairros.bai_nome, ')') AS nome
			FROM
				tab_condominios
				INNER JOIN tab_logradouros ON (tab_condominios.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_condominios.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_condominios.cid_id = tab_cidades.cid_id)
			WHERE
				cos_id IS NOT NULL
			ORDER BY
				nome");
				
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$condominios[$dados["id"]] = $dados["nome"];
		}
		
		return $condominios;
		
	}

	function buscaValidades() {
		
		$validades = array();
		
		for ($i = 1; $i <= 24; $i++) {
			$validades[$i] = $i.(($i > 1) ? " Meses" : " Mês");
		}
		
		return $validades;
		
	}

	#############
	### Geral ###
	#############

	function geraSelectValorTipos($tipos, $selecionado = "1") {
		
		$select = "";
		
		do {
			$tipo = current($tipos);
			$complemento = key($tipos) == $selecionado ? " selected" : "";
			$select .= '<option value="'.key($tipos).'" '.$complemento.'>'.$tipo.'</option>';
		} while (next($tipos));
		
		return $select;
		
	}

	function geraSelectValidades($validades, $selecionado = "") {
		
		$select = '<option value="">Indeterminada</option>';
		
		do {
			$validade = current($validades);
			$complemento = key($validades) == $selecionado ? " selected" : "";
			$select .= '<option value="'.key($validades).'" '.$complemento.'>'.$validade.'</option>';
		} while (next($validades));
		
		return $select;
		
	}

	function geraSelect($itens, $selecionado = null, $item_nulo = false) {
		
		$select = $item_nulo ? "<option></option>" : "";
		
		foreach ($itens as $valor => $item) {
			$select .= "<option value=\"$valor\"".(isset($selecionado) && $valor == $selecionado ? " selected" : "").">$item</option>";
		}
		
		return $select;
		
	}

	function geraPaginacao($filtro, $total, $pagina) {
		
		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
	
	}

	function descontoAutomatico($desconto_atual,$desconto_padrao) {
		
		if (strpos($desconto_padrao, "%") && strpos($desconto_atual, "%")) { // Porcentagem
			$desconto_atual = preg_replace("/[^0-9]/", "", $desconto_atual);
			$desconto_padrao = preg_replace("/[^0-9]/", "", $desconto_padrao);
			if ($desconto_atual <= $desconto_padrao) {
				return true;
			}
		} elseif (strpos($desconto_padrao, "\$") && strpos($desconto_atual, "\$")) { // Dinheiro
			$desconto_atual = preg_replace("/[^0-9]/", "", $desconto_atual);
			$desconto_padrao = preg_replace("/[^0-9]/", "", $desconto_padrao);
			if ($desconto_atual <= $desconto_padrao) {
				return true;
			}
		} elseif ($desconto_padrao == "" && $desconto_atual == "") {
			return true;
		}
		
		return false;
		
	}

	function corrigeDesconto($desconto, $limite = "-1") {
		
		if ($desconto) {
			if (strpos($desconto, "%")) {
				// Porcentagem
				$desconto = preg_replace("/[^0-9]/", "", $desconto);
				return $desconto / 100;
			} elseif (strpos($desconto, "$")) {
				// Dinheiro
				$desconto = preg_replace("/[^0-9]/", "", $desconto);
				if ($limite != "-1" && $desconto > $limite) $desconto = $limite;
				return intval($desconto, 10);
			}
		} else {
			return "NULL";
		}
		
	}

	function validadeAutomatica($validade_atual, $validade_padrao) {
		
		if (!$validade_padrao) {
			return true;
		} elseif ($validade_atual) {
			$validade_atual = explode("/",$validade_atual);
			$validade_atual = $validade_atual[2].$validade_atual[1].$validade_atual[0];
			$validade_padrao = explode("/",$validade_padrao);
			$validade_padrao = $validade_padrao[2].$validade_padrao[1].$validade_padrao[0];
			if ($validade_atual <= $validade_padrao) {
				return true;
			}
		}
		
		return false;
		
	}

	function corrigeValidade($validade) {
		
		if ($validade) {
			$validade = explode("/", $validade);
			return "'{$validade[2]}-{$validade[1]}-{$validade[0]}'";
		} else {
			return "NULL";
		}
	
	}

	function descontoExistente($desconto_concessao_id, $id, $tipo) {
		
		if ($desconto_concessao_id && $id && $tipo) {
			
			$query = $this->bancodedados->query("
				SELECT 
					{$tipo}_id
				FROM 
					tab_descontos_concessoes 
				WHERE 
					des_id = $desconto_concessao_id");
			
			return $this->bancodedados->resultado($query) == $id;
			
		}
		
		return false;
	
	}

	function formataDesconto($valor) {
		
		if ($valor <= 1 && $valor != 0) {
			return ($valor * 100)."%";
		} else {
			return $this->formataDinheiro($valor, 'R$');
		}
	
	}

	function formataDinheiro($valor, $moeda = 'R$') {
		
		$valor = number_format(preg_replace("/[^0-9]/", "", (int) $valor) / 100, 2, ",", ".");
		
		return $moeda ? "$moeda $valor" : $valor;
		
	}

	function calculaValidade($meses) {
		
		if ($meses) {
			return date("d/m/Y", strtotime("+{$meses} months"));
		}
		
	}

	function assinaturaPossuiContrato($assinatura) {

		$query = $this->bancodedados->query("
			SELECT 
				COUNT(cor_id)
			FROM 
				rel_ass_cor_estacontido 
			WHERE 
				ass_id = '$assinatura'");

		return $this->bancodedados->resultado($query) > 0;

	}

	function possuiContrato() {
		
		return $this->dados_item["assinatura"]["contrato"] && !in_array($this->dados_item["assinatura"]["contrato"]["status_id"], array(11, 12, 13, 14));
		
	}

	function restauraDescontosConcessoes($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				dec_id AS id,
				des_parametros AS parametros
			FROM
				tab_descontos_concessoes
				INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
			WHERE
				ass_id = $assinatura_id AND
				dec_validade IS NOT NULL AND
				dec_pai IS NULL
			ORDER BY
				dec_validade DESC");
				
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["parametros"] = unserialize($dados["parametros"]);
			
			if ($dados["parametros"]["mensalidade_fixa"]) {
				
				$this->bancodedados->query("
					UPDATE 
						tab_descontos_concessoes 
					SET 
						dec_mensalidade = {$dados["parametros"]["mensalidade"]} 
					WHERE 
						dec_id = {$dados["id"]}");
						
			}
			
		}
	
	}

	function recalculaDescontosConcessoes($assinatura_id) {
		
		$total_descontos = $this->buscaTotalDescontosCondominio($assinatura_id);
		if ($total_descontos) {
			$desconto_maior_validade = $this->buscaDescontoMaiorValidade($assinatura_id);
			if ($desconto_maior_validade) {
				$this->bancodedados->query("
					UPDATE 
						tab_descontos_concessoes 
					SET 
						dec_mensalidade = dec_mensalidade - $total_descontos 
					WHERE 
						dec_id = $desconto_maior_validade");
			}
		}
	
	}

	function atualizaAssinaturaVigencia($assinatura_id) {
		
		$assinaturas = controle::instanciaObjeto("assinaturas", $this->bancodedados, $this->permissoes, false);
		$assinaturas->atualizaAssinaturaVigencia($assinatura_id);
	
	}

	##############
	### Fechar ###
	##############

	function fechar() {
		
		if ($this->dados_item) {
			if ($this->dados_item["condominio"]) {
				$this->conteudo = '<script type="text/javascript">if (window.opener && window.opener.buscaDescontosCondominio) window.opener.buscaDescontosCondominio('.$this->dados_item["condominio"]["id"].');</script>';
			} elseif ($this->dados_item["assinatura"]) {
				$this->conteudo = '<script type="text/javascript">if (window.opener && window.opener.buscaAssinaturasCliente) window.opener.buscaAssinaturasCliente('.$this->dados_item["assinatura"]["cliente_id"].');</script>';
				$this->conteudo .= '<script type="text/javascript">if (window.opener && window.opener.buscaAssinaturaDescontos) window.opener.buscaAssinaturaDescontos('.$this->dados_item["assinatura"]["id"].');</script>';
			} elseif ($this->dados_item["plano"]) {
				$this->conteudo = '<script type="text/javascript">if (window.opener && window.opener.buscaDescontosConcessoesPlanos) window.opener.buscaDescontosConcessoesPlanos('.$this->dados_item["plano"]["id"].');</script>';
			}
			$this->conteudo .= '<script type="text/javascript">window.close();</script>';
		} else {
			$this->conteudo = '<script type="text/javascript">window.close();</script>';
		}
	
	}

	#################
	### Adicionar ###
	#################

	function adicionar() {
		
		if ($_GET["ass"]) {
			$this->adicionarDescontoAssinatura($_GET["ass"]);
		} elseif ($_GET["con"]) {
			$this->adicionarDescontoCondominio($_GET["con"]);
		} elseif ($_GET["pla"]) {
			$this->adicionarDescontoPlano($_GET["pla"]);
		} else {
			$this->fechar();
		}
		
	}

	function adicionarDescontoAssinatura($ass_id) {
		
		if (($dados_assinatura = $this->buscaDadosAssinatura($ass_id)) != false) {
			
			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

				$descontos_parametros = array();
				$descontos_parametros[] = $dados_assinatura["servico"];
				$descontos_parametros[] = "'ass'";
				
				$this->conteudo .= $this->carregaArquivo("descontos_concessoes.ass.adicionar.inc.php");
				
				$this->conteudo = str_replace("[cliente]", $dados_assinatura["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace("[plano_id]", $dados_assinatura["plano"], $this->conteudo);
				$this->conteudo = str_replace("[assinatura_id]", $dados_assinatura["id"], $this->conteudo);
				$this->conteudo = str_replace("[assinatura_nome]", $dados_assinatura["nome"], $this->conteudo);
				$this->conteudo = str_replace("[assinatura_instalacao]", $this->formataDinheiro($dados_assinatura["instalacao"]), $this->conteudo);
				$this->conteudo = str_replace("[assinatura_mensalidade]", $this->formataDinheiro($dados_assinatura["mensalidade"]), $this->conteudo);
				$this->conteudo = str_replace("[descontos_parametros]", implode(", ", $descontos_parametros), $this->conteudo);
				$this->conteudo = str_replace("[instalacao_tipo]", $this->geraSelectValorTipos($this->valor_tipos), $this->conteudo);
				$this->conteudo = str_replace("[mensalidade_tipo]", $this->geraSelectValorTipos($this->valor_tipos), $this->conteudo);
				$this->conteudo = str_replace("[validade]", $this->geraSelectValidades($this->buscaValidades()), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar&ass={$dados_assinatura["id"]}", $this->conteudo);
			
			} else {
				
				$dados_desconto = $this->buscaDadosDesconto($_POST["desconto"]);
				$parametros = unserialize($dados_desconto["parametros"]);
				$parametros["carencia"] = $parametros["carencia"] ? $parametros["carencia"] : 0;
				
				// Restaura Descontos Assinatura
				$this->restauraDescontosConcessoes($ass_id);
				
				// Campos Fixos
				if ($dados_desconto["instalacao_fixa"]) $_POST["instalacao_valor"] = $dados_desconto["instalacao_valor"];
				if ($dados_desconto["mensalidade_fixa"]) $_POST["mensalidade_valor"] = $dados_desconto["mensalidade_valor"];
				
				// Auto aplicado
				$status = $dados_desconto["auto_aprovado"] ? 2 : 1;
				if (!$this->descontoAutomatico($_POST["instalacao_valor"], $dados_desconto["instalacao_valor"])) $status = 1;
				if (!$this->descontoAutomatico($_POST["mensalidade_valor"], $dados_desconto["mensalidade_valor"])) $status = 1;
				if ($dados_desconto["validade_tipo"] && $dados_desconto["validade_tipo"] == 2) {
					if ($dados_desconto["validade_fixa"]) $_POST["validade_2"] = $dados_desconto["validade"];
					if (!$this->validadeAutomatica($_POST["validade_2"], $dados_desconto["validade"])) $status = 1;
				} else {
					if ($dados_desconto["validade_fixa"]) $_POST["validade_1"] = $dados_desconto["validade"];
					if (!$this->validadeAutomatica($_POST["validade_1"], $dados_desconto["validade"])) $status = 1;
				}
				if ($this->descontoExistente($_POST["desconto"], $_GET["con"], 'con')) $status = 1;
				
				// Correção dos dados
				$_POST["instalacao_valor"] = $this->corrigeDesconto($_POST["instalacao_valor"], $dados_assinatura["instalacao"]);
				$_POST["mensalidade_valor"] = $this->corrigeDesconto($_POST["mensalidade_valor"], $dados_assinatura["mensalidade"]);
				if ($dados_desconto["validade_tipo"] && $dados_desconto["validade_tipo"] == 2) {
					$_POST["data_termino"] = $_POST["validade_2"] ? $this->formataData($_POST["validade_2"]) : ($dados_desconto["validade"] ? $this->formataData($dados_desconto["validade"]) : "NULL");
					$_POST["validade"] = "NULL";
				} else {
					$_POST["data_termino"] = "NULL";
					$_POST["validade"] = $_POST["validade_1"] ? $_POST["validade_1"] : ($dados_desconto["validade"] ? $dados_desconto["validade"] : "NULL");
				}
				
				// Verifica se possui um desconto auto-aplicado duplicado
				if ($dados_desconto["tipo_id"] == 2 && $this->descontoDuplicadoAssinatura($dados_desconto["id"], $dados_assinatura["id"])) {
					$status = 1;
				}
				
				// Armazena Dados
				$query = "INSERT INTO  tab_descontos_concessoes (des_id, det_id, dec_data_cadastro, dec_data_termino, ass_id, dec_instalacao, dec_mensalidade, dec_fidelidade, dec_validade, dcs_id) VALUES ({$_POST["desconto"]}, {$dados_desconto["tipo_id"]}, NOW(), {$_POST["data_termino"]}, $ass_id, {$_POST["instalacao_valor"]}, {$_POST["mensalidade_valor"]}, {$dados_desconto["fidelidade"]}, {$_POST["validade"]}, $status)";
				$this->bancodedados->query($query);
				
				$id = $this->bancodedados->ultimoIdInserido();
				
				if (($contrato = $this->buscaContratoAssinatura($ass_id)) != false) {

					// Insere Associação Contrato/Desconto
					$this->bancodedados->query("
						INSERT INTO 
							rel_dec_cor_estacontido 
						SET
							dec_id = '$id', 
							cor_id = '{$contrato["id"]}'");
							
					// Seta contrato como não sincronizado
					$this->bancodedados->query("
						UPDATE 
							tab_contratos 
						SET 
							cor_sincronizado = 0 
						WHERE 
							cor_id = '{$contrato["id"]}' AND 
							crs_id < 19");
					
				}
				
				// Atualiza Vigência
				$this->atualizaAssinaturaVigencia($ass_id);
				
				// Recalcula Descontos Assinatura
				$this->recalculaDescontosConcessoes($ass_id);
				
				// Registra Comentários
				$this->registraComentario($id, "<b>Ação</b>: Adicionado".($status == 2 ? " (Auto Aprovado)" : ""), $_SESSION["usuario"]);
				if ($_POST["comentarios"]) {
					$this->registraComentario($id, $_POST["comentarios"], $_SESSION["usuario"]);
				}
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
				
			}
		
		} else {
			$this->fechar();
		}
	
	}

	function formataData($data) {

		return "'".substr($data, 6, 4)."-".substr($data, 3, 2)."-".substr($data, 0, 2)."'";
	
	}

	function adicionarDescontoCondominio($con_id) {
		
		if (($dados_condominio = $this->buscaDadosCondominio($con_id)) != false) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("descontos_concessoes.con.adicionar.inc.php");

				$this->conteudo = str_replace("[condominio]", $dados_condominio["nome"], $this->conteudo);
				$this->conteudo = str_replace("[condominio_id]", $con_id, $this->conteudo);
				$this->conteudo = str_replace("[instalacao_tipo]", $this->geraSelectValorTipos($this->valor_tipos), $this->conteudo);
				$this->conteudo = str_replace("[mensalidade_tipo]", $this->geraSelectValorTipos($this->valor_tipos), $this->conteudo);
				$this->conteudo = str_replace("[validade]", $this->geraSelectValidades($this->buscaValidades()), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar&con={$dados_condominio["id"]}", $this->conteudo);
			
			} else {
				
				$dados_desconto = $this->buscaDadosDesconto($_POST["desconto"]);
				$status = $dados_desconto["auto_aprovado"] ? 2 : 1;

				if ($dados_desconto["instalacao_fixa"]) $_POST["instalacao_valor"] = $dados_desconto["instalacao_valor"];
				if ($dados_desconto["mensalidade_fixa"]) $_POST["mensalidade_valor"] = $dados_desconto["mensalidade_valor"];
				if ($dados_desconto["validade_fixa"]) $_POST["validade"] = $dados_desconto["validade"];

				if (!$this->descontoAutomatico($_POST["instalacao_valor"], $dados_desconto["instalacao_valor"])) $status = 1;
				if (!$this->descontoAutomatico($_POST["mensalidade_valor"], $dados_desconto["mensalidade_valor"])) $status = 1;
				if (!$this->validadeAutomatica($_POST["validade"], $dados_desconto["validade"])) $status = 1;
				if ($this->descontoExistente($_POST["desconto"], $con_id, 'con')) $status = 1;

				$_POST["instalacao_valor"] = $this->corrigeDesconto($_POST["instalacao_valor"]);
				$_POST["mensalidade_valor"] = $this->corrigeDesconto($_POST["mensalidade_valor"]);
				$_POST["validade"] = $_POST["validade"] ? $_POST["validade"] : "NULL";
				$_POST["data_inicio"] = $_POST["data_inicio"] ? "'".substr($_POST["data_inicio"], 6, 4).substr($_POST["data_inicio"], 3, 2).substr($_POST["data_inicio"], 0, 2)."'" : "NULL";
				$_POST["data_termino"] = $_POST["data_termino"] ? "'".substr($_POST["data_termino"], 6, 4).substr($_POST["data_termino"], 3, 2).substr($_POST["data_termino"], 0, 2)."'" : "NULL";

				$this->bancodedados->query("
					INSERT INTO 
						tab_descontos_concessoes 
					SET
						des_id = '{$_POST["desconto"]}', 
						det_id = {$dados_desconto["tipo_id"]}, 
						dec_data_cadastro = NOW(), 
						con_id = '$con_id', 
						dec_instalacao = {$_POST["instalacao_valor"]}, 
						dec_mensalidade = {$_POST["mensalidade_valor"]}, 
						dec_fidelidade = {$dados_desconto["fidelidade"]}, 
						dec_validade = {$_POST["validade"]}, 
						dec_data_inicio = {$_POST["data_inicio"]}, 
						dec_data_termino = {$_POST["data_termino"]}, 
						dcs_id = '$status'");
				
				$id = $this->bancodedados->ultimoIdInserido();

				$this->registraComentario($id, "<b>Ação</b>: Adicionado".($_POST["comentarios"] ? "<br><b>Comentário:</b> {$_POST["comentarios"]}" : ""), $_SESSION["usuario"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
			
			}
		
		} else {
			$this->fechar();
		}
		
	}

	function adicionarDescontoPlano($plano_id) {
		
		if (($dados_plano = $this->buscaDadosPlano($plano_id)) != false) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

				$this->conteudo .= $this->carregaArquivo("descontos_concessoes.pla.adicionar.inc.php");

				$this->conteudo = str_replace("[plano]", "{$dados_plano["servico_nome"]} &raquo; {$dados_plano["nome"]}", $this->conteudo);
				$this->conteudo = str_replace("[descontos]", $this->geraSelect($this->buscaDescontos($plano_id, $dados_plano["servico_id"], $dados_plano["sistema_id"], "plano"), null, true), $this->conteudo);
				$this->conteudo = str_replace("[instalacao_tipo]", $this->geraSelectValorTipos($this->valor_tipos), $this->conteudo);
				$this->conteudo = str_replace("[mensalidade_tipo]", $this->geraSelectValorTipos($this->valor_tipos), $this->conteudo);
				$this->conteudo = str_replace("[validade]", $this->geraSelectValidades($this->buscaValidades()), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar&pla={$dados_plano["id"]}", $this->conteudo);
			
			} else {
				
				$dados_desconto = $this->buscaDadosDesconto($_POST["desconto"]);
				$status = $dados_desconto["auto_aprovado"] ? 2 : 1;

				if ($dados_desconto["instalacao_fixa"]) $_POST["instalacao_valor"] = $dados_desconto["instalacao_valor"];
				if ($dados_desconto["mensalidade_fixa"]) $_POST["mensalidade_valor"] = $dados_desconto["mensalidade_valor"];
				if ($dados_desconto["validade_fixa"]) $_POST["validade"] = $dados_desconto["validade"];

				if (!$this->descontoAutomatico($_POST["instalacao_valor"], $dados_desconto["instalacao_valor"])) $status = 1;
				if (!$this->descontoAutomatico($_POST["mensalidade_valor"], $dados_desconto["mensalidade_valor"])) $status = 1;
				if (!$this->validadeAutomatica($_POST["validade"], $dados_desconto["validade"])) $status = 1;
				if ($this->descontoExistente($_POST["desconto"], $plano_id, 'pla')) $status = 1;

				$_POST["instalacao_valor"] = $this->corrigeDesconto($_POST["instalacao_valor"]);
				$_POST["mensalidade_valor"] = $this->corrigeDesconto($_POST["mensalidade_valor"]);
				$_POST["validade"] = $_POST["validade"] ? $_POST["validade"] : "NULL";
				$_POST["data_inicio"] = $_POST["data_inicio"] ? "'".substr($_POST["data_inicio"], 6, 4).substr($_POST["data_inicio"], 3, 2).substr($_POST["data_inicio"], 0, 2)."'" : "NULL";
				$_POST["data_termino"] = $_POST["data_termino"] ? "'".substr($_POST["data_termino"], 6, 4).substr($_POST["data_termino"], 3, 2).substr($_POST["data_termino"], 0, 2)."'" : "NULL";


				$this->bancodedados->query("
					INSERT INTO 
						tab_descontos_concessoes
					SET
						des_id = {$_POST["desconto"]}, 
						det_id = {$dados_desconto["tipo_id"]}, 
						dec_data_cadastro = NOW(), 
						pla_id = $plano_id, 
						dec_instalacao = {$_POST["instalacao_valor"]}, 
						dec_mensalidade = {$_POST["mensalidade_valor"]}, 
						dec_fidelidade = {$dados_desconto["fidelidade"]}, 
						dec_validade = {$_POST["validade"]}, 
						dec_data_inicio = {$_POST["data_inicio"]}, 
						dec_data_termino = {$_POST["data_termino"]}, 
						dcs_id = $status");

				$id = $this->bancodedados->ultimoIdInserido();

				$this->registraComentario($id, "<b>Ação</b>: Adicionado".($_POST["comentarios"] ? "<br><b>Comentário:</b> {$_POST["comentarios"]}" : ""), $_SESSION["usuario"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
			
			}
			
		} else {
			$this->fechar();
		}
		
	}

	function descontoDuplicadoAssinatura($desconto_id, $assinatura_id) {
		
		$query = $this->bancodedados->query("
			SELECT 
				COUNT(dec_id)
			FROM 
				tab_descontos_concessoes 
			WHERE
				des_id = $desconto_id AND
				ass_id = $assinatura_id");
		
		return $this->bancodedados->resultado($query) > 0;
		
	}

	###########
	### Ver ###
	###########

	function ver() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			if ($this->dados_item["assinatura"]) {
				$this->verDescontoAssinatura();
			} elseif ($this->dados_item["condominio"]) {
				$this->verDescontoCondominio();
			} elseif ($this->dados_item["plano"]) {
				$this->verDescontoPlano();
			} else {
				$this->fechar();
			}
		} else {
			$this->fechar();
		}
		
	}

	function verDescontoAssinatura() {
		
		$dados_desconto = $this->buscaDadosDesconto($this->dados_item["desconto"]["id"]);
		
		$botoes = array();
		if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] == 1 && (($this->permissoes[$this->dados_modulo["nome"]]["aprovar_comercial"] && in_array($this->dados_item["tipo_id"], array(1, 2, 4))) || ($this->permissoes[$this->dados_modulo["nome"]]["aprovar_financeiro"] && $this->dados_item["tipo_id"] == 3))) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" style="width:60px" value="Aprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a='.($this->dados_item["tipo_id"] == 3 ? 'aprovar_financeiro' : 'aprovar_comercial').'&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["reprovar"]) $botoes[] = '<input type="button" name="reprovar" class="buttonAzulEscuroBranco11" style="width:60px" value="Reprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=reprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] <= 3 && $this->dados_item["assinatura"]["status"] == 1 && !$this->dados_item["assinatura"]["contrato"] && $this->permissoes[$this->dados_modulo["nome"]]["cancelar"]) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" style="width:60px" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] == 5 && $this->permissoes[$this->dados_modulo["nome"]]["finalizar"]) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=finalizar&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] <= 4 && $this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" style="width:60px" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
		$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
		$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
		
		$assinaturas_parametros = array();
		$assinaturas_parametros[] = $this->dados_item["assinatura"]["cliente_id"];
		$assinaturas_parametros[] = ($this->permissoes["assinaturas"]["ver"]) ? 1 : 0;
		
		$this->conteudo .= $this->carregaArquivo("descontos_concessoes.ass.ver.inc.php");
		
		$this->conteudo = str_replace("[cliente]", $this->dados_item["assinatura"]["cliente_nome"], $this->conteudo);
		$this->conteudo = str_replace("[cliente_id]", $this->dados_item["assinatura"]["cliente_id"], $this->conteudo);
		$this->conteudo = str_replace("[assinatura]", $this->dados_item["assinatura"]["nome"], $this->conteudo);
		$this->conteudo = str_replace("[assinatura_id]", $this->dados_item["assinatura"]["id"], $this->conteudo);
		$this->conteudo = str_replace("[desconto]", $this->dados_item["desconto"]["nome"], $this->conteudo);
		$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
		$this->conteudo = str_replace("[instalacao]", $this->dados_item["instalacao_valor"], $this->conteudo);
		$this->conteudo = str_replace("[mensalidade]", $this->dados_item["mensalidade_valor"], $this->conteudo);
		$this->conteudo = str_replace("[validade]", $dados_desconto["validade"], $this->conteudo);
		$this->conteudo = str_replace("[validade_visivel]", (($this->dados_item["status_id"] <= 4) ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row") : "none"), $this->conteudo);
		$this->conteudo = str_replace("[data_inicio]", $this->dados_item["data_inicio"], $this->conteudo);
		$this->conteudo = str_replace("[data_termino]", ($this->dados_item["data_termino"]) ? $this->dados_item["data_termino"] : "Indeterminado", $this->conteudo);
		$this->conteudo = str_replace("[inicio_termino_visivel]", (($this->dados_item["status_id"] > 4) ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row") : "none"), $this->conteudo);
		$this->conteudo = str_replace("[lista_comentarios]", $this->geraComentariosListagem($_GET["i"]), $this->conteudo);
		$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
		$this->conteudo = str_replace("[assinaturas_parametros]", implode(", ", $assinaturas_parametros), $this->conteudo);
		$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		
		$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
		$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
		
	}

	function verDescontoCondominio() {
		
		$dados_desconto = $this->buscaDadosDesconto($this->dados_item["desconto"]["id"]);
		
		$botoes = array();
		if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] == 1 && (($this->permissoes[$this->dados_modulo["nome"]]["aprovar_comercial"] && in_array($this->dados_item["tipo_id"], array(1, 2, 4))) || ($this->permissoes[$this->dados_modulo["nome"]]["aprovar_financeiro"] && $this->dados_item["tipo_id"] == 3))) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" style="width:60px" value="Aprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a='.($this->dados_item["tipo_id"] == 3 ? 'aprovar_financeiro' : 'aprovar_comercial').'&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] == 2 && $this->permissoes[$this->dados_modulo["nome"]]["ativar"]) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" style="width:60px" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["reprovar"]) $botoes[] = '<input type="button" name="reprovar" class="buttonAzulEscuroBranco11" style="width:60px" value="Reprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=reprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] == 5 && $this->permissoes[$this->dados_modulo["nome"]]["finalizar"]) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=finalizar&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] <= 4  && $this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" style="width:60px" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
		$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
		$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
		
		$descontos_parametros = array();
		$descontos_parametros[] = $this->dados_item["condominio"]["id"];
		$descontos_parametros[] = ($this->permissoes["descontos_concessoes"]["ver"]) ? 1 : 0;
		
		$this->conteudo .= $this->carregaArquivo("descontos_concessoes.con.ver.inc.php");
		
		$this->conteudo = str_replace("[condominio]", $this->dados_item["condominio"]["nome"],  $this->conteudo);
		$this->conteudo = str_replace("[servico]", $this->dados_item["desconto"]["servico_nome"], $this->conteudo);
		$this->conteudo = str_replace("[desconto]", $this->dados_item["desconto"]["nome"], $this->conteudo);
		$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
		$this->conteudo = str_replace("[instalacao]", $this->dados_item["instalacao_valor"], $this->conteudo);
		$this->conteudo = str_replace("[mensalidade]", $this->dados_item["mensalidade_valor"], $this->conteudo);
		$this->conteudo = str_replace("[validade]", $dados_desconto["validade"], $this->conteudo);
		$this->conteudo = str_replace("[data_inicio]", $this->dados_item["data_inicio"], $this->conteudo);
		$this->conteudo = str_replace("[data_termino]", $this->dados_item["data_termino"], $this->conteudo);
		$this->conteudo = str_replace("[lista_comentarios]", $this->geraComentariosListagem($_GET["i"]), $this->conteudo);
		$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
		$this->conteudo = str_replace('[descontos_parametros]', implode(", ", $descontos_parametros), $this->conteudo);
		$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		
		$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
		$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
	
	}

	function verDescontoPlano() {
		
		$dados_desconto = $this->buscaDadosDesconto($this->dados_item["desconto"]["id"]);
		
		$botoes = array();
		if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] == 1 && (($this->permissoes[$this->dados_modulo["nome"]]["aprovar_comercial"] && in_array($this->dados_item["tipo_id"], array(1, 2, 4))) || ($this->permissoes[$this->dados_modulo["nome"]]["aprovar_financeiro"] && $this->dados_item["tipo_id"] == 3))) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" style="width:60px" value="Aprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a='.($this->dados_item["tipo_id"] == 3 ? 'aprovar_financeiro' : 'aprovar_comercial').'&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] == 2 && $this->permissoes[$this->dados_modulo["nome"]]["ativar"]) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" style="width:60px" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["reprovar"]) $botoes[] = '<input type="button" name="reprovar" class="buttonAzulEscuroBranco11" style="width:60px" value="Reprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=reprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] == 5 && $this->permissoes[$this->dados_modulo["nome"]]["finalizar"]) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=finalizar&i='.$this->dados_item["id"].'\';">';
		if ($this->dados_item["status_id"] <= 4 && $this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" style="width:60px" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
		$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
		$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
		
		$descontos_parametros = array();
		$descontos_parametros[] = $this->dados_item["condominio"]["id"];
		$descontos_parametros[] = ($this->permissoes["descontos_concessoes"]["ver"]) ? 1 : 0;

		$this->conteudo .= $this->carregaArquivo("descontos_concessoes.pla.ver.inc.php");
		
		$this->conteudo = str_replace("[plano_id]", $this->dados_item["plano"]["id"],  $this->conteudo);
		$this->conteudo = str_replace("[plano]", "{$this->dados_item["plano"]["servico_nome"]} &raquo; {$this->dados_item["plano"]["nome"]}",  $this->conteudo);
		$this->conteudo = str_replace("[desconto]", $this->dados_item["desconto"]["nome"], $this->conteudo);
		$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
		$this->conteudo = str_replace("[instalacao]", $this->dados_item["instalacao_valor"], $this->conteudo);
		$this->conteudo = str_replace("[mensalidade]", $this->dados_item["mensalidade_valor"], $this->conteudo);
		$this->conteudo = str_replace("[validade]", $dados_desconto["validade"], $this->conteudo);
		$this->conteudo = str_replace("[data_inicio]", $this->dados_item["data_inicio"], $this->conteudo);
		$this->conteudo = str_replace("[data_termino]", $this->dados_item["data_termino"], $this->conteudo);
		$this->conteudo = str_replace("[lista_comentarios]", $this->geraComentariosListagem($_GET["i"]), $this->conteudo);
		$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
		$this->conteudo = str_replace('[descontos_parametros]', implode(", ", $descontos_parametros), $this->conteudo);
		$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

		$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
		$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
	
	}

	##############
	### Editar ###
	##############

	function editar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			if ($this->dados_item["assinatura"]) {
				$this->editarDescontoAssinatura();
			} elseif ($this->dados_item["condominio"]) {
				$this->editarDescontoCondominio();
			} elseif ($this->dados_item["plano"]) {
				$this->editarDescontoPlano();
			} else {
				$this->fechar();
			}
		} else {
			$this->fechar();
		}
		
	}

	function editarDescontoAssinatura() {
		
		$dados_desconto = $this->buscaDadosDesconto($this->dados_item["desconto"]["id"]);
		
		if (!$_POST) {
				
			$botoes = array();
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			if ($this->dados_item["status_id"] == 1 && (($this->permissoes[$this->dados_modulo["nome"]]["aprovar_comercial"] && in_array($this->dados_item["tipo_id"], array(1, 2, 4))) || ($this->permissoes[$this->dados_modulo["nome"]]["aprovar_financeiro"] && $this->dados_item["tipo_id"] == 3))) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" style="width:60px" value="Aprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a='.($this->dados_item["tipo_id"] == 3 ? 'aprovar_financeiro' : 'aprovar_comercial').'&i='.$this->dados_item["id"].'\';">';
			if ($this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["reprovar"]) $botoes[] = '<input type="button" name="reprovar" class="buttonAzulEscuroBranco11" style="width:60px" value="Reprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=reprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->dados_item["status_id"] <= 3 && $this->dados_item["assinatura"]["status"] == 1 && !$this->dados_item["assinatura"]["contrato"] && $this->permissoes[$this->dados_modulo["nome"]]["cancelar"]) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" style="width:60px" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
			if ($this->dados_item["status_id"] == 5 && $this->permissoes[$this->dados_modulo["nome"]]["finalizar"]) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=finalizar&i='.$this->dados_item["id"].'\';">';
			if ($this->dados_item["status_id"] <= 4 && $this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" style="width:60px" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

			$this->conteudo .= $this->carregaArquivo("descontos_concessoes.ass.editar.inc.php");
			
			$this->conteudo = str_replace("[cliente]", $this->dados_item["assinatura"]["cliente_nome"], $this->conteudo);
			$this->conteudo = str_replace("[cliente_id]", $this->dados_item["assinatura"]["cliente_id"], $this->conteudo);
			$this->conteudo = str_replace("[assinatura]", $this->dados_item["assinatura"]["nome"], $this->conteudo);
			$this->conteudo = str_replace("[assinatura_id]", $this->dados_item["assinatura"]["id"], $this->conteudo);
			$this->conteudo = str_replace("[desconto]", $this->dados_item["desconto"]["nome"], $this->conteudo);
			$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
			$this->conteudo = str_replace("[instalacao_valor]", $this->dados_item["instalacao_valor"], $this->conteudo);
			$this->conteudo = str_replace("[mensalidade_valor]", $this->dados_item["mensalidade_valor"], $this->conteudo);
			$this->conteudo = str_replace("[instalacao_tipo]", $this->geraSelectValorTipos($this->valor_tipos, $this->dados_item["instalacao_tipo"]), $this->conteudo);
			$this->conteudo = str_replace("[mensalidade_tipo]", $this->geraSelectValorTipos($this->valor_tipos, $this->dados_item["mensalidade_tipo"]), $this->conteudo);
			$this->conteudo = str_replace("[exibe_validade_1]", !(substr($dados_desconto["nome"], 0, 7) == "Síndico" || ($dados_desconto["validade_tipo"] && $dados_desconto["validade_tipo"] == 2)) ? "" : "none", $this->conteudo);
			$this->conteudo = str_replace("[exibe_validade_2]", substr($dados_desconto["nome"], 0, 7) == "Síndico" || ($dados_desconto["validade_tipo"] && $dados_desconto["validade_tipo"] == 2) ? "" : "none", $this->conteudo);
			$this->conteudo = str_replace("[validade_1]", $this->geraSelectValidades($this->buscaValidades(), $this->dados_item["validade"]), $this->conteudo);
			$this->conteudo = str_replace("[validade_2]", $this->dados_item["data_termino"], $this->conteudo);
			$this->conteudo = str_replace("[validade_visivel]", (($this->dados_item["status_id"] <= 4) ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row") : "none"), $this->conteudo);
			$this->conteudo = str_replace("[data_inicio]", $this->dados_item["data_inicio"], $this->conteudo);
			$this->conteudo = str_replace("[data_termino]", $this->dados_item["data_termino"], $this->conteudo);
			$this->conteudo = str_replace("[data_termino_fixa]", ($this->permissoes[$this->dados_modulo["nome"]]["editar_datas"] && $this->dados_item["status_id"] == 5) ? "" : "disabled", $this->conteudo);
			$this->conteudo = str_replace("[inicio_visivel]", ($this->dados_item["status_id"] > 4 ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row") : "none"), $this->conteudo);
			$this->conteudo = str_replace("[termino_visivel]", ($this->dados_item["status_id"] > 4 ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row") : "none"), $this->conteudo);
			$this->conteudo = str_replace("[instalacao_fixa]", ($dados_desconto["instalacao_fixa"] || $this->dados_item["status_id"] > 3) ? "disabled" : "", $this->conteudo);
			$this->conteudo = str_replace("[mensalidade_fixa]", ($dados_desconto["mensalidade_fixa"] || $this->dados_item["status_id"] > 3) ? "disabled" : "", $this->conteudo);
			$this->conteudo = str_replace("[validade_fixa]", ($dados_desconto["validade_fixa"] || $this->dados_item["status_id"] > 3) ? "disabled" : "", $this->conteudo);
			$this->conteudo = str_replace("[lista_comentarios]", $this->geraComentariosListagem($_GET["i"]), $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
		
		} else {
			
			if ($this->dados_item["status_id"] <= 3) {
				
				if ($dados_desconto["instalacao_fixa"]) $_POST["instalacao_valor"] = $this->dados_item["instalacao_valor"];
				if ($dados_desconto["mensalidade_fixa"]) $_POST["mensalidade_valor"] = $this->dados_item["mensalidade_valor"];
				if ($dados_desconto["validade_fixa"]) $_POST["validade"] = $this->dados_item["validade"];

				if ($this->dados_item["status_id"] <= 3) {
					$status = $dados_desconto["auto_aprovado"] ? 2 : 1;
					if (!$this->descontoAutomatico($_POST["instalacao_valor"], $dados_desconto["instalacao_valor"])) $status = 1;
					if (!$this->descontoAutomatico($_POST["mensalidade_valor"], $dados_desconto["mensalidade_valor"])) $status = 1;
					if ($dados_desconto["validade_tipo"] && $dados_desconto["validade_tipo"] == 2) {
						if ($dados_desconto["validade_fixa"]) $_POST["validade_2"] = $dados_desconto["validade"];
						if (!$this->validadeAutomatica($_POST["validade_2"], $dados_desconto["validade"])) $status = 1;
					} else {
						if ($dados_desconto["validade_fixa"]) $_POST["validade_1"] = $dados_desconto["validade"];
						if (!$this->validadeAutomatica($_POST["validade_1"], $dados_desconto["validade"])) $status = 1;
					}
					if ($this->descontoExistente($_POST["desconto"], $_GET["con"], 'con')) $status = 1;
				} else {
					$status = $this->dados_item["status_id"];
				}

				$_POST["instalacao_valor"] = $this->corrigeDesconto($_POST["instalacao_valor"], $this->dados_item["assinatura"]["instalacao"]);
				$_POST["mensalidade_valor"] = $this->corrigeDesconto($_POST["mensalidade_valor"], $this->dados_item["assinatura"]["mensalidade"]);
				
				if (substr($dados_desconto["nome"], 0, 7) == "Síndico" || ($dados_desconto["validade_tipo"] && $dados_desconto["validade_tipo"] == 2)) {
					$_POST["data_termino"] = $_POST["validade_2"] ? $this->formataData($_POST["validade_2"]) : ($dados_desconto["validade"] ? $this->formataData($dados_desconto["validade"]) : "NULL");
					$_POST["validade"] = "NULL";
				} else {
					$_POST["data_termino"] = "NULL";
					$_POST["validade"] = $_POST["validade_1"] ? $_POST["validade_1"] : ($dados_desconto["validade"] ? $dados_desconto["validade"] : "NULL");
				}
				
				if (!$_POST["validade"]) {
					$_POST["validade"] = "NULL";
				}
				
				$this->bancodedados->query("
					UPDATE 
						tab_descontos_concessoes 
					SET 
						dec_instalacao = {$_POST["instalacao_valor"]}, 
						dec_mensalidade = {$_POST["mensalidade_valor"]}, 
						dec_validade = {$_POST["validade"]}, 
						dec_data_termino = {$_POST["data_termino"]} 
					WHERE 
						dec_id = {$_GET["i"]}");
				
				if ($this->bancodedados->linhasAfetadas()) {
					// Altera Status para aguardando aprovação
					$this->bancodedados->query("UPDATE tab_descontos_concessoes SET dcs_id = $status WHERE dec_id = {$_GET["i"]}");
					// Muda situação do contrato, se houver
					if ($this->dados_item["assinatura"]["contrato"]) {
						$this->bancodedados->query("UPDATE tab_contratos SET cor_sincronizado = 0 WHERE cor_id = '{$this->dados_item["assinatura"]["contrato"]["id"]}' AND crs_id IN (11, 12, 13, 14)");
					}
				}
				
				if ($_POST["comentarios"]) {
					$this->registraComentario($_GET["i"], $_POST["comentarios"], $_SESSION["usuario"]);
				}
				
			} elseif ($this->permissoes[$this->dados_modulo["nome"]]["editar_datas"] && $this->dados_item["status_id"] == 5 && $this->dados_item["data_termino"] != $_POST["data_termino"]) {

				$data_termino_antiga = $this->dados_item["data_termino"] ? $this->dados_item["data_termino"] : "Indeterminado";
				$data_termino_nova = $_POST["data_termino"] ? $_POST["data_termino"] : "Indeterminado";
				
				$this->registraComentario($_GET["i"], "<b>Ação</b>: Alteração do Término: <b>{$data_termino_antiga}</b> para <b>{$data_termino_nova}</b>".($_POST["comentarios"] ? "<br><b>Comentários:</b> {$_POST["comentarios"]}" : ""), $_SESSION["usuario"]);
				
				$_POST["data_termino"] = $_POST["data_termino"] ? "'".substr($_POST["data_termino"], 6, 4)."-".substr($_POST["data_termino"], 3, 2)."-".substr($_POST["data_termino"], 0, 2)."'" : "NULL";
				
				$this->bancodedados->query("
					UPDATE
						tab_descontos_concessoes
					SET
						dec_data_termino = {$_POST["data_termino"]}
					WHERE
						dec_id = {$_GET["i"]}");
				
			} elseif ($_POST["comentarios"]) {
				
				$this->registraComentario($_GET["i"], $_POST["comentarios"], $_SESSION["usuario"]);
				
			}
			
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
			
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
		
		}
	
	}

	function editarDescontoCondominio() {
		
		$dados_desconto = $this->buscaDadosDesconto($this->dados_item["desconto"]["id"]);
		
		if (!$_POST) {
			
			$botoes = array();
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			if ($this->dados_item["status_id"] == 1 && (($this->permissoes[$this->dados_modulo["nome"]]["aprovar_comercial"] && in_array($this->dados_item["tipo_id"], array(1, 2, 4))) || ($this->permissoes[$this->dados_modulo["nome"]]["aprovar_financeiro"] && $this->dados_item["tipo_id"] == 3))) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" style="width:60px" value="Aprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a='.($this->dados_item["tipo_id"] == 3 ? 'aprovar_financeiro' : 'aprovar_comercial').'&i='.$this->dados_item["id"].'\';">';
			if ($this->dados_item["status_id"] == 2 && $this->permissoes[$this->dados_modulo["nome"]]["ativar"]) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" style="width:60px" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
			if ($this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["reprovar"]) $botoes[] = '<input type="button" name="reprovar" class="buttonAzulEscuroBranco11" style="width:60px" value="Reprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=reprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->dados_item["status_id"] == 5 && $this->permissoes[$this->dados_modulo["nome"]]["finalizar"]) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=finalizar&i='.$this->dados_item["id"].'\';">';
			if ($this->dados_item["status_id"] <= 4 && $this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" style="width:60px" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			
			$descontos_parametros = array();
			$descontos_parametros[] = $this->dados_item["condominio"]["id"];
			$descontos_parametros[] = ($this->permissoes["descontos_concessoes"]["ver"]) ? 1 : 0;
			
			$this->conteudo .= $this->carregaArquivo("descontos_concessoes.con.editar.inc.php");
			
			$this->conteudo = str_replace("[condominio]", $this->dados_item["condominio"]["nome"],$this->conteudo);
			$this->conteudo = str_replace("[servico]", $this->dados_item["desconto"]["servico_nome"], $this->conteudo);
			$this->conteudo = str_replace("[desconto]", $this->dados_item["desconto"]["nome"], $this->conteudo);
			$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
			$this->conteudo = str_replace("[instalacao_valor]", $this->dados_item["instalacao_valor"], $this->conteudo);
			$this->conteudo = str_replace("[mensalidade_valor]", $this->dados_item["mensalidade_valor"], $this->conteudo);
			$this->conteudo = str_replace("[instalacao_tipo]", $this->geraSelectValorTipos($this->valor_tipos, $this->dados_item["instalacao_tipo"]), $this->conteudo);
			$this->conteudo = str_replace("[mensalidade_tipo]", $this->geraSelectValorTipos($this->valor_tipos, $this->dados_item["mensalidade_tipo"]), $this->conteudo);
			$this->conteudo = str_replace("[exibe_validade_1]", !$dados_desconto["validade_tipo"] || $dados_desconto["validade_tipo"] == 1 ? "" : "none", $this->conteudo);
			$this->conteudo = str_replace("[exibe_validade_2]", $dados_desconto["validade_tipo"] && $dados_desconto["validade_tipo"] == 2 ? "" : "none", $this->conteudo);
			$this->conteudo = str_replace("[validade_1]", $this->geraSelectValidades($this->buscaValidades(), $this->dados_item["validade"]), $this->conteudo);
			$this->conteudo = str_replace("[validade_2]", $dados_desconto["validade"], $this->conteudo);
			$this->conteudo = str_replace("[validade_visivel]", (($this->dados_item["status_id"] <= 4) ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row") : "none"), $this->conteudo);
			$this->conteudo = str_replace("[data_inicio]", $this->dados_item["data_inicio"], $this->conteudo);
			$this->conteudo = str_replace("[data_termino]", $this->dados_item["data_termino"], $this->conteudo);
			$this->conteudo = str_replace("[instalacao_fixa]", ($dados_desconto["instalacao_fixa"] || $this->dados_item["status_id"] > 3) ? "disabled" : "", $this->conteudo);
			$this->conteudo = str_replace("[mensalidade_fixa]", ($dados_desconto["mensalidade_fixa"] || $this->dados_item["status_id"] > 3) ? "disabled" : "", $this->conteudo);
			$this->conteudo = str_replace("[validade_fixa]", ($dados_desconto["validade_fixa"] || $this->dados_item["status_id"] > 3) ? "disabled" : "", $this->conteudo);
			$this->conteudo = str_replace("[lista_comentarios]", $this->geraComentariosListagem($_GET["i"]), $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace('[descontos_parametros]', implode(", ", $descontos_parametros), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ",$botoes),$this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
		
		} else {
			
			if ($dados_desconto["instalacao_fixa"]) $_POST["instalacao_valor"] = $dados_desconto["instalacao_valor"];
			if ($dados_desconto["mensalidade_fixa"]) $_POST["mensalidade_valor"] = $dados_desconto["mensalidade_valor"];
			if ($dados_desconto["validade_fixa"]) $_POST["validade"] = $dados_desconto["validade"];

			if ($this->dados_item["status_id"] <= 3) {
				$status = $dados_desconto["auto_aprovado"] ? 2 : 1;
				if (!$this->descontoAutomatico($_POST["instalacao_valor"], $dados_desconto["instalacao_valor"])) $status = 1;
				if (!$this->descontoAutomatico($_POST["mensalidade_valor"], $dados_desconto["mensalidade_valor"])) $status = 1;
				if (!$this->validadeAutomatica($_POST["validade"], $dados_desconto["validade"])) $status = 1;
				if ($this->descontoExistente($_POST["desconto"], $_GET["con"], 'con')) $status = 1;
			} else {
				$status = $this->dados_item["status_id"];
			}

			$_POST["instalacao_valor"] = $this->corrigeDesconto($_POST["instalacao_valor"]);
			$_POST["mensalidade_valor"] = $this->corrigeDesconto($_POST["mensalidade_valor"]);
			$_POST["data_inicio"] = $_POST["data_inicio"] ? "'".substr($_POST["data_inicio"], 6, 4)."-".substr($_POST["data_inicio"], 3, 2)."-".substr($_POST["data_inicio"], 0, 2)."'" : "NULL";
			$_POST["data_termino"] = $_POST["data_termino"] ? "'".substr($_POST["data_termino"], 6, 4)."-".substr($_POST["data_termino"], 3, 2)."-".substr($_POST["data_termino"], 0, 2)."'" : "NULL";
			$_POST["validade"] = $_POST["validade"] ? $_POST["validade"] : "NULL";

			$this->bancodedados->query("
				UPDATE 
					tab_descontos_concessoes 
				SET 
					dec_instalacao = {$_POST["instalacao_valor"]}, 
					dec_mensalidade = {$_POST["mensalidade_valor"]}, 
					dec_validade = {$_POST["validade"]}, 
					dec_data_inicio = {$_POST["data_inicio"]}, 
					dec_data_termino = {$_POST["data_termino"]}, 
					dcs_id = $status 
				WHERE
					dec_id = {$_GET["i"]}");
			
			if ($_POST["comentarios"]) {
				$this->registraComentario($_GET["i"], $_POST["comentarios"], $_SESSION["usuario"]);
			}
			
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
			
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
		
		}
	
	}

	function editarDescontoPlano() {
		
		$dados_desconto = $this->buscaDadosDesconto($this->dados_item["desconto"]["id"]);
		
		if (!$_POST) {
			
			$botoes = array();
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			if ($this->dados_item["status_id"] == 1 && (($this->permissoes[$this->dados_modulo["nome"]]["aprovar_comercial"] && in_array($this->dados_item["tipo_id"], array(1, 2, 4))) || ($this->permissoes[$this->dados_modulo["nome"]]["aprovar_financeiro"] && $this->dados_item["tipo_id"] == 3))) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" style="width:60px" value="Aprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a='.($this->dados_item["tipo_id"] == 3 ? 'aprovar_financeiro' : 'aprovar_comercial').'&i='.$this->dados_item["id"].'\';">';
			if ($this->dados_item["status_id"] == 2 && $this->permissoes[$this->dados_modulo["nome"]]["ativar"]) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" style="width:60px" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
			if ($this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["reprovar"]) $botoes[] = '<input type="button" name="reprovar" class="buttonAzulEscuroBranco11" style="width:60px" value="Reprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=reprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->dados_item["status_id"] == 5 && $this->permissoes[$this->dados_modulo["nome"]]["finalizar"]) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=finalizar&i='.$this->dados_item["id"].'\';">';
			if ($this->dados_item["status_id"] <= 4 && $this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" style="width:60px" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			
			$this->conteudo .= $this->carregaArquivo("descontos_concessoes.pla.editar.inc.php");
			
			$this->conteudo = str_replace("[plano_id]", $this->dados_item["plano"]["id"],  $this->conteudo);
			$this->conteudo = str_replace("[plano]", "{$this->dados_item["plano"]["servico_nome"]} &raquo; {$this->dados_item["plano"]["nome"]}",  $this->conteudo);
			$this->conteudo = str_replace("[desconto]", $this->dados_item["desconto"]["nome"], $this->conteudo);
			$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
			$this->conteudo = str_replace("[instalacao_valor]", $this->dados_item["instalacao_valor"], $this->conteudo);
			$this->conteudo = str_replace("[mensalidade_valor]", $this->dados_item["mensalidade_valor"], $this->conteudo);
			$this->conteudo = str_replace("[instalacao_tipo]", $this->geraSelectValorTipos($this->valor_tipos, $this->dados_item["instalacao_tipo"]), $this->conteudo);
			$this->conteudo = str_replace("[mensalidade_tipo]", $this->geraSelectValorTipos($this->valor_tipos, $this->dados_item["mensalidade_tipo"]), $this->conteudo);
			$this->conteudo = str_replace("[validade]", $this->geraSelectValidades($this->buscaValidades(), $this->dados_item["validade"]), $this->conteudo);
			$this->conteudo = str_replace("[data_inicio]", $this->dados_item["data_inicio"], $this->conteudo);
			$this->conteudo = str_replace("[data_termino]", $this->dados_item["data_termino"], $this->conteudo);
			$this->conteudo = str_replace("[instalacao_fixa]", ($dados_desconto["instalacao_fixa"] || $this->dados_item["status_id"] > 3) ? "disabled" : "", $this->conteudo);
			$this->conteudo = str_replace("[mensalidade_fixa]", ($dados_desconto["mensalidade_fixa"] || $this->dados_item["status_id"] > 3) ? "disabled" : "", $this->conteudo);
			$this->conteudo = str_replace("[validade_fixa]", ($dados_desconto["validade_fixa"] || $this->dados_item["status_id"] > 3) ? "disabled" : "", $this->conteudo);
			$this->conteudo = str_replace("[lista_comentarios]", $this->geraComentariosListagem($_GET["i"]), $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
		
		} else {
			
			if ($dados_desconto["instalacao_fixa"]) $_POST["instalacao_valor"] = $this->dados_item["instalacao_valor"];
			if ($dados_desconto["mensalidade_fixa"]) $_POST["mensalidade_valor"] = $this->dados_item["mensalidade_valor"];
			if ($dados_desconto["validade_fixa"]) $_POST["validade"] = $this->dados_item["validade"];

			if ($this->dados_item["status_id"] <= 3) {
				$status = $dados_desconto["auto_aprovado"] ? 2 : 1;
				if (!$this->descontoAutomatico($_POST["instalacao_valor"], $dados_desconto["instalacao_valor"])) $status = 1;
				if (!$this->descontoAutomatico($_POST["mensalidade_valor"], $dados_desconto["mensalidade_valor"])) $status = 1;
				if (!$this->validadeAutomatica($_POST["validade"], $dados_desconto["validade"])) $status = 1;
				if ($this->descontoExistente($_POST["desconto"], $_GET["con"], 'con')) $status = 1;
			} else {
				$status = $this->dados_item["status_id"];
			}

			$_POST["instalacao_valor"] = $this->corrigeDesconto($_POST["instalacao_valor"]);
			$_POST["mensalidade_valor"] = $this->corrigeDesconto($_POST["mensalidade_valor"]);
			$_POST["data_inicio"] = ($_POST["data_inicio"]) ? "'".substr($_POST["data_inicio"],6,4).substr($_POST["data_inicio"],3,2).substr($_POST["data_inicio"],0,2)."'" : "NULL";
			$_POST["data_termino"] = ($_POST["data_termino"]) ? "'".substr($_POST["data_termino"],6,4).substr($_POST["data_termino"],3,2).substr($_POST["data_termino"],0,2)."'" : "NULL";
			
			if (!$_POST["validade"]) {
				$_POST["validade"] = "NULL";
			}
			
			$this->bancodedados->query("
				UPDATE 
					tab_descontos_concessoes 
				SET 
					dec_instalacao = {$_POST["instalacao_valor"]}, 
					dec_mensalidade = {$_POST["mensalidade_valor"]}, 
					dec_validade = {$_POST["validade"]}, 
					dec_data_inicio = {$_POST["data_inicio"]}, 
					dec_data_termino = {$_POST["data_termino"]}, 
					dcs_id = $status 
				WHERE
					dec_id = {$_GET["i"]}");
			
			if ($_POST["comentarios"]) {
				$this->registraComentario($_GET["i"], $_POST["comentarios"], $_SESSION["usuario"]);
			}
			
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao, $this->dados_item["id"]);
			
			header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver&i=".$_GET["i"]);
		
		}
	
	}

	####################
	### Mudar Status ###
	####################

	function ativar() {
		
		$this->mudaStatus("5", "Ativo");
		
	}

	function aprovar_comercial() {
		
		$this->mudaStatus("2", "Aprovado");
		
	}

	function aprovar_financeiro() {
		
		$this->mudaStatus("2", "Aprovado");
		
	}

	function reprovar() {
		
		$this->mudaStatus("3", "Reprovado");
		
	}

	function cancelar() {
		
		$this->mudaStatus("4", "Cancelado");
		
	}

	function finalizar() {
		
		$this->mudaStatus("6", "Finalizado");
		
	}

	function mudaStatus($status, $acao) {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

				$this->conteudo .= $this->carregaArquivo("descontos_concessoes.confirmar.inc.php");
				
				$this->conteudo = str_replace("[acao]", $this->buscaDescricaoAcao($this->acao), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				$this->bancodedados->query("
					UPDATE 
						tab_descontos_concessoes 
					SET 
						dcs_id = $status, 
						dec_data_termino = IF($status = 6, CURDATE(), dec_data_termino) 
					WHERE 
					dec_id = {$_GET["i"]}");
				
				if ($this->dados_item["assinatura"]) {
					$this->atualizaAssinaturaVigencia($this->dados_item["assinatura"]["id"]);
				}
				
				// Registra Comentário
				$this->registraComentario($_GET["i"], "<b>Ação</b>: $acao".($_POST["comentario"] ? "<br><b>Comentário: </b> {$_POST["comentario"]}" : ""), $_SESSION["usuario"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
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
			
			if ($this->dados_item["status_id"] <= 4) {
				
				if (!$_POST) {

					$botoes = array();
					$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
					$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
					
					$this->conteudo .= $this->carregaArquivo("descontos_concessoes.ass.remover.inc.php");
					
					$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
					$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}", $this->conteudo);
				
				} else {
					
					$this->bancodedados->query("DELETE FROM rel_dec_cor_estacontido WHERE dec_id = {$_GET["i"]}");
					if ($this->dados_item["assinatura"]["contrato"]) {
						$this->bancodedados->query("UPDATE tab_contratos SET cor_sincronizado = 0 WHERE cor_id = {$this->dados_item["assinatura"]["contrato"]["id"]} AND crs_id IN (11, 12, 13, 14)");
					}
					$this->bancodedados->query("DELETE FROM tab_descontos_concessoes_comentarios WHERE dec_id = {$_GET["i"]}");
					$this->bancodedados->query("DELETE FROM tab_descontos_concessoes WHERE dec_id = {$_GET["i"]}");
					
					if ($this->dados_item["assinatura"]) {
						// Atualiza Vigência
						$this->atualizaAssinaturaVigencia($this->dados_item["assinatura"]["id"]);
						// Recalcula Descontos Assinaturas
						$this->recalculaDescontosConcessoes($this->dados_item["assinatura"]["id"]);
					}
					
					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
					
					$this->fechar();
					
				}
			
			} else {
				
				$contrato_assinatura = $this->buscaContratoAssinatura($this->dados_item["assinatura"]["id"]);
				
				$botoes = array();
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

				$this->conteudo .= $this->carregaArquivo("descontos_concessoes.ass.remover.contrato.inc.php");
				
				$this->conteudo = str_replace("[contrato]", $contrato_assinatura["codigo"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				
			}
			
		} else {
			$this->fechar();
		}
	}

	#######################
	### Listar Clientes ###
	#######################

	function listar_clientes() {
		
		if (!$_GET["f"] && !$_GET["p"]) {
			$_GET["f"]["status"] = "-3";
		}
		
		$filtro = $this->inicializaFiltroClientes($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;
		
		$this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=descontos_concessoes" id="descontos_concessoes.js"></script>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
		$this->conteudo .= $filtro->formulario;
		
		if ($_GET["f"] || $_GET["p"]) {
			
			$itens = $this->buscaItensClientes($filtro, $pagina);
			$paginacao = $this->geraPaginacao($filtro, $itens["linhas"], $pagina);
			$listagem = $this->geraListagemClientes($itens["itens"]);
			
			$this->conteudo .= '</td></tr></table>';
			$this->conteudo .= $paginacao->conteudo;
			$this->conteudo .= $listagem->conteudo;
			$this->conteudo .= $paginacao->conteudo;
			
		} else {
			$this->conteudo .= '</td></tr></table>';
		}
	
	}

	function inicializaFiltroClientes($valores = "") {
		
		$itens = array();
		
		// Cliente
		$itens[] = array ("tipo" => "input", "nome" => "cliente", "descricao" => "Cliente", "valor" => "", "where" => "(tab_clientes.cli_nome LIKE '%[valor]%')");
		
		// Condomínio
		$condominios = $this->buscaCondominios();
		$select_condominios = array();
		foreach ($condominios as $id => $nome) {
			$select_condominios[] = array ("id" => $id, "nome" => $nome);
		}
		$select_condominios[] = array ("id" => "-1", "nome" => "&raquo Sem Condomínio", "where" => "(tab_condominios_cliente.con_id IS NULL)");
		if (!$valores["condominio"]) $cliente_condominio_nulo = " OR tab_condominios.con_id IS NULL";
		$itens[] = array ("tipo" => "select", "nome" => "condominio", "descricao" => "Condomínio", "valor" => $select_condominios, "where" => "(tab_condominios.con_id = [valor]$cliente_condominio_nulo)");
		
		// Desconto
		$descontos = $this->buscaDescontosServicos();
		$select_descontos = array();
		foreach ($descontos as $id => $nome) {
			$select_descontos[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "desconto", "descricao" => "Desconto", "valor" => $select_descontos, "where" => "(tab_descontos_concessoes.des_id = [valor])");
		
		// Desconto Descricao
		$itens[] = array ("tipo" => "input", "nome" => "desconto_nome", "descricao" => "Desconto » Nome", "valor" => "", "where" => "(tab_descontos.des_nome LIKE '%[valor]%')");
		
		// Tipo
		$tipos = $this->buscaDescontosTipos();
		$select_tipos = array();
		foreach ($tipos as $id => $nome) {
			$select_tipos[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Desconto » Tipo", "valor" => $select_tipos, "where" => "(tab_descontos_tipos.det_id = [valor])");
		
		// Status
		if (!$this->permissoes[$this->dados_modulo["nome"]]["listar_todos"]) $valores["status"] = 1;
		$status = $this->buscaStatus();
		$select_status = array();
		foreach ($status as $id => $nome) {
			$select_status[] = array ("id" => $id, "nome" => str_replace("|", " / ", $nome));
		}
		$select_status[] = array ("id" => "-1", "nome" => "Análise", "where" => "(tab_descontos_concessoes.dcs_id IN (1, 2, 3, 4))");
		$select_status[] = array ("id" => "-2", "nome" => "Inativo", "where" => "(tab_descontos_concessoes.dcs_id IN (6, 7))");
		$select_status[] = array ("id" => "-3", "nome" => "Análise (Aguardado Comercial)", "where" => "(tab_descontos_concessoes.dcs_id = 1 AND tab_descontos_tipos.det_id IN (1, 2, 4))");
		$select_status[] = array ("id" => "-4", "nome" => "Análise (Aguardado Financeiro)", "where" => "(tab_descontos_concessoes.dcs_id = 1 AND tab_descontos_tipos.det_id = 3)");
		$itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Desconto » Status", "valor" => $select_status, "where" => "(tab_descontos_concessoes.dcs_id = [valor])", "disabled" => ($this->permissoes[$this->dados_modulo["nome"]]["listar_todos"])? false : true);
		
		// Cortesia
		if ($_GET["f"]["cortesia"] == 1) {
			if ($_GET["f"]["indeterminado"] == 1) {
				$query = "(ass_mensalidade - IF(dec_mensalidade > 1, dec_mensalidade, ass_mensalidade * dec_mensalidade) - IFNULL((SELECT SUM(IF(outros_descontos.dec_mensalidade > 1, outros_descontos.dec_mensalidade, ass_mensalidade * outros_descontos.dec_mensalidade)) FROM tab_descontos_concessoes outros_descontos WHERE outros_descontos.dcs_id IN (1, 2, 5) AND outros_descontos.dec_data_termino IS NULL AND outros_descontos.ass_id = tab_assinaturas.ass_id AND outros_descontos.dec_id != tab_descontos_concessoes.dec_id), 0)) <= 0";
			} elseif ($_GET["f"]["indeterminado"] == 2) {
				$query = "(ass_mensalidade - IF(dec_mensalidade > 1, dec_mensalidade, ass_mensalidade * dec_mensalidade) - IFNULL((SELECT SUM(IF(outros_descontos.dec_mensalidade > 1, outros_descontos.dec_mensalidade, ass_mensalidade * outros_descontos.dec_mensalidade)) FROM tab_descontos_concessoes outros_descontos WHERE outros_descontos.dcs_id IN (1, 2, 5) AND outros_descontos.dec_data_termino IS NOT NULL AND outros_descontos.ass_id = tab_assinaturas.ass_id AND outros_descontos.dec_id != tab_descontos_concessoes.dec_id), 0)) <= 0";
			} else {
				$query = "(ass_mensalidade - IF(dec_mensalidade > 1, dec_mensalidade, ass_mensalidade * dec_mensalidade) - IFNULL((SELECT SUM(IF(outros_descontos.dec_mensalidade > 1, outros_descontos.dec_mensalidade, ass_mensalidade * outros_descontos.dec_mensalidade)) FROM tab_descontos_concessoes outros_descontos WHERE outros_descontos.dcs_id IN (1, 2, 5) AND outros_descontos.ass_id = tab_assinaturas.ass_id AND outros_descontos.dec_id != tab_descontos_concessoes.dec_id), 0)) <= 0";
			}
		} elseif ($_GET["f"]["cortesia"] == 2) {
			if ($_GET["f"]["indeterminado"] == 1) {
				$query = "(ass_mensalidade - IF(dec_mensalidade > 1, dec_mensalidade, ass_mensalidade * dec_mensalidade) - IFNULL((SELECT SUM(IF(outros_descontos.dec_mensalidade > 1, outros_descontos.dec_mensalidade, ass_mensalidade * outros_descontos.dec_mensalidade)) FROM tab_descontos_concessoes outros_descontos WHERE outros_descontos.dcs_id IN (1, 2, 5) AND outros_descontos.dec_data_termino IS NULL AND outros_descontos.ass_id = tab_assinaturas.ass_id AND outros_descontos.dec_id != tab_descontos_concessoes.dec_id), 0)) > 0";
			} elseif ($_GET["f"]["indeterminado"] == 2) {
				$query = "(ass_mensalidade - IF(dec_mensalidade > 1, dec_mensalidade, ass_mensalidade * dec_mensalidade) - IFNULL((SELECT SUM(IF(outros_descontos.dec_mensalidade > 1, outros_descontos.dec_mensalidade, ass_mensalidade * outros_descontos.dec_mensalidade)) FROM tab_descontos_concessoes outros_descontos WHERE outros_descontos.dcs_id IN (1, 2, 5) AND outros_descontos.dec_data_termino NOT IS NULL AND outros_descontos.ass_id = tab_assinaturas.ass_id AND outros_descontos.dec_id != tab_descontos_concessoes.dec_id), 0)) > 0";
			} else {
				$query = "(ass_mensalidade - IF(dec_mensalidade > 1, dec_mensalidade, ass_mensalidade * dec_mensalidade) - IFNULL((SELECT SUM(IF(outros_descontos.dec_mensalidade > 1, outros_descontos.dec_mensalidade, ass_mensalidade * outros_descontos.dec_mensalidade)) FROM tab_descontos_concessoes outros_descontos WHERE outros_descontos.dcs_id IN (1, 2, 5) AND outros_descontos.ass_id = tab_assinaturas.ass_id AND outros_descontos.dec_id != tab_descontos_concessoes.dec_id), 0)) > 0";
			}
		} else {
			$query = null;
		}
		$itens[] = array ("tipo" => "select", "nome" => "cortesia", "descricao" => "Desconto » Cortesia", "valor" => array(array("id" => 1, "nome" => "Sim"), array("id" => 2, "nome" => "Não")), "where" => $query);
		
		// Indeterminado
		if ($_GET["f"]["indeterminado"] == 1) {
			$query = "(dec_validade IS NULL AND dec_data_termino IS NULL)";
		} elseif ($_GET["f"]["indeterminado"] == 2) {
			$query = "(dec_validade IS NOT NULL OR dec_data_termino IS NOT NULL)";
		} else {
			$query = null;
		}
		$itens[] = array ("tipo" => "select", "nome" => "indeterminado", "descricao" => "Desconto » Indeterminado", "valor" => array(array("id" => 1, "nome" => "Sim"), array("id" => 2, "nome" => "Não")), "where" => $query);
		
		return controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		
	}

	function buscaItensClientes($filtro, $pagina) {
		
		$itens = array();
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		
		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_descontos_concessoes.dec_id AS id,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes.cls_id AS cliente_status,
				tab_planos.pla_nome AS plano,
				tab_condominios.con_nome AS condominio_nome,
				tab_descontos.des_nome AS desconto,
				tab_descontos_tipos.det_nome AS tipo,
				tab_descontos_concessoes.dec_instalacao AS instalacao,
				tab_descontos_concessoes.dec_mensalidade AS mensalidade,
				IF(tab_descontos_concessoes.dec_data_termino IS NOT NULL, DATE_FORMAT(tab_descontos_concessoes.dec_data_termino, '%d/%m/%Y'), IF(tab_descontos_concessoes.dec_validade IS NOT NULL, CONCAT(tab_descontos_concessoes.dec_validade, ' mês(es)'), 'Indeterminada')) AS validade,
				tab_descontos_concessoes_status.dcs_nome AS status
			FROM
				tab_descontos_concessoes_status
				INNER JOIN tab_descontos_concessoes ON (tab_descontos_concessoes_status.dcs_id = tab_descontos_concessoes.dcs_id)
				INNER JOIN tab_assinaturas ON (tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_condominios_blocos.con_id = tab_condominios.con_id)
				INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
				INNER JOIN tab_descontos_tipos ON (tab_descontos.det_id = tab_descontos_tipos.det_id)
			WHERE
				$filtro->parametros_sql".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
			GROUP BY
				dec_id
			ORDER BY
				cliente_nome,
				condominio_nome
			LIMIT
				$limit_inicio,
				$limit_fim");
				
		$linhas = $this->bancodedados->linhasTotal($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
				$dados["link"] = "javascript:verDescontoConcessao('{$dados["id"]}');";
			}
			if ($this->permissoes["clientes"]["ver_dm"]) {
				$dados["cliente_id"] = "<a href=\"./?m=clientes&a=ver_dm&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
			}
			if ($dados["condominio_nome"]) {
				$dados["cliente_nome"] .= "<br>({$dados["condominio_nome"]})";
			}
			$dados["instalacao"] = $this->formataDesconto($dados["instalacao"]);
			$dados["mensalidade"] = $this->formataDesconto($dados["mensalidade"]);
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			$dados["plano"] = str_replace(array(" [", " ("), array("<br>[", "<br>("), $dados["plano"]);
			$dados["tipo"] = str_replace(" [", "<br>[", $dados["tipo"]);
			
			$itens[] = $dados;
		
		}
		
		return array("linhas" => $linhas, "itens" => $itens);
		
	}

	function geraListagemClientes($itens) {
		
		$cabecalho = array ("contador" => "#", "cliente_id" => "ID", "cliente_nome" => "Cliente (Condomínio)", "desconto" => "Desconto", "instalacao" => "Instalação", "mensalidade" => "Mensalidade", "validade" => "Validade", "plano" => "Plano", "tipo" => "Tipo", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20", "35"), null, array("right"));
		
		return $listagem;
		
	}

	##########################
	### Listar Condominios ###
	##########################

	function listar_condominios() {
		
		$filtro = $this->inicializaFiltroCondominios($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;
		
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
		$this->conteudo .= $filtro->formulario;
		
		if ($_GET["f"] || $_GET["p"]) {
			
			$itens = $this->buscaItensCondominios($filtro, $pagina);
			$paginacao = $this->geraPaginacao($filtro, $itens["linhas"], $pagina);
			$listagem = $this->geraListagemCondominios($itens["itens"]);
			
			$this->conteudo .= '</td></tr></table>';
			$this->conteudo .= $paginacao->conteudo;
			$this->conteudo .= $listagem->conteudo;
			$this->conteudo .= $paginacao->conteudo;
		
		} else {
			$this->conteudo .= '</td></tr></table>';
		}
		
	}

	function inicializaFiltroCondominios($valores = "") {
		
		$itens = array();
		
		// Condomínio - Nome
		$itens[] = array ("tipo" => "input", "nome" => "condominio", "descricao" => "Condomínio", "valor" => "", "where" => "(tab_condominios.con_nome LIKE '%[valor]%')");
		
		// Condomínio
		$condominios = $this->buscaCondominios();
		$select_condominios = array();
		foreach ($condominios as $id => $nome) {
			$select_condominios[] = array ("id" => $id, "nome" => $nome);
		}
		if (!$valores["condominio_nome"]) $condominio_nome_nulo = " OR tab_condominios.con_id IS NULL";
		$itens[] = array ("tipo" => "select", "nome" => "condominio_nome", "descricao" => "Condomínio", "valor" => $select_condominios, "where" => "(tab_condominios.con_id = [valor]$condominio_nome_nulo)");
		
		// Desconto
		$descontos = $this->buscaDescontosServicos();
		$select_descontos = array();
		foreach ($descontos as $id => $nome) {
			$select_descontos[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "desconto", "descricao" => "Desconto", "valor" => $select_descontos, "where" => "(tab_descontos_concessoes.des_id = [valor])");
		
		// Tipo
		$tipos = $this->buscaDescontosTipos();
		$select_tipos = array();
		foreach ($tipos as $id => $nome) {
			$select_tipos[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $select_tipos, "where" => "(tab_descontos_tipos.det_id = [valor])");
		
		// Status
		if (!$this->permissoes[$this->dados_modulo["nome"]]["listar_todos"]) $valores["status"] = 1;
		$status = $this->buscaStatus();
		$select_status = array();
		foreach ($status as $id => $nome) {
			$select_status[] = array ("id" => $id, "nome" => str_replace("|", " / ", $nome));
		}
		$select_status[] = array ("id" => "-1", "nome" => "Análise", "where" => "(tab_descontos_concessoes.dcs_id IN (1, 2, 3, 4))");
		$select_status[] = array ("id" => "-2", "nome" => "Inativo", "where" => "(tab_descontos_concessoes.dcs_id IN (6, 7))");
		$select_status[] = array ("id" => "-3", "nome" => "Análise (Aguardado Comercial)", "where" => "(tab_descontos_concessoes.dcs_id = 1 AND tab_descontos_tipos.det_id IN (1, 2))");
		$select_status[] = array ("id" => "-4", "nome" => "Análise (Aguardado Financeiro)", "where" => "(tab_descontos_concessoes.dcs_id = 1 AND tab_descontos_tipos.det_id = 3)");
		$itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_descontos_concessoes.dcs_id = [valor])", "disabled" => ($this->permissoes[$this->dados_modulo["nome"]]["listar_todos"])? false : true);
		
		return controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		
	}

	function buscaItensCondominios($filtro, $pagina) {
		
		$itens = array();
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		
		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_condominios.con_id AS condominio_id,
				tab_condominios.con_nome AS condominio_nome,
				tab_condominios.cos_id AS condominio_status,
				tab_descontos.des_nome AS desconto,
				tab_descontos_tipos.det_nome AS tipo,
				tab_descontos_concessoes.dec_instalacao AS instalacao,
				tab_descontos_concessoes.dec_mensalidade AS mensalidade,
				IF(tab_descontos_concessoes.dec_validade IS NOT NULL, CONCAT(tab_descontos_concessoes.dec_validade, ' mês(es)'), 'Indeterminada') AS validade,
				tab_descontos_concessoes_status.dcs_nome AS status
			FROM
				tab_condominios
				INNER JOIN tab_descontos_concessoes ON (tab_condominios.con_id = tab_descontos_concessoes.con_id)
				INNER JOIN tab_descontos_concessoes_status ON (tab_descontos_concessoes.dcs_id = tab_descontos_concessoes_status.dcs_id)
				INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
				INNER JOIN tab_descontos_tipos ON (tab_descontos_concessoes.det_id = tab_descontos_tipos.det_id)
			WHERE
				$filtro->parametros_sql
			GROUP BY
				dec_id
			ORDER BY
				condominio_nome
			LIMIT
				$limit_inicio,
				$limit_fim");
		
		$linhas = $this->bancodedados->linhasTotal($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes["condominios"]["ver_dm"]) {
				$dados["link"] = "?m=condominios&a=ver_dm&i={$dados["condominio_id"]}";
			}
			$dados["instalacao"] = $this->formataDesconto($dados["instalacao"]);
			$dados["mensalidade"] = $this->formataDesconto($dados["mensalidade"]);
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
		
			$itens[] = $dados;
		
		}
		
		return array("linhas" => $linhas, "itens" => $itens);
		
	}

	function geraListagemCondominios($itens) {
		
		$cabecalho = array ("contador" => "#", "condominio_nome" => "Condomínio", "desconto" => "Desconto", "instalacao" => "Instalação", "mensalidade" => "Mensalidade", "validade" => "Validade", "tipo" => "Tipo", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20"), null, array("right"));
		
		return $listagem;
		
	}

	###################
	### Comentários ###
	###################

	function registraComentario($id, $conteudo, $responsavel) {

		$responsavel = $responsavel ? "'$responsavel'" : "NULL";
		
		// Descomentar a linha abaixo para registrar o comentário na tabela tab_descontos_concessoes_comentarios
		$this->bancodedados->query("
			INSERT INTO 
				tab_descontos_concessoes_comentarios 
			SET
				dcc_datahora = NOW(), 
				dec_id = '$id', 
				dcc_conteudo = '$conteudo', 
				usu_id = $responsavel");
		
	}

	function buscaComentarios($id) {
		
		$comentarios = array();

		$query = $this->bancodedados->query("
			SELECT
				IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel,
				DATE_FORMAT(tab_descontos_concessoes_comentarios.dcc_datahora, '%d/%m/%Y (%H:%i)') AS data,
				tab_descontos_concessoes_comentarios.dcc_conteudo AS conteudo
			FROM
				tab_descontos_concessoes_comentarios
				LEFT OUTER JOIN tab_usuarios ON (tab_descontos_concessoes_comentarios.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_descontos_concessoes_comentarios.dec_id = $id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["detalhamento"] = "<b>{$dados["responsavel"]}</b> em <b>{$dados["data"]}</b><br>";
			$dados["detalhamento"] .= substr($dados["conteudo"], 0, 3) == "<b>" ? $dados["conteudo"] : "<b>Conteúdo:</b> {$dados["conteudo"]}";
			
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

	function buscaDescontosCondominios($condominio_id) {
		
		$descontos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_descontos_concessoes.dec_id AS id,
				tab_planos_servicos.pls_nome AS servico,
				tab_descontos.des_nome AS nome,
				tab_descontos.des_parametros AS parametros,
				tab_descontos_concessoes.dec_instalacao AS instalacao_atual,
				tab_descontos_concessoes.dec_mensalidade AS mensalidade_atual,
				tab_descontos_concessoes.dec_validade AS validade,
				tab_descontos_concessoes_status.dcs_nome AS status,
				tab_descontos_tipos.det_nome AS tipo
			FROM
				tab_planos_servicos
				INNER JOIN tab_descontos ON (tab_planos_servicos.pls_id = tab_descontos.pls_id)
				INNER JOIN tab_descontos_concessoes ON (tab_descontos.des_id = tab_descontos_concessoes.des_id)
				INNER JOIN tab_descontos_concessoes_status ON (tab_descontos_concessoes.dcs_id = tab_descontos_concessoes_status.dcs_id)
				INNER JOIN tab_descontos_tipos ON (tab_descontos.det_id = tab_descontos_tipos.det_id)
			WHERE
				tab_descontos_concessoes.con_id = $condominio_id
			ORDER BY
				status,
				nome");
				
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$parametros = unserialize($dados["parametros"]);
			$validades = $this->buscaValidades();
			
			if ($this->permissoes["descontos_concessoes"]["ver"]) {
				$dados["link"] = "javascript:verDescontoConcessao({$dados["id"]})";
			}
			$dados["instalacao"] = $this->formataDesconto($dados["instalacao_atual"]);
			$dados["mensalidade"] = $this->formataDesconto($dados["mensalidade_atual"]);
			if ($parametros["validade_tipo"] && $parametros["validade_tipo"] == 2) {
				$dados["validade"] = $parametros["validade"];
			} else {
				$dados["validade"] = !$parametros["validade"] ? "Indeterminada" : ($dados["validade"] ? $validades[$dados["validade"]] : $parametros["validade"]);
			}
			
			$descontos[] = $dados;
		
		}
		
		return $descontos;
		
	}

	function geraListagemDescontosCondominios($dados) {
		
		$cabecalho = array ("servico" => "Serviço", "nome" => "Nome", "tipo" => "Tipo", "instalacao" => "Instalação", "mensalidade" => "Mensalidade", "validade" => "Validade", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
		
		return $listagem->conteudo;
		
	}

	function buscaDescontosPlanos($plano_id) {
		
		$descontos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_descontos_concessoes.dec_id AS id,
				tab_planos_servicos.pls_nome AS servico,
				tab_descontos.des_nome AS nome,
				tab_descontos.des_parametros AS parametros,
				tab_descontos_concessoes.dec_instalacao AS instalacao_atual,
				tab_descontos_concessoes.dec_mensalidade AS mensalidade_atual,
				tab_descontos_concessoes.dec_validade AS validade,
				tab_descontos_concessoes_status.dcs_nome AS status,
				tab_descontos_tipos.det_nome AS tipo
			FROM
				tab_planos_servicos
				INNER JOIN tab_descontos ON (tab_planos_servicos.pls_id = tab_descontos.pls_id)
				INNER JOIN tab_descontos_concessoes ON (tab_descontos.des_id = tab_descontos_concessoes.des_id)
				INNER JOIN tab_descontos_concessoes_status ON (tab_descontos_concessoes.dcs_id = tab_descontos_concessoes_status.dcs_id)
				INNER JOIN tab_descontos_tipos ON (tab_descontos_tipos.det_id = tab_descontos.det_id)
			WHERE
				tab_descontos_concessoes.pla_id = $plano_id
			ORDER BY
				status,
				nome");
				
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$parametros = unserialize($dados["parametros"]);
			$validades = $this->buscaValidades();
			
			if ($this->permissoes["descontos_concessoes"]["ver"]) {
				$dados["link"] = "javascript:verDescontoConcessao({$dados["id"]})";
			}
			$dados["instalacao"] = $this->formataDesconto($dados["instalacao_atual"]);
			$dados["mensalidade"] = $this->formataDesconto($dados["mensalidade_atual"]);
			if ($parametros["validade_tipo"] && $parametros["validade_tipo"] == 2) {
				$dados["validade"] = $parametros["validade"];
			} else {
				$dados["validade"] = !$dados["validade"] ? "Indeterminada" : $validades[$dados["validade"]];
			}
		
			$descontos[] = $dados;
		
		}
		
		return $descontos;
	
	}

	function geraListagemDescontosPlanos($dados) {
		
		$cabecalho = array ("servico" => "Serviço", "nome" => "Nome", "tipo" => "Tipo", "instalacao" => "Instalação", "mensalidade" => "Mensalidade", "validade" => "Validade", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
		
		return $listagem->conteudo;
		
	}

}

?>
