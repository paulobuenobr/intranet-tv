<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Finalizar&nbsp;</b></td></tr>
</table>
<script type="text/javascript" src="rds.js.php?m=ajustes_financeiros"></script>
<form name="cancelar" method="POST" action="[action]" onSubmit="return validaFinalizar(this)">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2">
			<p>Você deseja realmente <b>finalizar</b> este ajuste financeiro?</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="35%"><strong>&nbsp;Competência &raquo; Término:</strong></td>
    <td width="65%"><input name="competencia_termino" id="competencia_termino" type="text" class="inputBrancoAzulEscuro11" style="width:150px" maxlength="10" onkeypress="return formataCompetencia(this, event)" onblur="return formataCompetencia(this, event)" value="[competencia_termino]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><strong>&nbsp;Comentário:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="7" cols="85" style="width:100%"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center">[botoes]</td>
  </tr>
</table>
</form>