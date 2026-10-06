<?

class integracao_central_telefonica {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->acao = $_GET["a"];
    if (method_exists($this, $this->acao)) {
			$this->{$this->acao}();
    } else {
			$this->erro = 3;
    }
  }

  ##############
  ### Discar ###
  ##############

  function discar() {
    if ($_GET["numero"] && strlen($_GET["numero"]) == 14 && $_GET["destino"]) {
  		$central_telefonica = controle::instanciaObjeto("central_telefonica.asterisk", $this->bancodedados);
  		$central_telefonica->discarParaDestino($_GET["numero"], $_GET["destino"]);
  		die("OK");
    }
  }

}

?>
