<?php
//header('Content-Type: text/html; charset=iso-8859-1');

/**
 * <b>loadclass:</b> Classe principal da API. Responsável pela validação inicial da classe e seus parametros,
 * assim como chama as sub-classes conforme solicitado na url  
 * 
 * @author Acacio Fagundes
 * @copyright 2020 
 *
 */

require_once 'config/config_api.php';
require_once 'config/formats.php';

set_include_path(INTRANET_HOSTFISICO);
date_default_timezone_set('America/Sao_paulo');

/*require_once("../_global/constantes.inc.php");
require_once("../_global/controle.class.inc.php");
require_once("../_global/funcoes.class.inc.php");*/

require_once '../_global/bancodedados.class.inc.php';
require_once("../_global/controle.class.inc.php");
//require_once("../_global/modulo.class.inc.php");




class loadclass
{

    public $dadosClasse = array();
    public $parametrosURL = array();
    public $db;

    public $historyId = 0;

    //constroe a classe e chama as validações iniciais
    function __construct()
    {
        $this->dadosClasse['retorno_status'] = false;
    }

    function Inicializa()
    {
        // Inicializa e valida conexão com banco de dados
        $this->conectaDB();


        // Verifica se foi envia via POST a autenticação
        if (!$this->validaMetodoRequisicao()) {
            exit();
        }

        // Verifica login do parceiro
        if (!$this->validaAcesso()) {
            exit;
        }

        $this->verificaClasse();
    }



