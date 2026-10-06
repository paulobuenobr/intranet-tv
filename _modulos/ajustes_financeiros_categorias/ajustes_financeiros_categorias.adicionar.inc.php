<script type="text/javascript" src="rds.js.php?m=ajustes_financeiros_categorias"></script>
<form name="adicionar" method="POST" onSubmit="return validaCategoriaAjusteFinanceiro(this)" action="[action]">
	<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="1" cellpadding="3">
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td width="30%"><b>&nbsp;Nome:</b></td>
						<td><input type="text" name="afc_nome" id="afc_nome" class="inputBrancoAzulEscuro11" style="width:350px" maxlength="42"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Acr&eacute;scimo:</b></td>
						<td>[afc_acrescimo]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Decr&eacute;scimo:</b></td>
						<td>[afc_decrescimo]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Bonifica&ccedil;&atilde;o:</b></td>
						<td>[afc_bonificacao]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Solicitar NF:</b></td>
						<td>[afc_solicitar_nf]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Dispon&iacute;vel:</b></td>
						<td>[afc_disponivel]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Tipo Custom:</b></td>
						<td>[custom]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Gerar NFS-e:</b></td>
						<td>[gerar_nfse]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;C&oacute;digo Servi&ccedil;o:</b></td>
						<td>[codigo_servico_id]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;CNAE:</b></td>
						<td>[cnae_id]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Al&iacute;quota:</b></td>
						<td><input type="text" name="aliquota" id="aliquota" class="inputBrancoAzulEscuro11" style="width:100px" maxlength="10"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;ISS Retido:</b></td>
						<td>[issRetido]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;NBS:</b></td>
						<td>[nds]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="40" colspan="2" align="center">[botoes]</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
<script type="text/javascript">
	mudaGerarNota(document.getElementById("gerar_nfse").value);
</script>
