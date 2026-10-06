<?

class integracao_voip {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->acao = $_GET["a"] ? $_GET["a"] : "login";
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

  #############
  ### Login ###
  #############

  function login() {
    if (!$_POST) {
      $this->conteudo = $this->carregaArquivo("integracao.voip.login.inc.php");
      $this->conteudo = str_replace("[mensagem]",($_GET["mensagem"]?"<div align=center style='padding-bottom:10px; padding-top:10px; color:red;'>{$_GET["mensagem"]}</div>":"&nbsp;"),$this->conteudo);
    } elseif (($voip_conta = $this->buscaDadosVoipContaPorUsuarioPorSenha($_POST["usuario"], $_POST["senha"])) != false) {
      $this->registraLogin($voip_conta);
      $this->transicao("./?m=voip&a=chamadas");
    } else {
      $this->transicao("./?m=voip&a=login&mensagem=".urlencode("Usuário e/ou senha incorreto(s)!"));
    }
  }

  function buscaDadosVoipConta($id) {
    if (!$this->voip_contas) $this->voip_contas = controle::instanciaObjeto("voip_contas", $this->bancodedados, $this->permissoes, false);
    return $this->voip_contas->buscaDadosVoipConta($id);
  }

  function buscaDadosVoipContaPorUsuarioPorSenha($usuario, $senha, $md5 = false) {
    if (!$this->voip_contas) $this->voip_contas = controle::instanciaObjeto("voip_contas", $this->bancodedados, $this->permissoes, false);
    return $this->voip_contas->buscaDadosVoipContaPorUsuarioPorSenha($usuario, $senha, $md5, false);
  }

  function registraLogin($voip_conta) {
    $_SESSION["conta"] = $voip_conta["id"];
    $_SESSION["senha"] = md5($voip_conta["id"].$voip_conta["usuario"].$voip_conta["senha"].$_SESSION["id"]);
  }

  function verificaLogin($voip_conta) {
    return (md5($voip_conta["id"].$voip_conta["usuario"].$voip_conta["senha"].$_SESSION["id"]) == $_SESSION["senha"]) ? true : false;
  }

  function registraLogout() {
    unset($_SESSION["conta"]);
    unset($_SESSION["senha"]);
  }

  ##############
  ### Logout ###
  ##############

  function logout() {
    $this->registraLogout();
    $this->transicao("./?m=voip&a=login&mensagem=".urlencode("Logout realizado com sucesso!"));
  }

	################
  ### Chamadas ###
  ################

  function chamadas() {
    if ($_SESSION["conta"]) $voip_conta = $this->buscaDadosVoipConta($_SESSION["conta"]);
    if ($this->verificaLogin($voip_conta)) {
      if (!$_GET["pagina"]) $_GET["pagina"] = 1;
      $this->conteudo = $this->carregaArquivo("integracao.voip.chamadas.inc.php");
      $this->conteudo = str_replace("[conta_usuario]",$voip_conta["usuario"],$this->conteudo);
      $this->conteudo = str_replace("[conta_status]",$voip_conta["status_nome"],$this->conteudo);
      $this->conteudo = str_replace("[conta_situacao]",$voip_conta["situacao"],$this->conteudo);
      $this->conteudo = str_replace("[saldo_atual]",$this->formataDinheiro($voip_conta["credito"]["saldo"]),$this->conteudo);
      $this->conteudo = str_replace("[saldo_cheque_especial]",$this->formataDinheiro($voip_conta["credito"]["cheque_especial_limite"]),$this->conteudo);
      $this->conteudo = str_replace("[saldo_disponivel]",$this->formataDinheiro($voip_conta["credito"]["saldo"]+$voip_conta["credito"]["cheque_especial_limite"]),$this->conteudo);
      if (!$this->voip_contas) $this->voip_contas = controle::instanciaObjeto("voip_contas", $this->bancodedados, $this->permissoes, false);
      $consumo = $this->voip_contas->buscaConsumoVoipConta($voip_conta["id"]);
      $this->conteudo = str_replace("[consumo_dia]", $this->formataDinheiro($consumo["dia"]["valor"]) . " / " . $this->formataDuracao($consumo["dia"]["duracao"]),$this->conteudo);
      $this->conteudo = str_replace("[consumo_semana]", $this->formataDinheiro($consumo["semana"]["valor"]) . " / " . $this->formataDuracao($consumo["semana"]["duracao"]),$this->conteudo);
      $this->conteudo = str_replace("[consumo_mes]", $this->formataDinheiro($consumo["mes"]["valor"]) . " / " . $this->formataDuracao($consumo["mes"]["duracao"]),$this->conteudo);
      foreach ($_GET AS $get_parametro_nome => $get_parametro_valor) if (strpos($get_parametro_nome,"filtro_") === 0) $filtro[substr($get_parametro_nome,7)] = $get_parametro_valor;
      if (!$this->voip_chamadas) $this->voip_chamadas = controle::instanciaObjeto("voip_chamadas", $this->bancodedados, $this->permissoes, false);
      $chamadas = $this->voip_chamadas->buscaVoipChamadasVoipContaListagemInternet($voip_conta["id"], $filtro, $_GET["pagina"]);
      $this->conteudo = str_replace("[chamadas_linhas]", $this->geraVoipChamadasListagemLinhas($chamadas["itens"]),$this->conteudo);
      $this->conteudo = str_replace("[chamadas_rodape]", $this->geraVoipChamadasListagemRodape($chamadas["dados"]),$this->conteudo);
      $this->conteudo = str_replace("[filtro_pagina]", $_GET["pagina"],$this->conteudo);
      $this->conteudo = str_replace("[filtro_datahora_inicio]", $_GET["filtro_datahora_inicio"], $this->conteudo);
      $this->conteudo = str_replace("[filtro_datahora_termino]", $_GET["filtro_datahora_termino"], $this->conteudo);
      $this->conteudo = str_replace("[filtro_destino]", $_GET["filtro_destino"], $this->conteudo);
      $this->conteudo = str_replace("[filtro_duracao_inicio]", $_GET["filtro_duracao_inicio"], $this->conteudo);
      $this->conteudo = str_replace("[filtro_duracao_termino]", $_GET["filtro_duracao_termino"], $this->conteudo);
      $this->conteudo = str_replace("[filtro_valor_inicio]", $_GET["filtro_valor_inicio"], $this->conteudo);
      $this->conteudo = str_replace("[filtro_valor_termino]", $_GET["filtro_valor_termino"], $this->conteudo);
    } else {
      $this->transicao("./?m=voip&a=login");
    }
  }

