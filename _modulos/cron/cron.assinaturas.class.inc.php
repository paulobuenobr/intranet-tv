<?

class cron_assinaturas {

  var $bancodedados;
  var $assinaturas;
  var $contratos;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->assinaturas = controle::instanciaObjeto("assinaturas", $bancodedados, null, false);
    $this->contratos = controle::instanciaObjeto("contratos", $bancodedados, null, false);
  }

  ###########################
  ### Atualiza Servidores ###
  ###########################

  function anulaAntigas() {
  	$assinaturas = $this->buscaAssinaturasAntigas();
    if ($assinaturas) {
      foreach ($assinaturas as $assinatura_id) {
      	$dados_assinatura = $this->assinaturas->buscaDadosAssinatura($assinatura_id);
        if ($dados_assinatura) {
        	$contrato_id = $this->buscaContratoAssinatura($assinatura_id);
          if ($contrato_id) {
          	$dados_contrato = $this->contratos->buscaDadosContrato($contrato_id);
            if ($dados_contrato) {
              $this->contratos->removerContrato($dados_contrato);
            }
          } else {
          	$this->assinaturas->removerAssinatura($dados_assinatura);
          }
        }
      }
    }
  }

  ########################
  ### Funções de Busca ###
  ########################

  function buscaAssinaturasAntigas() {
    $query = "
      SELECT
        ass_id AS id
      FROM
        tab_assinaturas
      WHERE
        ass_data_fechamento < DATE_SUB(NOW(), INTERVAL 3 MONTH) AND
        ast_id = 1
      ORDER BY
        ass_pai DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $assinaturas[] = $dados["id"];
    }
    $query = "
      SELECT
        ass_id AS id
      FROM
        tab_assinaturas
      WHERE
        ass_data_cadastro < DATE_SUB(NOW(), INTERVAL 3 MONTH) AND
        ass_data_fechamento IS NULL AND
        ast_id = 1
      ORDER BY
        ass_pai DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $assinaturas[] = $dados["id"];
    }
    return array_unique($assinaturas);
  }

  function buscaContratoAssinatura($assinatura_id) {
    $query = "
      SELECT
        cor_id AS id
      FROM
        rel_ass_cor_estacontido
      WHERE
        ass_id = $assinatura_id";
    $query = $this->bancodedados->query($query);
    if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      return $dados["id"];
    }
  }

}

?>
