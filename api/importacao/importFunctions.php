<?php


$UFsList = array('RO','AC','AM','RR','PA','AP','TO','MA','PI','CE','RN','PB','PE','AL','SE','BA','MG','ES','RJ','SP','PR','SC','RS','MS','MT','GO','DF');


function chamadaAPI($action, $parameters = '' ) {
    //print ' | chamadaAPI -> '.$action; 
    
    //$host = 'https://192.168.74.3/api';// 
    //$host = 'https://devextranet.oletelecom.com.br/api';//oletv https://192.168.74.3/?m=clientes_origens&a=buscar
    //$host = API_HOST;
    $host = 'https://oletv.oletelecom.com.br/api';//
   // $host = 'http://localhost/intranet/api';//
    
    //echo 'host: '.$host.'/'.$action;

    
    $ch = curl_init( $host.'/'.$action );
   // $payload = json_encode( $parameters );
   
    
    curl_setopt($ch, CURLOPT_ENCODING, "UTF-8" );
    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
    
    $result = curl_exec($ch);
    
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    
    curl_close($ch);
    
    if ($curl_errno > 0) {
        //echo "cURL Error ($curl_errno): $curl_error\n";
        
        $objectResult['retorno_status'] = false;
        $objectResult['error'] = 'CURL Error ('.$curl_errno.'): '.$curl_error;
        
    }
    
    //print($result);
    
    $objectResult = json_decode($result,true);
    if ( !isset($objectResult['retorno_status']) ) {
        
        $objectResult['retorno_status'] = false;
        $objectResult['error'] = 'Houve uma falha da comunicação com o servidor!';

    }
    
    return $objectResult;//json_decode($result,true);
}

function setFileResult($nmFile, $line) {
    $myfile = @fopen($nmFile, "a");
    @fwrite($myfile, $line."\n");
    @fclose($myfile);
}

function clearPhone($number) {
    $number = sprintf( (strlen($number)==15 ? "%011s" : "%010s"), preg_replace("/[^0-9]/", "", $number) );
    
    return $number;
}

function formatDoc($cpf_cnpj) {
    //$cpf_cnpjClear 
    if ( strlen($cpf_cnpj) == 11 ) {
        return substr($cpf_cnpj,0,3).'.'.substr($cpf_cnpj,3,3).'.'.substr($cpf_cnpj,6,3).'-'.substr($cpf_cnpj,9,2);
    }
    elseif ( strlen($cpf_cnpj) == 14 ) {
        $Format = new formats();
        return $Format->formataCNPJ($cpf_cnpj);
    }
    else {
        return '-';
    }
    
}

function formatPhone() {
    
}

function validPhone($number) {
    $number = sprintf( (strlen($number)==15 ? "%011s" : "%010s"), preg_replace("/[^0-9]/", "", $number) );
    if ( strlen($number) == 10 || strlen($number) == 11 ) {
        $codigosDDD = array(11, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 24, 27, 28, 31, 32, 33, 34, 35, 37, 38, 41, 42, 43, 44, 45, 46, 47, 48, 49, 51, 53, 54, 55, 61, 62, 64, 63, 65, 66, 67, 68, 69, 71, 73, 74, 75, 77, 79, 81, 82, 83, 84, 85, 86, 87, 88, 89, 91, 92, 93, 94, 95, 96, 97, 98, 99);
        //$number = sprintf( (strlen($number)==15 ? "%011s" : "%010s"), preg_replace("/[^0-9]/", "", $number) );
        $findDDD = array_search( intval(substr($number,0,2)), $codigosDDD, true);
        if ( $findDDD === false ) {
            return false;
        }
        return true;
    }
    else {
        return false;
    }
}