  function formataDinheiro($valor, $moeda = 'R$', $casas_decimais = 2) {
    $valor = number_format($valor, $casas_decimais, ",", ".");
    if ($moeda) $valor = $moeda." ".$valor;
    return $valor;
  }

  function formataDuracao($duracao) {
  	$horas = 0;
  	$minutos = 0;
    while ($duracao >= 3600) {
    	$horas++;
    	$duracao -= 3600;
    }
    while ($duracao >= 60) {
    	$minutos++;
    	$duracao -= 60;
    }
    $segundos = $duracao;
    return sprintf("%02d:%02d:%02d", $horas, $minutos, $segundos);
  }

  function formataDataHora($datahora, $formato = "d/m/Y H:i:s") {
    if ($datahora) {
      $campos["Y"] = substr($datahora,0,4);
      $campos["m"] = substr($datahora,5,2);
      $campos["d"] = substr($datahora,8,2);
      $campos["H"] = substr($datahora,11,2);
      $campos["i"] = substr($datahora,14,2);
      $campos["s"] = substr($datahora,17,2);
      foreach ($campos AS $id => $valor) $formato = str_replace($id, $valor, $formato);
      return $formato;
    }
  }

  function geraVoipChamadasListagemLinhas ($chamadas) {
    if ($chamadas) {
      foreach ($chamadas AS $chamada) $linhas[] = "<td align=center>".@implode("</td><td align=center>",$chamada)."</td>";
      return "<tr>".@implode("</tr><tr>",$linhas)."</tr>";
    } else {
      return '<tr><td align="center" colspan="6">Nenhuma chamada disponível</td></tr>';
    }
  }

  function geraVoipChamadasListagemRodape($dados) {
    if ($dados) {
      $rodape .= '<tr><td colspan="6">';
      $rodape .= '<table border="0" width="100%"><tr><td align="left">';
      $rodape .= 'Registros encontrados: ' . $dados["quantidade_itens"] . ' (Exibindo ' . (($dados["quantidade_itens_pagina"]*($dados["pagina_atual"]-1))+1) . ($dados["pagina_atual"]!= $dados["quantidade_paginas"] ? ' a ' . (($dados["quantidade_itens_pagina"]*($dados["pagina_atual"]-1))+$dados["quantidade_itens_pagina_atual"]) : '') . ')';
      $rodape .= '</td>';
      $rodape .= '<td align="right">';
      $rodape .= $this->geraPaginacao($dados["quantidade_paginas"], $dados["pagina_atual"], "alert(1)");
      $rodape .= '</td></tr></table>';
      $rodape .= '</td></tr>';
      return $rodape;
    }
  }

  function geraPaginacao($paginas_total, $pagina, $link) {
    $paginas_exibir = 5;
    if ($paginas_total > 1) {
      $pagina_inicial = $this->buscaPaginaInicial($pagina, $paginas_total, $paginas_exibir);
      $pagina_final = ($pagina_inicial + $paginas_exibir - 1 < $paginas_total) ? $pagina_inicial + $paginas_exibir - 1 : $paginas_total;
      $paginas_conteudo[] = "<span" . ($pagina == 1 ? "" : " style=\"cursor:pointer\" onclick=\"mudaPagina(0, -1)\"") . "><</span>";
      for ($i = $pagina_inicial; $i <= $pagina_final; $i++) {
        if ($i == $pagina) {
          $paginas_conteudo[] = "<span style=\"font-weight:bold;margin-bottom:3px\">$i</span>";
        } else {
          $paginas_conteudo[] = "<span style=\"cursor:pointer\" onclick=\"mudaPagina($i, 0)\">$i</span>";
        }
      }
      $paginas_conteudo[] = "<span" . ($pagina == $paginas_total ? "" : " style=\"cursor:pointer\" onclick=\"mudaPagina(0, 1)\"") . ">></span>";
      return implode("&nbsp;|&nbsp;", $paginas_conteudo);
    } else {
      return "";
    }
  }

  function buscaPaginaInicial($pagina, $paginas_total, $paginas_exibir) {
    if ($pagina <= ceil($paginas_exibir / 2) || $paginas_total <= $paginas_exibir) {
      return 1;
    } else {
      if ($pagina < ($paginas_total - floor($paginas_exibir / 2))) {
        return $pagina - floor($paginas_exibir / 2);
      } else {
        return $paginas_total - floor($paginas_exibir / 2) - 2;
      }
    }
  }

}

?>
