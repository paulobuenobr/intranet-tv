<form action="?origem=[origem]&m=contratos" name="buscar" method="POST" onsubmit="return validaBusca(this)">
<table border="0" cellspacing="1" cellpadding="3" align="center" width="320">
  <tr >
    <td colspan="2" style="font-size:14px;font-weight:bold;text-align:center">Análise de Contrato</td>
  </tr>
  <tr >
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr >
    <td colspan="2" style="text-align:justify">
      Caro cliente,<br>
      <br>
      Para visualizar seu contrato, preencha os campos abaixo e clique em Continuar.<br>
      <br>
    </td>
  </tr>
  <tr>
    <td width="150"><div style="width:150">&raquo; Código do Contrato:</div></td>
    <td width="170"><input name="contrato" id="contrato" maxlength="20" type="text" class="formInput" style="width:170px" value="[contrato]" onKeyPress="return formataContrato(this, event)" onBlur="return formataContrato(this, event)"></td>
  </tr>
  <tr>
    <td>&raquo; CPF/CNPJ do Titular:</td>
    <td><input name="cpf_cnpj" id="cpf" maxlength="18" type="text" class="formInput" style="width:170px" value="[cpf_cnpj]" onKeyPress="return formataCPFCNPJ(this, event)" onBlur="return formataCPFCNPJ(this, event)"></td>
  </tr>
  <tr>
  	<td colspan="2" align="center">
      <br>
  		<input type="submit" class="formButton" value="Continuar" style="width:75px">
 	</tr>
  <tr>
    <td colspan="2">
      <br>
      Em caso de dúvidas, entre em contato através do telefone <b>(48) [telefone]</b>.<br>
      <br>
      Atenciosamente,<br>
      <br>
      [origem_nome]
    </td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
</table>
</form>

<script type="text/javascript">

	document.buscar.contrato.focus();

	function validaBusca(formulario) {
	  var erro_vazio = new Array();
	  var erro_invalido = new Array();

	  if (!formulario.contrato.value) {
	    erro_vazio[erro_vazio.length] = "Código do Contrato";
	  }

	  if (!formulario.cpf.value) {
	    erro_vazio[erro_vazio.length] = "CPF/CNPJ";
	  } else if (!validaCPF(formulario.cpf.value) && !validaCNPJ(formulario.cpf.value)) {
	    erro_invalido[erro_invalido.length] = "CPF/CNPJ";
	  }

	  return apresentaErros(erro_vazio, erro_invalido);
	}

</script>
