<script type="text/javascript" src="rds.js.php?m=clientes_origens"></script>
<form name="remover" method="POST" action="[action]" onSubmit="return validaOrigemCancelar(this)">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2">
		<p>Você deseja realmente <b>cancelar</b> esta origem?</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="200"><strong>&nbsp;Data &raquo; Cancelamento:</strong></td>
    <td><input name="data_cancelamento" id="data_cancelamento" type="text" class="inputBrancoAzulEscuro11" style="width:150px" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_cancelamento]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" align="center" colspan="2">[botoes]</td>
  </tr>
</table>
</form>
