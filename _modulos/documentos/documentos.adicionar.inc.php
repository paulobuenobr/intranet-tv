<script type="text/javascript" src="_modulos/documentos/documentos.js"></script>
<form name="adicionar" method="post" onSubmit="return validaDocumentosAdicionar(this)" action="[action]" enctype="multipart/form-data">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11">[acoes]</tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><b>&nbsp;Nome:</b></td>
    <td width="80%"><input name="nome" id="nome" value="" type="text" class="inputBrancoAzulEscuro11" style="width:100%"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Categoria:</b></td>
    <td><select name="categoria" id="categoria" class="inputBrancoAzulEscuro11" style="width:100%"><option></option>[categorias]</select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Tipo:</b></td>
    <td><select name="tipo" id="tipo" class="inputBrancoAzulEscuro11" style="width:200px" onchange="mudaDocumentosTipo(this.value);"><option></option>[tipos]</select></td>
  </tr>
  <tbody id="tipo_arquivo">
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><strong>&nbsp;Anexo:</strong></td>
      <td><input name="arquivo" id="arquivo" type="file" class="inputBrancoAzulEscuro11"></td>
    </tr>
  </tbody>
  <tbody id="tipo_conteudo" style="display:none">
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td height="30" colspan="2"><strong>&nbsp;Conteúdo:</strong></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td colspan="2"><textarea name="conteudo" id="conteudo" class="textBrancoAzulEscuro11" rows="15" style="font-family:Courier New; width:100%"></textarea></td>
    </tr>
  </tbody>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="3" cols="75" style="width: 100%"></textarea></td>
  </tr>
	<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" colspan="2" align="center">[botoes]</td>
  </tr>
</table>
</form>
