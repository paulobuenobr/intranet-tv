<script type="text/javascript" src="rds.js.php?m=clientes_comodatos"></script>
<form name="adicionar" method="POST" onSubmit="return validaEquipamentoComodato(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
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
				<td width="25%"><b>&nbsp;Contrato:</b></td>
  			    <td>[contrato]</td>
			</tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Equipamento:</b></td>
      </tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><b>&nbsp;&raquo; Tipo</b></td>
			    <td>[equipamento]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><b>&nbsp;&raquo; Número de Série: </b></td>
				<td colspan="2">[numero_serie]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><strong>&nbsp;&raquo; Patrimônio:</strong></td>
				<td colspan="2">[patrimonio]</td>
			</tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Nota Fiscal:</b></td>
      </tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><strong>&nbsp;&raquo; Comodato:</strong></td>
				<td colspan="2">[nf_comodato]</td>
			</tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Datas:</b></td>
      </tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><strong>&nbsp;&raquo; Início:</strong></td>
				<td colspan="2">[data_inicio]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td><strong>&nbsp;&raquo; Prazo:</strong></td>
				<td colspan="2">[prazo]</td>
			</tr>
			<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
				<td height="40" colspan="3" align="center">[botoes]</td>
			</tr>
		</table>
	</td>
	</tr>
</table>
</form>
