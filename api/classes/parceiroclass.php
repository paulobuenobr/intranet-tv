<?php


class parceiroclass extends loadclass {
    
    public $returnClass = array();
    public $urlClass = array();
    public $idParceiro = null;
    
    function __construct($idParceiro='', $nameParceiro='') {
        $this->returnClass['retorno_status'] = false;
        $this->idParceiro = $idParceiro;
        $this->nameParceiro = $nameParceiro;
        
        $this->urlClass = $this->getParametros();
        if ( trim( $this->urlClass[1]) == '') {
            $this->returnClass['error'] = 'Nenhum método definido!';
        }
        else {
            if ( method_exists(parceiroclass, $this->urlClass[1]) ) { eval(" parceiroclass::".$this->urlClass[1]."(); "); }
            else    { $this->returnClass['error'] = "Método2 '".$this->urlClass[1]."' inválido!"; }
        }
        
        return $this->returnClass;
    }
    
    
    function autenticacao() {
        $this->returnClass['retorno_status'] = false;
        if ($this->idParceiro > 0) {
            $this->returnClass['retorno_status'] = true;
            $this->returnClass['id_parceiro'] = $this->idParceiro;
            $this->returnClass['name_parceiro'] = urlencode( $this->nameParceiro );
        }
        else {
            $this->returnClass['retorno_status'] = false;
            $this->returnClass['error'] = "Autenticação inválida!";
        }
        
    }
    
    
    function verificaCidadeParceiro($cep = '') {
        
        if ( $cep != '' ) {
            $cep = str_replace('-', '', $cep);
        }
        else {
            $cep = str_replace('-', '', $_POST['cep']);
        }
        
        $this->conectaDB();
        
        $queryBairro = $this->db->query("SELECT cid_id FROM `tab_bairros` AS bairros WHERE bairros.bai_cep_geral = '".str_replace('-', '', $cep)."'" );
        $validaBairro = $this->db->resultadoArray($queryBairro);
        
        if (intval($validaBairro['cid_id']) > 0) {
        	
        	$queryCidade = $this->db->query("SELECT cidade.cid_id, cidade.est_id, cidade.cid_cep_geral, cidade.cid_nome FROM `tab_cidades` AS cidade INNER JOIN rel_clo_cid_contem ON (rel_clo_cid_contem.cid_id = cidade.cid_id) WHERE cidade.cid_id = '".intval($validaBairro['cid_id'])."' and rel_clo_cid_contem.clo_id=".$this->idParceiro );
        	$validaCidade = $this->db->resultadoArray($queryCidade);
        	
        }
        else {
        	
        	$queryCidade = $this->db->query("SELECT cidade.cid_id, cidade.est_id, cidade.cid_cep_geral, cidade.cid_nome FROM `tab_cidades` AS cidade INNER JOIN rel_clo_cid_contem ON (rel_clo_cid_contem.cid_id = cidade.cid_id) WHERE cidade.cid_cep_geral = '".str_replace('-', '', $cep)."' and rel_clo_cid_contem.clo_id=".$this->idParceiro );
        	$validaCidade = $this->db->resultadoArray($queryCidade);
        	
        }
        
        $nmCidade = '';
        if (intval($validaCidade['cid_id']) == 0) {
        
		$jsonCEP = $this->buscaCEP(substr($cep,0,5).'-'.substr($cep,5));
	        //$hostAPI = 'https://viacep.com.br/ws/'.$cep.'/json/';// 'https://api.postmon.com.br/v1/cep/'.$cep;
	        //$hostAPI = 'https://api.postmon.com.br/v1/cep/'.$cep;
	        
	        //$jsonCEP = json_decode( file_get_contents($hostAPI), TRUE );
	        //$jsonCEP['localidade']  = $jsonCEP['cidade'];
	        
	        $nmCidade = utf8_decode($jsonCEP['localidade']);
        
        }
        else {
        	
        	$nmCidade = $validaCidade['cid_nome'];
        	
        }
        
        
        if ( $nmCidade != '' ) {
            
            
            
            $queryAutoCEPCidade = $this->db->query("SELECT cidade.cid_id, cidade.est_id, cidade.cid_cep_geral FROM `tab_cidades` AS cidade INNER JOIN rel_clo_cid_contem ON (rel_clo_cid_contem.cid_id = cidade.cid_id) WHERE cidade.cid_nome = '".$nmCidade."' and rel_clo_cid_contem.clo_id=".$this->idParceiro );
            $validaAutoCEPCidade = $this->db->resultadoArray($queryAutoCEPCidade);
            
            if (intval($validaAutoCEPCidade['cid_id']) > 0) {
                
                if ( $_POST['autoinsertCEP'] == 'true' || boolval($_POST['autoinsertCEP']) == true ) {
                    $this->criaEndereco(intval($validaAutoCEPCidade['est_id']), intval($validaAutoCEPCidade['cid_id']), $cep, $jsonCEP['bairro'], $jsonCEP['logradouro']  );
                }
                
            }
            else {
                $this->returnClass['retorno_status'] = false;
                $this->returnClass['error'] = "Cidade do CEP não aceita - <i><u>(".$nmCidade['localidade']." - ".substr($cep, 0, 5) . '-' . substr($cep, 5, 3).")</u></i>";
            }
        }
        else {
            $this->returnClass['retorno_status'] = false;
            $this->returnClass['error'] = "CEP invalido <i><u>(".substr($cep, 0, 5) . '-' . substr($cep, 5, 3).")</u></i>!";
        }
        
    }
    
    
    
   
    
    
    function verificacaocidade() {
        $Format = new formats();
        
        if ( trim($_POST['cep']) == '')        { $this->returnClass['error'] = 'CEP do cliente não informado!'; }
        else {
            if ($Format->ValidCEP( trim($_POST['cep'])  ) == false) { $this->returnClass['error'] = 'CEP do cliente em formato inválido!'; }
            else {
                $dadosCEP = $this->getEnderecoByCEP( trim($_POST['cep']), trim($_POST['logradouro']), trim($_POST['bairro']) );
                if (intval($dadosCEP['id']) == 0) {
                    if ( isset($dadosCEP['msgError']) ) $this->returnClass['error'] = $dadosCEP['msgError'];
                    else                                $this->returnClass['error'] = 'CEP do cliente inválido!';
                }
                else {
                    if ($dadosCEP['logradouro'] == '') {
                        if ( trim($_POST['logradouro']) == '')        { $this->returnClass['error'] = 'Logradouro não informado!'; }
                    }
                    if ($dadosCEP['bairro'] == '') {
                        if ( trim($_POST['bairro']) == '')        { $this->returnClass['error'] = 'Bairro não informado!'; }
                    }
                }
            }
        }
        
        if ( !isset($this->returnClass['error']) || $this->returnClass['error'] != '' ) {
            $this->returnClass['retorno_status'] = true;
            //unset($this->returnClass['error']);
        }
        else {
            $this->returnClass['retorno_status'] = false;            
        }
    }
    
    
    function emailcontratoativo() {
        
        define('DIR_RAIZ', '../../');
        define('DIR_GLOBAL', '../../');
        
        $this->conectaDB();
        
        require_once '../_modulos/clientes_iptv/clientes_iptv.class.inc.php';
        $intranetClienteIPTV = new clientes_iptv($this->db, null, false);
        
        $idClienteIPTV = $intranetClienteIPTV->buscaIdPorUsuario($_POST["email"]);
        if ($idClienteIPTV != null) {
            $query = $this->db->query("SELECT cli_id, clo_id, cli_nome, cli_cpf_cnpj FROM tab_clientes WHERE tab_clientes.cli_id = '".$idClienteIPTV."'");
            $dadosCliente = $this->db->resultadoArray($query);

            $this->returnClass['retorno_status'] = true;
            $this->returnClass['idParceiro'] = $dadosCliente['clo_id'];
            $this->returnClass['cliente_id'] = $dadosCliente['cli_id'];
            $this->returnClass['cliente_nome'] = $dadosCliente['cli_nome'];
            $this->returnClass['cliente_doc'] = base64_encode( strrev( $dadosCliente['cli_cpf_cnpj'] ) );
        }
        else {
            $this->returnClass['retorno_status'] = false;
        }
        //return $return;
    }
    
    
    function servidoremail() {
        
	$this->conectaDB();

        if ( $this->idParceiro == 0 || $this->idParceiro == 1 ) { // Somente para Ole Telecom / Ol�TV
            
            $queryServer = $this->db->query("SELECT usuario, senha, host, porta FROM tab_email_smtp WHERE tipo='boleto' and ativo='1'");
            $dadosServer = $this->db->resultadoArray($queryServer);
            if (isset($dadosServer['host'])) {
                            
                $this->returnClass['retorno_status'] = true;
                $this->returnClass['host'] = $dadosServer['host'];
                $this->returnClass['porta'] = $dadosServer['porta'];
                $this->returnClass['usuario'] = $dadosServer['usuario'];
                $this->returnClass['senha'] = $dadosServer['senha'];
		$this->returnClass['sender'] = $dadosServer['usuario'];
                $this->returnClass['reply'] = $dadosServer['usuario'];
                
            }
        
        }
        else {
            
            $this->returnClass['error'] = "M�todo não dispon�vel!";
            return;
            
        }
        
    }
    
    
    function __destruct() {
        
    }
    
}