<script type="text/javascript" src="rds.js.php?m=mensagens_lotes"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Adicionar&nbsp;</b></td></tr>
</table>
<form name="adicionar" method="post" onSubmit="return validaMensagensLotesAdicionar(this)" action="[action]">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="25%"><b>&nbsp;&raquo; Nome:</b></td>
    <td width="75%"><input name="lote_nome" id="lote_nome" type="text" class="inputBrancoAzulEscuro11" maxlength="80" style="width:100%"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Datas:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Início:</b></td>
    <td><input name="inicio" id="inicio" type="text" class="inputBrancoAzulEscuro11" style="width:246px" onKeyPress="return formataData(this, event);" onblur="return formataData(this, event);"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Término:</b></td>
    <td><input name="termino" id="termino" type="text" class="inputBrancoAzulEscuro11" style="width:246px" onKeyPress="return formataData(this, event);" onblur="return formataData(this, event);"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Dados da Mensagem:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Tipo:</b></td>
    <td><select name="tipo" id="tipo" style="width:246px" class="inputBrancoAzulEscuro11" onchange="mudaTipo(this.value)">[tipos]</select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Modelo:</b></td>
    <td><select name="modelo" id="modelo" style="width:246px" class="inputBrancoAzulEscuro11">[modelos]</select> [ver_modelo]</td>
  </tr>
  <tbody id="conteudo_tipo_1">
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; Destino:</b></td>
      <td><select name="destino" id="destino" style="width:246px" class="inputBrancoAzulEscuro11">[destinos]</select></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; Remetente (Nome / E-mail):</b></td>
      <td><input name="remetente_nome" id="remetente_nome" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:246px"> <input name="remetente_email" id="remetente_email" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:300px"></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; CC (Nome / E-mail):</b></td>
      <td><input name="cc_nome" id="cc_nome" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:246px"> <input name="cc_email" id="cc_email" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:300px"></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; BCC (Nome / E-mail):</b></td>
      <td><input name="bcc_nome" id="bcc_nome" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:246px"> <input name="bcc_email" id="bcc_email" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:300px"></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; Reply (Nome / E-mail):</b></td>
      <td><input name="reply_nome" id="reply_nome" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:246px"> <input name="reply_email" id="reply_email" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:300px"></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; Assunto:</b></td>
      <td><input name="assunto" id="assunto" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:100%"></td>
    </tr>
  </tbody>
  <tbody id="conteudo_tipo_2" style="display:none">
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; Remetente:</b></td>
      <td><input name="remetente" id="remetente" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:246px"></td>
    </tr>
  </tbody>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Dados de Envio:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Quantidade:</b></td>
    <td><input name="envio_quantidade" id="envio_quantidade" type="text" class="inputBrancoAzulEscuro11" maxlength="5" style="width:90px" onKeyPress="return formataNumero(this, event)"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Intervalo:</b></td>
    <td><input name="envio_intervalo" id="envio_intervalo" type="text" class="inputBrancoAzulEscuro11" maxlength="5" style="width:90px" onKeyPress="return formataNumero(this, event)"> <select name="envio_intervalo_tipo" id="envio_intervalo_tipo" style="width:153px" class="inputBrancoAzulEscuro11">[tipos_intervalo]</select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Filtros:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2" id="filtros_listagem">[filtros]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" rows="3" cols="75" style="width: 100%"></textarea></td>
  </tr>
	<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" colspan="2" align="center">[botoes]</td>
  </tr>
</table>
<input type="hidden" name="filtros_lista" id="filtros_lista" value=''>
</form>
