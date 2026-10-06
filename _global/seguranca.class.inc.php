<?

class seguranca {

	var $bancodedados;
	var $permissoes;
	var $usuario;

	function __construct($bancodedados, $usuario) {
		
		$this->bancodedados = $bancodedados;
		$this->permissoes = array();
		$this->usuario = $usuario;
		
		if ($this->ehAdministrador()) {
			$this->buscaPermissoesPossiveis();
		} else {
			$this->buscaPermissoesUsuario();
		}
		
	}

	function buscaPermissoesUsuario() {
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_modulos.mod_nome AS modulo,
				tab_acoes.aco_nome AS acao
			FROM
				rel_usu_gru_fazparte
				INNER JOIN tab_grupos ON (rel_usu_gru_fazparte.gru_id = tab_grupos.gru_id)
				INNER JOIN rel_gru_aco_tempermissao ON (tab_grupos.gru_id = rel_gru_aco_tempermissao.gru_id)
				INNER JOIN tab_acoes ON (rel_gru_aco_tempermissao.aco_id = tab_acoes.aco_id)
				INNER JOIN tab_modulos ON (tab_acoes.mod_id = tab_modulos.mod_id)
			WHERE
				tab_grupos.grs_id = 1 AND
				tab_modulos.mos_id = 1 AND
				rel_usu_gru_fazparte.usu_id = '$this->usuario'");
			
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->permissoes[$dados["modulo"]][$dados["acao"]] = true;
		}
		
	}

	function buscaPermissoesPossiveis() {
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_modulos.mod_nome AS modulo,
				tab_acoes.aco_nome AS acao
			FROM
				tab_modulos
				INNER JOIN tab_acoes ON (tab_modulos.mod_id = tab_acoes.mod_id)
			WHERE
				tab_modulos.mos_id = 1");
					
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->permissoes[$dados["modulo"]][$dados["acao"]] = true;
		}
		
	}

	function ehAdministrador() {
		
		$query = $this->bancodedados->query("
			SELECT
				usu_administrador AS administrador
			FROM 
				tab_usuarios 
			WHERE 
				usu_id = '{$this->usuario}'");
		
		return $this->bancodedados->resultado($query) == "1";
		
	}

	function temPermissao($modulo, $acao) {
		
		if ($acao == "") {
			return $this->permissoes[$modulo]["listar"] || $this->permissoes[$modulo]["buscar"];
		}
		
		return $this->permissoes[$modulo][$acao];
		
	}

}

?>
