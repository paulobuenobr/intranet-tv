<script type="text/javascript" src="rds.js.php?m=pesquisas_aplicacoes"></script>
<script type="text/javascript" src="rds.js.php?m=chamados"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Ver&nbsp;</b></td></tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tbody id="grupo_dados_cliente">
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td height="30" colspan="2"><b>&nbsp;Dados do Cliente:</b></td>
	  </tr>
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td><b>&nbsp;&raquo; Cliente:</b></td>
	    <td>[nome]</td>
	  </tr>
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td><b>&nbsp;&raquo; Endereço:</b></td>
	    <td>[endereco]</td>
	  </tr>
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td><b>&nbsp;&raquo; Telefone(s):</b></td>
	    <td>[telefones]</td>
	  </tr>
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td><b>&nbsp;&raquo; E-mail(s):</b></td>
	    <td>[emails]</td>
	  </tr>
  </tbody>
  <tbody id="grupo_dados_condominio">
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td height="30" colspan="2"><b>&nbsp;Dados do Condomínio:</b></td>
	  </tr>
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td><b>&nbsp;&raquo; Nome:</b></td>
	    <td>[nome]</td>
	  </tr>
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	    <td><b>&nbsp;&raquo; Endereço:</b></td>
	    <td>[endereco]</td>
	  </tr>
  </tbody>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Dados do Chamado:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td style="width:20%"><b>&nbsp;&raquo; Código:</b></td>
    <td style="width:80%">[codigo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Solicitante:</b></td>
    <td>[solicitante]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Categoria:</b></td>
    <td>[categoria]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Responsável:</b></td>
    <td>[responsavel]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Prioridade:</b></td>
    <td>[prioridade]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Andamento:</b></td>
    <td>[andamento]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Datas:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Abertura:</b></td>
    <td>[abertura]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Encaminhamento:</b></td>
    <td>[encaminhamento]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Fechamento:</b></td>
    <td>[fechamento]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Duração:</b></td>
    <td>[duracao]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Visita Técnica:</b></td>
    <td>[data_visita_tecnica]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Status:</b></td>
    <td>[status]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Etapas:</b>
      <input type="radio" id="tipo_1" name="tipo_etapa" value="1" onclick="buscarEtapas([chamado_id], 0)" checked><label for="tipo_1">Resumida</label>&nbsp;
      <input type="radio" id="tipo_2" name="tipo_etapa" value="2" onclick="buscarEtapas([chamado_id], 1)"><label for="tipo_2">Detalhada</label>&nbsp;
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" id="etapas">[etapas]</td>
  </tr>
	<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" colspan="2" align="center">[botoes]</td>
  </tr>
</table>

<script type="text/javascript">
  var chamado_atual = '[chamado_id]';
  var instancia = '[instancia]';
  var mensagem_visualizacao = '[mensagem_visualizacao]';
  var atualiza_tela = setInterval('atualizaVisualizacao()', 60000);
  window.onbeforeunload = finalizaVisualizacao;
  if (mensagem_visualizacao != '') {
    alert(mensagem_visualizacao);
  }
</script>
