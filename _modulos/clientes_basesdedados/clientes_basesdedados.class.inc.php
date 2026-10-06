<?

class clientes_basesdedados {

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
      if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
  		$this->buscaDadosAcao(empty($_GET["a"]) ? "listar" : $_GET["a"]);
  		$this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
    }
	}

	function geraTitulo() {
		$titulo	= '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
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

	function buscaDadosCliente($cliente_id) {
	  $query = "SELECT cli_id AS id, cli_nome AS nome FROM tab_clientes WHERE cli_id = $cliente_id";
		$query = $this->bancodedados->query($query);
		$this->dados_cliente = $this->bancodedados->resultadoArray($query);
	}

  function buscaDadosItem($id) {
    $query = "
      SELECT 
        tab_contratos.cor_codigo AS contrato,
        tab_clientes.cli_id AS cliente_id,
        tab_clientes.cli_nome AS cliente_nome,
        tab_clientes_basesdedados.clb_id AS id,
        tab_clientes_basesdedados.clb_basededados AS basededados,
        tab_clientes_basesdedados.clb_login AS login,
        tab_clientes_basesdedados.clb_capacidade AS capacidade,
        tab_clientes_basesdedados.clb_comentarios AS comentarios,
        tab_clientes_basesdedados_status.cbs_id AS status_id,
        tab_clientes_basesdedados_status.cbs_nome AS status_nome,
        tab_clientes_basesdedados_tipos.cbt_id AS tipo_id,
        tab_clientes_basesdedados_tipos.cbt_nome AS tipo_nome,
        tab_assinaturas.ass_id AS assinatura_id,
        tab_assinaturas.ast_id AS assinatura_status,
        tab_planos_tipos.pls_nome AS servico
      FROM
        tab_clientes_basesdedados
	      INNER JOIN tab_clientes_basesdedados_status ON (tab_clientes_basesdedados_status.cbs_id = tab_clientes_basesdedados.cbs_id)
	      INNER JOIN tab_clientes_basesdedados_tipos ON (tab_clientes_basesdedados_tipos.cbt_id = tab_clientes_basesdedados.cbt_id)
        INNER JOIN rel_ass_cor_estacontido ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)
        INNER JOIN tab_contratos ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
        INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_basesdedados.ass_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id) 
  			INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id) 
      WHERE
        tab_clientes_basesdedados.clb_id = $id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
  }

  function buscaAssinaturas($id) {
    $query = "
			SELECT 
  			tab_assinaturas.ass_id AS assinatura_id,
  			tab_contratos.cor_codigo AS contrato,
  			tab_planos_servicos.pls_nome AS servico,
  			COUNT(tab_assinaturas.ass_id) AS quantidade_assinaturas,
 				tab_planos.pla_parametros AS parametros
			FROM
  			rel_ass_cor_estacontido
  			INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
				LEFT OUTER JOIN tab_assinaturas assinatura_pai ON ( tab_assinaturas.ass_pai = assinatura_pai.ass_id ) 
				LEFT OUTER JOIN tab_planos ON (IF(tab_assinaturas.ass_pai IS NULL , tab_assinaturas.pla_id = tab_planos.pla_id , assinatura_pai.pla_id = tab_planos.pla_id)) 
  			INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id) 
  			INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id)
			WHERE
	      (tab_assinaturas.ast_id IN (2, 3)) AND
				(tab_assinaturas.cli_id = $id)
			GROUP BY
  			contrato
			ORDER BY
  			rel_ass_cor_estacontido.ass_id";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->linhas($query)) {
    	while (($resultado = $this->bancodedados->resultadoArray($query)) != false) {
    		$parametros = unserialize($resultado["parametros"]); 
    		$basesdedados_quantidade = $this->buscaQuantidade($resultado["assinatura_id"], "basesdedados");
    		$disponivel = ($parametros["basesdedados_quantidade"] * $resultado["quantidade_assinaturas"]) - $basesdedados_quantidade; 
        if ($disponivel > 0) {
    		  $dados[$resultado["assinatura_id"]] = $resultado["contrato"]." - ".$resultado["servico"]." ($disponivel disponíve".(($disponivel > 1) ? "is" : "l").")";
        }
    	}
      return $dados;
    }
  }
  
  function buscaParametros($ass_id) {
    $query = "
      SELECT 
        tab_planos.pla_parametros AS parametros
      FROM
     		tab_assinaturas
				LEFT OUTER JOIN tab_assinaturas assinatura_pai ON ( tab_assinaturas.ass_pai = assinatura_pai.ass_id ) 
				LEFT OUTER JOIN tab_planos ON (IF(tab_assinaturas.ass_pai IS NULL , tab_assinaturas.pla_id = tab_planos.pla_id , assinatura_pai.pla_id = tab_planos.pla_id)) 
      WHERE
        tab_assinaturas.ass_id = $ass_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	$dados = unserialize($dados["parametros"]);
      return $dados;
    }
  }
  
  function buscaQuantidade($ass_id, $servico) {
    $query = "
      SELECT 
        tab_assinaturas.ass_id AS ass_id
      FROM
        tab_assinaturas
        INNER JOIN tab_clientes_$servico ON (tab_assinaturas.ass_id = tab_clientes_$servico.ass_id)
      WHERE
        tab_assinaturas.ass_id = $ass_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->linhas($query);
  }

  function buscaCliente($cli_id) {
    $query = "
      SELECT 
        tab_clientes.cli_nome AS nome
      FROM
        tab_clientes
      WHERE
        tab_clientes.cli_id = $cli_id";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->linhas($query)) {
    	$resultado = $this->bancodedados->resultadoArray($query);
      return $resultado ["nome"];
    }
  }

	function geraSelect($itens, $item_selecionado = "", $item_nulo = "") {
    if (sizeof($itens)>0) {
      if ($item_nulo) $select .= '<option></option>';
  		do {
  			$item = @current($itens);
  			(@key($itens) == $item_selecionado) ? $complemento = " selected" : $complemento = "";
  			$select .= '<option value="' . @key($itens) . '" ' . $complemento . '>' . $item . '</option>';
  		} while (@next($itens));
    }
  	return $select;
  }
	
  function buscaServidores() {
  	$query = "SELECT ser_id AS id, ser_nome AS nome FROM tab_servidores WHERE sep_id = '3' ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$servidor[$dados["id"]] = $dados["nome"];
    }
    return $servidor;
  }
	
  function buscaTipos() {
  	$query = "SELECT cbt_id AS id, cbt_nome AS nome FROM tab_clientes_basesdedados_tipos ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

	function buscaUnidadesCapacidade() {
  	$unidadecapacidade["1"] = "KB";
	  $unidadecapacidade["2"] = "MB";
	  $unidadecapacidade["3"] = "GB";
	  $unidadecapacidade["4"] = "TB";
		return $unidadecapacidade;
	}

	##########################
	######### Fechar #########
	##########################
  
	function fechar() {
		unset($this->conteudo);
		if (!empty($_GET["cliente_id"])) {
			$this->conteudo .= '<script type="text/javascript">if (!window.opener.closed && window.opener.buscaBasesdedados) window.opener.buscaBasesdedados('.$_GET["cliente_id"].', 1)</script>';
		}
		$this->conteudo .= '<script type="text/javascript">window.close();</script>';
	}

	#######################
	######### Ver #########
	#######################
  
	function ver() {
		$this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item)) {
      $unidade_capacidade = floor(log($this->dados_item["capacidade"], 1024));
      $unidades = $this->buscaUnidadesCapacidade();
      $capacidade = round($this->dados_item["capacidade"] / pow(1024, $unidade_capacidade), 2)." ".$unidades[$unidade_capacidade];
    	$this->conteudo .= $this->carregaArquivo("clientes_basesdedados.ver.inc.php");
    	$this->conteudo = str_replace('[contrato]', $this->dados_item["contrato"]." - ".$this->dados_item["servico"], $this->conteudo);
    	$this->conteudo = str_replace('[cliente_nome]', $this->dados_item["cliente_nome"], $this->conteudo);
    	$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
			$this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
			$this->conteudo = str_replace('[basededados]', $this->dados_item["basededados"], $this->conteudo);
			$this->conteudo = str_replace('[login]', $this->dados_item["login"], $this->conteudo);
  	 	$this->conteudo = str_replace('[capacidade]', $capacidade, $this->conteudo);
			$this->conteudo = str_replace('[comentarios]', nl2br($this->dados_item["comentarios"]), $this->conteudo);
			$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
  	  $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
    	$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, 'id='.$this->dados_item["id"]);
    } else {
			$this->fechar();
    }
	}

	##########################
	######### Editar #########
	##########################
  
	function editar() {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item)) {
      if (empty($_POST)) {
        $unidade_capacidade = floor(log($this->dados_item["capacidade"], 1024));
        $unidades = $this->buscaUnidadesCapacidade();
        $capacidade = round($this->dados_item["capacidade"] / pow(1024, $unidade_capacidade), 2)." ".$unidades[$unidade_capacidade];
        $this->conteudo .= $this->carregaArquivo("clientes_basesdedados.editar.inc.php");
	    	$this->conteudo = str_replace('[contrato]', $this->dados_item["contrato"]." - ".$this->dados_item["servico"], $this->conteudo);
       	$this->conteudo = str_replace('[cliente_nome]', $this->dados_item["cliente_nome"], $this->conteudo);
       	$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
      	$this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(), $this->dados_item["tipo_id"], "n"), $this->conteudo);
      	$this->conteudo = str_replace('[basededados]', $this->dados_item["basededados"], $this->conteudo);
  		  $this->conteudo = str_replace('[login]', $this->dados_item["login"], $this->conteudo);
  		  $this->conteudo = str_replace('[clb_id]', $_GET["i"], $this->conteudo);
  		 	$this->conteudo = str_replace('[capacidade]', $capacidade, $this->conteudo);
  		 	$this->conteudo = str_replace('[comentarios]', $this->dados_item["comentarios"], $this->conteudo);
  		 	$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
  		 	if ($this->dados_item["assinatura_status"] == 2 && $this->dados_item["status_id"] != 1 && $this->permissoes[$this->dados_modulo["nome"]]["ativar"]) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $this->dados_item["id"] . '\';">';
  		 	if ($this->dados_item["assinatura_status"] == 2 && $this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["desativar"]) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=desativar&i=' . $this->dados_item["id"] . '\';">';
  	   	$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
  	   	$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
  	    $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
 			 	if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
    	  $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
  	   	$this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].$this->janela, $this->conteudo);
      } else { 
			  $_POST["senha"] = trim($_POST["senha"]);
			  $_POST["login"] = trim($_POST["login"]);
			  $query = "
			   	UPDATE
			   		tab_clientes_basesdedados 
			   	SET
						clb_basededados = '{$_POST["basededados"]}', 
						clb_login = '{$_POST["login"]}', 
						clb_comentarios = '{$_POST["comentarios"]}', 
						cbt_id = '{$_POST["tipo"]}', 
			   	WHERE
			   		clb_id = '{$_GET["i"]}'";
			 	$this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, 'id='.$this->dados_item["id"]);
		  	header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver&i=".$_GET["i"]);
      } 
    } else {
			$this->fechar();
    }
	}

	#############################
	######### Adicionar #########
	#############################
  
	function adicionar() {
    $cliente_assinaturas = $this->buscaAssinaturas($_GET["i"]);
    if (!empty($cliente_assinaturas)) {
      if (empty($_POST)) {
        $this->conteudo .= $this->carregaArquivo("clientes_basesdedados.adicionar.inc.php");
      	$this->conteudo = str_replace('[cliente_nome]', $this->buscaCliente($_GET["i"]), $this->conteudo);
       	$this->conteudo = str_replace('[cliente_id]', $_GET["i"], $this->conteudo);
      	$this->conteudo = str_replace('[contrato]', $this->geraSelect($cliente_assinaturas, "", "n"), $this->conteudo);
      	$this->conteudo = str_replace('[servidor]', $this->geraSelect($this->buscaServidores(), "", "n"), $this->conteudo);
      	$this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(), "", "n"), $this->conteudo);
  		 	if ($this->dados_item["assinatura_status"] == 2 && $this->dados_item["status_id"] == 2 && $this->permissoes[$this->dados_modulo["nome"]]["ativar"]) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$_GET["i"].'\';">';
  		 	if ($this->dados_item["assinatura_status"] == 2 && $this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["desativar"]) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$_GET["i"].'\';">';
  	   	$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
  	   	$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
  	    $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
    	  $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
  	  	$this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=adicionar&i='.$_GET["i"], $this->conteudo);
      } else { 
			 	$parametros = $this->buscaParametros($_POST["assinatura"]);
 				$valor_capacidade = $parametros["basesdedados_capacidade"];
			  $_POST["senha"] = trim($_POST["senha"]);
			  $_POST["login"] = trim($_POST["login"]);
			  $query = "
			  	INSERT INTO
			  		tab_clientes_basesdedados (ass_id, cbt_id, cbs_id, ser_id, clb_login, clb_basededados, clb_capacidade, clb_comentarios) 
			  	VALUES ('{$_POST["assinatura"]}', '{$_POST["tipo"]}', '3', NULL, '{$_POST["login"]}', '{$_POST["basededados"]}', '$valor_capacidade', '{$_POST["comentarios"]}')";
			 	$this->bancodedados->query($query);
        $id = $this->bancodedados->ultimoIdInserido();
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, 'id='.$id);
		  	header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver&i=".$id);
      } 
    } else {
			$this->fechar();
    }
	}
  
	##########################
	######### Ativar #########
	##########################
  
	function ativar() {
    if (empty($_POST)) {
      $query = "UPDATE tab_clientes_basesdedados SET cbs_id = 1 WHERE clb_id = '{$_GET["i"]}'";
		 	$this->bancodedados->query($query);
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, 'id='.$this->dados_item["id"]);
      header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver&i=".$_GET["i"]);
    } else {
      $this->fechar();
    }
	} 
	
	##########################
	######## Desativar #######
	##########################
  
	function desativar() {
    if (empty($_POST)) {
      $query = "UPDATE tab_clientes_basesdedados SET cbs_id = 2 WHERE clb_id = '{$_GET["i"]}'";
      $this->bancodedados->query($query);
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, 'id='.$this->dados_item["id"]);
      header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver&i=".$_GET["i"]);
    } else {
      $this->fechar();
    }
  } 

	###########################
	######### Remover #########
	###########################
	
	function remover() {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item)) {
			if (empty($_POST["confirmar"])) {
	    	$this->conteudo .= $this->carregaArquivo("clientes_basesdedados.remover.inc.php");
	    	$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
	    	$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
	    	$this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'&cliente_id='.$this->dados_item["cliente_id"], $this->conteudo);
			} else {
				$query = "DELETE FROM tab_clientes_basesdedados WHERE clb_id = '{$_GET["i"]}'";
				$this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, 'id='.$this->dados_item["id"]);
				$this->fechar();
			}
    } else {
			$this->fechar();
    }
	}

}

?>
