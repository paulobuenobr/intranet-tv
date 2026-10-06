<?php

class clientesclass extends loadclass {
    
    public $returnClass = array();
    public $urlClass = array();
    public $idParceiro = null;
    
    public $tiposComplemento = array("a" => "Apartamento", "l" => "Loja", "s" => "Sala", "c" => "Casa", "o" => "Outro");
    
    function __construct($idParceiro='') {
        $this->returnClass['retorno_status'] = false;
        $this->idParceiro = $idParceiro;
        
        $this->urlClass = $this->getParametros();
        if ( trim( $this->urlClass[1]) == '') {
            $this->returnClass['error'] = 'Nenhum método definido!';
        }
        else {
            if ( @method_exists(clientesclass, $this->urlClass[1]) ) { eval(" clientesclass::".$this->urlClass[1]."(); "); }
            else    { $this->returnClass['error'] = "Método '".$this->urlClass[1]."' inválido!"; }
        }
        return $this->returnClass;
    }
    
    
    
    function listar() {
        $this->conectaDB();
        $query = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj, DATE_FORMAT(cli_nascimento, '%d/%m/%Y') AS nascimento, DATE_FORMAT(tab_clientes.cli_data_cadastro, '%d/%m/%Y') AS data_cadastro FROM tab_clientes WHERE clo_id = '".$this->idParceiro."'");
        
        $list = array();
        $k = 0;
        while (($v = $this->db->resultadoArray($query)) != false) {
            $list[$k]['id'] = $v['cli_id'];
            $list[$k]['nome'] = $this->textCharsetOutput( $v['cli_nome'] );
            $list[$k]['cpf_cnpj'] = $v['cli_cpf_cnpj'];
            $list[$k]['data_nascimento'] = $v['nascimento'];
            $list[$k]['data_cadastro'] = $v['data_cadastro'];
            $k++;
        }
        
        if (count($list) > 0) {
            $this->returnClass['retorno_status'] = true;
            $this->returnClass['lista'] = $list;
        }
        else {
            $this->returnClass['retorno_status'] = false;
            $this->returnClass['error'] = 'Nenhum cliente foi encontrado!';
        }
    }
    
