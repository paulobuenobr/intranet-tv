<?

require_once("_global/modulo.class.inc.php");

class condominios_contatos extends modulo {

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

  function buscaDadosCondominio($id) {
    $query = "
      SELECT
        con_id AS id,
        con_nome AS nome
      FROM
        tab_condominios
      WHERE
        con_id = $id";
    $query = $this->bancodedados->query($query);
    $this->dados_condominio = $this->bancodedados->resultadoArray($query);
  }

  function buscaDadosBloco($id) {
    $query = "
      SELECT
        cob_nome AS nome,
        con_id AS condominio
      FROM
        tab_condominios_blocos
      WHERE
        cob_id = $id";
    $query = $this->bancodedados->query($query);
    $this->dados_bloco = $this->bancodedados->resultadoArray($query);
  }

  function buscaDadosItem($id) {
    $query = "
      SELECT
        tab_condominios_contatos.coc_id AS id,
        tab_condominios_contatos.coc_nome AS nome,
        tab_condominios_contatos.coc_complemento AS complemento,
        tab_condominios_contatos.coc_tel_residencial AS tel_residencial,
        tab_condominios_contatos.coc_tel_comercial AS tel_comercial,
        tab_condominios_contatos.coc_tel_celular AS tel_celular,
        tab_condominios_contatos.coc_email AS email,
        DATE_FORMAT(tab_condominios_contatos.coc_nascimento, '%d/%m/%Y') AS nascimento,
        DATE_FORMAT(tab_condominios_contatos.coc_validade, '%d/%m/%Y') AS validade,
        tab_condominios_contatos.coc_comentarios AS comentarios,
        tab_condominios.con_id AS condominio_id,
        tab_condominios.con_nome AS condominio_nome,
        tab_condominios_blocos.cob_id AS bloco_id,
        tab_condominios_blocos.cob_nome AS bloco_nome,
        tab_condominios_contatos_tipos.cct_id AS tipo_id,
        tab_condominios_contatos_tipos.cct_nome AS tipo_nome
      FROM
        tab_condominios_contatos
        INNER JOIN tab_condominios_contatos_tipos ON (tab_condominios_contatos.cct_id = tab_condominios_contatos_tipos.cct_id)
        INNER JOIN tab_condominios ON (tab_condominios_contatos.con_id = tab_condominios.con_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_contatos.cob_id = tab_condominios_blocos.cob_id)
      WHERE
        coc_id = $id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
  }

