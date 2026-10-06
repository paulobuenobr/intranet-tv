<script type="text/javascript" src="rds.js.php?m=chamados_categorias"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Editar&nbsp;</b></td></tr>
</table>
<form name="editar" method="POST" onSubmit="return validaCategoria(this)" action="[action]">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td style="width:17%"><b>&nbsp;Nome:</b></td>
    <td style="width:83%"><input name="nome" id="nome" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="80" style="width: 70%" value="[nome]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Categoria Pai:</b></td>
    <td>[pai]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Restrita:</b></td>
    <td>[restrita]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Etapas:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" id="listagem_etapas">[etapas]</td>
  </tr>
	<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" colspan="2" align="center">[botoes]</td>
  </tr>
</table>
<input type="hidden" name="lista_etapas" id="lista_etapas" value='[lista_etapas]'>
</form>
