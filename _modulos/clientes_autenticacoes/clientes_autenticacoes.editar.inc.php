<script type="text/javascript" src="rds.js.php?m=clientes_autenticacoes"></script>
<form name="editar" method="POST" onSubmit="return validaAutenticacoesEditar(this)" action="[action]">
	<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
		<tr>
			<td>
				<table border="0" cellspacing="1" cellpadding="3" width="100%">
					<tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Editar&nbsp;</strong></td></tr>
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
						<td>
							<table cellpadding="0" cellspacing="0" border="0" width="100%">
								<tr>
									<td><input name="senha" id="senha" type="text" class="inputBrancoAzulEscuro11" value="[senha]" maxlength="32" style="width:100%" readonly></td>
									<td width="80px"><input type="button" name="senha_gerar" class="buttonAzulEscuroBranco11" style="width:80px;height:20px;border:0;margin-left:5px" value="Gerar" onClick="gerarClienteEmailSenha(this.form.senha)"></td>
								</tr>
							</table>
						</td>
					</tr>			
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11" id="linha_ssid" style="display:[linha_ssid_visivel]">
						<td><b>&nbsp;&raquo; AP/SSID:</b></td>
						<td>
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td id="campo_ssid">[ssid]</td>
									<td style="width:80px"><input name="verificar" id="verificar" style="width:80px;height:20px;border:0;margin-left:5px" class="buttonAzulEscuroBranco11" value="Verificar" type="button" onclick="return verificarConexao()"></td>
								</tr>
							</table>
							
						</td>
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
						<td>
							<input name="endereco_ip_1" id="endereco_ip_1" value="[endereco_ip_1]" type="text" class="inputBrancoAzulEscuro11_c" style="width:53px" size="3" maxlength="3" onKeyPress="return formataNumeroIP(this, event)"> .
							<input name="endereco_ip_2" id="endereco_ip_2" value="[endereco_ip_2]" type="text" class="inputBrancoAzulEscuro11_c" style="width:53px" size="3" maxlength="3" onKeyPress="return formataNumeroIP(this, event)"> .
							<input name="endereco_ip_3" id="endereco_ip_3" value="[endereco_ip_3]" type="text" class="inputBrancoAzulEscuro11_c" style="width:53px" size="3" maxlength="3" onKeyPress="return formataNumeroIP(this, event)"> .
							<input name="endereco_ip_4" id="endereco_ip_4" value="[endereco_ip_4]" type="text" class="inputBrancoAzulEscuro11_c" style="width:53px" size="3" maxlength="3" onKeyPress="return formataNumeroIP(this, event)">
						</td>
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
						<td><input name="observacao" id="observacao" value="[observacao]" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="128" maxlength="128"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><strong>&nbsp;&raquo; Situação:</strong></td>
						<td>[situacao]</td>
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
							<td><input name="equipamento_serie" id="equipamento_serie" value="[equipamento_serie]" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="17" maxlength="17"></td>
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
					<tbody id="erro_verificacao" style="display:none">
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td colspan="2" style="color:red; padding: 15px 0; text-align: center">
								<b>Atenção:</b><br />
								O ponto deste cliente não obteve os padrões mínimos de qualidade.<br />
								Para prosseguir, preencha o questionário abaixo, que será enviado
								para análise técnica.
							</td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td><b>Técnico</b></td>
							<td>[tecnico]</td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td height="30" colspan="2"><b>&nbsp;Pergunta 1 &raquo; Testou com um equipamento de maior potência?</b></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td colspan="2">
								<label><input type="radio" name="pergunta_1" id="pergunta_1_1" value="Sim"> Sim</label><br>
								<label><input type="radio" name="pergunta_1" id="pergunta_1_2" value="Não"> Não</label>
							</td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td height="30" colspan="2"><b>&nbsp;Pergunta 2 &raquo; Se não testou com um equipamento melhor, por que não?</b></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td colspan="2">
								<label><input type="radio" name="pergunta_2" id="pergunta_2_1" value="Não tinha um"> Não tinha um</label><br>
								<label><input type="radio" name="pergunta_2" id="pergunta_2_2" value="O que tinha ia para outro cliente"> O que tinha ia para outro cliente</label><br>
								<label><input type="radio" name="pergunta_2" id="pergunta_2_3" value="Estava com defeito"> Estava com defeito</label>
							</td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td height="30" colspan="2"><b>&nbsp;Pergunta 3 &raquo; Que tipo de equipamento será necessário para este cliente?</b></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td colspan="2">
								<label><input type="radio" name="pergunta_3" id="pergunta_3_1" value="Nano Station 5"> Nano Station 5</label><br>
								<label><input type="radio" name="pergunta_3" id="pergunta_3_2" value="Bullet5"> Bullet5</label><br>
								<label><input type="radio" name="pergunta_3" id="pergunta_3_3" value="Nano Station M5"> Nano Station M5</label><br>
								<label><input type="radio" name="pergunta_3" id="pergunta_3_4" value="Bullet M5"> Bullet M5</label><br>
								<label><input type="radio" name="pergunta_3" id="pergunta_3_5" value="AirGrid 23dB"> AirGrid 23dB</label><br>
								<label><input type="radio" name="pergunta_3" id="pergunta_3_6" value="AirGrid 27dB"> AirGrid 27dB</label>
							</td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td height="30" colspan="2"><b>&nbsp;Pergunta 4 &raquo; Há algum tipo de problema com a visada para o POP?</b></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td colspan="2">
								<textarea name="pergunta_4" cols="40" rows="2" class="textBrancoAzulEscuro11" style="width:100%"></textarea>
							</td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td height="30" colspan="2"><b>&nbsp;Pergunta 5 &raquo; Tem visada para algum outro POP?</b></td>
						</tr>
						<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
							<td colspan="2">
								<textarea name="pergunta_5" cols="40" rows="2" class="textBrancoAzulEscuro11" style="width:100%"></textarea>
							</td>
						</tr>
					</tbody>
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
	<input type="hidden" name="verificacao_id" id="verificacao_id" value="" />
	<input type="hidden" name="reprovado" id="reprovado" value="0" />
</form>

<script type="text/javascript">
	var cliente_id = '[cliente_id]';
	var autenticacao_id = [autenticacao_id];
	var aguardando_verificacao = false;
	if (window.opener && !window.opener.closed) {
		if (window.opener.buscaAutenticacoes) window.opener.buscaAutenticacoes(cliente_id);
		if (window.opener.buscaClientesEmailsCliente) window.opener.buscaClientesEmailsCliente(cliente_id);
	}
</script>
