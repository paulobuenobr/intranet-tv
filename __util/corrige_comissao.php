<PRE>
<?

ignore_user_abort(true);
set_time_limit(0);

require_once ("../global.inc.php");

global $bancodedados;

$util = new util($bancodedados);
$util->executar();

class util {
	
	private $bancodedados;
	
	private $contratos;
	
	function util($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
		$this->contratos = controle::instanciaObjeto("contratos", $this->bancodedados, null, false);

	}
	
	function executar() {

		$query = $this->bancodedados->query("
			SELECT
				tab_vendas.ven_id AS venda_id,
				tab_vendas.cor_id AS contrato_id,
				tab_assinaturas.ass_id AS assinatura_id,
				tab_assinaturas.cli_id AS cliente_id
			FROM
				tab_vendas
				INNER JOIN tab_assinaturas ON (tab_vendas.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
			WHERE
				tab_vendas.ven_datahora >= '2023-01-01 00:00:00' AND
				tab_clientes.clo_id = 1 AND
				(SELECT COUNT(*) FROM tab_vendas_comissoes WHERE tab_vendas_comissoes.ven_id = tab_vendas.ven_id) = 0");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			print "{$dados["cliente_id"]}\n";
			
			$dados_cliente = $this->contratos->buscaDadosCliente($dados["cliente_id"]);
			$dados_assinatura = $this->contratos->buscaDadosAssinatura($dados["assinatura_id"]);
			
			$this->contratos->geraVenda($dados_cliente, $dados_assinatura, $dados["contrato_id"]);
			
		}

	}
	
}

?>
</PRE>