    public function setHistory($login, $classe, $requestIp, $setPost)
    {

        $this->conectaDB();

        if (is_array($classe)) {
            $convClass = json_encode($classe);
        } else {
            $convClass = '';
        }

        if (is_array($setPost)) {
            unset($setPost['requestIp']);
            unset($setPost['login']);
            //$setPost['keyapi'] = ( $setPost['keyapi'] != '' ? 'informed' : '' );
            $setPost['pass'] = ($setPost['pass'] != '' ? 'informed' : '');

            $convPost = json_encode($setPost);
        } else {
            $convPost = '';
        }

        if (is_array($setGet)) {
            $convGet = json_encode($setGet);
        } else {
            $convGet = '';
        }

        $this->db->query(
            "INSERT 
        INTO 
            tab_clientes_origens_historicoapi 
        SET 
            requestDate='" . @date('Y-m-d H:i:s') . "',
            requestLogin='$login',
            requestIp='" . $requestIp . "',
            requestPost='" . addslashes(@substr(str_replace('\'', '', $convPost), 0, 1500)) . "',
            requestGet='" . addslashes(@substr(str_replace('\'', '', $convClass), 0, 1500)) . "',
            requestResult=''"
        );

        $this->historyId = $this->db->ultimoIdInserido();
    }


    public function setResultHistory($result)
    {

        $result = @substr(str_replace('\'', '', $result), 0, 1500);

        if (substr($result, -1) == '\\') {
            $result = substr($result, 0, -1);
        }

        if (intval($this->historyId) > 0) {

            $this->conectaDB();

            $this->db->query("
					UPDATE
						tab_clientes_origens_historicoapi
					SET
						requestResult = '" . ($result) . "'
					WHERE
						id = " . $this->historyId);
        }
    }


    public function validIP($login, $requestIp) {}


    public function validaCobrancaCarona()
    {

        $countFormPagtos = $this->db->resultadoArray($this->db->query("SELECT COUNT(cof_id) as total FROM rel_cof_clo_disponivel WHERE clo_id = " . $this->idParceiro));

        if ($countFormPagtos['total'] > 1) { // Tem outras cobranças além do carteira

            return false;
        }

        return true;
    }


    public function cobrancaAberto($idContrato)
    {

        $this->conectaDB();
        require_once '_modulos/cobrancas/cobrancas.class.inc.php';
        $intranetCobranca = new cobrancas($this->db, null, false);
        $dadosBoletos = $intranetCobranca->buscaClientesCobrancasContrato($idContrato);

        foreach ($dadosBoletos as $v) {

            if ($v["status_id"] == 1) {

                if (intval($v["valor_original"]) > 0) {

                    return true;
                }
            }
        }

        return false;
    }


    /**
     * <b>validaAcesso:</b> Função que executa a validade de login, senha e chave de acesso API.
     * Também verifica a validação do IP de origem, caso o parceiro tenha limitação de IP
     * 
     * @return array (status, error)
     */
    public function validaAcesso()
    {
        $login = trim($_POST['login']);
        $pass = trim($_POST['pass']);
        $key = trim($_POST['keyapi']); // sha1 do tab_clientes_origens.clo_id

        $this->dadosClasse['retorno_status'] = false;
        if ($login == '' || $pass == '' || $key == '') {

            $this->setHistory($login, '', $_POST['requestIp'], $_POST);
            $this->dadosClasse['error'] = 'Um dos campos de autenticação estão vazios ou não informado!';


            return false;
        } else {
            $query = $this->db->query("SELECT * FROM tab_clientes_origens WHERE clo_login = '" . addslashes($login) . "' and (clo_pass= '" . addslashes(md5($pass)) . "' or clo_pass= '" . addslashes($pass) . "') and cos_id='2'");
            //echo "SELECT * FROM tab_clientes_origens WHERE clo_login = '$login' and clo_pass= '".md5($pass)."'";
            $this->dadosLogin = $this->db->resultadoArray($query);

            if (intval(@$this->dadosLogin['clo_id']) == 0) {

                if ($login != '') {

                    $query = $this->db->query("SELECT * FROM tab_clientes_origens WHERE clo_login = '" . addslashes($login) . "'");
                    $this->dadosLogin = $this->db->resultadoArray($query);

                    if (intval(@$this->dadosLogin['clo_id']) > 0) {

                        if ($this->dadosLogin['clo_errorapi'] <= 3) {
                            $this->db->query("UPDATE tab_clientes_origens SET clo_errorapi = clo_errorapi + 1 WHERE clo_id = " . $this->dadosLogin['clo_id']);
                            $this->dadosClasse['error'] = 'Dados de autenticação são inválidos!';
                            $this->setHistory($login, '', $_POST['requestIp'], $_POST);
                        } else {
                            $this->dadosClasse['error'] = 'Seu acesso a API esta bloqueado!';
                            $this->setHistory($login, '', $_POST['requestIp'], $_POST);
                        }
                    } else {
                        $this->dadosClasse['error'] = 'Dados de autenticação são inválidos!';
                        $this->setHistory($login, '', $_POST['requestIp'], $_POST);
                    }
                } else {
                    $this->dadosClasse['error'] = 'Dados de autenticação são inválidos!';
                    $this->setHistory($login, '', $_POST['requestIp'], $_POST);
                }
            } else {

                if (sha1(@$this->dadosLogin['clo_hashapi']) == $key) {

                    if ($this->dadosLogin['clo_errorapi'] <= 3) {

                        if ($this->dadosLogin['clo_ip'] == '') {
                            $this->idParceiro = intval(@$this->dadosLogin['clo_id']);
                            $this->nameParceiro = @$this->dadosLogin['clo_nome'];
                            return true;
                        } else {

                            $IPsParceiro = explode('|', $this->dadosLogin['clo_ip']);

                            if (in_array($_POST['requestIp'], $IPsParceiro)) {
                                $this->idParceiro = intval(@$this->dadosLogin['clo_id']);
                                $this->nameParceiro = @$this->dadosLogin['clo_nome'];
                                $this->db->query("UPDATE tab_clientes_origens SET clo_errorapi = 0 WHERE clo_id = " . $this->dadosLogin['clo_id']);
                                return true;
                            } else {
                                $this->dadosClasse['error'] = 'IP não liberado para acesso a API!';
                                $this->setHistory($login, '', $_POST['requestIp'], $_POST);
                            }
                        }
                    } else {
                        $this->dadosClasse['error'] = 'Seu acesso a API esta bloqueado!';
                        $this->setHistory($login, '', $_POST['requestIp'], $_POST);
                    }
                } else {

                    $this->db->query("UPDATE tab_clientes_origens SET clo_errorapi = clo_errorapi + 1 WHERE clo_id = " . $this->dadosLogin['clo_id']);
                    $this->dadosClasse['error'] = 'Dados de autenticação são inválidos!';
                    $this->setHistory($login, '', $_POST['requestIp'], $_POST);
                }
            }
            return false;
        }
    }


    function conectaDB()
    {
        $this->db = new bancodedados(false); //  new database();
        $this->db->conectar(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->db->conexao->connect_error) {
            $this->dadosClasse['error'] = 'Não foi possível estabelecer uma conexão com o banco de dados';
            exit();
        }
    }


    function arrayEnderecoCEP($idCep, $idParceiro)
    {
        $this->conectaDB();


        $query = $this->db->query("
    SELECT
      tab_ceps.cep_id AS cep_id,
                    tab_ceps.est_id AS est_id,
                    tab_estados.est_nome AS estado,
                    tab_estados.est_sigla AS estado_sigla,
                    tab_ceps.cid_id AS cid_id,
                    tab_cidades.cid_nome AS cidade,
                    tab_ceps.bai_id as bai_id,
                    tab_bairros.bai_nome AS bairro,
                    tab_ceps.log_id as log_id,
                    tab_logradouros.log_nome AS logradouro,
                    tab_ceps.cep_exportacao as cep_exportacao,
                    tab_ceps.cep_zona as cep_zona
    FROM
      tab_ceps
      INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
      INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
      INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
      INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
    WHERE
      tab_ceps.cep_id = '" . str_replace('-', '', $idCep) . "' AND
				tab_cidades.cid_id IN (
					SELECT
						cid_id
					FROM
						rel_clo_cid_contem
					WHERE
						clo_id = " . $idParceiro . "
				)");
        return $this->db->resultadoArray($query);
    }


    function criaEndereco($est_id, $cid_id, $cep, $bairro, $logradouro)
    {

        $this->conectaDB();

        // Analisa Bairro
        $nmBairro = $this->textConvertName($bairro);
        $queryBairro = $this->db->query("SELECT bai_id FROM `tab_bairros` WHERE `bai_nome` = '" . addslashes($nmBairro) . "' and cid_id=" . $cid_id);
        $validaBairro = $this->db->resultadoArray($queryBairro);
        if (intval($validaBairro['bai_id']) == 0) { // Não achou o bairro, entao vai inserir
            $this->db->query("INSERT INTO tab_bairros SET bai_nome='" . addslashes($nmBairro) . "',bai_cep_geral=null,cid_id=" . intval($cid_id));
            $validaBairro['bai_id'] = $this->db->ultimoIdInserido();
        }

        // Analisa logradoudo
        $queryLogradouro = $this->db->query("SELECT log_id FROM `tab_logradouros` WHERE `log_nome` = '" . addslashes($logradouro) . "' and bai_id=" . $validaBairro['bai_id']);
        $validaLogradouro = $this->db->resultadoArray($queryLogradouro);
        if (intval($validaLogradouro['log_id']) == 0) { // Não achou o logradoudo, entao vai inserir
            $this->db->query("INSERT INTO tab_logradouros SET log_nome='" . addslashes($logradouro) . "',log_observacao='',bai_id=" . $validaBairro['bai_id']);
            $validaLogradouro['log_id'] = $this->db->ultimoIdInserido();
        }

        // Cria o CEP em si
        $this->db->query("INSERT INTO tab_ceps SET cep_id='" . $cep . "', log_id='" . $validaLogradouro['log_id'] . "',bai_id='" . $validaBairro['bai_id'] . "',cid_id='" . $cid_id . "',est_id='" . $est_id . "',cep_exportacao='" . $cep . "',cep_zona=''");
        $newCepId = $this->db->ultimoIdInserido();
        if (intval($newCepId) == 0) { // Não conseguiu inserir novo CEP
            $returnClass['retorno_status'] = false;
            $returnClass['error'] = "Houve uma falha ao inserir novo CEP - <i><u>(" . $cep . " - " . $logradouro . " - " . $nmBairro . " - " . $cid_id . "/" . $est_id . ")</u></i>";
        } else {
            $returnClass['retorno_status'] = true;
        }

        return $returnClass;
    }


    function getCEP2($url)
    {

        $response = file_get_contents($url); // faz a requisi��o � API

        return json_decode($response, true);
    }


    function getCEP($url)
    {

        $context = stream_context_create([
            'http' => [
                'timeout' => 10 // Timeout de X segundos
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return [];
        }

        $json = json_decode($response, true);

        return is_array($json) ? $json : [];
    }


    function buscaCEP2($cep)
    {

        $cep = str_replace('-', '', $cep);

        $jsonCEP = $this->getCEP('https://brasilapi.com.br/api/cep/v2/' . $cep);
        if ($jsonCEP['city'] != '') {
            // {"cep":"88102650","state":"SC","city":"São José","neighborhood":"Praia Comprida","street":"Rua Joaquim Vaz","service":"open-cep","location":{"type":"Point","coordinates":{}}}

            $jsonCEP['localidade'] = $jsonCEP['city'];
            $jsonCEP['logradouro'] = $jsonCEP['street'];
            $jsonCEP['bairro'] = $jsonCEP['neighborhood'];
        } else {

            $jsonCEP = $this->getCEP('https://opencep.com/v1/' . $cep . '.json');

            /*
            
            $jsonCEP = $this->getCEP('https://viacep.com.br/ws/'.$cep.'/json/');

            if ( $jsonCEP['localidade'] != '' ) {
                $jsonCEP = $this->getCEP('https://api.postmon.com.br/v1/cep/'.$cep);
                $jsonCEP['localidade']  = $jsonCEP['cidade'];
            }    
            else {
                $jsonCEP['localidade'] = $jsonCEP['localidade']; 
                $jsonCEP['logradouro'] = $jsonCEP['logradouro'];
                $jsonCEP['bairro'] = $jsonCEP['bairro']; 
            }
            */
        }



        return $jsonCEP;
    }

    function buscaCEP($cep)
    {

        $cep = str_replace('-', '', $cep);
        $jsonCEP['localidade'] = '';
        $jsonCEP['logradouro'] = '';
        $jsonCEP['bairro'] = '';

        $jsonCEP = $this->getCEP('https://brasilapi.com.br/api/cep/v2/' . $cep);
        if (!empty($jsonCEP['city'])) {

            $jsonCEP['localidade'] = $jsonCEP['city'];
            $jsonCEP['logradouro'] = $jsonCEP['street'];
            $jsonCEP['bairro'] = $jsonCEP['neighborhood'];
        } else {

            $jsonCEP = $this->getCEP('https://opencep.com/v1/' . $cep . '.json');

            if (empty($jsonCEP['localidade'])) {

                $jsonCEP = $this->getCEP('https://viacep.com.br/ws/' . $cep . '/json/');
            }
        }

        return $jsonCEP;
    }

    function buscaCEPDisponivel($prefixo_cep)
    {

        if (strlen($prefixo_cep) == 5) {

            $query = $this->db->query("
				SELECT
					COUNT(cep_id)
				FROM
					tab_ceps
				WHERE
					cep_id = '{$prefixo_cep}001'");

            $cep = $this->db->resultado($query);

			if (!$cep) {
                return "{$prefixo_cep}001";
            }
			
            $query = $this->db->query("
				SELECT
					MIN(cep_id)
				FROM
					tab_ceps
				WHERE
					cep_id LIKE '{$prefixo_cep}___' AND
					cep_id <> '{$prefixo_cep}999' AND
					cep_id + 1 NOT IN (
						SELECT
							cep_id
						FROM
							tab_ceps
						WHERE
							cep_id LIKE '{$prefixo_cep}%'
					)");

            $cep = $this->db->resultado($query);

            if ($cep && $cep != "{$prefixo_cep}999") {
                return $cep + 1;
            }
			
        } elseif (strlen($prefixo_cep) == 6) {

            $query = $this->db->query("
				SELECT
					COUNT(cep_id)
				FROM
					tab_ceps
				WHERE
					cep_id = '{$prefixo_cep}01'");

            $cep = $this->db->resultado($query);

			if (!$cep) {
                return "{$prefixo_cep}01";
            }

            $query = $this->db->query("
				SELECT
					MIN(cep_id)
				FROM
					tab_ceps
				WHERE
					cep_id LIKE '{$prefixo_cep}__' AND
					cep_id <> '{$prefixo_cep}99' AND
					cep_id + 1 NOT IN (
						SELECT
							cep_id
						FROM
							tab_ceps
						WHERE
							cep_id LIKE '{$prefixo_cep}%'
					)");

            $cep = $this->db->resultado($query);

            if ($cep && $cep != "{$prefixo_cep}99") {
                return $cep + 1;
            }
			
        }

        return $this->buscaCEPDisponivel($prefixo_cep + 1);
		
    }


    function getEnderecoByCEP($cep, $logradouro = '', $bairro = '', $cidade = '', $numero = '', $complemento = '')
    {
        $this->conectaDB();

        $endereco = $this->arrayEnderecoCEP($cep, $this->idParceiro);

        if (@intval($endereco['cep_id']) == 0) {

            //if ( $this->idParceiro == '383' ) {
            //	echo '-- MD Connect - passou pelo getEnderecoByCEP';
            //}

            if (trim($logradouro) == '' || trim($bairro) == '') {
                //if (trim($logradouro)=='' || trim($bairro) =='') {
                $dados["id"] = 0;
                $dados["msgError"] = "Logradouro e/ou bairro invalido! - " . trim($logradouro) . " - " . trim($bairro);
            } else {


                $queryBairro = $this->db->query("SELECT cid_id FROM `tab_bairros` AS bairros WHERE bairros.bai_cep_geral = '" . str_replace('-', '', $cep) . "'");
                $validaBairro = $this->db->resultadoArray($queryBairro);

                if (intval($validaBairro['cid_id']) > 0) {

                    //if ( $this->idParceiro == '383' ) {
                    //	echo '-- MD Connect - achou o bairro com o CEP: '+str_replace('-', '', $cep);
                    //}

                    $queryCidade = $this->db->query("SELECT cidade.cid_id, cidade.est_id, cidade.cid_cep_geral FROM `tab_cidades` AS cidade INNER JOIN rel_clo_cid_contem ON (rel_clo_cid_contem.cid_id = cidade.cid_id) WHERE cidade.cid_id = '" . intval($validaBairro['cid_id']) . "' and rel_clo_cid_contem.clo_id=" . $this->idParceiro);
                    $validaCidade = $this->db->resultadoArray($queryCidade);
                } else {

                    $queryCidade = $this->db->query("SELECT cidade.cid_id, cidade.est_id, cidade.cid_cep_geral FROM `tab_cidades` AS cidade INNER JOIN rel_clo_cid_contem ON (rel_clo_cid_contem.cid_id = cidade.cid_id) WHERE cidade.cid_cep_geral = '" . str_replace('-', '', $cep) . "' and rel_clo_cid_contem.clo_id=" . $this->idParceiro);
                    $validaCidade = $this->db->resultadoArray($queryCidade);
                }


                //if ( $this->idParceiro == '383' ) {
                //	echo '-- MD Connect - $validaCidade[cid_id]: ' .$validaCidade['cid_id'];
                //}


                if (intval($validaCidade['cid_id']) == 0) {

                    //print('Criando new CEP');
                    $validCEP = false;

                    $jsonCEP = $this->buscaCEP($cep);

                    $cep = str_replace('-', '', $cep);


                    /*$cep = str_replace('-', '', $cep);
                    
                    $hostAPI = 'https://viacep.com.br/ws/'.$cep.'/json/';
                    $jsonCEP = json_decode( file_get_contents($hostAPI), false);
                    if ( $jsonCEP['localidade'] != '' ) {
                    	
                    	$hostAPI = 'https://cdn.apicep.com/file/apicep/'.substr($cep, 0, 5).'-'.substr($cep,5).'.json';
                    	$jsonCEP = json_decode( file_get_contents($hostAPI), false);
                    	if ( $jsonCEP['code'] != 'not_found' ) {
                    		
                    		
                    		
                    		$jsonCEP['logradouro']  = $jsonCEP['address'];
                    		$jsonCEP['bairro']  = $jsonCEP['district'];
                    		$jsonCEP['localidade']  = $jsonCEP['city'];
                    		
                    		
                    	}
                    	else {
                    		
                    		$hostAPI = 'https://api.postmon.com.br/v1/cep/'.$cep;
                    	$jsonCEP = json_decode( file_get_contents($hostAPI), false);
                    	$jsonCEP['localidade']  = $jsonCEP['cidade'];
                    	
                    	}
                    	
                    	
                    	
                    	
                    }*/

                    //$jsonCEP = json_decode( file_get_contents($hostAPI) );
                    //$jsonCEP['localidade']  = $jsonCEP['cidade'];

                    if ($jsonCEP['localidade'] != '') {

                        $queryAutoCEPCidade = $this->db->query("SELECT cidade.cid_id, cidade.est_id, cidade.cid_cep_geral FROM `tab_cidades` AS cidade INNER JOIN rel_clo_cid_contem ON (rel_clo_cid_contem.cid_id = cidade.cid_id) WHERE cidade.cid_nome = '" . addslashes(utf8_decode($jsonCEP['localidade'])) . "' and rel_clo_cid_contem.clo_id=" . $this->idParceiro);
                        $validaAutoCEPCidade = $this->db->resultadoArray($queryAutoCEPCidade);

                        if (intval($validaAutoCEPCidade['cid_id']) > 0) {

                            //if ( $_POST['autoinsertCEP'] == 'true' || boolval($_POST['autoinsertCEP']) == true ) {
                            $this->criaEndereco(intval($validaAutoCEPCidade['est_id']), intval($validaAutoCEPCidade['cid_id']), $cep, $jsonCEP['bairro'], $jsonCEP['logradouro']);
                            //}
                            $validCEP = true;
                        } else {
                            $error = "Cidade do CEP não aceita - <i><u>(" . $jsonCEP['localidade'] . " - " . substr($cep, 0, 5) . '-' . substr($cep, 5, 3) . ")</u></i>";
                        }
                    } else {
                        $error = "CEP invalido!";
                    }





                    if ($validCEP == false) {
                        $dados["id"] = 0;
                        $dados["msgError"] = $error;
                    } else {
                        $queryCidade = $this->db->query("SELECT cidade.cid_id, cidade.est_id, cidade.cid_cep_geral FROM `tab_cidades` AS cidade INNER JOIN rel_clo_cid_contem ON (rel_clo_cid_contem.cid_id = cidade.cid_id) WHERE cidade.cid_cep_geral = '" . str_replace('-', '', $cep) . "' and rel_clo_cid_contem.clo_id=" . $this->idParceiro);
                        $validaCidade = $this->db->resultadoArray($queryCidade);
                    }
                } else {
                    $cep = str_replace('-', '', $cep);
                }



                if (intval($validaCidade['cid_id']) > 0) {


                    $nmBairro = @substr($this->textConvertName($bairro), 0, 90);
                    $queryBairro = $this->db->query("SELECT bai_id FROM `tab_bairros` WHERE `bai_nome` = '" . addslashes($nmBairro) . "' and cid_id=" . $validaCidade['cid_id']);
                    $validaBairro = $this->db->resultadoArray($queryBairro);
                    if (intval($validaBairro['bai_id']) == 0) { // Não achou o bairro, entao vai inserir
                        $this->db->query("INSERT INTO tab_bairros SET bai_nome='" . addslashes($nmBairro) . "',bai_cep_geral=null,cid_id=" . $validaCidade['cid_id']);
                        $validaBairro['bai_id'] = $this->db->ultimoIdInserido();
                    }

                    if (intval($validaBairro['bai_id']) > 0) { // Agora tem o bairro
                        $nmLogradouro = @substr($this->textConvertName($logradouro), 0, 90);

                        $nmLogradouro =  @preg_replace('/^(r. |r |R. |R )/', 'Rua ', $nmLogradouro);
                        $nmLogradouro =  @preg_replace('/^(av. |av |Av. |Av |AV. |AV )/', 'Avenida ', $nmLogradouro);
                        $nmLogradouro =  @preg_replace('/^(Serv. |Serv |serv. |serv |SERV. |SERV )/', 'Servidão ', $nmLogradouro);

                        if (!preg_match("/^(Alameda|Aeroporto|Área|Avenida|Campo|Chácara|Colônia|Condomínio|Conjunto|Distrito|Esplanada|Estação|Estrada|Favela|Fazenda|Feira|Jardim|Ladeira|Lago|Lagoa|Largo|Loteamento|Morro|Núcleo|Parque|Passarela|Pátio|Praça|Quadra|Recanto|Residencial|Rua|Rodovia|Servidão|Setor|Sítio|Travessa|Trecho|Trevo|Vale|Vereda|Via|Viaduto|Viela|Vila) /", $nmLogradouro)) {

                            $nmLogradouro = 'Rua ' . $nmLogradouro;

                            //$dados["id"] = 0;
                            //$dados["msgError"] = "Logradouro inválido (deve iniciar com: Rua, Servidão, Avenida, Estrada, Rodovia, Alameda, Via, Travessa, Praça ou Largo)!";//.$nmLogradouro;
                            //$dados["msgError"] = "Logradouro inválido! (deve iniciar com: Alameda, Aeroporto, Área, Avenida, Campo, Chácara, Colônia, Condomínio, Conjunto, Distrito, Esplanada, Estação, Estrada, Favela, Fazenda, Feira, Jardim, Ladeira, Lago, Lagoa, Largo, Loteamento, Morro, Núcleo, Parque, Passarela, Pátio, Praça, Quadra, Recanto, Residencial, Rua, Rodovia, Servidão, Setor, Sítio, Travessa, Trecho, Trevo, Vale, Vereda, Via, Viaduto, Viela ou Vila)";//.$nmLogradouro;

                        }

                        //else {

                        $queryLogradouro = $this->db->query("SELECT log_id FROM `tab_logradouros` WHERE `log_nome` = '" . addslashes($nmLogradouro) . "' and bai_id=" . $validaBairro['bai_id']);
                        $validaLogradouro = $this->db->resultadoArray($queryLogradouro);
                        if (intval($validaLogradouro['log_id']) == 0) { // Não achou o logradoudo, entao vai inserir

                            $this->db->query("INSERT INTO tab_logradouros SET log_nome='" . addslashes($nmLogradouro) . "',log_observacao='',bai_id=" . $validaBairro['bai_id']);
                            $validaLogradouro['log_id'] = $this->db->ultimoIdInserido();
                        }

                        if (intval($validaLogradouro['log_id']) > 0) { // Agora tem o logradoudo

                            $queryCeps = $this->db->query("SELECT cep_id,log_id,bai_id,cid_id,est_id,cep_exportacao,cep_zona FROM `tab_ceps` WHERE  `cid_id` = '" . $validaCidade['cid_id'] . "' and log_id=" . $validaLogradouro['log_id']);
                            $validaCeps = $this->db->resultadoArray($queryCeps);
                            if (intval($validaCeps['cep_id']) == 0) { // CEP não encontrado, vai inserir

                                $cep_id = sprintf("%08.8s", preg_replace("/[^0-9]/", "", $cep));
                                if (substr($cep_id, 5, 3) == "000") {
                                    $prefixo_cep = substr($cep_id, 0, 5);
                                } else if (substr($cep_id, 6, 2) == "00") {
                                    $prefixo_cep = substr($cep_id, 0, 6);
                                }
                                $newCep = $this->buscaCEPDisponivel($prefixo_cep);

                                $this->db->query("INSERT INTO tab_ceps SET cep_id='" . $newCep . "', log_id='" . $validaLogradouro['log_id'] . "',bai_id='" . $validaBairro['bai_id'] . "',cid_id='" . $validaCidade['cid_id'] . "',est_id='" . $validaCidade['est_id'] . "',cep_exportacao='" . $cep . "',cep_zona=''");
                                $newCepId = $this->db->ultimoIdInserido();
                                if (intval($newCepId) > 0) {
                                    unset($endereco);
                                    $endereco = $this->arrayEnderecoCEP($newCep, $this->idParceiro);
                                    $dados = array();
                                    $dados["id"] = $endereco["cep_id"];
                                    $dados["log_id"] = $endereco["log_id"];
                                    $dados["logradouro"] = $this->textCharsetOutput($endereco["logradouro"]);
                                    $dados["bai_id"] = $endereco["bai_id"];
                                    $dados["bairro"] = $this->textCharsetOutput($endereco["bairro"]);
                                    $dados["cid_id"] = $endereco["cid_id"];
                                    $dados["cidade"] = $this->textCharsetOutput($endereco["cidade"]);
                                    $dados["est_id"] = $endereco["est_id"];
                                    $dados["estado_sigla"] = $endereco["estado_sigla"];
                                    $dados["cep_exportacao"] = $endereco["cep_exportacao"];
                                    $dados["cep_zona"] = $endereco["cep_zona"];


                                    //$dados["msgError"] = "Falha CEP calculado!";
                                } else {
                                    $dados["id"] = 0;
                                    $dados["msgError"] = "CEP não aceito!"; //"Falha CEP calculado!";
                                }
                            } else {
                                $endereco = $this->arrayEnderecoCEP($validaCeps['cep_id'], $this->idParceiro);
                                $dados = array();
                                $dados["id"] = $endereco["cep_id"];
                                $dados["log_id"] = $endereco["log_id"];
                                $dados["logradouro"] = $this->textCharsetOutput($endereco["logradouro"]);
                                $dados["bai_id"] = $endereco["bai_id"];
                                $dados["bairro"] = $this->textCharsetOutput($endereco["bairro"]);
                                $dados["cid_id"] = $endereco["cid_id"];
                                $dados["cidade"] = $this->textCharsetOutput($endereco["cidade"]);
                                $dados["est_id"] = $endereco["est_id"];
                                $dados["estado_sigla"] = $endereco["estado_sigla"];
                                $dados["cep_exportacao"] = $endereco["cep_exportacao"];
                                $dados["cep_zona"] = $endereco["cep_zona"];
                            }
                        } else {
                            $dados["id"] = 0;
                            $dados["msgError"] = "CEP não tem logradouro válido!"; //"Falha CEP calculado!";
                        }
                        //}
                    } else {
                        $dados["id"] = 0;
                        $dados["msgError"] = "CEP não tem bairro válido!"; //"Falha CEP calculado!";
                    }
                } else {
                    $dados["id"] = 0;
                    //$dados["msgError"] = "Não identificado a cidade do CEP!";//"Falha CEP calculado!";

                    // Verifica se pode ser inserido




                    /*$nmBairro = $this->textConvertName( $bairro );
                    $queryBairro = $this->db->query("SELECT bai_id FROM `tab_bairros` WHERE `bai_nome` = '".$nmBairro."' and cid_id=".$validaCidade['cid_id'] );
                    $validaBairro = $this->db->resultadoArray($queryBairro);
                    if (intval($validaBairro['bai_id']) == 0) { // Não achou o bairro, entao vai inserir
                        $this->db->query("INSERT INTO tab_bairros SET bai_nome='".$nmBairro."',bai_cep_geral=null,cid_id=".$validaCidade['cid_id']);
                        $validaBairro['bai_id'] = $this->db->ultimoIdInserido();
                    }*/
                }
            }
        } else {

            $dados = array();
            $dados["id"] = $endereco["cep_id"];
            $dados["log_id"] = $endereco["log_id"];
            $dados["logradouro"] = $this->textCharsetOutput($endereco["logradouro"]);
            $dados["bai_id"] = $endereco["bai_id"];
            $dados["bairro"] = $this->textCharsetOutput($endereco["bairro"]);
            $dados["cid_id"] = $endereco["cid_id"];
            $dados["cidade"] = $this->textCharsetOutput($endereco["cidade"]);
            $dados["est_id"] = $endereco["est_id"];
            $dados["estado_sigla"] = $endereco["estado_sigla"];
            $dados["cep_exportacao"] = $endereco["cep_exportacao"];
            $dados["cep_zona"] = $endereco["cep_zona"];

            //$dados["msgError"] = "Falha CEP calculado!";

        }
        return $dados;
    }






    /**
     * <b>getParametros:</b> Função que busca automaticamente os parametros enviados na url e as
     * converto em array para o objeto parametrosURL
     */
    function getParametros()
    {
        $url  = explode('/',  $_GET['q']);
        for ($i = 0; $i < count($url); $i++) $this->parametrosURL[] = $url[$i];
        return $url;
    }



    /**
     * <b>verificaClasse:</b> Função que verifica se a classe existe, 
     * caso existir será dado um 'require', inicializa e busca retorno dela.
     */
    function verificaClasse()
    {
        $this->getParametros();
        if (file_exists('classes/' . $this->parametrosURL[0] . 'class.php')) {
            require_once 'classes/' . $this->parametrosURL[0] . 'class.php';
            $returnClasse = eval("return new " . $this->parametrosURL[0] . "class(" . $this->idParceiro . ", '" . $this->nameParceiro . "');");
            $this->dadosClasse = $returnClasse->returnClass;
        } else {
            $this->dadosClasse['error'] = 'Classe \'' . $this->parametrosURL[0] . '\' inexistente!';
        }

        $permitidos = ['inserir', 'alterar', 'alterarusuario', 'bloqueio', 'desbloqueio', 'mac', 'cancelar'];
        $acao = $this->parametrosURL[1];
        if (isset($acao) && in_array($acao, $permitidos, true)) {
            $this->setHistory($_POST['login'], $this->getParametros(), $_POST['requestIp'], $_POST);
        }
    }



    /**
     * <b>validaMetodoRequisicao:</b> Função que verifica se o método usado na requisião é válido, 
     * pois os dados de autenticação devem ser enviado via POST.
     * 
     * @return boolean
     */
    function validaMetodoRequisicao()
    {
        if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
            $this->dadosClasse['error'] = 'Método de requisição inválido!';
            return false;
        } else return true;
    }


    function getParametrosURL()
    {
        return $this->parametrosURL;
    }


    /**
     * <b>textCharset:</b> Função para codificar texto do mysql para UTF-8, conforme estiver no DB.
     * Caso precise mudar a codificação, basta mudar aqui
     *
     * @return string
     */
    function textCharsetOutput($var)
    {
        return utf8_encode($var);
        //return $var;
    }
    function textCharsetInput($var)
    {
        return utf8_decode($var);
    }

    function textConvertName($str, $delimiters = array(
        "",
        " ",
        "-",
        ".",
        "'",
        "O'",
        "Mc",
    ), $exceptions = array(
        "de",
        "do",
        "da",
        "dos",
        "das",
    ))
    {
        $result = '';

        foreach ($delimiters as $delimiter) {
            # If string has a delimiter
            if (strstr($str, $delimiter)) {

                $ucfirst = array();
                # Apply ucfirst to every word
                foreach (explode($delimiter, mb_strtolower($str)) as $word) {
                    $word = mb_convert_case($word, MB_CASE_TITLE);

                    # Working with exceptions
                    if (in_array(mb_strtoupper($word), $exceptions)) {
                        $word = mb_strtoupper($word);
                    } elseif (in_array(mb_strtolower($word), $exceptions)) {
                        $word = mb_strtolower($word);
                    } elseif (preg_match('/^M{0,4}(CM|CD|D?C{0,3})(XC|XL|L?X{0,3})(IX|IV|V?I{0,3})$/', mb_strtoupper($word))) {
                        # Is roman numerals? # http://stackoverflow.com/a/267405/437459
                        $word = mb_strtoupper($word);
                    }

                    $ucfirst[] = $word;
                }

                # string's first character uppercased
                $result = implode($delimiter, $ucfirst);
            }
        }
        if ($result == '') $result = ucfirst($str);

        return $result;
    }

    function getRequisicao($url)
    {


        $ch = curl_init(); // create cURL handle (ch)
        if (!$ch) {
            die("Couldn't initialize a cURL handle");
        }
        // set some cURL options
        $ret = curl_setopt($ch, CURLOPT_URL,            $url);
        $ret = curl_setopt($ch, CURLOPT_HEADER,         1);
        $ret = curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
        $ret = curl_setopt($ch, CURLOPT_TIMEOUT,        30);

        // execute
        $ret = curl_exec($ch);

        if (empty($ret)) {
            // some kind of an error happened
            die(curl_error($ch));
            curl_close($ch); // close cURL handler
        } else {
            $info = curl_getinfo($ch);
            curl_close($ch); // close cURL handler

            if (empty($info['http_code'])) {
                die("No HTTP code was returned");
            } else {
                // load the HTTP codes
                $http_codes = parse_ini_file("path/to/the/ini/file/I/pasted/above");

                // echo results
                echo "The server responded: <br />";
                echo $info['http_code'] . " " . $http_codes[$info['http_code']];
            }
        }


        return '-'; //$return;
    }


    function formataCPF($cpf)
    {

        $cpf = sprintf("%011s", preg_replace("/[^0-9]/", "", $cpf));

        return substr($cpf, 0, 3) . "." . substr($cpf, 3, 3) . "." . substr($cpf, 6, 3) . "-" . substr($cpf, 9, 2);
    }

    function limpaCNPJAlfanumerico($cnpj)
    {

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

    function valorCNPJMod11($caractere)
    {
        return ord($caractere) - 48;
    }

    function formataCNPJ($cnpj)
    {

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

    function validaCPF($cpf)
    {

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

    function validaCNPJ2($cnpj)
    {
        return $this->validaCNPJ($cnpj);
    }

    function validaCNPJ($cnpj)
    {

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


    /**
     * Ao destruir a classe, já retorna os dados obtidos como resposta da API
     */
    function __destruct()
    {
        //if ($this->dadosClasse['retorno_status'] === false) http_response_code(404);
        //else                                        http_response_code(200);
        $json = json_encode($this->dadosClasse);
        if (json_last_error() != 0) {
            $erro['error'] = json_last_error_msg();
            $erro['retorno_status'] = false;

            $this->setResultHistory(json_encode($erro));

            echo json_encode($erro);
        } else {

            $this->setResultHistory($json);

            echo $json;
        }


        //echo json_encode( $this->dadosClasse);
        //echo json_last_error();

    }
}
