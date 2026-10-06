<?

$filtro = unserialize('a:4:{s:10:"comparacao";a:2:{s:1:"=";s:7:"Igual a";s:2:"<>";s:12:"Diferente de";}s:14:"valor_conteudo";s:77:"SELECT clo_id AS id, clo_nome AS nome FROM tab_clientes_origens ORDER BY nome";s:6:"juncao";a:1:{i:0;s:71:"INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)";}s:6:"filtro";a:1:{s:5:"where";a:1:{i:0;s:40:"tab_clientes.clo_id [comparacao] [valor]";}}}');

$filtro["valor_conteudo"] = "SELECT est_id AS id, est_nome AS nome FROM tab_estados ORDER BY nome";

$filtro["juncao"][] = "INNER JOIN tab_ceps ON (tab_clientes.cep_id = tab_ceps.cep_id)";

$filtro["filtro"]["where"][0] = "tab_ceps.est_id [comparacao] [valor]";

//var_dump($filtro);

print serialize($filtro);

?>
