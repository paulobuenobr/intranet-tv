<form name="adicionar" method="POST" action="[action]">
	<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="1" cellpadding="3">
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td width="30%"><b>&nbsp;Taxa:</b></td>
						<td><input type="text" name="clf_taxa" id="clf_taxa" class="inputBrancoAzulEscuro11" style="width:100px" maxlength="13" value="0.00"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Plano:</b></td>
						<td>[pla_id]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Servi&ccedil;o:</b></td>
						<td>[pls_id]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Categoria Ajuste:</b></td>
						<td>[afc_id]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Tipo Fiscal:</b></td>
						<td>[cft_id]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="40" colspan="2" align="center">[botoes]</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
