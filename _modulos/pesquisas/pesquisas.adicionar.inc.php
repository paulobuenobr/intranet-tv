<script type="text/javascript" src="rds.js.php?m=pesquisas"></script>
<form name="adicionar" id="adicionar" method="POST" onSubmit="return validaAdicionarPesquisa(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Adicionar&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="15%"><b>&nbsp;Descrição:</b></td>
        <td width="85%"><input name="descricao" id="descricao" type="text" style="width:100%" class="inputBrancoAzulEscuro11" size="50" maxlength="80"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Texto Inicial</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="texto_inicial" id="texto_inicial" class="textBrancoAzulEscuro11" style="width:100%" rows="5" cols="70">[texto_inicial]</textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Texto Final</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="texto_final" id="texto_final" class="textBrancoAzulEscuro11" style="width:100%" rows="5" cols="70">[texto_final]</textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
