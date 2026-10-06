<?

class bancodedados {

	var $conexao;

	function __construct($conectar = true, $servidor = null, $usuario = null, $senha = null, $basededados = null) {

		$servidor = $servidor ? $servidor : "10.0.100.250";
		$usuario = $usuario ? $usuario : "tvintranetuser";
		$senha = $senha ? $senha : "WSinTr@net";
		$basededados = $basededados ? $basededados : "tvintranet";

		if ($conectar && !$this->conectar($servidor, $usuario, $senha, $basededados)) {
			header("Location: ./erro.php?id=bancodedados");
		}

	}

	function conectar($servidor, $usuario, $senha, $basededados) {

		$this->conexao = new mysqli($servidor, $usuario, $senha, $basededados);

		$this->conexao->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
		$this->conexao->options(MYSQLI_OPT_READ_TIMEOUT, 10);

		if ($this->conexao->connect_error) {
			return false;
		}

		return true;

	}

	function query($query) {

		if (is_string($query)) {

			$resultadoQuery = $this->conexao->query($query);

			if ($resultadoQuery === false) {

				ob_start();
				debug_print_backtrace();
				$trace = ob_get_contents();
				ob_end_clean();
				
				$fp = fopen(DIR_RAIZ."log".DIRECTORY_SEPARATOR."bancodedados.log", "a");
				
				fwrite($fp, date("d-m-Y H:i:s")." - ".$this->conexao->errno.": ". $this->conexao->error."\n\n{$trace}\n\n##########################\n\n");
				fclose($fp);

				//print "<PRE>".date("d-m-Y H:i:s")." - ".$this->conexao->errno.": ".$this->conexao->error."\r\n\r\n{$trace}\r\n\r\n";
				
				print "<PRE>Erro desconhecido. Procure o setor de desenvolvimento.";
				
				die();
				
			}

			return $resultadoQuery;

		}

		return null;

	}

	function linhas($resultadoQuery) {

		if ($resultadoQuery instanceof mysqli_result) {
			return $resultadoQuery->num_rows;
		}

		return 0;

	}

	function linhasAfetadas() {

		return $this->conexao->affected_rows;

	}

	function linhasTotal($resultadoQuery) {

		return $this->resultado($this->query("SELECT found_rows()"));

	}

	function colunas($resultadoQuery) {

		if ($resultadoQuery instanceof mysqli_result) {
			return $resultadoQuery->field_count;
		}

		return 0;

	}

	function resultado($resultadoQuery, $linha = 0, $coluna = 0) {

		if ($resultadoQuery instanceof mysqli_result) {
			$resultadoQuery->data_seek($linha);
			return $resultadoQuery->fetch_array()[$coluna];
		}

		return null;

	}

	function resultadoArray($resultadoQuery, $modo = MYSQLI_ASSOC) {

		if ($resultadoQuery instanceof mysqli_result) {
			return $resultadoQuery->fetch_array($modo);
		}

		return null;

	}

	function resultadoArrayTotal($resultadoQuery, $modo = MYSQLI_ASSOC) {

		$resultados = array();

		if ($resultadoQuery instanceof mysqli_result) {
			while (($resultado = $this->resultadoArray($resultadoQuery, $modo)) != false) {
				$resultados[] = $resultado;
			}
		}

		return $resultados;

	}

	function ultimoIdInserido() {

		return $this->conexao->insert_id;

	}
	
	function transacaoIniciar() {
		
		$this->query("BEGIN");
		
	}
	
	function transacaoTerminar() {
		
		$this->query("COMMIT");
		
	}
	
	function transacaoSalvar() {
		
		$this->query("COMMIT");
		
	}
	
	function transacaoCancelar() {
		
		$this->query("ROLLBACK");
		
	}

}

?>
