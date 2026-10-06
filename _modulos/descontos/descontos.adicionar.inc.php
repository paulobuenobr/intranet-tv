<script type="text/javascript" src="_modulos/descontos/rds.descontos.js.php"></script>
<form name="adicionar" method="POST" action="[action]" onSubmit="return validaModuloDesconto(this);">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="30%"><strong>&nbsp;Nome:</strong></td>
        <td><input type="text" name="nome" id="nome" class="inputBrancoAzulEscuro11" style="width:350px" maxlength="50"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Serviço:</strong></td>
        <td><select name="servico" class="inputBrancoAzulEscuro11" style="width:350px" onchange="mudaServico(this.value)">[servico]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Origens:</strong></td>
        <td><select name="origens[]" id="origens" class="inputBrancoAzulEscuro11" style="width:350px;height:145px" multiple size="10">[origens]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Planos Válidos:</strong></td>
        <td><select name="planos[]" id="planos" class="textBrancoAzulEscuro11" style="width:350px;height:145px" multiple size="10" disabled></select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Descontos Auto-Excludentes:</strong></td>
        <td><select name="descontos[]" id="descontos" class="textBrancoAzulEscuro11" style="width:350px;height:145px" multiple size="10" disabled></select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Sistema:</strong></td>
        <td><select name="sistema" class="inputBrancoAzulEscuro11" style="width:350px">[sistema]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td><select name="tipo" class="inputBrancoAzulEscuro11" style="width:174px">[tipo]</select>
          <input name="fidelidade" id="fidelidade" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:173px">
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><strong>&nbsp;Valores:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30"><strong>&nbsp;&raquo; Instalação:</strong></td>
        <td><select name="instalacao_tipo" id="instalacao_tipo" class="inputBrancoAzulEscuro11" style="width:100" onChange="mudaDescontoValorTipo(this)">[tipos]</select>&nbsp;<input name="instalacao" id="instalacao" type="text" class="inputBrancoAzulEscuro11" style="width:147" onKeyPress="return formataDesconto(this,event,this.form.instalacao_tipo.value)" onBlur="return formataDesconto(this,event,this.form.instalacao_tipo.value)" disabled>&nbsp;<select name="instalacao_fixa" class="inputBrancoAzulEscuro11" style="width:90">[fixas]</select>&nbsp;<input name="parcelamento_maximo" id="parcelamento_maximo" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:60px"> X</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30"><strong>&nbsp;&raquo; Mensalidade:</strong></td>
        <td><select name="mensalidade_tipo" id="mensalidade_tipo" class="inputBrancoAzulEscuro11" style="width:100" onChange="mudaDescontoValorTipo(this)">[tipos]</select>&nbsp;<input name="mensalidade" id="mensalidade" type="text" class="inputBrancoAzulEscuro11" style="width:147" onKeyPress="return formataDesconto(this,event,this.form.mensalidade_tipo.value)" onBlur="return formataDesconto(this,event,this.form.mensalidade_tipo.value)" disabled>&nbsp;<select name="mensalidade_fixa" class="inputBrancoAzulEscuro11" style="width:90">[fixas]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30"><strong>&nbsp;&raquo; Validade:</strong></td>
        <td><select name="validade_tipo" id="validade_tipo" class="inputBrancoAzulEscuro11" style="width:100" onChange="mudaValidadeTipo(this)">[validade_tipo]</select>&nbsp;<input tabindex="text" name="validade" id="validade" class="inputBrancoAzulEscuro11" style="width:147" onKeyPress="return formataValidade(this, event)" onBlur="return formataValidade(this, event)" disabled>&nbsp;<select name="validade_fixa" class="inputBrancoAzulEscuro11" style="width:90">[fixas]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Aplicações:</strong></td>
        <td>
          <table width="60%" border="0" cellspacing="0" cellpadding="0">
            <tr class="txtAzulEscuro11">
              <td><input type="checkbox" name="aplica_plano_principal" id="aplica_plano_principal" [aplica_plano_principal]> <label for="aplica_plano_principal">Assinaturas (Plano Principal)</label></td>
              <td><input type="checkbox" name="aplica_condominio" id="aplica_condominio" [aplica_condominio]> <label for="aplica_condominio">Condomínios</label></td>
            </tr>
            <tr class="txtAzulEscuro11">
              <td><input type="checkbox" name="aplica_plano_adicional" id="aplica_plano_adicional" [aplica_plano_adicional]> <label for="aplica_plano_adicional">Assinaturas (Plano Adicional)</label></td>
              <td><input type="checkbox" name="aplica_plano" id="aplica_plano" [aplica_plano]> <label for="aplica_plano">Planos</label></td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Carência (dias após cancelamento):</strong></td>
        <td><input type="text" name="carencia" id="carencia" class="inputBrancoAzulEscuro11" style="width:250" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Cota Mensal por Vendedor:</strong></td>
        <td><input type="text" name="cota_mensal" id="cota_mensal" class="inputBrancoAzulEscuro11" style="width:250" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Auto-Aprovado:</strong></td>
        <td><select name="auto_aprovado" class="inputBrancoAzulEscuro11" style="width:15%">[auto_aprovado]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Adicionar Retorno:</strong></td>
        <td><select name="adicionar_retorno" class="inputBrancoAzulEscuro11" style="width:15%">[adicionar_retorno]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td><select name="status" class="inputBrancoAzulEscuro11" style="width:15%">[status]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
