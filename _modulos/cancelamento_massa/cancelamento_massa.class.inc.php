<?php

set_time_limit(0);
ignore_user_abort(1);

require_once("_global/modulo.class.inc.php");

class cancelamento_massa extends modulo {

	/**
	 * Database object
	 */
	var $bancodedados;
	/**
	 * Permissions object
	 */
	var $permissoes;
	/**
	 * Action of the module
	 */
	var $acao;
	/**
	 * Content of the module
	 */
	var $conteudo;
	/**
	 * Data of the module
	 */
	var $dados_modulo;
	/**
	 * Data of the action
	 */
	var $dados_acao;
	/**
	 * Contracts object
	 */
	var $contratos;

	/**
	 * Constructor
	 * @param object $bancodedados
	 * @param object $permissoes
	 * @param bool $executar
	 */
	function __construct($bancodedados, $permissoes, $executar = true) {

		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;

		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "gerar_cancelamento");
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
	}

	/**
	 * Searches for the origins (mesmo padrão do módulo clientes)
	 * @param int|null $origem_id
	 * @return array
	 */
	function buscaOrigens($origem_id = null) {

		$origens = array();
		$origem_ids = array();

		if ($_SESSION["origem"]) {
			$origem_ids[] = $_SESSION["origem"];
		}

		if ($origem_id && $origem_id != $_SESSION["origem"]) {
			$origem_ids[] = $origem_id;
		}

		$origem_ids = implode(", ", $origem_ids);

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_origens.clo_id AS id,
				tab_clientes_origens.clo_nome AS nome
			FROM
				tab_clientes_origens
			WHERE" . ($origem_ids ? "
				clo_id IN ($origem_ids)" : "
				1") . "
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$origens[$dados["id"]] = $dados["nome"];
		}

		return $origens;
	}

	/**
	 * Verifica se o usuário é administrador
	 * @param int|null $id
	 * @return bool
	 */
	function ehAdmin($id = null) {

		$user = $id ? $id : $_SESSION["usuario"];
		$query = "SELECT usu_administrador FROM tab_usuarios WHERE usu_id = $user";
		$query = $this->bancodedados->query($query);
		$ehAdmin = $this->bancodedados->resultadoArray($query);

		return $ehAdmin["usu_administrador"] == 0 ? false : true;
	}

	/**
	 * Generates the cancelamento form
	 */
	function gerar_cancelamento() {

		$origens = isset($_POST["origem"]) && is_array($_POST["origem"]) ? $_POST["origem"] : array();
		$data_cancelamento = isset($_POST["data_cancelamento"]) ? trim($_POST["data_cancelamento"]) : date("Y-m-d");
		$tipo_lista = isset($_POST["tipo_lista"]) ? $_POST["tipo_lista"] : "id";
		$lista_clientes = isset($_POST["lista_clientes"]) ? trim($_POST["lista_clientes"]) : "";
		$resultado = "";
		$ehAdmin = $this->ehAdmin();

		if ($_POST) {
			$erros = $this->validaFormulario($origens, $data_cancelamento, $tipo_lista, $lista_clientes, $ehAdmin);

			if ($erros) {
				$resultado = "<pre style=\"color:#FF0000; text-align:left; margin:10px;\">" . htmlspecialchars(implode("\n", $erros)) . "</pre>";
			} else {
				$data_cancelamento_formatada = $this->formataDataCancelamento($data_cancelamento);
				$clientes = $this->montaArrayClientes($origens, $tipo_lista, $lista_clientes, $data_cancelamento_formatada);

				if (!$clientes) {
					$resultado = "<pre style=\"color:#FF0000; text-align:left; margin:10px;\">Nenhum cliente encontrado para cancelamento.</pre>";
				} else {
					$resultado = $this->executaCancelamento($clientes, $origens);
				}
			}
		}

		$tipos = array(
			"id" => "IDs dos clientes",
			"cpf_cnpj" => "CPF/CNPJ dos clientes"
		);

		$botoes = array();
		$botoes[] = '<input type="submit" name="executar" class="buttonAzulEscuroBranco11" value="Executar Cancelamento">';

		$this->conteudo .= $this->carregaArquivo("cancelamento_massa.gerar_cancelamento.inc.php");
		$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=gerar_cancelamento", $this->conteudo);
		$this->conteudo = str_replace("[origens]", $this->geraCheckBoxesLista("origem", $this->buscaOrigens()), $this->conteudo);
		$this->conteudo = str_replace("[data_cancelamento]", htmlspecialchars($data_cancelamento), $this->conteudo);
		$this->conteudo = str_replace("[data_max]", $ehAdmin ? "" : 'max="' . date("Y-m-d") . '"', $this->conteudo);
		$this->conteudo = str_replace("[tipo_lista]", $this->geraRadioButtons("tipo_lista", $tipos, $tipo_lista, null, true, 2), $this->conteudo);
		$this->conteudo = str_replace("[lista_clientes]", htmlspecialchars($lista_clientes), $this->conteudo);
		$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		$this->conteudo = str_replace("[resultado]", $resultado, $this->conteudo);
	}

	/**
	 * Generates the list of checkboxes
	 * @param string $nome
	 * @param array $itens
	 * @return string
	 */
	function geraCheckBoxesLista($nome, $itens) {
		
		if (! $itens) return "Nenhum registro disponível";
		
		$i = 0;
		$check_boxes = array ();
		
		foreach ($itens as $id => $descricao) {
			$check_boxes[] = "<div style='cursor:pointer;'><label style='display:block;cursor:pointer;font-weight:normal;line-height:18px;' for=\"{$nome}_{$i}\"><input type=\"checkbox\" name=\"{$nome}[]\" value=\"$id\" id=\"{$nome}_{$i}\"> $descricao </label></div>";
			
			$i++;
		}
		
		return implode("", $check_boxes);
		
	}

	/**
	 * Validates the form
	 * @param array $origens
	 * @param string $data_cancelamento
	 * @param string $tipo_lista
	 * @param string $lista_clientes
	 * @param bool $ehAdmin
	 * @return array
	 */
	function validaFormulario($origens, $data_cancelamento, $tipo_lista, $lista_clientes, $ehAdmin = false) {

		$erros = array();

		if (!is_array($origens) || !count($origens)) {
			$erros[] = "Selecione ao menos uma origem.";
		} else {
			foreach ($origens as $origem) {
				if (!preg_match("/^[0-9]+$/", $origem)) {
					$erros[] = "Origem inválida informada.";
					break;
				}
			}
		}

		if ($data_cancelamento === "") {
			$erros[] = "Informe a data de cancelamento.";
		} elseif (!$this->validaDataIso($data_cancelamento)) {
			$erros[] = "Data de cancelamento inválida.";
		} elseif (!$ehAdmin && $this->dataMaiorQueHoje($data_cancelamento)) {
			$erros[] = "A data de cancelamento não pode ser maior que a data atual.";
		}

		if ($lista_clientes === "") {
			$erros[] = "Informe a lista de clientes.";
		} else {
			$itens = $this->parseLista($lista_clientes);

			if (!count($itens)) {
				$erros[] = "Informe a lista de clientes.";
			} elseif ($tipo_lista == "id") {
				foreach ($itens as $item) {
					if (!preg_match("/^[0-9]+$/", $item)) {
						$erros[] = "A lista de IDs não pode conter caracteres não numéricos.";
						break;
					}
				}
			} elseif ($tipo_lista != "cpf_cnpj") {
				$erros[] = "Tipo de lista inválido.";
			}
		}

		return $erros;
	}

	/**
	 * Parses the list of clients
	 * @param string $lista
	 * @return array
	 */
	function parseLista($lista) {

		$itens = preg_split("/[\s,;]+/", trim($lista), -1, PREG_SPLIT_NO_EMPTY);
		$itens = array_map("trim", $itens);
		$itens = array_filter($itens, "strlen");

		return array_values(array_unique($itens));
	}

	/**
	 * Valida data no formato Y-m-d (input type=date)
	 * @param string $data
	 * @return bool
	 */
	function validaDataIso($data) {

		if (!preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $data, $partes)) {
			return false;
		}

		return checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1]);
	}

	/**
	 * Converte data Y-m-d para d/m/Y (formato esperado por acaoCancelarContrato)
	 * @param string $data
	 * @return string
	 */
	function formataDataCancelamento($data) {

		if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $data, $partes)) {
			return $partes[3] . "/" . $partes[2] . "/" . $partes[1];
		}

		return $data;
	}

	/**
	 * Verifica se a data é maior que a data atual
	 * @param string $data
	 * @return bool
	 */
	function dataMaiorQueHoje($data) {

		if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $data, $partes)) {
			$timestamp = mktime(0, 0, 0, (int) $partes[2], (int) $partes[3], (int) $partes[1]);
		} elseif (preg_match("/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/", $data, $partes)) {
			$timestamp = mktime(0, 0, 0, (int) $partes[2], (int) $partes[1], (int) $partes[3]);
		} else {
			return true;
		}

		$hoje = mktime(0, 0, 0, date("n"), date("j"), date("Y"));

		return $timestamp > $hoje;
	}

	/**
	 * Monta o array de clientes
	 * @param array $origens
	 * @param string $tipo_lista
	 * @param string $lista_clientes
	 * @param string $data_cancelamento
	 * @return array
	 */
	function montaArrayClientes($origens, $tipo_lista, $lista_clientes, $data_cancelamento) {

		$itens = $this->parseLista($lista_clientes);
		$clientes = array();

		$formato_data_cancelamento = $this->formataDataCancelamento($data_cancelamento);

		if ($tipo_lista == "id") {
			foreach ($itens as $cliente_id) {
				$clientes[] = array(
					"cliente_id" => $cliente_id,
					"cancelamento" => $formato_data_cancelamento
				);
			}
			return $clientes;
		}

		$cpfs = array();
		foreach ($itens as $item) {
			$cpf = strtoupper(preg_replace("/[^0-9a-zA-Z]/", "", $item));
			if ($cpf !== "") {
				$cpfs[] = $cpf;
			}
		}

		$cpfs = array_values(array_unique($cpfs));
		if (!count($cpfs)) {
			return array();
		}

		$origens_sql = implode(",", array_map("intval", $origens));
		$cpfs_sql = "'" . implode("','", array_map("addslashes", $cpfs)) . "'";

		$query = $this->bancodedados->query("
			SELECT
				cli_id AS cliente_id
			FROM
				tab_clientes
			WHERE
				clo_id IN ({$origens_sql}) AND
				UPPER(REGEXP_REPLACE(cli_cpf_cnpj, '[^0-9a-zA-Z]', '')) IN ({$cpfs_sql})
			ORDER BY
				cli_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$clientes[] = array(
				"cliente_id" => $dados["cliente_id"],
				"cancelamento" => $data_cancelamento
			);
		}

		return $clientes;
	}

	/**
	 * Executa o cancelamento dos contratos
	 * @param array $clientes
	 * @param array $origens
	 * @return string
	 */
	function executaCancelamento($clientes, $origens) {

		$this->contratos = controle::instanciaObjeto("contratos", $this->bancodedados, null, false);

		$linhas = array();
		$origens_sql = implode(",", array_map("intval", $origens));

		foreach ($clientes as $cliente) {
			$contratos = $this->buscaContratos($cliente["cliente_id"], $origens_sql);

			if (!count($contratos)) {
				$linhas[] = "[AVISO] Cliente {$cliente["cliente_id"]}: nenhum contrato elegível encontrado.";
				continue;
			}

			foreach ($contratos as $contrato) {
				$this->contratos->acaoCancelarContrato(
					$this->contratos->buscaDadosContrato($contrato["contrato_id"]),
					$cliente["cancelamento"],
					"Cancelamento em massa, Solicitação feita pelo Parceiro "
				);
				$linhas[] = "[OK] [{$contrato["contrato_codigo"]}] [{$contrato["cliente_id"]}] {$contrato["cliente_nome"]}: {$cliente["cancelamento"]}";
			}
		}

		return "<pre style=\"text-align:left; margin:10px;\">" . implode("\n", $linhas) . "</pre>";
	}

	/**
	 * Busca os contratos do cliente
	 * @param int $cliente_id
	 * @param string $origens_sql
	 * @return array
	 */
	function buscaContratos($cliente_id, $origens_sql) {

		$clientes_contratos = array();
		$cliente_id = preg_replace("/[^0-9]/", "", $cliente_id);

		$query = $this->bancodedados->query("
			SELECT
				tab_contratos.cor_id AS contrato_id,
				tab_contratos.cor_codigo AS contrato_codigo,
				tab_clientes.cli_id AS cliente_id,
				tab_contratos.cor_data_ativacao AS data_ativacao,
				tab_clientes.cli_nome AS cliente_nome
			FROM
				tab_clientes
				INNER JOIN tab_contratos ON (tab_clientes.cli_id = tab_contratos.cli_id)
			WHERE
				tab_clientes.cli_id = '{$cliente_id}' AND
				tab_clientes.clo_id IN ({$origens_sql}) AND
				tab_contratos.crs_id IN (19, 20) AND
				tab_contratos.cor_pai IS NULL");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$clientes_contratos[] = $dados;
		}

		return $clientes_contratos;
	}
}
?>
