<script type="text/javascript" src="rds.js.php?m=enderecos"></script>
<script type="text/javascript" src="rds.js.php?m=usuarios"></script>
<script type="text/javascript" src="rds.js.php?m=ceps"></script>
<form name="adicionar" method="POST" onSubmit="return validaUsuarioDadosCadastrais(this)" autocomplete="off" action="[action]">
<input type="text" style="display:none">
<input type="password" style="display:none">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Adicionar&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="150"><strong>&nbsp;Nome:</strong></td>
        <td><input name="nome" id="nome" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="50" maxlength="80"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;CPF:</strong></td>
        <td><input name="cpf" id="cpf" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="14" onKeyPress="return formataCPF(this,event);" onBlur="return formataCPF(this,event);"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Nascimento:</strong></td>
        <td><input name="nascimento" id="nascimento" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Endere&ccedil;o:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; CEP:</strong></td>
        <td><input name="cep" id="cep" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="9" onKeyPress="return formataCEP(this,event);" onBlur="return formataCEP(this,event);"> [adicionar_cep] [pesquisar_cep]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Estado:</strong></td>
        <td><input name="estado" id="estado" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Cidade:</strong></td>
        <td><input name="cidade" id="cidade" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bairro:</strong></td>
        <td><input name="bairro" id="bairro" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Logradouro:</strong></td>
        <td><input name="logradouro" id="logradouro" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Número:</strong></td>
        <td><input name="numero" id="numero" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="50" maxlength="10" onKeyPress="return formataNumero(this,event);" onBlur="return formataNumero(this,event);"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Complemento:</strong></td>
        <td><input name="complemento" id="complemento" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="50" maxlength="40"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Comentários:</strong></td>
        <td><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:100%" rows="4" cols="51"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Contatos:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Telefone Residencial:</strong></td>
        <td><input name="tel_residencial" id="tel_residencial" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="14" onKeyPress="return formataTelefone(this,event);" onBlur="return formataTelefone(this,event);"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Telefone Celular:</strong></td>
        <td><input name="tel_celular" id="tel_celular" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="14" onKeyPress="return formataTelefone(this,event);" onBlur="return formataTelefone(this,event);"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; E-mail:</strong></td>
        <td><input name="email" id="email" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="40"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Central Telefônica:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Ramal:</strong></td>
        <td><input name="ramal" id="ramal" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="10" onKeyPress="return formataNumero(this,event);" onBlur="return formataNumero(this,event);"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Agente &raquo; ID:</strong></td>
        <td><input name="ramal" id="agente_id" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="10" onKeyPress="return formataNumero(this,event);" onBlur="return formataNumero(this,event);"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Agente &raquo; Senha:</strong></td>
        <td><input name="ramal" id="agente_senha" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="10" onKeyPress="return formataNumero(this,event);" onBlur="return formataNumero(this,event);"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Login na Intranet:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Usuário:</strong></td>
        <td><input name="login" id="login" autocomplete="off" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="15" onChange="verificaUsuarioMesmoLogin(this)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Senha:</strong></td>
        <td><input name="senha" id="senha" autocomplete="off" type="password" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="20"></td>
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
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
<script type="text/javascript">var usuario_id = '';</script>
