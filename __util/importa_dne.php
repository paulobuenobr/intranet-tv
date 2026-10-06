<?

header('Content-Type: text/html; charset=UTF-8');

ignore_user_abort(1);
set_time_limit(0);

require_once("../global.inc.php");

global $bancodedados;

$util = new util($bancodedados);
$util->executar();

class util {

	/**
	 * @var bancodedados
	 */
	private $bancodedados;
	
	private $caminho = "D:\\Desenvolvimento\\eDNE_Basico_1906\\Delimitado";

	function util($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}
	
	function executar() {
		
		$this->importaCidades();
// 		$this->importaBairros();
// 		$this->importaLogradouros();
// 		$this->importaGrandeUsuario();

// 		$this->importaCPC();
    
	}
	
	function importaCidades() {
		
		$this->bancodedados->query("TRUNCATE ceps_cidades");
		
		$arquivo = fopen("{$this->caminho}\\LOG_LOCALIDADE.txt", "r");
		
		while (($linha = fgets($arquivo)) !== false) {
			
			$linha = explode("@", $linha);
			
			if ($linha[5] == "M") {
				
				print "Cidade $linha[2]/$linha[1]\n";
				
				$linha[2] = $this->limpaString($linha[2]);
				
				$linha[2] = addslashes(trim($linha[2]));
				$linha[3] = $linha[4] == "0" ? $linha[3] : "NULL";
				
				$this->bancodedados->query("
					INSERT INTO
						ceps_cidades
					SET
						id = {$linha[0]},
						estado = '{$linha[1]}',
						nome = '{$linha[2]}',
						cep_geral = $linha[3]");
				
			}
				
		}
		
		fclose($arquivo);
		
	}
	
	function importaBairros() {
		
		$this->bancodedados->query("TRUNCATE ceps_bairros");
		
		$arquivo = fopen("{$this->caminho}\\LOG_BAIRRO.TXT", "r");
		
		while (($linha = fgets($arquivo)) !== false) {
			
			$linha = explode("@", $linha);
			
			print "Bairro $linha[3]\n";
			
			$linha[3] = $this->limpaString($linha[3]);
			
			$linha[3] = addslashes(trim($linha[3]));
			
			$this->bancodedados->query("
				INSERT INTO
					ceps_bairros
				SET
					id = '{$linha[0]}',
					nome = '{$linha[3]}',
					cidade_id = {$linha[2]}");
			
		}
		
		fclose($arquivo);
		
	}
	
	function importaLogradouros() {
		
		$this->bancodedados->query("TRUNCATE ceps_logradouros");
		
		$arquivos = scandir($this->caminho);
		
		foreach ($arquivos as $arquivo) {
			if (preg_match("/LOG_LOGRADOURO_[A-Z]{2}\\.TXT/i", $arquivo)) {
				$this->importaLogradouro($arquivo);
			}
		}
		
		
	}
	
	function importaLogradouro($arquivo) {
		
		$arquivo = fopen("{$this->caminho}\\{$arquivo}", "r");
		
		while (($linha = fgets($arquivo)) !== false) {
			
			$linha = explode("@", $linha);

			$linha[5] = $this->limpaString($linha[5]);
			$linha[6] = $this->limpaString($linha[6]);
			
			if ($linha[8] && substr($linha[5], 0, strlen($linha[8])) != $linha[8]) {
				$linha[5] = "{$linha[8]} {$linha[5]}";
			}
			
			$linha[5] = addslashes(trim($linha[5]));
			$linha[6] = addslashes(trim($linha[6]));
			
			$this->bancodedados->query("
				INSERT INTO
					ceps_logradouros
				SET
					cep = {$linha[7]},
					logradouro = '{$linha[5]}',
					observacao = '{$linha[6]}',
					bairro_id = {$linha[3]},
					cidade_id = {$linha[2]},
					estado = '{$linha[1]}'");
			
		}
		
	}
	
	function importaGrandeUsuario() {
		
		$arquivo = fopen("{$this->caminho}\\LOG_GRANDE_USUARIO.txt", "r");
		
		while (($linha = fgets($arquivo)) !== false) {
			
			$linha = explode("@", $linha);
			
			$linha[5] = $this->limpaString($linha[5]);
			$linha[6] = $this->limpaString($linha[6]);
			
			$linha[5] = addslashes(trim($linha[5]));
			$linha[6] = addslashes(trim($linha[6]));
			
			$this->bancodedados->query("
				INSERT INTO
					ceps_logradouros
				SET
					cep = {$linha[7]},
					logradouro = '{$linha[6]}',
					observacao = '{$linha[5]}',
					bairro_id = {$linha[3]},
					cidade_id = {$linha[2]},
					estado = '{$linha[1]}'");
			
		}
		
	}
	
	function importaCPC() {
		
		$arquivo = fopen("{$this->caminho}\\LOG_CPC.TXT", "r");
		
		while (($linha = fgets($arquivo)) !== false) {
			
			$linha = explode("@", $linha);
			
			$linha[5] = $this->limpaString($linha[5]);
			$linha[6] = $this->limpaString($linha[6]);
			
			$linha[5] = addslashes(trim($linha[5]));
			$linha[6] = addslashes(trim($linha[6]));
			
			$this->bancodedados->query("
				INSERT INTO
					ceps_logradouros
				SET
					cep = {$linha[5]},
					logradouro = '{$linha[4]}',
					observacao = '{$linha[3]}',
					bairro_id = {$linha[3]},
					cidade_id = {$linha[2]},
					estado = '{$linha[1]}'");
			
		}
		
	}
	
	function limpaString($string) {
		
		$string = preg_replace("/[ì]/", "í", $string);
		$string = preg_replace("/[Í]/", "Í", $string);
		$string = preg_replace("/[°]/", "º", $string);
		$string = preg_replace("/[`´']+/", "'", $string);
		$string = preg_replace("/[^0-9a-zA-ZáàãâéêíóõôúÁÀÃÂÉÊÍÓÕÔÚçÇñÑ \\(\\)\\/\\.ªº,'&+_\"\\-]/", "", $string);
		$string = preg_replace("/^- /", "", $string);
		$string = preg_replace("/^\\(([^)]+)\\)?/", "$1", $string);
		$string = preg_replace("/\s+/", " ", $string);
		
		return $string;
		
	}
	
	function buscaEstado($sigla) {
		
		$query = $this->bancodedados->query("
			SELECT
				id
			FROM
				ceps_estados
			WHERE
				sigla = '{$sigla}'");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function buscaCidade($cidade, $estado_id) {
		
		$cidade = addslashes(trim($cidade));
		
		$cidade = preg_replace("/([^ ])\\(/", "$1 (", $cidade);
		
		$query = $this->bancodedados->query("
			SELECT
				id
			FROM
				ceps_cidades
			WHERE
				nome = '{$cidade}' AND
				estado_id = {$estado_id}");
		
		if (($cidade_id = $this->bancodedados->resultado($query)) != false) {
			return $cidade_id;
		}
		
		$this->bancodedados->query("
				INSERT INTO
					ceps_cidades
				SET
					nome = '{$cidade}',
					estado_id = {$estado_id}");
		
		return $this->bancodedados->ultimoIdInserido();

	}
	
	function buscaBairro($bairro, $cidade_id) {
		
		if ($bairro && $cidade_id) {
			
			$bairro = addslashes(trim($bairro));

			$query = $this->bancodedados->query("
				SELECT
					id
				FROM
					ceps_bairros
				WHERE
					nome = '{$bairro}' AND
					cidade_id = {$cidade_id}");

			if (($bairro_id = $this->bancodedados->resultado($query)) != false) {
				return $bairro_id;
			}
			
			$this->bancodedados->query("
				INSERT INTO
					ceps_bairros
				SET
					nome = '{$bairro}',
					cidade_id = {$cidade_id}");
			
			return $this->bancodedados->ultimoIdInserido();
			
		}
		
		return null;
		
	}
	
}

?>
