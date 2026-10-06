<?

class cron_cancelamentos {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
  }

  ##############################
  ### Gera Cobranças Avulsas ###
  ##############################

  function finalizarCancelamentos() {
    $query = "
      UPDATE
        tab_cancelamentos
      SET
        cas_id = 3,
        can_termino = NOW()
      WHERE
        cas_id <= 2 AND
        can_inicio <= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
    $this->bancodedados->query($query);
  }

}

?>
