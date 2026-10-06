<script type="text/javascript" src="rds.js.php?m=servidores_interfaces"></script>
<form name="adicionar" method="post" onSubmit="return validaServidoresInterfaces(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11">[acoes]</tr>
    </table></td>
  </tr>
  <tr>
   <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
         <td width="35%"><strong>&nbsp;Servidor:</strong></td>
        <td>[servidor_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Nome:</strong></td>
        <td><input name="nome" maxlength="64" id="nome" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="8" onBlur="return validaNome(this, [servidor_id], 0)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td><select name="tipo" id="tipo" class="inputBrancoAzulEscuro11" style="width:280px" onchange="mudaTipoInterface(this.value)">[tipo]</select></td>
      </tr>
      <tbody id="dados_ip" style="display:none">
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Endereço IP:</strong></td>
          <td><input name="prefixo_ip_1" id="prefixo_ip_1" value="[prefixo_ip_1]" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this, event)" onChange="return validaEnderecoIPServidorInterface(this, interface_id)" onBlur="return validaEnderecoIPServidorInterface(this, interface_id)"> .
            <input name="prefixo_ip_2" id="prefixo_ip_2" value="[prefixo_ip_2]" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this, event)" onChange="return validaEnderecoIPServidorInterface(this, interface_id)" onBlur="return validaEnderecoIPServidorInterface(this, interface_id)"> .
            <input name="prefixo_ip_3" id="prefixo_ip_3" value="[prefixo_ip_3]" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this, event)" onChange="return validaEnderecoIPServidorInterface(this, interface_id)" onBlur="return validaEnderecoIPServidorInterface(this, interface_id)"> .
            <input name="endereco_ip" id="endereco_ip" value="[endereco_ip]" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this, event)" onChange="return validaEnderecoIPServidorInterface(this, interface_id)" onBlur="return validaEnderecoIPServidorInterface(this, interface_id)"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Máscara de Rede:</strong></td>
          <td><input name="mascara_rede_1" id="mascara_rede_1" value="[mascara_rede_1]" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this, event)"> .
            <input name="mascara_rede_2" id="mascara_rede_2" value="[mascara_rede_2]" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this, event)"> .
            <input name="mascara_rede_3" id="mascara_rede_3" value="[mascara_rede_3]" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this, event)"> .
            <input name="mascara_rede_4" id="mascara_rede_4" value="[mascara_rede_4]" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this, event)"></td>
        </tr>
      </tbody>
      <tbody id="dados_carp" style="display:none">
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><strong>&nbsp;Interfaces CARP:</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" id="listagem_carp">[interfaces_carp]</td>
        </tr>
      </tbody>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Reserva de IP's:</strong></td>
        <td><input name="reserva_ips" id="reserva_ips" type="text" class="inputBrancoAzulEscuro11" style="width:159px" size="50" onKeyPress="return formataNumero(this, event)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Padrão:</strong></td>
        <td><select name="padrao" id="padrao" class="inputBrancoAzulEscuro11" style="width:280px" onChange="return alteraSituacaoPadrao(this)">[padrao]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; ESSID:</strong></td>
        <td><input name="essid" id="essid" type="text" class="inputBrancoAzulEscuro11" style="width:280px" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Chave de Criptografia:</strong></td>
        <td><input name="chave_criptografia" id="chave_criptografia" type="text" class="inputBrancoAzulEscuro11" style="width:280px" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Canal:</strong></td>
        <td><select name="canal" id="canal" class="inputBrancoAzulEscuro11" style="width:159px" disabled><option></option>[canal]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Comentários:</strong></td>
        <td><textarea name="descricao" id="descricao" class="textBrancoAzulEscuro11" style="width:280px;height:65px" rows="4" cols="52"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<input type="hidden" name="lista_carp" id="lista_carp" value="">
</form>

<script type="text/javascript">
  var servidor_id = '[servidor_id]';
  var interface_id = '0';
  if (!window.opener.closed && window.opener.buscaServidoresInterfaces) {
    window.opener.buscaServidoresInterfaces(servidor_id);
  }
</script>
