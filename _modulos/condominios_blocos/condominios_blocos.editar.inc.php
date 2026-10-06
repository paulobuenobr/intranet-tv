<script type="text/javascript" src="rds.js.php?m=enderecos"></script>
<form name="editar" method="POST" onSubmit="return validaBloco(this);" action="[action]" onReset="buscaEnderecoCEP('[cep]');">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Condomínio:</strong></td>
        <td id="condominio_nome">[condominio]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Nome:</strong></td>
        <td><input name="nome" id="nome" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="40" value="[nome]"></td>
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
        <td><input name="estado" id="estado" type="text" class="inputBrancoAzulEscuro11" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Cidade:</strong></td>
        <td><input name="cidade" id="cidade" type="text" class="inputBrancoAzulEscuro11" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bairro:</strong></td>
        <td><input name="bairro" id="bairro" type="text" class="inputBrancoAzulEscuro11" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Logradouro:</strong></td>
        <td><input name="logradouro" id="logradouro" type="text" class="inputBrancoAzulEscuro11" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Número:</strong></td>
        <td><input name="numero" id="numero" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" value="[numero]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Apto(s)/Sala(s):</strong></td>
        <td><input name="quantidade" id="quantidade" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" value="[quantidade]"></td>
      </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td>[status_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>

<script type="text/javascript">
  if (window.opener && window.opener.document.getElementById("nome") && document.getElementById('cep').value != '' && !document.getElementById("condominio_nome").innerHTML) {
    document.getElementById("condominio_nome").innerHTML = window.opener.document.getElementById("nome").value;
  }
  if (document.getElementById('cep')) {
    buscaEnderecoCEP(document.getElementById('cep').value);
  }
  if (window.opener && window.opener.buscaBlocos) {
    window.opener.buscaBlocos([condominio_id]);
  }
</script>
