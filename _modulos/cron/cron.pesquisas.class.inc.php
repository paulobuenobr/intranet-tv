<?

class cron_pesquisas {

  var $bancodedados;
  var $pesquisas;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->pesquisas = controle::instanciaObjeto("pesquisas", $bancodedados, null, false);
  }

  ############################
  ### Ajusta Inadimplentes ###
  ############################

  function gerarAplicacoes() {
    $this->gerarAplicacoesSatisfacaoResidencial();
  }
  
  #######################################
  ### Pesquisa Satisfação Residencial ###
  #######################################
  
  function gerarAplicacoesSatisfacaoResidencial() {
    $servico_id = 28;
    $pesquisa_id = 3;
    $quantidade = 15;
    $chamado_categoria_id = 1055;
    $clientes = $this->buscaClientesServicoSemAplicacao($servico_id, $pesquisa_id, $quantidade);
    if ($clientes) {
      foreach ($clientes as $cliente) {
        $this->pesquisas->inserePesquisa($cliente["cliente_id"], $cliente["contrato_id"], $pesquisa_id, $chamado_categoria_id);
      }
    }
  }
  
  ##############
  ### Buscas ###
  ##############
  
  function buscaClientesServicoSemAplicacao($servico_id, $pesquisa_id, $quantidade) {
    $query = "
      SELECT
        c.cli_id AS cliente_id,
        c.cor_id AS contrato_id
      FROM
        tab_contratos c
      WHERE
        c.pls_id = $servico_id AND
        c.cor_pai IS NULL AND
        c.crs_id IN (19, 20, 21) AND
        c.cor_data_ativacao <= '2010-01-31' AND
        c.cor_id NOT IN (SELECT p.cor_id FROM tab_pesquisas_aplicacoes p WHERE p.pes_id = $pesquisa_id AND p.cli_id = c.cli_id)
      LIMIT
        0, $quantidade";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $clientes[] = $dados;
    }
    return $clientes;
  }
  
}

?>
