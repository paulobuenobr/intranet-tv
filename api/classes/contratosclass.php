<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\FrameDecorator\NullFrameDecorator;


class contratosclass extends loadclass {
    
    public $returnClass = array();
    public $urlClass = array();
    public $idParceiro = null;
    
    function __construct($idParceiro='') {
        $this->returnClass['retorno_status'] = false;
        $this->idParceiro = $idParceiro;
        
        $this->urlClass = $this->getParametros();
        if ( trim( $this->urlClass[1]) == '') {
            $this->returnClass['error'] = 'Nenhum método definido!';
        }
        else {
            if ( @method_exists(contratosclass, $this->urlClass[1]) ) { eval(" contratosclass::".$this->urlClass[1]."(); "); }
            else    { $this->returnClass['error'] = "Método '".$this->urlClass[1]."' inválido!"; }
        }
        return $this->returnClass;
    }
    
    
    
    function listar () {
        if (intval(@$this->urlClass[2]) === 0 || empty($this->urlClass[2])) {
            $this->returnClass['error'] = "ID do cliente não informado!";
            return;
        }
        $cliente_id = intval($this->urlClass[2]);
        
        // Busca o Cliente do ID
        $this->conectaDB();
        $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".$cliente_id."' and clo_id = '".$this->idParceiro."'");
        $dadosCliente = $this->db->resultadoArray($queryCliente);
        
        if (!isset($dadosCliente['cli_id'])) {
            $this->returnClass['error'] = "ID do cliente não localizado!";
            return;
        }
        
        $lista_status = "1,2,3,4,5,11,12,13,14,15,16,17,18,19,20,21,22";
        require_once '_modulos/contratos/contratos.class.inc.php';
        $intranetContratos = new contratos($this->db, null, false);
        $contratos = $intranetContratos->buscaClientesContratos($cliente_id, $lista_status);
        
        $listContratos = array();
        foreach ($contratos AS $k => $contrato) {
            $listContratos[$k]['id'] = $contrato['id'];
            $listContratos[$k]['codigo'] = strip_tags( $contrato['codigo'] );
            $listContratos[$k]['tipo'] = $this->textCharsetOutput( $contrato['tipo'] );
            $listContratos[$k]['servico'] = $this->textCharsetOutput( $contrato['servico'] );
            $listContratos[$k]['data_geracao'] = $contrato['data_geracao'];
            $listContratos[$k]['data_ativacao'] = $contrato['data_ativacao'];
            $listContratos[$k]['data_anulacao'] = $contrato['data_anulacao'];
            $listContratos[$k]['data_cancelamento'] = $contrato['data_cancelamento'];
            $listContratos[$k]['status'] = $this->textCharsetOutput( str_replace('<br>', ' ', $contrato['status']) );
            $listContratos[$k]['assinaturas'] = $this->getAssinaturasContrato( $contrato['id'] );
            $listContratos[$k]['usuario'] = $this->listaEmailsIPTV( $contrato['id'] );
        }
        $this->returnClass['retorno_status'] = true;
        $this->returnClass['contratos'] = $listContratos;
        return $this->returnClass;
    }

    /**
     * Summary of listaEmailsIPTV
     * @param mixed $contrato_id
     * @return array
     */
    function listaEmailsIPTV ($contrato_id) {
        $this->conectaDB();
        $queryEmails = $this->db->query("SELECT cip_usuario usuario FROM tab_clientes_iptv WHERE cip_usuario IS NOT NULL AND cor_id = $contrato_id;");
        $listEmails = array();
        $k = 0;
        while (($email = $this->db->resultadoArray($queryEmails)) != false) {
            if($email['usuario'] != '' && $email['usuario'] != null) {
                $listEmails[$k] = $email['usuario'];
                $k++;
            } 
        }
        return $listEmails;
    }
    
