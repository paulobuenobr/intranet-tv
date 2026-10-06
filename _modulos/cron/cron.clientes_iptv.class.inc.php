<?

set_time_limit(0);

class cron_clientes_iptv {

	var $bancodedados;
	
	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;

	}
		
	###########################
	### Publicar Aguardando ###
	###########################
	
	function publicar_aguardando() {
		
		$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
		$publicacao->publicarAguardando();

	}
			
	###################
	### Sincronizar ###
	###################
	
	function sincronizar() {
		
		$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
		$publicacao->sincronizar();

	}
	

}

?>