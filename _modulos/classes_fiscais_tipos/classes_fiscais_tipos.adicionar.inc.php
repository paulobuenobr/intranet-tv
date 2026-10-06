<form name="adicionar" method="POST" action="[action]">
	<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="1" cellpadding="3">
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td width="30%"><b>&nbsp;Sigla:</b></td>
						<td><input type="text" name="cft_sigla" id="cft_sigla" class="inputBrancoAzulEscuro11" style="width:80px" maxlength="3"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Descri&ccedil;&atilde;o:</b></td>
						<td><input type="text" name="cft_descricao" id="cft_descricao" class="inputBrancoAzulEscuro11" style="width:350px" maxlength="60"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Tipo:</b></td>
						<td>[cft_tipo]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Tributa&ccedil;&atilde;o:</b></td>
						<td><input type="text" name="cft_tributacao" id="cft_tributacao" class="inputBrancoAzulEscuro11" style="width:80px" maxlength="3" value="1"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;ICMS:</b></td>
						<td><input type="text" name="cft_icms" id="cft_icms" class="inputBrancoAzulEscuro11" style="width:100px" maxlength="13" value="0.00"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;C&oacute;digo:</b></td>
						<td><input type="text" name="cft_codigo" id="cft_codigo" class="inputBrancoAzulEscuro11" style="width:80px" maxlength="4"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Natureza PF SC:</b></td>
						<td><input type="text" name="cft_natureza_pf_sc" id="cft_natureza_pf_sc" class="inputBrancoAzulEscuro11" style="width:120px" maxlength="8"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Natureza PJ SC:</b></td>
						<td><input type="text" name="cft_natureza_pj_sc" id="cft_natureza_pj_sc" class="inputBrancoAzulEscuro11" style="width:120px" maxlength="8"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Natureza PF Outros:</b></td>
						<td><input type="text" name="cft_natureza_pf_outros" id="cft_natureza_pf_outros" class="inputBrancoAzulEscuro11" style="width:120px" maxlength="8"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Natureza PJ Outros:</b></td>
						<td><input type="text" name="cft_natureza_pj_outros" id="cft_natureza_pj_outros" class="inputBrancoAzulEscuro11" style="width:120px" maxlength="8"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Empresa:</b></td>
						<td>[emp_id]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Gerar Nota:</b></td>
						<td>[cft_gerar_nota]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="40" colspan="2" align="center">[botoes]</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
