<form name="adicionar" method="POST" onSubmit="return validaClienteRetorno(this);" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Cliente:</strong></td>
        <td>[cliente]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Usuário:</strong></td>
        <td>[usuario]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Data / Período:</strong></td>
        <td>
        	<table border="0" cellpadding="0" cellspacing="0" width="100%">
        		<tr>
        			<td><input name="data" id="data" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="37" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"></td>
        			<td width="5">&nbsp;</td>
        			<td width="70">[periodo]</td>
        		</tr>
        	</table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Comentário:</strong></td>
        <td><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="8" cols="51"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
