<script type="text/javascript" src="rds.js.php?m=servidores_interfaces"></script>
<form name="editar" method="POST" onSubmit="return validaServidoresInterfaces(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11">[acoes]</tr>
    </table></td>
  </tr>
  <tr>
   <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="33%"><strong>&nbsp;Servidor:</strong></td>
        <td width="67%">[servidor_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Nome:</strong></td>
        <td><input name="nome" maxlength="64" id="nome" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="8" value="[nome]" onBlur="return validaNome(this, [servidor_id], [id])"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td><select name="tipo" id="tipo" class="inputBrancoAzulEscuro11" style="width:100%" disabled>[tipo]</select></td>
      </tr>
      [interface_carp]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Interfaces CARP:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="listagem_carp">[interfaces_carp]</td>
      </tr>
      [/interface_carp]
      [interface_redes]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Redes:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[redes]</td>
      </tr>
      [/interface_redes]
      [interface_clientes]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Endereço IP:</strong></td>
        <td><input name="prefixo_ip_1" id="prefixo_ip_1" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this, event)" onChange="return validaEnderecoIPServidorInterface(this, interface_id)" onBlur="return validaEnderecoIPServidorInterface(this, interface_id)" value="[prefixo_ip_1]"> .
          <input name="prefixo_ip_2" id="prefixo_ip_2" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this, event)" onChange="return validaEnderecoIPServidorInterface(this, interface_id)" onBlur="return validaEnderecoIPServidorInterface(this, interface_id)" value="[prefixo_ip_2]"> .
          <input name="prefixo_ip_3" id="prefixo_ip_3" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this, event)" onChange="return validaEnderecoIPServidorInterface(this, interface_id)" onBlur="return validaEnderecoIPServidorInterface(this, interface_id)" value="[prefixo_ip_3]"> .
          <input name="endereco_ip" id="endereco_ip" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this, event)" onChange="return validaEnderecoIPServidorInterface(this, interface_id)" onBlur="return validaEnderecoIPServidorInterface(this, interface_id)" value ="[endereco_ip]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Máscara de Rede:</strong></td>
        <td><input name="mascara_rede_1" id="mascara_rede_1" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this,event);" value="[mascara_rede_1]"> .
          <input name="mascara_rede_2" id="mascara_rede_2" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this,event);" value="[mascara_rede_2]"> .
          <input name="mascara_rede_3" id="mascara_rede_3" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this,event);" value="[mascara_rede_3]"> .
          <input name="mascara_rede_4" id="mascara_rede_4" type="text" class="inputBrancoAzulEscuro11_c" style="width:43px" size="3" onKeyPress="return formataNumeroIP(this,event);" value="[mascara_rede_4]"></td>
      </tr>
      [/interface_clientes]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Reserva de IP's:</strong></td>
        <td><input name="reserva_ips" id="reserva_ips" type="text" class="inputBrancoAzulEscuro11" style="width:159px" size="50" onKeyPress="return formataNumero(this, event)" value="[reserva_ips]"></td>
      </tr>
      [interface_pool]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Pool de IP's para AP's:</strong></td>
        <td id="listagem_pool">[listagem_pool]</td>
      </tr>
      [/interface_pool]
      [interface_ssid]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;SSID's</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	      <td id="listagem_ssid" colspan="2">[listagem_ssid]</td>
      </tr>
      [/interface_ssid]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Padrão:</strong></td>
        <td><select name="padrao" id="padrao" class="inputBrancoAzulEscuro11" style="width:100%" onChange="return alteraSituacaoPadrao(this);">[padrao]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; ESSID:</strong></td>
        <td><input name="essid" id="essid" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="50" value ="[essid]"[situacao_parametros]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Chave Criptografia:</strong></td>
        <td><input name="chave_criptografia" id="chave_criptografia" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="50" value ="[chave_criptografia]"[situacao_parametros]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Canal:</strong></td>
        <td><select name="canal" id="canal" class="inputBrancoAzulEscuro11" style="width:159px"[situacao_parametros]>[canal]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Comentários:</strong></td>
        <td><textarea name="descricao" id="descricao" class="textBrancoAzulEscuro11" style="width:100%;height:65px" rows="4" cols="52">[descricao]</textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td>[status]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<input type="hidden" name="lista_pool" id="lista_pool" value='[lista_pool]'>
<input type="hidden" name="lista_ssid" id="lista_ssid" value='[lista_ssid]'>
<input type="hidden" name="lista_carp" id="lista_carp" value='[lista_carp]'>
</form>

<script type="text/javascript">
  var interface_id = '[id]';
  var servidor_id = '[servidor_id]';
  if (window.opener && window.opener.buscaServidoresInterfaces) {
    window.opener.buscaServidoresInterfaces(servidor_id);
  }
</script>
