<script type="text/javascript" src="rds.js.php?m=cobrancas"></script>
<form name="filtro" method="GET" onSubmit="return validaBaixarEmLoteBuscar(this)">
<input type="hidden" name="m" value="cobrancas">
<input type="hidden" name="a" value="baixar_em_lote">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Baixar em Lote&nbsp;</strong></td></tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="15%"><strong>&nbsp;Origem:</strong></td>
        <td>[origem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Vencimento:</strong></td>
        <td>
			<input name="vencimento_inicial" value="[vencimento_inicial]" type="text" class="inputBrancoAzulEscuro11" maxlength="7" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)" style="200px">
			<input name="vencimento_final" value="[vencimento_final]" type="text" class="inputBrancoAzulEscuro11" maxlength="7" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)" style="200px">
		</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center"><input type="submit" name="buscar" class="buttonAzulEscuroBranco11" value="Buscar"></td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
<form name="baixar" method="POST">

	[listagem]
	
	<table width="100%" cellspacing="1" cellpadding="0" border="0"><tbody><tr><td>
		<table width="100%" cellspacing="1" cellpadding="3" border="0" bgcolor="#252525" align="center">
			<tbody id="96215" bgcolor="#E8E8E8"><tr>
				<td class="txtAzulEscuro11" style="" colspan="4"><b>Total</b></td>
				<td class="txtAzulEscuro11" style="" width="100" align="right"><input id="totalValorOriginal" value="[total]" type="text" class="inputBrancoAzulEscuro11" style="width: 100%; text-align: right; background-color: #E8E8E8;" disabled></td>
				<td class="txtAzulEscuro11" style="" width="100" align="right"><input id="totalValorFinal" value="[total]" type="text" class="inputBrancoAzulEscuro11" style="width: 100%; text-align: right; background-color: #E8E8E8;" disabled></td>
			</tbody></tr>
		</table>
	</td></tr></tbody></table>
	
	<table width="100%" cellspacing="1" cellpadding="0" border="0"><tbody><tr><td>
		<table width="100%" border="0" cellspacing="1" cellpadding="3">
		  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
			<td><strong>&nbsp;Tipo:</strong></td>
			<td>[tipo]</td>
		  </tr>
		  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
			<td><strong>&nbsp;Pagamento:</strong></td>
			<td><input name="data_pagamento" id="data_pagamento" type="text" class="inputBrancoAzulEscuro11" size="30" maxlength="10" onKeyPress="return formataData(this, event)" onblur="return formataData(this, event)" value="[data_pagamento]"></td>
		  </tr>
		  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
			<td height="30" colspan="2"><strong>&nbsp;Comentário:</strong></td>
		  </tr>
		  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
			<td align="center" colspan="2"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="4" cols="70"></textarea></td>
		  </tr>
		  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
			<td height="40" colspan="2" align="center"><input type="submit" name="baixar" class="buttonAzulEscuroBranco11" value="Baixar"></td>
		  </tr>
		</table>
	</td></tr></tbody></table>
	
</form>