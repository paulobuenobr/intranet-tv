<script type="text/javascript" src="rds.js.php?m=clientes_iptv"></script>
<form name="ver" method="POST" action="[action]">
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
						<td><b>&nbsp;Contrato:</b></td>
						<td>[contrato_codigo]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Usuário:</b></td>
						<td>[usuario]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="30" colspan="2"><b>&nbsp;Assinatura:</b></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;&raquo; Pacote:</b></td>
						<td>[plano_nome]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;&raquo; Adicionais:</b></td>
						<td>[planos_adicionais]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;&raquo; Pontos:</b></td>
						<td>[pontos]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;&raquo; Dispositivos:</b></td>
						<td>[dispositivos]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;&raquo; Catálogos VOD:</b></td>
						<td>[vod_catalogos]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;&raquo; Apps:</b></td>
						<td>[apps]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td><b>&nbsp;Status:</b></td>
						<td>[status]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="30" colspan="2"><b>&nbsp;Situação:</b></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td colspan="2">[situacao]</td>
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
						<td height="40" colspan="2" align="center">[botoes]</td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="30" colspan="2"><b>&nbsp;Comentários:</b></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:100%" rows="3" cols="52"></textarea></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="40" colspan="2" align="center"><input type="submit" name="salvar" class="buttonAzulEscuroBranco11" style="width:45px" value="Salvar"></td>
					</tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td colspan="2">[lista_comentarios]</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>

<script type="text/javascript">
	var cliente_id = [cliente_id];
	var iptv_id = [iptv_id];
	if (window.opener && !window.opener.closed) {
		if (window.opener.buscaIptv) window.opener.buscaIptv(cliente_id);
	}
</script>
