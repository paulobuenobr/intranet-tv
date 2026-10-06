<?

require_once("_global/modulo.class.inc.php");

class clientes_negativacoes extends modulo {

    function __construct($bancodedados, $permissoes, $executar = true) {
  	
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
    
    if ($executar) {
    	
      $this->buscaDadosModulo($_GET["m"]);
  		$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
      $this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
      
    }
    
  }
  
  function buscaDadosItem($cliente_negativacao_id) {
  	
  	 $query = "
      SELECT
  			cln_id AS id,
  			cln_codigo AS codigo,
  			cln_cpf_cnpj AS cpf_cnpj,
  			cln_nome AS nome,
  			cli_id AS cliente_id,
  			DATE_FORMAT(cln_inclusao, '%d/%m/%Y') AS inclusao,
  			DATE_FORMAT(cln_baixa, '%d/%m/%Y') AS baixa,
  			cln_status AS status
  		FROM
        tab_clientes_negativacoes
      WHERE
        tab_clientes_negativacoes.cln_id = $cliente_negativacao_id";
  	 
    $query = $this->bancodedados->query($query);
    
    $this->dados_item = $this->bancodedados->resultadoArray($query);
  	
  }
  
  function fechar() {
  	
    if ($this->dados_item) {
      $this->conteudo = "<script type=\"text/javascript\">if (window.opener && window.opener.buscaClientesNegativacoes) window.opener.buscaClientesNegativacoes({$this->dados_item["cliente_id"]}); window.close();</script>";
    } else {
    	$this->conteudo = "<script type=\"text/javascript\">window.close();</script>";
    }
    
  }
  
  function buscaClientesNegativacoes($cliente_id) {
  	
  	$clientes_negativacoes = array();
  	
  	$query = "
  		SELECT
  			cln_id AS id,
  			cln_codigo AS codigo,
  			cln_cpf_cnpj AS cpf_cnpj,
  			cln_nome AS nome,
  			cli_id AS cliente_id,
  			DATE_FORMAT(cln_inclusao, '%d/%m/%Y') AS inclusao,
  			DATE_FORMAT(cln_baixa, '%d/%m/%Y') AS baixa,
  			cln_status AS status
  		FROM
  			tab_clientes_negativacoes
  		WHERE
  			cli_id = $cliente_id OR
  			cln_cpf_cnpj = (
  				SELECT
  					cli_cpf_cnpj
  				FROM
  					tab_clientes
  				WHERE
  					cli_id = {$cliente_id}
  			)
  		ORDER BY
  			cln_status,
  			cln_inclusao";
  	
    $query = $this->bancodedados->query($query);
    
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	
      if ($this->permissoes["clientes_negativacoes"]["ver"]) {
        $dados["link"] = "javascript:verClienteNegativacao({$dados["id"]});";
      }
          	
      if ($this->permissoes["clientes"]["ver_df"]) {
        $dados["cliente_id"] = "<a href=\"?m=clientes&a=ver_df&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
      }
      
      if ($dados["status"] == "Ativa") {
      	$dados["cor_linha"] = "#FFC0C0";
      }
      
      $clientes_negativacoes[] = $dados;
      
    }
    
