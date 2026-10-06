<form name="editar" method="post" onSubmit="return validaMensagensLotesEditarDe(this)" action="[action]">
<script type="text/javascript" src="rds.js.php?m=mensagens_lotes" id="mensagens_lotes.js"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11">[acoes]</tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="25%"><b>&nbsp;&raquo; Nome:</b></td>
    <td width="75%">[nome]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Dados da Mensagem:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Tipo:</b></td>
    <td><select name="tipo" id="tipo" style="width:246px" class="inputBrancoAzulEscuro11" onchange="mudaTipo(this.value)">[tipo]</select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Modelo:</b></td>
    <td><select name="modelo" id="modelo" style="width:246px" class="inputBrancoAzulEscuro11"><option></option>[modelos]</select> [ver_modelo]</td>
  </tr>
  <tbody id="conteudo_tipo_1" style="display:[mostra_conteudo_tipo_1]">
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; Destino:</b></td>
      <td><select name="destino" id="destino" style="width:246px" class="inputBrancoAzulEscuro11">[destinos]</select></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; Remetente (Nome / E-mail):</b></td>
      <td><input name="remetente_nome" id="remetente_nome" value="[remetente_nome]" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:246px"> <input name="remetente_email" id="remetente_email" value="[remetente_email]" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:300px"></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; CC (Nome / E-mail):</b></td>
      <td><input name="cc_nome" id="cc_nome" value="[cc_nome]" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:246px"> <input name="cc_email" id="cc_email" value="[cc_email]" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:300px"></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; BCC (Nome / E-mail):</b></td>
      <td><input name="bcc_nome" id="bcc_nome" value="[bcc_nome]" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:246px"> <input name="bcc_email" id="bcc_email" value="[bcc_email]" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:300px"></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; Reply (Nome / E-mail):</b></td>
      <td><input name="reply_nome" id="reply_nome" value="[reply_nome]" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:246px"> <input name="reply_email" id="reply_email" value="[reply_email]" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:300px"></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; Assunto:</b></td>
      <td><input name="assunto" id="assunto" value="[assunto]" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:100%"></td>
    </tr>
  </tbody>
  <tbody id="conteudo_tipo_2" style="display:[mostra_conteudo_tipo_2]">
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; Remetente:</b></td>
      <td><input name="remetente" id="remetente" value="[remetente]" type="text" class="inputBrancoAzulEscuro11" maxlength="250" style="width:246px"></td>
    </tr>
  </tbody>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Dados de Envio:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Quantidade:</b></td>
    <td><input name="envio_quantidade" id="envio_quantidade" value="[envio_quantidade]" type="text" class="inputBrancoAzulEscuro11" maxlength="5" style="width:90px" onKeyPress="return formataNumero(this, event)"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Intervalo:</b></td>
    <td><input name="envio_intervalo" id="envio_intervalo" value="[envio_intervalo]" type="text" class="inputBrancoAzulEscuro11" maxlength="5" style="width:90px" onKeyPress="return formataNumero(this, event)"> <select name="envio_intervalo_tipo" id="envio_intervalo_tipo" class="inputBrancoAzulEscuro11" style="width:153px">[envio_intervalo_tipo]</select></td>
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
