<?

class clientes {

	/**
	 * @var bancodedados
	 */
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

            if ((strpos($_GET["a"], "editar") !== false || strpos($_GET["a"], "ver") !== false) && $_GET["i"] && in_array(strtolower($_GET["i"][0]), array("c", "f", "m", "t", "a", "h"))) {
                header("Location: ./?m=clientes&a=ver_" . (strtolower($_GET["i"][0]) == "h" ? "ch" : "d" . strtolower($_GET["i"][0])) . "&i=" . substr($_GET["i"], 1));
                die();
            }

            if (servidor::getGet("i") && preg_match("/^(ver|editar)/", servidor::getGet("a"))) {
                $_GET["i"] = strtoupper(preg_replace("/[^0-9A-Z]/", "", $_GET["i"]));
                if ($_GET["i"] && !$this->existeId($_GET["i"])) {
                    if ($this->validaCPF($_GET["i"])) {
                        $id = $this->buscaIdPorCPFCNPJ($this->formataCPF($_GET["i"]));
                        header("Location: ./?m=clientes&a={$_GET["a"]}&i=$id");
                        die();
                    } elseif ($this->validaCNPJ($_GET["i"])) {
                        $id = $this->buscaIdPorCPFCNPJ($this->formataCNPJ($_GET["i"]));
                        header("Location: ./?m=clientes&a={$_GET["a"]}&i=$id");
                        die();
                    }
                }
            }

            $this->buscaDadosModulo($_GET["m"]);

            if ($_GET["i"] && in_array($_GET["a"], array("ver_dc", "editar_dc", "ver_df", "editar_df", "ver_dm", "editar_dm", "ver_dt", "editar_dt", "ver_da", "editar_da"))) {
                $status_id = $this->buscaClienteStatus($_GET["i"]);
                if (in_array($_GET["a"], array("ver_dc", "editar_dc"))) {
                    $_GET["a"] = (($this->permissoes["clientes"]["editar_dc_plus"] || ($status_id != 2 && $this->permissoes["clientes"]["editar_dc"]))) ? "editar_dc" : "ver_dc";
                } elseif (in_array($_GET["a"], array("ver_df", "editar_df"))) {
                    $_GET["a"] = (($this->permissoes["clientes"]["editar_df_plus"] || ($status_id != 2 && $this->permissoes["clientes"]["editar_df"]))) ? "editar_df" : "ver_df";
                } elseif (in_array($_GET["a"], array("ver_dm", "editar_dm"))) {
                    $_GET["a"] = (($this->permissoes["clientes"]["editar_dm_plus"] || ($status_id != 2 && $this->permissoes["clientes"]["editar_dm"]))) ? "editar_dm" : "ver_dm";
                } elseif (in_array($_GET["a"], array("ver_dt", "editar_dt"))) {
                    $_GET["a"] = (($this->permissoes["clientes"]["editar_dt_plus"] || ($status_id != 2 && $this->permissoes["clientes"]["editar_dt"]))) ? "editar_dt" : "ver_dt";
                } elseif (in_array($_GET["a"], array("ver_da", "editar_da"))) {
                    $_GET["a"] = (($this->permissoes["clientes"]["editar_da_plus"] || ($status_id != 2 && $this->permissoes["clientes"]["editar_da"]))) ? "editar_da" : "ver_da";
                }
            }
        
            if ($_GET["a"] == "ver_ch" && $this->permissoes[$this->dados_modulo["nome"]]["editar_ch"]) $_GET["a"] = "editar_ch";

            $this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
            $this->conteudo = $this->geraTitulo();
            $this->{$this->acao}();

            if ($this->dados_item) {

                $this->conteudo = str_replace("[titulo_origem]", " ({$this->dados_item["origem_nome"]}) [{$this->dados_item["id"]}]", $this->conteudo);

                if ($this->dados_item && $this->dados_item["negativado"]) {
                    $this->conteudo = str_replace("[negativacao]", "<table border=\"0\" cellspacing=\"1\" cellpadding=\"3\" width=\"100%\"><tr class=\"txtBranco11\"><td style=\"background:#DD0000;border-bottom: 1px solid #FFFFFF;border-top: 1px solid #FFFFFF;padding-left:5px\"><b>Atenção: Cadastro negativado!</b></td></tr></table>", $this->conteudo);
                }

            } else {
                $this->conteudo = str_replace("[titulo_origem]", "", $this->conteudo);
            }

