<?

if ($_GET["a"]) {

	global $bancodedados;

	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$clientes_autenticacoes = controle::instanciaObjeto("clientes_autenticacoes", $bancodedados, $seguranca->permissoes, false);

	if ($_GET["a"] == "buscar_autenticacoes" && $_GET["cliente"]) {

		$listagem = $clientes_autenticacoes->geraClientesAutenticacoesListagem($clientes_autenticacoes->buscaClientesAutenticacoes($_GET["cliente"]));
		$botao_adicionar = $clientes_autenticacoes->geraBotaoAdicionar($_GET["cliente"]);
		print "if (document.getElementById(\"clientes_autenticacoes_listagem\")) document.getElementById(\"clientes_autenticacoes_listagem\").innerHTML = '".addslashes($listagem)."';";
		print "if (document.getElementById(\"clientes_autenticacoes_adicionar\")) document.getElementById(\"clientes_autenticacoes_adicionar\").innerHTML = '".addslashes($botao_adicionar)."';";

	} elseif ($_GET["a"] == "verifica_mac" && $_GET["mac"]) {

		$erro_mac = $clientes_autenticacoes->verificaMAC($_GET["mac"], $_GET["autenticacao_id"]);
		if ($erro_mac) {
			print "alert('$erro_mac');";
			print "if (document.getElementById(\"equipamento_mac\")) document.getElementById(\"equipamento_mac\").value = '';";
			print "if (document.getElementById(\"equipamento_mac\")) document.getElementById(\"equipamento_mac\").focus();";
		}

	} elseif ($_GET["a"] == "muda_assinatura" && $_GET["assinatura_id"]) {

		// Velocidade
		print 'document.getElementById("velocidade").innerHTML = "'.$clientes_autenticacoes->buscaVelocidadeAssinatura($_GET["assinatura_id"]).'";';
		
		$servico_id = $clientes_autenticacoes->buscaServicoAssinatura($_GET["assinatura_id"]);

		if (in_array($servico_id, array(1, 2, 32))) {
			// Olé Condomínios
			print 'document.getElementById("linha_ssid").style.display = "none";';
			print 'document.getElementById("campo_ssid").innerHTML = "";';
			print 'document.getElementById("linha_ap_ip").style.display = "none";';
			print 'document.getElementById("campo_ap_ip").innerHTML = "";';
			print 'document.getElementById("linha_concentrador").style.display = "none";';
			print 'document.getElementById("campo_concentrador").innerHTML = "";';
			print 'document.getElementById("linha_endereco_ip").style.display = "none";';
			print 'document.getElementById("equipamento").style.display = "none";';
			print 'aguardando_verificacao = false;';

		} else if (in_array($servico_id, array(3, 4, 9, 12, 30, 34, 35, 36))) {
			// Olé Empresas/PME/Monitoramento
			print 'document.getElementById("linha_ssid").style.display = "none";';
			print 'document.getElementById("campo_ssid").innerHTML = "";';
			print 'document.getElementById("linha_ap_ip").style.display = "none";';
			print 'document.getElementById("campo_ap_ip").innerHTML = "";';
			print 'document.getElementById("linha_concentrador").style.display = "table-row";';
			print 'document.getElementById("campo_concentrador").innerHTML = \''.$clientes_autenticacoes->geraSelect($clientes_autenticacoes->buscaConcentradores(), null, true, "concentrador", "100%").'\';';
			print 'document.getElementById("linha_endereco_ip").style.display = "table-row";';
			print 'document.getElementById("equipamento").style.display = "none";';
			print 'aguardando_verificacao = false;';
			
		} else if (in_array($servico_id, array(5))) {
			// Olé Casa
			print 'document.getElementById("linha_ssid").style.display = "none";';
			print 'document.getElementById("campo_ssid").innerHTML = "";';
			print 'document.getElementById("linha_ap_ip").style.display = "none";';
			print 'document.getElementById("campo_ap_ip").innerHTML = "";';
			print 'document.getElementById("linha_concentrador").style.display = "table-row";';
			print 'document.getElementById("campo_concentrador").innerHTML = \''.$clientes_autenticacoes->geraSelect($clientes_autenticacoes->buscaConcentradores(), null, true, "concentrador", "100%").'\';';
			print 'document.getElementById("linha_endereco_ip").style.display = "none";';
			print 'document.getElementById("equipamento").style.display = "none";';
			print 'aguardando_verificacao = false;';
			
		} else if (in_array($servico_id, array(28))) {
			// Olé Residencial
			print 'document.getElementById("linha_ssid").style.display = "table-row";';
			print 'document.getElementById("campo_ssid").innerHTML = \''.$clientes_autenticacoes->geraSelect($clientes_autenticacoes->buscaInterfaceSSIDs(), null, true, "ssid", "100%", "mudaSSID(this.value)").'\';';
			print 'document.getElementById("linha_ap_ip").style.display = "table-row";';
			print 'document.getElementById("campo_ap_ip").innerHTML = "";';
			print 'document.getElementById("linha_concentrador").style.display = "none";';
			print 'document.getElementById("campo_concentrador").innerHTML = "";';
			print 'document.getElementById("linha_endereco_ip").style.display = "none";';
			print 'document.getElementById("equipamento").style.display = "table-row-group";';
			print 'aguardando_verificacao = true;';
			
		} else if (in_array($servico_id, array(38))) {
			// Olé Fibra
			print 'document.getElementById("linha_ssid").style.display = "none";';
			print 'document.getElementById("campo_ssid").innerHTML = "";';
			print 'document.getElementById("linha_ap_ip").style.display = "none";';
			print 'document.getElementById("campo_ap_ip").innerHTML = "";';
			print 'document.getElementById("linha_concentrador").style.display = "table-row";';
			print 'document.getElementById("campo_concentrador").innerHTML = \''.$clientes_autenticacoes->geraSelect($clientes_autenticacoes->buscaConcentradores(), null, true, "concentrador", "100%").'\';';
			print 'document.getElementById("linha_endereco_ip").style.display = "none";';
			print 'document.getElementById("equipamento").style.display = "none";';
			print 'aguardando_verificacao = false;';
			
		}

	} elseif ($_GET["a"] == "muda_ssid" && $_GET["ssid_id"]) {

		$dados_ssid = $clientes_autenticacoes->buscaDadosSSID($_GET["ssid_id"]);
			
		if ($dados_ssid["tipo_id"] == 1) {

			$ssid_ip = $clientes_autenticacoes->buscaIpSSID($_GET["ssid_id"]);

			print "document.getElementById('verificar').disabled = true;";
			print "document.getElementById('verificar').className = 'buttonAzulEscuroBranco11disabled';";
			print "document.getElementById('campo_ap_ip').innerHTML = '<a href=\"http://$ssid_ip\" target=\"_blank\" >$ssid_ip</a>';";
			print "document.getElementById('equipamento_ip').innerHTML = '';";
			print 'aguardando_verificacao = false;';

		} else {

			$interface_id = $dados_ssid["interface_id"];

			if ($_GET["autenticacao_id"]) {
				$clientes_autenticacoes->buscaDadosItem($_GET["autenticacao_id"]);
			}
			$ssid_ip = $clientes_autenticacoes->buscaIpSSID($_GET["ssid_id"]);
			if ($_GET["autenticacao_id"] && ($clientes_autenticacoes->dados_item["interface_id"] == $interface_id) && $clientes_autenticacoes->dados_item["equipamento_ip"] && $clientes_autenticacoes->ehIpPoolInterface($interface_id, $clientes_autenticacoes->dados_item["equipamento_ip"])) {
//				$ip = array("ip" => $clientes_autenticacoes->dados_item["equipamento_ip"]);
				$ip = $clientes_autenticacoes->buscaIpPoolInterface($interface_id, $clientes_autenticacoes->dados_item["equipamento_ip"]);
			} else {
				$ip = $clientes_autenticacoes->buscaIpPoolInterface($interface_id);
			}
			if ($ip) {
				print "document.getElementById('verificar').disabled = false;";
				print "document.getElementById('verificar').className = 'buttonAzulEscuroBranco11';";
				print "document.getElementById('campo_ap_ip').innerHTML = '<a href=\"http://$ssid_ip\" target=\"_blank\" >$ssid_ip</a>';";
				print "document.getElementById('equipamento_ip').innerHTML = '<a href=\"http://{$ip["ip"]}\" target=\"_blank\" id=\"ip_link\">{$ip["ip"]}</a> (Máscara: {$ip["mascara"]}, Gateway: {$ip["gateway"]})';";
				print "if (document.getElementById('clientes_count')) document.getElementById('clientes_count').innerHTML = '{$dados_ssid["clientes"]}';";
			} else {
				print "document.getElementById('verificar').disabled = true;";
				print "document.getElementById('verificar').className = 'buttonAzulEscuroBranco11disabled';";
				print "document.getElementById('campo_ap_ip').innerHTML = '<a href=\"http://$ssid_ip\" target=\"_blank\" >$ssid_ip</a>';";
				print "document.getElementById('equipamento_ip').innerHTML = 'Nenhum IP disponível';";
				print "if (document.getElementById('clientes_count')) document.getElementById('clientes_count').innerHTML = '{$dados_ssid["clientes"]}';";
			}
			print 'aguardando_verificacao = true;';
		}

	} elseif ($_GET["a"] == "verificar_conexao" && $_GET["ip"]) {

		print "document.getElementById('verificar').value = 'Verificar';";
		print "document.getElementById('verificar').disabled = false;";
		print "document.getElementById('verificar').className = 'buttonAzulEscuroBranco11';";

		$ssid_ip = $clientes_autenticacoes->buscaIpSSID($_GET["ssid_id"]);
		$dados_ssid = $clientes_autenticacoes->buscaDadosSSID($_GET["ssid_id"]);
		$dados_cliente = $clientes_autenticacoes->buscaCliente($_GET["cliente_id"]);

		$nome_equipamento = $clientes_autenticacoes->geraNomeEquipamento($_GET["cliente_id"]);

		if ($dados_cliente["rwt"]) {
			$dados = array();
		} else {
			$dados = $clientes_autenticacoes->verificarConexao($_GET["ip"], $ssid_ip, $dados_ssid["tipo_id"], $nome_equipamento);
		}

		if ($dados["erro"]) {

			print "document.getElementById('qualidade').style.display = 'none';";
			print "alert('{$dados["erro"]}');";

		} else {

			print "document.getElementById('qualidade').style.display = 'table-row-group';";
			
			$autorizado = true;
			$autorizado_cliente = true;
			
			for ($i = 0; $i < count($dados); $i++) {
				
				// Cliente
				if (array_key_exists("signal", $dados[$i])) {
					$autorizado = $autorizado && $dados[$i]["signal"] >= clientes_autenticacoes::MIN_SIGNAL;
					$autorizado_cliente = $autorizado_cliente && $dados[$i]["signal"] >= clientes_autenticacoes::MIN_SIGNAL;
					$signal_formatado[] = $dados[$i]["signal"] >= clientes_autenticacoes::MIN_SIGNAL ? "<span style=\"color:green\">{$dados[$i]["signal"]}</span>" : "<span style=\"color:red\">{$dados[$i]["signal"]}</span>";
				}
				if (array_key_exists("snr", $dados[$i])) {
					$autorizado = $autorizado && $dados[$i]["snr"] >= clientes_autenticacoes::MIN_SNR;
					$autorizado_cliente = $autorizado_cliente && $dados[$i]["snr"] >= clientes_autenticacoes::MIN_SNR;
					$snr_formatado[] = $dados[$i]["snr"] >= clientes_autenticacoes::MIN_SNR ? "<span style=\"color:green\">{$dados[$i]["snr"]}</span>" : "<span style=\"color:red\">{$dados[$i]["snr"]}</span>";
				}
				if (array_key_exists("ccq", $dados[$i])) {
					$autorizado = $autorizado && $dados[$i]["ccq"] >= clientes_autenticacoes::MIN_CCQ;
					$autorizado_cliente = $autorizado_cliente && $dados[$i]["ccq"] >= clientes_autenticacoes::MIN_CCQ;
					$ccq_formatado[] = $dados[$i]["ccq"] >= clientes_autenticacoes::MIN_CCQ ? "<span style=\"color:green\">{$dados[$i]["ccq"]}</span>" : "<span style=\"color:red\">{$dados[$i]["ccq"]}</span>";
				}
				
				// AP
				if (array_key_exists("apsignal", $dados[$i])) {
					$autorizado = $autorizado && $dados[$i]["apsignal"] >= clientes_autenticacoes::MIN_SIGNAL;
					$apsignal_formatado[] = $dados[$i]["apsignal"] >= clientes_autenticacoes::MIN_SIGNAL ? "<span style=\"color:green\">{$dados[$i]["apsignal"]}</span>" : "<span style=\"color:red\">{$dados[$i]["apsignal"]}</span>";
				} elseif (array_key_exists("apsignal1", $dados[$i]) && array_key_exists("apsignal2", $dados[$i])) {
					$autorizado = $autorizado && $dados[$i]["apsignal1"] >= clientes_autenticacoes::MIN_SIGNAL && $dados[$i]["apsignal2"] >= clientes_autenticacoes::MIN_SIGNAL;
					$apsignal_formatado[] = ($dados[$i]["apsignal1"] >= clientes_autenticacoes::MIN_SIGNAL ? "<span style=\"color:green\">{$dados[$i]["apsignal1"]}</span>" : "<span style=\"color:red\">{$dados[$i]["apsignal1"]}</span>")."/".($dados[$i]["apsignal2"] >= clientes_autenticacoes::MIN_SIGNAL ? "<span style=\"color:green\">{$dados[$i]["apsignal2"]}</span>" : "<span style=\"color:red\">{$dados[$i]["apsignal2"]}</span>");
				}
				if (array_key_exists("apsnr", $dados[$i])) {
					$autorizado = $autorizado && $dados[$i]["apsnr"] >= clientes_autenticacoes::MIN_SNR;
					$apsnr_formatado[] = ($dados[$i]["apsnr"] >= clientes_autenticacoes::MIN_SNR ? "<span style=\"color:green\">{$dados[$i]["apsnr"]}</span>" : "<span style=\"color:red\">{$dados[$i]["apsnr"]}</span>");
				} elseif (array_key_exists("apsnr1", $dados[$i]) && array_key_exists("apsnr2", $dados[$i])) {
					$autorizado = $autorizado && $dados[$i]["apsnr1"] >= clientes_autenticacoes::MIN_SNR && $dados[$i]["apsnr2"] >= clientes_autenticacoes::MIN_SNR;
					$apsnr_formatado[] = ($dados[$i]["apsnr1"] >= clientes_autenticacoes::MIN_SNR ? "<span style=\"color:green\">{$dados[$i]["apsnr1"]}</span>" : "<span style=\"color:red\">{$dados[$i]["apsnr1"]}</span>")."/".($dados[$i]["apsnr2"] >= clientes_autenticacoes::MIN_SNR ? "<span style=\"color:green\">{$dados[$i]["apsnr2"]}</span>" : "<span style=\"color:red\">{$dados[$i]["apsnr2"]}</span>");
				}
				if (array_key_exists("apccq", $dados[$i])) {
					$autorizado = $autorizado && $dados[$i]["apccq"] >= clientes_autenticacoes::MIN_CCQ;
					$apccq_formatado[] = $dados[$i]["apccq"] >= clientes_autenticacoes::MIN_CCQ ? "<span style=\"color:green\">{$dados[$i]["apccq"]}</span>" : "<span style=\"color:red\">{$dados[$i]["apccq"]}</span>";
				}
				
			}
			
			// Cliente
			if ($signal_formatado) {
				$signal_formatado = implode(", ", $signal_formatado);
			}
			if ($snr_formatado) {
				$snr_formatado = implode(", ", $snr_formatado);
			}
			if ($ccq_formatado) {
				$ccq_formatado = implode(", ", $ccq_formatado);
			}
			
			// AP
			if ($apsignal_formatado) {
				$apsignal_formatado = implode(", ", $apsignal_formatado);
			}
			if ($apsnr_formatado) {
				$apsnr_formatado = implode(", ", $apsnr_formatado);
			}
			if ($apccq_formatado) {
				$apccq_formatado = implode(", ", $apccq_formatado);
			}

			if ($_GET["autenticacao_id"]) {
				// Cliente
				if ($signal_formatado) {
					$comentario_verificacao[] = "<b>Signal</b>: $signal_formatado";
				}
				if ($snr_formatado) {
					$comentario_verificacao[] = "<b>SNR</b>: $snr_formatado";
				}
				if ($ccq_formatado) {
					$comentario_verificacao[] = "<b>CCQ</b>: $ccq_formatado";
				}
				// AP
				if ($apsignal_formatado) {
					$comentario_verificacao[] = "<b>AP Signal</b>: $apsignal_formatado";
				}
				if ($apsnr_formatado) {
					$comentario_verificacao[] = "<b>AP SNR</b>: $apsnr_formatado";
				}
				if ($apccq_formatado) {
					$comentario_verificacao[] = "<b>AP CCQ</b>: $apccq_formatado";
				}
				if ($comentario_verificacao) {
					$comentario_verificacao = "<b>Ação</b>: Verificação<br>".implode("<br>", $comentario_verificacao);
					$clientes_autenticacoes->registraComentario($_GET["autenticacao_id"], $comentario_verificacao, $_SESSION["usuario"]);
				}
			}

			$i = count($dados) - 1;

			print "document.getElementById('equipamento_modelo').innerHTML = '{$dados[$i]["platform"]}';";
			print "document.getElementById('equipamento_mac').innerHTML = '{$dados[$i]["mac"]}';";

			// Cliente
			if ($signal_formatado) {
				print "document.getElementById('equipamento_signal').innerHTML = '$signal_formatado';";
				print "document.getElementById('equipamento_signal').parentElement.style.display = '';";
			} else {
				print "document.getElementById('equipamento_signal').parentElement.style.display = 'none';";
			}
			if ($snr_formatado) {
				print "document.getElementById('equipamento_snr').innerHTML = '$snr_formatado';";
				print "document.getElementById('equipamento_snr').parentElement.style.display = '';";
			} else {
				print "document.getElementById('equipamento_snr').parentElement.style.display = 'none';";
			}
			if ($ccq_formatado) {
				print "document.getElementById('equipamento_ccq').innerHTML = '$ccq_formatado';";
				print "document.getElementById('equipamento_ccq').parentElement.style.display = '';";
			} else {
				print "document.getElementById('equipamento_ccq').parentElement.style.display = 'none';";
			}

			// AP
			if ($apsignal_formatado) {
				print "document.getElementById('ap_signal').innerHTML = '$apsignal_formatado';";
				print "document.getElementById('ap_signal').parentElement.style.display = '';";
			} else {
				print "document.getElementById('ap_signal').parentElement.style.display = 'none';";
			}
			if ($apsnr_formatado) {
				print "document.getElementById('ap_snr').innerHTML = '$apsnr_formatado';";
				print "document.getElementById('ap_snr').parentElement.style.display = '';";
			} else {
				print "document.getElementById('ap_snr').parentElement.style.display = 'none';";
			}
			if ($apccq_formatado) {
				print "document.getElementById('ap_ccq').innerHTML = '$apccq_formatado';";
				print "document.getElementById('ap_ccq').parentElement.style.display = '';";
			} else {
				print "document.getElementById('ap_ccq').parentElement.style.display = 'none';";
			}

			if ($_GET["autenticacao_id"] || $autorizado_cliente || $dados_cliente["origem_id"] == 9) {
				$dados[0]["ip"] = $_GET["ip"];
				$dados[0]["ssid"] = $_GET["ssid_id"];
				$dados[0]["interface"] = $clientes_autenticacoes->buscaInterfaceSSID($_GET["ssid_id"]);
				$verificacao_id = md5($_GET["ip"].$dados[0]["apmac"].$dados[0]["signal"].$dados[0]["ccq"]);
				$_SESSION["qualidade"][$verificacao_id] = $dados;
				$_SESSION["equipamento"][$verificacao_id]["equipamento_modelo"] = $dados[$i]["platform"];
				$_SESSION["equipamento"][$verificacao_id]["equipamento_ip"] = $_GET["ip"];
				$_SESSION["equipamento"][$verificacao_id]["equipamento_mac"] = $dados[$i]["mac"];
				print "document.getElementById('verificacao_id').value = '$verificacao_id';";
				print "aguardando_verificacao = false;";
				if (!$autorizado_cliente && $_GET["autenticacao_id"]) {
					
					print "if (document.getElementById(\"erro_verificacao\")) document.getElementById('erro_verificacao').style.display = 'table-row-group';";
					print "document.getElementById('reprovado').value = '1'";
				}
			}

		}

	}

} else {

	if (false) { ?><script type="text/javascript"><? }

?>

function buscaAutenticacoes(autenticacao_id, ver) {
	
	if (autenticacao_id) {
		url = "rds.js.php?m=clientes_autenticacoes&a=buscar_autenticacoes&cliente=" + autenticacao_id;
		RDSCarregar("clientes_autenticacoes_buscar_autenticacoes", url);
	}
	
}

function verAutenticacao(autenticacao_id) {
	
	if (autenticacao_id) {
		abrir("clientes_autenticacoes", "?m=clientes_autenticacoes&a=ver&i=" + autenticacao_id, 555, 470, 1);
	}
	
}

function adicionarAutenticacao(cliente_id) {
	
	if (cliente_id) {
		abrir("clientes_autenticacoes", "?m=clientes_autenticacoes&a=adicionar&i=" + cliente_id, 555, 470, 1);
	}
	
}

function gerarClienteEmailSenha(campo) {
	
	if (campo) {
		var caracteres = "abcdefghijklmnopqrstuvwxyz0123456789";
		var senha_aleatoria = "";
		while (senha_aleatoria.length < 8) {
			senha_aleatoria += caracteres.charAt(Math.floor(Math.random()*caracteres.length));
		}
		campo.value = senha_aleatoria;
	}
	
}

function mudaAssinatura(assinatura_id) {
	
	document.getElementById("velocidade").innerHTML = '';
	document.getElementById("campo_ap_ip").innerHTML = '';
	document.getElementById("linha_ssid").style.display = 'none';
	document.getElementById("linha_concentrador").style.display = 'none';
	document.getElementById("linha_endereco_ip").style.display = 'none';
	document.getElementById("equipamento").style.display = 'none';
	document.getElementById("qualidade").style.display = 'none';

	document.getElementById("endereco_ip_1").value = "";
	document.getElementById("endereco_ip_2").value = "";
	document.getElementById("endereco_ip_3").value = "";
	document.getElementById("endereco_ip_4").value = "";
	document.getElementById("equipamento_modelo").innerHTML = "";
	document.getElementById("equipamento_mac").innerHTML = "";
	document.getElementById("equipamento_ip").innerHTML = "";
	document.getElementById("equipamento_signal").innerHTML = "";
	document.getElementById("equipamento_snr").innerHTML = "";
	document.getElementById("equipamento_ccq").innerHTML = "";
	document.getElementById("ap_signal").innerHTML = "";
	document.getElementById("ap_snr").innerHTML = "";
	document.getElementById("ap_ccq").innerHTML = "";

	if (assinatura_id) {
		url = "rds.js.php?m=clientes_autenticacoes&a=muda_assinatura&assinatura_id=" + assinatura_id;
		RDSCarregar("clientes_autenticacoes_muda_assinatura", url);
	}
	
}

function validaAutenticacoesAdicionar(formulario) {
	
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	
	if (formulario.assinatura && !formulario.assinatura.value) {
		erro_vazio[erro_vazio.length] = "Assinatura";
	}
	if (aguardando_verificacao) {
		erro_invalido[erro_invalido.length] = "Aguardando verificação";
	}
	if (document.getElementById("linha_ssid").style.display != "none" && formulario.ssid && !formulario.ssid.value) {
		erro_vazio[erro_vazio.length] = "Autenticação » SSID";
	}
	if (document.getElementById("linha_concentrador").style.display != "none" && formulario.concentrador && !formulario.concentrador.value) {
		erro_vazio[erro_vazio.length] = "Autenticação » Concentrador";
	}
	if (document.getElementById("linha_endereco_ip").style.display != "none") {
		if (!formulario.endereco_ip_1.value && !formulario.endereco_ip_2.value && !formulario.endereco_ip_3.value && !formulario.endereco_ip_4.value) {
			erro_vazio[erro_vazio.length] = "Autenticação » Endereço IP";
		} else if (!formulario.endereco_ip_1.value || !formulario.endereco_ip_2.value || !formulario.endereco_ip_3.value || !formulario.endereco_ip_4.value) {
			erro_invalido[erro_invalido.length] = "Endereço IP";
		}
	}
	if (formulario.autenticador && !formulario.autenticador.value) {
		erro_vazio[erro_vazio.length] = "Autenticação » Autenticador";
	}
	if (document.getElementById("equipamento").style.display != "none" && formulario.equipamento_serie && !formulario.equipamento_serie.value) {
		erro_vazio[erro_vazio.length] = "Equipamento » Nº de Série";
	}
	
	return apresentaErros(erro_vazio, erro_invalido);
	
}

function validaErroVerificacao(formulario) {
	
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	
	if (!formulario.tecnico.value) {
		erro_vazio[erro_vazio.length] = "Técnico";
	}
	for (i = 1; i <= 3; i++) {
		selected = false;
		j = 1;
		while (pergunta = document.getElementById('pergunta_' + i + '_' + j)) {
			selected = selected || pergunta.checked;
			j++;
		}
		if (!selected) {
			erro_vazio[erro_vazio.length] = "Pergunta " + i;
		}
	}
	if (!formulario.pergunta_4.value) {
		erro_vazio[erro_vazio.length] = "Pergunta 4";
	}
	if (!formulario.pergunta_5.value) {
		erro_vazio[erro_vazio.length] = "Pergunta 5";
	}
	
	return apresentaErros(erro_vazio, erro_invalido);
	
}

function validaAutenticacoesEditar(formulario) {
	
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	
	if (aguardando_verificacao) {
		erro_invalido[erro_invalido.length] = "Aguardando verificação";
	}
	if (document.getElementById("linha_ssid").style.display != "none" && formulario.ssid && !formulario.ssid.value) {
		erro_vazio[erro_vazio.length] = "Autenticação » SSID";
	}
	if (document.getElementById("linha_concentrador").style.display != "none" && formulario.concentrador && !formulario.concentrador.value) {
		erro_vazio[erro_vazio.length] = "Autenticação » Concentrador";
	}
	if (document.getElementById("linha_endereco_ip").style.display != "none") {
		if (!formulario.endereco_ip_1.value && !formulario.endereco_ip_2.value && !formulario.endereco_ip_3.value && !formulario.endereco_ip_4.value) {
			erro_vazio[erro_vazio.length] = "Autenticação » Endereço IP";
		} else if (!formulario.endereco_ip_1.value || !formulario.endereco_ip_2.value || !formulario.endereco_ip_3.value || !formulario.endereco_ip_4.value) {
			erro_invalido[erro_invalido.length] = "Endereço IP";
		}
	}
	if (formulario.autenticador && !formulario.autenticador.value) {
		erro_vazio[erro_vazio.length] = "Autenticação » Autenticador";
	}
	if (document.getElementById("equipamento").style.display != "none" && formulario.equipamento_serie && !formulario.equipamento_serie.value) {
		erro_vazio[erro_vazio.length] = "Equipamento » Nº de Série";
	}
	
	if (document.getElementById('erro_verificacao') && document.getElementById('erro_verificacao').style.display != "none") {
		if (!formulario.tecnico.value) {
			erro_vazio[erro_vazio.length] = "Técnico";
		}
		for (i = 1; i <= 3; i++) {
			selected = false;
			j = 1;
			while (pergunta = document.getElementById('pergunta_' + i + '_' + j)) {
				selected = selected || pergunta.checked;
				j++;
			}
			if (!selected) {
				erro_vazio[erro_vazio.length] = "Pergunta " + i;
			}
		}
		if (!formulario.pergunta_4.value) {
			erro_vazio[erro_vazio.length] = "Pergunta 4";
		}
		if (!formulario.pergunta_5.value) {
			erro_vazio[erro_vazio.length] = "Pergunta 5";
		}
	}
	
	return apresentaErros(erro_vazio, erro_invalido);
	
}

function mudaSSID(ssid_id) {
	
	document.getElementById("verificar").disabled = true;
	document.getElementById("verificar").className = 'buttonAzulEscuroBranco11disabled';
	document.getElementById("qualidade").style.display = 'none';
	if (document.getElementById("erro_verificacao")) {
		document.getElementById("erro_verificacao").style.display = 'none';
	}
	document.getElementById("campo_ap_ip").innerHTML = '';
	document.getElementById("equipamento_modelo").innerHTML = "";
	document.getElementById("equipamento_mac").innerHTML = "";
	document.getElementById("equipamento_ip").innerHTML = "";
	document.getElementById("equipamento_signal").innerHTML = "";
	document.getElementById("equipamento_snr").innerHTML = "";
	document.getElementById("equipamento_ccq").innerHTML = "";
	document.getElementById("ap_signal").innerHTML = "";
	document.getElementById("ap_snr").innerHTML = "";
	document.getElementById("ap_ccq").innerHTML = "";
	aguardando_verificacao = true;

	if (ssid_id) {
		url = "rds.js.php?m=clientes_autenticacoes&a=muda_ssid&ssid_id=" + ssid_id + "&autenticacao_id=" + autenticacao_id;
		RDSCarregar("clientes_autenticacoes_muda_ssid", url);
	}
	
}

function verificarConexao() {
	
	var ip = document.getElementById("ip_link").innerHTML;
	var ssid_id = document.getElementById("ssid").value;
	
	if (ip && ssid_id) {
		document.getElementById('verificar').value = 'Aguarde...';
		document.getElementById('verificar').disabled = true;
		document.getElementById('verificar').className = 'buttonAzulEscuroBranco11disabled';
		document.getElementById('qualidade').style.display = 'none';
		if (document.getElementById("erro_verificacao")) {
			document.getElementById('erro_verificacao').style.display = 'none';
		}
		url = "rds.js.php?m=clientes_autenticacoes&a=verificar_conexao&ip=" + ip + "&ssid_id=" + ssid_id + "&cliente_id=" + cliente_id + "&autenticacao_id=" + autenticacao_id;
		RDSCarregar("clientes_autenticacoes_verificar_conexao", url);
	}
	
}

function continuarAdicionar() {
	
	if (device) {
		if (autenticacao_id) {
			window.location = "./?m=clientes_autenticacoes&a=alterar_pop&i=" + autenticacao_id + "&device=" + device;
		} else {
			window.location = "./?m=clientes_autenticacoes&a=adicionar&i=" + cliente_id + "&device=" + device;
		}
	}
	
}

<?

	if (false) { ?></script><? }

}

?>
