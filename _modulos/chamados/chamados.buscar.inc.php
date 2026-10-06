<script type="text/javascript" src="rds.js.php?m=clientes.contatos"></script>
<script type="text/javascript" src="rds.js.php?m=chamados"></script>
<form name="buscar" method="POST" action="[action]">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td>
	    	<table border="0" cellspacing="1" cellpadding="3" width="100%">
	      	<tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr>
	    	</table>
    	</td>
	  </tr>
	  <tr>
	    <td>
		    <table width="100%" border="0" cellspacing="1" cellpadding="3">
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td width="20%"><strong>&nbsp;Código:</strong></td>
		        <td width="80%"><input name="codigo" id="codigo" type="text" class="inputBrancoAzulEscuro11" maxlength="80" value="[codigo]" style="width:150"></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Cliente:</strong></td>
		        <td><input name="cliente" id="cliente" type="text" class="inputBrancoAzulEscuro11" maxlength="80" value="[cliente]" style="width:450"></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Condomínio:</strong></td>
		        <td>[condominio]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Categoria:</strong></td>
		        <td>[categoria]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Responsável:</strong></td>
		        <td>[responsavel]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td colspan="2" height="30"><strong>&nbsp;Período:</strong></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;&raquo; Data Abertura:</strong></td>
		        <td><input name="data_abertura_inicio" id="data_abertura_inicio" style="width:100" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_abertura_inicio]" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)"> até <input name="data_abertura_termino" id="data_abertura_termino" type="text" style="width:100" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_abertura_termino]" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)"> (dd/mm/aaaa)</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;&raquo; Data Fechamento:</strong></td>
		        <td><input name="data_fechamento_inicio" id="data_fechamento_inicio" style="width:100" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_fechamento_inicio]" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)"> até <input name="data_fechamento_termino" id="data_fechamento_termino" type="text" style="width:100" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_fechamento_termino]" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)"> (dd/mm/aaaa)</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;&raquo; Duração:</strong></td>
		        <td><input name="duracao_inicio" id="duracao_inicio" style="width:100" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[duracao_inicio]" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)"> até <input name="duracao_termino" id="duracao_termino" type="text" style="width:100" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[duracao_termino]" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)"> hora(s)</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Prioridade:</strong></td>
		        <td>[prioridade]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td><strong>&nbsp;Status:</strong></td>
		        <td>[status]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="40" colspan="2" align="center">[botoes]</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="30" colspan="2"><strong>&nbsp;Resultado:</strong></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td colspan="2" align="center">[resultado]</td>
		      </tr>
		    </table>
	    </td>
	  </tr>
	</table>
</form>