function encodingText($text) {
    $cur_encoding = mb_detect_encoding($text) ;
    if($cur_encoding == "UTF-8" && mb_check_encoding($text,"UTF-8"))
        return utf8_decode( trataCaracteres( $text) );
        else
            return trataCaracteres( $text );
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

function validaDados($dadosLogin, $idParceiro, $nameParceiro, $posLinha, $nome, $cpf_cnpj, $dataNasc, $logradoudo, $complemento, $numero, $bairro, $uf, $cidade, $cep, $fone1, $fone2, $email, $mac1, $mac2, $mac3, $autoinsertCEP = false) {
    $return['type'] = '';// ignored, error, valid
    $return['errors'] = array();
    $lineError = false;
    $lineIgnored = false;
    $Format = new formats();
    
    
    
    // Valida tamanho minimo para none
    
    //echo 'Linha '.$posLinha.': '.mb_detect_encoding( trim( $nome ) ).' (Valida UTF-8: '.boolval($Format->isUTF8( $nome )).')<br>';
    
    $nome = trim( $nome ); 
    if ( strlen( $nome ) <= 5 ) {
        $lineError = true;
        $return['errors'][] = 'Razão Social/Nome é inválido! - <i><u>'. $nome .'</u></i>';
    }
    elseif ( $Format->isUTF8( $nome ) !== true ) { 
        $lineError = true;
        $return['errors'][] = 'Razão Social/Nome em formato de codificação inválido! (obrigatório estar em UTF-8)'; 
    }
    
    // Valida CPF/CNPJ
    $cpf_cnpjAlfa = strtoupper(preg_replace("/[^0-9A-Z]/", "", $cpf_cnpj));
    if ( strlen( sprintf("%011s", preg_replace("/[^0-9]/", "", $cpf_cnpj)) ) == 11 && !preg_match("/[A-Z]/", $cpf_cnpjAlfa) ) {
        $cpf_cnpjClear = sprintf("%011s", preg_replace("/[^0-9]/", "", $cpf_cnpj));
        $tpPessoa = 1; // 'Fisica'
        if ( $Format->validaCPF( $cpf_cnpjClear ) == false ) { $lineError = true; $return['errors'][] = 'CPF inválido! - <i><u>'.trim($cpf_cnpj).'</u></i>'; }
        // Valida Data Nascimento
        // Exceção aberta para a Smartnet, desobrigando a data de nascimento
        if ( in_array($idParceiro, idParceirosLiberaSemData) ) { }
        else {
            if ( $Format->ValidDtBr( trim($dataNasc) ) == false) { $lineError = true; $return['errors'][] = 'Data de Nascimento não informada ou inválida! - <i><u>'.trim($dataNasc).'</u></i>'; }
        }
    }
    elseif ( $Format->validaCNPJ( $cpf_cnpj ) ) {
        $cpf_cnpjClear = preg_replace("/[^0-9A-Z]/", "", strtoupper($Format->formataCNPJ($cpf_cnpj)));
        $tpPessoa = 2;// Juridica
    }
    elseif ( strlen($cpf_cnpjAlfa) == 14 ) {
        $cpf_cnpjClear = $cpf_cnpjAlfa;
        $tpPessoa = 2;// Juridica
        if ( $Format->validaCNPJ( $cpf_cnpjClear ) == false ) { $lineError = true; $return['errors'][] = 'CNPJ inválido! - <i><u>'.trim($cpf_cnpj).'</u></i>'; }
    }
    else {
        
        if ( in_array($idParceiro, idParceirosLiberaSemDoc) ) { }
        else {
            $lineError = true;
            $return['errors'][] = 'CPF/CNPJ não identificado! - <i><u>'.trim( $cpf_cnpj ).'</u></i>';
        }

    }

    // Verifica se cliente j� foi cadastrado
    if ( in_array($idParceiro, idParceirosLiberaSemDoc) ) { }
    else {

        $dadosCPFCNPJ = chamadaAPI('clientes/buscacpfcnpj/'.$cpf_cnpj, $dadosLogin );// $loadClass->verificaCidadeParceiro($cep);
        
        if ( $dadosCPFCNPJ['retorno_status'] == true ) {
            if ( count($dadosCPFCNPJ['lista']) > 0 ) {
                $lineError = true;
                $return['errors'][] = 'CPF/CNPJ já cadastrado! - <i><u>'. $cpf_cnpj .'</u></i>';
            }
        }

    }
    
    // Valida Endereço
    $logradoudo = ( trim($logradoudo) );//utf8_encode
    $logradoudo = str_replace(':', ' ', $logradoudo);
    $logradoudo = str_replace('Est.', 'Estrada ', $logradoudo);
    $logradoudo = str_replace('R.', 'Rua ', $logradoudo);
    $logradoudo = str_replace('Av.', 'Avenida ', $logradoudo);
    $logradoudo = str_replace('Av ', 'Avenida ', $logradoudo);
    $logradoudo = str_replace('Trav.', 'Travessa ', $logradoudo);
    $logradoudo = str_replace('  ', ' ', $logradoudo);
    
    if ( strlen( $logradoudo ) <= 3 ) {
        $lineError = true;
        $return['errors'][] = 'Endereço (Logradoudo) inválido! - <i><u>'.$logradoudo.'</u></i>';
    }
    elseif ( $Format->isUTF8( $logradoudo ) !== true ) {
        $lineError = true;
        $return['errors'][] = 'Endereço (Logradoudo) em formato de codificação inválido! (obrigatório estar em UTF-8)';
    }
    
    // Valida Bairro
    if ( strlen( trim( $bairro ) ) <= 1 ) {
        $lineError = true;
        $return['errors'][] = 'Bairro inválido! - <i><u>'.trim( $bairro ).'</u></i>';
    }
    elseif ( $Format->isUTF8( $bairro ) !== true ) {
        $lineError = true;
        $return['errors'][] = 'Bairro em formato de codificação inválido! (obrigatório estar em UTF-8)';
    }
    
    // Valida Estado
    if ( strlen( trim( $uf ) ) != 2 ) {
        $lineError = true;
        $return['errors'][] = 'Estado (UF) inválido! - <i><u>'.trim( $uf ).'</u></i>';
    }
    else {
        
        $findUF = array_search(trim($uf),$GLOBALS['UFsList'],true);
        if ( $findUF === false ) {
            $lineError = true;
            $return['errors'][] = 'Estado (UF) inválido! - <i><u>'.trim( $uf ).'</u></i>';
        }
        
    }
    
    

    
   
    // Valida CEP
    if ( trim($cep) == '')        { $lineError = true; $return['errors'][] = 'CEP não informado!'; }
    else {
        if ($Format->ValidCEP( trim($cep)  ) == false) { $lineError = true; $return['errors'][] = 'CEP inválido! - <i><u>'.trim($cep).'</u></i>'; }
        else {
            $dadosCidade = $dadosLogin .'&cep='.trim($cep).'&logradouro='.$logradoudo.'&bairro='.trim($bairro);
            $validaCEP = chamadaAPI('parceiro/verificacaocidade', $dadosCidade );
            if ( !empty($validaCEP['error']) ) {
                
                $dadosAutoCEP = chamadaAPI('parceiro/verificaCidadeParceiro', $dadosLogin.'&cep='.$cep.'&logradoudo='.$logradoudo.'&complemento='.$complemento.'&numero='.$numero.'&bairro='.$bairro.'&uf='.$uf.'&cidade='.$cidade.'&autoinsertCEP='.$autoinsertCEP );// $loadClass->verificaCidadeParceiro($cep);
                
                if ( !empty($dadosAutoCEP['error']) ) {
                    $lineError = true;
                    $return['errors'][] = $dadosAutoCEP['error'];
                }
                else {
                    /*if ( $autoinsertCEP == true ) {
                        $lineError = true;
                        $return['errors'][] = '$autoinsertCEP:'.$autoinsertCEP;
                    }
                    */
                }
                
            }
        }
    }
    
    
    // Valida telefone
    if ( trim($fone1) != '' ) {
        if ( validPhone(trim($fone1)) === false ) {
            $lineError = true;
            $return['errors'][] = 'Telefone 1 inválido! - <i><u>'.trim($fone1).'</u></i>';
        }
    }
    
    if ( trim($fone2) != '' ) {
        if ( validPhone(trim($fone2)) === false ) {
            $lineError = true;
            $return['errors'][] = 'Telefone 2 inválido! - <i><u>'.trim($fone2).'</u></i>';
        }
    }
    
    $validAutentication = false;
    if ( trim($email) != '' ) {
    // Validação de e-mail
    
        $findEmailsList = array_search(trim($email), $GLOBALS['emailsList'], true);
        if ( $findEmailsList === false ) {
            
            if ( $Format->ValidMail(trim($email)) == false) {
                $lineError = true;
                $return['errors'][] = 'E-mail não informado ou inválido! - <i><u>'.trim($email).'</u></i>';
            }
            else {
                $GLOBALS['emailsList'][$posLinha] = trim($email);
            }
        }
        else {
            $return['errors'][] = 'E-mail já esta em uso no registro da linha '.$findEmailsList;
        }
        $validAutentication = true;
    }

    if ( trim($mac1) != '' ) {

        $mac1 = preg_replace("/[^A-F0-9]/", "", strtoupper(trim($mac1)));
        if ( strlen($mac1) != 12) {
            $return['errors'][] = "MAC informado é inválido! - ".$mac1;
        }
        $validAutentication = true;
    }

    if ( $validAutentication == false ) {

        $return['errors'][] = 'Obrigado a ter uma forma de autenticação (E-mail usuario ou MAC)';

    }
    
    
    // Se nao houve erros até aqui, verifica se tem contrato ativo no e-mail
    if ( $lineError == false) {
        $dadosContrato = $dadosLogin .'&email='.trim($email);
        $validaContrato = chamadaAPI('parceiro/emailcontratoativo', $dadosContrato );
        
        if ( $validaContrato['retorno_status'] == true ) {
            
            if ( $validaContrato['idParceiro'] == $idParceiro ) {
                // Limpa CPF/CNPJ para comparar
                $contrato_cpf_cnpj = strrev(base64_decode($validaContrato['cliente_doc']));
                $contrato_cpf_cnpjAlfa = strtoupper(preg_replace("/[^0-9A-Z]/", "", $contrato_cpf_cnpj));
                if (strlen($contrato_cpf_cnpjAlfa) == 11 && !preg_match("/[A-Z]/", $contrato_cpf_cnpjAlfa)) {
                    $contrato_cpf_cnpj = sprintf("%011s", preg_replace("/[^0-9]/", "", $contrato_cpf_cnpj));
                } else {
                    $contrato_cpf_cnpj = $contrato_cpf_cnpjAlfa;
                }
                // Compara CPF/CNPJ com doc retornando
                if ( $contrato_cpf_cnpj == $cpf_cnpjClear ) {
                    //$countRepeated++;
                    $lineIgnored = true;
                    $return['detailIgnored'][] = 'Já foi identificado um Contrato Ativo como da mesma pessoa (Cliente existente - Id: '.$validaContrato['cliente_id'].' - Nome: '.$validaContrato['cliente_nome'].')';
                }
                else {
                    $lineError = true;
                    $return['errors'][] = 'E-mail já pertence a um contrato ativo de outro cliente seu! - <i><u>'.trim($email).'</u></i>';
                }
            }
            else {
                $lineError = true;
                $return['errors'][] = 'E-mail já pertence a um contrato ativo! - <i><u>'.trim($email).'</u></i>';
            }
            
        }
    }
    
    
    
    
    
    
    if ($lineError == false && $lineIgnored == false) {
        $return['type'] = 'valid';
        $return['nome'] = $nome;
        $return['tpPessoa'] = $tpPessoa;
        $return['cpf_cnpj'] = formatDoc($cpf_cnpjClear);
        $return['dtNascimento'] = trim($dataNasc);
        $return['cep'] = trim($cep);
        $return['logradouro'] = trim($logradoudo);
        $return['numero'] = trim($numero);
        $return['bairro'] = utf8_encode(trim($bairro));
        $return['fone1'] = trim($fone1);
        $return['fone2'] = trim($fone2);
        $return['email'] = trim($email);
        $return['mac1'] = trim($mac1);
        $return['mac2'] = trim($mac2);
        $return['mac3'] = trim($mac3);
    }
    elseif ($lineError == true) {
        $return['type'] = 'error';
    }
    elseif ($lineIgnored == true) {
        $return['type'] = 'ignored';
    }
    
    return $return;
}



function insereCliente($dadosLogin, $cliente) {
    
    $dadosCliente = $dadosLogin;// .'&cep='.trim($cep).'&logradouro='.trim($logradoudo).'&bairro='.trim($bairro);
    
    $dadosCliente .= '&nome='.$cliente['nome'];
    $dadosCliente .= '&tipo_pessoa='.$cliente['tpPessoa'];
    $dadosCliente .= '&nome_fantasia='.($cliente['tpPessoa']==1 ? '' : $cliente['nome']);
    $dadosCliente .= '&cpf_cnpj='.$cliente['cpf_cnpj'];
    $dadosCliente .= '&inscricao_estadual=';
    $dadosCliente .= '&data_nascimento='.($cliente['tpPessoa']==1 ? $cliente['dtNascimento'] : '');
    $dadosCliente .= '&endereco_cep='.$cliente['cep'];
    $dadosCliente .= '&endereco_logradouro='.$cliente['logradouro'];
    $dadosCliente .= '&endereco_numero='.$cliente['numero'];
    $dadosCliente .= '&endereco_bairro='.$cliente['bairro'];
    $dadosCliente .= '&endereco_ponto_referencia=';
    $dadosCliente .= '&endereco_complemento_tipo=o';
    $dadosCliente .= '&endereco_complemento_valor=';
    $dadosCliente .= '&contato=';
    if ( $cliente['fone1'] != '') {
        $dadosCliente .= '&telefone_ddd[]='.substr( $cliente['fone1'] , 1, 2);
        $dadosCliente .= '&telefone_numero[]='.substr( $cliente['fone1'] , 5);
        $dadosCliente .= '&telefone_ramal[]=';
        $dadosCliente .= '&telefone_tipo[]='. (strlen(substr( $cliente['fone1'] , 5))==9 ? ($cliente['tpPessoa']==1 ? '1' : '2') : '3'); // 1 = Residencial  2 = Comercial  3 = Celular   4 = Fax   5 = Recados   7 = WhatsApp
    }
    if ( $cliente['fone2'] != '') {
        $dadosCliente .= '&telefone_ddd[]='.substr( $cliente['fone2'] , 1, 2);
        $dadosCliente .= '&telefone_numero[]='.substr( $cliente['fone2'] , 5);
        $dadosCliente .= '&telefone_ramal[]=';
        $dadosCliente .= '&telefone_tipo[]='. (strlen(substr( $cliente['fone2'] , 5))==9 ? ($cliente['tpPessoa']==1 ? '1' : '2') : '3'); // 1 = Residencial  2 = Comercial  3 = Celular   4 = Fax   5 = Recados   7 = WhatsApp
    }
    $dadosCliente .= '&email[]='.$cliente['email'];
    $dadosCliente .= '&dia_vencimento=5'; // 5 = Dia 05  6 = Dia 06   7 = Dia 07   8 = Dia 08   10 = Dia 10    20 = Dia 20
    $dadosCliente .= '&endereco_cobranca=true';
    
    //echo '<hr>'.$dadosCliente.'<hr>';
    
    $chamadaCliente = chamadaAPI('clientes/inserir', $dadosCliente );
    //[retorno_status] => 1 [msg] => Cliente inserido com sucesso! [id] => 120576
    //print_r($chamadaCliente);

    
    //$chamadaCliente['retorno_status'] = true;
    //$chamadaCliente['msg'] = 'Cliente inserido com sucesso!';
    //$chamadaCliente['id'] = 120576;
    
    if ( $chamadaCliente['retorno_status'] == true ) {
        $return['status'] = true;
        $return['id'] = $chamadaCliente['id'];
    }
    else {
        $return['status'] = false;
        foreach (@$chamadaCliente['error'] AS $key => $val) {
            $return['error'][] = $val.' ('.$key.')';
        }
    }
    return $return;
    
}


function insereContrato($dadosLogin, $cliente_id, $email, $mac1 = '', $id_parceiro = 0) {
    
    $dadosContrato = $dadosLogin;
    $dadosContrato .= '&id_cliente='.$cliente_id;
    $dadosContrato .= '&id_contrato_origem=';
    $dadosContrato .= '&id_plano_principal='.($id_parceiro == 483 || $id_parceiro == 484 || $id_parceiro == 482 ? '731' : '729');
    $dadosContrato .= '&id_modelo[]='.($mac1!='' ? '7' : '');
    $dadosContrato .= '&mac[]='.$mac1;
    //$dadosContrato .= '&id_plano_adicional[]=';
    //$dadosContrato .= '&id_modelo[]=';
    //$dadosContrato .= '&mac[]=';
    $dadosContrato .= '&email_usuario='.$email;
    
    //echo '<hr>'.$dadosContrato.'<hr>';
    
    $chamadaContrato = chamadaAPI('contratos/inserir', $dadosContrato );
    //print_r($chamadaContrato);
    
    if ( $chamadaContrato['retorno_status'] == true ) {
        $return['status'] = true;
        $return['id'] = $chamadaContrato['id'];
    }
    else {
        $return['status'] = false;
        $return['error'][] = $chamadaContrato['error'];// $val.' ('.$key.')';
    }
    
    return $return;
}

function verificaContratosExistente($dadosLogin, $cpf_cnpj) {
    
    $returnContratro = false;
    
    $chamadaCliente = chamadaAPI('api/clientes/buscacpfcnpj/'.$cpf_cnpj, $dadosLogin );
    if ( $chamadaCliente['retorno_status'] == true ) {
        
        foreach (@$chamadaCliente['lista'] AS $key => $val) {
            
            $chamadaContrato = chamadaAPI('api/contratos/listar/'.$val['id'], $dadosLogin );
            if ( $chamadaContrato['retorno_status'] == true ) {
                
                if ( count($chamadaContrato['contratos']) > 0 ) {
                    $returnContratro = true;
                }
                
            }
            
        }        
        
    }
    
    return $returnContratro;

}