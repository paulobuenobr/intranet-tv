<script type="text/javascript" src="rds.js.php?m=chamados"></script>
<script type="text/javascript" src="_modulos/chamados_sistema/chamados_sistema.js"></script>
<form name="editar" method="POST" onSubmit="return validaEditarEtapa(this)" action="[action]">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Editar Etapa&nbsp;</b></td></tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Dados do Chamado:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td style="width:20%"><b>&nbsp;&raquo; Código:</b></td>
    <td style="width:80%">[codigo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Dados da Etapa:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Tipo:</b></td>
    <td>[tipo]</td>
  </tr>
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
              <option value="azul">Azul</option>
              <option value="verde">Verde</option>
              <option value="vermelho">Vermelho</option>
            </select>
          </td>
          <td align="right" id="caracteres_restantes"></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2"><textarea id="conteudo" name="conteudo" class="textBrancoAzulEscuro11" rows="10" cols="50" style="width:100%" onchange="chamado_alterado = true" onblur="verificaTamanhoMaximo(event); atualizaCaracteresRestantes()" onfocus="atualizaCaracteresRestantes()" onkeyup="atualizaCaracteresRestantes()" onkeypress="atualizaCaracteresRestantes(); return verificaTamanhoMaximo(event);">[conteudo]</textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" colspan="2" align="center">[botoes]</td>
  </tr>
</table>
</form>

<script type="text/javascript">
  atualizaCaracteresRestantes();
</script>
