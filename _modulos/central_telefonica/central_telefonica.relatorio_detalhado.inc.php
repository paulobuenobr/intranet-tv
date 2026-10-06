<script language="javascript">
	var janela;
	function ouvirArquivo (arquivo) {
		if (janela) janela.close();
		janela = window.open("", "ouvir", "height=100,width=350,resizable=no,menubar=no,scrollbars=no,status=no,titlebar=no,toolbar=no");
		janela.document.write("<html>");
		janela.document.write("<body>");
		janela.document.write("<table width=\"100%\" height=\"100%\" border=\"0\"><tr><td align=\"center\" valign=\"middle\">");
		janela.document.write("<object type=\"audio/x-wav\" data=\"" + arquivo + "\" width=\"300\" height=\"50\">");
		janela.document.write("<param name=\"src\" value=\"" + arquivo + "\">");
		janela.document.write("<param name=\"autoplay\" value=\"true\">");
		janela.document.write("<param name=\"controlelr\" value=\"true\">");
		janela.document.write("<param name=\"autoStart\" value=\"0\">");
		janela.document.write("</object>");
		janela.document.write("</td></tr></table>");
		janela.document.write("</body>");
		janela.document.write("</html>");
		janela.focus();
	}
	function salvarArquivo (arquivo) {
		window.location = arquivo;
	}
</script>

<form name="relatorio_detalhado" method="POST" action="[action]">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td>
		    <table border="0" cellspacing="1" cellpadding="3" width="100%">
		      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Relatório Detalhado&nbsp;</strong></td></tr>
		    </table>
	    </td>
	  </tr>
	  <tr>
	    <td>
				<table width="100%" border="0" cellspacing="1" cellpadding="3">
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td width="18%"><strong>&nbsp;Origem:</strong></td>
		        <td width="82%">
        		  [origem_tipo]
		        	<input name="origem" id="origem" type="text" style="width:140px" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[origem]" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)">
		        	(Quem efetuou a ligação)
	        	</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Destino:</strong></td>
		        <td>
        		  [destino_tipo]
		        	<input name="destino" id="destino" type="text" style="width:140px" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[destino]" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)">
		        	(Quem recebeu a ligação)
	        	</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Setor origem:</strong></td>
        		<td>
        			[setor_origem]
        			(Setor que efetuou a ligação)
        		</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Setor destino:</strong></td>
        		<td>
        			[setor_destino]
        			(Setor que recebeu a ligação)
        		</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Fila:</strong></td>
		        <td>
		        	<input name="fila" id="fila" type="text" style="width:243px" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[fila]">
	        	</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Período (data):</strong></td>
		        <td>
		          <input name="inicio" id="inicio" type="text" style="width:77px" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[inicio]" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)">
		          <input name="fim" id="fim" type="text" style="width:77px" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[fim]" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)">
		        </td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Período (hora):</strong></td>
		        <td>
		          <input name="hora_inicio" id="hora_inicio" type="text" style="width:40px" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[hora_inicio]" onKeyPress="return formataHora(this, event)" onBlur="return formataHora(this, event)">
		          <input name="hora_fim" id="hora_fim" type="text" style="width:40px" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[hora_fim]" onKeyPress="return formataHora(this, event)" onBlur="return formataHora(this, event)">
		        </td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Tipo:</strong></td>
        		<td>[tipo]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Situação:</strong></td>
        		<td>[situacao]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Opções:</strong></td>
		        <td>
							<table width="100%" border="0" cellspacing="0" cellpadding="1">
								<tr>
									<td align="left" width="15"><input type="checkbox" id="exportar_csv" value="exportar_csv" style="margin: 0px;" name="opcoes[]"></td>
									<td align="left" class="txtAzulEscuro11"><label for="exportar_csv">Exportar para CSV</label></td>
								</tr>
							</table>
		        </td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="40" colspan="2" align="center">[botoes]</td>
		      </tr>
					<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
						<td height="30" colspan="2">[resultado]</td>
					</tr>
		    </table>	    
	    </td>
	  </tr>
	</table>
</form>
