<script type="text/javascript" src="rds.js.php?m=cobrancas"></script>
<form name="dar_baixa" method="POST" action="[action]" onSubmit="return validaRegistrarNota(this)">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Registrar Nota&nbsp;</strong></td></tr>
    </table></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;Código:</strong></td>
    <td>[codigo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="25%"><strong>&nbsp;Cliente:</strong></td>
    <td width="75%">[cliente]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;Formato:</strong></td>
    <td>[formato]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;Vencimento:</strong></td>
    <td>[data_vencimento]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;Valor Original:</strong></td>
    <td>[valor_original]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	<td height="30" colspan="2"><strong>&nbsp;Nota Fiscal:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Tipo:</strong></td>
    <td>[tipo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	<td><strong>&nbsp;&raquo; Número:</strong></td>
	<td>[numero]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	<td><strong>&nbsp;&raquo; Emissão:</strong></td>
	<td>[emissao]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	<td><strong>&nbsp;&raquo; Valor:</strong></td>
	<td>[valor]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" align="center" colspan="2">[botoes]</td>
  </tr>
</table>
</form>
