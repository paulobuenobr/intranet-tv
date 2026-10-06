<?

class condominios_blocos {

  var $bancodedados;
  var $permissoes;
  var $acao;
  var $conteudo;
  var $dados_item;
  var $dados_condominio;
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
    $titulo = '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
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

  function buscaDadosCondominio($id) {
    $query = "SELECT con_nome AS nome, cep_id AS cep, con_numero AS numero FROM tab_condominios WHERE con_id = '$id'";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $this->dados_condominio["id"] = $id;
      $this->dados_condominio["nome"] = $dados["nome"];
      if ($dados["cep"]) {
        $this->dados_condominio["cep"] = substr($dados["cep"],0,5) . "-" . substr($dados["cep"],5,3);
      }
      $this->dados_condominio["numero"] = $dados["numero"];
    } else {
      unset($this->dados_condominio);
    }
  }

  function buscaDadosItem($id) {
    $query = "
      SELECT
        cob_nome AS nome,
        cep_id AS cep,
        cob_numero AS numero,
        cob_quantidade as quantidade,
        con_id AS condominio,
        tab_condominios_blocos_status.cbs_id AS status_id,
        cbs_nome AS status_nome
      FROM
        tab_condominios_blocos
        INNER JOIN tab_condominios_blocos_status ON (tab_condominios_blocos.cbs_id = tab_condominios_blocos_status.cbs_id)
      WHERE
        cob_id = '$id'";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $this->dados_item["id"] = $id;
      $this->dados_item["nome"] = $dados["nome"];
      $this->dados_item["status_id"] = $dados["status_id"];
      $this->dados_item["status_nome"] = $dados["status_nome"];
      $this->buscaDadosCondominio($dados["condominio"]);
      $this->dados_item["condominio"] = $this->dados_condominio["nome"];
      if (!$dados["cep"]) {
        $this->dados_item["cep"] = $this->dados_condominio["cep"];
        $this->dados_item["numero"] = $this->dados_condominio["numero"];
        $dados["cep"] = preg_replace("/[^0-9]/", "", $this->dados_condominio["cep"]);
      } else {
        $this->dados_item["cep"] = substr($dados["cep"],0,5) . "-" . substr($dados["cep"],5,3);
        $this->dados_item["numero"] = $dados["numero"] ? $dados["numero"] : "";
      }
      $this->dados_item["quantidade"] = $dados["quantidade"];
      $query = "
        SELECT
          tab_estados.est_nome AS estado,
          tab_cidades.cid_nome AS cidade,
          tab_bairros.bai_nome AS bairro,
          tab_logradouros.log_nome AS logradouro
        FROM
          tab_ceps,
          tab_logradouros,
          tab_bairros,
          tab_cidades,
          tab_estados
        WHERE
          (tab_ceps.log_id = tab_logradouros.log_id) AND
          (tab_logradouros.bai_id = tab_bairros.bai_id) AND
          (tab_bairros.cid_id = tab_cidades.cid_id) AND
          (tab_cidades.est_id = tab_estados.est_id) AND
          (tab_ceps.cep_id = '{$dados["cep"]}')";
      $query = $this->bancodedados->query($query);
      $endereco = $this->bancodedados->resultadoArray($query);
      if ($endereco) {
        $this->dados_item["estado"] = $endereco["estado"];
        $this->dados_item["cidade"] = $endereco["cidade"];
        $this->dados_item["bairro"] = $endereco["bairro"];
        $this->dados_item["logradouro"] = $endereco["logradouro"];
      }
    } else {
      unset($this->dados_item);
    }
  }

  function corrigeNome($nome) {
    $minusculas = array("da","de","do","das","dos","e");
    $maiusculas = array("LTDA","SC","S/C","SA","S/A","ME");
    $nome = str_replace(".",". ",$nome); // Adiciona espaço depois do ponto de abreviação
    $nome = preg_replace("/[\r\n\t ]+/"," ",$nome); // Remove espaços seguidos
    if (strtoupper($nome) == $nome || strtolower($nome) == $nome) {
      $nome = strtolower($nome); // Coloca todos os caracteres em minúsculo
      $nome = ucwords($nome); // Coloca as primeiras letras de cada nome em maiúsculo
    }
    for ($i=0;$i<sizeof($minusculas);$i++) $nome = str_ireplace(" $minusculas[$i] "," $minusculas[$i] "," $nome "); // Substitui termos minúsculos
    for ($i=0;$i<sizeof($maiusculas);$i++) $nome = str_ireplace(" $maiusculas[$i] "," $maiusculas[$i] "," $nome "); // Substitui termos maiúsculos
    if (strtoupper($nome) == $nome || strtolower($nome) == $nome) {
      $nome = strtolower($nome); // Coloca todos os caracteres em minúsculo
      $nome = ucwords($nome); // Coloca as primeiras letras de cada nome em maiúsculo
    }
    $nome = trim($nome); // Remove excessos do início e do fim
    return $nome;
  }

  ##############
  ### Fechar ###
  ##############

  function fechar() {
    unset($this->conteudo);
    if (!empty($this->dados_condominio["id"])) {
      $this->conteudo .= '<script type="text/javascript">window.opener.buscaBlocos(' . $this->dados_condominio["id"] . ');</script>';
    }
    $this->conteudo .= '<script type="text/javascript">window.close();</script>';
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
    $this->buscaDadosCondominio($_GET["c"]);
    if ($this->dados_condominio) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios_blocos.adicionar.inc.php");
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", implode(" ",$botoes),$this->conteudo);
        $this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=adicionar&c=' . $this->dados_condominio["id"],$this->conteudo);
        $this->conteudo = str_replace('[condominio]',$this->dados_condominio["nome"],$this->conteudo);
        $this->conteudo = str_replace('[cep]',$this->dados_condominio["cep"],$this->conteudo);
        $this->conteudo = str_replace('[numero]',$this->dados_condominio["numero"],$this->conteudo);
      } else {
        $_POST["nome"] = $this->corrigeNome($_POST["nome"]);
        $_POST["numero"] = $_POST["numero"] ? $_POST["numero"] : "0";
        if (($_POST["cep"] != $this->dados_condominio["cep"]) || ($_POST["numero"] != $this->dados_condominio["numero"])) {
          $_POST["cep"] = preg_replace("/[^0-9]/","",$_POST["cep"]);
          $_POST["cep"] = "'{$_POST["cep"]}'";
          $_POST["numero"] = "'{$_POST["numero"]}'";
        } else {
          $_POST["cep"] = "NULL";
          $_POST["numero"] = "NULL";
        }
        $query = "INSERT INTO tab_condominios_blocos (cob_data_cadastro, cob_nome, cep_id, cob_numero, cob_quantidade, con_id) VALUES (NOW(), '{$_POST["nome"]}', {$_POST["cep"]}, {$_POST["numero"]}, '{$_POST["quantidade"]}', '{$_GET["c"]}')";
        $this->bancodedados->query($query);
        $id = $this->bancodedados->ultimoIdInserido();
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao,'id=' . $id);
        header("Location: ./?m=" . $this->dados_modulo["nome"] . "&a=ver&i=" . $id);
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
    if (!empty($this->dados_item)) {
      $this->conteudo .= $this->carregaArquivo("condominios_blocos.ver.inc.php");
      $this->conteudo = str_replace('[condominio]',$this->dados_condominio["nome"],$this->conteudo);
      $this->conteudo = str_replace('[condominio_id]',$this->dados_condominio["id"],$this->conteudo);
      $this->conteudo = str_replace('[nome]',$this->dados_item["nome"],$this->conteudo);
      $this->conteudo = str_replace('[cep]',$this->dados_item["cep"],$this->conteudo);
      $this->conteudo = str_replace('[estado]',$this->dados_item["estado"],$this->conteudo);
      $this->conteudo = str_replace('[cidade]',$this->dados_item["cidade"],$this->conteudo);
      $this->conteudo = str_replace('[bairro]',$this->dados_item["bairro"],$this->conteudo);
      $this->conteudo = str_replace('[logradouro]',$this->dados_item["logradouro"],$this->conteudo);
      $this->conteudo = str_replace('[numero]',$this->dados_item["numero"],$this->conteudo);
      $this->conteudo = str_replace('[quantidade]',$this->dados_item["quantidade"],$this->conteudo);
      $this->conteudo = str_replace('[status_nome]',$this->dados_item["status_nome"],$this->conteudo);
      if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $botoes[] = '<input type="button" name="adicionar" class="buttonAzulEscuroBranco11" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar&c=' . $this->dados_condominio["id"] . '\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fecharrrrr" onClick="window.close()">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] != 1) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["desativar"] && $this->dados_item["status_id"] != 2) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$this->dados_item["id"].'\';">';
      $this->conteudo = str_replace("[botoes]",@implode(" ",$botoes),$this->conteudo);
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao,'id='.$this->dados_item["id"]);
    } else {
      $this->fechar();
    }
  }

  ##############
  ### Editar ###
  ##############

  function editar() {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item)) {
      if (empty($_POST)) {
        $this->conteudo .= $this->carregaArquivo("condominios_blocos.editar.inc.php");
        if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $botoes[] = '<input type="button" name="adicionar" class="buttonAzulEscuroBranco11" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar&c=' . $this->dados_condominio["id"] . '\';">';
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] != 1) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["desativar"] && $this->dados_item["status_id"] != 2) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$this->dados_item["id"].'\';">';
        $this->conteudo = str_replace("[botoes]",@implode(" ",$botoes),$this->conteudo);
        $this->conteudo = str_replace('[action]','?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"],$this->conteudo);
        $this->conteudo = str_replace('[condominio]',$this->dados_condominio["nome"],$this->conteudo);
        $this->conteudo = str_replace('[condominio_id]',$this->dados_condominio["id"],$this->conteudo);
        $this->conteudo = str_replace('[nome]',$this->dados_item["nome"],$this->conteudo);
        $this->conteudo = str_replace('[cep]',$this->dados_item["cep"],$this->conteudo);
        $this->conteudo = str_replace('[numero]',$this->dados_item["numero"],$this->conteudo);
        $this->conteudo = str_replace('[quantidade]',$this->dados_item["quantidade"],$this->conteudo);
        $this->conteudo = str_replace('[status_nome]',$this->dados_item["status_nome"],$this->conteudo);
      } else {
        $_POST["nome"] = $this->corrigeNome($_POST["nome"]);
        $_POST["numero"] = $_POST["numero"] ? $_POST["numero"] : "0";
        if (($_POST["cep"] != $this->dados_condominio["cep"]) || ($_POST["numero"] != $this->dados_condominio["numero"])) {
          $_POST["cep"] = preg_replace("/[^0-9]/", "", $_POST["cep"]);
          $_POST["cep"] = "'{$_POST["cep"]}'";
          $_POST["numero"] = "'{$_POST["numero"]}'";
        } else {
          $_POST["cep"] = "NULL";
          $_POST["numero"] = "NULL";
        }
        $query = "UPDATE tab_condominios_blocos SET cob_nome = '{$_POST["nome"]}', cep_id = {$_POST["cep"]}, cob_numero = {$_POST["numero"]}, cob_quantidade = '{$_POST["quantidade"]}' WHERE cob_id = '{$_GET["i"]}'";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao,'id='.$this->dados_item["id"]);
        header("Location: ./?m=" . $this->dados_modulo["nome"] . "&a=ver&i=" . $_GET["i"]);
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
     if (!empty($this->dados_item)) {
      if (empty($_POST["confirmar"])) {
        $this->conteudo .= $this->carregaArquivo("condominios_blocos.ativar.inc.php");
        $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        $this->conteudo = str_replace('[botoes]', @implode(" ",$botoes),$this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=ativar&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        $query = "UPDATE tab_condominios_blocos SET cbs_id = 1 WHERE cob_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        header("Location: ./?m=" . $this->dados_modulo["nome"] . "&a=ver&i=" . $_GET["i"]);
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
     if (!empty($this->dados_item)) {
      if (empty($_POST["confirmar"])) {
        $this->conteudo .= $this->carregaArquivo("condominios_blocos.desativar.inc.php");
        $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        $this->conteudo = str_replace('[botoes]', @implode(" ",$botoes),$this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=desativar&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        $query = "UPDATE tab_condominios_blocos SET cbs_id = 2 WHERE cob_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        header("Location: ./?m=" . $this->dados_modulo["nome"] . "&a=ver&i=" . $_GET["i"]);
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
    if (!empty($this->dados_item)) {
      if (empty($_POST["confirmar"])) {
        $this->conteudo .= $this->carregaArquivo("condominios_blocos.remover.inc.php");
        $this->conteudo = str_replace('[nome]',$this->dados_item["nome"],$this->conteudo);
        $this->conteudo = str_replace('[condominio]',$this->dados_item["condominio"],$this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]",@implode(" ",$botoes),$this->conteudo);
        $this->conteudo = str_replace('[action]','?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"],$this->conteudo);
      } else {
        $query = "DELETE FROM tab_condominios_blocos WHERE cob_id = '{$_GET["i"]}'";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao,'id='.$this->dados_item["id"]);
        $this->fechar();
      }
    } else {
      $this->fechar();
    }
  }

  ###########
  ### RDS ###
  ###########

  function buscaCondominioBlocos($condominio_id) {
  	$blocos = array();
  	if ($condominio_id) {
		$query = "
		  SELECT
			cob_id AS id,
			cob_nome AS nome,
			cob_quantidade AS quantidade,
			cbs_nome AS status_nome
		  FROM
			tab_condominios_blocos
			INNER JOIN tab_condominios_blocos_status ON (tab_condominios_blocos.cbs_id = tab_condominios_blocos_status.cbs_id)
		  WHERE
			con_id = $condominio_id
		  ORDER BY
			nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		  $dados["mailings"] = $this->buscaMailingsBloco($dados["id"]);
		  $dados["prospects"] = $this->buscaProspectsBloco($dados["id"]);
		  $dados["ativos"] = $this->buscaAtivosBloco($dados["id"]);
		  $dados["cancelados"] = $this->buscaCanceladosBloco($dados["id"]);
		  $dados["total"] = $dados["mailings"] + $dados["prospects"] + $dados["ativos"] + $dados["cancelados"];
		  if ($this->permissoes["condominios_blocos"]["ver"]) {
			$dados["link"] = "javascript:verBloco({$dados["id"]})";
		  }
		  $blocos[] = $dados;
		}
	}
    return $blocos;
  }

  function geraListagemCondominioBlocos($dados) {
    $cabecalho = array ("nome" => "Nome", "quantidade" => "Apto(s)/Sala(s)", "mailings" => "Mailings", "prospects" => "Prospects", "ativos" => "Ativos", "cancelados" => "Cancelados", "total" => "Total", "status_nome" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array(0, 80, 80, 80, 80, 80, 80, 80));
    return $listagem->conteudo;
  }

  function buscaProspectsBloco($bloco_id) {
    $query = "
      SELECT
        COUNT(DISTINCT cli_id) AS contagem
      FROM
        tab_clientes
      WHERE
        cob_id = $bloco_id AND
        cls_id = 1";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "contagem"));
  }

  function buscaAtivosBloco($bloco_id) {
    $query = "
      SELECT
        COUNT(DISTINCT cli_id) AS contagem
      FROM
        tab_clientes
      WHERE
        cob_id = $bloco_id AND
        cls_id = 2";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "contagem"));
  }

  function buscaCanceladosBloco($bloco_id) {
    $query = "
      SELECT
        COUNT(DISTINCT tab_clientes.cli_id) AS contagem
      FROM
        tab_clientes
      WHERE
        cob_id = $bloco_id AND
        cls_id = 3";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "contagem"));
  }

  function buscaMailingsBloco($bloco_id) {
    $query = "
      SELECT
        COUNT(DISTINCT tab_clientes.cli_id) AS contagem
      FROM
        tab_clientes
      WHERE
        cob_id = $bloco_id AND
        cls_id = 4";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "contagem"));
  }

}

?>
