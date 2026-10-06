<?php

require_once("_global/modulo.class.inc.php");

class catalogos_vods extends modulo {

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

    function buscaDadosItem($id) {

        $query = "
            SELECT
                id,
                codigo,
                nome,
                nome_catalogo,
                status
            FROM
                tab_catalogos_vods
            WHERE
                id = $id";

        $query = $this->bancodedados->query($query);
        $this->dados_item = $this->bancodedados->resultadoArray($query);
        if (!$this->dados_item) {
            unset($this->dados_item);
        }
    }

    function adicionar() {

        if (!$_POST) {
            $this->conteudo .= $this->carregaArquivo("catalogos_vods.adicionar.inc.php");

            $status_opcoes = array(0 => "Inativo", 1 => "Ativo");
            $this->conteudo = str_replace("[status]", $this->geraSelect($status_opcoes, null, true, "status", "250px"), $this->conteudo);

            $botoes = array();
            $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
            $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
            $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
        } else {
            $_POST["codigo"] = addslashes(trim($_POST["codigo"]));
            $_POST["nome"] = addslashes(trim($_POST["nome"]));
            $_POST["nome_catalogo"] = addslashes(trim($_POST["nome_catalogo"]));
            $status = ($_POST["status"] !== "" && is_numeric($_POST["status"])) ? intval($_POST["status"]) : 0;

            $query = "
                INSERT INTO
                    tab_catalogos_vods (codigo, nome, nome_catalogo, status)
                VALUES
                    ('{$_POST["codigo"]}', '{$_POST["nome"]}', '{$_POST["nome_catalogo"]}', $status)";

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

                $this->conteudo .= $this->carregaArquivo("catalogos_vods.editar.inc.php");
                $this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
                $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
                $this->conteudo = str_replace("[nome_catalogo]", $this->dados_item["nome_catalogo"], $this->conteudo);

                $status_opcoes = array(0 => "Inativo", 1 => "Ativo");
                $this->conteudo = str_replace("[status]", $this->geraSelect($status_opcoes, $this->dados_item["status"], true, "status", "250px"), $this->conteudo);

                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
                $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
            } else {
                $_POST["codigo"] = addslashes(trim($_POST["codigo"]));
                $_POST["nome"] = addslashes(trim($_POST["nome"]));
                $_POST["nome_catalogo"] = addslashes(trim($_POST["nome_catalogo"]));
                $status = ($_POST["status"] !== "" && is_numeric($_POST["status"])) ? intval($_POST["status"]) : 0;

                $query = "
                    UPDATE
                        tab_catalogos_vods
                    SET
                        codigo = '{$_POST["codigo"]}',
                        nome = '{$_POST["nome"]}',
                        nome_catalogo = '{$_POST["nome_catalogo"]}',
                        status = $status
                    WHERE
                        id = {$_GET["i"]}";

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
            $this->conteudo .= $this->carregaArquivo("catalogos_vods.ver.inc.php");
            $this->conteudo = str_replace("[id]", $this->dados_item["id"], $this->conteudo);
            $this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
            $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
            $this->conteudo = str_replace("[nome_catalogo]", $this->dados_item["nome_catalogo"], $this->conteudo);
            $this->conteudo = str_replace("[status]", $this->dados_item["status"] == 1 ? "Ativo" : "Inativo", $this->conteudo);

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

                $this->conteudo .= $this->carregaArquivo("catalogos_vods.remover.inc.php");
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
                $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
            } else {
                $this->bancodedados->query("DELETE FROM tab_catalogos_vods WHERE id = '{$_GET["i"]}'");

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
        $itens[] = array("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "(tab_catalogos_vods.nome LIKE '%[valor]%')");
        $itens[] = array("tipo" => "input", "nome" => "codigo", "descricao" => "Código", "valor" => "", "where" => "(tab_catalogos_vods.codigo LIKE '%[valor]%')");

        $select_status = array();
        $select_status[] = array("id" => 0, "nome" => "Inativo");
        $select_status[] = array("id" => 1, "nome" => "Ativo");
        $itens[] = array("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_catalogos_vods.status = [valor])");

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
                id,
                codigo,
                nome,
                nome_catalogo,
                CASE WHEN status = 1 THEN 'Ativo' ELSE 'Inativo' END AS status_desc
            FROM
                tab_catalogos_vods
            WHERE
                $filtro->parametros_sql
            ORDER BY
                nome
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
            "codigo" => "Código",
            "nome" => "Nome",
            "nome_catalogo" => "Nome Catálogo",
            "status_desc" => "Status"
        );
        return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, null, null, array("right"));
    }
}

?>
