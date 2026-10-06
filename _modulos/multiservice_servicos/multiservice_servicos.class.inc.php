<?php

require_once ("_global/modulo.class.inc.php");

class multiservice_servicos extends modulo {

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
            if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"])
                $_GET["a"] = "editar";
            $this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
            $this->conteudo = $this->geraTitulo();
            $this->{$this->acao}();
        }
    }

    function buscaDadosItem($id) {

        $query = "
                    SELECT
                        tab_multiservice_servicos.*,
                        tab_multiservice_status.nome AS status_nome,
                        tab_multiservice_players.nome AS player_nome
                    FROM
                        tab_multiservice_servicos
                        LEFT OUTER JOIN tab_multiservice_status ON (tab_multiservice_status.id = tab_multiservice_servicos.status)
                        LEFT OUTER JOIN tab_multiservice_players ON tab_multiservice_players.id = tab_multiservice_servicos.player
                    WHERE
                        tab_multiservice_servicos.id = $id";
        $query = $this->bancodedados->query($query);
        $this->dados_item = $this->bancodedados->resultadoArray($query);
        if (!$this->dados_item) {
            unset($this->dados_item);
        }
    }

    function buscaStatus() {

        $query = "SELECT id, nome FROM tab_multiservice_status ORDER BY nome";
        $query = $this->bancodedados->query($query);
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $status[$dados["id"]] = $dados["nome"];
        }
        return $status;
    }

    function buscaPlayers($player = 0) {

        $query = "SELECT id, nome FROM tab_multiservice_players WHERE status = 1 OR id = $player ORDER BY nome";
        $query = $this->bancodedados->query($query);
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $status[$dados["id"]] = $dados["nome"];
        }
        return $status;
    }

    function adicionar() {

        if (!$_POST) {

            $this->conteudo .= $this->carregaArquivo("multiservice_servicos.adicionar.inc.php");
            $this->conteudo = str_replace('[status]', $this->geraSelect($this->buscaStatus(), null, false, "status", "250px"), $this->conteudo);
            $this->conteudo = str_replace('[player]', $this->geraSelect($this->buscaPlayers(), null, true, "player", "250px"), $this->conteudo);
            // Botões
            $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
            $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
            $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
        } else {

            $_POST["nome"] = trim($_POST["nome"]);
            $_POST["descricao"] = addslashes(trim($_POST["descricao"]));
            $query = "
                    INSERT 
                        INTO tab_multiservice_servicos (nome, descricao ,player, codigo, status) 
                        VALUES ('{$_POST["nome"]}', '{$_POST["descricao"]}', {$_POST["player"]} , '{$_POST['codigo']}', {$_POST["status"]})";
            $this->bancodedados->query($query);

            $id = $this->bancodedados->ultimoIdInserido();

            $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
            $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);

            header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
        }
    }

    function editar() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            if (!$_POST) {
                $botoes = array();
                $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
                if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) {
                    $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\';">';
                }
                $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
                $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

                $this->conteudo .= $this->carregaArquivo("multiservice_servicos.editar.inc.php");
                $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
                $this->conteudo = str_replace("[descricao]", $this->dados_item["descricao"], $this->conteudo);
                $this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
                $this->conteudo = str_replace("[data_criacao]", $this->dados_item["data_criacao"], $this->conteudo);
                $this->conteudo = str_replace("[player]", $this->geraSelect($this->buscaPlayers($this->dados_item["player"]), $this->dados_item["player"], true, "player"), $this->conteudo);
                $this->conteudo = str_replace("[status]", $this->geraSelect($this->buscaStatus(), $this->dados_item["status"], true, "status"), $this->conteudo);
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
                $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
            } else {

                $_POST["nome"] = trim($_POST["nome"]);
                $_POST["descricao"] = addslashes(trim($_POST["descricao"]));

                $query = "
                            UPDATE
                                tab_multiservice_servicos
                            SET
                                nome = '{$_POST["nome"]}', 
                                descricao = '{$_POST["descricao"]}', 
                                codigo = '{$_POST["codigo"]}', 
                                player = {$_POST["player"]} ,
                                status = {$_POST["status"]} 
                            WHERE
                                id = {$_GET["i"]}";
                $this->bancodedados->query($query);
                header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
            }
        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}");
        }
    }

    function ver() {

        $this->buscaDadosItem($_GET["i"]);

        if (!empty($this->dados_item)) {
            $this->conteudo .= $this->carregaArquivo("multiservice_servicos.ver.inc.php");
            $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
            $this->conteudo = str_replace('[descricao]', $this->dados_item["descricao"], $this->conteudo);
            $this->conteudo = str_replace('[player]', $this->dados_item["player_nome"], $this->conteudo);
            $this->conteudo = str_replace('[data_criacao]', $this->dados_item["data_criacao"], $this->conteudo);

            $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
            // Ações
            $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_da" => "Dados Administrativos")), $this->conteudo);
            // Botões
            if ($this->permissoes[$this->dados_modulo["nome"]]["editar_dc"])
                $botoes[] = '<input type="button" name="editar_dc" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\';">';
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}");
        }
    }

    function listar() {
        if (!$_GET["f"] && !$_GET["p"]) {
            $_GET["f"]["status"] = 1; // Ativo
        }
        $filtro = $this->inicializaFiltro($_GET["f"]);
        $pagina = $_GET["p"] ? $_GET["p"] : 1;
        $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#252525"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
        $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"])
            $this->conteudo .= '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar\'">';
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

                $this->conteudo .= $this->carregaArquivo("multiservice_servicos.remover.inc.php");
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
                $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
            } else {

                $this->bancodedados->query("DELETE FROM tab_multiservice_servicos WHERE id = '{$_GET["i"]}'");
                header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
            }
        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
        }
    }

    function inicializaFiltro($valores = "") {
        // Nome
        $itens[] = array("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "(tab_multiservice_servicos.nome LIKE '%[valor]%')");

        // Status
        $status = $this->buscaStatus();
        foreach ($status as $id => $nome) {
            $select_status[] = array("id" => $id, "nome" => $nome);
        }
        $itens[] = array("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_multiservice_servicos.status = [valor])");
        // Players
        $players = $this->buscaPlayers();
        foreach ($players as $id => $nome) {
            $select_players[] = array("id" => $id, "nome" => $nome);
        }
        $itens[] = array("tipo" => "select", "nome" => "player", "descricao" => "Player", "valor" => $select_players, "where" => "(tab_multiservice_servicos.player = [valor])");
        $filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
        return $filtro;
    }

    function geraPaginacao($filtro, $total, $pagina) {

        $paginacao = controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
        return $paginacao;
    }

    function buscaItens($filtro, $pagina) {

        $limit_inicio = ($pagina - 1) * PAG_ITENS;
        $limit_fim = PAG_ITENS;
        $query = "
                    SELECT SQL_CALC_FOUND_ROWS
                        tab_multiservice_servicos.*,
                        tab_multiservice_status.nome AS status_nome,
                        tab_multiservice_players.nome AS player_nome
                    FROM
                        tab_multiservice_servicos
                        LEFT OUTER JOIN tab_multiservice_status ON (tab_multiservice_status.id = tab_multiservice_servicos.status)
                        LEFT OUTER JOIN tab_multiservice_players ON tab_multiservice_players.id = tab_multiservice_servicos.player
                    WHERE
                        $filtro->parametros_sql
                    GROUP BY
                        tab_multiservice_servicos.id
                    ORDER BY
                        tab_multiservice_servicos.nome ASC
                    LIMIT
                    $limit_inicio,
                    $limit_fim";

        $query = $this->bancodedados->query($query);
        $linhas = $this->bancodedados->linhasTotal($query);
        $status = $this->buscaStatus();
        $itens = array();
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
                $dados["link"] = "?m=multiservice_servicos&a=ver&i={$dados["id"]}";
            }
            $dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
            $itens[] = $dados;
        }
        return array("linhas" => $linhas, "itens" => $itens);
    }

    function geraListagem($itens) {
        $cabecalho = array("contador" => "#", "nome" => "Nome", "player_nome" => "Player", "status_nome" => "Status");
        $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, null, null, array("right"));
        return $listagem;
    }
}
