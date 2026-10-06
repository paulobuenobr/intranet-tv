<form name="editar" method="POST" onSubmit="return validaClienteRetorno(this);" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="30%"><strong>&nbsp;Cliente:</strong></td>
        <td width="70%">[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Usuário:</strong></td>
        <td>[usuario]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Data de Abertura:</strong></td>
        <td>[data_abertura]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Data / Período:</strong></td>
        <td>[data_periodo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Comentário:</strong></td>
        <td><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="6" style="width:100%">[comentario]</textarea></td>
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
</form>
<script type="text/javascript">
  if (window.opener && window.opener.buscarClienteRetornos) {
    window.opener.buscarClienteRetornos([cliente_id]);
  }
</script>
