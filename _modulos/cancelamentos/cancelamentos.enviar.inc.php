<script type="text/javascript" src="rds.js.php?m=cancelamentos"></script>
<form name="comercial_enviar" method="POST" action="[action]" onSubmit="return validaCancelamentoEnviar(this);">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Enviar Requerimento&nbsp;</b></td></tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><b>Atenção:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center">
      <p>Será enviada uma mensagem para o(s) e-mail(s) abaixo com o Requerimento de Rescisão de Contrato.</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="25%"><b>&nbsp;Email(s):</b></td>
    <td width="75%"><input name="emails" id="emails" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="75" value="[emails]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Data Cancelamento:</b></td>
    <td><input name="data_cancelamento" id="data_cancelamento" type="text" class="inputBrancoAzulEscuro11" style="width:150px" maxlength="10" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><b>&nbsp;Comentário:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="7" cols="105"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center">[botoes]</td>
  </tr>
</table>
</form>
