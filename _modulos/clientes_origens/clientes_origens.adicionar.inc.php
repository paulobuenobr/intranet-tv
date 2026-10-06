<script type="text/javascript" src="rds.js.php?m=clientes_origens"></script>
<script type="text/javascript" src="rds.js.php?m=ceps"></script>
<script type="text/javascript" src="rds.js.php?m=enderecos"></script>
<form name="editar" method="POST" action="[action]" onSubmit="return validaClienteOrigem(this)">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td>
    <table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="100"><b>&nbsp;Nome:</b></td>
        <td><input type="text" name="nome" id="nome" class="inputBrancoAzulEscuro11" value="" style="width:100%" maxlength="50"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="100"><b>&nbsp;Razão Social:</b></td>
        <td><input type="text" name="razao_social" id="razao_social" class="inputBrancoAzulEscuro11" style="width:100%" maxlength="50"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="100"><b>&nbsp;CNPJ:</b></td>
        <td><input name="cnpj" id="cnpj" type="text" style="width:250px" class="inputBrancoAzulEscuro11" maxlength="20" onKeyPress="return formataCNPJ(this, event)" onBlur="return formataCNPJ(this, event)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Endereço:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; CEP: </strong></td>
        <td><input name="cep" id="cep" type="text" style="width:250px" class="inputBrancoAzulEscuro11" maxlength="9" onKeyPress="return formataCEP(this, event)" onBlur="return formataCEP(this, event)"> [adicionar_cep] [pesquisar_cep]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Estado:</strong></td>
        <td><input name="estado" id="estado" type="text" style="width:250px" class="inputBrancoAzulEscuro11" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Cidade:</strong></td>
        <td><input name="cidade" id="cidade" type="text" style="width:250px" class="inputBrancoAzulEscuro11" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bairro:</strong></td>
        <td><input name="bairro" id="bairro" type="text" style="width:250px" class="inputBrancoAzulEscuro11" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Logradouro:</strong></td>
        <td><input name="logradouro" id="logradouro" type="text" style="width:400px" class="inputBrancoAzulEscuro11" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Número:</strong></td>
        <td><input name="numero" id="numero" type="text" style="width:400px" class="inputBrancoAzulEscuro11" maxlength="10"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Complemento:</strong></td>
        <td><input name="complemento" id="complemento" type="text" style="width:400px" class="inputBrancoAzulEscuro11" maxlength="80"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Contatos:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Telefone:</strong></td>
        <td><input name="telefone" id="telefone" type="text" style="width:400px" class="inputBrancoAzulEscuro11" maxlength="14"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; E-mail:</strong></td>
        <td><input name="email" id="email" type="text" style="width:400px" class="inputBrancoAzulEscuro11" maxlength="100"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Cidades:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="cidades_listagem">[cidades_listagem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<input type="hidden" name="cidades_lista" id="cidades_lista" value='[cidades_lista]'>
</form>
