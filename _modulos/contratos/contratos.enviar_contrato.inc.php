<script type="text/javascript" src="rds.js.php?m=contratos" id="contratos.js"></script>
<form name="comercial_enviar" method="POST" action="[action]" onSubmit="return validaContratoComercialEnviar(this);">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11">[acoes]</tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><b>Atenção:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center">
      [mensagem]
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="15%"><b>&nbsp;E-mail(s):</b></td>
    <td width="85%"><input name="emails" id="emails" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="75" value="[emails]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><b>&nbsp;Comentário:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="7" cols="105"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" [display_adicionar_emails]>
    <td colspan="2"  align="left">
    	<input type="checkbox" name="adicionar_emails" id="adicionar_emails" value="true"><label for="adicionar_emails">Adicionar e-mail(s) ao cadastro do cliente</label>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center">[botoes]</td>
  </tr>
</table>
</form>
