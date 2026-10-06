<script type="text/javascript" src="rds.js.php?m=pesquisas_aplicacoes"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.contatos"></script>
<script type="text/javascript" src="rds.js.php?m=clientes_emails"></script>
<form name="pesquisas_aplicacoes_completar" id="pesquisas_aplicacoes_completar" method="POST" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11">[acoes]</tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Cliente:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><b>&nbsp;&raquo; Nome:</b></td>
        <td width="80%">[cliente]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Contatos</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2">
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
              <td width="50%" valign="top" id="listagem_telefones">[listagem_telefones]</td>
              <td width="50%" valign="top" id="listagem_emails">[listagem_emails]</td>
            </tr>
          </table>
        </td>
      </tr>
      [clientes_emails]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><strong>&nbsp;E-mails:</strong> [emails_adicionar]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="clientes_emails_listagem">[emails_listagem]</td>
            </tr>
          </table>
        </td>
      </tr>
      [/clientes_emails]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<input type="hidden" name="lista_telefones" id="lista_telefones" value='[lista_telefones]'>
<input type="hidden" name="lista_emails" id="lista_emails" value='[lista_emails]'>
<input type="hidden" name="cliente_id" id="cliente_id" value='[cliente_id]'>
</form>

<script type="text/javascript">
  var cliente_id = '[cliente_id]';
</script>
