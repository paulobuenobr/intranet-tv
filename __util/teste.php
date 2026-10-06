<PRE>
<?

require_once("../global.inc.php");

$util = new Util($bancodedados);

$util->testar();

class Util {

	private $bancodedados;

	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}
	
	function testar() {
		
		$data_ativacao = '2022-03-31';
		
		$mes = substr($data_ativacao, 5, 2);
		$ano = substr($data_ativacao, 0, 4);

		//print date("Y-m-01", strtotime("$ano-$mes-01 - 1 month"));
		print date("Y-m-d", strtotime("$data_ativacao - 1 month"));

	}
 
}

?>
