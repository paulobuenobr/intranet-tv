<?

require_once ("../global.inc.php");

global $bancodedados;

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
		
		$query = $this->bancodedados->query("
			SELECT
				est_sigla AS sigla
			FROM
				tab_estados
			WHERE
				1
			ORDER BY
				est_sigla");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->verificaEstado($dados["sigla"]);
		}
		
	}
	
	function verificaEstado($estado) {
		
		$estado_id = $this->buscaEstado($estado);
		
		$query = $this->bancodedados->query("
			SELECT
				ceps_logradouros.cep AS cep,
				ceps_logradouros.logradouro AS logradouro,
				ceps_logradouros.observacao AS observacao,
				ceps_logradouros.estado AS estado,
				ceps_cidades.nome AS cidade,
				ceps_bairros.nome AS bairro
			FROM
				ceps_logradouros
				INNER JOIN ceps_cidades ON (ceps_logradouros.cidade_id = ceps_cidades.id)
				INNER JOIN ceps_bairros ON (ceps_logradouros.bairro_id = ceps_bairros.id)
			WHERE
				ceps_logradouros.estado = '{$estado}'");
		
		while (($dne = $this->bancodedados->resultadoArray($query)) != false) {
			
			$cidade_id = $this->buscaCidade($dne["cidade"], $estado_id);
			
			$this->bancodedados->query("
				UPDATE
					tab_cidades
				SET
					cid_cep_geral = NULL
				WHERE
					cid_id = {$cidade_id}");
			
			$bairro_id = $this->buscaBairro($dne["bairro"], $cidade_id);
			$dados_cep = $this->buscaDadosCEP($dne["cep"]);
			
			if ($dados_cep) {
				
				if ($dados_cep["estado_id"] != $estado_id ||
					$dados_cep["cidade_id"] != $cidade_id ||
					$dados_cep["bairro_id"] != $bairro_id ||
					$dados_cep["logradouro"] != $dne["logradouro"] ||
					$dados_cep["observacao"] != $dne["observacao"]) {
						
						$dne["logradouro"] = addslashes($dne["logradouro"]);
						$dne["observacao"] = addslashes($dne["observacao"]);
						
						$this->bancodedados->query("
							UPDATE
								tab_logradouros
							SET
								log_nome = '{$dne["logradouro"]}',
								log_observacao = '{$dne["observacao"]}',
								bai_id = {$bairro_id}
							WHERE
								log_id = {$dados_cep["logradouro_id"]}");
						
						$this->bancodedados->query("
							UPDATE
								tab_ceps
							SET
								bai_id = {$bairro_id},
								cid_id = {$cidade_id},
								est_id = {$estado_id}
							WHERE
								cep_id = {$dados_cep["cep"]}");
						
						print "CEP ATUALIZADO: {$dados_cep["cep"]}\n";
						
					}
					
			} else {
				
				$dne["logradouro"] = addslashes($dne["logradouro"]);
				$dne["observacao"] = addslashes($dne["observacao"]);
				
				$this->bancodedados->query("
					INSERT INTO
						tab_logradouros
					SET
						log_nome = '{$dne["logradouro"]}',
						log_observacao = '{$dne["observacao"]}',
						bai_id = {$bairro_id}");
				
				$logradouro_id = $this->bancodedados->ultimoIdInserido();
				
				$this->bancodedados->query("
					INSERT INTO
						tab_ceps
					SET
						cep_id = {$dne["cep"]},
						cep_exportacao = {$dne["cep"]},
						log_id = {$logradouro_id},
						bai_id = {$bairro_id},
						cid_id = {$cidade_id},
						est_id = {$estado_id}");
				
				print "CEP CADASTRADO: {$dne["cep"]}\n";
				
			}
			
		}
		
		// Atualiza CEPs Gerais
		
		$query = $this->bancodedados->query("
			SELECT
				id,
				nome,
				cep_geral,
				estado
			FROM
				ceps_cidades
			WHERE
				estado = '{$estado}'");
		
		while (($dne = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados_cidade = $this->buscaDadosCidade($dne["nome"], $estado_id);
			
			if ($dados_cidade) {
				
				if ($dados_cidade["cep_geral"] != $dne["cep_geral"]) {
					
					$dne["cep_geral"] = $dne["cep_geral"] ? $dne["cep_geral"] : "NULL";
					
					$this->bancodedados->query("
						UPDATE
							tab_cidades
						SET
							cid_cep_geral = {$dne["cep_geral"]}
						WHERE
							cid_id = {$dados_cidade["id"]}");
					
					print "CIDADE ATUALIZADA: {$dne["nome"]}/{$estado}\n";
					
				}
				
			} else {
				
				$dne["cep_geral"] = $dne["cep_geral"] ? "'{$dne["cep_geral"]}'" : "NULL";
				$dne["nome"] = addslashes($dne["nome"]);
				
				$this->bancodedados->query("
					INSERT INTO
						tab_cidades
					SET
						cid_nome = '{$dne["nome"]}',
						cid_cep_geral = {$dne["cep_geral"]},
						est_id = $estado_id");
				
				print "CIDADE CADASTRADA: {$dne["nome"]}/{$estado}\n";
				
			}
			
		}
		
	}
	
	function buscaDadosCEP($cep) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_ceps.cep_id AS cep,
				tab_ceps.est_id AS estado_id,
				tab_ceps.cid_id AS cidade_id,
				tab_ceps.bai_id AS bairro_id,
				tab_logradouros.log_id AS logradouro_id,
				tab_logradouros.log_nome AS logradouro,
				tab_logradouros.log_observacao AS observacao
			FROM
				tab_ceps
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
			WHERE
				tab_ceps.cep_id = '$cep'");
		
		return $this->bancodedados->resultadoArray($query);
		
	}
	
	function buscaDadosDNE($cep) {
		
		$query = $this->bancodedados->query("
			SELECT
				ceps.id AS cep,
				ceps.logradouro AS logradouro,
				ceps.observacao AS observacao,
				ceps_bairros.nome AS bairro,
				ceps_cidades.nome AS cidade,
				ceps_estados.nome AS estado,
				ceps_estados.sigla AS sigla
			FROM
				ceps
				INNER JOIN ceps_bairros ON (ceps.bairro_id = ceps_bairros.id)
				INNER JOIN ceps_cidades ON (ceps.cidade_id = ceps_cidades.id)
				INNER JOIN ceps_estados ON (ceps.estado_id = ceps_estados.id)
			WHERE
				ceps.id = '$cep'");
		
		return $this->bancodedados->resultadoArray($query);
		
	}
	
	var $estados_por_sigla = array();
	
	function buscaEstado($sigla) {
		
		if (!array_key_exists($sigla, $this->estados_por_sigla)) {
			
			$query = $this->bancodedados->query("
				SELECT
					est_id
				FROM
					tab_estados
				WHERE
					est_sigla = '$sigla'");
			
			$this->estados_por_sigla[$sigla] = $this->bancodedados->resultado($query);

		}

		return $this->estados_por_sigla[$sigla];
		
	}
	
	function buscaCidade($nome, $estado_id) {
		
		$nome = addslashes($nome);
		
		$query = $this->bancodedados->query("
			SELECT
				cid_id
			FROM
				tab_cidades
			WHERE
				cid_nome = '$nome' AND
				est_id = $estado_id");
		
		if (($cidade_id = $this->bancodedados->resultado($query)) != false) {
			return $cidade_id;
		}
		
		$this->bancodedados->query("
			INSERT INTO
				tab_cidades
			SET
				cid_nome = '$nome',
				est_id = $estado_id");

		return $this->bancodedados->ultimoIdInserido();
		
	}
	
	function buscaDadosCidade($nome, $estado_id) {
		
		$nome = addslashes($nome);
		
		$query = $this->bancodedados->query("
			SELECT
				cid_id AS id,
				cid_nome AS nome,
				cid_cep_geral AS cep_geral
			FROM
				tab_cidades
			WHERE
				cid_nome = '$nome' AND
				est_id = $estado_id");
		
		return $this->bancodedados->resultadoArray($query);
			
	}
	
	function buscaBairro($nome, $cidade_id) {
		
		$nome = addslashes($nome);
		
		$query = $this->bancodedados->query("
			SELECT
				bai_id
			FROM
				tab_bairros
			WHERE
				bai_nome = '$nome' AND
				cid_id = $cidade_id");
		
		if (($bairro_id = $this->bancodedados->resultado($query)) != false) {
			return $bairro_id;
		}
		
		$this->bancodedados->query("
			INSERT INTO
				tab_bairros
			SET
				bai_nome = '$nome',
				cid_id = $cidade_id");
		
		return $this->bancodedados->ultimoIdInserido();
		
	}
	
}

?>
