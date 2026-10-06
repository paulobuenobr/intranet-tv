<?

class planos {

	var $bancodedados;
	var $permissoes;
	var $acao;
	var $conteudo;
	var $dados_item;
	var $dados_modulo;
	var $dados_acao;

	var $streaming = array(
		0 => "Não",
		1 => "Sim"
	);

	// Identificadores dos aplicativos disponíveis para os planos de IPTV.
	// Novos aplicativos devem ser adicionados como constante e listados em $apps.
	const APP_HBO_MAX = "HBO_MAX";

	var $apps = array(
		"HBO_MAX" => "HBO MAX"
	);

	var $comissoes_tipos = array(
		"1" => "Dinheiro",
		"2" => "Percentual"
	);
	
	var $comissoes_referencias = array(
		"1" => "Valor Original",
		"2" => "Valor Final"
	);

	var $comissoes_condicoes = array(
		">"  => "Maior que",
		">=" => "Maior ou igual a",
		"==" => "Igual a",
		"<"  => "Menor que",
		"<=" => "Menor ou igual a"
	);

	var $periodicidades = array(
		"1"  => "Mensal",
		"2"  => "Bimestral",
		"3"  => "Trimestral",
		"6"  => "Semestral",
		"12" => "Anual"
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

	########################
	### Busca Dados Item ###
	########################

	function buscaDadosItem($id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_planos.pla_id AS id,
				tab_planos.pla_nome AS nome,
				tab_planos.pla_instalacao AS instalacao,
				tab_planos.pla_mensalidade AS mensalidade,
				tab_planos.pla_parcelamento_maximo AS parcelamento_maximo,
				tab_planos.pla_parametros AS parametros,
				tab_planos.pla_tipo_contrato_principal AS tipo_contrato_principal,
				tab_planos.pla_tipo_migracao AS tipo_migracao,
				tab_planos_parceiro.pla_id AS parceiro_plano_id,
				tab_planos_parceiro.pla_nome AS parceiro_plano_nome,
				tab_planos.pla_parceiro_max AS parceiro_max,
				tab_planos_servicos.pls_id AS servico_id,
				tab_planos_servicos.pls_nome AS servico_nome,
				tab_planos_tipos.plt_id AS tipo_id,
				tab_planos_tipos.plt_nome AS tipo_nome,
				tab_planos_status.plu_id AS status_id,
				tab_planos_status.plu_nome AS status_nome,
				tab_servicos_ajustes_categorias.sac_id AS ajuste_categoria_id,
				tab_servicos_ajustes_categorias.sac_nome AS ajuste_categoria_nome,
				IF(tab_planos.pli_id IS NOT NULL, tab_planos_sistemas.pli_id, '-1') AS sistema_id,
				IF(tab_planos.pli_id IS NOT NULL, tab_planos_sistemas.pli_nome, 'Qualquer um') AS sistema_nome,
				tab_condominios.con_id AS condominio_id,
				tab_condominios.con_nome AS condominio_nome,
				tab_planos.gerar_nfse AS gerar_nfse,
				tab_planos.codigo_servico_id AS codigo_servico,
				tab_planos.aliquota AS aliquota,
				tab_planos.issRetido AS issRetido,
				tab_planos.cnae_id AS cnae,
				tab_planos.nbs AS codigo_nbs,
				tab_planos.pla_tipo_limitacao AS tipo_limitacao,
				tab_planos.pla_valor_max AS valor_max
			FROM
				tab_planos
				INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
				INNER JOIN tab_planos_tipos ON (tab_planos.plt_id = tab_planos_tipos.plt_id)
				INNER JOIN tab_planos_status ON (tab_planos.plu_id = tab_planos_status.plu_id)
				LEFT OUTER JOIN tab_planos_sistemas ON (tab_planos_sistemas.pli_id = tab_planos.pli_id)
				LEFT OUTER JOIN tab_servicos_ajustes_categorias ON (tab_planos.sac_id = tab_servicos_ajustes_categorias.sac_id)
				LEFT OUTER JOIN tab_condominios ON (tab_planos.con_id = tab_condominios.con_id)
				LEFT OUTER JOIN tab_planos tab_planos_parceiro ON (tab_planos_parceiro.pla_parceiro_plano_id = tab_planos.pla_id)
			WHERE
				tab_planos.pla_id = $id");
				
		if (($this->dados_item = $this->bancodedados->resultadoArray($query)) != false) {
			$this->dados_item["parametros"] = $this->trataParametrosSaida(unserialize($this->dados_item["parametros"]));
			$this->dados_item["vinculos"] = $this->buscaVinculos($id);
			$this->dados_item["multiservice"] = $this->buscaMultiservices($id);
			$this->dados_item["planos_limitacao"] = $this->buscaPlanosLimitacao($id);
			$this->dados_item["formas_pagamento"] = $this->buscaFormasPagamento($id);
			$this->dados_item["tipo_limitacao_descricao"] = $this->buscaTipoLimitacaoDescricao($this->dados_item["tipo_limitacao"]);
		}
		
	}

	###########################
	### Busca Multiservices ###
	###########################

	function buscaMultiservices($plano_id) {
		
		$multiservice = array();
		
		$query = $this->bancodedados->query("
			SELECT
				player AS player,
				servico AS servico
			FROM
				tab_planos_multiservice
			WHERE
				pla_id = $plano_id");
				
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$multiservice[] = $dados;
		}

		return $multiservice;

	}

	###############################
	### Busca Planos Limitacao ###
	###############################

	function buscaPlanosLimitacao($plano_id) {

		$planos_limitacao = array();

		$query = $this->bancodedados->query("
			SELECT
				id,
				plano AS plano,
				valor AS valor
			FROM
				tab_planos_limitacao
			WHERE
				pla_id = $plano_id
			ORDER BY
				id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$planos_limitacao[] = $dados;
		}

		return $planos_limitacao;

	}

	#####################
	### Busca Players ###
	#####################

	function buscaMultiservicePlayers() {

		$servicos = array();

		$query = $this->bancodedados->query("
			SELECT id, nome
			FROM tab_multiservice_players
			WHERE status = 1
			ORDER BY nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servicos[$dados["id"]] = $dados["nome"];
		}
		
		return $servicos;
		
	}
	
	#####################
	### Busca Players ###
	#####################

	function buscaMultiserviceServicos($id = false) {

		$servicos = array();
                $where = "";
                if($id != false) {
                    $where = "AND player = $id";
                }
		$query = $this->bancodedados->query("
			SELECT id, nome
			FROM tab_multiservice_servicos
			WHERE status = 1 $where
			ORDER BY nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servicos[$dados["id"]] = $dados["nome"];
		}
		
		return $servicos;
		
	}

	function buscaVinculos($plano_id) {
		
		$vinculos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				pls_id AS servico,
				pla_filho AS plano,
				pva_id AS aplicacao,
				pvt_id AS tipo
			FROM
				tab_planos_vinculos
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_planos_vinculos.pla_filho)
			WHERE
				tab_planos_vinculos.pla_id = $plano_id");
				
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$vinculos[] = $dados;
		}
		
