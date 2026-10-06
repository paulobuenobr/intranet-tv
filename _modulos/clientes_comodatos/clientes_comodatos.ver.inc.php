<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
	<tr>
		<td>
		<table border="0" cellspacing="1" cellpadding="3" width="100%">
			<tr align="center" class="txtBranco11">
				<td bgcolor="#505050"><strong>&nbsp;Ver&nbsp;</strong></td>
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
        <td height="30" colspan="2"><b>&nbsp;Equipamento:</b></td>
      </tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><b>&nbsp;&raquo; Tipo</b></td>
				<td colspan="2">[equipamento]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><b>&nbsp;&raquo; Modelo</b></td>
				<td colspan="2">[modelo]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><b>&nbsp;&raquo; MAC: </b></td>
				<td colspan="2" style="font-family: monospace">[mac_address]</td>
			</tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Datas:</b></td>
      </tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><strong>&nbsp;&raquo; Início:</strong></td>
				<td colspan="2">[data_inicio]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><strong>&nbsp;&raquo; Término:</strong></td>
				<td colspan="2">[data_termino]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><strong>&nbsp;&raquo; Retirada:</strong></td>
				<td colspan="2">[data_retirada]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><strong>&nbsp;Status:</strong></td>
				<td colspan="2">[status]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td height="40" colspan="3" align="center">[botoes]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td height="30" colspan="2"><b>&nbsp;Comentários:</b></td>
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
<script type="text/javascript">
	if (!window.opener.closed) {
		if (window.opener.atualizaEquipamentosComodatos) {
			window.opener.atualizaEquipamentosComodatos('[cliente_id]', window.opener.document.getElementById('exibir_comodatos_inativos').checked);
		}
	}
</script>
