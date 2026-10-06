<script type="text/javascript" src="rds.js.php?m=cobrancas"></script>
<form name="dar_baixa" method="POST" action="[action]" onSubmit="return validaGeraSegundaVia(this)">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Gerar Segunda Via&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><strong>&nbsp;Cliente:</strong></td>
        <td width="75%">[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;1ª Via:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Formato:</strong></td>
        <td>[formato_1_via]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Código:</strong></td>
        <td>[codigo_1_via]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Vencimento:</strong></td>
        <td>[data_vencimento_1_via]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Valor:</strong></td>
        <td>[valor_1_via]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;2ª Via:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Formato:</strong></td>
        <td>[formato]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Vencimento:</strong></td>
        <td>
					<table width="100%" border="0" cellpadding="0" cellspacing="0">
						<tr class="txtAzulEscuro11">
							<td><input name="data_vencimento" id="data_vencimento" style="width:100px" type="text" class="inputBrancoAzulEscuro11" size="30" maxlength="10" onKeyPress="return mudaVencimento(this, event)" onBlur="return mudaVencimento(this, event)"></td>
							<td style="width:20px"><input type="checkbox" id="nao_cobrar_juro" name="nao_cobrar_juro" value="1" style="margin:0" onchange="return mudaVencimento(this, event)" /></td>
							<td style="width:80px"><label for="nao_cobrar_juro">Não cobrar juro</label></td>
							<td style="width:20px"><input type="checkbox" id="manter_bonificacao" name="manter_bonificacao" value="1" style="margin:0" /></td>
							<td style="width:95px"><label for="manter_bonificacao">Manter bonificação</label></td>
						</tr>
					</table>
				</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Valores:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2">[valores]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><table border="0" cellpadding="0" cellspacing="0"><tr class="txtAzulEscuro11"><td><input type="checkbox" value="1" name="desbloquear" id="desbloquear" checked></td><td><label for="desbloquear"> Desbloquear cliente</label></td></tr></table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentário:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td align="center" colspan="2"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="4" cols="70"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<input type="hidden" name="data_vencimento_maximo" id="data_vencimento_maximo" value="[data_vencimento_maximo]">
<input type="hidden" name="valor_desconto_maximo" id="valor_desconto_maximo" value="[valor_desconto_maximo]">
</form>

<script type="text/javascript">
  var data_vencimento_1_via = '[data_vencimento_1_via]';
  var tarifas_bancarias = new Array();
  [tarifas_bancarias]
  if (window.opener != null && window.opener.document.getElementById("cobrancas") != null) {
    window.opener.buscarCobrancas([cliente_id]);
  }
  document.getElementById('data_vencimento').focus();
</script>
