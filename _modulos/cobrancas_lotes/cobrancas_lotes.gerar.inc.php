<script type="text/javascript" src="rds.js.php?m=cobrancas_lotes"></script>
<form name="gerar" method="POST" action="[action]" onSubmit="return validaGeraCobrancaLote(this)">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Gerar&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="15%"><strong>&nbsp;Vencimento:</strong></td>
        <td width="85%"><input name="vencimento" id="vencimento" type="text" class="inputBrancoAzulEscuro11" maxlength="7" onKeyPress="return formataCompetencia(this, event);" onBlur="return formataCompetencia(this, event);" style="width:100%"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Formatos:</strong></td>
        <td>[formatos]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="3" style="width:100%"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
   </table></td>
  </tr>
</table>
</form>
