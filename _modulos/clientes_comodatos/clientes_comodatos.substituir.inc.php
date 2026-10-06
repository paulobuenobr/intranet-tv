<script type="text/javascript" src="rds.js.php?m=clientes_comodatos"></script>
<form name="editar" method="POST" onSubmit="return validaEquipamentoComodatoSubstituir(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
	<tr>
		<td>
		<table border="0" cellspacing="1" cellpadding="3" width="100%">
			<tr align="center" class="txtBranco11">
				<td bgcolor="#505050"><strong>&nbsp;Substituir&nbsp;</strong></td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td>
		<table width="100%" border="0" cellspacing="1" cellpadding="3">
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td width="20%"><b>&nbsp;Cliente:</b></td>
				<td width="80%">[[cliente_id]] [cliente]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td width="20%"><b>&nbsp;Contrato:</b></td>
				<td width="80%">[contrato]</td>
			</tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="30" colspan="2"><b>&nbsp;Equipamento atual:</b></td>
		      </tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><b>&nbsp;&raquo; Tipo</b></td>
			    <td>[equipamento]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><b>&nbsp;&raquo; Modelo</b></td>
			    <td>[modelo_atual]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><b>&nbsp;&raquo; MAC: </b></td>
				<td colspan="2" style="font-family: monospace">[mac_address_atual]</td>
			</tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="30" colspan="2"><b>&nbsp;Novo Equipamento:</b></td>
		      </tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><b>&nbsp;&raquo; Modelo</b></td>
			    <td>[modelo]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><b>&nbsp;&raquo; MAC: </b></td>
				<td colspan="2">[mac_address]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td height="40" colspan="3" align="center">[botoes]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td height="30" colspan="2"><b>&nbsp;Comentários:</b></td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td colspan="2"><textarea name="comentarios" id="comentarios"
					class="textBrancoAzulEscuro11" style="width: 100%" rows="3"
					cols="52"></textarea></td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td colspan="2">[lista_comentarios]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td height="40" colspan="3" align="center">[botoes]</td>
			</tr>
		</table>
	</td>
	</tr>
</table>
</form>
<script type="text/javascript">
	if (!window.opener.closed) {
		if (window.opener.atualizaEquipamentosComodatos) {
			window.opener.atualizaEquipamentosComodatos('[cliente_id]', window.opener.document.getElementById('exibir_comodatos_inativos').checked);
		}
	}
</script>