            $this->conteudo = str_replace("[negativacao]", "", $this->conteudo);

        }

    }

    function ehAdmin($id = null) {

        $user = $id ? $id : $_SESSION["usuario"];
        $query = "SELECT usu_administrador FROM tab_usuarios WHERE usu_id = $user";
        $query = $this->bancodedados->query($query);
        $ehAdmin = $this->bancodedados->resultadoArray($query);

        return $ehAdmin["usu_administrador"] == 0 ? false : true;
    }

    function existeId($id) {

        $query = $this->bancodedados->query("
            SELECT
                COUNT(*)
            FROM
                tab_clientes
            WHERE
                cli_id = $id");

        return $this->bancodedados->resultado($query) > 0;

    }

    function buscaIdPorCPFCNPJ($cpf_cnpj) {

        $query = $this->bancodedados->query("
            SELECT
                cli_id
            FROM
                tab_clientes
            WHERE
                cli_cpf_cnpj = '{$cpf_cnpj}'".($_SESSION["origem"] ? " AND
				clo_id = {$_SESSION["origem"]}" : "")."
			LIMIT 0, 1");

        return $this->bancodedados->resultado($query);

    }

    function formataCPF($cpf) {

        $cpf = sprintf("%011s", preg_replace("/[^0-9]/", "", $cpf));

        return substr($cpf, 0, 3).".".substr($cpf, 3, 3).".".substr($cpf, 6, 3)."-".substr($cpf, 9, 2);

    }

    function limpaCNPJAlfanumerico($cnpj) {

        $cnpj = strtoupper(preg_replace("/[^0-9A-Z]/", "", $cnpj));
        $base = "";
        $dv = "";
        $len = strlen($cnpj);

        for ($i = 0; $i < $len && strlen($base) < 12; $i++) {
            if (preg_match("/^[0-9A-Z]$/", $cnpj[$i])) {
                $base .= $cnpj[$i];
            }
        }

        for (; $i < $len && strlen($dv) < 2; $i++) {
            if (preg_match("/^[0-9]$/", $cnpj[$i])) {
                $dv .= $cnpj[$i];
            }
        }

        return $base . $dv;
    }

    function valorCNPJMod11($caractere) {
        return ord($caractere) - 48;
    }

    function formataCNPJ($cnpj) {

        $cnpj = $this->limpaCNPJAlfanumerico($cnpj);

        if ($cnpj == "") {
            return "";
        }

        return substr($cnpj, 0, 2) . "." .
            substr($cnpj, 2, 3) . "." .
            substr($cnpj, 5, 3) . "/" .
            substr($cnpj, 8, 4) . "-" .
            substr($cnpj, 12, 2);
    }

    function validaCPF($cpf) {

        $cpf = sprintf("%011s", preg_replace("/[^0-9]/", "", $cpf));

        if (strlen($cpf) != 11 || preg_match("/^([0-9])\1{10}$/", $cpf)) {
            return false;
        }

        $dv1 = $dv2 = 0;

        for ($i = 0; $i < 9; $i++) {
            $dv1 += $cpf[$i] * (10 - $i);
            $dv2 += $cpf[$i] * (11 - $i);
        }

        $dv1 = $dv1 % 11 > 1 ? 11 - ($dv1 % 11) : 0;
        $dv2 = ($dv2 + ($dv1 * 2)) % 11 > 1 ? 11 - (($dv2 + ($dv1 * 2)) % 11) : 0;

        return $cpf[9] == $dv1 && $cpf[10] == $dv2;

    }

    function validaCNPJ($cnpj) {

        $cnpj = $this->limpaCNPJAlfanumerico($cnpj);

        if (strlen($cnpj) != 14 || preg_match("/^(.)\1{13}$/", $cnpj)) {
            return false;
        }

        $mult1 = array(5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);
        $mult2 = array(6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);

        $d1 = 0;
        for ($i = 0; $i < 12; $i++) {
            $d1 += $this->valorCNPJMod11($cnpj[$i]) * $mult1[$i];
        }

        $r1 = $d1 % 11;
        $dv1 = $r1 > 1 ? 11 - $r1 : 0;

        if ((int) $cnpj[12] !== $dv1) {
            return false;
        }

        $d2 = 0;
        for ($i = 0; $i < 13; $i++) {
            $d2 += $this->valorCNPJMod11($cnpj[$i]) * $mult2[$i];
        }

        $r2 = $d2 % 11;
        $dv2 = $r2 > 1 ? 11 - $r2 : 0;

        return (int) $cnpj[13] === $dv2;
    }

    function geraTitulo() {

        $titulo = '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
        $titulo .= '<tr align="center" class="txtBranco11">';
        $titulo .= '<td bgcolor="#505050"><b>';
        $titulo .= $this->dados_modulo["descricao"] . "[titulo_origem]";
        $titulo .= '</b></td></tr></table>[negativacao]';

        return $titulo;

    }

    function carregaArquivo($arquivo) {

		if (file_exists(dirname(__FILE__) . "/" . $arquivo)) {
			$arquivo = dirname(__FILE__) . "/" . $arquivo;
		}
		
        $fp = fopen($arquivo, "r");
        $retorno = fread($fp, filesize($arquivo));
		
        fclose($fp);

        return $retorno;

    }

    function buscaDadosModulo($nome) {

        $query = $this->bancodedados->query("
			SELECT 
				mod_id AS id, 
				mod_nome AS nome, 
				mod_descricao AS descricao 
			FROM 
				tab_modulos 
			WHERE 
				mod_nome = '$nome'");
        
        $this->dados_modulo = $this->bancodedados->resultadoArray($query);

    }

    function buscaDadosAcao($nome) {

    	$query = $this->bancodedados->query("
			SELECT 
				aco_id AS id, 
				aco_nome AS nome, 
				aco_descricao AS descricao, 
				act_id AS tipo 
			FROM 
				tab_acoes 
			WHERE 
				aco_nome = '$nome' AND
				mod_id = '{$this->dados_modulo["id"]}'");
        
        if (($this->dados_acao = $this->bancodedados->resultadoArray($query)) != false) {
        	$this->acao = $this->dados_acao["nome"];
        }

    }

    function buscaDadosCondominio($condominio_id) {

        $query = $this->bancodedados->query("
            SELECT
                con_id AS id,
                con_nome AS nome,
                LPAD(cep_id, 8, '0') AS cep,
                con_numero AS numero,
                cos_id AS status
            FROM
                tab_condominios
            WHERE
                con_id = $condominio_id");

        return $this->bancodedados->resultadoArray($query);

    }

    function buscaDadosUsuario($usuario_id) {

        $query = $this->bancodedados->query("
            SELECT
                usu_id AS id,
                usu_nome AS nome
            FROM
                tab_usuarios
            WHERE
                usu_id = $usuario_id");

        return $this->bancodedados->resultadoArray($query);

    }

    function buscaDadosResponsabilidade($id) {

        $query = $this->bancodedados->query("
            SELECT
				crt_parametros
            FROM
                tab_clientes_responsabilidades_categorias
            WHERE
                crt_id = $id");
        
		if (($parametros = $this->bancodedados->resultado($query)) != false) {
			
			$dados = array();
        	
            $dados["categoria"] = $id;
			$dados["parametros"] = unserialize($parametros);
            $dados["termino"] = date("Y-m-d H:i:s", strtotime("+ {$dados["parametros"]["prazo"]} days"));
            
            return $dados;
            
        }

    }

    function buscaDadosBloco($bloco_id) {

        $query = $this->bancodedados->query("
            SELECT
                cob_id AS id,
                cob_nome AS nome,
                con_id AS condominio,
                LPAD(cep_id, 8, '0') AS cep,
                cob_numero AS numero
            FROM
                tab_condominios_blocos
            WHERE
                cob_id = $bloco_id");

        return $this->bancodedados->resultadoArray($query);

    }

    function buscaConsultores() {
        
        $query = "SELECT * FROM tab_consultor WHERE status = 1 ORDER BY nome";
        $query = $this->bancodedados->query($query);
        $consultores = array();
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $consultores[$dados["id"]] = $dados["nome"];
        }

        return $consultores;
    }

    function buscaDadosItem($id) {

        $this->dados_item = array();

        $query = $this->bancodedados->query("
            SELECT
                tab_clientes.cli_id AS id,
                tab_clientes.cli_nome AS nome,
                tab_clientes.cli_nome_fantasia AS nome_fantasia,
                DATE_FORMAT(tab_clientes.cli_data_cadastro, '%d/%m/%Y') AS data_cadastro,
                tab_clientes.cli_ccd_vencimento AS cobranca_vencimento,
                tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
                tab_clientes.cli_inscricao_estadual AS inscricao_estadual,
                DATE_FORMAT(tab_clientes.cli_nascimento, '%d/%m/%Y') AS nascimento,
                tab_clientes.cob_id AS bloco,
                tab_clientes.con_id AS condominio,
                LPAD(tab_clientes.cep_id, 8, '0') AS cep,
                tab_clientes.cli_numero AS numero,
                tab_clientes.cli_complemento AS complemento,
                tab_clientes.cli_ponto_referencia AS ponto_referencia,
                tab_clientes.cli_contato AS contato,
                tab_clientes.cli_copia_cobranca AS copia_cobranca_email,
                tab_clientes.cli_comentarios AS comentarios,
                tab_clientes.cli_pai AS condominial_id,
                tab_clientes.cli_ccd_debito_operacao AS cobranca_debito_operacao,
                tab_clientes.cli_ccd_debito_agencia AS cobranca_debito_agencia,
                tab_clientes.cli_ccd_debito_conta AS cobranca_debito_conta,
                tab_clientes.cli_ccd_debito_codigo AS cobranca_debito_codigo,
                tab_clientes.cli_bloqueio_dias AS bloqueio_dias,
                tab_cobrancas_formatos.cof_id AS cobranca_formato_id,
                tab_cobrancas_formatos.cof_nome AS cobranca_formato_nome,
                tab_cobrancas_formatos.cft_id AS cobranca_formato_tipo_id,
                tab_clientes_indicacoes.cld_id AS indicacao_id,
                tab_clientes_indicacoes.cld_nome AS indicacao_nome,
                tab_clientes_operadoras_origens.coo_id AS operadora_origem_id,
                tab_clientes_operadoras_origens.coo_nome AS operadora_origem_nome,
                tab_clientes_situacoes.clu_id AS situacao_id,
                tab_clientes_situacoes.clu_nome AS situacao_nome,
                tab_clientes_origens.clo_id AS origem_id,
                tab_clientes_origens.clo_nome AS origem_nome,
                tab_clientes_tipos.clt_id AS tipo_id,
                tab_clientes_tipos.clt_nome AS tipo_nome,
                tab_clientes_status.cls_id AS status_id,
                tab_clientes_status.cls_nome AS status_nome,
                tab_usuarios.usu_id AS responsavel_id,
                tab_usuarios.usu_nome AS responsavel_nome,
                tab_consultor.id AS consultor_id,
                tab_consultor.nome AS consultor_nome
            FROM
                tab_clientes
                LEFT OUTER JOIN tab_usuarios ON (tab_clientes.usu_id = tab_usuarios.usu_id)
                LEFT OUTER JOIN tab_clientes_tipos ON (tab_clientes.clt_id = tab_clientes_tipos.clt_id)
                LEFT OUTER JOIN tab_clientes_situacoes ON (tab_clientes.clu_id = tab_clientes_situacoes.clu_id)
                LEFT OUTER JOIN tab_clientes_indicacoes ON (tab_clientes.cld_id = tab_clientes_indicacoes.cld_id)
                LEFT OUTER JOIN tab_clientes_operadoras_origens ON (tab_clientes.coo_id = tab_clientes_operadoras_origens.coo_id)
                INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
                INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
                LEFT OUTER JOIN tab_cobrancas_formatos ON (tab_clientes.cof_id = tab_cobrancas_formatos.cof_id)
                LEFT OUTER JOIN tab_consultor ON tab_clientes.consultor = tab_consultor.id
            WHERE
                tab_clientes.cli_id = '$id' AND
                tab_clientes.cli_data_cadastro IS NOT NULL".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : ""));
        
        $dados = $this->bancodedados->resultadoArray($query);

        if ($dados && ($this->permissoes["clientes"]["buscar_plus"] || $this->permissoes["clientes"]["buscar_plus_plus"] || $dados["responsavel_id"] == $_SESSION["usuario"] || $dados["status_id"] != 2 || $this->possuiRetornoAberto($id))) {

            $clientes_negativacoes = controle::instanciaObjeto("clientes_negativacoes", $this->bancodedados, $this->permissoes, false);
            $vencimentos = $this->buscaCobrancaVencimentos($dados["origem_id"], $dados["cobranca_vencimento"]);
            $telefones = $this->buscaClientesTelefones($id);

            $this->dados_item["id"] = $dados["id"];
            $this->dados_item["nome"] = $dados["nome"];
            $this->dados_item["nome_fantasia"] = $dados["nome_fantasia"];
            $this->dados_item["data_cadastro"] = $dados["data_cadastro"];
            $this->dados_item["cobranca_vencimento_id"] = $dados["cobranca_vencimento"];
            $this->dados_item["cobranca_vencimento_nome"] = $vencimentos[$dados["cobranca_vencimento"]];
            $this->dados_item["cobranca_formato_id"] = $dados["cobranca_formato_id"];
            $this->dados_item["cobranca_formato_nome"] = $dados["cobranca_formato_nome"];
            $this->dados_item["cobranca_formato_tipo_id"] = $dados["cobranca_formato_tipo_id"];
            $this->dados_item["cobranca_debito_operacao"] = $dados["cobranca_debito_operacao"];
            $this->dados_item["cobranca_debito_agencia"] = $dados["cobranca_debito_agencia"];
            $this->dados_item["cobranca_debito_conta"] = $dados["cobranca_debito_conta"];
            $this->dados_item["copia_cobranca_email"] = $dados["copia_cobranca_email"];
            $this->dados_item["cobranca_debito_codigo"] = $dados["cobranca_debito_codigo"];
            $this->dados_item["bloqueio_dias"] = $dados["bloqueio_dias"];
            $this->dados_item["cpf_cnpj"] = $dados["cpf_cnpj"];
            $this->dados_item["inscricao_estadual"] = $dados["inscricao_estadual"] == -1 ? "" : $dados["inscricao_estadual"];
            $this->dados_item["isento_inscricao_estadual"] = $dados["inscricao_estadual"] == -1 ? 1 : 0;
            $this->dados_item["nascimento"] = $dados["nascimento"];
            $this->dados_item["numero"] = $dados["numero"];
            $this->dados_item["consultor_id"] = $dados["consultor_id"];
            $this->dados_item["consultor_nome"] = $dados["consultor_nome"];


            if ($dados["complemento"]) {
                $this->dados_item["complemento_tipo_id"] = $dados["complemento"][0];
                $this->dados_item["complemento_tipo_nome"] = $this->buscaComplementoTipos($this->dados_item["complemento_tipo_id"], true);
                $this->dados_item["complemento_valor"] = substr($dados["complemento"], 1, strlen($dados["complemento"]));
                if ($this->dados_item["complemento_tipo_id"] != "o") {
                    $this->dados_item["complemento"] = $this->dados_item["complemento_tipo_nome"] . " " . $this->dados_item["complemento_valor"];
                } else {
                    $this->dados_item["complemento"] = $this->dados_item["complemento_valor"];
                }
            }

            $this->dados_item["ponto_referencia"] = $dados["ponto_referencia"];
            $this->dados_item["telefone"] = $telefones["tel_com_ramal"];
            $this->dados_item["telefone_sem_ramal"] = $telefones["tel_sem_ramal"];
            $this->dados_item["email"] = $this->buscaClientesEmails($id);
            $this->dados_item["contato"] = $dados["contato"];
            $this->dados_item["comentarios"] = $dados["comentarios"];
            $this->dados_item["indicacao_id"] = $dados["indicacao_id"];
            $this->dados_item["indicacao_nome"] = $dados["indicacao_nome"];
            $this->dados_item["operadora_origem_id"] = $dados["operadora_origem_id"];
            $this->dados_item["operadora_origem_nome"] = $dados["operadora_origem_nome"];
            $this->dados_item["situacao_id"] = $dados["situacao_id"];
            $this->dados_item["situacao_nome"] = $dados["situacao_nome"];
            $this->dados_item["origem_id"] = $dados["origem_id"];
            $this->dados_item["origem_nome"] = $dados["origem_nome"];
            $this->dados_item["tipo_id"] = $dados["tipo_id"];
            $this->dados_item["tipo_nome"] = $dados["tipo_nome"];
            $this->dados_item["responsavel_id"] = $dados["responsavel_id"];
            $this->dados_item["responsavel_nome"] = $dados["responsavel_nome"];
            $this->dados_item["condominial_id"] = $dados["condominial_id"];
            $this->dados_item["status_id"] = $dados["status_id"];
            $this->dados_item["status_nome"] = $dados["status_nome"];

            if ($dados["bloco"]) {

                $bloco = $this->buscaDadosBloco($dados["bloco"]);
                $condominio = $this->buscaDadosCondominio($bloco["condominio"]);
                $dados["cep"] = $bloco["cep"] ? $bloco["cep"] : $condominio["cep"];
                
                $this->dados_item["bloco_id"] = $bloco["id"];
                $this->dados_item["bloco_nome"] = $bloco["nome"];
                $this->dados_item["condominio_id"] = $condominio["id"];
                $this->dados_item["condominio_nome"] = $condominio["nome"];
                $this->dados_item["numero"] = $bloco["cep"] ? $bloco["numero"] : $condominio["numero"];

            } elseif ($dados["condominio"]) {

                $condominio = $this->buscaDadosCondominio($dados["condominio"]);
                $dados["cep"] = $condominio["cep"];
                
                $this->dados_item["condominio_id"] = $condominio["id"];
                $this->dados_item["condominio_nome"] = $condominio["nome"];
                $this->dados_item["numero"] = $condominio["numero"];
                
            } else {
                
                $this->dados_item["bloco_id"] = null;
                $this->dados_item["bloco_nome"] = "";
                $this->dados_item["condominio_id"] = null;
                $this->dados_item["condominio_nome"] = "";
                
            }

            $this->dados_item["numero"] = $this->dados_item["numero"] ? $this->dados_item["numero"] : "";
            $this->dados_item["cep_id"] = $dados["cep"];
            $this->dados_item["cep"] = substr($dados["cep"], 0, 5) . "-" . substr($dados["cep"], 5, 3);

            $query = $this->bancodedados->query("
                SELECT
                    tab_estados.est_nome AS estado,
                    tab_estados.est_sigla AS estado_sigla,
                    tab_cidades.cid_nome AS cidade,
                    tab_bairros.bai_nome AS bairro,
                    tab_logradouros.log_nome AS logradouro
                FROM
                    tab_ceps
                    INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
                    INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
                    INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
                    INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
                WHERE
                    tab_ceps.cep_id = '{$dados["cep"]}'");
            
            if (($endereco = $this->bancodedados->resultadoArray($query)) != false) {
                $this->dados_item["estado"] = $endereco["estado"];
                $this->dados_item["estado_sigla"] = $endereco["estado_sigla"];
                $this->dados_item["cidade"] = $endereco["cidade"];
                $this->dados_item["bairro"] = $endereco["bairro"];
                $this->dados_item["logradouro"] = $endereco["logradouro"];
            }

            $this->dados_item["cobranca"] = $this->buscaDadosCobranca($this->dados_item["id"]);
            $this->dados_item["endereco"] = $this->geraEnderecoFormatado($this->dados_item);
            $this->dados_item["negativado"] = $clientes_negativacoes->ehClienteNegativado($this->dados_item["id"]);

			return true;

        }
		
		return false;

    }

    function buscaSituacaoFinanceira($cliente_id) {

        $situacao_financeira = "<b>" . $this->buscaStatusCliente($cliente_id, true) . "</b>";
        $dados_ajuste = $this->buscaAjusteAtivo($cliente_id);

        if ($dados_ajuste) {

            $cor = $dados_ajuste["desbloqueio_inicio"] ? "orange" : "red";
            $descricao = $dados_ajuste["desbloqueio_inicio"] ? "Susp. Temporária" : "Bloqueado";
            $mensalidades = $this->buscaMensalidadesPendentes($cliente_id);
            $hint = array();

            $hint[] = $dados_ajuste["categoria"];
            $hint[] = $dados_ajuste["desbloqueio_inicio"] ? "<br>Início: {$dados_ajuste["desbloqueio_inicio"]}<br>Término: {$dados_ajuste["desbloqueio_termino"]}" : "<br>Início: {$dados_ajuste["ajuste_inicio"]}" . ($dados_ajuste["ajuste_termino"] ? "<br>Término: {$dados_ajuste["ajuste_termino"]}" : "");

            if ($mensalidades) {
                $plural = $mensalidades["quantidade"] > 1 ? "s" : "";
                $hint[] = "<hr>{$mensalidades["quantidade"]} cobrança{$plural} vencida{$plural}:<hr>";
                $hint[] = $mensalidades["hint"];
            }

            $hint = implode("", $hint);

            $situacao_financeira .= " <span style=\"color:$cor;font-weight:bold;cursor:pointer\" onmouseover=\"hintShow('$hint', 250)\" onmouseout=\"hintHide()\">($descricao)</span>";

        } else {

            $mensalidades = $this->buscaMensalidadesPendentes($cliente_id);

            if ($mensalidades) {
                $plural = $mensalidades["quantidade"] > 1 ? "s" : "";
                $situacao_financeira .= " <span style=\"color:orange;font-weight:bold;cursor:pointer\" onmouseover=\"hintShow('{$mensalidades["quantidade"]} cobrança$plural vencida$plural:<hr>{$mensalidades["hint"]}', 150)\" onmouseout=\"hintHide()\">(Pendente)</span>";
            } else {
                $situacao_financeira .= " <span style=\"color:green;font-weight:bold\">(Regular)</span>";
            }

        }

        return $situacao_financeira;

    }

    function buscaDadosPendentes($dados_cliente) {

        $dados_pendentes = array();

		/*
        if (!$dados_cliente["tipo_id"]) {
            $dados_pendentes["dados"]["tipo"] = "» Tipo";
        }
        if (!$dados_cliente["cpf_cnpj"]) {
            $dados_pendentes["dados"]["cpf_cnpj"] = $dados_cliente["tipo_id"] == 1 ? "» CPF" : "» CNPJ";
        }
        if ($dados_cliente["tipo_id"] == 1 && !$dados_cliente["nascimento"]) {
            $dados_pendentes["dados"]["nascimento"] = "» Nascimento";
        }
        if ($dados_cliente["condominio_id"] && !$dados_cliente["bloco_id"]) {
            $dados_pendentes["dados"]["bloco"] = "» Bloco";
        }
        if ($dados_cliente["condominio_id"] && !$dados_cliente["complemento_valor"]) {
            $dados_pendentes["dados"]["complemento_valor"] = "» Complemento";
        }
        if (!$dados_cliente["telefone"]) {
            $dados_pendentes["dados"]["telefone"] = "» Telefone(s)";
        }
        if (!$dados_cliente["email"]) {
            $dados_pendentes["dados"]["email"] = "» E-mail(s)";
        }
        if ($dados_pendentes) {
            if (!$this->permissoes["clientes"]["editar_dc"] || $dados_cliente["status_id"] != 2) {
                $dados_pendentes["alertar"] = false;
            } else {
                $dados_pendentes["alertar"] = true;
            }
		} */

        return $dados_pendentes;

    }

    function buscaDadosCobrancaFormato($formato_id) {

		$query = $this->bancodedados->query("
            SELECT
                cof_id AS id,
                cof_nome AS nome,
                cof_parametros AS parametros,
                cof_publicavel AS publicavel,
                cft_id AS tipo_id,
                cfs_id AS status_id
            FROM
                tab_cobrancas_formatos
            WHERE
				cof_id = $formato_id");

        return $this->bancodedados->resultadoArray($query);

    }

    function geraPendencias($pendencias) {

        if ($pendencias && $pendencias["alertar"]) {
            return "['" . implode("', '", $pendencias["dados"]) . "']";
        } else {
            return "[]";
        }

    }

    function geraAlertaPendencias($pendencias) {

        $alerta = array();

        if ($pendencias) {

            $plural = count($pendencias["dados"]) > 1 ? "s" : "";
            
            $alerta[] = "<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">";
            $alerta[] = "<td colspan=\"3\" style=\"font-weight:bold;text-align:center\">";
            $alerta[] = "<br><span style=\"color:#FF0000\">ATENÇÃO!</span><br><br>Esse cliente possui o$plural seguinte$plural dado$plural pendente$plural:<br><br>";
            $alerta[] = implode("<br>", $pendencias["dados"]);
            if ($pendencias["alertar"]) {
            	$alerta[] = "<br><br>Favor solicitá-lo$plural e cadastrá-lo$plural no sistema. Obrigado.";
            }
            $alerta[] = "<br><br>";
            $alerta[] = "</td>";
            $alerta[] = "</tr>";

        } else {
            $alerta[] = "";
        }

        return implode($alerta);

    }

    function buscaMensalidadesPendentes($cliente_id) {

        $hints = array();

        $query = $this->bancodedados->query("
            SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_codigo AS codigo,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_cobrancas.col_id AS lote_id,
				tab_cobrancas.coa_pai AS coa_pai
            FROM
                tab_cobrancas
            WHERE
                tab_cobrancas.cli_id = $cliente_id AND
                tab_cobrancas.cot_id = 1 AND
				tab_cobrancas.coa_pai IS NULL AND
				tab_cobrancas.coa_data_vencimento < CURRENT_DATE()
            ORDER BY
				tab_cobrancas.coa_data_vencimento");
                
        $quantidade = $this->bancodedados->linhas($query);

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

            $dados["valor_original"] = $this->formataDinheiro($dados["valor_original"]);

            $dados_hint = "Código: {$dados["codigo"]}<br>";
            $dados_hint .= "Vencimento: {$dados["data_vencimento"]}<br>";
            $dados_hint .= "Valor: {$dados["valor_original"]}";

            if (count($hints) > 4) {
                $hints[] = "...";
                break;
            }

            $hints[] = $dados_hint;

        }

        if (count($hints)) {
            return array("quantidade" => $quantidade, "hint" => implode("<hr>", $hints));
        }

    }

    function buscaCobrancaCompetencia($cobranca_id) {

        $competencias = array();

		$query = $this->bancodedados->query("
            SELECT
                DATE_FORMAT(cod_competencia_inicio, '%m/%Y') AS competencia
            FROM
                tab_cobrancas_detalhes
            WHERE
                coa_id = $cobranca_id AND
                cod_competencia_inicio IS NOT NULL AND
				ass_id IS NOT NULL");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $competencias[] = $this->formataData($dados["competencia"]);
        }

        if (!$competencias) {

			$query = $this->bancodedados->query("
                SELECT
                    DATE_FORMAT(cod_competencia_inicio, '%m/%Y') AS competencia
                FROM
                    tab_cobrancas_detalhes
                WHERE
                    coa_id = $cobranca_id AND
					cod_competencia_inicio IS NOT NULL");

            while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
                $competencias[] = $this->formataData($dados["competencia"]);
            }

        }

        return implode(", ", array_unique($competencias));

    }

	function formataDinheiro($valor, $moeda = "R$") {

        $valor = preg_replace("/[^0-9]/", "", intval($valor));
        $valor = number_format($valor / 100, 2, ",", ".");
		
		if ($moeda) {
			$valor = $moeda . " " . $valor;
		}

        return $valor;

    }

    function formataData($vencimento) {

        $meses = array(1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
        $vencimento = explode("/", $vencimento);

        return $meses[intval($vencimento[0])]."/".$vencimento[1];

    }

    function buscaAjusteAtivo($cliente_id) {

		$query = $this->bancodedados->query("
            SELECT
                tab_servicos_ajustes.saj_id AS id,
                DATE_FORMAT(tab_servicos_ajustes.saj_inicio, '%d/%m/%Y (%H:%i)') AS ajuste_inicio,
                DATE_FORMAT(tab_servicos_ajustes.saj_termino, '%d/%m/%Y (%H:%i)') AS ajuste_termino,
                DATE_FORMAT(desbloqueio.saj_inicio, '%d/%m/%Y (%H:%i)') AS desbloqueio_inicio,
                DATE_FORMAT(desbloqueio.saj_termino, '%d/%m/%Y (%H:%i)') AS desbloqueio_termino,
                tab_servicos_ajustes_categorias.sac_id AS categoria_id,
                tab_servicos_ajustes_categorias.sac_nome AS categoria
            FROM
                tab_servicos_ajustes
                INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes.sac_id = tab_servicos_ajustes_categorias.sac_id)
                LEFT OUTER JOIN tab_servicos_ajustes desbloqueio ON (tab_servicos_ajustes.saj_id = desbloqueio.saj_pai AND desbloqueio.sas_id IN (5, 9))
            WHERE
                tab_servicos_ajustes.cli_id = $cliente_id AND
                tab_servicos_ajustes.sac_id IN (1, 3, 10) AND
                tab_servicos_ajustes.sas_id IN (5, 9)
            ORDER BY
				tab_servicos_ajustes.sac_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $dados["categoria"] = preg_replace("/\\([^\\)]*\\)/", "", $dados["categoria"]);
            return $dados;
        }

    }

    function geraCodigoDebitoAutomatico($cliente_id, $formato_id, $agencia, $conta, $operacao) {

        if ($this->ehFormatoDebito($formato_id) && $agencia && $conta) {

			if (($codigo = $this->buscaCodigoCliente($formato_id, $agencia, $conta, $operacao)) != false) {
                return $codigo;
            }

            if ($formato_id == 3) {

                $agencia = preg_replace("/[^0-9]/", "", substr($agencia, 0, -2));
                $conta_corrente = preg_replace("/[^0-9]/", "", substr($conta, 0, -2));
			
                return sprintf("%010s", $cliente_id) . sprintf("%05s", $agencia) . sprintf("%010s", $conta_corrente);

            } elseif ($formato_id == 25) {

                $operacao = preg_replace("/[^0-9]/", "", $operacao);
                $agencia = preg_replace("/[^0-9]/", "", $agencia);
                $conta = preg_replace("/[^0-9]/", "", $conta);

                return "1" . sprintf("%08s", $cliente_id) . sprintf("%03s", $operacao) . sprintf("%05s", $agencia) . sprintf("%08s", $conta);

            } else {

                $operacao = preg_replace("/[^0-9]/", "", $operacao);
                $agencia = preg_replace("/[^0-9]/", "", $agencia);
                $conta = preg_replace("/[^0-9]/", "", $conta);

                return "1" . sprintf("%08s", $cliente_id) . sprintf("%03s", $operacao) . sprintf("%04s", $agencia) . sprintf("%09s", $conta);

            }

        }

    }

    function buscaCodigoCliente($formato_id, $agencia, $conta, $operacao) {

        $where = array();

        $where[] = "cof_id = $formato_id";
        $where[] = "cli_ccd_debito_agencia = '$agencia'";
        $where[] = "cli_ccd_debito_conta = '$conta'";
        if ($operacao) {
            $where[] = "cli_ccd_debito_operacao = '$operacao'";
        }

        $where = implode(" AND\n                  ", $where);

		$query = $this->bancodedados->query("
              SELECT
                  cli_ccd_debito_codigo
              FROM
                  tab_clientes
              WHERE
				$where");

        return $this->bancodedados->resultado($query);

    }

    function ehFormatoDebito($formato_id) {

		$query = $this->bancodedados->query("
            SELECT
                IF(cft_id = 4, 1, 0)
            FROM
                tab_cobrancas_formatos
            WHERE
				cof_id = $formato_id");

		return $this->bancodedados->resultado($query) == "1";

    }

    function buscaCobrancasFormatosDebito() {

        $formatos = array();

		$query = $this->bancodedados->query("
            SELECT
                cof_id AS formato_id
            FROM
                tab_cobrancas_formatos
            WHERE
				cft_id = 4");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $formatos[] = $dados["formato_id"];
        }

        return "[" . implode(", ", $formatos) . "]";

    }

    function buscaClientesEmails($cliente_id) {

        $emails = array();

		$query = $this->bancodedados->query("
            SELECT
                CONCAT(ccm_usuario, '@', ccm_dominio) AS email
            FROM
                tab_clientes_contatos_emails
            WHERE
				cli_id = $cliente_id");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $emails[] = $dados["email"];
        }

        return implode(", ", $emails);

    }

    function possuiServicoCorporativo($cliente_id) {

		$query = $this->bancodedados->query("
            SELECT
				COUNT(tab_assinaturas.ass_id)
            FROM
                tab_assinaturas
                INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
            WHERE
                tab_assinaturas.cli_id = $cliente_id AND
                tab_assinaturas.ast_id IN (2, 5) AND
				tab_planos.pls_id = 3");

        return $this->bancodedados->resultado($query) > 0;

    }

    function buscaDadosCobranca($cliente_id) {

        $dados_cobranca = array();

        $query = $this->bancodedados->query("
            SELECT
                cob_id AS bloco,
                LPAD(cep_id, 8, '0') AS cep,
                ccd_numero AS numero,
                ccd_complemento AS complemento
            FROM
                tab_clientes_cobranca_dados
            WHERE
                cli_id = $cliente_id");

        if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

            if ($dados["complemento"]) {
                $complemento_tipos = $this->buscaComplementoTipos();
                $dados_cobranca["complemento_tipo_id"] = $dados["complemento"][0];
                $dados_cobranca["complemento_tipo_nome"] = $complemento_tipos[$dados_cobranca["complemento_tipo_id"]];
                $dados_cobranca["complemento_valor"] = substr($dados["complemento"], 1, strlen($dados["complemento"]));
                if ($dados_cobranca["complemento_tipo_id"] != "o") {
                    $dados_cobranca["complemento"] = $dados_cobranca["complemento_tipo_nome"] . " " . intval($dados_cobranca["complemento_valor"]);
                } else {
                    $dados_cobranca["complemento"] = $dados_cobranca["complemento_valor"];
                }
            } else {
                $dados_cobranca["complemento_tipo_id"] = "o";
                $dados_cobranca["complemento_valor"] = "";
                $dados_cobranca["complemento"] = "";
            }

            if ($dados["bloco"]) {

                if ($dados["bloco"] != $this->dados_item["bloco_id"]) {

                    $bloco = $this->buscaDadosBloco($dados["bloco"]);
                    $dados_cobranca["bloco_id"] = $bloco["id"];
                    $dados_cobranca["bloco_nome"] = $bloco["nome"];
                    $condominio = $this->buscaDadosCondominio($bloco["condominio"]);
                    $dados_cobranca["condominio_id"] = $condominio["id"];
                    $dados_cobranca["condominio_nome"] = $condominio["nome"];

                    if ($bloco["cep"]) {
                        $dados["cep"] = $bloco["cep"];
                        $dados_cobranca["numero"] = $bloco["numero"];
                    } else {
                        $dados["cep"] = $condominio["cep"];
                        $dados_cobranca["numero"] = $condominio["numero"];
                    }

                    if ($dados["cep"] != $this->dados_item["cep"]) {

                        $dados_cobranca["cep"] = substr($dados["cep"], 0, 5) . "-" . substr($dados["cep"], 5, 3);
						
						$query = $this->bancodedados->query("
                            SELECT
                                tab_estados.est_nome AS estado,
                                tab_cidades.cid_nome AS cidade,
                                tab_bairros.bai_nome AS bairro,
                                tab_logradouros.log_nome AS logradouro
                            FROM
                                tab_ceps
                                INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
                                INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
                                INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
                                INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
                            WHERE
								tab_ceps.cep_id = {$dados["cep"]}");

						if (($endereco = $this->bancodedados->resultadoArray($query)) != false) {
                            $dados_cobranca["estado"] = $endereco["estado"];
                            $dados_cobranca["cidade"] = $endereco["cidade"];
                            $dados_cobranca["bairro"] = $endereco["bairro"];
                            $dados_cobranca["logradouro"] = $endereco["logradouro"];
                        }

                    } else {

                        $dados_cobranca["cep"] = $this->dados_item["cep"];
                        $dados_cobranca["estado"] = $this->dados_item["estado"];
                        $dados_cobranca["cidade"] = $this->dados_item["cidade"];
                        $dados_cobranca["bairro"] = $this->dados_item["bairro"];
                        $dados_cobranca["logradouro"] = $this->dados_item["logradouro"];

                    }

                } else {

                    $dados_cobranca["condominio_id"] = $this->dados_item["condominio_id"];
                    $dados_cobranca["condominio_nome"] = $this->dados_item["condominio_nome"];
                    $dados_cobranca["bloco_id"] = $this->dados_item["bloco_id"];
                    $dados_cobranca["bloco_nome"] = $this->dados_item["bloco_nome"];
                    $dados_cobranca["numero"] = $this->dados_item["numero"];
                    $dados_cobranca["cep"] = $this->dados_item["cep"];
                    $dados_cobranca["estado"] = $this->dados_item["estado"];
                    $dados_cobranca["cidade"] = $this->dados_item["cidade"];
                    $dados_cobranca["bairro"] = $this->dados_item["bairro"];
                    $dados_cobranca["logradouro"] = $this->dados_item["logradouro"];

                }

            } elseif ($dados["cep"]) {

                $dados_cobranca["cep"] = substr($dados["cep"], 0, 5) . "-" . substr($dados["cep"], 5, 3);

				$query = $this->bancodedados->query("
                    SELECT
                        tab_estados.est_nome AS estado,
                        tab_cidades.cid_nome AS cidade,
                        tab_bairros.bai_nome AS bairro,
                        tab_logradouros.log_nome AS logradouro
                    FROM
                        tab_ceps
                        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
                        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
                        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
                        INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
                    WHERE
						tab_ceps.cep_id = {$dados["cep"]}");

				if (($endereco = $this->bancodedados->resultadoArray($query)) != false) {
					$dados_cobranca["condominio_id"] = "";
					$dados_cobranca["condominio_nome"] = "";
					$dados_cobranca["bloco_id"] = "";
					$dados_cobranca["bloco_nome"] = "";
                    $dados_cobranca["estado"] = $endereco["estado"];
                    $dados_cobranca["cidade"] = $endereco["cidade"];
                    $dados_cobranca["bairro"] = $endereco["bairro"];
                    $dados_cobranca["logradouro"] = $endereco["logradouro"];
                    if ($dados["numero"]) {
                        $dados_cobranca["numero"] = $dados["numero"];
                    } else {
                        $dados_cobranca["numero"] = "";
                    }
                }

            } else {

                $dados_cobranca["condominio_id"] = $this->dados_item["condominio_id"];
                $dados_cobranca["condominio_nome"] = $this->dados_item["condominio_nome"];
                $dados_cobranca["bloco_id"] = $this->dados_item["bloco_id"];
                $dados_cobranca["bloco_nome"] = $this->dados_item["bloco_nome"];
                $dados_cobranca["cep"] = $this->dados_item["cep"];
                $dados_cobranca["estado"] = $this->dados_item["estado"];
                $dados_cobranca["cidade"] = $this->dados_item["cidade"];
                $dados_cobranca["bairro"] = $this->dados_item["bairro"];
                $dados_cobranca["logradouro"] = $this->dados_item["logradouro"];
                $dados_cobranca["numero"] = $this->dados_item["numero"];

            }

        } else {

            $dados_cobranca["nome"] = $this->dados_item["nome"];
            $dados_cobranca["condominio_id"] = $this->dados_item["condominio_id"];
            $dados_cobranca["condominio_nome"] = $this->dados_item["condominio_nome"];
            $dados_cobranca["bloco_id"] = $this->dados_item["bloco_id"];
            $dados_cobranca["bloco_nome"] = $this->dados_item["bloco_nome"];
            $dados_cobranca["cep"] = $this->dados_item["cep"];
            $dados_cobranca["estado"] = $this->dados_item["estado"];
            $dados_cobranca["cidade"] = $this->dados_item["cidade"];
            $dados_cobranca["bairro"] = $this->dados_item["bairro"];
            $dados_cobranca["logradouro"] = $this->dados_item["logradouro"];
            $dados_cobranca["numero"] = $this->dados_item["numero"];
            $dados_cobranca["complemento_tipo_id"] = $this->dados_item["complemento_tipo_id"];
            $dados_cobranca["complemento_tipo_nome"] = $this->dados_item["complemento_tipo_nome"];
            $dados_cobranca["complemento_valor"] = $this->dados_item["complemento_valor"];
            $dados_cobranca["complemento"] = $this->dados_item["complemento"];

        }

        return $dados_cobranca;

    }

    function temPermissao($usuario, $modulo, $acao) {

		$query = $this->bancodedados->query("
        SELECT
            COUNT(rel_gru_aco_tempermissao.aco_id)
        FROM
            tab_usuarios
            INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
            INNER JOIN rel_gru_aco_tempermissao ON (rel_usu_gru_fazparte.gru_id = rel_gru_aco_tempermissao.gru_id)
            INNER JOIN tab_acoes ON (rel_gru_aco_tempermissao.aco_id = tab_acoes.aco_id)
            INNER JOIN tab_modulos ON (tab_acoes.mod_id = tab_modulos.mod_id)
        WHERE
				tab_usuarios.usu_id = '$usuario' AND
            tab_modulos.mod_nome = '$modulo' AND
				tab_acoes.aco_nome = '$acao'");

        if ($this->bancodedados->resultado($query)) {
            return 2;
        }

        return 1;

    }

    ###################
    ### Busca Itens ###
    ###################

    function buscaTipos() {

        $tipos = array();

		$query = $this->bancodedados->query("
			SELECT 
				clt_id AS id, 
				clt_nome AS nome 
			FROM 
				tab_clientes_tipos 
			ORDER BY 
				nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $tipos[$dados["id"]] = $dados["nome"];
        }

        return $tipos;

    }

    function buscaCondominios() {

        return $this->buscaCondominiosPorStatus("1, 2, 3");

    }

    function buscaPanoramaCondominio($condominio_id) {

		$query = $this->bancodedados->query("
            SELECT
                tab_condominios_status.cos_nome AS status,
                SUM(IF(tab_clientes.cls_id = 2, 1, 0)) AS clientes
            FROM
                tab_condominios
                INNER JOIN tab_condominios_status ON (tab_condominios.cos_id = tab_condominios_status.cos_id)
                LEFT OUTER JOIN tab_clientes ON (tab_condominios.con_id = tab_clientes.con_id)
            WHERE
                tab_condominios.con_id = $condominio_id
            GROUP BY
				tab_condominios.con_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            return "<b>{$dados["status"]}</b> ({$dados["clientes"]} cliente" . ($dados["clientes"] == 1 ? "" : "s") . ")";
        }

    }

    function buscaCondominiosPorStatus($status_id) {

        $condominios = array();

		$query = $this->bancodedados->query("
            SELECT
                con_id AS id,
                CONCAT(tab_condominios.con_nome, ' (', tab_cidades.cid_nome, ' / ', tab_bairros.bai_nome, ')') AS nome
            FROM
                tab_condominios
                INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
                INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
                INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
                INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
            WHERE
                cos_id IN ($status_id)
            ORDER BY
				nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $condominios[$dados["id"]] = $dados["nome"];
        }

        return $condominios;

    }

    function buscaPanoramaBloco($bloco_id) {

		$query = $this->bancodedados->query("
            SELECT
                tab_condominios_blocos_status.cbs_nome AS status,
                SUM(IF(tab_clientes.cls_id = 2, 1, 0)) AS clientes
            FROM
                tab_condominios_blocos
                INNER JOIN tab_condominios_blocos_status ON (tab_condominios_blocos.cbs_id = tab_condominios_blocos_status.cbs_id)
                LEFT OUTER JOIN tab_clientes ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
            WHERE
                tab_condominios_blocos.cob_id = $bloco_id
            GROUP BY
				tab_condominios_blocos.cob_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            return "<b>{$dados["status"]}</b> ({$dados["clientes"]} cliente" . ($dados["clientes"] == 1 ? "" : "s") . ")";
        }

    }

    function buscaBlocos($condominio_id) {

        $blocos = array();

        if ($condominio_id) {

			$query = $this->bancodedados->query("
                SELECT
                    cob_id AS id,
                    cob_nome AS nome
                FROM
                    tab_condominios_blocos
                    WHERE
                con_id = $condominio_id
                ORDER BY
					nome");

            while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
                $blocos[$dados["id"]] = $dados["nome"];
            }

        }

        return $blocos;

    }

    function buscaCobrancaVencimentos($origem_id, $vencimento = null) {

        $vencimentos = array();

        if ($vencimento) {
            $vencimentos[intval($vencimento, 10)] = "Dia " . sprintf("%02s", $vencimento);
        }

        $vencimentos[5] = "Dia 05";
        $vencimentos[6] = "Dia 06";
        $vencimentos[7] = "Dia 07";
        $vencimentos[8] = "Dia 08";
        $vencimentos[10] = "Dia 10";
		
		if ($origem_id == 1) {
			$vencimentos[15] = "Dia 15";
		} else {
			$vencimentos[20] = "Dia 20";
        }
        $vencimentos[28] = "Dia 28";

        ksort($vencimentos);

        return $vencimentos;

    }

    function possuiRetornoAberto($cliente_id) {

		$query = $this->bancodedados->query("
            SELECT
				COUNT(clr_id) AS retornos
            FROM
                tab_clientes_retornos
            WHERE
                usu_id = {$_SESSION["usuario"]} AND
                cli_id = $cliente_id AND
				ces_id = 1");

        return $this->bancodedados->resultado($query);

    }

    function buscaCobrancaFormatos($status_id = "1", $exibir_plus = true, $id_atual = null, $cliente_origem_id = 1, $cliente_cobranca_email = null) {

        $formatos = array();

		$query = $this->bancodedados->query("
            SELECT
                CONCAT(cft_id, ',', cof_id) AS id,
                cof_nome AS nome
            FROM
                tab_cobrancas_formatos
            WHERE
				(
					cof_id IN (SELECT cof_id FROM rel_cof_clo_disponivel WHERE rel_cof_clo_disponivel.clo_id = $cliente_origem_id) AND
					cfs_id = '$status_id' AND
					cof_id <> 26".($cliente_cobranca_email ? "" : " AND
					cft_id != 3").($exibir_plus ? "" : " AND
					cft_id NOT IN (4)")."
				)".($id_atual ? " OR
				cof_id = $id_atual" : "")."
            ORDER BY
				nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $formatos[$dados["id"]] = $dados["nome"];
        }

        return $formatos;

    }

    function buscaSituacoes() {

        $situacoes = array();

		$query = $this->bancodedados->query("
            SELECT
                clu_id AS id,
                clu_nome AS nome
            FROM
                tab_clientes_situacoes
            ORDER BY
				nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $situacoes[$dados["id"]] = $dados["nome"];
        }

        return $situacoes;

    }

    function buscaCondominiais($condominio_id, $cliente_id) {

		$situacoes = array();

		if ($condominio_id && $cliente_id) {

			$query = $this->bancodedados->query("
	            SELECT DISTINCT
	                tab_clientes.cli_id AS id,
	                tab_clientes.cli_nome AS nome
	            FROM
	                tab_clientes
	                INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
	            WHERE
	                tab_clientes.cli_id != $cliente_id AND
	                tab_clientes.con_id = $condominio_id AND
	                tab_assinaturas.ast_id = 2 AND
	                tab_assinaturas.pla_id = 244
	            ORDER BY
					nome");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$situacoes[$dados["id"]] = $dados["nome"];
			}

        }

		return $situacoes;

    }

    function buscaClientesCondominiais($cliente_id) {

        $clientes = array();

		$query = $this->bancodedados->query("
            SELECT DISTINCT
                tab_clientes.cli_id AS cliente_id,
                tab_clientes.cli_nome AS nome,
                tab_clientes.cof_id AS formato_id,
                tab_condominios_blocos.cob_nome AS bloco,
                SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento,
                tab_clientes_origens.clo_nome AS origem
            FROM
                tab_clientes
                INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
                LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
            WHERE
                tab_clientes.cli_pai = $cliente_id AND
                tab_clientes.cls_id = 2
            ORDER BY
                tab_condominios_blocos.cob_nome,
				SUBSTRING(tab_clientes.cli_complemento, 2) + 0");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

            $dados["links"] = $this->geraLinks($dados["cliente_id"]);
			if ($this->permissoes["clientes"]["ver_dc"]) {
				$dados["link"] = "?m=clientes&a=ver_dc&i=" . $dados["cliente_id"];
			}
			$dados["apartamento"] = array();
			if ($dados["complemento"]) {
				$dados["apartamento"][] = $dados["complemento"];
			}
			if ($dados["bloco"] && $dados["bloco"] != "Único") {
				$dados["apartamento"][] = $dados["bloco"];
			}
            $dados["apartamento"] = implode(" / ", $dados["apartamento"]);
            $dados["cobranca"] = $this->buscaProximaCobranca($cliente_id, $dados["cliente_id"], $dados["formato_id"]);
            $dados["id"] = count($clientes) + 1;

            $clientes[] = $dados;

        }

        return $clientes;

    }

    function buscaProximaCobranca($condominial_id, $cliente_id, $formato_id) {

		$query = $this->bancodedados->query("
            SELECT
				SUM(cod_valor_final)
            FROM
                tab_cobrancas
                INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
                LEFT OUTER JOIN tab_assinaturas ON (tab_cobrancas_detalhes.ass_id = tab_assinaturas.ass_id)
                LEFT OUTER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id)
                LEFT OUTER JOIN tab_descontos_concessoes ON (tab_cobrancas_detalhes.dec_id = tab_descontos_concessoes.dec_id)
                LEFT OUTER JOIN tab_assinaturas assinaturas_descontos ON (tab_descontos_concessoes.ass_id = assinaturas_descontos.ass_id)
            WHERE
                tab_cobrancas.cli_id = $condominial_id AND
                DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%Y%m') = DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y%m') AND
				tab_cobrancas.coa_pai IS NULL AND (
					tab_assinaturas.cli_id = $cliente_id OR
                 tab_ajustes_financeiros.cli_id = $cliente_id OR
					assinaturas_descontos.cli_id = $cliente_id
				)
            GROUP BY
				tab_cobrancas.coa_id");

		if (($valor_final = $this->bancodedados->resultado($query)) !== false) {
			return $this->formataDinheiro($valor_final);
		}

        $cobrancas_geracao = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);
        $dados_formato = $cobrancas_geracao->buscaDadosFormato($formato_id);
        $vencimento = date("m/Y", strtotime("next month"));
        $vencimento = explode("/", $vencimento);
		
		if (($cobranca = $cobrancas_geracao->buscaCobrancasCliente($cliente_id, $vencimento[0], $vencimento[1], $dados_formato["id"], $dados_formato["parametros"]["valor_cobranca"]["valor"])) != false) {
            return $this->formataDinheiro($cobranca["valor_final"]);
        }

	}

    function geraListagemClientesCondominiais($dados) {

        $campos = array("id" => "#", "cliente_id" => "ID", "nome" => "Nome", "apartamento" => "Apartamento", "origem" => "Origem", "cobranca" => "Cobrança", "links" => "Links");
        $listagem = controle::instanciaObjeto("listagem", $campos, $dados, 0, false, array("20"), null, array("right", "center", "left"));

        return $listagem->conteudo;

    }

    function buscaNomeCliente($cliente_id) {

		if ($cliente_id) {

			$query = $this->bancodedados->query("
	            SELECT
	                cli_nome
	            FROM
	                tab_clientes
	            WHERE
					cli_id = $cliente_id");

			return $this->bancodedados->resultado($query);

		}

    }

    function buscaOrigens($origem_id = null) {

        $origens = array();
		$origem_ids = array();
		
		if ($_SESSION["origem"]) {
			$origem_ids[] = $_SESSION["origem"];
		}
		
		if ($origem_id && $origem_id != $_SESSION["origem"]) {
			$origem_ids[] = $origem_id;
		}
		
		$origem_ids = implode(", ", $origem_ids);

        $query = $this->bancodedados->query("
            SELECT
                tab_clientes_origens.clo_id AS id,
                tab_clientes_origens.clo_nome AS nome
            FROM
                tab_clientes_origens
            WHERE".($origem_ids ? "
                clo_id IN ($origem_ids)": "
				1")."
            ORDER BY
                nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $origens[$dados["id"]] = $dados["nome"];
        }

        return $origens;

    }

    function buscaIndicacoes() {

        $indicacoes = array();

		$query = $this->bancodedados->query("
            SELECT
                cld_id AS id,
                cld_nome AS nome
            FROM
                tab_clientes_indicacoes
            ORDER BY
				nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $indicacoes[$dados["id"]] = $dados["nome"];
        }

        return $indicacoes;

    }

    function buscaOperadorasOrigens() {

        $operadoras_origens = array();

		$query = $this->bancodedados->query("
            SELECT
                coo_id AS id,
                coo_nome AS nome
            FROM
                tab_clientes_operadoras_origens
            ORDER BY
				nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $operadoras_origens[$dados["id"]] = $dados["nome"];
        }

        return $operadoras_origens;

    }

    function buscaPlanos($servico_id) {

        $planos = array();

		$query = $this->bancodedados->query("
            SELECT
                tab_planos.pla_id AS id,
                tab_planos.pla_nome AS nome
            FROM
                tab_planos
            WHERE
                tab_planos.pls_id = $servico_id
            ORDER BY
				nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $planos[$dados["id"]] = $dados["nome"];
        }

        return $planos;

    }

    function buscaPlanosServico($servico_id) {

		$planos = array();

		if ($servico_id) {

			$query = $this->bancodedados->query("
	            SELECT
	                tab_planos.pla_id AS id,
	                tab_planos.pla_nome AS nome
	            FROM
	                tab_planos_servicos
	                INNER JOIN tab_planos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
	                INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)
	            WHERE
	                tab_planos_servicos.pls_id = $servico_id
	            ORDER BY
					nome");

	        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	            $planos[$dados["id"]] = $dados["nome"];
	        }

		}

        return $planos;

    }

    function buscaServicos() {

        $servicos = array();

		$query = $this->bancodedados->query("
            SELECT
                tab_planos_servicos.pls_id AS id,
                tab_planos_servicos.pls_nome AS nome
            FROM
                tab_planos_servicos
            ORDER BY
				nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $servicos[$dados["id"]] = $dados["nome"];
        }

        return $servicos;

    }

    function buscaRetornosPeriodos() {

        $periodos = array();

		$query = $this->bancodedados->query("
			SELECT 
				cee_id AS id, 
				cee_nome AS nome 
			FROM 
				tab_clientes_retornos_periodos 
			ORDER BY 
				nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $periodos[$dados["id"]] = $dados["nome"];
        }

        return $periodos;

    }

    function buscaComplementoTipos($tipo_id = null, $abreviado = false) {

        $tipos = array();

        $tipos["a"] = $abreviado ? "Ap" : "Apartamento";
        $tipos["l"] = $abreviado ? "Lj" : "Loja";
        $tipos["s"] = $abreviado ? "Sl" : "Sala";
        $tipos["c"] = "Casa";
        $tipos["o"] = "Outro";

        return $tipo_id ? ($tipo_id != "o" ? $tipos[$tipo_id] . " " : "") : $tipos;

    }

    function buscaResponsaveis($somente_ativos = true, $usuario_atual_id = null, $somente_este_id = null) {

        $responsaveis = array();

        $query = $this->bancodedados->query("
            SELECT DISTINCT
                tab_usuarios.usu_id AS id,
                tab_usuarios.usu_nome AS nome
            FROM
                tab_usuarios
                INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
                INNER JOIN rel_gru_aco_tempermissao ON (rel_usu_gru_fazparte.gru_id = rel_gru_aco_tempermissao.gru_id)
                INNER JOIN tab_acoes ON (rel_gru_aco_tempermissao.aco_id = tab_acoes.aco_id)
                INNER JOIN tab_modulos ON (tab_acoes.mod_id = tab_modulos.mod_id)
            WHERE
                tab_acoes.aco_nome = 'assumir_responsabilidade' AND
                tab_modulos.mod_nome = 'clientes_responsabilidades'".($somente_este_id ? " AND
                tab_usuarios.usu_id = $somente_este_id" : ($somente_ativos ? " AND
				(tab_usuarios.uss_id = 1".($usuario_atual_id ? " OR
				tab_usuarios.usu_id = $usuario_atual_id" : "").")" : ""))."
            ORDER BY
                nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $responsaveis[$dados["id"]] = $dados["nome"];
        }

        return $responsaveis;

    }

    function buscaSupervisoresComerciais() {

        $supervisores = array();

		$query = $this->bancodedados->query("
			SELECT
				usu_id AS id
            FROM
                tab_usuarios
            WHERE
				usu_id IN (SELECT usu_id FROM rel_usu_gru_fazparte WHERE gru_id = 22) AND
				uss_id = 1
            ORDER BY
				usu_nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $supervisores[] = $dados["id"];
        }

        return $supervisores;

    }

    function buscaStatus() {

        $status = array();

		$query = $this->bancodedados->query("
			SELECT 
				cls_id AS id, 
				cls_nome AS nome 
			FROM 
				tab_clientes_status 
			ORDER BY 
				cls_nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $status[$dados["id"]] = $dados["nome"];
        }

        return $status;

    }

    function buscaClienteStatus($cliente_id) {

        $cliente_id = preg_replace("/[^0-9]/", "", $cliente_id);

        if ($cliente_id) {
			
			$query = $this->bancodedados->query("
				SELECT 
					cls_id 
				FROM 
					tab_clientes 
				WHERE 
					cli_id = $cliente_id");
			
            return $this->bancodedados->resultado($query);
			
        }

    }

    function buscaCondominio($condominio_id) {

        $condominio_id = preg_replace("/[^0-9]/", "", $condominio_id);

        if ($condominio_id) {

			$query = $this->bancodedados->query("
				SELECT 
					con_nome 
				FROM 
					tab_condominios 
				WHERE 
					con_id = $condominio_id");
			
            return $this->bancodedados->resultado($query);
			
        }

    }

    function buscaAssinaturasCliente($cliente_id) {

        $assinaturas = controle::instanciaObjeto("assinaturas", $this->bancodedados, $this->permissoes, false);

        return $assinaturas->geraListagemClientesAssinaturas($assinaturas->buscaClientesAssinaturas($cliente_id, "1,2,3,5"));

    }

    function buscaClientesResponsabilidades($cliente_id) {

        $clientes_responsabilidades = controle::instanciaObjeto("clientes_responsabilidades", $this->bancodedados, $this->permissoes, false);

        return $clientes_responsabilidades->geraListagemClientesResponsabilidades($clientes_responsabilidades->buscaClientesResponsabilidades($cliente_id));

    }

    function buscaClientesOperacoes($cliente_id, $sistema_id) {

        $contatos = array();

		$query = $this->bancodedados->query("
            SELECT
              DATE_FORMAT(tab_operacoes_contatos.opc_inicio, '%d/%m/%Y (%H:%i)') AS data_hora,
              SEC_TO_TIME(UNIX_TIMESTAMP(tab_operacoes_contatos.opc_termino) - UNIX_TIMESTAMP(tab_operacoes_contatos.opc_inicio)) AS duracao,
              tab_operacoes_contatos.opc_comentario AS comentario,
              tab_operacoes_contatos_categorias.occ_nome AS categoria,
              tab_usuarios.usu_nome AS responsavel
            FROM
              tab_operacoes_contatos
              INNER JOIN tab_operacoes_contatos_categorias ON (tab_operacoes_contatos.occ_id = tab_operacoes_contatos_categorias.occ_id)
              INNER JOIN tab_usuarios ON (tab_operacoes_contatos.usu_id = tab_usuarios.usu_id)
            WHERE
              tab_operacoes_contatos.cli_id = $cliente_id AND
                 tab_operacoes_contatos.opc_termino IS NOT NULL AND
                 tab_operacoes_contatos_categorias.oli_id = $sistema_id
            ORDER BY
				tab_operacoes_contatos.opc_termino");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $contatos[] = $dados;
        }

        $cabecalho = array("data_hora" => "Data", "duracao" => "Duração", "categoria" => "Categoria", "comentario" => "Comentário", "responsavel" => "Responsável");
        $listagem = controle::instanciaObjeto("listagem", $cabecalho, $contatos);

        return $listagem->conteudo;

    }

    function buscaClientesRetornos($cliente_id) {

        $clientes_retornos = controle::instanciaObjeto("clientes_retornos", $this->bancodedados, $this->permissoes, false);

        return $clientes_retornos->geraListagemClientesRetornos($clientes_retornos->buscaClientesRetornos($cliente_id));

    }

    function buscaClientesContratos($cliente_id) {

        $contratos = controle::instanciaObjeto("contratos", $this->bancodedados, $this->permissoes, false);

        return $contratos->geraListagemClientesContratos($contratos->buscaClientesContratos($cliente_id, "1,2,3,4,5,11,12,13,14,15,16,17,18,19,20,21,22"));

    }

    function buscaClientesContratosAdicionar($cliente_id) {

        $contratos = controle::instanciaObjeto("contratos", $this->bancodedados, $this->permissoes, false);

        return $contratos->buscaBotaoAdicionar($cliente_id);

    }

    function buscaClientesAjustes($cliente_id) {

        $servicos_ajustes = controle::instanciaObjeto("servicos_ajustes", $this->bancodedados, $this->permissoes, false);

        return $servicos_ajustes->geraListagemClientesAjustes($servicos_ajustes->buscaClientesAjustes($cliente_id));

    }

    function buscaClientesAjustesAdicionar($cliente_id) {

        $servicos_ajustes = controle::instanciaObjeto("servicos_ajustes", $this->bancodedados, $this->permissoes, false);

        return $servicos_ajustes->buscaBotaoAdicionar($cliente_id);

    }

    function buscaClientesAjustesBloqueioTemporarioAdicionar($cliente_id) {

        $servicos_ajustes = controle::instanciaObjeto("servicos_ajustes", $this->bancodedados, $this->permissoes, false);

        return $servicos_ajustes->buscaBotaoAdicionarBloqueioTemporario($cliente_id);

    }

    function buscaPesquisasAplicacoes($cliente_id) {

        $pesquisas_aplicacoes = controle::instanciaObjeto("pesquisas_aplicacoes", $this->bancodedados, $this->permissoes, false);

        return $pesquisas_aplicacoes->geraListagemPesquisasAplicacoes($pesquisas_aplicacoes->buscaPesquisasAplicacoes($cliente_id));

    }

    function buscaPesquisasAplicacoesAdicionar($cliente_id) {

        if ($this->permissoes["pesquisas_aplicacoes"]["adicionar"]) {
            return '<input type="button" class="buttonAzulEscuroBranco11" name="pesquisas_aplicacoes_adicionar" value="Adicionar" onClick="adicionarPesquisasAplicacoes(' . $cliente_id . ')"> ';
        } else {
            return "";
        }

    }

    function buscaClientesCancelamentos($cliente_id) {

        $cancelamentos = controle::instanciaObjeto("cancelamentos", $this->bancodedados, $this->permissoes, false);

        return $cancelamentos->geraListagemCancelamentos($cancelamentos->buscaClientesCancelamentos($cliente_id));

    }

    function buscaClientesCancelamentosAdicionar($cliente_id) {

        $cancelamentos = controle::instanciaObjeto("cancelamentos", $this->bancodedados, $this->permissoes, false);

        return $cancelamentos->buscaBotaoAdicionar($cliente_id);

    }

    function buscaClientesCobrancas($cliente_id) {

        $cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, $this->permissoes, false);

        return $cobrancas->geraListagemClientesCobrancas($cobrancas->buscaClientesCobrancas($cliente_id));

    }

    function buscaClientesAjustesFinanceiros($cliente_id) {

        $ajustes_financeiros = controle::instanciaObjeto("ajustes_financeiros", $this->bancodedados, $this->permissoes, false);

        return $ajustes_financeiros->geraListagemAjustesFinanceiros($ajustes_financeiros->buscaAjustesFinanceiros($cliente_id));

    }

    function geraClientesPontosListagem($cliente_id) {

        $clientes_pontos = controle::instanciaObjeto("clientes_pontos", $this->bancodedados, $this->permissoes, false);

        return $clientes_pontos->geraClientesPontosListagem($clientes_pontos->buscaClientesPontos($cliente_id));

    }

    function geraClientesPontosBotao($cliente_id) {

        $clientes_pontos = controle::instanciaObjeto("clientes_pontos", $this->bancodedados, $this->permissoes, false);

        return $clientes_pontos->geraBotaoAdicionar($cliente_id);

    }

    function geraClientesAutenticacoesListagem($cliente_id) {

        $clientes_autenticacoes = controle::instanciaObjeto("clientes_autenticacoes", $this->bancodedados, $this->permissoes, false);

        return $clientes_autenticacoes->geraClientesAutenticacoesListagem($clientes_autenticacoes->buscaClientesAutenticacoes($cliente_id));

    }

    function geraClientesIptvListagem($cliente_id) {

        $clientes_iptv = controle::instanciaObjeto("clientes_iptv", $this->bancodedados, $this->permissoes, false);

        return $clientes_iptv->geraClientesIptvListagem($clientes_iptv->buscaClientesIptv($cliente_id));

    }

    function geraClientesAutenticacoesBotao($cliente_id) {

        $clientes_autenticacoes = controle::instanciaObjeto("clientes_autenticacoes", $this->bancodedados, $this->permissoes, false);

        return $clientes_autenticacoes->geraBotaoAdicionar($cliente_id);

    }

    function geraClientesEmailsListagem($cliente_id) {

        $clientes_emails = controle::instanciaObjeto("clientes_emails", $this->bancodedados, $this->permissoes, false);
        $listagem = $clientes_emails->geraClientesEmailsClienteListagem($cliente_id);

        return $listagem->conteudo;

    }

    function geraClientesEmailsBotao($cliente_id) {

        $clientes_emails = controle::instanciaObjeto("clientes_emails", $this->bancodedados, $this->permissoes, false);

        return $clientes_emails->geraClientesEmailsClienteAdicionar($cliente_id);

    }

    function geraClientesDominiosListagem($cliente_id) {

        $dominios = controle::instanciaObjeto("dominios", $this->bancodedados, $this->permissoes, false);

        return $dominios->geraClientesDominiosListagem($dominios->buscaClientesDominios($cliente_id));

    }

    function geraClientesVisitasListagem($cliente_id) {

        $visitas = controle::instanciaObjeto("visitas", $this->bancodedados, $this->permissoes, false);

        return $visitas->geraClientesVisitasListagem($visitas->buscaClientesVisitas($cliente_id));

    }

    function buscaConsultasCredito($cliente_id) {

        $consultas_creditos = controle::instanciaObjeto("clientes_consultas_credito", $this->bancodedados, $this->permissoes, false);

        return $consultas_creditos->geraListagemClientesConsultasCredito($consultas_creditos->buscaClientesConsultasCredito($cliente_id));

    }

    function buscaDadosCliente($cliente_id) {

		$query = $this->bancodedados->query("
            SELECT
                cli_id AS id,
                cli_nome AS nome,
                cli_cpf_cnpj AS cpf_cnpj
            FROM
                tab_clientes
            WHERE
				cli_id = $cliente_id");

        return $this->bancodedados->resultadoArray($query);

    }

    #############
    ### Geral ###
    #############

    function geraSelect($itens, $selecionado = null, $item_nulo = false) {

        if ($item_nulo) {
            $select = "<option></option>";
		} else {
			$select = "";
        }

		if (is_array($itens)) {
            foreach ($itens as $valor => $item) {
                if (is_array($item)) {
                    $select .= "<optgroup label=\"$valor\">";
                    foreach ($item as $valor_item => $item_item) {
                        $select .= "<option value=\"$valor_item\"" . (isset($selecionado) && $valor_item == $selecionado ? " selected" : "") . ">$item_item</option>";
                    }
                    $select .= "</optgroup>";
                } else {
                    $select .= "<option value=\"$valor\"" . (isset($selecionado) && $valor == $selecionado ? " selected" : "") . ">$item</option>";
                }
            }
        }

        return $select;

    }

    function geraEnderecoFormatado($endereco) {

        if ($endereco["condominio_nome"]) {
            if ($this->permissoes["condominios"][$_GET["a"]]) {
                $endereco_formatado = "<a class=\"txtAzulEscuro11\" href=\"?m=condominios&a={$_GET["a"]}&i={$endereco["condominio_id"]}\" style=\"text-decoration:underline\">{$endereco["condominio_nome"]}</a>";
            } elseif ($this->permissoes["condominios"]["ver_dc"]) {
                $endereco_formatado = "<a class=\"txtAzulEscuro11\" href=\"?m=condominios&a=ver_dc&i={$endereco["condominio_id"]}\" style=\"text-decoration:underline\">{$endereco["condominio_nome"]}</a>";
            } else {
                $endereco_formatado = $endereco["condominio_nome"];
            }
            if ($endereco["complemento"]) {
                $endereco_formatado .= ", " . $endereco["complemento"];
                if ($endereco["bloco_nome"] && $endereco["bloco_nome"] != "Único")
                    $endereco_formatado .= " / " . $endereco["bloco_nome"];
            } else {
                if ($endereco["bloco_nome"] && $endereco["bloco_nome"] != "Único")
                    $endereco_formatado .= ", Bloco " . $endereco["bloco_nome"];
            }
        } else {
            if ($endereco["logradouro"])
                $endereco_formatado = $endereco["logradouro"];
            if ($endereco["numero"])
                $endereco_formatado .= ", " . $endereco["numero"];
            if ($endereco["complemento"])
                $endereco_formatado .= ", " . $endereco["complemento"];
            if ($endereco["bairro"])
                $endereco_formatado .= ", " . $endereco["bairro"];
            if ($endereco["cidade"])
                $endereco_formatado .= ", " . $endereco["cidade"];
            if ($endereco["estado_sigla"])
                $endereco_formatado .= ", " . $endereco["estado_sigla"];
        }

        return $endereco_formatado;

    }

    function corrigeNome($nome) {

        $minusculas = array("da", "de", "do", "das", "dos", "e");
        $maiusculas = array("LTDA", "SC", "S/C", "SA", "S/A", "ME");
		
        $nome = str_replace(".", ". ", $nome); // Adiciona espaço depois do ponto de abreviação
        $nome = preg_replace("/[ ]+/", " ", $nome); // Remove espaços seguidos
        $nome = preg_replace("/[']/", "`", $nome);
        $nome = preg_replace("/[^a-zA-Z0-9 áéíóúâêôãõñçÁÉÍÓÚÂÊÔÃÕÑÇ()`&+.!\\-\\[\\]]/", "", $nome); // Remove caracteres estranhos
		
        if (mb_strtoupper($nome, "iso-8859-1") == $nome || mb_strtolower($nome, "iso-8859-1") == $nome) {
            $nome = mb_strtolower($nome, "iso-8859-1"); // Coloca todos os caracteres em minúsculo
            $nome = mb_convert_case($nome, MB_CASE_TITLE, "iso-8859-1"); // Coloca as primeiras letras de cada nome em maiúsculo
        }
		
        for ($i = 0; $i < sizeof($minusculas); $i++) $nome = str_ireplace(" {$minusculas[$i]} ", " {$minusculas[$i]} ", " $nome "); // Substitui termos minúsculos
        for ($i = 0; $i < sizeof($maiusculas); $i++) $nome = str_ireplace(" {$maiusculas[$i]} ", " {$maiusculas[$i]} ", " $nome "); // Substitui termos maiúsculos
		
		$nome = trim($nome);

        return $nome;

    }

    function corrigeNascimento($nascimento) {

        if ($nascimento) {
            $nascimento = explode("/", $nascimento);
            $nascimento = "'{$nascimento[2]}-{$nascimento[1]}-{$nascimento[0]}'";
        } else {
            $nascimento = "NULL";
        }

        return $nascimento;

    }

    function corrigeComplemento($valor, $tipo) {

        if ($valor) {
            $complemento = $tipo . $valor;
        } else {
            $complemento = "o";
        }

        return addslashes($complemento);

    }

    function existeDuplicidadeCPFCNPJ($cliente_id, $cpf_cnpj) {

        $query = $this->bancodedados->query("
            SELECT
                COUNT(cli_id)
            FROM
                tab_clientes
            WHERE
                cli_id != $cliente_id AND
                cli_cpf_cnpj = '$cpf_cnpj'");

        return $this->bancodedados->resultado($query) > 0;

    }

    function existeDuplicidadeEndereco($cliente_id, $bloco, $complemento_tipo, $complemento_valor) {

        if ($complemento_valor) {
            if ($complemento_tipo != "o") {
                $complemento_valor = intval($complemento_valor);
            }
            $complemento_valor = $complemento_tipo . $complemento_valor;
        }

        if (!$bloco || !$complemento_valor) {
            return false;
        }

        $query = $this->bancodedados->query("
            SELECT
                COUNT(cli_id)
            FROM
                tab_clientes
            WHERE
                cob_id = $bloco AND
                cli_complemento = '$complemento_valor' AND
                cli_id != $cliente_id AND
                cls_id != 4");

        return $this->bancodedados->resultado($query);

    }

    function buscaChamados($cliente_id) {

        $chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);

        return $chamados->geraChamadosListagem($chamados->buscaChamados($cliente_id));

    }

    function buscaMensagens($cliente_id) {

        $mensagens = controle::instanciaObjeto("mensagens", $this->bancodedados, $this->permissoes, false);

        return $mensagens->geraMensagensClientesListagem($mensagens->buscaMensagensClientes($cliente_id));

    }

    function ehAlteravelOrigem() {

        return $this->permissoes[$this->dados_modulo["nome"]]["alterar_origem"] && !$_SESSION["origem"];

    }

    function ehBloqueavelFinanceiro() {

        if (!$this->permissoes["clientes"]["bloquear_financeiro"]) return false;
        if ($this->dados_item["status_id"] != 2) return false;
        if (!$this->buscaMensalidadesPendentes($this->dados_item["id"])) return false;
        if ($this->buscaAjusteAtivo($this->dados_item["id"])) return false;

        return true;

    }

    function buscaAlertas($cliente_id, $cpf_cnpj = "", $bloco_id = "", $cep_id = "", $numero = "", $complemento = "") {

        $alertas = array();

        $complemento = preg_replace("/[\r\n \t]+/", "", $complemento);

        while (strlen($complemento) > 0 && $complemento[0] == "0") {
            $complemento = substr($complemento, 1);
        }

        if (($bloco_id && $complemento) || ($cep_id && ($numero || $complemento))) {

            if ($bloco_id) {
                $bloco_id = $bloco_id ? "= $bloco_id" : "IS NULL";
                $cep_id = "IS NULL";
                $numero = "IS NULL";
                $complemento = $complemento ? "REGEXP '^[alsco][0]*{$complemento}$'" : "";
            } else {
                $bloco_id = "IS NULL";
                $cep_id = "= '" . preg_replace("/[^0-9]/", "", $cep_id) . "'";
                $numero = $numero ? "= '$numero'" : "IS NULL";
                $complemento = $complemento ? "REGEXP '^[alsco][0]*".addslashes($complemento)."$'" : "";
            }

			$query = $this->bancodedados->query("
                SELECT
                    tab_clientes.cli_id AS id,
                    tab_clientes.cli_nome AS nome,
                    tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
                    tab_clientes_status.cls_id AS status_id,
                    tab_clientes_status.cls_nome AS status,
                    tab_clientes_origens.clo_nome AS origem,
                    tab_usuarios.usu_id AS responsavel_id,
                    tab_usuarios.usu_nome AS responsavel
                FROM
                    tab_clientes
                    INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
                    INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
                    LEFT OUTER JOIN tab_usuarios ON (tab_clientes.usu_id = tab_usuarios.usu_id)
                WHERE
                    tab_clientes.cob_id $bloco_id AND
                    tab_clientes.cep_id $cep_id AND
                    tab_clientes.cli_numero $numero AND" . ($complemento ? "
                    tab_clientes.cli_complemento $complemento AND" : "") . "
                    tab_clientes.cli_id != $cliente_id".($_SESSION["origem"] ? " AND
					tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
                GROUP BY
                    tab_clientes.cli_id
                ORDER BY
					tab_clientes.cli_nome");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

                $dados["endereco"] = $this->buscaClienteEndereco($dados["id"]);
				
                $alertas["endereco"]["clientes"][$dados["id"]] = $dados;
				
                if ($dados["status_id"] <= 2) {
                    $alertas["bloqueio_endereco_duplicado"] = true;
                }
				
            }

            if (!$alertas && $numero != "IS NULL" && !$complemento) {

                $numero = preg_replace("/[^0-9]/", "", $numero);

				$query = $this->bancodedados->query("
                    SELECT
                        tab_clientes.cli_id AS id,
                        tab_clientes.cli_nome AS nome,
                        tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
                        tab_clientes_status.cls_id AS status_id,
                        tab_clientes_status.cls_nome AS status,
                        tab_clientes_origens.clo_nome AS origem,
                        tab_usuarios.usu_id AS responsavel_id,
                        tab_usuarios.usu_nome AS responsavel
                    FROM
                        tab_clientes
                        INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
                        INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
                        LEFT OUTER JOIN tab_usuarios ON (tab_clientes.usu_id = tab_usuarios.usu_id)
                    WHERE
                        tab_clientes.cob_id IS NULL AND
                        tab_clientes.cep_id $cep_id AND
                        tab_clientes.cli_numero IS NULL AND
                        tab_clientes.cli_complemento LIKE '_$numero' AND
                        tab_clientes.cli_id != $cliente_id".($_SESSION["origem"] ? " AND
						tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
                    GROUP BY
						tab_clientes.cli_id");

				while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

                    $dados["endereco"] = $this->buscaClienteEndereco($dados["id"]);
					
                    $alertas["endereco"]["clientes"][$dados["id"]] = $dados;
					
                    if ($dados["status_id"] <= 2) {
                        $alertas["bloqueio_endereco_duplicado"] = true;
                    }
					
                }

            } elseif (!$alertas && $numero == "IS NULL" && $complemento) {

                $complemento = preg_replace("/[^0-9]/", "", $complemento);
				
				$query = $this->bancodedados->query("
                    SELECT
                        tab_clientes.cli_id AS id,
                        tab_clientes.cli_nome AS nome,
                        tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
                        tab_clientes_status.cls_id AS status_id,
                        tab_clientes_status.cls_nome AS status,
                        tab_clientes_origens.clo_nome AS origem,
                        tab_usuarios.usu_id AS responsavel_id,
                        tab_usuarios.usu_nome AS responsavel
                    FROM
                        tab_clientes
                        INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
                        INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
                        LEFT OUTER JOIN tab_usuarios ON (tab_clientes.usu_id = tab_usuarios.usu_id)
                    WHERE
                        tab_clientes.cob_id IS NULL AND
                        tab_clientes.cep_id $cep_id AND
                        tab_clientes.cli_numero = $complemento AND
                        tab_clientes.cli_complemento LIKE 'o' AND
                        tab_clientes.cli_id != $cliente_id".($_SESSION["origem"] ? " AND
						tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
                    GROUP BY
						tab_clientes.cli_id");

				while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

                    $dados["endereco"] = $this->buscaClienteEndereco($dados["id"]);
					
                    $alertas["endereco"]["clientes"][$dados["id"]] = $dados;
					
                    if ($dados["status_id"] <= 2) {
                        $alertas["bloqueio_endereco_duplicado"] = true;
                    }
					
                }

            }

        }

        // Alertas CPF/CNPJ
        if ($cpf_cnpj) {

			$query = $this->bancodedados->query("
                SELECT
                    tab_clientes.cli_id AS id,
                    tab_clientes.cli_nome AS nome,
                    tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
                    tab_clientes_status.cls_id AS status_id,
                    tab_clientes_status.cls_nome AS status,
                    tab_clientes_origens.clo_nome AS origem,
                    tab_usuarios.usu_id AS responsavel_id,
                    tab_usuarios.usu_nome AS responsavel,
                    tab_clientes_responsabilidades.cod_id AS condominio_responsabilidade_id
                FROM
                    tab_clientes
                    INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
                    INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
                    LEFT OUTER JOIN tab_usuarios ON (tab_clientes.usu_id = tab_usuarios.usu_id)
                    LEFT OUTER JOIN tab_clientes_responsabilidades ON (tab_clientes.cli_id = tab_clientes_responsabilidades.cli_id AND tab_clientes_responsabilidades.cra_id = 4 AND tab_clientes_responsabilidades.cod_id IS NOT NULL)
                WHERE
                    tab_clientes.cli_cpf_cnpj = '$cpf_cnpj' AND
                    tab_clientes.cli_id != $cliente_id".($_SESSION["origem"] ? " AND
					tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
                GROUP BY
					tab_clientes.cli_id");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

                if ($dados["condominio_responsabilidade_id"]) {
                    $dados["responsavel_id"] = null;
                }
				
                $dados["endereco"] = $this->buscaClienteEndereco($dados["id"]);
				
                if ($alertas["endereco"]["clientes"][$dados["id"]]) {
                    $alertas["cpf_cnpj_endereco"]["clientes"][$dados["id"]] = $alertas["endereco"]["clientes"][$dados["id"]];
                    unset($alertas["endereco"]["clientes"][$dados["id"]]);
                } else {
                    $alertas["cpf_cnpj"]["clientes"][$dados["id"]] = $dados;
                }
				
				if (($dados["status_id"] != 2 || $this->buscaMensalidadesPendentes($dados["id"])) && !$this->permissoes["clientes"]["duplicar_cpf_cnpj"]) {
                    $alertas["bloqueio"] = true;
                }
				
                $alertas["bloqueio_cpf_cnpj_duplicado"] = true;
				
            }

        }

        return $alertas;

    }

    function geraMensagemHtml($alertas) {

        if ($alertas) {

            $mensagens = array();

            foreach ($alertas as $tipo => $mensagens_alerta) {
                if ($mensagens_alerta["clientes"] && $tipo != "bloqueio") {
                    if ($tipo == "cpf_cnpj") {
                        $mensagens[] = "<b>Cliente(s) cadastrado(s) no mesmo CPF/CNPJ:</b>";
                    } elseif ($tipo == "endereco") {
                        $mensagens[] = "<b>Cliente(s) cadastrado(s) no mesmo Endereço:</b>";
                    } else {
                        $mensagens[] = "<b>Cliente(s) cadastrado(s) no mesmo Endereço e CPF/CNPJ:</b>";
                    }
                    foreach ($mensagens_alerta["clientes"] as $mensagem_alerta) {
                        $dados_cliente = array();
                        $dados_cliente[] = "<b>[{$mensagem_alerta["id"]}] <a href=\"?m=clientes&amp;a=ver_dc&amp;i={$mensagem_alerta["id"]}\" class=\"txtAzulEscuro11\">{$mensagem_alerta["nome"]}</a></b> ({$mensagem_alerta["origem"]}) - {$mensagem_alerta["status"]}";
                        if ($tipo == "cpf_cnpj") {
                            $dados_cliente[] = "<b>&nbsp;» Endereço</b>: {$mensagem_alerta["endereco"]}";
                        } elseif ($tipo == "endereco") {
                            $dados_cliente[] = "<b>&nbsp;» CPF/CNPJ</b>: {$mensagem_alerta["cpf_cnpj"]}";
                        }
                        if ($mensagem_alerta["responsavel"]) {
                            $dados_cliente[] = "<b>&nbsp;» Responsável</b>: {$mensagem_alerta["responsavel"]}";
                        }
                        $dados_cliente[] = "<b>&nbsp;» Links</b>: " . $this->geraLinks($mensagem_alerta["id"], false);
                        $mensagens[] = implode("<br>", $dados_cliente);
                    }
                }
            }

            return str_replace("'", '"', implode("<br><br>", $mensagens));

        }

    }

    function geraMensagemTexto($alertas) {

        $mensagens = array();

        foreach ($alertas as $tipo => $mensagens_alerta) {
            if ($mensagens_alerta["clientes"] && $tipo != "bloqueio") {
                if ($tipo == "cpf_cnpj") {
                    $mensagens[] = "Cliente(s) cadastrado(s) com o CPF/CPNPJ {$mensagens_alerta["cpf_cnpj"]}:";
                } elseif ($tipo == "endereco") {
                    $mensagens[] = "Cliente(s) cadastrado(s) no endereço {$mensagens_alerta["endereco"]}:";
                } else {
                    $mensagens[] = "Cliente(s) cadastrado(s) no endereço {$mensagens_alerta["endereco"]} e com o CPF/CPNPJ {$mensagens_alerta["cpf_cpnpj"]}:";
                }
                foreach ($mensagens_alerta["clientes"] as $mensagem_alerta) {
                    $dados_cliente = array();
                    $dados_cliente[] = "[{$mensagem_alerta["id"]}] {$mensagem_alerta["nome"]} ({$mensagem_alerta["origem"]}) - {$mensagem_alerta["status"]}";
                    if ($tipo == "cpf_cnpj") {
                        $dados_cliente[] = "» Endereço: {$mensagem_alerta["endereco"]}";
                    } elseif ($tipo == "endereco") {
                        $dados_cliente[] = "» CPF/CNPJ: {$mensagem_alerta["cpf_cnpj"]}";
                    }
                    if ($mensagem_alerta["responsavel"]) {
                        $dados_cliente[] = "» Responsável: {$mensagem_alerta["responsavel"]}";
                    }
                    $mensagens[] = implode("\\n", $dados_cliente);
                }
            }
        }

        return implode("\\n\\n", $mensagens);

    }

    function buscaClienteEndereco($cliente_id, $completo = false) {

        $query = $this->bancodedados->query("
            SELECT
                SUBSTRING(tab_clientes.cli_complemento, 1, 1) AS complemento_tipo_id,
                SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento_valor,
                tab_condominios.con_nome AS condominio,
                tab_condominios_blocos.cob_nome AS bloco,
                tab_estados.est_sigla AS estado,
                tab_cidades.cid_nome AS cidade,
                tab_logradouros.log_nome AS logradouro,
                tab_clientes.cli_numero AS numero
            FROM
                tab_clientes
                LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
                LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
                INNER JOIN tab_ceps ON (IF(tab_condominios_blocos.cep_id IS NULL, tab_clientes.cep_id, tab_condominios_blocos.cep_id) = tab_ceps.cep_id)
                INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
                INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
                INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
                INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
            WHERE
                tab_clientes.cli_id = $cliente_id");

        if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

            $endereco = array();

            if ($dados["condominio"] && !$completo) {
                if ($dados["condominio"]) $endereco[] = "Condomínio {$dados["condominio"]}";
                if ($dados["bloco"] && $dados["bloco"] != "Único") $endereco[] = "Bloco {$dados["bloco"]}";
                if ($dados["complemento_valor"]) $endereco[] = $this->buscaComplementoTipos($dados["complemento_tipo_id"], true) . $dados["complemento_valor"];
            } else {
                if ($dados["logradouro"]) $endereco[] = $dados["logradouro"];
                if ($dados["numero"]) $endereco[] = $dados["numero"];
                if ($dados["condominio"]) $endereco[] = "Cond. {$dados["condominio"]}";
                if ($dados["bloco"] && $dados["bloco"] != "Único") $endereco[] = "Bloco {$dados["bloco"]}";
                if ($dados["complemento_valor"]) $endereco[] = $this->buscaComplementoTipos($dados["complemento_tipo_id"], true) . $dados["complemento_valor"];
                if ($dados["cidade"]) $endereco[] = $dados["cidade"];
                if ($dados["estado"]) $endereco[] = $dados["estado"];
            }

            return implode(", ", $endereco);
            
        }

    }

    #################
    ### Adicionar ###
    #################

    function adicionar() {

        if (!$_POST || !servidor::getGet("c")) {

            $contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);

            $id = $this->geraIdProspect();

            $botoes = array();
            $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
            $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

            $situacoes = $this->buscaSituacoes();

            unset($situacoes["4"], $situacoes["5"], $situacoes["6"]);

            $this->conteudo .= $this->carregaArquivo("clientes.adicionar.inc.php");

			$this->conteudo = str_replace("[id]", $id, $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar&c=$id", $this->conteudo);
			$this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaTipos()), $this->conteudo);
			$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios()), $this->conteudo);
			$this->conteudo = str_replace("[responsavel]", $this->geraSelect($this->buscaResponsaveis(true, null, $this->permissoes["clientes_responsabilidades"]["aprovar"] || !$this->permissoes["clientes_responsabilidades"]["assumir_responsabilidade"] ? null : $_SESSION["usuario"]), $_SESSION["usuario"], true), $this->conteudo);
			$this->conteudo = str_replace("[listagem_telefones]", $contatos->geraContatosTelefonesListagem($contatos->buscaContatosTelefones($_GET["c"]), true), $this->conteudo);
			$this->conteudo = str_replace("[lista_telefones]", "", $this->conteudo);
			$this->conteudo = str_replace("[listagem_emails]", $contatos->geraContatosEmailsListagem($contatos->buscaContatosEmails($_GET["c"]), true), $this->conteudo);
			$this->conteudo = str_replace("[lista_emails]", "", $this->conteudo);
			$this->conteudo = str_replace("[complemento_tipo]", $this->geraSelect($this->buscaComplementoTipos()), $this->conteudo);
			$this->conteudo = str_replace("[cobranca_vencimento]", $this->geraSelect($this->buscaCobrancaVencimentos($this->dados_item["origem_id"], $this->dados_item["cobranca_vencimento"]), 8), $this->conteudo);
			//TODO retirar campo cobranca_formato, deve ser definido automaticamente
			$this->conteudo = str_replace("[cobranca_formato]", $this->geraSelect($this->buscaCobrancaFormatos("1", false, null, 4), "{$this->dados_item["cobranca_formato_tipo_id"]},{$this->dados_item["cobranca_formato_id"]}", $this->dados_item["origem_id"]), $this->conteudo);
			$this->conteudo = str_replace("[retorno_periodo]", $this->geraSelect($this->buscaRetornosPeriodos()), $this->conteudo);
			$this->conteudo = str_replace("[assinaturas]", $this->buscaAssinaturasCliente($id), $this->conteudo);
			//$this->conteudo = str_replace("[assinaturas_adicionar]", ($this->permissoes["assinaturas"]["adicionar"]) ? '<input type="button" class="buttonAzulEscuroBranco11" name="assinaturas_adicionar" value="Adicionar" onClick="adicionarAssinatura(' . $id . ')">' : "", $this->conteudo);
			$this->conteudo = str_replace("[assinaturas_adicionar]", "", $this->conteudo);
			$this->conteudo = str_replace("[duplicar_endereco]", $this->permissoes["clientes"]["duplicar_endereco"] ? 1 : 0, $this->conteudo);
			$this->conteudo = str_replace("[duplicar_cpf_cnpj]", $this->permissoes["clientes"]["duplicar_cpf_cnpj"] ? 1 : 0, $this->conteudo);
			$this->conteudo = str_replace("[pesquisar_cep]", $this->permissoes["ceps"]["pesquisar"] ? "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"pesquisar_cep\" id=\"pesquisar_cep\" value=\"Pesquisar\" onClick=\"pesquisarCEP()\" />" : "", $this->conteudo);
			$this->conteudo = str_replace("[adicionar_cep]", $this->permissoes["ceps"]["adicionar"] ? "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"adicionar_cep\" id=\"adicionar_cep\" value=\"Adicionar\" onClick=\"adicionarCEP()\" />" : "", $this->conteudo);
			$this->conteudo = str_replace("[situacao]", $this->geraSelect($situacoes), $this->conteudo);
            $this->conteudo = str_replace('[consultor]', $this->geraSelect($this->buscaConsultores()), $this->conteudo);
			$this->conteudo = str_replace("[indicacao]", $this->geraSelect($this->buscaIndicacoes()), $this->conteudo);
			$this->conteudo = str_replace("[operadora_origem]", $this->geraSelect($this->buscaOperadorasOrigens()), $this->conteudo);
			$this->conteudo = str_replace("[origem]", $this->geraSelect($this->buscaOrigens(), "4", true), $this->conteudo);
            $this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Adicionar&nbsp;</b></td>', $this->conteudo);
            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

        } else {

            if ($_POST["complemento_tipo"] != "o") {
                $_POST["complemento_valor"] = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST["complemento_valor"]);
            }

            $alertas = $this->buscaAlertas(servidor::getGet("c"), $_POST["cpf_cnpj"], $_POST["bloco"], $_POST["cep"], $_POST["numero"], $_POST["complemento_valor"]);

            if (($this->permissoes["clientes"]["duplicar_endereco"] || !$alertas["bloqueio_endereco_duplicado"]) && ($this->permissoes["clientes"]["duplicar_cpf_cnpj"] || !$alertas["bloqueio_cpf_cnpj_duplicado"])) {

            	// $_POST["origem"] = $this->buscaOrigem(preg_replace("/[^0-9]/", "", $_POST["cep"]), $_POST["condominio"]);
            	$_POST["origem"] = $_SESSION["origem"];
            	$_POST["cobranca_formato"] = $this->buscaCobrancaFormatoPorOrigem(3, $_POST["origem"]);
                $_POST["responsavel"] = $_POST["responsavel"] ? $_POST["responsavel"] : ($_POST["responsavel_duplicado"] ? $_POST["responsavel_duplicado"] : "NULL");
                $_POST["nome"] = addslashes($this->corrigeNome($_POST["nome"]));
                $_POST["nascimento"] = $this->corrigeNascimento($_POST["nascimento"]);
                $_POST["bloco"] = $_POST["bloco"] ? $_POST["bloco"] : "NULL";
                $_POST["consultor"] = $_POST["consultor"] ? $_POST["consultor"] : "NULL";
                $_POST["condominio"] = $_POST["condominio"] ? $_POST["condominio"] : "NULL";
                $_POST["situacao"] = $_POST["situacao"] ? $_POST["situacao"] : "NULL";
                $_POST["indicacao"] = $_POST["indicacao"] ? $_POST["indicacao"] : "NULL";
                $_POST["operadora_origem"] = $_POST["operadora_origem"] ? $_POST["operadora_origem"] : "NULL";
                $_POST["cep"] = $_POST["cep"] ? "'" . preg_replace("/[^0-9]/", "", $_POST["cep"]) . "'" : "NULL";
                $_POST["numero"] = intval(preg_replace("/[^0-9]/", "", $_POST["numero"]));
                $_POST["numero"] = $_POST["numero"] ? $_POST["numero"] : "NULL";
				$_POST["ponto_referencia"] = addslashes($_POST["ponto_referencia"]);

                $complemento = $this->corrigeComplemento($_POST["complemento_valor"], $_POST["complemento_tipo"]);

                if ($_POST["responsavel"] != "NULL") {
                    $responsabilidades_condominio = $this->buscaResponsabilidadeCondominio($_POST["condominio"]);
                    if ($responsabilidades_condominio) {
                        // Registrar Responsáveis Pelo Condomínio
                        $usuario = $this->buscaDadosUsuario($_SESSION["usuario"]);
                        foreach ($responsabilidades_condominio as $responsabilidade) {
                            // Verifica se a responsabilidade está num prazo vigente
                            if ($responsabilidade["atual"]) {
                                $_POST["responsavel"] = $responsabilidade["responsavel"];
                                if ($responsabilidade["tipo"] == 2) {
                                    $tem_responsabilidade_atual = 1;
                                }
                                $id = $this->registraResponsabilidade(servidor::getGet("c"), $responsabilidade["inicio"], $responsabilidade["termino"], $responsabilidade["parametros"], $responsabilidade["responsavel"], null, 'NULL', $responsabilidade["tipo"], 4);
                                $this->registraComentarioResponsabilidade($id, "Adicionado com Auto-Ativação (Herdado do Condomínio)");
                            } else {
                                if (!$tem_responsabilidade_atual)
                                    $_POST["responsavel"] = "NULL";
                                $id = $this->registraResponsabilidade(servidor::getGet("c"), $responsabilidade["inicio"], $responsabilidade["termino"], $responsabilidade["parametros"], $responsabilidade["responsavel"], $responsabilidade["id"], 'NULL', $responsabilidade["tipo"], 2);
                                $this->registraComentarioResponsabilidade($id, "Adicionado com Auto-Aprovação (Herdado do Condomínio)");
                            }
                            if ($responsabilidade["tipo"] == 2) {
                                $this->registraRetorno($responsabilidade["responsavel"], $responsabilidade["inicio"], "", "Entrar em contato (Cadastrado por {$usuario["nome"]})", 2);
                            }
                        }
                    } elseif ($this->temPermissao($_POST["responsavel"], "clientes_responsabilidades", "assumir_responsabilidade") == 2) {
                        // Registrar Responsável Por Ter Permissão
                        $dados = $this->buscaDadosResponsabilidade(1);
                        $id = $this->registraResponsabilidade(servidor::getGet("c"), "NOW()", $dados["termino"], $dados["parametros"], $_POST["responsavel"], null, $dados["categoria"], 2, 4);
                        $this->registraComentarioResponsabilidade($id, "Adicionado com Auto-Ativação");
                        if ($_SESSION["usuario"] != $_POST["responsavel"]) {
                            $usuario = $this->buscaDadosUsuario($_SESSION["usuario"]);
                            $this->registraRetorno($_POST["responsavel"], "NOW()", "", "Entrar em contato (Cadastrado por {$usuario["nome"]})", 2);
                        }
                    } else {
                        // Registrar Indicação e Retornos Para Supervisores
                        $dados = $this->buscaDadosResponsabilidade(1);
                        $id = $this->registraResponsabilidade(servidor::getGet("c"), "NOW()", $dados["termino"], $dados["parametros"], $_POST["responsavel"], null, $dados["categoria"], 1, 4);
                        $this->registraComentarioResponsabilidade($id, "Adicionado com Auto-Ativação");
                        // Enviar retorno para todos os supervisores
                        $supervisores = $this->buscaSupervisoresComerciais();
                        if ($supervisores) {
                            $usuario = $this->buscaDadosUsuario($_SESSION["usuario"]);
                            for ($i = 0; $i < sizeof($supervisores); $i++) {
                                $this->registraRetorno($supervisores[$i], "NOW()", "", "Entrar em contato (Cadastrado por {$usuario["nome"]})", 2);
                            }
                        }
                        $_POST["responsavel"] = "NULL";
                    }
                    // Registra Retorno
                    if ($_POST["retorno_data"] && $_POST["retorno_comentario"] && $this->temPermissao($_POST["responsavel"], "clientes_responsabilidades", "assumir_responsabilidade")) {
                        $_POST["retorno_data"] = substr($_POST["retorno_data"], 6, 4) . "-" . substr($_POST["retorno_data"], 3, 2) . "-" . substr($_POST["retorno_data"], 0, 2);
                        $this->registraRetorno($_POST["responsavel"], $_POST["retorno_data"], $_POST["retorno_periodo"], $_POST["retorno_comentario"], 1);
                    }
                }

                $_POST["inscricao_estadual"] = $_POST["inscricao_estadual"] ? "'{$_POST["inscricao_estadual"]}'" : ($_POST["isento_inscricao_estadual"] ? "-1" : "NULL");
                
                // Atualiza Contatos (Telefones e Emails)
                $contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);
                $contatos->atualizaContatoTelefones(servidor::getGet("c"), unserialize(stripslashes($_POST["lista_telefones"])));
                $contatos->atualizaContatoEmails(servidor::getGet("c"), unserialize(stripslashes($_POST["lista_emails"])));

                // Atualiza Dados Cliente
                $this->bancodedados->query("
					UPDATE 
						tab_clientes 
					SET 
						cli_nome = '{$_POST["nome"]}', 
						cli_data_cadastro = NOW(), 
						cli_cpf_cnpj = '{$_POST["cpf_cnpj"]}', 
						cli_inscricao_estadual = {$_POST["inscricao_estadual"]}, 
						cli_nascimento = {$_POST["nascimento"]}, 
						cep_id = {$_POST["cep"]}, 
						cli_numero = {$_POST["numero"]}, 
						con_id = {$_POST["condominio"]}, 
						cob_id = {$_POST["bloco"]}, 
						cli_complemento = '$complemento', 
						cli_ponto_referencia = '{$_POST["ponto_referencia"]}',
						cli_contato = '{$_POST["contato"]}', 
						cli_comentarios = '{$_POST["comentarios"]}', 
						clu_id = {$_POST["situacao"]}, 
						clo_id = {$_POST["origem"]}, 
						clt_id = {$_POST["tipo"]}, 
						usu_id = {$_POST["responsavel"]}, 
						cli_ccd_vencimento = {$_POST["cobranca_vencimento"]}, 
						cof_id = {$_POST["cobranca_formato"]}, 
						cld_id = {$_POST["indicacao"]}, 
						coo_id = {$_POST["operadora_origem"]} ,
                        consultor = {$_POST['consultor']}
					WHERE 
						cli_id = {$_GET["c"]}");
				
                $this->atualizaStatusCliente(servidor::getGet("c"));

                // Registra Atividade
                $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
                $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, servidor::getGet("c"));

                // Redireciona Página
                header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["c"]}&n={$_GET["n"]}");
                exit();

            } else {
                header("Location: ./?m={$this->dados_modulo["nome"]}&a=adicionar");
                exit();
            }

        }

    }

    ###############################
    ### Atualiza Status Cliente ###
    ###############################

    function buscaOrigem($cep_id, $condominio_id) {

        if ($cep_id) {

            $query = "
                SELECT
                    tab_cidades.clo_id
                FROM
                    tab_cidades
                    INNER JOIN tab_ceps ON (tab_cidades.cid_id = tab_ceps.cid_id)
                WHERE
                    tab_ceps.cep_id = $cep_id";
            $query = $this->bancodedados->query($query);

            $origem_id = $this->bancodedados->resultado($query);

        } else if ($condominio_id) {

            $query = "
                SELECT
                    tab_cidades.clo_id
                FROM
                    tab_cidades
                    INNER JOIN tab_ceps ON (tab_cidades.cid_id = tab_ceps.cid_id)
                    INNER JOIN tab_condominios ON (tab_condominios.cep_id = tab_ceps.cep_id)
                WHERE
                    tab_condominios.con_id = $condominio_id";
            $query = $this->bancodedados->query($query);

            $origem_id = $this->bancodedados->resultado($query);

        }

        return $origem_id ? $origem_id : 0;

    }

    function atualizaStatusCliente($cliente_id) {

        $status_atual_id = $this->buscaStatusCliente($cliente_id);
        $assinaturas_status = $this->buscaAssinaturasStatus($cliente_id);

        if ($assinaturas_status) {
            if ($assinaturas_status[2] || $assinaturas_status[5]) {
                $status_id = 2; // Status Ativo
            } elseif ($assinaturas_status[1]) {
                $status_id = 1; // Status Prospect
            } elseif ($assinaturas_status[3]) {
                $status_id = 3; // Status Cancelado
            } elseif ($assinaturas_status[6]) {
                $status_id = 4; // Status Malling
            }
        } else {
            $status_id = 4; // Status Mailing
        }

        if ($status_id != $status_atual_id) {
            $this->bancodedados->query("
            	UPDATE 
            		tab_clientes 
            	SET 
            		cls_id = $status_id 
            	WHERE 
            		cli_id = $cliente_id");
        }

        return $status_id;

    }

    function buscaStatusCliente($cliente_id, $nome = false) {

        $query = $this->bancodedados->query("
            SELECT
                tab_clientes_status.cls_id AS id,
                tab_clientes_status.cls_nome AS nome
            FROM
                tab_clientes
                INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
            WHERE
                tab_clientes.cli_id = $cliente_id");

        return $this->bancodedados->resultado($query, 0, $nome ? "nome" : "id");

    }

    function buscaAssinaturasStatus($cliente_id) {

        $assinaturas_status = array();

        $query = $this->bancodedados->query("
            SELECT
                ast_id AS status_id,
                COUNT(ass_id) AS quantidade
            FROM
                tab_assinaturas
            WHERE
                cli_id = $cliente_id
            GROUP BY
                ast_id");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $assinaturas_status[$dados["status_id"]] = $dados["quantidade"];
        }

        return $assinaturas_status;

    }

    function buscaResponsabilidadeCondominio($condominio_id) {

        $responsabilidades = array();

        $query = $this->bancodedados->query("
            SELECT
                cod_id AS id,
                IF(cod_inicio < NOW(), NOW(), cod_inicio) AS inicio,
                cod_parametros AS parametros,
                usu_id AS responsavel,
                crp_id AS tipo,
                IF((NOW() BETWEEN cod_inicio AND cod_termino) AND (crp_id = 2), 1, 0) AS atual
            FROM
                tab_condominios_responsabilidades
            WHERE
                cod_termino >= NOW() AND
                cru_id IN (2, 4) AND
                con_id = $condominio_id
            ORDER BY
                atual DESC");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

            $dados["parametros"] = unserialize($dados["parametros"]);
            $dados["termino"] = date("Y-m-d H:i:s", strtotime("{$dados["inicio"]} + {$dados["parametros"]["prazo"]} days"));

            $responsabilidades[] = $dados;

        }

        return $responsabilidades;

    }

    function buscaResponsavelCondominio($condominio_id) {

        $query = $this->bancodedados->query("
            SELECT
                cod_id AS id,
                cod_inicio AS inicio,
                cod_termino AS termino,
                usu_id AS responsavel_id
            FROM
                tab_condominios_responsabilidades
            WHERE
                cru_id = 4 AND
                crp_id = 2 AND
                con_id = $condominio_id");

        return $this->bancodedados->resultadoArray($query);

    }

    function registraResponsabilidade($cliente, $inicio, $termino, $parametros, $responsavel, $pai, $categoria, $tipo, $status) {

        if (!$pai) $pai = "NULL";
        if ($inicio != 'NOW()') $inicio = "'$inicio'";
        if ($termino != 'NOW()') $termino = "'$termino'";

        $parametros = serialize($parametros);

        $this->bancodedados->query("
        	INSERT INTO 
        		tab_clientes_responsabilidades 
        	SET
        		cli_id = $cliente, 
        		cln_inicio = $inicio, 
        		cln_termino = $termino, 
        		cln_parametros = '$parametros', 
        		usu_id = $responsavel, 
        		cod_id = $pai, 
        		crt_id = $categoria, 
        		cri_id = $tipo, 
        		cra_id = $status");

        return $this->bancodedados->ultimoIdInserido();

    }

    function registraComentarioResponsabilidade($id, $comentario) {

        $this->bancodedados->query("
        	INSERT INTO 
        		tab_clientes_responsabilidades_comentarios 
        	SET
        		crc_datahora = NOW(), 
        		cln_id = '$id', 
        		crc_conteudo = '<b>Ação:</b> $comentario'");

    }

    function registraRetorno($responsavel, $data, $periodo, $comentario, $tipo) {

        $comentario = addslashes(trim($comentario));
        $periodo = ($periodo) ? "'$periodo'" : "NULL";
        if ($data != 'NOW()') $data = "'$data'";

        $this->bancodedados->query("
        	INSERT INTO 
        		tab_clientes_retornos 
        	SET
        		cli_id = {$_GET["c"]}, 
        		usu_id = $responsavel, 
        		clr_abertura = NOW(), 
        		clr_data = $data, 
        		cee_id = $periodo, 
        		clr_comentario = '$comentario', 
        		cet_id = $tipo, 
        		ces_id = 1");

    }

    function geraIdProspect() {

        $query = $this->bancodedados->query("
        	SELECT 
        		cli_id
        	FROM 
        		tab_clientes 
        	WHERE 
        		usu_id = '{$_SESSION["usuario"]}' AND 
        		cls_id IS NULL");

        if (($cliente_id = $this->bancodedados->resultado($query)) != false) {

            $query = $this->bancodedados->query("
            	SELECT 
            		ass_id AS id 
            	FROM 
            		tab_assinaturas 
            	WHERE 
            		cli_id = {$cliente_id}");

            while (($assinatura = $this->bancodedados->resultadoArray($query)) != false) {
            	
                $query_adicional = $this->bancodedados->query("
                	SELECT 
                		ass_id AS id 
                	FROM 
                		tab_assinaturas 
                	WHERE 
                		ass_pai = {$assinatura["id"]}");
                
                while (($adicional = $this->bancodedados->resultado($query_adicional)) != false) {
                    
                    $this->bancodedados->query("DELETE FROM tab_descontos_concessoes WHERE ass_id = {$adicional["id"]}");
                    $this->bancodedados->query("DELETE FROM tab_dominios WHERE ass_id = {$adicional["id"]}");
                    $this->bancodedados->query("DELETE FROM tab_assinaturas_vinculos WHERE ass_id = {$adicional["id"]} OR ass_filha = {$adicional["id"]}");
                    $this->bancodedados->query("DELETE FROM tab_assinaturas WHERE ass_id = {$adicional["id"]}");

                }

                $this->bancodedados->query("DELETE FROM tab_descontos_concessoes WHERE ass_id = {$assinatura["id"]}");
                $this->bancodedados->query("DELETE FROM tab_dominios WHERE ass_id = {$assinatura["id"]}");
                $this->bancodedados->query("DELETE FROM tab_assinaturas_vinculos WHERE ass_id = {$assinatura["id"]} OR ass_filha = {$assinatura["id"]}");
                $this->bancodedados->query("DELETE FROM tab_assinaturas WHERE ass_id = {$assinatura["id"]}");

            }

            $this->bancodedados->query("DELETE FROM tab_assinaturas WHERE cli_id = $cliente_id");

        } else {

            $this->bancodedados->query("
            	INSERT INTO 
            		tab_clientes 
            	SET
            		usu_id = {$_SESSION["usuario"]}");
            		
            $cliente_id = $this->bancodedados->ultimoIdInserido();

        }

        return $cliente_id;

    }

    ###################
    ### Busca Acoes ###
    ###################

    function buscaAcoes($cliente_id) {

        $acoes = array();

        $acoes[] = $this->geraAcao("ver_dc", "Dados Cadastrais", $cliente_id);
        $acoes[] = $this->geraAcao("ver_df", "Dados Financeiros", $cliente_id);
        $acoes[] = $this->geraAcao("ver_dm", "Dados Comerciais", $cliente_id);
        $acoes[] = $this->geraAcao("ver_dt", "Dados Técnicos", $cliente_id);
        $acoes[] = $this->geraAcao("ver_da", "Dados Administrativos", $cliente_id);
        $acoes[] = $this->geraAcao("ver_ch", "Chamados", $cliente_id);
        $acoes[] = $this->geraAcao("ver_me", "Mensagens", $cliente_id);

        return implode($acoes);

    }

    function geraAcao($modulo, $titulo, $cliente_id) {

        if (!$this->permissoes["clientes"][$modulo]) {
            return "";
        } elseif ($this->acao == $modulo || str_replace("editar_", "ver_", $this->acao) == $modulo) {
            return "<td bgcolor=\"#505050\"><b>&nbsp;{$titulo}&nbsp;</b></td>";
        } else {
            return "<td bgcolor=\"#828282\"><b>&nbsp;<a href=\"?m=clientes&a={$modulo}&i={$cliente_id}\" class=\"txtBranco11\">{$titulo}</a>&nbsp;</b></td>";
        }

    }

    ############################
    ### Ver Dados Cadastrais ###
    ############################

    function ver_dc() {

        $this->buscaDadosItem(servidor::getGet("i"));

        if ($this->dados_item) {

            if (servidor::getGet("imprimir_endereco")) {
                $this->imprimirEndereco();
                die();
            }

            $contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);
            $dados_pendentes = $this->buscaDadosPendentes($this->dados_item);

            $alertas = $this->buscaAlertas($this->dados_item["id"], $this->dados_item["cpf_cnpj"], $this->dados_item["bloco_id"], $this->dados_item["cep"], $this->dados_item["numero"], $this->dados_item["complemento_valor"]);

            $botoes = array();
            if ($this->permissoes["clientes"]["editar_dc"]) $botoes[] = '<input type="button" name="editar_dc" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar_dc&i=' . $this->dados_item["id"] . '\';">';
            if ($this->dados_item["status_id"] == 4 && $this->permissoes["clientes"]["remover"] && (!$this->dados_item["responsavel_id"] || $this->dados_item["responsavel_id"] == $_SESSION["usuario"] || $this->permissoes["clientes"]["mudar_responsavel"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
            if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"]) $botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
            if ($this->permissoes[$this->dados_modulo["nome"]]["migrar_residencial"] && $this->dados_item["condominio_id"] && ($this->dados_item["status_id"] == 1 || $this->permissoes[$this->dados_modulo["nome"]]["migrar_residencial_plus"])) $botoes[] = '<input type="button" name="migrar_residencial" class="buttonAzulEscuroBranco11" value="Migrar para Resindencia" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar_residencial&i=' . $this->dados_item["id"] . '\';">';

            $this->conteudo .= $this->carregaArquivo("clientes.ver_dc.inc.php");

			$this->conteudo = str_replace("[alertas]", $this->geraMensagemHtml($alertas), $this->conteudo);
			$this->conteudo = str_replace("[alerta_pendencias]", $this->geraAlertaPendencias($dados_pendentes), $this->conteudo);
			$this->conteudo = str_replace("[id]", $this->dados_item["id"], $this->conteudo);
			$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace("[nome_label]", $this->dados_item["tipo_id"] == 2 ? "Razão Social" : "Nome", $this->conteudo);
			$this->conteudo = str_replace("[nome_fantasia_visivel]", $this->dados_item["tipo_id"] == 2 ? "table-row" : "none", $this->conteudo);
			$this->conteudo = str_replace("[nome_fantasia]", $this->dados_item["nome_fantasia"], $this->conteudo);
			$this->conteudo = str_replace("[situacao_financeira]", $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
			$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
			$this->conteudo = str_replace("[cpf_cnpj_label]", ($this->dados_item["tipo_id"] == 2) ? "CNPJ" : "CPF", $this->conteudo);
			$this->conteudo = str_replace("[cpf_cnpj]", $this->dados_item["cpf_cnpj"], $this->conteudo);
			$this->conteudo = str_replace("[inscricao_estadual]", $this->dados_item["inscricao_estadual"], $this->conteudo);
			$this->conteudo = str_replace("[nascimento]", $this->dados_item["nascimento"], $this->conteudo);
			$this->conteudo = str_replace("[condominio]", $this->dados_item["condominio_nome"], $this->conteudo);
			$this->conteudo = str_replace("[bloco]", $this->dados_item["bloco_nome"], $this->conteudo);
			$this->conteudo = str_replace("[cep]", $this->dados_item["cep"], $this->conteudo);
			$this->conteudo = str_replace("[estado]", $this->dados_item["estado"], $this->conteudo);
			$this->conteudo = str_replace("[cidade]", $this->dados_item["cidade"], $this->conteudo);
			$this->conteudo = str_replace("[bairro]", $this->dados_item["bairro"], $this->conteudo);
			$this->conteudo = str_replace("[logradouro]", $this->dados_item["logradouro"], $this->conteudo);
			$this->conteudo = str_replace("[numero]", $this->dados_item["numero"], $this->conteudo);
			$this->conteudo = str_replace("[complemento]", $this->dados_item["complemento"], $this->conteudo);
			$this->conteudo = str_replace("[ponto_referencia]", $this->dados_item["ponto_referencia"], $this->conteudo);
			$this->conteudo = str_replace("[contato]", $this->dados_item["contato"], $this->conteudo);
			$this->conteudo = str_replace("[listagem_telefones]", $contatos->geraContatosTelefonesListagem($contatos->buscaContatosTelefones($_GET["i"]), false), $this->conteudo);
			$this->conteudo = str_replace("[listagem_emails]", $contatos->geraContatosEmailsListagem($contatos->buscaContatosEmails($_GET["i"]), false), $this->conteudo);
			$this->conteudo = str_replace("[comentarios]", nl2br($this->dados_item["comentarios"]), $this->conteudo);
			$this->conteudo = str_replace("[data_cadastro]", $this->dados_item["data_cadastro"], $this->conteudo);
			$this->conteudo = str_replace("[indicacao]", $this->dados_item["indicacao_nome"], $this->conteudo);
			$this->conteudo = str_replace("[operadora_origem]", $this->dados_item["operadora_origem_nome"], $this->conteudo);
			$this->conteudo = str_replace("[situacao]", $this->dados_item["situacao_nome"], $this->conteudo);
            $this->conteudo = str_replace("[origem]", $this->dados_item["origem_nome"], $this->conteudo);
			$this->conteudo = str_replace("[responsavel]", $this->dados_item["responsavel_nome"], $this->conteudo);
			$this->conteudo = str_replace("[consultor]", $this->dados_item["consultor_nome"], $this->conteudo);
            $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[acoes]", $this->buscaAcoes($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

            $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
            $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    function geraEmailsDinamico($emails) {

        if ($emails) {

            $campos = '<tr bgcolor="#E8E8E8" class="txtAzulEscuro11"><td height="30" colspan="2"><strong>&nbsp;E-Mail:</strong></td></tr>';

            foreach ($emails as $email) {
                $campos .= '<tr bgcolor="#E8E8E8" class="txtAzulEscuro11"><td><strong>&nbsp;&raquo; ' . $email["contato_email_tipo"] . ':</strong></td><td>' . $email["contato_email"] . '</td></tr>';
            }

            return $campos;

        }

    }

    function geraTelefonesDinamico($telefones) {

        if ($telefones) {

            $campos = '<tr bgcolor="#E8E8E8" class="txtAzulEscuro11"><td height="30" colspan="2"><strong>&nbsp;Telefone:</strong></td></tr>';

            foreach ($telefones as $telefone) {

                $tel_numero = '(' . $telefone["contato_ddd"] . ') ' . $telefone["contato_numero"];

                if ($telefone["contato_ramal"]) {
                    $tel_numero .= " (Ramal " . $telefone["contato_ramal"] . ")";
                }

                $campos .= '<tr bgcolor="#E8E8E8" class="txtAzulEscuro11"><td><strong>&nbsp;&raquo; ' . $telefone["contato_telefone_tipo"] . ':</strong></td><td>' . $tel_numero . '</td></tr>';

            }

            return $campos;

        }

    }

    function geraParametrosOS() {

        $cliente_id = $this->dados_item["id"];
        $cliente_nome = urlencode($this->dados_item["nome"]);
        $cliente_email = $this->dados_item["email"];
        $cliente_condominio = urlencode($this->dados_item["condominio_nome"]);
        $cliente_complemento = urlencode($this->dados_item["complemento"]);
        if ($this->dados_item["bloco_nome"] && $this->dados_item["bloco_nome"] != "Único" && $cliente_complemento) $cliente_complemento .= urlencode("/" . $this->dados_item["bloco_nome"]);
        $cliente_telefone = urlencode($this->dados_item["telefone_sem_ramal"]);
        $usuario = $this->buscaDadosUsuario($_SESSION["usuario"]);
        $usuario = urlencode($usuario["nome"]);
        $parametros = "'" . URL_SISTEMA_OS . "', '$cliente_id', '$cliente_nome', '$cliente_condominio', '$cliente_complemento', '$cliente_telefone', '$usuario', '$cliente_email'";

        return $parametros;

    }

    function geraParametrosCancelamento() {

        $cliente_id = $this->dados_item["id"];
        $cliente_nome = urlencode($this->dados_item["nome"]);
        $cliente_condominio = urlencode($this->dados_item["condominio_nome"]);
        $cliente_complemento = urlencode($this->dados_item["complemento"]);
        if ($this->dados_item["bloco_nome"] && $this->dados_item["bloco_nome"] != "Único" && $cliente_complemento) $cliente_complemento .= urlencode("/" . $this->dados_item["bloco_nome"]);
        $cliente_telefone = urlencode($this->dados_item["telefone"]);
        $parametros = "'" . URL_SISTEMA_CANCELAMENTO . "', '$cliente_id', '$cliente_nome', '$cliente_condominio', '$cliente_complemento', '$cliente_telefone'";

        return $parametros;

    }

    ###############################
    ### Editar Dados Cadastrais ###
    ###############################

    function editar_dc() {

    	$this->buscaDadosItem(servidor::getGet("i"));

        if ($this->dados_item) {

            if (servidor::getGet("imprimir_endereco")) {
                $this->imprimirEndereco();
                die();
            }

            if (!$_POST) {

                $contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);

                $this->conteudo .= $this->carregaArquivo("clientes.editar_dc.inc.php");

                // Busca Alertas
                $alertas = $this->buscaAlertas($this->dados_item["id"], $this->dados_item["cpf_cnpj"], $this->dados_item["bloco_id"], $this->dados_item["cep"], $this->dados_item["numero"], $this->dados_item["complemento_valor"]);
                $dados_pendentes = $this->buscaDadosPendentes($this->dados_item);

                // Permissões de Alteração
                $desabilitados = array("nome", "nome_fantasia", "nascimento", "tipo", "cpf_cnpj", "nascimento", "condominio", "bloco", "cep", "numero", "complemento_tipo", "complemento_valor", "contato", "comentarios", "situacao");
                $habilitados = array("contato", "comentarios");

                if ($this->permissoes["clientes"]["atualizar_dc"] && $this->dados_item["status_id"] == 4) {
                    $habilitados = $desabilitados;
                }
                if ($this->permissoes["clientes"]["atualizar_nome_dc"]) {
                    $habilitados[] = "nome";
                    $habilitados[] = "nome_fantasia";
                }

                if ($this->ehAdmin()) {
                    $habilitados = $desabilitados;
                    $habilitados[] = "nome";
                    $habilitados[] = "nome_fantasia";
                }

                $temContratos = $this->possuiContratos($this->dados_item["id"], "", "", "IN", "3 , 4 , 19 , 20 , 21");
                if ($this->dados_item["status_id"] == 1 && !$temContratos) {
                    $habilitados = funcoes::mescla_arrays($habilitados, array("nome", "nome_fantasia", "condominio", "bloco", "cep", "numero", "complemento_tipo", "complemento_valor", "situacao"));
                }
                
                if ($this->permissoes["clientes"]["alterar_situacao_plus"]) {
                    $habilitados[] = "situacao";
                }
                if (!$this->dados_item["cpf_cnpj"]) {
                    $habilitados[] = "cpf_cnpj";
                }
                if (!$this->dados_item["nascimento"]) {
                    $habilitados[] = "nascimento";
                }
                if (!$this->dados_item["tipo_id"]) {
                    $habilitados[] = "tipo";
                }
                if ($this->dados_item["condominio_id"] && !$this->dados_item["bloco_id"]) {
                    $habilitados[] = "bloco";
                }
                if ($this->dados_item["condominio_id"] && !$this->dados_item["complemento_valor"]) {
                    $habilitados[] = "complemento_tipo";
                    $habilitados[] = "complemento_valor";
                }

                if ($this->permissoes["clientes"]["atualizar_endereco"]) {
					$habilitados[] = "condominio";
					$habilitados[] = "bloco";
					$habilitados[] = "cep";
					$habilitados[] = "numero";
					$habilitados[] = "complemento_tipo";
					$habilitados[] = "complemento_valor";
				}

                $desabilitados = array_unique($desabilitados);

                $campos_status = array();

                for ($i = 0; $i < sizeof($desabilitados); $i++) {
                    $campos_status[$desabilitados[$i]] = (in_array($desabilitados[$i], $habilitados)) ? false : true;
                }

                $this->conteudo = str_replace('[atualizacao_cadastro]', $this->dados_item["status_id"] == 2 ? 1 : 0, $this->conteudo);
                $this->conteudo = str_replace('[duplicado_cpf_cnpj]', 0, $this->conteudo);
                $this->conteudo = str_replace('[duplicado_endereco]', 0, $this->conteudo);
                $this->conteudo = str_replace('[duplicar_endereco]', $this->permissoes["clientes"]["duplicar_endereco"] ? 1 : 0, $this->conteudo);
                $this->conteudo = str_replace('[duplicar_cpf_cnpj]', $this->permissoes["clientes"]["duplicar_cpf_cnpj"] ? 1 : 0, $this->conteudo);
                $this->conteudo = str_replace('[alertas]', $this->geraMensagemHtml($alertas), $this->conteudo);
                
                $this->conteudo = str_replace('[alerta_pendencias]', $this->geraAlertaPendencias($dados_pendentes), $this->conteudo);
                $this->conteudo = str_replace('[pendencias]', $this->geraPendencias($dados_pendentes), $this->conteudo);
                $this->conteudo = str_replace('[pendencia_cpf_cnpj]', $dados_pendentes && $dados_pendentes["dados"]["cpf_cnpj"] ? '<span style="color:red;font-weight:bold">*</span>' : '', $this->conteudo);
                $this->conteudo = str_replace('[pendencia_nascimento]', $dados_pendentes && $dados_pendentes["dados"]["nascimento"] ? '<span style="color:red;font-weight:bold">*</span>' : '', $this->conteudo);
                $this->conteudo = str_replace('[pendencia_bloco]', $dados_pendentes && $dados_pendentes["dados"]["bloco"] ? '<span style="color:red;font-weight:bold">*</span>' : '', $this->conteudo);
                $this->conteudo = str_replace('[pendencia_complemento]', $dados_pendentes && $dados_pendentes["dados"]["complemento"] ? '<span style="color:red;font-weight:bold">*</span>' : '', $this->conteudo);
                $this->conteudo = str_replace('[pendencia_contatos]', $dados_pendentes && ($dados_pendentes["dados"]["email"] || $dados_pendentes["dados"]["telefone"]) ? '<span style="color:red;font-weight:bold">*</span>' : '', $this->conteudo);
                
                $this->conteudo = str_replace('[id]', $this->dados_item["id"], $this->conteudo);
                $this->conteudo = str_replace('[nome_label]', $this->dados_item["tipo_id"] == 2 ? "Razão Social" : "Nome", $this->conteudo);
                $this->conteudo = str_replace('[nome_fantasia_visivel]', $this->dados_item["tipo_id"] == 2 ? "table-row" : "none", $this->conteudo);
                $this->conteudo = str_replace('[nome_fantasia]', $this->dados_item["nome_fantasia"], $this->conteudo);
                $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
                $this->conteudo = str_replace('[situacao_financeira]', $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
                $this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(), $this->dados_item["tipo_id"], true), $this->conteudo);
                $this->conteudo = str_replace('[tipo_id]', $this->dados_item["tipo_id"], $this->conteudo);
                $this->conteudo = str_replace('[cpf_cnpj]', $this->dados_item["cpf_cnpj"], $this->conteudo);
                $this->conteudo = str_replace('[cpf_cnpj_label]', ($this->dados_item["tipo_id"] == 2) ? "CNPJ" : "CPF", $this->conteudo);
                $this->conteudo = str_replace('[cpf_cnpj_maxlength]', ($this->dados_item["tipo_id"] == 2) ? "18" : "14", $this->conteudo);
                $this->conteudo = str_replace('[inscricao_estadual]', $this->dados_item["inscricao_estadual"], $this->conteudo);
                $this->conteudo = str_replace('[inscricao_disabled]', ($this->dados_item["tipo_id"] == 2) ? "" : "disabled", $this->conteudo);
                $this->conteudo = str_replace('[isento_inscricao_estadual_checked]', $this->dados_item["isento_inscricao_estadual"] ? "checked" : "", $this->conteudo);
                $this->conteudo = str_replace('[nascimento]', $this->dados_item["nascimento"], $this->conteudo);

                if ($this->dados_item["condominio_id"]) {
                    $this->conteudo = str_replace('[condominio]', $this->geraSelect($this->buscaCondominios(), $this->dados_item["condominio_id"]), $this->conteudo);
                    $this->conteudo = str_replace('[panorama_condominio]', $this->buscaPanoramaCondominio($this->dados_item["condominio_id"]), $this->conteudo);
                    $this->conteudo = str_replace('[bloco]', $this->geraSelect($this->buscaBlocos($this->dados_item["condominio_id"]), $this->dados_item["bloco_id"]), $this->conteudo);
                    $this->conteudo = str_replace('[bloco_original]', $this->dados_item["bloco_id"], $this->conteudo);
                    $this->conteudo = str_replace('[panorama_bloco]', $this->buscaPanoramaBloco($this->dados_item["bloco_id"]), $this->conteudo);
                    $campos_status["cep"] = true;
                    $campos_status["numero"] = true;
                } else {
                    $this->conteudo = str_replace('[condominio]', $this->geraSelect($this->buscaCondominios()), $this->conteudo);
                    $this->conteudo = str_replace('[panorama_condominio]', '', $this->conteudo);
                    $this->conteudo = str_replace('[bloco]', '', $this->conteudo);
                    $this->conteudo = str_replace('[bloco_original]', '', $this->conteudo);
                    $this->conteudo = str_replace('[panorama_bloco]', '', $this->conteudo);
                    $campos_status["bloco"] = true;
                }

                $this->conteudo = str_replace('[cep]', $this->dados_item["cep"], $this->conteudo);
                $this->conteudo = str_replace('[estado]', $this->dados_item["estado"], $this->conteudo);
                $this->conteudo = str_replace('[cidade]', $this->dados_item["cidade"], $this->conteudo);
                $this->conteudo = str_replace('[bairro]', $this->dados_item["bairro"], $this->conteudo);
                $this->conteudo = str_replace('[logradouro]', $this->dados_item["logradouro"], $this->conteudo);
                $this->conteudo = str_replace('[numero]', $this->dados_item["numero"], $this->conteudo);
                $this->conteudo = str_replace('[complemento_tipo]', $this->geraSelect($this->buscaComplementoTipos(), $this->dados_item["complemento_tipo_id"]), $this->conteudo);
                $this->conteudo = str_replace('[complemento_valor]', $this->dados_item["complemento_valor"], $this->conteudo);
                $this->conteudo = str_replace('[ponto_referencia]', $this->dados_item["ponto_referencia"], $this->conteudo);
                $this->conteudo = str_replace('[contato]', $this->dados_item["contato"], $this->conteudo);
                $this->conteudo = str_replace('[listagem_telefones]', $contatos->geraContatosTelefonesListagem($contatos->buscaContatosTelefones(servidor::getGet("i")), true, $this->dados_item["status_id"] == 2), $this->conteudo);
                $this->conteudo = str_replace('[lista_telefones]', serialize($contatos->buscaContatosTelefones(servidor::getGet("i"))), $this->conteudo);
                $this->conteudo = str_replace('[listagem_emails]', $contatos->geraContatosEmailsListagem($contatos->buscaContatosEmails(servidor::getGet("i")), true, $this->dados_item["status_id"] == 2), $this->conteudo);
                $this->conteudo = str_replace('[lista_emails]', serialize($contatos->buscaContatosEmails(servidor::getGet("i"))), $this->conteudo);
                $this->conteudo = str_replace('[comentarios]', $this->dados_item["comentarios"], $this->conteudo);
                $this->conteudo = str_replace('[data_cadastro]', $this->dados_item["data_cadastro"], $this->conteudo);

                $situacoes = $this->buscaSituacoes();
                if ($this->dados_item["situacao_id"] != 5) unset($situacoes["5"]);
                if ($this->dados_item["situacao_id"] != 6) unset($situacoes["6"]);

                $this->conteudo = str_replace('[situacao]', $this->geraSelect($situacoes, $this->dados_item["situacao_id"], true), $this->conteudo);
                $this->conteudo = str_replace('[indicacao]', $this->geraSelect($this->buscaIndicacoes(), $this->dados_item["indicacao_id"], true), $this->conteudo);
                $this->conteudo = str_replace('[indicacao_disabled]', $this->permissoes["clientes"]["atualizar_dc"] ? "" : " disabled", $this->conteudo);
                $this->conteudo = str_replace('[operadora_origem]', $this->geraSelect($this->buscaOperadorasOrigens(), $this->dados_item["operadora_origem_id"], true), $this->conteudo);
                $this->conteudo = str_replace('[operadora_origem_disabled]', in_array($this->dados_item["status_id"], array(1, 4)) ? "" : " disabled", $this->conteudo);
                
                $this->conteudo = str_replace('[origem]', $this->geraSelect($this->buscaOrigens($this->ehAlteravelOrigem() ? null : $this->dados_item["origem_id"]), $this->dados_item["origem_id"]), $this->conteudo);
                $this->conteudo = str_replace('[consultor]', $this->geraSelect($this->buscaConsultores(), $this->dados_item["consultor_id"], true), $this->conteudo);
                
                $this->conteudo = str_replace('[origem_disabled]', $this->ehAlteravelOrigem() ? "" : " disabled", $this->conteudo);
                $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
                $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);

                // Ações
                $this->conteudo = str_replace('[acoes]', $this->buscaAcoes($this->dados_item["id"]), $this->conteudo);

                $campos_desabilitados = array();

                // Desabilitar Campos
                foreach ($campos_status as $campo => $valor) {
                    $this->conteudo = str_replace("[{$campo}_disabled]", ($valor) ? "disabled" : "", $this->conteudo);
                    if ($valor) $campos_desabilitados[] = $campo;
                }
                $this->conteudo = str_replace('[campos_desabilitados]', implode(",", $campos_desabilitados), $this->conteudo);
                
                if ($this->permissoes["ceps"]["pesquisar"]) {
                	$this->conteudo = str_replace("[pesquisar_cep]", "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"pesquisar_cep\" id=\"pesquisar_cep\" value=\"Pesquisar\" onClick=\"pesquisarCEP()\" />", $this->conteudo);
                } else {
                	$this->conteudo = str_replace("[pesquisar_cep]", "", $this->conteudo);
                }
                
                if ($this->permissoes["ceps"]["adicionar"]) {
                	$this->conteudo = str_replace("[adicionar_cep]", "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"adicionar_cep\" id=\"adicionar_cep\" value=\"Adicionar\" onClick=\"adicionarCEP()\" />", $this->conteudo);
                } else {
                	$this->conteudo = str_replace("[adicionar_cep]", "", $this->conteudo);
                }
                
                // Botões
                $botoes = array();
                $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
                if ($this->ehAdmin() || $this->dados_item["status_id"] == 4 && $this->permissoes["clientes"]["remover"] && (!$this->dados_item["responsavel_id"] || $this->dados_item["responsavel_id"] == $_SESSION["usuario"] || $this->permissoes["clientes"]["mudar_responsavel"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
                $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
                if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"]) $botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
                if ($this->permissoes[$this->dados_modulo["nome"]]["migrar_residencial"] && $this->dados_item["condominio_id"] && ($this->dados_item["status_id"] == 1 || $this->permissoes[$this->dados_modulo["nome"]]["migrar_residencial_plus"])) $botoes[] = '<input type="button" name="migrar_residencial" class="buttonAzulEscuroBranco11" value="Migrar para Residência" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar_residencial&i=' . $this->dados_item["id"] . '\';">';
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

                // Action
                $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar_dc&i={$this->dados_item["id"]}", $this->conteudo);


            } else {

                if ($_POST["complemento_tipo"] != "o") {
					$_POST["complemento_valor"] = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST["complemento_valor"]);
                }

                $alertas = $this->buscaAlertas($_GET["i"], $_POST["cpf_cnpj"], $_POST["bloco"], $_POST["cep"], $_POST["numero"], $_POST["complemento_valor"]);

                if ((($_POST["bloco"] == $this->dados_item["bloco_id"] && $_POST["complemento_tipo"] == $this->dados_item["complemento_tipo_id"] && $_POST["complemento_valor"] == $this->dados_item["complemento_valor"]) || $this->permissoes["clientes"]["duplicar_endereco"] || !$alertas["bloqueio_endereco_duplicado"]) && ($_POST["cpf_cnpj"] == $this->dados_item["cpf_cnpj"] || $this->permissoes["clientes"]["duplicar_cpf_cnpj"] || !$alertas["bloqueio_cpf_cnpj_duplicado"])) {

                    $_POST["campos_desabilitados"] = explode(",", $_POST["campos_desabilitados"]);
                    for ($i = 0; $i < sizeof($_POST["campos_desabilitados"]); $i++) {
                        if (!$_POST[$_POST["campos_desabilitados"][$i]]) {
                            $_POST[$_POST["campos_desabilitados"][$i]] = $this->dados_item[$_POST["campos_desabilitados"][$i]];
                        }
                    }
                    if (!$_POST["condominio"] && in_array("condominio", $_POST["campos_desabilitados"]))
                        $_POST["condominio"] = $this->dados_item["condominio_id"];
                    if (!$_POST["bloco"] && in_array("bloco", $_POST["campos_desabilitados"]))
                        $_POST["bloco"] = $this->dados_item["bloco_id"];
                    if (!$_POST["complemento_tipo"] && in_array("complemento_tipo", $_POST["campos_desabilitados"]))
                        $_POST["complemento_tipo"] = $this->dados_item["complemento_tipo_id"];
                    if (!$_POST["tipo"] && in_array("tipo", $_POST["campos_desabilitados"]))
                        $_POST["tipo"] = $this->dados_item["tipo_id"];
                    if (!$_POST["situacao"] && in_array("situacao", $_POST["campos_desabilitados"]))
                        $_POST["situacao"] = $this->dados_item["situacao_id"];
                    $_POST["indicacao"] = $_POST["indicacao"] ? $_POST["indicacao"] : ($this->dados_item["indicacao_id"] ? $this->dados_item["indicacao_id"] : "NULL");
                    $_POST["operadora_origem"] = $_POST["operadora_origem"] ? $_POST["operadora_origem"] : "NULL";
                    $_POST["nome"] = $this->corrigeNome($_POST["nome"]);
                    $_POST["nome_fantasia"] = trim($_POST["nome_fantasia"]) ? "'" . $this->corrigeNome($_POST["nome_fantasia"]) . "'" : "NULL";
                    $_POST["nascimento"] = $this->corrigeNascimento($_POST["nascimento"]);
                    $_POST["inscricao_estadual"] = $_POST["inscricao_estadual"] ? "'{$_POST["inscricao_estadual"]}'" : ($_POST["isento_inscricao_estadual"] ? "-1" : "NULL");
                    $_POST["condominio"] = $_POST["condominio"] ? $_POST["condominio"] : "NULL";
                    $_POST["bloco"] = $_POST["bloco"] ? $_POST["bloco"] : "NULL";
                    if ($_POST["bloco"] == "NULL") {
                        $_POST["cep"] = "'" . preg_replace("/[^0-9]/", "", $_POST["cep"]) . "'";
                        $_POST["numero"] = intval(preg_replace("/[^0-9]/", "", $_POST["numero"]));
                        $_POST["numero"] = $_POST["numero"] ? $_POST["numero"] : "NULL";
                    } else {
                        $_POST["cep"] = "NULL";
                        $_POST["numero"] = "NULL";
                    }
                    $_POST["ponto_referencia"] = addslashes(trim($_POST["ponto_referencia"]));
                    // $_POST["origem"] = $this->ehAlteravelOrigem() && array_key_exists("origem", $_POST) ? $_POST["origem"] : $this->buscaOrigem(preg_replace("/[^0-9]/", "", $_POST["cep"]), $_POST["condominio"]);
                    $_POST["origem"] = $this->ehAlteravelOrigem() && array_key_exists("origem", $_POST) ? $_POST["origem"] : $this->dados_item["origem_id"];
                    $complemento = $this->corrigeComplemento($_POST["complemento_valor"], $_POST["complemento_tipo"]);
                    $cobranca_formato_id = $_POST["origem"] != $this->dados_item["origem_id"] ? $this->buscaCobrancaFormatoPorOrigem($this->dados_item["cobranca_formato_tipo_id"], $_POST["origem"]) : $this->dados_item["cobranca_formato_id"];

                    // Define Contratos como Não-Sincronizados
                    if ($this->ehSincronizavelDC()) {
                        $query = "UPDATE tab_contratos SET cor_sincronizado = 0 WHERE cli_id = {$_GET["i"]} AND crs_id < 19";
                        $this->bancodedados->query($query);
                    }

                    // Atualiza Contatos (Telefones e Emails)
                    $contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);
                    
                    $lista_emails =  unserialize(stripslashes($_POST["lista_emails"]));
                    if(!empty($lista_emails)) {
                        $contatos->atualizaContatoEmails($_GET["i"], unserialize(stripslashes($_POST["lista_emails"])));
                    }
                    
                    $lista_telefones =  unserialize(stripslashes($_POST["lista_telefones"]));
                    if(!empty($lista_telefones)) {
                        $contatos->atualizaContatoTelefones($_GET["i"], unserialize(stripslashes($_POST["lista_telefones"])));
                    }
                    

                    // Atualiza Resposnabilidade Novo Condomínio
                    $_POST["responsavel"] = $this->dados_item["responsavel_id"] ? $this->dados_item["responsavel_id"] : "NULL";
                    if (($_POST["condominio"] != "NULL") && ($_POST["condominio"] != $this->dados_item["condominio_id"]) && ($responsabilidade = $this->buscaResponsavelCondominio($_POST["condominio"]))) {
                        if ($responsabilidade["responsavel_id"] != $this->dados_item["responsavel_id"]) {
                            $responsabilidades_condominio = $this->buscaResponsabilidadeCondominio($_POST["condominio"]);
                            if ($responsabilidades_condominio) {
                                // Registrar Responsáveis Pelo Condomínio
                                $usuario = $this->buscaDadosUsuario($_SESSION["usuario"]);
                                foreach ($responsabilidades_condominio as $responsabilidade) {
                                    // Verifica se a responsabilidade está num prazo vigente
                                    if ($responsabilidade["atual"]) {
                                        $_POST["responsavel"] = $responsabilidade["responsavel"];
                                        if ($responsabilidade["tipo"] == 2) {
                                            $tem_responsabilidade_atual = 1;
                                        }
                                        $id = $this->registraResponsabilidade($this->dados_item["id"], $responsabilidade["inicio"], $responsabilidade["termino"], $responsabilidade["parametros"], $responsabilidade["responsavel"], null, 'NULL', $responsabilidade["tipo"], 4);
                                        $this->registraComentarioResponsabilidade($id, "Adicionado com Auto-Ativação (Herdado do Condomínio)");
                                    } else {
                                        if (!$tem_responsabilidade_atual)
                                            $_POST["responsavel"] = "NULL";
                                        $id = $this->registraResponsabilidade($this->dados_item["id"], $responsabilidade["inicio"], $responsabilidade["termino"], $responsabilidade["parametros"], $responsabilidade["responsavel"], $responsabilidade["id"], 'NULL', $responsabilidade["tipo"], 2);
                                        $this->registraComentarioResponsabilidade($id, "Adicionado com Auto-Aprovação (Herdado do Condomínio)");
                                    }
                                    if ($responsabilidade["tipo"] == 2) {
                                        $this->registraRetorno($responsabilidade["responsavel"], $responsabilidade["inicio"], "", "Entrar em contato (Cadastrado por {$usuario["nome"]})", 2);
                                    }
                                }
                            }
                        } else {
                            $query = "
                                UPDATE
                                    tab_clientes_responsabilidades
                                SET
                                    cln_termino = '{$responsabilidade["termino"]}'
                                WHERE
                                    cli_id = {$this->dados_item["id"]} AND
                                    cln_termino < '{$responsabilidade["termino"]}' AND
                                    cri_id = 2 AND
                                    cra_id = 4";
                            $this->bancodedados->query($query);
                        }

                    }

                    // Atualiza Dados Cliente
                    $this->bancodedados->query("
                        UPDATE
                            tab_clientes
                        SET
                            cli_nome = '{$_POST["nome"]}',
                            cli_nome_fantasia = {$_POST["nome_fantasia"]},
                            cli_cpf_cnpj = '{$_POST["cpf_cnpj"]}',
                            cli_inscricao_estadual = {$_POST["inscricao_estadual"]},
                            cli_nascimento = {$_POST["nascimento"]},
                            con_id = {$_POST["condominio"]},
                            cob_id = {$_POST["bloco"]},
                            cep_id = {$_POST["cep"]},
                            cli_numero = {$_POST["numero"]},
                            cli_complemento = '$complemento',
                            cli_ponto_referencia = '{$_POST["ponto_referencia"]}',
                            cli_contato = '{$_POST["contato"]}',
                            cli_comentarios = '{$_POST["comentarios"]}',
                            clu_id = {$_POST["situacao"]},
                            cld_id = {$_POST["indicacao"]},
                            coo_id = {$_POST["operadora_origem"]},
                            clt_id = {$_POST["tipo"]},
                            clo_id = {$_POST["origem"]},
                            cof_id = $cobranca_formato_id,
                            usu_id = {$_POST["responsavel"]},
                            consultor = " . (isset($_POST["consultor"]) && $_POST["consultor"] ? $_POST["consultor"] : "NULL") . "
                        WHERE
                            cli_id = {$_GET["i"]}");

                    // Atualiza Pontos
                    if ($_POST["bloco"] != "NULL" && ($_POST["bloco"] != $this->dados_item["bloco_id"]) && ($dados_interface = $this->buscaInterfaceBloco($_POST["bloco"]))) {

                        // Instancia ClientesPontos
                        $clientes_pontos = controle::instanciaObjeto("clientes_pontos", $this->bancodedados, $this->permissoes, false);

                        // Busca pontos do cliente
                        $query = "
                            SELECT
                                tab_clientes_pontos.clp_id AS id,
                                tab_clientes_pontos.clp_regra AS regra,
                                tab_servidores_interfaces.sei_id AS interface_id,
                                tab_servidores_interfaces.ser_id AS servidor_id,
                                tab_assinaturas.ass_id AS assinatura_id
                            FROM
                                tab_assinaturas
                                INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
                                INNER JOIN tab_clientes_pontos ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
                                LEFT OUTER JOIN tab_servidores_interfaces ON (tab_clientes_pontos.sei_id = tab_servidores_interfaces.sei_id)
                            WHERE
                                tab_assinaturas.cli_id = {$_GET["i"]} AND
                                tab_clientes_pontos.cps_id IN (1, 2) AND
                                tab_planos.pls_id != 3";
                        $query = $this->bancodedados->query($query);

                        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
                            if ($dados["interface_id"] == $dados_interface["id"]) {
                                continue;
                            }
                            // atualiza interface/ip do ponto
                            $regra = $dados["servidor_id"] == $dados_interface["servidor_id"] ? $dados["regra"] : $this->buscaProximaRegra($dados_interface["servidor_id"]);
                            $ip = $clientes_pontos->buscaIPLivre($dados_interface["id"], $dados["assinatura_id"], $dados["id"]);
                            $ip = $ip ? "'$ip'" : "NULL";
                            $query_ponto = "UPDATE tab_clientes_pontos SET clp_regra = $regra, sei_id = {$dados_interface["id"]}, clp_ip = $ip, clp_publicado = 0 WHERE clp_id = {$dados["id"]}";
                            $this->bancodedados->query($query_ponto);
                        }

                    } elseif ($_POST["bloco"] == "NULL") {

                        $query = "
                            UPDATE
                                tab_clientes_pontos
                                INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
                            INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
                            SET
                                sei_id = NULL,
                                clp_ip = NULL,
                                clp_publicado = 0
                            WHERE
                                tab_assinaturas.cli_id = {$_GET["i"]} AND
                                tab_clientes_pontos.cps_id IN (1, 2) AND
                                tab_planos.pls_id NOT IN (3, 5, 33, 34, 35, 36)";
                        $this->bancodedados->query($query);
                    }

                    // Registra atividades
                    $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
                    $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

                    header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
                    exit();

                } else {

                    // Atualiza Contatos (Telefones e Emails)
                    $contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);
                    $contatos->atualizaContatoTelefones($_GET["i"], unserialize(stripslashes($_POST["lista_telefones"])));
                    $contatos->atualizaContatoEmails($_GET["i"], unserialize(stripslashes($_POST["lista_emails"])));

                    header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
                    exit();

                }

            }

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    function buscaInterfaceBloco($bloco_id) {

        $query = $this->bancodedados->query("
            SELECT
                tab_servidores_interfaces.sei_id AS id,
                tab_servidores_interfaces.ser_id AS servidor_id
            FROM
                tab_condominios_blocos
                INNER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
            WHERE
                cob_id = $bloco_id");

        return $this->bancodedados->resultadoArray($query);

    }

    function ehSincronizavelDC() {

        return $_POST["nome"] != $this->dados_item["nome"] || $_POST["tipo"] != $this->dados_item["tipo_id"] || $_POST["cpf_cnpj"] != $this->dados_item["cpf_cnpj"] || preg_replace("/[^0-9]/", "", ($_POST["bloco"])) != $this->dados_item["bloco_id"] || $_POST["cep"] != $this->dados_item["cep"] || $_POST["numero"] != $this->dados_item["numero"] || $_POST["complemento_tipo"] != $this->dados_item["complemento_tipo_id"] || $_POST["complemento_valor"] != $this->dados_item["complemento_valor"] || $_POST["lista_telefones"] != serialize($this->buscaContatosTelefones($_GET["i"]));

    }

    function buscaCobrancaFormatoPorOrigem($tipo_id, $origem_id) {

        if ($tipo_id == 4){

            return $this->buscaCobrancaFormatoPorOrigem(2, $origem_id);

        } else {

            $query = "
                SELECT
                    tab_cobrancas_formatos.cof_id
                FROM
                    tab_cobrancas_formatos
                    INNER JOIN rel_cof_clo_disponivel ON (tab_cobrancas_formatos.cof_id = rel_cof_clo_disponivel.cof_id)
                WHERE
                    tab_cobrancas_formatos.cft_id = $tipo_id AND
                    tab_cobrancas_formatos.cfs_id = 1 AND
                    rel_cof_clo_disponivel.clo_id = $origem_id";

            $query = $this->bancodedados->query($query);
            $dados = $this->bancodedados->resultadoArray($query);

            if ($dados) {
                return $dados["cof_id"];
            } else {
                return 1;
            }

        }

    }

    function buscaProximaRegra($servidor_id) {

        $regra = $this->buscaRegraLivre($servidor_id);

        if ($regra) {
            return $regra;
        } else {
            return $this->buscaRegraMaxima($servidor_id) + 1;
        }

    }

    function migrar_boleto_online(){

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            if (!$_POST) {

                $this->conteudo .= $this->carregaArquivo("clientes.migrar_boleto_online.inc.php");

                $botoes = array();

                $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
                $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
                $this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=migrar_boleto_online&i=' . $_GET["i"], $this->conteudo);

            } else {

                $query_update = "UPDATE tab_clientes SET cof_id = ".($this->buscaCobrancaFormatoPorOrigem(3, $this->dados_item["origem_id"]))." WHERE cli_id = {$this->dados_item["id"]}";
                $this->bancodedados->query($query_update);

                header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_df&i={$this->dados_item["id"]}");

            }

        }

    }

    function buscaRegraLivre($servidor_id) {

        $query = "
            SELECT
                tab_clientes_pontos.clp_id AS id,
                tab_clientes_pontos.clp_regra AS regra
            FROM
                tab_servidores_interfaces
                INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
            WHERE
                tab_servidores_interfaces.ser_id = $servidor_id AND
                tab_clientes_pontos.clp_regra IS NOT NULL AND
                tab_clientes_pontos.cps_id IN (3)
            ORDER BY
                tab_clientes_pontos.clp_regra
            LIMIT
                0, 1";

        $query = $this->bancodedados->query($query);
        $dados = $this->bancodedados->resultadoArray($query);

        if ($dados) {
            $this->bancodedados->query("UPDATE tab_clientes_pontos SET clp_regra = NULL WHERE clp_id = {$dados["id"]}");
            return $dados["regra"];
        }

    }

    function buscaRegraMaxima($servidor_id) {

        $query = "
            SELECT
                MAX(tab_clientes_pontos.clp_regra) AS regra
            FROM
                tab_servidores_interfaces
                INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
            WHERE
                tab_servidores_interfaces.ser_id = $servidor_id AND
                tab_clientes_pontos.clp_regra IS NOT NULL AND
                tab_clientes_pontos.cps_id IN (1, 2)";

        $query = $this->bancodedados->query($query);
        $dados = $this->bancodedados->resultadoArray($query);

        if ($dados) {
            return $dados["regra"] ? $dados["regra"] : 0;
        } else {
            return 0;
        }

    }

    #######################
    ### Editar Contatos ###
    #######################

    function editar_ct() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            if (!$_POST) {

                $contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);

                $this->conteudo .= $this->carregaArquivo("clientes.editar_ct.inc.php");

                // Dados
                $this->conteudo = str_replace('[id]', $this->dados_item["id"], $this->conteudo);
                $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
                $this->conteudo = str_replace('[situacao_financeira]', $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
                $this->conteudo = str_replace("[listagem_telefones]", $contatos->geraContatosTelefonesListagem($contatos->buscaContatosTelefones($_GET["i"]), true), $this->conteudo);
                $this->conteudo = str_replace("[lista_telefones]", serialize($contatos->buscaContatosTelefones($_GET["i"])), $this->conteudo);
                $this->conteudo = str_replace("[listagem_emails]", $contatos->geraContatosEmailsListagem($contatos->buscaContatosEmails($_GET["i"]), true), $this->conteudo);
                $this->conteudo = str_replace("[lista_emails]", serialize($contatos->buscaContatosEmails($_GET["i"])), $this->conteudo);

                // Botões
                $botoes = array();
                $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
                $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

                // Action
                $this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=editar_ct&i=' . $this->dados_item["id"], $this->conteudo);

            } else {

                // Trata Dados (Telefones e Emails)
                $contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);
                $contatos->atualizaContatoTelefones($_GET["i"], unserialize(stripslashes($_POST["lista_telefones"])));
                $contatos->atualizaContatoEmails($_GET["i"], unserialize(stripslashes($_POST["lista_emails"])));

                // Registra atividades
                $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
                $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

                header("Location: ./?m={$this->dados_modulo["nome"]}&a=editar_ct&i={$_GET["i"]}");
                exit();

            }

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    #############################
    ### Ver Dados Financeiros ###
    #############################

    function ver_df() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            $clientes_condominiais = $this->buscaClientesCondominiais($this->dados_item["id"]);

            $this->conteudo .= $this->carregaArquivo("clientes.ver_df.inc.php");

            // Dados Gerais
            $dados_pendentes = $this->buscaDadosPendentes($this->dados_item);
            $this->conteudo = str_replace('[cliente_id]', $this->dados_item["id"], $this->conteudo);
            $this->conteudo = str_replace('[alerta_pendencias]', $this->geraAlertaPendencias($dados_pendentes), $this->conteudo);
            $this->conteudo = str_replace('[pendencias]', $this->geraPendencias($dados_pendentes), $this->conteudo);
            $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
            $this->conteudo = str_replace('[cpf_cnpj]', $this->dados_item["cpf_cnpj"], $this->conteudo);
            $this->conteudo = str_replace('[situacao_financeira]', $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
            $this->conteudo = str_replace('[telefone]', $this->dados_item["telefone"], $this->conteudo);
            $this->conteudo = str_replace('[origem]', $this->dados_item["origem_nome"], $this->conteudo);
            $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
            $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);

            // Dados
            $this->conteudo = str_replace('[cliente_id]', $this->dados_item["id"], $this->conteudo);
            $this->conteudo = str_replace('[condominial]', $this->buscaNomeCliente($this->dados_item["condominial_id"]), $this->conteudo);
            $this->conteudo = str_replace('[exibir_condominial]', $this->dados_item["condominial_id"] ? "table-row" : "none", $this->conteudo);
            $this->conteudo = str_replace('[cobranca_vencimento]', $this->dados_item["cobranca_vencimento_nome"], $this->conteudo);
            $this->conteudo = str_replace('[bloqueio_dias]', $this->dados_item["bloqueio_dias"] ? "(Bloqueio após <b>{$this->dados_item["bloqueio_dias"]}</b> dia(s))" : "", $this->conteudo);
            $this->conteudo = str_replace('[cobranca_formato]', $this->dados_item["cobranca_formato_nome"], $this->conteudo);
            $this->conteudo = str_replace('[cobranca_debito_operacao]', $this->dados_item["cobranca_debito_operacao"], $this->conteudo);
            $this->conteudo = str_replace('[cobranca_debito_agencia]', $this->dados_item["cobranca_debito_agencia"], $this->conteudo);
            $this->conteudo = str_replace('[cobranca_debito_conta]', $this->dados_item["cobranca_debito_conta"], $this->conteudo);
            $this->conteudo = str_replace('[codigo_debito_automatico]', $this->dados_item["cobranca_debito_codigo"], $this->conteudo);
            $this->conteudo = str_replace('[exibir_debito_codigo]', $this->dados_item["cobranca_debito_codigo"] ? "table-row" : "none", $this->conteudo);
            $this->conteudo = str_replace('[clientes_condominiais]', $this->geraListagemClientesCondominiais($clientes_condominiais), $this->conteudo);
            $this->conteudo = str_replace('[exibir_clientes_condominiais]', $clientes_condominiais ? "table-row" : "none", $this->conteudo);

            // Dados Cobrança
            $this->conteudo = str_replace('[condominio]', $this->dados_item["cobranca"]["condominio_nome"], $this->conteudo);
            $this->conteudo = str_replace('[bloco]', $this->dados_item["cobranca"]["bloco_nome"], $this->conteudo);
            $this->conteudo = str_replace('[cep]', $this->dados_item["cobranca"]["cep"], $this->conteudo);
            $this->conteudo = str_replace('[estado]', $this->dados_item["cobranca"]["estado"], $this->conteudo);
            $this->conteudo = str_replace('[cidade]', $this->dados_item["cobranca"]["cidade"], $this->conteudo);
            $this->conteudo = str_replace('[bairro]', $this->dados_item["cobranca"]["bairro"], $this->conteudo);
            $this->conteudo = str_replace('[logradouro]', $this->dados_item["cobranca"]["logradouro"], $this->conteudo);
            $this->conteudo = str_replace('[numero]', $this->dados_item["cobranca"]["numero"], $this->conteudo);
            $this->conteudo = str_replace('[complemento]', $this->dados_item["cobranca"]["complemento"], $this->conteudo);

            // Listagem Ajustes Financeiros
            $this->conteudo = str_replace('[ajustes_financeiros]', $this->buscaClientesAjustesFinanceiros($this->dados_item["id"]), $this->conteudo);

            // Listagem Cobrancas
            $this->conteudo = str_replace('[cobrancas]', $this->buscaClientesCobrancas($this->dados_item["id"]), $this->conteudo);

            if ($this->permissoes["cobrancas"]["adicionar_cobranca_avulsa"]) {
                $this->conteudo = str_replace("[cobrancas_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="cobrancas_adicionar" value="Adicionar" onClick="adicionarCobranca(' . $this->dados_item["id"] . ')">', $this->conteudo);
            } else {
                $this->conteudo = str_replace("[cobrancas_adicionar]", '', $this->conteudo);
            }

            if ($this->permissoes["clientes"]["relatorio_financeiro"]) {
                $this->conteudo = str_replace("[relatorio_financeiro]", '<input type="button" class="buttonAzulEscuroBranco11" name="relatorio_financeiro" value="Relatorio Financeiro" onClick="verRelatorioFinanceiro(' . $this->dados_item["id"] . ')">', $this->conteudo);
            } else {
                $this->conteudo = str_replace("[relatorio_financeiro]", '', $this->conteudo);
            }

            // Listagem Consutlas de Crédito
            $this->conteudo = str_replace('[consultas_credito]', $this->buscaConsultasCredito($this->dados_item["id"]), $this->conteudo);
            if ($this->permissoes["clientes_consultas_credito"]["consultar"]) {
                $this->conteudo = str_replace("[consultas_credito_efetuar]", "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"consultas_credito_efetuar\" value=\"Consultar\" onClick=\"efetuarConsulta({$this->dados_item["id"]})\">", $this->conteudo);
            } else {
                $this->conteudo = str_replace("[consultas_credito_efetuar]", "", $this->conteudo);
            }

            // Ações
            $this->conteudo = str_replace('[acoes]', $this->buscaAcoes($this->dados_item["id"]), $this->conteudo);

            // Botões
            $botoes = array();
            if ($this->permissoes["clientes"]["editar_df"]) $botoes[] = '<input type="button" name="editar_df" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar_df&i=' . $this->dados_item["id"] . '\';">';
            if ($this->dados_item["status_id"] == 4 && $this->permissoes["clientes"]["remover"] && (!$this->dados_item["responsavel_id"] || $this->dados_item["responsavel_id"] == $_SESSION["usuario"] || $this->permissoes["clientes"]["mudar_responsavel"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
            if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"]) $botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
            if ($this->permissoes[$this->dados_modulo["nome"]]["migrar_boleto_online"] && $this->dados_item["cobranca_formato_tipo_id"] == 2) $botoes[] = '<input type="button" name="migrar_boleto_online" class="buttonAzulEscuroBranco11" value="Migrar Para Boleto Online" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar_boleto_online&i=' . $this->dados_item["id"] . '\';">';
			if ($this->ehCancelavelReajuste()) $botoes[] = "<input type=\"button\" name=\"cancelar_reajuste\" class=\"buttonAzulEscuroBranco11\" value=\"Cancelar Reajuste\" onClick=\"window.location='?m={$this->dados_modulo["nome"]}&a=cancelar_reajuste&i={$this->dados_item["id"]}';\">";
            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

            $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
            $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    ################################
    ### Editar Dados Financeiros ###
    ################################

    function editar_df() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            if (!$_POST) {

                $this->conteudo .= $this->carregaArquivo("clientes.editar_df.inc.php");

                $dados_pendentes = $this->buscaDadosPendentes($this->dados_item);
                $condominiais = $this->buscaCondominiais($this->dados_item["condominio_id"], $this->dados_item["id"]);
                $clientes_condominiais = $this->buscaClientesCondominiais($this->dados_item["id"]);
                $emails = $this->buscaClientesEmails($this->dados_item["id"]) ? true : false;

                // Permissões de Alteração
                $desabilitados = array("cobranca_vencimento", "cobranca_formato", "cobranca_debito_operacao", "cobranca_debito_agencia", "cobranca_debito_conta", "condominio", "bloco", "cep", "numero", "complemento_tipo", "complemento_valor");
                if (!$this->possuiContratos($this->dados_item["id"], "", "", "NOT IN", "5, 11, 12, 13, 14, 22, 23, 24") || $this->permissoes["clientes"]["atualizar_df"]) {
                    $habilitados = $desabilitados;
                }

                $campos_status = array();

                for ($i = 0; $i < sizeof($desabilitados); $i++) {
                    $campos_status[$desabilitados[$i]] = (in_array($desabilitados[$i], $habilitados)) ? false : true;
                }

                if ($this->dados_item["cobranca_formato_tipo_id"] != 4) {
                    $campos_status["cobranca_debito_operacao"] = true;
                    $campos_status["cobranca_debito_agencia"] = true;
                    $campos_status["cobranca_debito_conta"] = true;
                }

                if (!in_array($this->dados_item["cobranca_formato_id"], array(4, 18, 23))) {
                    $campos_status["cobranca_debito_operacao"] = true;
                }

                if ($this->dados_item["condominial_id"]) {
                    $campos_status["cobranca_vencimento"] = true;
                    $campos_status["cobranca_formato"] = true;
                    $campos_status["cobranca_debito_operacao"] = true;
                    $campos_status["cobranca_debito_agencia"] = true;
                    $campos_status["cobranca_debito_conta"] = true;
                }

                // Dados Gerais
                $this->conteudo = str_replace('[cliente_id]', $this->dados_item["id"], $this->conteudo);
                $this->conteudo = str_replace('[alerta_pendencias]', $this->geraAlertaPendencias($dados_pendentes), $this->conteudo);
                $this->conteudo = str_replace('[pendencias]', $this->geraPendencias($dados_pendentes), $this->conteudo);
                $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
                $this->conteudo = str_replace('[cpf_cnpj]', $this->dados_item["cpf_cnpj"], $this->conteudo);
                $this->conteudo = str_replace('[situacao_financeira]', $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
                $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
                $this->conteudo = str_replace('[telefone]', $this->dados_item["telefone"], $this->conteudo);
                $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
                $this->conteudo = str_replace('[origem]', $this->dados_item["origem_nome"], $this->conteudo);
                $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);

                // Dados
                $this->conteudo = str_replace('[cliente_id]', $this->dados_item["id"], $this->conteudo);
                $this->conteudo = str_replace('[condominial]', $this->geraSelect($condominiais, $this->dados_item["condominial_id"]), $this->conteudo);
                $this->conteudo = str_replace('[exibir_condominial]', $condominiais ? "table-row" : "none", $this->conteudo);
                $this->conteudo = str_replace('[cobranca_vencimento]', $this->geraSelect($this->buscaCobrancaVencimentos($this->dados_item["origem_id"], $this->dados_item["cobranca_vencimento_id"]), $this->dados_item["cobranca_vencimento_id"]), $this->conteudo);
                $this->conteudo = str_replace('[bloqueio]', $this->dados_item["bloqueio_dias"], $this->conteudo);
                $this->conteudo = str_replace('[bloqueio_disabled]', $this->permissoes["clientes"]["editar_dias_bloqueio"] && $this->possuiServicoCorporativo($this->dados_item["id"]) ? "" : "disabled", $this->conteudo);
                $this->conteudo = str_replace('[cobranca_formato]', $this->geraSelect($this->buscaCobrancaFormatos("1", $this->permissoes["clientes"]["mudar_formato"], $this->dados_item["cobranca_formato_id"], $this->dados_item["origem_id"], $emails), "{$this->dados_item["cobranca_formato_tipo_id"]},{$this->dados_item["cobranca_formato_id"]}"), $this->conteudo);
                $this->conteudo = str_replace('[cobranca_formato_copia_email]', in_array($this->dados_item["cobranca_formato_tipo_id"], array(1, 2, 3)) && $emails ? '<div style="display:inline-block; vertical-align:bottom"><table border="0" cellspacing="0" cellpadding="0"><tr class="txtAzulEscuro11"><td><input type="checkbox" id="cobranca_formato_email" name="cobranca_formato_email" '.($this->dados_item["copia_cobranca_email"] == 1 ? "checked" : "").' ></td><td><label for="cobranca_formato_email">Enviar cópia por e-mail</label></td></tr></table></div>' : "", $this->conteudo);
                $this->conteudo = str_replace('[cobranca_debito_operacao]', $this->dados_item["cobranca_debito_operacao"], $this->conteudo);
                $this->conteudo = str_replace('[cobranca_debito_agencia]', $this->dados_item["cobranca_debito_agencia"], $this->conteudo);
                $this->conteudo = str_replace('[cobranca_debito_conta]', $this->dados_item["cobranca_debito_conta"], $this->conteudo);
                $this->conteudo = str_replace('[codigo_debito_automatico]', $this->dados_item["cobranca_debito_codigo"], $this->conteudo);
                $this->conteudo = str_replace('[exibir_debito_codigo]', $this->dados_item["cobranca_debito_codigo"] ? "table-row" : "none", $this->conteudo);
                $this->conteudo = str_replace('[cobrancas_formatos_debito]', $this->buscaCobrancasFormatosDebito(), $this->conteudo);
                $this->conteudo = str_replace('[clientes_condominiais]', $this->geraListagemClientesCondominiais($clientes_condominiais), $this->conteudo);
                $this->conteudo = str_replace('[exibir_clientes_condominiais]', $clientes_condominiais ? "table-row" : "none", $this->conteudo);

                // Dados Cobrança
                if ($this->dados_item["cobranca"]["condominio_id"]) {
                    $this->conteudo = str_replace('[condominio]', $this->geraSelect($this->buscaCondominios(), $this->dados_item["cobranca"]["condominio_id"]), $this->conteudo);
                    $this->conteudo = str_replace('[bloco]', $this->geraSelect($this->buscaBlocos($this->dados_item["cobranca"]["condominio_id"]), $this->dados_item["cobranca"]["bloco_id"]), $this->conteudo);
                    $campos_status["cep"] = true;
                    $campos_status["numero"] = true;
                } else {
                    $this->conteudo = str_replace('[condominio]', $this->geraSelect($this->buscaCondominios()), $this->conteudo);
                    $this->conteudo = str_replace('[bloco]', "", $this->conteudo);
                    $campos_status["bloco"] = true;
                }

                $this->conteudo = str_replace('[cep]', $this->dados_item["cobranca"]["cep"], $this->conteudo);
                $this->conteudo = str_replace('[estado]', $this->dados_item["cobranca"]["estado"], $this->conteudo);
                $this->conteudo = str_replace('[cidade]', $this->dados_item["cobranca"]["cidade"], $this->conteudo);
                $this->conteudo = str_replace('[bairro]', $this->dados_item["cobranca"]["bairro"], $this->conteudo);
                $this->conteudo = str_replace('[logradouro]', $this->dados_item["cobranca"]["logradouro"], $this->conteudo);
                $this->conteudo = str_replace('[numero]', $this->dados_item["cobranca"]["numero"], $this->conteudo);
                $this->conteudo = str_replace('[complemento_tipo]', $this->geraSelect($this->buscaComplementoTipos(), $this->dados_item["cobranca"]["complemento_tipo_id"]), $this->conteudo);
                $this->conteudo = str_replace('[complemento_valor]', $this->dados_item["cobranca"]["complemento_valor"], $this->conteudo);

                // Negativacoes
                $clientes_negativacoes = controle::instanciaObjeto("clientes_negativacoes", $this->bancodedados, $this->permissoes, false);
                $this->conteudo = str_replace('[clientes_negativacoes]', $clientes_negativacoes->geraListagemClientesNegativacoes($clientes_negativacoes->buscaClientesNegativacoes($this->dados_item["id"])), $this->conteudo);
                $this->conteudo = str_replace('[clientes_negativacoes_adicionar]', $clientes_negativacoes->buscaClientesNegativacoesAdicionar($this->dados_item["id"]), $this->conteudo);

                // Ajustes Financeiros
                $this->conteudo = str_replace('[ajustes_financeiros]', $this->buscaClientesAjustesFinanceiros($this->dados_item["id"]), $this->conteudo);
                if ($this->permissoes["ajustes_financeiros"]["adicionar"]) {
                    $this->conteudo = str_replace("[ajustes_financeiros_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="ajustes_financeiros_adicionar" value="Adicionar" onClick="adicionarAjusteFinanceiro(' . $this->dados_item["id"] . ')">', $this->conteudo);
                } else {
                    $this->conteudo = str_replace("[ajustes_financeiros_adicionar]", '', $this->conteudo);
                }

                // Cobrancas
                $this->conteudo = str_replace('[cobrancas]', $this->buscaClientesCobrancas($this->dados_item["id"]), $this->conteudo);

                if ($this->permissoes["cobrancas"]["adicionar_cobranca_avulsa"]) {
                    $this->conteudo = str_replace("[cobrancas_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="cobrancas_adicionar" value="Adicionar" onClick="adicionarCobranca(' . $this->dados_item["id"] . ')">', $this->conteudo);
                } else {
                    $this->conteudo = str_replace("[cobrancas_adicionar]", '', $this->conteudo);
                }

                if ($this->permissoes["clientes"]["relatorio_financeiro"]) {
                    $this->conteudo = str_replace("[relatorio_financeiro]", '<input type="button" class="buttonAzulEscuroBranco11" name="relatorio_financeiro" value="Relatorio Financeiro" onClick="verRelatorioFinanceiro(' . $this->dados_item["id"] . ')">', $this->conteudo);
                } else {
                    $this->conteudo = str_replace("[relatorio_financeiro]", '', $this->conteudo);
                }

                // Listagem Consutlas de Crédito
                $this->conteudo = str_replace("[consultas_credito]", $this->buscaConsultasCredito($this->dados_item["id"]), $this->conteudo);
                if ($this->permissoes["clientes_consultas_credito"]["consultar"]) {
                    $this->conteudo = str_replace("[consultas_credito_efetuar]", "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"consultas_credito_efetuar\" value=\"Consultar\" onClick=\"efetuarConsulta({$this->dados_item["id"]})\">", $this->conteudo);
                } else {
                    $this->conteudo = str_replace("[consultas_credito_efetuar]", "", $this->conteudo);
                }

                // Ações
                $this->conteudo = str_replace('[acoes]', $this->buscaAcoes($this->dados_item["id"]), $this->conteudo);

                // Desabilitar Campos
                $campos_desabilitados = array();
                foreach ($campos_status as $campo => $valor) {
					$this->conteudo = str_replace("[{$campo}_disabled]", ($valor) ? "disabled" : "", $this->conteudo);
                    if ($valor) $campos_desabilitados[] = $campo;
                }
                $this->conteudo = str_replace('[campos_desabilitados]', implode(",", $campos_desabilitados), $this->conteudo);

                if ($this->permissoes["ceps"]["pesquisar"]) {
                    $this->conteudo = str_replace("[pesquisar_cep]", "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"pesquisar_cep\" id=\"pesquisar_cep\" value=\"Pesquisar\" onClick=\"pesquisarCEP()\" />", $this->conteudo);
                } else {
                    $this->conteudo = str_replace("[pesquisar_cep]", "", $this->conteudo);
                }
                
                if ($this->permissoes["ceps"]["adicionar"]) {
                	$this->conteudo = str_replace("[adicionar_cep]", "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"adicionar_cep\" id=\"adicionar_cep\" value=\"Adicionar\" onClick=\"adicionarCEP()\" />", $this->conteudo);
                } else {
                	$this->conteudo = str_replace("[adicionar_cep]", "", $this->conteudo);
                }
                
                // Botões
                $botoes = array();
                $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
                if ($this->dados_item["status_id"] == 4 && $this->permissoes["clientes"]["remover"] && (!$this->dados_item["responsavel_id"] || $this->dados_item["responsavel_id"] == $_SESSION["usuario"] || $this->permissoes["clientes"]["mudar_responsavel"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
                $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
                if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"]) $botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
                if ($this->permissoes[$this->dados_modulo["nome"]]["migrar_boleto_online"] && $this->dados_item["cobranca_formato_tipo_id"] == 2) $botoes[] = '<input type="button" name="migrar_boleto_online" class="buttonAzulEscuroBranco11" value="Migrar Para Boleto Online" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar_boleto_online&i=' . $this->dados_item["id"] . '\';">';
				if ($this->ehCancelavelReajuste()) $botoes[] = "<input type=\"button\" name=\"cancelar_reajuste\" class=\"buttonAzulEscuroBranco11\" value=\"Cancelar Reajuste\" onClick=\"window.location='?m={$this->dados_modulo["nome"]}&a=cancelar_reajuste&i={$this->dados_item["id"]}';\">";
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

                $this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=editar_df&i=' . $this->dados_item["id"], $this->conteudo);

            } else {

                // Trata Campos
                $_POST["cobranca_formato"] = $_POST["cobranca_formato"] ? substr($_POST["cobranca_formato"], strpos($_POST["cobranca_formato"], ",") + 1) : $_POST["cobranca_formato"];
                $_POST["campos_desabilitados"] = explode(",", $_POST["campos_desabilitados"]);
                for ($i = 0; $i < sizeof($_POST["campos_desabilitados"]); $i++) {
                    if (!$_POST[$_POST["campos_desabilitados"][$i]]) {
                        $_POST[$_POST["campos_desabilitados"][$i]] = $this->dados_item[$_POST["campos_desabilitados"][$i]];
                    }
                }
                if (!$_POST["cobranca_vencimento"] && in_array("cobranca_vencimento", $_POST["campos_desabilitados"]))
                    $_POST["cobranca_vencimento"] = $this->dados_item["cobranca_vencimento_id"];
                if (!$_POST["cobranca_formato"] && in_array("cobranca_formato", $_POST["campos_desabilitados"]))
                    $_POST["cobranca_formato"] = $this->dados_item["cobranca_formato_id"];
                if (!$_POST["condominio"] && in_array("condominio", $_POST["campos_desabilitados"]))
                    $_POST["condominio"] = $this->dados_item["cobranca"]["condominio_id"];
                if (!$_POST["bloco"] && in_array("bloco", $_POST["campos_desabilitados"]))
                    $_POST["bloco"] = $this->dados_item["cobranca"]["bloco_id"];
                if (!$_POST["complemento_tipo"] && in_array("complemento_tipo", $_POST["campos_desabilitados"]))
                    $_POST["complemento_tipo"] = $this->dados_item["cobranca"]["complemento_tipo_id"];
                if ($_POST["condominial"]) {
                    $_POST['cobranca_formato'] = "NULL";
                    $_POST['cobranca_vencimento'] = "NULL";
                    $_POST['cobranca_debito_operacao'] = "NULL";
                    $_POST['cobranca_debito_agencia'] = "NULL";
                    $_POST['cobranca_debito_conta'] = "NULL";
                    $_POST['cobranca_debito_codigo'] = "NULL";
                } else {
                    $_POST["condominial"] = "NULL";
                    if ($this->dados_item["cobranca_formato_id"] == $_POST['cobranca_formato'] && $this->dados_item["cobranca_debito_agencia"] == $_POST['cobranca_debito_agencia'] && $this->dados_item["cobranca_debito_conta"] == $_POST['cobranca_debito_conta'] && $this->dados_item["cobranca_debito_operacao"] == $_POST['cobranca_debito_operacao']) {
                        $_POST['cobranca_debito_codigo'] = $this->dados_item["cobranca_debito_codigo"];
                    } else {
                        $_POST['cobranca_debito_codigo'] = $this->geraCodigoDebitoAutomatico($_GET["i"], $_POST['cobranca_formato'], $_POST['cobranca_debito_agencia'], $_POST['cobranca_debito_conta'], $_POST['cobranca_debito_operacao']);
                    }
                    $_POST['cobranca_vencimento'] = "'{$_POST["cobranca_vencimento"]}'";
                    $_POST['cobranca_debito_codigo'] = $_POST['cobranca_debito_codigo'] ? "'{$_POST['cobranca_debito_codigo']}'" : "NULL";
                    $_POST['cobranca_debito_operacao'] = $_POST['cobranca_debito_operacao'] ? "'" . strtoupper($_POST['cobranca_debito_operacao']) . "'" : "NULL";
                    $_POST['cobranca_debito_agencia'] = $_POST['cobranca_debito_agencia'] ? "'" . strtoupper($_POST['cobranca_debito_agencia']) . "'" : "NULL";
                    $_POST['cobranca_debito_conta'] = $_POST['cobranca_debito_conta'] ? "'" . strtoupper($_POST['cobranca_debito_conta']) . "'" : "NULL";
                }
                if ($_POST["bloqueio"] !== null) {
                    $_POST["bloqueio"] = $_POST["bloqueio"] ? intval($_POST["bloqueio"], 10) : "NULL";
                    $_POST["bloqueio_customizado"] = "1";
                } else {
                    $_POST["bloqueio"] = "cli_bloqueio_dias";
                    $_POST["bloqueio_customizado"] = "cli_bloqueio_dias_customizado";
                }
                $dados_formato = $this->buscaDadosCobrancaFormato($_POST["cobranca_formato"]);
                $_POST["cobranca_formato_email"] = $_POST["cobranca_formato_email"] && in_array($dados_formato["tipo_id"], array(1, 2, 3)) && $this->buscaClientesEmails($this->dados_item["id"]) ? "1" : "0";

                // Atualiza Dados Cliente
                $query = "
                    UPDATE
                        tab_clientes
                    SET
                        cli_bloqueio_dias = {$_POST["bloqueio"]},
                        cli_bloqueio_dias_customizado = {$_POST["bloqueio_customizado"]},
                        cli_ccd_vencimento = {$_POST["cobranca_vencimento"]},
                        cof_id = {$_POST["cobranca_formato"]},
                        cli_ccd_debito_operacao = {$_POST["cobranca_debito_operacao"]},
                        cli_ccd_debito_agencia = {$_POST["cobranca_debito_agencia"]},
                        cli_ccd_debito_conta = {$_POST["cobranca_debito_conta"]},
                        cli_ccd_debito_codigo = {$_POST["cobranca_debito_codigo"]},
                        cli_pai = {$_POST["condominial"]},
                        cli_copia_cobranca = {$_POST["cobranca_formato_email"]}
                    WHERE
                        cli_id = {$_GET["i"]}";
                $this->bancodedados->query($query);

                // Atualiza Data Bloqueio Cobranças
                if ($_POST["bloqueio"] !== "cli_bloqueio_dias") {
                    if ($_POST["bloqueio"] !== "NULL") {
                        $query = "
                            SELECT
                                tab_cobrancas.coa_id AS id,
                                tab_cobrancas.coa_data_vencimento AS data_vencimento,
                                MAX(tab_cobrancas_filhas.coa_data_vencimento) AS data_vencimento_filha
                            FROM
                                tab_cobrancas
                                LEFT OUTER JOIN tab_cobrancas tab_cobrancas_filhas ON (tab_cobrancas_filhas.coa_pai = tab_cobrancas.coa_id)
                            WHERE
                                tab_cobrancas.cli_id = {$_GET["i"]} AND
                                tab_cobrancas.coa_pai IS NULL
                            GROUP BY
                                tab_cobrancas.coa_id";
                        $query = $this->bancodedados->query($query);
                        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
                            $data_bloqueio = date("Y-m-d", strtotime("{$dados["data_vencimento"]} + {$_POST["bloqueio"]} days"));
                            if ($dados["data_vencimento_filha"]) {
                                $data_bloqueio_filha = date("Y-m-d", strtotime("{$dados["data_vencimento_filha"]} " . BLOQUEIO_INADINPLENCIA_SEGUNDA_VIA . " days"));
                                if (date("w", strtotime($data_bloqueio_filha)) == 0) { // Domingo
                                    $data_bloqueio_filha = date("Y-m-d", strtotime("$data_bloqueio_filha 1 days"));
                                } elseif (date("w", strtotime($data_bloqueio_filha)) == 6) { // Sábado
                                    $data_bloqueio_filha = date("Y-m-d", strtotime("$data_bloqueio_filha 2 days"));
                                }
                                if (strtotime($data_bloqueio) < strtotime($data_bloqueio_filha)) {
                                    $data_bloqueio = $data_bloqueio_filha;
                                }
                            }
                            $query = "UPDATE tab_cobrancas SET coa_data_bloqueio = '{$data_bloqueio}' WHERE coa_id = {$dados["id"]}";
                            $this->bancodedados->query($query);
                        }
                    } else {
                        $query = "UPDATE tab_cobrancas SET coa_data_bloqueio = NULL WHERE cli_id = {$_GET["i"]}";
                        $this->bancodedados->query($query);
                    }
                }

                // Define Contrato Como Não Sincronizado
                if ($this->bancodedados->linhasAfetadas() || $this->ehSincronizavel_df()) {
                    $query = "UPDATE tab_contratos SET cor_sincronizado = 0 WHERE cli_id = {$_GET["i"]} AND crs_id IN (11, 12, 13, 14)";
                    $this->bancodedados->query($query);
                }

                // Atualiza Endereço Cobrança
                $query = "DELETE FROM tab_clientes_cobranca_dados WHERE cli_id = {$_GET["i"]}";
                $this->bancodedados->query($query);

                if ($_POST["complemento_valor"] && ($_POST["cep"] != $this->dados_item["cep"] || $_POST["bloco"] != $this->dados_item["bloco_id"] || $_POST["complemento_tipo"] != $this->dados_item["complemento_tipo_id"] || $_POST["complemento_valor"] != $this->dados_item["complemento_valor"] || $_POST["numero"] != $this->dados_item["numero"])) {
                    if ($_POST["complemento_tipo"] != "o")
                        $_POST["complemento_valor"] = intval($_POST["complemento_valor"]);
                    $complemento = "'{$_POST["complemento_tipo"]}{$_POST["complemento_valor"]}'";
                    $existe_dados_cobranca = true;
                } else {
                    $complemento = "NULL";
                }
                if ($_POST["bloco"] && ($_POST["bloco"] != $this->dados_item["bloco_id"] || $_POST["complemento_tipo"] != $this->dados_item["complemento_tipo_id"] || $_POST["complemento_valor"] != $this->dados_item["complemento_valor"] || $_POST["numero"] != $this->dados_item["numero"])) {
                    $_POST["bloco"] = "'{$_POST["bloco"]}'";
                    $existe_dados_cobranca = true;
                } else {
                    $_POST["bloco"] = "NULL";
                }
                if ($_POST["cep"] && ($_POST["cep"] != $this->dados_item["cep"] || $_POST["numero"] != $this->dados_item["numero"] || $_POST["complemento_tipo"] != $this->dados_item["complemento_tipo_id"] || $_POST["complemento_valor"] != $this->dados_item["complemento_valor"])) {
                    $_POST["cep"] = "'" . preg_replace("/[^0-9]/", "", $_POST["cep"]) . "'";
                    $_POST["numero"] = ($_POST["numero"]) ? "'{$_POST["numero"]}'" : "NULL";
                    $existe_dados_cobranca = true;
                } else {
                    $_POST["cep"] = "NULL";
                    $_POST["numero"] = "NULL";
                }

                if ($existe_dados_cobranca) {
                    $query = "INSERT INTO tab_clientes_cobranca_dados (cli_id, cob_id, cep_id, ccd_numero, ccd_complemento) VALUES ('{$_GET["i"]}', {$_POST["bloco"]}, {$_POST["cep"]}, {$_POST["numero"]}, $complemento)";
                    $this->bancodedados->query($query);
                }

                // Registra Atividade
                $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
                $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

                header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_df&i={$_GET["i"]}");

            }

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    function ehSincronizavel_df() {

        return preg_replace("/[^0-9]/", "", ($_POST["bloco"])) != $this->dados_item["bloco_id"] || $_POST["cep"] != $this->dados_item["cep"] || $_POST["numero"] != $this->dados_item["numero"] || $_POST["complemento_tipo"] != $this->dados_item["complemento_tipo_id"] || $_POST["complemento_valor"] != $this->dados_item["complemento_valor"];

    }

    ############################
    ### Ver Dados Comerciais ###
    ############################

    function ver_dm() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            $dados_pendentes = $this->buscaDadosPendentes($this->dados_item);

            $this->conteudo .= $this->carregaArquivo("clientes.ver_dm.inc.php");
            // Dados Gerais

            $this->conteudo = str_replace('[cliente_id]', $this->dados_item["id"], $this->conteudo);
            $this->conteudo = str_replace('[alerta_pendencias]', $this->geraAlertaPendencias($dados_pendentes), $this->conteudo);
            $this->conteudo = str_replace('[pendencias]', $this->geraPendencias($dados_pendentes), $this->conteudo);
            $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
            $this->conteudo = str_replace('[cpf_cnpj]', $this->dados_item["cpf_cnpj"], $this->conteudo);
            $this->conteudo = str_replace('[situacao_financeira]', $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
            $this->conteudo = str_replace('[telefone]', $this->dados_item["telefone"], $this->conteudo);
            $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
            $this->conteudo = str_replace('[origem]', $this->dados_item["origem_nome"], $this->conteudo);
            $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);

            // Dados
            $this->conteudo = str_replace('[condominio_id]', $this->dados_item["condominio_id"], $this->conteudo);
            $this->conteudo = str_replace('[cobranca_vencimento]', $this->dados_item["cobranca_vencimento_nome"], $this->conteudo);
            $this->conteudo = str_replace('[cobranca_formato]', $this->dados_item["cobranca_formato_nome"], $this->conteudo);
            $this->conteudo = str_replace('[cobranca_debito_operacao]', $this->dados_item["cobranca_debito_operacao"], $this->conteudo);
            $this->conteudo = str_replace('[cobranca_debito_agencia]', $this->dados_item["cobranca_debito_agencia"], $this->conteudo);
            $this->conteudo = str_replace('[cobranca_debito_conta]', $this->dados_item["cobranca_debito_conta"], $this->conteudo);
            $this->conteudo = str_replace('[operacoes]', $this->buscaClientesOperacoes($this->dados_item["id"], 1), $this->conteudo);
            $this->conteudo = str_replace('[retornos]', $this->buscaClientesRetornos($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace('[assinaturas]', $this->buscaAssinaturasCliente($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace('[responsabilidades]', $this->buscaClientesResponsabilidades($this->dados_item["id"]), $this->conteudo);

            if ($this->permissoes["assinaturas"]["adicionar"]) {
                $this->conteudo = str_replace("[assinaturas_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="assinaturas_adicionar" value="Adicionar" onClick="adicionarAssinatura(' . $this->dados_item["id"] . ')">', $this->conteudo);
            } else {
                $this->conteudo = str_replace("[assinaturas_adicionar]", '', $this->conteudo);
            }

            if ($this->permissoes["clientes_retornos"]["adicionar"]) {
                $this->conteudo = str_replace("[retornos_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="retornos_adicionar" value="Adicionar" onClick="adicionarClienteRetorno(' . $this->dados_item["id"] . ')">', $this->conteudo);
            } else {
                $this->conteudo = str_replace("[retornos_adicionar]", '', $this->conteudo);
            }

            if ($this->permissoes["clientes_responsabilidades"]["adicionar"]) {
                $this->conteudo = str_replace("[responsabilidades_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="responsabilidades_adicionar" value="Adicionar" onClick="adicionarClienteResponsabilidade(' . $this->dados_item["id"] . ')">', $this->conteudo);
            } else {
                $this->conteudo = str_replace("[responsabilidades_adicionar]", '', $this->conteudo);
            }

            // Ações
            $this->conteudo = str_replace('[acoes]', $this->buscaAcoes($this->dados_item["id"]), $this->conteudo);

            // pfpj & ccsc
            ($this->dados_item["tipo_id"] == 1) ? $pfpj = 1 : $pfpj = 0;
            ($this->dados_item["condominio_id"]) ? $ccsc = 1 : $ccsc = 0;
            $this->conteudo = str_replace('[pfpj]', $pfpj, $this->conteudo);
            $this->conteudo = str_replace('[ccsc]', $ccsc, $this->conteudo);

            // Botões
            $botoes = array();
            if ($this->permissoes["clientes"]["editar_dm"]) $botoes[] = '<input type="button" name="editar_dm" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar_dm&i=' . $this->dados_item["id"] . '\';">';
            if ($this->dados_item["status_id"] == 4 && $this->permissoes["clientes"]["remover"] && (!$this->dados_item["responsavel_id"] || $this->dados_item["responsavel_id"] == $_SESSION["usuario"] || $this->permissoes["clientes"]["mudar_responsavel"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
            if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"]) $botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

            // Parametros da Função buscaAssinaturasCliente
            $this->conteudo = str_replace('[assinaturas_parametros]', "{$this->dados_item["id"]}, 0}", $this->conteudo);

            $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
            $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    ###############################
    ### Editar Dados Comerciais ###
    ###############################

    function editar_dm() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

			$desabilitados = array("cobranca_vencimento", "cobranca_formato", "cobranca_debito_operacao", "cobranca_debito_agencia", "cobranca_debito_conta");
			
			if (!$this->possuiContratos($this->dados_item["id"], "", "", "NOT IN", "5, 11, 12, 13, 14, 22, 23, 24") || $this->permissoes["clientes"]["atualizar_df"]) {
				$habilitados = $desabilitados;
			}

			$campos_status = array();

			for ($i = 0; $i < sizeof($desabilitados); $i++) {
				$campos_status[$desabilitados[$i]] = (in_array($desabilitados[$i], $habilitados)) ? false : true;
			}

			if ($this->dados_item["cobranca_formato_tipo_id"] != 4) {
				$campos_status["cobranca_debito_operacao"] = true;
				$campos_status["cobranca_debito_agencia"] = true;
				$campos_status["cobranca_debito_conta"] = true;
			}

			if (!in_array($this->dados_item["cobranca_formato_id"], array(4, 18, 23))) {
				$campos_status["cobranca_debito_operacao"] = true;
			}

			if ($this->dados_item["condominial_id"]) {
				$campos_status["cobranca_vencimento"] = true;
				$campos_status["cobranca_formato"] = true;
				$campos_status["cobranca_debito_operacao"] = true;
				$campos_status["cobranca_debito_agencia"] = true;
				$campos_status["cobranca_debito_conta"] = true;
			}

            if (!$_POST) {

                $dados_pendentes = $this->buscaDadosPendentes($this->dados_item);
                $emails = $this->buscaClientesEmails($this->dados_item["id"]) ? true : false;

                $this->conteudo .= $this->carregaArquivo("clientes.editar_dm.inc.php");

                // Dados Gerais
                $this->conteudo = str_replace('[cliente_id]', $this->dados_item["id"], $this->conteudo);
                $this->conteudo = str_replace('[alerta_pendencias]', $this->geraAlertaPendencias($dados_pendentes), $this->conteudo);
                $this->conteudo = str_replace('[pendencias]', $this->geraPendencias($dados_pendentes), $this->conteudo);
                $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
                $this->conteudo = str_replace('[cpf_cnpj]', $this->dados_item["cpf_cnpj"], $this->conteudo);
                $this->conteudo = str_replace('[situacao_financeira]', $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
                $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
                $this->conteudo = str_replace('[telefone]', $this->dados_item["telefone"], $this->conteudo);
                $this->conteudo = str_replace('[origem]', $this->dados_item["origem_nome"], $this->conteudo);
                $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
                $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);

                // Dados
                $this->conteudo = str_replace('[condominio_id]', $this->dados_item["condominio_id"], $this->conteudo);
                $this->conteudo = str_replace('[cobranca_vencimento]', $this->geraSelect($this->buscaCobrancaVencimentos($this->dados_item["origem_id"], $this->dados_item["cobranca_vencimento_id"]), $this->dados_item["cobranca_vencimento_id"]), $this->conteudo);
                $this->conteudo = str_replace('[cobranca_formato]', $this->geraSelect($this->buscaCobrancaFormatos("1", $this->permissoes["clientes"]["mudar_formato"], $this->dados_item["cobranca_formato_id"], $this->dados_item["origem_id"], $emails), "{$this->dados_item["cobranca_formato_tipo_id"]},{$this->dados_item["cobranca_formato_id"]}").($this->dados_item["cobranca_formato_id"] == 27 && $emails ? '<input type="checkbox" id="cobranca_formato_email" name="cobranca_formato_email" '.($this->dados_item["copia_cobranca_email"] == 1 ? "checked" : "").' ><label for="cobranca_formato_email">Enviar Cópia por Email</label>&nbsp;' : "") , $this->conteudo);
                $this->conteudo = str_replace('[cobranca_debito_operacao]', $this->dados_item["cobranca_debito_operacao"], $this->conteudo);
                $this->conteudo = str_replace('[cobranca_debito_agencia]', $this->dados_item["cobranca_debito_agencia"], $this->conteudo);
                $this->conteudo = str_replace('[cobranca_debito_conta]', $this->dados_item["cobranca_debito_conta"], $this->conteudo);
                $this->conteudo = str_replace('[operacoes]', $this->buscaClientesOperacoes($this->dados_item["id"], 1), $this->conteudo);
                $this->conteudo = str_replace('[retornos]', $this->buscaClientesRetornos($this->dados_item["id"]), $this->conteudo);
                $this->conteudo = str_replace('[assinaturas]', $this->buscaAssinaturasCliente($this->dados_item["id"]), $this->conteudo);
                $this->conteudo = str_replace('[responsabilidades]', $this->buscaClientesResponsabilidades($this->dados_item["id"]), $this->conteudo);

                // Desabilitar Campos
                foreach ($campos_status as $campo => $valor) {
                    $this->conteudo = str_replace("[{$campo}_disabled]", ($valor) ? "disabled" : "", $this->conteudo);
                }

                $assinaturas_parametros = array();
                $assinaturas_parametros[] = $this->dados_item["id"];
                $assinaturas_parametros[] = $this->permissoes["assinaturas"]["ver"] ? "1" : "0";
                $assinaturas_parametros[] = 0;
				
                $this->conteudo = str_replace('[assinaturas_parametros]', implode(",", $assinaturas_parametros), $this->conteudo);

                if ($this->permissoes["assinaturas"]["adicionar"]) {
                    $this->conteudo = str_replace("[assinaturas_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="assinaturas_adicionar" value="Adicionar" onClick="adicionarAssinatura(' . $this->dados_item["id"] . ')">', $this->conteudo);
                } else {
                    $this->conteudo = str_replace("[assinaturas_adicionar]", '', $this->conteudo);
                }

                if ($this->permissoes["clientes_retornos"]["adicionar"]) {
                    $this->conteudo = str_replace("[retornos_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="retornos_adicionar" value="Adicionar" onClick="adicionarClienteRetorno(' . $this->dados_item["id"] . ')">', $this->conteudo);
                } else {
                    $this->conteudo = str_replace("[retornos_adicionar]", '', $this->conteudo);
                }

                if ($this->permissoes["clientes_responsabilidades"]["adicionar"]) {
                    $this->conteudo = str_replace("[responsabilidades_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="responsabilidades_adicionar" value="Adicionar" onClick="adicionarClienteResponsabilidade(' . $this->dados_item["id"] . ')">', $this->conteudo);
                } else {
                    $this->conteudo = str_replace("[responsabilidades_adicionar]", '', $this->conteudo);
                }

                // pfpj & ccsc
                $pfpj = ($this->dados_item["tipo_id"] == 1) ? 1 : 0;
                $ccsc = ($this->dados_item["condominio_id"]) ? 1 : 0;
                $this->conteudo = str_replace('[pfpj]', $pfpj, $this->conteudo);
                $this->conteudo = str_replace('[ccsc]', $ccsc, $this->conteudo);

                // Ações
                $this->conteudo = str_replace('[acoes]', $this->buscaAcoes($this->dados_item["id"]), $this->conteudo);

                // Botões
                $botoes = array();
                $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
                if ($this->dados_item["status_id"] == 4 && $this->permissoes["clientes"]["remover"] && (!$this->dados_item["responsavel_id"] || $this->dados_item["responsavel_id"] == $_SESSION["usuario"] || $this->permissoes["clientes"]["mudar_responsavel"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
                $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
                if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"]) $botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

                $this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=editar_dm&i=' . $this->dados_item["id"], $this->conteudo);

            } else {

                $_POST["campos_desabilitados"] = explode(",", $_POST["campos_desabilitados"]);
                $_POST["cobranca_formato"] = $_POST["cobranca_formato"] ? substr($_POST["cobranca_formato"], strpos($_POST["cobranca_formato"], ",") + 1) : $_POST["cobranca_formato"];
                
                foreach ($campos_status as $campo => $valor) {
					if ($valor) {
                        $_POST[$campo] = $this->dados_item[$campo];
					}
				}

				//for ($i = 0; $i < sizeof($_POST["campos_desabilitados"]); $i++) {
                //    if (!$_POST[$_POST["campos_desabilitados"][$i]]) {
                //        $_POST[$_POST["campos_desabilitados"][$i]] = $this->dados_item[$_POST["campos_desabilitados"][$i]];
				//	}
                //}
				
                if (!$_POST["cobranca_vencimento"] /* && $campos_status["cobranca_vencimento"] */) {
                    $_POST["cobranca_vencimento"] = $this->dados_item["cobranca_vencimento_id"];
				}
				
                if (!$_POST["cobranca_formato"] /* && $campos_status["cobranca_formato"] */) {
                    $_POST["cobranca_formato"] = $this->dados_item["cobranca_formato_id"];
				}

                $_POST['cobranca_debito_operacao'] = strtoupper($_POST['cobranca_debito_operacao']);
                $_POST['cobranca_debito_agencia'] = strtoupper($_POST['cobranca_debito_agencia']);
                $_POST['cobranca_debito_conta'] = strtoupper($_POST['cobranca_debito_conta']);
                $_POST["copia_cobranca"] = $_POST["cobranca_formato_email"] && $_POST["cobranca_formato"] == 27 ? 1 : 0;

                $query = "
					UPDATE 
						tab_clientes 
					SET 
						cli_ccd_vencimento = '{$_POST["cobranca_vencimento"]}', 
						cof_id = '{$_POST["cobranca_formato"]}', 
						cli_ccd_debito_operacao = '{$_POST["cobranca_debito_operacao"]}', 
						cli_ccd_debito_agencia = '{$_POST["cobranca_debito_agencia"]}', 
						cli_ccd_debito_conta = '{$_POST["cobranca_debito_conta"]}', 
						cli_copia_cobranca = {$_POST["copia_cobranca"]}
					WHERE 
						cli_id = '{$_GET["i"]}'";
                
                $this->bancodedados->query($query);

                if ($this->bancodedados->linhasAfetadas()) {
                    $query = "UPDATE tab_contratos SET cor_sincronizado = 0 WHERE cli_id = '{$_GET["i"]}' AND crs_id IN (11, 12, 13, 14)";
                    $this->bancodedados->query($query);
                }

                $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
                $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

                header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dm&i={$_GET["i"]}");

            }

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }
    }

    ##########################
    ### Ver Dados Técnicos ###
    ##########################

    function ver_dt() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            $this->conteudo .= $this->carregaArquivo("clientes.ver_dt.inc.php");

            // Dados Gerais
            $dados_pendentes = $this->buscaDadosPendentes($this->dados_item);
            $this->conteudo = str_replace('[cliente_id]', $this->dados_item["id"], $this->conteudo);
            $this->conteudo = str_replace('[alerta_pendencias]', $this->geraAlertaPendencias($dados_pendentes), $this->conteudo);
            $this->conteudo = str_replace('[pendencias]', $this->geraPendencias($dados_pendentes), $this->conteudo);
            $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
            $this->conteudo = str_replace('[cpf_cnpj]', $this->dados_item["cpf_cnpj"], $this->conteudo);
            $this->conteudo = str_replace('[situacao_financeira]', $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
            $this->conteudo = str_replace('[telefone]', $this->dados_item["telefone"], $this->conteudo);
            $this->conteudo = str_replace('[origem]', $this->dados_item["origem_nome"], $this->conteudo);
            $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
            $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);

			// IPTV
			$this->conteudo = str_replace("[iptv_listagem]", $this->geraClientesIptvListagem($_GET["i"]), $this->conteudo);

            // Equipamentos Comodatos
            $this->conteudo = str_replace("[equipamentos_comodatos]", $this->geraListagemClientesComodatos($this->dados_item["id"], true), $this->conteudo);

			// Ajustes Programados
			$this->conteudo = str_replace("[ajustes]", $this->buscaClientesAjustes($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace("[ajustes_adicionar]", $this->buscaClientesAjustesAdicionar($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace("[ajustes_bloqueio_temporario_adicionar]", $this->buscaClientesAjustesBloqueioTemporarioAdicionar ( $this->dados_item ["id"] ), $this->conteudo );

            // Visitas
            $this->conteudo = str_replace("[visitas_listagem]", $this->geraClientesVisitasListagem($_GET["i"]), $this->conteudo);

            // Ações
            $this->conteudo = str_replace('[acoes]', $this->buscaAcoes($this->dados_item["id"]), $this->conteudo);

            // Botões
            $botoes = array();
            if ($this->ehBloqueavelFinanceiro()) $botoes[] = '<input type="button" name="bloquear_financeiro" class="buttonAzulEscuroBranco11" value="Bloquear Financeiro" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=bloquear_financeiro&i=' . $this->dados_item["id"] . '\';">';
            if ($this->permissoes["clientes"]["editar_dt"]) $botoes[] = '<input type="button" name="editar_dt" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar_dt&i=' . $this->dados_item["id"] . '\';">';
            if ($this->dados_item["status_id"] == 4 && $this->permissoes["clientes"]["remover"] && ($this->dados_item["responsavel_id"] == $_SESSION["usuario"] || $this->permissoes["clientes"]["mudar_responsavel"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
            if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"]) $botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

            $this->conteudo = str_replace("[action]", '?m=' . $this->dados_modulo["nome"] . '&a=editar_dt&i=' . $this->dados_item["id"], $this->conteudo);

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    #############################
    ### Editar Dados Técnicos ###
    #############################

    function editar_dt() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            if (!$_POST) {

                $dados_pendentes = $this->buscaDadosPendentes($this->dados_item);

                $this->conteudo .= $this->carregaArquivo("clientes.editar_dt.inc.php");

                // Ações
                $this->conteudo = str_replace("[acoes]", $this->buscaAcoes($this->dados_item["id"]), $this->conteudo);

                // Dados Gerais
                $this->conteudo = str_replace("[cliente_id]", $this->dados_item["id"], $this->conteudo);
                $this->conteudo = str_replace("[alerta_pendencias]", $this->geraAlertaPendencias($dados_pendentes), $this->conteudo);
                $this->conteudo = str_replace("[pendencias]", $this->geraPendencias($dados_pendentes), $this->conteudo);
                $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
                $this->conteudo = str_replace("[cpf_cnpj]", $this->dados_item["cpf_cnpj"], $this->conteudo);
                $this->conteudo = str_replace("[situacao_financeira]", $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
                $this->conteudo = str_replace("[endereco]", $this->dados_item["endereco"], $this->conteudo);
                $this->conteudo = str_replace("[telefone]", $this->dados_item["telefone"], $this->conteudo);
                $this->conteudo = str_replace("[origem]", $this->dados_item["origem_nome"], $this->conteudo);
                $this->conteudo = str_replace("[responsavel]", $this->dados_item["responsavel_nome"], $this->conteudo);
                $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);

                // IPTV
                $this->conteudo = str_replace("[iptv_listagem]", $this->geraClientesIptvListagem($_GET["i"]), $this->conteudo);

                // Equipamentos Comodatos
                $this->conteudo = str_replace("[equipamentos_comodatos]", $this->geraListagemClientesComodatos($this->dados_item["id"], true), $this->conteudo);

				// Ajustes Programados
				$this->conteudo = str_replace("[ajustes]", $this->buscaClientesAjustes($this->dados_item["id"]), $this->conteudo);
                $this->conteudo = str_replace("[ajustes_adicionar]", $this->buscaClientesAjustesAdicionar($this->dados_item["id"]), $this->conteudo);
                $this->conteudo = str_replace("[ajustes_bloqueio_temporario_adicionar]", $this->buscaClientesAjustesBloqueioTemporarioAdicionar ( $this->dados_item ["id"] ), $this->conteudo );

                // Visitas
                $this->conteudo = str_replace("[visitas_listagem]", $this->geraClientesVisitasListagem($_GET["i"]), $this->conteudo);

                // Botões
                $botoes = array();
                if ($this->ehBloqueavelFinanceiro()) $botoes[] = '<input type="button" name="bloquear_financeiro" class="buttonAzulEscuroBranco11" value="Bloquear Financeiro" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=bloquear_financeiro&i=' . $this->dados_item["id"] . '\';">';
                $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
                if ($this->dados_item["status_id"] == 4 && $this->permissoes["clientes"]["remover"] && (!$this->dados_item["responsavel_id"] || $this->dados_item["responsavel_id"] == $_SESSION["usuario"] || $this->permissoes["clientes"]["mudar_responsavel"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
                $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
                if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"]) $botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

                $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar_dt&i={$this->dados_item["id"]}", $this->conteudo);

            } else {

                $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
                $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
                header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dt&i={$_GET["i"]}");

            }

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    function geraListagemClientesComodatos($cliente_id, $exibe_inativos = false) {

        $clientes_comodatos = controle::instanciaObjeto("clientes_comodatos", $this->bancodedados, $this->permissoes, false);
		
        return $clientes_comodatos->geraListagemClientesComodatos($cliente_id, $exibe_inativos);

    }

    #################################
    ### Ver Dados Administrativos ###
    #################################

    function ver_da() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            $dados_pendentes = $this->buscaDadosPendentes($this->dados_item);

            $this->conteudo .= $this->carregaArquivo("clientes.ver_da.inc.php");

            // Dados Gerais
            $this->conteudo = str_replace('[cliente_id]', $this->dados_item["id"], $this->conteudo);
            $this->conteudo = str_replace('[alerta_pendencias]', $this->geraAlertaPendencias($dados_pendentes), $this->conteudo);
            $this->conteudo = str_replace('[pendencias]', $this->geraPendencias($dados_pendentes), $this->conteudo);
            $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
            $this->conteudo = str_replace('[cpf_cnpj]', $this->dados_item["cpf_cnpj"], $this->conteudo);
            $this->conteudo = str_replace('[situacao_financeira]', $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace("[endereco]", $this->dados_item["endereco"], $this->conteudo);
            $this->conteudo = str_replace("[telefone]", $this->dados_item["telefone"], $this->conteudo);
            $this->conteudo = str_replace("[origem]", $this->dados_item["origem_nome"], $this->conteudo);
            $this->conteudo = str_replace("[responsavel]", $this->dados_item["responsavel_nome"], $this->conteudo);
            $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);

            $this->conteudo = str_replace("[contratos]", $this->buscaClientesContratos($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace("[contratos_adicionar]", $this->buscaClientesContratosAdicionar($this->dados_item["id"]), $this->conteudo);

            $this->conteudo = str_replace("[pesquisas]", $this->buscaPesquisasAplicacoes($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace("[pesquisas_adicionar]", $this->buscaPesquisasAplicacoesAdicionar($this->dados_item["id"]), $this->conteudo);

            $this->conteudo = str_replace("[cancelamentos]", $this->buscaClientesCancelamentos($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace("[cancelamentos_adicionar]", $this->buscaClientesCancelamentosAdicionar($this->dados_item["id"]), $this->conteudo);

            // Ações
            $this->conteudo = str_replace('[acoes]', $this->buscaAcoes($this->dados_item["id"]), $this->conteudo);

            // Botões
            $botoes = array();
            if ($this->permissoes["clientes"]["editar_da"]) $botoes[] = '<input type="button" name="editar_da" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar_da&i=' . $this->dados_item["id"] . '\';">';
            if ($this->dados_item["status_id"] == 4 && $this->permissoes["clientes"]["remover"] && (!$this->dados_item["responsavel_id"] || $this->dados_item["responsavel_id"] == $_SESSION["usuario"] || $this->permissoes["clientes"]["mudar_responsavel"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
            if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"]) $botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

            $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
            $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    function possuiAssinaturaPrincipalSemContrato($cliente_id) {

        $servicos_disponiveis = array();
        $servicos_existentes = array();

        $query = "
            SELECT DISTINCT
                pls_id
            FROM
                tab_planos
                INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)
                LEFT OUTER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
            WHERE
                tab_assinaturas.cli_id = $cliente_id AND
                tab_assinaturas.ast_id = 1 AND
                rel_ass_cor_estacontido.cor_id IS NULL AND
                tab_planos.plt_id = 1";

        $query = $this->bancodedados->query($query);

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $servicos_disponiveis[] = $dados["pls_id"];
        }

        $query = "
            SELECT DISTINCT
                pls_id
            FROM
                tab_contratos
                INNER JOIN rel_ass_cor_estacontido ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)
                INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
                INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
            WHERE
                tab_contratos.cli_id = $cliente_id AND
                tab_contratos.crs_id NOT IN (5, 22, 23, 24)";

        $query = $this->bancodedados->query($query);

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $servicos_existentes[] = $dados["pls_id"];
        }

        return array_diff($servicos_disponiveis, $servicos_existentes);

    }

    ####################################
    ### Editar Dados Administrativos ###
    ####################################

    function editar_da() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            if (!$_POST) {

                $dados_pendentes = $this->buscaDadosPendentes($this->dados_item);

                $this->conteudo .= $this->carregaArquivo("clientes.editar_da.inc.php");

                // Dados Gerais
                $this->conteudo = str_replace("[cliente_id]", $this->dados_item["id"], $this->conteudo);
                $this->conteudo = str_replace("[alerta_pendencias]", $this->geraAlertaPendencias($dados_pendentes), $this->conteudo);
                $this->conteudo = str_replace("[pendencias]", $this->geraPendencias($dados_pendentes), $this->conteudo);
                $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
                $this->conteudo = str_replace("[cpf_cnpj]", $this->dados_item["cpf_cnpj"], $this->conteudo);
                $this->conteudo = str_replace("[situacao_financeira]", $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
                $this->conteudo = str_replace("[endereco]", $this->dados_item["endereco"], $this->conteudo);
                $this->conteudo = str_replace("[telefone]", $this->dados_item["telefone"], $this->conteudo);
                $this->conteudo = str_replace("[origem]", $this->dados_item["origem_nome"], $this->conteudo);
                $this->conteudo = str_replace("[responsavel]", $this->dados_item["responsavel_nome"], $this->conteudo);
                $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);

                $this->conteudo = str_replace("[contratos]", $this->buscaClientesContratos($this->dados_item["id"]), $this->conteudo);
                $this->conteudo = str_replace("[contratos_adicionar]", $this->buscaClientesContratosAdicionar($this->dados_item["id"]), $this->conteudo);

                $this->conteudo = str_replace("[cancelamentos]", $this->buscaClientesCancelamentos($this->dados_item["id"]), $this->conteudo);
                $this->conteudo = str_replace("[cancelamentos_adicionar]", $this->buscaClientesCancelamentosAdicionar($this->dados_item["id"]), $this->conteudo);

                $this->conteudo = str_replace("[pesquisas]", $this->buscaPesquisasAplicacoes($this->dados_item["id"]), $this->conteudo);
                $this->conteudo = str_replace("[pesquisas_adicionar]", $this->buscaPesquisasAplicacoesAdicionar($this->dados_item["id"]), $this->conteudo);

                // Ações
                $this->conteudo = str_replace("[acoes]", $this->buscaAcoes($this->dados_item["id"]), $this->conteudo);

                // Botões
                $botoes = array();
                $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
                if ($this->dados_item["status_id"] == 4 && $this->permissoes["clientes"]["remover"] && (!$this->dados_item["responsavel_id"] || $this->dados_item["responsavel_id"] == $_SESSION["usuario"] || $this->permissoes["clientes"]["mudar_responsavel"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
                $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
                if ($this->permissoes[$this->dados_modulo["nome"]]["migrar"]) $botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

            } else {

                $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
                $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
                header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_da&i={$_GET["i"]}");

            }

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    #######################
    ### Editar Chamados ###
    #######################

    function editar_ch() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            $dados_pendentes = $this->buscaDadosPendentes($this->dados_item);

            $this->conteudo .= $this->carregaArquivo("clientes.editar_ch.inc.php");

            // Dados Gerais
            $this->conteudo = str_replace('[cliente_id]', $this->dados_item["id"], $this->conteudo);
            $this->conteudo = str_replace('[alerta_pendencias]', $this->geraAlertaPendencias($dados_pendentes), $this->conteudo);
            $this->conteudo = str_replace('[pendencias]', $this->geraPendencias($dados_pendentes), $this->conteudo);
            $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
            $this->conteudo = str_replace('[cpf_cnpj]', $this->dados_item["cpf_cnpj"], $this->conteudo);
            $this->conteudo = str_replace('[situacao_financeira]', $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
            $this->conteudo = str_replace('[telefone]', $this->dados_item["telefone"], $this->conteudo);
            $this->conteudo = str_replace('[origem]', $this->dados_item["origem_nome"], $this->conteudo);
            $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
            $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
            $this->conteudo = str_replace("[chamados]", $this->buscaChamados($_GET["i"]), $this->conteudo);
            $this->conteudo = str_replace("[chamados_adicionar]", $this->permissoes["chamados"]["adicionar"] ? '<input type="button" name="chamados_adicionar" class="buttonAzulEscuroBranco11" value="Adicionar" onClick="adicionarChamado(\'' . $this->dados_item["id"] . '\');">' : '', $this->conteudo);

            // Ações
            $this->conteudo = str_replace('[acoes]', $this->buscaAcoes($this->dados_item["id"]), $this->conteudo);

            // Botões
            $botoes = array();
            if ($this->dados_item["status_id"] == 4 && $this->permissoes["clientes"]["remover"] && (!$this->dados_item["responsavel_id"] || $this->dados_item["responsavel_id"] == $_SESSION["usuario"] || $this->permissoes["clientes"]["mudar_responsavel"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    ####################
    ### Ver Chamados ###
    ####################

    function ver_ch() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            $dados_pendentes = $this->buscaDadosPendentes($this->dados_item);

            $this->conteudo .= $this->carregaArquivo("clientes.ver_ch.inc.php");
            // Dados Gerais

            $this->conteudo = str_replace('[cliente_id]', $this->dados_item["id"], $this->conteudo);
            $this->conteudo = str_replace('[alerta_pendencias]', $this->geraAlertaPendencias($dados_pendentes), $this->conteudo);
            $this->conteudo = str_replace('[pendencias]', $this->geraPendencias($dados_pendentes), $this->conteudo);
            $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
            $this->conteudo = str_replace('[cpf_cnpj]', $this->dados_item["cpf_cnpj"], $this->conteudo);
            $this->conteudo = str_replace('[situacao_financeira]', $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace("[endereco]", $this->dados_item["endereco"], $this->conteudo);
            $this->conteudo = str_replace("[telefone]", $this->dados_item["telefone"], $this->conteudo);
            $this->conteudo = str_replace("[origem]", $this->dados_item["origem_nome"], $this->conteudo);
            $this->conteudo = str_replace("[responsavel]", $this->dados_item["responsavel_nome"], $this->conteudo);
            $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
            $this->conteudo = str_replace("[chamados]", $this->buscaChamados($_GET["i"]), $this->conteudo);
            $this->conteudo = str_replace("[chamados_adicionar]", $this->permissoes["chamados"]["adicionar"] ? '<input type="button" name="chamados_adicionar" class="buttonAzulEscuroBranco11" value="Adicionar" onClick="adicionarChamado(\'' . $this->dados_item["id"] . '\');">' : '', $this->conteudo);

            // Ações
            $this->conteudo = str_replace('[acoes]', $this->buscaAcoes($this->dados_item["id"]), $this->conteudo);

            // Botões
            $botoes = array();
            if ($this->dados_item["status_id"] == 4 && $this->permissoes["clientes"]["remover"] && (!$this->dados_item["responsavel_id"] || $this->dados_item["responsavel_id"] == $_SESSION["usuario"] || $this->permissoes["clientes"]["mudar_responsavel"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    #####################
    ### Ver Mensagens ###
    #####################

    function ver_me() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            $dados_pendentes = $this->buscaDadosPendentes($this->dados_item);

            $this->conteudo .= $this->carregaArquivo("clientes.ver_me.inc.php");
            // Dados Gerais

            $this->conteudo = str_replace('[cliente_id]', $this->dados_item["id"], $this->conteudo);
            $this->conteudo = str_replace('[alerta_pendencias]', $this->geraAlertaPendencias($dados_pendentes), $this->conteudo);
            $this->conteudo = str_replace('[pendencias]', $this->geraPendencias($dados_pendentes), $this->conteudo);
            $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
            $this->conteudo = str_replace('[cpf_cnpj]', $this->dados_item["cpf_cnpj"], $this->conteudo);
            $this->conteudo = str_replace('[situacao_financeira]', $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
            $this->conteudo = str_replace("[endereco]", $this->dados_item["endereco"], $this->conteudo);
            $this->conteudo = str_replace("[telefone]", $this->dados_item["telefone"], $this->conteudo);
            $this->conteudo = str_replace("[origem]", $this->dados_item["origem_nome"], $this->conteudo);
            $this->conteudo = str_replace("[responsavel]", $this->dados_item["responsavel_nome"], $this->conteudo);
            $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
            $this->conteudo = str_replace("[mensagens]", $this->buscaMensagens($_GET["i"]), $this->conteudo);

            // Ações
            $this->conteudo = str_replace('[acoes]', $this->buscaAcoes($this->dados_item["id"]), $this->conteudo);

            // Botões
            $botoes = array();
            if ($this->dados_item["status_id"] == 4 && $this->permissoes["clientes"]["remover"] && (!$this->dados_item["responsavel_id"] || $this->dados_item["responsavel_id"] == $_SESSION["usuario"] || $this->permissoes["clientes"]["mudar_responsavel"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    ##########################
    ### Migrar Residencial ###
    ##########################

    function migrar_residencial() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            if (!$_POST) {

                $this->conteudo .= $this->carregaArquivo("clientes.migrar_residencial.inc.php");
                $this->conteudo = str_replace('[cliente_id]', $this->dados_item["id"], $this->conteudo);
                $this->conteudo = str_replace('[cliente_nome]', $this->dados_item["nome"], $this->conteudo);

                $botoes = array();
                $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
                $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
                $this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);

                $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=migrar_residencial&i={$_GET["i"]}", $this->conteudo);

            } else {

                $query = "
                    UPDATE
                        tab_clientes
                    SET
                        cob_id = NULL,
                        con_id = NULL,
                        cep_id = {$this->dados_item["cep_id"]},
                        cli_numero = {$this->dados_item["numero"]}
                    WHERE
                        cli_id = {$this->dados_item["id"]}";
                $this->bancodedados->query($query);

                header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$this->dados_item["id"]}");

            }

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    ##############
    ### Migrar ###
    ##############

    function migrar() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            if (!$_POST) {

                $this->conteudo .= $this->carregaArquivo("clientes.migrar.inc.php");

                $this->conteudo = str_replace('[cliente_origem_id]', $this->dados_item["id"], $this->conteudo);
                $this->conteudo = str_replace('[cliente_origem_nome]', $this->dados_item["nome"] . ($this->dados_item["cpf_cnpj"] ? " ({$this->dados_item["cpf_cnpj"]})" : ""), $this->conteudo);

                $botoes = array();
                $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
                $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

                $this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $_GET["i"], $this->conteudo);

            } else {

                $this->migraCliente($_GET["i"], $_POST["cliente_destino_id"], $_POST["comentarios"]);

                header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_POST["cliente_destino_id"]}");

            }

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    function migraCliente($origem_id, $destino_id, $comentario = null) {

        $comentario = "Migração de cliente (ID original: $origem_id)" . ($comentario ? "<br><b>Comentário Adicional<b>:" . addslashes($comentario) : "");

        $query = "SELECT ajf_id AS id FROM tab_ajustes_financeiros WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));

        for ($i = 0; $i < sizeof($dados); $i++) {

            $query = "UPDATE tab_ajustes_financeiros SET cli_id = $destino_id WHERE ajf_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);

            $query = "INSERT INTO tab_ajustes_financeiros_comentarios (afm_datahora, ajf_id, afm_conteudo, usu_id) VALUES (NOW(), {$dados[$i]["id"]}, '$comentario', {$_SESSION["usuario"]})";
            $this->bancodedados->query($query);

        }

        $query = "SELECT ass_id AS id FROM tab_assinaturas WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));

        for ($i = 0; $i < sizeof($dados); $i++) {

            $query = "UPDATE tab_assinaturas SET cli_id = $destino_id WHERE ass_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);

            $query = "SELECT dec_id AS id FROM tab_descontos_concessoes WHERE ass_id = {$dados[$i]["id"]}";
            $subdados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
            for ($j = 0; $j < sizeof($subdados); $j++) {
                $query = "INSERT INTO tab_descontos_concessoes_comentarios (dcc_datahora, dec_id, dcc_conteudo, usu_id) VALUES (NOW(), {$subdados[$j]["id"]}, '$comentario', {$_SESSION["usuario"]})";
                
                // Descomentar a linha abaixo para atualizar o comentário na tabela tab_descontos_concessoes_comentarios
                $this->bancodedados->query($query);
            }

            $query = "SELECT clp_id AS id FROM tab_clientes_pontos WHERE ass_id = {$dados[$i]["id"]}";
            $subdados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
            for ($j = 0; $j < sizeof($subdados); $j++) {
                $this->atualizaClientePontoMigrado($subdados[$j]["id"]);
                $query = "INSERT INTO tab_clientes_pontos_comentarios (cpc_datahora, clp_id, cpc_conteudo, usu_id) VALUES (NOW(), {$subdados[$j]["id"]}, '$comentario', {$_SESSION["usuario"]})";
                $this->bancodedados->query($query);
            }

            $query = "SELECT cle_id AS id FROM tab_clientes_emails WHERE ass_id = {$dados[$i]["id"]}";
            $subdados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
            for ($j = 0; $j < sizeof($subdados); $j++) {
                $query = "INSERT INTO tab_clientes_emails_comentarios (cec_datahora, cle_id, cec_conteudo, usu_id) VALUES (NOW(), {$subdados[$j]["id"]}, '$comentario', {$_SESSION["usuario"]})";
                $this->bancodedados->query($query);
            }

            $query = "SELECT ven_id AS id FROM tab_vendas WHERE ass_id = {$dados[$i]["id"]}";
            $subdados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
            for ($j = 0; $j < sizeof($subdados); $j++) {

                $query = "INSERT INTO tab_vendas_comentarios (veo_datahora, ven_id, veo_conteudo, usu_id) VALUES (NOW(), {$subdados[$j]["id"]}, '$comentario', {$_SESSION["usuario"]})";
                $this->bancodedados->query($query);

                $query = "SELECT vec_id AS id FROM tab_vendas_comissoes WHERE ven_id = {$subdados[$j]["id"]}";
                $subsubdados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
                for ($k = 0; $k < sizeof($subsubdados); $k++) {
                    $query = "INSERT INTO tab_vendas_comissoes_comentarios (vco_datahora, vec_id, vco_conteudo, usu_id) VALUES (NOW(), {$subsubdados[$k]["id"]}, '$comentario', {$_SESSION["usuario"]})";
                    $this->bancodedados->query($query);
                }

            }

        }

        $query = "SELECT can_id AS id FROM tab_cancelamentos WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
        for ($i = 0; $i < sizeof($dados); $i++) {
            $query = "UPDATE tab_cancelamentos SET cli_id = $destino_id WHERE can_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);
            $parametros = serialize(array("descricao" => $comentario));
            $query = "INSERT INTO tab_cancelamentos_etapas (can_id, cae_inicio, cae_termino, cae_parametros, usu_id, cet_id, cen_id, ces_id) VALUES ({$dados[$i]["id"]}, NOW(), NOW(), '{$parametros}', {$_SESSION["usuario"]}, 3, 3, 2)";
            $this->bancodedados->query($query);
        }

        $query = "SELECT cha_id AS id FROM tab_chamados WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
        for ($i = 0; $i < sizeof($dados); $i++) {
            $query = "UPDATE tab_chamados SET cli_id = $destino_id WHERE cha_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);
            $conteudo = serialize(array("conteudo" => $comentario));
            $query = "INSERT INTO tab_chamados_etapas (che_datahora, che_conteudo, cha_id, usu_id, cen_id, cet_id) VALUES (NOW(), '$conteudo', {$dados[$i]["id"]}, {$_SESSION["usuario"]}, NULL, 5)";
            $this->bancodedados->query($query);
        }

        $query = "DELETE FROM tab_clientes_cobranca_dados WHERE cli_id = $origem_id";
        $this->bancodedados->query($query);

        $query = "SELECT ccc_id AS id FROM tab_clientes_consultas_credito WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
        for ($i = 0; $i < sizeof($dados); $i++) {
            $query = "UPDATE tab_clientes_consultas_credito SET cli_id = $destino_id WHERE ccc_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);
        }

        $query = "SELECT ccm_id AS id FROM tab_clientes_contatos_emails WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
        for ($i = 0; $i < sizeof($dados); $i++) {
            $query = "UPDATE tab_clientes_contatos_emails SET cli_id = $destino_id WHERE ccm_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);
        }

        $query = "SELECT ccl_id AS id FROM tab_clientes_contatos_telefones WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
        for ($i = 0; $i < sizeof($dados); $i++) {
            $query = "UPDATE tab_clientes_contatos_telefones SET cli_id = $destino_id WHERE ccl_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);
        }

        $query = "SELECT cln_id AS id FROM tab_clientes_responsabilidades WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
        for ($i = 0; $i < sizeof($dados); $i++) {
            $query = "UPDATE tab_clientes_responsabilidades SET cli_id = $destino_id WHERE cln_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);
            $query = "INSERT INTO tab_clientes_responsabilidades_comentarios (crc_datahora, cln_id, crc_conteudo, usu_id) VALUES (NOW(), {$dados[$i]["id"]}, '$comentario', {$_SESSION["usuario"]})";
            $this->bancodedados->query($query);
        }

        $query = "SELECT clr_id AS id FROM tab_clientes_retornos WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
        for ($i = 0; $i < sizeof($dados); $i++) {
            $query = "UPDATE tab_clientes_retornos SET cli_id = $destino_id WHERE clr_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);
        }

        $query = "SELECT coa_id AS id FROM tab_cobrancas WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
        for ($i = 0; $i < sizeof($dados); $i++) {
            $query = "UPDATE tab_cobrancas SET cli_id = $destino_id WHERE coa_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);
            $query = "INSERT INTO tab_cobrancas_comentarios (com_datahora, coa_id, com_conteudo, usu_id) VALUES (NOW(), {$dados[$i]["id"]}, '$comentario', {$_SESSION["usuario"]})";
            $this->bancodedados->query($query);
        }

        $query = "SELECT cor_id AS id FROM tab_contratos WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
        for ($i = 0; $i < sizeof($dados); $i++) {
            $query = "UPDATE tab_contratos SET cli_id = $destino_id WHERE cor_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);
            $query = "INSERT INTO tab_contratos_comentarios (coo_datahora, cor_id, coo_conteudo, ccc_id, usu_id) VALUES (NOW(), {$dados[$i]["id"]}, '$comentario', 21, {$_SESSION["usuario"]})";
            $this->bancodedados->query($query);
        }

        $query = "SELECT men_id AS id FROM tab_mensagens WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
        for ($i = 0; $i < sizeof($dados); $i++) {
            $query = "UPDATE tab_mensagens SET cli_id = $destino_id WHERE men_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);
            $query = "INSERT INTO tab_mensagens_comentarios (men_id, mcc_id, mec_conteudo, mec_datahora, usu_id) VALUES ({$dados[$i]["id"]}, 4, '$comentario', NOW(), {$_SESSION["usuario"]})";
            $this->bancodedados->query($query);
        }

        $query = "SELECT opc_id AS id FROM tab_operacoes_contatos WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
        for ($i = 0; $i < sizeof($dados); $i++) {
            $query = "UPDATE tab_operacoes_contatos SET cli_id = $destino_id WHERE opc_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);
        }

        $query = "SELECT saj_id AS id FROM tab_servicos_ajustes WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
        for ($i = 0; $i < sizeof($dados); $i++) {
            $query = "UPDATE tab_servicos_ajustes SET cli_id = $destino_id WHERE saj_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);
            $query = "INSERT INTO tab_servicos_ajustes_comentarios (saj_id, sam_datahora, sam_conteudo, usu_id) VALUES ({$dados[$i]["id"]}, NOW(), '$comentario', {$_SESSION["usuario"]})";
            $this->bancodedados->query($query);
        }

        $query = "SELECT vis_id AS id FROM tab_visitas WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
        for ($i = 0; $i < sizeof($dados); $i++) {
            $query = "UPDATE tab_visitas SET cli_id = $destino_id WHERE vis_id = {$dados[$i]["id"]}";
            $this->bancodedados->query($query);
            $query = "INSERT INTO tab_visitas_comentarios (vic_datahora, vis_id, vic_conteudo, vcc_id, usu_id) VALUES (NOW(), {$dados[$i]["id"]}, '$comentario', 2, {$_SESSION["usuario"]})";
            $this->bancodedados->query($query);
        }

        $query = "SELECT cli_nascimento AS nascimento, cli_cpf_cnpj AS cpf_cnpj FROM tab_clientes WHERE cli_id = $origem_id";
        $dados = $this->bancodedados->resultado($this->bancodedados->query($query));

        if ($dados["nascimento"]) {
            $query = "UPDATE tab_clientes SET cli_nascimento = IF('{$dados["nascimento"]}' != '', '{$dados["nascimento"]}', NULL) WHERE cli_id = $destino_id AND cli_nascimento IS NULL";
            $this->bancodedados->query($query);
        }

        if ($dados["cpf_cnpj"]) {
            $query = "UPDATE tab_clientes SET cli_cpf_cnpj = IF('{$dados["cpf_cnpj"]}' != '', '{$dados["cpf_cnpj"]}', NULL) WHERE cli_id = $destino_id AND cli_cpf_cnpj IS NULL";
            $this->bancodedados->query($query);
        }

        $query = "UPDATE tab_clientes_iptv SET cli_id = $destino_id WHERE cli_id = $origem_id";
        $this->bancodedados->query($query);

        $query = "UPDATE tab_central_telefonica_ligacoes SET cli_id = $destino_id WHERE cli_id = $origem_id";
        $this->bancodedados->query($query);
        $query = "UPDATE tab_operacoes_lotes_responsabilidades SET cli_id = $destino_id WHERE cli_id = $origem_id";
        $this->bancodedados->query($query);
        $query = "UPDATE tab_pesquisas_aplicacoes SET cli_id = $destino_id WHERE cli_id = $origem_id";
        $this->bancodedados->query($query);
        $query = "UPDATE tab_clientes_comodatos SET cli_id = $destino_id WHERE cli_id = $origem_id";
        $this->bancodedados->query($query);
        $query = "UPDATE tab_faturas SET cli_id = $destino_id WHERE cli_id = $origem_id";
        $this->bancodedados->query($query);
        $query = "UPDATE tab_faturas_lotes_clientes SET cli_id = $destino_id WHERE cli_id = $origem_id";
        $this->bancodedados->query($query);
        $query = "UPDATE tab_clientes_negativacoes SET cli_id = $destino_id WHERE cli_id = $origem_id";
        $this->bancodedados->query($query);
        $query = "DELETE FROM tab_clientes WHERE cli_id = $origem_id";
        $this->bancodedados->query($query);

        $this->removeClienteContatosEmailsDuplicados($destino_id);
        $this->removeClienteContatosTelefonesDuplicados($destino_id);
        $this->atualizaStatusCliente($destino_id);

    }

    function atualizaClientePontoMigrado($ponto_id) {

        $query = "
            UPDATE
                tab_clientes
                INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
                INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
                INNER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
                INNER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
                INNER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
            SET
                tab_clientes_pontos.sei_id = tab_condominios_blocos.sei_id
            WHERE
                tab_clientes_pontos.clp_id = $ponto_id AND
                tab_clientes_pontos.cps_id IN (1, 2)";
        $this->bancodedados->query($query);

        if ($this->bancodedados->linhasAfetadas()) {
            $clientes_pontos = controle::instanciaObjeto("clientes_pontos", $this->bancodedados, $this->permissoes, false);
            $clientes_pontos->atualizaPontoIP($ponto_id);
        }

    }

    function removeClienteContatosEmailsDuplicados($cliente_id) {

        $query = "
            SELECT
                ccm_id AS id,
                CONCAT(ccm_usuario, '@', ccm_dominio) AS email
            FROM
                tab_clientes_contatos_emails
            WHERE
                cli_id = $cliente_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));

        if ($dados) {
            $emails_utilizados = array();
            for ($i = 0; $i < sizeof($dados); $i++) {
                if (!in_array($dados[$i]["email"], $emails_utilizados)) {
                    $emails_utilizados[] = $dados[$i]["email"];
                } else {
                    $query = "DELETE FROM tab_clientes_contatos_emails WHERE ccm_id = {$dados[$i]["id"]}";
                    $this->bancodedados->query($query);
                }
            }
        }

    }

    function removeClienteContatosTelefonesDuplicados($cliente_id) {

        $query = "
            SELECT
                ccl_id AS id,
                CONCAT('(', ccl_ddd, ') ', ccl_numero, ' - ', IF (ccl_ramal IS NOT NULL, ccl_ramal, '')) AS telefone
            FROM
                tab_clientes_contatos_telefones
            WHERE
                cli_id = $cliente_id";
        $dados = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));

        if ($dados) {
            $telefones_utilizados = array();
            for ($i = 0; $i < sizeof($dados); $i++) {
                if (!in_array($dados[$i]["telefone"], $telefones_utilizados)) {
                    $telefones_utilizados[] = $dados[$i]["telefone"];
                } else {
                    $query = "DELETE FROM tab_clientes_contatos_telefones WHERE ccl_id = {$dados[$i]["id"]}";
                    $this->bancodedados->query($query);
                }
            }
        }

    }

    ###########################
    ### Bloquear Financeiro ###
    ###########################

    function bloquear_financeiro() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            if (!$_POST) {

                $this->conteudo .= $this->carregaArquivo("clientes.bloquear_financeiro.inc.php");
                $this->conteudo = str_replace('[cliente]', $this->dados_item["nome"], $this->conteudo);

                $botoes = array();
                $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
                $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

                $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=bloquear_financeiro&i={$_GET["i"]}", $this->conteudo);

            } else {

                $cron = controle::instanciaObjeto("cron.bloqueio_inadimplencia", $this->bancodedados);
                $cron->geraBloqueio($_GET["i"]);

                header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dt&i={$_GET["i"]}");

            }

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    ###############
    ### Remover ###
    ###############

    function remover() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            $botoes = array();

            if (!$_POST) {

                $this->conteudo .= $this->carregaArquivo("clientes.remover.inc.php");
                $this->conteudo = str_replace('[cliente]', $this->dados_item["nome"], $this->conteudo);

                $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
                $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

                $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);

            } else {

                // Verificar se o usuário tem permissão para remover o prospect (Responsável ou pode mudar o responsável)
                if ($this->dados_item["responsavel_id"] == $_SESSION["usuario"] || $this->permissoes["clientes"]["mudar_responsavel"]) {

                    // Verificar se existe algum contrato que não seja Em Aberto (Não Impresso)
                    if (!$this->possuiContratos($this->dados_item["id"], "", "", "IN", "11, 12, 13")) {

                        $this->removeCliente($this->dados_item["id"]);
                        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
                        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
                        header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");

                    } else {

                        // Existe algum contrato que não seja Em Aberto (Não Impresso) ou não possui permissão de remover contratos
                        $this->conteudo .= $this->carregaArquivo("clientes.remover.contrato.inc.php");
                        $this->conteudo = str_replace('[cliente]', $this->dados_item["nome"], $this->conteudo);
                        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
                        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

                    }

                } else {

                    // Usuário não tem permissão para remover o prospect (Responsável ou pode mudar o responsável)
                    $this->conteudo .= $this->carregaArquivo("clientes.remover.responsavel.inc.php");
                    $this->conteudo = str_replace('[cliente]', $this->dados_item["nome"], $this->conteudo);
                    $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
                    $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

                }

            }

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

    }

    function removeCliente($cliente_id) {

        $cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, $this->permissoes, false);

        $contratos = array();
        $descontos = array();
        $iptv = array();
        $autenticacoes = array();
        $emails = array();
        $dominios = array();
        $pontos = array();
        $responsabilidades = array();
        
        $query = $this->bancodedados->query("SELECT cor_id AS id FROM tab_contratos WHERE cli_id = $cliente_id");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $contratos[] = $dados["id"];
        }

        $query = $this->bancodedados->query("SELECT tab_descontos_concessoes.dec_id AS id FROM tab_assinaturas INNER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id) WHERE tab_assinaturas.cli_id = $cliente_id");
        
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $descontos[] = $dados["id"];
        }

        $query = $this->bancodedados->query("SELECT tab_clientes_iptv.cip_id AS id FROM tab_clientes_iptv WHERE tab_clientes_iptv.cli_id = $cliente_id");
        
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $iptv[] = $dados["id"];
        }

        $query = $this->bancodedados->query("SELECT tab_clientes_autenticacoes.cau_id AS id FROM tab_assinaturas INNER JOIN tab_clientes_autenticacoes ON (tab_assinaturas.ass_id = tab_clientes_autenticacoes.ass_id) WHERE tab_assinaturas.cli_id = $cliente_id");
        
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $autenticacoes[] = $dados["id"];
        }

        $query = $this->bancodedados->query("SELECT tab_clientes_emails.cle_id AS id FROM tab_assinaturas INNER JOIN tab_clientes_emails ON (tab_assinaturas.ass_id = tab_clientes_emails.ass_id) WHERE tab_assinaturas.cli_id = $cliente_id");
        
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $emails[] = $dados["id"];
        }

        $query = $this->bancodedados->query("SELECT tab_dominios.dom_id AS id FROM tab_assinaturas INNER JOIN tab_dominios ON (tab_assinaturas.ass_id = tab_dominios.ass_id) WHERE tab_assinaturas.cli_id = $cliente_id");
        
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $dominios[] = $dados["id"];
        }

        $query = $this->bancodedados->query("SELECT clp_id AS id FROM tab_assinaturas INNER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id) WHERE tab_assinaturas.cli_id = $cliente_id");
        
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $pontos[] = $dados["id"];
        }

        $query = $this->bancodedados->query("SELECT tab_clientes_responsabilidades_comentarios.crc_id AS id FROM tab_clientes_responsabilidades INNER JOIN tab_clientes_responsabilidades_comentarios ON (tab_clientes_responsabilidades.cln_id = tab_clientes_responsabilidades_comentarios.cln_id) WHERE tab_clientes_responsabilidades.cli_id = $cliente_id");
        
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $responsabilidades[] = $dados["id"];
        }

        $this->bancodedados->query("UPDATE tab_central_telefonica_ligacoes SET cli_id = NULL WHERE cli_id = $cliente_id");

        $this->bancodedados->query("DELETE FROM tab_clientes_contatos_emails WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE FROM tab_clientes_contatos_telefones WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE FROM tab_clientes_retornos WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE FROM tab_operacoes_contatos WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE tab_visitas_comentarios FROM tab_visitas INNER JOIN tab_visitas_comentarios ON (tab_visitas.vis_id = tab_visitas_comentarios.vis_id) WHERE tab_visitas.cli_id = $cliente_id");
        $this->bancodedados->query("DELETE FROM tab_visitas WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE FROM tab_operacoes_lotes_responsabilidades WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE FROM tab_clientes_consultas_credito WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE tab_mensagens_comentarios FROM tab_mensagens INNER JOIN tab_mensagens_comentarios ON (tab_mensagens.men_id = tab_mensagens_comentarios.men_id) WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE tab_mensagens_envios FROM tab_mensagens INNER JOIN tab_mensagens_envios ON (tab_mensagens.men_id = tab_mensagens_envios.men_id) WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE tab_mensagens_links FROM tab_mensagens INNER JOIN tab_mensagens_links ON (tab_mensagens.men_id = tab_mensagens_links.men_id) WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE FROM tab_mensagens WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE rel_cha_usu_ehfavorito FROM tab_chamados INNER JOIN rel_cha_usu_ehfavorito ON (tab_chamados.cha_id = rel_cha_usu_ehfavorito.cha_id) WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE rel_cha_usu_ehacompanhado FROM tab_chamados INNER JOIN rel_cha_usu_ehacompanhado ON (tab_chamados.cha_id = rel_cha_usu_ehacompanhado.cha_id) WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE tab_chamados_etapas FROM tab_chamados INNER JOIN tab_chamados_etapas ON (tab_chamados.cha_id = tab_chamados_etapas.cha_id) WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE FROM tab_chamados WHERE cli_id = $cliente_id");
        $this->bancodedados->query("UPDATE tab_cobrancas SET fat_id = NULL, fco_id = NULL, coa_grupo_id = NULL WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE tab_faturas_itens FROM tab_faturas_itens INNER JOIN tab_faturas ON (tab_faturas.fat_id = tab_faturas_itens.fat_id) WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE tab_faturas_lotes_cobrancas FROM tab_faturas_lotes_cobrancas INNER JOIN tab_cobrancas ON (tab_cobrancas.coa_id = tab_faturas_lotes_cobrancas.coa_id) WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE FROM tab_faturas WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE FROM tab_faturas_lotes_clientes WHERE cli_id = $cliente_id");

        $query = $this->bancodedados->query("SELECT coa_id AS id FROM tab_cobrancas WHERE cli_id = $cliente_id ORDER BY coa_id DESC");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $cobrancas->removerCobranca($dados["id"]);
        }

        $this->bancodedados->query("DELETE tab_ajustes_financeiros_comentarios FROM tab_ajustes_financeiros INNER JOIN tab_ajustes_financeiros_comentarios ON (tab_ajustes_financeiros_comentarios.ajf_id = tab_ajustes_financeiros.ajf_id) WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE FROM tab_ajustes_financeiros WHERE cli_id = $cliente_id");

        for ($i = 0; $i < count($iptv); $i++) {
            $this->bancodedados->query("DELETE FROM tab_clientes_iptv_ajustes WHERE cip_id = {$iptv[$i]}");
            $this->bancodedados->query("DELETE FROM rel_saj_cip_contem WHERE cip_id = {$iptv[$i]}");
            $this->bancodedados->query("DELETE FROM tab_clientes_iptv_comentarios WHERE cip_id = {$iptv[$i]}");
            $this->bancodedados->query("DELETE FROM tab_clientes_iptv WHERE cip_id = {$iptv[$i]}");
        }

        for ($i = 0; $i < count($autenticacoes); $i++) {
            $this->bancodedados->query("DELETE FROM tab_clientes_autenticacoes_ajustes WHERE cau_id = {$autenticacoes[$i]}");
            $this->bancodedados->query("DELETE FROM rel_saj_cau_contem WHERE cau_id = {$autenticacoes[$i]}");
            $this->bancodedados->query("DELETE FROM tab_clientes_autenticacoes_comentarios WHERE cau_id = {$autenticacoes[$i]}");
            $this->bancodedados->query("DELETE FROM tab_clientes_autenticacoes WHERE cau_id = {$autenticacoes[$i]}");
        }

        for ($i = 0; $i < count($emails); $i++) {
            $this->bancodedados->query("DELETE FROM tab_clientes_emails_ajustes WHERE cle_id = {$emails[$i]}");
            $this->bancodedados->query("DELETE FROM rel_saj_cle_contem WHERE cle_id = {$emails[$i]}");
            $this->bancodedados->query("DELETE FROM tab_clientes_emails_comentarios WHERE cle_id = {$emails[$i]}");
            $this->bancodedados->query("DELETE FROM tab_clientes_emails WHERE cle_id = {$emails[$i]}");
        }

        for ($i = 0; $i < count($dominios); $i++) {
            $this->bancodedados->query("DELETE FROM tab_dominios WHERE dom_id = {$dominios[$i]}");
        }

        for ($i = 0; $i < count($pontos); $i++) {
            $this->bancodedados->query("DELETE FROM rel_saj_clp_contem WHERE clp_id = {$pontos[$i]}");
            $this->bancodedados->query("DELETE FROM tab_clientes_pontos_ajustes WHERE clp_id = {$pontos[$i]}");
            $this->bancodedados->query("DELETE FROM tab_clientes_pontos_comentarios WHERE clp_id = {$pontos[$i]}");
            $this->bancodedados->query("DELETE FROM tab_clientes_pontos WHERE clp_id = {$pontos[$i]}");
        }

        for ($i = 0; $i < count($descontos); $i++) {
            $this->bancodedados->query("DELETE FROM rel_dec_cor_estacontido WHERE dec_id = {$descontos[$i]}");
            $this->bancodedados->query("DELETE FROM tab_descontos_concessoes_comentarios WHERE dec_id = {$descontos[$i]}");
            $this->bancodedados->query("DELETE FROM tab_cobrancas_detalhes WHERE dec_id = {$descontos[$i]}");
            $this->bancodedados->query("DELETE FROM tab_descontos_concessoes WHERE dec_id = {$descontos[$i]}");
        }

        $this->bancodedados->query("UPDATE tab_contratos SET cor_pai = NULL WHERE cli_id = $cliente_id");

        for ($i = 0; $i < count($contratos); $i++) {
            $this->bancodedados->query("DELETE FROM tab_clientes_comodatos WHERE cor_id = {$contratos[$i]}");
            $this->bancodedados->query("DELETE tab_vendas_comentarios FROM tab_vendas INNER JOIN tab_vendas_comentarios ON (tab_vendas.ven_id = tab_vendas_comentarios.ven_id) WHERE cor_id = {$contratos[$i]}");
            $this->bancodedados->query("DELETE tab_vendas_comissoes FROM tab_vendas INNER JOIN tab_vendas_comissoes ON (tab_vendas.ven_id = tab_vendas_comissoes.ven_id) WHERE cor_id = {$contratos[$i]}");
            $this->bancodedados->query("DELETE FROM tab_vendas WHERE cor_id = {$contratos[$i]}");
            $this->bancodedados->query("UPDATE tab_cancelamentos SET cae_id = NULL WHERE cor_id = {$contratos[$i]}");
            $this->bancodedados->query("DELETE tab_cancelamentos_etapas FROM tab_cancelamentos INNER JOIN tab_cancelamentos_etapas ON (tab_cancelamentos.can_id = tab_cancelamentos_etapas.can_id) WHERE cor_id = {$contratos[$i]}");
            $this->bancodedados->query("DELETE FROM tab_cancelamentos WHERE cor_id = {$contratos[$i]}");
            $this->bancodedados->query("DELETE FROM rel_dec_cor_estacontido WHERE cor_id = {$contratos[$i]}");
            $this->bancodedados->query("DELETE FROM rel_ass_cor_estacontido WHERE cor_id = {$contratos[$i]}");
            $this->bancodedados->query("DELETE FROM rel_fop_cor_estacontido WHERE cor_id = {$contratos[$i]}");
            $this->bancodedados->query("DELETE FROM tab_contratos_comentarios WHERE cor_id = {$contratos[$i]}");
            $this->bancodedados->query("DELETE FROM tab_contratos WHERE cor_id = {$contratos[$i]}");
        }

        for ($i = 0; $i < count($responsabilidades); $i++) {
            $this->bancodedados->query("DELETE FROM tab_clientes_responsabilidades_comentarios WHERE crc_id = '$responsabilidades[$i]'");
        }

        $this->bancodedados->query("DELETE FROM tab_clientes_responsabilidades WHERE cli_id = $cliente_id");
        $this->bancodedados->query("UPDATE tab_assinaturas SET ass_pai = NULL, ass_migrada = NULL WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE rel_saj_ass_contem FROM tab_assinaturas INNER JOIN rel_saj_ass_contem ON (tab_assinaturas.ass_id = rel_saj_ass_contem.ass_id) WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE tab_cobrancas_detalhes FROM tab_assinaturas INNER JOIN tab_cobrancas_detalhes ON (tab_assinaturas.ass_id = tab_cobrancas_detalhes.ass_id) WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE FROM tab_assinaturas WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE FROM tab_clientes_cobranca_dados WHERE cli_id = $cliente_id");

        $this->bancodedados->query("DELETE tab_servicos_ajustes_comentarios FROM tab_servicos_ajustes INNER JOIN tab_servicos_ajustes_comentarios ON (tab_servicos_ajustes.saj_id = tab_servicos_ajustes_comentarios.saj_id) WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE tab_servicos_ajustes_regras FROM tab_servicos_ajustes INNER JOIN tab_servicos_ajustes_regras ON (tab_servicos_ajustes.saj_id = tab_servicos_ajustes_regras.saj_id) WHERE cli_id = $cliente_id");
        $this->bancodedados->query("UPDATE tab_servicos_ajustes SET saj_pai = NULL WHERE cli_id = $cliente_id");
        $this->bancodedados->query("DELETE FROM tab_servicos_ajustes WHERE cli_id = $cliente_id");

        $this->bancodedados->query("DELETE FROM tab_clientes WHERE cli_id = $cliente_id");

    }

    function possuiContratos($cliente_id, $tipo_filtro, $tipo_id, $status_filtro, $status_id) {

        $where = array();

        $where[] = "cli_id = $cliente_id";
        if ($tipo_filtro && $tipo_id) {
            $where[] = "crt_id $tipo_filtro ($tipo_id)";
        }
        if ($status_filtro && $status_id) {
            $where[] = "crs_id $status_filtro ($status_id)";
        }

        $query = $this->bancodedados->query("SELECT COUNT(cor_id) FROM tab_contratos WHERE " . implode(" AND ", $where));

        return $this->bancodedados->resultado($query) > 0;

    }

    ##############
    ### Buscar ###
    ##############

    function buscar() {

        if ($_POST) {
            $id = md5(uniqid(rand(), true));
            $_SESSION["POST"][$id] = $_POST;
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar&i=$id");
            exit();
        } elseif ($_SESSION["POST"][$_GET["i"]]) {
            $_POST = $_SESSION["POST"][$_GET["i"]];
        }

        $status = $this->buscaStatus();

        $this->conteudo .= $this->carregaArquivo("clientes.buscar.inc.php");
        $acoes .= '<td bgcolor="#505050"><b>&nbsp;Buscar&nbsp;</b></td>';
        $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
        $this->conteudo = str_replace('[nome]', $_POST["nome"], $this->conteudo);

        if (!$_POST["condominio2"]) {
            $this->conteudo = str_replace('[condominio1]', $_POST["condominio1"], $this->conteudo);
            $this->conteudo = str_replace('[condominio2]', $this->geraSelect($this->buscaCondominios()), $this->conteudo);
            $this->conteudo = str_replace('[condominio_disabled]', "", $this->conteudo);
        } else {
            $this->conteudo = str_replace('[condominio1]', $this->buscaCondominio($_POST["condominio2"]), $this->conteudo);
            $this->conteudo = str_replace('[condominio2]', $this->geraSelect($this->buscaCondominios(), $_POST["condominio2"]), $this->conteudo);
            $this->conteudo = str_replace('[condominio_disabled]', "disabled", $this->conteudo);
        }

        if ($_POST["condominio2"]) {
            $this->conteudo = str_replace('[bloco]', $this->geraSelect($this->buscaBlocos($_POST["condominio2"]), $_POST["bloco"]), $this->conteudo);
            $this->conteudo = str_replace('[bloco_disabled]', "", $this->conteudo);
        } else {
            $this->conteudo = str_replace('[bloco]', "", $this->conteudo);
            $this->conteudo = str_replace('[bloco_disabled]', "disabled", $this->conteudo);
        }

        $this->conteudo = str_replace('[complemento]', $_POST["complemento"], $this->conteudo);
        $this->conteudo = str_replace('[cidade1]', $_POST["cidade2"] ? $this->buscaCidade($_POST["cidade2"]) : $_POST["cidade1"], $this->conteudo);
        $this->conteudo = str_replace('[cidade2]', $this->geraSelect($this->buscaCidades(), $_POST["cidade2"]), $this->conteudo);
        $this->conteudo = str_replace('[bairro1]', $_POST["bairro2"] ? $this->buscaBairro($_POST["bairro2"]) : $_POST["bairro1"], $this->conteudo);
        $this->conteudo = str_replace('[bairro2]', $this->geraSelect($_POST["cidade2"] ? $this->buscaBairros($_POST["cidade2"]) : null, $_POST["bairro2"]), $this->conteudo);
        $this->conteudo = str_replace('[bairro2_disabled]', $_POST["cidade2"] ? "" : "disabled", $this->conteudo);
        $this->conteudo = str_replace('[logradouro]', $_POST["logradouro"], $this->conteudo);
        $this->conteudo = str_replace('[cep]', $_POST["cep"], $this->conteudo);
        $this->conteudo = str_replace('[numero]', $_POST["numero"], $this->conteudo);
        $this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(), $_POST["tipo"]), $this->conteudo);
        $this->conteudo = str_replace('[cpf_cnpj]', $_POST["cpf_cnpj"], $this->conteudo);
        $this->conteudo = str_replace('[telefone]', $_POST["telefone"], $this->conteudo);
        $this->conteudo = str_replace('[email]', $_POST["email"], $this->conteudo);
        $this->conteudo = str_replace('[situacao]', $this->geraSelect($this->buscaSituacoes(), $_POST["situacao"]), $this->conteudo);
        $this->conteudo = str_replace('[data_cadastro_inicio]', $_POST["data_cadastro_inicio"], $this->conteudo);
        $this->conteudo = str_replace('[data_cadastro_termino]', $_POST["data_cadastro_termino"], $this->conteudo);
        $this->conteudo = str_replace('[status]', $this->geraSelect($status, $_POST["status"]), $this->conteudo);
        $this->conteudo = str_replace('[origem]', $this->geraSelect($this->buscaOrigens(), $_POST["origem"]), $this->conteudo);
        $this->conteudo = str_replace('[responsavel_disabled]', "", $this->conteudo);
        $this->conteudo = str_replace('[responsavel]', $this->geraSelect($this->buscaResponsaveis(true, null, $this->permissoes["clientes"]["buscar_plus"] || $this->permissoes["clientes"]["buscar_plus_plus"] ? null : $_SESSION["usuario"])), $this->conteudo);
        $this->conteudo = str_replace('[servico]', $this->geraSelect($this->buscaServicos(), $_POST["servico"]), $this->conteudo);
        $this->conteudo = str_replace('[plano_disabled]', $_POST["servico"] ? '' : 'disabled', $this->conteudo);
        $this->conteudo = str_replace('[plano]', $this->geraSelect($_POST["servico"] ? $this->buscaPlanos($_POST["servico"]) : null, $_POST["plano"]), $this->conteudo);
        $this->conteudo = str_replace('[data_ass_cadastro_inicio]', $_POST["data_ass_cadastro_inicio"], $this->conteudo);
        $this->conteudo = str_replace('[data_ass_cadastro_termino]', $_POST["data_ass_cadastro_termino"], $this->conteudo);
        $this->conteudo = str_replace('[data_ass_fechamento_inicio]', $_POST["data_ass_fechamento_inicio"], $this->conteudo);
        $this->conteudo = str_replace('[data_ass_fechamento_termino]', $_POST["data_ass_fechamento_termino"], $this->conteudo);
        $this->conteudo = str_replace('[data_ass_ativacao_inicio]', $_POST["data_ass_ativacao_inicio"], $this->conteudo);
        $this->conteudo = str_replace('[data_ass_ativacao_termino]', $_POST["data_ass_ativacao_termino"], $this->conteudo);
        $this->conteudo = str_replace('[data_ass_cancelamento_inicio]', $_POST["data_ass_cancelamento_inicio"], $this->conteudo);
        $this->conteudo = str_replace('[data_ass_cancelamento_termino]', $_POST["data_ass_cancelamento_termino"], $this->conteudo);

        $this->conteudo = str_replace('[resultado]', $this->geraResultadoBusca($_POST), $this->conteudo);

        $botoes = array();
        $botoes[] = '<input type="submit" name="buscar" class="buttonAzulEscuroBranco11" value="Buscar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=buscar', $this->conteudo);
        $this->conteudo = str_replace('[modulo]', $this->dados_modulo["nome"], $this->conteudo);

    }

    function geraResultadoBusca($dados) {

        if ($dados["g"]) {

            $tabelas = array();
            $campos = array();
            $where = array();

            // Campos
            $campos["id"] = "#";
            $campos["nome"] = "Nome";
            $campos["endereco"] = "Endereço";
            $campos["data_cadastro"] = "Cadastro";

            if (!$dados["responsavel"]) $campos["responsavel"] = "Responsável";
            if (!$dados["status"]) $campos["status"] = "Status";
            if (!$dados["origem"]) $campos["origem"] = "Origem";

            // Filtro - Nome
            if (($query = $this->geraQueryTexto($dados["nome"], "tab_clientes.cli_nome", "tab_clientes.cli_nome_fantasia")) != null) {
                $where[] = $query;
            }
            // Filtro - Cidade
            if (($query = $this->geraQueryTexto($dados["cidade1"], "tab_cidades.cid_nome")) != null) {
                $where[] = $query;
            }
            if ($dados["cidade2"]) {
                $where[] = "(tab_bairros.cid_id = {$dados["cidade2"]})";
            }
            // Filtro - Bairro
            if (($query = $this->geraQueryTexto($dados["bairro1"], "tab_bairros.bai_nome")) != null) {
                $where[] = $query;
            }
            if ($dados["bairro2"]) {
                $where[] = "(tab_logradouros.bai_id = {$dados["bairro2"]})";
            }
            // Filtro - Logradouro
            if (($query = $this->geraQueryTexto($dados["logradouro"], "tab_logradouros.log_nome")) != null) {
                $where[] = $query;
            }
            // Filtro - CEP
            $dados["cep"] = preg_replace("/[^0-9]/", "", $dados["cep"]);
            if ($dados["cep"]) {
                $where[] = "(tab_ceps.cep_id = {$dados["cep"]})";
            }
            // Filtro - Número
            if ($dados["numero"]) {
                $where[] = "(IF(tab_clientes.cob_id IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_clientes.con_id IS NOT NULL, tab_condominios.con_numero, tab_clientes.cli_numero)) = '{$dados["numero"]}')";
            }
            // Filtro - Condomínio
            if ($dados["condominio2"]) {
                $where[] = "(tab_condominios.con_id = {$dados["condominio2"]})";
            } elseif (($query = $this->geraQueryTexto($dados["condominio1"], "tab_condominios.con_nome")) != null) {
                $where[] = $query;
            }
            // Filtro - Bloco
            if ($dados["bloco"] && !trim($dados["condominio1"])) {
                $where[] = "(tab_condominios_blocos.cob_id = {$dados["bloco"]})";
            }
            // Filtro - Complemento
            if (trim($dados["complemento"])) {
                $dados["complemento"] = trim($dados["complemento"]);
                $where[] = "(tab_clientes.cli_complemento LIKE '_{$dados["complemento"]}')";
            }
            // Filtro - Tipo
            if ($dados["tipo"]) {
                $where[] = "(tab_clientes.clt_id = {$dados["tipo"]})";
            }
            // Filtro - CPF/CNPJ
            if (trim($dados["cpf_cnpj"])) {
                $dados["cpf_cnpj"] = trim($dados["cpf_cnpj"]);
                $where[] = "(tab_clientes.cli_cpf_cnpj = '{$dados["cpf_cnpj"]}')";
            }
            // Filtro - Situação
            if ($dados["situacao"]) {
                $where[] = "(tab_clientes.clu_id = {$dados["situacao"]})";
            }
            // Filtro - Data Cadastro
            if (!$dados["data_cadastro_inicio"] && !$dados["data_cadastro_termino"]) {
                $where[] = "(tab_clientes.cli_data_cadastro IS NOT NULL)";
            }
            // Filtro - Data Cadastro (Início)
            if ($dados["data_cadastro_inicio"]) {
                $dados["data_cadastro_inicio"] = substr($dados["data_cadastro_inicio"], 6, 4) . substr($dados["data_cadastro_inicio"], 3, 2) . substr($dados["data_cadastro_inicio"], 0, 2);
                $where[] = "(DATE_FORMAT(tab_clientes.cli_data_cadastro, '%Y%m%d') >= '{$dados["data_cadastro_inicio"]}')";
            }
            // Filtro - Data Cadastro (Término)
            if ($dados["data_cadastro_termino"]) {
                $dados["data_cadastro_termino"] = substr($dados["data_cadastro_termino"], 6, 4) . substr($dados["data_cadastro_termino"], 3, 2) . substr($dados["data_cadastro_termino"], 0, 2);
                $where[] = "(DATE_FORMAT(tab_clientes.cli_data_cadastro, '%Y%m%d') <= '{$dados["data_cadastro_termino"]}')";
            }
            // Filtro - Responsável
            if (!$this->permissoes["clientes"]["buscar_plus"] && !$this->permissoes["clientes"]["buscar_plus_plus"]) {
                if ($dados["responsavel"]) {
                    $where[] = "(tab_clientes.usu_id = {$_SESSION["usuario"]})";
                } else {
                    $where[] = "(tab_clientes.usu_id = {$_SESSION["usuario"]} OR tab_clientes.cls_id != 2)";
                }
            } else {
                if ($dados["responsavel"]) {
                    $where[] = "(tab_clientes.usu_id = {$dados["responsavel"]} OR (tab_clientes.cls_id = 4 AND tab_clientes.usu_id IS NULL))";
                }
            }
            // Filtro - Status
            if ($dados["status"]) {
                $where[] = "(tab_clientes.cls_id = {$dados["status"]})";
            } else {
                $where[] = "(tab_clientes.cls_id IS NOT NULL)";
            }
            // Filtro - Origem
            if ($dados["origem"]) {
                $where[] = "(tab_clientes.clo_id = {$dados["origem"]})";
            }
            // Filtro - Assinatura - Plano / Serviço
            if ($dados["plano"]) {
                $where[] = "(tab_assinaturas.pla_id = {$dados["plano"]})";
                $where[] = "(tab_assinaturas.ast_id IN (2, 5))";
                $tabelas[] = "INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)";
            } elseif ($dados["servico"]) {
                $where[] = "(tab_planos.pls_id = {$dados["servico"]})";
                $where[] = "(tab_assinaturas.ast_id IN (2, 5))";
                $tabelas[] = "INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)";
                $tabelas[] = "INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)";
            }
            // Filtro - Assinatura - Data Cadastro (Início)
            if ($dados["data_ass_cadastro_inicio"]) {
                $dados["data_ass_cadastro_inicio"] = substr($dados["data_ass_cadastro_inicio"], 6, 4) . substr($dados["data_ass_cadastro_inicio"], 3, 2) . substr($dados["data_ass_cadastro_inicio"], 0, 2);
                $where[] = "(DATE_FORMAT(tab_assinaturas.ass_data_cadastro, '%Y%m%d') >= '{$dados["data_ass_cadastro_inicio"]}')";
                $tabelas[] = "INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)";
            }
            // Filtro - Assinatura - Data Cadastro (Término)
            if ($dados["data_ass_cadastro_termino"]) {
                $dados["data_ass_cadastro_termino"] = substr($dados["data_ass_cadastro_termino"], 6, 4) . substr($dados["data_ass_cadastro_termino"], 3, 2) . substr($dados["data_ass_cadastro_termino"], 0, 2);
                $where[] = "(DATE_FORMAT(tab_assinaturas.ass_data_cadastro, '%Y%m%d') <= '{$dados["data_ass_cadastro_termino"]}')";
                $tabelas[] = "INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)";
            }
            // Filtro - Assinatura - Data Fechamento (Início)
            if ($dados["data_ass_fechamento_inicio"]) {
                $dados["data_ass_fechamento_inicio"] = substr($dados["data_ass_fechamento_inicio"], 6, 4) . substr($dados["data_ass_fechamento_inicio"], 3, 2) . substr($dados["data_ass_fechamento_inicio"], 0, 2);
                $where[] = "(DATE_FORMAT(tab_assinaturas.ass_data_fechamento, '%Y%m%d') >= '{$dados["data_ass_fechamento_inicio"]}')";
                $tabelas[] = "INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)";
            }
            // Filtro - Assinatura - Data Fechamento (Término)
            if ($dados["data_ass_fechamento_termino"]) {
                $dados["data_ass_fechamento_termino"] = substr($dados["data_ass_fechamento_termino"], 6, 4) . substr($dados["data_ass_fechamento_termino"], 3, 2) . substr($dados["data_ass_fechamento_termino"], 0, 2);
                $where[] = "(DATE_FORMAT(tab_assinaturas.ass_data_fechamento, '%Y%m%d') <= '{$dados["data_ass_fechamento_termino"]}')";
                $tabelas[] = "INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)";
            }
            // Filtro - Assinatura - Data Ativação (Início)
            if ($dados["data_ass_ativacao_inicio"]) {
                $dados["data_ass_ativacao_inicio"] = substr($dados["data_ass_ativacao_inicio"], 6, 4) . substr($dados["data_ass_ativacao_inicio"], 3, 2) . substr($dados["data_ass_ativacao_inicio"], 0, 2);
                $where[] = "(DATE_FORMAT(tab_assinaturas.ass_data_ativacao, '%Y%m%d') >= '{$dados["data_ass_ativacao_inicio"]}')";
                $tabelas[] = "INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)";
            }
            // Filtro - Assinatura - Data Ativação (Término)
            if ($dados["data_ass_ativacao_termino"]) {
                $dados["data_ass_ativacao_termino"] = substr($dados["data_ass_ativacao_termino"], 6, 4) . substr($dados["data_ass_ativacao_termino"], 3, 2) . substr($dados["data_ass_ativacao_termino"], 0, 2);
                $where[] = "(DATE_FORMAT(tab_assinaturas.ass_data_ativacao, '%Y%m%d') <= '{$dados["data_ass_ativacao_termino"]}')";
                $tabelas[] = "INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)";
            }
            // Filtro - Assinatura - Data Cancelamento (Início)
            if ($dados["data_ass_cancelamento_inicio"]) {
                $dados["data_ass_cancelamento_inicio"] = substr($dados["data_ass_cancelamento_inicio"], 6, 4) . substr($dados["data_ass_cancelamento_inicio"], 3, 2) . substr($dados["data_ass_cancelamento_inicio"], 0, 2);
                $where[] = "(DATE_FORMAT(tab_assinaturas.ass_data_cancelamento, '%Y%m%d') >= '{$dados["data_ass_cancelamento_inicio"]}')";
                $tabelas[] = "INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)";
            }
            // Filtro - Assinatura - Data Cancelamento (Término)
            if ($dados["data_ass_cancelamento_termino"]) {
                $dados["data_ass_cancelamento_termino"] = substr($dados["data_ass_cancelamento_termino"], 6, 4) . substr($dados["data_ass_cancelamento_termino"], 3, 2) . substr($dados["data_ass_cancelamento_termino"], 0, 2);
                $where[] = "(DATE_FORMAT(tab_assinaturas.ass_data_cancelamento, '%Y%m%d') <= '{$dados["data_ass_cancelamento_termino"]}')";
                $tabelas[] = "INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)";
            }
            // Filtro - E-mail
            if ($dados["email"]) {
                $dados["email"] = explode("@", $dados["email"]);
                $where[] = "(tab_clientes_contatos_emails.ccm_usuario = '{$dados["email"][0]}' AND tab_clientes_contatos_emails.ccm_dominio = '{$dados["email"][1]}')";
                $tabelas[] = "INNER JOIN tab_clientes_contatos_emails ON (tab_clientes.cli_id = tab_clientes_contatos_emails.cli_id)";
            }
            // Filtro - Telefone
            if ($dados["telefone"]) {
                $dados["telefone"] = explode(" ", $dados["telefone"]);
                $dados["telefone"][0] = preg_replace("/[^0-9]/", "", $dados["telefone"][0]);
                $where[] = "(tab_clientes_contatos_telefones.ccl_ddd = '{$dados["telefone"][0]}' AND tab_clientes_contatos_telefones.ccl_numero = '{$dados["telefone"][1]}')";
                $tabelas[] = "INNER JOIN tab_clientes_contatos_telefones ON (tab_clientes.cli_id = tab_clientes_contatos_telefones.cli_id)";
            }

			if ($_SESSION["origem"]) {
                $where[] = "(tab_clientes.clo_id = {$_SESSION["origem"]})";
			}
			
            $where = implode(" AND\n          ", $where);
            $tabelas = implode("\n", array_unique($tabelas));

            if ($this->permissoes["clientes"]["buscar_ilimitado"] || ($this->permissoes["clientes"]["buscar_plus_plus"] && ($dados["condominio2"] || ($dados["bairro2"] && $dados["status"] == 4))) || ($this->permissoes["clientes"]["buscar_plus"] && $dados["condominio2"])) {
                $limite_itens = 0;
            } else {
                $limite_itens = BUS_ITENS;
            }

            $query = "
                SELECT DISTINCT
                    tab_clientes.cli_id AS id,
                    tab_clientes.cli_nome AS nome,
                    tab_clientes.cli_nome_fantasia AS nome_fantasia,
                    tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
                    tab_logradouros.log_nome AS logradouro,
                    IF(tab_clientes.cob_id IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_clientes.con_id IS NOT NULL, tab_condominios.con_numero, tab_clientes.cli_numero)) AS numero,
                    tab_bairros.bai_nome AS bairro,
                    tab_cidades.cid_nome AS cidade,
                    tab_condominios.con_nome AS condominio,
                    tab_condominios_blocos.cob_nome AS bloco,
                    SUBSTRING(tab_clientes.cli_complemento,2) AS complemento,
                    DATE_FORMAT(tab_clientes.cli_data_cadastro,'%d/%m/%Y') AS data_cadastro,
                    tab_clientes_tipos.clt_nome AS tipo,
                    tab_clientes_situacoes.clu_nome AS situacao,
                    tab_clientes.cls_id AS status,
                    tab_usuarios.usu_nome AS responsavel,
                    tab_clientes_origens.clo_nome AS origem
                FROM
                    tab_clientes
                    LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
                    LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
                    INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_clientes.cep_id IS NOT NULL, tab_clientes.cep_id, IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, tab_condominios.cep_id)))
                    INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
                    INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
                    INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
                    INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
                    LEFT OUTER JOIN tab_clientes_tipos ON (tab_clientes.clt_id = tab_clientes_tipos.clt_id)
                    LEFT OUTER JOIN tab_clientes_situacoes ON (tab_clientes.clu_id = tab_clientes_situacoes.clu_id)
                    LEFT OUTER JOIN tab_usuarios ON (tab_clientes.usu_id = tab_usuarios.usu_id)
                    $tabelas
                WHERE
                    $where
                ORDER BY
                    tab_clientes.cli_nome ASC";
	
            $query = $this->bancodedados->query($query);

            if (!$limite_itens || ($this->bancodedados->linhas($query) <= $limite_itens)) {

                $id = 1;
                $status = $this->buscaStatus();
                $campos["links"] = "Link Rápido";
                $registros = array();

                while (($registro = $this->bancodedados->resultadoArray($query)) != false) {

                    $registro["links"] = $this->geraLinks($registro["id"]);
                    if ($this->permissoes["clientes"]["ver_dc"]) $registro["link"] = "?m=clientes&amp;a=ver_dc&amp;i=" . $registro["id"];
                    $endereco = array();
                    if ($registro["condominio"]) {
                        $endereco[] = $registro["condominio"];
                        if ($registro["bloco"] && $registro["bloco"] != "Único")
                            $endereco[] = $registro["bloco"];
                    } else {
                        $endereco[] = $registro["logradouro"];
                        if ($registro["numero"])
                            $endereco[] = $registro["numero"];
                        $endereco[] = $registro["bairro"];
                    }
                    if ($registro["nome_fantasia"]) {
                        $registro["nome"] = "{$registro["nome"]} ({$registro["nome_fantasia"]})";
                    }
                    if ($registro["complemento"]) $endereco[] = $registro["complemento"];
                    $registro["endereco"] = ($endereco) ? implode(" &raquo; ", $endereco) : $registro["endereco"] = "Sem Condomínio";
                    $registro["status"] = $status[$registro["status"]];
                    $telefones = $this->buscaClientesTelefones($registro["id"]);
                    $registro["telefone"] = $telefones["tel_com_ramal"];
                    $registro["id"] = $id++;

                    $registros[] = $registro;

                }
                $listagem = controle::instanciaObjeto("listagem", $campos, $registros, 0, false, null, null, array("right", "left", "left"));
                $resultado = $listagem->conteudo;

            } else {
                $resultado = "<br><b>Atenção!</b><br>Muitos registros foram encontrados, especifique melhor a sua busca!<br>(Máximo $limite_itens itens)<br><br>";
            }

            $resultado = "
        <tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
          <td height=\"30\" colspan=\"2\"><strong>&nbsp;Resultado:</strong></td>
        </tr>
        <tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
          <td colspan=\"2\" align=\"center\">
            $resultado
          </td>
        </tr>";

        } else {
            $resultado = "";
        }

        return $resultado;

    }

    function geraLinks($cliente_id, $div = true) {

        $links = array();

        if ($this->permissoes["clientes"]["ver_dc"]) $links[] = "<a class='txtAzulEscuro11' title='Dados Cadastrais' href='?m=clientes&amp;a=ver_dc&amp;i=$cliente_id'>DC</a>";
        if ($this->permissoes["clientes"]["ver_df"]) $links[] = "<a class='txtAzulEscuro11' title='Dados Financeiros' href='?m=clientes&amp;a=ver_df&amp;i=$cliente_id'>DF</a>";
        if ($this->permissoes["clientes"]["ver_dm"]) $links[] = "<a class='txtAzulEscuro11' title='Dados Comerciais' href='?m=clientes&amp;a=ver_dm&amp;i=$cliente_id'>DM</a>";
        if ($this->permissoes["clientes"]["ver_dt"]) $links[] = "<a class='txtAzulEscuro11' title='Dados Técnicos' href='?m=clientes&amp;a=ver_dt&amp;i=$cliente_id'>DT</a>";
        if ($this->permissoes["clientes"]["ver_da"]) $links[] = "<a class='txtAzulEscuro11' title='Dados Administrativos' href='?m=clientes&amp;a=ver_da&amp;i=$cliente_id'>DA</a>";
        if ($this->permissoes["clientes"]["ver_ch"]) $links[] = "<a class='txtAzulEscuro11' title='Chamados' href='?m=clientes&amp;a=ver_ch&amp;i=$cliente_id'>CH</a>";

        if ($links) {
            $links = $div ? '<div style="width:100px">' . implode(" ", $links) . '</div>' : implode(" ", $links);
        }

        return $links;

    }

    function buscaClientesTelefones($cliente_id) {

        global $login;

        $lista = array();
        $lista_sem_ramal = array();
        $lista_com_ramal = array();

        $query = "
            SELECT
                ccl_ramal AS ramal,
                CONCAT('(', ccl_ddd, ') ', ccl_numero) AS telefone,
                ccl_ddd AS ddd,
                ccl_numero AS numero
            FROM
                tab_clientes_contatos_telefones
            WHERE
                cli_id = $cliente_id AND
				ccf_id > 0";

        $query = $this->bancodedados->query($query);

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

            $telefone = $dados["telefone"];

            if ($dados["ddd"] == 48) {
                $numero = preg_replace("/[^0-9]/", "", "0" . $dados["numero"]);
            } else {
                $numero = preg_replace("/[^0-9]/", "", "00" . $dados["ddd"] . $dados["numero"]);
            }

            $lista_sem_ramal[] = $telefone;

            if ($dados["ramal"]) $telefone .= " (Ramal {$dados["ramal"]})";

            if ($login->usuario["agente_id"] || $login->usuario["ramal"]) {
                $lista_com_ramal[] = "<a href=\"javascript:discar('$numero')\" title=\"Discar para este número\" class=\"linkDiscar\">$telefone</a>";
            } else {
                $lista_com_ramal[] = $telefone;
            }

        }

        $lista["tel_sem_ramal"] = implode(" | ", $lista_sem_ramal);
        $lista["tel_com_ramal"] = implode(" | ", $lista_com_ramal);

        return $lista;

    }

    function buscaCidades() {

        $cidades = array();

        $query = "
            SELECT DISTINCT
                tab_cidades.cid_id AS id,
                CONCAT(tab_cidades.cid_nome, ' / ', tab_estados.est_sigla) AS nome
            FROM
                tab_cidades
                INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				".($_SESSION["origem"] ? "
				tab_cidades.cid_id IN (
					SELECT
						cid_id
					FROM
						rel_clo_cid_contem
					WHERE
						clo_id = {$_SESSION["origem"]}
				)" : "1")."
            ORDER BY
                tab_cidades.cid_nome";

        $query = $this->bancodedados->query($query);

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $cidades[$dados["id"]] = $dados["nome"];
        }

        return $cidades;

    }

    function buscaBairros($cidade_id) {

        $bairros = array();

        $query = "
            SELECT
                tab_bairros.bai_id AS id,
                tab_bairros.bai_nome AS nome
            FROM
                tab_bairros
            WHERE
                tab_bairros.cid_id = $cidade_id
            ORDER BY
                nome";

        $query = $this->bancodedados->query($query);

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $bairros[$dados["id"]] = $dados["nome"];
        }

        return $bairros;

    }

    function buscaCidade($cidade_id) {

        $query = "
            SELECT
                cid_nome AS nome
            FROM
                tab_cidades
            WHERE
                cid_id = $cidade_id";

        $query = $this->bancodedados->query($query);

        return $this->bancodedados->resultado($query);

    }

    function buscaBairro($bairro_id) {

        $query = "
            SELECT
                bai_nome AS nome
            FROM
                tab_bairros
            WHERE
                bai_id = $bairro_id";

        $query = $this->bancodedados->query($query);

        return $this->bancodedados->resultado($query);

    }

    ###########
    ### RDS ###
    ###########

    function buscaEndereco($cliente_id, $complemento_tipo = true) {

        // Endereço em condomínio
        $query = "
            SELECT
                tab_condominios.con_nome AS condominio,
                tab_condominios_blocos.cob_nome AS bloco,
                SUBSTRING(tab_clientes.cli_complemento, 1, 1) AS complemento_tipo_id,
                SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento_valor
            FROM
                tab_clientes
                INNER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
                LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
            WHERE
                tab_clientes.cli_id = $cliente_id";

        $query = $this->bancodedados->query($query);
        $dados = $this->bancodedados->resultadoArray($query);

        if ($dados) {
            $dados["complemento"] = $complemento_tipo ? $this->buscaComplementoTipos($dados["complemento_tipo_id"]) . " " . $dados["complemento_valor"] : $dados["complemento_valor"];
            return 'Condomínio ' . $dados["condominio"] . ($dados["bloco"] ? ', Bloco ' . $dados["bloco"] : '') . ($dados["complemento"] ? ', ' . $dados["complemento"] : '');
        }

        // Endereço Cliente
        $query = "
            SELECT
                tab_estados.est_sigla AS estado,
                tab_cidades.cid_nome AS cidade,
                tab_logradouros.log_nome AS logradouro,
                tab_clientes.cli_numero AS numero,
                SUBSTRING(tab_clientes.cli_complemento, 1, 1) AS complemento_tipo_id,
                SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento_valor
            FROM
                tab_clientes
                INNER JOIN tab_ceps ON (tab_clientes.cep_id = tab_ceps.cep_id)
                INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
                INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
                INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
                INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
            WHERE
                tab_clientes.cli_id = $cliente_id";

        $query = $this->bancodedados->query($query);
        $dados = $this->bancodedados->resultadoArray($query);

        if ($dados) {

            $endereco = array();

            if ($dados["logradouro"]) $endereco[] = $dados["logradouro"];
            if ($dados["numero"]) $endereco[] = $dados["numero"];
            if ($dados["complemento"]) $endereco[] = $this->buscaComplementoTipos($dados["complemento_tipo_id"]) . $dados["complemento_valor"];
            if ($dados["cidade"]) $endereco[] = $dados["cidade"];
            if ($dados["estado"]) $endereco[] = $dados["estado"];

            return implode(", ", $endereco);

        }

    }

    #############################
    ### RDS - Clientes Buscar ###
    #############################

    function geraScriptAtualizacaoSelect($nome, $itens = null, $ativo = "") {

        if ($nome) {
            $script_itens = array();
            if ($itens) {
                foreach ($itens as $id => $item) {
                    $script_itens[] = 'new Array("' . $id . '", "' . $item . '")';
                }
            }
            $script_conteudo = 'var itens_' . $nome . ' = new Array (' . implode(', ', $script_itens) . ');';
            $script_conteudo .= 'if (document.getElementById("' . $nome . '")) atualizaSelect(document.getElementById("' . $nome . '"), itens_' . $nome . ', "' . $ativo . '");';
        }

        return $script_conteudo;

    }

    ################################
    ### Busca Inscrição Estadual ###
    ################################

    function buscaInscricaoEstadual($cpf_cnpj, $pessoa_tipo_id) {

        $variaveis_post = array();

        $variaveis_post["btn_pesquisar"] = "Pesquisar";
        $variaveis_post["hidd_748129"] = "748129";
        $variaveis_post["opt_pessoa"] = $pessoa_tipo_id;
        $variaveis_post["txt_CPFCNPJ"] = $this->limpaCNPJAlfanumerico($cpf_cnpj);
        $variaveis_post["txt_IE"] = "";
        $variaveis_post["digito"] = "748129";
        $variaveis_post["EstadoSelecionado"] = "de Santa Catarina";
        $variaveis_post["SiglaEstadoSelecionado"] = "SC";

        // URL
        $url = "http://sistemas.sef.sc.gov.br/sintegra/consulta_empresa_inscricoes.asp";

        // Conexão cURL
        $conexao = curl_init();

        curl_setopt($conexao, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($conexao, CURLOPT_URL, $url);
        curl_setopt($conexao, CURLOPT_FAILONERROR, 1);
        curl_setopt($conexao, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($conexao, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($conexao, CURLOPT_TIMEOUT, 10);
        curl_setopt($conexao, CURLOPT_COOKIEFILE, "/tmp/cookie/" . session_id());
        curl_setopt($conexao, CURLOPT_COOKIEJAR, "/tmp/cookie/" . session_id());
        curl_setopt($conexao, CURLOPT_POST, 1);
        curl_setopt($conexao, CURLOPT_POSTFIELDS, $this->trataVariaveis($variaveis_post));

        $retorno = curl_exec($conexao);
        $retorno = preg_replace("/[\r\n \t]+/", " ", strip_tags($retorno));

        if (strpos($retorno, "Inscrição Estadual: ") !== false) {
            $retorno = substr($retorno, strpos($retorno, "Inscrição Estadual: ") + 20, 11);
        } else {
            $retorno = "";
        }

        return $retorno;

    }

    function trataVariaveis($variaveis) {

        if ($variaveis) {
            $variaveis_tratadas = array();
            foreach ($variaveis as $chave => $valor) {
                $variaveis_tratadas[] = urlencode($chave) . "=" . urlencode($valor);
            }
            return $variaveis_tratadas ? implode("&", $variaveis_tratadas) : "";
        }

    }

    var $pdf;

    const PAGE_WIDTH = 210;
    const PAGE_HEIGHT = 297;

    function imprimirEndereco() {

        $endereco_cobranca = $this->buscaEnderecoCobranca($this->dados_item["id"]);

        $this->pdf = pdf_new();

        $this->iniciaDocumento();
        $this->iniciaPagina();

        $this->carregaFontes();

        $this->desenhaCodigoBarrasCEP($endereco_cobranca);
        $this->escreveEnderecoCliente($endereco_cobranca);

        $this->setLarguraLinha(0.2);

        $this->desenhaLinha(0, 114, 210, 114);

        $this->finalizaPagina();
        $this->finalizaDocumento();

        $conteudo = pdf_get_buffer($this->pdf);

        header("Content-type: application/pdf;");
        header("Content-disposition: attachment; filename=Endereco-{$this->dados_item["id"]}.pdf;");
        header("Content-length: ".strlen($conteudo));

        print $conteudo;

        die();

    }

    var $fontes = array();

    function carregaFontes() {

        $this->fontes["Courier"] = pdf_findfont($this->pdf, "Courier", "host", 0);
        $this->fontes["Courier-Bold"] = pdf_findfont($this->pdf, "Courier-Bold", "host", 0);

    }

    function iniciaDocumento() {

        pdf_begin_document($this->pdf, "", "destination={type=fitwidth} openmode=none viewerpreferences={printscaling=none}");
        pdf_set_info($this->pdf, "Author", "Olé Internet");
        pdf_set_info($this->pdf, "Title", "Boleto {$this->dados_item["codigo"]}");

    }

    function finalizaDocumento() {

        pdf_end_document($this->pdf, "");

    }

    function iniciaPagina() {

      pdf_begin_page_ext($this->pdf, $this->mm2Point(self::PAGE_WIDTH), $this->mm2Point(self::PAGE_HEIGHT), "");

    }

    function finalizaPagina() {

        pdf_end_page($this->pdf);

    }

    function desenhaCodigoBarrasCEP($endereco_cobranca) {

        $tabela = array();

        $tabela[0] = "11000";
        $tabela[1] = "00011";
        $tabela[2] = "00101";
        $tabela[3] = "00110";
        $tabela[4] = "01001";
        $tabela[5] = "01010";
        $tabela[6] = "01100";
        $tabela[7] = "10001";
        $tabela[8] = "10010";
        $tabela[9] = "10100";

        $cep = sprintf("%02s", preg_replace("/[^0-9]/", "", $endereco_cobranca["cep"]));

        $soma = 0;

        for ($i = 0; $i < 8; $i++) {
            $soma += $cep[$i];
        }

        $dv = $soma % 10;
        $dv = $dv == 0 ? 0 : 10 - $dv;
        $cep = "{$cep}{$dv}";

        $this->desenhaBarraCEP("1");

        for ($i = 0; $i < 9; $i++) {
            for ($j = 0; $j < 5; $j++) {
                $this->desenhaBarraCEP($tabela[$i][$j]);
            }
        }

        $this->desenhaBarraCEP("1");

    }

    var $posicao_barra_cep = 40;
    var $barra_cep_lartura = 0.5;
    var $barra_cep_espacamento = 0.6;
    var $barra_cep_curta = 1.4;
    var $barra_cep_alta = 3.0;

    function desenhaBarraCEP($tipo) {

        if ($tipo == "0") {
            $offset_y = $this->barra_cep_alta - $this->barra_cep_curta;
            $altura = $this->barra_cep_curta;
        } else {
            $offset_y = 0;
            $altura = $this->barra_cep_alta;
        }

        $this->desenhaQuadrado($this->posicao_barra_cep, 69 + $offset_y, $this->barra_cep_lartura, $altura);

        $this->posicao_barra_cep += $this->barra_cep_lartura + $this->barra_cep_espacamento;

    }

    function setFonte($fonte, $tamanho) {

		pdf_setfont($this->pdf, $this->fontes[$fonte], $tamanho);

    }

    function desenhaQuadrado($x, $y, $largura, $altura) {

        pdf_rect($this->pdf, $this->mm2Point($x), $this->mm2Point(self::PAGE_HEIGHT - ($altura + $y)), $this->mm2Point($largura), $this->mm2Point($altura));
        pdf_fill($this->pdf);

    }

    function setLarguraLinha($largura, $tracejado = false) {

        pdf_setlinewidth($this->pdf, $this->mm2Point($largura));

        if ($tracejado) {
            pdf_setdash($this->pdf, $this->mm2Point(1), $this->mm2Point(1));
        } else {
            pdf_setdash($this->pdf, 0, 0);
        }

    }

    function desenhaLinha($x_inicial, $y_inicial, $x_final, $y_final) {

        pdf_moveto($this->pdf, $this->mm2Point($x_inicial), $this->mm2Point(self::PAGE_HEIGHT - $y_inicial));
        pdf_lineto($this->pdf, $this->mm2Point($x_final), $this->mm2Point(self::PAGE_HEIGHT - $y_final));
        pdf_stroke($this->pdf);

    }

    function escreveTexto($texto, $x, $y, $largura, $altura, $alinhamento = "left") {

        pdf_show_boxed($this->pdf, $texto, $this->mm2Point($x), $this->mm2Point(self::PAGE_HEIGHT - ($altura + $y)), $this->mm2Point($largura), $this->mm2Point($altura), "$alinhamento", "");

    }

    function escreveLinha($texto, $x, $y, $espacamento = 0) {

        pdf_set_value($this->pdf, "charspacing", $espacamento);
        pdf_show_xy($this->pdf, $texto, $this->mm2Point($x), $this->mm2Point(self::PAGE_HEIGHT - $y));
        pdf_set_value($this->pdf, "charspacing", $espacamento);

    }

    function mm2Point($value) {

        return $value / 25.4 * 72;

    }

    function point2MM($value) {

        return $value * 25.4 / 72;

    }

    function escreveEnderecoCliente($endereco_cobranca) {

        $this->setFonte("Courier-Bold", 9);

        $this->escreveLinha(mb_strtoupper($this->dados_item["nome"], "iso-8859-1"), 40, 77);

        $this->setFonte("Courier", 9);

        $this->escreveTexto(mb_strtoupper($endereco_cobranca["endereco"], "iso-8859-1"), 40, 77, 110, 20, "left");

    }

    function buscaEnderecoCobranca($cliente_id) {

        $query = $this->bancodedados->query("
            SELECT
                tab_condominios.con_nome AS condominio,
                tab_logradouros.log_nome AS logradouro,
                IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, con_numero, tab_clientes_cobranca_dados.ccd_numero)) AS numero,
                tab_clientes_cobranca_dados.ccd_complemento AS complemento,
                tab_condominios_blocos.cob_nome AS bloco,
                tab_bairros.bai_nome AS bairro,
                tab_cidades.cid_nome AS cidade,
                tab_estados.est_sigla AS estado,
                INSERT(LPAD(tab_ceps.cep_id, 8, '0'), 6, 0, '-') AS cep
            FROM
                tab_clientes_cobranca_dados
                LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes_cobranca_dados.cob_id = tab_condominios_blocos.cob_id)
                LEFT OUTER JOIN tab_condominios ON (tab_condominios.con_id = tab_condominios_blocos.con_id)
                INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes_cobranca_dados.cep_id)))
                INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
                INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
                INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
                INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
            WHERE
                tab_clientes_cobranca_dados.cli_id = $cliente_id");

        if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $dados["complemento"] = $this->corrigeComplementoCliente($dados["complemento"]);
        } else {
            $dados = $this->buscaEnderecoInstalacao($cliente_id);
        }

        $endereco = array();
        $logradouro = array();
        $complemento = array();
        $apartamento = array();

        if ($dados["logradouro"]) $logradouro[] = $dados["logradouro"];
        if ($dados["numero"]) $logradouro[] = $dados["numero"];
        if ($dados["condominio"]) {
            $dados["condominio"] = preg_replace("/ *\\[[^\\]]*\\]/", "", $dados["condominio"]);
            $complemento[] = "Cond. {$dados["condominio"]}";
            if ($dados["complemento"]) $apartamento[] = $dados["complemento"];
            if ($dados["bloco"] && $dados["bloco"] != "Único") $apartamento[] = $dados["bloco"];
            if ($apartamento) $complemento[] = implode(" / ", $apartamento);
        } else {
            if ($dados["complemento"]) $logradouro[] = $dados["complemento"];
        }

        $logradouro = implode(", ", $logradouro);
        $endereco = implode(", ", $endereco);
        $complemento = implode(", ", $complemento);

        $endereco[] = $logradouro;
        if ($complemento) {
            $endereco[] = $complemento;
        }
        $endereco[] = $dados["bairro"];
        $endereco[] = "{$dados["cep"]} - {$dados["cidade"]} - {$dados["estado"]}";

        $dados["endereco"] = implode("\n", $endereco);

        return $dados;

    }

    function buscaEnderecoInstalacao($cliente_id) {

        $query = $this->bancodedados->query("
            SELECT
                tab_condominios.con_nome AS condominio,
                tab_logradouros.log_nome AS logradouro,
                IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, con_numero, tab_clientes.cli_numero)) AS numero,
                tab_clientes.cli_complemento AS complemento,
                tab_condominios_blocos.cob_nome AS bloco,
                tab_bairros.bai_nome AS bairro,
                tab_cidades.cid_nome AS cidade,
                tab_estados.est_sigla AS estado,
                INSERT(LPAD(tab_ceps.cep_id, 8, '0'), 6, 0, '-') AS cep
            FROM
                tab_clientes
                LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
                LEFT OUTER JOIN tab_condominios ON (tab_condominios.con_id = tab_clientes.con_id)
                INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))
                INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
                INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
                INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
                INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
            WHERE
                tab_clientes.cli_id = $cliente_id");

        if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $dados["complemento"] = $this->corrigeComplementoCliente($dados["complemento"]);
            return $dados;
        }

    }

    function corrigeComplementoCliente($complemento) {

        $complementos_tipos = array();

        $complementos_tipos["a"] = "Apto ";
        $complementos_tipos["l"] = "Loja ";
        $complementos_tipos["s"] = "Sala ";
        $complementos_tipos["c"] = "Casa ";
        $complementos_tipos["o"] = "";

        if ($complemento && array_key_exists($complemento[0], $complementos_tipos)) {
            $complemento = $complementos_tipos[$complemento[0]].substr($complemento, 1, strlen($complemento));
            return addslashes($complemento);
        } else {
            return false;
        }

    }

    function relatorio_financeiro() {

        $this->buscaDadosItem($_GET["i"]);

        if ($this->dados_item) {

            $modulo = controle::instanciaObjeto("cobrancas", $this->bancodedados, $this->permissoes, false);
            $cobrancas = $modulo->buscaClientesCobrancasRelatorio($this->dados_item["id"]);

            $conteudo = $this->carregaArquivo("clientes.relatorio_financeiro.inc.php");

            $conteudo = str_replace("[id]", $this->dados_item["id"], $conteudo);
            $conteudo = str_replace("[cliente]", $this->dados_item["nome"], $conteudo);
            $conteudo = str_replace("[cpf_cnpj]", $this->dados_item["cpf_cnpj"], $conteudo);
            $conteudo = str_replace("[endereco]", $this->buscaClienteEndereco($this->dados_item["id"], true), $conteudo);
            $conteudo = str_replace("[telefones]", str_replace(" | ", ", ", strip_tags($this->dados_item["telefone"])), $conteudo);
            $conteudo = str_replace("[emails]", strip_tags($this->dados_item["email"]), $conteudo);

            if (count($cobrancas) > 0) {

                $listagem = "<table>";

                $listagem .= "<tr>";
                $listagem .= "<td style=\"text-align:center\"><b>Código</b></td>";
                $listagem .= "<td style=\"text-align:center\"><b>Competência</b></td>";
                $listagem .= "<td style=\"text-align:center\"><b>Vencimento</b></td>";
                $listagem .= "<td style=\"text-align:center\"><b>Recebimento</b></td>";
                $listagem .= "<td style=\"text-align:right\"><b>Valor original</b></td>";
                $listagem .= "<td style=\"text-align:right\"><b>Valor recebido</b></td>";
                $listagem .= "<td style=\"text-align:center\"><b>Status</b></td>";
                $listagem .= "</tr>";

                for ($i = 0; $i < count($cobrancas); $i++) {
                    $listagem .= "<tr>";
                    $listagem .= "<td style=\"text-align:center\">{$cobrancas[$i]["codigo"]}</td>";
                    $listagem .= "<td style=\"text-align:center\">{$cobrancas[$i]["competencia"]}</td>";
                    $listagem .= "<td style=\"text-align:center\">{$cobrancas[$i]["data_vencimento"]}</td>";
                    $listagem .= "<td style=\"text-align:center\">{$cobrancas[$i]["data_pagamento"]}</td>";
                    $listagem .= "<td style=\"text-align:right\">{$cobrancas[$i]["valor_original"]}</td>";
                    $listagem .= "<td style=\"text-align:right\">{$cobrancas[$i]["valor_total"]}</td>";
                    $listagem .= "<td style=\"text-align:center\">{$cobrancas[$i]["status"]}</td>";
                    $listagem .= "</tr>";
                }

                $listagem .= "</table>";

            } else {

                $listagem = "Nenhuma cobrança registrada.";

            }

            $conteudo = str_replace("[listagem]", $listagem, $conteudo);
            $conteudo = str_replace("[geracao]", date("Y/m/d h:i:s"), $conteudo);

        } else {

            $conteudo = '<script type="text/javascript">window.close();</script>';

        }

        print $conteudo;

        die();

    }

    function geraQueryTexto($texto, $campo, $campo_alternativo = null) {

		$texto = trim($texto);

		if (!$texto) {
			return null;
		}

        $blacklist = array("o", "a", "e", "de", "do", "dos", "di", "da", "das");

        $texto = mb_strtolower($texto, "iso-8859-1");
        $texto = preg_replace("/[ç]/", "c", $texto);
        $texto = preg_replace("/[ñ]/", "n", $texto);
        $texto = preg_replace("/[áàâãä]/", "a", $texto);
        $texto = preg_replace("/[éèêë]/", "e", $texto);
        $texto = preg_replace("/[íìîï]/", "i", $texto);
        $texto = preg_replace("/[óòôõö]/", "o", $texto);
        $texto = preg_replace("/[úùûü]/", "u", $texto);
        $texto = trim(preg_replace("/[^a-z0-9]+/", " ", $texto));
        $texto = explode(" ", $texto);

        $tokens = array();

        for ($i = 0; $i < count($texto); $i++) {
            if (!in_array($texto[$i], $blacklist)) {
                if ($campo_alternativo) {
                    $tokens[] = "({$campo} REGEXP '[[:<:]]{$this->geraExpressao($texto[$i])}' OR {$campo_alternativo} REGEXP '[[:<:]]{$this->geraExpressao($texto[$i])}')";
                } else {
                    $tokens[] = "({$campo} REGEXP '[[:<:]]{$this->geraExpressao($texto[$i])}')";
                }
            }
        }

        return implode(" AND ", $tokens);

    }

    function geraExpressao($token) {

        $origem = array(
            "/(.)\\1+/",
            "/nh/",
            "/([^p])h/",
            "/s$/",
            "/(.)/",
            "/([aeiou]) l ([b-df-hj-np-tv-z])/",
            "/(g n)/",
            "/(d t|t d|t) $/",
            "/(f|p h) /",
            "/([vw] )+/",
            "/([mn] )+/",
            "/(([jg]|gu) )+/",
            "/(([cszxkq]|qu) )+/",
            "/a /",
            "/[eiy]+ /",
            "/[ou]+ /",
            "/([a-z0-9]) /",
            "/ +/",
            "/$/"
        );

        $destino = array(
            "$1",
            "gn",
            "$1",
            "",
            "$1 ",
            "$1 (h?[lu])+ $2",
            " (gn|nh) ",
            " (t+|dt|td) ",
            " (f+|ph) ",
            " (h?[vw])+ ",
            " (h?[mn])+ ",
            " (h?[jgu])+ ",
            " (h?[cszxkquç]h?)+ ",
            "(h?[aáàâãä])+ ",
            "(h?[eéèêëiíìîïy])+ ",
            "(h?[oóòôõöuúùûü])+ ",
            "(h?$1)+ ",
            "",
            "h?"
        );

        return preg_replace($origem, $destino, $token);

    }
		
	function ehCancelavelReajuste() {
		
        if (!$this->permissoes["clientes"]["cancelar_reajuste"]) return false;
		if (!$this->possuiAssinaturaReajustada($this->dados_item["id"])) return false;
		
		return true;
		
	}
	
	function possuiAssinaturaReajustada($cliente_id) {
		
        $query = $this->bancodedados->query("
            SELECT
                COUNT(*)
            FROM
                tab_assinaturas
            WHERE
                tab_assinaturas.cli_id = $cliente_id AND
				tab_assinaturas.ast_id = 2 AND
				tab_assinaturas.ass_migrada IS NOT NULL AND
				tab_assinaturas.ass_data_ativacao = '2022-04-01'");

        return $this->bancodedados->resultado($query) > 0;
		
	}
	
	function cancelaReajuste($cliente_id) {
		
		$assinaturas = array();
		
		$this->bancodedados->transacaoIniciar();
		
        $query = $this->bancodedados->query("
            SELECT
                tab_assinaturas.ass_id AS id,
				tab_assinaturas.ass_migrada AS migrada_id,
				(SELECT tab_contratos.cor_id FROM rel_ass_cor_estacontido INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id) WHERE rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id AND tab_contratos.cor_pai IS NULL) AS contrato_id
            FROM
                tab_assinaturas
            WHERE
                tab_assinaturas.cli_id = {$cliente_id} AND
				tab_assinaturas.ast_id = 2 AND
				tab_assinaturas.ass_migrada IS NOT NULL AND
				tab_assinaturas.ass_data_ativacao = '2022-04-01'");
				
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$this->bancodedados->query("UPDATE tab_assinaturas SET ast_id = 3, ass_data_cancelamento = '2022-04-01' WHERE ass_id = {$dados["id"]}");
			$this->bancodedados->query("UPDATE tab_assinaturas SET ast_id = 2, ass_data_cancelamento = NULL WHERE ass_id = {$dados["migrada_id"]}");

			$query_descontos = $this->bancodedados->query("
				SELECT
					dec_id AS id
				FROM
					tab_descontos_concessoes
				WHERE
					tab_descontos_concessoes.ass_id = {$dados["migrada_id"]}");

			while (($desconto = $this->bancodedados->resultadoArray($query_descontos)) != false) {
				$this->bancodedados->query("UPDATE tab_descontos_concessoes SET dcs_id = 5 WHERE dec_id = {$desconto["id"]} AND dcs_id = 6");
				$this->bancodedados->query("INSERT INTO rel_dec_cor_estacontido (dec_id, cor_id) VALUES ({$desconto["id"]}, {$dados["contrato_id"]})");
			}

			$this->bancodedados->query("UPDATE rel_ass_cor_estacontido SET ass_id = {$dados["migrada_id"]} WHERE ass_id = {$dados["id"]} AND cor_id = {$dados["contrato_id"]}");
			$this->bancodedados->query("UPDATE rel_ass_cor_estacontido SET ass_id = {$dados["migrada_id"]} WHERE ass_id = {$dados["id"]} AND cor_id = (SELECT cor_id FROM tab_contratos WHERE cor_pai = {$dados["contrato_id"]} AND crs_id < 18)");

			$this->bancodedados->query("UPDATE rel_saj_ass_contem SET ass_id = {$dados["migrada_id"]} WHERE ass_id = {$dados["id"]}");

			$this->bancodedados->query("UPDATE tab_clientes_comodatos SET ass_id = {$dados["migrada_id"]} WHERE ass_id = {$dados["id"]}");

			$assinaturas[] = $dados["id"];

        }
		
		if (count($assinaturas) > 0) {
		
			$cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, $this->permissoes, false);
		
			$assinaturas = implode(",", $assinaturas);
		
			$query = $this->bancodedados->query("
				SELECT DISTINCT
					tab_cobrancas.coa_id AS id
				FROM
					tab_cobrancas
					INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
				WHERE
					tab_cobrancas.cli_id = {$cliente_id} AND
					tab_cobrancas.cot_id = 1 AND
					tab_cobrancas.coa_pai IS NULL AND
					tab_cobrancas_detalhes.ass_id IN ({$assinaturas}) AND
					tab_cobrancas_detalhes.cod_competencia_inicio >= '2022-04-01'");
					
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$cobrancas->regerarCobranca($dados["id"]);
			}
			
		}
		
		//die();
		
		$this->bancodedados->transacaoTerminar();

	}

	function cancelar_reajuste() {

        if ($this->buscaDadosItem(servidor::getGet("i"))) {
					
			if ($this->ehCancelavelReajuste($this->dados_item["id"])) {
	
				if (!$_POST) {

					$this->conteudo .= $this->carregaArquivo("clientes.cancelar_reajuste.inc.php");

					$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
					$this->conteudo = str_replace("[cpf_cnpj]", $this->dados_item["cpf_cnpj"], $this->conteudo);
					$this->conteudo = str_replace("[situacao_financeira]", $this->buscaSituacaoFinanceira($this->dados_item["id"]), $this->conteudo);
					$this->conteudo = str_replace("[endereco]", $this->dados_item["endereco"], $this->conteudo);
					$this->conteudo = str_replace("[telefone]", $this->dados_item["telefone"], $this->conteudo);
					$this->conteudo = str_replace("[origem]", $this->dados_item["origem_nome"], $this->conteudo);

					$botoes = array();
					$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
					$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
					
					$this->conteudo = str_replace("[acoes]", $this->buscaAcoes($this->dados_item["id"]), $this->conteudo);
					$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
					$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=cancelar_reajuste&i={$this->dados_item["id"]}", $this->conteudo);

				} else {

					$this->cancelaReajuste($this->dados_item["id"]);

					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_df&i={$this->dados_item["id"]}");

				}
				
			} else {
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_df&i={$this->dados_item["id"]}");
			}

        } else {
            header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
        }

	}

}

?>
