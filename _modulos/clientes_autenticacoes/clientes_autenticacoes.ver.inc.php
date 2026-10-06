<script type="text/javascript" src="rds.js.php?m=clientes_autenticacoes"></script>
<form name="editar" method="POST" onSubmit="return validaAutenticacoesEditar(this)" action="[action]">
	<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
		<tr>
			<td>
				<table border="0" cellspacing="1" cellpadding="3" width="100%">
					<tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Ver&nbsp;</strong></td></tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="1" cellpadding="3">
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td width="20%"><b>&nbsp;Cliente:</b></td>
						<td width="80%" id="cliente">[cliente_nome]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Assinatura:</b></td>
						<td>[assinatura]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Velocidade:</b></td>
						<td>[velocidade]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="30" colspan="2"><b>&nbsp;Autenticação:</b></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;&raquo; Usuário:</b></td>
						<td>[usuario]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;&raquo; Senha:</b></td>
						<td>[senha]</td>
					</tr>			
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11" id="linha_ssid" style="display:[linha_ssid_visivel]">
						<td><b>&nbsp;&raquo; AP/SSID:</b></td>
						<td>[ssid]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11" id="linha_ap_ip" style="display:[linha_ap_ip_visivel]">
						<td><b>&nbsp;&raquo; AP/IP:</b></td>
						<td id="campo_ap_ip">[ap_ip]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11" id="linha_concentrador" style="display:[linha_concentrador_visivel]">
						<td><b>&nbsp;&raquo; Concentrador:</b></td>
						<td id="campo_concentrador">[concentrador]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11" id="linha_endereco_ip" style="display:[linha_endereco_ip_visivel]">
						<td><b>&nbsp;&raquo; Endereço IP:</b></td>
						<td>[endereco_ip]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11" id="linha_pool" style="display:[linha_pool_visivel]">
						<td><strong>&nbsp;&raquo; Pool:</strong></td>
						<td>[pool]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;&raquo; Autenticador:</b></td>
						<td>[autenticador]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;&raquo; Observação:</b></td>
						<td>[observacao]</td>
					</tr>
					<tbody id="equipamento" style="display:[equipamento_visivel]">
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td height="30" colspan="2"><b>&nbsp;Equipamento:</b></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>&nbsp;&raquo; Modelo:</b></td>
							<td id="equipamento_modelo">[equipamento_modelo]</td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>&nbsp;&raquo; MAC:</b></td>
							<td id="equipamento_mac">[equipamento_mac]</td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>&nbsp;&raquo; IP:</b></td>
							<td id="equipamento_ip">[equipamento_ip]</td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>&nbsp;&raquo; Nº de Série:</b></td>
							<td>[equipamento_serie]</td>
						</tr>
					</tbody>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><strong>&nbsp;&raquo; Situação:</strong></td>
						<td>[situacao]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="40" colspan="2" align="center">[botoes]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="30" colspan="2"><b>&nbsp;Ajustes Programados:</b></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td colspan="2">
							<table width="100%" border="0" cellspacing="1" cellpadding="3">
								<tr>
									<td class="txtAzulEscuro11">[ajustes_programados]</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Status:</b></td>
						<td>[status]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="30" colspan="2"><b>&nbsp;Comentários:</b></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:100%" rows="3" cols="52"></textarea></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td colspan="2">[lista_comentarios]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="40" colspan="2" align="center">[botoes]</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<input type="hidden" id="assinatura" value="[assinatura]">
</form>

<script type="text/javascript">
	if (!window.opener.closed && window.opener.buscaAutenticacoes) {
		window.opener.buscaAutenticacoes([cliente_id]);
	}
</script>
