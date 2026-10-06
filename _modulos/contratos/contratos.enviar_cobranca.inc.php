<form name="enviar" method="POST" action="[action]" onSubmit="return validaContratoComercialEnviar(this)">
<script type="text/javascript" src="rds.js.php?m=contratos" id="contratos.js"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11">[acoes]</tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><strong>&nbsp;Código:</strong></td>
    <td width="80%">[codigo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;Cliente:</strong></td>
    <td>[cliente_nome]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;Serviço:</strong></td>
    <td>[servico]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;Tipo:</strong></td>
    <td>[tipo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><strong>&nbsp;Dados Cobrança:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Código:</strong></td>
    <td>[cobranca_codigo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Formato:</strong></td>
    <td>[cobranca_formato]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Vencimento:</strong></td>
    <td>[cobranca_vencimento]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Valor:</strong></td>
    <td>[cobranca_valor]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2" align="center"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center">
      Será enviada uma mensagem para o(s) e-mail(s) abaixo com um link para o boleto.
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;E-mail(s):</b></td>
    <td><input name="emails" id="emails" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="75" value="[emails]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><b>&nbsp;Comentário:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="5" cols="105"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center">[botoes]</td>
  </tr>
</table>
</form>

<script type="text/javascript">

  if (window.opener  && window.opener.buscaContratosCliente) {
    window.opener.buscaContratosCliente([cliente_id]);
  }

  if (window.opener  && window.opener.buscaAjustesCliente) {
    window.opener.buscaAjustesCliente([cliente_id]);
  }

  if (window.opener  && window.opener.buscaCancelamentosCliente) {
    window.opener.buscaCancelamentosCliente([cliente_id]);
  }

</script>
