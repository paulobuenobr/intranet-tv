<script type="text/javascript" src="rds.js.php?m=clientes_telefones"></script>
<form name="adicionar" method="POST" onSubmit="return validaAssinaturaClienteTelefone(this)" action="[action]">
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
						<td width="30%"><b>&nbsp;Assinatura:</b></td>
	  			  <td>[assinaturas]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td width="25%"><b>&nbsp;Tabela de Venda:</b></td>
	  			  <td id="tabela_venda">[tabela_venda]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td width="25%"><b>&nbsp;Tabela de Roteamento:</b></td>
	  			  <td id="tabela_roteamento">[tabela_roteamento]</td>
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
	var erro = '[erro]';
	if (erro) alert(erro);
</script>
