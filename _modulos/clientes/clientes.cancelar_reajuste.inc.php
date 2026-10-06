<script type="text/javascript" src="rds.js.php?m=clientes.migrar" id="clientes.migrar.js"></script>
<form name="migrar" method="POST" action="[action]" onSubmit="return validaClienteMigrar(this)">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11">[acoes]</tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><strong>&nbsp;Nome:</strong></td>
        <td width="50%" valign="middle">[nome]</td>
        <td width="25%" align="center" bgcolor="#D7D7D7">[situacao_financeira]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;CPF/CNPJ:</b></td>
        <td colspan="2">[cpf_cnpj]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Endere&ccedil;o:</strong></td>
        <td colspan="2">[endereco]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Telefone(s):</strong></td>
        <td colspan="2">[telefone]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Origem:</strong></td>
        <td colspan="2">[origem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><strong>&nbsp;Cancelar Reajuste:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3" style="text-align:center">Você deseja realmente <b>cancelar o reajuste</b> deste cliente?</td>
      </tr>
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		<td colspan="3" height="40" align="center">[botoes]</td>
	  </tr>
    </table></td>
  </tr>
</table>
</form>