    function getAssinaturasContrato($contrato_id) {
        $this->conectaDB();
        $queryAssinaturas = $this->db->query("
			SELECT
				tab_assinaturas.ass_id AS id,
				tab_clientes_iptv.cip_pontos AS pontos,
				tab_clientes_iptv.cip_dispositivos AS dispositivos,
				tab_clientes_iptv.cip_pacotes AS pacotes,
				tab_clientes_iptv.cip_catalogs AS catalogs,
				tab_clientes_iptv.cip_publicado AS publicado,
				tab_planos.pla_id AS plano_id,
				tab_planos.pla_nome AS plano_nome,
				tab_clientes_iptv_status.cis_id AS status_id,
				tab_clientes_iptv_status.cis_nome AS status_nome
			FROM
                tab_assinaturas
                INNER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
                INNER JOIN tab_clientes_iptv ON (tab_clientes_iptv.cor_id = rel_ass_cor_estacontido.cor_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN tab_clientes_iptv_status ON (tab_clientes_iptv.cis_id = tab_clientes_iptv_status.cis_id)
			WHERE
				rel_ass_cor_estacontido.cor_id = $contrato_id
            GROUP BY tab_assinaturas.ass_id");
        $listAssinaturas = array();
        $k = 0;
        while (($assinatura = $this->db->resultadoArray($queryAssinaturas)) != false) {
            $listAssinaturas[$k]['id'] = $assinatura['id'];
            $listAssinaturas[$k]['plano'] = $this->textCharsetOutput( $assinatura['plano_nome'] );
            $listAssinaturas[$k]['box'] = $assinatura['pontos'];
            $listAssinaturas[$k]['dispositivos'] = $assinatura['dispositivos'];
            $listAssinaturas[$k]['status_assinatura'] = $this->textCharsetOutput( $assinatura['status_nome'] );
            $listAssinaturas[$k]['equipamentos'] = $this->getComodatos( $assinatura['id'] );
            $k++;
        }
        return $listAssinaturas;
    }
    
    function getAssinaturasContratoMultiservise($contrato_id) {
        $this->conectaDB();
        $queryAssinaturas = $this->db->query("
			SELECT
				tab_assinaturas.ass_id AS id,
				tab_planos.pla_id AS plano_id,
				tab_planos.pla_nome AS plano_nome
			FROM
                tab_assinaturas
                INNER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
                INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				rel_ass_cor_estacontido.cor_id = $contrato_id
            GROUP BY tab_assinaturas.ass_id");
        $listAssinaturas = array();
        $k = 0;
        while (($assinatura = $this->db->resultadoArray($queryAssinaturas)) != false) {
            $listAssinaturas[$k]['id'] = $assinatura['id'];
            $listAssinaturas[$k]['plano'] = $this->textCharsetOutput( $assinatura['plano_nome'] );
            $k++;
        }
        return $listAssinaturas;
    }
    
    
    function getComodatos($ass_id) {
        $queryEquipamento = $this->db->query("
			SELECT
				tab_clientes_comodatos.clc_id AS id,
				tab_clientes_comodatos.clc_mac_address AS mac_address,
				tab_equipamentos.equ_nome AS equipamento,
				tab_equipamentos_modelos.eqm_nome AS equipamento_modelo,
				DATE_FORMAT(tab_clientes_comodatos.clc_data_retirada, '%d/%m/%Y') AS data_retirada,
				DATE_FORMAT(tab_clientes_comodatos.clc_data_inicio, '%d/%m/%Y') AS data_inicio,
				DATE_FORMAT(tab_clientes_comodatos.clc_data_termino, '%d/%m/%Y') AS data_termino,
				tab_clientes_comodatos_status.ccs_nome AS status_nome
			FROM
				tab_clientes_comodatos
				INNER JOIN tab_clientes_comodatos_status ON (tab_clientes_comodatos.ccs_id = tab_clientes_comodatos_status.ccs_id)
				INNER JOIN tab_equipamentos ON (tab_clientes_comodatos.equ_id = tab_equipamentos.equ_id)
				LEFT JOIN tab_equipamentos_modelos ON (tab_clientes_comodatos.eqm_id = tab_equipamentos_modelos.eqm_id)
				INNER JOIN tab_assinaturas ON (tab_clientes_comodatos.ass_id = tab_assinaturas.ass_id)
			WHERE
				tab_clientes_comodatos.ass_id = ".$ass_id." && tab_clientes_comodatos_status.ccs_id IN (1,2)" );
        $listEquipamentos = array();
        $k = 0;
        while (($equipamento = $this->db->resultadoArray($queryEquipamento)) != false) {
            $listEquipamentos[$k]['id'] = $equipamento['id'];
            $listEquipamentos[$k]['equipamento'] = $this->textCharsetOutput( $equipamento['equipamento'] );
            $listEquipamentos[$k]['equipamento_modelo'] = $this->textCharsetOutput( $equipamento['equipamento_modelo'] );
            $listEquipamentos[$k]['mac'] = $this->textCharsetOutput( $equipamento['mac_address'] );
            $listEquipamentos[$k]['data_inicio'] = $equipamento['data_inicio'];
            $listEquipamentos[$k]['data_termino'] = $equipamento['data_termino'];
            $listEquipamentos[$k]['status_equipamento'] = $this->textCharsetOutput( $equipamento['status_nome'] );
            $k++;
        }
        return $listEquipamentos;
    }
    
    function recuperarsenha() {
        
        return $this->alterarusuario(true);
    }
    
    function alterarusuario($recuperar = false) {
        $idContrato = intval( @$this->urlClass[2] );
        if ($idContrato == 0) {
            $this->returnClass['error'] = "ID do contrato não informado ou inválido!";
            return;
        }
        
        $emailAntigo = null;
        $temEmailAntigo = false;
        if(!empty($_POST['email_antigo'])) {
            $emailAntigo = $_POST['email_antigo'];
            $temEmailAntigo = true;
        }

        $_POST["email_usuario"] = addslashes(trim($_POST["email_usuario"]));
        
        if ($_POST["email_usuario"] == '') {
            $this->returnClass['error'] = "E-mail para o usuário não informado!";
            return;
        }
        
        if (!filter_var($_POST["email_usuario"], FILTER_VALIDATE_EMAIL)) {
            $this->returnClass['error'] = "Usuário {$_POST["email_usuario"]} não é um e-mail válido!";
            return;
        }
        
        if (!empty($_POST['email_antigo']) && !filter_var($_POST["email_antigo"], FILTER_VALIDATE_EMAIL)) {
            $this->returnClass['error'] = "Usuário {$_POST["email_antigo"]} não é um e-mail válido!";
            return;
        }
        
        define('DIR_RAIZ', '../../');
        define('DIR_GLOBAL', '../../');
        
        $this->conectaDB();
        require_once '../_modulos/contratos/contratos.class.inc.php';
        $intranetContratos = new contratos($this->db, null, false);
        
        $dados = $intranetContratos->buscaDadosContrato($idContrato);
        if ($dados['cliente'] > 0) {
            $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".$dados['cliente']."' and clo_id = '".$this->idParceiro."'");
            $dadosCliente = $this->db->resultadoArray($queryCliente);
            
            if (!isset($dadosCliente['cli_id'])) {
                $this->returnClass['error'] = "ID do contrato não pertence ao seu cliente!";
                return;
            }
            
            
            require_once '../_modulos/clientes_iptv/clientes_iptv.class.inc.php';
            $intranetClienteIPTV = new clientes_iptv($this->db, null, false);
            
            if($temEmailAntigo) {
                $queryCliente = $this->db->query("SELECT cip_id AS id, cli_id FROM tab_clientes_iptv WHERE cor_id='{$idContrato}' AND cip_usuario LIKE '{$emailAntigo}' AND cis_id = 1");
            } else {
                if($recuperar) {
                    $queryCliente = $this->db->query("SELECT cip_id AS id, cli_id FROM tab_clientes_iptv WHERE cor_id='{$idContrato}' AND cip_usuario LIKE '{$_POST['email_usuario']}' AND cis_id = 1");
                } else {
                    $queryCliente = $this->db->query("SELECT cip_id AS id, cli_id FROM tab_clientes_iptv WHERE cor_id='{$idContrato}' AND cis_id = 1");
                }
            }
            $dadosIPTV = $this->db->resultadoArray($queryCliente);
            $linhas = $this->db->linhas($queryCliente);

            if($linhas > 1 && !$temEmailAntigo) {
                $this->returnClass['error'] = "Seu contrato {$idContrato} possui multiplos pontos, precisa do 'email_antigo' ";
                return;
            }

            $dadosClienteId = $intranetClienteIPTV->buscaIdPorUsuario($_POST["email_usuario"]);
            if ( $dadosClienteId != null ) {
                
                if ($dadosIPTV['cli_id'] != $dadosClienteId) {
                    $this->returnClass['error'] = "Usuário {$_POST["email_usuario"]} já está em uso!";
                    return;
                }
        		else {
                    
                    $sqlConsultaUltimaRecuperacao = $this->db->query("SELECT cip_recuperacao_datahora FROM tab_clientes_iptv WHERE cor_id='{$idContrato}' AND cis_id = 1 ORDER BY cip_recuperacao_datahora DESC LIMIT 1");
                    $dadosUltimaRecuperacao = $this->db->resultadoArray($sqlConsultaUltimaRecuperacao);
                    if ($dadosUltimaRecuperacao['cip_recuperacao_datahora'] > date('Y-m-d H:i:s', strtotime('-5 minutes'))) {
                        $this->returnClass['error'] = "Você já recebeu um e-mail de recuperação de senha em menos de 5 minutos!";
                        return;
                    }

                    if ( $intranetClienteIPTV->recuperarSenha($dadosIPTV['id']) ) {
                        $intranetClienteIPTV->registraComentario($dadosIPTV['id'], $this->textCharsetInput("<b>Ação</b>: Envio de e-mail: Foi enviado e-mail para <b>{$_POST["email_usuario"]}</b>."), $_SESSION["usuario"]);   
                        $this->returnClass['retorno_status'] = true;
                        $this->returnClass['msg'] = "Foi enviado para o e-mail {$_POST["email_usuario"]} suas instruções!";
                        return;                 
                    }
                    

                    $this->db->query("UPDATE tab_clientes_iptv SET cip_recuperacao_datahora = NOW() WHERE cor_id='{$idContrato}'");
                    
                    $this->returnClass['error'] = "Falha no disparo do e-mail!";
                    return;

                }
            }
            
            // Chama troca intranet
                            
            $this->db->query("
						UPDATE
							tab_clientes_iptv
						SET
							cip_usuario = '{$_POST["email_usuario"]}',
							cip_publicado = 0
						WHERE
							cip_id = ".$dadosIPTV['id']);
            
            
            $intranetClienteIPTV->registraComentario($dadosIPTV['id'], $this->textCharsetInput("<b>Ação</b>: Alteração de usuário: de <b>{$_POST["email_antigo"]}</b> para <b>{$_POST["email_usuario"]}</b>."), $_SESSION["usuario"]);
            
            
            require_once '../_modulos/clientes_iptv/clientes_iptv.publicacao.class.inc.php';
            $publicacao = new clientes_iptv_publicacao($this->db);
            if ($publicacao->publicar($dadosIPTV['id'])) {
                $intranetClienteIPTV->recuperarSenha($dadosIPTV['id']);
            }
            
            $this->returnClass['retorno_status'] = true;
            $this->returnClass['msg'] = "Foi enviado para o e-mail {$_POST["email_usuario"]} suas instruções!";// (No ambiente de homologação o e-mail não é enviado)
            return;
        }
        else {
            $this->returnClass['error'] = "ID do contrato não localizado!";
            return;
        }
        
    }
       
    function alterarsenha() {
        
        $idContrato = intval(@$this->urlClass[2]);
        if ($idContrato == 0) {
            $this->returnClass['error'] = "ID do contrato não informado ou inválido!";
            return;
        }
        
        $_POST["email_usuario"] = addslashes(trim($_POST["email_usuario"]));
        
        if ($_POST["email_usuario"] == '') {
            $this->returnClass['error'] = "E-mail para o usuário não informado!";
            return;
        }
        
        if (!filter_var($_POST["email_usuario"], FILTER_VALIDATE_EMAIL)) {
            $this->returnClass['error'] = "Usuário {$_POST["email_usuario"]} não é um e-mail válido!";
            return;
        }        
         
        $_POST["senha"] = addslashes(trim($_POST["senha"]));        
        if ($_POST["senha"] == '') {
            $this->returnClass['error'] = "Senha não informado!";
            return;
        }
        
        if (strlen($_POST["senha"]) < 6) {
            $this->returnClass['error'] = "Senha inválida!";
            return;
        }      

        define('DIR_RAIZ', '../../');
        define('DIR_GLOBAL', '../../');
        
        $this->conectaDB();
        require_once '../_modulos/contratos/contratos.class.inc.php';
        $intranetContratos = new contratos($this->db, null, false);

        $dados = $intranetContratos->buscaDadosContrato($idContrato);

        if ($dados['cliente'] > 0) {
			
            $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".addslashes($dados['cliente'])."' and clo_id = '".addslashes($this->idParceiro)."'");
            $dadosCliente = $this->db->resultadoArray($queryCliente);
            
            if (!isset($dadosCliente['cli_id'])) {
                $this->returnClass['error'] = "ID do contrato não pertence ao seu cliente!";
                return;
            }            
            
            require_once '../_modulos/clientes_iptv/clientes_iptv.class.inc.php';
            $intranetClienteIPTV = new clientes_iptv($this->db, null, false);
            
            require_once '../_modulos/clientes_iptv/clientes_iptv.publicacao.class.inc.php';
            $publicacao = new clientes_iptv_publicacao($this->db);
            
            $queryCliente = $this->db->query("SELECT cip_id AS id, cli_id FROM tab_clientes_iptv WHERE cor_id='{$idContrato}' AND cis_id = 1");
            $dadosIPTV = $this->db->resultadoArray($queryCliente);
            
            $dadosClienteId = $intranetClienteIPTV->buscaIdPorUsuario($_POST["email_usuario"]);
			
            if ($dadosClienteId != null) {
				
                if ($dadosIPTV['cli_id'] != $dadosClienteId) {
                    $this->returnClass['error'] = "Usuário {$_POST["email_usuario"]} já está em uso!";
                    return;
                } else {
                    if ($publicacao->alterarSenha($dadosIPTV['id'], $_POST["senha"])) {
                        $intranetClienteIPTV->registraComentario($dadosIPTV['id'], $this->textCharsetInput("<b>Ação</b>: Alteração de senha via API."), $_SESSION["usuario"]);
						$this->returnClass['retorno_status'] = true;
						$this->returnClass['msg'] = "Senha alterada com sucesso!";
						return;                 
					}                    
					$this->returnClass['error'] = "Falha ao alterar senha!";
					return;
                }
				
            } else {
								
				$this->db->query("
						UPDATE
							   tab_clientes_iptv
						SET
								cip_usuario = '{$_POST["email_usuario"]}',
								cip_publicado = 0
						WHERE
								cip_id = ".$dadosIPTV['id']);
				
				if ($publicacao->publicar($dadosIPTV['id'])) {
					if ($publicacao->alterarSenha($dadosIPTV['id'], $_POST["senha"])) {
						$intranetClienteIPTV->registraComentario($dadosIPTV['id'], $this->textCharsetInput("<b>Ação</b>: Alteração de usuário ({$intranetClienteIPTV->dados_item["usuario"]}) e alteração de senha via API."));
						$this->returnClass['retorno_status'] = true;
						$this->returnClass['msg'] = "Senha alterada com sucesso!";
						return;
					}
				}
				
			}
            
            $this->returnClass['error'] = "Falha ao alterar senha!";
            return;
			
        } else {
            $this->returnClass['error'] = "ID do contrato não localizado!";
            return;
        }
        
    }

    function pontosregistrados() {
        
        $idContrato = intval( @$this->urlClass[2] );
        if ($idContrato == 0) {
            $this->returnClass['error'] = "ID do contrato não informado ou inválido!";
            return;
        }
        
        define('DIR_RAIZ', '../../');
        define('DIR_GLOBAL', '../../');
        
        $this->conectaDB();
        require_once '../_modulos/contratos/contratos.class.inc.php';
        $intranetContratos = new contratos($this->db, null, false);
        
        $dados = $intranetContratos->buscaDadosContrato($idContrato);
        if ($dados['cliente'] > 0) {
            $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".$dados['cliente']."' and clo_id = '".$this->idParceiro."'");// and (cls_id='1' OR cls_id='2')
            $dadosCliente = $this->db->resultadoArray($queryCliente);
            
            if (!isset($dadosCliente['cli_id'])) {
                $this->returnClass['error'] = "ID do contrato não pertence ao seu cliente!";
                return;
            }            
        }
        else {
            $this->returnClass['error'] = "ID do contrato não localizado!";
            return;
        }
        
        
        require_once '../_modulos/clientes_iptv/clientes_iptv.class.inc.php';
        require_once '../_modulos/clientes_iptv/clientes_iptv.publicacao.class.inc.php';
        $intranetClienteIPTV = new clientes_iptv($this->db, null, false);
        
        $IPTV = $intranetClienteIPTV->buscaIptvPorContrato($idContrato);
        
        if ( count($IPTV) == 0 ) {
            $this->returnClass['error'] = "Nenhum ponto registrado no contrato!";
            return;
        }
        
        $intranetPublicacao = new clientes_iptv_publicacao($this->db, null, false);
        $dados_status =  $intranetPublicacao->status($IPTV["id"]);
        
        //print_r($dados_status);
        $listPontos = array();
        if(is_array($IPTV)) {
            for($j=0; $j < count($IPTV); $j++) {
                $pos = 0;
                $dados_status =  $intranetPublicacao->status($IPTV[$j]["id"]);
                for ($i = 0; $i < count($dados_status); $i++) {
                    if ( $dados_status[$i]['remotion'] == '0' ) {
                    
                        //$listPontos[$i]['id'] = $dados_status[$i]['id'];
                        $listPontos[$pos]['marca'] = $dados_status[$i]['brand'];
                        $listPontos[$pos]['modelo'] = $dados_status[$i]['model'];
                        $listPontos[$pos]['mac'] = $dados_status[$i]['macAddress'];
                        $listPontos[$pos]['status'] = ($dados_status[$i]['online']==true?'online':'offline');
                        $pos++;
                    }
                }
            }
        } else {
            $pos = 0;
            $dados_status =  $intranetPublicacao->status($IPTV["id"]);
            for ($i = 0; $i < count($dados_status); $i++) {
                if ( $dados_status[$i]['remotion'] == '0' ) {
                    //$listPontos[$i]['id'] = $dados_status[$i]['id'];
                    $listPontos[$pos]['marca'] = $dados_status[$i]['brand'];
                    $listPontos[$pos]['modelo'] = $dados_status[$i]['model'];
                    $listPontos[$pos]['mac'] = $dados_status[$i]['macAddress'];
                    $listPontos[$pos]['status'] = ($dados_status[$i]['online']==true?'online':'offline');
                    $pos++;
                }
            }
        }
        
        if (  count($listPontos) == 0 ) {
            $this->returnClass['error'] = "Nenhum equipamento encontrado!";
            return;
        }
        
        $this->returnClass['retorno_status'] = true;
        $this->returnClass['pontos'] = $listPontos;
        
    }
    
    
    function criarUsuarioMobile($idContrato, $mail) {
        $this->conectaDB();
        define('DIR_RAIZ', '../../');
        define('DIR_GLOBAL', '../../');
        
        //echo 'passou - '.$mail;
        
        require_once '../_modulos/clientes_iptv/clientes_iptv.class.inc.php';
        $intranetClienteIPTV = new clientes_iptv($this->db, null, false);
        
        if ($intranetClienteIPTV->buscaIdPorUsuario($mail) != null) {
            //$this->returnClass['error'] = "Usuário {$mail} já está em uso!";
            return array('status'=>false, 'error'=>"Usuário {$mail} já está em uso!");
        }
        
        $queryCliente = $this->db->query("SELECT cip_id AS id, cip_usuario, cip_dispositivos, cip_pontos FROM tab_clientes_iptv WHERE cor_id='$idContrato'");
        
        require_once '../_modulos/clientes_iptv/clientes_iptv.publicacao.class.inc.php';
        $publicacao = new clientes_iptv_publicacao($this->db);  

        $podeSepararPontos = $this->podeSepararPontos($this->idParceiro);
        if($podeSepararPontos || is_array($mail)) {
            $dadosIPTV = array();
            $i = 0;
            while (($dados = $this->db->resultadoArray($queryCliente)) != false) {
                
                $dadosIPTV[] = $dados;
                if(!empty($mail[$i])) {
                    $email = $mail[$i];
                    $dispositivos = 1;
                    $ponto = 0;
                } else {
                    $email = "";
                    $ponto = 1;
                    $dispositivos = 0;
                }
                
                $this->db->query("
                                UPDATE
                                    tab_clientes_iptv
                                SET
                                    cip_usuario = '".$email."',
                                    cip_publicado = 0,
                                    cip_publicado = 0,
                                    cip_pontos = $ponto,
                                    cip_dispositivos = $dispositivos,
                                    cip_recuperacao_datahora = NOW(),
                                    cip_recuperacao_hash = NULL
                                WHERE
                                    cip_id = ".$dados['id']);

                $intranetClienteIPTV->registraComentario($dados['id'], $this->textCharsetInput("<b>Ação</b>: Alteração de usuário: de <b>{$dados["cip_usuario"]}</b> para <b>{$email}</b>."), $_SESSION["usuario"]);

                if ($publicacao->publicar($dados['id'])) {
                    $intranetClienteIPTV->recuperarSenha($dados['id'], true);
                }
                $i++;
            }
        } else {

            $dadosIPTV = $this->db->resultadoArray($queryCliente);
            // Chama troca intranet
            $this->db->query("
                            UPDATE
                                tab_clientes_iptv
                            SET
                                cip_usuario = '".$mail."',
                                cip_publicado = 0,
                                cip_recuperacao_datahora = NOW(),
                                cip_recuperacao_hash = NULL
                            WHERE
                                cip_id = ".$dadosIPTV['id']);
            
            $intranetClienteIPTV->registraComentario($dadosIPTV['id'], $this->textCharsetInput("<b>Ação</b>: Alteração de usuário: de <b>{$dadosIPTV["cip_usuario"]}</b> para <b>{$_POST["email_usuario"]}</b>."), $_SESSION["usuario"]);
            
            if ($publicacao->publicar($dadosIPTV['id'])) {
                $intranetClienteIPTV->recuperarSenha($dadosIPTV['id'], true);
            }
        }
        
        return array('status'=>true);
    }
    
    function bloqueio() {
        
        //print_r( $_SERVER );
        //print_r( $_POST );
        
        // Verifica se ID foi informado
        $idContrato = intval( @$this->urlClass[2] );
        if ($idContrato == 0) {
            $this->returnClass['error'] = "ID do contrato não informado ou inválido!";
            return;
        }
        
        define('DIR_RAIZ', '../../');
        define('DIR_GLOBAL', '../../');
        
        $this->conectaDB();
        require_once '../_modulos/contratos/contratos.class.inc.php';
        require_once('_global/funcoes.class.inc.php');
        $intranetContratos = new contratos($this->db, null, false);
        
        $dados = $intranetContratos->buscaDadosContrato($idContrato);
        if ($dados['cliente'] > 0) {
            $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".$dados['cliente']."' and clo_id = '".$this->idParceiro."'");// and (cls_id='1' OR cls_id='2')
            $dadosCliente = $this->db->resultadoArray($queryCliente);
            
            if (!isset($dadosCliente['cli_id'])) {
                $this->returnClass['error'] = "ID do contrato não pertence ao seu cliente!";
                return;
            }
            
            if ( $dados['status_id'] != 3 && $dados['status_id'] != 4 && $dados['status_id'] != 19 && $dados['status_id'] != 20 && $dados['status_id'] != 21 ) {
                $this->returnClass['error'] = "Só é permitido bloqueio de contrato ativo!";
                return;
            }
        }
        else {
            $this->returnClass['error'] = "ID do contrato não localizado!";
            return;
        }     
        
        if ( $_POST["motivo_suspensao"] != 1 && $_POST["motivo_suspensao"] != 2) {
            $this->returnClass['error'] = "Motivo da suspenção não informado (motivo_suspensao)!";
            return;
        }
        
        $existeBloqueio = $this->getListBloqueio($idContrato, true);
        if (count($existeBloqueio) > 0) {
            $this->returnClass['error'] = "Esse contrato já contém bloqueio ativo!";
            return;
        }
        
        // Verifica se cobrança é pela OléTv
        if ( $this->validaCobrancaCarona() == false ) {
        	
        	if ( $this->cobrancaAberto( $idContrato ) ) {
        		
        		$this->returnClass['error'] = "Não é permitido bloqueios de contratos com cobrança realizada pela OléTV!";
        		return;
        		
        	}
        	
        }
        
		
        // Fazer outras validações
        $converted_date_time_start	= date_create_from_format("d/m/Y H:i:s", date('d/m/Y H:i:s'));
        $converted_date_time_end	= date_create_from_format("d/m/Y H:i:s", $_POST["data_encerramento"]." 23:59:59");
        
        
        // Verificação de final menor que início...
        if ($converted_date_time_end != null && $converted_date_time_end->getTimestamp() < $converted_date_time_start->getTimestamp()) {
            $this->returnClass['error'] = "A data de término deve ser maior do que a data atual!";
            return;
        }
        
        
        // Verificaçao de sobreposição de datas...
        $queryVerificaSobreposicaoDatas = $this->db->query("SELECT COUNT(*) FROM `tab_servicos_ajustes` WHERE `cli_id` = '" . $dados['cliente'] . ($converted_date_time_end != null ? "' AND `saj_inicio` <= '" . $converted_date_time_end->format("Y-m-d H:i:s") : "") . "' AND `saj_termino` >= '" . $converted_date_time_start->format("Y-m-d H:i:s") . "' AND `sac_id` = 10 AND `sas_id` >= 5");
        $dadosVerificaSobreposicaoDatas = $this->db->resultadoArray($queryVerificaSobreposicaoDatas);
        if ( $dadosVerificaSobreposicaoDatas['saj_id'] ) {
            $this->returnClass['error'] = "Já existe um bloqueio neste período!";
            return;
        }
		
        // Insere Ajuste
        $motivosSuspencao = array( 1 =>array(1,'Bloqueio por Inadimplência'), 2=>array(10,'Bloqueio a Pedido do Cliente') );
        
        $queryAjuste = $this->db->query("INSERT INTO tab_servicos_ajustes (cli_id, sac_id, saj_inicio, sas_id) VALUES (".$dadosCliente['cli_id'].", ".$motivosSuspencao[$_POST["motivo_suspensao"]][0].", '".date('Y-m-d H:i:s')."', 5)");
        $idAjuste = $this->db->ultimoIdInserido();
        
        
        // Busca IPTV
        $queryIPTVs = $this->db->query("SELECT cip_id FROM tab_clientes_iptv WHERE cor_id='".$idContrato."'");
        $dadosIPTVs = $this->db->resultadoArray($queryIPTVs);
        
        // Insere Bloqueio no IPTV
        $queryBloqueioIPTV = $this->db->query("INSERT INTO rel_saj_cip_contem (saj_id, cip_id) VALUES ($idAjuste, ".$dadosIPTVs['cip_id'].")");
        
        $queryRegraBloqueioIPTV = $this->db->query("INSERT INTO tab_servicos_ajustes_regras (sar_dia_semana,sar_hora_inicio,sar_hora_termino,sar_ajuste,saj_id,sat_id ) VALUES (NULL,NULL,NULL,0.00,".$idAjuste.",NULL)");
        
        $listaAssinaturas = $intranetContratos->buscaAssinaturasContrato($idContrato);
        foreach ($listaAssinaturas AS $k => $assinatura) {
            $intranetContratos->adicionaBloqueioAssinatura($idAjuste, $assinatura);
        }   
        
        //$this->registraComentario($dados, 'Bloqueio gerado via API Parceiro');
        
        $this->publicIPTV( $idContrato );
       
        $this->returnClass['retorno_status'] = true;
        $this->returnClass['id_bloqueio'] = $idAjuste;
        $this->returnClass['msg'] = "Contrato ID ".$idContrato." bloqueado com sucesso!";
        
    }
    
    function suspenderContrato($idContrato) {
        
        
    }
    
    
    
    function listarbloqueios() {
        // Verifica se ID foi informado
        $idContrato = intval( @$this->urlClass[2] );
        if ($idContrato == 0) {
            $this->returnClass['error'] = "ID do contrato não informado ou inválido!";
            return;
        }
        
        $somenteAtivo = (@$this->urlClass[3]=='true'?true:false);
        
        define('DIR_RAIZ', '../../');
        define('DIR_GLOBAL', '../../');
        
        $this->conectaDB();
        require_once '../_modulos/contratos/contratos.class.inc.php';
        $intranetContratos = new contratos($this->db, null, false);
        
        $dados = $intranetContratos->buscaDadosContrato($idContrato);
        if ($dados['cliente'] > 0) {
            $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".$dados['cliente']."' and clo_id = '".$this->idParceiro."'");// and (cls_id='1' OR cls_id='2' OR cls_id='3')
            $dadosCliente = $this->db->resultadoArray($queryCliente);
            
            if (!isset($dadosCliente['cli_id'])) {
                $this->returnClass['error'] = "ID do contrato não pertence ao seu cliente!";
                return;
            }
            
            if ($dados['status_id'] == 5 || $dados['status_id'] == 22 || $dados['status_id'] == 23 || $dados['status_id'] == 24) {
                $this->returnClass['error'] = "Só é permitido bloqueio de contrato ativo!";
                return;
            }
        }
        else {
            $this->returnClass['error'] = "ID do contrato não localizado!";
            return;
        }   
        
        $this->returnClass['retorno_status'] = true;
        $this->returnClass['bloqueios'] = $this->getListBloqueio($idContrato, $somenteAtivo);
    }
    
    
    function getListBloqueio($idContrato, $somenteAtivo) {
        
        $this->conectaDB();
        $query = $this->db->query("
            SELECT
                distinct(rel_saj_ass_contem.saj_id) AS id,
                tab_servicos_ajustes_categorias.sac_id AS tipo_id,
                tab_servicos_ajustes_categorias.sac_nome AS tipo_nome,
                DATE_FORMAT(tab_servicos_ajustes.saj_inicio, '%d/%m/%Y') AS inicio,
                DATE_FORMAT(tab_servicos_ajustes.saj_termino, '%d/%m/%Y') AS termino,
                tab_servicos_ajustes_status.sas_nome AS status_nome
            FROM
                rel_saj_ass_contem
                INNER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = rel_saj_ass_contem.ass_id)
                INNER JOIN tab_servicos_ajustes ON (tab_servicos_ajustes.saj_id = rel_saj_ass_contem.saj_id)
                INNER JOIN tab_servicos_ajustes_status ON (tab_servicos_ajustes.sas_id = tab_servicos_ajustes_status.sas_id)
                INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes_categorias.sac_id = tab_servicos_ajustes.sac_id)
            WHERE
				rel_ass_cor_estacontido.cor_id = $idContrato".($somenteAtivo==true?" and tab_servicos_ajustes.sas_id=5":""));
        
        $list = array();
        $k = 0;
        while (($v = $this->db->resultadoArray($query)) != false) {
            $list[$k]['id'] = $v['id'];
            $list[$k]['tipo_id'] = $v['tipo_id'];
            $list[$k]['tipo_nome'] = $this->textCharsetOutput( $v['tipo_nome'] );
            $list[$k]['inicio'] = $v['inicio'];
            $list[$k]['termino'] = $v['termino'];
            $list[$k]['status_nome'] = $this->textCharsetOutput( $v['status_nome'] );
            $k++;
        }
        return $list;
    }
    
    
    
    
    function desbloqueio() {
        
    	   	
        // Verifica se ID foi informado
        $idContrato = intval( @$this->urlClass[2] );
        if ($idContrato == 0) {
            $this->returnClass['error'] = "ID do contrato não informado ou inválido!";
            return;
        }
        
        $idBloqueio = intval( @$this->urlClass[3] );
        if ($idContrato == 0) {
            $this->returnClass['error'] = "ID do bloqueio não informado ou inválido!";
            return;
        }
        
        define('DIR_RAIZ', '../../');
        define('DIR_GLOBAL', '../../');
        
        $this->conectaDB();
        require_once '../_modulos/contratos/contratos.class.inc.php';
        $intranetContratos = new contratos($this->db, null, false);
        
        $dados = $intranetContratos->buscaDadosContrato($idContrato);
        if ($dados['cliente'] > 0) {
            $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".$dados['cliente']."' and clo_id = '".$this->idParceiro."'");// and (cls_id='1' OR cls_id='2')
            $dadosCliente = $this->db->resultadoArray($queryCliente);
            
            if (!isset($dadosCliente['cli_id'])) {
                $this->returnClass['error'] = "ID do contrato não pertence ao seu cliente!";
                return;
            }
            
            if ($dados['status_id'] == 3 || $dados['status_id'] == 4 ) { //  || $dados['status_id'] == 19 || $dados['status_id'] == 20
            //if ($dados['status_id'] == 5 || $dados['status_id'] == 21 || $dados['status_id'] == 22 || $dados['status_id'] == 23 || $dados['status_id'] == 24) {
                $this->returnClass['error'] = "Só é permitido desbloqueio de contrato bloqueado!";
                return;
            }
        }
        else {
            $this->returnClass['error'] = "ID do contrato não localizado!";
            return;
        }
		
		// Verifica se cobrança é pela OléTv, sendo, cria a suspensão temporaria
        if ( $this->validaCobrancaCarona() == false && $this->cobrancaAberto( $idContrato ) ) {
        	
        	//echo 'entrou no validaCobrancaCarona';
        	
        	$query = $this->db->query("SELECT COUNT(saj_id) FROM tab_servicos_ajustes WHERE saj_pai = {$idBloqueio}");
        	
        	// Limitado a 1 suspensão somente
        	if ( $this->db->resultado($query) < 1 ) {
        		
        		require_once '../_modulos/servicos_ajustes/servicos_ajustes.class.inc.php';
        		$intranetAjustes = new servicos_ajustes($this->db, null, false);
        		
        		// Insere Ajuste
        		if (in_array(date("w", strtotime("+ 2 days")), array(0, 6)) || (date("w", strtotime("+ 2 days")) == 1 && date("H") <= 12) || (date("w", strtotime("+ 2 days")) == 5 && date("H") >= 18)) {
        			$termino = date("Y-m-d", strtotime("Monday"))." 12:00";
        		} else {
        			$termino = date("Y-m-d H:i", strtotime("+ 2 days"));
        		}
        		
        		$query = "
				INSERT INTO tab_servicos_ajustes
					(sac_id, saj_pai, cli_id, saj_inicio, saj_termino, sas_id)
				VALUES
					(2, {$idBloqueio}, {$dados['cliente']}, NOW(), '$termino', 5)";
        		$this->db->query($query);
        		
        		$id = $this->db->ultimoIdInserido();
        		
        		// Registra Regras
        		$dados_categoria = $intranetAjustes->buscaDadosCategoria(2);
        		$this->registraRegras($id, $dados_categoria["regras"]);
        		
        		// Insere relação bloqueio/assinatura
        		$query = "SELECT ass_id AS id FROM rel_saj_ass_contem WHERE saj_id = {$idBloqueio}";
        		$query = $this->db->query($query);
        		while (($dados = $this->db->resultadoArray($query)) != false) {
        			$query_insert = "INSERT INTO rel_saj_ass_contem (saj_id, ass_id) VALUES ($id, {$dados["id"]})";
        			$this->db->query($query_insert);
        		}
        		
        		// Insere relação bloqueio/iptv
        		$query = "SELECT cip_id AS id FROM rel_saj_cip_contem WHERE saj_id = {$idBloqueio}";
        		$query = $this->db->query($query);
        		while (($dados = $this->db->resultadoArray($query)) != false) {
        			$query_insert = "INSERT INTO rel_saj_cip_contem (saj_id, cip_id) VALUES ($id, {$dados["id"]})";
        			$this->db->query($query_insert);
        		}
        		
        		// Insere relação bloqueio/autenticacoes
        		$query = "SELECT cau_id AS id FROM rel_saj_cau_contem WHERE saj_id = {$idBloqueio}";
        		$query = $this->db->query($query);
        		while (($dados = $this->db->resultadoArray($query)) != false) {
        			$query_insert = "INSERT INTO rel_saj_cau_contem (saj_id, cau_id) VALUES ($id, {$dados["id"]})";
        			$this->db->query($query_insert);
        		}
        		
        		// Insere relação bloqueio/pontos
        		$query = "SELECT clp_id AS id FROM rel_saj_clp_contem WHERE saj_id = {$idBloqueio}";
        		$query = $this->db->query($query);
        		while (($dados = $this->db->resultadoArray($query)) != false) {
        			$query_insert = "INSERT INTO rel_saj_clp_contem (saj_id, clp_id) VALUES ($id, {$dados["id"]})";
        			$this->db->query($query_insert);
        		}
        		
        		// Insere relação bloqueio/emails
        		$query = "SELECT cle_id AS id FROM rel_saj_cle_contem WHERE saj_id = {$idBloqueio}";
        		$query = $this->db->query($query);
        		while (($dados = $this->db->resultadoArray($query)) != false) {
        			$query_insert = "INSERT INTO rel_saj_cle_contem (saj_id, cle_id) VALUES ($id, {$dados["id"]})";
        			$this->db->query($query_insert);
        		}
        		
        		// Aplica Suspensão
        		$intranetAjustes->aplicarAjuste($idBloqueio);
        		
        		// Registra comentário
        		$intranetAjustes->registraComentario($idBloqueio, "<b>A&ccedil;&atilde;o</b>: Suspens&atilde;o Tempor&aacute;ria via API");
        		
        	}
        	else {
        		
        		$this->returnClass['error'] = "Já houve suspensão temporária para o mesmo, aguardando o pagamento para ser liberado!";
        		
        	}
        	
        }
        // Senao, finaliza o bloqueio atual
        else {
        	
        	$BloqueioAtuais = $this->getListBloqueio($idContrato, true);
	        //$findbloqueio = false;
	        foreach ($BloqueioAtuais as $bloqueio) {
	            if ($bloqueio['id'] == $idBloqueio) {
	                if ($bloqueio['tipo_id'] == 1 || $bloqueio['tipo_id'] == 10) {
	                    
	                    require_once '../_modulos/servicos_ajustes/servicos_ajustes.class.inc.php';
	                    $intranetAjustes = new servicos_ajustes($this->db, null, false);
	                    $intranetAjustes->removeAjuste($bloqueio['id'], date('Y-m-d H:i:s'));
	                    $intranetAjustes->registraComentario($bloqueio['id'], "<b>A&ccedil;&atilde;o</b>: Bloqueio finalizado via API");
	
	                    $this->returnClass['retorno_status'] = true;
	                    $this->returnClass['msg'] = "Contrato ID ".$idContrato." desbloqueado com sucesso!";
	                    return;
	                }
	                else {
	                    $this->returnClass['error'] = "Tipo de bloqueio não permite o desbloqueio via API!";
	                    return;
	                }
	            }
	        }
	        
	        $this->publicIPTV( $idContrato );
	        
	        $this->returnClass['error'] = "ID do Bloqueio não localizado!";
        	
        }
        
        return;
        
        
    }
    
    
    function termo() {
        $idContrato = intval( @$this->urlClass[2] );
        if ($idContrato == 0) {
            $this->returnClass['error'] = "ID do contrato não informado ou inválido!";
            return;
        }
        
        define('DIR_RAIZ', '../../');
        define('DIR_GLOBAL', '../../');
        
        $this->conectaDB();
        require_once '../_modulos/contratos/contratos.class.inc.php';
        $intranetContratos = new contratos($this->db, null, false);
        
        $dados = $intranetContratos->buscaDadosContrato($idContrato);
        if ($dados['cliente'] > 0) {
            $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".$dados['cliente']."' and clo_id = '".$this->idParceiro."'");// and (cls_id='1' OR cls_id='2')
            $dadosCliente = $this->db->resultadoArray($queryCliente);
            
            if (!isset($dadosCliente['cli_id'])) {
                $this->returnClass['error'] = "ID do contrato não pertence ao seu cliente!";
                return;
            }
		}
        else {
            $this->returnClass['error'] = "ID do contrato não localizado!";
            return;
        }
        
        $termo = $this->textCharsetOutput( $intranetContratos->geraConteudo($dados, "vistoria", false, true) );
        $termo = str_replace('/fonts/', INTRANET_HOST.'/fonts/', $termo);
        $termo = str_replace('line-height: 100%;', 'line-height: 70%;', $termo);
        
        $dompdf = new Dompdf();
        $options = new Options();
        $options->set('isRemoteEnabled', TRUE);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml( $termo );        
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');
        // Render the HTML as PDF
        $dompdf->render(  );
        
        $this->returnClass['retorno_status'] = true;
        $this->returnClass['formato'] = 'application/pdf';
        $this->returnClass['conteudo'] = base64_encode( $dompdf->output() );
    }
    
    
    function mac() {
        // Verifica ID do contrato
        $idContrato = intval( @$this->urlClass[2] );
        if ( $idContrato == 0 ) {
            $this->returnClass['error'] = "ID do contrato não informado!";
            return;
        }
        
        // Verifica ID do equipamento atual
        $idEquipamento = intval( @$this->urlClass[3] );
        if ( $idEquipamento == 0 ) {
            $this->returnClass['error'] = "ID do equipamento atual não informado!";
            return;
        }
        
        // Verifica se o ID do Contrato pertence a cliente do parceiro
        $this->conectaDB();
        require_once '../_modulos/contratos/contratos.class.inc.php';
        $intranetContratos = new contratos($this->db, null, false);
        
        $dados = $intranetContratos->buscaDadosContrato($idContrato);
        if ($dados['cliente'] > 0) {
            $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".$dados['cliente']."' and clo_id = '".$this->idParceiro."'");// and (cls_id='1' OR cls_id='2')
            $dadosCliente = $this->db->resultadoArray($queryCliente);
            
            if (!isset($dadosCliente['cli_id'])) {
                $this->returnClass['error'] = "ID do contrato não pertence ao seu cliente!";
                return;
            }
        }
        else {
            $this->returnClass['error'] = "ID do contrato não localizado!";
            return;
        }
        
        $idAssinatura = $this->validaAssinatura($idContrato, $idEquipamento);
        if ($idAssinatura === false) {
            $this->returnClass['error'] = "Contrato sem assinatura ativa!";
            return;
        }
        if ( intval($_POST['id_modelo']) == 0 ) {
            
            if (  $this->idParceiro == 482 || $this->idParceiro == 483 || $this->idParceiro == 484 ) {
                $_POST['id_modelo'] = '7';
            }
            else {
                $this->returnClass['error'] = "ID do modelo não informado!";
                return;
            }

        }
        else {
            
            if ($this->validaModelo( DECODER_PADRAO, $_POST['id_modelo']) == false) {
                $this->returnClass['error'] = "ID do novo modelo é inválido!";
                return;
            }
        }
		
		$idComodato = $this->validaEquipamentoAtual($idContrato, $idEquipamento);
        if ($idComodato==false) {
            $this->returnClass['error'] = "ID do equipamento atual é inválido!";
            return;
        }
		
		$queryIPTV = $this->db->query("SELECT cip_usuario FROM tab_clientes_iptv WHERE cor_id='".$dados['id']."'");// and (cls_id='1' OR cls_id='2')
        $dadosIPTV = $this->db->resultadoArray($queryIPTV);
        $mailUsuario = '';
        if ( !isset($dadosIPTV['cip_usuario']) || $dadosIPTV['cip_usuario'] == '') {
            // Valida MAC
            $_POST["mac"] = preg_replace("/[^A-F0-9]/", "", strtoupper($_POST["mac"]));
            if ( strlen($_POST["mac"]) != 12) {
                $this->returnClass['error'] = "MAC informado é inválido! -".$dadosIPTV['cip_usuario'];
                return;
            }
        }
        else {
            $mailUsuario = $dadosIPTV['cip_usuario'];
        }
        
        $this->trocarMAC($idEquipamento, $dados['cliente'], $idAssinatura, $idContrato,$_POST["mac"],DECODER_PADRAO,$_POST["id_modelo"], $mailUsuario);
        
    }
    
    function trocarMAC($idComodatoAtual, $idCliente, $idAssinatura, $idContrato, $MAC, $idEquipamento, $idModelo, $mailUsuario = '') {
        
        $this->conectaDB();
        
        if ( $MAC != '' || $mailUsuario == '' ) {

            $MAC = preg_replace("/[^A-F0-9]/", "", strtoupper($MAC));

            $query = $this->db->query("SELECT COUNT(*) FROM tab_clientes_comodatos WHERE clc_mac_address = '{$MAC}' AND ccs_id IN (1, 2)".($idComodatoAtual ? " AND clc_id != $idComodatoAtual" : ""));
            $ponto_existente = $this->db->resultado($query);
            if ($ponto_existente) {
                $this->returnClass['error'] = "Este MAC já está sendo utilizado!";
                return;
            }
        }
        
        //$this->conectaDB();
        require_once '../_modulos/contratos/contratos.class.inc.php';
        require_once('_global/funcoes.class.inc.php');
        $intranetContratos = new contratos($this->db, null, false);
        
        require_once '../_modulos/clientes_comodatos/clientes_comodatos.class.inc.php';
        $clientesComodatos = new clientes_comodatos($this->db, null, false);
        
        // Trocando MAC de equipamento existente
        if ( $idComodatoAtual > 0 ) {
        
            $queryComodato = $this->db->query("SELECT eqm_id,clc_mac_address FROM tab_clientes_comodatos WHERE clc_id='".$idComodatoAtual."'");
            $dadosComodato = $this->db->resultadoArray($queryComodato);
            
            $queryEquipamentoAtual = $this->db->query("SELECT eqm_nome FROM tab_equipamentos_modelos WHERE eqm_id='".$dadosComodato['eqm_id']."'");
            $dadosEquipamentoAtual = $this->db->resultadoArray($queryEquipamentoAtual);
            
            $queryEquipamentoNovo = $this->db->query("SELECT eqm_nome FROM tab_equipamentos_modelos WHERE eqm_id='".$idModelo."'");
            $dadosEquipamentoNovo = $this->db->resultadoArray($queryEquipamentoNovo);
            
            
            // trocar por Update
            $this->db->query("
    						UPDATE
    							tab_clientes_comodatos
    						SET
    							clc_mac_address = '{$MAC}',
    							ass_id = {$idAssinatura},
    							cor_id = {$idContrato},
    							equ_id = {$idEquipamento},
    							eqm_id = {$idModelo},
    							ccs_id = 2
    						WHERE
    							clc_id = {$idComodatoAtual}");
            
            // Registra Comentario no Contrato
            $clientesComodatos->registraComentario($idComodatoAtual, "<b>".utf8_decode("Substituição").":</b> <i>".$dadosEquipamentoAtual['eqm_nome']."/".$dadosComodato['clc_mac_address']."</i> para <i>".$dadosEquipamentoNovo['eqm_nome']."/".$MAC."</i> via API do Parceiro", null);
            
        }
        // Inserindo um equipamento na assinatura/Contrato
        else {
            $this->db->query("
			     		INSERT INTO
				        	tab_clientes_comodatos
						SET
							clc_mac_address = '{$MAC}',
                            clc_data_inicio = '".@date('Y-m-d')."',
                            cli_id = {$idCliente}, 
							ass_id = {$idAssinatura},
							cor_id = {$idContrato},
							equ_id = {$idEquipamento},
							eqm_id = {$idModelo},
							ccs_id = 2
                        ");
            
            $idComodatoAtual = $this->db->ultimoIdInserido();
            
            // Registra Comentario no Contrato
            $clientesComodatos->registraComentario($idComodatoAtual, "Equipamento inserido via API do Parceiro", null);
            
        }
        
        $query = $this->db->query("
			SELECT
				tab_clientes_comodatos.clc_id AS id,
				tab_clientes_comodatos.clc_mac_address AS mac_address,
				tab_clientes_comodatos.ass_id AS assinatura_id,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_contratos.cor_id AS contrato_id,
				tab_contratos.cor_codigo AS codigo,
				tab_clientes_iptv.cip_id AS iptv_id,
				tab_clientes_comodatos_status.ccs_id AS status_id,
				tab_clientes_comodatos_status.ccs_nome AS status_nome
			FROM
				tab_clientes_comodatos
				INNER JOIN tab_clientes ON (tab_clientes_comodatos.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_contratos ON (tab_clientes_comodatos.cor_id = tab_contratos.cor_id)
				INNER JOIN tab_clientes_iptv ON (tab_clientes_comodatos.cor_id = tab_clientes_iptv.cor_id)
				INNER JOIN tab_clientes_comodatos_status ON(tab_clientes_comodatos.ccs_id = tab_clientes_comodatos_status.ccs_id)
			WHERE
				tab_clientes_comodatos.clc_id = $idComodatoAtual");
        $dados = $this->db->resultado($query);
        
        $listContratos['retorno_status'] = true;
        $listContratos['id_contrato'] = $idContrato;
        $listContratos['codigo'] = $dados['codigo'];
        $listContratos['assinaturas'] = $this->getAssinaturasContrato($idContrato);
        
        $this->publicIPTV( $idContrato );
        
        $this->returnClass = $listContratos;
        return $this->returnClass;
    }
    
    function validaEquipamentoAtual($idContrato, $idEquipamentoAtual) {
        $this->conectaDB();
        $queryEquipamentoAtual = $this->db->query("
			SELECT
				tab_clientes_comodatos.clc_id AS id,
				tab_clientes_comodatos.clc_mac_address AS mac_address,
				tab_equipamentos.equ_nome AS equipamento,
				tab_equipamentos_modelos.eqm_nome AS equipamento_modelo,
				DATE_FORMAT(tab_clientes_comodatos.clc_data_retirada, '%d/%m/%Y') AS data_retirada,
				DATE_FORMAT(tab_clientes_comodatos.clc_data_inicio, '%d/%m/%Y') AS data_inicio,
				DATE_FORMAT(tab_clientes_comodatos.clc_data_termino, '%d/%m/%Y') AS data_termino,
				tab_clientes_comodatos_status.ccs_nome AS status_nome
			FROM
				tab_clientes_comodatos
				INNER JOIN tab_clientes_comodatos_status ON (tab_clientes_comodatos.ccs_id = tab_clientes_comodatos_status.ccs_id)
				INNER JOIN tab_equipamentos ON (tab_clientes_comodatos.equ_id = tab_equipamentos.equ_id)
				LEFT JOIN tab_equipamentos_modelos ON (tab_clientes_comodatos.eqm_id = tab_equipamentos_modelos.eqm_id)
				INNER JOIN tab_assinaturas ON (tab_clientes_comodatos.ass_id = tab_assinaturas.ass_id)
                INNER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
			WHERE
				rel_ass_cor_estacontido.cor_id = {$idContrato} and tab_clientes_comodatos.clc_id={$idEquipamentoAtual}");
        
        $dadosEquipamentoAtual = $this->db->resultadoArray($queryEquipamentoAtual);
        return (isset($dadosEquipamentoAtual['id'])?true:false);
    }
    
    function validaAssinatura($idContrato,$idEquipamentoAtual) {
        $this->conectaDB();
        $queryAssinaturas = $this->db->query("
			SELECT
				tab_assinaturas.ass_id AS id,
				tab_clientes_iptv.cip_pontos AS pontos,
				tab_clientes_iptv.cip_dispositivos AS dispositivos,
				tab_clientes_iptv.cip_pacotes AS pacotes,
				tab_clientes_iptv.cip_catalogs AS catalogs,
				tab_clientes_iptv.cip_publicado AS publicado,
				tab_planos.pla_id AS plano_id,
				tab_planos.pla_nome AS plano_nome,
				tab_clientes_iptv_status.cis_id AS status_id,
				tab_clientes_iptv_status.cis_nome AS status_nome
			FROM
                tab_assinaturas
                INNER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
                INNER JOIN tab_clientes_iptv ON (tab_clientes_iptv.cor_id = rel_ass_cor_estacontido.cor_id)
				INNER JOIN tab_planos ON (tab_clientes_iptv.pla_id = tab_planos.pla_id)
				INNER JOIN tab_clientes_iptv_status ON (tab_clientes_iptv.cis_id = tab_clientes_iptv_status.cis_id)
                INNER JOIN tab_clientes_comodatos ON (tab_clientes_comodatos.ass_id = tab_assinaturas.ass_id)
			WHERE
				rel_ass_cor_estacontido.cor_id = {$idContrato} and tab_clientes_iptv_status.cis_id='1' and tab_clientes_comodatos.clc_id={$idEquipamentoAtual}"  );
        $dadosAssinaturas = $this->db->resultadoArray($queryAssinaturas);
        return (isset($dadosAssinaturas['id'])?$dadosAssinaturas['id']:false);
    }
    
    
    function validaEquipamento($idEquipamento) {
        $this->conectaDB();
        $queryEquipamento = $this->db->query("SELECT * FROM tab_equipamentos WHERE equ_id={$idEquipamento}");
        $dadosEquipamento = $this->db->resultadoArray($queryEquipamento);
        return (isset($dadosEquipamento['equ_id'])?true:false);
    }
    
    
    function validaModelo($idEquipamento, $idModelo) {
        $this->conectaDB();
        $queryModelo = $this->db->query("SELECT * FROM tab_equipamentos_modelos WHERE equ_id={$idEquipamento} and  eqm_disponivel='1' and eqm_id={$idModelo}");
        $dadosModelo = $this->db->resultadoArray($queryModelo);
        return (isset($dadosModelo['eqm_id'])?true:false);
    }
    
    function validaObrigacaoIdContratoOrigem( $id_contrato_origem ) {
        //$this->idParceiro
        $this->conectaDB();
        $queryContratoOrigem = $this->db->query("SELECT clo_system_rpg FROM tab_clientes_origens WHERE clo_id={$this->idParceiro}");
        $dadosContratoOrigem = $this->db->resultadoArray($queryContratoOrigem);
        return ( $dadosContratoOrigem['clo_system_rpg'] == 'IXC Soft' ? true : false );
    }
    
    
    function setContratoOrigem($idContrado, $idCodigoOrigem) {
        //echo "UPDATE tab_contratos SET cor_codigo_origem = '$idCodigoOrigem' WHERE cor_id = {$idContrado}";
        $this->conectaDB();
        $this->db->query("UPDATE tab_contratos SET cor_codigo_origem = '$idCodigoOrigem' WHERE cor_id = {$idContrado}");
    }
    
    
    function cancelar() {
        // Verifica ID do contrato
        $idContrato = intval( @$this->urlClass[2] );
        if ( $idContrato == 0 ) {
            $this->returnClass['error'] = "ID do contrato não informado!";
            return;
        }
        
        // Verifica se o ID do Contrato pertence a cliente do parceiro
        $this->conectaDB();
        require_once '../_modulos/contratos/contratos.class.inc.php';
        require_once('../_global/funcoes.class.inc.php');
        $intranetContratos = new contratos($this->db, null, false);
        
        $dadosContrato = $intranetContratos->buscaDadosContrato($idContrato);
        if ($dadosContrato['cliente'] > 0) {
            $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".$dadosContrato['cliente']."' and clo_id = '".$this->idParceiro."'");// and (cls_id='1' OR cls_id='2')
            $dadosCliente = $this->db->resultadoArray($queryCliente);
            
            if (!isset($dadosCliente['cli_id'])) {
                $this->returnClass['error'] = "ID do contrato não pertence ao seu cliente!";
                return;
            }
            
            if ($dadosContrato['status_id'] == 5 || $dadosContrato['status_id'] == 21 || $dadosContrato['status_id'] == 22 || $dadosContrato['status_id'] == 23 || $dadosContrato['status_id'] == 24) {
                //$this->returnClass['error'] = "Não é permitido cancelar contrato com o status ".$dadosContrato['status_nome']."!";
                $this->returnClass['retorno_status'] = true;
                $this->returnClass['msg'] = "Contrato cancelado com sucesso!";
                return;
            }
            
        }
        else {
            $this->returnClass['error'] = "ID do contrato não localizado!";
            return;
        }
        
        
        
        if ($cobrancas = $intranetContratos->buscaCobrancasAtrasadas($dadosContrato["cliente"])) {
            $this->returnClass['error'] = "Contrato não pode ser cancelado! Cliente possui $cobrancas cobrança(s) em atraso.";
            return;
        }
        if ( $intranetContratos->possuiAditivoPendente($idContrato) ) {
            $this->returnClass['error'] = "Contrato não pode ser cancelado! Cliente possui um contrato aditivo em aberto.";
            return;
        }
		
		if ( $dadosContrato['status_id'] == 19 || $dadosContrato['status_id'] == 20 || $dadosContrato['status_id'] == 21 ) {
            
            $intranetContratos->acaoCancelarContrato($dadosContrato, date('d/m/Y', strtotime('-1 days')), 'Cancelamento via API Parceiro');
            $this->returnClass['retorno_status'] = true;
            $this->returnClass['msg'] = "Contrato cancelado com sucesso!";
            
        }
        else if ( $dadosContrato['status_id'] < 19 ) {
            
            $intranetContratos->anularContrato($dadosContrato, 8, 'Anulado via API Parceiro');
            $this->returnClass['retorno_status'] = true;
            $this->returnClass['msg'] = "Contrato cancelado com sucesso!";
            
        }
        else {
            
            $this->returnClass['retorno_status'] = false;
            $this->returnClass['error'] = "Contrato invalido para cancelar!";
            
        }
    }
    
    /**
	 * Busca cobrancas atrasadas para um cliente
	 * @param int $cliente_id ID do cliente
	 * @return array Array de cobrancas atrasadas
	 */
	function buscaCobrancasAtrasadas($cliente_id) {

        $this->conectaDB();
        $query = $this->db->query("
                SELECT 
                    cli_id,
                    coa_id,
                    coa_data_vencimento,
                    DATEDIFF(CURRENT_DATE, coa_data_vencimento) AS dias_atraso
                FROM tab_cobrancas
                WHERE cot_id = 1 
                AND cof_id NOT IN (1, 2, 3)
                AND cli_id IN ( $cliente_id )
                AND coa_data_vencimento < CURRENT_DATE - INTERVAL 15 DAY
                AND cli_id IN (
                    -- Subquery para manter apenas quem tem mais de 1 cobrança nessa condição
                    SELECT cli_id
                    FROM tab_cobrancas
                    WHERE cot_id = 1 
                        AND cof_id NOT IN (1, 2, 3)
                        AND coa_data_vencimento < CURRENT_DATE - INTERVAL 15 DAY
                    GROUP BY cli_id
                    HAVING COUNT(coa_id) > 1
                )
                ORDER BY cli_id, coa_data_vencimento ASC;");

        $retorno = $this->db->resultadoArrayTotal($query);

        if (count($retorno) > 0) {
            return $retorno;
        } else {
            return false;
        }

    }
    
    function inserir() {
        $idCliente = intval( $_POST['id_cliente'] );

        $dadosCobrancasAtrasadas = $this->buscaCobrancasAtrasadas($idCliente);
        if ($dadosCobrancasAtrasadas !== false) {
            $this->returnClass['error'] = "Não é possível criar um novo contrato para o cliente, pois ele possui ".count($dadosCobrancasAtrasadas)." cobrança(s) em atraso.";
            return;
        }

        $podeSepararPontos = $this->podeSepararPontos($this->idParceiro);
        if ($idCliente == 0) {
            $this->returnClass['error'] = "ID do cliente não informado!";
            return;
        }
        $this->conectaDB();
        // Verifica se cliente é do parceiro
        $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".$idCliente."' and clo_id = '".$this->idParceiro."'");
        $dadosCliente = $this->db->resultadoArray($queryCliente);
        if (!isset($dadosCliente['cli_id'])) {
            $this->returnClass['error'] = "ID do cliente inválido!";
            return;
        }
        //
        $idPlano = intval( $_POST['id_plano_principal'] );
        $ehMultiService = $this->validaMultiservice($idPlano);

        $validaSePossuiMultiservice = $this->validaSeJaTemMultiservice($idCliente, $idPlano);
        if($validaSePossuiMultiservice && $ehMultiService) {
            $this->returnClass['error'] = "Plano inválido, já existe um contrato com o plano ".$validaSePossuiMultiservice."!";
            return;
        }

        if(!$this->clienteCPF($idCliente) && $ehMultiService){
            $this->returnClass['error'] = "ID do plano principal é válido apenas para CPF!";
            return;
        }

        if ($idPlano == 0) {
            $this->returnClass['error'] = "ID do plano principal não informado!";
            return;
        }
        
        if ( $this->validaPlanoPrincipal($idPlano) === false) {
            $this->returnClass['error'] = "ID do plano principal inválido!";
            return;
        }
        else {
            $serviceId = $this->getServiceIdPlanoPrincipal($idPlano);
        }

        // Verifica se parceiro tem ERP que obriga id_contrato_origem
        $idContratoOrigem = trim( $_POST['id_contrato_origem'] );
        if ( $this->validaObrigacaoIdContratoOrigem( $idContratoOrigem ) === true) {
            
            if ( $idContratoOrigem == '') {
                $this->returnClass['error'] = "ID do Contrato de Origem não informado!";
                return;
            }
        }
        
        // Imprementação nova
        require_once '_modulos/assinaturas/assinaturas.class.inc.php';
        $intranetAssinaturas = new assinaturas($this->db, null, false);
        
        if ($intranetAssinaturas->possuiPlanoParceiroDisponivel($idPlano, $this->idParceiro) < 1) {
        	
            $this->returnClass['error'] = "Plano principal não disponível! ($idPlano)";
            return;
            
        }
        /**/
        
        $validaQuantidadesDePontos = $this->validaQuantidadesDePontos($idPlano, $_POST['id_plano_adicional']);
        $mobile = false;
        if ( $this->validaPlanoMobile($idPlano) === true) {
            // Verificado que é um plano Mobile, executa as validações necessária
            $mobile = true;
            require_once '../_modulos/clientes_iptv/clientes_iptv.class.inc.php';
            $intranetClienteIPTV = new clientes_iptv($this->db, null, false);
            
            $listAdicionais = array();
            if($podeSepararPontos && is_array($_POST["email_usuario"])) {
                
                $_POST['email_usuario'] = array_merge([], array_unique($_POST['email_usuario']));
                $qtdPontosAdicional = 0;
                $qtdDispositivosAdicional = 0;
                foreach ($_POST['id_plano_adicional'] AS $adicional) {
                    $quantidadesAdicional = $this->validaQuantidadesDePontos($adicional, []);
                    $qtdPontosAdicional = $qtdPontosAdicional + $quantidadesAdicional['pontos'];
                    $qtdDispositivosAdicional = $qtdDispositivosAdicional + $quantidadesAdicional['aplicativos'];
                }
                $qtdDispositivos = $validaQuantidadesDePontos['aplicativos'];
                $qtdEmail = count($_POST['email_usuario']);
                $qtdtotalDispositivos = $qtdDispositivos + $qtdDispositivosAdicional;
                
                if($qtdEmail > $qtdtotalDispositivos) {
                    $this->returnClass['error'] = "Quantidade de e-mails ({$qtdEmail}) menor que a quantidade de dispositivos para o plano ({$qtdtotalDispositivos}) ou possui e-mails duplicados";
                    return;
                } else if($qtdEmail < $qtdtotalDispositivos) {
                    $this->returnClass['error'] = "Quantidade de e-mails ({$qtdEmail}) maior que a quantidade de dispositivos para o plano ({$qtdtotalDispositivos}) ";
                    return;
                }
                
                foreach($_POST["email_usuario"] as $email) {
                    $email = addslashes(trim($email));
                    if ($email == '') {
                        $this->returnClass['error'] = "E-mail para o usuário não informado!";
                        return;
                    }

                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $this->returnClass['error'] = "Usuário {$email} não é um e-mail válido!";
                        return;
                    }
                    
                    if ($intranetClienteIPTV->buscaIdPorUsuario($email) != null) {
                        $this->returnClass['error'] = "Usuário {$email} já está em uso!";
                        return;
                    }
                }
            } else {


                $_POST["email_usuario"] = addslashes(trim($_POST["email_usuario"]));
                
                if ($_POST["email_usuario"] == '') {
                    $this->returnClass['error'] = "E-mail para o usuário não informado!";
                    return;
                }
                
                if (!filter_var($_POST["email_usuario"], FILTER_VALIDATE_EMAIL)) {
                    $this->returnClass['error'] = "Usuário {$_POST["email_usuario"]} não é um e-mail válido!";
                    return;
                }
                
                if ($intranetClienteIPTV->buscaIdPorUsuario($_POST["email_usuario"]) != null) {
                    $this->returnClass['error'] = "Usuário {$_POST["email_usuario"]} já está em uso!";
                    return;
                }
            }
            $listAdicionais[] = array('id'=>$idPlano,  'modelo'=>'','mac'=>'', 'type'=>'principal');
            
            foreach ($_POST['id_plano_adicional'] AS $adicional) {
                $idAdicional = intval( $adicional );
                if ( $idAdicional > 0 ) {
                    $adicionalEhMultiService = $this->validaMultiservice($idAdicional);
                    
                    $validaSePossuiMultiservice = $this->validaSeJaTemMultiservice($idCliente, $idAdicional);
                    if($validaSePossuiMultiservice && $adicionalEhMultiService) {
                        $this->returnClass['error'] = "Plano inválido, já existe um contrato com o plano ".$validaSePossuiMultiservice."!";
                        return;
                    }

                    if($ehMultiService && $adicionalEhMultiService){
                        $this->returnClass['error'] = "ID do plano adicional não é válido para esse plano!";
                        return;
                    }

                    if(!$this->clienteCPF($idCliente) && $adicionalEhMultiService){
                        $this->returnClass['error'] = "ID do plano adicional é válido apenas para CPF!";
                        return;
                    }

                    if ($idAdicional!=736 && $idAdicional!=751 && $idAdicional!=753 ) {
                        if (count($_POST['id_modelo']) > 0 || count($_POST['mac']) > 0) {
                            if(!$podeSepararPontos) {
                                $this->returnClass['error'] = "Plano Mobile não contem dados do equipamento (id_modelo e/ou mac)!";
                                return;
                            } else {
                                $listAdicionais[] = array('id'=>$idAdicional,  'modelo'=>'','mac'=>'','type'=>'');
                            }
                        }
                        else {
                            $listAdicionais[] = array('id'=>$idAdicional,  'modelo'=>'','mac'=>'','type'=>'');
                        }
                    }
                    else {
                        $this->returnClass['error'] = "Plano {$idAdicional} inválido como adicional para Mobile!";
                        return;
                    }
                }
            }
        }
        else {
            
            if($podeSepararPontos && !$ehMultiService) {
                $qtdPontosAdicional = 0;
                $qtdDispositivosAdicional = 0;
                foreach ($_POST['id_plano_adicional'] AS $adicional) {
                    $quantidadesAdicional = $this->validaQuantidadesDePontos($adicional, []);
                    $qtdPontosAdicional = $qtdPontosAdicional + $quantidadesAdicional['pontos'];
                    $qtdDispositivosAdicional = $qtdDispositivosAdicional + $quantidadesAdicional['aplicativos'];
                }
                $qtdPontos = $validaQuantidadesDePontos['pontos'];
                $qtdDispositivos = $validaQuantidadesDePontos['aplicativos'];
                $qtdTotalPontos = $qtdPontos + $qtdPontosAdicional;
                $qtdtotalDispositivos = $qtdDispositivos + $qtdDispositivosAdicional;
                $qtdPontoDispositivos = $qtdtotalDispositivos  + $qtdTotalPontos;
                
                $_POST['email_usuario'] = array_merge([], array_unique($_POST['email_usuario']));
                $_POST['mac'] = array_merge([], array_unique($_POST['mac']));
                $qtdMac = count($_POST['mac']);
                $qtdModelo = count($_POST['id_modelo']);
                $qtdEmail = count($_POST['email_usuario']);
                $qtdMacEmail = $qtdMac + $qtdEmail;
                
                if($qtdPontoDispositivos > $qtdMacEmail) {
                    $this->returnClass['error'] = "Quantidade de E-mails/MACs ({$qtdMacEmail}) menor que a quantidade requisitada para o plano ({$qtdPontoDispositivos}) ou possui E-mails/MACs duplicados";
                    return;
                } else if($qtdPontoDispositivos < $qtdMacEmail) {
                    $this->returnClass['error'] = "Quantidade de E-mails/MACs ({$qtdMacEmail}) maior que a quantidade requisitada para o plano ({$qtdPontoDispositivos})";
                    return;
                }
                
                if($qtdMac > $qtdModelo) {
                    $this->returnClass['error'] = "Quantidade de Modelos ({$qtdModelo}) menor que a quantidade requisitada para o plano ({$qtdMac})";
                    return;
                } else if($qtdMac < $qtdModelo) {
                    $this->returnClass['error'] = "Quantidade de Modelos ({$qtdModelo}) maior que a quantidade requisitada para o plano ({$qtdMac})";
                    return;
                }
            }
            
            $semEmail = false;
            if(!is_array ($_POST["email_usuario"]) && trim($_POST["email_usuario"]) == '') {
                $semEmail = true;
            } else if (is_array ($_POST["email_usuario"]) && count($_POST['email_usuario']) == 0) {
                $semEmail = true;
            }
         
            // Verificado que NÃO é um plano Mobile, executa as validações necessária
            
            if ($semEmail && !$ehMultiService) {
                
                $idModelo = intval( $_POST['id_modelo'][0] );
                if ($idModelo == 0) {
                    
                    if (  $this->idParceiro == 482 || $this->idParceiro == 483 || $this->idParceiro == 484 ) {
                        $idModelo = '7';
                    }
                    else {
                        $this->returnClass['error'] = "ID do modelo não informado!";
                        return;
                    }
                }
                if ($this->validaModelo( DECODER_PADRAO , $idModelo) === false) {
                    $this->returnClass['error'] = "ID do modelo é inválido!";
                    return;
                }
                // Valida MAC
                $MAC = preg_replace("/[^A-F0-9]/", "", strtoupper( trim( $_POST["mac"][0] ) ) );
                if ($MAC == '') {
                    $this->returnClass['error'] = "MAC não informado!";
                    return;
                }
                if ( strlen($MAC) != 12 ) {
                    $this->returnClass['error'] = "MAC informado é inválido!";
                    return;
                }
                else {
                    $queryMAC = $this->db->query("SELECT COUNT(*) FROM tab_clientes_comodatos WHERE clc_mac_address = '".$MAC."' AND ccs_id IN (1, 2)");
                    $dadosMAC = $this->db->resultado($queryMAC);
                    if ($dadosMAC) {
                        $this->returnClass['error'] = "Este MAC já está sendo utilizado!";
                        return;
                    }
                }
            
            }
            else {
                
                $idModelo = intval( $_POST['id_modelo'][0] );
                
                if ( $idModelo > 0 && !$ehMultiService) {
                
                    if ($idModelo > 0 && $this->validaModelo( DECODER_PADRAO , $idModelo) === false) {
                        $this->returnClass['error'] = "ID do modelo é invalido! ($idModelo)!";
                        return;
                    }
                    
                    $MAC = preg_replace("/[^A-F0-9]/", "", strtoupper( trim( $_POST["mac"][0] ) ) );
                    
                    if ($MAC != '') {
    
                        if ( strlen($MAC) > 0 &&  strlen($MAC) != 12 ) {
                            $this->returnClass['error'] = "MAC informado é inválido!";
                            return;
                        }
                        elseif ( strlen($MAC) > 0 ) {
                            $queryMAC = $this->db->query("SELECT COUNT(*) FROM tab_clientes_comodatos WHERE clc_mac_address = '".$MAC."' AND ccs_id IN (1, 2)");
                            $dadosMAC = $this->db->resultado($queryMAC);
                            if ($dadosMAC) {
                                $this->returnClass['error'] = "Este MAC já está sendo utilizado!";
                                return;
                            }
                        }
                        
                    }
                    else {
                        $MAC = '';
                    }
                    
                }
                else {
                    $MAC = '';
                }
            }
            
            $posModelos = 1;
            $listAdicionais = array();
            $listAdicionais[] = array('id'=>$idPlano,  'modelo'=>($idModelo>0?$idModelo:''),'mac'=>$MAC, 'type'=>'principal');
            
            $limitPontoAdicional = 3;


            // 36 - One Telecom - Novo Hamburgo
            // 38 - Sunway
            // 76 - Box Telecomunicações
            // 99 - Knet
            // 137 - Isuper
            // 370 - Net Vale MA
            // 408 - TH Internet
            // 498 - Ipop Telecom
            // 636 - Opção Telecom
            // 639 - SCM Evolutt - MS
            // 650 - SCM Evolutt - PR
            // 654 - WS FIBRA
            // 673 - PG ON Provedor de Internet e Serviços Digitais
            if(in_array($this->idParceiro, array(36, 38, 76, 99, 498, 370, 137, 408, 654, 75, 636, 650, 639, 673))) {
                $limitPontoAdicional = 4;
            }
            
            $countPontoAdicional = 0;
            
            foreach ($_POST['id_plano_adicional'] AS $adicional) {
                $idAdicional = intval( $adicional );
                if ($idAdicional > 0) {

                    $adicionalEhMultiService = $this->validaMultiservice($idAdicional);
                    
                    $validaSePossuiMultiservice = $this->validaSeJaTemMultiservice($idCliente, $idAdicional);
                    if($validaSePossuiMultiservice && $adicionalEhMultiService) {
                        $this->returnClass['error'] = "Plano inválido, já existe um contrato com o plano ".$validaSePossuiMultiservice."!";
                        return;
                    }

                    if($ehMultiService && $adicionalEhMultiService){
                        $this->returnClass['error'] = "ID do plano adicional não é válido para esse plano!";
                        return;
                    }

                    if(!$this->clienteCPF($idCliente) && $adicionalEhMultiService){
                        $this->returnClass['error'] = "ID do plano adicional é válido apenas para CPF!";
                        return;
                    }
                    

                	if ($this->validaPlanoAdicional($idAdicional, $idPlano) === false) {
                        $this->returnClass['error'] = "ID do plano adicional inválido!";
                        return;
                    }
                    //else {
                        if ($idAdicional==736) {

                            // Verifica a quantidade de pontos adicionais
                            $countPontoAdicional++;
                            if ( $countPontoAdicional > $limitPontoAdicional ) {
                                $this->returnClass['error'] = "Ultrapassou limite de pontos adicionais!";
				                return;
                            }

                            // Obriga novo modelo e mac
                            $idModeloAdicional = intval( $_POST['id_modelo'][$posModelos] );
                            if ($idModeloAdicional > 0) {
                            	if ($this->validaModelo( DECODER_PADRAO , $idModeloAdicional) == false) {
	                                $this->returnClass['error'] = "ID do modelo para o plano adicional é inválido!";
	                                return;
	                            } 
                            }
                            
                            // Valida MAC
                            $MACAdd = preg_replace("/[^A-F0-9]/", "", strtoupper( trim( $_POST["mac"][$posModelos] ) ) );
                            if(!empty($MACAdd) && !is_array($_POST["email_usuario"])) 
                            {

                                if ( addslashes(trim($_POST["email_usuario"])) == '') {
                                    if ($MACAdd == '') {
                                        $this->returnClass['error'] = "MAC para o plano adicional não informado!";
                                        return;
                                    }
                                    if ( strlen($MACAdd) != 12 ) {
                                        $this->returnClass['error'] = "MAC para o plano adicional informado é inválido!";
                                        return;
                                    }
                                    else {
                                        $queryMAC = $this->db->query("SELECT COUNT(*) FROM tab_clientes_comodatos WHERE clc_mac_address = '".$MACAdd."' AND ccs_id IN (1, 2)");
                                        $dadosMAC = $this->db->resultado($queryMAC);
                                        if ($dadosMAC) {
                                            $this->returnClass['error'] = "O MAC para o plano adicional já está sendo utilizado!";
                                            return;
                                        }
                                    }
                                }
                                else {
                                    if ( strlen($MACAdd) > 0 && strlen($MACAdd) != 12 ) {
										$this->returnClass['error'] = "MAC para o plano adicional informado é inválido!";
                                        return;
                                    }
                                    elseif ( strlen($MACAdd) > 0 ) {
                                        $queryMAC = $this->db->query("SELECT COUNT(*) FROM tab_clientes_comodatos WHERE clc_mac_address = '".$MACAdd."' AND ccs_id IN (1, 2)");
                                        $dadosMAC = $this->db->resultado($queryMAC);
                                        if ($dadosMAC) {
                                            $this->returnClass['error'] = "O MAC para o plano adicional já está sendo utilizado!";
                                            return;
                                        }
                                    }
                                }
                                $listAdicionais[] = array('id'=>$idAdicional,  'modelo'=>$idModeloAdicional,'mac'=>$MACAdd, 'type'=>'adicional');
                                $posModelos++;
                            } else {
                                $listAdicionais[] = array('id'=>$idAdicional,  'modelo'=>'','mac'=>'','type'=>'');
                            }
                        }
                        else {
                            // Verificar se é VOD
                            
                            if ( $this->validaVODAdicional($idAdicional, $this->idParceiro) === false) {
                                
                                $this->returnClass['error'] = "Plano VOD não disponível!";
                                return;
                                
                            }
                            
                            $listAdicionais[] = array('id'=>$idAdicional,  'modelo'=>'','mac'=>'','type'=>'');
                            
                        }
                    //}
                }
            }
            
            require_once '../_modulos/clientes_iptv/clientes_iptv.class.inc.php';
            $intranetClienteIPTV = new clientes_iptv($this->db, null, false);
            if(!$ehMultiService) {
                if($podeSepararPontos && is_array($_POST["email_usuario"])) {
                    foreach($_POST["email_usuario"] as $email) {
                        $email = addslashes(trim($email));
                        
                        if ( $email == '' ) {
                            $this->returnClass['error'] = "E-mail para o usuário não informado!";
                            return;
                        }
                        
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $this->returnClass['error'] = "Usuário {$email} não é um e-mail válido!";
                            return;
                        }
    
                        if ($intranetClienteIPTV->buscaIdPorUsuario($email) != null) {
                            $this->returnClass['error'] = "Usuário {$email} já está em uso!";
                            return;
                        }
                    }
                } else {
                    if ( addslashes(trim($_POST["email_usuario"])) != '') {
                        $_POST["email_usuario"] = addslashes(trim($_POST["email_usuario"]));
                        
                        if ($_POST["email_usuario"] == '') {
                            $this->returnClass['error'] = "E-mail para o usuário não informado!";
                            return;
                        }
                        
                        if (!filter_var($_POST["email_usuario"], FILTER_VALIDATE_EMAIL)) {
                            $this->returnClass['error'] = "Usuário {$_POST["email_usuario"]} não é um e-mail válido!";
                            return;
                        }
                        
                        if ($intranetClienteIPTV->buscaIdPorUsuario($_POST["email_usuario"]) != null) {
                            $this->returnClass['error'] = "Usuário {$_POST["email_usuario"]} já está em uso!";
                            return;
                        }
                    }
                }
            }
        }
        
        require_once '_modulos/assinaturas/assinaturas.class.inc.php';
        $intranetAssinaturas = new assinaturas($this->db, null, false);
        
        $dados_cliente = $intranetAssinaturas->buscaDadosCliente($idCliente);
        
        $listAssinaturas = array();
        $idAssinaturaPrincipal = 0;
		foreach ($listAdicionais AS $assAdicionais) {
            $idAssinaturaAtual = $intranetAssinaturas->adicionarAssinatura($dados_cliente, null, $assAdicionais['id'], 2, ($assAdicionais['type']!='principal'?$idAssinaturaPrincipal:null) );
            if ($assAdicionais['type'] == 'principal') {
                $idAssinaturaPrincipal = $idAssinaturaAtual;
            }
            $listAssinaturas[] = $idAssinaturaAtual;
        }
        unset($assAdicionais);
 
        require_once '_modulos/contratos/contratos.class.inc.php';
        require_once('_global/funcoes.class.inc.php');
        //require_once '../_modulos/clientes_iptv/clientes_iptv.class.inc.php';
        $intranetContratos = new contratos($this->db, null, false);
        
        $contrato_tipo = $intranetContratos->buscaDadosTipo(11);
        $contrato_id = $intranetContratos->geraContrato($dados_cliente, $contrato_tipo, $serviceId, $listAssinaturas );
        if ( $idContratoOrigem != '') {
            
            $this->setContratoOrigem($contrato_id, $idContratoOrigem);
        }

        if($ehMultiService) {

            $this->db->query("UPDATE tab_contratos SET cor_data_ativacao = NOW(), crs_id = 18 WHERE cor_id = $contrato_id");
            $intranetContratos->registraComentario($contrato_id, 16, null, null);
        }
		
		if ($mobile) {
			// Registra o usuário para versão Mobile
            if( is_array($_POST["email_usuario"])) {
                $userCriado = $this->criarUsuarioMobile($contrato_id, $_POST["email_usuario"]);
            } else {
                $userCriado = $this->criarUsuarioMobile($contrato_id, addslashes(strtolower(trim($_POST["email_usuario"]))) );
            }
            if($userCriado === false) {
                $this->returnClass['error'] = "ID do plano adicional inválido!";
                return;
            }
        }
        else {

            if(!$ehMultiService) {
                if ( addslashes(trim($_POST["email_usuario"])) != '' || count($_POST["email_usuario"]) > 0 ) {
                    if( is_array($_POST["email_usuario"])) {
                        $userCriado = $this->criarUsuarioMobile($contrato_id, $_POST["email_usuario"] );
                    } else {
                        $userCriado = $this->criarUsuarioMobile($contrato_id, addslashes(strtolower(trim($_POST["email_usuario"]))) );
                    }
                }
                
                // Pontos registrados
                $posEquipamento = 0;
                $equipamentoClienteQuery = $this->db->query("SELECT clc_id FROM tab_clientes_comodatos WHERE cli_id='".$idCliente."' and cor_id = '".$contrato_id."'");
                while (($v = $this->db->resultadoArray($equipamentoClienteQuery)) != false) {
                    $idEquipamento[$posEquipamento] = $v['clc_id'];
                    $posEquipamento++;
                }
    
                
                $listMacs = array();
                if($podeSepararPontos) {
                    $c = 0;
                    foreach($_POST["mac"] as $MAC) {
                        $MAC = preg_replace("/[^A-F0-9]/", "", strtoupper(trim($MAC) ) );
                        $listMacs[] = array('id'=>$idPlano,  'modelo'=>$_POST["id_modelo"][$c] ,'mac'=>$MAC, 'type'=>'principal');
                        $c++;
                    }
    
                    $posMac = 0;
                    foreach ($listAssinaturas AS $assinatura) {
                        if ( !isset($idEquipamento[$posMac]) ) {
                            $idEquipamento[$posMac] = 0;
                        }
                        
                        if($idAssinaturaPrincipal == $assinatura) {
                            $c = 0;
                            foreach ($listMacs as $item) {
                                if($item['type'] == "principal" ) {
                                    $codMAC = (isset($listMacs[$c]['mac'])?$listMacs[$c]['mac']:$MAC);
                                    $codModelo = (isset($listMacs[$c]['modelo'])?$listMacs[$c]['modelo']:$idModelo);
                                    if ($listMacs[$c]['modelo'] != '') {
                                        if ($codModelo > 0) {
                                            $this->trocarMAC($idEquipamento[$posMac], $idCliente, $assinatura, $contrato_id,$codMAC,DECODER_PADRAO,$codModelo);
                                            $idEquipamento[$posMac] = 0;
                                        }
                                    }
                                    unset($listMacs[$c]);
                                }
                                $c++;
                            }
                        } else {
                            $c = 0;
                            foreach ($listMacs as $item) {
                                $codMAC = (isset($listMacs[$c]['mac'])?$listMacs[$c]['mac']:$MAC);
                                $codModelo = (isset($listMacs[$c]['modelo'])?$listMacs[$c]['modelo']:$idModelo);
                                if ($listMacs[$c]['modelo'] != '') {
                                    if ($codModelo > 0) {
                                        $this->trocarMAC($idEquipamento[$posMac], $idCliente, $assinatura, $contrato_id,$codMAC,DECODER_PADRAO,$codModelo);
                                    }
                                }
                                unset($listMacs[$c]);
                                $c++;
                            }
                        }
                       
                        $posMac++;
                    }
                } else {
                    $posMac = 0;
                    foreach ($listAssinaturas AS $assinatura) {
                        if ( !isset($idEquipamento[$posMac]) ) {
                            $idEquipamento[$posMac] = 0;
                        }
                        
                        if ($listAdicionais[$posMac]['modelo'] != '') {
                            $codMAC = (isset($listAdicionais[$posMac]['mac'])?$listAdicionais[$posMac]['mac']:$MAC);
                            $codModelo = (isset($listAdicionais[$posMac]['modelo'])?$listAdicionais[$posMac]['modelo']:$idModelo);
                            if ($codModelo > 0) {// && $codMAC != ''
                                $this->trocarMAC($idEquipamento[$posMac], $idCliente, $assinatura, $contrato_id,$codMAC,DECODER_PADRAO,$codModelo);
                            }
                        }
                        $posMac++;
                    }
                }
            }
        }
        if(!$ehMultiService) {
            $this->publicIPTV( $contrato_id, $podeSepararPontos);
        }
        
        unset($this->returnClass);
        $this->returnClass['retorno_status'] = false;
        
        $dados_contrato = $intranetContratos->buscaDadosContrato($contrato_id);
        $this->returnClass['retorno_status'] = true;
        $this->returnClass['id'] = $dados_contrato['id'];
        $this->returnClass['codigo'] = $dados_contrato['codigo'];
        $this->returnClass['tipo'] = $this->textCharsetOutput( $dados_contrato['tipo_nome'] );
        $this->returnClass['servico'] = $this->textCharsetOutput( $dados_contrato['servico_nome'] );
        $this->returnClass['data_geracao'] = $dados_contrato['data_geracao'];
        $this->returnClass['data_ativacao'] = $dados_contrato['data_ativacao'];
        $this->returnClass['status'] = $this->textCharsetOutput( $dados_contrato['status_nome'] );
        $this->returnClass['retorno_status'] = true;
        if($ehMultiService) {
            $this->returnClass['assinaturas'] = $this->getAssinaturasContratoMultiservise( $dados_contrato['id']? $dados_contrato['id'] : $contrato_id );
        } else {
            $this->returnClass['assinaturas'] = $this->getAssinaturasContrato( $dados_contrato['id']? $dados_contrato['id'] : $contrato_id );
        }
		$this->returnClass['usuario'] = $this->listaEmailsIPTV( $dados_contrato['id']? $dados_contrato['id'] : $contrato_id);
    }
    
    
    function publicIPTV($idContrato, $podeSepararPontos = false) {
        
        if (DB_NAME == 'tvintranet') {
            $queryCliente = $this->db->query("SELECT cip_id AS id FROM tab_clientes_iptv WHERE cor_id='$idContrato'");
            require_once '../_modulos/clientes_iptv/clientes_iptv.publicacao.class.inc.php';
			
			$publicacao = new clientes_iptv_publicacao($this->db);
			
			if($podeSepararPontos) {
				while (($dadosIPTV = $this->db->resultadoArray($queryCliente)) != false) {
                    $publicacao->publicar($dadosIPTV['id']);
                }
            } else {
                $publicacao = new clientes_iptv_publicacao($this->db);
                $dadosIPTV = $this->db->resultadoArray($queryCliente);
                $publicacao->publicar($dadosIPTV['id']);
            }
        }
    }
    
    function enviardocumentacao() {
        $idCliente = intval($this->urlClass[2]);
        if ($idCliente == 0) {
            $this->returnClass['error'] = "ID do cliente não informado!";
            return;
        }
        $idContrato = intval( @$this->urlClass[3] );
        if ($idContrato == 0) {
            $this->returnClass['error'] = "ID do contrato não informado ou inválido!";
            return;
        }
        
        define('DIR_RAIZ', '../../');
        define('DIR_GLOBAL', '../../');
        
        $this->conectaDB();
        require_once '../_modulos/contratos/contratos.class.inc.php';
        // Verifica se cliente é do parceiro
        $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".$idCliente."' and clo_id = '".$this->idParceiro."'");// and (cls_id='1' OR cls_id='2' OR cls_id='4')
        $dadosCliente = $this->db->resultadoArray($queryCliente);
        if (!isset($dadosCliente['cli_id'])) {
            $this->returnClass['error'] = "ID do cliente inválido!";
            return;
        }
        
        $intranetContratos = new contratos($this->db, null, false);        
        $dados = $intranetContratos->buscaDadosContrato($idContrato);
        if ($dados['cliente'] > 0) {
            if ($idCliente == $dados['cliente']) {
                
                $extensao = $intranetContratos->pegaExtensao($_POST["nome"]);  //$this->pegaExtensao($_FILES["arquivo"]["name"]);
                if ($extensao != 'pdf') {   
                    $this->returnClass['error'] = "Formato do arquivo inválido! (aceitos em formato PDF)";
                    return;
                }
                else {
                    $arquivo = md5(sha1("Olé {$dados["codigo"]} Telecom"));
                    $conteudoArquivo = base64_decode( $_POST["conteudo"] );
                    
                    file_put_contents('../contratos/'.$arquivo, $conteudoArquivo);
                    if (file_exists('../contratos/'.$arquivo)) {
                        // verifica se arquico gerado é um pdf mesmo.
                        $intranetContratos->registraComentario($idContrato, 34);
                        
                        $this->db->query("UPDATE tab_contratos SET cor_arquivo_extensao = '$extensao', crs_id = IF(crs_id = 19, 20, crs_id) WHERE cor_id = '$idContrato'");
                        
                        $this->returnClass['retorno_status'] = true;
                        $this->returnClass['messagem'] = 'Arquivo enviado com sucesso!';
                    }
                    else {
                        $this->returnClass['error'] = "Falha ao escrever arquivo!";
                        return; 
                    }
                    
                    
                    $_POST["conteudo"] = substr( $_POST["conteudo"], 0 , 500);
                }
            }
            else {
                $this->returnClass['error'] = "ID do contrato não pertence ao cliente informado ou não localizado!";
                return;
            }
        }
        else {
            $this->returnClass['error'] = "ID do contrato não localizado!";
            return;
        }
        
    }
    
    function validaPlanoPrincipal($idPlano) {
        $this->conectaDB();
        $queryPlano = $this->db->query("SELECT * FROM tab_planos WHERE pla_id={$idPlano} and plt_id=1");
        $dadosPlano = $this->db->resultadoArray($queryPlano);
        return (isset($dadosPlano['pla_id'])?true:false);
    }

    function validaQuantidadesDePontos($idPlano, $plano_adicional) {
        
        $qtdPlanoAdicional = count($plano_adicional);
        $this->conectaDB();
        $queryPlano = $this->db->query("SELECT * FROM tab_planos WHERE pla_id={$idPlano}");
        $dadosPlano = $this->db->resultadoArray($queryPlano);
        $parametros = unserialize( $dadosPlano['pla_parametros'] );
        
        $total = 0;
        if (isset($parametros['iptv_stb_quantidade'])) {
            $total = $total + $parametros['iptv_stb_quantidade'];
        } 
        if (isset($parametros['iptv_aplicativos_quantidade'])) {
            $total = $total + $parametros['iptv_aplicativos_quantidade'];
        } 
        
        $total = $total + $qtdPlanoAdicional;
        
        
        $quantidade['aplicativos'] = $parametros['iptv_aplicativos_quantidade'];
        $quantidade['pontos'] = $parametros['iptv_stb_quantidade'];
        $quantidade['adicional'] = $qtdPlanoAdicional;
        $quantidade['total'] = $total;
        
        return $quantidade;
    }

    function validaSeJaTemMultiservice($cliente, $plano) {

        $sql = "
            SELECT 
                COUNT(*) AS qtd, 
                p.pla_nome AS nome
            FROM 
                rel_ass_cor_estacontido ac
                INNER JOIN tab_contratos c ON c.cor_id = ac.cor_id 
                INNER JOIN tab_assinaturas a ON ac.ass_id = a.ass_id 
                INNER JOIN tab_planos p ON p.pla_id = a.pla_id 
                INNER JOIN tab_planos_multiservice m ON m.pla_id = p.pla_id
            WHERE 
                c.crs_id NOT IN (5,22,23,24)
                AND c.cli_id = {$cliente}
            LIMIT 1";

        $this->conectaDB();
        $query = $this->db->query($sql);
        $dados = $this->db->resultadoArray($query);

        if ($dados['qtd'] > 0) {
            return $dados['nome']; // já possui multiservice
        } else {
            return false;
        }
    }
    
    function validaMultiservice($idPlano) {

        $sql = "SELECT * FROM tab_planos_multiservice WHERE pla_id ={$idPlano}";
        $this->conectaDB();
        $queryCancelamento = $this->db->query($sql);
        if ($this->db->linhas($queryCancelamento)) {
            return true;
        } else {
            return false;
        }
    }
    
    function getServiceIdPlanoPrincipal($idPlano) {
        $this->conectaDB();
        $queryPlano = $this->db->query("SELECT * FROM tab_planos WHERE pla_id={$idPlano} and plt_id=1");
        $dadosPlano = $this->db->resultadoArray($queryPlano);
        return $dadosPlano['pls_id'];
    }

    function clienteCPF($cliente) {
        
        $this->conectaDB();
        $sql = "SELECT cli_cpf_cnpj FROM tab_clientes WHERE cli_id = " . $cliente;  
        $query = $this->db->query($sql);
        $dados = $this->db->resultadoArray($query);

        if (!isset($dados['cli_cpf_cnpj']) || empty($dados['cli_cpf_cnpj'])) {
            return false;
        }

        if (!$this->ehCPF($dados['cli_cpf_cnpj'])) {
            return false;
        }

        return true;
    }

    /**
     * Valida CPF apenas pela quantidade de números
     * CPF: exatamente 11 dígitos
     * Rejeita se todos os dígitos são iguais
     * @param string $cpf
     * @return bool
     */
    private function ehCPF($cpf)
    {
        $numeros = preg_replace('/[^0-9]/', '', (string)$cpf);
        $tamanho = strlen($numeros);

        // Valida apenas CPF: 11 dígitos
        if ($tamanho !== 11) {
            return false;
        }

        // Rejeita se todos os dígitos são iguais
        if (preg_match('/^(\d)\1{10}$/', $numeros)) {
            return false;
        }

        return true;
    }

    function podeSepararPontos($idParceiro) {
        
        $this->conectaDB();
        $sql = "SELECT clo_separar_pontos AS separar_pontos FROM tab_clientes_origens WHERE clo_id = " . $idParceiro;  
        $query = $this->db->query($sql);
        $dados = $this->db->resultadoArray($query);

        if($dados['separar_pontos'] == "1") {
            return true;
        }
        return false;
    }
    
    function validaPlanoMobile($idPlano) {
        
	if ( $idPlano == '751' || $idPlano == '753') {  
            return true;
        }
        else {
            return false;
        }    
        
        /*$this->conectaDB();
        $queryPlano = $this->db->query("SELECT * FROM tab_planos WHERE pla_id={$idPlano} and plt_id=1");
        $dadosPlano = $this->db->resultadoArray($queryPlano);
        
        $parametros = unserialize( $dadosPlano['pla_parametros'] );
        
        return (intval($parametros['iptv_stb_quantidade'])==0?true:false);*/
    }
    
    /**
     * Valida se o plano adicional é aplicável ao plano principal
     * @param int $idAdicional - ID do plano adicional
     * @param int $idPlano - ID do plano principal
     * @return bool
     */
    function validaPlanoAdicional($idAdicional, $idPlano) {
        $this->conectaDB();
        $queryPlano = $this->db->query("SELECT * FROM tab_planos WHERE pla_id={$idAdicional} and plt_id=2");
        $dadosPlano = $this->db->resultadoArray($queryPlano);
        
        if ( isset($dadosPlano['pla_id']) ) {
        	
        	$parametros = unserialize($dadosPlano["pla_parametros"]);

            if ( array_search($idPlano.'', $parametros["aplicavel"], true) !== false ) {    
	        	
	        	return true;
	        	
	        }
	        return false;
        	
        }
        else {
        	
        	return false;
        	
        }
        
        //return (isset($dadosPlano['pla_id'])?true:false);
        
    }
    
    
    function validaVODAdicional($idAdicional) {
        $this->conectaDB();
        $queryPlano = $this->db->query("SELECT * FROM tab_planos WHERE pla_id={$idAdicional} and plt_id=2");
        $dadosPlano = $this->db->resultadoArray($queryPlano);
        
        require_once '_modulos/assinaturas/assinaturas.class.inc.php';
        $intranetAssinaturas = new assinaturas($this->db, null, false);  
        
        $vod_pacotes = -1;
        
        $parametros = unserialize($dadosPlano["pla_parametros"]);
        if ($parametros["vod_telas"] > 0) {
            
            if ($vod_pacotes == -1) {
                $vod_pacotes = $intranetAssinaturas->buscaPacotesVodDisponivel($this->idParceiro);
            }
            
            if ($vod_pacotes == 0) {
                return false;
            }
            else {
                return true;
            }
            
        }
        else {
            return true;
        }
    }
    
    
    
    function __destruct() {
        
    }
    
}