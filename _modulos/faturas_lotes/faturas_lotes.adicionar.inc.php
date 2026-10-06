<script type="text/javascript" src="rds.js.php?m=faturas_lotes"></script>
<form name="adicionar" method="POST" action="[action]" onSubmit="return validaAdicionarFaturaLote(this)">
<table border="0" cellpadding="0" cellspacing="0" align="center"
	width="100%">
	<tr>
		<td>
		<table border="0" cellspacing="1" cellpadding="3" width="100%">
			<tr align="center" class="txtBranco11">
				<td bgcolor="#505050"><strong>&nbsp;Adicionar&nbsp;</strong></td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td>
		<table width="100%" border="0" cellspacing="1" cellpadding="3">
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td width="100"><strong>&nbsp;Data do Ciclo:</strong></td>
				<td><input name="data_ciclo" id="data_ciclo" value="[data_ciclo]" style="width:150px" type="text" class="inputBrancoAzulEscuro11" size="30" maxlength="10" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)"></td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><strong>&nbsp;Tipo:</strong></td>
				<td>[tipo]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td colspan="2" height="30"><strong>&nbsp;Filtros</strong></td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td colspan="2" id="filtros_listagem">[filtros]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td height="30" colspan="2"><strong>&nbsp;Comentário:</strong></td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td align="center" colspan="2"><textarea name="comentario"
					id="comentario" class="textBrancoAzulEscuro11" style="width: 100%"
					rows="3" cols="70"></textarea></td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td height="40" colspan="2" align="center">[botoes]</td>
			</tr>
		</table>
		</td>
	</tr>
</table>
<input type="hidden" name="filtros_lista" id="filtros_lista"></form>
