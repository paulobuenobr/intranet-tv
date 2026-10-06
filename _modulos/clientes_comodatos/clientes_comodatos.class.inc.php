<?

require_once("_global/modulo.class.inc.php");

class clientes_comodatos extends modulo {
	
    function __construct($bancodedados, $permissoes, $executar = true) {
		
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		
		$this->buscaDadosModulo(__CLASS__);
		
		if ($executar) {
			if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
		
	}
	
	########################
	### Busca Dados Item ###
	########################

	function buscaDadosItem($id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_comodatos.clc_id AS id,
				tab_clientes_comodatos.clc_mac_address AS mac_address,
				DATE_FORMAT(tab_clientes_comodatos.clc_data_retirada, '%d/%m/%Y')AS data_retirada,
				DATE_FORMAT(tab_clientes_comodatos.clc_data_inicio, '%d/%m/%Y')AS data_inicio,
				DATE_FORMAT(tab_clientes_comodatos.clc_data_termino,'%d/%m/%Y')AS data_termino,
				tab_clientes_comodatos.ass_id AS assinatura_id,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_contratos.cor_id AS contrato_id,
				tab_contratos.cor_codigo AS contrato,
				tab_clientes_iptv.cip_id AS iptv_id,
				tab_equipamentos.equ_id AS equipamento_id,
				tab_equipamentos.equ_nome AS equipamento,
				tab_equipamentos_modelos.eqm_id AS equipamento_modelo_id,
				tab_equipamentos_modelos.eqm_nome AS equipamento_modelo,
				tab_clientes_comodatos_status.ccs_id AS status_id,
				tab_clientes_comodatos_status.ccs_nome AS status_nome
			FROM
				tab_clientes_comodatos
				INNER JOIN tab_clientes ON (tab_clientes_comodatos.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_contratos ON (tab_clientes_comodatos.cor_id = tab_contratos.cor_id)
				INNER JOIN tab_clientes_iptv ON (tab_clientes_comodatos.cor_id = tab_clientes_iptv.cor_id)
				INNER JOIN tab_equipamentos ON (tab_clientes_comodatos.equ_id = tab_equipamentos.equ_id)
				LEFT JOIN tab_equipamentos_modelos ON (tab_clientes_comodatos.eqm_id = tab_equipamentos_modelos.eqm_id)
				INNER JOIN tab_clientes_comodatos_status ON(tab_clientes_comodatos.ccs_id = tab_clientes_comodatos_status.ccs_id)
			WHERE
				tab_clientes_comodatos.clc_id = $id".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : ""));
		
		$this->dados_item = $this->bancodedados->resultadoArray($query);
		
	}
			
	###########
	### Ver ###
	###########

	function ver() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$botoes = array();
			
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11"  value="Fechar" onclick="window.close()">';
			if ($this->dados_item["status_id"] == 3 && $this->permissoes["clientes_comodatos"]["registrar_retirada"]) {
				$botoes[] = '<input type="button" name="registrar_retirada" class="buttonAzulEscuroBranco11" value="Registrar Retirada" onclick="window.location=\'?m=' . $this->dados_modulo ["nome"] . '&a=registrar_retirada&i=' . $this->dados_item ["id"] . '\'">';
			}
			if ($this->dados_item["status_id"] == 2 && $this->permissoes["clientes_comodatos"]["substituir"] && $this->dados_item["mac_address"]) {
				$botoes[] = '<input type="button" name="substituir" class="buttonAzulEscuroBranco11" value="Substituir" onclick="window.location=\'?m=' . $this->dados_modulo ["nome"] . '&a=substituir&i=' . $this->dados_item ["id"] . '\'">';
			}
			if ($this->dados_item["status_id"] == 2 && $this->permissoes["clientes_comodatos"]["resetar"]) {
				$botoes[] = '<input type="button" name="resetar" class="buttonAzulEscuroBranco11" value="Resetar" onclick="window.location=\'?m=' . $this->dados_modulo ["nome"] . '&a=resetar&i=' . $this->dados_item ["id"] . '\'">';
			}
			if ($this->dados_item["status_id"] == 2 && $this->permissoes["clientes_comodatos"]["verificar_status"]) {
				$botoes[] = '<input type="button" name="verificar_status" class="buttonAzulEscuroBranco11" value="Verificar Status" onclick="window.location=\'?m=' . $this->dados_modulo ["nome"] . '&a=verificar_status&i=' . $this->dados_item ["id"] . '\'">';
			}
			
			$this->conteudo .= $this->carregaArquivo("clientes_comodatos.ver.inc.php");
			
			$this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
			$this->conteudo = str_replace("[cliente]", $this->dados_item["cliente_nome"], $this->conteudo);
			$this->conteudo = str_replace("[contrato]", $this->dados_item["contrato"], $this->conteudo);
			$this->conteudo = str_replace("[equipamento]", $this->dados_item["equipamento"], $this->conteudo);
			$this->conteudo = str_replace("[mac_address]", $this->dados_item["mac_address"], $this->conteudo);
			$this->conteudo = str_replace("[data_retirada]", $this->dados_item["data_retirada"], $this->conteudo);
			$this->conteudo = str_replace("[data_inicio]", $this->dados_item["data_inicio"], $this->conteudo);
			$this->conteudo = str_replace("[data_termino]", $this->dados_item["data_termino"], $this->conteudo);
			$this->conteudo = str_replace("[lista_comentarios]", $this->geraComentariosListagem($_GET["i"]), $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			
		} else {
			$this->fechar();
		}
		
	}
			
	##############
	### Editar ###
	##############

	function editar() { 
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$modelos = $this->buscaModelos($this->dados_item["equipamento_id"]);
			
			if (!$_POST){
				
				$botoes = array();
				
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				if ($this->dados_item["status_id"] == 3 && $this->permissoes["clientes_comodatos"]["registrar_retirada"]) {
					$botoes[] = '<input type="button" name="registrar_retirada" class="buttonAzulEscuroBranco11" value="Registrar Retirada" onclick="window.location=\'?m=' . $this->dados_modulo ["nome"] . '&a=registrar_retirada&i=' . $this->dados_item ["id"] . '\'">';
				}
				if ($this->dados_item["status_id"] == 2 && $this->permissoes["clientes_comodatos"]["substituir"] && $this->dados_item["mac_address"]) {
					$botoes[] = '<input type="button" name="substituir" class="buttonAzulEscuroBranco11" value="Substituir" onclick="window.location=\'?m=' . $this->dados_modulo ["nome"] . '&a=substituir&i=' . $this->dados_item ["id"] . '\'">';
				}
				if ($this->dados_item["status_id"] == 2 && $this->permissoes["clientes_comodatos"]["resetar"]) {
					$botoes[] = '<input type="button" name="resetar" class="buttonAzulEscuroBranco11" value="Resetar" onclick="window.location=\'?m=' . $this->dados_modulo ["nome"] . '&a=resetar&i=' . $this->dados_item ["id"] . '\'">';
				}
				if ($this->dados_item["status_id"] == 2 && $this->permissoes["clientes_comodatos"]["verificar_status"]) {
					$botoes[] = '<input type="button" name="verificar_status" class="buttonAzulEscuroBranco11" value="Verificar Status" onclick="window.location=\'?m=' . $this->dados_modulo ["nome"] . '&a=verificar_status&i=' . $this->dados_item ["id"] . '\'">';
				}
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onclick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("clientes_comodatos.editar.inc.php");
				
				$this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
				$this->conteudo = str_replace("[cliente]", $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace("[contrato]", $this->dados_item["contrato"], $this->conteudo);
				$this->conteudo = str_replace("[equipamento]", $this->dados_item["equipamento"], $this->conteudo);
				$this->conteudo = str_replace("[modelo]", $this->dados_item["status_id"] == 1 || ($this->dados_item["status_id"] == 2 && !$this->dados_item["mac_address"]) ? $this->geraSelect($modelos, $this->dados_item["equipamento_modelo_id"], true, "modelo", "100%") : $this->dados_item["equipamento_modelo"], $this->conteudo);
				$this->conteudo = str_replace("[mac_address]", $this->dados_item["status_id"] == 1 || ($this->dados_item["status_id"] == 2 && !$this->dados_item["mac_address"]) ? '<input type="text" id="mac_address" class="inputBrancoAzulEscuro11" name="mac_address" value="'.$this->dados_item["mac_address"].'" maxlength="12" style="font-family: monospace; text-transform: uppercase" onkeypress="return /[a-fA-F0-9]/.test(String.fromCharCode(event.which || event.keyCode))" style="text-transform: uppercase; width:100%"/>' : $this->dados_item["mac_address"], $this->conteudo);
				$this->conteudo = str_replace("[data_inicio]", $this->dados_item["data_inicio"], $this->conteudo);
				$this->conteudo = str_replace("[data_termino]", $this->dados_item["data_termino"], $this->conteudo);
				$this->conteudo = str_replace("[data_retirada]", $this->dados_item["data_retirada"], $this->conteudo);
				$this->conteudo = str_replace("[lista_comentarios]", $this->geraComentariosListagem($_GET["i"]), $this->conteudo);
				$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);	
				
			} else {
				
				if ($this->dados_item["status_id"] == 1 || ($this->dados_item["status_id"] == 2 && !$this->dados_item["mac_address"])) {
				
					$alteracoes = array();
				
					$_POST["mac_address"] = preg_replace("/[^A-F0-9]/", "", strtoupper($_POST["mac_address"]));
					$_POST["mac_address"] = strlen($_POST["mac_address"]) == 12 ? $_POST["mac_address"] : null;
					$_POST["modelo"] = preg_replace("/[^0-9]/", "", $_POST["modelo"]);

					if ($_POST["mac_address"]) {

						$ponto_existente = $this->buscaPontoExistente($_POST["mac_address"], $_GET["i"]);
						
						if ($ponto_existente) {
							print "<script type=\"text/javascript\">alert('Este MAC está sendo utilizado!'); window.location = './?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}';</script>";
							die();
						}
					
					}
					
					if ($this->dados_item["equipamento_modelo_id"] != $_POST["modelo"]) {
						if ($this->dados_item["equipamento_modelo_id"]) {
							$alteracoes[] = "<b>Alteração de Modelo:</b> de '{$this->dados_item["equipamento_modelo_nome"]}' para '{$modelos[$_POST["modelo"]]}'.";
						} else {
							$alteracoes[] = "<b>Definição de Modelo:</b> '{$modelos[$_POST["modelo"]]}'.";
						}
					}
					
					if ($this->dados_item["mac_address"] != $_POST["mac_address"]) {
						if ($this->dados_item["mac_address"]) {
							$alteracoes[] = "<b>Alteração de MAC:</b> de '{$this->dados_item["mac_address"]}' para '{$_POST["mac_address"]}'.";
						} else {
							$alteracoes[] = "<b>Definição de MAC:</b> '{$_POST["mac_address"]}'.";
						}
					}
									
					if (count($alteracoes)) {
						
						$_POST["mac_address"] = $_POST["mac_address"] ? "'{$_POST["mac_address"]}'" : "NULL";
						$_POST["modelo"] = $_POST["modelo"] ? $_POST["modelo"] : "NULL";
						
						$this->bancodedados->query("
							UPDATE
								tab_clientes_comodatos
							SET
								clc_mac_address = {$_POST["mac_address"]},
								eqm_id = {$_POST["modelo"]}
							WHERE
								clc_id = {$_GET["i"]}");

						$this->registraComentario($_GET["i"], implode("<br>", $alteracoes), $_SESSION["usuario"]);
						
						$this->bancodedados->query("
							UPDATE 
								tab_clientes_iptv 
							SET 
								cip_publicado = 0 
							WHERE 
								cip_id = {$this->dados_item["iptv_id"]}");
				
						$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
						$publicacao->publicar($this->dados_item["iptv_id"]);
						
					}
						
				}
				
				if ($_POST["comentarios"]) {
					$this->registraComentario($_GET["i"], $_POST["comentarios"], $_SESSION["usuario"]);
				}

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			$this->fechar();
		}
		
	}
				
	##################
	### Substituir ###
	##################

	function substituir() {
				
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item && $this->dados_item["status_id"] == 2) {
			
			$modelos = $this->buscaModelos($this->dados_item["equipamento_id"]);
			
			if (!$_POST){
				
				$botoes = array();
				
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onclick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("clientes_comodatos.substituir.inc.php");
				
				$this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
				$this->conteudo = str_replace("[cliente]", $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace("[contrato]", $this->dados_item["contrato"], $this->conteudo);
				$this->conteudo = str_replace("[equipamento]", $this->dados_item["equipamento"], $this->conteudo);
				
				$this->conteudo = str_replace("[modelo_atual]", $this->dados_item["equipamento_modelo"], $this->conteudo);
				$this->conteudo = str_replace("[mac_address_atual]", $this->dados_item["mac_address"], $this->conteudo);
				
				$this->conteudo = str_replace("[modelo]", $this->geraSelect($modelos, null, true, "modelo", "100%"), $this->conteudo);
				$this->conteudo = str_replace("[mac_address]", '<input type="text" id="mac_address" class="inputBrancoAzulEscuro11" name="mac_address" value="" maxlength="12" style="font-family: monospace; text-transform: uppercase" onkeypress="return /[a-fA-F0-9]/.test(String.fromCharCode(event.which || event.keyCode))" style="text-transform: uppercase; width:100%"/>', $this->conteudo);
				
				$this->conteudo = str_replace("[lista_comentarios]", $this->geraComentariosListagem($_GET["i"]), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=substituir&i={$this->dados_item["id"]}", $this->conteudo);	
				
			} else {
				
				if ($this->dados_item["status_id"] == 2) {
				
					$_POST["mac_address"] = preg_replace("/[^A-F0-9]/", "", strtoupper($_POST["mac_address"]));
					$_POST["mac_address"] = strlen($_POST["mac_address"]) == 12 ? $_POST["mac_address"] : null;
					$_POST["modelo"] = preg_replace("/[^0-9]/", "", $_POST["modelo"]);
					
					if ($_POST["mac_address"] && (!$_POST["modelo"] || !array_key_exists($_POST["modelo"], $modelos))) {
						print "<script type=\"text/javascript\">alert('Modelo inválido ou não definido!'); window.location = './?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}';</script>";
						die();
					}
					
					if ($_POST["mac_address"] == $this->dados_item["mac_address"] && $_POST["modelo"] == $this->dados_item["equipamento_modelo_id"]) {
						print "<script type=\"text/javascript\">alert('Novo equipamento é igual ao atual!'); window.location = './?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}';</script>";
						die();
					}
					
					if ($_POST["mac_address"]) {
						
						$ponto_existente = $this->buscaPontoExistente($_POST["mac_address"], $_GET["i"]);
					
						if ($ponto_existente) {
							print "<script type=\"text/javascript\">alert('Este MAC está sendo utilizado!'); window.location = './?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}';</script>";
							die();
						}
						
					}
								
					$mac_address = $_POST["mac_address"] ? "'{$_POST["mac_address"]}'" : "NULL";
					$modelo = $_POST["modelo"] ? $_POST["modelo"] : "NULL";
								
					$this->bancodedados->query("
			     		INSERT INTO
				        	tab_clientes_comodatos
						SET						
							clc_mac_address = {$mac_address},
							clc_data_inicio = CURRENT_DATE,
							cli_id = {$this->dados_item["cliente_id"]},
							ass_id = {$this->dados_item["assinatura_id"]},
							cor_id = {$this->dados_item["contrato_id"]},
							equ_id = {$this->dados_item["equipamento_id"]},
							eqm_id = {$modelo},
							ccs_id = 2");

					$comodato_id = $this->bancodedados->ultimoIdInserido();
					
					$this->bancodedados->query("
						UPDATE
							tab_clientes_comodatos
						SET
							clc_substituto_id = {$comodato_id},
							clc_data_termino = CURRENT_DATE,
							clc_data_retirada = CURRENT_DATE,
							ccs_id = 5
						WHERE
							clc_id = {$_GET["i"]}");
					
					if ($_POST["mac_address"]) {
						if ($this->dados_item["mac_address"]) {
							$comentario = "<b>Ação: Equipamento substituído:</b> {$this->dados_item["equipamento_modelo"]} ({$this->dados_item["mac_address"]})<br><b>Novo:</b> {$modelos[$_POST["modelo"]]} ({$_POST["mac_address"]})";
						} else {
							$comentario = "<b>Ação: Equipamento definido:</b> {$modelos[$_POST["modelo"]]} ({$_POST["mac_address"]})";
						}
					} else {
						$comentario = "<b>Ação: Equipamento removido:</b> {$this->dados_item["equipamento_modelo"]} ({$this->dados_item["mac_address"]})";
					}
					
					$this->registraComentario($_GET["i"], $comentario, $_SESSION["usuario"]);
					$this->registraComentario($comodato_id, $comentario, $_SESSION["usuario"]);
					
					if ($_POST["comentarios"]) {
						$this->registraComentario($_GET["i"], $_POST["comentarios"], $_SESSION["usuario"]);
						$this->registraComentario($comodato_id, $_POST["comentarios"], $_SESSION["usuario"]);
					}
					
					$this->bancodedados->query("
						UPDATE
							tab_clientes_iptv
						SET
							cip_publicado = 0
						WHERE
							cip_id = {$this->dados_item["iptv_id"]}");
					
					$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
					$publicacao->publicar($this->dados_item["iptv_id"]);

					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$comodato_id}");
					
				} else {
					
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
					
				}
				
			}
			
		} else {
			$this->fechar();
		}
		
	}
				
	##########################
	### Registrar Retirada ###
	##########################
	
	function registrar_retirada() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				$this->conteudo .= $this->carregaArquivo("clientes_comodatos.registrar_retirada.inc.php");
				$this->conteudo = str_replace("[data_retirado]", '<input id="data_retirado" class="inputBrancoAzulEscuro11" type="text" value="'.date("d/m/Y").'"   onblur="return formataData(this,event);" onkeypress="return formataData(this,event);" value="" maxlength="10" size="10" name="data_retirado">', $this->conteudo);
				
				$botoes = array();

				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				$data_retirado = $_POST["data_retirado"] ? substr($_POST["data_retirado"], 6, 4)."-".substr($_POST["data_retirado"], 3, 2)."-".substr($_POST["data_retirado"], 0, 2) : date("Y-m-d");
				
				$this->bancodedados->query("
					UPDATE 
						tab_clientes_comodatos 
					SET 
						ccs_id = 5, 
						clc_data_retirada = '$data_retirado' 
					WHERE 
						tab_clientes_comodatos.clc_id = {$this->dados_item["id"]}");
				
				// Registra Comentário
				$comentario = "<b>Ação:</b> Retirado";
				if (trim($_POST["comentario"])) $comentario .= "<br><b>Comentário:</b> ".preg_replace("/[\r\n\t ]+/", " ", trim($_POST["comentario"]));
				$this->registraComentario($this->dados_item["id"], $comentario, $_SESSION["usuario"]);
				
				// Redireciona
				print header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		}
		
	}
		
	##############
	### Listar ###
	##############

	function listar() {

		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;

		$this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=clientes_comodatos"></script>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
		$this->conteudo .= $filtro->formulario;

		if ($_GET["f"] || $_GET["p"]) {

			$itens = $this->buscaItens($filtro, $pagina);
			$paginacao = $this->geraPaginacao($filtro, $itens["linhas"], $pagina);
			$listagem = $this->geraListagem($itens["itens"]);

			$this->conteudo .= '</td></tr></table>';
			$this->conteudo .= $paginacao->conteudo;
			$this->conteudo .= $listagem->conteudo;
			$this->conteudo .= $paginacao->conteudo;

		} else {
			$this->conteudo .= '</td></tr></table>';
		}

	}
  
	function inicializaFiltro($valores = "") {
		
		$itens[] = array ("tipo" => "input", "nome" => "data_inicio_inicio", "descricao" => "Data Início &raquo; Início", "valor" => "", "where" => "(tab_clientes_comodatos.clc_data_inicio >= '".substr($valores["data_inicio_inicio"], 6, 4)."-".substr($valores["data_inicio_inicio"], 3, 2)."-".substr($valores["data_inicio_inicio"], 0, 2)."')");
		$itens[] = array ("tipo" => "input", "nome" => "data_inicio_termino", "descricao" => "Data Início &raquo; Término", "valor" => "", "where" => "(tab_clientes_comodatos.clc_data_inicio <= '".substr($valores["data_inicio_termino"], 6, 4)."-".substr($valores["data_inicio_termino"], 3, 2)."-".substr($valores["data_inicio_termino"], 0, 2)."')");
		$itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $this->buscaStatus(), "where" => "(tab_clientes_comodatos_status.ccs_nome = '[valor]')");
		$itens[] = array ("tipo" => "input", "nome" => "data_termino_inicio", "descricao" => "Data Término &raquo; Início", "valor" => "", "where" => "(tab_clientes_comodatos.clc_data_termino >= '".substr($valores["data_termino_inicio"], 6, 4)."-".substr($valores["data_termino_inicio"], 3, 2)."-".substr($valores["data_termino_inicio"], 0, 2)."')");
		$itens[] = array ("tipo" => "input", "nome" => "data_termino_termino", "descricao" => "Data Término &raquo; Término", "valor" => "", "where" => "(tab_clientes_comodatos.clc_data_termino <= '".substr($valores["data_termino_termino"], 6, 4)."-".substr($valores["data_termino_termino"], 3, 2)."-".substr($valores["data_termino_termino"], 0, 2)."')");
		$itens[] = array ("tipo" => "select", "nome" => "equipamento", "descricao" => "Equipamento", "valor" => $this->buscaEquipamentos(), "where" => "(tab_equipamentos.equ_nome LIKE '%[valor]%')");
		$itens[] = array ("tipo" => "input", "nome" => "mac_address", "descricao" => "MAC", "valor" => "", "where" => "(tab_clientes_comodatos.clc_mac_address LIKE '%[valor]%')");
		$itens[] = array ("tipo" => "input", "nome" => "cliente", "descricao" => "Cliente", "valor" => "", "where" => "(tab_clientes.cli_nome LIKE '%[valor]%')");
		$itens[] = array ("tipo" => "select", "nome" => "origem", "descricao" => "Origem", "valor" => $this->buscaOrigens(), "where" => "(tab_clientes_origens.clo_id = '[valor]')");
		
		$filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		
		return $filtro;
		
	}

	function buscaItens($filtro, $pagina) {
		
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
				
		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_clientes_comodatos.clc_id AS id,
				tab_clientes_comodatos.clc_mac_address AS mac_address,
				DATE_FORMAT(tab_clientes_comodatos.clc_data_inicio, '%d/%m/%Y') AS data_inicio,
				DATE_FORMAT(tab_clientes_comodatos.clc_data_termino, '%d/%m/%Y') AS data_termino,
				DATE_FORMAT(tab_clientes_comodatos.clc_data_retirada, '%d/%m/%Y') AS data_retirada,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes_origens.clo_id AS origem_id,
				tab_clientes_origens.clo_nome AS origem_nome,
				tab_equipamentos.equ_nome AS equipamento,
				tab_equipamentos_modelos.eqm_nome AS equipamento_modelo,
				tab_clientes_comodatos_status.ccs_nome AS status_nome
			FROM
				tab_clientes_comodatos
				INNER JOIN tab_clientes ON (tab_clientes_comodatos.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
				INNER JOIN tab_contratos ON (tab_clientes_comodatos.cor_id = tab_contratos.cor_id)
				INNER JOIN tab_equipamentos ON (tab_clientes_comodatos.equ_id = tab_equipamentos.equ_id)
				LEFT JOIN tab_equipamentos_modelos ON (tab_clientes_comodatos.eqm_id = tab_equipamentos_modelos.eqm_id)
				INNER JOIN tab_clientes_comodatos_status ON(tab_clientes_comodatos.ccs_id = tab_clientes_comodatos_status.ccs_id)
			WHERE
				$filtro->parametros_sql".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
			ORDER BY
				tab_clientes.cli_nome
			LIMIT
				$limit_inicio,
				$limit_fim");
				
		$linhas = $this->bancodedados->linhasTotal($query);
		$itens = array();
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes["clientes"]["ver_dt"]) {
				$dados["cliente_id"] = "<a href=\"./?m=clientes&a=ver_dt&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
			}
			
			$dados["cliente_nome"] = "[{$dados["cliente_id"]}]<div style=\"float:right;\"><i>{$dados["origem_nome"]}</i></div><br><div style=\"height:14px;width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;\">{$dados["cliente_nome"]}</div>";
			
			if ($this->permissoes["clientes_comodatos"]["ver"]) {
				$dados["link"] = "javascript:verClientesComodatos({$dados["id"]})";
			}
			
			$dados["parametros"] = unserialize($dados["parametros"]);
			
			if ($dados["equipamento_modelo"]) {
				$dados["equipamento"] .= "<br><i>{$dados["equipamento_modelo"]}</i>";
			} else {
				$dados["equipamento"] .= "<br><i>Modelo não definido</i>";
			}
			
			if (!$dados["mac_address"]) {
				$dados["mac_address"] = "<i>Não definido</i>";
			} else {
				$dados["mac_address"] = "<span style=\"font-family:monospace\">{$dados["mac_address"]}</span>";
			}
			
			$dados["status_nome"] = str_replace(" ", "<br>", $dados["status_nome"]);

			$itens[] = $dados;
			
		}
		
		return array("linhas" => $linhas, "itens" => $itens);
		
	}
  
	function geraListagem($itens) {
	    
		$cabecalho = array("cliente_nome" => "Cliente", "equipamento" => "Equipamento", "mac_address" => "MAC", "data_inicio" => "Início", "data_termino" => "Término", "data_retirada" => "Retirada", "status_nome" => "Status" );
	    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(0, 110, 80, 60, 60, 60, 90), null, array("left", "left"));
	    
		return $listagem;
		
	}
	  
	function geraPaginacao($filtro, $total, $pagina) {
		
		$paginacao = controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);

		return $paginacao;
	  
	}
		
	##############
	### Fechar ###
	##############

	function fecharRemover($id) {
		
		unset($this->conteudo);
		
		if (!empty($id)) {
			$this->conteudo .= "
			<script type=\"text/javascript\">
				if (!window.opener.closed) {
					if (window.opener.atualizaEquipamentosComodatos) {
					window.opener.atualizaEquipamentosComodatos('$id');
					}
				}
			</script>";
		}
		
		$this->conteudo .= '<script type="text/javascript">window.close();</script>';
		
	}
	
	function fechar(){
		
		$this->conteudo .= '<script type="text/javascript">window.close();</script>';
		
	}
			
	###########
	### RDS ###
	###########

	function geraListagemClientesComodatos($cliente_id, $exibe_inativos = false) {
		
		$comodatos = $this->buscaClienteComodatos($cliente_id, $exibe_inativos);
		
		$cabecalho = array("equipamento" => "Equipamento", "plano" => "Plano", "mac_address" => "MAC", "data_inicio" => "Início", "data_termino" => "Término", "data_retirada" => "Retirada", "status_nome" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $comodatos, 0, false, array(0, 0, 100, 60, 60, 60, 100), null, array("left", "left", "center", "center"));
		
		return $listagem->conteudo;
		
	}

	#################
	### Consultas ###
	#################
	
	function buscaPontoExistente($mac_address, $cliente_comodato_id = null) {
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
		    FROM
		    	tab_clientes_comodatos
			WHERE
				clc_mac_address = '$mac_address' AND
				ccs_id IN (1, 2)".($cliente_comodato_id ? " AND
				clc_id != $cliente_comodato_id" : ""));
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function buscaComentarios($cliente_comodato_id) {
		
		$comentarios = array();
				
		$query = $this->bancodedados->query("
			SELECT
				IF(tab_usuarios.usu_id IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel, 
			    DATE_FORMAT(tab_clientes_comodatos_comentarios.ccc_datahora, '%d/%m/%Y (%H:%i)') AS data,
			    tab_clientes_comodatos_comentarios.ccc_conteudo AS conteudo 
		    FROM
		    	tab_clientes_comodatos_comentarios 
			    LEFT OUTER JOIN tab_usuarios ON (tab_clientes_comodatos_comentarios.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_clientes_comodatos_comentarios.clc_id = $cliente_comodato_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["detalhamento"][] = "<b>{$dados["responsavel"]}</b> em <b>{$dados["data"]}</b>";
			if ($dados["conteudo"]) $dados["detalhamento"][] = substr($dados["conteudo"], 0, 3) == "<b>" ? $dados["conteudo"] : "<b>Conteúdo:</b> {$dados["conteudo"]}";
			$dados["detalhamento"] = implode("<br>", $dados["detalhamento"]);
			
			$comentarios[] = $dados;
			
		}
		
		return $comentarios;
		
	}
	
	function registraComentario($comodato_id, $conteudo, $responsavel_id) {
		
		$responsavel_id = $responsavel_id ? $responsavel_id : "NULL";
		$conteudo = addslashes($conteudo);
		
		$this->bancodedados->query("
			INSERT INTO 
				tab_clientes_comodatos_comentarios
			SET
				ccc_datahora = NOW(), 
				ccc_conteudo = '$conteudo', 
				clc_id = $comodato_id, 
				usu_id = $responsavel_id");
		
	}
	
	function geraComentariosListagem($id) {
		
		$comentarios = $this->buscaComentarios($id);
		$cabecalho = array ("detalhamento" => "Detalhamento");
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
		
		return $listagem->conteudo;
		
	}
	
	function geraSelectContratosCliente(){
		
		$contratos = array();
				
		$query = $this->bancodedados->query("
			SELECT
				CONCAT(tab_planos.pla_nome, ' (', tab_contratos.cor_codigo, ')') AS nome_plano,
				CONCAT(rel_ass_cor_estacontido.cor_id, ',' , tab_assinaturas.ass_id) AS id  
			FROM
				tab_assinaturas 
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id) 
				INNER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id)
			WHERE
				tab_assinaturas.cli_id = {$_GET["i"]} AND
				tab_contratos.cor_pai IS NULL");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != null){
			$contratos[$dados["id"]] = $dados["nome_plano"];			
		}
		
		return $contratos;
		
	}
	
	function geraSelectEquipamentosComodatos(){
		
		$equipamentos = array();
		
		$query = $this->bancodedados->query("
			SELECT 
				equ_nome AS equipamento, 
				equ_id AS id 
			FROM 
				tab_equipamentos 
			ORDER BY 
				equ_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != null){
			$equipamentos[$dados["id"]] = $dados["equipamento"];			
		}
		
		return $equipamentos;
		
	}
	
	function buscaModelos($equipamento_id) {
		
		$modelos = array();
		
		$query = $this->bancodedados->query("
			SELECT 
				eqm_id AS id, 
				eqm_nome AS nome 
			FROM 
				tab_equipamentos_modelos 
			WHERE 
				equ_id = {$equipamento_id} AND 
				eqm_disponivel = 1 
			ORDER BY 
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != null){
			$modelos[$dados["id"]] = $dados["nome"];			
		}
		
		return $modelos;
		
	}

	function buscaStatus(){

		$status = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT 
				ccs_nome AS nome 
			FROM 
				tab_clientes_comodatos_status 
			WHERE 
				ccs_nome IS NOT NULL 
			ORDER BY 
				ccs_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["id"] = $dados["nome"];
			$status[] = $dados;
		}
		
		return $status;
		
	}

	function buscaOrigens(){

		$origens = array();

		$query = $this->bancodedados->query("
			SELECT 
				clo_id AS id,
				clo_nome AS nome
			FROM 
				tab_clientes_origens
			WHERE 
				1 
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$origens[] = $dados;
		}
		
		return $origens;
		
	}

	function buscaEquipamentos(){

		$equipamentos = array();

		$query = $this->bancodedados->query("
			SELECT
				equ_nome AS nome 
			FROM 
				tab_equipamentos 
			WHERE 
				equ_nome IS NOT NULL 
			ORDER BY 
				equ_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["id"] = $dados["nome"];
			$equipamentos[] = $dados;
		}
		
		return $equipamentos;
		
	}
	
	function buscaClienteComodatos($cliente_id, $exibe_inativos) {
		
		$comodatos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_comodatos.clc_id AS id,
				tab_clientes_comodatos.clc_mac_address AS mac_address,
				tab_equipamentos.equ_nome AS equipamento,
				tab_equipamentos_modelos.eqm_nome AS equipamento_modelo,
				tab_planos.pla_nome AS plano,
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
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				tab_clientes_comodatos.cli_id = $cliente_id".($exibe_inativos ? "" : " AND
				tab_clientes_comodatos.ccs_id <> 5"));
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
				$dados["link"] = "javascript:verClientesComodatos({$dados["id"]})";
			}
			
			if ($dados["equipamento_modelo"]) {
				$dados["equipamento"] .= "<br><i>{$dados["equipamento_modelo"]}</i>";
			} else {
				$dados["equipamento"] .= "<br><i>Modelo não definido</i>";
			}
			
			if (!$dados["mac_address"]) {
				$dados["mac_address"] = "<i>Não definido</i>";
			} else {
				$dados["mac_address"] = "<span style=\"font-family:monospace\">{$dados["mac_address"]}</span>";
			}

			$dados["status_nome"] = str_replace(" ", "<br>", $dados["status_nome"]);
			
			$comodatos[] = $dados;
			
		}
		
		return $comodatos;
		
	}
	
}

?>
