<?

class clientes_contatos {
	
	/**
	 * @var bancodedados
	 */
	private $bancodedados;
	private $permissoes;
	
	function __construct($bancodedados, $permissoes = null) {
		
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
	}
	
	function geraSelect($itens, $selecionado = null, $item_nulo = false) {
		
		$select = $item_nulo ? "<option></option>" : "";
		
		foreach ($itens as $valor => $item) {
			$select .= "<option value=\"$valor\"" . (isset($selecionado) && $valor == $selecionado ? " selected" : "") . ">$item</option>";
		}
		
		return $select;
		
	}
	
	function buscaContatosEmails($cliente_id) {
		
		$lista = array ();
		
		if ($cliente_id) {
			
			$query = $this->bancodedados->query("
                SELECT
                    ccm_id AS id,
                    CONCAT(ccm_usuario, '@', ccm_dominio) AS contato_email,
                    tab_clientes_contatos_emails_tipos.cca_id AS contato_email_tipo_id,
                    tab_clientes_contatos_emails_tipos.cca_nome AS contato_email_tipo
                FROM
                    tab_clientes_contatos_emails
                    INNER JOIN tab_clientes_contatos_emails_tipos ON (tab_clientes_contatos_emails.cca_id = tab_clientes_contatos_emails_tipos.cca_id)
                WHERE
                    cli_id = $cliente_id
                ORDER BY
                    contato_email_tipo,
                    contato_email");
			
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$lista[] = $dados;
			}
		}
		
		return $lista;
		
	}
	
	function buscaContatosEmailsAdicionar($removivel = true) {
		
		$adicionar = array ();
		
		$adicionar["contato_email"] = '<input type="text" name="contato_email" id="contato_email" style="width:100%" class="inputBrancoAzulEscuro11">';
		$adicionar["contato_email_tipo"] = '<select name="contato_email_tipo" id="contato_email_tipo" style="width:100%" class="inputBrancoAzulEscuro11"><option></option>' . $this->geraSelect($this->buscaContatosEmailsTipos()) . '</select>';
		$adicionar["botoes"] = '<input type="button" name="adicionar_email" id="adicionar_email" value="OK" style="width:25px;height:20px" class="buttonAzulEscuroBranco11" onclick="adicionarEmail(' . ($removivel ? 1 : 0) . ')">';
		
		return $adicionar;
		
	}
	
	function buscaContatosTelefones($cliente_id) {
		
		$lista = array ();
		
		if ($cliente_id) {
			
			$query = $this->bancodedados->query("
				SELECT
					ccl_id AS id,
					ccl_ddd AS contato_ddd,
					ccl_numero AS contato_numero,
					ccl_ramal AS contato_ramal,
					tab_clientes_contatos_telefones_tipos.ccf_id AS contato_telefone_tipo_id,
					tab_clientes_contatos_telefones_tipos.ccf_nome AS contato_telefone_tipo
				FROM
					tab_clientes_contatos_telefones
					INNER JOIN tab_clientes_contatos_telefones_tipos ON (tab_clientes_contatos_telefones.ccf_id = tab_clientes_contatos_telefones_tipos.ccf_id)
				WHERE
					cli_id = $cliente_id
				ORDER BY
					contato_telefone_tipo DESC,
					contato_ddd,
					contato_numero");
			
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$lista[] = $dados;
			}
			
		}
		
		return $lista;
		
	}
	
