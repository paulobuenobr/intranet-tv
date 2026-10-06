<?

require_once("_global/modulo.class.inc.php");

class grupos extends modulo {

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

  function buscaDadosItem($id) {
  	$query = "SELECT gru_nome AS nome, grs_id AS status FROM tab_grupos WHERE gru_id = '$id'";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
			$this->dados_item["id"] = $id;
			$this->dados_item["nome"] = $dados["nome"];
			$this->dados_item["permissoes"] = $this->buscaPermissoes($id);
			$status = $this->buscaStatus();
			$this->dados_item["status_id"] = $dados["status"];
			$this->dados_item["status_nome"] = $status[$dados["status"]];
    } else {
      unset($this->dados_item);
    }
  }

  function buscaPermissoes($grupo_id) {
    $query = "
      SELECT
        tab_acoes.aco_id AS id
      FROM
        tab_modulos
        INNER JOIN tab_acoes ON (tab_acoes.mod_id = tab_modulos.mod_id)
        INNER JOIN rel_gru_aco_tempermissao ON (rel_gru_aco_tempermissao.aco_id = tab_acoes.aco_id)
      WHERE
        rel_gru_aco_tempermissao.gru_id = $grupo_id
      ORDER BY
        tab_modulos.mod_descricao,
        tab_acoes.aco_descricao";
    $query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$permissoes[$dados["id"]] = true;
		}
		return $permissoes;
  }

  function ehAdmin($id = null) {
      
      $user = $id? $id : $_SESSION["usuario"];
      
      $query = "SELECT usu_administrador FROM tab_usuarios WHERE usu_id = $user";
      $query = $this->bancodedados->query($query);
      
      $ehAdmin = $this->bancodedados->resultadoArray($query);
       
      return $ehAdmin['usu_administrador'] ;
  }
  
  function geraPermissoes($permissoes = '', $ver = false) {
    $acoes = $this->buscaAcoes();
    if (sizeof($acoes) > 0) {
    	$i = 0;
      do {
        $modulo = current($acoes);
        $mostra_modulo = false;
        $conteudo_modulo = '<b>'.key($acoes).':</b><br>';
        do {
          $acao = current($modulo);
          if ($ver) {
            if ($permissoes[$acao["id"]]) {
              $conteudo_modulo .= " &nbsp; &raquo; {$acao["nome"]}<br>";
              $mostra_modulo = true;
            }
          } else {
            $conteudo_modulo .= " &nbsp; <input type=\"checkbox\" name=\"permissoes[{$acao["id"]}]\" id=\"permissoes_{$acao["id"]}\" ".(($permissoes[$acao["id"]]) ? "checked" : "")." value=\"true\"><label for=\"permissoes_{$acao["id"]}\"> {$acao["nome"]}</label><br>";
            $mostra_modulo = true;
          }
        } while(next($modulo));
        if ($mostra_modulo) {
          $conteudo_permissoes .= $conteudo_modulo;
          $i++;
        }
        if ($i > intval(sizeof($acoes) / 2)) {
          $conteudo_permissoes .= '</td><td class="txtAzulEscuro11" valign="top">';
          $i = 0;
        }
      } while (next($acoes));

      $conteudo = '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td class="txtAzulEscuro11" valign="top">';
      $conteudo .= ($conteudo_permissoes) ? $conteudo_permissoes : "Sem Permissões";
      $conteudo .= '</td></tr></table>';
    }
    return $conteudo;
  }

  function buscaAcoes() {
      
    $ehAdmin = $this->ehAdmin($_SESSION['usuario']);
    $where = "AND aco_nome != 'ver_degustacao' AND aco_nome != 'ver_degustacao_tv' ";
    if($ehAdmin) {
        $where = "";
    }
    
    $query = "
      SELECT
        tab_modulos.mod_descricao AS modulo,
        tab_acoes.aco_id AS id,
        tab_acoes.aco_descricao AS nome
      FROM
        tab_acoes,
        tab_modulos
      WHERE
        (tab_acoes.mod_id = tab_modulos.mod_id) AND
        (mos_id = 1) $where
      ORDER BY 
        tab_modulos.mod_descricao,
        tab_acoes.aco_descricao";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$acoes[$dados["modulo"]][] = array("id" => $dados["id"], "nome" => $dados["nome"]);
		}
		return $acoes;
  }

  function buscaStatus() {
  	$query = "SELECT grs_id AS id, grs_nome AS nome FROM tab_grupos_status ORDER BY nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$status[$dados["id"]] = $dados["nome"];
		}
		return $status;
  }

  function corrigeNome($nome) {
    $minusculas = array("da","de","do","das","dos","e");
    $maiusculas = array("LTDA","SC","S/C","SA","S/A","ME");
    $nome = str_replace(".",". ",$nome); // Adiciona espaço depois do ponto de abreviação
    $nome = preg_replace("/[\r\n\t ]+/", " ", $nome); // Remove espaços seguidos
    if (strtoupper($nome) == $nome || strtolower($nome) == $nome) {
      $nome = strtolower($nome); // Coloca todos os caracteres em minúsculo
      $nome = ucwords($nome); // Coloca as primeiras letras de cada nome em maiúsculo
    }
    for ($i=0;$i<sizeof($minusculas);$i++) $nome = str_ireplace(" {$minusculas[$i]} "," {$minusculas[$i]} "," $nome "); // Substitui termos minúsculos
    for ($i=0;$i<sizeof($maiusculas);$i++) $nome = str_ireplace(" {$maiusculas[$i]} "," {$maiusculas[$i]} "," $nome "); // Substitui termos maiúsculos
    $nome = trim($nome); // Remove excessos do início e do fim
    return $nome;
  }

  ##########################
	######### Listar #########
	##########################

  function listar() {
  	$this->paginas_atual = $_GET["p"] ? $_GET["p"] : 1;
    $this->paginas_total = $this->contaPaginas();
    $this->buscaItens();
    $this->geraListagem();
    //$this->geraPaginacao();
  	$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td  bgcolor="#E8E8E8" align="center" class="txtBranco11">';
    if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) {
    	$this->conteudo .= '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar\'">';
    }
    $this->conteudo .= $this->paginacao->conteudo;
  	$this->conteudo .= '</td></tr></table>';
    $this->conteudo .= $this->listagem->conteudo;
  }

  function contaPaginas() {
    $query = "SELECT count(*) AS itens FROM tab_grupos";
    $query = $this->bancodedados->query($query);
    $itens = $this->bancodedados->resultado($query,0,"itens");
		$paginas = $itens/PAG_ITENS;
    if (!is_int($paginas)) {
      $paginas = intval($paginas) + 1;
    }
    return $paginas;
  }

  function buscaItens() {
    $limit_inicio = ($this->paginas_atual-1) * PAG_ITENS;
    $limit_fim = PAG_ITENS;
    $query = "
      SELECT
        gru_id AS id,
        gru_nome AS nome,
        grs_id AS status
      FROM
        tab_grupos
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
		$status = $this->buscaStatus();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
			  $item_tratado["link"] = "?m={$this->dados_modulo["nome"]}&a=ver&i={$dados["id"]}";
			}
  		$item_tratado["nome"] = $dados["nome"];
  		$item_tratado["permissoes"] = $this->buscaGrupoPermissoes($dados["id"]);
    	$item_tratado["status"] = $status[$dados["status"]];
    	$this->lista_itens[] = $item_tratado;
    }
  }

	function buscaGrupoPermissoes($grupo_id) {
  	$query = "
      SELECT DISTINCT
        tab_modulos.mod_descricao AS modulo,
        tab_acoes.aco_descricao AS acao
      FROM
        rel_gru_aco_tempermissao
        INNER JOIN tab_acoes ON (rel_gru_aco_tempermissao.aco_id = tab_acoes.aco_id)
        INNER JOIN tab_modulos ON (tab_acoes.mod_id = tab_modulos.mod_id)
      WHERE
        rel_gru_aco_tempermissao.gru_id = '$grupo_id'
      ORDER BY
        tab_modulos.mod_descricao,
        tab_acoes.aco_descricao";
		$query = $this->bancodedados->query($query);
		if ($this->bancodedados->linhas($query)) {
		  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		  	$modulos_acoes[$dados["modulo"]][] = $dados["acao"];
		  }
		  foreach ($modulos_acoes as $modulo => $acoes) {
        $permissoes[] = '<span onMouseOver="hintShow(\''.implode(", ", $acoes).'\');" onMouseOut="hintHide();">'.$modulo."</span>";
		  }
		  $permissoes = implode(", ", $permissoes);
		} else {
		  $permissoes = "Sem Permissões";
		}
		return $permissoes;
	}

  function geraListagem() {
    $cabecalho = array ("nome" => "Nome", "permissoes" => "Permissões", "status" => "Status");
		$this->listagem = controle::instanciaObjeto("listagem", $cabecalho, $this->lista_itens);
  }

  #############################
	######### Adicionar #########
	#############################

	function adicionar() {
  	if (!$_POST) {
    	$this->conteudo .= $this->carregaArquivo("grupos.adicionar.inc.php");
    	$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
    	$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
    	$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
    	$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
    	$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
			$this->conteudo = str_replace('[permissoes]', $this->geraPermissoes(), $this->conteudo);
			$this->conteudo = str_replace('[status]', $this->geraSelect($this->buscaStatus(), null, false, "status"), $this->conteudo);
	  } else {
			$_POST["nome"] = $this->corrigeNome($_POST["nome"]);
			$query = "INSERT INTO tab_grupos (gru_nome, grs_id) VALUES ('{$_POST["nome"]}', {$_POST["status"]})";
			$this->bancodedados->query($query);
      $id = $this->bancodedados->ultimoIdInserido();
      if ($_POST["permissoes"]) {
        $permissoes = array_keys($_POST["permissoes"]);
        for ($i = 0; $i < sizeof($permissoes); $i++) {
          $query = "INSERT INTO rel_gru_aco_tempermissao (gru_id, aco_id) VALUES ($id, {$permissoes[$i]})";
          $this->bancodedados->query($query);
        }
      }
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"], $this->acao, $id);
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
		}
  }

	#######################
	######### Ver #########
	#######################

	function ver() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
    	$this->conteudo .= $this->carregaArquivo("grupos.ver.inc.php");
    	$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace('[permissoes]', $this->geraPermissoes($this->dados_item["permissoes"], true), $this->conteudo);
			$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
	  	if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\';">';
	  	if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
    	$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
    	$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

	##########################
	######### Editar #########
	##########################

	function editar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
			if (!$_POST) {
	    	$this->conteudo .= $this->carregaArquivo("grupos.editar.inc.php");
	    	$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
  			$this->conteudo = str_replace('[permissoes]', $this->geraPermissoes($this->dados_item["permissoes"]), $this->conteudo);
				$this->conteudo = str_replace('[status]', $this->geraSelect($this->buscaStatus(), $this->dados_item["status_id"], false, "status"), $this->conteudo);
	    	$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
	    	$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
	    	$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
	    	$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
	    	$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
			} else {
			  $_POST["nome"] = $this->corrigeNome($_POST["nome"]);
    		$query = "UPDATE tab_grupos SET gru_nome = '{$_POST["nome"]}', grs_id = {$_POST["status"]} WHERE gru_id = {$_GET["i"]}";
    		$this->bancodedados->query($query);
        $query = "DELETE FROM rel_gru_aco_tempermissao WHERE gru_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $permissoes = array_keys($_POST["permissoes"]);
        for ($i = 0; $i < sizeof($permissoes); $i++) {
          $query = "INSERT INTO rel_gru_aco_tempermissao (gru_id, aco_id) VALUES ({$_GET["i"]}, {$permissoes[$i]})";
          $this->bancodedados->query($query);
        }
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			}
    } else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
	}

	###########################
	######### Remover #########
	###########################

	function remover () {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item)) {
			if (empty($_POST["confirmar"])) {
	    	$this->conteudo .= $this->carregaArquivo("grupos.remover.inc.php");
	    	$this->conteudo = str_replace('[nome]',$this->dados_item["nome"],$this->conteudo);
	    	$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
	    	$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
	    	$this->conteudo = str_replace("[botoes]",@implode(" ",$botoes),$this->conteudo);
	    	$this->conteudo = str_replace('[action]','?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"],$this->conteudo);
			} else {
				$query = "DELETE FROM rel_gru_aco_tempermissao WHERE gru_id = '{$_GET["i"]}'";
				$this->bancodedados->query($query);
				$query = "DELETE FROM tab_grupos WHERE gru_id = '{$_GET["i"]}'";
				$this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao,$this->dados_item["id"]);
				header("Location: ./?m=" . $this->dados_modulo["nome"]);
			}
    } else {
			header("Location: ./?m=" . $this->dados_modulo["nome"]);
    }
	}

}

?>
