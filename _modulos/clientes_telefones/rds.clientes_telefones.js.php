<?

if ($_GET["a"]){

	global $bancodedados;
	
	if ($_GET["a"] == "buscar_telefones" && $_GET["cliente"]) {

		/**
		 * @var seguranca $seguranca
		 */
		$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
		$clientes = controle::instanciaObjeto("clientes", $bancodedados, $seguranca->permissoes, false);
		
		$listagem = $clientes->geraClientesTelefonesListagem($_GET["cliente"]);
	  $botao_adicionar = $clientes->geraBotaoAdicionarTelefones($_GET["cliente"]);
	  print "if (document.getElementById(\"clientes_telefones_listagem\")) document.getElementById(\"clientes_telefones_listagem\").innerHTML = '".addslashes($listagem)."';";
	  print "if (document.getElementById(\"clientes_telefones_adicionar\")) document.getElementById(\"clientes_telefones_adicionar\").innerHTML = '".addslashes($botao_adicionar)."';";
		
	} elseif ($_GET["a"] == "muda_assinatura" && $_GET["assinatura_id"]) {
		
		/**
		 * @var seguranca $seguranca
		 */
		$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
		$clientes_telefones = controle::instanciaObjeto("clientes_telefones", $bancodedados, $seguranca->permissoes, false);
		
		$dados_assinatura = $clientes_telefones->buscaDadosAssinatura($_GET["assinatura_id"]);

		if ($dados_assinatura["parametros"]["telefonia_tabela_venda_id"]) { 
			print "document.getElementById('tabela_venda').innerHTML = '".$clientes_telefones->buscaTabelaVenda($dados_assinatura["parametros"]["telefonia_tabela_venda_id"])."';";
		} else {
			print "document.getElementById('tabela_venda').innerHTML = '".$this->geraSelect($this->buscaTabelasVenda(), null, true, "tabela_venda", "100%")."';";
		}

		if ($dados_assinatura["parametros"]["telefonia_tabela_roteamento_id"]) { 
			print "document.getElementById('tabela_roteamento').innerHTML = '".$clientes_telefones->buscaTabelaVenda($dados_assinatura["parametros"]["telefonia_tabela_roteamento_id"])."';";
		} else {
			print "document.getElementById('tabela_roteamento').innerHTML = '".$this->geraSelect($this->buscaTabelasRoteamento(), null, true, "tabela_roteamento", "100%")."';";
		}
		
	}
	
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function verTelefone(telefone) {
	if (telefone) {
    abrir("clientes_telefones", "?m=clientes_telefones&a=ver&i=" + telefone, 525, 460, 1);
	}
}

function adicionarTelefone(cliente_id){
	if (cliente_id) {
	    abrir("clientes_telefones", "?m=clientes_telefones&a=adicionar&i=" + cliente_id , 525, 460, 1);
	}
}

function buscaTelefones(id) {
	if (id) {
		url = "rds.js.php?m=clientes_telefones&a=buscar_telefones&cliente=" + id;
		RDSCarregar("clientes_telefones_buscar_telefones", url);
	}
}

function validaAssinaturaClienteTelefone(formulario){
	  var erro_vazio = new Array();
	  var erro_invalido = new Array();

	  if (formulario.assinatura && !formulario.assinatura.value) {
	    erro_vazio[erro_vazio.length] = "Assinatura";
	  }

	  if (formulario.tabela_venda && !formulario.tabela_venda.value) {
		    erro_vazio[erro_vazio.length] = "Tabela de Venda";
		}

	  if (formulario.tabela_roteamento && !formulario.tabela_roteamento.value) {
		    erro_vazio[erro_vazio.length] = "Tabela de Roteamento";
		}
	  
	  return apresentaErros(erro_vazio, erro_invalido);
}

function validaInserirCredito(formulario) {
	  var erro_vazio = new Array();
	  var erro_invalido = new Array();

	  if (formulario.valor && !formulario.valor.value) {
	    erro_vazio[erro_vazio.length] = "Valor";
	  }
	
	  return apresentaErros(erro_vazio, erro_invalido);
}

function mudaAssinatura(assinatura_id) {
	if (assinatura_id) {
		url = "rds.js.php?m=clientes_telefones&a=muda_assinatura&assinatura_id=" + assinatura_id;
		RDSCarregar("clientes_telefones_muda_assinatura", url);
	} else {
		document.getElementById('tabela_venda').innerHTML = '';
		document.getElementById('tabela_roteamento').innerHTML = '';
	}
}

<?

	if (false) { ?></script><? }

}

?>
