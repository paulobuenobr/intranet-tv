<?

set_time_limit(0);

class placar {

  var $bancodedados;
  var $permissoes;
  var $acao;
  var $conteudo;
  var $dados_item;
  var $dados_modulo;
  var $dados_acao;
  var $ultima_atualizacao;

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

  function formataDinheiro($valor, $moeda = 'R$', $meta = 0) {
    $valor = intval(preg_replace("/[^0-9]/", "", $valor), 10);
    $meta = intval(preg_replace("/[^0-9]/", "", $meta), 10);
    $valor_formatado = number_format($valor / 100, 2, ",", ".");
    if ($moeda) {
    	$valor_formatado = "$moeda $valor_formatado";
    }
    if ($meta) {
      if ($valor / $meta <= 0.5) {
        $valor_formatado = "<font color=\"red\">$valor_formatado</font>";
      } elseif ($valor / $meta < 1.0) {
        $valor_formatado = "<font color=\"orange\">$valor_formatado</font>";
      } else {
        $valor_formatado = "<font color=\"green\">$valor_formatado</font>";
      }
    }
    return $valor_formatado;
  }
  
  function buscaPrimeiroNome($nome) {
    return trim(substr($nome, 0, strpos("$nome ", " ")));
  }
  
  function buscaCargo($tipo) {
    if ($tipo == -1) return "Diretor Comercial";
    if ($tipo == 0) return "Telemarketing Ativo";
    if ($tipo == 1) return "Agente Comercial";
    if ($tipo == 2) return "Telemarketing Passivo";
    if ($tipo == 3) return "Vendedor Externo";
  }

  function formataNome($nome) {
    $nome = strtolower($nome);
    $nome = str_replace(array("ç", "ñ"), array("c", "n"), $nome);
    $nome = preg_replace("/[áàâãäª]/", "a", $nome);
    $nome = preg_replace("/[éèêë]/", "e", $nome);
    $nome = preg_replace("/[íìîï]/", "i", $nome);
    $nome = preg_replace("/[óòôõö°º]/", "o", $nome);
    $nome = preg_replace("/[úùûü]/", "u", $nome);
    $nome = preg_replace("/[^a-z0-9]/", " ", $nome);
    $nome = preg_replace("/[\r\n\t ]+/", "_", trim($nome));
    return $nome;
  }
  
  function formataMes($mes, $abreviado = false) {
    $meses[1]  = "Janeiro";
    $meses[2]  = "Fevereiro";
    $meses[3]  = "Março";
    $meses[4]  = "Abril";
    $meses[5]  = "Maio";
    $meses[6]  = "Junho";
    $meses[7]  = "Julho";
    $meses[8]  = "Agosto";
    $meses[9]  = "Setembro";
    $meses[10] = "Outubro";
    $meses[11] = "Novembro";
    $meses[12] = "Dezembro";
    return $abreviado ? substr($meses[intval($mes, 10)], 0, 3) : $meses[intval($mes, 10)];
  }
  
