<?php

class cobrancas_boleto_sicredi_util {

    var $bancodedados;

    /**
     * @param type $bd
     */
    function __construct($bd) {
        $this->bancodedados = $bd;
    }

    function carregaDadosDaCobranca($cobranca_id) {

        $query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_id AS cobranca_id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas.coa_nosso_numero AS nossoNumero,
				tab_cobrancas.coa_parametros AS parametros,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_cobrancas.coa_valor_desconto AS valor_desconto,
				tab_cobrancas.coa_valor_bonificacao AS desconto,
				tab_cobrancas.coa_data_vencimento AS data_vencimento,
				tab_cobrancas.cof_id AS formato_id,
				tab_cobrancas.fat_id AS fatura_id,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.clo_id AS origem_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
				tab_clientes.clt_id AS cliente_tipo_id,
                IF(tab_cobrancas.col_id IS NULL, 1, 0) AS geracao_manual,
                (SELECT REPLACE(CONCAT(ccl_ddd, ccl_numero),'-', '') FROM tab_clientes_contatos_telefones WHERE cli_id = tab_clientes.cli_id LIMIT 1) AS cliente_telefone
			FROM
				tab_cobrancas
				INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
			WHERE
				tab_cobrancas.coa_id = $cobranca_id");

        if (($dadosCobranca = $this->bancodedados->resultadoArray($query)) != false) {

            $parametros = unserialize($dadosCobranca["parametros"]);
            if(!empty($parametros['parametros'])) {
                $dadosCobranca["parametros"] = $parametros['parametros'];
            } else {
                $dadosCobranca["parametros"] = $parametros;
            }

            $dadosCobranca["cliente_nome"] = preg_replace("/\\[[^\\]]+\\]/", "", $dadosCobranca["cliente_nome"]);

            $dadosCobranca["valor_original_formatado"] = $this->formataDinheiro($dadosCobranca["valor_original"]);

            $dadosCobranca = array_merge((array)$dadosCobranca, (array)$dadosCobranca["parametros"]["boleto"]);
            $dadosCobranca = array_merge((array)$dadosCobranca, (array)$this->buscaEnderecoCobranca($dadosCobranca["cliente_id"]));

            $this->dadosdadosCobranca["competencia"] = $this->buscaCompetencia($dadosCobranca["id"]);

            if ($this->ehCobrancaCondominial($cobranca_id)) {
                $dadosCobranca["detalhamento"] = $this->buscaDetalhamentoCondominial($dadosCobranca["id"]);
            } else {
                $dadosCobranca["detalhamento"] = $this->buscaDetalhamento($dadosCobranca["id"]);
            }
        }

        return $dadosCobranca;
    }

