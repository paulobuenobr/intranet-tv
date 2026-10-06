<form name="publicar" method="POST" onSubmit="return validaRetornosPublicar(this);" action="[action]" enctype="multipart/form-data">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Publicar&nbsp;</strong></td></tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="15%"><strong>&nbsp;Formato:</strong></td>
        <td width="85%">[formatos]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Arquivo:</strong></td>
        <td><input name="arquivo" size="80" id="arquivo" type="file" class="inputBrancoAzulEscuro11" style="height: auto; width: 100%">
        	<table border="0" cellspacing="0" cellpadding="0">
        		<tr class="txtAzulEscuro11">
        			<td><input type="checkbox" id="ignorar_sequencial" name="ignorar_sequencial" value="1"></td>
        			<td><label for="ignorar_sequencial">Ignorar sequencial</label></td>
        		</tr>
        	</table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
<script type="text/javascript">
  var mensagem = '[mensagem]';
  if (mensagem) alert(mensagem);
</script>
