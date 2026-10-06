<form name="adicionar" method="POST" onSubmit="return validaClienteNegativacaoAdicionar(this)" action="[action]">
	<table border="0" cellspacing="1" cellpadding="3" width="100%">
		<tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Consultar&nbsp;</b></td></tr>
	</table>
	<table width="100%" border="0" cellspacing="1" cellpadding="3">
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td width="20%"><strong>&nbsp;Cliente:</strong></td>
	    <td>[cliente_nome]</td>
	  </tr>
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td width="20%"><strong>&nbsp;CPF/CNPJ:</strong></td>
	    <td>[cliente_cpf_cnpj]</td>
	  </tr>
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td width="20%"><strong>&nbsp;Telefones:</strong></td>
	    <td>[cliente_telefones]</td>
	  </tr>
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td width="20%"><strong>&nbsp;Erro:</strong></td>
	    <td style="color:#FF0000">[erro]</td>
	  </tr>
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td colspan="2"  height="40" align="center">[botoes]</td>
	  </tr>
	</table>
</form>
