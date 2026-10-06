<script type="text/javascript" src="rds.js.php?m=clientes.migrar"></script>
<form name="migrar" method="POST" action="[action]" onSubmit="return validaClienteMigrar(this)">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center">
      <p>Você deseja realmente <b>migrar</b> o e-mail deste cliente?</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="60"><strong>&nbsp;E-mail:</strong></td>
    <td>[email]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;Origem:</strong></td>
    <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="55"><input name="cliente_origem_id" id="cliente_origem_id" type="text" class="inputBrancoAzulEscuro11" style="width:50px" value="[cliente_origem_id]" disabled></td>
          <td><input name="cliente_origem_nome" id="cliente_origem_nome" type="text" class="inputBrancoAzulEscuro11" style="width:100%" value="[cliente_origem_nome]" disabled></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;Destino:</strong></td>
    <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="55"><input name="cliente_destino_id" id="cliente_destino_id" type="text" class="inputBrancoAzulEscuro11" style="width:50px" onchange="buscaClienteMigracaoNome(this.form.cliente_origem_id.value, this.form.cliente_destino_id.value)"></td>
          <td><input name="cliente_destino_nome" id="cliente_destino_nome" type="text" class="inputBrancoAzulEscuro11" style="width:100%" disabled></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><strong>&nbsp;Comentário:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="7" cols="105"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center">[botoes]</td>
  </tr>
</table>
</form>
<script type="text/javascript">
  var mensagem = '[mensagem]';
  if (mensagem) alert(mensagem);
</script>
