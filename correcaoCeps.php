<?php

header('Content-Type: text/html; charset=utf-8');

set_time_limit(0);
ignore_user_abort(1);

require_once 'api/config/config_api.php';





print 'Correção dos CEPs ficticios do CEP geral 85929000<br>'.PHP_EOL;
print '----------------------------------------------------<br>'.PHP_EOL;

$startCep = 85929069;

try {
	// Conexão com PDO
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);//DB_USER, DB_PASS
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// Sua query
	$sql = "SELECT * FROM `tab_ceps` WHERE cep_id > $startCep AND cep_exportacao = 85929000";

	// Executa a consulta
	$stmt = $pdo->query($sql);

	//$startCep = 85929070;

	// Percorre os resultados
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		// Aqui você trata cada linha

	    $startCep++;

	    echo "CEP anterior: " . $row["cep_id"] . " | CEP novo: " . $startCep . "<br>";

		$sql = "INSERT INTO `tab_ceps` (cep_id, log_id, bai_id, cid_id, est_id, cep_exportacao, cep_zona)
            VALUES ($startCep, ".$row["log_id"].", ".$row["bai_id"].", ".$row["cid_id"].", ".$row["est_id"].", ".$row["cep_exportacao"].", '".$row["cep_zona"]."')";

		$pdo->exec($sql); // executa a query
		echo "- sql: ".$sql."<br>";

        // Atualiza cliente com o CEP errado
		$clienteSQL = "UPDATE tab_clientes SET cep_id=".$startCep." WHERE cep_id = ".$row["cep_id"];
		$pdo->exec($clienteSQL); // executa a query
		echo "- sql: ".$clienteSQL."<br>";

		// Exclui o CEP errado
		$delSQL = "DELETE FROM tab_ceps WHERE cep_id = ".$row["cep_id"];
		$pdo->exec($delSQL); // executa a query
		echo "- sql: ".$delSQL."<hr>";




	}

} catch (PDOException $e) {
	echo "Erro na conexão: " . $e->getMessage();
}

?>