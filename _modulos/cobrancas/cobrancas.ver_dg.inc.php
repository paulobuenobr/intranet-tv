<script type="text/javascript" src="rds.js.php?m=cobrancas"></script>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td>[acoes]</td>
  </tr>
  <tr>
    <td>
	    <table width="100%" border="0" cellspacing="1" cellpadding="3">
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td width="25%"><strong>&nbsp;Código:</strong></td>
	        <td width="75%">[codigo]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;Cliente:</strong></td>
	        <td style="overflow: hidden; white-space: nowrap;">[cliente_nome]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;Formato:</strong></td>
	        <td>[formato]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;Competência(s):</strong></td>
	        <td>[competencia]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;Bonificação:</strong></td>
	        <td>[valor_bonificacao]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td height="30" colspan="2"><strong>&nbsp;Datas:</strong></td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Geração:</strong></td>
	        <td>[data_geracao]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Vencimento:</strong></td>
	        <td>[data_vencimento]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Processamento:</strong></td>
	        <td>[data_processamento]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Pagamento:</strong></td>
	        <td>[data_pagamento]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Crédito:</strong></td>
	        <td>[data_credito]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td height="30" colspan="2"><strong>&nbsp;Valores:</strong></td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Original:</strong></td>
	        <td>[valor_original]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Juro:</strong></td>
	        <td>[valor_juro]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Desconto:</strong></td>
	        <td>[valor_desconto]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Acréscimo:</strong></td>
	        <td>[valor_acrescimo]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Decréscimo:</strong></td>
	        <td>[valor_decrescimo]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Total:</strong></td>
	        <td>[valor_total]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td colspan="2">[comentarios]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td height="30" colspan="2"><strong>&nbsp;Operações Contatos:</strong></td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td colspan="2">[operacoes]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td height="30" colspan="2"><strong>&nbsp;Contatos:</strong></td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td colspan="2">[contatos]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;Status:</strong></td>
	        <td>[status]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td height="40" colspan="2" align="center">[botoes]</td>
	      </tr>
	    </table>
    </td>
  </tr>
</table>
<script type="text/javascript">
	if (window.opener != null && window.opener.document.getElementById("cobrancas") != null) {
		window.opener.buscarCobrancas([cliente_id]);
	}
</script>
