<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// Versão Produção

//$idParceirosLiberaSemData = [1, 232, 230, 482, 483, 484];
//define( "idParceirosLiberaSemData", serialize($idParceirosLiberaSemData) );
@define (idParceirosLiberaSemData, array(1, 232, 230, 482, 483, 484) );

//$idParceirosLiberaSemDoc = [1, 482, 483, 484];
//define( "idParceirosLiberaSemDoc", serialize($idParceirosLiberaSemDoc) );
@define (idParceirosLiberaSemDoc, array(1, 482, 483, 484) );

//$idParceirosLiberaComErros = [230];
//define( "idParceirosLiberaComErros", serialize($idParceirosLiberaComErros) );
@define (idParceirosLiberaComErros, array(1, 230) );

// Versão Homologação
//define (idParceirosLiberaSemData, array(1, 0152) );
//define (idParceirosLiberaComErros, array(1, 152) );


header("Access-Control-Allow-Origin: *");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
set_time_limit(0);
error_reporting(0);//E_ALL


if ( isset($_GET['confirm']) ) {
    ignore_user_abort(1);
}



/***********/

$ultimaModificacao = '19/01/2023';
$numeroVersao = '1.1.0';

/***********/


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> -->
<title>Documentação API - IPTV Olé</title>
</head>

<link rel="stylesheet" href="../documentacao/bootstrap-4.5.3-dist/css/bootstrap.min.css">

<style type="text/css">
#showSend {
    background-color: rgba(0,0,0,0.2);
    position: absolute;
    top: 0;
    left: 0;
}

.grid-striped > .row:nth-of-type(odd) {
    background-color: rgba(0,0,0,0.05);
}

.grid-striped > .row:hover { 
	background-color: rgba(255, 255, 0, 0.2);
}

.blink_me {
  animation: blinker 1s linear infinite;
}

@keyframes blinker {  
  50% { opacity: 0; }
}


@font-face {
  font-family: 'password';
  font-style: normal;      
  src: url(../importacao/password.ttf);
}
#pass {
  font-family: 'password';
}

</style>



<body>

<div class="row w-100">
	<div class="col-12 col-md-3 col-lg-3 col-xl-2"><a href="../importacao"><img src="../../imagens/logo_ole_tv.png" width="200"></a></div>
	<div class="col-12 col-md-9 col-lg-9 col-xl-10 pl-4 pr-4">
		<h1>Importação de Arquivo de Dados - IPTV Olé TV <sup><span class="badge badge-secondary">v<?=$numeroVersao;?></span></sup></h1>
		<p class="blockquote-footer">Atualizado em <?=$ultimaModificacao;?></p>
	</div>
</div>


<script src="../documentacao/jquery-3.5.1.js" type="text/javascript"></script>
<script src="../documentacao/popper.min.js" type="text/javascript"></script>

<script src="../documentacao/bootstrap-4.5.3-dist/js/bootstrap.min.js"></script>
<script src="../documentacao/bootstrap-4.5.3-dist/js/bootstrap.bundle.min.js"></script>


