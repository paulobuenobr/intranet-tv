<script type="text/javascript" src="rds.js.php?m=enderecos"></script>
<script type="text/javascript" src="rds.js.php?m=usuarios"></script>
<form name="editar_dp" method="POST" onSubmit="return validaUsuarioDadosPessoais(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Meus Dados Pessoais&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Nome:</strong></td>
        <td>[nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;CPF:</strong></td>
        <td>[cpf]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Nascimento:</strong></td>
        <td><input name="nascimento" id="nascimento" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[nascimento]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Endere&ccedil;o:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; CEP:</strong></td>
        <td><input name="cep" id="cep" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="9" onKeyPress="return formataCEP(this,event);" onBlur="return formataCEP(this,event);" value="[cep]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Estado:</strong></td>
        <td><input name="estado" id="estado" type="text" class="inputBrancoAzulEscuro11" size="50" value="[estado]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Cidade:</strong></td>
        <td><input name="cidade" id="cidade" type="text" class="inputBrancoAzulEscuro11" size="50" value="[cidade]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bairro:</strong></td>
        <td><input name="bairro" id="bairro" type="text" class="inputBrancoAzulEscuro11" size="50" value="[bairro]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Logradouro:</strong></td>
        <td><input name="logradouro" id="logradouro" type="text" class="inputBrancoAzulEscuro11" size="50" value="[logradouro]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Número:</strong></td>
        <td><input name="numero" id="numero" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onKeyPress="return formataNumero(this,event);" onBlur="return formataNumero(this,event);" value="[numero]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Complemento:</strong></td>
        <td><input name="complemento" id="complemento" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="40" value="[complemento]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Contatos:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Residencial:</strong></td>
        <td><input name="tel_residencial" id="tel_residencial" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="14" onKeyPress="return formataTelefone(this,event);" onBlur="return formataTelefone(this,event);" value="[tel_residencial]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Celular:</strong></td>
        <td><input name="tel_celular" id="tel_celular" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="14" onKeyPress="return formataTelefone(this,event);" onBlur="return formataTelefone(this,event);" value="[tel_celular]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; E-mail:</strong></td>
        <td><input name="email" id="email" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="40" value="[email]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Central Telefônica:</strong></td>
      </tr>
[dados_central_telefonica]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Login na Intranet:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Usuário:</strong></td>
        <td>[login]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Senha:</strong></td>
        <td><input name="senha" id="senha" type="password" class="inputBrancoAzulEscuro11" size="50" maxlength="20"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Senha (Confirmação):</strong></td>
        <td><input name="senha_confirmacao" id="senha_confirmacao" type="password" class="inputBrancoAzulEscuro11" size="50" maxlength="20"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>

<script type="text/javascript">buscaEnderecoCEP('[cep]');</script>
