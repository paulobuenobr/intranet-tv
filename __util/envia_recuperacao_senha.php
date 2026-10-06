<?php

require_once ("../global.inc.php");

global $bancodedados;

$envia_recuperacao_senha = new envia_recuperacao_senha($bancodedados);

//$envia_recuperacao_senha->enviar();

class envia_recuperacao_senha {

	private $bancodedados;
		
	##################
	### Construtor ###
	##################

	function envia_recuperacao_senha($bancodedados) {
		
		$this->bancodedados = $bancodedados;
				
	}

	################
	### Importar ###
	################

	function enviar() {
		
		$clientes_iptv = controle::instanciaObjeto("clientes_iptv", $this->bancodedados, null, false);

		print "<pre>";

		$clientes = $this->buscaClientesIptv();

		foreach ($clientes as $iptv) {
			
			if ($clientes_iptv->recuperarSenha($iptv["iptv_id"])) {
				$clientes_iptv->registraComentario($iptv["iptv_id"], "<b>Ação</b>: Recuperação de senha enviada para: {$iptv["usuario"]}", null);
				print "[OK] [{$iptv["cliente_id"]}] {$iptv["nome"]}\n";
			} else {
				print "[ERRO] [{$iptv["cliente_id"]}] {$iptv["nome"]}\n";
			}
			

		}

	    print "</pre>";

	}
	
	function buscaClientesIptv() {
		
		$clientes_contratos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_iptv.cip_id AS iptv_id,
				tab_clientes_iptv.cip_usuario AS usuario,
				tab_clientes.cli_id as cliente_id,
				tab_clientes.cli_nome AS nome				
			FROM
				tab_clientes_iptv
				INNER JOIN tab_clientes ON (tab_clientes_iptv.cli_id = tab_clientes.cli_id)
			WHERE
				tab_clientes.clo_id = 550 AND
				tab_clientes.cli_id NOT IN (649212) AND
				tab_clientes_iptv.cis_id = 1");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$clientes_contratos[] = $dados;
		}
		
		return $clientes_contratos;
	
	}

}

?>