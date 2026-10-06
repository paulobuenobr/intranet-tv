<script type="text/javascript" src="rds.js.php?m=clientes_negativacoes"></script>

<form name="adicionar" method="POST" onSubmit="return validaClienteNegativacaoBaixar(this)" action="[action]">

	<table border="0" cellspacing="1" cellpadding="3" width="100%">
		<tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Adicionar&nbsp;</b></td></tr>
	</table>
	<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
	  <tr>
	    <td>
		    <table width="100%" border="0" cellspacing="1" cellpadding="3">
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td width="25%"><strong>&nbsp;ID:</strong></td>
		        <td width="75%">[cliente_id]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td width="25%"><strong>&nbsp;Cliente:</strong></td>
		        <td width="75%">[cliente]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td width="25%"><strong>&nbsp;CPF/CNPJ:</strong></td>
		        <td width="75%">[cpf_cnpj]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="30" colspan="2"><strong>&nbsp;Negativação:</strong></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;&raquo; Código:</strong></td>
		        <td>[codigo]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;&raquo; Data de inclusão:</strong></td>
		        <td>[data_inclusao]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;&raquo; Data de baixa:</strong></td>
		        <td><input name="data_baixa" id="data_baixa" value="[data_baixa]" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10"  onkeypress="return formataData(this, event)" onblur="return formataData(this, event)" style="width: 135px"></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;&raquo; Status:</strong></td>
		        <td>[status]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" rows="4" cols="75" style="width: 100%"></textarea></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="40" colspan="2" align="center">[botoes]</td>
		      </tr>
		    </table>
	    </td>
	  </tr>
	</table>

</form>
