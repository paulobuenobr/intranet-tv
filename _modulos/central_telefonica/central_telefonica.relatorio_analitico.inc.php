<form name="relatorio_detalhado" method="POST" action="[action]">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td>
		    <table border="0" cellspacing="1" cellpadding="3" width="100%">
		      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Relatório Analítico&nbsp;</strong></td></tr>
		    </table>
	    </td>
	  </tr>
	  <tr>
	    <td>
				<table width="100%" border="0" cellspacing="1" cellpadding="3">
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td width="120"><strong>&nbsp;Data:</strong></td>
		        <td><input name="data_inicio" id="data_inicio" type="text" style="width:80px" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[data_inicio]" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)">
							<input name="data_fim" id="data_fim" type="text" style="width:80px" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[data_fim]" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)">
		        </td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Tipo:</strong></td>
        		<td>[tipo]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td width="120"><strong>&nbsp;Setor:</strong></td>
        		<td>[setor]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Agrupadores:</strong></td>
		        <td>[agrupadores]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Campos:</strong></td>
		        <td>[campos]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Opções:</strong></td>
		        <td>[opcoes]</td>
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

<script type="text/javascript">
	$('.subir-linha').click(function() {
		$(this).closest('tr').insertBefore($(this).closest('tr').prev());
	});
	$('.descer-linha').click(function() {
		$(this).closest('tr').insertAfter($(this).closest('tr').next());
	});
</script>
