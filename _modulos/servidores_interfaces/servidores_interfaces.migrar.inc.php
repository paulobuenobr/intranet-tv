<script type="text/javascript" src="rds.js.php?m=servidores_interfaces"></script>
<form name="migrar" method="POST" action="[action]" onSubmit="return validaMigrar(this)">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11">[acoes]</tr>
    </table></td>
  </tr>
  <tr>
    <td>
	    <table width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
			    <td height="30" colspan="2"><strong>&nbsp;Origem:</strong></td>
			  </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td width="17%"><strong>&nbsp;&raquo; Servidor:</strong></td>
	        <td width="83%">[servidor_nome]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Interface:</strong></td>
	        <td>[nome]</td>
	      </tr>
			  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
			    <td height="30" colspan="2"><strong>&nbsp;Destino:</strong></td>
			  </tr>
			  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
			    <td><strong>&nbsp;&raquo; Servidor:</strong></td>
			    <td><select name="servidor" id="servidor" id="tipo" class="inputBrancoAzulEscuro11" style="width:100%" onchange="buscaInterfaces(this.value, [interface_tipo_id])">[servidor]</select></td>
			  </tr>
			  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
			    <td><strong>&nbsp;&raquo; Interface:</strong></td>
			    <td><select name="interface" id="interface" id="tipo" class="inputBrancoAzulEscuro11" style="width:50%" disabled></select></td>
			  </tr>
			  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
			    <td height="40" align="center" colspan="2">[botoes]</td>
			  </tr>
			</table>
		</td>
	</tr>
</table>
</form>
