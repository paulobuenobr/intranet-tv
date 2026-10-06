<?

if (array_key_exists("a", $_GET)) {

	global $bancodedados;
	
  if ($_GET["a"] == "verifica_ip_servidor_existente" && $_GET["endereco_ip"]) {

    $query = "
      SELECT
        sel_nome as nome
      FROM
        tab_servidores_links
      WHERE
        sel_endereco_ip = '{$_GET["endereco_ip"]}'
      LIMIT 0, 1";
    $query = $bancodedados->query($query);
    $link = $bancodedados->resultadoArray($query);
    if ($link) {
      $mensagem = "Este Endereço IP já está sendo utilizado pelo Link \"".addslashes($link["nome"])."\".";
      print "alert('$mensagem');";
      print "document.getElementById(\"endereco_ip\").value = '';";
      print "document.getElementById(\"endereco_ip\").focus();";
    } else {
      $query = "
        SELECT
  	      tab_servidores.ser_nome AS nome
        FROM
          tab_servidores
          INNER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
        WHERE
    	   tab_servidores.ser_id <> {$_GET["servidor"]} AND
  	     CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) = '{$_GET["endereco_ip"]}'
  	   	LIMIT 0, 1";
      $query = $bancodedados->query($query);
      $servidor = $bancodedados->resultadoArray($query);
      if ($servidor) {
        $mensagem = "Este Endereço IP já está sendo utilizado pelo servidor \"".addslashes($servidor["nome"])."\".";
        print "alert('$mensagem');";
        print "document.getElementById(\"endereco_ip\").value = '';";
        print "document.getElementById(\"endereco_ip\").focus();";
      } else {
        $query = "
           SELECT
  	         CONCAT(tab_servidores.ser_nome,' (', tab_servidores_interfaces.sei_nome, ', ', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip, ')') AS nome
           FROM
             tab_servidores_interfaces
             INNER JOIN tab_servidores ON (tab_servidores_interfaces.ser_id = tab_servidores.ser_id)
           WHERE
  	         CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip) = '{$_GET["endereco_ip"]}'
  	       LIMIT 0, 1";
        $query = $bancodedados->query($query);
        $interface = $bancodedados->resultadoArray($query);
        if ($interface) {
          $mensagem = "Este Endereço IP já está sendo utilizado pela interface \"".addslashes($interface["nome"])."\".";
          print "alert('$mensagem');";
          print "document.getElementById(\"endereco_ip\").value = '';";
          print "document.getElementById(\"endereco_ip\").focus();";
        } else {
          $query = "
             SELECT
    	         tab_clientes.cli_nome AS nome
             FROM
             	 tab_clientes_pontos
             	 INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
             	 INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
             WHERE
               tab_clientes_pontos.clp_ip = '{$_GET["endereco_ip"]}'
             LIMIT 0, 1";
          $query = $bancodedados->query($query);
          $cliente_ponto = $bancodedados->resultadoArray($query);
          if ($cliente_ponto) {
            $mensagem = "Este Endereço IP já está sendo utilizado pelo cliente \"".addslashes($cliente_ponto["nome"])."\".";
            print "alert('$mensagem');";
            print "document.getElementById(\"endereco_ip\").value = '';";
            print "document.getElementById(\"endereco_ip\").focus();";
          } else {
	          $query = "
	          	SELECT
	    	         tab_clientes.cli_nome AS nome
	             FROM
	             	 tab_clientes_autenticacoes
	             	 INNER JOIN tab_assinaturas ON (tab_clientes_autenticacoes.ass_id = tab_assinaturas.ass_id)
	             	 INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
	             WHERE
	               cau_endereco_ip = '{$_GET["endereco_ip"]}' OR
	               cau_equipamento_ip = '{$_GET["endereco_ip"]}'
	             LIMIT 0, 1";
	          $query = $bancodedados->query($query);
	          $cliente_autenticacao = $bancodedados->resultadoArray($query);
	          if ($cliente_autenticacao) {
	            $mensagem = "Este Endereço IP já está sendo utilizado pelo cliente \"".addslashes($cliente_autenticacao["nome"])."\".";
	            print "alert('$mensagem');";
	            print "document.getElementById(\"endereco_ip\").value = '';";
	            print "document.getElementById(\"endereco_ip\").focus();";
	          }           	
          }
        }
      }
    }
    
  } elseif ($_GET["a"] == "buscar_prefixo_interface") {

    $query = "
      SELECT
  	    sei_prefixo_ip as prefixo
      FROM
        tab_servidores_interfaces
      WHERE
  	    sei_id = {$_GET["interface"]}";
    $query = $bancodedados->query($query);
    $prefixo = $bancodedados->resultadoArray($query);
    if ($prefixo) {
      $prefixo = explode('.', $prefixo["prefixo"]);
      print "document.getElementById(\"prefixo_ip_1\").value = '{$prefixo[0]}';";
      print "document.getElementById(\"prefixo_ip_2\").value = '{$prefixo[1]}';";
      print "document.getElementById(\"prefixo_ip_3\").value = '{$prefixo[2]}';";
    }
  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function verificaEnderecoIPExistenteServidor(endereco_ip, servidor, servidor_interface) {
  url = "rds.js.php?m=servidores&a=verifica_ip_servidor_existente&endereco_ip=" + endereco_ip.value +"&interface=" + servidor_interface.value + "&servidor=" + servidor;
	RDSCarregar("servidores_verifica_ip_servidor_existente", url);
}

function mudaInterface(servidor_interface) {
 	servidor_interface.form.endereco_ip.value = "";
 	servidor_interface.form.endereco_ip.disabled = !servidor_interface.value;
 	if (servidor_interface) {
    url = "rds.js.php?m=servidores&a=buscar_prefixo_interface&interface=" + servidor_interface.value;
  	RDSCarregar("servidores_buscar_prefixo_interface", url);
 	} else {
   	servidor_interface.form.prefixo_ip_1.value = "";
   	servidor_interface.form.prefixo_ip_2.value = "";
   	servidor_interface.form.prefixo_ip_3.value = "";
 	}
}

function validaServidor(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.nome.value) {
		erro_vazio[erro_vazio.length] = "Nome";
	}
	if (!formulario.tipo.value) {
		erro_vazio[erro_vazio.length] = "Tipo";
	}
	if (!formulario.servidor_interface.value) {
		erro_vazio[erro_vazio.length] = "Interface";
	}
	endereco_ip = formulario.prefixo_ip_1.value + "." +
								formulario.prefixo_ip_2.value + "." +
								formulario.prefixo_ip_3.value + "." +
								formulario.endereco_ip.value;
	if (endereco_ip == "...") {
		erro_vazio[erro_vazio.length] = "Endereço IP";
	} else if (!validaMascaraIP(endereco_ip)) {
		erro_invalido[erro_invalido.length] = "Endereço IP";
	}
	if (!formulario.sistema_operacional.value) {
		erro_vazio[erro_vazio.length] = "Sistema Operacional";
	}
	if (!formulario.processador_modelo.value) {
		erro_vazio[erro_vazio.length] = "Processador (Modelo)";
	}
	if (!formulario.processador_clock.value) {
		erro_vazio[erro_vazio.length] = "Processador (Clock)";
	} else if (isNaN(formulario.processador_clock.value)) {
		erro_invalido[erro_invalido.length] = "Processador (Clock)";
	}
	if (!formulario.hd_tipo.value) {
		erro_vazio[erro_vazio.length] = "HD (Tipo)";
	}
	if (!formulario.hd_tamanho.value) {
		erro_vazio[erro_vazio.length] = "HD (Tamanho)";
	} else if (isNaN(formulario.hd_tamanho.value)) {
		erro_invalido[erro_invalido.length] = "HD (Tamanho)";
	}
	if (!formulario.memoria_tipo.value) {
		erro_vazio[erro_vazio.length] = "Memória (Tipo)";
	}
	if (!formulario.memoria_tamanho.value) {
		erro_vazio[erro_vazio.length] = "Memória (Tamanho)";
	} else if (isNaN(formulario.memoria_tamanho.value)) {
		erro_invalido[erro_invalido.length] = "Memória (Tamanho)";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function validaMigrar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.servidor.value) erro_vazio[erro_vazio.length] = "Servidor";
	return apresentaErros(erro_vazio, erro_invalido);
}

function validaClonar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.servidor.value) erro_vazio[erro_vazio.length] = "Servidor";
	return apresentaErros(erro_vazio, erro_invalido);
}

<?

	if (false) { ?></script><? }

}

?>