    return $clientes_negativacoes;
  	
  }
  
  function geraListagemClientesNegativacoes($itens) {
  	
  	$cabecalho = array ("codigo" => "Código", "cliente_id" => "ID", "cpf_cnpj" => "CPF/CNPJ", "nome" => "Nome", "inclusao" => "Inclusão", "baixa" => "Baixa", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("60", "50", "120", "0", "80", "80", "80"), null, array("center", "center", "center", "left", "center", "center", "center"));
    
    return $listagem->conteudo;
  	
  }
  
  function buscaClientesNegativacoesAdicionar($cliente_id) {
  	
  	if ($this->permissoes["clientes_negativacoes"]["adicionar"]) {
			return "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"clientes_negativacoes_adicionar\" value=\"Adicionar\" onClick=\"adicionarClienteNegativacao({$cliente_id})\">";
		}
		
		return "";
		
  }
  
  function ehClienteNegativado($cliente_id) {
  	
  	$query = "
  		SELECT
  			COUNT(*)
  		FROM
  			tab_clientes_negativacoes
  		WHERE
  			cln_status = 'Ativa' AND
  			cln_cpf_cnpj = (
  				SELECT
  					cli_cpf_cnpj
  				FROM
  					tab_clientes
  				WHERE
  					cli_id = {$cliente_id}
  			)";
  	
  	return $this->bancodedados->resultado($this->bancodedados->query($query)) > 0;
  	
  }
  
  function buscaDadosCliente($cliente_id) {
  	
  	$cliente_id = intval($cliente_id, 10);
  	
    $query = "
      SELECT
        cli_id AS id,
        cli_nome AS nome,
        cli_cpf_cnpj AS cpf_cnpj
      FROM
        tab_clientes
      WHERE
        cli_id = $cliente_id";
    
    $query = $this->bancodedados->query($query);
    
    return $this->bancodedados->resultadoArray($query);

  }
  
  function registraComentario($cliente_negativacao_id, $conteudo, $responsavel_id = null) {
  	
    $responsavel_id = $responsavel_id ?  $responsavel_id : "NULL";
    
    $query = "INSERT INTO tab_clientes_negativacoes_comentarios (cnc_datahora, cln_id, cnc_conteudo, usu_id) VALUES (NOW(), $cliente_negativacao_id, '$conteudo', $responsavel_id)";
    $this->bancodedados->query($query);
    
  }
  
  function buscaComentarios($cliente_negativacao_id) {
  	
  	$comentarios = array();
  	
    $query = "
			SELECT
			  IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel,
			  DATE_FORMAT(tab_clientes_negativacoes_comentarios.cnc_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
			  tab_clientes_negativacoes_comentarios.cnc_conteudo AS conteudo
			FROM
			  tab_clientes_negativacoes_comentarios
			  LEFT OUTER JOIN tab_usuarios ON (tab_clientes_negativacoes_comentarios.usu_id = tab_usuarios.usu_id)
			WHERE
			  tab_clientes_negativacoes_comentarios.cln_id = $cliente_negativacao_id
			ORDER BY
			  tab_clientes_negativacoes_comentarios.cnc_datahora ASC,
			  tab_clientes_negativacoes_comentarios.cnc_id ASC";
    
    $query = $this->bancodedados->query($query);
    
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	
      $dados["detalhamento"] = "<b>{$dados["responsavel"]}</b> em ";
      $dados["detalhamento"] .= "<b>{$dados["data_hora"]}</b><br>";
      $dados["detalhamento"] .= substr($dados["conteudo"], 0, 3) == "<b>" ? $dados["conteudo"] : "<b>Comentário:</b> {$dados["conteudo"]}";
      
      $comentarios[] = $dados;
      
    }
    
    return $comentarios;
    
  }

  function geraComentariosListagem($comentarios) {
  	
    $cabecalho = array ("detalhamento" => "Detalhamento");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
    
    return $listagem->conteudo;
    
  }
  
  #################
  ### Adicionar ###
  #################
  
  function adicionar() {
  	
  	$dados_cliente = $this->buscaDadosCliente($_GET["i"]);
  	
    if ($dados_cliente) {
	  	
			if (!$_POST) {
				
	        $this->conteudo .= $this->carregaArquivo("clientes_negativacoes.adicionar.inc.php");
	        
	        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar&i={$dados_cliente["id"]}", $this->conteudo);
	        $this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
	        $this->conteudo = str_replace("[cliente]", $dados_cliente["nome"], $this->conteudo);
	        $this->conteudo = str_replace("[cpf_cnpj]", $dados_cliente["cpf_cnpj"], $this->conteudo);
	        $this->conteudo = str_replace("[data_inclusao]", date("d/m/Y"), $this->conteudo);
	        
	        $botoes[] = "<input type=\"submit\" name=\"salvar\" class=\"buttonAzulEscuroBranco11\" value=\"Salvar\">";
	        $botoes[] = "<input type=\"button\" name=\"fechar\" class=\"buttonAzulEscuroBranco11\" value=\"Fechar\" onClick=\"window.close()\">";
	        
	        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
	        
	      } else {
	      	
					$_POST["codigo"] = intval($_POST["codigo"], 10);
					$_POST["data_inclusao"] = substr($_POST["data_inclusao"], 6, 4)."-".substr($_POST["data_inclusao"], 3, 2)."-".substr($_POST["data_inclusao"], 0, 2);
					
	        $query = "
	        	INSERT INTO
	        		tab_clientes_negativacoes (cln_codigo, cln_cpf_cnpj, cln_nome, cln_inclusao, cli_id)
	        	VALUES
	        		('{$_POST["codigo"]}', '{$dados_cliente["cpf_cnpj"]}', '{$dados_cliente["nome"]}', '{$_POST["data_inclusao"]}', {$dados_cliente["id"]})";
	        
	        $this->bancodedados->query($query);
	        
	        $id = $this->bancodedados->ultimoIdInserido();
	        
	        // Registra Comentário
	        
	        if ($_POST["comentarios"]) $comentarios = "<br><b>Comentário:</b> ".trim(addslashes($_POST["comentarios"]));
	        $this->registraComentario($id, "<b>Ação:</b> Adicionada".$comentarios, $_SESSION["usuario"]);
	        
	        // Registra Atividade
	        
	        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
	        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"],$this->acao, $id);
	        
	        // Redireciona
	        
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
  	
  		if (!$_POST) {
	  		
		    $this->conteudo .= $this->carregaArquivo("clientes_negativacoes.ver.inc.php");
	  		
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=ver&i={$this->dados_item["id"]}", $this->conteudo);
		    $this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
	      $this->conteudo = str_replace("[cliente]", $this->dados_item["nome"], $this->conteudo);
	      $this->conteudo = str_replace("[cpf_cnpj]", $this->dados_item["cpf_cnpj"], $this->conteudo);
	      $this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
	      $this->conteudo = str_replace("[data_inclusao]", $this->dados_item["inclusao"], $this->conteudo);
	      $this->conteudo = str_replace("[data_baixa]", $this->dados_item["baixa"], $this->conteudo);
	      $this->conteudo = str_replace("[comentarios]", $this->geraComentariosListagem($this->buscaComentarios($this->dados_item["id"])), $this->conteudo);
	      $this->conteudo = str_replace("[status]", $this->dados_item["status"], $this->conteudo);
	      
	      // Botões
	      
	      if ($this->permissoes[$this->dados_modulo["nome"]]["baixar"] && $this->dados_item["status"] == "Ativa") $botoes[] = "<input type=\"button\" name=\"baixar\" class=\"buttonAzulEscuroBranco11\" value=\"Baixar\" onClick=\"window.location='?m={$this->dados_modulo["nome"]}&a=baixar&i={$this->dados_item["id"]}';\">";
	      $botoes[] = "<input type=\"submit\" name=\"salvar\" class=\"buttonAzulEscuroBranco11\" value=\"Salvar\">";
	      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
	      
	      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      
  		} else {
  			
  			$_POST["comentarios"] = trim($_POST["comentarios"]);
  			
  			if ($_POST["comentarios"]) {
  				
          $_POST["comentarios"] = "<b>Comentario</b>: ".addslashes($_POST["comentarios"]);
          $this->registraComentario($this->dados_item["id"], $_POST["comentarios"], $_SESSION["usuario"]);
  				
  			}
	        
        // Redireciona
        
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$this->dados_item["id"]}");
  			
  		}
	      
    } else {
    	
      $this->fechar();
      
    }
  	
  }
    
  ##############
  ### Baixar ###
  ##############
  
  function baixar() {
  	
  	$this->buscaDadosItem($_GET["i"]);
  	
  	if ($this->dados_item) {
	  	
			if (!$_POST) {
				
	        $this->conteudo .= $this->carregaArquivo("clientes_negativacoes.baixar.inc.php");
	        
	        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=baixar&i={$this->dados_item["id"]}", $this->conteudo);
			    $this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
		      $this->conteudo = str_replace("[cliente]", $this->dados_item["nome"], $this->conteudo);
		      $this->conteudo = str_replace("[cpf_cnpj]", $this->dados_item["cpf_cnpj"], $this->conteudo);
		      $this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
		      $this->conteudo = str_replace("[data_inclusao]", $this->dados_item["inclusao"], $this->conteudo);
		      $this->conteudo = str_replace("[data_baixa]", date("d/m/Y"), $this->conteudo);
		      $this->conteudo = str_replace("[status]", $this->geraSelect(array("Baixada" => "Baixada", "Decursada" => "Decursada"), null, true, "status"), $this->conteudo);
		      
	        $botoes[] = "<input type=\"submit\" name=\"salvar\" class=\"buttonAzulEscuroBranco11\" value=\"Salvar\">";
        	$botoes[] = "<input type=\"button\" name=\"voltar\" class=\"buttonAzulEscuroBranco11\" value=\"Voltar\" onClick=\"history.back()\">";
	        $botoes[] = "<input type=\"button\" name=\"fechar\" class=\"buttonAzulEscuroBranco11\" value=\"Fechar\" onClick=\"window.close()\">";
	        
	        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
	        
	      } else {
	      	
					$_POST["data_baixa"] = substr($_POST["data_baixa"], 6, 4)."-".substr($_POST["data_baixa"], 3, 2)."-".substr($_POST["data_baixa"], 0, 2);
					
	        $query = "
	        	UPDATE
	        		tab_clientes_negativacoes
	        	SET
	        		cln_baixa = '{$_POST["data_baixa"]}',
	        		cln_status = '{$_POST["status"]}'
	        	WHERE
	        		cln_id = {$this->dados_item["id"]}";
	        
	        $this->bancodedados->query($query);
	        
	        // Registra Comentário
	        
	        if ($_POST["comentarios"]) $comentarios = "<br><b>Comentário:</b> ".trim(addslashes($_POST["comentarios"]));
	        $this->registraComentario($this->dados_item["id"], "<b>Ação:</b> {$_POST["status"]}".$comentarios, $_SESSION["usuario"]);
	        
	        // Registra Atividade
	        
	        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
	        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"],$this->acao, $this->dados_item["id"]);
	        
	        // Redireciona
	        
	        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$this->dados_item["id"]}");
					
	      }
	      
    } else {
    	
      $this->fechar();
    	
    }
    
  }
  
}
 
?>
