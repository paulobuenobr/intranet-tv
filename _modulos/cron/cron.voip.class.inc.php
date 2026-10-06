<?

class cron_voip {

	var $bancodedados;

	function __construct($bancodedados) {
		$this->bancodedados = $bancodedados;
	}

	######################
	### Publica Contas ###
	######################
	
	function publicaVoipContas () {
    if (!$this->voip_contas) $this->voip_contas = controle::instanciaObjeto("voip_contas", $this->bancodedados);
    if (!$this->voip_asterisk) $this->voip_asterisk = controle::instanciaObjeto("voip_asterisk", $this->bancodedados);
    $voip_contas = $this->voip_contas->buscaVoipContasNaoPublicadas();
    if ($voip_contas) {
      if ($this->voip_asterisk->publicaVoipContas($voip_contas)) {
        foreach ($voip_contas AS $voip_conta) $this->voip_contas->registraComentario($voip_conta["id"], "<b>Ação:</b> Publicado<br>");
      }
    }
	}

	#########################
	### Sincroniza Contas ###
	#########################

	function sincronizaVoipContas () {
    if (!$this->voip_contas) $this->voip_contas = controle::instanciaObjeto("voip_contas", $this->bancodedados);
    if (!$this->voip_asterisk) $this->voip_asterisk = controle::instanciaObjeto("voip_asterisk", $this->bancodedados);
    $voip_contas = $this->voip_contas->buscaVoipContasNaoSincronizadas();
    if ($voip_contas) {
      $this->voip_asterisk->sincronizaVoipContas($voip_contas);
      foreach ($voip_contas AS $voip_conta) {
        $this->registraVoipContaSincronizacao($voip_conta["id"]);
        $this->voip_contas->registraComentario($voip_conta["id"], "<b>Ação:</b> Sincronizado<br>");
      }
    }
	}
	
	##################################
	### Sincroniza Contas Chamadas ###
	##################################
	
	function sincronizaVoipContasChamadas () {
    if (!$this->voip_contas) $this->voip_contas = controle::instanciaObjeto("voip_contas", $this->bancodedados);
    if (!$this->voip_asterisk) $this->voip_asterisk = controle::instanciaObjeto("voip_asterisk", $this->bancodedados);
    $voip_contas = $this->voip_contas->buscaVoipContasNaoSincronizadas();
    if ($voip_contas) {
      $this->voip_asterisk->sincronizaVoipContas($voip_contas, "chamadas");
      foreach ($voip_contas AS $voip_conta) $this->voip_contas->registraComentario($voip_conta["id"], "<b>Ação:</b> Sincronizado &raquo; Chamadas<br>");
    }
	}
	
	#####################################
	### Solicita Contas Sincronizacao ###
	#####################################
  
	function solicitaVoipContaSincronizacao () {
    if (!$this->voip_contas) $this->voip_contas = controle::instanciaObjeto("voip_contas", $this->bancodedados);
    $this->voip_contas->solicitaVoipContaSincronizacao();
	}
	
}

?>
