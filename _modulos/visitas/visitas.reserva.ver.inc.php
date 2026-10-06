<script type="text/javascript" src="rds.js.php?m=contratos"></script>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><strong>&nbsp;Data/Período:</strong></td>
        <td width="75%">[data_periodo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[comentarios]</td>
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

<script type="text/javascript">
  var alterado = '[alterado]';
  var cliente = '[cliente_id]';
  if (alterado != '0' && cliente != "" && window.opener && window.opener.buscaVisitas) {
    window.opener.buscaVisitas(cliente);
  }
  if (alterado != '0' && window.opener && window.opener.atualizaVisitas) {
    window.opener.atualizaVisitas('[tipo_id]');
  }
</script>
