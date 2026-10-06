<?

class interfaces {

  var $cabecalho;
  var $menu;
  var $corpo;
  var $rodape;
  var $conteudo;

  function __construct($bancodedados = null, $permissoes = null) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
  }
  
  function temPermissao($acao, $modulo) {
  	return is_array($this->permissoes) && is_string($acao) && array_key_exists($modulo, $this->permissoes) && array_key_exists($acao, $this->permissoes[$modulo]);
  }
  
  function carregaArquivo($arquivo) {
    if (file_exists(dirname(__FILE__)."/".$arquivo)) $arquivo = dirname(__FILE__)."/".$arquivo;
    $fp = fopen($arquivo, "r");
    $retorno = fread($fp, filesize($arquivo));
    fclose($fp);
    return $retorno;
  }

  function geraEstrutura() {
    $this->conteudo = $this->carregaArquivo("interfaces.estrutura.inc.php");
  }

  function geraBuscaCliente() {
    $acao = ($_GET["m"] == "clientes" && in_array($_GET["a"], array("ver_dc", "editar_dc", "ver_df", "editar_df", "ver_dm", "editar_dm", "ver_dt", "editar_dt", "ver_da", "editar_da", "ver_ch", "editar_ch"))) ? $_GET["a"] : "ver_dc";
    $busca_cliente  = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
    $busca_cliente .= '  <tr>';
    $busca_cliente .= '    <td><input type="text" id="id" class="inputBrancoAzulEscuro11" onkeypress="irPara(this, event); return formataID(this, event);" maxlength="20" style="height:16px;width:100%;padding-left:2px;padding-right:2px;padding-top:1px;padding-bottom:1px;border:0px"></td>';
    $busca_cliente .= '    <td width="2"></td>';
    $busca_cliente .= '    <td width="18"><input type="button" class="buttonAzulEscuroBranco11" id="ir" value="Ir" onClick="window.location=\'?m=clientes&a='.$acao.'&i=\' + document.getElementById(\'id\').value" style="width:18px;height:16px;padding:0px"></td>';
    $busca_cliente .= '  </tr>';
    $busca_cliente .= '</table>';
    $this->conteudo = str_replace("[busca_cliente]", $busca_cliente, $this->conteudo);
  }
  
  function geraCabecalho($painel) {
    $this->cabecalho = $this->carregaArquivo("interfaces.cabecalho.inc.php");
    $this->conteudo = str_replace("[cabecalho]", $this->cabecalho, $this->conteudo);
    if ($painel) {
      $this->conteudo = str_replace("[painel_mensagem]", stripslashes(geraPainelMensagem($_SESSION["usuario"])), $this->conteudo);
      $this->conteudo = str_replace("[painel_ramais]", $this->geraPainelRamais(), $this->conteudo);
      $this->conteudo = str_replace("[painel_datahora]", date("d/m H:i"), $this->conteudo);
      $this->conteudo = str_replace("[placar]", stripslashes(geraPainelPlacar($_SESSION["placar_setor"], $_SESSION["placar_dia"], $_SESSION["placar_mes"], $_SESSION["placar_ano"], $this->permissoes)), $this->conteudo);
    }
  }
  
  function geraPainelRamais() {
    global $login;
    $painel = "";
    $usuarios = $this->buscaUsuarios();
    if ($usuarios) {
      foreach ($usuarios as $usuario) {
        if ($login->usuario["ramal"]) {
          $painel .= ", ['<table width=\"100%\" height=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr class=\"m0innerOver\" style=\"font-weight:normal\"><td width=\'130\'>{$usuario["nome"]}</td><td>{$usuario["setor"]}</td><td width=\'50\' align=\'right\'>{$usuario["ramal"]}&nbsp;</td></tr></table>', 'javascript:discar(\'{$usuario["ramal"]}\')', {'bl': 249, 'tw' : '_top'}]";
        } else {
          $painel .= ", ['<table width=\"100%\" height=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr class=\"m0innerOver\" style=\"font-weight:normal\"><td width=\'130\'>{$usuario["nome"]}</td><td>{$usuario["setor"]}</td><td width=\'50\' align=\'right\'>{$usuario["ramal"]}&nbsp;</td></tr></table>', '', {'bl': 249, 'tw' : '_top'}]";
        }
      }
    }
    return "['<img src=\"./imagens/telefone.png\" width=\"13\" height=\"13\">', null, {'sw': 28}{$painel}]";
  }
  
  function buscaRamal() {
    $query = "
      SELECT
        usu_ramal
      FROM
        tab_usuarios
      WHERE
        usu_id = {$_SESSION["usuario"]}";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query);
  }

  function geraHintRamais() {
  	$usuarios = $this->buscaUsuarios();
  	$hint = array();
    if ($usuarios) {
	  	$hint[] = "<table width=\"100%\" cellpadding=\"1\" cellspacing=\"1\" border=\"0\" bgcolor=\"#FFFFFF\">";
	  	$hint[] = "<tr bgcolor=\"#505050\" class=\"txtBranco10\"><td colspan=\"3\" align=\"center\"><b>Lista de Ramais</b></td></tr>";
      $hint[] = "<tr bgcolor=\"#505050\" class=\"txtBranco10\">";
      $hint[] = "<td><b>Setor</b></td>";
      $hint[] = "<td><b>Nome</b></td>";
      $hint[] = "<td align=\"center\"><b>Ramal</b></td>";
      $hint[] = "</tr>";
      foreach ($usuarios as $ramal => $setores) {
        foreach ($setores as $setor => $usuarios) {
          $hint[] = "<tr bgcolor=\"#505050\" class=\"txtBranco10\">";
          $hint[] = "<td>$setor</td>";
          $hint[] = "<td>".implode(" / ", $usuarios)."</td>";
          $hint[] = "<td align=\"center\">$ramal</td>";
          $hint[] = "</tr>";
        }
      }
      $hint[] = "</table>";
	  	return str_replace('"', '\\\'', implode($hint));
    }
  }

  function buscaUsuarios() {
  	$usuarios = array();
    $query = "
      SELECT
        SUBSTRING_INDEX(tab_usuarios.usu_nome, ' ', 1) AS nome,
        CONCAT(IF(tab_organizacao_setores.ors_pai = 2 AND tab_organizacao_setores.ors_id != 15, 'Comercial ', ''), tab_organizacao_setores.ors_nome) AS setor,
        tab_usuarios.usu_ramal AS ramal
      FROM
        tab_usuarios
        INNER JOIN rel_usu_orf_ors_exerce ON (tab_usuarios.usu_id = rel_usu_orf_ors_exerce.usu_id)
        INNER JOIN tab_organizacao_setores ON (rel_usu_orf_ors_exerce.ors_id = tab_organizacao_setores.ors_id)
      WHERE
        tab_usuarios.uss_id = 1 AND
        tab_usuarios.usu_ramal != ''".($_SESSION["origem"] ? " AND
		tab_usuarios.clo_id = {$_SESSION["origem"]}" : "")."
      GROUP BY
        tab_usuarios.usu_id
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["nome"] = explode(" ", $dados["nome"]);
      $dados["nome"] = $dados["nome"][0];
      $usuarios[] = $dados;
    }
    return $usuarios;
  }

  function geraRodape() {
    $this->rodape = $this->carregaArquivo("interfaces.rodape.inc.php");
    $this->conteudo = str_replace("[rodape]", $this->rodape, $this->conteudo);
  }

  function geraMenu() {
    $links = $this->geraMenuLinks();
    $this->conteudo = str_replace("[menu_itens]", $this->geraMenuEstrutura($links), $this->conteudo);
    $this->conteudo = str_replace("[menu]", $this->menu, $this->conteudo);
  }

  function geraMenuLinks() {
  	$menu_itens = array();
    $query = "
      SELECT
        men_id AS id,
        men_nome AS nome,
        men_pai AS pai,
        men_modulo AS modulo,
        men_acao AS acao,
        men_url AS url,
        men_target AS target
      FROM
        tab_menus
      ORDER BY
        men_pai DESC, men_ordem, men_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $menu_itens[$dados["id"]] = $dados;
    }
    foreach ($menu_itens as $id => $menu_item) {
      if ($menu_item["modulo"] || $menu_item["acao"] || $menu_item["url"]) {
        if ($this->temPermissao($menu_item["acao"], $menu_item["modulo"]) || (!$menu_item["modulo"] && !$menu_item["acao"] && $menu_item["url"])) {
          if (!$menu_item["url"]) $menu_itens[$id]["url"] = "./?m={$menu_item["modulo"]}&a={$menu_item["acao"]}";
          if (!$menu_item["target"]) $menu_itens[$id]["target"] = "_top";
          $menu_itens[$menu_item["pai"]]["filhos"][] = $menu_itens[$id];
        }
        unset($menu_itens[$id]);
      } elseif (array_key_exists($menu_item["pai"], $menu_itens) && $menu_itens[$menu_item["pai"]] && array_key_exists("filhos", $menu_itens[$id]) && $menu_itens[$id]["filhos"]) {
        $menu_itens[$menu_item["pai"]]["filhos"][] = $menu_itens[$id];
        unset($menu_itens[$id]);
      } elseif (!array_key_exists("filhos", $menu_itens[$id]) || !$menu_itens[$id]["filhos"]) {
        unset($menu_itens[$id]);
      }
    }
    ksort($menu_itens);
    return $menu_itens;
  }

  function geraMenuEstrutura($categorias) {
    $estrutura = "";
  	$quant_categorias = sizeof($categorias);
    if ($quant_categorias > 0) {
      $tamanho = $this->buscaMenuTamanho($quant_categorias);
      $categoria = 0;
      do {
        $modulos = current ($categorias);
        if ($categoria > 0 && $categoria < $quant_categorias) {
        	$estrutura .= ", "; 
        }
        $categoria++;
        // Gera abas principais
        $estrutura .= "['{$modulos["nome"]}', null, {'sw':{$tamanho}, 'sb':'{$modulos["nome"]}'}";

        for ($i = 0; $i < sizeof($modulos["filhos"]); $i++) {
          $estrutura .= ", ";
          $pai = $this->formataPaiNome($modulos["filhos"][$i]);

          // Gera link direto quando existe apenas um filho no sub-menu
          if (sizeof($pai["filhos"]) == 1) {
            $filho = $this->formataFilhoLink($pai["filhos"][0]);
            $estrutura .= "['{$pai["nome"]}', '{$filho["url"]}', {'tw' : '{$filho["target"]}'}";
          } else {
            $submenu = "";
          	// Gera todos os itens do sub-menu
            for ($j=0; $j < sizeof($pai["filhos"]); $j++) {
              $filho = $this->formataFilhoLink($pai["filhos"][$j]);
              $submenu .= ", ['{$filho["nome"]}', '{$filho["url"]}', {'tw' : '{$filho["target"]}', 'sb':'{$filho["nome"]}'}]";
            }
            // Anexa o sub-menu ao menu
            $estrutura .= '[\'<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0"><tr class="m0innerOver"><td>'.$pai["nome"].'</td><td align=\\\'right\\\'>&raquo;&nbsp;</td></tr></table>\', null, {\'bl\':149, \'bt\':-1, \'sb\':\''.$pai["nome"].'\'}';
            $estrutura .= $submenu;
          }
          $estrutura .= "]";
        }
        $estrutura .= "]";
      } while (next($categorias));
    }
    return $estrutura;
  }

  function buscaMenuTamanho($quant_categorias) {
    return ceil((536 + (2*($quant_categorias))) / ($quant_categorias));
  }

  function formataPaiNome($pai) {
    if (strlen($pai["nome"]) > 24) {
    	$pai["nome"] = substr($pai["nome"], 0, 20) . "...";
    }
    return $pai;
  }

  function formataFilhoLink($filho) {
    if (!$filho["url"]) {
      $filho["url"] = "/?m={$filho["modulo"]}&a={$filho["acao"]}";
    }
    return $filho;
  }

  function geraCorpo($corpo) {
    $this->conteudo = str_replace("[corpo]", $corpo, $this->conteudo);
    $this->conteudo = str_replace("[exibe_corpo]", $corpo ? "table-row" : "none", $this->conteudo);
  }

  function geraTelaModulo ($modulo) {
    if (!$modulo) $this->geraTelaInterna($modulo->conteudo);
    if ($modulo->dados_acao["tipo"] == 2) return $this->geraTelaJanela($modulo->conteudo);
    return $this->geraTelaInterna($modulo->conteudo);
  }

  function geraTelaInterna($conteudo = "") {
    $this->geraEstrutura();
    $this->geraBuscaCliente();
    $this->geraCabecalho(true);
    $this->geraRodape();
    $this->geraMenu();
    $this->geraCorpo($conteudo);
  }

  function geraTelaLogin($retorno, $alerta = '') {
    $this->conteudo = $this->carregaArquivo("interfaces.login.inc.php");
    $this->conteudo = str_replace("[alerta]", $alerta, $this->conteudo);
    $this->conteudo = str_replace("[retorno]", $retorno, $this->conteudo);
    $this->geraCabecalho(false);
    $this->geraRodape();
  }

  function geraTelaErro($mensagem) {
    $this->conteudo = $this->carregaArquivo("interfaces.erro.inc.php");
    $this->conteudo = str_replace("[mensagem]", $mensagem, $this->conteudo);
    $this->geraCabecalho(false);
    $this->geraRodape();
  }

  function geraTelaJanela($conteudo = "") {
    $this->conteudo = $this->carregaArquivo("interfaces.janela.inc.php");
    $this->conteudo = str_replace("[conteudo]", $conteudo, $this->conteudo);
  }

  ##########################
  ### Sistema de Mailing ###
  ##########################

  function geraEstruturaMailingSistema() {
    $this->conteudo = $this->carregaArquivo("interfaces.mailing_sistema.inc.php");
  }

  function geraEstruturaMailingSistemaOperacao() {
    $operacao = $this->carregaArquivo("interfaces.mailing_sistema.operacao.inc.php");
    $this->conteudo = str_replace("[operacao]", $operacao, $this->conteudo);
  }

  function geraEstruturaMailingSistemaScript() {
    $script = $this->carregaArquivo("interfaces.mailing_sistema.script.inc.php");
    $this->conteudo = str_replace("[script]", $script, $this->conteudo);
  }

  function geraEstruturaMailingSistemaContato() {
    $contato = $this->carregaArquivo("interfaces.mailing_sistema.contato.inc.php");
    $this->conteudo = str_replace("[contato]", $contato, $this->conteudo);
  }

  function geraTelaMailingSistema() {
    $this->geraEstruturaMailingSistema();
    $this->geraCabecalho(false);
    $this->geraEstruturaMailingSistemaOperacao();
    $this->geraEstruturaMailingSistemaScript();
    $this->geraEstruturaMailingSistemaContato();
    $this->geraRodape();
  }

  ##############################
  ### Sistema de Recuperação ###
  ##############################

  function geraEstruturaRecuperacaoSistema() {
    $this->conteudo = $this->carregaArquivo("interfaces.recuperacao_sistema.inc.php");
  }

  function geraEstruturaRecuperacaoSistemaOperacao() {
    $operacao = $this->carregaArquivo("interfaces.recuperacao_sistema.operacao.inc.php");
    $this->conteudo = str_replace("[operacao]", $operacao, $this->conteudo);
  }

  function geraEstruturaRecuperacaoSistemaScript() {
    $script = $this->carregaArquivo("interfaces.recuperacao_sistema.script.inc.php");
    $this->conteudo = str_replace("[script]", $script, $this->conteudo);
  }

  function geraEstruturaRecuperacaoSistemaContato () {
    $contato = $this->carregaArquivo("interfaces.recuperacao_sistema.contato.inc.php");
    $this->conteudo = str_replace("[contato]", $contato, $this->conteudo);
  }

  function geraTelaRecuperacaoSistema() {
    $this->geraEstruturaRecuperacaoSistema();
    $this->geraCabecalho(false);
    $this->geraEstruturaRecuperacaoSistemaOperacao();
    $this->geraEstruturaRecuperacaoSistemaScript();
    $this->geraEstruturaRecuperacaoSistemaContato();
    $this->geraRodape();
  }

  ############################
  ### Sistema de Operações ###
  ############################

  function geraEstruturaOperacaoSistema() {
    $this->conteudo = $this->carregaArquivo("interfaces.operacoes_sistema.inc.php");
  }

  function geraEstruturaOperacaoSistemaOperacao() {
    $operacao = $this->carregaArquivo("interfaces.operacoes_sistema.operacao.inc.php");
    $this->conteudo = str_replace("[operacao]", $operacao, $this->conteudo);
  }

  function geraEstruturaOperacaoSistemaScript() {
    $script = $this->carregaArquivo("interfaces.operacoes_sistema.script.inc.php");
    $this->conteudo = str_replace("[script]", $script, $this->conteudo);
  }

  function geraEstruturaOperacaoSistemaContato() {
    $contato = $this->carregaArquivo("interfaces.operacoes_sistema.contato.inc.php");
    $this->conteudo = str_replace("[contato]", $contato, $this->conteudo);
  }

  function geraEstruturaOperacaoSistemaRegistro() {
    $this->conteudo = str_replace("[registro]", "Carregando...", $this->conteudo);
  }

  function geraEstruturaOperacaoSistemaHistorico() {
    $this->conteudo = str_replace("[historico]", "Carregando...", $this->conteudo);
  }

  function geraTelaOperacaoSistema() {
    $this->geraEstruturaOperacaoSistema();
    $this->geraCabecalho(false);
    $this->geraEstruturaOperacaoSistemaOperacao();
    $this->geraEstruturaOperacaoSistemaScript();
    $this->geraEstruturaOperacaoSistemaContato();
    $this->geraEstruturaOperacaoSistemaRegistro();
    $this->geraEstruturaOperacaoSistemaHistorico();
    $this->geraRodape();
  }

  ###########################
  ### Integração - Online ###
  ###########################

  function geraTelaIntegracaoOnline($conteudo, $origem_id = null) {
    $this->conteudo = $this->carregaArquivo("interfaces.online.inc.php");
    $this->conteudo = str_replace("[logo]", $this->buscaLogoOrigem($origem_id), $this->conteudo);
    $this->conteudo = str_replace("[origem_nome]", $this->buscaOrigemNome($origem_id), $this->conteudo);
    $this->conteudo = str_replace("[conteudo]", $conteudo, $this->conteudo);
  }

  function buscaLogoOrigem($origem_id) {
    $origem_id = $origem_id ? $origem_id : 1;
    $origens[1] = "wave";
    $origens[2] = "csm";
    $origens[3] = "sodisa";
    $origens[4] = "logo_ole";
    return $origens[$origem_id];
  }

  function buscaOrigemNome($origem_id) {
    $origem_id = $origem_id ? $origem_id : 4;
    $origens[1] = "Wave System Internet";
    $origens[2] = "CSM Telecom";
    $origens[3] = "SodisaNET";
    $origens[4] = "Olé Telecom";
    return $origens[$origem_id];
  }

  ##################
  ### Exibe Tela ###
  ##################

  function mostra() {
    preg_match_all('/<script type="text\/javascript" src="[^<]+"><\/script>[\s]+/', $this->conteudo, $scripts);
    $this->conteudo = str_replace($scripts[0], "", $this->conteudo);
    $this->conteudo = str_replace("</head>", implode($scripts[0])."</head>", $this->conteudo);
    print $this->conteudo;
  }

}

?>
