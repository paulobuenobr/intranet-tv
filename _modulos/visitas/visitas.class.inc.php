<?

require_once ("_global/modulo.class.inc.php");

class visitas extends modulo {
	
    function __construct($bancodedados, $permissoes, $executar = true) {
		
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		
		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) {
				$_GET["a"] = "editar";
			}
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
		
	}
	
	function buscaDadosCliente($id) {
		
		$query = "SELECT cli_id AS id, cli_nome AS nome FROM tab_clientes WHERE cli_id = '$id'";
		$query = $this->bancodedados->query($query);
		
		if ($this->bancodedados->linhas($query)) {
			$this->dados_cliente = $this->bancodedados->resultadoArray($query);
		} else {
			unset($this->dados_cliente);
		}
		
	}
	
	function buscaDadosItem($id) {
		
		$this->dados_item = $this->buscaDadosVisita($id);
		
	}
	
	function buscaDadosVisita($visita_id) {
		
		$query = "
			SELECT
				tab_visitas.vis_id AS id,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_visitas.con_id AS condominio_id,
				tab_visitas.cha_id AS chamado_id,
				tab_condominios.con_nome AS condominio_nome,
				tab_visitas.vis_data AS data_sem_formatacao,
				DATE_FORMAT(tab_visitas.vis_data, '%d/%m/%Y') AS data,
				tab_visitas.vip_id AS periodo_id,
				tab_visitas_tipos.vit_id AS tipo_id,
				tab_visitas_tipos.vit_nome AS tipo_nome,
				tab_visitas_status.via_id AS status_id,
				CONCAT(tab_visitas_status.via_nome, ' (', tab_visitas.vis_agendamentos, ')') AS status_nome,
				tab_visitas.cor_id AS contrato_id,
				tab_usuarios.usu_id AS usuario_id,
				tab_usuarios.usu_nome AS usuario_nome,
				tab_contratos.crt_id AS contrato_tipo_id,
				tab_contratos.cor_pai AS contrato_pai_id,
				tab_contratos.cor_codigo AS contrato_codigo
			FROM
				tab_visitas
				INNER JOIN tab_visitas_tipos ON (tab_visitas.vit_id = tab_visitas_tipos.vit_id)
				INNER JOIN tab_visitas_status ON (tab_visitas.via_id = tab_visitas_status.via_id)
				LEFT OUTER JOIN tab_condominios ON ( tab_condominios.con_id = tab_visitas.con_id)
				LEFT OUTER JOIN tab_clientes ON (tab_visitas.cli_id = tab_clientes.cli_id)
				LEFT OUTER JOIN tab_contratos ON (tab_contratos.cor_id = tab_visitas.cor_id)
				LEFT OUTER JOIN tab_usuarios ON (tab_visitas.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_visitas.vis_id = $visita_id".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : "");
		
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			
			$periodos = $this->buscaPeriodos();
			
			$dados["periodo_nome"] = $periodos[$dados["periodo_id"]];
			if ($dados["data"] && $dados["periodo_nome"]) {
				$dados["data_periodo"] = "{$dados["data"]} ({$dados["periodo_nome"]})";
			}
			if ($dados["contrato_tipo_id"] == 11) {
				$dados["tipo_nome"] .= " (Ponto Principal)";
			} elseif ($dados["contrato_tipo_id"] == 12) {
				$dados["tipo_nome"] .= " (Ponto Adicional)";
			} elseif ($dados["contrato_tipo_id"] == 14) {
				$dados["tipo_nome"] .= " (Mudança de Endereço)";
			} elseif ($dados["contrato_tipo_id"] == 13) {
				$dados["tipo_nome"] .= " (Migração)";
			}
			$dados["comentarios"] = $this->buscaComentarios($dados["id"]);
			
		}
		
		return $dados;
		
	}
	
	function buscaDadosTurno($visita_tipo_id, $data, $periodo) {
		
		$data = substr($data, 0, 4) . "-" . substr($data, 4, 2) . "-" . substr($data, 6, 2);
		
		$query = "
			SELECT
				vic_id AS id,
				vic_cota_residencia AS cotas_casa,
				vic_cota_condominio AS cotas_condominio
			FROM
				tab_visitas_cotas
			WHERE
				vit_id = {$visita_tipo_id} AND
				vic_data = '$data' AND
				vic_periodo = $periodo";
				
		$query = $this->bancodedados->query($query);
		
		$dados = $this->bancodedados->resultadoArray($query);
		
		if (!$dados) {
			
			$semana = date('w', strtotime($data));
			
			if ($semana == 6) {
				if ($periodo == 1) {
					$dados["cotas_casa"] = VISITAS_CASAS_SABADO_MANHA; 
					$dados["cotas_condominio"] = VISITAS_CONDOMINIO_SABADO_MANHA; 
				} elseif ($periodo == 2) {				
					$dados["cotas_casa"] = VISITAS_CASAS_SABADO_TARDE; 
					$dados["cotas_condominio"] = VISITAS_CONDOMINIO_SABADO_TARDE; 
				} elseif ($periodo == 3) {				
					$dados["cotas_casa"] = VISITAS_CASAS_SABADO_NOITE; 
					$dados["cotas_condominio"] = VISITAS_CONDOMINIO_SABADO_NOITE;
				}
			} elseif ($semana == 0) {
				if ($periodo == 1) {
					$dados["cotas_casa"] = VISITAS_CASAS_DOMINGO_MANHA; 
					$dados["cotas_condominio"] = VISITAS_CONDOMINIO_DOMINGO_MANHA; 
				} elseif ($periodo == 2) {				
					$dados["cotas_casa"] = VISITAS_CASAS_DOMINGO_TARDE; 
					$dados["cotas_condominio"] = VISITAS_CONDOMINIO_DOMINGO_TARDE; 
				} elseif ($periodo == 3) {				
					$dados["cotas_casa"] = VISITAS_CASAS_DOMINGO_NOITE; 
					$dados["cotas_condominio"] = VISITAS_CONDOMINIO_DOMINGO_NOITE;
				}
			} else {
				if ($periodo == 1) {
					$dados["cotas_casa"] = VISITAS_CASAS_MANHA; 
					$dados["cotas_condominio"] = VISITAS_CONDOMINIO_MANHA; 
				} elseif ($periodo == 2) {				
					$dados["cotas_casa"] = VISITAS_CASAS_TARDE; 
					$dados["cotas_condominio"] = VISITAS_CONDOMINIO_TARDE; 
				} elseif ($periodo == 3) {				
					$dados["cotas_casa"] = VISITAS_CASAS_NOITE; 
					$dados["cotas_condominio"] = VISITAS_CONDOMINIO_NOITE;
				}
			}
		}
		
		return $dados;
		
	}
	
	function alterar_turno() {
		
		$dados_turno = $this->buscaDadosTurno($_GET["visita_tipo"], $_GET["data"], $_GET["periodo"]);
		
		if (!$_POST) {
			
			$this->conteudo .= $this->carregaArquivo("visitas.alterar_turno.inc.php");
			
			$periodos = array("1" => "Manhã", "2" => "Tarde", "3" => "Noite");
			$data_periodo = substr($_GET["data"], 6, 2) . "/" . substr($_GET["data"], 4, 2) . "/" . substr($_GET["data"], 0, 4) . " ( {$periodos[$_GET["periodo"]]} )";
			
			$this->conteudo = str_replace("[data_visita]", $data_periodo, $this->conteudo);
			$this->conteudo = str_replace("[n_visitas_residencial]", '<input name="n_visitas_residencial" id="n_visitas_residencial" type="text" value="' . $dados_turno["cotas_casa"] . '"   class="inputBrancoAzulEscuro11" style="width:120px" maxlength="10" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)">', $this->conteudo);
			$this->conteudo = str_replace("[n_visitas_condominiais]", '<input name="n_visitas_condominial" id="n_visitas_condominial" type="text" value="' . $dados_turno["cotas_condominio"] . '"  class="inputBrancoAzulEscuro11" style="width:120px" maxlength="10" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)">', $this->conteudo);
			
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			
			$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace('[action]', "?m=visitas&a=alterar_turno&visita_tipo={$_GET["visita_tipo"]}&data={$_GET["data"]}&periodo={$_GET["periodo"]}", $this->conteudo);

		} else {
				
			$_POST["n_visitas_residencial"] = preg_replace("/[^0-9]/", "", $_POST["n_visitas_residencial"]);
			$_POST["n_visitas_residencial"] = is_numeric($_POST["n_visitas_residencial"]) ? $_POST["n_visitas_residencial"] : "NULL";
			$_POST["n_visitas_condominial"] = preg_replace("/[^0-9]/", "", $_POST["n_visitas_condominial"]);
			$_POST["n_visitas_condominial"] = is_numeric($_POST["n_visitas_condominial"]) ? $_POST["n_visitas_condominial"] : "NULL";

			if ($_POST["n_visitas_residencial"] == "NULL" && $_POST["n_visitas_condominial"] == "NULL") {
				
				if ($dados_turno) {
					$this->bancodedados->query("DELETE FROM tab_visitas_cotas WHERE vit_id = {$_GET["visita_tipo"]} AND vic_id = {$dados_turno["id"]}");
				}
				
			} elseif ($_POST["n_visitas_residencial"] != $dados_turno["cotas_casa"] || $_POST["n_visitas_condominial"]  != $dados_turno["cotas_condominio"]) {
				
				if ($dados_turno["id"]) {
					$query = "
						UPDATE
							tab_visitas_cotas
						SET
							vic_cota_residencia = {$_POST["n_visitas_residencial"]},
							vic_cota_condominio = {$_POST["n_visitas_condominial"]}
						WHERE
							vit_id = {$_GET["visita_tipo"]} AND
							vic_id = {$dados_turno["id"]}";
					$this->bancodedados->query($query);
				} else {
					$query = "
						INSERT INTO
							tab_visitas_cotas (vic_data, vic_periodo, vic_cota_residencia, vic_cota_condominio, vit_id)
						VALUES ({$_GET["data"]}, {$_GET["periodo"]}, {$_POST["n_visitas_residencial"]}, {$_POST["n_visitas_condominial"]}, {$_GET["visita_tipo"]})";
						$this->bancodedados->query($query);
				}
			
			}
			
			$this->conteudo .= '<script type="text/javascript">if (window.opener) window.opener.location.reload(true); window.close()</script>';

		}
		
	}
	
	function listar_visitas_suporte_tecnico() {
		
		$_GET["mes"] = $_GET["mes"] ? $_GET["mes"] : date("m");
		$_GET["ano"] = $_GET["ano"] ? $_GET["ano"] : date("Y");
		
		$this->conteudo .= $this->carregaArquivo("visitas.listar_visita_tecnica.inc.php");
		
		$this->conteudo = str_replace('[visita_tipo]', '2', $this->conteudo);
		$this->conteudo = str_replace('[visitas_agenda_periodo]', $this->buscaVisitasAgendaPeriodo($_GET["mes"], $_GET["ano"]), $this->conteudo);
		$this->conteudo = str_replace('[visitas_agenda]', $this->geraAgendaVisitaTecnica(2, $_GET["mes"], $_GET["ano"], true), $this->conteudo);
		$visitas_hoje = $this->geraListagemVisitasTecnicaMigracao(2, 2, 2);
		$this->conteudo = str_replace('[visitas_hoje_casas]', $visitas_hoje["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_hoje_condominios]', $visitas_hoje["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_hoje]', $visitas_hoje["listagem"], $this->conteudo);
		$visitas_proximos_dias = $this->geraListagemVisitasTecnicaMigracao(2, 3, 2);
		$this->conteudo = str_replace('[visitas_proximos_dias_casas]', $visitas_proximos_dias["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_proximos_dias_condominios]', $visitas_proximos_dias["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_proximos_dias]', $visitas_proximos_dias["listagem"], $this->conteudo);
		$visitas_pendentes = $this->geraListagemVisitasTecnicaMigracao(2, 1, 2);
		$this->conteudo = str_replace('[visitas_pendentes_casas]', $visitas_pendentes["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_pendentes_condominios]', $visitas_pendentes["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_pendentes]', $visitas_pendentes["listagem"], $this->conteudo);
		$visitas_desagendadas = $this->geraListagemVisitasTecnicaMigracao(1, 4, 2);
		$this->conteudo = str_replace('[visitas_desagendadas_casas]', $visitas_desagendadas["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_desagendadas_condominios]', $visitas_desagendadas["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_desagendadas]', $visitas_desagendadas["listagem"], $this->conteudo);
		$this->conteudo = str_replace('[agenda_mes]', $_GET["mes"], $this->conteudo);
		$this->conteudo = str_replace('[agenda_ano]', $_GET["ano"], $this->conteudo);
		
	}
		
	function listar_visitas_retiradas() {
		
		$_GET["mes"] = $_GET["mes"] ? $_GET["mes"] : date("m");
		$_GET["ano"] = $_GET["ano"] ? $_GET["ano"] : date("Y");
		
		$this->conteudo .= $this->carregaArquivo("visitas.listar_visita_tecnica.inc.php");
		
		$this->conteudo = str_replace('[visita_tipo]', '4', $this->conteudo);
		$this->conteudo = str_replace('[visitas_agenda_periodo]', $this->buscaVisitasAgendaPeriodo($_GET["mes"], $_GET["ano"]), $this->conteudo);
		$this->conteudo = str_replace('[visitas_agenda]', $this->geraAgendaVisitaTecnica(4, $_GET["mes"], $_GET["ano"], true), $this->conteudo);
		$visitas_hoje = $this->geraListagemVisitasTecnicaMigracao(2, 2, 4);
		$this->conteudo = str_replace('[visitas_hoje_casas]', $visitas_hoje["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_hoje_condominios]', $visitas_hoje["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_hoje]', $visitas_hoje["listagem"], $this->conteudo);
		$visitas_proximos_dias = $this->geraListagemVisitasTecnicaMigracao(2, 3, 4);
		$this->conteudo = str_replace('[visitas_proximos_dias_casas]', $visitas_proximos_dias["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_proximos_dias_condominios]', $visitas_proximos_dias["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_proximos_dias]', $visitas_proximos_dias["listagem"], $this->conteudo);
		$visitas_pendentes = $this->geraListagemVisitasTecnicaMigracao(2, 1, 4);
		$this->conteudo = str_replace('[visitas_pendentes_casas]', $visitas_pendentes["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_pendentes_condominios]', $visitas_pendentes["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_pendentes]', $visitas_pendentes["listagem"], $this->conteudo);
		$visitas_desagendadas = $this->geraListagemVisitasTecnicaMigracao(1, 4, 4);
		$this->conteudo = str_replace('[visitas_desagendadas_casas]', $visitas_desagendadas["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_desagendadas_condominios]', $visitas_desagendadas["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_desagendadas]', $visitas_desagendadas["listagem"], $this->conteudo);
		$this->conteudo = str_replace('[agenda_mes]', $_GET["mes"], $this->conteudo);
		$this->conteudo = str_replace('[agenda_ano]', $_GET["ano"], $this->conteudo);
		
	}
	
	function buscaDadosChamado($chamado_id) {
		
		$query = "
			SELECT
				cha_id AS id,
				chc_id AS categoria_id,
				chs_id AS status_id
			FROM	
				tab_chamados
			WHERE
				cha_id = $chamado_id";
				
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultadoArray($query);
		
	}

	function agendar_visita_tecnica() {
		
		$chamado = $this->buscaDadosChamado($_GET["i"]);
		$visita = $this->buscaVisitaChamado($_GET["i"]);
		$cliente_tipo = $this->buscaClienteTipo($_GET["i"]);
		
		//if ($chamado["categoria_id"] == 1052455) {
		//	$visita_tipo_id = 4;
		//	$agenda_tipo = "Retirada";
		//} else {
			$visita_tipo_id = 2;
			$agenda_tipo = "Suporte Técnico";
		//}
		
		$_GET["mes"] = $_GET["mes"] ? $_GET["mes"] : ($visita["mes"] ? $visita["mes"] : date("m"));
		$_GET["ano"] = $_GET["ano"] ? $_GET["ano"] : ($visita["ano"] ? $visita["ano"] : date("Y"));
		
		$this->conteudo .= $this->carregaArquivo("visitas.agendar_visita_tecnica.inc.php");
		
		$this->conteudo = str_replace('[visita_tipo]', $visita_tipo_id, $this->conteudo);
		$this->conteudo = str_replace('[agenda_tipo]', $agenda_tipo, $this->conteudo);
		$this->conteudo = str_replace('[visitas_agenda_periodo]', $this->buscaVisitasAgendaPeriodo($_GET["mes"], $_GET["ano"]), $this->conteudo);
		$this->conteudo = str_replace('[visitas_agenda]', $this->geraAgendaVisitaTecnica($visita_tipo_id, $_GET["mes"], $_GET["ano"], false, $cliente_tipo, $visita["id"]), $this->conteudo);

	}
	
	function buscaClienteTipo($chamado_id) {
		
		$query = "
			SELECT
				IF(tab_chamados.con_id IS NOT NULL OR tab_clientes.con_id IS NOT NULL, 'condominial', 'residencial') AS tipo
			FROM
				tab_chamados
				LEFT OUTER JOIN tab_clientes ON (tab_chamados.cli_id = tab_clientes.cli_id)
			WHERE
				cha_id = $chamado_id";
				
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query, 0, 0);
		
	}
	
	function buscaVisitaChamado($chamado_id) {
		
		$query = "
			SELECT
				vis_id AS id,
				DATE_FORMAT(tab_visitas.vis_data, '%m') AS mes,
				DATE_FORMAT(tab_visitas.vis_data, '%Y') AS ano
			FROM	
				tab_visitas
			WHERE
				cha_id = $chamado_id";
				
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultadoArray($query);
		
	}
	
	function buscaPeriodos($periodo_id = null) {
		
		$query = "
			SELECT
				vip_id AS id,
				vip_nome AS nome
			FROM
				tab_visitas_periodos
			ORDER BY
				vip_nome";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$periodos[$dados["id"]] = $dados["nome"];
		}
		
		return $periodo_id ? $periodos[$periodo_id] : $periodos;
		
	}
	
	function buscaVendedor($contrato_id) {
		
		if (!$contrato_id) {
			return "";
		}
		
		$query = "
			SELECT
				tab_usuarios.usu_nome AS nome
			FROM
				tab_vendas
				INNER JOIN tab_usuarios ON (tab_vendas.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_vendas.cor_id = $contrato_id";
				
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query, 0, 0);
		
	}
	
	###################
	### Comentarios ###
	###################
	

	function buscaComentariosCategorias($tipo = '0,1,2') {
		
		$query = "SELECT vcc_id AS id, vcc_nome AS nome FROM tab_visitas_comentarios_categorias WHERE vcc_tipo IN ($tipo) ORDER BY nome";
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$categorias[$dados["id"]] = $dados["nome"];
		}
		
		return $categorias;
		
	}
	
	function registraComentario($visita_id, $conteudo, $categoria_id, $usuario_id = null) {
		
		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$categoria_id = $categoria_id ? $categoria_id : "NULL";
		$conteudo = addslashes(trim($conteudo));
		
		$query = "INSERT INTO tab_visitas_comentarios (vic_datahora, vis_id, vic_conteudo, vcc_id, usu_id) VALUES (NOW(), $visita_id, '$conteudo', $categoria_id, $usuario_id)";
		$this->bancodedados->query($query);
		
	}
	
	function buscaComentarios($id) {
		
		$query = "
			SELECT
				DATE_FORMAT(tab_visitas_comentarios.vic_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
				IF(tab_visitas_comentarios_categorias.vcc_id = 1, '', tab_visitas_comentarios_categorias.vcc_nome) AS categoria,
				tab_visitas_comentarios.vic_conteudo AS conteudo,
				IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel
			FROM
				tab_visitas_comentarios
				LEFT OUTER JOIN tab_usuarios ON (tab_visitas_comentarios.usu_id = tab_usuarios.usu_id)
				LEFT OUTER JOIN tab_visitas_comentarios_categorias ON (tab_visitas_comentarios.vcc_id = tab_visitas_comentarios_categorias.vcc_id)
			WHERE
				tab_visitas_comentarios.vis_id = $id
			ORDER BY
				tab_visitas_comentarios.vic_datahora ASC,
				tab_visitas_comentarios.vic_id ASC";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["detalhamento"][] = "<b>{$dados["responsavel"]}</b> em <b>{$dados["data_hora"]}</b>";
			if ($dados["categoria"])
				$dados["detalhamento"][] .= "<b>Categoria:</b> {$dados["categoria"]}";
			if ($dados["conteudo"])
				$dados["detalhamento"][] = substr($dados["conteudo"], 0, 3) == "<b>" ? $dados["conteudo"] : "<b>Conteúdo:</b> {$dados["conteudo"]}";
			$dados["detalhamento"] = @implode('<br>', $dados["detalhamento"]);
			$comentarios[] = $dados;
		}
		
		return $comentarios;
		
	}
	
	function geraComentariosListagem($comentarios) {
		
		$cabecalho = array("detalhamento" => "Detalhamento");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
		
		return $listagem->conteudo;
		
	}
	
	function buscaClientesVisitas($cliente_id) {
		
		$periodos = $this->buscaPeriodos();
		
		$query = "
		SELECT
			tab_visitas.vis_id AS id,
			tab_visitas_tipos.vit_nome AS tipo,
			DATE_FORMAT(tab_visitas.vis_data, '%d/%m/%Y') AS data,
			tab_visitas.vip_id AS periodo,
			CONCAT(tab_visitas_status.via_nome, ' (', tab_visitas.vis_agendamentos, ')') AS status
		FROM
			tab_visitas
			INNER JOIN tab_visitas_status ON (tab_visitas_status.via_id = tab_visitas.via_id)
			INNER JOIN tab_visitas_tipos ON (tab_visitas_tipos.vit_id = tab_visitas.vit_id)
		WHERE
			tab_visitas.cli_id = $cliente_id";
			
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["data"]) {
				$dados["periodo"] = $periodos[$dados["periodo"]];
				$dados["data_periodo"] = $dados["periodo"] ? "{$dados["data"]} ({$dados["periodo"]})" : $dados["data"];
			}
			$dados["ultimo_comentario"] = $this->buscaVisitaUltimoComentario($dados["id"]);
			if ($this->permissoes["visitas"]["ver"]) {
				$dados["link"] = "javascript:verVisita({$dados["id"]});";
			}
			$visitas[] = $dados;
		}
		
		return $visitas;
		
	}
	
	function geraClientesVisitasListagem($dados) {
		
		$cabecalho = array("tipo" => "Tipo", "data_periodo" => "Data/Período", "status" => "Status", "ultimo_comentario" => "Último Comentário");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, null, null, array("center", "center", "center", "left"));
		
		return $listagem->conteudo;
		
	}
	
	function buscaVisitaUltimoComentario($id) {
		
		$periodos = $this->buscaPeriodos();
		
		$query = "
			SELECT
				DATE_FORMAT(tab_visitas_comentarios.vic_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
				IF(tab_visitas_comentarios_categorias.vcc_id = 1, '', tab_visitas_comentarios_categorias.vcc_nome) AS categoria,
				tab_visitas_comentarios.vic_conteudo AS conteudo,
				IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel
			FROM
				tab_visitas_comentarios
				LEFT OUTER JOIN tab_usuarios ON (tab_visitas_comentarios.usu_id = tab_usuarios.usu_id)
				LEFT OUTER JOIN tab_visitas_comentarios_categorias ON (tab_visitas_comentarios.vcc_id = tab_visitas_comentarios_categorias.vcc_id)
			WHERE
				tab_visitas_comentarios.vis_id = $id
			ORDER BY
				tab_visitas_comentarios.vic_datahora DESC,
				tab_visitas_comentarios.vic_id DESC
			LIMIT 0, 1";
			
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			$ultimo_comentario[] = "<b>{$dados["responsavel"]}</b> em <b>{$dados["data_hora"]}</b>";
			if ($dados["categoria"])
				$ultimo_comentario[] .= "<b>Categoria:</b> {$dados["categoria"]}";
			if ($dados["conteudo"])
				$ultimo_comentario[] = substr($dados["conteudo"], 0, 3) == "<b>" ? $dados["conteudo"] : "<b>Conteúdo:</b> {$dados["conteudo"]}";
			$ultimo_comentario = @implode('<br>', $ultimo_comentario);
		} else {
			$ultimo_comentario = "Nenhum Comentário Disponível";
		}
		
		return preg_replace("/[\r\n\t ]+/", " ", $ultimo_comentario);
		
	}
	
	function getVisitaChamado($chamado_id) {
		
		$query = "
			SELECT
				vis_id AS visita_id
			FROM
				tab_visitas
			WHERE
				cha_id = $chamado_id";
				
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			
			return $dados["visita_id"];
			
		} else {
			
			$query_select = "
				SELECT
					cli_id AS cliente_id,
					con_id AS condominio_id,
					chc_id AS categoria_id
				FROM
				   tab_chamados
				WHERE 
					cha_id = {$_GET["chamado_id"]}";
					
			$query_select = $this->bancodedados->query($query_select);
			$dados_chamado = $this->bancodedados->resultadoArray($query_select);
			
			if ($dados_chamado) {
				
				$dados_chamado["cliente_id"] = $dados_chamado["cliente_id"] ? $dados_chamado["cliente_id"] : "NULL";
				$dados_chamado["condominio_id"] = $dados_chamado["condominio_id"] ? $dados_chamado["condominio_id"] : "NULL";
				
				//$visita_tipo_id = $dados_chamado["categoria_id"] == 1052455 ? 4 : 2;
				$visita_tipo_id = 2;
				
				$query_insert = "
					INSERT INTO
						tab_visitas (cli_id, con_id, vit_id, vis_agendamentos, via_id, cha_id, usu_id, vis_data_geracao)
					VALUES ({$dados_chamado["cliente_id"]}, {$dados_chamado["condominio_id"]}, {$visita_tipo_id}, 0, 1, {$_GET["chamado_id"]}, {$_SESSION["usuario"]}, CURDATE())";
				$query_insert = $this->bancodedados->query($query_insert);
				
			}
			
			return $this->bancodedados->ultimoIdInserido();
			
		}
		
	}
	
	##############
	### Fechar ###
	##############
	

	function fechar() {
		
		if ($this->dados_cliente["id"]) {
			
			$this->conteudo = "
				<script type=\"text/javascript\">
					if (window.opener && window.opener.buscaVisitas) {
						window.opener.buscaVisitas('{$this->dados_cliente["id"]}');
					}
					window.close();
				</script>";
				
		} elseif (!$this->dados_item["condominio_id"]) {
			
			$this->conteudo	= "
				<script type=\"text/javascript\">
				  if (window.opener && window.opener.atualizaVisitas) {
				    window.opener.atualizaVisitas('{$this->dados_item["tipo_id"]}');
				  }
				  window.close();
				</script>";
				
		}
		
	}
	
	###############
	### Remover ###
	###############
	

	function remover() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("visitas.remover.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
			} else {
				$this->bancodedados->query("DELETE FROM tab_visitas_comentarios WHERE vis_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM tab_visitas WHERE vis_id = {$_GET["i"]}");
				$this->fechar();
			}
			
		} else {
			
			$this->fechar();
			
		}
		
	}
	
	###########
	### Ver ###
	###########

	function ver() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if ($this->dados_item["condominio_id"] || $this->dados_item["cliente_id"]) {
				$this->conteudo .= $this->carregaArquivo("visitas.ver.inc.php");
				if ($this->dados_item["condominio_id"]) {
					$this->conteudo = str_replace('[nome_titulo]', "Condominio", $this->conteudo);
					$this->conteudo = str_replace('[nome_valor]', $this->dados_item["condominio_nome"], $this->conteudo);
				} else {
					$this->conteudo = str_replace('[nome_titulo]', "Cliente", $this->conteudo);
					$this->conteudo = str_replace('[nome_valor]', $this->dados_item["cliente_nome"], $this->conteudo);
				}
				$this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
				$this->conteudo = str_replace('[vendedor]', $this->buscaVendedor($this->dados_item["contrato_id"]), $this->conteudo);
				$this->conteudo = str_replace('[instalador]', $this->dados_item["usuario_nome"], $this->conteudo);
				$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
			} else {
				$this->conteudo .= $this->carregaArquivo("visitas.reserva.ver.inc.php");
			}
			
			$this->conteudo = str_replace('[data_periodo]', $this->dados_item["data_periodo"], $this->conteudo);
			$this->conteudo = str_replace('[comentarios]', $this->geraComentariosListagem($this->dados_item["comentarios"]), $this->conteudo);
			$this->conteudo = str_replace('[tipo_id]', $this->dados_item["tipo_id"], $this->conteudo);
			$this->conteudo = str_replace('[alterado]', $_GET["alterado"] ? "1" : "0", $this->conteudo);
			
			$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
			
			if ($this->permissoes["visitas"]["editar"]) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->dados_item["status_id"] == 2 && $this->permissoes["visitas"]["desagendar"]) $botoes[] = '<input type="button" name="desagendar" class="buttonAzulEscuroBranco11" value="Desagendar" onClick="window.location=\'?m=visitas&a=desagendar&i=' . $_GET["i"] . '\';">';
			if ($this->permissoes["contratos"]["ver"] && $this->dados_item["contrato_id"]) $botoes[] = '<input type="button" name="ver_contrato" class="buttonAzulEscuroBranco11" value="Ver Contrato" onClick="verContrato(' . $this->dados_item["contrato_id"] . ');">';
			if ($this->dados_item["status_id"] == 2 && $this->permissoes["contratos"]["visualizar_vistoria"] && $this->dados_item["contrato_id"]) $botoes[] = '<input type="button" name="visualizar_vistoria" class="buttonAzulEscuroBranco11" value="Visualizar (Vistoria)" onClick="visualizarContrato(' . $this->dados_item["contrato_id"] . ', \'vistoria\');">';
			if ($this->permissoes["visitas"]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=visitas&a=remover&i=' . $_GET["i"] . '\';">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
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
			
			if (!$_POST) {
				
				if ($this->dados_item["condominio_id"] || $this->dados_item["cliente_id"]) {
					$this->conteudo .= $this->carregaArquivo("visitas.editar.inc.php");
					if ($this->dados_item["condominio_id"]) {
						$this->conteudo = str_replace('[nome_titulo]', "Condominio", $this->conteudo);
						$this->conteudo = str_replace('[nome_valor]', $this->dados_item["condominio_nome"], $this->conteudo);
					} else {
						$this->conteudo = str_replace('[nome_titulo]', "Cliente", $this->conteudo);
						$this->conteudo = str_replace('[nome_valor]', $this->dados_item["cliente_nome"], $this->conteudo);
					}
					$this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
					$this->conteudo = str_replace('[vendedor]', $this->buscaVendedor($this->dados_item["contrato_id"]), $this->conteudo);
					$this->conteudo = str_replace('[instalador]', $this->dados_item["usuario_nome"], $this->conteudo);
					$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
				} else {
					$this->conteudo .= $this->carregaArquivo("visitas.reserva.editar.inc.php");
				}
				
				$this->conteudo = str_replace('[data_periodo]', $this->dados_item["data_periodo"], $this->conteudo);
				$this->conteudo = str_replace('[comentarios]', $this->geraComentariosListagem($this->dados_item["comentarios"]), $this->conteudo);
				$this->conteudo = str_replace('[comentario_categoria]', $this->geraSelect($this->buscaComentariosCategorias(($this->permissoes["visitas"]["editar_plus"]) ? "1,2,3,4,5,6" : "1"), null, true, "comentario_categoria", "100%"), $this->conteudo);
				$this->conteudo = str_replace('[alterado]', $_GET["alterado"] ? "1" : "0", $this->conteudo);
				$this->conteudo = str_replace('[tipo_id]', $this->dados_item["tipo_id"], $this->conteudo);
				
				$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
				
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				if ($this->dados_item["status_id"] == 2 && $this->permissoes["visitas"]["desagendar"])
					$botoes[] = '<input type="button" name="desagendar" class="buttonAzulEscuroBranco11" value="Desagendar" onClick="window.location=\'?m=visitas&a=desagendar&i=' . $_GET["i"] . '\';">';
				if ($this->permissoes["contratos"]["ver"] && $this->dados_item["contrato_id"])
					$botoes[] = '<input type="button" name="ver_contrato" class="buttonAzulEscuroBranco11" value="Ver Contrato" onClick="verContrato(' . $this->dados_item["contrato_id"] . ');">';
				if ($this->dados_item["status_id"] == 2 && $this->permissoes["contratos"]["visualizar_vistoria"] && $this->dados_item["contrato_id"])
					$botoes[] = '<input type="button" name="visualizar_vistoria" class="buttonAzulEscuroBranco11" value="Visualizar (Vistoria)" onClick="visualizarContrato(' . $this->dados_item["contrato_id"] . ', \'vistoria\');">';
				if ($this->permissoes["visitas"]["remover"])
					$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=visitas&a=remover&i=' . $_GET["i"] . '\';">';
					
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}{$this->janela}", $this->conteudo);

			} else {
				
				// Registra Comentário
				$this->registraComentario($_GET["i"], trim($_POST["comentario_conteudo"]), $_POST["comentario_categoria"], $_SESSION["usuario"]);
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
				// Redireciona
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}&alterado=" . ($this->dados_item["status_id"] <= 2 ? "1" : "0"));

			}
			
		} else {
			
			$this->fechar();
			
		}
		
	}
	
	###############
	### Agendar ###
	###############	

	function visitasAlocar($visita_id, $data, $periodo) {
		
		$this->buscaDadosItem($visita_id);
		
		if ($this->dados_item) {
			
			if ($this->dados_item["status_id"] > 2) {
				return;
			}
			
			// Aloca Visita
			$query = "UPDATE tab_visitas SET vis_data = '$data', vip_id = $periodo, via_id = 2, vis_agendamentos = vis_agendamentos + 1 WHERE vis_id = $visita_id";
			$this->bancodedados->query($query);
			
			// Atualiza Confirmação
			if ($this->dados_item["contrato_id"]) {
				$query = "UPDATE tab_contratos SET cor_data_confirmacao = IF(cor_data_confirmacao IS NULL, CURDATE(), cor_data_confirmacao) WHERE tab_contratos.cor_id = {$this->dados_item["contrato_id"]}";
				$this->bancodedados->query($query);
				// Define Data Confirmação Assinaturas
				if ($this->dados_item["contrato_tipo_id"] < 14) {
					$query = "UPDATE tab_assinaturas INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id) SET ass_data_confirmacao = IF(ass_data_confirmacao IS NULL, CURDATE(), ass_data_confirmacao) WHERE rel_ass_cor_estacontido.cor_id = {$this->dados_item["contrato_id"]} AND tab_assinaturas.ast_id = 1";
					$this->bancodedados->query($query);
				}
			}
			
			// Registra Comentário
			$this->registraComentario($visita_id, "<b>Alocada:</b> Para " . date("d/m/Y", strtotime($data)) . " (" . $this->buscaPeriodos($periodo) . ")", 17, $_SESSION["usuario"]);
			
			// Mudança de Endereço
			if ($this->dados_item["contrato_tipo_id"] == 14) {
				$contratos = controle::instanciaObjeto("contratos", $this->bancodedados, $this->permissoes, false);
				if ($this->dados_item["data_sem_formatacao"] && strtotime($this->dados_item["data_sem_formatacao"]) <= strtotime(date("Y-m-d")) && strtotime($data) > strtotime(date("Y-m-d"))) {
					$contratos->desmigraEnderecoCliente($this->dados_item["contrato_id"]);
				} elseif ((!$this->dados_item["data_sem_formatacao"] || (strtotime($this->dados_item["data_sem_formatacao"]) > strtotime(date("Y-m-d")))) && ($data == date("Y-m-d"))) {
					$contratos->migraEnderecoCliente($this->dados_item["contrato_id"]);
				}
				$contratos->finalizaBloqueioContratoInativo($this->dados_item["contrato_pai_id"]);
			}
			
			// Registra Atividade
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], 13, "alocar", $visita_id);
			
		}
		
	}
	
	function visitasReservar($data, $periodo){
		
		$query = "
			INSERT INTO
				tab_visitas (vit_id, vis_agendamentos, vis_data, vip_id,  via_id, usu_id, vis_data_geracao)
			VALUES (1, 0, '$data' , $periodo, 2, {$_SESSION["usuario"]}, CURDATE())";
		$query = $this->bancodedados->query($query);
		
	}
	
	function visitasDeseservar($visita_id){
		
		// Comentario
		$query = "DELETE FROM tab_visitas_comentarios WHERE vis_id = $visita_id";
		$query = $this->bancodedados->query($query);
		
		// Visita
		$query = "DELETE FROM tab_visitas WHERE vis_id = $visita_id";
		$query = $this->bancodedados->query($query);
		
	}
	
	function visitasAlocarMigracao($visita_id, $data, $periodo) {
		
		$this->buscaDadosItem($visita_id);
		
		if ($this->dados_item) {
			
			if ($this->dados_item["status_id"] > 2) {
				return;
			}
			
			// Aloca Visita
			$query = "UPDATE tab_visitas SET vis_data = '$data', vip_id = $periodo, via_id = 2, vis_agendamentos = vis_agendamentos + 1 WHERE vis_id = $visita_id";
			$this->bancodedados->query($query);
			
			// Atualiza Confirmação
			if ($this->dados_item["contrato_id"]) {
				$query = "UPDATE tab_contratos SET cor_data_confirmacao = IF(cor_data_confirmacao IS NULL, CURDATE(), cor_data_confirmacao) WHERE tab_contratos.cor_id = {$this->dados_item["contrato_id"]}";
				$this->bancodedados->query($query);
				// Define Data Confirmação Assinaturas
				if ($this->dados_item["contrato_tipo_id"] < 14) {
					$query = "UPDATE tab_assinaturas INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id) SET ass_data_confirmacao = IF(ass_data_confirmacao IS NULL, CURDATE(), ass_data_confirmacao) WHERE rel_ass_cor_estacontido.cor_id = {$this->dados_item["contrato_id"]} AND tab_assinaturas.ast_id = 1";
					$this->bancodedados->query($query);
				}
			}
			
			// Registra Comentário
			$this->registraComentario($visita_id, "<b>Alocada:</b> Para " . date("d/m/Y", strtotime($data)) . " (" . $this->buscaPeriodos($periodo) . ")", 17, $_SESSION["usuario"]);
			
			// Mudança de Endereço
			if ($this->dados_item["contrato_tipo_id"] == 14) {
				$contratos = controle::instanciaObjeto("contratos", $this->bancodedados, $this->permissoes, false);
				if ($this->dados_item["data_sem_formatacao"] && strtotime($this->dados_item["data_sem_formatacao"]) <= strtotime(date("Y-m-d")) && strtotime($data) > strtotime(date("Y-m-d"))) {
					$contratos->desmigraEnderecoCliente($this->dados_item["contrato_id"]);
				} elseif ((!$this->dados_item["data_sem_formatacao"] || (strtotime($this->dados_item["data_sem_formatacao"]) > strtotime(date("Y-m-d")))) && ($data == date("Y-m-d"))) {
					$contratos->migraEnderecoCliente($this->dados_item["contrato_id"]);
				}
				$contratos->finalizaBloqueioContratoInativo($this->dados_item["contrato_pai_id"]);
			}
			
			// Registra Atividade
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], 13, "alocar", $visita_id);
			
		}
		
	}
	
	################################
	### Gera Cobrança Instalação ###
	################################
	

	function geraCobrancaInstalacao($dados_visita, $data_instalacao) {
		
		if (!($ajuste_id = $this->existeAjuste($dados_visita["contrato_id"], $data_instalacao))) {
			$ajuste_id = $this->geraAjusteInstalacao($dados_visita["contrato_id"], $data_instalacao);
		}
		
		if ($ajuste_id) {
			$vencimento = $this->avancarDias($data_instalacao, 5);
			$dados_cobranca = $this->buscaCobranca($ajuste_id);
			if ($dados_cobranca) {
				if ($dados_cobranca["vencimento"] == $vencimento) {
					return $dados_cobranca["id"];
				}
				$this->cancelaCobranca($dados_cobranca["id"]);
			}
			$cobranca_id = $this->geraCobranca($dados_visita["cliente_id"], $vencimento, $data_instalacao);
			if ($cobranca_id) {
				$query = "UPDATE tab_cobrancas SET coa_data_bloqueio = '" . $this->avancarDias($vencimento, 5) . "' WHERE coa_id = $cobranca_id";
				$this->bancodedados->query($query);
				return $cobranca_id;
			}
		}
		
	}
	
	function buscaCobranca($ajuste_id) {
		
		$query = "
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_data_vencimento AS vencimento
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
			WHERE
				tab_cobrancas_detalhes.ajf_id = $ajuste_id AND
				tab_cobrancas.cot_id = 1";
		
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultadoArray($query);
				
	}
	
	function cancelaCobranca($cobranca_id, $desagendamento = false) {
		
		$query = "UPDATE tab_cobrancas SET cot_id = 12 WHERE coa_id = $cobranca_id";
		$this->bancodedados->query($query);
		
		$query = "UPDATE tab_cobrancas_detalhes SET cod_cancelado = 1 WHERE coa_id = $cobranca_id";
		$this->bancodedados->query($query);
		
		// Registra Comentário
		$this->registraComentarioCobranca($cobranca_id, "<b>Ação:</b> Cobrança Cancelada (Visita " . ($desagendamento ? "Desagendada" : "Reagendada)"));

	}
	
	function registraComentarioCobranca($cobranca_id, $conteudo, $usuario_id = null) {
		
		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$conteudo = addslashes(trim($conteudo));
		
		$query = "INSERT INTO tab_cobrancas_comentarios (com_datahora, coa_id, com_conteudo, usu_id) VALUES (NOW(), $cobranca_id, '$conteudo', $usuario_id)";
		$this->bancodedados->query($query);
		
	}
	
	function geraCobranca($cliente_id, $vencimento, $data_instalacao) {
		
		$formato_id = $this->buscaFormatoBoleto();
		
		if ($formato_id) {
			$geracao = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);
			$vencimento_cobranca = date("m/Y", strtotime(date("Y-m-01", strtotime($data_instalacao)) . " + 1 month"));
			return $geracao->geraCobrancaAvulsa($cliente_id, $formato_id, $vencimento_cobranca, $vencimento_cobranca, $vencimento, date('d', strtotime($vencimento)));
		}
		
	}
	
	function buscaFormatoBoleto() {
		
		$query = "
			SELECT
				MAX(cof_id) AS id
			FROM
				tab_cobrancas_formatos
			WHERE
				cft_id = 3 AND
				cfs_id = 1";
		
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query, 0, 0);
		
	}
	
	function avancarDias($data_instalacao, $dias = 0) {
		
		$vencimento = date("Y-m-d", strtotime("$data_instalacao + $dias days"));
		
		if (date("w", strtotime($vencimento)) == 0 || date("w", strtotime($vencimento)) == 6) {
			$vencimento = date("Y-m-d", strtotime("$vencimento next monday"));
		}
		
		return $vencimento;
		
	}
	
	function existeAjuste($contrato_id, $data_instalacao) {
		
		$query = "
			SELECT
				ajf_id AS ajuste_id
			FROM
				tab_ajustes_financeiros
			WHERE
				cor_id = $contrato_id AND
				afs_id IN (5, 7) AND
				afc_id = 19";
		
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			return $this->geraAjusteInstalacao($contrato_id, $data_instalacao, $dados["ajuste_id"]);
		}
		
	}
	
	function geraAjusteInstalacao($contrato_id, $data_instalacao, $ajuste_id = null) {
		
		$query = "
			SELECT
				cli_id AS cliente_id,
				cor_codigo AS codigo,
				cor_parametros AS parametros
			FROM
				tab_contratos
			WHERE
				cor_id = $contrato_id";
		
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			$valor_instalacao = $dados["parametros"]["tabela_valor_final_instalacao"];
			if ($valor_instalacao) {
				$parcelamento = $dados["parametros"]["parcelamento"] ? $dados["parametros"]["parcelamento"] - 1 : 0;
				$valor_instalacao = intval($valor_instalacao / ($parcelamento + 1));
				$competencia = date("Y-m-01", strtotime($data_instalacao));
				// Insere Ajuste
				if ($ajuste_id) {
					$query = "UPDATE tab_ajustes_financeiros SET ajf_valor = $valor_instalacao, ajf_competencia_inicio = '$competencia', ajf_competencia_termino = '$competencia' WHERE ajf_id = $ajuste_id";
					$this->bancodedados->query($query);
					// Cancela Cobrança se houve alteração no ajuste
					if ($this->bancodedados->linhasAfetadas() && ($dados_cobranca = $this->buscaCobranca($ajuste_id))) {
						$this->cancelaCobranca($dados_cobranca["id"]);
					}
					return $ajuste_id;
				} else {
					$query = "INSERT INTO tab_ajustes_financeiros (ajf_datahora, cli_id, cor_id, afc_id, aft_id, ajf_valor, ajf_valor_tipo, ajf_competencia_inicio, ajf_competencia_termino, afs_id) VALUES (NOW(), {$dados["cliente_id"]}, $contrato_id, 19, 1, $valor_instalacao, 1, '$competencia', '$competencia', 5)";
					$this->bancodedados->query($query);
					$ajuste_id = $this->bancodedados->ultimoIdInserido();
					// Registra Comentário
					$this->registraComentarioAjusteFinanceiro($ajuste_id, "Instalação referente ao contrato <b>{$dados["codigo"]}</b>");
					return $ajuste_id;
				}
			}
		}
		
		if ($ajuste_id) {
			// Cancela Ajuste
			$query = "UPDATE tab_ajustes_financeiros SET afs_id = 6 WHERE ajf_id = $ajuste_id";
			$this->bancodedados->query($query);
		}
		
	}
	
	function registraComentarioAjusteFinanceiro($ajuste_id, $conteudo, $usuario_id = null) {
		
		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$conteudo = addslashes(trim($conteudo));
		
		$query = "INSERT INTO tab_ajustes_financeiros_comentarios (afm_datahora, ajf_id, afm_conteudo, usu_id) VALUES (NOW(), $ajuste_id, '$conteudo', $usuario_id)";
		$this->bancodedados->query($query);
		
	}
	
	##################
	### Desagendar ###
	##################

	function desagendar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				$this->conteudo .= $this->carregaArquivo("visitas.desagendar.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=desagendar&i={$_GET["i"]}", $this->conteudo);

			} else {
				
				if ($this->dados_item["condominio_id"] || $this->dados_item["cliente_id"]) {
					
					$query = "UPDATE tab_visitas SET via_id = 1, vis_data = NULL WHERE vis_id = {$_GET["i"]}";
					$this->bancodedados->query($query);
					if ($this->dados_item["contrato_id"]) {
						// Retira Data Confirmação
						$query = "UPDATE tab_contratos SET cor_data_confirmacao = NULL WHERE tab_contratos.cor_id = {$this->dados_item["contrato_id"]}";
						$this->bancodedados->query($query);
						if ($this->dados_item["contrato_tipo_id"] < 14) {
							// Retira Data Data Confirmação Assinaturas
							$query = "UPDATE tab_assinaturas INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id) SET ass_data_confirmacao = NULL WHERE rel_ass_cor_estacontido.cor_id = {$this->dados_item["contrato_id"]} AND tab_assinaturas.ast_id = 1";
							$this->bancodedados->query($query);
						}
						// Mudança de Endereço
						if ($this->dados_item["contrato_tipo_id"] == 14) {
							if ($this->dados_item["data_sem_formatacao"] && strtotime($this->dados_item["data_sem_formatacao"]) <= strtotime(date("Y-m-d"))) {
								$contratos = controle::instanciaObjeto("contratos", $this->bancodedados, $this->permissoes, false);
								$contratos->desmigraEnderecoCliente($this->dados_item["contrato_id"]);
							}
						}
					}
					
					// Registra Comentário
					$this->registraComentario($_GET["i"], $_POST["comentario"], 19, $_SESSION["usuario"]);
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}&alterado=1");
					
				} else {
					
					$this->visitasDeseservar($this->dados_item["id"]);
					$this->fechar();
					
				}
				
			}
			
		} else {
			
			$this->fechar();
			
		}
		
	}
	
	##############
	### Listar ###
	##############	

	function listar_visitas_instalacao() {
		
		$_GET["mes"] = $_GET["mes"] ? $_GET["mes"] : date("m");
		$_GET["ano"] = $_GET["ano"] ? $_GET["ano"] : date("Y");
		
		$this->conteudo .= $this->carregaArquivo("visitas.listar.inc.php");
		
		$this->conteudo = str_replace('[visitas_agenda_periodo]', $this->buscaVisitasAgendaPeriodo($_GET["mes"], $_GET["ano"]), $this->conteudo);
		$this->conteudo = str_replace('[visitas_agenda]', $this->buscaVisitasAgenda($_GET["mes"], $_GET["ano"]), $this->conteudo);
		$visitas_hoje = $this->buscaVisitasHoje();
		$this->conteudo = str_replace('[visitas_hoje_condominios]', $visitas_hoje["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_hoje_casas]', $visitas_hoje["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_hoje]', $visitas_hoje["listagem"], $this->conteudo);
		$visitas_proximos_dias = $this->buscaVisitasProximosDias();
		$this->conteudo = str_replace('[visitas_proximos_dias_condominios]', $visitas_proximos_dias["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_proximos_dias_casas]', $visitas_proximos_dias["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_proximos_dias]', $visitas_proximos_dias["listagem"], $this->conteudo);
		$visitas_pendentes = $this->buscaVisitasPendentes();
		$this->conteudo = str_replace('[visitas_pendentes_condominios]', $visitas_pendentes["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_pendentes_casas]', $visitas_pendentes["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_pendentes]', $visitas_pendentes["listagem"], $this->conteudo);
		$visitas_aguardando_sem_contato = $this->buscaVisitasAguardandoSemContato();
		$this->conteudo = str_replace('[visitas_aguardando_sem_contato_condominios]', $visitas_aguardando_sem_contato["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_aguardando_sem_contato_casas]', $visitas_aguardando_sem_contato["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_aguardando_sem_contato]', $visitas_aguardando_sem_contato["listagem"], $this->conteudo);
		$visitas_aguardando_com_contato = $this->buscaVisitasAguardandoComContato();
		$this->conteudo = str_replace('[visitas_aguardando_com_contato_condominios]', $visitas_aguardando_com_contato["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_aguardando_com_contato_casas]', $visitas_aguardando_com_contato["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_aguardando_com_contato]', $visitas_aguardando_com_contato["listagem"], $this->conteudo);
		$this->conteudo = str_replace('[agenda_mes]', $_GET["mes"], $this->conteudo);
		$this->conteudo = str_replace('[agenda_ano]', $_GET["ano"], $this->conteudo);
		
	}

	function listar_visitas_migracao() {
		
		$_GET["mes"] = $_GET["mes"] ? $_GET["mes"] : date("m");
		$_GET["ano"] = $_GET["ano"] ? $_GET["ano"] : date("Y");
		
		$this->conteudo .= $this->carregaArquivo("visitas.listar_visita_migracao.inc.php");
		
		$this->conteudo = str_replace('[visitas_agenda_periodo]', $this->buscaVisitasAgendaPeriodo($_GET["mes"], $_GET["ano"]), $this->conteudo);
		$this->conteudo = str_replace('[visitas_agenda]', $this->buscaVisitasAgendaMigracao($_GET["mes"], $_GET["ano"]), $this->conteudo);
		$visitas_hoje = $this->buscaVisitasMigracaoHoje();
		$this->conteudo = str_replace('[visitas_hoje_condominios]', $visitas_hoje["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_hoje_casas]', $visitas_hoje["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_hoje]', $visitas_hoje["listagem"], $this->conteudo);
		$visitas_proximos_dias = $this->buscaVisitasMigracaoProximosDias();
		$this->conteudo = str_replace('[visitas_proximos_dias_condominios]', $visitas_proximos_dias["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_proximos_dias_casas]', $visitas_proximos_dias["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_proximos_dias]', $visitas_proximos_dias["listagem"], $this->conteudo);
		$visitas_pendentes = $this->buscaVisitasMigracaoPendentes();
		$this->conteudo = str_replace('[visitas_pendentes_condominios]', $visitas_pendentes["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_pendentes_casas]', $visitas_pendentes["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_pendentes]', $visitas_pendentes["listagem"], $this->conteudo);
		$visitas_desagendadas = $this->buscaVisitasMigracaoDesagendadas();
		$this->conteudo = str_replace('[visitas_desagendadas_condominios]', $visitas_desagendadas["condominios"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_desagendadas_casas]', $visitas_desagendadas["casas"], $this->conteudo);
		$this->conteudo = str_replace('[visitas_desagendadas]', $visitas_desagendadas["listagem"], $this->conteudo);
		$this->conteudo = str_replace('[agenda_mes]', $_GET["mes"], $this->conteudo);
		$this->conteudo = str_replace('[agenda_ano]', $_GET["ano"], $this->conteudo);
		
	}
	
	function buscaVisitasAgendaPeriodo($mes, $ano) {
		
		$meses = array("Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");

		$mes_anterior = date("m", strtotime("$ano-$mes-01 LAST MONTH"));
		$ano_anterior = date("Y", strtotime("$ano-$mes-01 LAST MONTH"));
		$mes_posterior = date("m", strtotime("$ano-$mes-01 1 MONTH"));
		$ano_posterior = date("Y", strtotime("$ano-$mes-01 1 MONTH"));
		
		$url_anterior = "?m=visitas&a={$_GET["a"]}" . ($_GET["i"] ? "&i={$_GET["i"]}" : "") . "&mes=$mes_anterior&ano=$ano_anterior";
		$url_posterior = "?m=visitas&a={$_GET["a"]}" . ($_GET["i"] ? "&i={$_GET["i"]}" : "") . "&mes=$mes_posterior&ano=$ano_posterior";

		$conteudo = ' ( <a class="txtBranco11" href="' . $url_anterior . '">&laquo;</a> ';
		$conteudo .= $meses[$_GET["mes"] - 1] . " de " . $_GET["ano"];
		$conteudo .= ' <a class="txtBranco11" href="' . $url_posterior . '">&raquo;</a></b> ) ';

		return $conteudo;
		
	}
	
	
	function buscaVisitasAgenda($mes, $ano) {
		
		return $this->geraAgendaInstalacoes($mes, $ano);
		
	}
	
	function buscaVisitasAgendaMigracao($mes, $ano) {
		
		return $this->geraAgendaVisitaMigracao($mes, $ano);
		
	}
	
	//	VISITAS MIGRACAO
	
	function buscaVisitasMigracaoHoje() {
		
		return $this->geraListagemVisitasTecnicaMigracao(2, 2, 3);
		
	}
	
	function buscaVisitasMigracaoProximosDias() {
		
		return $this->geraListagemVisitasTecnicaMigracao(2, 3, 3);
		
	}
	
	function buscaVisitasMigracaoPendentes() {
		
		return $this->geraListagemVisitasTecnicaMigracao(2, 1, 3);
		
	}
	
	function buscaVisitasMigracaoDesagendadas() {
		
		return $this->geraListagemVisitasTecnicaMigracao(1, 4, 3);
		
	}
	
	//	VISITAS TECNICAS
	
	function buscaVisitasTecnicaHoje($visita_tipo_id) {
		
		return $this->geraListagemVisitasTecnicaMigracao(2, 2, $visita_tipo_id);
		
	}
	
	function buscaVisitasTecnicaProximosDias($visita_tipo_id) {
		
		return $this->geraListagemVisitasTecnicaMigracao(2, 3, $visita_tipo_id);
		
	}
	
	function buscaVisitasTecnicaPendentes($visita_tipo_id) {
		
		return $this->geraListagemVisitasTecnicaMigracao(2, 1, $visita_tipo_id);
		
	}
	
	function buscaVisitasTecnicaDesagendadas($visita_tipo_id) {
		
		return $this->geraListagemVisitasTecnicaMigracao(1, 4, $visita_tipo_id);
		
	}
	
	//	VISITAS INSTALACOES
	
	function buscaVisitasHoje() {
		
		return $this->geraListagemVisitas(2, 2);
		
	}
	
	function buscaVisitasProximosDias() {
		
		return $this->geraListagemVisitas(2, 3);
		
	}
	
	function buscaVisitasPendentes() {
		
		return $this->geraListagemVisitas(2, 1);
		
	}
	
	function buscaVisitasAguardandoSemContato() {
		
		return $this->geraListagemVisitas(1, 4, false);
		
	}
	
	function buscaVisitasAguardandoComContato() {
		
		return $this->geraListagemVisitas(1, 4, true);
		
	}
	
	function geraAgendaInstalacoes($mes, $ano) {
		
		$calendario = controle::instanciaObjeto("visitas.calendario", $this->bancodedados, $this->permissoes);
		$calendario->setDados($this->buscaAgendaInstalacoesVisitas($mes, $ano));
		
		return $calendario->getMonthView($mes, $ano, $this->permissoes["visitas"]);
		
	}
	
	function geraAgendaVisitaTecnica($visita_tipo_id, $mes, $ano, $tipo_listar, $cliente_tipo = null, $visita_id = null) {
		
		$calendario = controle::instanciaObjeto("visitas.calendario", $this->bancodedados, $this->permissoes);
		$calendario->setDados($this->buscaAgendaVisitasTecnicas($visita_tipo_id, $mes, $ano));
		
		return $calendario->getMonthHTMLSuporteTecnico($visita_tipo_id, $mes, $ano, $tipo_listar, $cliente_tipo, $visita_id);
		
	}
	
	function geraAgendaVisitaMigracao($mes, $ano) {
		
		$calendario = controle::instanciaObjeto("visitas.calendario", $this->bancodedados, $this->permissoes);
		$calendario->setDados($this->buscaAgendaVisitasMigracao($mes, $ano));
		
		return $calendario->getMonthHTMLMigracao($mes, $ano);
		
	}
	
	function buscaAgendaVisitasMigracao($mes, $ano) {
		
		$query = "
			SELECT
				tab_visitas.vis_id AS id,
				DATE_FORMAT(tab_visitas.vis_data, '%Y%m%d') AS data,
				tab_visitas.vip_id AS periodo,
				tab_visitas.cli_id AS cliente_id,
				tab_visitas.con_id AS condominio_id,
				tab_visitas.cha_id AS chamado_id,
				tab_clientes.cli_nome AS cliente,
				tab_condominios.con_nome AS condominio,
				tab_condominios_blocos.cob_nome AS bloco,
				SUBSTRING(tab_clientes.cli_complemento, 1, 1) AS complemento_tipo,
				SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento,
				tab_logradouros.log_nome AS logradouro,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, tab_condominios.con_numero, tab_clientes.cli_numero)) AS numero,
				tab_visitas.vis_agendamentos AS agendamentos,
				tab_visitas.via_id AS status_id
			FROM
				tab_visitas
				LEFT OUTER JOIN tab_clientes ON (tab_visitas.cli_id = tab_clientes.cli_id)
				LEFT OUTER JOIN tab_condominios ON (IF(tab_visitas.con_id IS NOT NULL, tab_visitas.con_id, tab_clientes.con_id) = tab_condominios.con_id)
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				INNER JOIN tab_ceps ON (IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)) = tab_ceps.cep_id)
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
			WHERE
				tab_visitas.vis_data BETWEEN '" . date("Y-m-d", strtotime("$ano-$mes-01")) . "' AND '" . date("Y-m-t", strtotime("$ano-$mes-01")) . "' AND
				tab_visitas.via_id IN (2, 3) AND
				tab_visitas.vit_id = 3
			GROUP BY
				tab_visitas.vis_data,
				tab_visitas.vip_id,
				tab_visitas.vis_id
			ORDER BY
				tab_visitas.via_id DESC";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$tipo = $dados["condominio"] ? "condominio" : "casa";
			if ($dados["cliente_id"] && $dados["condominio"]) {
				$dados["local"][] = "{$dados["condominio"]} {$dados["bairro"]}";
			} else {
				$dados["local"][] = $dados["logradouro"];
				if ($dados["numero"])
					$dados["local"][] = $dados["numero"];
				$dados["local"][] = $dados["bairro"];
				$dados["local"][] = $dados["cidade"];
			}
			if ($dados["complemento"]) {
				$dados["local"][] = $this->buscaComplementoTipo($dados["complemento_tipo"]) . $dados["complemento"];
			}
			$dados["local"] = implode(", ", $dados["local"]);
			if ($dados["cliente_id"]) {
				$dados["telefone"] = $this->buscaClienteTelefones($dados["cliente_id"], " / ", false);
			} elseif ($dados["condominio_id"]) {
				$dados["telefone"] = $this->buscaCondominioTelefones($dados["condominio_id"], " / ", false);
			}
			$dados["ultimo_comentario"] = preg_replace("/[\r\n\t ]+/", " ", $this->buscaVisitaComentariosUltimo($dados["id"]));
			
			$visitas[$dados["data"]][$dados["periodo"]][] = $dados;
			
		}
		
		return $visitas;
		
	}
	
	
	function buscaAgendaVisitasTecnicas($visita_tipo_id, $mes, $ano) {
		
		$query = "
			SELECT
				tab_visitas.vis_id AS id,
				DATE_FORMAT(tab_visitas.vis_data, '%Y%m%d') AS data,
				tab_visitas.vip_id AS periodo,
				tab_visitas.cli_id AS cliente_id,
				tab_visitas.con_id AS condominio_id,
				tab_visitas.cha_id AS chamado_id,
				tab_clientes.cli_nome AS cliente,
				tab_condominios.con_nome AS condominio,
				tab_condominios_blocos.cob_nome AS bloco,
				SUBSTRING(tab_clientes.cli_complemento, 1, 1) AS complemento_tipo,
				SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento,
				tab_logradouros.log_nome AS logradouro,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_id AS cidade_id,
				tab_cidades.cid_nome AS cidade,
				IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, tab_condominios.con_numero, tab_clientes.cli_numero)) AS numero,
				tab_visitas.vis_agendamentos AS agendamentos,
				tab_visitas.via_id AS status_id
			FROM
				tab_visitas
				LEFT OUTER JOIN tab_clientes ON (tab_visitas.cli_id = tab_clientes.cli_id)
				LEFT OUTER JOIN tab_condominios ON (IF(tab_visitas.con_id IS NOT NULL, tab_visitas.con_id, tab_clientes.con_id) = tab_condominios.con_id)
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				INNER JOIN tab_ceps ON (IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)) = tab_ceps.cep_id)
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
			WHERE
				tab_visitas.vis_data BETWEEN '" . date("Y-m-d", strtotime("$ano-$mes-01")) . "' AND '" . date("Y-m-t", strtotime("$ano-$mes-01")) . "' AND
				tab_visitas.via_id IN (2, 3) AND
				tab_visitas.vit_id = {$visita_tipo_id}
			GROUP BY
				tab_visitas.vis_data,
				tab_visitas.vip_id,
				tab_visitas.vis_id
			ORDER BY
				tab_visitas.via_id DESC";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$tipo = $dados["condominio"] ? "condominio" : "casa";
			if ($dados["cliente_id"] && $dados["condominio"]) {
				$dados["local"][] = "{$dados["condominio"]} {$dados["bairro"]}";
			} else {
				$dados["local"][] = $dados["logradouro"];
				if ($dados["numero"]) $dados["local"][] = $dados["numero"];
				$dados["local"][] = $dados["bairro"];
				$dados["local"][] = $dados["cidade"];
			}
			if ($dados["complemento"]) {
				$dados["local"][] = $this->buscaComplementoTipo($dados["complemento_tipo"]) . $dados["complemento"];
			}
			$dados["local"] = implode(", ", $dados["local"]);
			if ($dados["cliente_id"]) {
				$dados["telefone"] = $this->buscaClienteTelefones($dados["cliente_id"], " / ", false);
			} elseif ($dados["condominio_id"]) {
				$dados["telefone"] = $this->buscaCondominioTelefones($dados["condominio_id"], " / ", false);
			}
			$dados["ultimo_comentario"] = preg_replace("/[\r\n\t ]+/", " ", $this->buscaVisitaComentariosUltimo($dados["id"]));
			
			$visitas[$dados["data"]][$dados["periodo"]][$tipo][] = $dados;
			
		}
		
		return $visitas;
		
	}
	
	var $coresZonas = array(
		"A" => "70ad47",
		"B" => "4472c4",
		"C" => "ffc000",
		"D" => "ff0000",
		"E" => "ff0066",
		"F" => "c65911",
		"G" => "7030a0",
		"X" => "002060"
	);
	
	function geraListagemVisitasTecnicaMigracao($status, $abrangencia = "4", $tipo_visita) {
		
		// 1 = Dias Anteriores
		// 2 = Hoje
		// 3 = Próximos Dias
		// 4 = Todos
		
		if ($status == 1) {
			$parametro = "IS NULL";
		} elseif ($status == 2) {
			if ($abrangencia == 1) {
				$parametro = "< CURDATE()";
			} elseif ($abrangencia == 2) {
				$parametro = "= CURDATE()";
			} elseif ($abrangencia == 3) {
				$parametro = "> CURDATE()";
			} else {
				$parametro = "IS NOT NULL";
			}
		}
		
		$periodos = $this->buscaPeriodos();
		
		// Busca Visitas
		$query = "     
			SELECT
				tab_visitas.vis_id AS id,
				tab_visitas.cor_id AS contrato_id,
				DATE_FORMAT(tab_visitas.vis_data, '%d/%m/%Y') AS data,
				DATEDIFF(NOW(), tab_visitas.vis_data_geracao) AS dias,
				tab_visitas.vip_id AS periodo,
				tab_visitas.cli_id AS cliente_id,
				tab_visitas.con_id AS condominio_id,
				tab_visitas.cha_id AS chamado_id,
				tab_clientes.cli_nome AS cliente,
				tab_condominios.con_nome AS condominio,
				tab_condominios.cit_id AS condominio_tipo_instalacao_id,
				tab_condominios_blocos.cob_nome AS bloco,
				SUBSTRING(tab_clientes.cli_complemento, 1, 1) AS complemento_tipo,
				SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento,
				tab_logradouros.log_nome AS logradouro,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_ceps.cep_zona AS zona,
				IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, tab_condominios.con_numero, tab_clientes.cli_numero)) AS numero,
				tab_visitas.vis_agendamentos AS agendamentos,
				tab_visitas.via_id AS status_id
			FROM
				tab_visitas
				LEFT OUTER JOIN tab_clientes ON (tab_visitas.cli_id = tab_clientes.cli_id)
				LEFT OUTER JOIN tab_condominios ON (IF(tab_visitas.con_id IS NOT NULL, tab_visitas.con_id, tab_clientes.con_id) = tab_condominios.con_id)
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				INNER JOIN tab_ceps ON (IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)) = tab_ceps.cep_id)
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
			WHERE
				tab_visitas.vis_data $parametro AND
				tab_visitas.via_id = $status AND
				tab_visitas.vit_id = $tipo_visita".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
			GROUP BY
				tab_visitas.vis_id
			ORDER BY
				tab_ceps.cep_zona,
				dias DESC";
				
		$query = $this->bancodedados->query($query);
		
		$casas = 0;
		$condominios = 0;
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($dados["condominio_id"]) {
				$condominios++;
				$tipo = "condominial";
				$dados["tipo"] = "Condomínio";
			} elseif ($dados["condominio"]) {
				$condominios++;
				$tipo = "condominial";
				$dados["tipo"] = "Cliente Condominial";
			} else {
				$casas++;
				$tipo = "residencial";
				$dados["tipo"] = "Cliente Residencial";
			}
			
			$visita_atual = array();
			
			if ($dados["cliente_id"] && $dados["condominio"]) {
				$visita_atual["endereco"] = $dados["condominio"];
				if ($dados["complemento"]) {
					$visita_atual["endereco"] .= ", " . $dados["complemento"];
				}
				if ($dados["bloco"] && $dados["bloco"] != "Único") {
					$visita_atual["endereco"] .= ($dados["complemento"] ? " / " : ", ") . $dados["bloco"];
				}
			} else {
				$visita_atual["endereco"][] = $dados["logradouro"];
				if ($dados["numero"])
					$visita_atual["endereco"][] = $dados["numero"];
				$visita_atual["endereco"][] = $dados["bairro"];
				$visita_atual["endereco"][] = $dados["cidade"];
				if ($dados["complemento"]) {
					$visita_atual["endereco"][] = $this->buscaComplementoTipo($dados["complemento_tipo"]) . $dados["complemento"];
				}
				$visita_atual["endereco"] = implode(", ", $visita_atual["endereco"]);
			}
			$visita_atual["descricao"][] = "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr class=\"txtAzulEscuro11\"><td width='15'>" . ($dados["condominio"] && $dados["condominio_tipo_instalacao_id"] == 2 ? "<img src='imagens/condominio.png' width='13' height='13' border='0'>" : "<img src='imagens/casa.png' width='13' height='13' border='0'>") . "</td><td id=\"visitas_tipo_{$dados["id"]}\" style=\"background-color:rgba(50,50,50,0.3);color:#ffffff;font-weight:bold\">&nbsp;» " . $dados["tipo"] . "</td>" . ($this->permissoes["contratos"]["ver"] ? "<td style=\"width:3px\"></td><td id=\"visitas_contrato_{$dados["id"]}\" style=\"text-align:center;width:60px;background-color:rgba(50,50,50,0.3);color:#ffffff;font-weight:bold\"><a  href=\"javascript:".($tipo_visita == 2 ? "verChamado({$dados["chamado_id"]})\" style=\"color:#ffffff\">Chamado</a></td>" : "verContrato({$dados["contrato_id"]})\" style=\"color:#ffffff\">Contrato</a></td>") : "") . ($this->permissoes["visitas"]["ver"] ? "<td style=\"width:3px\"></td><td id=\"visitas_visita_{$dados["id"]}\" style=\"text-align:center;width:45px;background-color:rgba(50,50,50,0.3);color:#ffffff;font-weight:bold\"><a href=\"javascript:verVisita({$dados["id"]})\" style=\"color:#ffffff\">Visita</a></td>" : "") . "</tr></table>";
			if ($dados["cliente_id"]) {
				$visita_atual["descricao"][] = "<b>Cliente:</b> [<a class=\"txtAzulEscuro11\" href=\"./?m=clientes&a=ver_dc&i={$dados["cliente_id"]}\">{$dados["cliente_id"]}</a>] {$dados["cliente"]}<br>";
			} else {
				$visita_atual["descricao"][] = "<b>Condominio:</b> [<a class=\"txtAzulEscuro11\" href=\"./?m=condominios&a=ver_dc&i={$dados["condominio_id"]}\">{$dados["condominio_id"]}</a>] {$dados["condominio"]}<br>";
			}
			$visita_atual["descricao"][] = "<b>Endereço:</b> {$visita_atual["endereco"]}";
			$visita_atual["descricao"] = implode($visita_atual["descricao"]);
			if ($dados["cliente_id"]) {
				$dados["telefone"] = $this->buscaClienteTelefones($dados["cliente_id"]);
			} elseif ($dados["condominio_id"]) {
				$dados["telefone"] = $this->buscaCondominioTelefones($dados["condominio_id"]);
			}
			$visita_atual["telefone"] = "<div nowrap>{$dados["telefone"]}</div>";
			if ($status == 2)
				$visita_atual["data_periodo"] = $dados["data"] . '<br>(' . $periodos[$dados["periodo"]] . ')';
			if ($this->permissoes["visitas"]["alocar"]) {
				$tipo = $tipo_visita == 3 ? "" : ",'$tipo'";
				$visita_atual["link"] = "javascript:definirVisita({$dados["id"]} $tipo)";
			}
			if ($this->permissoes["visitas"]["ver"]) {
				$visita_atual["link_dc"] = "javascript:resetaGatilho(); verVisita({$dados["id"]})";
			}
			$visita_atual["link_dc"] = "javascript:resetaGatilho(); verVisita({$dados["id"]})";
			$visita_atual["ultimo_comentario"] .= '<div style="text-align:left;cursor:pointer" onMouseOver="hintShow(\'' . addslashes($this->buscaVisitaComentarios($dados["id"])) . '\', 300)" onMouseOut="hintHide()" class="txtAzul11">' . $this->buscaVisitaComentariosUltimo($dados["id"]) . '</div>';
			$visita_atual["id"] = "visitas_{$dados["id"]}";
			$visita_atual["vertical_align"]["descricao"] = "top";
			$visita_atual["vertical_align"]["ultimo_comentario"] = "top";
			if ($dados["instalado"]) {
				$visita_atual["cor_linha"] = "#98FB98";
			}
			
			$visita_atual["zona"] = "<div style=\"text-align:center;font-size:18pt;color:#ffffff\">{$dados["zona"]}<span style=\"font-size:10pt\">{$dados["dias"]}</span></div>";
			$visita_atual["zona_cor_fundo"] = $this->coresZonas[$dados["zona"]];
			
			$visitas[] = $visita_atual;
			
		}
		
		$cabecalho["zona"] = "Zona";
		$cabecalho["descricao"] = "Descrição";
		if ($status == 2) $cabecalho["data_periodo"] = "Data";
		$cabecalho["telefone"] = "Telefone";
		$cabecalho["ultimo_comentario"] = "Último Comentário";
		
		if ($status == 2) {
			$larguras = array("20", "40%", "8%", "14%", "38%");
		} else {
			$larguras = array("20", "48%", "14%", "38%");
		}
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $visitas, 0, false, $larguras);
		
		return array("casas" => $casas, "condominios" => $condominios, "listagem" => $listagem->conteudo);
		
	}
	
	function buscaAgendaInstalacoesVisitas($mes, $ano) {
		
		$query = "
			SELECT
				tab_visitas.vis_id AS id,
				DATE_FORMAT(tab_visitas.vis_data, '%Y%m%d') AS data,
				tab_visitas.vip_id AS periodo,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente,
				CONCAT(tab_condominios.con_nome, ' (', tab_bairros.bai_nome, ')') AS condominio,
				tab_condominios_blocos.cob_nome AS bloco,
				tab_condominios_blocos.cbs_id AS status_bloco,
				SUBSTRING(tab_clientes.cli_complemento, 1, 1) AS complemento_tipo,
				SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento,
				tab_logradouros.log_nome AS logradouro,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_clientes.cli_numero AS numero,
				tab_visitas.vis_agendamentos AS agendamentos,
				tab_visitas.cor_id AS contrato_id,
				tab_visitas.via_id AS status_id,
				COUNT(DISTINCT tab_clientes_pontos.clp_id) + COUNT(DISTINCT tab_clientes_autenticacoes.cau_id) AS instalado,
				IF(COUNT(DISTINCT tab_clientes_pontos.clp_id) + COUNT(DISTINCT tab_clientes_autenticacoes.cau_id) > 0, 0, tab_visitas.vis_agendamentos) AS ordem,
				tab_contratos.pls_id AS servico_id,
				IF(tab_visitas.cli_id IS NULL, 1 , 0 ) AS reservado
			FROM
				tab_visitas
				LEFT OUTER JOIN tab_clientes ON (tab_visitas.cli_id = tab_clientes.cli_id)
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios_blocos_status ON (tab_condominios_blocos.cbs_id = tab_condominios_blocos_status.cbs_id)
				LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
				LEFT OUTER JOIN tab_ceps ON (IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id) = tab_ceps.cep_id)
				LEFT OUTER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				LEFT OUTER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				LEFT OUTER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
				LEFT OUTER JOIN rel_ass_cor_estacontido ON (tab_visitas.cor_id = rel_ass_cor_estacontido.cor_id)
				LEFT OUTER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id AND tab_assinaturas.ast_id IN (1))
				LEFT OUTER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
				LEFT OUTER JOIN tab_clientes_autenticacoes ON (tab_assinaturas.ass_id = tab_clientes_autenticacoes.ass_id)
				LEFT OUTER JOIN tab_contratos ON (tab_visitas.cor_id = tab_contratos.cor_id)
			WHERE
				(tab_clientes.con_id IS NULL OR (tab_condominios.cos_id = 2 AND tab_condominios_blocos_status.cbs_id = 1)) AND
				tab_visitas.vis_data BETWEEN '" . date("Y-m-d", strtotime("$ano-$mes-01")) . "' AND '" . date("Y-m-t", strtotime("$ano-$mes-01")) . "' AND
				tab_visitas.via_id IN (2, 3) AND
				tab_visitas.vit_id = 1".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
			GROUP BY
				tab_visitas.vis_id
			ORDER BY
				tab_visitas.via_id DESC,
				instalado DESC,
				ordem DESC,
				tab_clientes.con_id";
			
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["tipo"] = $this->buscaContratoTipo($dados["contrato_id"]);
			if ($dados["condominio"]) {
				$dados["local"] = $dados["condominio"];
			} else {
				$dados["local"][] = $dados["logradouro"];
				if ($dados["numero"])
					$dados["local"][] = $dados["numero"];
				$dados["local"][] = $dados["bairro"];
				$dados["local"][] = $dados["cidade"];
				if ($dados["complemento"]) {
					$dados["local"][] = $this->buscaComplementoTipo($dados["complemento_tipo"]) . $dados["complemento"];
				}
				$dados["local"] = implode(", ", $dados["local"]);
			}
			if ($dados["instalado"]) {
				$dados["bloqueado"] = $this->contratoBloqueado($dados["cliente_id"]);
				if ($dados["bloqueado"]) {
					$dados["suspensao"] = $this->suspensaoTemporaria($dados["bloqueado"]);
				}
			}
			if ($dados["cliente_id"]) {
				$dados["telefone"] = $this->buscaClienteTelefones($dados["cliente_id"], " / ", false);
			}
			$dados["ultimo_comentario"] = preg_replace("/[\r\n\t ]+/", " ", $this->buscaVisitaComentariosUltimo($dados["id"]));
			
			$visitas[$dados["data"]][$dados["periodo"]][] = $dados;
		}
		
		return $visitas;
		
	}
	
	function buscaCondominioTelefones($condominio_id, $separador = "<br>", $discador = true) {
		
		global $login;
		
		$query = "
			SELECT
				coc_tel_residencial AS tel_residencial,
				coc_tel_comercial AS tel_comercial,
				coc_tel_celular AS tel_celular
			FROM
				tab_condominios_contatos
			WHERE 
				con_id = $condominio_id";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($discador && ($login->usuario["ramal"] || $login->usuario["agente_id"])) {
				if ($dados["tel_residencial"]) {
					$lista[] = "<a href=\"javascript:discar('{$dados["tel_residencial"]}')\" title=\"Discar para este número\" class=\"linkDiscar\">{$dados["tel_residencial"]}</a>";
				}
				if ($dados["tel_celular"]) {
					$lista[] = "<a href=\"javascript:discar('{$dados["tel_celular"]}')\" title=\"Discar para este número\" class=\"linkDiscar\">{$dados["tel_celular"]}</a>";
				}
				if ($dados["tel_comercial"]) {
					$lista[] = "<a href=\"javascript:discar('{$dados["tel_comercial"]}')\" title=\"Discar para este número\" class=\"linkDiscar\">{$dados["tel_comercial"]}</a>";
				}
			} else {
				if ($dados["tel_residencial"]) {
					$lista[] = $dados["tel_residencial"];
				}
				if ($dados["tel_celular"]) {
					$lista[] = $dados["tel_celular"];
				}
				if ($dados["tel_comercial"]) {
					$lista[] = $dados["tel_comercial"];
				}
			}
			
		}
		
		return @implode($separador, $lista);
		
	}
	
	function contratoBloqueado($cliente_id) {
		
		$query = "
			SELECT
				saj_id AS id
			FROM
				tab_servicos_ajustes
			WHERE
				cli_id = $cliente_id AND
				sac_id = 3 AND
				sas_id = 5";
				
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);
				
	}
	
	function suspensaoTemporaria($ajuste_id) {
		
		$query = "
			SELECT
				saj_id AS id
			FROM
				tab_servicos_ajustes
			WHERE
				saj_pai = $ajuste_id AND
				sac_id = 2 AND
				sas_id = 5";
		
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function buscaClienteTelefones($cliente_id, $separador = "<br>", $discador = true) {
		
		global $login;
		
		$query = "
			SELECT
				ccl_ramal AS ramal,
				CONCAT('(', ccl_ddd, ') ', ccl_numero) AS telefone,
				ccl_ddd AS ddd,
				ccl_numero AS numero
			FROM
				tab_clientes_contatos_telefones
			WHERE
				cli_id = $cliente_id";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($dados["ddd"] == 48) {
				$numero = preg_replace("/[^0-9]/", "", "0" . $dados["numero"]);
			} else {
				$numero = preg_replace("/[^0-9]/", "", "00" . $dados["ddd"] . $dados["numero"]);
			}
			if ($dados["ramal"]) {
				$dados["telefone"] .= " (Ramal {$dados["ramal"]})";
			}
			if ($discador && ($login->usuario["ramal"] || $login->usuario["agente_id"])) {
				$lista[] = "<a href=\"javascript:discar('$numero')\" title=\"Discar para este número\" class=\"linkDiscar\" style=\"white-space:nowrap\">{$dados["telefone"]}</a>";
			} else {
				$lista[] = $dados["telefone"];
			}
		}
		
		return @implode($separador, $lista);
		
	}
	
	//TODO
	
	function geraListagemVisitas($status, $abrangencia = "4", $com_contato = null) {
		
		// 1 = Dias Anteriores
		// 2 = Hoje
		// 3 = Próximos Dias
		// 4 = Todos
		
		if ($status == 1) {
			$parametro = "IS NULL";
		} elseif ($status == 2) {
			if ($abrangencia == 1) {
				$parametro = "< CURDATE()";
			} elseif ($abrangencia == 2) {
				$parametro = "= CURDATE()";
			} elseif ($abrangencia == 3) {
				$parametro = "> CURDATE()";
			} else {
				$parametro = "IS NOT NULL";
			}
		}
		
		$periodos = $this->buscaPeriodos();
		
		// Busca Visitas
		$query = "
			SELECT
				tab_visitas.vis_id AS id,
				CASE 
					WHEN DATE_ADD(tab_visitas.vis_data_geracao, INTERVAL 1 DAY) = CURDATE() THEN 'amarelo'
					WHEN DATE_ADD(tab_visitas.vis_data_geracao, INTERVAL 2 DAY) <= CURDATE() THEN 'vermelho'
					ELSE 'normal'
					END AS cor,
				CONCAT(tab_condominios.con_nome, ' (', tab_bairros.bai_nome, ')') AS condominio,
				tab_condominios.cit_id AS condominio_tipo_instalacao_id,
				tab_condominios_blocos.cob_nome AS bloco,
				tab_condominios_blocos_status.cbs_id AS status_bloco,
				SUBSTRING(tab_clientes.cli_complemento, 1, 1) AS complemento_tipo,
				SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento,
				tab_logradouros.log_nome AS logradouro,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_ceps.cep_zona AS zona,
				tab_clientes.cli_numero AS numero,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente,
				DATE_FORMAT(tab_visitas.vis_data, '%d/%m/%Y') AS data,
				DATEDIFF(NOW(), tab_visitas.vis_data_geracao) AS dias,
				tab_visitas.vip_id AS periodo,
				tab_condominios.con_id AS condominio_id,
				tab_visitas.vis_agendamentos AS agendamentos,
				tab_visitas.cor_id AS contrato_id,
				COUNT(DISTINCT tab_clientes_pontos.clp_id) + COUNT(DISTINCT tab_clientes_autenticacoes.cau_id) AS instalado,
				(SELECT usu_nome FROM tab_usuarios WHERE usu_id = (SELECT usu_id FROM tab_vendas WHERE tab_vendas.cor_id = tab_visitas.cor_id LIMIT 0, 1)) AS vendedor
			FROM
				tab_visitas
				INNER JOIN tab_clientes ON (tab_visitas.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_contratos ON (tab_visitas.cor_id = tab_contratos.cor_id)
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios_blocos_status ON (tab_condominios_blocos.cbs_id = tab_condominios_blocos_status.cbs_id)
				LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
				INNER JOIN tab_ceps ON (IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id) = tab_ceps.cep_id)
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				LEFT OUTER JOIN rel_ass_cor_estacontido ON (tab_visitas.cor_id = rel_ass_cor_estacontido.cor_id)
				LEFT OUTER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id AND tab_assinaturas.ast_id IN (1))
				LEFT OUTER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
				LEFT OUTER JOIN tab_clientes_autenticacoes ON (tab_assinaturas.ass_id = tab_clientes_autenticacoes.ass_id)   
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)" . (is_null($com_contato) ? "" : "
				LEFT OUTER JOIN tab_visitas_comentarios ON (tab_visitas.vis_id = tab_visitas_comentarios.vis_id)") . "
			WHERE
				(tab_clientes.con_id IS NULL OR (tab_condominios.cos_id = 2 AND tab_condominios_blocos_status.cbs_id = 1)) AND
				tab_visitas.vis_data $parametro AND
				tab_visitas.via_id = $status AND
				tab_visitas.vit_id = 1".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
			GROUP BY
				id" . (is_null($com_contato) ? "" : "
			HAVING
				SUM(IF(tab_visitas_comentarios.vcc_id < 16, 1, 0)) " . ($com_contato ? "> 0" : "= 0")) . "
			ORDER BY
				tab_ceps.cep_zona,
				dias DESC";
			
		$query = $this->bancodedados->query($query);
		
		$casas = 0;
		$condominios = 0;
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$visita_atual = array();
			
			if ($dados["condominio"]) {
				$condominios++;
				$visita_atual["endereco"] = $dados["condominio"];
				if ($dados["complemento"]) {
					$visita_atual["endereco"] .= ", " . $dados["complemento"];
				}
				if ($dados["bloco"] && $dados["bloco"] != "Único") {
					$visita_atual["endereco"] .= ($dados["complemento"] ? " / " : ", ") . $dados["bloco"];
				}
			} else {
				$casas++;
				$visita_atual["endereco"][] = $dados["logradouro"];
				if ($dados["numero"])
					$visita_atual["endereco"][] = $dados["numero"];
				$visita_atual["endereco"][] = $dados["bairro"];
				$visita_atual["endereco"][] = $dados["cidade"];
				if ($dados["complemento"]) {
					$visita_atual["endereco"][] = $this->buscaComplementoTipo($dados["complemento_tipo"]) . $dados["complemento"];
				}
				$visita_atual["endereco"] = implode(", ", $visita_atual["endereco"]);
			}
			$visita_atual["descricao"][] = "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr class=\"txtAzulEscuro11\"><td width='15'>" . ($dados["condominio_id"] && $dados["condominio_tipo_instalacao_id"] == 2 ? "<img src='imagens/condominio.png' width='13' height='13' border='0'>" : "<img src='imagens/casa.png' width='13' height='13' border='0'>") . "</td><td id=\"visitas_tipo_{$dados["id"]}\" style=\"background-color:rgba(50,50,50,0.3);color:#ffffff;font-weight:bold;white-space:nowrap;\">&nbsp;» " . $this->buscaContratoTipo($dados["contrato_id"]) . "</td>" . ($this->permissoes["contratos"]["ver"] ? "<td style=\"width:3px\"></td><td id=\"visitas_contrato_{$dados["id"]}\" style=\"text-align:center;width:60px;background-color:rgba(50,50,50,0.3);color:#ffffff;font-weight:bold\"><a href=\"javascript:verContrato({$dados["contrato_id"]})\" style=\"color:#ffffff\">Contrato</a></td>" : "") . ($this->permissoes["visitas"]["ver"] ? "<td style=\"width:3px\"></td><td id=\"visitas_visita_{$dados["id"]}\" style=\"text-align:center;width:45px;background-color:rgba(50,50,50,0.3);color:#ffffff;font-weight:bold\"><a href=\"javascript:verVisita({$dados["id"]})\" style=\"color:#ffffff\">Visita</a></td>" : "") . "</tr></table>";
			$visita_atual["descricao"][] = "<b>Cliente:</b> [<a class=\"txtAzulEscuro11\" href=\"./?m=clientes&a=ver_dc&i={$dados["cliente_id"]}\">{$dados["cliente_id"]}</a>] {$dados["cliente"]}<br>";
			$visita_atual["descricao"][] = "<b>Endereço:</b> {$visita_atual["endereco"]}";
			$visita_atual["descricao"] = implode($visita_atual["descricao"]);
			if ($dados["cliente_id"]) {
				$visita_atual["telefone"] = "<div nowrap>" . $this->buscaClienteTelefones($dados["cliente_id"]) . "</div>";
			}
			if ($status == 2)
				$visita_atual["data_periodo"] = $dados["data"] . '<br>(' . $periodos[$dados["periodo"]] . ')';
			if ($this->permissoes["visitas"]["alocar"]) {
				$visita_atual["link"] = "javascript:definirVisita({$dados["id"]})";
			}
			if ($this->permissoes["visitas"]["ver"]) {
				$visita_atual["link_dc"] = "javascript:resetaGatilho(); verVisita({$dados["id"]})";
			}
			$visita_atual["link_dc"] = "javascript:resetaGatilho(); verVisita({$dados["id"]})";
			$visita_atual["ultimo_comentario"] = "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr class=\"txtAzulEscuro11\"><td id=\"visitas_vendedor_{$dados["id"]}\" style=\"background-color:rgba(50,50,50,0.3);color:#ffffff;white-space:nowrap;\"><b>&nbsp;Vendedor:</b> {$dados["vendedor"]}</td></tr></table>";
			$visita_atual["ultimo_comentario"] .= '<div style="text-align:left;cursor:pointer" onMouseOver="hintShow(\'' . addslashes($this->buscaVisitaComentarios($dados["id"])) . '\', 300)" onMouseOut="hintHide()" class="txtAzul11">' . $this->buscaVisitaComentariosUltimo($dados["id"]) . '</div>';
			$visita_atual["id"] = "visitas_{$dados["id"]}";
			$visita_atual["vertical_align"]["descricao"] = "top";
			$visita_atual["vertical_align"]["ultimo_comentario"] = "top";
			if ($dados["instalado"]) {
				$visita_atual["cor_linha"] = "#98FB98";
			} elseif ($dados["cor"] == "amarelo" && $abrangencia == 4) {
				$visita_atual["cor_linha"] = "#FFF25F";
			} elseif ($dados["cor"] == "vermelho" && $abrangencia == 4) {
				$visita_atual["cor_linha"] = "#FFC9C9";
			}
			
			$visita_atual["zona"] = "<div style=\"text-align:center;font-size:18pt;color:#ffffff\">{$dados["zona"]}<span style=\"font-size:10pt\">{$dados["dias"]}</span></div>";
			$visita_atual["zona_cor_fundo"] = $this->coresZonas[$dados["zona"]];
			
			$visitas[] = $visita_atual;
			
		}
		
		$cabecalho["zona"] = "Zona";
		$cabecalho["descricao"] = "Descrição";
		if ($status == 2) $cabecalho["data_periodo"] = "Data";
		$cabecalho["telefone"] = "Telefone";
		$cabecalho["ultimo_comentario"] = "Último Comentário";
		
		if ($status == 2) {
			$larguras = array("20", "40%", "8%", "14%", "38%");
		} else {
			$larguras = array("20", "48%", "14%", "38%");
		}
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $visitas, 0, false, $larguras, null, array("center", "left"));
		
		return array("casas" => $casas, "condominios" => $condominios, "listagem" => $listagem->conteudo);
		
	}
	
	function buscaContratoTipo($contrato_id) {
		if ($contrato_id) {
			$query = "
	      SELECT
	        crt_id
	      FROM
	        tab_contratos
	      WHERE
	        cor_id = $contrato_id";
			$query = $this->bancodedados->query($query);
			$tipo_id = $this->bancodedados->resultado($query);
			if ($tipo_id == "11")
				return "Ponto Principal";
			if ($tipo_id == "12")
				return "Ponto Adicional";
			if ($tipo_id == "14")
				return "Mudança de Endereço";
			if ($tipo_id == "13")
				return "Migração";
		}
		return "Suporte Técnico";
	}
	
	function buscaComplementoTipo($complemente_tipo) {
		$tipos["a"] = "Ap. ";
		$tipos["l"] = "Lj. ";
		$tipos["s"] = "Sl. ";
		$tipos["c"] = "Casa ";
		$tipos["o"] = "";
		return $tipos[$complemente_tipo];
	}
	
	function buscaVisitaComentariosUltimo($id) {
		$query = "
      SELECT
        DATE_FORMAT(tab_visitas_comentarios.vic_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_visitas_comentarios_categorias.vcc_id AS categoria_id,
        tab_visitas_comentarios_categorias.vcc_nome AS categoria,
        tab_visitas_comentarios.vic_conteudo AS conteudo,
        IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel
      FROM
        tab_visitas_comentarios
        LEFT OUTER JOIN tab_usuarios ON (tab_visitas_comentarios.usu_id = tab_usuarios.usu_id)
        LEFT OUTER JOIN tab_visitas_comentarios_categorias ON (tab_visitas_comentarios.vcc_id = tab_visitas_comentarios_categorias.vcc_id)
      WHERE
        tab_visitas_comentarios.vis_id = $id
      ORDER BY
        tab_visitas_comentarios.vic_datahora DESC,
        tab_visitas_comentarios.vic_id DESC
      LIMIT 0, 1";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			$ultimo_comentario[] = "<b>" . $dados["responsavel"] . "</b> em <b>" . $dados["data_hora"] . "</b>";
			if ($dados["categoria"] && !$dados["conteudo"])
				$ultimo_comentario[] .= "<b>Categoria:</b> " . $dados["categoria"];
			if ($dados["conteudo"])
				$ultimo_comentario[] = substr($dados["conteudo"], 0, 3) == "<b>" ? $dados["conteudo"] : "<b>Conteúdo:</b> " . $dados["conteudo"];
			$ultimo_comentario = @implode('<br>', $ultimo_comentario);
		} else {
			$ultimo_comentario = "Nenhum Comentário Disponível";
		}
		return preg_replace("/[\r\n\t ]+/", " ", str_replace("\"", "&quot;", $ultimo_comentario));
	}
	
	function buscaVisitaComentarios($id) {
		$query = "
      SELECT
        DATE_FORMAT(tab_visitas_comentarios.vic_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_visitas_comentarios_categorias.vcc_id AS categoria_id,
        tab_visitas_comentarios_categorias.vcc_nome AS categoria,
        tab_visitas_comentarios.vic_conteudo AS conteudo,
        IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel
      FROM
        tab_visitas_comentarios
        LEFT OUTER JOIN tab_usuarios ON (tab_visitas_comentarios.usu_id = tab_usuarios.usu_id)
        LEFT OUTER JOIN tab_visitas_comentarios_categorias ON (tab_visitas_comentarios.vcc_id = tab_visitas_comentarios_categorias.vcc_id)
      WHERE
        tab_visitas_comentarios.vis_id = $id
      ORDER BY
        tab_visitas_comentarios.vic_datahora ASC,
        tab_visitas_comentarios.vic_id ASC";
		$query = $this->bancodedados->query($query);
		$comentarios = array();
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["detalhamento"][] = "<b>{$dados["responsavel"]}</b> em <b>{$dados["data_hora"]}</b>";
			//if ($dados["categoria"]) $dados["detalhamento"][] .= "<b>Categoria:</b> ".$dados["categoria"];
			if ($dados["conteudo"])
				$dados["detalhamento"][] = substr($dados["conteudo"], 0, 3) == "<b>" ? $dados["conteudo"] : "<b>Conteúdo:</b> {$dados["conteudo"]}";
			$dados["detalhamento"] = @implode('<br>', $dados["detalhamento"]);
			$comentarios[] = str_replace("\"", "&quot;", $dados["detalhamento"]);
		}
		return preg_replace("/[\r\n\t ]+/", " ", implode('<hr>', $comentarios));
	}
	
	function ordena_multiarray($array, $campos) {
		if (is_array($array) && sizeof($array)) {
			$tamanho_chave = 0;
			reset($array);
			while (($array_ordenacao_atual = current($array)) != false) {
				for ($i = 0; $i < sizeof($campos); $i++) {
					$chave[] = $array_ordenacao_atual[$campos[$i]];
				}
				$chave = @implode(",", $chave);
				$tamanho_chave = strlen($chave) > $tamanho_chave ? strlen($chave) : $tamanho_chave;
				unset($chave);
				next($array);
			}
			reset($array);
			$array_ordenacao = array();
			while (($array_ordenacao_atual = current($array)) != false) {
				for ($i = 0; $i < sizeof($campos); $i++) {
					$chave[] = $array_ordenacao_atual[$campos[$i]];
				}
				$chave = @implode(",", $chave);
				if (!$array_ordenacao[$chave]) {
					$array_ordenacao[sprintf("% -{$tamanho_chave}s", $chave) . "_" . key($array)] = key($array);
				}
				unset($chave);
				next($array);
			}
			ksort($array_ordenacao);
			while (is_int($array_id = current($array_ordenacao))) {
				$array_final[] = $array[$array_id];
				next($array_ordenacao);
			}
			return $array_final;
		}
	}

}

?>
