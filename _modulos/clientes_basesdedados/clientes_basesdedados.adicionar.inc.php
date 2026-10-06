<script type="text/javascript" src="rds.js.php?m=clientes.basesdedados"></script>
<form name="editar" method="POST" onSubmit="return validaClienteBasededados(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Cliente:</strong></td>
        <td id="cliente">[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Contrato:</strong></td>
         <td>[contrato]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
         <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Nome:</strong></td>
         <td><input name="basededados" id="basededados" type="text" class="inputBrancoAzulEscuro11" style="width:150" size="25" maxlength="32" onchange="verificaBasededadosExistente(this, 0)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Login:</strong></td>
         <td><input name="login" id="login" type="text" class="inputBrancoAzulEscuro11" style="width:150" size="25" maxlength="32" onchange="verificaLoginExistente(this, 0)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Senha:</strong></td>
        <td><input name="senha" id="senha" type="password" class="inputBrancoAzulEscuro11" style="width:150" size="25" maxlength="32"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Confirmação de Senha:</strong></td>
        <td><input name="senha_confirmacao" id="senha_confirmacao" type="password" class="inputBrancoAzulEscuro11" style="width:150" size="25" maxlength="32"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Comentários:</strong></td>
        <td><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:280" rows="4" cols="52"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Dados Técnicos:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Servidor:</strong></td>
        <td>[servidor]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Capacidade:</strong></td>
        <td><span id="capacidade"></span></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>

<script type="text/javascript">
  if (!window.opener.closed && window.opener.buscaBasesdedados) {
    window.opener.buscaBasesdedados([cliente_id]);
  }
</script>
