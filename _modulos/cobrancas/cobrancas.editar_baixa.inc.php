<script type="text/javascript" src="rds.js.php?m=cobrancas"></script>
<form name="editar_baixa" method="POST" action="[action]" onSubmit="return validaCobrancaBaixa(this)">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Editar Baixa&nbsp;</strong></td></tr>
    </table></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="25%"><strong>&nbsp;Código:</strong></td>
    <td width="75%">[codigo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;Cliente:</strong></td>
    <td>[cliente]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;Formato:</strong></td>
    <td>[formato]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;Bonificação:</strong></td>
    <td>[valor_bonificacao]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;Tipo:</strong></td>
    <td>[tipo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><strong>&nbsp;Datas:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Vencimento:</strong></td>
    <td><input name="data_vencimento" id="data_vencimento" type="text" class="inputBrancoAzulEscuro11" size="30" maxlength="10" value="[data_vencimento]" disabled readonly></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Pagamento:</strong></td>
    <td><input name="data_pagamento" id="data_pagamento" type="text" class="inputBrancoAzulEscuro11" size="30" maxlength="10" onKeyPress="return atualizaCobrancaBaixa(this, event)" onBlur="return atualizaCobrancaBaixa(this, event)" value="[data_pagamento]" readonly></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><strong>&nbsp;Valores:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Original:</strong></td>
    <td><input name="valor_original" id="valor_original" type="text" class="inputBrancoAzulEscuro11" size="30"  value="[valor_original]" disabled readonly></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Juro:</strong></td>
    <td><input name="valor_juro" id="valor_juro" type="text" class="inputBrancoAzulEscuro11" size="30"  value="[valor_juro]" readonly></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Desconto:</strong></td>
    <td><input name="valor_desconto" id="valor_desconto" type="text" class="inputBrancoAzulEscuro11" size="30"  value="[valor_desconto]" readonly></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Acréscimo:</strong></td>
    <td><input name="valor_acrescimo" id="valor_acrescimo" type="text" class="inputBrancoAzulEscuro11" size="30" onKeyPress="return atualizaCobrancaBaixa(this, event)" onblur="return atualizaCobrancaBaixa(this, event)" value="[valor_acrescimo]" readonly></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Decréscimo:</strong></td>
    <td><input name="valor_decrescimo" id="valor_decrescimo" type="text" class="inputBrancoAzulEscuro11" size="30" onKeyPress="return atualizaCobrancaBaixa(this, event)" onblur="return atualizaCobrancaBaixa(this, event)" value="[valor_decrescimo]" readonly></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Total:</strong></td>
    <td><input name="valor_total" id="valor_total" type="text" class="inputBrancoAzulEscuro11" size="30" onKeyPress="return atualizaCobrancaBaixa(this, event)" onblur="return atualizaCobrancaBaixa(this, event)" readonly></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><strong>&nbsp;Comentário:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="4" cols="70" readonly></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" align="center" colspan="2">[botoes]</td>
  </tr>
  </table>
<input name="valor_bonificacao" id="valor_bonificacao" type="hidden" value="[valor_bonificacao]">
</form>

<script type="text/javascript">atualizaCobrancaBaixa(document.editar_baixa.tipo);</script>
