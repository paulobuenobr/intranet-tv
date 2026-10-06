<?

header('Content-Type: text/html; charset=UTF-8');

ignore_user_abort(1);
set_time_limit(0);

require_once("../global.inc.php");

global $bancodedados;

print "<PRE>";

$util = new util($bancodedados);
$util->executar();

class util {

	/**
	 * @var bancodedados
	 */
	private $bancodedados;

	function util($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}
	
	function executar() {
		
		$this->importaDNE();
    
	}
	
	function importaDNE() {
		
		$arquivo = fopen("ceps.txt", "r");
		
		while (($linha = fgets($arquivo)) !== false) {
			
			$linha = trim(preg_replace("/([0-9]\t[^\\/]+\\/[A-Z]{2})  - (Distrito|Povoado)/", "$1", $linha));
			
			$linha = explode("\t", $linha);
			
			$linha[1] = explode("/", $linha[1]);
			
			$estado_id = $this->buscaEstado($linha[1][1]);
			
			if ($estado_id) {
			
				$cidade_id = $this->buscaCidade($linha[1][0], $estado_id);
				
				if ($cidade_id) {
					
					while (count($linha) < 5) {
						$linha[] = "";
					}
					
					$bairro_id = $this->buscaBairro($linha[2], $cidade_id);
					
					$matches = array();
					
					$linha[3] = trim($linha[3]);
					
					if (preg_match("/( - (de|até|lado|do) .*)$/", $linha[3], $matches)) {
						$logradouro = trim(substr($linha[3], 0, strlen($linha[3]) - strlen($matches[0])));
						$observacao = trim(substr($linha[3], strlen($linha[3]) - strlen($matches[0]) + 3));
					} elseif (preg_match("/- (impar|Ímpar|Impar|IMPAR)$/", $linha[3], $matches)) {
						$logradouro = trim(substr($linha[3], 0, strlen($linha[3]) - strlen($matches[0])));
						$observacao = "lado ímpar";
					} elseif (preg_match("/- (par|PAR)$/", $linha[3], $matches)) {
						$logradouro = trim(substr($linha[3], 0, strlen($linha[3]) - strlen($matches[0])));
						$observacao = "lado par";
					} elseif (preg_match("/Blocos (.*) \\(Lado par\\)$/", $linha[3], $matches)) {
						$logradouro = trim(substr($linha[3], 0, strlen($linha[3]) - strlen($matches[0])));
						$observacao = "blocos {$matches[0]} - lado par";
					} elseif (preg_match("/Blocos (.*) \\(Lado impar\\)$/", $linha[3], $matches)) {
						$logradouro = trim(substr($linha[3], 0, strlen($linha[3]) - strlen($matches[0])));
						$observacao = "blocos {$matches[0]} - lado ímpar";
					} elseif (preg_match("/\\(Lado par\\)$/", $linha[3], $matches)) {
						$logradouro = trim(substr($linha[3], 0, strlen($linha[3]) - strlen($matches[0])));
						$observacao = "lado par";
					} elseif (preg_match("/\\(Lado [íiIÍ]mpar\\)$/", $linha[3], $matches)) {
						$logradouro = trim(substr($linha[3], 0, strlen($linha[3]) - strlen($matches[0])));
						$observacao = "lado ímpar";
					} else{
					 	$logradouro = trim($linha[3]);
						$observacao = "";
					}
					
					$linha[4] = trim($linha[4]);
					
					if ($linha[4]) {
						$observacao = $linha[4];
					}
					
					$logradouro = preg_replace("/ {2,}/", " ", $logradouro);
					$logradouro = preg_replace("/,? nº ?/", ", ", $logradouro);
					$logradouro = preg_replace("/s\\/nº/", "s/n", $logradouro);
					$logradouro = preg_replace("/\sAv.\s/", "Avenida", $logradouro);
					$logradouro = preg_replace("/\sRod.\s/", "Rodovia", $logradouro);
					
					$observacao = preg_replace("/ {2,}/", " ", $observacao);
					
					print "{$linha[1][1]}\t{$estado_id}\t{$linha[1][0]}\t{$cidade_id}\t{$linha[2]}\t{$bairro_id}\t$logradouro\t$observacao\n";
					
					$logradouro = addslashes(iconv("UTF-8", "ISO-8859-1", $logradouro));
					$observacao = addslashes(iconv("UTF-8", "ISO-8859-1", $observacao));
					
					$bairro_id = $bairro_id ? $bairro_id : "NULL";
					
					$this->bancodedados->query("
						REPLACE INTO
							ceps
						SET
							id = {$linha[0]},
							logradouro = '{$logradouro}',
							observacao = '{$observacao}',
							bairro_id = {$bairro_id},
							cidade_id = {$cidade_id},
							estado_id = {$estado_id}");
					
				} else {
					var_dump($linha);
					die();
				}
			
			} else {
				var_dump($linha);
				die();
			}
			
		}
		
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
		
		$cidade = addslashes(iconv("UTF-8", "ISO-8859-1", trim($cidade)));
		
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
			
			$bairro = addslashes(iconv("UTF-8", "ISO-8859-1", trim($bairro)));

			$bairro = preg_replace("/([^ ])\\(/", "$1 (", $bairro);
			
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
