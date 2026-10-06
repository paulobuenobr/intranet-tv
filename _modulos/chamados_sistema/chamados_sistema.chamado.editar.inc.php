<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td style="width:24%"><b>&nbsp;Código:</b></td>
    <td style="width:76%">[codigo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Solicitante:</b></td>
    <td>[solicitante]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Categoria:</b></td>
    <td><select id="categoria" class="inputBrancoAzulEscuro11" style="width:100%" [editavel] onchange="chamado_alterado = true; mudaCategoria(this)"><option></option>[categoria]</select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Responsável:</b></td>
    <td><select id="responsavel" class="inputBrancoAzulEscuro11" style="width:100%" [editavel] onchange="chamado_alterado = true"><option></option>[responsavel]</select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="[exibir_chamado_pai]">
    <td><b>&nbsp;Vínculo:</b></td>
    <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="90%"><select id="chamado_pai" class="inputBrancoAzulEscuro11" style="width:100%" [editavel] onchange="chamado_alterado = true"><option></option>[chamado_pai]</select></td>
          <td width="5">&nbsp;</td>
          <td width="10%"><input type="button" id="ver_chamado_pai" value="Ver" class="buttonAzulEscuroBranco11" style="width:100%" onclick="verChamadoPai(event)"></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Prioridade:</b></td>
    <td><select id="prioridade" class="inputBrancoAzulEscuro11" style="width:150px" [editavel] onchange="chamado_alterado = true"><option></option>[prioridade]</select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Andamento:</b></td>
    <td><select id="andamento" class="inputBrancoAzulEscuro11" style="width:150px" [editavel] onchange="chamado_alterado = true"><option></option>[andamento]</select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Retorno:</b></td>
    <td colspan="2">
      <select name="retorno" class="inputBrancoAzulEscuro11" id="retorno" style="width:150px" onchange="chamado_alterado = true; mudaRetorno();" [editavel] onchange="chamado_alterado = true">[retorno]</select> 
      <input name="data_hora_retorno" id="data_hora_retorno" type="text" class="inputBrancoAzulEscuro11" style="width:120px;cursor:pointer" onClick="if (document.getElementById('retorno').value == 1) carregaTarefaCalendarioNavegacao(this.name, 1, this.value)" value="[data_hora_retorno]" maxlength="10" onKeyPress="return formataDataHora(this, event)" onBlur="return formataDataHora(this, event)" disabled  onchange="chamado_alterado = true">
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Datas:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Abertura:</b></td>
    <td>[abertura]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo;&nbsp;Encaminhamento:</b></td>
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
      <td height="30" colspan="2"><b>&nbsp;Chamados Vinculados:</b></td>
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

  [nova_etapa]

	<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" colspan="2" align="center">[botoes]</td>
  </tr>
</table>
