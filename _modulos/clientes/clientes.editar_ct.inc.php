<form name="editar_ct" id="editar_ct" method="POST" action="[action]">
<script type="text/javascript" src="rds.js.php?m=clientes.contatos" id="clientes.contatos.js"></script>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><strong>&nbsp;Nome:</strong></td>
        <td width="50%">[nome]</td>
        <td width="25%" align="center" bgcolor="#D7D7D7">[situacao_financeira]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;CPF/CNPJ:</b></td>
        <td colspan="2">[cpf_cnpj]</td>
      </tr>
      [alerta_pendencias]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Telefone(s):</strong></td>
        <td colspan="2">
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
              <td valign="top" id="listagem_telefones">[listagem_telefones]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Email(s):</strong></td>
        <td colspan="2">
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
              <td valign="top" id="listagem_emails">[listagem_emails]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="3" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<input type="hidden" name="cliente_id" id="cliente_id" value='[id]'>
<input type="hidden" name="lista_emails" id="lista_emails" value='[lista_emails]'>
<input type="hidden" name="lista_telefones" id="lista_telefones" value='[lista_telefones]'>
</form>
