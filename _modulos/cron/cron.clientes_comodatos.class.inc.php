<?php

class cron_clientes_comodatos {
	
	var $bancodedados;
	
	function __construct($bancodedados) {
		$this->bancodedados = $bancodedados;
	}
	
	function abreChamadoDoacaoEquipamentos($dados) {
		// Instancia Classe Chamados
		$chamados = controle::instanciaObjeto ( "chamados", $this->bancodedados, null, false );
		$chamado_id = $chamados->buscaChamadoClienteCategoria ( $dados ["cliente_id"], 111 );
		$strEquipamentos .= "[d][n]" . $dados ["nome_equipamento"] . "[/n]\n";
		$strEquipamentos .= "[n] Número de Série: [/n]" . $dados ["numero_serie"] . "\n";
		$strEquipamentos .= "[n] Patrimonio: [/n]" . $dados ["patrimonio"] . "[/d]";
		if ($chamado_id) {
			$dados ["conteudo"] = "Providenciar doação de equipamento(s) em comodato a partir de " . date ( "d/m/Y", strtotime ( "{$dados["data_termino"]}" ) ) . ":";
			$dados ["conteudo"] .= "[d]Contrato: [<a class=\"txtAzulEscuro11\" style=\"text-decoration:underline\" href=\"javascript:verContrato({$dados["contrato_id"]})\">{$dados["contrato_id"]}</a>][/d]";
			$dados ["conteudo"] .= "Equipamentos:";
			$dados ["conteudo"] .= $strEquipamentos;
			$dados ["sistema"] = true;
			// Registra Etapa
			$chamados->registraEtapa ( $chamados->buscaDadosChamado ( $chamado_id, true ), 5, $dados );
		} else {
			// Gera Dados Chamado
			$dados_chamado ["solicitante"] = "Sistema (Intranet)";
			$dados_chamado ["categoria"] = 111;
			$dados_chamado ["prioridade"] = 3;
			$dados_chamado ["tipo"] = 5;
			$dados_chamado ["sistema"] = true;
			$dados_chamado ["conteudo"] = "Providenciar doação de equipamento(s) em comodato a partir de " . date ( "d/m/Y", strtotime ( "{$dados["data_termino"]}" ) ) . ":";
			$dados_chamado ["conteudo"] .= "[d]Contrato: [<a class=\"txtAzulEscuro11\" style=\"text-decoration:underline\" href=\"javascript:verContrato({$dados["contrato_id"]})\">{$dados["contrato_id"]}</a>][/d]";
			$dados_chamado ["conteudo"] .= "Equipamentos:";
			$dados_chamado ["conteudo"] .= $strEquipamentos;
			// Adiciona Chamado
			$chamados->adicionarChamado ( $dados ["contrato_id"], null, $dados_chamado );
		}
	}
	
	function verificaRetiradaEquipamento() {
		$query = "SELECT tab_clientes_comodatos.cli_id AS cliente_id,
						tab_clientes_comodatos.cor_id AS contrato_id,
						tab_equipamentos.equ_nome AS nome_equipamento,
						tab_clientes_comodatos.clc_patrimonio AS patrimonio,
						tab_clientes_comodatos.clc_numero_serie AS numero_serie,
						tab_clientes_comodatos.clc_data_termino AS data_termino
		 			FROM tab_clientes_comodatos
		 			INNER JOIN tab_equipamentos ON (tab_clientes_comodatos.equ_id = tab_equipamentos.equ_id )
		 				WHERE tab_clientes_comodatos.clc_data_termino = (SELECT DATE_ADD(CURDATE(), INTERVAL 1 day))
		 				AND tab_clientes_comodatos.ccs_id = 1";
		
		$query = $this->bancodedados->query ( $query );
		while ( ($dados = $this->bancodedados->resultadoArray ( $query )) != false ) {
			$this->abreChamadoDoacaoEquipamentos ( $dados );
		}
		$query = "UPDATE tab_clientes_comodatos 
		            SET tab_clientes_comodatos.ccs_id = 3 
		               WHERE tab_clientes_comodatos.clc_data_termino = (SELECT DATE_ADD(CURDATE(), INTERVAL 1 day))";
		$query = $this->bancodedados->query ( $query );
	}
}
?>
