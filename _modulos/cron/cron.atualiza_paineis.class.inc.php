<?

// Geração dos Painéis
require_once(DIR_PAINEL."painel.placar.geral.inc.php");
require_once(DIR_PAINEL."painel.placar.comercial.inc.php");
require_once(DIR_PAINEL."painel.placar.administrativo.inc.php");
require_once(DIR_PAINEL."painel.placar.financeiro.inc.php");
//require_once(DIR_PAINEL."painel.placar.tecnico.inc.php");

class cron_atualiza_paineis {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
  }

  ###########################
  ### Atualiza Servidores ###
  ###########################

  function atualizaPaineis() {
    //$this->limpaPaineis();
    $this->geraPaineis(date("d"), date("m"), date("Y"));
  }

  ########################
  #### Limpa Painéis #####
  ########################

  function limpaPaineis() {
    $query = "DELETE FROM tab_placares WHERE plb_id != 5 AND (UNIX_TIMESTAMP(plc_geracao + 0) < UNIX_TIMESTAMP(NOW() + 0) - ".PAINEL_DELAY." AND DATE_FORMAT(plc_data, '%Y%m') >= DATE_FORMAT(NOW(), '%Y%m')) OR (UNIX_TIMESTAMP(plc_geracao + 0) < UNIX_TIMESTAMP(NOW() + 0) - ".PAINEL_DELAY_ANTERIORES." AND DATE_FORMAT(plc_data, '%Y%m') < DATE_FORMAT(NOW(), '%Y%m'))";
    $query = $this->bancodedados->query($query);
  }

  ########################
  ##### Gera Painéis #####
  ########################

  function geraPaineis($dia, $mes, $ano) {
    // Administrativo
    $conteudo = addslashes(geraPainelPlacarConteudoAdministrativo($dia, $mes, $ano));
    $this->armazenaPainel(1, $dia, $mes, $ano, trim($conteudo));
    // Comercial
    $conteudo = addslashes(geraPainelPlacarConteudoComercial($dia, $mes, $ano));
    $this->armazenaPainel(2, $dia, $mes, $ano, trim($conteudo));
    // Financeiro
    $conteudo = addslashes(geraPainelPlacarConteudoFinanceiro($mes, $ano));
    $this->armazenaPainel(3, $dia, $mes, $ano, trim($conteudo));
    // Técnico
    //$conteudo = addslashes(geraPainelPlacarConteudoTecnico($dia, $mes, $ano));
    //$this->armazenaPainel(4, $dia, $mes, $ano, trim($conteudo));
  }

  ########################
  ### Armazena Painéis ###
  ########################

  function armazenaPainel($setor, $dia, $mes, $ano, $conteudo) {
    if ($this->buscaPlacaresNoDia($setor, $dia, $mes, $ano) == 0) {
      $query = "INSERT INTO tab_placares (plb_id, plc_data, plc_geracao, plc_conteudo) VALUES ('$setor', '$ano$mes$dia', NOW(), '$conteudo')";
      $query = $this->bancodedados->query($query);
    } else {
      $query = "UPDATE tab_placares SET plc_conteudo = '$conteudo', plc_geracao = NOW() WHERE (plb_id = '$setor' AND plc_data = '$ano$mes$dia') LIMIT 1";
      $query = $this->bancodedados->query($query);
    }
  }

  function buscaPlacaresNoDia($setor, $dia, $mes, $ano) {
    $query = "
      SELECT
        plc_id AS id
      FROM
        tab_placares
      WHERE
        plb_id = '$setor' AND
        plc_data = '$ano$mes$dia'";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->linhas($query);
  }

}

?>