  function buscaBlocos($condominio_id) {
  	$blocos = array();
  	if ($condominio_id) {
	    $query = "
	    	SELECT
	    		cob_id AS id,
	    		cob_nome AS nome
	    	FROM
	    		tab_condominios_blocos
	    	WHERE
	    		con_id = $condominio_id
	   		ORDER BY
	   			nome";
	    $query = $this->bancodedados->query($query);
	    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	      $blocos[$dados["id"]] = $dados["nome"];
	    }
  	}
    return $blocos;
  }

  function buscaTipos() {
    $query = "
      SELECT
        cct_id AS id,
        cct_nome AS nome
      FROM
        tab_condominios_contatos_tipos
      ORDER BY
        cct_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function corrigeNome($nome) {
    $minusculas = array("da", "de", "do", "das", "dos", "e");
    $maiusculas = array("LTDA", "SC", "S/C", "SA", "S/A", "ME");
    $nome = str_replace(".", ". ", $nome); // Adiciona espaço depois do ponto de abreviação
    $nome = preg_replace("/[\r\n\t ]+/", " ", $nome); // Remove espaços seguidos
    if (strtoupper($nome) == $nome || strtolower($nome) == $nome) {
      $nome = strtolower($nome); // Coloca todos os caracteres em minúsculo
      $nome = ucwords($nome); // Coloca as primeiras letras de cada nome em maiúsculo
    }
    for ($i = 0; $i < sizeof($minusculas); $i++) $nome = str_ireplace(" $minusculas[$i] ", " $minusculas[$i] ", " $nome "); // Substitui termos minúsculos
    for ($i = 0; $i < sizeof($maiusculas); $i++) $nome = str_ireplace(" $maiusculas[$i] ", " $maiusculas[$i] ", " $nome "); // Substitui termos maiúsculos
    $nome = trim($nome); // Remove excessos do início e do fim
    return $nome;
  }

  ##############
  ### Fechar ###
  ##############

  function fechar() {
    unset($this->conteudo);
    if ($this->dados_item["id"]) {
      $this->conteudo  = '<script type="text/javascript">if (window.opener && window.opener.buscaContatos) window.opener.buscaContatos('.$this->dados_item["condominio_id"].')</script>';
      $this->conteudo .= '<script type="text/javascript">if (window.opener && window.opener.buscaCondominioContatos) window.opener.buscaCondominioContatos('.$this->dados_item["condominio_id"].')</script>';
    }
    $this->conteudo .= '<script type="text/javascript">window.close()</script>';
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
    $this->buscaDadosCondominio($_GET["c"]);
    if ($this->dados_condominio) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios_contatos.adicionar.inc.php");
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar&c={$this->dados_condominio["id"]}", $this->conteudo);
        $this->conteudo = str_replace('[condominio]', $this->dados_condominio["nome"], $this->conteudo);
        $this->conteudo = str_replace('[bloco]', $this->geraSelect($this->buscaBlocos($this->dados_condominio["id"]), null, true, "bloco", "50%"), $this->conteudo);
        $this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(), null, true, "tipo", "50%"), $this->conteudo);
      } else {
        $_POST["nome"] = $this->corrigeNome($_POST["nome"]);
        $_POST["bloco"] = $_POST["bloco"] ? $_POST["bloco"] : "NULL";
        if (!empty($_POST["nascimento"])) {
          $_POST["nascimento"] = explode("/", $_POST["nascimento"]);
          $_POST["nascimento"] = "'{$_POST["nascimento"][2]}-{$_POST["nascimento"][1]}-{$_POST["nascimento"][0]}'";
        } else {
          $_POST["nascimento"] = "NULL";
        }
        if (!empty($_POST["validade"])) {
          $_POST["validade"] = explode("/", $_POST["validade"]);
          $_POST["validade"] = "'{$_POST["validade"][2]}-{$_POST["validade"][1]}-{$_POST["validade"][0]}'";
        } else {
          $_POST["validade"] = "NULL";
        }
        $_POST["tel_residencial"] = $_POST["tel_residencial"] ? "'{$_POST["tel_residencial"]}'" : "NULL";
        $_POST["tel_comercial"] = $_POST["tel_comercial"] ? "'{$_POST["tel_comercial"]}'" : "NULL";
        $_POST["tel_celular"] = $_POST["tel_celular"] ? "'{$_POST["tel_celular"]}'" : "NULL";
        $_POST["comentarios"] = trim($_POST["comentarios"]);
        $query = "INSERT INTO tab_condominios_contatos (coc_data_cadastro, coc_nome, con_id, cob_id, coc_complemento, coc_tel_residencial, coc_tel_comercial, coc_tel_celular, coc_email, coc_nascimento, coc_validade, coc_comentarios, cct_id) VALUES (NOW(), '{$_POST["nome"]}', {$_GET["c"]}, {$_POST["bloco"]}, '{$_POST["complemento"]}', {$_POST["tel_residencial"]}, {$_POST["tel_comercial"]}, {$_POST["tel_celular"]}, '{$_POST["email"]}', {$_POST["nascimento"]}, {$_POST["validade"]}, '{$_POST["comentarios"]}' , {$_POST["tipo"]})";
        $this->bancodedados->query($query);
        $id = $this->bancodedados->ultimoIdInserido();
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
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
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("condominios_contatos.ver.inc.php");
      $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[condominio]', $this->dados_item["condominio_nome"], $this->conteudo);
      $this->conteudo = str_replace('[condominio_id]', $this->dados_item["condominio_id"], $this->conteudo);
      $this->conteudo = str_replace('[bloco]', $this->dados_item["bloco_nome"], $this->conteudo);
      $this->conteudo = str_replace('[complemento]', $this->dados_item["complemento"], $this->conteudo);
      $this->conteudo = str_replace('[tel_residencial]', $this->dados_item["tel_residencial"], $this->conteudo);
      $this->conteudo = str_replace('[tel_comercial]', $this->dados_item["tel_comercial"], $this->conteudo);
      $this->conteudo = str_replace('[tel_celular]', $this->dados_item["tel_celular"], $this->conteudo);
      $this->conteudo = str_replace('[email]', $this->dados_item["email"], $this->conteudo);
      $this->conteudo = str_replace('[nascimento]', $this->dados_item["nascimento"], $this->conteudo);
      $this->conteudo = str_replace('[validade]', $this->dados_item["validade"], $this->conteudo);
      $this->conteudo = str_replace('[comentarios]', nl2br($this->dados_item["comentarios"]), $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
      if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $botoes[] = '<input type="button" name="adicionar" class="buttonAzulEscuroBranco11" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar&c=' . $this->dados_item["condominio_id"] . '\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
      $this->fechar();
    }
  }

  ##############
  ### Editar ###
  ##############

  function editar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios_contatos.editar.inc.php");
        $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
        $this->conteudo = str_replace('[condominio]', $this->dados_item["condominio_nome"], $this->conteudo);
        $this->conteudo = str_replace('[condominio_id]', $this->dados_item["condominio_id"], $this->conteudo);
        $this->conteudo = str_replace('[bloco]', $this->geraSelect($this->buscaBlocos($this->dados_item["condominio_id"]), $this->dados_item["bloco_id"], true, "bloco", "50%"), $this->conteudo);
        $this->conteudo = str_replace('[complemento]', $this->dados_item["complemento"], $this->conteudo);
        $this->conteudo = str_replace('[tel_residencial]', $this->dados_item["tel_residencial"], $this->conteudo);
        $this->conteudo = str_replace('[tel_comercial]', $this->dados_item["tel_comercial"], $this->conteudo);
        $this->conteudo = str_replace('[tel_celular]', $this->dados_item["tel_celular"], $this->conteudo);
        $this->conteudo = str_replace('[email]', $this->dados_item["email"], $this->conteudo);
        $this->conteudo = str_replace('[nascimento]', $this->dados_item["nascimento"], $this->conteudo);
        $this->conteudo = str_replace('[validade]', $this->dados_item["validade"], $this->conteudo);
        $this->conteudo = str_replace('[comentarios]', $this->dados_item["comentarios"], $this->conteudo);
        $this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(), $this->dados_item["tipo_id"], true, "tipo", "50%"), $this->conteudo);
        if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $botoes[] = '<input type="button" name="adicionar" class="buttonAzulEscuroBranco11" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar&c=' . $this->dados_item["condominio_id"] . '\';">';
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        $_POST["nome"] = $this->corrigeNome($_POST["nome"]);
        $_POST["bloco"] = $_POST["bloco"] ? $_POST["bloco"] : "NULL";
        if ($_POST["nascimento"]) {
          $_POST["nascimento"] = explode("/", $_POST["nascimento"]);
          $_POST["nascimento"] = "'{$_POST["nascimento"][2]}-{$_POST["nascimento"][1]}-{$_POST["nascimento"][0]}'";
        } else {
          $_POST["nascimento"] = "NULL";
        }
        if ($_POST["validade"]) {
          $_POST["validade"] = explode("/", $_POST["validade"]);
          $_POST["validade"] = "'{$_POST["validade"][2]}-{$_POST["validade"][1]}-{$_POST["validade"][0]}'";
        } else {
          $_POST["validade"] = "NULL";
        }
        $_POST["comentarios"] = trim($_POST["comentarios"]);
        $_POST["tel_residencial"] = $_POST["tel_residencial"] ? "'{$_POST["tel_residencial"]}'" : "NULL";
        $_POST["tel_comercial"] = $_POST["tel_comercial"] ? "'{$_POST["tel_comercial"]}'" : "NULL";
        $_POST["tel_celular"] = $_POST["tel_celular"] ? "'{$_POST["tel_celular"]}'" : "NULL";
        $query = "UPDATE tab_condominios_contatos SET coc_nome = '{$_POST["nome"]}', cob_id = {$_POST["bloco"]}, coc_complemento = '{$_POST["complemento"]}', coc_tel_residencial = {$_POST["tel_residencial"]}, coc_tel_comercial = {$_POST["tel_comercial"]}, coc_tel_celular = {$_POST["tel_celular"]}, coc_email = '{$_POST["email"]}', coc_nascimento = {$_POST["nascimento"]}, coc_validade = {$_POST["validade"]}, coc_comentarios = '{$_POST["comentarios"]}', cct_id = {$_POST["tipo"]} WHERE coc_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
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
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios_contatos.remover.inc.php");
        $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
        $this->conteudo = str_replace('[condominio]', $this->dados_item["condominio"], $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        $query = "DELETE FROM tab_condominios_contatos WHERE coc_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"],  $this->acao, $this->dados_item["id"]);
        $this->fechar();
      }
    } else {
      $this->fechar();
    }
  }

  ###########
  ### RDS ###
  ###########

  function buscaCondominioContatos($condominio_id) {
  	$contatos = array();
  	if ($condominio_id) {
	    $query = "
	      SELECT 
	        tab_condominios_contatos.coc_id AS id, 
	        tab_condominios_contatos.coc_nome AS nome, 
	        tab_condominios_blocos.cob_nome AS bloco, 
	        tab_condominios_contatos.coc_complemento AS complemento, 
	        tab_condominios_contatos.coc_tel_residencial AS telefone_residencial, 
	        tab_condominios_contatos.coc_tel_comercial AS telefone_comercial, 
	        tab_condominios_contatos.coc_tel_celular AS telefone_celular, 
	        tab_condominios_contatos_tipos.cct_nome AS tipo 
	      FROM 
	        tab_condominios_contatos
	        INNER JOIN tab_condominios_contatos_tipos ON (tab_condominios_contatos.cct_id = tab_condominios_contatos_tipos.cct_id)
	        INNER JOIN tab_condominios ON (tab_condominios_contatos.con_id = tab_condominios.con_id)
	        LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_contatos.cob_id = tab_condominios_blocos.cob_id)
	      WHERE 
	        tab_condominios.con_id = $condominio_id
	      ORDER BY 
	        nome";
	    $query = $this->bancodedados->query($query);
	    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	      if ($this->permissoes["condominios_contatos"]["ver"]) $dados["link"] = "javascript:verContato({$dados["id"]});";
	      if ($dados["bloco"] && $dados["bloco"] != "Único") $dados["complemento"] .= ($dados["complemento"] ? " / " : "").$dados["bloco"];
	      if ($dados["telefone_residencial"]) $dados["telefones"][] = $dados["telefone_residencial"];
	      if ($dados["telefone_comercial"]) $dados["telefones"][] = $dados["telefone_comercial"];
	      if ($dados["telefone_celular"]) $dados["telefones"][] = $dados["telefone_celular"];
	      $dados["telefones"] = implode(" / ", $dados["telefones"]);
	      $contatos[] = $dados;
	    }
  	}
    return $contatos;
  }

  function geraListagemCondominioContatos($dados) {
    $cabecalho = array ("nome" => "Nome", "complemento" => "Complemento", "telefones" => "Telefone(s)", "tipo" => "Tipo");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
    return $listagem->conteudo;
  }

}

?>
