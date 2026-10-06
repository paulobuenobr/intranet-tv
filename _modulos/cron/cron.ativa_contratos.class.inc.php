<?php

class cron_ativa_contratos {
	
	/**
	 * @var bancodedados
	 */
	var $bancodedados;
	
	function cron_ativa_contratos($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}
	
	##########################
	### Cancelar Contratos ###
	##########################
	
	function ativarContratos() {
		
		/**
		 * @var contratos $contratos
		 */
		$contratos = controle::instanciaObjeto("contratos", $this->bancodedados, null, false);
		
		$contratos->ativaContratosAguardandoAtivacao();
		$contratos->migraEnderecoContratosAguardandoMudancaEndereco();
		
	}
	
}

?>