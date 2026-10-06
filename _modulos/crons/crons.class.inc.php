<?php

require_once ("_global/modulo.class.inc.php");

class crons extends modulo {

    var $bancodedados;
    /**
     * Permissões do módulo
     * @var array
     */
    var $permissoes;
    /**
     * Ação do módulo
     * @var string
     */
    var $acao;
    /**
     * Conteúdo do módulo
     * @var string
     */
    var $conteudo;
    /**
     * Dados do item
     * @var array
     */
    var $dados_item;
    /**
     * Dados do módulo
     * @var array
     */
    var $dados_modulo;
    /**
     * Dados da ação
     * @var array
     */
    var $dados_acao;

    /**
     * Construtor da classe
     * @param bancodedados $bancodedados
     * @param array $permissoes
     */
    function __construct($bancodedados, $permissoes) {

        $this->bancodedados = $bancodedados;
        $this->permissoes = $permissoes;
        
        $ehAdmin = $this->ehAdmin();
        if ($ehAdmin) {
            
            $this->buscaDadosModulo($_GET["m"]);
            if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"])
                $_GET["a"] = "editar";
            $this->acao = $_GET["a"] ? $_GET["a"] : "listar";
            $this->conteudo = $this->geraTitulo();
            $this->{$this->acao}();
        }
    }

    /**
     * Verifica se o usuário é administrador
     * @param int $id
     * @return bool
     */
    function ehAdmin($id = null) {

		$user = $id? $id : $_SESSION["usuario"];

		$query = "SELECT usu_administrador FROM tab_usuarios WHERE usu_id = $user";
		$query = $this->bancodedados->query($query);
		$ehAdmin = $this->bancodedados->resultadoArray($query);

		return $ehAdmin['usu_administrador']== 0 ? false : true;
	}

    /**
     * Busca os dados do item
     * @param int $id
     */
    function buscaDadosItem($id) {

        $query = "
                    SELECT
                        cro_id AS id,
                        cro_classe,
                        cro_acao,
                        cro_dias_semana,
                        cro_dias_mes,
                        cro_intervalo_minutos,
                        cro_ultima_execucao_real,
                        cro_ultima_execucao_inicio,
                        cro_ultima_execucao_termino,
                        cro_tempo_real,
                        cro_em_execucao,
                        cro_ativo,
                        IF(cro_tempo_real = 's', 'Sim', 'Não') AS cro_tempo_real_nome,
                        IF(cro_em_execucao = 1, 'Sim', 'Não') AS cro_em_execucao_nome,
                        IF(cro_ativo = 1, 'Sim', 'Não') AS cro_ativo_nome
                    FROM
                        tab_cron
                    WHERE
                        cro_id = $id";

        $query = $this->bancodedados->query($query);
        $this->dados_item = $this->bancodedados->resultadoArray($query);
        if (!$this->dados_item) {
            unset($this->dados_item);
        }
    }

    function buscaSimNao() {
        return array(
            1 => "Sim",
            0 => "Não"
        );
    }

    function buscaTempoReal() {
        return array(
            "s" => "Sim",
            "n" => "Não"
        );
    }

    /**
     * Formata a data e hora para o formato local
     * @param string $datahora
     * @return string
     */
    function formataDatetimeLocal($datahora) {
        if (!$datahora || $datahora == "0000-00-00 00:00:00") {
            return "";
        }
        return date("Y-m-d\\TH:i", strtotime($datahora));
    }

    /**
     * Formata a data e hora para o formato banco
     * @param string $datahora
     * @return string
     */
    function formataDatetimeBanco($datahora) {
        $datahora = trim($datahora);
        if ($datahora == "") {
            return "NULL";
        }

        $datahora = str_replace("T", " ", $datahora);
        if (strlen($datahora) == 16) {
            $datahora .= ":00";
        }

        return "'" . addslashes($datahora) . "'";
    }