  function buscaDiaSemana($data, $abreviado = true) {
    $semanas[0]  = "Domingo";
    $semanas[1]  = "Segunda-feira";
    $semanas[2]  = "Terça-feira";
    $semanas[3]  = "Quarta-feira";
    $semanas[4]  = "Quinta-feira";
    $semanas[5]  = "Sexta-feira";
    $semanas[6]  = "Sábado";
    $semana = intval(date("w", strtotime($data)), 10);
    return $abreviado ? substr($semanas[$semana], 0, 3) : $semanas[$semanas];
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
    if ($_POST) {
      $id = md5(uniqid(rand(), true));
      $_SESSION["POST"][$id] = $_POST;
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
      exit;
    } elseif ($_SESSION["POST"][$_GET["i"]]) {
      $_POST = $_SESSION["POST"][$_GET["i"]];
    }
    if (!$_POST) {
      $_POST["inicio"] = date("d/m/Y");
      $_POST["termino"] = date("d/m/Y");
      $_POST["competencia_inicio"] = date("m/Y");
      $_POST["competencia_termino"] = date("m/Y");
      $_POST["tipo"] = 1;
      $_POST["cvs"] = 0;
    }
    if ($_POST["tipo"] == 1) {
      $_POST["termino"] = $_POST["inicio"];
    } elseif ($_POST["tipo"] > 2) {
      $_POST["inicio"] = "01/{$_POST["competencia_inicio"]}";
      $_POST["termino"] = date("t/m/Y", strtotime(substr($_POST["competencia_termino"], 3, 4)."-".substr($_POST["competencia_termino"], 0, 2)."-01"));
    }
    if ($_POST["cvs"]) {
      $this->geraPlacar($_POST["inicio"], $_POST["termino"], $_POST["tipo"], true);
    } else {
      $this->conteudo .= $this->carregaArquivo("placar.ver.inc.php");
      $this->conteudo = str_replace("[exibe_periodo]", $_POST["tipo"] <= 2 ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row") : "none", $this->conteudo);
      $this->conteudo = str_replace("[exibe_competencia]", $_POST["tipo"] <= 2 ? "none" : ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row"), $this->conteudo);
      $this->conteudo = str_replace("[exibe_periodo_termino]", $_POST["tipo"] == 1 ? "none" : "inline", $this->conteudo);
      $this->conteudo = str_replace("[inicio]", $_POST["inicio"], $this->conteudo);
      $this->conteudo = str_replace("[termino]", $_POST["termino"], $this->conteudo);
      $this->conteudo = str_replace("[competencia_inicio]", $_POST["competencia_inicio"], $this->conteudo);
      $this->conteudo = str_replace("[competencia_termino]", $_POST["competencia_termino"], $this->conteudo);
      $this->conteudo = str_replace("[tipo_1_checked]", $_POST["tipo"] == 1 ? "checked" : "", $this->conteudo);
      $this->conteudo = str_replace("[tipo_2_checked]", $_POST["tipo"] == 2 ? "checked" : "", $this->conteudo);
      $this->conteudo = str_replace("[tipo_3_checked]", $_POST["tipo"] == 3 ? "checked" : "", $this->conteudo);
      $this->conteudo = str_replace("[tipo_4_checked]", $_POST["tipo"] == 4 ? "checked" : "", $this->conteudo);
      $this->conteudo = str_replace("[placar]", $this->geraPlacar($_POST["inicio"], $_POST["termino"], $_POST["tipo"]), $this->conteudo);
      $this->conteudo = str_replace("[atualizacao]", date("d/m/Y (H:i)", $this->ultima_atualizacao), $this->conteudo);
      $botoes[] = '<input type="submit" name="gerar" class="buttonAzulEscuroBranco11" value="Gerar">';
      $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=ver", $this->conteudo);
    }
  }

  function geraPlacar($referencia_inicio, $referencia_termino, $tipo, $cvs = false) {
    $referencia_inicio = substr($referencia_inicio, 6, 4)."-".substr($referencia_inicio, 3, 2)."-".substr($referencia_inicio, 0, 2);
    $referencia_termino = substr($referencia_termino, 6, 4)."-".substr($referencia_termino, 3, 2)."-".substr($referencia_termino, 0, 2);
    $dados = $this->buscaDadosPlacar($referencia_inicio, $referencia_termino, $tipo);
    if ($dados) {
      // Aplica Metas
      $this->aplicaMetas($dados, $tipo);
      // Mescla Dados
      $chaves = array_keys($dados);
      for ($i = 0; $i < count($chaves); $i++) {
        $placar = funcoes::mescla_arrays($placar, $dados[$chaves[$i]]);
      }
    }
    // Ordena Dados
    $placar = $this->ordena_multiarray($placar, array("ordem"));
    // Formata placar
    $this->formataConteudo($placar, $cvs);
    // Gera Cabeçalho
    $cabecalho = $this->geraCabecalho($referencia_inicio, $referencia_termino, $tipo);
    if ($cvs) {
      $this->geraArquivoCSV($cabecalho, $placar);
    } else {
      return $this->geraListagemPlacar($placar, $cabecalho);
    }
  }

  function geraArquivoCSV($campos, $linhas, $resumido = false) {
    header("Content-type: application/csv;");
    header("Content-Disposition: attachment; filename=placar.csv;");
    foreach ($campos as $chave => $descricao) {
      if (is_array($descricao)) {
        foreach ($descricao as $sub_descricao) {
          if ($resumido) {
            $cabecalho[] = strip_tags($sub_descricao);
          } else {
            $cabecalho[] = strip_tags($chave)." » ".strip_tags($sub_descricao);
          }
        }
      } else {
        $cabecalho[] = $descricao;
      }
    }
    $conteudo[] = @implode(";", $cabecalho);
    do {
      $linha_atual = current($linhas);
      $linha_atual_tratada = array();
      foreach ($campos as $chave => $descricao) {
        if (is_array($descricao)) {
          foreach ($descricao as $descricao_chave => $descricao_nome) {
            $linha_atual_tratada[$descricao_chave] = $linha_atual[$descricao_chave] ? $this->limpaString($linha_atual[$descricao_chave]) : "";
          }
        } else {
          $linha_atual_tratada[$chave] = $linha_atual[$chave] ? $this->limpaString($linha_atual[$chave]) : "";
        }
      }
      $conteudo[] = @implode(";", $linha_atual_tratada);
    } while (next($linhas));
    $conteudo = strip_tags(str_replace("<br>", " ", implode("\n", $conteudo)));
    print $conteudo;
    exit();
  }
  
  function limpaString($string) {
    return trim(html_entity_decode(strip_tags($string)));
  }

  function formataConteudo(&$conteudo, $cvs = false) {
  	$agrupador = null;
    for ($i = 0; $i < count($conteudo); $i++) {
      if ($conteudo[$i]["subgrupo_agrupador"]) {
        $conteudo[$i]["descricao"] = ($conteudo[$i]["subgrupo_agrupador_id"] ? "&nbsp; &nbsp; " : "")."<img src=\"imagens/adicionar".($conteudo[$i]["subgrupo_agrupador"] == $conteudo[$i + 1]["subgrupo_item"] ? "" : "_disabled").".gif\" id=\"botao_subgrupo_".($agrupador != $conteudo[$i]["subgrupo_agrupador"] ? "{$agrupador}_" : "").$conteudo[$i]["subgrupo_agrupador"].($conteudo[$i]["subgrupo_agrupador_nome"] ? "_".$this->formataNome($conteudo[$i]["subgrupo_agrupador_nome"]) : "")."\" width=\"9\" height=\"9\" border=\"0\"> &nbsp;".($cvs ? $conteudo[$i]["descricao"] : $this->cortaNome($conteudo[$i]["descricao"], $conteudo[$i]["subgrupo_agrupador_id"] ? 23 : 25));
        $conteudo[$i]["itens"] = $conteudo[$i]["subgrupo_agrupador"] == $conteudo[$i + 1]["subgrupo_item"] ? 1 : 0;
      } elseif ($conteudo[$i]["agrupador"]) {
        $agrupador = $conteudo[$i]["agrupador"];
      } else {
        $conteudo[$i]["descricao"] = ($conteudo[$i]["subgrupo_agrupador_nome"] ? "&nbsp; &nbsp; &nbsp;" : "")."&nbsp; &nbsp; &nbsp;» {$conteudo[$i]["descricao"]}";
      }
      foreach ($conteudo[$i] as $campo => $valor) {
        if (substr($campo, -4) != "hint" && substr($campo, -4) != "_sec" && substr($campo, -9) != "cor_texto") {
          if (substr($campo, 0, 10) == "quantidade") {
            if ($conteudo[$i]["{$campo}_sec"]) {
              $conteudo[$i][$campo] = $valor ? number_format($valor - $conteudo[$i]["{$campo}_sec"], 0, ",", ".")."+".number_format($conteudo[$i]["{$campo}_sec"], 0, ",", ".") : "";
            } else {
              $conteudo[$i][$campo] = $valor ? number_format($valor, 0, ",", ".") : "";
            }
          }
          if (substr($campo, 0, 5) == "valor") {
            $conteudo[$i][$campo] = $this->formataDinheiro($valor);
          }
        }
      }
    }
  }

  function cortaNome($nome, $tamanho) {
    if (strlen($nome) <= $tamanho) {
      return $nome;
    } else {
      return substr($nome, 0, $tamanho - 3)."...";
    }
  }

  function geraCabecalho($referencia_inicio, $referencia_termino, $tipo) {
    if ($tipo == 1) {
      $cabecalho["descricao"] = "<div style=\"text-align:center;width:150px\">Descrição</div>";
      if ($referencia_inicio == date("Y-m-d")) {
        $cabecalho["Hoje"]["quantidade_dia"] = "<div style=\"width:29px\">Qtde</div>";
        $cabecalho["Hoje"]["valor_dia"] = "<div style=\"width:72px\">Valor</div>";
      } else {
        $cabecalho[date("d/m/Y", strtotime($referencia_inicio))]["quantidade_dia"] = "<div style=\"width:29px\">Qtde</div>";
        $cabecalho[date("d/m/Y", strtotime($referencia_inicio))]["valor_dia"] = "<div style=\"width:72px\">Valor</div>";
      }
      $cabecalho["Semana Atual"]["quantidade_semana_atual"] = "<div style=\"width:29px\">Qtde</div>";
      $cabecalho["Semana Atual"]["valor_semana_atual"] = "<div style=\"width:72px\">Valor</div>";
      $cabecalho["Semana Anterior"]["quantidade_semana_anterior"] = "<div style=\"width:29px\">Qtde</div>";
      $cabecalho["Semana Anterior"]["valor_semana_anterior"] = "<div style=\"width:72px\">Valor</div>";
      $cabecalho[$this->formataMes(date("m", strtotime($referencia_inicio)))]["quantidade_mes_atual"] = "<div style=\"width:29px\">Qtde</div>";
      $cabecalho[$this->formataMes(date("m", strtotime($referencia_inicio)))]["valor_mes_atual"] = "<div style=\"width:72px\">Valor</div>";
      $cabecalho[$this->formataMes(date("m", strtotime("$referencia_inicio 1 month ago")))]["quantidade_mes_anterior"] = "<div style=\"width:29px\">Qtde</div>";
      $cabecalho[$this->formataMes(date("m", strtotime("$referencia_inicio 1 month ago")))]["valor_mes_anterior"] = "<div style=\"width:72px\">Valor</div>";
    } elseif ($tipo == 2) {
      $cabecalho["descricao"] = "<div style=\"text-align:center;width:150px\">Descrição</div>";
      while (strtotime($referencia_inicio) <= strtotime($referencia_termino)) {
        $cabecalho[date("d/m/Y", strtotime($referencia_inicio))." (".$this->buscaDiaSemana($referencia_inicio).")"]["quantidade_dia_".date("Ymd", strtotime($referencia_inicio))] = "<div style=\"width:29px\">Qtde</div>";
        $cabecalho[date("d/m/Y", strtotime($referencia_inicio))." (".$this->buscaDiaSemana($referencia_inicio).")"]["valor_dia_".date("Ymd", strtotime($referencia_inicio))] = "<div style=\"width:72px\">Valor</div>";
        $referencia_inicio = date("Y-m-d", strtotime("$referencia_inicio tomorrow"));
      }
    } elseif ($tipo == 3) {
      $cabecalho["descricao"] = "<div style=\"text-align:center;width:150px\">Descrição</div>";
      while (strtotime($referencia_inicio) <= strtotime($referencia_termino)) {
        $mes = $this->formataMes(date("m", strtotime($referencia_inicio)), true);
        $ano = date("Y", strtotime($referencia_inicio));
        $semana = $this->buscaSemanaMes($referencia_inicio);
        $cabecalho["$mes / $ano ($semana)"]["quantidade_semana_{$mes}_{$ano}_{$semana}"] = "<div style=\"width:29px\">Qtde</div>";
        $cabecalho["$mes / $ano ($semana)"]["valor_semana_{$mes}_{$ano}_{$semana}"] = "<div style=\"width:72px\">Valor</div>";
        $referencia_inicio = date("Y-m-d", strtotime("$referencia_inicio next week"));
        if (date("w", strtotime($referencia_inicio)) != 0) {
          $referencia_inicio = date("Y-m-d", strtotime("$referencia_inicio last sunday"));
        }
      }
    } elseif ($tipo == 4) {
      $cabecalho["descricao"] = "<div style=\"text-align:center;width:150px\">Descrição</div>";
      while (strtotime($referencia_inicio) <= strtotime($referencia_termino)) {
        $mes = $this->formataMes(date("m", strtotime($referencia_inicio)));
        $ano = date("Y", strtotime($referencia_inicio));
        $cabecalho["$mes / $ano"]["quantidade_mes_{$mes}_{$ano}"] = "<div style=\"width:29px\">Qtde</div>";
        $cabecalho["$mes / $ano"]["valor_mes_{$mes}_{$ano}"] = "<div style=\"width:72px\">Valor</div>";
        $referencia_inicio = date("Y-m-d", strtotime("$referencia_inicio next week"));
      }
    }
    return $cabecalho;
  }
  
  function geraListagemPlacar($dados, $cabecalho) {
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, null, array(150), null, $this->geraAlinhamento(count($cabecalho)), $this->buscaCoresColunas($cabecalho));
    return $listagem->conteudo;
  }
  
  function geraAlinhamento($quantidade) {
    $alinhamento = array_fill(1, ($quantidade - 1) * 2, "right");
    $alinhamento[0] = "left";
    return $alinhamento;
  }
  
  function buscaCoresColunas($cabecalho) {
    $cor_atual = true;
    foreach ($cabecalho as $coluna => $valor) {
      if ($coluna == "descricao") {
        $cores["descricao"] = -15;
      } else {
        if ($cor_atual) {
          $cor_atual = false;
        } else {
          foreach ($valor as $campo => $dados) {
            $cores[$campo] = -15;
          }
          $cor_atual = true;
        }
      }
    }
    return $cores;
  }

  function buscaDadosPlacar($referencia_inicio, $referencia_termino = null, $tipo = 1) {
    // Inicializa Painel
    $painel = array();
    $this->ultima_atualizacao = 0;
    // Filtro
    $where[] = "plb_id = 5";
    if ($tipo == 1) {
      $where[] = "plc_data BETWEEN '".(date("Y-m-01", strtotime("$referencia_inicio 1 month ago")))."' AND '$referencia_inicio'";
    } else {
      if ($tipo == 3) {
        if (date("w", strtotime($referencia_inicio)) != 0) {
          $referencia_inicio = date("Y-m-d", strtotime("$referencia_inicio last sunday"));
        }
        if (date("w", strtotime($referencia_termino)) != 6) {
          $referencia_termino = date("Y-m-d", strtotime("$referencia_termino next saturday"));
        }
      }
      $where[] = "plc_data BETWEEN '$referencia_inicio' AND '$referencia_termino'";
    }
    $where = implode(" AND \n        ", $where);
    // Busca
    $query = "
      SELECT
        plc_id AS id,
        plc_data AS data,
        plc_geracao AS geracao,
        plc_conteudo AS conteudo
      FROM
        tab_placares
      WHERE
        $where
      ORDER BY
        plc_data";
		$query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->ultima_atualizacao < strtotime($dados["geracao"])) {
        $this->ultima_atualizacao = strtotime($dados["geracao"]);
      }
      $dados["conteudo"] = unserialize($dados["conteudo"]);
      $grupos = array_keys($dados["conteudo"]);
      $sufixos = $this->buscaSufixos($dados["data"], $referencia_inicio, $referencia_termino, $tipo);
      for ($i = 0; $i < count($grupos); $i++) {
        if ($dados["conteudo"][$grupos[$i]]) {
          $sub_grupos = array_keys($dados["conteudo"][$grupos[$i]]);
          for ($j = 0; $j < count($sub_grupos); $j++) {
            foreach ($sufixos as $sufixo) {
              $painel[$grupos[$i]][$sub_grupos[$j]]["descricao"] = $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["descricao"];
              if ($grupos[$i] == "clientes_ativos") {
                if (substr($sufixo, -5) != "atual") {
                  $painel[$grupos[$i]][$sub_grupos[$j]]["quantidade{$sufixo}"] = $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["quantidade"];
                  $painel[$grupos[$i]][$sub_grupos[$j]]["valor{$sufixo}"] = $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["valor"];
                }
              } else {
                $painel[$grupos[$i]][$sub_grupos[$j]]["quantidade{$sufixo}"] += $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["quantidade"];
                $painel[$grupos[$i]][$sub_grupos[$j]]["valor{$sufixo}"] += $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["valor"];
                if ($grupos[$i] == "clientes_cancelados" && ($sub_grupos[$j] == "Sem solicitação" || $sub_grupos[$j] == "Solicitação interna")) {
                  $painel[$grupos[$i]]["clientes_cancelados"]["quantidade{$sufixo}_sec"] += $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["quantidade"];
                  $painel[$grupos[$i]]["clientes_cancelados"]["valor{$sufixo}_sec"] += $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["valor"];
                }
              }
              
              $painel[$grupos[$i]][$sub_grupos[$j]]["ordem"] = $this->buscaGrupoOrdem($grupos[$i]).$dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["ordem"];
              $painel[$grupos[$i]][$sub_grupos[$j]]["agrupador"] = $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["agrupador"];
              $painel[$grupos[$i]][$sub_grupos[$j]]["agrupador_subgrupo"] = $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["agrupador_subgrupo"];
              $painel[$grupos[$i]][$sub_grupos[$j]]["subgrupo_agrupador"] = $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["subgrupo_agrupador"];
              $painel[$grupos[$i]][$sub_grupos[$j]]["subgrupo_agrupador_id"] = $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["subgrupo_agrupador_id"];
              $painel[$grupos[$i]][$sub_grupos[$j]]["subgrupo_agrupador_nome"] = $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["subgrupo_agrupador_nome"];
              $painel[$grupos[$i]][$sub_grupos[$j]]["subgrupo_item"] = $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["subgrupo_item"];
              $painel[$grupos[$i]][$sub_grupos[$j]]["cor_linha"] = $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["cor_linha"];
              $painel[$grupos[$i]][$sub_grupos[$j]]["cor_texto"] = $dados["conteudo"][$grupos[$i]][$sub_grupos[$j]]["cor_texto"];
              
            }
          }
        }
      }
    }
    // Retira Índices dos Sub-grupos
    $chaves = array_keys($painel);
    for ($i = 0; $i < count($chaves); $i++) {
      sort($painel[$chaves[$i]]);
    }
    return $painel;
  }
  
  function buscaGrupoOrdem($grupo) {
    $ordem["clientes_ativos"]         = "00";
    $ordem["clientes_novos"]          = "01";
    $ordem["fechamentos_vendas"]      = "02";
    $ordem["fechamentos_migracoes"]   = "03";
    $ordem["clientes_cancelados"]     = "04";
    $ordem["pedidos_de_cancelamento"] = "05";
    $ordem["clientes_retidos"]        = "06";
    $ordem["clientes_perdidos"]       = "07";
    return $ordem[$grupo];
  }

  function buscaSufixos($referencia, $referencia_inicio, $referencia_termino = null, $tipo = 1) {
    if ($tipo == 1) {
      if ($referencia_inicio == $referencia){
        $sufixos[] = "_dia";
      }
      if (strtotime($referencia) >= strtotime("$referencia_inicio last sunday") && strtotime($referencia) <= strtotime("$referencia_inicio next saturday")) {
        $sufixos[] = "_semana_atual";
      }
      if (strtotime($referencia) >= strtotime("$referencia_inicio 1 week ago last sunday") && strtotime($referencia) <= strtotime("$referencia_inicio 1 week ago next saturday")) {
        $sufixos[] = "_semana_anterior";
      }
      if (substr($referencia_inicio, 0, 7) == substr($referencia, 0, 7)) {
        $sufixos[] = "_mes_atual";
      }
      if (date("Y-m", strtotime("$referencia_inicio 1 month ago")) == substr($referencia, 0, 7)) {
        $sufixos[] = "_mes_anterior";
      }
    } elseif ($tipo == 2) {
      $sufixos[] = "_dia_".date("Ymd", strtotime($referencia));
    } elseif ($tipo == 3) {
      if (date("w", strtotime($referencia)) != 0) {
        $referencia = date("Y-m-d", strtotime("$referencia last sunday"));
        if (strtotime($referencia) <= strtotime($referencia_inicio)) {
          $referencia = date("Y-m-d", strtotime("$referencia next saturday"));
        }
      }
      $sufixos[] = "_semana_".$this->formataMes(date("m", strtotime($referencia)), true)."_".date("Y", strtotime($referencia))."_".$this->buscaSemanaMes($referencia);
    } elseif ($tipo == 4) {
      $sufixos[] = "_mes_".$this->formataMes(date("m", strtotime($referencia)))."_".date("Y", strtotime($referencia));
    }
    return $sufixos;
  }
  
  function buscaSemanaMes($referencia) {
    $dia = date("d", strtotime($referencia));
    $primeiro_dia = date("w", strtotime(date("Y-m-01", strtotime($referencia))));
    return sprintf("%02s", ceil(($dia + $primeiro_dia) / 7));
  }

  ####################
  ### Aplica Metas ###
  ####################

  function aplicaMetas(&$painel, $tipo) {
    if (($metas = $this->buscaMetas()) != false) {
      foreach ($metas as $grupo => $meta) {
        if ($painel[$grupo]) {
          for ($i = 0; $i < count($painel[$grupo]); $i++) {
            foreach ($meta as $campo => $dados) {
              if (($dados["agrupador_subgrupo"] && $painel[$grupo][$i]["agrupador_subgrupo"]) ||
                  ($dados["agrupador"] && $painel[$grupo][$i]["agrupador"] == $dados["agrupador"])) {
                foreach ($painel[$grupo][$i] as $campo_painel => $valor) {
                  if (strpos($campo_painel, $campo) !== false) {
                    $painel[$grupo][$i]["{$campo_painel}_cor_texto"] = $this->buscaCorMeta($painel[$grupo][$i][$campo_painel], $dados["valor"], $dados["tipo"]);
                    $painel[$grupo][$i]["{$campo_painel}_hint"] = "<center>Meta: {$dados["valor"]}".($dados["tipo"] == ">" ? "<br>(Mínimo)" : "<br>(Máximo)")."</center>";
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  function buscaCorMeta($valor, $meta, $tipo) {
    $valor = intval(preg_replace("/[^0-9\\+]/", "", strip_tags($valor)));
  	$meta = intval(preg_replace("/[^0-9]/", "", strip_tags($meta)));
    $indice = ($valor / $meta) * 100;
    if ($tipo == ">") {
      if ($indice < 85.0) {
        return "#FF0000"; // Vermelho
      } elseif ($indice < 100.0) {
        return "#FFFF00"; // Amarelo
      } else {
        return "#006600"; // Verde
      }
    } elseif ($tipo == "<") {
      if ($indice <= 85.0) {
        return "#006600"; // Verde
      } elseif ($indice <= 95.0) {
        return "#FFFF00"; // Amarelo
      } else {
        return "#FF0000"; // Vermelho
      }
    }
  }

  ###################
  ### Busca Metas ###
  ###################

  function buscaMetas($tipo = null) {
    // Clientes Novos
    $metas["clientes_novos"]["valor_dia"]["valor"] = "R$ 900,00";
    $metas["clientes_novos"]["valor_dia"]["agrupador"] = "clientes_novos";
    $metas["clientes_novos"]["valor_dia"]["tipo"] = ">";
    $metas["clientes_novos"]["valor_semana"]["valor"] = "R$ 4.900,00";
    $metas["clientes_novos"]["valor_semana"]["agrupador"] = "clientes_novos";
    $metas["clientes_novos"]["valor_semana"]["tipo"] = ">";
    $metas["clientes_novos"]["valor_mes"]["valor"] = "R$ 19.000,00";
    $metas["clientes_novos"]["valor_mes"]["agrupador"] = "clientes_novos";
    $metas["clientes_novos"]["valor_mes"]["tipo"] = ">";
    // Fechamentos (Vendas)
    $metas["fechamentos_vendas"]["valor_dia"]["valor"] = "R$ 2.067,70";
    $metas["fechamentos_vendas"]["valor_dia"]["agrupador_subgrupo"] = true;
    $metas["fechamentos_vendas"]["valor_dia"]["tipo"] = ">";
    $metas["fechamentos_vendas"]["valor_semana"]["valor"] = "R$ 10.338,50";
    $metas["fechamentos_vendas"]["valor_semana"]["agrupador_subgrupo"] = true;
    $metas["fechamentos_vendas"]["valor_semana"]["tipo"] = ">";
    $metas["fechamentos_vendas"]["valor_mes"]["valor"] = "R$ 41.354,00";
    $metas["fechamentos_vendas"]["valor_mes"]["agrupador_subgrupo"] = true;
    $metas["fechamentos_vendas"]["valor_mes"]["tipo"] = ">";
    // Fechamentos (Vendas) Individual
    $metas["fechamentos_vendas_individual"][0]["valor_dia"]["valor"] = "R$ 250,00";
    $metas["fechamentos_vendas_individual"][0]["valor_semana"]["valor"] = "R$ 1.250,00";
    $metas["fechamentos_vendas_individual"][0]["valor_mes"]["valor"] = "R$ 4.500,00";
    $metas["fechamentos_vendas_individual"][2]["valor_dia"]["valor"] = "R$ 300,00";
    $metas["fechamentos_vendas_individual"][2]["valor_semana"]["valor"] = "R$ 1.500,00";
    $metas["fechamentos_vendas_individual"][2]["valor_mes"]["valor"] = "R$ 5.500,00";
    $metas["fechamentos_vendas_individual"][3]["valor_dia"]["valor"] = "R$ 300,00";
    $metas["fechamentos_vendas_individual"][3]["valor_semana"]["valor"] = "R$ 1.500,00";
    $metas["fechamentos_vendas_individual"][3]["valor_mes"]["valor"] = "R$ 5.500,00";
    $metas["fechamentos_vendas_equipe"]["valor_dia"]["valor"] = "R$ 2.067,70";
    $metas["fechamentos_vendas_equipe"]["valor_semana"]["valor"] = "R$ 10.338,50";
    $metas["fechamentos_vendas_equipe"]["valor_mes"]["valor"] = "R$ 22.500,00";
    $metas["fechamentos_vendas_equipe"]["valor_ano"]["valor"] = "R$ 216.000,00";
    // Clientes Cancelados
    $metas["clientes_cancelados"]["quantidade_dia"]["valor"] = 6;
    $metas["clientes_cancelados"]["quantidade_dia"]["agrupador_subgrupo"] = true;
    $metas["clientes_cancelados"]["quantidade_dia"]["tipo"] = "<";
    $metas["clientes_cancelados"]["quantidade_semana"]["valor"] = 32;
    $metas["clientes_cancelados"]["quantidade_semana"]["agrupador_subgrupo"] = true;
    $metas["clientes_cancelados"]["quantidade_semana"]["tipo"] = "<";
    $metas["clientes_cancelados"]["quantidade_mes"]["valor"] = 130;
    $metas["clientes_cancelados"]["quantidade_mes"]["agrupador_subgrupo"] = true;
    $metas["clientes_cancelados"]["quantidade_mes"]["tipo"] = "<";
    // Clientes Perdidos
    $metas["clientes_perdidos"]["quantidade_dia"]["valor"] = 6;
    $metas["clientes_perdidos"]["quantidade_dia"]["agrupador_subgrupo"] = true;
    $metas["clientes_perdidos"]["quantidade_dia"]["tipo"] = "<";
    $metas["clientes_perdidos"]["quantidade_semana"]["valor"] = 32;
    $metas["clientes_perdidos"]["quantidade_semana"]["agrupador_subgrupo"] = true;
    $metas["clientes_perdidos"]["quantidade_semana"]["tipo"] = "<";
    $metas["clientes_perdidos"]["quantidade_mes"]["valor"] = 130;
    $metas["clientes_perdidos"]["quantidade_mes"]["agrupador_subgrupo"] = true;
    $metas["clientes_perdidos"]["quantidade_mes"]["tipo"] = "<";
    return $tipo? $metas[$tipo] : $metas;
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
  
  #####################
  ### Ver Comercial ###
  #####################
  
  function ver_comercial() {
    $dados = $this->buscaDadosPlacarComercial(date("Y-01-01"), date("Y-m-d"));
    $this->conteudo = $this->carregaArquivo("placar.ver_comercial.inc.php");
    $this->conteudo = str_replace("[placar]", $this->geraPlacarComercial($dados), $this->conteudo);
    $this->conteudo = str_replace("[placar_total]", $this->geraPlacarTotal($dados), $this->conteudo);
    $this->conteudo = str_replace("[data_hora]", $this->buscaDataHora(), $this->conteudo);
    print $this->conteudo;
    exit();
  }
  
  function buscaDataHora() {
    $data_hora = explode(" ", date('d/m/Y H:i'));
    return "<div style=\"font-size:24px;font-weight:bold\">$data_hora[0]</div><div style=\"font-size:32px;font-weight:bold\">$data_hora[1]</div>";
  }
  
  function geraPlacarTotal($vendedores) {
    $metas = $this->buscaMetas("fechamentos_vendas_equipe");
    foreach ($vendedores as $dados) {
      if ($dados["comissionamento"] == 1) {
        $total_agentes_mensal += $dados["mes"]["valor"];
        $total_agentes_anual += $dados["ano"]["valor"];
      } else {
        $total_equipe_mensal += $dados["mes"]["valor"];
        $total_equipe_anual += $dados["ano"]["valor"];
      }
      $total_mensal += $dados["mes"]["valor"];
      $total_anual += $dados["ano"]["valor"];
    }
    $meta_mensal = intval(preg_replace("/[^0-9]/", "", $metas["valor_mes"]["valor"]), 10);
    $meta_anual = intval(preg_replace("/[^0-9]/", "", $metas["valor_ano"]["valor"]), 10);
    $conteudo[] = "<table width=\"100%\" cellspacing=\"3\" cellpadding=\"0\" border=\"0\">";
    $conteudo[] = "<tr>";
    $conteudo[] = "<td width=\"50%\">";
    $conteudo[] = "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"3\" border=\"0\">";
    $conteudo[] = "<tr>";
    $conteudo[] = "<td width=\"55%\" align=\"left\" class=\"txtBranco11\" style=\"background-color:#505050;font-size:18px;font-weight:bold;\">Meta Mensal (Equipe)</td>";
    $conteudo[] = "<td align=\"right\" class=\"txtAzulEscuro11\" style=\"background-color:#E8E8E8;font-size:24px;font-weight:bold;\">".$this->formataDinheiro($meta_mensal)."</td>";
    $conteudo[] = "</tr>";
    $conteudo[] = "<tr>";
    $conteudo[] = "<td align=\"left\" class=\"txtBranco11\" style=\"background-color:#505050;font-size:18px;font-weight:bold;\">Realizado Mensal (Equipe)</td>";
    $conteudo[] = "<td align=\"right\" class=\"txtAzulEscuro11\" style=\"background-color:#E8E8E8;font-size:24px;font-weight:bold;\">".$this->formataDinheiro($total_equipe_mensal, "R$", $meta_mensal)."</td>";
    $conteudo[] = "</tr>";
    $conteudo[] = "<tr>";
    $conteudo[] = "<td></td>";
    $conteudo[] = "</tr>";
    $conteudo[] = "<tr>";
    $conteudo[] = "<td align=\"left\" class=\"txtBranco11\" style=\"background-color:#505050;font-size:18px;font-weight:bold;\">Realizado Mensal (Agentes)</td>";
    $conteudo[] = "<td align=\"right\" class=\"txtAzulEscuro11\" style=\"background-color:#E8E8E8;font-size:24px;font-weight:bold;\">".$this->formataDinheiro($total_agentes_mensal)."</td>";
    $conteudo[] = "</tr>";
    $conteudo[] = "<tr>";
    $conteudo[] = "<td></td>";
    $conteudo[] = "</tr>";
    $conteudo[] = "<tr>";
    $conteudo[] = "<td align=\"left\" class=\"txtBranco11\" style=\"background-color:#505050;font-size:18px;font-weight:bold;\">Realizado Mensal (Total)</td>";
    $conteudo[] = "<td align=\"right\" class=\"txtAzulEscuro11\" style=\"background-color:#E8E8E8;font-size:24px;font-weight:bold;\">".$this->formataDinheiro($total_mensal)."</td>";
    $conteudo[] = "</tr>";
    $conteudo[] = "</table>";
    $conteudo[] = "</td>";
    $conteudo[] = "<td width=\"50%\">";
    $conteudo[] = "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"3\" border=\"0\">";
    $conteudo[] = "<tr>";
    $conteudo[] = "<td width=\"55%\" align=\"left\" class=\"txtBranco11\" style=\"background-color:#505050;font-size:18px;font-weight:bold;\">Meta Anual (Equipe)</td>";
    $conteudo[] = "<td align=\"right\" class=\"txtAzulEscuro11\" style=\"background-color:#E8E8E8;font-size:24px;font-weight:bold;\">".$this->formataDinheiro($meta_anual)."</td>";
    $conteudo[] = "</tr>";
    $conteudo[] = "<tr>";
    $conteudo[] = "<td align=\"left\" class=\"txtBranco11\" style=\"background-color:#505050;font-size:18px;font-weight:bold;\">Realizado Anual (Equipe)</td>";
    $conteudo[] = "<td align=\"right\" class=\"txtAzulEscuro11\" style=\"background-color:#E8E8E8;font-size:24px;font-weight:bold;\">".$this->formataDinheiro($total_equipe_anual, "R$", $meta_anual)."</td>";
    $conteudo[] = "</tr>";
    $conteudo[] = "<tr>";
    $conteudo[] = "<td></td>";
    $conteudo[] = "</tr>";
    $conteudo[] = "<tr>";
    $conteudo[] = "<td align=\"left\" class=\"txtBranco11\" style=\"background-color:#505050;font-size:18px;font-weight:bold;\">Realizado Anual (Agentes)</td>";
    $conteudo[] = "<td align=\"right\" class=\"txtAzulEscuro11\" style=\"background-color:#E8E8E8;font-size:24px;font-weight:bold;\">".$this->formataDinheiro($total_agentes_anual)."</td>";
    $conteudo[] = "</tr>";
    $conteudo[] = "<tr>";
    $conteudo[] = "<td></td>";
    $conteudo[] = "</tr>";
    $conteudo[] = "<tr>";
    $conteudo[] = "<td align=\"left\" class=\"txtBranco11\" style=\"background-color:#505050;font-size:18px;font-weight:bold;\">Realizado Anual (Total)</td>";
    $conteudo[] = "<td align=\"right\" class=\"txtAzulEscuro11\" style=\"background-color:#E8E8E8;font-size:24px;font-weight:bold;\">".$this->formataDinheiro($total_anual)."</td>";
    $conteudo[] = "</tr>";
    $conteudo[] = "</table>";
    $conteudo[] = "</td>";
    $conteudo[] = "</tr>";
    $conteudo[] = "</table>";
    return @implode($conteudo);
  }
  
  function geraPlacarComercial($vendedores) {
    $metas = $this->buscaMetas("fechamentos_vendas_individual");
    $i = 1;
    $linha = 1;
    $conteudo[] = "<table width=\"100%\" cellspacing=\"3\" cellpadding=\"0\" border=\"0\">";
    foreach ($vendedores as $dados) {
      if ($dados["status_id"] != 1) {
        continue;
      }
      if ($dados["comissionamento"] > 1 && $linha == 1) {
        $conteudo[] = "</tr>";
        $i = 1;
        $linha++;        
      }
      if ($i == 1) {
        $conteudo[] = "<tr>";
        $conteudo[] = "<td>";
        $conteudo[] = "<table width=\"300\" cellspacing=\"1\" cellpadding=\"3\" border=\"0\">";
        $conteudo[] = "<tr class=\"txtBranco11\" style=\"background-color:#505050;font-size:24px;font-weight:bold;\"><td align=\"center\" colspan=\"2\">Metas</td></tr>";
        $conteudo[] = "<tr class=\"txtBranco11\" style=\"background-color:#505050;font-size:11px\"><td align=\"center\" colspan=\"2\">".($linha == 1 ? "Telemarketing Ativo" : "Telemarketing Passivo & Vendedores Externos")."</td></tr>";
        $conteudo[] = "<tr class=\"txtAzulEscuro11\" style=\"font-size:24px;font-weight:bold\" bgcolor=\"#E8E8E8\"><td align=\"left\">Diária</td><td align=\"right\">{$metas[($linha == 1 ? 0 : 2)]["valor_dia"]["valor"]}</td></tr>";
        $conteudo[] = "<tr class=\"txtAzulEscuro11\" style=\"font-size:24px;font-weight:bold\" bgcolor=\"#E8E8E8\"><td align=\"left\">Semanal</td><td align=\"right\">{$metas[($linha == 1 ? 0 : 2)]["valor_semana"]["valor"]}</td></tr>";
        $conteudo[] = "<tr class=\"txtAzulEscuro11\" style=\"font-size:24px;font-weight:bold\" bgcolor=\"#E8E8E8\"><td align=\"left\">Mensal</td><td align=\"right\">{$metas[($linha == 1 ? 0 : 2)]["valor_mes"]["valor"]}</td></tr>";
        $conteudo[] = "</table>";
        $conteudo[] = "</td>";
      }
      $conteudo[] = "<td width=\"25%\">";
      $conteudo[] = "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"3\" border=\"0\">";
      $conteudo[] = "<tr class=\"txtBranco11\" style=\"background-color:#505050;font-size:24px;font-weight:bold;\"><td align=\"center\">".$this->buscaPrimeiroNome($dados["nome"])."</td></tr>";
      $conteudo[] = "<tr class=\"txtBranco11\" style=\"background-color:#505050;font-size:11px\"><td align=\"center\">".$this->buscaCargo($dados["comissionamento"])."</td></tr>";
      $conteudo[] = "<tr class=\"txtAzulEscuro11\" style=\"font-size:24px;font-weight:bold\" bgcolor=\"#E8E8E8\"><td align=\"right\">".$this->formataDinheiro($dados["dia"]["valor"], "R$", $metas[$dados["comissionamento"]]["valor_dia"]["valor"])."</td></tr>";
      $conteudo[] = "<tr class=\"txtAzulEscuro11\" style=\"font-size:24px;font-weight:bold\" bgcolor=\"#E8E8E8\"><td align=\"right\">".$this->formataDinheiro($dados["semana"]["valor"], "R$", $metas[$dados["comissionamento"]]["valor_semana"]["valor"])."</td></tr>";
      $conteudo[] = "<tr class=\"txtAzulEscuro11\" style=\"font-size:24px;font-weight:bold\" bgcolor=\"#E8E8E8\"><td align=\"right\">".$this->formataDinheiro($dados["mes"]["valor"], "R$", $metas[$dados["comissionamento"]]["valor_mes"]["valor"])."</td></tr>";
      $conteudo[] = "</table>";
      $conteudo[] = "</td>";
      if ($i == 4) {
        $conteudo[] = "</tr>";
        $linha++;
        $i = 1;
      } else {
        $i++;
      }
    }
    $conteudo[] = "</table>";
    return @implode($conteudo);
  }
  
  function buscaDadosPlacarComercial($referencia_inicio, $referencia_termino) {
    $query = "
      SELECT
        plc_id AS id,
        plc_data AS data,
        plc_geracao AS geracao,
        plc_conteudo AS conteudo,
        IF(plc_data = '".date("Y-m-d")."', 1, 0) AS dia_atual,
        IF(plc_data BETWEEN '".(date("w") != 0 ? date("Y-m-d", strtotime("last sunday")) : date("Y-m-d"))."' AND '".date("Y-m-d")."', 1, 0) AS semana_atual,
        IF(plc_data BETWEEN '".date("Y-m-01")."' AND '".date("Y-m-d")."', 1, 0) AS mes_atual
      FROM
        tab_placares
      WHERE
        plc_data BETWEEN '$referencia_inicio' AND '$referencia_termino' AND
        plb_id = 5
      ORDER BY
        plc_data";
		$query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["conteudo"] = unserialize($dados["conteudo"]);
      foreach (array("fechamentos_vendas", "fechamentos_migracoes") as $tipo) {
        if ($dados["conteudo"][$tipo]) {
          foreach ($dados["conteudo"][$tipo] as $nome => $conteudo) {
            if ($conteudo["subgrupo_agrupador_nome"] == "Vendedor") {
            	$usuario = $this->buscaUsuarioAtivo($conteudo["usuario_id"]);
              if ($usuario) {
                $nome = $usuario["nome"];
                $painel["{$usuario["comissionamento"]}_{$nome}"]["nome"] = $usuario["nome"];
                $painel["{$usuario["comissionamento"]}_{$nome}"]["comissionamento"] = $usuario["comissionamento"];
                $painel["{$usuario["comissionamento"]}_{$nome}"]["status_id"] = $usuario["status_id"];
                if ($dados["dia_atual"]) {
                  $painel["{$usuario["comissionamento"]}_{$nome}"]["dia"]["quantidade"] += $conteudo["quantidade"];
                  $painel["{$usuario["comissionamento"]}_{$nome}"]["dia"]["valor"] += $conteudo["valor"];
                }
                if ($dados["semana_atual"]) {
                  $painel["{$usuario["comissionamento"]}_{$nome}"]["semana"]["quantidade"] += $conteudo["quantidade"];
                  $painel["{$usuario["comissionamento"]}_{$nome}"]["semana"]["valor"] += $conteudo["valor"];
                }
                if ($dados["mes_atual"]) {
                  $painel["{$usuario["comissionamento"]}_{$nome}"]["mes"]["quantidade"] += $conteudo["quantidade"];
                  $painel["{$usuario["comissionamento"]}_{$nome}"]["mes"]["valor"] += $conteudo["valor"];
                }
                $painel["{$usuario["comissionamento"]}_{$nome}"]["ano"]["quantidade"] += $conteudo["quantidade"];
                $painel["{$usuario["comissionamento"]}_{$nome}"]["ano"]["valor"] += $conteudo["valor"];
              }
            }
          }
        }
      }
    }
    @ksort($painel);
    return $painel;
  }
  
  function buscaUsuarioAtivo($usuario_id) {
    if ($this->usuarios[$usuario_id]) {
      return $this->usuarios[$usuario_id];
    }
    $query = "
      SELECT DISTINCT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome,
        IF(rel_usu_gru_fazparte.gru_id = 2, -1, IF(rel_usu_orf_ors_exerce.ors_id = 24, 0, IF(rel_usu_orf_ors_exerce.ors_id = 9, 2, IF(rel_usu_orf_ors_exerce.ors_id = 8, 3, 1)))) AS comissionamento,
        tab_usuarios.uss_id AS status_id
      FROM
        tab_usuarios
        LEFT OUTER JOIN rel_usu_orf_ors_exerce ON (tab_usuarios.usu_id = rel_usu_orf_ors_exerce.usu_id)
        LEFT OUTER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
      WHERE
        tab_usuarios.usu_id = $usuario_id AND
        (rel_usu_orf_ors_exerce.ors_id IN (8, 9, 24) OR rel_usu_gru_fazparte.gru_id IN (2, 34))";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($dados["comissionamento"] == 1) {
        $dados["nome"] = "Agentes";
      }
      $this->usuarios[$usuario_id] = $dados;
      return $dados;
    }
  }

}

?>
