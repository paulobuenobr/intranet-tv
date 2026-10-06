<?

class login {

	var $bancodedados;

	var $usuario;

	function __construct($bancodedados) {

		$this->bancodedados = $bancodedados;

	}

	function estaLogado() {

		return $this->checaUsuario($_SESSION["usuario"], $_SESSION["senha"]);

	}

	function logaUsuario($usuario, $senha) {

		return $this->checaUsuario($usuario, $senha, "login");

	}

	function deslogaUsuario() {

		unset($_SESSION["usuario"]);
		unset($_SESSION["senha"]);
		unset($_SESSION["origem"]);
		unset($_SESSION["administrador"]);
		
	}

	function checaUsuario($usuario, $senha, $modo = "id") {

		$query = $this->bancodedados->query("
			SELECT
				tab_usuarios.usu_id AS id,
				tab_usuarios.usu_senha AS senha,
				tab_usuarios.clo_id AS origem,
				tab_usuarios.usu_administrador AS administrador
			FROM
				tab_usuarios
				INNER JOIN tab_clientes_origens ON (tab_usuarios.clo_id = tab_clientes_origens.clo_id)
			WHERE
				tab_usuarios.usu_$modo = '".addslashes($usuario)."' AND
				tab_usuarios.usu_senha = '".addslashes($senha)."' AND
				tab_usuarios.uss_id = 1 AND
				tab_clientes_origens.cos_id = 2");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$_SESSION["usuario"] = $dados["id"];
			$_SESSION["senha"] = $dados["senha"];
			$_SESSION["origem"] = (int) $dados["origem"];
			$_SESSION["administrador"] = (int) $dados["administrador"];

			return true;

		}

		return false;

	}

}

?>
