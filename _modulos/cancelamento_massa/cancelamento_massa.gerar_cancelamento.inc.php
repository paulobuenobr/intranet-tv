<script type="text/javascript" src="rds.js.php?m=cancelamento_massa"></script>
<form name="gerar_cancelamento" method="POST" action="[action]" onSubmit="return validaCancelamentoMassa(this);">
	<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="1" cellpadding="3">
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td width="25%" valign="top"><b>&nbsp;Origens:</b></td>
						<td width="75%">
							<div style="max-height:180px; overflow:auto; border:1px solid #CCCCCC; padding:5px; background:#FFFFFF;">
								[origens]
							</div>
						</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Data de Cancelamento:</b></td>
						<td>
							<input type="date" name="data_cancelamento" id="data_cancelamento" class="inputBrancoAzulEscuro11" style="width:150px" value="[data_cancelamento]" [data_max] required>
						</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Tipo de Lista:</b></td>
						<td>[tipo_lista]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td colspan="2" height="30"><b>&nbsp;Clientes (IDs ou CPF/CNPJ):</b></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td colspan="2" align="center">
							<textarea name="lista_clientes" id="lista_clientes" class="textBrancoAzulEscuro11" style="width:100%" rows="12" cols="105" required>[lista_clientes]</textarea>
							<br>
							<small>Separe os itens por quebra de linha, espaço, ponto e vírgula ou vírgula.</small>
						</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="40" colspan="2" align="center">[botoes]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td colspan="2" align="left">[resultado]</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
