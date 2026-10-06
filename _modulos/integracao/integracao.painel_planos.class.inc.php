<?

class integracao_painel_planos {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->acao = $_GET["a"] ? $_GET["a"] : "listar";
    if (method_exists($this, $this->acao)) {
			$this->{$this->acao}();
    } else {
			$this->erro = 3;
    }
  }

	##############
	### Listar ###
	##############
	
	function listar() {
	  $query = "
	    SELECT
	      tab_assinaturas.ass_id AS id,
	      DATE_FORMAT(tab_assinaturas.ass_data_ativacao, '%d/%m/%Y') AS data_ativacao,
	      tab_assinaturas.ass_mensalidade AS mensalidade,
	      tab_planos.pla_nome AS plano,
	      tab_planos_servicos.pls_nome AS servico
	    FROM
	      tab_assinaturas
	      INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
	      INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
	    WHERE
	      tab_assinaturas.cli_id = {$_GET["i"]} AND
	      tab_assinaturas.ast_id IN (2, 5)
	    ORDER BY
	      tab_assinaturas.ass_data_ativacao
	    LIMIT 0, 10";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["plano"] = preg_replace("/\\[[^\\]]*\\]/", "", $dados["plano"]);
      $dados["mensalidade"] = $this->formataDinheiro($dados["mensalidade"]);
      $planos[] = $dados;
    }
    $this->conteudo = serialize($planos);
	}

	#############
	### Geral ###
	#############
	
  function formataDinheiro($valor, $moeda = 'R$') {
    $valor = preg_replace("/[^0-9]/", "", intval($valor));
    $valor = number_format($valor / 100, 2, ",", ".");
    if ($moeda) $valor = $moeda." ".$valor;
    return $valor;
  }

	##############
	### Buscas ###
	##############
	
}

?>