    function ehCobrancaCondominial($cobranca_id) {

        $query = $this->bancodedados->query("
            SELECT
                    COUNT(DISTINCT tab_assinaturas.cli_id)
            FROM
                    tab_cobrancas_detalhes
                    INNER JOIN tab_assinaturas ON (tab_cobrancas_detalhes.ass_id = tab_assinaturas.ass_id)
            WHERE
                    tab_cobrancas_detalhes.coa_id = $cobranca_id");

        return $this->bancodedados->resultado($query) > 1;
    }

    function formataDinheiro($valor, $moeda = "R$", $exibir_nulo = false) {

        $valor_formatado = preg_replace("/[^0-9]/", "", intval($valor, 10));
        if (!$valor_formatado && !$exibir_nulo)
            return false;
        $valor_formatado = number_format($valor_formatado / 100, 2, ",", ".");
        if ($moeda)
            $valor_formatado = "$moeda $valor_formatado";
        if ($valor < 0)
            $valor_formatado = "($valor_formatado)";

        return $valor_formatado;
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
				INSERT(LPAD(tab_ceps.cep_exportacao, 8, '0'), 6, 0, '-') AS cep
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

        $dados = $this->bancodedados->resultadoArray($query);

        if ($dados) {
            $dados["complemento"] = $this->corrigeComplementoCliente($dados["complemento"]);
        } else {
            $dados = $this->buscaEndereco($cliente_id);
        }

        $endereco = array();
        $logradouro = array();
        $complemento = array();
        $apartamento = array();

        if ($dados["logradouro"])
            $logradouro[] = $dados["logradouro"];
        if ($dados["numero"])
            $logradouro[] = $dados["numero"];

        if ($dados["condominio"]) {
            if ($dados["complemento"])
                $apartamento[] = $dados["complemento"];
            if ($dados["bloco"] && $dados["bloco"] != "Único")
                $apartamento[] = $dados["bloco"];
            if ($apartamento)
                $complemento[] = implode(" / ", $apartamento);
        } else {
            if ($dados["complemento"])
                $logradouro[] = $dados["complemento"];
        }

        if ($complemento) {
            $logradouro[] = implode(", ", $complemento);
        }

        $logradouro = implode(", ", $logradouro);

        $endereco[] = $logradouro;
        $endereco[] = $dados["bairro"];
        $endereco[] = "{$dados["cep"]} - {$dados["cidade"]} - {$dados["estado"]}";

        $dados["endereco"] = implode("\n", $endereco);

        return $dados;
    }

    function buscaEndereco($cliente_id) {

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
				INSERT(LPAD(tab_ceps.cep_exportacao, 8, '0'), 6, 0, '-') AS cep
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

        $complementos_tipos = array("a" => "Apto ", "l" => "Loja ", "s" => "Sala ", "c" => "Casa ", "o" => "");

        if ($complemento && array_key_exists($complemento[0], $complementos_tipos)) {
            return $complementos_tipos[$complemento[0]] . substr($complemento, 1, strlen($complemento));
        }
    }

    function buscaCompetencia($cobranca_id) {

        $query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(MAX(cod_competencia_termino), '%m / %Y')
			FROM
				tab_cobrancas_detalhes
			WHERE
				coa_id  = {$cobranca_id}");

        return $this->bancodedados->resultado($query);
    }

    function buscaDetalhamentoCondominial($cobranca_id) {

        $total = array("descricao" => "Total", "valor_original" => 0, "valor_final" => 0, "inicio" => "", "termino" => "", "filho" => 0);

        $detalhamento = array();

        $query = $this->bancodedados->query("
			SELECT
				CONCAT(tab_clientes.cli_nome, ' (', SUBSTRING(tab_clientes.cli_complemento, 2), IF(cob_nome IS NOT NULL AND cob_nome <> 'Único', IF(LENGTH(cli_complemento) > 1, CONCAT(' / ' , cob_nome), cob_nome), ''), ')') AS descricao,
				SUM(cod_valor_original) AS valor_original,
				SUM(cod_valor_final) AS valor_final,
				CONCAT(DATE_FORMAT(MIN(cod_competencia_inicio), '%d/%m/%Y'), ' à ', DATE_FORMAT(MAX(cod_competencia_termino), '%d/%m/%Y')) AS periodo
			FROM
				tab_cobrancas_detalhes
				LEFT OUTER JOIN tab_assinaturas ON (tab_cobrancas_detalhes.ass_id = tab_assinaturas.ass_id)
				LEFT OUTER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id)
				LEFT OUTER JOIN tab_descontos_concessoes ON (tab_cobrancas_detalhes.dec_id = tab_descontos_concessoes.dec_id)
				LEFT OUTER JOIN tab_assinaturas tab_assinaturas_descontos ON (tab_descontos_concessoes.ass_id = tab_assinaturas_descontos.ass_id)
				INNER JOIN tab_clientes ON (tab_clientes.cli_id = IF(tab_assinaturas.cli_id IS NOT NULL, tab_assinaturas.cli_id, IF(tab_ajustes_financeiros.cli_id IS NOT NULL, tab_ajustes_financeiros.cli_id, tab_assinaturas_descontos.cli_id)))
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
			WHERE
				coa_id  = {$cobranca_id}
			GROUP BY
				tab_clientes.cli_id
			ORDER BY
				IF(cli_pai IS NULL, 1, 0),
				cli_nome");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

            $total["valor_original"] += $dados["valor_original"];
            $total["valor_final"] += $dados["valor_final"];

            $dados["descricao"] = preg_replace("/\\[[^\\]]+\\]/", "", $dados["descricao"]);
            $dados["valor_original"] = number_format($dados["valor_original"] / 100, 2, ",", ".");
            $dados["valor_final"] = number_format($dados["valor_final"] / 100, 2, ",", ".");

            $detalhamento[] = $dados;
        }

        $total["valor_original"] = number_format($total["valor_original"] / 100, 2, ",", ".");
        $total["valor_final"] = number_format($total["valor_final"] / 100, 2, ",", ".");

        $detalhamento[] = $total;

        return $detalhamento;
    }

