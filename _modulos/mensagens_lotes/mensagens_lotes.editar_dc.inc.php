<script type="text/javascript" src="rds.js.php?m=mensagens_lotes" id="mensagens_lotes.js"></script>
<form name="editar" method="post" onSubmit="return validaMensagensLotesEditarDc(this)" action="[action]">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11">[acoes]</tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="15%"><b>&nbsp;&raquo; Nome:</b></td>
    <td width="85%"><input name="nome" id="nome" value="[nome]" type="text" class="inputBrancoAzulEscuro11" size="80" maxlength="80" style="width:100%" [edita_nome]></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Datas:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Início:</b></td>
    <td><input name="inicio" id="inicio" value="[inicio]" type="text" class="inputBrancoAzulEscuro11" size="80" maxlength="80" style="width:246px" onKeyPress="return formataData(this, event)" onblur="return formataData(this, event)" [datas_disabled]></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Término:</b></td>
    <td><input name="termino" id="termino" value="[termino]" type="text" class="inputBrancoAzulEscuro11" size="80" maxlength="80" style="width:246px" onKeyPress="return formataData(this, event)" onblur="return formataData(this, event)" [datas_disabled]></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Filtros:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2" id="filtros_listagem">[filtros]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Comentários:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" rows="3" cols="75" style="width: 100%"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2">[comentarios]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Status:</b></td>
    <td>[status]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" colspan="2" align="center">[botoes]</td>
  </tr>
</table>
<input type="hidden" name="filtros_lista" id="filtros_lista" value='[filtros_lista]'>
</form>
