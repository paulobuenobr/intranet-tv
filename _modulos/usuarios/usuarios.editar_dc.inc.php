<script type="text/javascript" src="rds.js.php?m=enderecos"></script>
<script type="text/javascript" src="rds.js.php?m=usuarios"></script>
<script type="text/javascript" src="rds.js.php?m=ceps"></script>
<form name="editar_dc" method="POST" onSubmit="return validaUsuarioDadosCadastrais(this);" action="[action]">
<input type="text" style="display:none">
<input type="password" style="display:none">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td>[acoes]</td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="150"><strong>&nbsp;Nome:</strong></td>
        <td><input name="nome" id="nome" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="50" maxlength="80" value="[nome]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;CPF:</strong></td>
        <td><input name="cpf" id="cpf" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="14" onKeyPress="return formataCPF(this,event);" onBlur="return formataCPF(this,event);" value="[cpf]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Nascimento:</strong></td>
        <td><input name="nascimento" id="nascimento" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[nascimento]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Endere&ccedil;o:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; CEP:</strong></td>
        <td><input name="cep" id="cep" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="9" onKeyPress="return formataCEP(this,event);" onBlur="return formataCEP(this,event);" value="[cep]"> [adicionar_cep] [pesquisar_cep]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Estado:</strong></td>
        <td><input name="estado" id="estado" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" value="[estado]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Cidade:</strong></td>
        <td><input name="cidade" id="cidade" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" value="[cidade]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bairro:</strong></td>
        <td><input name="bairro" id="bairro" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" value="[bairro]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Logradouro:</strong></td>
        <td><input name="logradouro" id="logradouro" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="50" value="[logradouro]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Número:</strong></td>
        <td><input name="numero" id="numero" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="50" maxlength="10" onKeyPress="return formataNumero(this,event);" onBlur="return formataNumero(this,event);" value="[numero]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Complemento:</strong></td>
        <td><input name="complemento" id="complemento" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="50" maxlength="40" value="[complemento]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Comentários:</strong></td>
        <td><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:100%" rows="4" cols="51">[comentarios]</textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Contatos:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Telefone Residencial:</strong></td>
        <td><input name="tel_residencial" id="tel_residencial" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="14" onKeyPress="return formataTelefone(this,event);" onBlur="return formataTelefone(this,event);" value="[tel_residencial]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Telefone Celular:</strong></td>
        <td><input name="tel_celular" id="tel_celular" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="14" onKeyPress="return formataTelefone(this,event);" onBlur="return formataTelefone(this,event);" value="[tel_celular]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; E-mail:</strong></td>
        <td><input name="email" id="email" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="40" value="[email]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Central Telefônica:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Senha de Discagem:</strong></td>
        <td><input name="senha_discagem" id="senha_discagem" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="10" onKeyPress="return formataNumero(this,event);" onBlur="return formataNumero(this,event);" value="[senha_discagem]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Ramal:</strong></td>
        <td><input name="ramal" id="ramal" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="10" onKeyPress="return formataNumero(this,event);" onBlur="return formataNumero(this,event);" value="[ramal]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Agente &raquo; ID:</strong></td>
        <td><input name="agente_id" id="agente_id" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="10" onKeyPress="return formataNumero(this,event);" onBlur="return formataNumero(this,event);" value="[agente_id]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Agente &raquo; Senha:</strong></td>
        <td><input name="agente_senha" id="agente_senha" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="10" onKeyPress="return formataNumero(this,event);" onBlur="return formataNumero(this,event);" value="[agente_senha]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Login na Intranet:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Usuário:</strong></td>
        <td><input name="login" id="login" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="15" value="[login]" onChange="verificaUsuarioMesmoLogin(this);"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Senha:</strong></td>
        <td><input name="senha" id="senha" type="password" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="20"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Senha (Confirmação):</strong></td>
        <td><input name="senha_confirmacao" id="senha_confirmacao" type="password" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="20"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Origem:</strong></td>
        <td>[origem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td>[status]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display: [verEhAdministrador]">
        <td><strong>&nbsp;É administrador:</strong></td>
        <td>[ehAdministrador]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
<script type="text/javascript">
  var usuario_id = '[id]';
</script>