<div class="row m-2">

	<div class="col-12 p-2">
	
		<?php 
		
		if (isset($_POST['sendFile'])) {
		    
		    //print('Inicando leitura...<br>');
		    
		    require_once '../config/config_api.php';
		    require_once '../config/formats.php';
		    require_once '../importacao/importFunctions.php';
		    
		    require_once '../../_global/bancodedados.class.inc.php';
		    require_once("../../_global/controle.class.inc.php");

		    
		    //$db;
		    //$errorDB = '';
		    //$errorDB = conectaDB();
		    
		    $html = file_get_contents('resultImport.html');
		    
		    
		    //$dadosLogin = 'keyapi='=>$_POST['keyapi'], 'login'=>$_POST['login'], 'pass'=>$_POST['pass'] );
		    $dadosLogin = 'keyapi='.$_POST['keyapi'].'&login='.$_POST['login'].'&pass='.$_POST['pass'];
		    //print('Iniciando autenticação<br>');
		    $session = chamadaAPI('parceiro/autenticacao', $dadosLogin );
		    
		    //print_r($session);
		    //$session['retorno_status'] = false;
		    //$session['error'] = 'Testando...';

		    
		    if ( $session['retorno_status'] != true ) {;
		        
		        $html = str_replace('{fileImport}', 'Erro de Conexão', $html);
		        $html = str_replace('{classResult}', 'bg-danger', $html);
		        $html = str_replace('{resultImport}', '<p class="text-danger">'.$session['error'].'</p>', $html);
		        $html = str_replace('{detailImport}', '&nbsp;', $html);
		        $html = str_replace('{buttonFooter}', '<a href="../importacao"><button class="btn btn-success" onclick="">Voltar</button></a>', $html);
		        
		    }
		    else {
		        
		        
		        
		        $idParceiro = $session['id_parceiro'];
		        $nameParceiro = $session['name_parceiro'];
		        
		        $erro = false;
		        $errorFile = false;
		        $countSuccess = 0;
		        $countError = 0;
		        $countRepeated = 0;
		        
		        
		        
		        $errorsDetected = [];
		        $linha = 0;
		        
		        $exampleData = '';
		        $exampleError = '';
		        $countExample = 0;
		        $emailsList = array();
		        
		        $fileImport = str_pad($idParceiro, 6, '0', STR_PAD_LEFT).'-'.time();
		        
		        $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');
		        if(in_array($_FILES['fileImport']['type'],$mimes) && $_FILES['fileImport']['error'] == 0){
		            if (is_uploaded_file($_FILES['fileImport']['tmp_name'])) {
		                
		                
		                if (move_uploaded_file($_FILES['fileImport']['tmp_name'], '../importacao/files/'.$fileImport.'.csv')) {
		                    
                            $html = str_replace('{fileImport}', 'Resultado da análise do arquivo "'.$_FILES['fileImport']['name'].'"', $html);
                            
		                } else {
		                    $errorFile = true;
		                }
		            
		                
		            }
		            else {
		                $errorFile = true;
		            }
		        }
		        else {
		            $errorFile = true;
		        }
		        
		        
		        
		       
		        
		        
		        if ( $errorFile == true ) {
		            
		            $html = str_replace('{fileImport}', 'Falha no upload', $html);
		            $html = str_replace('{classResult}', 'bg-danger', $html);
		            $html = str_replace('{resultImport}', '<p class="text-danger">Arquivo não foi encontrato ou seu formato é inválido!', $html);
		            $html = str_replace('{detailImport}', 'Lembre-se: O arquivo deserá ser obrigatóriamente do tipo CSV com delimitador ponto e vírgula (";")', $html);
		            $html = str_replace('{buttonFooter}', '<a href="../importacao"><button class="btn btn-success" onclick="">Voltar</button></a>', $html);
		            
		        }
		        else {
		            
		           
		            
		            
		            
		            
		            
		            
		            $handle = fopen('../importacao/files/'.$fileImport.'.csv', "r");
		            
		            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
		                $linha++;
		                if ( $linha > 1) {
		                    
	                        /*
	                         * 0 - Nome (Razão Social)
	                         * 1 - CPF / CNPJ
	                         * 2 - Data Nascimento (dd/mm/AAAA)
	                         * 3 - Endereço (Logradoudo)
	                         * 4 - Complemento
	                         * 5 - Numero
	                         * 6 - Bairro
	                         * 7 - UF
	                         * 8 - Cidade
	                         * 9- CEP
	                         * 10- Telefone 1
	                         * 11- Telefone 2
	                         * 12- E-mail
	                         * 13- MAC 1
	                         * 14- MAC 2
	                         * 15- MAC 3
	                         */
	                        
		                    $validDados = validaDados($dadosLogin, $idParceiro, $nameParceiro, $linha, $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], @$data[11], @$data[12], @$data[13], @$data[14], @$data[15], false);
	                        
	                        switch ( $validDados['type'] ) {
	                            
	                            case 'valid':
	                                $countSuccess++;
	                                break;
	                                
	                            case 'error':
	                                $countError++;
	                                $errorsDetected[$linha] = $validDados['errors'];
	                                break;
	                                
	                                
	                            case 'ignored':
	                                $countRepeated++;
	                                break;
	                            
	                        }
		                    
		                }
		                
		            }
		            fclose($handle);
		            
		            
		            if ( count($errorsDetected) > 0 ) {
		                $resultImport  = '<p><b>Foram encontrados os seguintes erros:</b></p>';
		                
		                //$resultImport = '<div class="row">';
		                
		                $resultImport .= '<div class="grid-striped">';
		                foreach ($errorsDetected AS $key => $val) {
		                    $resultImport .= '<div class="row p-1 rowHover">';
		                    $detailError = '';
		                    if (is_array($val) || is_object($val)) {
    		  		            foreach ($val as $v) {
    		  		                $detailError .= ($detailError != '' ? '<br>':'').' - '.$v;
    		  		            }
		                    }
		  		            $resultImport .= ' <div class="col-12 col-sm-3 col-md-2 col-lg-2 col-xl-1"><b>Linha: '.$key.'</b></div><div class="col">'.$detailError.'</div>';
		                    $resultImport .= '</div>';
		                }
		                $resultImport .= '</div>';
		                
		                
		                $buttonFooter = '';
		                if ( in_array($idParceiro, idParceirosLiberaComErros) ) {
		                    if ($countSuccess > 0) {
		                     
		                        $resultImport .= '<hr><p>';
		                        $resultImport .= '<b><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-hand-thumbs-up-fill" viewBox="0 0 16 16">';
		                        $resultImport .= ' <path d="M6.956 1.745C7.021.81 7.908.087 8.864.325l.261.066c.463.116.874.456 1.012.965.22.816.533 2.511.062 4.51a9.84 9.84 0 0 1 .443-.051c.713-.065 1.669-.072 2.516.21.518.173.994.681 1.2 1.273.184.532.16 1.162-.234 1.733.058.119.103.242.138.363.077.27.113.567.113.856 0 .289-.036.586-.113.856-.039.135-.09.273-.16.404.169.387.107.819-.003 1.148a3.163 3.163 0 0 1-.488.901c.054.152.076.312.076.465 0 .305-.089.625-.253.912C13.1 15.522 12.437 16 11.5 16H8c-.605 0-1.07-.081-1.466-.218a4.82 4.82 0 0 1-.97-.484l-.048-.03c-.504-.307-.999-.609-2.068-.722C2.682 14.464 2 13.846 2 13V9c0-.85.685-1.432 1.357-1.615.849-.232 1.574-.787 2.132-1.41.56-.627.914-1.28 1.039-1.639.199-.575.356-1.539.428-2.59z"/>';
		                        $resultImport .= '</svg> <span class="text-success">Arquivo Aprovado para importação parcial!</span></b><br>';
		                        $resultImport .= 'Alguns dados encontrados no arquivo foram válidados com sucesso.<hr>Ao clicar em <b>Confirmar Importação</b>, todos os dados (<i>válidos</i>) serão inserido na Olé TV (<i class="text-danger blink_me">Após iniciada, não poderá ser cancelada/desfeita</i>)';
		                        $resultImport .= '</p><p>&nbsp;</p><p>&nbsp;</p>';
		                        $resultImport .= '<p class="text-danger"><b>Obs.:</b> Dependendo da quantidade de registros, essa operação pode ser demorada!<br>Mesmo fechando a janela o processo será executado até o fim.</p>';
		                        
		                        
		                        
		                      $hash = base64_encode( strrev( base64_encode(strrev($dadosLogin)).'|'.$idParceiro.'|'.$fileImport ) );
		                      $buttonFooter  = '<a href="../importacao?confirm='.$hash.'"><button id="confirmForm" class="btn btn-success" onclick="">Confirmar Importação dos válidos</button></a> ';
		                    }
		                    $buttonFooter .= '<a href="../importacao"><button class="btn btn-danger" onclick="">Voltar</button></a>';
		                    
		                }
		                else {
		                    $buttonFooter = '<a href="../importacao"><button class="btn btn-success" onclick="">Voltar</button></a>';
		                }
		                
		                
		                $html = str_replace('{buttonFooter}', $buttonFooter, $html);
		            }
		            else {
		                
		                $resultImport  = '<p>';
		                $resultImport .= '<b><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-hand-thumbs-up-fill" viewBox="0 0 16 16">';
		                $resultImport .= ' <path d="M6.956 1.745C7.021.81 7.908.087 8.864.325l.261.066c.463.116.874.456 1.012.965.22.816.533 2.511.062 4.51a9.84 9.84 0 0 1 .443-.051c.713-.065 1.669-.072 2.516.21.518.173.994.681 1.2 1.273.184.532.16 1.162-.234 1.733.058.119.103.242.138.363.077.27.113.567.113.856 0 .289-.036.586-.113.856-.039.135-.09.273-.16.404.169.387.107.819-.003 1.148a3.163 3.163 0 0 1-.488.901c.054.152.076.312.076.465 0 .305-.089.625-.253.912C13.1 15.522 12.437 16 11.5 16H8c-.605 0-1.07-.081-1.466-.218a4.82 4.82 0 0 1-.97-.484l-.048-.03c-.504-.307-.999-.609-2.068-.722C2.682 14.464 2 13.846 2 13V9c0-.85.685-1.432 1.357-1.615.849-.232 1.574-.787 2.132-1.41.56-.627.914-1.28 1.039-1.639.199-.575.356-1.539.428-2.59z"/>';
		                $resultImport .= '</svg> Arquivo Aprovado!</b><br>';
		                $resultImport .= 'Os dados encontrados no arquivo foram válidados com sucesso.<hr>Ao clicar em <b>Confirmar Importação</b>, todos os dados serão inserido na Olé TV (<i class="text-danger blink_me">Após iniciada, não poderá ser cancelada/desfeita</i>)';
		                $resultImport .= '</p><p>&nbsp;</p><p>&nbsp;</p>';
		                $resultImport .= '<p class="text-danger"><b>Obs.:</b> Dependendo da quantidade de registros, essa operação pode ser demorada!<br>Mesmo fechando a janela o processo será executado até o fim.</p>';
		                
		                
		                $hash = base64_encode( strrev( base64_encode(strrev($dadosLogin)).'|'.$idParceiro.'|'.$fileImport ) );
		                
		                
		                $html = str_replace('{buttonFooter}', '<a href="../importacao?confirm='.$hash.'"><button id="confirmForm" class="btn btn-success" onclick="">Confirmar Importação</button></a>', $html);
		                
		                
		            }
		            $html = str_replace('{resultImport}', $resultImport, $html);
		            
		            
		            
		            //echo '--'. $countSuccess .'---';
		            
		            // Detalhes do Rodapé
		            $detailImport  = '<b>Total de Registros: '.($countSuccess+$countError).'</b><br>';
		            $detailImport .= ' - <span class="text-success">Registros válidos: '.$countSuccess.'</span><br>';
		            $detailImport .= ' - <span class="text-danger">Registros inválidos: '.$countError.'</span><br>';
		            $detailImport .= ' - <span class="text-info">Registros ignorados: '.$countRepeated.' (contratos já existentes)</span><br>';
		            $html = str_replace('{detailImport}', $detailImport, $html);
		            
		            if ($erro == false) {
		                $html = str_replace('{classResult}', 'bg-success', $html);
		            }
		            else {
		                $html = str_replace('{classResult}', 'bg-danger', $html);
		            }
		            
		        }
		        
		        
		        
		    }
		    echo $html;
		    
		}
		
		
		elseif ( isset($_GET['confirm']) ) {
		    require_once '../config/config_api.php';
		    require_once '../config/formats.php';
		    require_once '../importacao/importFunctions.php';
		    
		    require_once '../../_global/bancodedados.class.inc.php';
		    require_once("../../_global/controle.class.inc.php");
		    
		    
		    $dadosConfirm = explode('|', strrev( base64_decode( $_GET['confirm'] ) ) );
		    

		    $fileImport = 'Não autorizado!';
		    $resultImport = '<p><b>URL foi considerada inválida!</b></p><p><i>As informações enviadas não são válidas ou já foram executadas.</i></p>';
		    $detailImport = '&nbsp;';
		    
		    //print_r( $dadosConfirm );
		    
		    // Valida Dados
		    $dadosLogin = strrev( base64_decode($dadosConfirm[0]));
		    $session = chamadaAPI('parceiro/autenticacao', $dadosLogin );
		    if ( boolval($session['retorno_status']) == true ) {
		    
		        if ( $dadosConfirm[1] == $session['id_parceiro'] ) {
		            
		            
		            $detailFile = explode('-', $dadosConfirm[2]);
		            if ( $session['id_parceiro'] == intval($detailFile[0]) ) {
		                
		                $nameParceiro = preg_replace(array("/(á|à|ã|â|ä|å)/","/(Á|À|Ã|Â|Ä|Å)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/", "/(ÿ)/","/(Ç)/","/(ç)/", "/( )/"),explode(" ","a A e E i I o O u U n N y C c _"),$session['name_parceiro']);
		                
		                $fileParceiroSuccess = '../importacao/results/'.$nameParceiro.'-'.date('d-m-Y_H:i:s').'_(Sucessos).csv';
		                $fileParceiroErrors = '../importacao/results/'.$nameParceiro.'-'.date('d-m-Y_H:i:s').'_(Erros).csv';
		                $fileParceiroIgnorados = '../importacao/results/'.$nameParceiro.'-'.date('d-m-Y_H:i:s').'_(Ignorados).csv';
		                
		               
		                
		                
		                if ( file_exists('../importacao/files/'.$dadosConfirm[2].'.csv') ) {
		                
		                    // Passsou por todas as validações, começa execução
                            $fileImport = 'Operação concluída!';
                            $erro = false;
                            $errorFile = false;
                            $countSuccess = 0;
                            $countError = 0;
                            $countRepeated = 0;
                            
                            $detailErrors = [];
                            $detailRepeated = [];
                            
                            $errorsDetected = [];
                            $linha = 0;
                            
                            $exampleData = '';
                            $exampleError = '';
                            $countExample = 0;
                            $emailsList = array();
                            
                            
                            
                            $listForSuporte = '';
                            
                            
                            $renameFile = $dadosConfirm[2].'-edit'.time();
                            rename('../importacao/files/'.$dadosConfirm[2].'.csv', '../importacao/files/'.$renameFile.'.csv');
                            
                            
                            $handle = fopen('../importacao/files/'.$renameFile.'.csv', "r");
                            
                            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                                $linha++;

                                if ( $linha > 1) {
                                    
                                    $validDados = validaDados($dadosLogin, $session['id_parceiro'], $session['name_parceiro'], $linha, $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12], @$data[13], @$data[14], @$data[15], true);
                                    
                                    switch ( $validDados['type'] ) {
                                        
                                        case 'valid':
                                            
                                            //echo 'CPF:'.$data[1].'<hr>';
                                            
                                            if ( verificaContratosExistente( $dadosLogin, $data[1] ) ) {
                                                
                                                $countError++;
                                                foreach ($dadosContrato['error'] AS $kDadosContrato => $vDadosContrato) {
                                                    $detailErrors[$linha][] = 'Cliente com contrato já existente';
                                                }
                                                setFileResult($fileParceiroErrors, utf8_decode($data[0].';'.$data[1].';'.$data[2].';'.$data[3].';'.$data[4].';'.$data[5].';'.$data[6].';'.$data[7].';'.$data[8].';'.$data[9].';'.$data[10].';'.$data[11].';'.$data[12].';'.strip_tags(implode(' | ', $detailErrors[$linha]))) );
                                                
                                            }
                                            else {
                                            
                                                // Insere o Cliente
                                                $dadosCliente = insereCliente($dadosLogin, $validDados);
                                               
                                                
                                                if ($dadosCliente['status'] == true) {
                                                
                                               
                                                
                                                    // Insere o Contrato
                                                    $dadosContrato = insereContrato($dadosLogin, $dadosCliente['id'], $validDados['email'], $validDados['mac1'], $session['id_parceiro']);
                                                    
                                                    if ($dadosContrato['status'] == true) {
                                                    
                                                        $countSuccess++;
                                                        
                                                        $listForSuporte .= ' - Cliente: '.$dadosCliente['id'].' - '.utf8_decode( $validDados['nome'] ).PHP_EOL ;
                                                        
                                                        setFileResult($fileParceiroSuccess, utf8_decode($data[0].';'.$data[1].';'.$data[2].';'.$data[3].';'.$data[4].';'.$data[5].';'.$data[6].';'.$data[7].';'.$data[8].';'.$data[9].';'.$data[10].';'.$data[11].';'.$data[12]) );
                                                    
                                                    }
                                                    else {
                                                        $countError++;
                                                        foreach ($dadosContrato['error'] AS $kDadosContrato => $vDadosContrato) {
                                                            $detailErrors[$linha][] = $vDadosContrato;
                                                        }
                                                        setFileResult($fileParceiroErrors, utf8_decode($data[0].';'.$data[1].';'.$data[2].';'.$data[3].';'.$data[4].';'.$data[5].';'.$data[6].';'.$data[7].';'.$data[8].';'.$data[9].';'.$data[10].';'.$data[11].';'.$data[12].';'.strip_tags(implode(' | ', $detailErrors[$linha]))) );
                                                    }
                                                
                                          
                                                
                                                }
                                                else {
                                                    $countError++;
                                                    foreach (@$dadosCliente['error'] AS $kDadosCliente => $vDadosCliente) {
                                                        $detailErrors[$linha][] = $vDadosCliente;
                                                    }
                                                    setFileResult($fileParceiroErrors, utf8_decode($data[0].';'.$data[1].';'.$data[2].';'.$data[3].';'.$data[4].';'.$data[5].';'.$data[6].';'.$data[7].';'.$data[8].';'.$data[9].';'.$data[10].';'.$data[11].';'.$data[12].';'.strip_tags(implode(' | ', $detailErrors[$linha]))) );
                                                }
                                            
                                            }
                                            
                                            //
                                            break;
                                            
                                        case 'error':
                                            $countError++;
                                            $detailErrors[$linha] = $validDados['errors'];
                                            setFileResult($fileParceiroErrors, utf8_decode($data[0].';'.$data[1].';'.$data[2].';'.$data[3].';'.$data[4].';'.$data[5].';'.$data[6].';'.$data[7].';'.$data[8].';'.$data[9].';'.$data[10].';'.$data[11].';'.$data[12].';'.strip_tags(implode(' | ', $validDados['errors']) )) );
                                            break;
                                            
                                            
                                        case 'ignored':
                                            $countRepeated++;
                                            $detailRepeated[$linha] = $validDados['detailIgnored'];
                                            setFileResult($fileParceiroIgnorados, utf8_decode($data[0].';'.$data[1].';'.$data[2].';'.$data[3].';'.$data[4].';'.$data[5].';'.$data[6].';'.$data[7].';'.$data[8].';'.$data[9].';'.$data[10].';'.$data[11].';'.$data[12].';'.strip_tags(implode(' | ', $validDados['detailIgnored']) )) );
                                            break;
                                            
                                    }
                                    
                                }
                                
                            }
                            fclose($handle);
		                  
                            
                            $detailErrorsMsg  = '<div class="grid-striped">';
                            foreach ($detailErrors AS $key => $val) {
                                $detailErrorsMsg .= '<div class="row p-1 rowHover">';
                                $detailErrorItens = '';
                                if (is_array($val) || is_object($val)) {
                                    foreach ($val as $v) {
                                        $detailErrorItens .= ($detailErrorItens != '' ? '<br>':'').' - '.$v;
                                    }
                                }
                                $detailErrorsMsg .= ' <div class="col-12 col-sm-3 col-md-2 col-lg-2 col-xl-1"><b>Linha: '.$key.'</b></div><div class="col">'.$detailErrorItens.'</div>';
                                $detailErrorsMsg .= '</div>';
                            }
                            $detailErrorsMsg .= '</div>';
                            
                            
                            
                            $detailRepeatedMsg  = '<div class="grid-striped">';
                            foreach ($detailRepeated AS $key => $val) {
                                $detailRepeatedMsg .= '<div class="row p-1 rowHover">';
                                $detailRepeatedMsgItens = '';
                                if (is_array($val) || is_object($val)) {
                                    foreach ($val as $v) {
                                        $detailRepeatedMsgItens .= ($detailRepeatedMsgItens != '' ? '<br>':'').' - '.$v;
                                    }
                                }
                                $detailRepeatedMsg .= ' <div class="col-12 col-sm-3 col-md-2 col-lg-2 col-xl-1"><b>Linha: '.$key.'</b></div><div class="col">'.$detailRepeatedMsgItens.'</div>';
                                $detailRepeatedMsg .= '</div>';
                            }
                            $detailRepeatedMsg .= '</div>';
                            
                            
                            $linkFileSuccess = '';
                            $linkFileError = '';
                            $linkFileIgnored = '';
                            
                            if ( file_exists($fileParceiroSuccess) ) {
                                
                                $conteudo = file_get_contents($fileParceiroSuccess);
                                $myfile = fopen($fileParceiroSuccess, "r+");
                                fwrite($myfile, utf8_decode("Razão social/Nome;CNPJ/CPF;Dt nascimento;Endereço;Complemento;Número;Bairro;UF;Cidade;CEP;Fone1;Fone2;E-mail")."\n".$conteudo);
                                fclose($myfile);
                                $linkFileSuccess = ' (<a href="'.str_replace('../importacao/', '', $fileParceiroSuccess).'">Salvar/Baixar arquivo CSV</a>)';
                                
                            }
                            if ( file_exists($fileParceiroErrors) ) {

                                $conteudo = file_get_contents($fileParceiroErrors);
                                $myfile = fopen($fileParceiroErrors, "r+");
                                fwrite($myfile, utf8_decode("Razão social/Nome;CNPJ/CPF;Dt nascimento;Endereço;Complemento;Número;Bairro;UF;Cidade;CEP;Fone1;Fone2;E-mail;Mensagem do Erro")."\n".$conteudo);
                                fclose($myfile);
                                $linkFileError = ' (<a href="'.str_replace('../importacao/', '', $fileParceiroErrors).'">Salvar/Baixar arquivo CSV</a>)';
                                
                            }
                            if ( file_exists($fileParceiroIgnorados) ) {
                                
                                $conteudo = file_get_contents($fileParceiroIgnorados);
                                $myfile = fopen($fileParceiroIgnorados, "r+");
                                fwrite($myfile, utf8_decode("Razão social/Nome;CNPJ/CPF;Dt nascimento;Endereço;Complemento;Número;Bairro;UF;Cidade;CEP;Fone1;Fone2;E-mail;Mensagem")."\n".$conteudo);
                                fclose($myfile);
                                $linkFileIgnored = ' (<a href="'.str_replace('../importacao/', '', $fileParceiroIgnorados).'">Salvar/Baixar arquivo CSV</a>)';
                                
                            }
                            
		                  
                            $resultImport  = '<h6><b>Detalhes da importação</b></h6>';
                            $resultImport .= '&nbsp;<ul>';
                            
                            $resultImport .= ' <li class="text-success"><b>Contratos gerados: '.$countSuccess.' Novos'.$linkFileSuccess.'</b><br><i>Todos os contratos novos inseridos, os clientes receberão automáticamente em seu e-mail o processo para configuração da senha de acesso ao Aplicativo.</i><br>&nbsp;</li>';
                            $resultImport .= ' <li class="text-danger">Registros negados: '.$countError.$linkFileError.'<br>'.$detailErrorsMsg.'<br>&nbsp;</li>';
                            $resultImport .= ' <li class="text-info">Registros ignorados: '.$countRepeated.$linkFileIgnored.'<br>'.$detailRepeatedMsg.'<br>&nbsp;</li>';
                            
                            
                            $resultImport .= '</ul>';
                            
                            
                            
                            // Detalhes do Rodapé
                            $detailImport  = '<b>Total de Registros: '.($countSuccess+$countError).'</b>';
                            //$html = str_replace('{detailImport}', $detailImport, $html);
		                
                            
                            @unlink('../importacao/files/'.$renameFile.'.csv');
                            
                            if ( $listForSuporte != '' ) {
                                
                                
                                /*require_once "../../_global/PHPMailer/PHPMailerAutoload.php";
                                
                                $mail = new PHPMailer();
                                
                                $mail->isSMTP();
                                $mail->Host = "mail.ole.net.br";
                                $mail->SMTPSecure = "tls";
                                $mail->Port = 587;
                                $mail->CharSet = "UTF-8";
                                $mail->Encoding = 'base64';
                                
                                $mail->SMTPOptions = array ( 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true ) );
                                $mail->SMTPAuth = true;
                                
                                $mail->Username = "noreply@oletv.net.br";
                                $mail->Password = "@b!LbPywn1z4oTR!;jzg9zFR@3nUuef!";
                                
                                $mail->From = "noreply@oletv.net.br";
                                $mail->FromName = "Olé TV";// iconv('ISO-8859-1', 'UTF-8', "Olé TV");
                                $mail->Subject = "Importação do parceiro ".$session['name_parceiro']." (".$session['id_parceiro'].")";// iconv('ISO-8859-1', 'UTF-8', "Importação do parceiro ".$session['id_parceiro']);
                                
                                $mail->isHTML(false);
                                
                                $mail->addAddress('suporte@futurasites.com.br', 'Futura Sites');
                                
                                $mail->Body = iconv('ISO-8859-1', 'UTF-8', 'Listagem de Clientes importados pelo parceiro ID '.$session['id_parceiro'].' ('.$session['name_parceiro'].'): '.PHP_EOL.PHP_EOL.$listForSuporte);
                                
                                $mail->send();*/
                                
                                
                            }
                        }
		            }
		        }
		    }
		    
		    
		    
		    
		    
		    
		    
		    $html = file_get_contents('resultImport.html');
		    
		    $html = str_replace('{fileImport}', $fileImport, $html);
		    $html = str_replace('{classResult}', 'bg-success', $html);
		    
		    
		    $html = str_replace('{resultImport}', $resultImport, $html);
		    
		    $html = str_replace('{detailImport}', $detailImport, $html);
		    
		    $html = str_replace('{buttonFooter}', '', $html);
		    
		    //echo 'Nada por enquanto';
		      
		    echo $html;
		}
		
		else {
		    $html = file_get_contents('sendImport.html');//include 'sendImport.html';
		    
		    if ( $_SERVER['HTTP_HOST'] == 'localhost') {
                $html = str_replace('{keyapi}', '6648f1f895bff3d517d3c74acee882c2af93a330',/* 'b8f1587de45f33742d5ac8559b048234431f4bbe',*/ $html);
                $html = str_replace('{loginapi}', 'ole', $html);
                $html = str_replace('{passapi}', 'testehomologacao',/* '0c81db6dcf47da0424d6',*/ $html);
		    }
		    else {
		        $html = str_replace('{keyapi}', '', $html);
                $html = str_replace('{loginapi}', '', $html);
                $html = str_replace('{passapi}', '', $html);
		    }
		    echo $html;
		}
	
        ?>	
	
	
	
		
	
	
	</div>




<script>

//sendForm

$(document).ready(function() {

	$(document).on('click', '#sendForm ', function(e) {
		$('#sendForm').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...');
		$('#sendForm').prop('disabled', true);
		$('#showSend').removeClass('d-none');
		$('form').submit();
	});


	$(document).on('click', '#confirmForm ', function(e) {
		$('#confirmForm').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importando...');
		$('#confirmForm').prop('disabled', true);
		//$('#showSend').removeClass('d-none');
	});
	
});

</script>


</body>
</html>






