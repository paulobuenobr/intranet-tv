<script type="text/javascript" src="rds.js.php?m=condominios_contatos"></script>
<form name="adicionar" method="POST" onSubmit="return validaContato(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><strong>&nbsp;Nome:</strong></td>
        <td width="75%"><input name="nome" id="nome" type="text" style="width:100%" class="inputBrancoAzulEscuro11" size="50" maxlength="40"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Endere&ccedil;o:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Condomínio:</strong></td>
        <td id="condominio_nome">[condominio]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bloco:</strong></td>
        <td>[bloco]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Complemento:</strong></td>
        <td><input name="complemento" id="complemento" type="text" style="width:50%" class="inputBrancoAzulEscuro11" size="50" maxlength="30"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Telefone:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Residencial:</strong></td>
        <td><input name="tel_residencial" id="tel_residencial" type="text" style="width:50%" class="inputBrancoAzulEscuro11" size="50" maxlength="14" onKeyPress="return formataTelefone(this,event);" onBlur="return formataTelefone(this,event);"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Comercial:</strong></td>
        <td><input name="tel_comercial" id="tel_comercial" type="text" style="width:50%" class="inputBrancoAzulEscuro11" size="50" maxlength="14" onKeyPress="return formataTelefone(this,event);" onBlur="return formataTelefone(this,event);"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Celular:</strong></td>
        <td><input name="tel_celular" id="tel_celular" type="text" style="width:50%" class="inputBrancoAzulEscuro11" size="50" maxlength="14" onKeyPress="return formataTelefone(this,event);" onBlur="return formataTelefone(this,event);"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;E-mail:</strong></td>
        <td><input name="email" id="email" type="text" style="width:100%" class="inputBrancoAzulEscuro11" size="50" maxlength="40"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Nascimento:</strong></td>
        <td><input name="nascimento" id="nascimento" type="text" style="width:50%" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Validade:</strong></td>
        <td><input name="validade" id="validade" type="text" style="width:50%" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Comentários:</strong></td>
        <td><textarea name="comentarios" id="comentarios" style="width:100%" class="textBrancoAzulEscuro11" rows="2" cols="51"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>

<script type="text/javascript">
  if (!document.getElementById("condominio_nome").innerHTML && window.opener.document.adicionar) {
    document.getElementById("condominio_nome").innerHTML = window.opener.document.getElementById("nome").value;
  }
</script>
