<script type="text/javascript" src="rds.js.php?m=relatorios"></script>
<script type="text/javascript" src="_modulos/relatorios/relatorios.js"></script>
<form name="buscar" method="POST" onSubmit="return [funcoes];" action="[action]">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
	      <tr align="center" class="txtBranco11">
	        <td bgcolor="#505050"><strong>[acoes]</strong></td>
	      </tr>
	    </table></td>
	  </tr>
	  <tr>
	    <td>
				[filtros]
				[periodos]
				[intervalos]
	    	<table width="100%" border="0" cellspacing="1" cellpadding="3">
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display:[exibe_campos]">
		        <td height="30" colspan="2"><strong>&nbsp;Campos:</strong></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display:[exibe_campos]">
		        <td colspan="2">[campos]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td width="25%"><strong>&nbsp;Formato:</strong></td>
		        <td width="75%">
		          <label><input name="tipo_saida" id="exportar" type="radio" value="tela" checked> Tela</label>
		          <label><input name="tipo_saida" id="exportar" type="radio" value=";"> CSV (Excel)</label> &nbsp; 
		          <label><input name="tipo_saida" id="exportar" type="radio" value=","> CSV (Open Office)</label> &nbsp; 
		        </td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="40" colspan="2" align="center">[botoes]
		        <input name="periodo" id="periodo" type="hidden" value="[periodo]"></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		      	<td colspan="2" align="center" height="20">[resultado]</td>
		      </tr>
		    </table>
	    </td>
	  </tr>
	</table>
</form>
