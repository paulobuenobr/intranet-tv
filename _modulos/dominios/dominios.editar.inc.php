<script type="text/javascript" src="rds.js.php?m=dominios"></script>
<form name="editar" method="POST" onSubmit="return validaClienteDominio(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Cliente:</strong></td>
        <td id="cliente">[cliente]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Plano:</strong></td>
        <td width="80%">[plano]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Origem:</strong></td>
        <td><select name="origem" id="origem" class="inputBrancoAzulEscuro11" [bloqueio_ativo]>[origem]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Responsável:</strong></td>
        <td><input name="responsavel" id="responsavel" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[responsavel]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;ID Fapesp:</strong></td>
        <td><input name="fapesp" id="fapesp" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" value="[fapesp]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Domínio:</strong></td>
        <td><input name="dominio" id="dominio" type="text" class="inputBrancoAzulEscuro11" size="37" maxlength="250" value="[dominio]" [bloqueio_ativo]> <input type="button" name="checar_dominio" id="checar_dominio" class="buttonAzulEscuroBranco11" value="Checar" onClick="checaDominio(this.form.dominio.value);" [bloqueio_ativo]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;E-mail(s):</strong></td>
        <td><input name="emails" id="emails" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="250" value="[emails]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Comentários:</strong></td>
        <td><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" rows="7" cols="51">[comentarios]</textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td>[status]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>

<script type="text/javascript">
  if (window.opener && window.opener.buscaDominios) {
    window.opener.buscaDominios([cliente_id]);
  }
</script>
