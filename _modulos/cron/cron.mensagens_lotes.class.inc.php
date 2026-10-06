<?
/*
set_time_limit(0);

date_default_timezone_set('America/Sao_paulo');

require_once("../../global.inc.php");

$cron = controle::instanciaObjeto("cron_mensagens_lotes", $bancodedados);
$cron->gerarMensagens();
*/
class cron_mensagens_lotes {

	var $bancodedados;

	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}

	#######################
	### Gerar Mensagens ###
	#######################

	function gerarMensagens() {
		
		$dados_lotes = $this->buscaDadosLotes();
		
		foreach ($dados_lotes as $dados_lote) {
			
			$cliente_campos = array();
			$cliente_juncoes = array();
			$cliente_filtros = array();
			$campos_padrao = array();
			$cliente_campos_serializados = array();
			$funcoes = array();

			$filtros_tratados = $this->trataFiltros($dados_lote["parametros"]["filtros"]);

			foreach ($filtros_tratados as $filtro) {
				$dados_filtro_tipo = $this->buscaDadosFiltroTipo($filtro["campo"]);
				if ($dados_filtro_tipo["parametros"]["juncao"]) {
					foreach ($dados_filtro_tipo["parametros"]["juncao"] as $dados_juncao) {
						$cliente_juncoes[] = $dados_juncao;
					}
				}
				if ($dados_filtro_tipo["parametros"]["filtro"]["where"]) {
					foreach ($dados_filtro_tipo["parametros"]["filtro"]["where"] as $dados_filtro) {
						$cliente_filtros["where"][] = str_replace(array("[comparacao]", "[valor]"), array($filtro["comparacao"], $filtro["valor"]), $dados_filtro);
					}
				}
				if ($dados_filtro_tipo["parametros"]["filtro"]["having"]) {
					foreach ($dados_filtro_tipo["parametros"]["filtro"]["having"] as $dados_filtro) {
						$cliente_filtros["having"][] = str_replace(array("[comparacao]", "[valor]"), array($filtro["comparacao"], $filtro["valor"]), $dados_filtro);
					}
				}
			}
			
			$cliente_campos[] = "tab_clientes.cli_id AS id";
			$cliente_campos[] = "tab_clientes.cli_nome AS cliente_nome";
			$cliente_campos[] = "tab_clientes.clt_id AS cliente_tipo_id";
			
			$lista_campos = $this->buscaCamposVariaveis($dados_lote["conteudo_texto"].$dados_lote["conteudo_html"]);
			
			foreach ($lista_campos as $campo) {
				if ($this->buscaCamposPadrao($campo)) {
					$campos_padrao[$campo] = $this->buscaCamposPadrao($campo);
				} elseif (($dados_campo = $this->buscaMensagensLotesCampos($campo)) != false) {
					if ($dados_campo["parametros"]["campo"]) {
						$cliente_campos[] = "{$dados_campo["parametros"]["campo"]} AS $campo";
						if ($dados_campo["parametros"]["juncao"]) {
							$cliente_juncoes = funcoes::mescla_arrays($cliente_juncoes, $dados_campo["parametros"]["juncao"]);
						}
						if ($dados_campo["parametros"]["campo_serializado"]) {
							$cliente_campos_serializados[$campo] = $dados_campo["parametros"]["campo_serializado"];
						}
					}
					if ($dados_campo["parametros"]["extra"]) {
						foreach ($dados_campo["parametros"]["extra"] as $campo_nome => $campo_extra) {
							$cliente_campos[] = "$campo_extra AS $campo_nome";
						}
					}
					if ($dados_campo["parametros"]["funcao"]) {
						$funcoes[$campo] = $dados_campo["parametros"]["funcao"];
					}
				}
			}
			
			$dados_clientes = $this->buscaClientes(implode(",\n        ", array_unique($cliente_campos)), $cliente_campos_serializados, implode("\n        ", array_unique($cliente_juncoes)), implode(" AND\n        ", array_unique($cliente_filtros["where"])), implode(" AND\n        ", array_unique($cliente_filtros["having"])), $funcoes);
			
			if ($dados_clientes) {
				foreach ($dados_clientes as $dados_cliente) {
					if (!$this->mensagemGerada($dados_lote["id"], $dados_cliente["id"])) {
						$dados_cliente = $campos_padrao ? funcoes::mescla_arrays($dados_cliente, $campos_padrao) : $dados_cliente;
						$this->geraMensagem($dados_lote, $dados_cliente);
					}
				}
			}
			
		}
		
	}

	########################
	### Enviar Mensagens ###
	########################

	var $passwords = array(
		"contato@oletelecom.com.br" => "Hy3;RqM?Wd7c!V#RuEmhg@Tffo4nH",
		"financeiro@oletelecom.com.br" => "@!9gXseNMM7hVYEcc#4!",
		"contratos@oletelecom.com.br" => "34Kv!kLz#ucnz3FaRd!@"
	);

	function enviarEmails() {

		require_once "_global/PHPMailer/PHPMailerAutoload.php";

		$this->expirarMensagens();

		$mensagens = $this->buscaMensagensEmails();

		foreach ($mensagens as $mensagem) {

			if (array_key_exists($mensagem["parametros"]["remetente_email"], $this->passwords)) {

				$mail = new PHPMailer();

				$mail->isSMTP();
				$mail->Host = "mail.ole.net.br";
				$mail->SMTPAuth = true;
				$mail->SMTPSecure = "tls";
				$mail->Port = 587;

				$mail->SMTPOptions = array (
					'ssl' => array(
						'verify_peer' => false,
						'verify_peer_name' => false,
						'allow_self_signed' => true
					)
				);

				$mail->isHTML(true);

				$mail->Username = $mensagem["parametros"]["remetente_email"];
				$mail->Password = $this->passwords[$mensagem["parametros"]["remetente_email"]];

				$mail->From = $mensagem["parametros"]["remetente_email"];
				$mail->FromName = $mensagem["parametros"]["remetente_nome"];
				$mail->Subject = $mensagem["parametros"]["assunto"];

				foreach ($mensagem["parametros"]["destinatarios"] as $destinatario) {
					$mail->addAddress($destinatario, $mensagem["parametros"]["dados_conteudo"]["cliente_nome"]);
				}

				if (key_exists("cc_email", $mensagem["parametros"]) && $mensagem["parametros"]["cc_email"]) {
					foreach ($mensagem["parametros"]["cc_email"] as $destinataria_cc) {
						$mail->addCC($destinataria_cc, $mensagem["parametros"]["cc_nome"]);
					}
				}

				if (key_exists("bcc_email", $mensagem["parametros"]) && $mensagem["parametros"]["bcc_email"]) {
					foreach ($mensagem["parametros"]["bcc_email"] as $destinataria_bcc) {
						$mail->addBCC($destinataria_bcc, $mensagem["parametros"]["bcc_nome"]);
					}
				}
				
				$imagens = array();

				preg_match_all('/\<imagem:(.*)\>/U', $mensagem["conteudo_html"], $imagens);

				if ($imagens[0]) {
					foreach ($imagens[0] as $ordem => $imagem) {
						$mensagem["conteudo_html"] = str_replace($imagem, "cid:{$imagens[1][$ordem]}@imagem", $mensagem["conteudo_html"]);
						$mail->addEmbeddedImage(DIR_RAIZ."imagens/{$imagens[1][$ordem]}", "{$imagens[1][$ordem]}@imagem");
					}
				}

				$mail->Body = $mensagem["conteudo_html"];
				$mail->AltBody = $mensagem["conteudo_texto"];

				if (strpos($mensagem["conteudo_html"], "cid:logo@wavesystem") !== false) {
					$mail->addEmbeddedImage(DIR_RAIZ."imagens/logo.jpg", "logo@wavesystem");
				}

				if (key_exists("anexos", $mensagem["parametros"]) && $mensagem["parametros"]["anexos"]) {
					foreach ($mensagem["parametros"]["anexos"] as $anexo) {
						if (key_exists("anexos", $anexo) && $anexo["arquivo"]) {
							$mail->addAttachment($anexo["arquivo"]);
						} elseif ($anexo["conteudo"]) {
							$mail->addStringAttachment($anexo["conteudo"], $anexo["nome"]);
						}
					}
				}

				if ($mail->send()) {
					$this->registraEnvioMensagem($mensagem["id"]);
					$this->registraComentarioMensagens($mensagem["id"], 2);
				}

				$mail->smtpClose();

			}

		}

	}

	function expirarMensagens() {
		
		// Expira mensagens não agendadas
		$this->bancodedados->query("
			UPDATE 
				tab_mensagens 
			SET 
				mes_id = 3 
			WHERE 
				mes_id = 1 AND 
				men_data_hora_envio IS NULL AND 
				men_data_hora_geracao < DATE_SUB(NOW(), INTERVAL 2 DAY)");
		
		// Expira mensagens agendadas
		$this->bancodedados->query("
			UPDATE 
				tab_mensagens 
			SET 
				mes_id = 3 
			WHERE 
				mes_id = 1 AND 
				men_data_hora_envio < DATE_SUB(NOW(), INTERVAL 2 DAY)");
		
	}

	#####################
	### Expirar Lotes ###
	#####################

	function expirarLotes() {
		
		$lotes = $this->buscaLotesExpirados();
		
		foreach ($lotes as $lote_id) {
			
			$this->bancodedados->query("
				UPDATE 
					tab_mensagens_lotes 
				SET 
					mls_id = 7 
				WHERE 
					mel_id = $lote_id");
					
			$this->registraComentarioMensagensLotes($lote_id, 7);
			
		}
		
	}

	######################
	### Funções Gerais ###
	######################

	function buscaCamposVariaveis($mensagem) {
		
		$campos = array();
		
		preg_match_all('/\[(.*)\]/U', $mensagem, $campos);
		
		return array_unique($campos[1]);
		
	}

	function buscaCamposPadrao($campo) {
		
		$campos = array();
		
		$campos["data"] = date("d/m/Y");
		$campos["data_extenso"] = $this->geraDataExtenso(date("d/m/Y"));
		
		return $campos[$campo];
		
	}

	function geraDataExtenso($data) {
		
		$meses = array("janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro");
		$data = explode("/", $data);
		
		return "$data[0] de {$meses[$data[1] - 1]} de $data[2]";
		
	}

	function formataDinheiro($valor, $moeda = 'R$') {
		
		$valor = preg_replace("/[^0-9]/", "", intval($valor));
		$valor = number_format($valor / 100, 2, ",", ".");
		
		if ($moeda) $valor = $moeda." ".$valor;
		
		return $valor;
		
	}

	function geraValorExtenso($valor, $dinheiro = false, $maiusculas = false) {

		if (strpos($valor, ",") > 0) {
			$valor = str_replace(".", "", $valor);
			$valor = str_replace(",", ".", $valor);
		}
		
		$singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
		$plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");

		if (!$dinheiro) {
			$singular[0] = "";
			$plural[0] = "";
			$singular[1] = "";
			$plural[1] = "";
		}

		$c = array("", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
		$d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa");
		$d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezesete", "dezoito", "dezenove");
		$u = array("", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");

		$z = 0;

		$valor = number_format($valor, 2, ".", ".");
		$inteiro = explode(".", $valor);
		
		for ($i = 0; $i < count($inteiro); $i++) {
			for($ii = strlen($inteiro[$i]); $ii < 3; $ii++) {
				$inteiro[$i] = "0".$inteiro[$i];
			}
		}

		$fim = count($inteiro) - ($inteiro[count($inteiro)-1] > 0 ? 1 : 2);
		$rt = "";
		
		for ($i = 0; $i < count($inteiro); $i++) {
			$valor = $inteiro[$i];
			$rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
			$rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
			$ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
			$r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd && $ru) ? " e " : "").$ru;
			$t = count($inteiro)-1-$i;
			$r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
			if ($valor == "000")$z++; elseif ($z > 0) $z--;
			if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t];
			if ($r) $rt = $rt.((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? (($i < $fim) ? ", " : " e ") : " ").$r;
		}

		if (!trim($rt)) $rt = "zero ".$plural[1];
		if ($maiusculas) $rt = ucwords($rt);

		return trim($rt);
		
	}

	function trataFiltros($filtros_lote) {
		
		$filtros_agrupados = array();
		$filtros_tratados = array();
		
		foreach ($filtros_lote as $filtro) {
			$filtros_agrupados[$filtro["campo"]][$filtro["comparacao"]][] = $this->trataFiltroValor($filtro["valor"]);
		}
		
		foreach ($filtros_agrupados as $campo => $filtros) {
			foreach ($filtros as $comparacao => $valores) {
				if ($comparacao == "=" && count($valores) > 1) {
					$filtros_tratados[] = array("campo" => $campo, "comparacao" => "IN", "valor" => "(".implode(", ", $valores).")");
				} elseif ($comparacao == "<>" && count($valores) > 1) {
					$filtros_tratados[] = array("campo" => $campo, "comparacao" => "NOT IN", "valor" => "(".implode(", ", $valores).")");
				} else {
					foreach ($valores as $valor) {
						$filtros_tratados[] = array("campo" => $campo, "comparacao" => $comparacao, "valor" => $valor);
					}
				}
			}
		}
		
		return $filtros_tratados;
	
	}

	function trataFiltroValor($valor) {
		
		if (strpos($valor, "/") !== false) {
			$valor = explode("/", $valor);
			$valor = "'{$valor[2]}-{$valor[1]}-{$valor[0]}'";
		}
		
		return $valor;
		
	}

	function limpaValor($valor) {
		
		return strip_tags(str_replace("<br>", "\n", $valor));
		
	}

	########################
	### Funções de Busca ###
	########################

	function buscaDadosLotes() {
	  
		$lotes = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_mensagens_lotes.mel_id AS id,
				tab_mensagens_lotes.mel_nome AS nome,
				tab_mensagens_lotes.mlt_id AS tipo_id,
				tab_mensagens_lotes.mel_parametros AS parametros,
				tab_mensagens_lotes_modelos.mlm_id AS modelo_id,
				tab_mensagens_lotes_modelos.mlm_conteudo_texto AS conteudo_texto,
				tab_mensagens_lotes_modelos.mlm_conteudo_html AS conteudo_html
			FROM
				tab_mensagens_lotes
				INNER JOIN tab_mensagens_lotes_modelos ON (tab_mensagens_lotes.mlm_id = tab_mensagens_lotes_modelos.mlm_id)
			WHERE
				(mel_data_inicio IS NULL OR mel_data_inicio <= CURDATE()) AND
				(mel_data_termino IS NULL OR mel_data_termino >= CURDATE()) AND
				mls_id = 5 AND
				mel_id NOT IN (5, 6, 22) AND
				mel_sistema = 0");
				
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			$lotes[] = $dados;
		}
		
		return $lotes;
		
	}

	function buscaLotesExpirados() {
		
		$lotes = array();

		$query = $this->bancodedados->query("
			SELECT
				mel_id AS id
			FROM
				tab_mensagens_lotes
			WHERE
				mel_data_termino < CURDATE() AND
				mls_id = 5");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lotes[] = $dados["id"];
		}
		
		return $lotes;
		
	}

	function buscaMensagensEmails() {
		
		$mensagens = array();
		
		$query = $this->bancodedados->query("
			SELECT
				men_id AS id,
				men_parametros AS parametros,
				mlm_conteudo_texto AS conteudo_texto,
				mlm_conteudo_html AS conteudo_html
			FROM
				tab_mensagens
				INNER JOIN tab_mensagens_lotes_modelos ON (tab_mensagens.mlm_id = tab_mensagens_lotes_modelos.mlm_id)
			WHERE
				mel_id NOT IN (5, 6, 22) AND
				mes_id = 1 AND
				mlt_id = 1 AND
				(men_data_hora_envio IS NULL OR men_data_hora_envio <= NOW())
			ORDER BY
				RAND( )
			LIMIT
				0, 200");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["parametros"] = unserialize($dados["parametros"]);
			
			foreach ($dados["parametros"]["dados_conteudo"] as $chave => $valor) {
				$dados["conteudo_texto"] = str_replace("[$chave]", $this->limpaValor($valor), $dados["conteudo_texto"]);
				$dados["conteudo_html"] = str_replace("[$chave]", $valor, $dados["conteudo_html"]);
			}
			
			$dados["conteudo_texto"] = preg_replace("/\\[[^\\]]*\\]/", "", $dados["conteudo_texto"]);
			$dados["conteudo_html"] = preg_replace("/\\[[^\\]]*\\]/", "", $dados["conteudo_html"]);
			
			$mensagens[] = $dados;
			
		}
	
		return $mensagens;
	
	}

	function buscaClientesContatosEmails($cliente_id) {
		
		$emails = array();
		
		$query = $this->bancodedados->query("
			SELECT
				CONCAT(ccm_usuario, '@', ccm_dominio) AS email
			FROM
				tab_clientes_contatos_emails
			WHERE
				cli_id = $cliente_id AND
				cca_id = 1");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$emails[] = $dados["email"];
		}
		
		return $emails;
		
	}

	function buscaClientesEmails($cliente_id) {
				
		$emails = array();

		$query = $this->bancodedados->query("
			SELECT
				CONCAT(cle_usuario, '@', dom_dominio) AS email
			FROM
				tab_clientes_emails
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_emails.ass_id)
				INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
			WHERE
				cli_id = $cliente_id AND
				ces_id = 1 AND
				cet_id = 1");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$emails[] = $dados["email"];
		}
		
		return $emails;
		
	}

	function buscaClientesCelulares($cliente_id) {
		
		$celulares = array();
		
		$query = $this->bancodedados->query("
			SELECT
				CONCAT(ccl_ddd, ccl_numero) AS celular
			FROM
				tab_clientes_contatos_telefones
			WHERE
				cli_id = $cliente_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$celulares[] = $dados["celular"];
		}
		
		return $celulares;
		
	}

	function buscaClientes($campos, $campos_serializados, $juncoes, $filtro_where, $filtro_having, $funcoes) {
		
		$clientes = array();
		
		$query = "
			SELECT
				$campos
			FROM
				tab_clientes";
				
		if ($juncoes) {
			$query .= "
				$juncoes";
		}
		
		if ($filtro_where) {
			$query .= "
				WHERE
					$filtro_where";
		}
		
		$query .= "
			GROUP BY
				id";
				
		if ($filtro_having) {
			$query .= "
				HAVING
					$filtro_having";
		}
		
		$constantes = $this->buscaCamposVariaveis($query);
		
		foreach ($constantes as $constante) {
			$query = str_replace("[$constante]", constant($constante), $query);
		}
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			foreach ($dados as $chave => $valor) {
				if (strpos($chave, "_extenso") !== false) {
					if (strpos($chave, "data_") !== false) {
						$dados[$chave] = $this->geraDataExtenso($valor);
					} elseif (strpos($chave, "valor_") !== false) {
						$dados[$chave] = $this->geraValorExtenso($valor, true);
					}
				}
				if (strpos($chave, "_formatado") !== false) {
					if (strpos($chave, "valor_") !== false) {
						$dados[$chave] = $this->formataDinheiro($valor);
					}
				}
				if ($funcoes) {
					foreach ($funcoes as $campo => $funcao) {
						eval('$dados['.$campo.'] = $this->'.$funcao.';');
					}
				}
				if ($campos_serializados[$chave]) {
					$dados[$chave] = unserialize($dados[$chave]);
					eval('$dados['.$chave.'] = $dados['.$chave.']'.$campos_serializados[$chave].';');
				}
			}
			
			$clientes[] = $dados;
			
		}
		
		return $clientes;
	
	}

	function buscaDadosFiltroTipo($filtro_tipo_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				mli_nome AS nome,
				mli_parametros AS parametros
			FROM
				tab_mensagens_lotes_filtros
			WHERE
				mli_id = $filtro_tipo_id");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			$dados["parametros"]["comparacao"]["IN"] = "IN";
			return $dados;
		}
		
	}

	function buscaMensagensLotesCampos($campo) {
		
		$campo = (strpos($campo, "_extenso") === false) ? $campo : substr($campo, 0, strpos($campo, "_extenso"));
		
		$query = $this->bancodedados->query("
			SELECT
				mla_nome AS nome,
				mla_parametros AS parametros
			FROM
				tab_mensagens_lotes_campos
			WHERE
				mla_nome = '$campo'");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}
	
	}

	function mensagemGerada($lote_id, $cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(men_id)
			FROM
				tab_mensagens
			WHERE
				mel_id = $lote_id AND
				cli_id = $cliente_id AND
				men_data_hora_geracao = NOW()");
		
		return $this->bancodedados->resultado($query) > 0;
		
	}

	################################
	### Inserções e Atualizações ###
	################################

	function geraMensagem($dados_lote, $dados_cliente) {
			
		$parametros = $dados_lote["parametros"]["envio"];
		$parametros["dados_conteudo"] = $dados_cliente;
		
		// Destinatários
		if ($dados_lote["tipo_id"] == 1) {
			if (in_array($dados_lote["parametros"]["envio"]["destino"], array(0, 1))) {
				$destinatarios = $this->buscaClientesContatosEmails($dados_cliente["id"]);
			} else {
				$destinatarios = array();
			}
			$cliente_emails = $this->buscaClientesEmails($dados_cliente["id"]);
			if (in_array($dados_lote["parametros"]["envio"]["destino"], array(0, 2)) || $dados_cliente["cliente_tipo_id"] == 1 || count($cliente_emails) == 1) {
				$destinatarios = array_unique(funcoes::mescla_arrays($cliente_emails, $destinatarios));
			}
			foreach ($destinatarios as $destinatario) {
				if ($parametros["destinatarios"]) {
					$parametros["bcc_email"][] = $destinatario;
				} else {
					$parametros["destinatarios"][] = $destinatario;
				}
			}
		} else {
			$parametros["destinatarios"] = $this->buscaClientesCelulares($dados_cliente["id"]);
		}
		
		if ($parametros["destinatarios"]) {
		
			// Serializa Prametros
			$parametros = serialize($parametros);
			
			// Insere mensagem no banco
			$this->bancodedados->query("
				INSERT INTO
					tab_mensagens
				SET
					men_parametros = '$parametros', 
					men_data_hora_geracao = NOW(), 
					cli_id = {$dados_cliente["id"]}, 
					mlm_id = {$dados_lote["modelo_id"]}, 
					mel_id = {$dados_lote["id"]}, 
					mes_id = 1");
			
			// ID da mensagem inseridas
			$mensagem_id = $this->bancodedados->ultimoIdInserido();
			
			// Insere Link no Banco
			if ($dados_cliente["cobranca_link"]) {
				$this->bancodedados->query("
					INSERT INTO
						tab_mensagens_links 
					SET
						mei_md5 = '{$dados_cliente["cobranca_link"]}', 
						mei_url_modulo = 'cobrancas', 
						mei_url_acao = 'imprimir', 
						mei_url_id = '{$dados_cliente["cobranca_id"]}', 
						men_id = $mensagem_id");
			}
		
		}
		
	}

	function registraEnvioMensagem($mensagem_id) {
		
		// Registra Envio
		$this->bancodedados->query("
			INSERT INTO 
				tab_mensagens_envios 
			SET
				mee_datahora = NOW(), 
				men_id = $mensagem_id");
		
		// Define como Enviada
		$this->bancodedados->query("
			UPDATE 
				tab_mensagens 
			SET 
				mes_id = 2, 
				men_data_hora_envio = NOW() 
			WHERE 
				men_id = $mensagem_id");
		
	}

	function registraComentarioMensagens($mensagem_id, $categoria_id, $conteudo = "", $responsavel_id = null) {
		
		$responsavel_id = $responsavel_id ? $responsavel_id : "NULL";
		
		$this->bancodedados->query("
			INSERT INTO 
				tab_mensagens_comentarios 
			SET
				men_id = $mensagem_id, 
				mcc_id = $categoria_id, 
				mec_conteudo = '$conteudo', 
				mec_datahora = NOW(), 
				usu_id = $responsavel_id");
		
	}

	function registraComentarioMensagensLotes($lote_id, $categoria_id, $conteudo, $responsavel_id = null) {
		
		$responsavel_id = $responsavel_id ? $responsavel_id : "NULL";
		
		$this->bancodedados->query("
			INSERT INTO 
				tab_mensagens_lostes_comentarios 
			SET
				mel_id = $lote_id, 
				mlo_id = $categoria_id, 
				mlc_conteudo = '$conteudo', 
				mlc_datahora = NOW(), 
				usu_id = $responsavel_id");
		
	}

	############################################################
	### Função Extra : buscaDescontosConcessoes($cliente_id) ###
	############################################################

	function buscaDescontosConcessoes($cliente_id) {
		
		$descontos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				des_nome AS nome,
				IF(dec_mensalidade > 1, CONCAT('R$ ', REPLACE(REPLACE(FORMAT(dec_mensalidade / 100, 2), ',', ''), '.', ',')), CONCAT(FORMAT(dec_mensalidade * 100, 0), '%')) AS valor,
				IF(dec_data_termino IS NULL, 'Indeterminada', DATE_FORMAT(dec_data_termino, '%d/%m/%Y'))AS data_termino
			FROM
				tab_assinaturas
				INNER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
				INNER JOIN tab_descontos ON (des_id)
			WHERE
				cli_id = $cliente_id AND
				dec_mensalidade IS NOT NULL AND
				dcs_id = 5
			ORDER BY
				dec_data_termino");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["nome"] = trim(preg_replace("/\\[[^\\]]*\\]/", "", $dados["nome"]));
			$descontos[] = "- <b>{$dados["nome"]}: {$dados["valor"]} (Validade: {$dados["data_termino"]})";
		}
		
		return implode("<br>", $descontos);
		
	}

	###############################################################
	### Função Extra : buscaClienteProximaCobranca($cliente_id) ###
	###############################################################

	function buscaClienteProximaCobranca($cliente_id) {
		
		$dados_formato = $this->buscaFormatoCobranca($cliente_id);
		$cobranca = $this->buscaValorCobranca($cliente_id, date("m"), date("Y"), $dados_formato["id"], $dados_formato["parametros"]["valor_cobranca"]["valor"]);
		
		return $this->formataDinheiro($cobranca["valor_final"]);
		
	}

	function buscaValorCobranca($cliente_id, $competencia_mes, $competencia_ano, $formato_id, $valor_cobranca = 0) {
		
		$cobranca = array();
		$assinaturas = array();
		$mensalidade_dias_total = date("t", strtotime("{$competencia_ano}-{$competencia_mes}-01"));
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_assinaturas.ass_id AS id,
				tab_planos.pla_nome AS descricao,
				tab_assinaturas.ass_mensalidade AS valor_original,
				tab_assinaturas.ass_data_ativacao AS data_ativacao,
				tab_assinaturas.ass_data_cancelamento AS data_cancelamento
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
			WHERE
				tab_assinaturas.cli_id = $cliente_id AND
				tab_assinaturas.ass_mensalidade > 0 AND
				DATE_FORMAT(tab_assinaturas.ass_data_ativacao, '%Y%m') <= '$competencia_ano$competencia_mes' AND
				(tab_assinaturas.ast_id = 2 OR
				(tab_assinaturas.ast_id IN (3, 4, 5) AND
				DATE_FORMAT(tab_assinaturas.ass_data_cancelamento, '%Y%m') >= '$competencia_ano$competencia_mes'))");
				
		while (($assinatura = $this->bancodedados->resultadoArray($query)) != false) {
			
			$mensalidade_dias_inicio = (date("Ym", strtotime($assinatura["data_ativacao"])) == $competencia_ano.$competencia_mes) ? date("d", strtotime($assinatura["data_ativacao"])) : "01";
			$mensalidade_dias_termino = ($assinatura["data_cancelamento"] && (date("Ym", strtotime($assinatura["data_cancelamento"])) == $competencia_ano.$competencia_mes)) ? date("d", strtotime($assinatura["data_cancelamento"])) : $mensalidade_dias_total;
			$mensalidade_dias_ativo = ($mensalidade_dias_termino - $mensalidade_dias_inicio) + 1;
			
			$assinatura["valor_original"] = intval($assinatura["valor_original"]);
			$assinatura["valor_final"] = intval($assinatura["valor_original"] / $mensalidade_dias_total * $mensalidade_dias_ativo);
			$assinatura["competencia_inicio"] = "$competencia_ano-$competencia_mes-$mensalidade_dias_inicio";
			$assinatura["competencia_termino"] = "$competencia_ano-$competencia_mes-$mensalidade_dias_termino";
			$assinatura["descontos_concessoes"] = $this->buscaClienteDescontosConcessoes($assinatura, $competencia_mes, $competencia_ano);
			$assinatura["ajustes_financeiros"] = $this->buscaAjustesFinanceirosAssinatura($assinatura, $competencia_mes, $competencia_ano);
			
			// Calcula Valor Total Assinatura
			if ($this->calculaValorTotalAssinatura($assinatura) < 0) {
				$assinatura = $this->recalculaAssinatura($assinatura);
			}

			$assinaturas[] = $assinatura;
			
		}
		
		$cobranca["assinaturas"] = $assinaturas;
		// Busca Ajustes Financeiros do Cliente
		$valor_total = $this->calculaValorTotalAssinaturas($assinaturas);
		$cobranca["ajustes_financeiros"] = $this->buscaAjustesFinanceirosCliente($cliente_id, $valor_total, $competencia_mes, $competencia_ano);
		$cobranca["competencia_inicio"] = "$competencia_ano-$competencia_mes-01";
		$cobranca["competencia_termino"] = "$competencia_ano-$competencia_mes-$mensalidade_dias_total";
		$cobranca["formato"] = $formato_id;
		// Calcula Valores
		$cobranca["valor_total"] = $this->calculaValorTotalCobranca($cobranca, $valor_total);
		$cobranca["valor_cobranca"] = ($cobranca["valor_total"]) ? $valor_cobranca : 0;
		$cobranca["valor_final"] = $cobranca["valor_total"] + $cobranca["valor_cobranca"];

		if (count($cobranca["assinaturas"]) || $cobranca["valor_final"] > 0) {
			return $cobranca;
		}
		
	}

	function buscaClienteDescontosConcessoes($assinatura, $competencia_mes, $competencia_ano) {
		
		$descontos = array();
		$desconto_dias_total = date("t", strtotime("{$competencia_ano}-{$competencia_mes}-01"));
		$desconto_dias_inicio = date("Ym", strtotime($assinatura["data_ativacao"])) == $competencia_ano.$competencia_mes ? date("d", strtotime($assinatura["data_ativacao"])) : "01";
		$desconto_dias_termino_assinatura = $assinatura["data_cancelamento"] && (date("Ym", strtotime($assinatura["data_cancelamento"])) == $competencia_ano.$competencia_mes) ? date("d", strtotime($assinatura["data_cancelamento"])) : $desconto_dias_total;
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				dec_id AS id,
				des_nome AS descricao,
				det_nome AS tipo,
				dec_mensalidade AS valor_original,
				dec_data_termino AS data_termino
			FROM
				tab_descontos_concessoes
				INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
				INNER JOIN tab_descontos_tipos ON (tab_descontos.det_id = tab_descontos_tipos.det_id)
			WHERE
				(dcs_id >= 5) AND
				(dec_mensalidade > 0) AND
				(ass_id = {$assinatura["id"]}) AND
				(dec_data_termino IS NULL OR DATE_FORMAT(dec_data_termino, '%Y%m') >= '$competencia_ano$competencia_mes')
			ORDER BY
				tab_descontos_concessoes.det_id DESC");
				
		while (($desconto_atual = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($desconto_atual["data_termino"] && date("Ym", strtotime($desconto_atual["data_termino"])) == $competencia_ano.$competencia_mes && date("d", strtotime($desconto_atual["data_termino"])) < $desconto_dias_termino_assinatura) {
				$desconto_dias_termino = date("d", strtotime($desconto_atual["data_termino"]));
			} else {
				$desconto_dias_termino = $desconto_dias_termino_assinatura;
			}

			$desconto_dias_ativo = ($desconto_dias_termino - $desconto_dias_inicio) + 1;
			
			$desconto_atual["descricao"] = "Desconto {$desconto_atual["tipo"]} ({$desconto_atual["descricao"]})";
			$desconto_atual["valor_original"] = -intval(($desconto_atual["valor_original"] <= 1) ? $desconto_atual["valor_original"] * $assinatura["valor_original"] : $desconto_atual["valor_original"]);
			$desconto_atual["valor_final"] = intval(($desconto_atual["valor_original"] * $desconto_dias_ativo) / $desconto_dias_total);
			$desconto_atual["competencia_inicio"] = "$competencia_ano-$competencia_mes-$desconto_dias_inicio";
			$desconto_atual["competencia_termino"] = "$competencia_ano-$competencia_mes-$desconto_dias_termino";
			
			unset($desconto_atual["data_termino"]);
			
			$descontos[] = $desconto_atual;
			
		}
		
		return $descontos;
		
	}

	function buscaAjustesFinanceirosCliente($cliente_id, $valor_total, $competencia_mes, $competencia_ano) {

		$ajuste_dias_mes = date("t", strtotime("{$competencia_ano}-{$competencia_mes}-01"));
		$data_cancelamento_contrato = $this->buscaDataCancelamentoContrato($cliente_id);
		$ajustes_financeiros = array();
		$valor_total_ajustes = 0;
			
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_ajustes_financeiros.ajf_id AS id,
				afc_nome AS descricao,
				aft_id AS tipo,
				ajf_valor AS valor_original,
				ajf_valor_tipo AS valor_tipo
			FROM
				tab_ajustes_financeiros_categorias
				INNER JOIN tab_ajustes_financeiros ON (tab_ajustes_financeiros_categorias.afc_id = tab_ajustes_financeiros.afc_id)
				LEFT OUTER JOIN tab_cobrancas_detalhes ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id AND '$competencia_ano$competencia_mes' BETWEEN DATE_FORMAT(tab_cobrancas_detalhes.cod_competencia_inicio, '%Y%m') AND DATE_FORMAT(tab_cobrancas_detalhes.cod_competencia_termino, '%Y%m'))
			WHERE
				(tab_cobrancas_detalhes.cod_id IS NULL) AND
				(afs_id IN (5, 7, 8)) AND
				(cli_id = $cliente_id) AND
				(tab_ajustes_financeiros.ass_id IS NULL) AND
				(ajf_competencia_inicio <= '$competencia_ano-$competencia_mes-01') AND
				(ajf_competencia_termino IS NULL OR ajf_competencia_termino >= '$competencia_ano-$competencia_mes-01')
			ORDER BY
				ajf_competencia_inicio");

		while (($ajuste_financeiro = $this->bancodedados->resultadoArray($query)) != false) {
			
			$ajuste_financeiro["descricao"] = "Ajuste Financeiro ({$ajuste_financeiro["descricao"]})";
			$ajuste_financeiro["valor_original"] = intval(($ajuste_financeiro["valor_tipo"] == 1) ? $ajuste_financeiro["valor_original"] : (($ajuste_financeiro["valor_tipo"] == 2) ? $valor_total * $ajuste_financeiro["valor_original"] : $valor_total / $ajuste_dias_mes * $ajuste_financeiro["valor_original"]));
			$ajuste_financeiro["valor_original"] *= ($ajuste_financeiro["tipo"] == 1) ? 1 : -1;
			
			if ($ajuste_financeiro["valor_tipo"] == 1 && $data_cancelamento_contrato) {
				$ajuste_dias_total = date("d", strtotime($data_cancelamento_contrato));
			} else {
				$ajuste_dias_total = date("t", strtotime("{$competencia_ano}-{$competencia_mes}-01"));
			}
			
			$ajuste_financeiro["valor_final"] = intval($ajuste_financeiro["valor_original"] * $ajuste_dias_total / $ajuste_dias_mes);
			$ajuste_financeiro["competencia_inicio"] = "$competencia_ano-$competencia_mes-01";
			$ajuste_financeiro["competencia_termino"] = "$competencia_ano-$competencia_mes-$ajuste_dias_mes";
			
			unset($ajuste_financeiro["tipo"]);
			unset($ajuste_financeiro["valor_tipo"]);
			
			$valor_total_ajustes += $ajuste_financeiro["valor_final"];
			
			$ajustes_financeiros[] = $ajuste_financeiro;
			
		}
		
		// Trata valore_final
		if ($valor_total + $valor_total_ajustes < 0) {
			$valor_total_cobranca = $valor_total;
			// Ajustes Positivos
			foreach ($ajustes_financeiros as $chave => $ajuste_financeiro) {
				if ($ajuste_financeiro["valor_final"] > 0) {
					$valor_total_cobranca += $ajuste_financeiro["valor_final"];
				}
			}
			// Ajustes Negativos
			foreach ($ajustes_financeiros as $chave => $ajuste_financeiro) {
				if ($ajuste_financeiro["valor_final"] < 0) {
					if ($valor_total_cobranca + $ajuste_financeiro["valor_final"] > 0) {
						$valor_total_cobranca += $ajuste_financeiro["valor_final"];
					} else {
						$ajustes_financeiros[$chave]["valor_final"] = -$valor_total_cobranca;
						$valor_total_cobranca = 0;
					}
				}
			}
		}
		
		return $ajustes_financeiros;
	
	}

	function buscaAjustesFinanceirosAssinatura($assinatura, $competencia_mes, $competencia_ano) {
		
		$ajustes_financeiros = array();
		$ajuste_dias_mes = date("t", strtotime("{$competencia_ano}-{$competencia_mes}-01"));
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				ajf_id AS id,
				afc_nome AS descricao,
				aft_id AS tipo,
				ajf_valor AS valor_original,
				ajf_valor_tipo AS valor_tipo
			FROM
				tab_ajustes_financeiros
				INNER JOIN tab_ajustes_financeiros_categorias ON (tab_ajustes_financeiros.afc_id = tab_ajustes_financeiros_categorias.afc_id)
			WHERE
				(afs_id IN (5, 7, 8)) AND
				(ass_id = {$assinatura["id"]}) AND
				(ajf_competencia_inicio <= '$competencia_ano-$competencia_mes-01') AND
				(ajf_competencia_termino IS NULL OR ajf_competencia_termino >= '$competencia_ano-$competencia_mes-01')");

		while (($ajuste_financeiro = $this->bancodedados->resultadoArray($query)) != false) {
			
			$ajuste_financeiro["descricao"] = "Ajuste Financeiro ({$ajuste_financeiro["descricao"]})";
			$ajuste_financeiro["valor_original"] = intval(($ajuste_financeiro["valor_tipo"] == 1) ? $ajuste_financeiro["valor_original"] : (($ajuste_financeiro["valor_tipo"] == 2) ? $assinatura["valor_original"] * $ajuste_financeiro["valor_original"] : $assinatura["valor_original"] / $ajuste_dias_mes * $ajuste_financeiro["valor_original"]));
			$ajuste_financeiro["valor_original"] *= ($ajuste_financeiro["tipo"] == 1) ? 1 : -1;
			
			if ($ajuste_financeiro["valor_tipo"] == 1 && $assinatura["data_cancelamento"]) {
				$ajuste_dias_total = date("d", strtotime($assinatura["data_cancelamento"]));
			} else {
				$ajuste_dias_total = date("t", mktime(0, 0, 0, $competencia_mes, 1, $competencia_ano));
			}
			
			$ajuste_financeiro["valor_final"] = intval($ajuste_financeiro["valor_original"] * $ajuste_dias_total / $ajuste_dias_mes);
			$ajuste_financeiro["competencia_inicio"] = "$competencia_ano-$competencia_mes-01";
			$ajuste_financeiro["competencia_termino"] = "$competencia_ano-$competencia_mes-$ajuste_dias_total";
			
			unset($ajuste_financeiro["tipo"]);
			unset($ajuste_financeiro["valor_tipo"]);
			
			$ajustes_financeiros[] = $ajuste_financeiro;
			
		}
		
		return $ajustes_financeiros;
	
	}

	function calculaValorTotalAssinatura($assinatura) {
		
		$valor_total = $assinatura["valor_final"];
		
		foreach ($assinatura["descontos_concessoes"] as $desconto_concessao) {
			$valor_total += $desconto_concessao["valor_final"];
		}

		foreach ($assinatura["ajustes_financeiros"] as $ajuste_financeiro) {
			$valor_total += $ajuste_financeiro["valor_final"];
		}

		return $valor_total;
	
	}

	function calculaValorTotalAssinaturas($assinaturas) {
		
		$valor_total = 0;
		
		foreach ($assinaturas as $assinatura) {
			
			$valor_total_assinatura = $assinatura["valor_final"];
			
			foreach ($assinatura["descontos_concessoes"] as $desconto_concessao) {
				$valor_total_assinatura += $desconto_concessao["valor_final"];
			}
			
			foreach ($assinatura["ajustes_financeiros"] as $ajuste_financeiro) {
				$valor_total_assinatura += $ajuste_financeiro["valor_final"];
			}
			
			$valor_total += $valor_total_assinatura > 0 ? $valor_total_assinatura : 0;
		
		}
		
		return $valor_total > 0 ? $valor_total : 0;
	
	}

	function recalculaAssinatura($assinatura) {
		
		$valor_total = $assinatura["valor_final"];
		
		foreach ($assinatura["descontos_concessoes"] as $chave => $desconto_concessao) {
			if ($desconto_concessao["valor_final"] > 0) {
				$valor_total += $desconto_concessao["valor_final"];
			}
		}

		// Calcula valores acréscimo (ajustes)
		foreach ($assinatura["ajustes_financeiros"] as $chave => $ajuste_financeiro) {
			if ($ajuste_financeiro["valor_final"] > 0) {
				$valor_total += $ajuste_financeiro["valor_final"];
			}
		}

		// Reduz valor descontos/ajustes negativos
		foreach ($assinatura["descontos_concessoes"] as $chave => $desconto_concessao) {
			if ($desconto_concessao["valor_final"] < 0) {
				$valor_total += $desconto_concessao["valor_final"];
				if ($valor_total < 0) {
					$assinatura["descontos_concessoes"][$chave]["valor_final"] -= $valor_total;
					$valor_total = 0;
				}
			}
		}

		// Reduz valor ajustes negativos
		foreach ($assinatura["ajustes_financeiros"] as $chave => $ajuste_financeiro) {
			if ($ajuste_financeiro["valor_final"] < 0) {
				$valor_total += $ajuste_financeiro["valor_final"];
				if ($valor_total < 0) {
					$assinatura["ajustes_financeiros"][$chave]["valor_final"] -= $valor_total;
					$valor_total = 0;
				}
			}
		}
		
		return $assinatura;
		
	}

	function calculaValorTotalCobranca($cobranca, $valor_total) {
		
		foreach ($cobranca["ajustes_financeiros"] as $ajuste_financeiro) {
			$valor_total += $ajuste_financeiro["valor_final"];
		}
		
		return $valor_total > 0 ? $valor_total : 0;
		
	}

	function buscaFormatoCobranca($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas_formatos.cof_id AS id,
				tab_cobrancas_formatos.cof_parametros AS parametros
			FROM
				tab_clientes
				INNER JOIN tab_cobrancas_formatos ON (tab_clientes.cof_id = tab_cobrancas_formatos.cof_id)
			WHERE
				cli_id = $cliente_id");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}
		
	}

	function buscaDataCancelamentoContrato($cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				MAX(tab_contratos.cor_data_cancelamento)
			FROM
				tab_contratos
			WHERE
				cor_pai IS NULL AND
				cor_data_cancelamento IS NOT NULL AND
				cli_id = $cliente_id");
		
		return $this->bancodedados->resultado($query);
		
	}

}

?>
