<script type="text/javascript" src="rds.js.php?m=clientes_autenticacoes"></script>
<form name="editar" method="POST" onSubmit="return validaAutenticacoesAdicionar(this)" action="[action]">
	<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
		<tr>
			<td>
				<table border="0" cellspacing="1" cellpadding="3" width="100%">
					<tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Adicionar&nbsp;</b></td></tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="1" cellpadding="3">
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td width="20%"><b>&nbsp;&raquo; Cliente:</b></td>
						<td width="80%" id="cliente">[cliente_nome]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;&raquo; Assinatura:</b></td>
						<td>[assinatura]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;&raquo; Velocidade:</b></td>
						<td><span id="velocidade">[velocidade]</span></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="30" colspan="2"><b>&nbsp;Autenticação:</b></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11" id="linha_ssid" style="display:[linha_ssid_visivel]">
						<td><b>&nbsp;&raquo; AP/SSID:</b></td>
						<td>
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td id="campo_ssid">[ssid]</td>
									<td style="width:22px"><input name="verificar" id="verificar" style="width:80px;height:20px;border:0;margin-left:5px" class="buttonAzulEscuroBranco11disabled" value="Verificar" type="button" onclick="return verificarConexao()" disabled></td>
								</tr>
							</table>
							
						</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11" id="linha_ap_ip" style="display:[linha_ap_ip_visivel]">
						<td><b>&nbsp;&raquo; AP/IP:</b></td>
						<td id="campo_ap_ip"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11" id="linha_concentrador" style="display:[linha_concentrador_visivel]">
						<td><b>&nbsp;&raquo; Concentrador:</b></td>
						<td id="campo_concentrador">[concentrador]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11" id="linha_endereco_ip" style="display:[linha_endereco_ip_visivel]">
						<td><b>&nbsp;&raquo; Endereço IP:</b></td>
						<td>
							<input name="endereco_ip_1" id="endereco_ip_1" type="text" class="inputBrancoAzulEscuro11_c" style="width:53px" size="3" maxlength="3" onKeyPress="return formataNumeroIP(this, event)"> .
							<input name="endereco_ip_2" id="endereco_ip_2" type="text" class="inputBrancoAzulEscuro11_c" style="width:53px" size="3" maxlength="3" onKeyPress="return formataNumeroIP(this, event)"> .
							<input name="endereco_ip_3" id="endereco_ip_3" type="text" class="inputBrancoAzulEscuro11_c" style="width:53px" size="3" maxlength="3" onKeyPress="return formataNumeroIP(this, event)"> .
							<input name="endereco_ip_4" id="endereco_ip_4" type="text" class="inputBrancoAzulEscuro11_c" style="width:53px" size="3" maxlength="3" onKeyPress="return formataNumeroIP(this, event)">
						</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;&raquo; Autenticador:</b></td>
						<td>[autenticador]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;&raquo; Observação:</b></td>
						<td><input name="observacao" id="observacao" value="" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="128" maxlength="128"></td>
					</tr>
					<tbody id="equipamento" style="display:[equipamento_visivel]">
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td height="30" colspan="2"><b>&nbsp;Equipamento:</b></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>&nbsp;&raquo; Modelo:</b></td>
							<td id="equipamento_modelo"></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>&nbsp;&raquo; MAC:</b></td>
							<td id="equipamento_mac"></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>&nbsp;&raquo; IP:</b></td>
							<td id="equipamento_ip"></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>&nbsp;&raquo; Nº de Série:</b></td>
							<td><input name="equipamento_serie" id="equipamento_serie" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="17" maxlength="17"></td>
						</tr>
					</tbody>
					<tbody id="qualidade" style="display:none">
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td height="30" colspan="2"><b>&nbsp;Qualidade do Sinal &raquo; Cliente:</b></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>&nbsp;&raquo; Signal:</b></td>
							<td id="equipamento_signal"></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>&nbsp;&raquo; SNR:</b></td>
							<td id="equipamento_snr"></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>&nbsp;&raquo; CCQ:</b></td>
							<td id="equipamento_ccq"></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td height="30" colspan="2"><b>&nbsp;Qualidade do Sinal &raquo; AP:</b></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>&nbsp;&raquo; Signal:</b></td>
							<td id="ap_signal"></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>&nbsp;&raquo; SNR:</b></td>
							<td id="ap_snr"></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>&nbsp;&raquo; CCQ:</b></td>
							<td id="ap_ccq"></td>
						</tr>
					</tbody>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="30" colspan="2"><b>&nbsp;Comentários:</b></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:100%" rows="5" cols="52"></textarea></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="40" colspan="2" align="center">[botoes]</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<input type="hidden" name="verificacao_id" id="verificacao_id" value="" />
</form>

<script type="text/javascript">
	var cliente_id = '[cliente_id]';
	var autenticacao_id = 0;
	var aguardando_verificacao = [aguardando_verificacao];
	document.getElementById('assinatura').focus();
	if (!window.opener.closed && window.opener.buscaAutenticacoes) {
		window.opener.buscaAutenticacoes(cliente_id);
	}
</script>
