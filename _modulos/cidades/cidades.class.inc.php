<?php

require_once("_global/modulo.class.inc.php");

class cidades extends modulo {

    var $bancodedados;
    var $permissoes;
    var $acao;
    var $conteudo;
    var $dados_item;
    var $dados_modulo;
    var $dados_acao;

    function __construct($bancodedados, $permissoes) {

        $this->bancodedados = $bancodedados;
        $this->permissoes = $permissoes;

        $this->buscaDadosModulo($_GET["m"]);
        if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) {
            $_GET["a"] = "editar";
        }
        
        $this->acao = $_GET["a"] ? $_GET["a"] : "listar";
        $this->conteudo = $this->geraTitulo();
        $this->{$this->acao}();
    }

    function ehAdmin($id = null) {

        $user = $id ? $id : $_SESSION["usuario"];
        $query = "SELECT usu_administrador FROM tab_usuarios WHERE usu_id = $user";
        $query = $this->bancodedados->query($query);
        $ehAdmin = $this->bancodedados->resultadoArray($query);

        return $ehAdmin["usu_administrador"] == 0 ? false : true;
    }

    function buscaEstados() {

        $estados = array();
        $query = $this->bancodedados->query("
            SELECT
                est_id AS id,
                est_nome AS nome
            FROM
                tab_estados
            ORDER BY
                est_nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $estados[$dados["id"]] = $dados["nome"];
        }

        return $estados;
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
                clo_nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $origens[$dados["id"]] = $dados["nome"];
        }

        return $origens;
    }

    function buscaDadosItem($id) {

        $query = "
            SELECT
                tab_cidades.cid_id AS id,
                tab_cidades.cid_nome AS cid_nome,
                tab_cidades.cid_ibge AS cid_ibge,
                tab_cidades.cid_tom AS cid_tom,
                tab_cidades.cid_cep_geral AS cid_cep_geral,
                tab_cidades.est_id AS est_id,
                tab_estados.est_nome AS est_nome,
                tab_cidades.clo_id AS clo_id,
                tab_clientes_origens.clo_nome AS clo_nome
            FROM
                tab_cidades
                INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
                LEFT JOIN tab_clientes_origens ON (tab_cidades.clo_id = tab_clientes_origens.clo_id)
            WHERE
                tab_cidades.cid_id = $id";

        $query = $this->bancodedados->query($query);
        $this->dados_item = $this->bancodedados->resultadoArray($query);
        if (!$this->dados_item) {
            unset($this->dados_item);
        }
    }

    function adicionar() {

        if (!$_POST) {
            $this->conteudo .= $this->carregaArquivo("cidades.adicionar.inc.php");
            $this->conteudo = str_replace("[est_id]", $this->geraSelect($this->buscaEstados(), null, true, "est_id", "250px"), $this->conteudo);
            $this->conteudo = str_replace("[clo_id]", $this->geraSelect($this->buscaOrigens(), null, true, "clo_id", "250px"), $this->conteudo);

            $botoes = array();
            $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
            $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
            $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
        } else {
            $_POST["cid_nome"] = addslashes(trim($_POST["cid_nome"]));
            $_POST["cid_cep_geral"] = preg_replace("/[^0-9]/", "", $_POST["cid_cep_geral"]);

            $cid_ibge = ($_POST["cid_ibge"] !== "" && is_numeric($_POST["cid_ibge"])) ? intval($_POST["cid_ibge"]) : 0;
            $cid_tom = ($_POST["cid_tom"] !== "" && is_numeric($_POST["cid_tom"])) ? intval($_POST["cid_tom"]) : "NULL";
            $cid_cep_geral = (strlen($_POST["cid_cep_geral"]) >= 8 && is_numeric($_POST["cid_cep_geral"])) ? intval($_POST["cid_cep_geral"]) : "NULL";
            $est_id = ($_POST["est_id"] !== "" && is_numeric($_POST["est_id"])) ? intval($_POST["est_id"]) : 0;
            $clo_id = ($_POST["clo_id"] !== "" && is_numeric($_POST["clo_id"])) ? intval($_POST["clo_id"]) : "NULL";

            $query = "
                INSERT INTO
                    tab_cidades (cid_nome, cid_ibge, cid_tom, cid_cep_geral, est_id, clo_id)
                VALUES
                    ('{$_POST["cid_nome"]}', $cid_ibge, $cid_tom, $cid_cep_geral, $est_id, $clo_id)";

            $this->bancodedados->query($query);
            $id = $this->bancodedados->ultimoIdInserido();

            $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
            $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);

            header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
        }
    }

    function editar() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {
            if (!$_POST) {
                $botoes = array();
                $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
                
                if ($this->ehAdmin()) {
                    $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\';">';
                }
                
                $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
                $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

                $this->conteudo .= $this->carregaArquivo("cidades.editar.inc.php");
                $this->conteudo = str_replace("[cid_nome]", $this->dados_item["cid_nome"], $this->conteudo);
                $this->conteudo = str_replace("[cid_ibge]", $this->dados_item["cid_ibge"], $this->conteudo);
                $this->conteudo = str_replace("[cid_tom]", $this->dados_item["cid_tom"], $this->conteudo);
                $this->conteudo = str_replace("[cid_cep_geral]", $this->dados_item["cid_cep_geral"], $this->conteudo);
                $this->conteudo = str_replace("[est_id]", $this->geraSelect($this->buscaEstados(), $this->dados_item["est_id"], true, "est_id", "250px"), $this->conteudo);
                $this->conteudo = str_replace("[clo_id]", $this->geraSelect($this->buscaOrigens(), $this->dados_item["clo_id"], true, "clo_id", "250px"), $this->conteudo);
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
                $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
            } else {
                $_POST["cid_nome"] = addslashes(trim($_POST["cid_nome"]));
                $_POST["cid_cep_geral"] = preg_replace("/[^0-9]/", "", $_POST["cid_cep_geral"]);

                $cid_ibge = ($_POST["cid_ibge"] !== "" && is_numeric($_POST["cid_ibge"])) ? intval($_POST["cid_ibge"]) : 0;
                $cid_tom = ($_POST["cid_tom"] !== "" && is_numeric($_POST["cid_tom"])) ? intval($_POST["cid_tom"]) : "NULL";
                if ($_POST["cid_cep_geral"] === "") {
                    $cid_cep_geral = "NULL";
                } else if (strlen($_POST["cid_cep_geral"]) != 8) {
                    $cid_cep_geral = ($this->dados_item["cid_cep_geral"] !== null && $this->dados_item["cid_cep_geral"] !== "" && is_numeric($this->dados_item["cid_cep_geral"]))
                        ? intval($this->dados_item["cid_cep_geral"])
                        : "NULL";
                } else {
                    $cid_cep_geral = is_numeric($_POST["cid_cep_geral"]) ? intval($_POST["cid_cep_geral"]) : "NULL";
                }
                $est_id = ($_POST["est_id"] !== "" && is_numeric($_POST["est_id"])) ? intval($_POST["est_id"]) : 0;
                $clo_id = ($_POST["clo_id"] !== "" && is_numeric($_POST["clo_id"])) ? intval($_POST["clo_id"]) : "NULL";

                $query = "
                    UPDATE
                        tab_cidades
                    SET
                        cid_nome = '{$_POST["cid_nome"]}',
                        cid_ibge = $cid_ibge,
                        cid_tom = $cid_tom,
                        cid_cep_geral = $cid_cep_geral,
                        est_id = $est_id,
                        clo_id = $clo_id
                    WHERE
                        cid_id = {$_GET["i"]}";

                $this->bancodedados->query($query);

                $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
                $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

                header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
            }
        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}");
        }
    }

    function ver() {

        $this->buscaDadosItem($_GET["i"]);

        if (!empty($this->dados_item)) {
            $this->conteudo .= $this->carregaArquivo("cidades.ver.inc.php");
            $this->conteudo = str_replace("[cid_id]", $this->dados_item["id"], $this->conteudo);
            $this->conteudo = str_replace("[cid_nome]", $this->dados_item["cid_nome"], $this->conteudo);
            $this->conteudo = str_replace("[est_nome]", $this->dados_item["est_nome"], $this->conteudo);
            $this->conteudo = str_replace("[cid_ibge]", $this->dados_item["cid_ibge"], $this->conteudo);
            $this->conteudo = str_replace("[cid_tom]", $this->dados_item["cid_tom"], $this->conteudo);
            $this->conteudo = str_replace("[cid_cep_geral]", $this->dados_item["cid_cep_geral"], $this->conteudo);
            $this->conteudo = str_replace("[clo_nome]", $this->dados_item["clo_nome"], $this->conteudo);

            $this->conteudo = str_replace("[acoes]", $this->geraAcoes(array("ver_dc" => "Dados Cadastrais")), $this->conteudo);

            $botoes = array();
            if ($this->permissoes[$this->dados_modulo["nome"]]["editar_dc"]) {
                $botoes[] = '<input type="button" name="editar_dc" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\';">';
            }
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}");
        }
    }

    function listar() {

        $filtro = $this->inicializaFiltro($_GET["f"]);
        $pagina = $_GET["p"] ? $_GET["p"] : 1;

        $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#252525"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
        $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) {
            $this->conteudo .= '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar\'">';
        }
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

    function remover() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {
            if (!$_POST) {
                $botoes = array();
                $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
                $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

                $this->conteudo .= $this->carregaArquivo("cidades.remover.inc.php");
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
                $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
            } else {
                $this->bancodedados->query("DELETE FROM tab_cidades WHERE cid_id = '{$_GET["i"]}'");

                $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
                $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

                header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
            }
        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
        }
    }

    function inicializaFiltro($valores = "") {

        $itens = array();
        $itens[] = array("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "(tab_cidades.cid_nome LIKE '%[valor]%')");

        $select_estados = array();
        $estados = $this->buscaEstados();
        foreach ($estados as $id => $nome) {
            $select_estados[] = array("id" => $id, "nome" => $nome);
        }
        $itens[] = array("tipo" => "select", "nome" => "estado", "descricao" => "Estado", "valor" => $select_estados, "where" => "(tab_cidades.est_id = [valor])");

        $filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
        return $filtro;
    }

    function geraPaginacao($filtro, $total, $pagina) {

        return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
    }

    function buscaItens($filtro, $pagina) {

        $limit_inicio = ($pagina - 1) * PAG_ITENS;
        $limit_fim = PAG_ITENS;
        $query = "
            SELECT SQL_CALC_FOUND_ROWS
                tab_cidades.cid_id AS id,
                tab_cidades.cid_nome AS nome,
                tab_estados.est_nome AS estado,
                tab_cidades.cid_ibge AS ibge,
                tab_cidades.cid_tom AS tom,
                tab_cidades.cid_cep_geral AS cep_geral,
                tab_clientes_origens.clo_nome AS origem
            FROM
                tab_cidades
                INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
                LEFT JOIN tab_clientes_origens ON (tab_cidades.clo_id = tab_clientes_origens.clo_id)
            WHERE
                $filtro->parametros_sql
            GROUP BY
                tab_cidades.cid_id
            ORDER BY
                tab_cidades.cid_nome
            LIMIT
                $limit_inicio,
                $limit_fim";

        $query = $this->bancodedados->query($query);
        $linhas = $this->bancodedados->linhasTotal($query);
        $itens = array();
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
                $dados["link"] = "?m={$this->dados_modulo["nome"]}&a=ver&i={$dados["id"]}";
            }
            $dados["contador"] = number_format($limit_inicio + count($itens) + 1, 0, ",", ".");
            $itens[] = $dados;
        }

        return array("linhas" => $linhas, "itens" => $itens);
    }

    function geraListagem($itens) {

        $cabecalho = array(
            "contador" => "#",
            "nome" => "Nome",
            "estado" => "Estado",
            "ibge" => "IBGE",
            "tom" => "TOM",
            "cep_geral" => "CEP geral",
            "origem" => "Origem"
        );
        return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, null, null, array("right"));
    }
}

?>
