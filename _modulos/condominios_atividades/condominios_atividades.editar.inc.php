<script type="text/javascript" src="rds.js.php?m=condominios_atividades"></script>
<form name="adicionar" method="POST" onSubmit="return validaAtividade(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Editar&nbsp;</b></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Condomínio:</strong></td>
        <td width="80%">[condominio]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Responsável:</strong></td>
        <td>[responsavel]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Início:</strong></td>
        <td><input name="inicio" id="inicio" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onclick="" onkeypress="return formataData(this, event)" onblur="return formataData(this, event)" style="width:135px" value="[inicio]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Término:</strong></td>
        <td><input name="termino" id="termino" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onclick="" onkeypress="return formataData(this, event)" onblur="return formataData(this, event)" style="width:135px" value="[termino]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Atividades:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[atividades]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="conteudo" id="conteudo" class="textBrancoAzulEscuro11" rows="5" cols="75" style="width: 100%">[conteudo]</textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
<script type="text/javascript">
  if (window.opener && window.opener.buscaAtividades) {
    window.opener.buscaAtividades([condominio_id]);
  }
</script>
