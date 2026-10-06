<?php

require_once("_global/modulo.class.inc.php");

class consultor extends modulo
{

  public $paginas_atual;
  public $paginas_total;
  public $paginacao;
  public $listagem;
  public $lista_itens;

  private $valor_status = array(
    1 => "Ativo",
    0 => "Inativo",
  );

  function __construct($bancodedados, $permissoes, $executar = true)
  {
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

  function buscaDadosItem($id)
  {
    $query = "SELECT * FROM tab_consultor WHERE id = '$id'";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $this->dados_item["id"] = $id;
      $this->dados_item["nome"] = $dados["nome"];
      $this->dados_item["data_cadastro"] = !empty($dados["data_cadastro"]) ? date("d/m/Y H:i:s", strtotime($dados["data_cadastro"])) : "";
      $this->dados_item["status_id"] = $dados["status"];
      $this->dados_item["status_nome"] = $this->valor_status[$dados["status"]];
    } else {
      unset($this->dados_item);
    }
  }
  ##########################
  ######### Listar #########
  ##########################

  function listar()
  {
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

  function contaPaginas()
  {
    $query = "SELECT count(*) AS itens FROM tab_consultor";
    $query = $this->bancodedados->query($query);
    $itens = $this->bancodedados->resultado($query, 0, "itens");
    $paginas = $itens / PAG_ITENS;
    if (!is_int($paginas)) {
      $paginas = intval($paginas) + 1;
    }
    return $paginas;
  }

  function buscaItens()
  {
    $limit_inicio = ($this->paginas_atual - 1) * PAG_ITENS;
    $limit_fim = PAG_ITENS;
    $query = "SELECT * FROM tab_consultor ORDER BY nome";
    $query = $this->bancodedados->query($query);
    $status = 1;
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
        $item_tratado["link"] = "?m={$this->dados_modulo["nome"]}&a=ver&i={$dados["id"]}";
      }
      $item_tratado["nome"] = $dados["nome"];
      $item_tratado["data_cadastro"] = !empty($dados["data_cadastro"]) ? date("d/m/Y H:i:s", strtotime($dados["data_cadastro"])) : "";
      $item_tratado["status"] = $dados["status"] ? "Ativo" : "Inativo";
      $this->lista_itens[] = $item_tratado;
    }
  }


  function geraListagem()
  {
    $cabecalho = array("nome" => "Nome", "data_cadastro" => "Data Cadastro", "status" => "Status");
    $this->listagem = controle::instanciaObjeto("listagem", $cabecalho, $this->lista_itens);
  }

  #############################
  ######### Adicionar #########
  #############################

  function adicionar()
  {
    if (!$_POST) {

      $this->conteudo .= $this->carregaArquivo("consultor.adicionar.inc.php");
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=listar\';">';
      
      $this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->geraSelect($this->valor_status, null, false, "status"), $this->conteudo);
    } else {

      $query = "INSERT INTO tab_consultor (nome, status) VALUES ('{$_POST["nome"]}', {$_POST["status"]})";
      $this->bancodedados->query($query);
      $id = $this->bancodedados->ultimoIdInserido();

      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
    }
  }

  #######################
  ######### Ver #########
  #######################

  function ver()
  {
    $this->buscaDadosItem($_GET["i"]);

    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("consultor.ver.inc.php");
      $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[data_cadastro]', $this->dados_item["data_cadastro"], $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);

      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=listar\';">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ##########################
  ######### Editar #########
  ##########################

  function editar()
  {
    $this->buscaDadosItem($_GET["i"]);

    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("consultor.editar.inc.php");
        $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
        $this->conteudo = str_replace('[data_cadastro]', $this->dados_item["data_cadastro"], $this->conteudo);
        $this->conteudo = str_replace('[status]', $this->geraSelect($this->valor_status, $this->dados_item["status_id"], false, "status"), $this->conteudo);

        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["remover"] && !$this->temConsultorCadastrado($this->dados_item["id"])) {
          $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="if (confirm(\'Tem certeza que deseja remover este consultor?\')) { window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\'; }">';
        }
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=listar\';">';

        $this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
      } else {

        $query = "UPDATE tab_consultor SET nome = '{$_POST["nome"]}', status = {$_POST["status"]} WHERE id = {$_GET["i"]}";
        $this->bancodedados->query($query);

        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  function temConsultorCadastrado($consultorId)
  {
    $query = "SELECT COUNT(*) AS total FROM tab_clientes WHERE consultor = '$consultorId'";
    $query = $this->bancodedados->query($query);
    $total = $this->bancodedados->resultado($query, 0, "total");

    return $total > 0;
  }

  ###########################
  ######### Remover #########
  ###########################

  function remover()
  {
    $this->buscaDadosItem($_GET["i"]);

    if (!empty($this->dados_item)) {
      if (empty($_POST["confirmar"])) {
        $this->conteudo .= $this->carregaArquivo("consultor.remover.inc.php");
        $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"], $this->conteudo);
      } else {

        $query = "DELETE FROM tab_consultor WHERE id = '{$_GET["i"]}'";
        $this->bancodedados->query($query);

        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        header("Location: ./?m=" . $this->dados_modulo["nome"]);
      }
    } else {
      header("Location: ./?m=" . $this->dados_modulo["nome"]);
    }
  }
}