    function buscacpfcnpj() {
        // Verifica valor da pesquisa

        if ( !empty($this->urlClass[3]) ) {
            $this->urlClass[2] .= '/'.$this->urlClass[3];
        }

        if ( $this->urlClass[2] == '048.010.891-94' ) {
            echo $this->validaCPF($this->urlClass[2]).' - '.$this->formataCPF($this->urlClass[2]);
        }

        if ( $this->validaCPF($this->urlClass[2]) ) {
            $this->urlClass[2] = $this->formataCPF($this->urlClass[2]);
        }
        else if ( $this->validaCNPJ($this->urlClass[2]) ) {
            $this->urlClass[2] = $this->formataCNPJ($this->urlClass[2]);
        }
        else {
            $this->returnClass['error'] = "CPF/CNPJ invalido!";
            return;
        }



        if (trim(@$this->urlClass[2]) === '' || empty($this->urlClass[2])) {
            $this->returnClass['error'] = "CPF/CNPJ n�o informado!";
            return;
        }
        
        $cpfcnpj = trim(@$this->urlClass[2]);
        //if (strlen($cpfcnpj) < 14) $cpfcnpj .= '/'.trim(@$this->urlClass[3]);
        
        $this->conectaDB();
        $query = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj, DATE_FORMAT(cli_nascimento, '%d/%m/%Y') AS nascimento, DATE_FORMAT(tab_clientes.cli_data_cadastro, '%d/%m/%Y') AS data_cadastro FROM tab_clientes WHERE cli_cpf_cnpj='".$cpfcnpj."' and  clo_id = '".$this->idParceiro."'");
        
        $list = array();
        $k = 0;
        while (($v = $this->db->resultadoArray($query)) != false) {
            $list[$k]['id'] = $v['cli_id'];
            $list[$k]['nome'] = str_replace('\'','',$this->textCharsetOutput($v['cli_nome']));
            $list[$k]['cpf_cnpj'] = $v['cli_cpf_cnpj'];
            $list[$k]['data_nascimento'] = $v['nascimento'];
            $list[$k]['data_cadastro'] = $v['data_cadastro'];
            $k++;
        }
                
        if (count($list) > 0) {
            $this->returnClass['retorno_status'] = true;
            $this->returnClass['lista'] = $list;
        }
        else {
            $this->returnClass['retorno_status'] = false;
            $this->returnClass['error'] = 'Nenhum cliente foi encontrado!';
        }
    }
    
    
    /**
     * <b>buscadados:</b> Função para buscar os dados cadastrais de um cliente especifico, de acordo com
     * o código de referencia passado via parâmetro 
     */
    function buscadados() {
        // Verifica valor da pesquisa
        
        //echo 'passsouuu ('.intval(@$this->urlClass[2]).')';
        //if (trim(@$this->urlClass[2]) === '' || empty($this->urlClass[2])) {
        if ( intval(@$this->urlClass[2]) == 0) {
            $this->returnClass['error'] = "ID do cliente não informado ou inválido!";
            return;
        }
        
        $this->executaBuscaDados();
    }

    
    /**
     * <b>executaBusca:</b> Função que executa a busca e monta o resultado, após ter passado pelo processo de
     * validação do método/função 'buscar'. 
     */
    function executaBuscaDados() {
        $this->conectaDB();
        $query = $this->db->query("
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
                tab_clientes_tipos.clt_id AS tipo_id,
                tab_clientes_tipos.clt_nome AS tipo_nome,
                tab_clientes_status.cls_id AS status_id,
                tab_clientes_status.cls_nome AS status_nome
            FROM
                tab_clientes
                LEFT OUTER JOIN tab_clientes_tipos ON (tab_clientes.clt_id = tab_clientes_tipos.clt_id)
                LEFT OUTER JOIN tab_clientes_situacoes ON (tab_clientes.clu_id = tab_clientes_situacoes.clu_id)
                INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
            WHERE
				tab_clientes.clo_id = '".$this->idParceiro."' AND tab_clientes.cli_id = '".intval($this->urlClass[2])."'");
        
        $dados = $this->db->resultadoArray($query);
        if (isset($dados['id'])) {
            $this->returnClass['retorno_status'] = true;
            $this->returnClass['dados']['id'] = $dados['id'];
            $this->returnClass['dados']['nome'] = $this->textCharsetOutput( $dados['nome'] );
            $this->returnClass['dados']['tipo_pessoa'] = $this->textCharsetOutput(  $dados['tipo_nome'] );
            $this->returnClass['dados']['nome_fantasia'] = $this->textCharsetOutput( $dados['nome_fantasia'] );
            $this->returnClass['dados']['cpf_cnpj'] = $dados['cpf_cnpj'];
            $this->returnClass['dados']['inscricao_estadual'] = $dados['inscricao_estadual'];
            $this->returnClass['dados']['data_nascimento'] = $dados['nascimento'];
            
            $dadosCEP = $this->getEnderecoByCEP($dados["cep"],'','');
            
            $this->returnClass['dados']['endereco']['logradouro'] = $dadosCEP['logradouro'];
            $this->returnClass['dados']['endereco']['numero'] = $dados["numero"];
            $this->returnClass['dados']['endereco']['bairro'] = $dadosCEP['bairro'];
            $this->returnClass['dados']['endereco']['cidade'] = $dadosCEP['cidade'];
            $this->returnClass['dados']['endereco']['estado_sigla'] = $dadosCEP['estado_sigla'];
            
            if ($dados["complemento"]) {
                $this->returnClass['dados']['endereco']['complemento']['tipo'] = TIPOS_COMPLEMENTO[$dados["complemento"][0]];
                $this->returnClass['dados']['endereco']['complemento']['valor'] = substr($dados["complemento"], 1, strlen($dados["complemento"]));
            }
            if ($dados["bloco"]) {
                $this->returnClass['dados']['endereco']['bloco'] = $this->getEnderecoBloco( $dados['bloco'] );
                $this->returnClass['dados']['endereco']['condominio'] = $this->getEnderecoCondominio( $bloco["condominio"] );
                $dados['cep'] = $this->returnClass['dados']['endereco']['bloco']['cep'] ? $this->returnClass['dados']['endereco']['bloco']['cep'] : $this->returnClass['dados']['endereco']['condominio']['cep'];
            } 
            elseif ($dados["condominio"]) {
                $this->returnClass['dados']['endereco']['condominio'] = $this->getEnderecoCondominio( $bloco["condominio"] );
                $dados['cep'] = $this->returnClass['dados']['endereco']['condominio']['cep'];
            } 
            
            $this->returnClass['dados']['endereco']['ponto_referencia'] = $this->textCharsetOutput( $dados["ponto_referencia"] );
            $this->returnClass['dados']['endereco']['cep'] = substr($dados["cep"], 0, 5) . "-" . substr($dados["cep"], 5, 3);
            
            $this->returnClass['dados']['contato'] = $this->textCharsetOutput( $dados['contato'] );
            $this->returnClass['dados']['telefones'] = $this->getListTelefones( $dados['id'] );
            $this->returnClass['dados']['emails'] = $this->getListEmails( $dados['id'] );
            
            $this->returnClass['dados']['dia_vencimento'] = $dados['cobranca_vencimento'];
            $enderecoCobranca = $this->getEnderecoCobranca($dados['id']);
            if ($enderecoCobranca == false ) {
                $this->returnClass['dados']['endereco_cobranca'] = $this->returnClass['dados']['endereco'];
            }
            else {
                $this->returnClass['dados']['endereco_cobranca'] = $enderecoCobranca;
            }
            $this->returnClass['dados']['status'] = $dados['status_nome'];
        }
        else {
            $this->returnClass['error'] = "Nenhum cliente localizado com o ID '".$this->urlClass[2]."'!";
        }
        
    }
    
    function getListTelefones($id_cliente, $show_id = false) {
        $this->conectaDB();
        $query = $this->db->query("
            SELECT
                tab_clientes_contatos_telefones.ccl_id AS ccl_id,                
                ccl_ramal AS ramal,
                ccl_ddd AS ddd,
                ccl_numero AS numero,
                tab_clientes_contatos_telefones_tipos.ccf_nome AS ccf_nome
            FROM
                tab_clientes_contatos_telefones
                INNER JOIN tab_clientes_contatos_telefones_tipos ON (tab_clientes_contatos_telefones.ccf_id = tab_clientes_contatos_telefones_tipos.ccf_id)
            WHERE
                tab_clientes_contatos_telefones.cli_id = ".$id_cliente." AND
				tab_clientes_contatos_telefones.ccf_id > 0");
        
        $list = array();
        $k = 0;
        while (($v = $this->db->resultadoArray($query)) != false) {
            if ($show_id) $list[$k]['id'] = $v['ccl_id'];
            $list[$k]['ddd'] = $v['ddd'];
            $list[$k]['numero'] = $v['numero'];
            $list[$k]['ramal'] = $v['ramal'];
            $list[$k]['tipo'] = $v['ccf_nome'];
            $k++;
        }
        return $list;
    }
    
    function getListEmails($id_cliente, $show_id = false) {
        $this->conectaDB();
        $query = $this->db->query("
            SELECT
                tab_clientes_contatos_emails.ccm_id AS ccm_id,
                CONCAT(ccm_usuario, '@', ccm_dominio) AS email, 
                tab_clientes_contatos_emails_tipos.cca_nome AS tipo
            FROM
                tab_clientes_contatos_emails
                INNER JOIN tab_clientes_contatos_emails_tipos ON (tab_clientes_contatos_emails.cca_id = tab_clientes_contatos_emails_tipos.cca_id)
            WHERE
				cli_id = $id_cliente");
        
        $list = array();
        $k = 0;
        while (($v = $this->db->resultadoArray($query)) != false) {
            if ($show_id) $list[$k]['id'] = $v['ccm_id'];
            $list[$k]['email'] = $v['email'];
            $list[$k]['tipo'] = $v['tipo'];
            $k++;
        }
        return $list;
    }
    
    
    function arrayEnderecoCEP($idCep, $idParceiro) {
        $this->conectaDB();
        
        //echo 'Pesquisando CEP do parceiro';
        
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
                    tab_ceps.cep_id = '".str_replace('-', '', $idCep)."' AND
                                tab_cidades.cid_id IN (
                                    SELECT
                                        cid_id
                                    FROM
                                        rel_clo_cid_contem
                                    WHERE
                                        clo_id = ".$idParceiro."
                                )" );
        return $this->db->resultadoArray($query);
    }
    
    
    
    function getTpComplemento($vl) {
        $tiposComplemento = array();
        
        $tiposComplemento["a"] = "Apartamento";
        $tiposComplemento["l"] = "Loja";
        $tiposComplemento["s"] = "Sala";
        $tiposComplemento["c"] = "Casa";
        $tiposComplemento["o"] = "Outro";
        return $tiposComplemento[$vl];
    }
    
    function getEnderecoBloco($idBloco) {
        $this->conectaDB();
        $query = $this->db->query("
            SELECT
                cob_id AS id,
                cob_nome AS nome,
                con_id AS condominio,
                LPAD(cep_id, 8, '0') AS cep,
                cob_numero AS numero
            FROM
                tab_condominios_blocos
            WHERE
                cob_id = $idBloco");
        return $this->db->resultadoArray($query);
        
    }
    
    function getEnderecoCondominio($idCondominio) {
        $this->conectaDB();
        $query = $this->db->query("
            SELECT
                con_id AS id,
                con_nome AS nome,
                LPAD(cep_id, 8, '0') AS cep,
                con_numero AS numero,
                cos_id AS status
            FROM
                tab_condominios
            WHERE
                con_id = $idCondominio");
        return $this->db->resultadoArray($query);
    }
    
    function getEnderecoCobranca($id_cliente) {
        $this->conectaDB();
        $query = $this->db->query("
            SELECT
                cob_id AS bloco,
                LPAD(cep_id, 8, '0') AS cep,
                ccd_numero AS numero,
                ccd_complemento AS complemento
            FROM
                tab_clientes_cobranca_dados
            WHERE
                cli_id = $id_cliente");
        
        $dados = $this->db->resultadoArray($query);
        
        if ($dados != false) {
        
        //$list = array();
        
        $list = $this->getEnderecoByCEP($dados["cep"],'','');
        $list['numero'] = $dados["numero"];
        $list['cep'] = $dados['cep'];
        
        }
        else {
            $list = false;
        }
        
        return $list;
    }
    
    
    function inserir() {
        $parametroValid = $this->validaFormInsercao();
        if (count($parametroValid['error']) > 0) {
            $this->returnClass['error'] = $parametroValid['error'];
        }
        else {
            $this->salvaCliente( $_POST, $parametroValid['cep'], $parametroValid['cep_cobranca'] );
        }
        
        //
    }
    
    function alterar() {
        // Verifica se ID foi informado
        if (trim(@$this->urlClass[2]) === '' || empty($this->urlClass[2])) {
            $this->returnClass['error'] = "ID do cliente não informado!";
            return;
        }
        
        // Busca o Cliente do ID
        $this->conectaDB();
        $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".intval($this->urlClass[2]) ."' and clo_id = '".$this->idParceiro."'");
        $dadosCliente = $this->db->resultadoArray($queryCliente);
        
        if (!isset($dadosCliente['cli_id'])) {
            $this->returnClass['error'] = "ID do cliente não localizado!";
            return;
        }
        
        $parametroValid = $this->validaFormInsercao(true);
        if (count($parametroValid['error']) > 0) {
            $this->returnClass['error'] = $parametroValid['error'];
        }
        else {
            $_POST['cliente_id'] = intval($this->urlClass[2]);
            //print_r($_POST);
            
            
            $this->salvaCliente( $_POST, $parametroValid['cep'], $parametroValid['cep_cobranca'], true );
        }
        
    }
    
    
    
    function encodingText($text) {
        $cur_encoding = mb_detect_encoding($text) ;
        if($cur_encoding == "UTF-8" && mb_check_encoding($text,"UTF-8"))
            return utf8_decode( $this->trataCaracteres( $text) );
        else
            return $this->trataCaracteres( $text );
    }
    
    function trataCaracteres($text) {
        $saida = '';
        $text = mb_strtolower($text); // Converter o nome todo para minúsculo
        $text = explode(" ", $text); // Separa o nome por espaços
        for ($i=0; $i < count($text); $i++) {
            // Tratar cada palavra do nome
            if ($text[$i] == "de" or $text[$i] == "da" or $text[$i] == "e" or $text[$i] == "dos" or $text[$i] == "do") {
                $saida .= $text[$i].' '; // Se a palavra estiver dentro das complementares mostrar toda em minúsculo
            }else {
                $saida .= mb_strtoupper(mb_substr($text[$i], 0, 1)) . mb_substr($text[$i], 1).' ';//  ucfirst($text[$i]).' '; // Se for um nome, mostrar a primeira letra maiúscula
            }
        }
        return trim($saida);
    }
    
    function salvaCliente($dados, $dados_cep, $dados_cep_cobranca, $changed = false) {
        $this->conectaDB();
        $Format = new formats();
        
        if ( !isset($dados['cliente_id']) ) {
            $this->db->query("INSERT INTO tab_clientes SET usu_id = NULL");
            $cliente_id = $this->db->ultimoIdInserido();
            $statusCliente = "cls_id = '4',";
        }
        else {
            $cliente_id = intval($dados['cliente_id']);
            $statusCliente = "";
        }
        
        if ($cliente_id == 0) { 
            $this->returnClass['error']['cliente_id'] = 'ID do cliente não informado ou inválido!';
            return ;
        }
        
        // Formatando CPF/CNPJ
        if ( $dados["tipo_pessoa"] == '1') {
            $cpf = sprintf("%011s", preg_replace("/[^0-9]/", "", $dados["cpf_cnpj"]));
            $dados["cpf_cnpj"] = substr($cpf, 0, 3).".".substr($cpf, 3, 3).".".substr($cpf, 6, 3)."-".substr($cpf, 9, 2);
            $nomeFantasia = '';
        }
        elseif ( $dados["tipo_pessoa"] == '2') {
            $cnpj = sprintf("%014s", preg_replace("/[^0-9]/", "", $dados["cpf_cnpj"]));
            $dados["cpf_cnpj"] = substr($cnpj, 0, 2).".".substr($cnpj, 2, 3).".".substr($cnpj, 5, 3)."/".substr($cnpj, 8, 4)."-".substr($cnpj, 12, 2);
            $nomeFantasia = addslashes( utf8_decode( $dados["nome_fantasia"] ) );
        }
        
        if (!isset($dados["endereco_complemento_tipo"])  || $dados["endereco_complemento_tipo"] == '' ) {
            $dados["endereco_complemento_tipo"] = 'o';
        }
        elseif ($dados["endereco_complemento_tipo"] != 'a' && $dados["endereco_complemento_tipo"] != 'l' && $dados["endereco_complemento_tipo"] != 's' && $dados["endereco_complemento_tipo"] != 'c') {
            $dados["endereco_complemento_tipo"] = 'o';
        }
        
        
        // Verifica se CPF/CNPJ do cliente já foi cadastrado dentro do parceiro
        if ( $changed == false ) {
            if (  $this->idParceiro != 482 && $this->idParceiro != 483 && $this->idParceiro != 484 ) {
                $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_cpf_cnpj='".$dados["cpf_cnpj"]."' and clo_id = '".$this->idParceiro."'");
                $dadosCliente = $this->db->resultadoArray($queryCliente);
                if (isset($dadosCliente['cli_id'])) {
                    $this->returnClass['error'] = "CPF/CNPJ já cadastrado!";
                    return;
                }
            }
        }
        
        // tab_cobrancas_formatos e rel_cof_clo_disponivel
        $queryCobranca = $this->db->query("SELECT rel_cof.cof_id, f.cft_id FROM `rel_cof_clo_disponivel` AS rel_cof INNER JOIN tab_cobrancas_formatos as f ON (rel_cof.cof_id = f.cof_id) WHERE rel_cof.clo_id = ".$this->idParceiro." and cfs_id=1 order by cft_id DESC LIMIT 1");
        $dadosCobranca = $this->db->resultadoArray($queryCobranca);
        
        $idCobranca = @$dadosCobranca['cof_id'];
        if (!isset($dadosCobranca['cof_id'])) {
            $idCobranca = 1;
        }

        if ( strlen(trim($_POST['endereco_numero'])) > 7 ) {
            $this->returnClass['error'] = 'Número inválido!';
            return;
        }
        
        // Atualiza Dados Cliente
        // COBRANCA_FORMATO trocado para $idCobranca
        //// (addslashes($dados["nome_fantasia"])==''?"NULL":"'".addslashes( utf8_decode( $dados["nome_fantasia"] ) )."'").",
        $this->db->query("
					UPDATE
						tab_clientes
					SET
						cli_nome = '".str_replace("'","",( utf8_decode( $dados["nome"]) ))."',
						cli_data_cadastro = NOW(),
						cli_cpf_cnpj = '{$dados["cpf_cnpj"]}',
                        cli_nome_fantasia = '{$nomeFantasia}', 
						cli_inscricao_estadual = '".addslashes(@substr($dados["inscricao_estadual"],0,15))."',
						cli_nascimento = ".($dados["tipo_pessoa"]==1 && $this->idParceiro != 232 && $this->idParceiro != 482 && $this->idParceiro != 483 && $this->idParceiro != 484?"'".$Format->FormatDate($dados["data_nascimento"], true)."'":"NULL").",
                        cli_complemento = '{$dados["endereco_complemento_tipo"]}".addslashes( substr( utf8_decode( $dados["endereco_complemento_valor"] ), 0, 104 ) )."',
						cli_ponto_referencia = '".addslashes( substr( utf8_decode( $dados["endereco_ponto_referencia"] ), 0, 104 ) )."',
						cli_numero = '".intval($dados["endereco_numero"])."',
                        cli_contato = '".addslashes( utf8_decode( $dados["contato"] ) )."',
                        cli_ccd_vencimento = '{$dados["dia_vencimento"]}',
                        cep_id = '{$dados_cep["id"]}',
                        log_id = '{$dados_cep["log_id"]}',	
                        bai_id = '{$dados_cep["bai_id"]}',
                        cid_id = '{$dados_cep["cid_id"]}',
                        est_id = '{$dados_cep["est_id"]}',
                        cli_bloqueio_dias = '".DIAS_BLOQUEIO."',
						cli_comentarios = '',
                        cof_id = '".$idCobranca."', 
                        $statusCliente 
						clt_id = '{$dados["tipo_pessoa"]}',
						clo_id = '{$this->idParceiro}'
					WHERE
						cli_id = $cliente_id");
        //Alterado cls_id de 2 para 4 (status Ativo para Mailing)
        
        
        // Atualiza Telefones
        $listaDeEmails = array_filter($dados['telefone_ddd']);
        if($listaDeEmails) {
            $listPhones = $this->getListTelefones($cliente_id, true);
            foreach ($dados['telefone_ddd'] AS $k => $v) {
                if ( $dados["telefone_ddd"][$k] != '') {
                    $this->db->query("
                            INSERT INTO
                                    tab_clientes_contatos_telefones
                            SET
                                    cli_id = $cliente_id,
                                    ccl_ddd = '".trim($dados["telefone_ddd"][$k])."',
                                    ccl_numero = '".trim($dados["telefone_numero"][$k])."',
                                    ccl_ramal = '".trim($dados["telefone_ramal"][$k])."',
                                    ccf_id = {$dados["telefone_tipo"][$k]}");
                }
            }
            if (!empty($listPhones)) {
                foreach ($listPhones AS $vPhone) {
                    $this->db->query("DELETE FROM tab_clientes_contatos_telefones WHERE ccl_id='{$vPhone['id']}'");
                }
            }
        }
        
        // Atualiza E-mails
        $listEmails = $this->getListEmails($cliente_id, true);
        $listaDeEmails = array_filter($dados['email']);
        if($listaDeEmails) {
            foreach ($dados['email'] AS $km => $vm) {
                if ( $dados['email'][$km] != '') {
                    $tmpMail = explode('@', mb_strtolower($dados['email'][$km]) );
                    $this->db->query("
                            INSERT INTO
                                    tab_clientes_contatos_emails
                            SET
                                    cli_id = $cliente_id,
                                    ccm_usuario = '{$tmpMail[0]}',
                                    ccm_dominio = '{$tmpMail[1]}',
                                    cca_id = '2'");
                }
            }
            if (!empty($listEmails)) {
                foreach ($listEmails AS $vMail) {
                    $this->db->query("DELETE FROM tab_clientes_contatos_emails WHERE ccm_id='{$vMail['id']}'");
                }
            }
        }
        
        $this->returnClass['retorno_status'] = true;
        $this->returnClass['msg'] = (intval(@$dados['cliente_id']) > 0?"Cliente alterado com sucesso!":"Cliente inserido com sucesso!") ;
        $this->returnClass['id'] = $cliente_id;
        return;
    }
    
    
    function validarNome($nome) {
        $nome = trim($nome);
        
        return preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\'-]+(?:\s+[A-Za-zÀ-ÖØ-öø-ÿ\'-]+)+$/',$nome) === 1;
        
    }
    
    function validaFormInsercao($changed = false) {
        $parametroError = array();
        $Format = new formats();
        $this->conectaDB();
              
        // Validações
        if ( trim($_POST['nome']) == '')        { 
            $parametroError['nome'] = 'Nome do cliente não informado!'; 
        } elseif ( $Format->isUTF8( trim($_POST['nome']) ) == false ) { 
            $parametroError['nome'] = 'Nome do cliente em formato de codificação inválido! (obrigatório estar em UTF-8)'; 
        } elseif (!$this->validarNome($_POST['nome']) && $_POST['tipo_pessoa'] == '1') {
            $parametroError['nome'] = 'Nome do cliente inválido, verifique se não há caracteres numéricos ou caracteres especiais (exceto acentos, hÍfen e apóstrofos)!'; 
        }
        
        if ( $_POST['tipo_pessoa'] != '1' && $_POST['tipo_pessoa'] != '2' ) { $parametroError['tipo_pessoa'] = 'Tipo do cliente não informado ou inválido!'; }
        if ( $_POST['tipo_pessoa'] == '2') {
            if ( trim($_POST['nome_fantasia']) == '')  { $parametroError['nome_fantasia'] = 'Nome fantasia não informado!'; }
            elseif ( $Format->isUTF8( trim($_POST['nome_fantasia']) ) == false ) { $parametroError['nome_fantasia'] = 'Nome fantasia em formato de codificação inválido! (obrigatório estar em UTF-8)'; }
        }
        if ( trim($_POST['cpf_cnpj']) == '')        { $parametroError['cpf_cnpj'] = 'CPF/CNPJ não informado!'; }
        else {
            if ( $_POST['tipo_pessoa'] == '1') {
                if ( $Format->validaCPF( $_POST['cpf_cnpj'] ) == false ) { $parametroError['cpf_cnpj'] = 'CPF informado é inválido!'; }
            }
            elseif ( $_POST['tipo_pessoa'] == '2') {
                if ( $Format->validaCNPJ( $_POST['cpf_cnpj'] ) == false ) { $parametroError['cpf_cnpj'] = 'CNPJ informado é inválido!'; }
            }
            else {
                $parametroError['cpf_cnpj'] = 'CPF/CNPJ não informado ou inválido!';
            }
        }
        
        /***************/
        // Formatando CPF/CNPJ
        if ( $_POST["tipo_pessoa"] == '1') {
            $cpf = sprintf("%011s", preg_replace("/[^0-9]/", "", $_POST["cpf_cnpj"]));
            $_POST["cpf_cnpj"] = substr($cpf, 0, 3).".".substr($cpf, 3, 3).".".substr($cpf, 6, 3)."-".substr($cpf, 9, 2);
        }
        elseif ( $_POST["tipo_pessoa"] == '2') {
            $cnpj = sprintf("%014s", preg_replace("/[^0-9]/", "", $_POST["cpf_cnpj"]));
            $dados["cpf_cnpj"] = substr($cnpj, 0, 2).".".substr($cnpj, 2, 3).".".substr($cnpj, 5, 3)."/".substr($cnpj, 8, 4)."-".substr($cnpj, 12, 2);
        }
        
        // Verifica se cliente já foi cadastrado
        if ( $changed == false ) {
            if (  $this->idParceiro != 482 && $this->idParceiro != 483 && $this->idParceiro != 484 ) {
                $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_cpf_cnpj='".$_POST["cpf_cnpj"]."' and clo_id = '".$this->idParceiro."'");
                $dadosCliente = $this->db->resultadoArray($queryCliente);
                if (isset($dadosCliente['cli_id'])) {
                    $parametroError['cpf_cnpj'] = 'CPF/CNPJ já cadastrado!';
                }
            }
        }
        /********************/
        
        
        if ( $_POST['tipo_pessoa'] == '1') {
            // Exceção aberta para a Smartnet, desobrigando a data de nascimento
            if ( $this->idParceiro == 232 || $this->idParceiro == 482 || $this->idParceiro == 483 || $this->idParceiro == 484) { }
            else {
                if ( trim($_POST['data_nascimento']) == '')        { $parametroError['data_nascimento'] = 'Data de nascimento não informada!'; }
                else {
                    if ($Format->ValidDtBr($_POST['data_nascimento']) == false) { $parametroError['data_nascimento'] = 'Data de nascimento não informada ou inválida!'; }
                }
            }
        }
        
        if ( trim($_POST['endereco_cep']) == '')        { $parametroError['endereco_cep'] = 'CEP do cliente não informado!'; }
        else {
            if ($Format->ValidCEP( trim($_POST['endereco_cep'])  ) == false) { $parametroError['endereco_cep'] = 'CEP do cliente em formato inválido!'; }
            else {
                
                $dadosCEP = $this->getEnderecoByCEP( trim($_POST['endereco_cep']), trim($_POST['endereco_logradouro']), trim($_POST['endereco_bairro']), trim($_POST['endereco_cidade']), trim($_POST['endereco_numero']), trim($_POST['endereco_complemento']) );
                if (intval($dadosCEP['id']) == 0) { 
                    if ( isset($dadosCEP['msgError']) ) $parametroError['endereco_cep'] = $dadosCEP['msgError'];
                    else                                $parametroError['endereco_cep'] = 'CEP do cliente inválido!';
                }
                else {
                    if ($dadosCEP['logradouro'] == '') {
                        if ( trim($_POST['endereco_logradouro']) == '')        { $parametroError['endereco_logradouro'] = 'Logradouro não informado!'; }
                        elseif ( $Format->isUTF8( trim($_POST['endereco_logradouro']) ) == false ) { $parametroError['endereco_logradouro'] = 'Logradouro em formato de codificação inválido! (obrigatório estar em UTF-8)'; }
                    }
                    if ($dadosCEP['bairro'] == '') {
                        if ( trim($_POST['endereco_bairro']) == '')        { $parametroError['endereco_bairro'] = 'Bairro não informado!'; }
                        elseif ( $Format->isUTF8( trim($_POST['endereco_bairro']) ) == false ) { $parametroError['endereco_bairro'] = 'Bairro em formato de codificação inválido! (obrigatório estar em UTF-8)'; }
                    }
                }
            }
        }
        
        if ( trim($_POST['endereco_complemento_tipo']) != '') {
            if (!isset( TIPOS_COMPLEMENTO[ trim($_POST['endereco_complemento_tipo']) ] )) { $parametroError['endereco_complemento_tipo'] = 'Tipo de complemento inválido!'; }
        }
        
        if ( strlen(trim($_POST['endereco_numero'])) > 7 ) {
            $parametroError['endereco_numero'] = 'Número inválido!';
        }
        
        // Contato Telefones
        $foneContato = false;
        foreach ($_POST['telefone_ddd'] AS $k => $v) {
            if (trim($_POST['telefone_ddd'][$k]) != '' || trim($_POST['telefone_numero'][$k]) != '' || trim($_POST['telefone_tipo'][$k]) != '') {
                $erroFone = false;
                if ( trim($_POST['telefone_ddd'][$k]) == '') { 
                    $erroFone = true; 
                    $parametroError['telefone_ddd'] = 'DD do telefone não informado!'; 
                }
                else {
                    if ( strlen(intval($_POST['telefone_ddd'][$k])) != 2) { 
                        $erroFone = true; 
                        $parametroError['telefone_ddd'] = 'DD do telefone inválido!'; 
                    }
                }
                    
                if ( trim($_POST['telefone_numero'][$k]) == '') { 
                    $erroFone = true; 
                    $parametroError['telefone_numero'] = 'Número do telefone não informado!'; 
                }
                else {
                    if ( $Format->ValidPhoneSemDDD( trim($_POST['telefone_numero'][$k]) ) === false ) { 
                        $erroFone = true; 
                        $parametroError['telefone_numero'] = 'Número do telefone inválido!'; 
                    }
                }
                
                if ( trim($_POST['telefone_tipo'][$k]) == '') { 
                    $erroFone = true; 
                    $parametroError['telefone_tipo'] = 'Tipo do telefone não informado!'; 
                }
                else {
                    if (!isset( TIPOS_TELEFONE[intval($_POST['telefone_tipo'][$k])] )) { 
                        $parametroError['telefone_tipo'] = 'Tipo do telefone inválido!';
                    }
                }
                if ($erroFone == false) $foneContato = true;
            }
        }
        
        // Contato Email
        $mailContato = false;
        foreach ($_POST['email'] AS $k => $v) {
            if (trim($_POST['email'][$k]) != '' || trim(@$_POST['email_tipo'][$k]) != '') {
                $erroMail = false;
      
                if ( trim($_POST['email'][$k]) == '') { 
                    $erroMail = true;
                    $parametroError['email'] = 'E-mail não informado!'; 
                } else {         

                    if (!filter_var(trim($_POST['email'][$k]), FILTER_VALIDATE_EMAIL)) {
                        $parametroError['email'] = 'E-mail inválido!!';
                        $erroMail = true;
                    }

                }
                if ($erroMail == false) $mailContato = true;
            }
        }
        
        if ($mailContato == false) { $parametroError['outros'] = 'É obrigatório ter um e-mail de contato!'; }
        
        $_POST['dia_vencimento'] = intval($_POST['dia_vencimento']);
        switch ( intval($_POST['dia_vencimento']) ) {
            case 0:
            case 1:
            case 2:
            case 3:
            case 4: $_POST['dia_vencimento'] = 5; break;
            case 9: $_POST['dia_vencimento'] = 10; break;
            case 11:
            case 12:
            case 13:
            case 14: $_POST['dia_vencimento'] = 10; break;
            case 15:
            case 16:
            case 17:
            case 18:
            case 19: 
            case 21:
            case 22:
            case 23:
            case 24:
            case 25:
            case 26:
            case 27:
            case 28:
            case 29:
            case 30:
            case 31: $_POST['dia_vencimento'] = 20; break;
            default: $_POST['dia_vencimento'] = 20; break;
        }

        /*
        if ( == 0 )  { $parametroError['dia_vencimento'] = 'Dia de vencimento n�o informado!'; }
        else {
        	if ( $this->validaCobrancaCarona() == false ) {
                // Megaweb Telecom - ID: 463
                if ( $this->idParceiro == 463 ) {
                    
                }
                else {
                   $_POST['dia_vencimento'] = intval($_POST['dia_vencimento']); 
                }
        		if (!isset( DIAS_VENCIMENTO[$_POST['dia_vencimento']] )) { $parametroError['dia_vencimento'] = 'Dia de vencimento inv�lido!'; }
        	}
        }
        */
        
        // Endereço p/ Cobrança
        if ( trim($_POST['endereco_cobranca']) == '') { $parametroError['endereco_cobranca'] = 'Endereço de cobrança não informado!'; }
        else {
            if ( trim($_POST['endereco_cobranca']) != 'true') {
                if ( trim($_POST['cobranca_cep']) == '')        { $parametroError['cobranca_cep'] = 'CEP do endereço de cobrança não informado!'; }
                else {
                    $dadosCEP_cobranca = $this->getEnderecoByCEP( trim($_POST['cobranca_cep']), trim($_POST['cobranca_logradouro']), trim($_POST['cobranca_bairro']) );
                    if (intval($dadosCEP_cobranca['id']) == 0) { $parametroError['cobranca_cep'] = 'CEP do endereço de cobrança inválido!'; }
                    else {
                        if ($dadosCEP_cobranca['logradouro'] == '') {
                            if ( trim($_POST['cobranca_logradouro']) == '')        { $parametroError['cobranca_logradouro'] = 'Logradouro do endereço de cobrança não informado!'; }
                            elseif ( $Format->isUTF8( trim($_POST['cobranca_logradouro']) ) == false ) { $parametroError['cobranca_logradouro'] = 'Logradouro do endereço de cobrança em formato de codificação inválido! (obrigatório estar em UTF-8)'; }
                        }
                        if ($dadosCEP_cobranca['bairro'] == '') {
                            if ( trim($_POST['cobranca_bairro']) == '')        { $parametroError['cobranca_bairro'] = 'Bairro do endereço de cobrança não informado!'; }
                            elseif ( $Format->isUTF8( trim($_POST['cobranca_bairro']) ) == false ) { $parametroError['cobranca_bairro'] = 'Bairro do endereço de cobrança em formato de codificação inválido! (obrigatório estar em UTF-8)'; }
                        }
                    }
                    
                }
                
                if ( trim($_POST['cobranca_complemento_tipo']) != '') {
                    if (!isset( TIPOS_COMPLEMENTO[ trim($_POST['cobranca_complemento_tipo']) ] )) { $parametroError['cobranca_complemento_tipo'] = 'Tipo de complemento do endereço de cobrança inválido!'; }
                }
            }
        }
        return array('error'=>$parametroError, 'cep'=>@$dadosCEP, 'cep_cobranca'=>@$dadosCEP_cobranca);
    }
    
    
    function __destruct() {
        
    }
    
}