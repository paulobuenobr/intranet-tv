<form name="adicionar" method="POST" onSubmit="return validaRecuperacaoScript(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td>
      <table border="0" cellspacing="1" cellpadding="3" width="100%">
        <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Adicionar&nbsp;</strong></td></tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><strong>&nbsp;Título:</strong></td>
          <td><input name="titulo" id="titulo" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="80"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Sistema:</strong></td>
          <td>[sistema]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Tipo:</strong></td>
          <td>[tipo]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Conteúdo:</strong></td>
          <td><textarea name="conteudo" id="conteudo" class="textBrancoAzulEscuro11" rows="20" cols="80"></textarea></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Formato:</strong></td>
          <td>[formato]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Status:</strong></td>
          <td>[status]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="40" colspan="2" align="center">[botoes]</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