		return $vinculos;
		
	}

	function buscaFormasPagamento($plano_id) {
		
		$formas_pagamento = array();
		
		$query = $this->bancodedados->query("
			SELECT
				fop_id AS id,
				fop_entrada AS entrada,
				fop_parcelas AS parcelas,
				fop_valor_parcela AS valor_parcela,
				fop_multa AS multa,
				fop_fidelidade AS fidelidade,
				fpc_id AS categoria,
				fpf_id AS forma
			FROM
				tab_formas_pagamento
			WHERE
				pla_id = $plano_id
			ORDER BY
				fpc_id,
				fop_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$formas_pagamento[] = $dados;
		}
		
		return $formas_pagamento;
		
	}

	function temAssinatura($id) {
		
		$query = $this->bancodedados->query("
			SELECT 
				count(ass_id) 
			FROM 
				tab_assinaturas 
			WHERE
				tab_assinaturas.pla_id = $id");
		
		return $this->bancodedados->resultado($query) > 0;
		
	}

	###################
	### Busca Itens ###
	###################

	function buscaServicos() {

		$servicos = array();

		$query = $this->bancodedados->query("
			SELECT
				pls_id AS id,
				pls_nome AS nome
			FROM
				tab_planos_servicos
			ORDER BY
				pls_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servicos[$dados["id"]] = $dados["nome"];
		}
		
		return $servicos;
		
	}

	function buscaTipos() {
		
		$tipos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				plt_id AS id,
				plt_nome AS nome
			FROM
				tab_planos_tipos
			ORDER BY
				plt_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$tipos[$dados["id"]] = $dados["nome"];
		}
		
		return $tipos;
		
	}

	function buscaAjustesCategorias() {
		
		$categorias = array();
		
		$query = $this->bancodedados->query("
			SELECT
				sac_id AS id,
				sac_nome AS nome
			FROM
				tab_servicos_ajustes_categorias
			ORDER BY
				nome");
			
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$categorias[$dados["id"]] = $dados["nome"];
		}
		
		return $categorias;
		
	}

	function buscaStatus() {
		
		$status = array();

		$query = $this->bancodedados->query("
			SELECT
				plu_id AS id,
				plu_nome AS nome
			FROM
				tab_planos_status
			ORDER BY
				plu_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$status[$dados["id"]] = $dados["nome"];
		}
		
		return $status;
		
	}

	function buscaOrigens() {

		$origens = array();

		$query = $this->bancodedados->query("
			SELECT
				clo_id AS id,
				clo_nome AS nome
			FROM
				tab_clientes_origens
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$origens[$dados["id"]] = $dados["nome"];
		}

		return $origens;

	}

	function buscaReferenciaComissaoDescricao($id) {
		
		return $this->comissoes_referencias[$id];
		
	}

	function buscaCondicaoComissaoDescricao($id) {

		return $this->comissoes_condicoes[$id];

	}

	function buscaTipoLimitacaoDescricao($id) {

		$tipos = array(
			1 => "Por Quantidade",
			2 => "Por Valor"
		);

		return isset($tipos[$id]) ? $tipos[$id] : "";

	}

	function buscaValor($valor = null) {
		
	    $valores = array();
	    
		if (!$valor) {
			$valores["valor"] = "";
			$valores["tipo"] = null;
		} elseif ($valor <= 1) {
			$valores["valor"] = intval($valor * 100)."%";
			$valores["tipo"] = 2;
		} else {
			$valores["valor"] = $this->formataDinheiro($valor);
			$valores["tipo"] = 1;
		}
		
		return $valores;
	
	}

	function buscaUnidades($unidade = "") {

		return array(
			"1" => "K".$unidade,
			"2" => "M".$unidade,
			"3" => "G".$unidade
		);

	}

        function arraySimNao() {

		return array(
			"0" => "Não",
			"1" => "Sim",
		);

	}

        function arrayIssRetido() {

		return array(
			"1" => "ISS Retido",
			"2" => "ISS não Retido",
		);

	}

	function buscaTamanho($tamanho) {
		
		$tamanhos = array(
			"tamanho" => $tamanho,
			"tipo" => 0
		);
		
		while ($tamanhos["tamanho"] > 1023) {
			$tamanhos["tamanho"] = $tamanhos["tamanho"] / 1024;
			$tamanhos["tipo"] += 1;
		}
		
		if ($tamanhos["tamanho"] == '0') {
			$tamanhos["tamanho"] = '';
		}
		
		return $tamanhos;
		
	}

        function buscaCodigoServico() {
            
            $query = $this->bancodedados->query("
                        SELECT 
                            id, cod_serv AS nome, descricao 
                        FROM 
                            tab_notas_servicos 
                        ORDER BY cod_serv");

			$servicos = array();
            while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
                
                if(strlen($dados['descricao']) > 150){
                    $servicos[$dados["id"]] = $dados["nome"] . " - " . substr($dados['descricao'], 0, 150) . "...";
                } else {
                    $servicos[$dados["id"]] = $dados["nome"] . " - " . $dados['descricao'];
                }
            }

            return $servicos;
        }
        
        function buscaCodigoCnae() {
            
            $query = $this->bancodedados->query("
                        SELECT 
                            id, cnae AS nome, descricao
                        FROM 
                            tab_notas_cnae 
                        ORDER BY cnae");

			$cnae = array();
            while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
                if(strlen($dados['descricao']) > 150){
                    $cnae[$dados["id"]] = $dados["nome"] . " - " . substr($dados['descricao'], 0, 150) . "...";
                } else {
                    $cnae[$dados["id"]] = $dados["nome"] . " - " . $dados['descricao'];
                }
            }   

            return $cnae;
        }
		function buscaCodigoNbs() {
			$query = $this->bancodedados->query("
				SELECT
					nbs_limpo AS id, nbs, descricao
				FROM
					tab_notas_nbs
				ORDER BY descricao");

			$nbs = array();
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				if(strlen($dados['descricao']) > 150){
					$nbs[$dados["id"]] =  $dados['nbs'] . " - " . substr($dados['descricao'], 0, 150) . "...";
				} else {
					$nbs[$dados["id"]] =  $dados['nbs'] . " - " . $dados['descricao'];
				}
			}
			return $nbs;
		}

	function buscaSistemasPagamento() {
		
		$sistemas = array(
			"-1" => "Qualquer um"
		);
		
		$query = $this->bancodedados->query("
			SELECT
				pli_id AS id,
				pli_nome AS nome
			FROM
				tab_planos_sistemas
			ORDER BY
				pli_nome");
				
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$sistemas[$dados["id"]] = $dados["nome"];
		}
		
		return $sistemas;
		
	}

	function buscaDescontosConcessoes($plano_id) {

		$descontos_concessoes = controle::instanciaObjeto("descontos_concessoes", $this->bancodedados, $this->permissoes, false);

		return $descontos_concessoes->geraListagemDescontosPlanos($descontos_concessoes->buscaDescontosPlanos($plano_id));

	}
  
	function buscaCondominios() {

		$condominios = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_condominios.con_id AS id,
				CONCAT(tab_condominios.con_nome, ' (', tab_cidades.cid_nome, ' / ', tab_bairros.bai_nome, ')') AS nome
			FROM
				tab_condominios
				INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$condominios[$dados["id"]] = $dados["nome"];
		}
		
		return $condominios;
		
	}
  
	function buscaParceiroPlanos($plano_id = null) {

		$planos = array();

		$query = $this->bancodedados->query("
			SELECT
				pla_id AS id,
				CONCAT(pls_nome, ' &raquo; ', pla_nome) AS nome
			FROM
				tab_planos
				INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
			WHERE
				".($plano_id ? "
				pla_id != {$plano_id} AND
				(pla_parceiro_plano_id IS NULL OR pla_parceiro_plano_id = {$plano_id})" : "
				pla_parceiro_plano_id IS NULL")."
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$planos[$dados["id"]] = $dados["nome"];
		}
		
		return $planos;
		
	}

	#############
	### Geral ###
	#############

	function geraSelect($itens, $selecionado = null, $item_nulo = false) {
		
		if ($item_nulo) {
			$select = "<option></option>";
		} else {
			$select = "";
		}
		
		if ($itens) {
			foreach ($itens as $valor => $item) {
				$select .= "<option value=\"$valor\"".(isset($selecionado) && (is_array($selecionado) ? in_array($valor, $selecionado) : $valor == $selecionado) ? " selected" : "").">$item</option>";
			}
		}
		
		return $select;
		
	}

	function geraCheckBoxes($nome, $itens, $selecionados = null, $desativados = null, $todos_desativados = false) {
		
		if (!$itens) {
			return "Nenhum registro disponível";
		}
		
		$i = 0;
		$check_boxes = array();
		
		foreach ($itens as $id => $descricao) {
			$check_boxes[] = "<input type=\"checkbox\" name=\"{$nome}[]\" value=\"$id\" id=\"{$nome}_$i\"".($selecionados && in_array($id, $selecionados) ? " checked" : "").($todos_desativados || ($desativados && in_array($id, $desativados)) ? " disabled" : "")."><label for=\"{$nome}_$i\"> $descricao</label><br>";
			if ($desativados && in_array($id, $desativados)) {
				$check_boxes[] = "<input type=\"hidden\" name=\"{$nome}[]\" value=\"$id\">";
			}
			$i++;
		}
		
		return implode("", $check_boxes);
		
	}

	function formataDinheiro($valor, $exibir_vazio = false, $moeda = 'R$') {
		
		$valor = preg_replace("/[^0-9]/", "", intval($valor));
		
		if ($valor || $exibir_vazio) {
			$valor = number_format($valor / 100, 2, ",", ".");
			if ($moeda) $valor = "$moeda $valor";
			return $valor;
		}
		
	}

	function buscaIptvPacotes($tipo) {

		$tipo = ($tipo === "CATALOGO") ? "CATALOGO" : "PACOTE";

		$itens = array();

		$query = $this->bancodedados->query("
			SELECT
				ipt_codigo,
				ipt_nome
			FROM
				tab_iptv_pacotes
			WHERE
				ipt_tipo = '$tipo'
			ORDER BY
				ipt_nome");

		while (($linha = $this->bancodedados->resultadoArray($query)) != false) {
			$itens[(string) $linha["ipt_codigo"]] = $linha["ipt_nome"];
		}

		return $itens;

	}

	function geraMultiSelectPacotes($nome, $itens, $selecionados, $largura = "350px", $size = 5) {

		$id = preg_replace("/\\[(.*)\\]/i", "_$1", $nome);
		$selecionados = is_array($selecionados) ? array_map("strval", $selecionados) : array();

		$html = '<select name="' . $nome . '" id="' . $id . '" class="inputBrancoAzulEscuro11" style="width:' . $largura . ';height:auto" multiple size="' . $size . '">';
		if ($itens) {
			foreach ($itens as $valor => $rotulo) {
				$sel = in_array((string) $valor, $selecionados, true) ? " selected" : "";
				$html .= '<option value="' . $valor . '"' . $sel . '>' . $rotulo . '</option>';
			}
		}
		$html .= '</select>';

		return $html;

	}

	// Gera uma lista rolável de checkboxes ($nome deve incluir [] para que o POST chegue como array).
	// Substitui multiselects (<select multiple>), que têm usabilidade ruim, mantendo o mesmo contrato de envio do form.
	// Inclui um master "Marcar todos" no topo, sempre visível (header fixo via flex), com estado
	// coerente: checked quando todos marcados, indeterminate quando parcial, unchecked quando nenhum.
	function geraCheckBoxesLista($nome, $itens, $selecionados, $largura = "350px", $altura = "110px") {

		static $jsEmitido = false;

		$id = preg_replace("/\\[(.*)\\]/i", "_$1", $nome);
		$selecionados = is_array($selecionados) ? array_map("strval", $selecionados) : array();

		$itensCount = is_array($itens) ? count($itens) : 0;
		$marcadosCount = 0;
		if ($itens) {
			foreach ($itens as $valor => $rotulo) {
				if (in_array((string) $valor, $selecionados, true)) $marcadosCount++;
			}
		}
		$todosMarcados = $itensCount > 0 && $marcadosCount === $itensCount;
		$indeterminate = $marcadosCount > 0 && !$todosMarcados;

		// Emite as funções JS uma única vez por request (compartilhadas entre todas as listas da página).
		$script = "";
		if (!$jsEmitido) {
			$jsEmitido = true;
			$script = '<script type="text/javascript">'
				. 'function marcarTodosCheckBoxesLista(masterEl){'
					. 'var d=masterEl.closest("div.cb-lista-wrapper");'
					. 'if(!d)return;'
					. 'var es=d.querySelectorAll(\'input[type="checkbox"][name]\');'
					. 'for(var i=0;i<es.length;i++)es[i].checked=masterEl.checked;'
					. 'masterEl.indeterminate=false;'
				. '}'
				. 'function atualizaMasterCheckBoxesLista(itemEl){'
					. 'var d=itemEl.closest("div.cb-lista-wrapper");'
					. 'if(!d)return;'
					. 'var m=d.querySelector("input.cb-lista-master");'
					. 'if(!m)return;'
					. 'var es=d.querySelectorAll(\'input[type="checkbox"][name]\');'
					. 'var todos=es.length>0,algum=false;'
					. 'for(var i=0;i<es.length;i++){if(es[i].checked)algum=true;else todos=false;}'
					. 'm.checked=todos;'
					. 'm.indeterminate=algum&&!todos;'
				. '}'
				. '</script>';
		}

		$masterId = $id . "_marcartodos";
		$masterChecked = $todosMarcados ? " checked" : "";

		$html = $script;
		$html .= '<div id="' . $id . '" class="inputBrancoAzulEscuro11 cb-lista-wrapper" style="width:' . $largura . ';height:' . $altura . ';background-color:#FFFFFF;border:1px solid #506478;box-sizing:border-box;display:flex;flex-direction:column">';

		if ($itensCount) {
			$html .= '<div style="padding:3px;background-color:#E8E8E8;border-bottom:1px solid #506478;line-height:18px;flex:0 0 auto">';
			$html .= '<label style="display:block;cursor:pointer;font-weight:bold;line-height:18px;margin:0">';
			$html .= '<input type="checkbox" class="cb-lista-master" id="' . $masterId . '" onclick="marcarTodosCheckBoxesLista(this)"' . $masterChecked . ' style="vertical-align:middle;margin:0 4px 0 0">';
			$html .= ' Marcar todos</label>';
			$html .= '</div>';

			$html .= '<div style="overflow:auto;padding:3px;flex:1 1 auto;min-height:0">';
			foreach ($itens as $valor => $rotulo) {
				$checked = in_array((string) $valor, $selecionados, true) ? " checked" : "";
				$html .= '<label style="display:block;cursor:pointer;font-weight:normal;line-height:18px"><input type="checkbox" name="' . $nome . '" value="' . $valor . '"' . $checked . ' onchange="atualizaMasterCheckBoxesLista(this)" style="vertical-align:middle;margin:0 4px 0 0"> ' . $rotulo . '</label>';
			}
			$html .= '</div>';
		}
		$html .= '</div>';

		// indeterminate só pode ser setado via JS (não é atributo HTML).
		if ($indeterminate) {
			$html .= '<script type="text/javascript">document.getElementById(' . json_encode($masterId) . ').indeterminate=true;</script>';
		}

		return $html;

	}

	function trataParametrosEntrada(&$dados) {
	    
	    $parametros = array();

		$parametros["aplicavel"] = $dados["aplicavel"] ? $dados["aplicavel"] : array();
		$parametros["origens"] = $dados["origens"] ? $dados["origens"] : array();
		
		// Periodicidade
		$parametros["periodicidade"] = intval($dados["periodicidade"]);
		
		// IPTV
		// Pacotes IPTV: array de IDs (strings) validados contra tab_iptv_pacotes (tipo PACOTE).
		// array_keys pode devolver ints quando o ipt_codigo for numérico (PHP normaliza chaves de string numérica
		// para int). Aplicamos strval para manter a comparação estrita coerente com o que vem do POST (string).
		$parametros["iptv_pacotes"] = array();
		if (isset($dados["iptv_pacotes"]) && is_array($dados["iptv_pacotes"])) {
			$pacotes_iptv_validos = array_map("strval", array_keys($this->buscaIptvPacotes("PACOTE")));
			foreach ($dados["iptv_pacotes"] as $pacote_id) {
				$pacote_id = trim((string) $pacote_id);
				if (strlen($pacote_id) && in_array($pacote_id, $pacotes_iptv_validos, true) && !in_array($pacote_id, $parametros["iptv_pacotes"], true)) {
					$parametros["iptv_pacotes"][] = $pacote_id;
				}
			}
		}
		$parametros["iptv_stb_quantidade"] = intval(preg_replace("/[^0-9]/", "", $dados["iptv_stb_quantidade"]));
		$parametros["iptv_aplicativos_quantidade"] = intval(preg_replace("/[^0-9]/", "", $dados["iptv_aplicativos_quantidade"]));
		$parametros["iptv_gravacao_horas"] = intval(preg_replace("/[^0-9]/", "", $dados["iptv_gravacao_horas"]));
		$parametros["iptv_streaming"] = intval(preg_replace("/[^0-9]/", "", $dados["iptv_streaming"]));

		// IPTV - Apps disponíveis no plano (array de IDs validados contra $this->apps)
		$parametros["apps"] = array();
		if (isset($dados["apps"]) && is_array($dados["apps"])) {
			$apps_validos = array_keys($this->apps);
			foreach ($dados["apps"] as $app_id) {
				$app_id = (string) $app_id;
				if (in_array($app_id, $apps_validos, true) && !in_array($app_id, $parametros["apps"], true)) {
					$parametros["apps"][] = $app_id;
				}
			}
		}

		// VOD
		// Pacotes VOD: array de IDs (strings) validados contra tab_iptv_pacotes (tipo CATALOGO).
		// Mesmo ajuste de strval explicado acima para iptv_pacotes.
		$parametros["vod_pacotes"] = array();
		if (isset($dados["vod_pacotes"]) && is_array($dados["vod_pacotes"])) {
			$pacotes_vod_validos = array_map("strval", array_keys($this->buscaIptvPacotes("CATALOGO")));
			foreach ($dados["vod_pacotes"] as $pacote_id) {
				$pacote_id = trim((string) $pacote_id);
				if (strlen($pacote_id) && in_array($pacote_id, $pacotes_vod_validos, true) && !in_array($pacote_id, $parametros["vod_pacotes"], true)) {
					$parametros["vod_pacotes"][] = $pacote_id;
				}
			}
		}
		$parametros["vod_telas"] = intval(preg_replace("/[^0-9]/", "", $dados["vod_telas"]));
		
		// Pontos
		$parametros["pontos_quantidade"] = intval($dados["pontos_quantidade"]);
		$parametros["pontos_velocidade_download"] = $dados["pontos_velocidade_download"] * pow(1024, $dados["pontos_velocidade_download_unidade"]);
		$parametros["pontos_velocidade_upload"] = $dados["pontos_velocidade_upload"] * pow(1024, $dados["pontos_velocidade_upload_unidade"]);
		
		// E-mails
		$parametros["emails_quantidade"] = intval($dados["emails_quantidade"]);
		$parametros["emails_capacidade"] = $dados["emails_capacidade"] * pow(1024, $dados["emails_capacidade_unidade"]);
		
		// Hospedagens
		$parametros["hospedagens_quantidade"] = intval($dados["hospedagens_quantidade"]);
		$parametros["hospedagens_capacidade"] = $dados["hospedagens_capacidade"] * pow(1024, $dados["hospedagens_capacidade_unidade"]);
		$parametros["hospedagens_transferencia"] = $dados["hospedagens_transferencia"] * pow(1024, $dados["hospedagens_transferencia_unidade"]);
		
		// Bases de Dados
		$parametros["basesdedados_quantidade"] = intval($dados["basesdedados_quantidade"]);
		$parametros["basesdedados_capacidade"] = $dados["basesdedados_capacidade"] * pow(1024, $dados["basesdedados_capacidade_unidade"]);
		
		// Opções
		$parametros["desconto_condominio"] = $dados["desconto_condominio"] == "on" ? 1 : 0;
		$parametros["aplica_pessoa_fisica"] = $dados["aplica_pessoa_fisica"] == "on" ? 1 : 0;
		$parametros["aplica_pessoa_juridica"] = $dados["aplica_pessoa_juridica"] == "on" ?  1 : 0;
		$parametros["aplica_com_condominio"] = $dados["aplica_com_condominio"] == "on" ? 1 : 0;
		$parametros["aplica_sem_condominio"] = $dados["aplica_sem_condominio"] == "on" ? 1 : 0;
		
		// ole total
		$parametros["ole_total"] = $dados["ole_total"] ? true : false;
		$parametros["valor_comodato"] = intval(preg_replace("/[^0-9]/", "", $dados["valor_comodato"]));
		
		// Comissões Instalação
		$parametros["comissoes"]["instalacao_referencia"] = $dados["instalacao_referencia"];
		$parametros["comissoes"]["instalacao_responsabilidade_interno"] = ($dados["instalacao_responsabilidade_interno_tipo"] == 2) ? round(preg_replace("/[^0-9]/", "", $dados["instalacao_responsabilidade_interno"]) / 100, 2) : intval(preg_replace("/[^0-9]/", "", $dados["instalacao_responsabilidade_interno"]));
		$parametros["comissoes"]["instalacao_responsabilidade_externo"] = ($dados["instalacao_responsabilidade_externo_tipo"] == 2) ? round(preg_replace("/[^0-9]/", "", $dados["instalacao_responsabilidade_externo"]) / 100, 2) : intval(preg_replace("/[^0-9]/", "", $dados["instalacao_responsabilidade_externo"]));
		$parametros["comissoes"]["instalacao_responsabilidade_ativo"] = ($dados["instalacao_responsabilidade_ativo_tipo"] == 2) ? round(preg_replace("/[^0-9]/", "", $dados["instalacao_responsabilidade_ativo"]) / 100, 2) : intval(preg_replace("/[^0-9]/", "", $dados["instalacao_responsabilidade_ativo"]));
		$parametros["comissoes"]["instalacao_responsabilidade_parceiro"] = ($dados["instalacao_responsabilidade_parceiro_tipo"] == 2) ? round(preg_replace("/[^0-9]/", "", $dados["instalacao_responsabilidade_parceiro"]) / 100, 2) : intval(preg_replace("/[^0-9]/", "", $dados["instalacao_responsabilidade_parceiro"]));
		$parametros["comissoes"]["instalacao_indicacao"] = ($dados["instalacao_indicacao_tipo"] == 2) ? round(preg_replace("/[^0-9]/", "", $dados["instalacao_indicacao"]) / 100, 2) : intval(preg_replace("/[^0-9]/", "", $dados["instalacao_indicacao"]));
		$parametros["comissoes"]["instalacao_bonificacao"] = ($dados["instalacao_bonificacao_tipo"] == 2) ? round(preg_replace("/[^0-9]/", "", $dados["instalacao_bonificacao"]) / 100, 2) : intval(preg_replace("/[^0-9]/", "", $dados["instalacao_bonificacao"]));
		$parametros["comissoes"]["instalacao_bonificacao_filtro"] = intval(preg_replace("/[^0-9]/", "", $dados["instalacao_bonificacao_filtro"]));
		$parametros["comissoes"]["instalacao_bonificacao_condicao"] = $dados["instalacao_bonificacao_condicao"];
		
		// Comissões Mensalidade
		$parametros["comissoes"]["mensalidade_referencia"] = $dados["mensalidade_referencia"];
		$parametros["comissoes"]["mensalidade_responsabilidade_interno"] = ($dados["mensalidade_responsabilidade_interno_tipo"] == 2) ? round(preg_replace("/[^0-9]/", "", $dados["mensalidade_responsabilidade_interno"]) / 100, 2) : intval(preg_replace("/[^0-9]/", "", $dados["mensalidade_responsabilidade_interno"]));
		$parametros["comissoes"]["mensalidade_responsabilidade_externo"] = ($dados["mensalidade_responsabilidade_externo_tipo"] == 2) ? round(preg_replace("/[^0-9]/", "", $dados["mensalidade_responsabilidade_externo"]) / 100, 2) : intval(preg_replace("/[^0-9]/", "", $dados["mensalidade_responsabilidade_externo"]));
		$parametros["comissoes"]["mensalidade_responsabilidade_ativo"] = ($dados["mensalidade_responsabilidade_ativo_tipo"] == 2) ? round(preg_replace("/[^0-9]/", "", $dados["mensalidade_responsabilidade_ativo"]) / 100, 2) : intval(preg_replace("/[^0-9]/", "", $dados["mensalidade_responsabilidade_ativo"]));
		$parametros["comissoes"]["mensalidade_responsabilidade_parceiro"] = ($dados["mensalidade_responsabilidade_parceiro_tipo"] == 2) ? round(preg_replace("/[^0-9]/", "", $dados["mensalidade_responsabilidade_parceiro"]) / 100, 2) : intval(preg_replace("/[^0-9]/", "", $dados["mensalidade_responsabilidade_parceiro"]));
		$parametros["comissoes"]["mensalidade_indicacao"] = ($dados["mensalidade_indicacao_tipo"] == 2) ? round(preg_replace("/[^0-9]/", "", $dados["mensalidade_indicacao"]) / 100, 2) : intval(preg_replace("/[^0-9]/", "", $dados["mensalidade_indicacao"]));
		$parametros["comissoes"]["mensalidade_bonificacao"] = ($dados["mensalidade_bonificacao_tipo"] == 2) ? round(preg_replace("/[^0-9]/", "", $dados["mensalidade_bonificacao"]) / 100, 2) : intval(preg_replace("/[^0-9]/", "", $dados["mensalidade_bonificacao"]));
		$parametros["comissoes"]["mensalidade_bonificacao_filtro"] = intval(preg_replace("/[^0-9]/", "", $dados["mensalidade_bonificacao_filtro"]));
		$parametros["comissoes"]["mensalidade_bonificacao_condicao"] = $dados["mensalidade_bonificacao_condicao"];
		
		// Premiação
		$parametros["comissoes"]["premiacao_pontos"] = round($dados["premiacao_pontos"], 2);
		
		// Retorna Parametros Serializados
		return serialize($parametros);
		
	}

	function trataParametrosSaida($dados) {
	  
		// Pacotes IPTV: sempre devolvidos como array de IDs (strings normalizadas), prontos para o multiselect.
		// Faz trim para sanear planos antigos que acumularam espaços antes da migração para multiselect.
		$dados["iptv_pacotes"] = is_array($dados["iptv_pacotes"]) ? array_values(array_filter(array_map("trim", array_map("strval", $dados["iptv_pacotes"])), "strlen")) : array();
		$dados["iptv_stb_quantidade"] = $dados["iptv_stb_quantidade"] ? $dados["iptv_stb_quantidade"] : "";
		$dados["iptv_aplicativos_quantidade"] = $dados["iptv_aplicativos_quantidade"] ? $dados["iptv_aplicativos_quantidade"] : "";
		$dados["iptv_gravacao_horas"] = $dados["iptv_gravacao_horas"] ? $dados["iptv_gravacao_horas"] : "";

		// Garante o atributo "apps" para planos antigos que não possuem o parâmetro.
		$dados["apps"] = (isset($dados["apps"]) && is_array($dados["apps"])) ? $dados["apps"] : array();

		// Pacotes VOD: mesmo tratamento dos pacotes IPTV.
		$dados["vod_pacotes"] = is_array($dados["vod_pacotes"]) ? array_values(array_filter(array_map("trim", array_map("strval", $dados["vod_pacotes"])), "strlen")) : array();
		$dados["vod_telas"] = $dados["vod_telas"] ? $dados["vod_telas"] : "";

		$dados["pontos_quantidade"] = $dados["pontos_quantidade"] ? $dados["pontos_quantidade"] : "";
		$dados["emails_quantidade"] = $dados["emails_quantidade"] ? $dados["emails_quantidade"] : "";
		$dados["hospedagens_quantidade"] = $dados["hospedagens_quantidade"] ? $dados["hospedagens_quantidade"] : "";
		$dados["basesdedados_quantidade"] = $dados["basesdedados_quantidade"] ? $dados["basesdedados_quantidade"] : "";

		return $dados;

	}

	##########################
	### Listagem Comissões ###
	##########################

	function buscaComissoesListagem($dados = array(), $editavel = true) {
		
		$dados = !$dados ? array() : $dados;
		$instalacoes = array();
		$mensalidades = array();
		$comissoes = array();
		
		// Instalação - Referência
		$instalacoes[0]["tipo"] = '<b>&nbsp;&raquo; Referência:</b>';
		$instalacoes[0]["valores"] = $editavel ? '<select name="instalacao_referencia" class="inputBrancoAzulEscuro11" style="width:173">[instalacao_referencia]</select>' : "[instalacao_referencia]";
		
		// Mensalidade - Referência
		$mensalidades[0]["tipo"] = '<b>&nbsp;&raquo; Referência:</b>';
		$mensalidades[0]["valores"] = $editavel ? '<select name="mensalidade_referencia" class="inputBrancoAzulEscuro11" style="width:173">[mensalidade_referencia]</select>' : "[mensalidade_referencia]";
		
		// Instalação - Vendedor Interno
		$instalacoes[1]["tipo"] = '<b>&nbsp;&raquo; Comissão » Vend. Interno:</b>';
		$instalacoes[1]["valores"] = $editavel ? '<select name="instalacao_responsabilidade_interno_tipo" id="instalacao_responsabilidade_interno_tipo" class="inputBrancoAzulEscuro11" style="width:109" onChange="mudaComissaoValorTipo(this)">[instalacao_responsabilidade_interno_tipo]</select>&nbsp;<input name="instalacao_responsabilidade_interno" id="instalacao_responsabilidade_interno" type="text" class="inputBrancoAzulEscuro11" style="width:60" onKeyPress="return formataComissao(this, event, this.form.instalacao_responsabilidade_interno_tipo.value)" onBlur="return formataComissao(this, event, this.form.instalacao_responsabilidade_interno_tipo.value)" value="[instalacao_responsabilidade_interno]">' : "[instalacao_responsabilidade_interno]";
		
		// Mensalidade - Vendedor Interno
		$mensalidades[1]["tipo"] = '<b>&nbsp;&raquo; Comissão » Vend. Interno:</b>';
		$mensalidades[1]["valores"] = $editavel ? '<select name="mensalidade_responsabilidade_interno_tipo" id="mensalidade_responsabilidade_interno_tipo" class="inputBrancoAzulEscuro11" style="width:109" onChange="mudaComissaoValorTipo(this)">[mensalidade_responsabilidade_interno_tipo]</select>&nbsp;<input name="mensalidade_responsabilidade_interno" id="mensalidade_responsabilidade_interno" type="text" class="inputBrancoAzulEscuro11" style="width:60" onKeyPress="return formataComissao(this, event, this.form.mensalidade_responsabilidade_interno_tipo.value)" onBlur="return formataComissao(this, event, this.form.mensalidade_responsabilidade_interno_tipo.value)" value="[mensalidade_responsabilidade_interno]">' : "[mensalidade_responsabilidade_interno]";
		
		// Instalação - Vendedor Externo
		$instalacoes[2]["tipo"] = '<b>&nbsp;&raquo; Comissão » Vend. Externo:</b>';
		$instalacoes[2]["valores"] = $editavel ? '<select name="instalacao_responsabilidade_externo_tipo" id="instalacao_responsabilidade_externo_tipo" class="inputBrancoAzulEscuro11" style="width:109" onChange="mudaComissaoValorTipo(this)">[instalacao_responsabilidade_externo_tipo]</select>&nbsp;<input name="instalacao_responsabilidade_externo" id="instalacao_responsabilidade_externo" type="text" class="inputBrancoAzulEscuro11" style="width:60" onKeyPress="return formataComissao(this, event, this.form.instalacao_responsabilidade_externo_tipo.value)" onBlur="return formataComissao(this, event, this.form.instalacao_responsabilidade_externo_tipo.value)" value="[instalacao_responsabilidade_externo]">' : "[instalacao_responsabilidade_externo]";
		
		// Mensalidade - Vendedor Externo
		$mensalidades[2]["tipo"] = '<b>&nbsp;&raquo; Comissão » Vend. Externo:</b>';
		$mensalidades[2]["valores"] = $editavel ? '<select name="mensalidade_responsabilidade_externo_tipo" id="mensalidade_responsabilidade_externo_tipo" class="inputBrancoAzulEscuro11" style="width:109" onChange="mudaComissaoValorTipo(this)">[mensalidade_responsabilidade_externo_tipo]</select>&nbsp;<input name="mensalidade_responsabilidade_externo" id="mensalidade_responsabilidade_externo" type="text" class="inputBrancoAzulEscuro11" style="width:60" onKeyPress="return formataComissao(this, event, this.form.mensalidade_responsabilidade_externo_tipo.value)" onBlur="return formataComissao(this, event, this.form.mensalidade_responsabilidade_externo_tipo.value)" value="[mensalidade_responsabilidade_externo]">' : "[mensalidade_responsabilidade_externo]";
		
		// Instalação - Vendedor Ativo
		$instalacoes[3]["tipo"] = '<b>&nbsp;&raquo; Comissão » Vend. Ativo:</b>';
		$instalacoes[3]["valores"] = $editavel ? '<select name="instalacao_responsabilidade_ativo_tipo" id="instalacao_responsabilidade_ativo_tipo" class="inputBrancoAzulEscuro11" style="width:109" onChange="mudaComissaoValorTipo(this)">[instalacao_responsabilidade_ativo_tipo]</select>&nbsp;<input name="instalacao_responsabilidade_ativo" id="instalacao_responsabilidade_ativo" type="text" class="inputBrancoAzulEscuro11" style="width:60" onKeyPress="return formataComissao(this, event, this.form.instalacao_responsabilidade_ativo_tipo.value)" onBlur="return formataComissao(this, event, this.form.instalacao_responsabilidade_ativo_tipo.value)" value="[instalacao_responsabilidade_ativo]">' : "[instalacao_responsabilidade_ativo]";
		
		// Mensalidade - Vendedor Ativo
		$mensalidades[3]["tipo"] = '<b>&nbsp;&raquo; Comissão » Vend. Ativo:</b>';
		$mensalidades[3]["valores"] = $editavel ? '<select name="mensalidade_responsabilidade_ativo_tipo" id="mensalidade_responsabilidade_ativo_tipo" class="inputBrancoAzulEscuro11" style="width:109" onChange="mudaComissaoValorTipo(this)">[mensalidade_responsabilidade_ativo_tipo]</select>&nbsp;<input name="mensalidade_responsabilidade_ativo" id="mensalidade_responsabilidade_ativo" type="text" class="inputBrancoAzulEscuro11" style="width:60" onKeyPress="return formataComissao(this, event, this.form.mensalidade_responsabilidade_ativo_tipo.value)" onBlur="return formataComissao(this, event, this.form.mensalidade_responsabilidade_ativo_tipo.value)" value="[mensalidade_responsabilidade_ativo]">' : "[mensalidade_responsabilidade_ativo]";
		
		// Instalação - Vendedor Ativo
		$instalacoes[4]["tipo"] = '<b>&nbsp;&raquo; Comissão » Agente:</b>';
		$instalacoes[4]["valores"] = $editavel ? '<select name="instalacao_responsabilidade_parceiro_tipo" id="instalacao_responsabilidade_parceiro_tipo" class="inputBrancoAzulEscuro11" style="width:109" onChange="mudaComissaoValorTipo(this)">[instalacao_responsabilidade_parceiro_tipo]</select>&nbsp;<input name="instalacao_responsabilidade_parceiro" id="instalacao_responsabilidade_parceiro" type="text" class="inputBrancoAzulEscuro11" style="width:60" onKeyPress="return formataComissao(this, event, this.form.instalacao_responsabilidade_parceiro_tipo.value)" onBlur="return formataComissao(this, event, this.form.instalacao_responsabilidade_parceiro_tipo.value)" value="[instalacao_responsabilidade_parceiro]">' : "[instalacao_responsabilidade_parceiro]";
		
		// Mensalidade - Vendedor parceiro
		$mensalidades[4]["tipo"] = '<b>&nbsp;&raquo; Comissão » Agente:</b>';
		$mensalidades[4]["valores"] = $editavel ? '<select name="mensalidade_responsabilidade_parceiro_tipo" id="mensalidade_responsabilidade_parceiro_tipo" class="inputBrancoAzulEscuro11" style="width:109" onChange="mudaComissaoValorTipo(this)">[mensalidade_responsabilidade_parceiro_tipo]</select>&nbsp;<input name="mensalidade_responsabilidade_parceiro" id="mensalidade_responsabilidade_parceiro" type="text" class="inputBrancoAzulEscuro11" style="width:60" onKeyPress="return formataComissao(this, event, this.form.mensalidade_responsabilidade_parceiro_tipo.value)" onBlur="return formataComissao(this, event, this.form.mensalidade_responsabilidade_parceiro_tipo.value)" value="[mensalidade_responsabilidade_parceiro]">' : "[mensalidade_responsabilidade_parceiro]";
		
		// Instalação - Sub-Comissão
		$instalacoes[5]["tipo"] = '<b>&nbsp;&raquo; Sub-Comissão:</b>';
		$instalacoes[5]["valores"] = $editavel ? '<select name="instalacao_indicacao_tipo" id="instalacao_indicacao_tipo" class="inputBrancoAzulEscuro11" style="width:109" onChange="mudaComissaoValorTipo(this)">[instalacao_indicacao_tipo]</select>&nbsp;<input name="instalacao_indicacao" id="instalacao_indicacao" type="text" class="inputBrancoAzulEscuro11" style="width:60" onKeyPress="return formataComissao(this, event, this.form.instalacao_indicacao_tipo.value)" onBlur="return formataComissao(this, event, this.form.instalacao_indicacao_tipo.value)" value="[instalacao_indicacao]">' : "[instalacao_indicacao]";
		
		// Mensalidade - Sub-Comissão
		$mensalidades[5]["tipo"] = '<b>&nbsp;&raquo; Sub-Comissão:</b>';
		$mensalidades[5]["valores"] = $editavel ? '<select name="mensalidade_indicacao_tipo" id="mensalidade_indicacao_tipo" class="inputBrancoAzulEscuro11" style="width:109" onChange="mudaComissaoValorTipo(this)">[mensalidade_indicacao_tipo]</select>&nbsp;<input name="mensalidade_indicacao" id="mensalidade_indicacao" type="text" class="inputBrancoAzulEscuro11" style="width:60" onKeyPress="return formataComissao(this, event, this.form.mensalidade_indicacao_tipo.value)" onBlur="return formataComissao(this, event, this.form.mensalidade_indicacao_tipo.value)" value="[mensalidade_indicacao]">' : "[mensalidade_indicacao]";
		
		// Instalação - Bonificação
		$instalacoes[6]["tipo"] = '<b>&nbsp;&raquo; Bonificação:</b>';
		$instalacoes[6]["valores"] = $editavel ? '<div style="padding-bottom:3px"><select name="instalacao_bonificacao_condicao" id="instalacao_bonificacao_condicao" class="inputBrancoAzulEscuro11" style="width:109">[instalacao_bonificacao_condicao]</select>&nbsp;<input name="instalacao_bonificacao_filtro" id="instalacao_bonificacao_filtro" type="text" class="inputBrancoAzulEscuro11" style="width:60" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" value="[instalacao_bonificacao_filtro]"></div><div><select name="instalacao_bonificacao_tipo" id="instalacao_bonificacao_tipo" class="inputBrancoAzulEscuro11" style="width:109" onChange="mudaComissaoValorTipo(this)">[instalacao_bonificacao_tipo]</select>&nbsp;<input name="instalacao_bonificacao" id="instalacao_bonificacao" type="text" class="inputBrancoAzulEscuro11" style="width:60" onKeyPress="return formataComissao(this, event, this.form.instalacao_bonificacao_tipo.value)" onBlur="return formataComissao(this, event, this.form.instalacao_bonificacao_tipo.value)" value="[instalacao_bonificacao]"></div>' : '[instalacao_bonificacao_condicao] [instalacao_bonificacao_filtro], [instalacao_bonificacao]';
		
		// Mensalidade - Bonificação
		$mensalidades[6]["tipo"] = '<b>&nbsp;&raquo; Bonificação:</b>';
		$mensalidades[6]["valores"] = $editavel ? '<div style="padding-bottom:3px"><select name="mensalidade_bonificacao_condicao" id="mensalidade_bonificacao_condicao" class="inputBrancoAzulEscuro11" style="width:109">[mensalidade_bonificacao_condicao]</select>&nbsp;<input name="mensalidade_bonificacao_filtro" id="mensalidade_bonificacao_filtro" type="text" class="inputBrancoAzulEscuro11" style="width:60" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" value="[mensalidade_bonificacao_filtro]"></div><div><select name="mensalidade_bonificacao_tipo" id="mensalidade_bonificacao_tipo" class="inputBrancoAzulEscuro11" style="width:109" onChange="mudaComissaoValorTipo(this)">[mensalidade_bonificacao_tipo]</select>&nbsp;<input name="mensalidade_bonificacao" id="mensalidade_bonificacao" type="text" class="inputBrancoAzulEscuro11" style="width:60" onKeyPress="return formataComissao(this, event, this.form.mensalidade_bonificacao_tipo.value)" onBlur="return formataComissao(this, event, this.form.mensalidade_bonificacao_tipo.value)" value="[mensalidade_bonificacao]"></div>' : '[mensalidade_bonificacao_condicao] [mensalidade_bonificacao_filtro], [mensalidade_bonificacao]';

		$cabecalho = array ("tipo" => "Tipo", "valores" => "Valores");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $instalacoes, 0, false, array("60%", "40%"));
		$instalacao = $listagem->conteudo;
		
		$cabecalho = array ("tipo" => "Tipo", "valores" => "Valores");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $mensalidades, 0, false, array("60%", "40%"));
		$mensalidade = $listagem->conteudo;

		$comissoes[0]["instalacao"] = $instalacao;
		$comissoes[0]["mensalidade"] = $mensalidade;
		
		$cabecalho = array ("instalacao" => "<center>Instalação</center>", "mensalidade" => "Mensalidade");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $comissoes);
		$conteudo = $listagem->conteudo;

		// Referência
		$conteudo = str_replace("[instalacao_referencia]", $editavel ? $this->geraSelect($this->comissoes_referencias, ($dados ? $dados["instalacao_referencia"] : null), true) : $this->buscaReferenciaComissaoDescricao($dados["instalacao_referencia"]), $conteudo);
		$conteudo = str_replace("[mensalidade_referencia]", $editavel ? $this->geraSelect($this->comissoes_referencias, ($dados ? $dados["mensalidade_referencia"] : null), true) : $this->buscaReferenciaComissaoDescricao($dados["mensalidade_referencia"]), $conteudo);
		
		// Instalação - Vendedor Interno
		$retorno = $this->buscaValor($dados["instalacao_responsabilidade_interno"]);
		$conteudo = str_replace("[instalacao_responsabilidade_interno]", $retorno["valor"], $conteudo);
		if ($editavel) $conteudo = str_replace("[instalacao_responsabilidade_interno_tipo]", $this->geraSelect($this->comissoes_tipos, $retorno["tipo"], true), $conteudo);
		
		// Mensalidade - Vendedor Interno
		$retorno = $this->buscaValor($dados["mensalidade_responsabilidade_interno"]);
		$conteudo = str_replace("[mensalidade_responsabilidade_interno]", $retorno["valor"], $conteudo);
		if ($editavel) $conteudo = str_replace("[mensalidade_responsabilidade_interno_tipo]", $this->geraSelect($this->comissoes_tipos, $retorno["tipo"], true), $conteudo);
		
		// Instalação - Vendedor Externo
		$retorno = $this->buscaValor($dados["instalacao_responsabilidade_externo"]);
		$conteudo = str_replace("[instalacao_responsabilidade_externo]", $retorno["valor"], $conteudo);
		if ($editavel) $conteudo = str_replace("[instalacao_responsabilidade_externo_tipo]", $this->geraSelect($this->comissoes_tipos, $retorno["tipo"], true), $conteudo);
		
		// Mensalidade - Vendedor Externo
		$retorno = $this->buscaValor($dados["mensalidade_responsabilidade_externo"]);
		$conteudo = str_replace("[mensalidade_responsabilidade_externo]", $retorno["valor"], $conteudo);
		if ($editavel) $conteudo = str_replace("[mensalidade_responsabilidade_externo_tipo]", $this->geraSelect($this->comissoes_tipos, $retorno["tipo"], true), $conteudo);
		
		// Instalação - Vendedor Ativo
		$retorno = $this->buscaValor($dados["instalacao_responsabilidade_ativo"]);
		$conteudo = str_replace("[instalacao_responsabilidade_ativo]", $retorno["valor"], $conteudo);
		if ($editavel) $conteudo = str_replace("[instalacao_responsabilidade_ativo_tipo]", $this->geraSelect($this->comissoes_tipos, $retorno["tipo"], true), $conteudo);
		
		// Mensalidade - Vendedor Ativo
		$retorno = $this->buscaValor($dados["mensalidade_responsabilidade_ativo"]);
		$conteudo = str_replace("[mensalidade_responsabilidade_ativo]", $retorno["valor"], $conteudo);
		if ($editavel) $conteudo = str_replace("[mensalidade_responsabilidade_ativo_tipo]", $this->geraSelect($this->comissoes_tipos, $retorno["tipo"], true), $conteudo);
		
		// Instalação - Vendedor Parceiro
		$retorno = $this->buscaValor($dados["instalacao_responsabilidade_parceiro"]);
		$conteudo = str_replace("[instalacao_responsabilidade_parceiro]", $retorno["valor"], $conteudo);
		if ($editavel) $conteudo = str_replace("[instalacao_responsabilidade_parceiro_tipo]", $this->geraSelect($this->comissoes_tipos, $retorno["tipo"], true), $conteudo);
		
		// Mensalidade - Vendedor Parceiro
		$retorno = $this->buscaValor($dados["mensalidade_responsabilidade_parceiro"]);
		$conteudo = str_replace("[mensalidade_responsabilidade_parceiro]", $retorno["valor"], $conteudo);
		if ($editavel) $conteudo = str_replace("[mensalidade_responsabilidade_parceiro_tipo]", $this->geraSelect($this->comissoes_tipos, $retorno["tipo"], true), $conteudo);
		
		// Instalação - Sub-Comissão
		$retorno = $this->buscaValor($dados["instalacao_indicacao"]);
		$conteudo = str_replace("[instalacao_indicacao]", $retorno["valor"], $conteudo);
		if ($editavel) $conteudo = str_replace("[instalacao_indicacao_tipo]", $this->geraSelect($this->comissoes_tipos, $retorno["tipo"],  true), $conteudo);
		
		// Mensalidade - Sub-Comissão
		$retorno = $this->buscaValor($dados["mensalidade_indicacao"]);
		$conteudo = str_replace("[mensalidade_indicacao]", $retorno["valor"], $conteudo);
		if ($editavel) $conteudo = str_replace("[mensalidade_indicacao_tipo]", $this->geraSelect($this->comissoes_tipos, $retorno["tipo"],  true), $conteudo);
		
		// Instalação - Bonificação
		$retorno = $this->buscaValor($dados["instalacao_bonificacao"]);
		$conteudo = str_replace("[instalacao_bonificacao]", $retorno["valor"], $conteudo);
		if ($editavel) $conteudo = str_replace("[instalacao_bonificacao_tipo]", $this->geraSelect($this->comissoes_tipos, $retorno["tipo"],  true), $conteudo);
		$conteudo = str_replace("[instalacao_bonificacao_condicao]", $editavel ? $this->geraSelect($this->comissoes_condicoes, $dados["instalacao_bonificacao_condicao"],  true) : $this->buscaCondicaoComissaoDescricao($comissoes["instalacao_bonificacao_condicao"]), $conteudo);
		$conteudo = str_replace("[instalacao_bonificacao_filtro]", $this->formataDinheiro($dados["instalacao_bonificacao_filtro"]), $conteudo);
		
		// Mensalidade - Bonificação
		$retorno = $this->buscaValor($dados["mensalidade_bonificacao"]);
		$conteudo = str_replace("[mensalidade_bonificacao]", $retorno["valor"], $conteudo);
		if ($editavel) $conteudo = str_replace("[mensalidade_bonificacao_tipo]", $this->geraSelect($this->comissoes_tipos, $retorno["tipo"],  true), $conteudo);
		$conteudo = str_replace("[mensalidade_bonificacao_condicao]", $editavel ? $this->geraSelect($this->comissoes_condicoes, $dados["mensalidade_bonificacao_condicao"],  true) : $this->buscaCondicaoComissaoDescricao($comissoes["mensalidade_bonificacao_condicao"]), $conteudo);
		$conteudo = str_replace("[mensalidade_bonificacao_filtro]", $this->formataDinheiro($dados["mensalidade_bonificacao_filtro"]), $conteudo);

		return $conteudo;
		
	}

	#################
	### Adicionar ###
	#################

	function adicionar() {
		
		if (!$_POST) {
			
			$botoes = array();
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

			$this->conteudo .= $this->carregaArquivo("planos.adicionar.inc.php");

			$multi = $this->buscaMultiservicePlayers();
			if(empty($multi)) {
				$this->conteudo = str_replace("[mostrar_multiservice]", "none", $this->conteudo);
			}
			$this->conteudo = str_replace("[multiservice_listagem]", $this->buscaMultiserviceListagem(), $this->conteudo);
			
			$this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaTipos(), "",  true), $this->conteudo);
			$this->conteudo = str_replace("[sistema]", $this->geraSelect($this->buscaSistemasPagamento(), "",  true), $this->conteudo);
			$this->conteudo = str_replace("[ajuste_categoria]", $this->geraSelect($this->buscaAjustesCategorias(), "",  true), $this->conteudo);
			$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios(), "",  true), $this->conteudo);
			// Aplicável à: linha começa oculta no novo plano; JS (atualizaAplicavel) mostra/popula via AJAX quando o usuário escolher Serviço + Tipo "Personalizado".
			$this->conteudo = str_replace("[aplicavel_display]", "none", $this->conteudo);
			$this->conteudo = str_replace("[aplicavel]", "", $this->conteudo);
			$this->conteudo = str_replace("[origens]", $this->geraCheckBoxesLista("origens[]", $this->buscaOrigens(), array(), "350px", "170px"), $this->conteudo);
			$this->conteudo = str_replace("[parceiro_planos]", $this->geraSelect($this->buscaParceiroPlanos(), null,  true), $this->conteudo);
			$this->conteudo = str_replace("[periodicidade]", $this->geraSelect($this->periodicidades, $this->dados_item["ajuste_categoria_id"],  true), $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->geraSelect($this->buscaStatus(), "",  true), $this->conteudo);
			$this->conteudo = str_replace("[servico]", $this->geraSelect($this->buscaServicos(), "",  true), $this->conteudo);
			$this->conteudo = str_replace("[unidades]", $this->geraSelect($this->buscaUnidades("B"), "",  true), $this->conteudo);
			$this->conteudo = str_replace("[unidades_bits]", $this->geraSelect($this->buscaUnidades("bps"), "",  true), $this->conteudo);
			$this->conteudo = str_replace("[iptv_streaming]", $this->geraSelect($this->streaming, 0), $this->conteudo);
			$this->conteudo = str_replace("[iptv_apps]", $this->geraCheckBoxesLista("apps[]", $this->apps, array(), "350px", "90px"), $this->conteudo);
			$this->conteudo = str_replace("[iptv_pacotes]", $this->geraCheckBoxesLista("iptv_pacotes[]", $this->buscaIptvPacotes("PACOTE"), array(), "350px", "135px"), $this->conteudo);
			$this->conteudo = str_replace("[vod_pacotes]", $this->geraCheckBoxesLista("vod_pacotes[]", $this->buscaIptvPacotes("CATALOGO"), array(), "350px", "135px"), $this->conteudo);

			$this->conteudo = str_replace("[vinculos_listagem]", $this->buscaVinculosListagem(), $this->conteudo);
			$this->conteudo = str_replace("[comissoes]", $this->buscaComissoesListagem(), $this->conteudo);
			$this->conteudo = str_replace("[tipos]", $this->geraSelect($this->comissoes_tipos, "",  true), $this->conteudo);
			$this->conteudo = str_replace("[referencias]", $this->geraSelect($this->comissoes_referencias, "",  true), $this->conteudo);
			$this->conteudo = str_replace("[condicoes]", $this->geraSelect($this->comissoes_condicoes, "",  true), $this->conteudo);
			$this->conteudo = str_replace("[vinculos_listagem]", $this->buscaVinculosListagem(), $this->conteudo);
			$this->conteudo = str_replace("[equipamentos_comodatos]", $this->buscaEquipamentosComodatos($this->dados_item["id"]), $this->conteudo);
			$this->conteudo = str_replace("[plano_tipo_contrato_principal]", $this->dados_item["tipo_contrato_principal"], $this->conteudo);
			$this->conteudo = str_replace("[plano_tipo_migracao]", $this->dados_item["tipo_migracao"], $this->conteudo);
			$this->conteudo = str_replace("[valor_comodato]", "<input type='text' class='inputBrancoAzulEscuro11' style='width:100px' onblur=\"return formataDinheiro(this, event)\" onkeypress=\"return formataDinheiro(this, event)\" id='valor_comodato' name='valor_comodato' value=\"".$this->formataDinheiro($this->dados_item["parametros"]["valor_comodato"])."\" />", $this->conteudo);
			$gerarNota = 1;
			$this->conteudo = str_replace("[gerar_nfse]", $this->geraSelect($this->arraySimNao(), $gerarNota,  true), $this->conteudo);
			$this->conteudo = str_replace("[issRetido]", $this->geraSelect($this->arrayIssRetido(), 2,  true), $this->conteudo);
			$this->conteudo = str_replace("[codigo_servico]", $this->geraSelect($this->buscaCodigoServico(), 206,  true), $this->conteudo);
			$this->conteudo = str_replace("[codigo_nbs]", $this->geraSelect($this->buscaCodigoNbs(), 117039900,  true), $this->conteudo);
			$this->conteudo = str_replace("[cnae]", $this->geraSelect($this->buscaCodigoCnae(), 312,  true), $this->conteudo);
			$this->conteudo = str_replace("[aliquota]", '2,00', $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(' ', $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);

			$disabled_issRetido = $disable_aliquota = $disabled_codigo_servico = $disable_cnae = $disabled_codigo_nbs = "disabled";
			if($gerarNota == 1) {
				$disabled_issRetido = $disable_aliquota = $disabled_codigo_servico = $disable_cnae = $disabled_codigo_nbs = "";
			}
			$this->conteudo = str_replace("[disable_cnae]", $disable_cnae , $this->conteudo);
			$this->conteudo = str_replace("[disabled_codigo_servico]", $disabled_codigo_servico, $this->conteudo);
			$this->conteudo = str_replace("[disabled_codigo_nbs]", $disabled_codigo_nbs, $this->conteudo);
			$this->conteudo = str_replace("[disable_aliquota]", $disable_aliquota, $this->conteudo);
			$this->conteudo = str_replace("[disabled_issRetido]", $disabled_issRetido, $this->conteudo);

			$this->conteudo = str_replace("[planos_limitacao_listagem]", $this->buscaPlanosLimitacaoListagem(), $this->conteudo);
			
		} else {
			
			$parametros = $this->trataParametrosEntrada($_POST);
			
			$_POST["instalacao"] = preg_replace("/[^0-9]/", "", $_POST["instalacao"]);
			$_POST["mensalidade"] = preg_replace("/[^0-9]/", "", $_POST["mensalidade"]);
			$_POST["parcelamento_maximo"] = preg_replace("/[^0-9]/", "", $_POST["parcelamento_maximo"]);
			$_POST["parcelamento_maximo"] = $_POST["parcelamento_maximo"] < 1 ? 1 : ($_POST["parcelamento_maximo"] > 12 ? 12 : $_POST["parcelamento_maximo"]);
			$_POST["ajuste_categoria"] = $_POST["ajuste_categoria"] ? $_POST["ajuste_categoria"] : "NULL";
			$_POST["plano_auto_geravel"] = $_POST["plano_auto_geravel"] ? $_POST["plano_auto_geravel"] : "NULL";
			$_POST["sistema"] = (!$_POST["sistema"] || $_POST["sistema"] == "-1") ? "NULL" : $_POST["sistema"];
			$_POST["condominio"] = $_POST["condominio"] ? $_POST["condominio"] : "NULL";
			$_POST["plano_tipo_contrato_principal"] = $_POST["plano_tipo_contrato_principal"] ? "1" : "0";
			$_POST["plano_tipo_migracao"] = $_POST["plano_tipo_migracao"] ? "1" : "0"; 
			
			$gerarNota = "";
			if($_POST["gerar_nfse"] == 1 ) {
				$aliquota = str_replace(",", ".", $_POST["aliquota"]);
				$gerarNota = "gerar_nfse = {$_POST["gerar_nfse"]}, codigo_servico_id = {$_POST["codigo_servico"]}, cnae_id = {$_POST["cnae"]}, aliquota = {$aliquota}, issRetido = {$_POST["issRetido"]}, nbs = {$_POST["codigo_nbs"]},";
			} else {
				$gerarNota = "gerar_nfse = 0, codigo_servico_id = null, cnae_id = null, aliquota = null, issRetido = null, nbs = null,";
			}

			$valor_max = (int) preg_replace("/[^0-9]/", "", $_POST["valor_max"]);
			$tipo_limitacao = $_POST["tipo_limitacao"] ? $_POST["tipo_limitacao"] : "1";
			if($tipo_limitacao != 2) {
				$valor_max = 0;
			} else {
				$valor_max = $valor_max == 0 ? "1" : $valor_max;
			}

			$query = $this->bancodedados->query("
				INSERT INTO
					tab_planos 
				SET
					pla_nome = '{$_POST["nome"]}', 	
					pla_instalacao = {$_POST["instalacao"]}, 	
					pla_mensalidade = {$_POST["mensalidade"]}, 	
					pla_parcelamento_maximo = {$_POST["parcelamento_maximo"]}, 	
					pla_parametros = '$parametros', 	
					plt_id = {$_POST["tipo"]}, 	
					pls_id = {$_POST["servico"]}, 	
					pli_id = {$_POST["sistema"]}, 	
					plu_id = {$_POST["status"]}, 	
					sac_id = {$_POST["ajuste_categoria"]}, 	
					con_id = {$_POST["condominio"]}, 	
					pla_tipo_contrato_principal = {$_POST["plano_tipo_contrato_principal"]},	
					pla_tipo_limitacao = {$tipo_limitacao}, 	
					pla_valor_max = {$valor_max},	
					$gerarNota	
					pla_tipo_migracao = {$_POST["plano_tipo_migracao"]}");
			
			$id = $this->bancodedados->ultimoIdInserido();
	
			// Insere Equipamentos
			foreach ($_POST["equipamentos"] as $equipamento_id) {
				$_POST["prazos"][$equipamento_id] = $_POST["prazos"][$equipamento_id] ? intval($_POST["prazos"][$equipamento_id]) : "NULL";
				$this->bancodedados->query("
					INSERT INTO 
						tab_planos_equipamentos 
					SET
						pla_id = $id, 
						equ_id = $equipamento_id, 
						ple_prazo = {$_POST["prazos"][$equipamento_id]}");
				
			}
			
			// Insere Vinculos
			$vinculos = unserialize(stripslashes($_POST["vinculos_lista"]));
			foreach ($vinculos as $vinculo) {
				$this->bancodedados->query("
					INSERT INTO 
						tab_planos_vinculos 
					SET
						pla_id = $id, 
						pla_filho = {$vinculo["plano"]}, 
						pva_id = {$vinculo["aplicacao"]}, 
						pvt_id = {$vinculo["tipo"]}");
			}

			// Insere Multiservice
			$multiservices = unserialize(stripslashes($_POST["multiservice_lista"]));
			foreach ($multiservices as $multiservice) {

				$this->bancodedados->query("
						INSERT INTO 
								tab_planos_multiservice 
						SET
								pla_id = {$id}, 
								player = {$multiservice["player"]}, 
								servico = {$multiservice["servico"]}");
			}

			// Insere Planos Limitacao
			$planos_limitacao = unserialize(stripslashes($_POST["planos_limitacao_lista"]));
			foreach ($planos_limitacao as $plano_limitacao) {

				$plano_limitacao["valor"] = intval(preg_replace("/[^0-9]/", "", $plano_limitacao["valor"]));
				if($plano_limitacao["valor"] == 0) {
					$plano_limitacao["valor"] = "1";
				}
				$this->bancodedados->query("
					INSERT INTO 
						tab_planos_limitacao 
					SET
						pla_id = {$id}, 
						plano = {$plano_limitacao["plano"]},
						valor = {$plano_limitacao["valor"]}");
			}


			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);

			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
		
		}
		
	}

	###########
	### Ver ###
	###########

	function ver() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$unidades = $this->buscaUnidades("B");
			$unidades_bits = $this->buscaUnidades("bps");

			$botoes = array();
			if ($this->permissoes["planos"]["editar"]) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].'\'">';
			if ($this->temAssinatura($_GET["i"]) > 0 && $this->permissoes[$this->dados_modulo["nome"]]["sincronizar"]) $botoes[] = '<input type="button" name="sincronizar" class="buttonAzulEscuroBranco11" value="Sincronizar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=sincronizar&i=' . $_GET["i"] . '\';">';
			if ($this->temAssinatura($_GET["i"]) == 0 && $this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

			$aplicacoes = array();
			if ($this->dados_item["parametros"]["desconto_condominio"] == '1') $aplicacoes[] = "Herda Desconto Condomínio";
			if ($this->dados_item["parametros"]["aplica_pessoa_fisica"] == '1') $aplicacoes[] = "Pessoa Física";
			if ($this->dados_item["parametros"]["aplica_pessoa_juridica"] == '1') $aplicacoes[] = "Pessoa Jurídica";
			if ($this->dados_item["parametros"]["aplica_com_condominio"] == '1') $aplicacoes[] = "Com Condomínio";
			if ($this->dados_item["parametros"]["aplica_sem_condominio"] == '1') $aplicacoes[] = "Sem Condomínio";
			$aplicacoes = implode(" / ", $aplicacoes);

			$pontos_download = $this->buscaTamanho($this->dados_item["parametros"]["pontos_velocidade_download"]);
			$pontos_upload = $this->buscaTamanho($this->dados_item["parametros"]["pontos_velocidade_upload"]);
			$emails_capacidade = $this->buscaTamanho($this->dados_item["parametros"]["emails_capacidade"]);
			$hospedagens_capacidade = $this->buscaTamanho($this->dados_item["parametros"]["hospedagens_capacidade"]);
			$hospedagens_transferencia = $this->buscaTamanho($this->dados_item["parametros"]["hospedagens_transferencia"]);
			$basesdedados_capacidade = $this->buscaTamanho($this->dados_item["parametros"]["basesdedados_capacidade"]);

			$this->conteudo .= $this->carregaArquivo("planos.ver.inc.php");
			
			$multi = $this->buscaMultiservicePlayers();
			if(empty($multi)) {
				$this->conteudo = str_replace("[mostrar_multiservice]", "none", $this->conteudo);
			}
			$this->conteudo = str_replace("[multiservice_listagem]", $this->buscaMultiserviceListagem($this->dados_item["multiservice"], false), $this->conteudo);
			$this->conteudo = str_replace("[multiservice_lista]", serialize($this->dados_item["multiservice"]), $this->conteudo);

			$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace("[servico]", $this->dados_item["servico_nome"], $this->conteudo);
			$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
			$this->conteudo = str_replace("[sistema]", $this->dados_item["sistema_nome"], $this->conteudo);

			$this->conteudo = str_replace("[parceiro_plano]", $this->dados_item["parceiro_plano_nome"], $this->conteudo);
			$this->conteudo = str_replace("[parceiro_max]", $this->dados_item["parceiro_plano_id"] ? $this->dados_item["parceiro_max"] : "", $this->conteudo);
			
			$this->conteudo = str_replace("[instalacao]", $this->formataDinheiro($this->dados_item["instalacao"]), $this->conteudo);
			$this->conteudo = str_replace("[mensalidade]", $this->formataDinheiro($this->dados_item["mensalidade"]), $this->conteudo);
			$this->conteudo = str_replace("[parcelamento_maximo]", $this->dados_item["parcelamento_maximo"], $this->conteudo);
			$this->conteudo = str_replace("[ajuste_categoria]", $this->dados_item["ajuste_categoria_nome"], $this->conteudo);
			$this->conteudo = str_replace("[ajuste_programado]", $this->dados_item["ajuste_programado_nome"], $this->conteudo);
			$this->conteudo = str_replace("[condominio]", $this->dados_item["condominio_nome"], $this->conteudo);
			$this->conteudo = str_replace("[descontos_plano]", $this->buscaDescontosConcessoes($this->dados_item["id"]), $this->conteudo);
			$this->conteudo = str_replace("[descontos_adicionar]", ($this->permissoes["descontos_concessoes"]["adicionar"]) ? '<input type="button" class="buttonAzulEscuroBranco11" name="descontos_adicionar" value="Adicionar" onClick="adicionarDescontoConcessaoPlano('.$this->dados_item["id"].')">' : '', $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[vinculos_listagem]", $this->buscaVinculosListagem($this->dados_item["vinculos"], false), $this->conteudo);
			$this->conteudo = str_replace("[vinculos_lista]", serialize($this->dados_item["vinculos"]), $this->conteudo);
			$this->conteudo = str_replace("[comissoes]", $this->buscaComissoesListagem($this->dados_item["parametros"]["comissoes"], false), $this->conteudo);
			$this->conteudo = str_replace("[formas_pagamento_listagem]", $this->buscaFormasPagamentoListagem($this->dados_item["formas_pagamento"], false), $this->conteudo);
			$this->conteudo = str_replace("[pontos_quantidade]", $this->dados_item["parametros"]["pontos_quantidade"], $this->conteudo);
			$this->conteudo = str_replace("[pontos_velocidade_download]", $pontos_download["tamanho"]." ".$unidades_bits[$pontos_download["tipo"]], $this->conteudo);
			$this->conteudo = str_replace("[pontos_velocidade_upload]", $pontos_upload["tamanho"]." ".$unidades_bits[$pontos_upload["tipo"]], $this->conteudo);
			$this->conteudo = str_replace("[emails_quantidade]" , $this->dados_item["parametros"]["emails_quantidade"], $this->conteudo);
			$this->conteudo = str_replace("[emails_capacidade]" , $emails_capacidade["tamanho"]." ".$unidades[$emails_capacidade["tipo"]], $this->conteudo);
			$this->conteudo = str_replace("[hospedagens_quantidade]" , $this->dados_item["parametros"]["hospedagens_quantidade"], $this->conteudo);
			$this->conteudo = str_replace("[hospedagens_capacidade]" , $hospedagens_capacidade["tamanho"]." ".$unidades[$hospedagens_capacidade["tipo"]], $this->conteudo);
			$this->conteudo = str_replace("[hospedagens_transferencia]" , $hospedagens_transferencia["tamanho"]." ".$unidades[$hospedagens_transferencia["tipo"]], $this->conteudo);
			$this->conteudo = str_replace("[basesdedados_quantidade]" , $this->dados_item["parametros"]["basesdedados_quantidade"], $this->conteudo);
			$this->conteudo = str_replace("[basesdedados_capacidade]" , $basesdedados_capacidade["tamanho"]." ".$unidades[$basesdedados_capacidade["tipo"]], $this->conteudo);
			$this->conteudo = str_replace("[aplicacoes]", $aplicacoes, $this->conteudo);

			$apps_nomes = array();
			if (is_array($this->dados_item["parametros"]["apps"])) {
				foreach ($this->dados_item["parametros"]["apps"] as $app_id) {
					if (array_key_exists($app_id, $this->apps)) $apps_nomes[] = $this->apps[$app_id];
				}
			}
			$this->conteudo = str_replace("[iptv_apps]", $apps_nomes ? implode(", ", $apps_nomes) : "&mdash;", $this->conteudo);

			$pacotes_iptv = $this->buscaIptvPacotes("PACOTE");
			$iptv_pacotes_nomes = array();
			if (is_array($this->dados_item["parametros"]["iptv_pacotes"])) {
				foreach ($this->dados_item["parametros"]["iptv_pacotes"] as $pacote_id) {
					if (array_key_exists((string) $pacote_id, $pacotes_iptv)) $iptv_pacotes_nomes[] = $pacotes_iptv[(string) $pacote_id];
				}
			}
			$this->conteudo = str_replace("[iptv_pacotes]", $iptv_pacotes_nomes ? implode(", ", $iptv_pacotes_nomes) : "&mdash;", $this->conteudo);

			$pacotes_vod = $this->buscaIptvPacotes("CATALOGO");
			$vod_pacotes_nomes = array();
			if (is_array($this->dados_item["parametros"]["vod_pacotes"])) {
				foreach ($this->dados_item["parametros"]["vod_pacotes"] as $pacote_id) {
					if (array_key_exists((string) $pacote_id, $pacotes_vod)) $vod_pacotes_nomes[] = $pacotes_vod[(string) $pacote_id];
				}
			}
			$this->conteudo = str_replace("[vod_pacotes]", $vod_pacotes_nomes ? implode(", ", $vod_pacotes_nomes) : "&mdash;", $this->conteudo);

			// Aplicável à: só aparece quando tipo == 2 (mesmo critério do editar).
			$aplicavel_visivel = ($this->dados_item["tipo_id"] == 2);
			$this->conteudo = str_replace("[aplicavel_display]", $aplicavel_visivel ? "table-row" : "none", $this->conteudo);
			$aplicavel_nomes = array();
			if ($aplicavel_visivel && is_array($this->dados_item["parametros"]["aplicavel"])) {
				$planos_aplicaveis = $this->buscaPlanosAplicaveis($this->dados_item["servico_id"]);
				foreach ($this->dados_item["parametros"]["aplicavel"] as $plano_id) {
					if (array_key_exists($plano_id, $planos_aplicaveis)) $aplicavel_nomes[] = $planos_aplicaveis[$plano_id];
				}
			}
			$this->conteudo = str_replace("[aplicavel]", $aplicavel_nomes ? implode(", ", $aplicavel_nomes) : "&mdash;", $this->conteudo);

			// Origens: lista os nomes das origens selecionadas.
			$origens_nomes = array();
			if (is_array($this->dados_item["parametros"]["origens"])) {
				$origens_todas = $this->buscaOrigens();
				foreach ($this->dados_item["parametros"]["origens"] as $origem_id) {
					if (array_key_exists($origem_id, $origens_todas)) $origens_nomes[] = $origens_todas[$origem_id];
				}
			}
			$this->conteudo = str_replace("[origens]", $origens_nomes ? implode(", ", $origens_nomes) : "&mdash;", $this->conteudo);

			// NFS-e
			$gerar_nfse = intval($this->dados_item["gerar_nfse"]);
			$this->conteudo = str_replace("[gerar_nfse]", $gerar_nfse == 1 ? "Sim" : "Não", $this->conteudo);
			if ($gerar_nfse == 1) {
				$cods_servico = $this->buscaCodigoServico();
				$cnaes = $this->buscaCodigoCnae();
				$nbs = $this->buscaCodigoNbs();
				$iss_retidos = $this->arrayIssRetido();
				$this->conteudo = str_replace("[codigo_servico]", isset($cods_servico[$this->dados_item["codigo_servico"]]) ? $cods_servico[$this->dados_item["codigo_servico"]] : "&mdash;", $this->conteudo);
				$this->conteudo = str_replace("[codigo_nbs]", isset($nbs[$this->dados_item["codigo_nbs"]]) ? $nbs[$this->dados_item["codigo_nbs"]] : "&mdash;", $this->conteudo);
				$this->conteudo = str_replace("[cnae]", isset($cnaes[$this->dados_item["cnae"]]) ? $cnaes[$this->dados_item["cnae"]] : "&mdash;", $this->conteudo);
				$this->conteudo = str_replace("[issRetido]", isset($iss_retidos[$this->dados_item["issRetido"]]) ? $iss_retidos[$this->dados_item["issRetido"]] : "&mdash;", $this->conteudo);
				$this->conteudo = str_replace("[aliquota]", number_format($this->dados_item["aliquota"], 2, ",", "") . "%", $this->conteudo);
			} else {
				$this->conteudo = str_replace("[codigo_servico]", "&mdash;", $this->conteudo);
				$this->conteudo = str_replace("[codigo_nbs]", "&mdash;", $this->conteudo);
				$this->conteudo = str_replace("[cnae]", "&mdash;", $this->conteudo);
				$this->conteudo = str_replace("[issRetido]", "&mdash;", $this->conteudo);
				$this->conteudo = str_replace("[aliquota]", "&mdash;", $this->conteudo);
			}

			// Parceiro » Tipo de Limitação / Valor Máximo / Planos Vinculados (estavam faltando substituição no ver).
			$this->conteudo = str_replace("[tipo_limitacao]", $this->buscaTipoLimitacaoDescricao($this->dados_item["tipo_limitacao"]), $this->conteudo);
			$this->conteudo = str_replace("[valor_max]", $this->formataDinheiro($this->dados_item["valor_max"] ? $this->dados_item["valor_max"] : 0), $this->conteudo);
			$this->conteudo = str_replace("[planos_limitacao_listagem]", $this->buscaPlanosLimitacaoListagem($this->dados_item["planos_limitacao"], false), $this->conteudo);

			// Periodicidade
			$periodicidade_id = $this->dados_item["parametros"]["periodicidade"];
			$this->conteudo = str_replace("[periodicidade]", isset($this->periodicidades[$periodicidade_id]) ? $this->periodicidades[$periodicidade_id] : "&mdash;", $this->conteudo);

			// IPTV - campos numéricos e streaming.
			$this->conteudo = str_replace("[iptv_stb_quantidade]", $this->dados_item["parametros"]["iptv_stb_quantidade"] !== "" ? $this->dados_item["parametros"]["iptv_stb_quantidade"] : "&mdash;", $this->conteudo);
			$this->conteudo = str_replace("[iptv_aplicativos_quantidade]", $this->dados_item["parametros"]["iptv_aplicativos_quantidade"] !== "" ? $this->dados_item["parametros"]["iptv_aplicativos_quantidade"] : "&mdash;", $this->conteudo);
			$this->conteudo = str_replace("[iptv_gravacao_horas]", $this->dados_item["parametros"]["iptv_gravacao_horas"] !== "" ? $this->dados_item["parametros"]["iptv_gravacao_horas"] : "&mdash;", $this->conteudo);
			$streaming_id = $this->dados_item["parametros"]["iptv_streaming"];
			$this->conteudo = str_replace("[iptv_streaming]", isset($this->streaming[$streaming_id]) ? $this->streaming[$streaming_id] : "&mdash;", $this->conteudo);

			// VOD » Telas
			$this->conteudo = str_replace("[vod_telas]", $this->dados_item["parametros"]["vod_telas"] !== "" ? $this->dados_item["parametros"]["vod_telas"] : "&mdash;", $this->conteudo);

			// Olé Total
			$this->conteudo = str_replace("[ole_total]", $this->dados_item["parametros"]["ole_total"] ? "Sim" : "Não", $this->conteudo);

			// Equipamentos em Comodato: listagem read-only (apenas os equipamentos vinculados, com prazo).
			$tipos_equipamentos = $this->buscaTiposEquipamentos();
			$equipamentos_plano = $this->buscaEquipamentosPlanos($this->dados_item["id"]);
			$equipamentos_lista = array();
			foreach ($tipos_equipamentos as $eq) {
				if (array_key_exists($eq["equipamento_id"], $equipamentos_plano)) {
					$prazo = $equipamentos_plano[$eq["equipamento_id"]];
					$equipamentos_lista[] = $eq["equipamento"] . ($prazo ? " (prazo: {$prazo})" : "");
				}
			}
			$this->conteudo = str_replace("[equipamentos_comodatos]", $equipamentos_lista ? implode(", ", $equipamentos_lista) : "&mdash;", $this->conteudo);

			// Valor Comodato
			$this->conteudo = str_replace("[valor_comodato]", $this->formataDinheiro($this->dados_item["parametros"]["valor_comodato"]), $this->conteudo);

			// Tipo de Contrato: lista os tipos marcados (Contrato Principal / Migração).
			$tipos_contrato = array();
			if ($this->dados_item["tipo_contrato_principal"]) $tipos_contrato[] = "Contrato Principal";
			if ($this->dados_item["tipo_migracao"]) $tipos_contrato[] = "Migração";
			$this->conteudo = str_replace("[tipo_contrato]", $tipos_contrato ? implode(" / ", $tipos_contrato) : "&mdash;", $this->conteudo);

			$this->conteudo = str_replace("[premiacao_pontos]", $this->dados_item["parametros"]["comissoes"]["premiacao_pontos"], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
		
	}
    
	function buscaTiposEquipamentos(){

		$query = $this->bancodedados->query("
			SELECT
				equ_id AS equipamento_id,
				equ_nome AS equipamento
			FROM
				tab_equipamentos");

		return $this->bancodedados->resultadoArrayTotal($query);

	}

	function buscaEquipamentosPlanos($plano_id) {
		
		$equipamentos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				equ_id AS equipamento_id,
				ple_prazo AS prazo 
			FROM
				tab_planos_equipamentos
			WHERE
				pla_id = '$plano_id'");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$equipamentos[$dados["equipamento_id"]] = $dados["prazo"];
		}
		
		return $equipamentos;
		
	}
  
	function buscaEquipamentosComodatos($plano_id = null) {
		
		$equipamentos = $this->buscaTiposEquipamentos();
		$equipamentosPlanos = $this->buscaEquipamentosPlanos($plano_id);
		
		for ($i = 0; $i < count($equipamentos); $i++){
			$checked = "";
			$prazo = "";
			if(array_key_exists($equipamentos[$i]["equipamento_id"], $equipamentosPlanos)){
				$checked = "checked";
				$prazo = $equipamentosPlanos[$equipamentos[$i]["equipamento_id"]];
			}
			$equipamentos[$i]["botoes"] = '<input type="checkbox" value="'.$equipamentos[$i]["equipamento_id"].'" id="chb_equipamentos" name="equipamentos[]" '.$checked.'/>';
			$equipamentos[$i]["prazo"] = '<input type="text" id="prazo" name="prazos['.$equipamentos[$i]["equipamento_id"].']" style="width:100%" value="'.$prazo.'" class="inputBrancoAzulEscuro11" />';
		}
		
		$cabecalho = array("botoes" => "", "equipamento" => "Equipamentos", "prazo" => "Prazo");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $equipamentos, 0, false, array("20px", null, "100px"), null, array("left", "left", "center"));
		
		return $listagem->conteudo;
		
	}	

	##############
	### Editar ###
	##############

	function editar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				if ($this->temAssinatura($_GET["i"]) > 0 && $this->permissoes[$this->dados_modulo["nome"]]["sincronizar"]) $botoes[] = '<input type="button" name="sincronizar" class="buttonAzulEscuroBranco11" value="Sincronizar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=sincronizar&i=' . $_GET["i"] . '\';">';
				if ($this->temAssinatura($_GET["i"]) == 0 && $this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\';">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

				$pontos_download = $this->buscaTamanho($this->dados_item["parametros"]["pontos_velocidade_download"]);
				$pontos_upload = $this->buscaTamanho($this->dados_item["parametros"]["pontos_velocidade_upload"]);
				$email_capacidade = $this->buscaTamanho($this->dados_item["parametros"]["emails_capacidade"]);
				$hospedagem_capacidade = $this->buscaTamanho($this->dados_item["parametros"]["hospedagens_capacidade"]);
				$hospedagem_transferencia = $this->buscaTamanho($this->dados_item["parametros"]["hospedagens_transferencia"]);
				$basesdedados_capacidade = $this->buscaTamanho($this->dados_item["parametros"]["basesdedados_capacidade"]);

				$this->conteudo .= $this->carregaArquivo("planos.editar.inc.php");

				$multi = $this->buscaMultiservicePlayers();
				if(empty($multi)) {
					$this->conteudo = str_replace("[mostrar_multiservice]", "none", $this->conteudo);
				}
				$this->conteudo = str_replace("[multiservice_listagem]", $this->buscaMultiserviceListagem($this->dados_item["multiservice"]), $this->conteudo);
				$this->conteudo = str_replace("[multiservice_lista]", sizeof($this->dados_item["multiservice"]) ? serialize($this->dados_item["multiservice"]) : "", $this->conteudo);

				$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
				$this->conteudo = str_replace("[servico]", $this->geraSelect($this->buscaServicos(), $this->dados_item["servico_id"],  true), $this->conteudo);
				$this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaTipos(), $this->dados_item["tipo_id"], true), $this->conteudo);
				$this->conteudo = str_replace("[aplicavel_display]", $this->dados_item["tipo_id"] == 2 ? "table-row" : "none", $this->conteudo);
				// Aplicável à: quando tipo = 2 (Personalizado), monta lista de checkboxes com os planos do mesmo serviço; caso contrário, fica vazio (linha oculta).
				$this->conteudo = str_replace("[aplicavel]", $this->dados_item["tipo_id"] == 2 ? $this->geraCheckBoxesLista("aplicavel[]", $this->buscaPlanosAplicaveis($this->dados_item["servico_id"]), $this->dados_item["parametros"]["aplicavel"], "350px", "170px") : "", $this->conteudo);
				$this->conteudo = str_replace("[sistema]", $this->geraSelect($this->buscaSistemasPagamento(), $this->dados_item["sistema_id"],  true), $this->conteudo);
				$this->conteudo = str_replace("[ajuste_categoria]", $this->geraSelect($this->buscaAjustesCategorias(), $this->dados_item["ajuste_categoria_id"],  true), $this->conteudo);
				$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios(), $this->dados_item["condominio_id"],  true), $this->conteudo);
				$this->conteudo = str_replace("[origens]", $this->geraCheckBoxesLista("origens[]", $this->buscaOrigens(), $this->dados_item["parametros"]["origens"], "350px", "170px"), $this->conteudo);
				$this->conteudo = str_replace("[periodicidade]", $this->geraSelect($this->periodicidades, $this->dados_item["parametros"]["periodicidade"],  true), $this->conteudo);
				
				$this->conteudo = str_replace("[parceiro_planos]", $this->geraSelect($this->buscaParceiroPlanos($this->dados_item["id"]), $this->dados_item["parceiro_plano_id"],  true), $this->conteudo);
				$this->conteudo = str_replace("[parceiro_max]", $this->dados_item["parceiro_plano_id"] ? $this->dados_item["parceiro_max"] : "", $this->conteudo);
				
				$this->conteudo = str_replace("[instalacao]", $this->formataDinheiro($this->dados_item["instalacao"], true), $this->conteudo);
				$this->conteudo = str_replace("[mensalidade]", $this->formataDinheiro($this->dados_item["mensalidade"], true), $this->conteudo);
				$this->conteudo = str_replace("[parcelamento_maximo]", $this->dados_item["parcelamento_maximo"], $this->conteudo);
				$this->conteudo = str_replace("[status]", $this->geraSelect($this->buscaStatus(), $this->dados_item["status_id"],  true), $this->conteudo);
				$this->conteudo = str_replace("[descontos_plano]", $this->buscaDescontosConcessoes($this->dados_item["id"]), $this->conteudo);
				$this->conteudo = str_replace("[descontos_adicionar]", $this->permissoes["descontos_concessoes"]["adicionar"] ? '<input type="button" class="buttonAzulEscuroBranco11" name="descontos_adicionar" value="Adicionar" onClick="adicionarDescontoConcessaoPlano('.$this->dados_item["id"].')">' : '', $this->conteudo);
				$this->conteudo = str_replace("[comissoes]", $this->buscaComissoesListagem($this->dados_item["parametros"]["comissoes"], true), $this->conteudo);
				$this->conteudo = str_replace("[ole_total]", "<select id='ole_total' class='inputBrancoAzulEscuro11' style='width:90px' name='ole_total'>".$this->geraSelect(array(0 => "Não", 1 => "Sim"), $this->dados_item["parametros"]["ole_total"])."</select>" , $this->conteudo);
				$this->conteudo = str_replace("[valor_comodato]", "<input type='text' class='inputBrancoAzulEscuro11' style='width:100px' onblur=\"return formataDinheiro(this, event)\" onkeypress=\"return formataDinheiro(this, event)\" id='valor_comodato' name='valor_comodato' value=\"".$this->formataDinheiro($this->dados_item["parametros"]["valor_comodato"])."\" />", $this->conteudo);
				$this->conteudo = str_replace("[formas_pagamento_listagem]", $this->buscaFormasPagamentoListagem($this->dados_item["formas_pagamento"]), $this->conteudo);
				$this->conteudo = str_replace("[formas_pagamento_lista]", sizeof($this->dados_item["formas_pagamento"]) ? serialize($this->dados_item["formas_pagamento"]) : "", $this->conteudo);
				$this->conteudo = str_replace("[equipamentos_comodatos]", $this->buscaEquipamentosComodatos($this->dados_item["id"])  , $this->conteudo);
				$this->conteudo = str_replace("[plano_tipo_contrato_principal]", ($this->dados_item["tipo_contrato_principal"] ? "checked" : "") , $this->conteudo);
				$this->conteudo = str_replace("[plano_tipo_migracao]", $this->dados_item["tipo_migracao"] ? "checked" : "", $this->conteudo);
				
				$this->conteudo = str_replace("[pontos_quantidade]", $this->dados_item["parametros"]["pontos_quantidade"], $this->conteudo);
				$this->conteudo = str_replace("[pontos_velocidade_download]", $pontos_download["tamanho"], $this->conteudo);
				$this->conteudo = str_replace("[pontos_velocidade_download_unidade]", $this->geraSelect($this->buscaUnidades("bps"), $pontos_download["tipo"],  true), $this->conteudo);
				$this->conteudo = str_replace("[pontos_velocidade_upload]", $pontos_upload["tamanho"], $this->conteudo);
				$this->conteudo = str_replace("[pontos_velocidade_upload_unidade]", $this->geraSelect($this->buscaUnidades("bps"), $pontos_upload["tipo"],  true), $this->conteudo);
				$this->conteudo = str_replace("[gerar_nfse]", $this->geraSelect($this->arraySimNao(), $this->dados_item["gerar_nfse"],  true), $this->conteudo);
				$disabled_issRetido = $disable_aliquota = $disabled_codigo_servico = $disable_cnae = $disabled_codigo_nbs = "disabled";
				if(intval($this->dados_item["gerar_nfse"]) == 1) {
					$disabled_issRetido = $disable_aliquota = $disabled_codigo_servico = $disable_cnae = $disabled_codigo_nbs = "";
				}
				$this->conteudo = str_replace("[disable_cnae]", $disable_cnae , $this->conteudo);
				$this->conteudo = str_replace("[disabled_codigo_servico]", $disabled_codigo_servico, $this->conteudo);
				$this->conteudo = str_replace("[disabled_codigo_nbs]", $disabled_codigo_nbs, $this->conteudo);
				$this->conteudo = str_replace("[disable_aliquota]", $disable_aliquota, $this->conteudo);
				$this->conteudo = str_replace("[disabled_issRetido]", $disabled_issRetido, $this->conteudo);

				$this->conteudo = str_replace("[codigo_servico]", $this->geraSelect($this->buscaCodigoServico(), $this->dados_item["codigo_servico"],  true), $this->conteudo);
				$this->conteudo = str_replace("[codigo_nbs]", $this->geraSelect($this->buscaCodigoNbs(), $this->dados_item["codigo_nbs"],  true), $this->conteudo);
				$this->conteudo = str_replace("[cnae]", $this->geraSelect($this->buscaCodigoCnae(), $this->dados_item["cnae"],  true), $this->conteudo);
				$aliquota = number_format($this->dados_item['aliquota'] ,2,",","");
				$this->conteudo = str_replace("[aliquota]", $aliquota, $this->conteudo);
				$this->conteudo = str_replace("[issRetido]", $this->geraSelect($this->arrayIssRetido(), $this->dados_item["issRetido"],  true), $this->conteudo);
				
				$this->conteudo = str_replace("[iptv_pacotes]", $this->geraCheckBoxesLista("iptv_pacotes[]", $this->buscaIptvPacotes("PACOTE"), $this->dados_item["parametros"]["iptv_pacotes"], "350px", "135px"), $this->conteudo);
				$this->conteudo = str_replace("[iptv_stb_quantidade]", $this->dados_item["parametros"]["iptv_stb_quantidade"],$this->conteudo);
				$this->conteudo = str_replace("[iptv_aplicativos_quantidade]", $this->dados_item["parametros"]["iptv_aplicativos_quantidade"],$this->conteudo);
				$this->conteudo = str_replace("[iptv_gravacao_horas]", $this->dados_item["parametros"]["iptv_gravacao_horas"],$this->conteudo);
				$this->conteudo = str_replace("[iptv_streaming]", $this->geraSelect($this->streaming, $this->dados_item["parametros"]["iptv_streaming"]), $this->conteudo);
				$this->conteudo = str_replace("[iptv_apps]", $this->geraCheckBoxesLista("apps[]", $this->apps, $this->dados_item["parametros"]["apps"], "350px", "90px"), $this->conteudo);

				$this->conteudo = str_replace("[vod_pacotes]", $this->geraCheckBoxesLista("vod_pacotes[]", $this->buscaIptvPacotes("CATALOGO"), $this->dados_item["parametros"]["vod_pacotes"], "350px", "135px"), $this->conteudo);
				$this->conteudo = str_replace("[vod_telas]", $this->dados_item["parametros"]["vod_telas"],$this->conteudo);

				$this->conteudo = str_replace("[emails_quantidade]", $this->dados_item["parametros"]["emails_quantidade"],$this->conteudo);
				$this->conteudo = str_replace("[emails_capacidade]", $email_capacidade["tamanho"], $this->conteudo);
				$this->conteudo = str_replace("[emails_capacidade_unidade]", $this->geraSelect($this->buscaUnidades("B"), $email_capacidade["tipo"],  true), $this->conteudo);
				$this->conteudo = str_replace("[hospedagens_quantidade]", $this->dados_item["parametros"]["hospedagens_quantidade"],$this->conteudo);
				$this->conteudo = str_replace("[hospedagens_capacidade]", $hospedagem_capacidade["tamanho"], $this->conteudo);
				$this->conteudo = str_replace("[hospedagens_capacidade_unidade]", $this->geraSelect($this->buscaUnidades("B"), $hospedagem_capacidade["tipo"],  true), $this->conteudo);
				$this->conteudo = str_replace("[hospedagens_transferencia]", $hospedagem_transferencia["tamanho"], $this->conteudo);
				$this->conteudo = str_replace("[hospedagens_transferencia_unidade]", $this->geraSelect($this->buscaUnidades("B"), $hospedagem_transferencia["tipo"],  true), $this->conteudo);
				$this->conteudo = str_replace("[basesdedados_quantidade]", $this->dados_item["parametros"]["basesdedados_quantidade"], $this->conteudo);
				$this->conteudo = str_replace("[basesdedados_capacidade]", $basesdedados_capacidade["tamanho"],$this->conteudo);
				$this->conteudo = str_replace("[basesdedados_capacidade_unidade]", $this->geraSelect($this->buscaUnidades("B"), $basesdedados_capacidade["tipo"],  true), $this->conteudo);
				$this->conteudo = str_replace("[desconto_condominio]", $this->dados_item["parametros"]["desconto_condominio"] == '1' ? "checked" : "", $this->conteudo);
				$this->conteudo = str_replace("[aplica_pessoa_fisica]", $this->dados_item["parametros"]["aplica_pessoa_fisica"] == '1' ? "checked" : "", $this->conteudo);
				$this->conteudo = str_replace("[aplica_pessoa_juridica]", $this->dados_item["parametros"]["aplica_pessoa_juridica"] == '1' ? "checked" : "", $this->conteudo);
				$this->conteudo = str_replace("[aplica_com_condominio]", $this->dados_item["parametros"]["aplica_com_condominio"] == '1' ? "checked" : "", $this->conteudo);
				$this->conteudo = str_replace("[aplica_sem_condominio]", $this->dados_item["parametros"]["aplica_sem_condominio"] == '1' ? "checked" : "", $this->conteudo);
				$this->conteudo = str_replace("[premiacao_pontos]", $this->dados_item["parametros"]["comissoes"]["premiacao_pontos"], $this->conteudo);
				$this->conteudo = str_replace("[vinculos_listagem]", $this->buscaVinculosListagem($this->dados_item["vinculos"]), $this->conteudo);

				$this->conteudo = str_replace("[checked_tipo_limitacao1]", $this->dados_item["tipo_limitacao"] == 1 ? "checked" : "", $this->conteudo);
				$this->conteudo = str_replace("[checked_tipo_limitacao2]", $this->dados_item["tipo_limitacao"] == 2 ? "checked" : "", $this->conteudo);
				$this->conteudo = str_replace("[valor_max]", $this->formataDinheiro($this->dados_item["valor_max"] ? $this->dados_item["valor_max"] : 0), $this->conteudo);
				$this->conteudo = str_replace("[tipo_limitacao]", $this->dados_item["tipo_limitacao"], $this->conteudo);

				$planos_limitacao_listagem = $this->buscaPlanosLimitacaoListagem($this->dados_item["planos_limitacao"]);
				$this->conteudo = str_replace("[planos_limitacao_listagem]", $planos_limitacao_listagem , $this->conteudo);

				$planos_limitacao_lista = sizeof($this->dados_item["planos_limitacao"]) ? serialize($this->dados_item["planos_limitacao"]) : "";
				$this->conteudo = str_replace("[planos_limitacao_lista]", $planos_limitacao_lista, $this->conteudo);
				
				$this->conteudo = str_replace("[vinculos_lista]", sizeof($this->dados_item["vinculos"]) ? serialize($this->dados_item["vinculos"]) : "", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);

			} else {

				$parametros = $this->trataParametrosEntrada($_POST);

				$_POST["instalacao"] = (int) preg_replace("/[^0-9]/", "", $_POST["instalacao"]);
				$_POST["mensalidade"] = (int) preg_replace("/[^0-9]/", "", $_POST["mensalidade"]);
				$_POST["parcelamento_maximo"] = (int) preg_replace("/[^0-9]/", "", $_POST["parcelamento_maximo"]);
				$_POST["parcelamento_maximo"] = $_POST["parcelamento_maximo"] < 1 ? 1 : ($_POST["parcelamento_maximo"] > 12 ? 12 : (int) $_POST["parcelamento_maximo"]);
				$_POST["ajuste_categoria"] = $_POST["ajuste_categoria"] ? $_POST["ajuste_categoria"] : "NULL";
				$_POST["plano_auto_geravel"] = $_POST["plano_auto_geravel"] ? $_POST["plano_auto_geravel"] : "NULL";
				$_POST["sistema"] = !$_POST["sistema"] || $_POST["sistema"] == "-1" ? "NULL" : $_POST["sistema"];
				$_POST["condominio"] = $_POST["condominio"] ? $_POST["condominio"] : "NULL"; 
				$_POST["plano_tipo_contrato_principal"] = $_POST["plano_tipo_contrato_principal"] ? "1" : "0";
				$_POST["plano_tipo_migracao"] = $_POST["plano_tipo_migracao"] ? "1" : "0"; 
				$_POST["parceiro_max"] = $_POST["parceiro_plano"] ? (int) preg_replace("/[^0-9]/", "", $_POST["parceiro_max"]) : 0;
				
				$gerarNota = "";
				if($_POST["gerar_nfse"] == 1 ) {
					$aliquota = str_replace(",", ".", $_POST["aliquota"]);
					$gerarNota = "gerar_nfse = {$_POST["gerar_nfse"]}, codigo_servico_id = {$_POST["codigo_servico"]}, cnae_id = {$_POST["cnae"]}, aliquota = {$aliquota}, issRetido = {$_POST["issRetido"]}, nbs = {$_POST["codigo_nbs"]},";
				} else {
					$gerarNota = "gerar_nfse = 0, codigo_servico_id = null, cnae_id = null, aliquota = null, issRetido = null, nbs = null,";
				}
			
				$valor_max = (int) preg_replace("/[^0-9]/", "", $_POST["valor_max"]);
				$tipo_limitacao = $_POST["tipo_limitacao"] ? $_POST["tipo_limitacao"] : "1";
				if($tipo_limitacao != 2) {
					$valor_max = 0;
				} else {
					$valor_max = $valor_max == 0 ? "1" : $valor_max;
				}

				$myQuery = "
					UPDATE 
						tab_planos 
					SET 
						pla_nome = '{$_POST["nome"]}', 
						pla_parceiro_max = '{$_POST["parceiro_max"]}', 
						pla_instalacao = {$_POST["instalacao"]}, 
						pla_mensalidade = {$_POST["mensalidade"]}, 
						pla_parcelamento_maximo = {$_POST["parcelamento_maximo"]}, 
						pla_parametros = '$parametros', 
						pls_id = {$_POST["servico"]}, 
						pli_id = {$_POST["sistema"]}, 
						plt_id = {$_POST["tipo"]}, 
						plu_id = {$_POST["status"]}, 
						sac_id = {$_POST["ajuste_categoria"]}, 
						con_id = {$_POST["condominio"]},
						pla_tipo_contrato_principal = {$_POST["plano_tipo_contrato_principal"]},
						pla_tipo_limitacao = {$tipo_limitacao},
						pla_valor_max = {$valor_max},
						$gerarNota
						pla_tipo_migracao = {$_POST["plano_tipo_migracao"]} 
					WHERE 
						pla_id = {$_GET["i"]}";
				$this->bancodedados->query($myQuery);
				
				$this->bancodedados->query("
					UPDATE 
						tab_planos 
					SET 
						pla_parceiro_plano_id = NULL, 
						pla_parceiro_max = 0
					WHERE 
						pla_parceiro_plano_id = {$_GET["i"]}");
				
				if ($_POST["parceiro_plano"]) {
					
					$this->bancodedados->query("
						UPDATE 
							tab_planos 
						SET 
							pla_parceiro_plano_id = {$_GET["i"]}, 
							pla_parceiro_max = 0
						WHERE 
							pla_id = {$_POST["parceiro_plano"]}");
							
				}

				
				// Remove Vinculos Antigos
				$this->bancodedados->query("
					DELETE FROM 
						tab_planos_vinculos 
					WHERE 
						pla_id = {$_GET["i"]}");
				
				// Atualiza Vinculos
				$vinculos = unserialize(stripslashes($_POST["vinculos_lista"]));
				
				foreach ($vinculos as $vinculo) {
					$this->bancodedados->query("
						INSERT INTO 
							tab_planos_vinculos 
						SET
							pla_id = {$_GET["i"]}, 
							pla_filho = {$vinculo["plano"]}, 
							pva_id = {$vinculo["aplicacao"]}, 
							pvt_id = {$vinculo["tipo"]}");
				}
				
				// Remove equipamentos
				$this->bancodedados->query("
					DELETE FROM 
						tab_planos_equipamentos 
					WHERE 
						pla_id = {$_GET["i"]}");
				
				// Insere equipamentos
				foreach ($_POST["equipamentos"] as $equipamento_id) {
					
					$_POST["prazos"][$equipamento_id] = $_POST["prazos"][$equipamento_id] ? intval($_POST["prazos"][$equipamento_id]): "null";
					
					$this->bancodedados->query("
						INSERT INTO 
							tab_planos_equipamentos 
						SET
							pla_id = {$_GET["i"]}, 
							equ_id = $equipamento_id, 
							ple_prazo = {$_POST["prazos"][$equipamento_id]}");
					
				}

				// Remove Multiservice Antigos
				$this->bancodedados->query("
					DELETE FROM 
						tab_planos_multiservice 
					WHERE 
						pla_id = {$_GET["i"]}");

				// Insere Multiservice
				$multiservices = unserialize(stripslashes($_POST["multiservice_lista"]));
				foreach ($multiservices as $multiservice) {
					$this->bancodedados->query("
						INSERT INTO 
							tab_planos_multiservice 
						SET
							pla_id = {$_GET["i"]}, 
							player = {$multiservice["player"]}, 
							servico = {$multiservice["servico"]}");
				}

				// Remove Planos Limitacao Antigos
				$this->bancodedados->query("
					DELETE FROM 
						tab_planos_limitacao 
					WHERE 
						pla_id = {$_GET["i"]}");
				
				// Insere Planos Limitacao
				$planos_limitacao = unserialize(stripslashes($_POST["planos_limitacao_lista"]));
				foreach ($planos_limitacao as $plano_limitacao) {

					$plano_limitacao["valor"] = (int) preg_replace("/[^0-9]/", "", $plano_limitacao["valor"]);
					if($plano_limitacao["valor"] == 0) {
						$plano_limitacao["valor"] = "1";
					}
					$this->bancodedados->query("
						INSERT INTO 
							tab_planos_limitacao 
						SET
							pla_id = {$_GET["i"]}, 
							plano = {$plano_limitacao["plano"]},
							valor = {$plano_limitacao["valor"]}");
				}

				// Registra Formas Pagamento
				$validos = array();
				$formas_pagamento = unserialize(stripslashes($_POST["formas_pagamento_lista"]));
				foreach ($formas_pagamento as $forma_pagamento) {
					$forma_pagamento["entrada"] = intval(preg_replace("/[^0-9]/", "", $forma_pagamento["entrada"]), 10);
					$forma_pagamento["parcelas"] = intval(preg_replace("/[^0-9]/", "", $forma_pagamento["parcelas"]), 10);
					$forma_pagamento["valor_parcela"] = intval(preg_replace("/[^0-9]/", "", $forma_pagamento["valor_parcela"]), 10);
					if (!$forma_pagamento["parcelas"] || !$forma_pagamento["valor_parcela"]) {
						$forma_pagamento["parcelas"] = 0;
						$forma_pagamento["valor_parcela"] = 0;
					}
					$forma_pagamento["forma"] = $forma_pagamento["parcelas"] ? $forma_pagamento["forma"] : "NULL";
					$forma_pagamento["multa"] = intval(preg_replace("/[^0-9]/", "", $forma_pagamento["multa"]), 10);
					$forma_pagamento["fidelidade"] = intval(preg_replace("/[^0-9]/", "", $forma_pagamento["fidelidade"]), 10);
					if (!$forma_pagamento["multa"] || !$forma_pagamento["fidelidade"]) {
						$forma_pagamento["multa"] = 0;
						$forma_pagamento["fidelidade"] = 0;
					}
					if ($forma_pagamento["id"] && $this->formaPagamentoUtilizada($forma_pagamento["id"])) {
						$this->bancodedados->query("UPDATE tab_formas_pagamento SET pla_id = NULL WHERE fop_id = {$forma_pagamento["id"]}");
						$forma_pagamento["id"] = null;
					}
					if ($forma_pagamento["id"]) {
						$this->bancodedados->query("UPDATE tab_formas_pagamento SET fop_entrada = {$forma_pagamento["entrada"]}, fop_parcelas = {$forma_pagamento["parcelas"]}, fop_valor_parcela = {$forma_pagamento["valor_parcela"]}, fpf_id = {$forma_pagamento["forma"]}, fop_multa = {$forma_pagamento["multa"]}, fop_fidelidade = {$forma_pagamento["fidelidade"]}, fpc_id = {$forma_pagamento["categoria"]} WHERE fop_id = {$forma_pagamento["id"]}");
						$validos[] = $forma_pagamento["id"];
					} else {
						$this->bancodedados->query("INSERT INTO tab_formas_pagamento (fop_entrada, fop_parcelas, fop_valor_parcela, fpf_id, fop_multa, fop_fidelidade, fpc_id, pla_id) VALUES ({$forma_pagamento["entrada"]}, {$forma_pagamento["parcelas"]}, {$forma_pagamento["valor_parcela"]}, {$forma_pagamento["forma"]}, {$forma_pagamento["multa"]}, {$forma_pagamento["fidelidade"]}, {$forma_pagamento["categoria"]}, {$_GET["i"]})");
						$validos[] = $this->bancodedados->ultimoIdInserido();
					}
				}
				
				if ($_POST["ole_total"] == 1 && !in_array(4, $_POST["equipamentos"])){
					$this->bancodedados->query("INSERT INTO tab_planos_equipamentos (pla_id, equ_id, ple_prazo) VALUE( {$_GET["i"]} , 4 , 18)");
				}

				$query = $this->bancodedados->query("
					SELECT
						fop_id AS id,
						(SELECT COUNT(*) FROM rel_fop_cor_estacontido WHERE rel_fop_cor_estacontido.fop_id = tab_formas_pagamento.fop_id) AS utilizados
					FROM 
						tab_formas_pagamento 
					WHERE 
						pla_id = {$_GET["i"]}".($validos ? " AND
						fop_id NOT IN (".implode(",", $validos).")" : ""));
						
				while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
					if ($dados["utilizados"]) {
						$this->bancodedados->query("UPDATE tab_formas_pagamento SET pla_id = NULL WHERE fop_id = {$dados["id"]}");
					} else {
						$this->bancodedados->query("DELETE FROM tab_formas_pagamento WHERE fop_id = {$dados["id"]}");
					}
				}
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
			}

		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}

	}

	function geraMultiserviceListagem($dados, $editavel = true) {
	
		if ($editavel) {
		$cabecalho = array("multiservice_player" => "Player", "multiservice_servico" => "Serviço", "botoes" => "");
		} else {
		$cabecalho = array("multiservice_player" => "Player", "multiservice_servico" => "Serviço");
		}
		
                $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array("50%", "50%", ""));
		return $listagem->conteudo;
	
	}

	function buscaMultiserviceAdicionar() {
		
		$adicionar = array();
		
		$adicionar["multiservice_player"] = '<select name="multiservice_player" id="multiservice_player" style="width:100%" class="inputBrancoAzulEscuro11" onchange="mudaMultiserviceServico(this.value)">'.$this->geraSelect($this->buscaMultiservicePlayers(), null, true).'</select>';
		$adicionar["multiservice_servico"] = '<div id="div_multiservice"><select name="multiservice_servico" id="multiservice_servico" style="width:100%" class="inputBrancoAzulEscuro11" disabled></select></div>';
		$adicionar["botoes"] = '<input type="button" name="multiservice_adicionar" id="multiservice_adicionar" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="multiserviceAdicionar()">';

		return $adicionar;
		
	}

	function buscaMultiserviceListagem($dados = null, $editavel = true) {
		
		if ($dados) {
                    
			$player = $this->buscaMultiservicePlayers();
			$servico = $this->buscaMultiserviceServicos(false);
			for ($i = 0; $i < count($dados); $i++) {
				if ($editavel) {
					$dados[$i]["link"] = "javascript:selecionarbuscaMultiservice(this, event, ".($i + 1).")";
					$dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerMultiservice(\''.($i + 1).'\')">';
				}
				$dados[$i]["multiservice_player"] = $player[$dados[$i]["player"]];
				$dados[$i]["multiservice_servico"] = $servico[$dados[$i]["servico"]];
			}
			
			sort($dados);
			
		} else {
			$dados = array(array("mesclado" => '<table width="100%" height="20" cellpadding="0" cellspacing="0" border="0"><tr><td class="txtAzulEscuro11" align="center">Nenhum registro disponível</td></tr></table>'));
		}
		
		if ($editavel) {
			$dados = funcoes::mescla_arrays(array($this->buscaMultiserviceAdicionar()), $dados);
		}
		
		return $this->geraMultiserviceListagem($dados, $editavel);
		
	}

	function formaPagamentoUtilizada($forma_pagamento_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				rel_fop_cor_estacontido
			WHERE
				fop_id = $forma_pagamento_id");

		return $this->bancodedados->resultado($query) > 0;
		
	}

	###############
	### Remover ###
	###############

	function remover() {
			
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("planos.remover.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ",$botoes),$this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
			
			} else {
				
				if ($this->temAssinatura($_GET["i"]) == 0) {

					$this->bancodedados->query("DELETE FROM tab_planos_vinculos WHERE pla_id = '{$_GET["i"]}'");
					
					$this->bancodedados->query("DELETE FROM tab_planos_multiservice WHERE pla_id = '{$_GET["i"]}'");
					
					$this->bancodedados->query("DELETE FROM tab_planos_limitacao WHERE pla_id = '{$_GET["i"]}'");

					$this->bancodedados->query("DELETE FROM tab_planos WHERE pla_id = '{$_GET["i"]}'");
					
					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
					
				}
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
				
			}
		
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
		
	}

	####################
	### Sincronizar ###
	####################

	function sincronizar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

				$this->conteudo .= $this->carregaArquivo("planos.sincronizar.inc.php");

				$this->conteudo = str_replace("[plano_nome]", $this->dados_item["nome"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=sincronizar&i={$_GET["i"]}", $this->conteudo);

			} else {

				set_time_limit(0);
				ignore_user_abort(1);

				$clientes_iptv = controle::instanciaObjeto("clientes_iptv", $this->bancodedados, $this->permissoes, false);

				$plano_id = (int) $_GET["i"];

				$query = $this->bancodedados->query("
					SELECT
						pla_parametros
					FROM
						tab_planos
					WHERE
						pla_id = $plano_id");
				$pla_parametros = $this->bancodedados->resultado($query);

				$query = $this->bancodedados->query("
					SELECT
						ass_id AS id,
						ass_parametros AS parametros
					FROM
						tab_assinaturas
					WHERE
						pla_id = $plano_id AND ast_id IN (1, 2, 6)
					ORDER BY
						ass_id DESC");

				while (($assinatura = $this->bancodedados->resultadoArray($query)) != false) {
									
					if ($assinatura["parametros"] != $pla_parametros) {

						$this->bancodedados->query("
							UPDATE
								tab_assinaturas
							SET
								ass_parametros = '" . addslashes($pla_parametros) . "'
							WHERE
								ass_id = {$assinatura["id"]}");

						$query_iptv = $this->bancodedados->query("
							SELECT DISTINCT
								tab_clientes_iptv.cip_id AS id,
								tab_clientes_iptv.cor_id AS contrato_id
							FROM
								tab_clientes_iptv
								INNER JOIN rel_ass_cor_estacontido ON (tab_clientes_iptv.cor_id = rel_ass_cor_estacontido.cor_id)
							WHERE
								rel_ass_cor_estacontido.ass_id = {$assinatura["id"]}");

						while (($iptv = $this->bancodedados->resultadoArray($query_iptv)) != false) {
							$clientes_iptv->sincronizarClienteIptv($iptv, false);
						}
						
					}
					
				}

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

			}

		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}

	}

	##############
	### Listar ###
	##############

	function listar() {
		
		if (!$_GET["f"] && !$_GET["p"]) {
			$_GET["f"]["status"] = 1;
		}
		
		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;
		
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
		if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $this->conteudo .= '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar\'">';
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
		
		$itens = array();
		
		// Nome
		$itens[] = array ("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "(tab_planos.pla_nome LIKE '%[valor]%')");

		// Serviços
		$servicos = $this->buscaServicos();
		$select_servicos = array();
		foreach ($servicos as $id => $nome) {
			$select_servicos[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "servico", "descricao" => "Serviço", "valor" => $select_servicos, "where" => "tab_planos_servicos.pls_id LIKE '[valor]'");
		
		// Tipos
		$tipos = $this->buscaTipos();
		$select_tipos = array();
		foreach ($tipos as $id => $nome) {
			$select_tipos[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $select_tipos, "where" => "tab_planos_tipos.plt_id LIKE '[valor]'");
		
		// Sistemas
		$sistemas = $this->buscaSistemasPagamento();
		$select_sistemas = array();
		foreach ($sistemas as $id => $nome) {
			$select_sistemas[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "sistema", "descricao" => "Sistema", "valor" => $select_sistemas, "where" => "IF('[valor]' = '-1', tab_planos.pli_id IS NULL, tab_planos.pli_id LIKE '[valor]')");
		
		// Status
		$status = $this->buscaStatus();
		$select_status = array();
		foreach ($status as $id => $nome) {
			$select_status[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "tab_planos_status.plu_id LIKE '[valor]'");

		// Contrato principal
		$itens[] = array ("tipo" => "select", "nome" => "contrato_principal", "descricao" => "Contrato Principal", "valor" => array(array("id" => "s", "nome" => "Sim"),array("id" => "n", "nome" => "Não")), "where" => "tab_planos.pla_tipo_contrato_principal = IF('[valor]' = 's', 1, 0)");
		
		// Migrçaão
		$itens[] = array ("tipo" => "select", "nome" => "migracao", "descricao" => "Migração", "valor" => array(array("id" => "s", "nome" => "Sim"),array("id" => "n", "nome" => "Não")), "where" => "tab_planos.pla_tipo_migracao = IF('[valor]' = 's', 1, 0)");

		return controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
	
	}

	function geraPaginacao($filtro, $total, $pagina) {

		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);

	}

	function buscaItens($filtro, $pagina) {
		
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		$itens = array();
		
		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_planos.pla_id AS id,
				tab_planos.pla_nome AS nome,
				tab_planos.pla_instalacao AS instalacao,
				tab_planos.pla_mensalidade AS mensalidade,
				tab_planos.pla_parametros AS parametros,
				tab_planos_servicos.pls_nome AS servico,
				tab_planos_tipos.plt_nome AS tipo,
				tab_planos_status.plu_nome AS status,
				tab_planos_sistemas.pli_id AS sistema_id,
				IF(tab_planos.pli_id IS NOT NULL, tab_planos_sistemas.pli_nome, 'Qualquer um') AS sistema_nome,
				IFNULL((SELECT SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_planos.pla_mensalidade)) FROM tab_descontos_concessoes WHERE tab_descontos_concessoes.pla_id = tab_planos.pla_id AND tab_descontos_concessoes.dcs_id = 5 AND tab_descontos_concessoes.det_id NOT IN (1,2,3)), 0) AS bonificacao,
				IFNULL((SELECT SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_planos.pla_mensalidade)) FROM tab_descontos_concessoes WHERE tab_descontos_concessoes.pla_id = tab_planos.pla_id AND tab_descontos_concessoes.dcs_id = 5 AND tab_descontos_concessoes.det_id NOT IN (4)), 0) AS descontos
			FROM
				tab_planos
				INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
				INNER JOIN tab_planos_tipos ON (tab_planos.plt_id = tab_planos_tipos.plt_id)
				INNER JOIN tab_planos_status ON (tab_planos.plu_id = tab_planos_status.plu_id)
				LEFT OUTER JOIN tab_planos_sistemas ON (tab_planos.pli_id = tab_planos_sistemas.pli_id)
			WHERE
				$filtro->parametros_sql
			ORDER BY
				tab_planos_servicos.pls_nome,
				tab_planos.plt_id,
				tab_planos.pla_nome
			LIMIT
				$limit_inicio,
				$limit_fim");
				
		$linhas = $this->bancodedados->linhasTotal($query);

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes["planos"]["ver"]) {
				$dados["link"] = "?m=planos&a=ver&i={$dados["id"]}";
			}
			
			$dados["parametros"] = unserialize($dados["parametros"]);

			$dados["ate_vencimento"] = $dados["bonificacao"] > 0 ? $dados["mensalidade"] - $dados["bonificacao"] : 0;
			$dados["promocional"] = $dados["descontos"] > 0 ? $dados["mensalidade"] - $dados["bonificacao"] - $dados["descontos"] : 0;

			$dados["instalacao"] = $this->formataDinheiro($dados["instalacao"]);
			$dados["mensalidade"] = $this->formataDinheiro($dados["mensalidade"]);
			$dados["ate_vencimento"] = $this->formataDinheiro($dados["ate_vencimento"]);
			$dados["promocional"] = $this->formataDinheiro($dados["promocional"]);

			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");

			$itens[] = $dados;
		
		}
		
		return array("linhas" => $linhas, "itens" => $itens);
		
	}

	function geraListagem($itens) {
		
		$cabecalho = array("contador" => "#", "nome" => "Nome", "servico" => "Serviço", "tipo" => "Tipo", "sistema_nome" => "Sistema", "instalacao" => "Adesão", "mensalidade" => "Mens.", "ate_vencimento" => "Até Venc.", "promocional" => "Promoção", "status" => "Status");
		
		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20"), null, array("right", "left", "center", "center", "center", "right", "right", "right", "right"));
		
	}

	###########
	### RDS ###
	###########

	function geraVinculosListagem($dados, $editavel = true) {
		
		if ($editavel) {
			$cabecalho = array("servico" => "Serviço", "plano" => "Plano", "aplicacao" => "Aplicação", "tipo" => "Tipo", "botoes" => "");
		} else {
			$cabecalho = array("servico" => "Serviço", "plano" => "Plano", "aplicacao" => "Aplicação", "tipo" => "Tipo");
		}
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
		
		return $listagem->conteudo;
		
	}

	function buscaPlanosServico($servico_id) {
		
		$planos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				pla_id AS id,
				pla_nome AS nome
			FROM
				tab_planos
			WHERE
				pls_id = '$servico_id'
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$planos[$dados["id"]] = $dados["nome"];
		}
		
		return $planos;
		
	}

	function buscaPlanosParaLimitacao() {
		
		$planos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				pla_id AS id,
				pla_nome AS nome
			FROM
				tab_planos
			WHERE
				pls_id != 2 AND
				plu_id = 1
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$planos[$dados["id"]] = $dados["nome"];
		}
		
		return $planos;
		
	}

	function buscaPlanosAplicaveis($servico_id) {

		$planos = array();

		$query = $this->bancodedados->query("
			SELECT
				pla_id AS id,
				pla_nome AS nome
			FROM
				tab_planos
			WHERE
				pls_id = '$servico_id' AND
				plt_id = 1
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$planos[$dados["id"]] = $dados["nome"];
		}
		
		return $planos;
		
	}

	function buscaVinculosAplicacoes() {
		
		$aplicacoes = array();
		
		$query = $this->bancodedados->query("
			SELECT
				pva_id AS id,
				pva_nome AS nome
			FROM
				tab_planos_vinculos_aplicacoes");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$aplicacoes[$dados["id"]] = $dados["nome"];
		}
		
		return $aplicacoes;
	
	}

	function buscaVinculosTipos() {
		
		$tipos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				pvt_id AS id,
				pvt_nome AS nome
			FROM
				tab_planos_vinculos_tipos");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$tipos[$dados["id"]] = $dados["nome"];
		}
		
		return $tipos;
		
	}

	function buscaVinculosAdicionar() {
		
		$adicionar = array();
		
		$adicionar["servico"] = '<select name="vinculos_servico" id="vinculos_servico" style="width:170px" class="inputBrancoAzulEscuro11" onchange="mudaServico(this.value)">'.$this->geraSelect($this->buscaServicos(), null, true).'</select>';
		$adicionar["plano"] = '<div id="div_plano"><select name="vinculos_plano" id="vinculos_plano" style="width:233px" class="inputBrancoAzulEscuro11" disabled></select></div>';
		$adicionar["aplicacao"] = '<select name="vinculos_aplicacao" id="vinculos_aplicacao" style="width:110px" class="inputBrancoAzulEscuro11">'.$this->geraSelect($this->buscaVinculosAplicacoes(), null, true).'</select>';
		$adicionar["tipo"] = '<select name="vinculos_tipo" id="vinculos_tipo" style="width:160px" class="inputBrancoAzulEscuro11">'.$this->geraSelect($this->buscaVinculosTipos(), null, true).'</select>';
		$adicionar["botoes"] = '<input type="button" name="vinculos_adicionar" id="vinculos_adicionar" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="vinculoAdicionar()">';

		return $adicionar;
		
	}

	function buscaVinculosListagem($dados = null, $editavel = true) {
		
		if ($dados) {
			
			$servicos = $this->buscaServicos();
			$planos = $this->buscaPlanosServico('%');
			$aplicacoes = $this->buscaVinculosAplicacoes();
			$tipos = $this->buscaVinculosTipos();
			
			for ($i = 0; $i < count($dados); $i++) {
				if ($editavel) {
					$dados[$i]["link"] = "javascript:selecionarVinculo(this, event, ".($i + 1).")";
					$dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerVinculo(\''.($i + 1).'\')">';
				}
				$dados[$i]["servico"] = $servicos[$dados[$i]["servico"]];
				$dados[$i]["plano"] = $planos[$dados[$i]["plano"]];
				$dados[$i]["aplicacao"] = $aplicacoes[$dados[$i]["aplicacao"]];
				$dados[$i]["tipo"] = $tipos[$dados[$i]["tipo"]];
			}
			
			sort($dados);
			
		} else {
			$dados = array(array("mesclado" => '<table width="100%" height="20" cellpadding="0" cellspacing="0" border="0"><tr><td class="txtAzulEscuro11" align="center">Nenhum registro disponível</td></tr></table>'));
		}
		
		if ($editavel) {
			$dados = funcoes::mescla_arrays(array($this->buscaVinculosAdicionar()), $dados);
		}
		
		return $this->geraVinculosListagem($dados, $editavel);
		
	}

	function buscaPlanosLimitacaoListagem($dados = null, $editavel = true, $disabled = false) {
		
		if ($dados) {
			$planos = $this->buscaPlanosParaLimitacao();
			
			for ($i = 0; $i < count($dados); $i++) {
				if ($editavel) {
					$dados[$i]["link"] = "javascript:selecionarPlanoLimitacao(this, event, ".($i + 1).")";
					$dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerPlanosLimitacao(\''.($i + 1).'\')">';
				}
				$dados[$i]["planos_limitacao_plano"] = $planos[$dados[$i]["plano"]];
				$dados[$i]["planos_limitacao_valor"] = $this->formataDinheiro($dados[$i]["valor"]);
			}
			
			sort($dados);
			
		} else {
			$dados = array(array("mesclado" => '<table width="100%" height="20" cellpadding="0" cellspacing="0" border="0"><tr><td class="txtAzulEscuro11" align="center">Nenhum registro disponpivel</td></tr></table>'));
		}
		
		if ($editavel) {
			$dados = funcoes::mescla_arrays(array($this->buscaPlanosLimitacaoAdicionar($disabled)), $dados);
		}

		$retorno = $this->geraPlanosLimitacaoListagem($dados, $editavel);
		
		return $retorno;
	}

	
	function buscaPlanosLimitacaoAdicionar($disabled = false) {
		
		$adicionar = array();
		
		$disabled_attr = '';
		if ($disabled) {
			$disabled_attr = 'disabled';
		}

		$adicionar["planos_limitacao_plano"] = '<select name="planos_limitacao_plano" id="planos_limitacao_plano" style="width:100%" class="inputBrancoAzulEscuro11" '.$disabled_attr.'>'.$this->geraSelect($this->buscaPlanosParaLimitacao(), null, true).'</select>';
		$adicionar["planos_limitacao_valor"] = '<div id="div_planos_limitacao_valor"><input name="planos_limitacao_valor" id="planos_limitacao_valor" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" style="width:100%;text-align:right" '.$disabled_attr.'></div>';
		$adicionar["botoes"] = '<input type="button" name="planos_limitacao_adicionar" id="planos_limitacao_adicionar" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="planosLimitacaoAdicionar()" '.$disabled_attr.'>';

		return $adicionar;
		
	}


	function geraPlanosLimitacaoListagem($dados, $editavel = true) {
		
		if ($editavel) {
			$cabecalho = array("planos_limitacao_plano" => "Plano", "planos_limitacao_valor" => "Valor", "botoes" => "");
		} else {
			$cabecalho = array("planos_limitacao_plano" => "Plano", "planos_limitacao_valor" => "Valor");
		}

		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array("50%", "50%", ""));

		return $listagem->conteudo;
		
	}

	function buscaFormasPagamentosCategorias() {
		
		$tipos = array();

		$query = $this->bancodedados->query("
			SELECT
				fpc_id AS id,
				fpc_nome AS nome
			FROM
				tab_formas_pagamento_categorias
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$tipos[$dados["id"]] = $dados["nome"];
		}
		
		return $tipos;
		
	}

	function buscaFormasPagamentosFormas() {
		
		$tipos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				fpf_id AS id,
				fpf_nome AS nome
			FROM
				tab_formas_pagamento_formas
			ORDER BY
				nome");
				
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$tipos[$dados["id"]] = $dados["nome"];
		}
		
		return $tipos;
		
	}

	function buscaFormasPagamentosCategoria($categoria_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				fpc_nome
			FROM
				tab_formas_pagamento_categorias
			WHERE
				fpc_id = $categoria_id");
			
		return $this->bancodedados->resultado($query);
	
	}

	function buscaFormasPagamentosForma($forma_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				fpf_nome
			FROM
				tab_formas_pagamento_formas
			WHERE
				fpf_id = $forma_id");
				
		return $this->bancodedados->resultado($query);
		
	}

	function buscaFormasPagamentoAdicionar() {
		
		$adicionar = array();
		
		$adicionar["categoria"] = '<select name="formas_pagamento_categoria" id="formas_pagamento_categoria" style="width:160px" class="inputBrancoAzulEscuro11">'.$this->geraSelect($this->buscaFormasPagamentosCategorias(), null, true).'</select>';
		$adicionar["entrada"] = '<input name="formas_pagamento_entrada" id="formas_pagamento_entrada" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" style="width:90px;text-align:right">';
		$adicionar["parcelas"] = '<input name="formas_pagamento_parcelas" id="formas_pagamento_parcelas" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:60px;text-align:right">';
		$adicionar["valor_parcela"] = '<input name="formas_pagamento_valor_parcela" id="formas_pagamento_valor_parcela" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" style="width:90px;text-align:right">';
		$adicionar["forma"] = '<select name="formas_pagamento_forma" id="formas_pagamento_forma" style="width:100px" class="inputBrancoAzulEscuro11">'.$this->geraSelect($this->buscaFormasPagamentosFormas(), null, true).'</select>';
		$adicionar["multa"] = '<input name="formas_pagamento_multa" id="formas_pagamento_multa" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" style="width:90px;text-align:right">';
		$adicionar["fidelidade"] = '<input name="formas_pagamento_fidelidade" id="formas_pagamento_fidelidade" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:60px;text-align:right">';
		$adicionar["botoes"] = '<input type="button" name="formas_pagamento_adicionar" id="formas_pagamento_adicionar" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="formaPagamentoAdicionar()">';
		
		return $adicionar;
		
	}

	function buscaFormasPagamentoListagem($dados = null, $editavel = true) {
		
		if ($dados) {
			
			for ($i = 0; $i < count($dados); $i++) {
				if ($editavel) {
					$dados[$i]["link"] = "javascript:selecionarFormaPagamento(this, event, ".($i + 1).")";
					$dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerFormaPagamento(\''.($i + 1).'\')">';
				}
				$dados[$i]["categoria"] = $this->buscaFormasPagamentosCategoria($dados[$i]["categoria"]);
				$dados[$i]["entrada"] = $dados[$i]["entrada"] > 0 ? $this->formataDinheiro($dados[$i]["entrada"]) : "";
				$dados[$i]["parcelas"] = $dados[$i]["parcelas"] > 0 ? $dados[$i]["parcelas"] : "";
				$dados[$i]["valor_parcela"] = $dados[$i]["valor_parcela"] > 0 ? $this->formataDinheiro($dados[$i]["valor_parcela"]) : "";
				$dados[$i]["forma"] = $dados[$i]["forma"] ? $this->buscaFormasPagamentosForma($dados[$i]["forma"]) : "";
				$dados[$i]["multa"] = $dados[$i]["multa"] > 0 ? $this->formataDinheiro($dados[$i]["multa"]) : "";
				$dados[$i]["fidelidade"] = $dados[$i]["fidelidade"] > 0 ? $dados[$i]["fidelidade"] : "";
			}
		
		} else {
			$dados = array(array("mesclado" => '<table width="100%" height="20" cellpadding="0" cellspacing="0" border="0"><tr><td class="txtAzulEscuro11" align="center">Nenhum registro disponível</td></tr></table>'));
		}
		
		if ($editavel) {
			$dados = funcoes::mescla_arrays(array($this->buscaFormasPagamentoAdicionar()), $dados);
		}
		
		return $this->geraFormasPagamentoListagem($dados, $editavel);
	
	}

	function geraFormasPagamentoListagem($dados, $editavel = true) {
		
		if ($editavel) {
			$cabecalho = array("categoria" => "Categoria", "entrada" => "Entrada", "parcelas" => "Parcelas", "valor_parcela" => "Valor Parcelas", "forma" => "Forma", "multa" => "Multa", "fidelidade" => "Fidelidade", "botoes" => "");
		} else {
			$cabecalho = array("categoria" => "Categoria", "entrada" => "Entrada", "parcelas" => "Parcelas", "valor_parcela" => "Valor Parcelas", "forma" => "Forma", "multa" => "Multa", "fidelidade" => "Fidelidade");
		}
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, null, null, array("left", "right", "right", "right", "center", "right", "right"));
		
		return $listagem->conteudo;
		
	}

	function ordena_multiarray($array, $campos) {
		
		if (is_array($array) && sizeof($array)) {
			
		    $array_final = array();
			$array_ordenacao = array();
			$chave = array();
			
			while (($array_ordenacao_atual = current($array)) != false) {
				for ($i=0; $i < sizeof($campos); $i++) {
					$chave[] = $array_ordenacao_atual[$campos[$i]];
				}
				$chave = implode(",", $chave);
				if (!$array_ordenacao[$chave]) {
					$array_ordenacao[$chave."_".key($array)] = key($array);
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

}

?>
