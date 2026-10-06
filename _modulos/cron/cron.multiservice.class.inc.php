<?php

class cron_multiservice
{

    /**
     * @var bancodedados
     */
    private $bancodedados;
    /**
     * @var string
     */
    private $diaDe;
    /**
     * @var string
     */
    private $diaAte;
    /**
     * @var string
     */
    private $token;
    private $inicio = "2026-02-16";
    /**
     * @var string
     */
    private $usuario;
    /**
     * @var string
     */
    private $senha;
    /**
     * @var string
     */
    private $link;

    /**
     * Summary of cron_multiservice
     * @param mixed $bancodedados
     * @return void
     */
    function cron_multiservice($bancodedados)
    {

        $this->bancodedados = $bancodedados;
    }

    ##########################
    #### multiservice ###
    ###########################
    function envia()
    {

        $acesso = $this->buscaDadosLogin();
        $this->link = $acesso['link'];
        $this->senha = $acesso['senha'];
        $this->usuario = $acesso['usuario'];
        $this->token();

        $this->diaDe = date('Y-m-d', strtotime("-90 day"));
        $this->diaAte = date('Y-m-d', strtotime("-1 day"));


        $this->diaDe = $this->resolveDateParam($_GET['de'] ?? null, $this->diaDe);
        $this->diaAte = $this->resolveDateParam($_GET['ate'] ?? null, $this->diaAte);

        // Envia itens SVA
        if ($_GET['apenas'] == "ativos" || $_GET['apenas'] == 'ambos' || empty($_GET['apenas'])) {
            $this->inicio = empty($acesso['data_ativa_sva']) ? $this->inicio : $acesso['data_ativa_sva'];
            $itensAtivados = $this->geraAtivados();
            foreach ($itensAtivados as $itemAtivado) {
                $item[0] = $itemAtivado;
                $lista[] = $this->send($item, '/sva/batch', 'POST');
            }
        }

        sleep(1);
        // Envia itens cancelados SVA
        if ($_GET['apenas'] == "cancelados" || $_GET['apenas'] == 'ambos' || empty($_GET['apenas'])) {
            sleep(1);
            $this->inicio = empty($acesso['data_ativa_sva']) ? $this->inicio : $acesso['data_ativa_sva'];
            $itensCancelado = $this->geraCancelados();
            foreach ($itensCancelado as $itemCancelado) {
                $item[0] = $itemCancelado;
                $lista[] = $this->send($item, "/sva/batch/cancel", 'DELETE');
            }
        }
    }

    function buscaDadosLogin()
    {

        $sql = "SELECT * FROM tab_multiservice_login WHERE ativo IS TRUE LIMIT 1;";
        $query = $this->bancodedados->query($sql);

        return $this->bancodedados->resultadoArray($query);
    }


    /**
     * Summary of token
     */
    function token()
    {

        $this->token = $this->buscaToken();
        if ($this->token) {

            return $this->token;
        } else {
            $token = $this->loginNaApi();
            $this->atualizaTokenNoBanco($token);

            return $this->token = $token->access_token;
        }
    }

    /**
     * Summary of buscaToken
     */
    function buscaToken()
    {

        $sql = "SELECT * FROM tab_multiservice_authenticate WHERE id = 1;";
        $query = $this->bancodedados->query($sql);
        $token = $this->bancodedados->resultadoArray($query);

        if ($token["accessToken"] == null) {
            return false;
        } else {
            $agora = strtotime(date('Y-m-d H:i:s'));
            $expira = strtotime(date('Y-m-d H:i:s', strtotime("{$token['data_expira']}")));
            if ($agora > $expira) {
                return false;
            } else {
                return $token['accessToken'];
            }
        }
    }

