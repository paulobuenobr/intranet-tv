<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><strong>&nbsp;Cliente:</strong></td>
    <td>[cliente_nome]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><strong>&nbsp;Usuário:</strong></td>
    <td>[usuario_nome]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><strong>&nbsp;Data/Hora:</strong></td>
    <td>[data_hora]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><strong>&nbsp;Situação:</strong></td>
    <td>[situacao]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><strong>&nbsp;Conteúdo:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center" style="padding:6px">
      [conteudo]
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2"  height="40" align="center">[botoes]</td>
  </tr>
</table>

<script type="text/javascript">
  if (window.opener && window.opener.buscaClientesConsultasCredito) {
	  window.opener.buscaClientesConsultasCredito([cliente_id]);
  }
</script>
