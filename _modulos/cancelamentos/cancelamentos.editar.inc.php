<script type="text/javascript" src="rds.js.php?m=cancelamentos"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Editar&nbsp;</b></td></tr>
</table>
<form name="editar" method="POST" onSubmit="return validaCancelamentoEditar(this)" action="[action]">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><b>&nbsp;Cliente:</b></td>
    <td width="80%">[cliente]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Endereço:</b></td>
    <td>[endereco]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Contrato:</b></td>
    <td>[contrato]</td>
  </tr>
  <tbody style="display:[exibir_mensagem_vigencia]">
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="color:#FF0000">
	    <td colspan="2" align="center" colspan="2">
	      <b>Atenção!</b><br>
	      Este é um contrato com plano de fidelidade.<br>
	      Vigência: [vigencia].
	    </td>
	  </tr>
  </tbody>
  <tbody style="display:[exibir_mensagem_comodato]">
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="color:#FF0000">
	    <td colspan="2" align="center" colspan="2">
	      <b>Atenção!</b><br>
	      Este contrato tem o(s) seguinte(s) esquipamento(s) em comodato:<br>
	      [equipamentos_comodato].
	    </td>
	  </tr>
  </tbody>
  <tbody style="display:[mensagem_inadimplente]">
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td colspan="2" style="color:#FF0000;text-align:center"><br>
        <b>Atenção!</b><br>
        Este cliente possui [cobrancas] cobrança(s) em atraso e o cancelamento / alteração<br>
        de seus contratos só será possível após a quitação destes débitos.<br>
        <br>
      </td>
    </tr>
  </tbody>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Origem:</b></td>
    <td>[origem]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Motivo:</b></td>
    <td>[motivo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Datas:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Abertura:</b></td>
    <td>[inicio]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Fechamento:</b></td>
    <td>[termino]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Etapas:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2">[etapas]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Nova Etapa:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Tipo:</b></td>
    <td>[etapa_tipo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Nível de Stress:</b></td>
    <td>[etapa_nivel]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Conteúdo:</b></td>
    <td id="etapa_conteudo"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Status:</b></td>
    <td>[status]</td>
  </tr>
	<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" colspan="2" align="center">[botoes]</td>
  </tr>
</table>
</form>

<script type="text/javascript">

  var etapas_cumpridas = [[etapas_cumpridas]];

  if (window.opener && window.opener.buscaCancelamentosCliente) {
    window.opener.buscaCancelamentosCliente([cliente_id]);
  }

  if (window.opener && window.opener.buscaContratosCliente) {
    window.opener.buscaContratosCliente([cliente_id]);
  }

  if (window.opener && window.opener.buscaAjustesCliente) {
    window.opener.buscaAjustesCliente([cliente_id]);
  }

</script>

<div id="etapa_conteudo_1" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
  <b>Origem:</b><br>
  [etapa_conteudo_1_origem]<br>
  <b>Comentário:</b><br>
  <textarea name="etapa_conteudo_1_comentario" id="etapa_conteudo_1_comentario" class="textBrancoAzulEscuro11" rows="4" cols="50" style="width: 100%"></textarea>
</div>

<div id="etapa_conteudo_2" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
  <b>Motivo:</b><br>
  [etapa_conteudo_2_motivo]<br>
  <b>Comentário:</b><br>
  <textarea name="etapa_conteudo_2_comentario" id="etapa_conteudo_2_comentario" class="textBrancoAzulEscuro11" rows="4" cols="50" style="width: 100%"></textarea>
</div>

<div id="etapa_conteudo_3" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
  <b>Descrição:</b><br>
  <textarea name="etapa_conteudo_3_descricao" id="etapa_conteudo_3_descricao" class="textBrancoAzulEscuro11" rows="4" cols="50" style="width: 100%"></textarea>
</div>

<div id="etapa_conteudo_4" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
  <span id="etapa_conteudo_4_descarte_etapa_3"></span>
  <b>Usuário:</b><br>
  [etapa_conteudo_4_usuario]<br>
  <b>Descrição:</b><br>
  <textarea name="etapa_conteudo_4_descricao" id="etapa_conteudo_4_descricao" class="textBrancoAzulEscuro11" rows="4" cols="50" style="width: 100%"></textarea>
</div>

<div id="etapa_conteudo_4_conteudo_descarte_etapa_3" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
  <b><font color="#FF0000">Registro de Negociação (Motivo do Descarte):</font></b><br>
  <textarea name="etapa_conteudo_4_conteudo_descarte_etapa_3_motivo" id="etapa_conteudo_4_conteudo_descarte_etapa_3_motivo" class="textBrancoAzulEscuro11" rows="2" cols="50" style="width: 100%"></textarea>
</div>

<div id="etapa_conteudo_5" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
  <b>Data:</b><br>
  <input name="etapa_conteudo_5_data" id="etapa_conteudo_5_data" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" style="width: 100%"><br>
  <b>Período:</b><br>
  [etapa_conteudo_5_periodo]<br>
  <b>Local:</b><br>
  [etapa_conteudo_5_local]<br>
  <b>Comentário:</b><br>
  <textarea name="etapa_conteudo_5_comentario" id="etapa_conteudo_5_comentario" class="textBrancoAzulEscuro11" rows="4" cols="50" style="width: 100%"></textarea>
</div>

<div id="etapa_conteudo_6" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
  <span id="etapa_conteudo_6_descarte_etapa_3"></span>
  <span id="etapa_conteudo_6_descarte_etapa_4"></span>
  <b>Motivo:</b><br>
  [etapa_conteudo_6_motivo]<br>
  <b>Duração (em dias):</b><br>
  <input name="etapa_conteudo_6_duracao" id="etapa_conteudo_6_duracao" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="2" onKeyPress="return formataDuracao(this,event);" onBlur="return formataDuracao(this,event);" style="width: 100%"><br>
  <b>Comentário:</b><br>
  <textarea name="etapa_conteudo_6_comentario" id="etapa_conteudo_6_comentario" class="textBrancoAzulEscuro11" rows="4" cols="50" style="width: 100%"></textarea>
</div>

<div id="etapa_conteudo_6_conteudo_descarte_etapa_3" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
  <b><font color="#FF0000">Registro de Negociação (Motivo do Descarte):</font></b><br>
  <textarea name="etapa_conteudo_6_conteudo_descarte_etapa_3_motivo" id="etapa_conteudo_6_conteudo_descarte_etapa_3_motivo" class="textBrancoAzulEscuro11" rows="2" cols="50" style="width: 100%"></textarea>
</div>

<div id="etapa_conteudo_6_conteudo_descarte_etapa_4" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
  <b><font color="#FF0000">Registro de Auxilio (Motivo do Descarte):</font></b><br>
  <textarea name="etapa_conteudo_6_conteudo_descarte_etapa_4_motivo" id="etapa_conteudo_6_conteudo_descarte_etapa_4_motivo" class="textBrancoAzulEscuro11" rows="2" cols="50" style="width: 100%"></textarea>
</div>

<div id="etapa_conteudo_7" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
  <b>Data:</b><br>
  <input name="etapa_conteudo_7_data" id="etapa_conteudo_7_data" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" style="width: 100%"><br>
  <b>Formato:</b><br>
  [etapa_conteudo_7_formato]<br>
  <b>Comentário:</b><br>
  <textarea name="etapa_conteudo_7_comentario" id="etapa_conteudo_7_comentario" class="textBrancoAzulEscuro11" rows="4" cols="50" style="width: 100%"></textarea>
</div>

<div id="etapa_conteudo_8" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
  <b>Data:</b><br>
  <input name="etapa_conteudo_8_data" id="etapa_conteudo_8_data" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" style="width: 100%"><br>
  <b>Comentário:</b><br>
  <textarea name="etapa_conteudo_8_comentario" id="etapa_conteudo_8_comentario" class="textBrancoAzulEscuro11" rows="4" cols="50" style="width: 100%"></textarea>
</div>

<div id="etapa_conteudo_9" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
  <b>Novo Endereço:</b><br>
  <textarea name="etapa_conteudo_9_novo_endereco" id="etapa_conteudo_9_novo_endereco" class="textBrancoAzulEscuro11" rows="2" cols="50" style="width: 100%"></textarea>
  <b>Novo Morador:</b><br>
  <textarea name="etapa_conteudo_9_novo_morador" id="etapa_conteudo_9_novo_morador" class="textBrancoAzulEscuro11" rows="2" cols="50" style="width: 100%"></textarea>
  <b>Comentário:</b><br>
  <textarea name="etapa_conteudo_9_comentario" id="etapa_conteudo_9_comentario" class="textBrancoAzulEscuro11" rows="2" cols="50" style="width: 100%"></textarea>
</div>

<div id="etapa_conteudo_10" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
  <b>Comentário:</b><br>
  <textarea name="etapa_conteudo_10_comentario" id="etapa_conteudo_10_comentario" class="textBrancoAzulEscuro11" rows="4" cols="50" style="width: 100%"></textarea>
</div>