    /**
     * Summary of loginNaApi
     * @return bool|object
     */
    function loginNaApi()
    {

        $curl = curl_init();
        $url = "{$this->link}/auth/login";
        $filds = '{"username": "' . $this->usuario . '","password": "' . $this->senha . '"}';
        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$url}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $filds,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json',),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $json = json_decode($response);
        if (is_object($json)) {
            if (!empty($json->access_token)) {

                return $json;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Summary of atualizaTokenNoBanco
     * @param mixed $token
     * @return void
     */
    function atualizaTokenNoBanco($token)
    {

        $data = date('Y-m-d H:i:s');
        $newData = "data = '$data',";
        $expiraEM = $token->expiresIn - 30;
        $dataExpira = "data_expira = '" . date('Y-m-d H:i:s', strtotime("{$data} {$expiraEM} second")) . "',";

        if (!empty($token)) {
            $sql = "UPDATE 
                        tab_multiservice_authenticate
                    SET
                        {$newData}
                        {$dataExpira}
                        accessToken = '{$token->access_token}',
                        expireIn = '{$token->expiresIn}'
                    WHERE 
                        id = 1;";
            $this->bancodedados->query($sql);
        }
    }

    /**
     * Summary of send
     * @param mixed $itens
     * @param mixed $endPoint
     * @param mixed $requestMetode
     * @return string
     */
    function send($itens, $endPoint = '/residentials/batch', $requestMetode = 'POST')
    {

        $json = json_encode($itens);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->link . $endPoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $requestMetode,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer {$this->token}"
            ),
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $saida['json'] = $json;
        $saida['response'] = $response;

        $caminhoLog = __DIR__ . '/log_envio.log';
        $fp = fopen($caminhoLog, "a+");
        $envio = "\nJson: " . $json . "\nRetorno: " . utf8_decode($response);
        fwrite($fp, date("d-m-Y H:i:s") . "\n{$envio}\n\n##########################\n\n");
        fclose($fp);

        $recebido = addslashes(utf8_decode($response));
        $enviado = addslashes($json);

        $status = 0;
        if ($httpcode == 201) {
            $status = 1;
        }

        $jsonRetorno = json_decode($saida['response'] , true);
        if($jsonRetorno['statusCode'] != 201) {
            $status = 0;
        }

        // Registra Contrato
        if(empty($itens[0]['contract'])) {
            $contrato = $itens[0] ;
        } else {
            $contrato = $itens[0]['contract'];
        }

        $this->bancodedados->query("
                INSERT INTO
                    tab_multiservice_envio
                SET
                    cliente = (SELECT cli_id FROM tab_contratos WHERE cor_id = '{$contrato}'),
                    contrato = '{$contrato}',
                    enviado = '$enviado',
                    recebido = '$recebido',
                    status = $status");

        // Atualiza contrato
        $this->bancodedados->query("
            UPDATE
                tab_contratos
            SET
                cor_multiservice_enviado = $status
            WHERE
                cor_id = {$contrato} OR cor_pai = {$contrato}");


        return $saida;
    }

    /**
     * 
     * @return bool | array
     */
    function geraAtivados()
    {

        $queryAtivacao = $this->bancodedados->query($this->gerarSql(true));
        if ($this->bancodedados->linhas($queryAtivacao)) {
            $itemAtivados = $this->gerarItens($queryAtivacao, true);

            return $itemAtivados;
        } else {

            return false;
        }
    }

    /**
     * 
     * @return bool | array
     */
    function geraCancelados()
    {

        $queryCancelamento = $this->bancodedados->query($this->gerarSql(false));
        if ($this->bancodedados->linhas($queryCancelamento)) {
            $itemCancelado = $this->gerarItens($queryCancelamento, false);

            return $itemCancelado;
        } else {

            return false;
        }
    }

    /**
     * Summary of gerarSql
     * @param mixed $ativado
     * @return string
     */
    function gerarSql($ativado = true)
    {

        $selectMotivoCancelamento = "IFNULL(tab_cancelamentos_motivos.cam_nome, 'Cancelamento por inadimplencia' ) AS contrato_cancelamento_motivo, ";

        $joinMotivoCancelamento = " LEFT OUTER JOIN tab_cancelamentos ON (tab_contratos.cor_id = tab_cancelamentos.cor_id AND tab_cancelamentos.cas_id = 4) 
                                    LEFT OUTER JOIN tab_cancelamentos_motivos ON (tab_cancelamentos.cam_id = tab_cancelamentos_motivos.cam_id) ";
        $sqlAtivadoCancelado = " AND (tab_contratos.crs_id <> 23) ";
        $sqlAtivadoCancelado .= "AND (
                                        ((tab_contratos.cor_data_cancelamento >= '{$this->diaDe}') AND (tab_contratos.cor_data_cancelamento <= '{$this->diaAte}')) OR
                                        ((tab_contratos.cor_data_anulacao >= '{$this->diaDe}') AND (tab_contratos.cor_data_anulacao <= '{$this->diaAte}'))
                                    ) ";
        $sqlAtivadoCancelado .= "AND (tab_contratos.cor_data_ativacao >= '{$this->inicio}')";
        if ($ativado) {
            $joinMotivoCancelamento = "";
            $selectMotivoCancelamento = "";
            $sqlAtivadoCancelado = " AND (tab_contratos.cor_data_ativacao >= '{$this->diaDe}') AND (tab_contratos.cor_data_ativacao <= '{$this->diaAte}') ";
            $sqlAtivadoCancelado .= "AND (tab_contratos.cor_data_cancelamento IS NULL OR tab_contratos.cor_data_anulacao IS NULL) AND crs_id  IN(3,4,19,20,21) ";
            $sqlAtivadoCancelado .= "AND cor_multiservice_enviado IS FALSE";
        }

        $sql = "SELECT 
                    DISTINCT tab_contratos.cor_id AS contrato_id, tab_contratos.cor_pai contrato_pai,
                    {$selectMotivoCancelamento}
                    tab_contratos.cor_data_ativacao AS data_ativacao,
                    tab_contratos.cor_data_cancelamento AS data_cancelamento,
                    tab_contratos.cli_id AS cliente_id,
                    tab_contratos.cor_id AS contrato_id,
                    tab_clientes.clt_id AS clt_id,
                    tab_clientes.cli_nome AS cliente_nome,
                    tab_clientes.cli_nascimento AS cliente_nascimento,
                    tab_clientes.cli_data_cadastro AS cliente_data_cadastro,
                    SUBSTRING(tab_clientes.cli_complemento, 2) AS cliente_end_complemento,
                    tab_logradouros.log_nome AS cliente_end_logradouro,
                    tab_bairros.bai_nome AS cliente_end_bairro,
                    tab_cidades.cid_nome AS cliente_end_cidade,
                    tab_clientes.cli_numero AS cliente_end_numero,
                    tab_estados.est_sigla AS cliente_end_estado,
                    CONCAT(SUBSTRING(tab_ceps.cep_id, 1, 5), '-', SUBSTRING(tab_ceps.cep_id, 6, 3)) AS cliente_end_cep,
                    tab_ceps.cep_id AS cliente_cep,
                    tab_clientes.cli_cpf_cnpj AS cliente_cpf_cnpj,
                    REPLACE(CONCAT(tab_clientes_contatos_telefones.ccl_ddd, tab_clientes_contatos_telefones.ccl_numero),'-', '') AS cliente_telefone_individual,
                    CONCAT(tab_clientes_contatos_emails.ccm_usuario, '@', tab_clientes_contatos_emails.ccm_dominio) AS cliente_email_individual, 
                    tab_contratos.cor_parametros AS contrato_parametros
                FROM tab_contratos 
                    INNER JOIN tab_clientes ON (tab_contratos.cli_id = tab_clientes.cli_id)
                    LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
                    LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
                    LEFT OUTER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))
                    LEFT OUTER JOIN tab_logradouros ON (tab_logradouros.log_id = tab_ceps.log_id)
                    LEFT OUTER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
                    LEFT OUTER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
                    LEFT OUTER JOIN tab_clientes_comodatos ON tab_clientes_comodatos.cor_id = tab_contratos.cor_id 
                    {$joinMotivoCancelamento}
                    INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
                    INNER JOIN tab_clientes_contatos_telefones ON (tab_clientes.cli_id = tab_clientes_contatos_telefones.cli_id)
                    INNER JOIN tab_clientes_contatos_emails ON (tab_clientes.cli_id = tab_clientes_contatos_emails.cli_id)
                WHERE 
                    (tab_contratos.crt_id IN (11,13))
                    -- AND tab_clientes.clt_id = 1
                    {$sqlAtivadoCancelado}
                GROUP BY 
                    tab_contratos.cor_id 
                ORDER BY 
                    tab_contratos.cor_data_ativacao";
        return $sql;
    }

