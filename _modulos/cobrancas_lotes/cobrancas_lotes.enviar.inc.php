<form name="enviar" method="POST" action="[action]">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center">
			<p>Você deseja realmente <b>enviar</b> este lote?</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="17%"><strong>&nbsp;Data/Hora de Envio:</strong></td>
    <td width="83%"><input name="data_hora_envio" id="data_hora_envio" type="text" class="inputBrancoAzulEscuro11" style="width:150px" maxlength="10" onKeyPress="return formataDataHora(this, event)" onBlur="return formataDataHora(this, event)" value="[data_hora_envio]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><strong>&nbsp;Comentário:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="7" style="width:100%"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center">[botoes]</td>
  </tr>
</table>
</form>