    function adicionar() {

        if (!$_POST) {

            $this->conteudo .= $this->carregaArquivo("crons.adicionar.inc.php");
            $this->conteudo = str_replace('[cro_tempo_real]', $this->geraSelect($this->buscaTempoReal(), "n", true, "cro_tempo_real", "250px"), $this->conteudo);
            $this->conteudo = str_replace('[cro_em_execucao]', $this->geraSelect($this->buscaSimNao(), 0, true, "cro_em_execucao", "250px"), $this->conteudo);
            $this->conteudo = str_replace('[cro_ativo]', $this->geraSelect($this->buscaSimNao(), 1, true, "cro_ativo", "250px"), $this->conteudo);

            $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
            $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
            $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
        } else {

            $_POST["cro_classe"] = addslashes(trim($_POST["cro_classe"]));
            $_POST["cro_acao"] = addslashes(trim($_POST["cro_acao"]));
            $_POST["cro_dias_semana"] = addslashes(trim($_POST["cro_dias_semana"]));
            $_POST["cro_dias_mes"] = addslashes(trim($_POST["cro_dias_mes"]));

            $cro_intervalo_minutos = ($_POST["cro_intervalo_minutos"] !== "" && is_numeric($_POST["cro_intervalo_minutos"])) ? intval($_POST["cro_intervalo_minutos"]) : "NULL";
            $cro_dias_semana = $_POST["cro_dias_semana"] !== "" ? "'{$_POST["cro_dias_semana"]}'" : "NULL";
            $cro_dias_mes = $_POST["cro_dias_mes"] !== "" ? "'{$_POST["cro_dias_mes"]}'" : "NULL";
            $cro_tempo_real = ($_POST["cro_tempo_real"] == "s") ? "s" : "n";
            $cro_em_execucao = ($_POST["cro_em_execucao"] == 1) ? 1 : 0;
            $cro_ativo = ($_POST["cro_ativo"] == 1) ? 1 : 0;

            $query = "
                    INSERT
                        INTO tab_cron (cro_classe, cro_acao, cro_dias_semana, cro_dias_mes, cro_intervalo_minutos, cro_tempo_real, cro_em_execucao, cro_ativo)
                        VALUES ('{$_POST["cro_classe"]}', '{$_POST["cro_acao"]}', $cro_dias_semana, $cro_dias_mes, $cro_intervalo_minutos, '$cro_tempo_real', $cro_em_execucao, $cro_ativo)";

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

                $this->conteudo .= $this->carregaArquivo("crons.editar.inc.php");
                $this->conteudo = str_replace("[cro_classe]", $this->dados_item["cro_classe"], $this->conteudo);
                $this->conteudo = str_replace("[cro_acao]", $this->dados_item["cro_acao"], $this->conteudo);
                $this->conteudo = str_replace("[cro_dias_semana]", $this->dados_item["cro_dias_semana"], $this->conteudo);
                $this->conteudo = str_replace("[cro_dias_mes]", $this->dados_item["cro_dias_mes"], $this->conteudo);
                $this->conteudo = str_replace("[cro_intervalo_minutos]", $this->dados_item["cro_intervalo_minutos"], $this->conteudo);
                $this->conteudo = str_replace("[cro_ultima_execucao_real]", $this->formataDatetimeLocal($this->dados_item["cro_ultima_execucao_real"]), $this->conteudo);
                $this->conteudo = str_replace("[cro_ultima_execucao_inicio]", $this->formataDatetimeLocal($this->dados_item["cro_ultima_execucao_inicio"]), $this->conteudo);
                $this->conteudo = str_replace("[cro_ultima_execucao_termino]", $this->formataDatetimeLocal($this->dados_item["cro_ultima_execucao_termino"]), $this->conteudo);
                $this->conteudo = str_replace("[cro_tempo_real]", $this->geraSelect($this->buscaTempoReal(), $this->dados_item["cro_tempo_real"], true, "cro_tempo_real", "250px"), $this->conteudo);
                $this->conteudo = str_replace("[cro_em_execucao]", $this->geraSelect($this->buscaSimNao(), $this->dados_item["cro_em_execucao"], true, "cro_em_execucao", "250px"), $this->conteudo);
                $this->conteudo = str_replace("[cro_ativo]", $this->geraSelect($this->buscaSimNao(), $this->dados_item["cro_ativo"], true, "cro_ativo", "250px"), $this->conteudo);
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
                $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
            } else {

                $_POST["cro_classe"] = addslashes(trim($_POST["cro_classe"]));
                $_POST["cro_acao"] = addslashes(trim($_POST["cro_acao"]));
                $_POST["cro_dias_semana"] = addslashes(trim($_POST["cro_dias_semana"]));
                $_POST["cro_dias_mes"] = addslashes(trim($_POST["cro_dias_mes"]));

                $cro_intervalo_minutos = ($_POST["cro_intervalo_minutos"] !== "" && is_numeric($_POST["cro_intervalo_minutos"])) ? intval($_POST["cro_intervalo_minutos"]) : "NULL";
                $cro_dias_semana = $_POST["cro_dias_semana"] !== "" ? "'{$_POST["cro_dias_semana"]}'" : "NULL";
                $cro_dias_mes = $_POST["cro_dias_mes"] !== "" ? "'{$_POST["cro_dias_mes"]}'" : "NULL";
                $cro_tempo_real = ($_POST["cro_tempo_real"] == "s") ? "s" : "n";
                $cro_em_execucao = ($_POST["cro_em_execucao"] == 1) ? 1 : 0;
                $cro_ativo = ($_POST["cro_ativo"] == 1) ? 1 : 0;
                $cro_ultima_execucao_real = $this->formataDatetimeBanco($_POST["cro_ultima_execucao_real"]);
                $cro_ultima_execucao_inicio = $this->formataDatetimeBanco($_POST["cro_ultima_execucao_inicio"]);
                $cro_ultima_execucao_termino = $this->formataDatetimeBanco($_POST["cro_ultima_execucao_termino"]);

                $query = "
                            UPDATE
                                tab_cron
                            SET
                                cro_classe = '{$_POST["cro_classe"]}',
                                cro_acao = '{$_POST["cro_acao"]}',
                                cro_dias_semana = $cro_dias_semana,
                                cro_dias_mes = $cro_dias_mes,
                                cro_intervalo_minutos = $cro_intervalo_minutos,
                                cro_ultima_execucao_real = $cro_ultima_execucao_real,
                                cro_ultima_execucao_inicio = $cro_ultima_execucao_inicio,
                                cro_ultima_execucao_termino = $cro_ultima_execucao_termino,
                                cro_tempo_real = '$cro_tempo_real',
                                cro_em_execucao = $cro_em_execucao,
                                cro_ativo = $cro_ativo
                            WHERE
                                cro_id = {$_GET["i"]}";
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
            $this->conteudo .= $this->carregaArquivo("crons.ver.inc.php");
            $this->conteudo = str_replace('[cro_id]', $this->dados_item["id"], $this->conteudo);
            $this->conteudo = str_replace('[cro_classe]', $this->dados_item["cro_classe"], $this->conteudo);
            $this->conteudo = str_replace('[cro_acao]', $this->dados_item["cro_acao"], $this->conteudo);
            $this->conteudo = str_replace('[cro_dias_semana]', $this->dados_item["cro_dias_semana"], $this->conteudo);
            $this->conteudo = str_replace('[cro_dias_mes]', $this->dados_item["cro_dias_mes"], $this->conteudo);
            $this->conteudo = str_replace('[cro_intervalo_minutos]', $this->dados_item["cro_intervalo_minutos"], $this->conteudo);
            $this->conteudo = str_replace('[cro_ultima_execucao_real]', $this->dados_item["cro_ultima_execucao_real"], $this->conteudo);
            $this->conteudo = str_replace('[cro_ultima_execucao_inicio]', $this->dados_item["cro_ultima_execucao_inicio"], $this->conteudo);
            $this->conteudo = str_replace('[cro_ultima_execucao_termino]', $this->dados_item["cro_ultima_execucao_termino"], $this->conteudo);
            $this->conteudo = str_replace('[cro_tempo_real]', $this->dados_item["cro_tempo_real_nome"], $this->conteudo);
            $this->conteudo = str_replace('[cro_em_execucao]', $this->dados_item["cro_em_execucao_nome"], $this->conteudo);
            $this->conteudo = str_replace('[cro_ativo]', $this->dados_item["cro_ativo_nome"], $this->conteudo);

            $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_da" => "Dados Administrativos")), $this->conteudo);

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
            $_GET["f"]["cro_ativo"] = 1;
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

                $this->conteudo .= $this->carregaArquivo("crons.remover.inc.php");
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
                $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
            } else {

                $this->bancodedados->query("DELETE FROM tab_cron WHERE cro_id = '{$_GET["i"]}'");
                header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
            }
        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
        }
    }

    /**
     * Inicializa o filtro
     * @param string $valores
     * @return filtro
     */
    function inicializaFiltro($valores = "") {

        $itens[] = array("tipo" => "input", "nome" => "cro_classe", "descricao" => "Classe", "valor" => "", "where" => "(tab_cron.cro_classe LIKE '%[valor]%')");
        $itens[] = array("tipo" => "input", "nome" => "cro_acao", "descricao" => "Ação", "valor" => "", "where" => "(tab_cron.cro_acao LIKE '%[valor]%')");

        $select_ativo = array();
        $select_execucao = array();
        $select_tempo_real = array();
        $sim_nao = $this->buscaSimNao();
        foreach ($sim_nao as $id => $nome) {
            $select_ativo[] = array("id" => $id, "nome" => $nome);
            $select_execucao[] = array("id" => $id, "nome" => $nome);
        }

        $itens[] = array("tipo" => "select", "nome" => "cro_ativo", "descricao" => "Ativo", "valor" => $select_ativo, "where" => "(tab_cron.cro_ativo = [valor])");
        $itens[] = array("tipo" => "select", "nome" => "cro_em_execucao", "descricao" => "Em execução", "valor" => $select_execucao, "where" => "(tab_cron.cro_em_execucao = [valor])");

        $tempo_real = $this->buscaTempoReal();
        foreach ($tempo_real as $id => $nome) {
            $select_tempo_real[] = array("id" => $id, "nome" => $nome);
        }
        $itens[] = array("tipo" => "select", "nome" => "cro_tempo_real", "descricao" => "Tempo real", "valor" => $select_tempo_real, "where" => "(tab_cron.cro_tempo_real = '[valor]')");

        $filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
        return $filtro;
    }

    /**
     * Gera a paginação
     * @param filtro $filtro
     * @param int $total
     * @param int $pagina
     * @return paginacao
     */
    function geraPaginacao($filtro, $total, $pagina) {

        $paginacao = controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
        return $paginacao;
    }

    /**
     * Busca os itens
     * @param filtro $filtro
     * @param int $pagina
     * @return array
     */
    function buscaItens($filtro, $pagina) {

        $limit_inicio = ($pagina - 1) * PAG_ITENS;
        $limit_fim = PAG_ITENS;
        $query = "
                    SELECT SQL_CALC_FOUND_ROWS
                        cro_id AS id,
                        cro_classe,
                        cro_acao,
                        cro_intervalo_minutos,
                        cro_tempo_real,
                        cro_em_execucao,
                        cro_ativo,
                        IF(cro_tempo_real = 's', 'Sim', 'Não') AS cro_tempo_real_nome,
                        IF(cro_em_execucao = 1, 'Sim', 'Não') AS cro_em_execucao_nome,
                        IF(cro_ativo = 1, 'Sim', 'Não') AS cro_ativo_nome
                    FROM
                        tab_cron
                    WHERE
                        $filtro->parametros_sql
                    GROUP BY
                        cro_id
                    ORDER BY
                        cro_id DESC
                    LIMIT
                    $limit_inicio,
                    $limit_fim";

        $query = $this->bancodedados->query($query);
        $linhas = $this->bancodedados->linhasTotal($query);
        $itens = array();
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
                $dados["link"] = "?m=crons&a=ver&i={$dados["id"]}";
            }
            $dados["contador"] = number_format($limit_inicio + count($itens) + 1, 0, ",", ".");
            $itens[] = $dados;
        }
        return array("linhas" => $linhas, "itens" => $itens);
    }

    /**
     * Gera a listagem
     * @param array $itens
     * @return listagem
     */
    function geraListagem($itens) {
        $cabecalho = array("contador" => "#", "cro_classe" => "Classe", "cro_acao" => "Ação", "cro_intervalo_minutos" => "Intervalo (min)", "cro_tempo_real_nome" => "Tempo real", "cro_em_execucao_nome" => "Em execução", "cro_ativo_nome" => "Ativo");
        $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, null, null, array("right"));
        return $listagem;
    }
}
