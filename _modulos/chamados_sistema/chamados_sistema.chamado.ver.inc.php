<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td style="width:23%"><b>&nbsp;Código:</b></td>
    <td style="width:77%">[codigo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Solicitante:</b></td>
    <td>[solicitante]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Categoria:</b></td>
    <td>[categoria]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Responsável:</b></td>
    <td>[responsavel]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="[exibir_chamado_pai]">
    <td><b>&nbsp;Vínculo:</b></td>
    <td>[chamado_pai]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Prioridade:</b></td>
    <td>[prioridade]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Andamento:</b></td>
    <td>[andamento]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Retorno:</b></td>
    <td>[data_hora_retorno]</td>
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
    <td id="data_visita">[data_visita_tecnica]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Status:</b></td>
    <td>[status]</td>
  </tr>
	<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" colspan="2" align="center">[botoes]</td>
  </tr>
  <tbody id="chamados_filho" style="[exibir_chamados_filho]">
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td height="30" colspan="2"><b>&nbsp;Vínculos:</b></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td colspan="2">[listagem_chamados_filho]</td>
    </tr>
  </tbody>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Etapas:</b>
      <input type="radio" id="tipo_1" name="tipo_etapa" value="1" onclick="buscarEtapasChamado()" [resumida_checked]><label for="tipo_1">Resumida</label>&nbsp;
      <input type="radio" id="tipo_2" name="tipo_etapa" value="2" onclick="buscarEtapasChamado()" [detalhada_checked]><label for="tipo_2">Detalhada</label>&nbsp;
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" id="etapas">[etapas]</td>
  </tr>
	<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" colspan="2" align="center">[botoes]</td>
  </tr>
</table>
