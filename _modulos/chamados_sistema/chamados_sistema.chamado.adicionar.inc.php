<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><b>&nbsp;Solicitante:</b></td>
    <td width="80%"><input id="solicitante" value="[solicitante]" type="text" class="inputBrancoAzulEscuro11" size="80" maxlength="80" style="width:100%" onkeypress="executarComando(event, 'adicionarChamadoSalvar')" onchange="chamado_alterado = true" [solicitante_editavel]></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Nível de Stress:</b></td>
    <td><select id="nivel_stress" class="inputBrancoAzulEscuro11" style="width: 100%" onkeypress="executarComando(event, 'adicionarChamadoSalvar')" onchange="chamado_alterado = true"><option></option>[nivel_stress]</select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Categoria:</b></td>
    <td><select id="categoria" class="inputBrancoAzulEscuro11" style="width: 100%" onkeypress="executarComando(event, 'adicionarChamadoSalvar')" onchange="chamado_alterado = true; mudaCategoria(this, true);"><option></option>[categoria]</select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Responsável:</b></td>
    <td><select id="responsavel" class="inputBrancoAzulEscuro11" style="width:100%" onchange="chamado_alterado = true" disabled><option></option>[responsavel]</select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Prioridade:</b></td>
    <td><select id="prioridade" class="inputBrancoAzulEscuro11" style="width: 150" onkeypress="executarComando(event, 'adicionarChamadoSalvar')" onchange="chamado_alterado = true"><option></option>[prioridade]</select></td>
  </tr>

  [nova_etapa]
	<!--solicitacao_fechamento-->
  <tbody>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td height="30" colspan="2"><b>&nbsp;Solicitação de Fechamento:</b></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; Responsável:</b></td>
      <td><select id="responsavel_fechamento" class="inputBrancoAzulEscuro11" style="width:100%"><option></option>[responsavel_fechamento]</select></td>
    </tr>
	<!--/solicitacao_fechamento-->
  </tbody>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Opções:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2">
      <table cellpadding="0" cellspacing="0" border="0">
        <tr class="txtAzulEscuro11">
          <td><input type="checkbox" id="acompanhar">&nbsp;</td>
          <td><label for="acompanhar">Acompanhar Chamado</label></td>
        </tr>
        <tr class="txtAzulEscuro11">
          <td><input type="checkbox" id="favoritos">&nbsp;</td>
          <td><label for="favoritos">Adicionar ao Favoritos</label></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" colspan="2" align="center">[botoes]</td>
  </tr>
</table>
<input type="hidden" name="lista_telefones" id="lista_telefones" value='[lista_telefones]'>
<input type="hidden" name="lista_emails" id="lista_emails" value='[lista_emails]'>
<input type="hidden" name="cliente_id" id="cliente_id" value='[cliente_id]'>
