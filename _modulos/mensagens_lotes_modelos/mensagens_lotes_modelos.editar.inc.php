<form name="adicionar" method="post" onSubmit="return validaMensagensLotesModelosEditar(this)" action="[action]">
<script type="text/javascript" src="rds.js.php?m=mensagens_lotes_modelos"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Editar&nbsp;</b></td></tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><b>&nbsp;Nome:</b></td>
    <td width="80%">[nome]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><b>&nbsp;Tipo:</b></td>
    <td width="80%">[tipo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><strong>&nbsp;Conteúdo Texto:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2"><textarea name="conteudo_texto" id="conteudo_texto" class="textBrancoAzulEscuro11" rows="15" cols="75" style="font-family:Courier New; width:100%">[conteudo_texto]</textarea></td>
  </tr>
  <tbody id="conteudo_email_html" style="display:[mostra_conteudo_html]">
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td height="30" colspan="2"><strong>&nbsp;Conteúdo HTML:</strong></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td colspan="2"><textarea name="conteudo_html" id="conteudo_html" class="textBrancoAzulEscuro11" rows="15" style="font-family:Courier New; width:100%">[conteudo_html]</textarea></td>
    </tr>
  </tbody>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><b>&nbsp;Versão:</b></td>
    <td width="80%">[versao]</td>
  </tr>
	<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" colspan="2" align="center">[botoes]</td>
  </tr>
</table>
<input name="nome" id="nome" type="hidden" value="[nome]">
</form>

<script type="text/javascript">
  if (window.opener && window.opener.atualizarListagem) {
    window.opener.atualizarListagem();
  }
  if (window.opener && window.opener.atualizaListaModelos) {
    window.opener.atualizaListaModelos([modelo_id]);
  }
</script>