	function buscaContatoTelefoneTipo($telefone_tipo_id) {
		
		if ($telefone_tipo_id) {
			
			$query = $this->bancodedados->query("
				SELECT
					ccf_nome
				FROM
					tab_clientes_contatos_telefones_tipos
				WHERE
					ccf_id = $telefone_tipo_id");
			
			return $this->bancodedados->resultado($query);
			
		}
		
	}
	
	function buscaContatoEmailTipo($email_tipo_id) {
		
		if ($email_tipo_id) {
			
			$query = $this->bancodedados->query("
				SELECT
					cca_nome
				FROM
					tab_clientes_contatos_emails_tipos
				WHERE
					cca_id = $email_tipo_id");
			
			return $this->bancodedados->resultado($query);
			
		}
		
	}
	
	function buscaContatosTelefonesAdicionar($removivel = true) {
		
		$adicionar = array();
		
		$adicionar["contato_ddd"] = '<input type="text" name="contato_ddd" id="contato_ddd" style="width:100%" maxlength="2" onkeypress="return pulaDDD(this, event)" onblur="return pulaDDD(this, event)" class="inputBrancoAzulEscuro11">';
		$adicionar["contato_numero"] = '<input type="text" name="contato_numero" id="contato_numero" style="width:100%" maxlength="10" onkeypress="return pulaTelefoneSemDDD(this, event)" onblur="return pulaTelefoneSemDDD(this, event)" class="inputBrancoAzulEscuro11">';
		$adicionar["contato_ramal"] = '<input type="text" name="contato_ramal" id="contato_ramal" style="width:100%" maxlength="3" onkeypress="return formataNumero(this, event)" onblur="return formataNumero(this, event)" class="inputBrancoAzulEscuro11">';
		$adicionar["contato_telefone_tipo"] = '<select name="contato_telefone_tipo" id="contato_telefone_tipo" style="width:100%" class="inputBrancoAzulEscuro11"><option></option>' . $this->geraSelect($this->buscaContatosTelefonesTipos()) . '</select>';
		$adicionar["botoes"] = '<input type="button" name="adicionar_telefone" id="adicionar_telefone" value="OK" style="width:25px;height:20px" class="buttonAzulEscuroBranco11" onclick="adicionarTelefone(' . ($removivel ? 1 : 0) . ')">';
		
		return $adicionar;
		
	}
	
	function buscaContatosTelefonesTipos() {
		
		$lista = array ();
		
		$query = $this->bancodedados->query("
			SELECT
				ccf_id AS id,
				ccf_nome AS nome
			FROM
				tab_clientes_contatos_telefones_tipos
			WHERE
				ccf_id > 0
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[$dados["id"]] = $dados["nome"];
		}
		
		return $lista;
		
	}
	
	function buscaContatosEmailsTipos() {
		
		$lista = array ();
		
		$query = $this->bancodedados->query("
			SELECT
				cca_id AS id,
				cca_nome AS nome
			FROM
				tab_clientes_contatos_emails_tipos
			ORDER BY
				nome DESC");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[$dados["id"]] = $dados["nome"];
		}
		
		return $lista;
		
	}
	
	function geraContatosEmailsListagem($dados, $editavel = false, $removivel = true) {
		
		if ($dados) {
			if ($editavel) {
				$dados = funcoes::mescla_arrays(array($this->buscaContatosEmailsAdicionar($removivel)), $dados);
			}
			for ($i = 0; $i < count($dados); $i++) {
				if ($editavel && $dados[$i]["id"]) {
					$dados[$i]["link"] = "javascript:selecionarEmail(this, event, {$dados[$i]["id"]})";
					$dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px;height:20px' . ($removivel || count($dados) > 2 ? '"' : ';background-color:#CCCCCC;cursor:default" disabled') . ' class="buttonAzulEscuroBranco11" onclick="removerEmail(\'' . $dados[$i]["id"] . '\', \'' . $removivel . '\')">';
				}
			}
		} else {
			if ($editavel)
				$dados[] = $this->buscaContatosEmailsAdicionar($removivel);
				$dados[] = array (
					"mesclado" => "<table width=\"100%\" height=\"20\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>"
				);
		}
		
		if ($editavel) {
			$cabecalho = array (
				"contato_email" => "E-mail",
				"contato_email_tipo" => "Tipo",
				"botoes" => ""
			);
		} else {
			$cabecalho = array (
				"contato_email" => "E-mail",
				"contato_email_tipo" => "Tipo"
			);
		}
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array (
			"50%",
			"50%"
		));
		
		return $listagem->conteudo;
		
	}
	
	function geraContatosTelefonesListagem($dados, $editavel = false, $removivel = 1) {
		
		global $login;
		
		if ($dados) {
			if ($editavel) {
				$dados = funcoes::mescla_arrays(array($this->buscaContatosTelefonesAdicionar($removivel)), $dados);
			}
			foreach ($dados as $chave => $contato) {
				if ($editavel && $contato["id"]) {
					$dados[$chave]["link"] = "javascript:selecionarTelefone(this, event, {$contato["id"]})";
					$dados[$chave]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px;height:20px' . '" class="buttonAzulEscuroBranco11" onclick="removerTelefone(\'' . $contato["id"] . '\', \'' . $removivel . '\')">';
				}
				if ($contato["id"] && ($login->usuario["agente_id"] || $login->usuario["ramal"]) && $dados[$chave]["contato_telefone_tipo_id"] > 0) {
					if ($dados[$chave]["contato_ddd"] == 48) {
						$numero = preg_replace("/[^0-9]/", "", "0" . $dados[$chave]["contato_numero"]);
					} else {
						$numero = preg_replace("/[^0-9]/", "", "00" . $dados[$chave]["contato_ddd"] . $dados[$chave]["contato_numero"]);
					}
					$dados[$chave]["contato_numero"] = "<a href=\"javascript:discar('$numero')\" title=\"Discar para este número\" class=\"linkDiscar\">{$dados[$chave]["contato_numero"]}</a>";
				}
				$dados[$chave]["contato_ddd"] = "<center>{$contato["contato_ddd"]}</center>";
				
				if (array_key_exists("contato_telefone_tipo_id", $dados[$chave]) && $dados[$chave]["contato_telefone_tipo_id"] == 0) {
					$dados[$chave]["cor_linha"] = "#FFFFFF";
				}
				
			}
		} else {
			if ($editavel) {
				$dados[] = $this->buscaContatosTelefonesAdicionar($removivel);
			}
			$dados[] = array (
				"mesclado" => "<table width=\"100%\" height=\"20\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>"
			);
		}
		
		if ($editavel) {
			$cabecalho = array (
				"contato_ddd" => "DDD",
				"contato_numero" => "Número",
				"contato_ramal" => "Ramal",
				"contato_telefone_tipo" => "Tipo",
				"botoes" => ""
			);
		} else {
			$cabecalho = array (
				"contato_ddd" => "DDD",
				"contato_numero" => "Número",
				"contato_ramal" => "Ramal",
				"contato_telefone_tipo" => "Tipo"
			);
		}
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array (
			"10%",
			"35%",
			"15%",
			"40%"
		));
		
		return $listagem->conteudo;
		
	}
	
	function atualizaContatoTelefones($cliente_id, $telefones) {
		
		if ($cliente_id) {
			
			$atuais = $this->buscaContatosTelefones($cliente_id);
			
			$lista_atuais = array();
			$lista_novos = array();
			
			foreach ($atuais as $telefone) {
				$lista_atuais["{$telefone["contato_ddd"]} {$telefone["contato_numero"]}"] = $telefone;
			}
				
			foreach ($telefones as $telefone) {
				$lista_novos["{$telefone["contato_ddd"]} {$telefone["contato_numero"]}"] = $telefone;
			}
			
			foreach ($lista_atuais as $id => $telefone) {
				if (!array_key_exists($id, $lista_novos)) {
					$this->bancodedados->query("
						UPDATE
							tab_clientes_contatos_telefones
						SET
							ccf_id = 0
						WHERE
							ccl_id = {$telefone["id"]}");
				}
			}
			
			foreach ($lista_novos as $id => $telefone) {
				if (array_key_exists($id, $lista_atuais)) {
					$this->bancodedados->query("
						UPDATE
							tab_clientes_contatos_telefones
						SET
							ccl_ramal = '{$telefone["contato_ramal"]}',
							ccf_id = {$telefone["contato_telefone_tipo_id"]}
						WHERE
							ccl_id = {$lista_atuais[$id]["id"]}");
				} else {
					$this->bancodedados->query("
						INSERT INTO
							tab_clientes_contatos_telefones
						SET
							cli_id = $cliente_id,
							ccl_ddd = '{$telefone["contato_ddd"]}',
							ccl_numero = '{$telefone["contato_numero"]}',
							ccl_ramal = '{$telefone["contato_ramal"]}',
							ccf_id = {$telefone["contato_telefone_tipo_id"]}");
				}
			}
			
		}
		
	}
	
	function atualizaContatoEmails($cliente_id, $emails) {
		
		if ($cliente_id) {
			
			$this->bancodedados->query("DELETE FROM tab_clientes_contatos_emails WHERE cli_id = $cliente_id");
			
			foreach ($emails as $email) {
				
				$email["contato_email"] = explode('@', $email["contato_email"]);
				$email["contato_usuario"] = $email["contato_email"][0];
				$email["contato_dominio"] = $email["contato_email"][1];

				 $this->bancodedados->query("
					INSERT INTO
						tab_clientes_contatos_emails
					SET 
						cli_id = $cliente_id, 
						ccm_usuario = '{$email["contato_usuario"]}', 
						ccm_dominio = '{$email["contato_dominio"]}', 
						cca_id = {$email["contato_email_tipo_id"]}");
				
				 $this->atualizaServicoEmail($cliente_id, $email["contato_usuario"], $email["contato_dominio"], $email["contato_email_tipo_id"]);
			
			}
			
		}
		
	}
	
	function atualizaServicoEmail($cliente_id, $usuario, $dominio, $tipo_id) {
		
		if ($cliente_id && $usuario && $dominio && $tipo_id) {
			
			$this->bancodedados->query("
				UPDATE
					tab_clientes_emails
					INNER JOIN tab_assinaturas ON (tab_clientes_emails.ass_id = tab_assinaturas.ass_id)
					INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
				SET
					tab_clientes_emails.cet_id = $tipo_id
				WHERE
					tab_assinaturas.cli_id = $cliente_id AND
					tab_clientes_emails.cle_usuario = '$usuario' AND
					tab_dominios.dom_dominio = '$dominio'");
			
		}
		
	}
	
	function buscaTelefoneDuplicado($cliente_id, $numero, $ddd) {
		
		$clientes = array ();
		
		if ($cliente_id && $numero && $ddd) {
			
			$query = $this->bancodedados->query("
				SELECT
					CONCAT(tab_clientes.cli_nome, ' - ', ccf_nome, ': (', ccl_ddd, ') ', ccl_numero, IF(ccl_ramal, CONCAT(' (', ccl_ramal, ')'), '')) AS nome
				FROM
					tab_clientes
					INNER JOIN tab_clientes_contatos_telefones ON (tab_clientes.cli_id = tab_clientes_contatos_telefones.cli_id)
					INNER JOIN tab_clientes_contatos_telefones_tipos ON (tab_clientes_contatos_telefones.ccf_id = tab_clientes_contatos_telefones_tipos.ccf_id)
				WHERE
					tab_clientes_contatos_telefones.cli_id != $cliente_id AND
					tab_clientes_contatos_telefones.ccl_numero = '$numero' AND
					tab_clientes_contatos_telefones.ccl_ddd = '$ddd'
				ORDER BY
					nome");
			
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$clientes[] = $dados["nome"];
			}
			
		}
		
		return $clientes;
		
	}
		
	function buscaTelefones($cliente_id, $numero, $ddd) {

		if($this->permissoes["clientes"]["duplicar_cpf_cnpj"] ) {
			return false;
		}
			
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tab_clientes
				INNER JOIN tab_clientes_contatos_telefones ON (tab_clientes.cli_id = tab_clientes_contatos_telefones.cli_id)
			WHERE
				tab_clientes_contatos_telefones.cli_id != $cliente_id AND
				tab_clientes_contatos_telefones.ccl_numero = '$numero' AND
				tab_clientes_contatos_telefones.ccl_ddd = '$ddd'".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : ""));
			
		return $this->bancodedados->resultado($query);
		
	}

	function buscaEmailDuplicado($cliente_id, $usuario, $dominio) {

		if($this->permissoes["clientes"]["duplicar_cpf_cnpj"] ) {
			return false;
		}
		
		$clientes = array ();
		
		if ($cliente_id && $usuario && $dominio) {
			
			$query = $this->bancodedados->query("
				SELECT
					CONCAT(tab_clientes.cli_nome, ' - ', ccm_usuario, '@', ccm_dominio, ' (', cca_nome, ')') AS nome
				FROM
					tab_clientes
					INNER JOIN tab_clientes_contatos_emails ON (tab_clientes.cli_id = tab_clientes_contatos_emails.cli_id)
					INNER JOIN tab_clientes_contatos_emails_tipos ON (tab_clientes_contatos_emails.cca_id = tab_clientes_contatos_emails_tipos.cca_id)
				WHERE
					tab_clientes_contatos_emails.cli_id != $cliente_id AND
					tab_clientes_contatos_emails.ccm_usuario = '$usuario' AND
					tab_clientes_contatos_emails.ccm_dominio = '$dominio'
				ORDER BY
					nome");
			
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$clientes[] = $dados["nome"];
			}
			
		}
		
		return $clientes;
		
	}
	
}

?>
