<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Inserir Crédito&nbsp;</strong></td></tr>
</table>
<script type="text/javascript" src="rds.js.php?m=clientes_telefones"></script>
<form name="inserir_credito" method="POST" action="[action]" onSubmit="return validaInserirCredito(this)">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td>
	    <table width="100%" border="0" cellspacing="1" cellpadding="3">
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td width="25%"><strong>&nbsp;Cliente:</strong></td>
	        <td width="75%">[cliente_nome]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td height="30" colspan="2"><strong>&nbsp;Dados do Serviço:</strong></td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Plano:</strong></td>
	        <td>[assinatura]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Número:</strong></td>
	        <td>[numero]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Saldo:</strong></td>
	        <td>[telefone_saldo]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td height="30" colspan="2"><strong>&nbsp;Inserir Crédito:</strong></td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Valor:</strong></td>
       	  <td><input name="valor" id="valor" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" style="width:50%"></td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Observação:</strong></td>
       	  <td><input name="observacao" id="observacao" type="text" maxlength="256" class="inputBrancoAzulEscuro11" style="width:100%"></td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td height="40" colspan="2" align="center">[botoes]</td>
	      </tr>
	    </table>
    </td>
  </tr>
</table>
</form>
