<script type="text/javascript" src="rds.js.php?m=ceps"></script>
<table style="border: 0; border-collapse: collapse; width: 100%">
	<tr>
		<td>
			<table  style="border: 0; border-collapse: collapse; width: 100%">
				<tr align="center" class="txtBranco11"><td bgcolor="#505050" style="padding: 3px"><b>&nbsp;Adicionar / Atualziar&nbsp;</b></td></tr>
			</table>
		</td>
	</tr>
	<tr>
		<td style="padding: 0">
			<table style="border: 0; border-spacing: 1px; width: 100%">
				<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
					<td style="padding: 3px" width="100"><b>&nbsp;&raquo; CEP:</b></td>
					<td style="padding: 3px"><input autocomplete="off" name="cep" id="cep" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="9" onKeyPress="return formataCEP(this, event, null, true);" onkeyup="buscaCEP(this);" onBlur="buscaCEP(this); return formataCEP(this, event, null, true);"></td>
				</tr>
				<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
					<td style="padding: 3px"><b>&nbsp;&raquo; Estado:</b></td>
					<td style="padding: 3px">[estado]</td>
				</tr>
				<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
					<td style="padding: 3px"><b>&nbsp;&raquo; Cidade:</b></td>
					<td style="padding: 3px"><select name="cidade" id="cidade" style="width:250px" class="inputBrancoAzulEscuro11" onChange="mudaCidade(this)" disabled><option></option></select></td>
				</tr>
				<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
					<td style="padding: 3px"><b>&nbsp;&raquo; Bairro:</b></td>
					<td style="padding: 3px"><select name="bairro" id="bairro" style="width:250px" class="inputBrancoAzulEscuro11" disabled><option></option></select><input name="novo_bairro" id="novo_bairro" autocomplete="off" type="text" style="width:250px;display:none" class="inputBrancoAzulEscuro11" size="50" onKeyDown="novoBairroOnKeyPress(event)"> <input type="button" id="novo" class="buttonAzulEscuroBranco11" value="Novo Bairro" style="width:80px;height:20px" onClick="novoBairro()" disabled></td>
				</tr>
				<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
					<td style="padding: 3px"><b>&nbsp;&raquo; Logradouro:</b></td>
					<td style="padding: 3px"><input autocomplete="off" name="logradouro" id="logradouro" type="text" style="width:546px" class="inputBrancoAzulEscuro11" size="50"></td>
				</tr>
				<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
					<td style="padding: 3px"><b>&nbsp;&raquo; Observa&ccedil;&atilde;o:</b></td>
					<td style="padding: 3px"><input autocomplete="off" name="observacao" id="observacao" type="text" style="width:546px" class="inputBrancoAzulEscuro11" size="32" maxlength="32"></td>
				</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
					<td style="padding: 3px" height="40" colspan="2" align="center">[botoes]</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<script type="text/javascript">
	if (window.opener && window.opener.document.getElementById('cep') && !window.opener.document.getElementById('cep').disabled && window.opener.document.getElementById('cep').value) {
		document.getElementById('cep').value = window.opener.document.getElementById('cep').value;
		buscaCEP(document.getElementById('cep'));
	}
</script>
