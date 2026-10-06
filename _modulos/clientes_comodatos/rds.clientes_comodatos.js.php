<?

if ($_GET["a"]) {
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$clientes_comodatos = controle::instanciaObjeto("clientes_comodatos", $bancodedados, $seguranca->permissoes, false);
	
	if($_GET["a"] == "atualizar_equipamentos_comodatos"){
		
		 $listagem = $clientes_comodatos->geraListagemClientesComodatos($_GET["cliente"], $_GET["exibir_inativos"] == "true");
		 print "if (document.getElementById(\"equipamentos_comodatos\")) document.getElementById(\"equipamentos_comodatos\").innerHTML = '".addslashes($listagem)."';";
		
	}
		
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function verClientesComodatos(cliente_id) {
	
	if (cliente_id) {
		abrir("clientes_comodatos", "?m=clientes_comodatos&a=ver&i=" + cliente_id , 525, 500, 1);
	}
	
}

function adicionarEquipamentoComodato(cliente_id){
	
	if (cliente_id) {
	    abrir("clientes_comodatos", "?m=clientes_comodatos&a=adicionar&i=" + cliente_id , 525, 500, 1);
	}
	
}

function validaEquipamentoComodato(formulario) {
	
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	
	return apresentaErros(erro_vazio, erro_invalido);

}

function validaEquipamentoComodatoSubstituir(formulario) {
	
	var erro_vazio = new Array();
	var erro_invalido = new Array();

  	if (formulario.mac_address.value) {

		if (!formulario.modelo.value) {
			erro_vazio[erro_vazio.length] = "Modelo";
		}

		if (!formulario.mac_address.value) {
			erro_vazio[erro_vazio.length] = "MAC";
		} else if (!/^[a-fA-F0-9]{12}$/.test(formulario.mac_address.value)) {
			erro_invalido[erro_invalido.length] = "MAC";
		} 
		
	}
	
	return apresentaErros(erro_vazio, erro_invalido);

}

function atualizaEquipamentosComodatos(cliente, exibir_inativos){

	url = "rds.js.php?m=clientes_comodatos&a=atualizar_equipamentos_comodatos&cliente=" + cliente + "&exibir_inativos=" + exibir_inativos;
	RDSCarregar("clientes_comodatos_atualizar_equipamentos_comodatos", url);
	
}
<?

		if (false) { ?></script><? }

	}

?>
