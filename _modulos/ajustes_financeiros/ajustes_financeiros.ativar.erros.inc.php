<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Ativar&nbsp;</b></td></tr>
</table>
<script type="text/javascript" src="rds.js.php?m=ajustes_financeiros"></script>
<form name="ativar" method="POST" action="[action]" onSubmit="return validaAjusteAtivar(this)">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2">
      <br>
      Foi(ram) encontrado(s) o(s) seguinte(s) erro(s):<br>
      <br>
			<table cellpadding="0" cellspacing="0" border="0"><tr><td class="txtAzulEscuro11"><b>[erros]</b></td></tr></table>
			<br>
      [continuar]
    </td>
  </tr>
  <tbody id="grupo_formato">
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td height="30" colspan="2"><strong>&nbsp;Cobrança:</strong></td>
	  </tr>
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td width="20%"><strong>&nbsp;&raquo; Formato:</strong></td>
	    <td width="80%">[formato]</td>
	  </tr>
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td><strong>&nbsp;&raquo; Vencimento:</strong></td>
	    <td><input name="data_vencimento" id="data_vencimento" style="width:150px" type="text" class="inputBrancoAzulEscuro11" size="30" maxlength="10" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)"></td>
	  </tr>
  </tbody>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><strong>&nbsp;Comentário:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="4" cols="85" style="width:100%"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" align="center" colspan="2">[botoes]</td>
  </tr>
</table>
<input type="hidden" name="dia_vencimento" value="[dia_vencimento]">
<input type="hidden" name="vencimento_inicio" value="[vencimento_inicio]">
<input type="hidden" name="vencimento_maximo" value="[vencimento_maximo]">
</form>