    /**
     * Summary of gerarItens
     * @param mixed $query
     * @param mixed $ativacao
     * @return array
     */
    function gerarItens($query, $ativacao = true)
    {

        $cliente = array();
        $c = 0;

        while (($item = $this->bancodedados->resultadoArray($query)) != false) {

            if ($item['clt_id'] == 2) {

                $this->bancodedados->query("UPDATE tab_contratos SET cor_multiservice_enviado = 1 WHERE cor_id = {$item['contrato_id']}");

                return null;
            }
            $sql = "SELECT 
                        tab_multiservice_servicos.codigo codigoServico, tab_multiservice_players.codigo codigoPlayer, tab_assinaturas.pla_id planoId
                    FROM 
                        tab_planos_multiservice
                        LEFT OUTER JOIN tab_assinaturas ON tab_assinaturas.pla_id = tab_planos_multiservice.pla_id
                        LEFT OUTER JOIN rel_ass_cor_estacontido ON rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id
                        LEFT OUTER JOIN tab_multiservice_players ON tab_multiservice_players.id = tab_planos_multiservice.player
                        LEFT OUTER JOIN tab_multiservice_servicos ON tab_multiservice_servicos.id = tab_planos_multiservice.servico
                    WHERE 
                        rel_ass_cor_estacontido.cor_id = {$item['contrato_id']} 
                    GROUP BY 
                        tab_multiservice_servicos.id, tab_multiservice_players.id";
            $sql = $this->bancodedados->query($sql);
            $linhas = $this->bancodedados->linhas($sql);
            if (!$linhas) {
                $this->bancodedados->query("UPDATE tab_contratos SET cor_multiservice_enviado = 1 WHERE cor_id = {$item['contrato_id']}");
            }

            while (($servicos = $this->bancodedados->resultadoArray($sql)) != false) {

                $item['contrato_origem'] = $item['contrato_id'];
                if ($item['contrato_pai']) {
                    $item['contrato_id'] = $item['contrato_pai'];
                }

                if ($ativacao) {
                    $cliente[$c]["cpf"] = preg_replace('/[^0-9]/', "", $item["cliente_cpf_cnpj"]);
                    $cliente[$c]["contract"] = $item["contrato_id"];
                    $cliente[$c]["cliente_id"] = $item["cliente_id"];
                    $cliente[$c]["name"] = utf8_encode($item["cliente_nome"]);
                    $cliente[$c]["email"] = $item["cliente_email_individual"];
                    $cliente[$c]["phone"] = preg_replace('/[^0-9]/', "", $item["cliente_telefone_individual"]);
                } else {
                    $cliente[$c] = $item["contrato_id"];
                }

                $c++;
            }
        }

        $clientes = array_unique($cliente, SORT_REGULAR);
        return $clientes;
    }

    /**
     * 
     * @param mixed $day
     * @param string $formato
     * @return string
     */
    function converteData($day, $formato = 'd/m/Y')
    {

        return date($formato, strtotime($day));
    }

    /**
     * @param mixed $value
     * @param string $default
     * @return string
     */
    private function resolveDateParam($value, $default)
    {

        if ($value === null) {
            return $default;
        }

        $value = trim((string)$value);
        if ($value === '') {
            return $default;
        }

        if ($this->isValidDate($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            $days = (int)$value;
            $days = $days < 0 ? abs($days) : $days;

            return date('Y-m-d', strtotime("-{$days} day"));
        }

        return $default;
    }

    /**
     * @param string $value
     * @param string $format
     * @return bool
     */
    private function isValidDate($value, $format = 'Y-m-d')
    {

        if ($format === 'Y-m-d') {
            $parts = explode('-', $value);
            if (count($parts) !== 3) {
                return false;
            }

            $year = (int)$parts[0];
            $month = (int)$parts[1];
            $day = (int)$parts[2];

            return checkdate($month, $day, $year) && $value === date('Y-m-d', strtotime($value));
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return false;
        }

        return date($format, $timestamp) === $value;
    }
}
