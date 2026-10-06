<script type="text/javascript" src="rds.js.php?m=cobrancas"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Enviar&nbsp;</b></td></tr>
</table>
<form name="enviar" method="POST" action="[action]" onSubmit="return validaCobrancasEnviar(this)">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center">
      Será enviada uma mensagem para o(s) e-mail(s) abaixo com um link para o boleto.
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="15%"><b>&nbsp;E-mail(s):</b></td>
    <td width="85%"><input name="emails" id="emails" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="75" value="[emails]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30" style="color:#FF0000"><b>&nbsp;Mensagem no corpo do e-mail:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="7" cols="105"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2"  align="left">
    	<table cellspacing="0" cellpading="0" border="0"><tr><td width="10"><input type="checkbox" name="adicionar_emails" id="adicionar_emails" value="true"></td><td class="txtAzulEscuro11"><label for="adicionar_emails">Adicionar e-mail(s) ao cadastro do cliente</label></td></tr></table>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center">[botoes]</td>
  </tr>
</table>
</form>