    /**
     * 
     * @param type $cobranca_id
     * @return type
     */
    function buscaDetalhamento($cobranca_id) {

        $total = array("descricao" => "Total", "valor_original" => 0, "valor_final" => 0, "inicio" => "", "termino" => "", "filho" => 0);
        $detalhamento = array();

        $query = $this->bancodedados->query("
			SELECT
				cod_descricao AS descricao,
				cod_valor_original AS valor_original,
				cod_valor_final AS valor_final,
				CONCAT(DATE_FORMAT(cod_competencia_inicio, '%d/%m/%Y'), ' à ', DATE_FORMAT(cod_competencia_termino, '%d/%m/%Y')) AS periodo,
				IF(cod_pai IS NULL, 0, 1) AS filho,
				(SELECT afc_id FROM tab_ajustes_financeiros WHERE tab_ajustes_financeiros.ajf_id = tab_cobrancas_detalhes.ajf_id) AS ajuste_categoria_id,
				dec_id AS desconto_concessao_id
			FROM
				tab_cobrancas_detalhes
			WHERE
				coa_id  = {$cobranca_id}
			ORDER BY
				IF(cod_competencia_inicio IS NULL, '9999-12-31', cod_competencia_inicio),
				cod_id");

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

            $total["valor_original"] += $dados["valor_original"];
            $total["valor_final"] += $dados["valor_final"];

            $dados["descricao"] = preg_replace("/\\[[^\\]]+\\]/", "", $dados["descricao"]);
            $dados["valor_original"] = number_format($dados["valor_original"] / 100, 2, ",", ".");
            $dados["valor_final"] = number_format($dados["valor_final"] / 100, 2, ",", ".");

            if ($dados["desconto_concessao_id"]) {
                $dados["descricao"] = preg_replace("/.*\\(([^\\)]+)\\)/", "$1", $dados["descricao"]);
            } elseif ($dados["ajuste_categoria_id"] == 10) {
                $dados["descricao"] = "Multa Rescisória";
                $dados["periodo"] = "";
            } elseif ($dados["ajuste_categoria_id"] == 13) {
                $dados["descricao"] = "Estorno Migração de Plano";
            } elseif ($dados["ajuste_categoria_id"] == 18) {
                $dados["descricao"] = "Desconto Síndico";
            } elseif ($dados["ajuste_categoria_id"] == 19) {
                $dados["descricao"] = "Taxa de Instalação";
                $dados["periodo"] = "";
            } elseif ($dados["ajuste_categoria_id"] == 20) {
                $dados["descricao"] = "Serviços Técnicos";
                $dados["periodo"] = "";
            } elseif ($dados["ajuste_categoria_id"] == 22) {
                $dados["descricao"] = "Desconto Indique Amigo";
            } elseif ($dados["ajuste_categoria_id"] == 22) {
                $dados["descricao"] = "Compra de Equipamentos";
                $dados["periodo"] = "";
            }

            $detalhamento[] = $dados;
        }

        $total["valor_original"] = number_format($total["valor_original"] / 100, 2, ",", ".");
        $total["valor_final"] = number_format($total["valor_final"] / 100, 2, ",", ".");

        $detalhamento[] = $total;

        return $detalhamento;
    }

    /**
     * 
     * @param type $token
     */
    function atualizaTokenNoBanco($token, $naoAtualizarData = true) {
        
        $dataExpira = $data = "";
        if (!$naoAtualizarData) {
            $data = date('Y-m-d H:i:s');
            $newData = "data = '$data',";
            $expiraEM = $token->getExpiresIn() - 30;
            $dataExpira = "data_expira = '" . date('Y-m-d H:i:s', strtotime("{$data} {$expiraEM} second")) . "',";
        }
        if(!empty($token)) {
            $sql = "UPDATE 
                        tab_sicredi_authenticate
                    SET
                        {$newData}
                        {$dataExpira}
                        scope = '{$token->getScope()}',
                        access_token = '{$token->getAccessToken()}',
                        expires_in = '{$token->getExpiresIn()}',
                        refresh_expires_in = '{$token->getRefreshExpiresIn()}',
                        refresh_token = '{$token->getRefreshToken()}',
                        token_type = '{$token->getTokenType()}',
                        id_token = '{$token->getIdToken()}',
                        not_before_policy = '{$token->getNotBeforePolicy()}',
                        session_state = '{$token->getSessionState()}'
                    WHERE 
                        id = 1;";
            $this->bancodedados->query($sql);
        }
    }
}
