  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Nova Etapa:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Tipo:</b></td>
    <td><select id="tipo" class="inputBrancoAzulEscuro11" style="width:100%" onchange="mudaChamadoEtapaTipo(this.value)" [editavel]><option></option>[tipo]</select></td>
  </tr>
  
  <tbody id="etapa_conteudo_20" style="[etapa_conteudo_20_display]">
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; MAC:</b></td>
      <td><select id="mac" class="inputBrancoAzulEscuro11" style="width:120px"><option></option>[mac]</select></td>
    </tr>
  </tbody>
  
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr class="txtAzulEscuro11">
          <td>
            <b>&nbsp;&raquo; Conteúdo:</b>
            <input type="button" title="Negrito [Ctrl + N]" id="negrito" value="N" onclick="aplicarTag('n')" style="width:23px;height:22px" class="buttonAzulEscuroBranco11">
            <input type="button" title="Itálico [Ctrl + I]" id="italico" value="I" onclick="aplicarTag('i')" style="width:23px;height:22px;font-style:italic" class="buttonAzulEscuroBranco11">
            <input type="button" title="Sublinhado [Ctrl + S]" id="sublinhado" value="S" onclick="aplicarTag('s')" style="width:23px;height:22px;text-decoration:underline" class="buttonAzulEscuroBranco11">
            <input type="button" title="Destaque [Ctrl + D]" id="destaque" value="D" onclick="aplicarTag('d')" style="width:23px;height:22px" class="buttonAzulEscuroBranco11">
            <input type="button" title="Limpar [Ctrl + L]" id="limpar" value="L" onclick="limparTags()" style="width:23px;height:22px" class="buttonAzulEscuroBranco11">
            <select title="Cor" id="cor" class="inputBrancoAzulEscuro11" onchange="aplicarTag(this.value); this.value = '';">
              <option></option>
              <option value="azul" style="color:blue">Azul</option>
              <option value="verde" style="color:green">Verde</option>
              <option value="vermelho" style="color:red">Vermelho</option>
            </select>
          </td>
          <td align="right" id="caracteres_restantes"></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2"><textarea id="conteudo" class="textBrancoAzulEscuro11" rows="10" cols="50" style="width:100%" onchange="chamado_alterado = true" onblur="verificaTamanhoMaximo(event); atualizaCaracteresRestantes()" onfocus="atualizaCaracteresRestantes()" onkeyup="atualizaCaracteresRestantes()" onkeypress="chamado_alterado = true; atualizaCaracteresRestantes(); return verificaTamanhoMaximo(event);"></textarea></td>
  </tr>
