<script type="text/javascript" src="rds.js.php?m=contratos"></script>
<form name="cancelar" method="POST" action="[action]" onSubmit="return validaContratoCancelar(this)">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Cancelar&nbsp;</b></td></tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tbody style="display:[exibir_mensagem_vigencia]">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="color:#FF0000">
    <td colspan="2" align="center" colspan="2">
      <b>Atenção!</b><br>
      Este é um contrato com plano de fidelidade.<br>
      Vigência: [vigencia].
    </td>
  </tr>
  </tbody>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center">
      <p>Você deseja realmente <b>cancelar</b> este contrato?</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="25%"><strong>&nbsp;Data &raquo; Cancelamento:</strong></td>
    <td width="75%"><input name="data_cancelamento" id="data_cancelamento" type="text" class="inputBrancoAzulEscuro11" style="width:150px" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_cancelamento]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><strong>&nbsp;Comentário:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="7" cols="105"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center">[botoes]</td>
  </tr>
</table>
</form>
