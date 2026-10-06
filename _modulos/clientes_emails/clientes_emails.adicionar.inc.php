<script type="text/javascript" src="rds.js.php?m=clientes_emails"></script>
<form name="adicionar" method="POST" onSubmit="return validaClienteEmail(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Adicionar&nbsp;</b></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Cliente:</strong></td>
        <td>[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Assinatura:</strong></td>
        <td>[assinatura]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;E-mail:</strong></td>
        <td>
        	<table width="100%" border="0" cellspacing="0" cellpadding="0">
        		<tr class="txtAzulEscuro11">
        			<td style="width:100px">[usuario]</td>
        			<td style="width:20px;text-align:center" width="">@</td>
        			<td>[dominio]</td>
        		</tr>
        	</table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Senha:</strong></td>
        <td>
        	<table width="100%" border="0" cellspacing="0" cellpadding="0">
        		<tr class="txtAzulEscuro11">
        			<td><input name="senha" id="senha" type="text" class="inputBrancoAzulEscuro11" maxlength="32" style="width:100%"></td>
        			<td style="width:5px"></td>
        			<td style="width:100px"><input type="button" name="senha_gerar" class="buttonAzulEscuroBranco11" value="Gerar" onClick="gerarClienteEmailSenha(this.form.senha)" style="width:100%"></td>
        		</tr>
        	</table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Descrição:</strong></td>
        <td><input name="descricao" id="descricao" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="80" style="width:100%"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Capacidade:</strong></td>
        <td>[capacidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Olé Controle:</strong></td>
        <td>[ole_controle</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="3" cols="75" style="width:100%"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="3" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
<script type="text/javascript">
  var cliente_id = '[cliente_id]';
  var cliente_email_id = '';
  if (document.getElementById('assinatura').value) {
  	document.getElementById('usuario').focus();
  } else {
  	document.getElementById('assinatura').focus();
  }
</script>
