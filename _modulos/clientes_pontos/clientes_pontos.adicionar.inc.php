<form name="editar" method="POST" onSubmit="return validaClientePontos(this)" action="[action]">
<script type="text/javascript" src="rds.js.php?m=clientes_pontos"></script>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Adicionar&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Cliente:</strong></td>
        <td width="80%" id="cliente">[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Assinatura:</strong></td>
         <td>[assinatura]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Interface:</strong></td>
        <td>[interface]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Endereço IP:</b></td>
         <td>
           <input name="prefixo_ip_1" id="prefixo_ip_1" value="[prefixo_ip_1]" type="text" class="inputBrancoAzulEscuro11_c" style="width:53px" size="3"> .
           <input name="prefixo_ip_2" id="prefixo_ip_2" value="[prefixo_ip_2]" type="text" class="inputBrancoAzulEscuro11_c" style="width:53px" size="3"> .
           <input name="prefixo_ip_3" id="prefixo_ip_3" value="[prefixo_ip_3]" type="text" class="inputBrancoAzulEscuro11_c" style="width:53px" size="3"> .
           <input name="endereco_ip" id="endereco_ip" value="[endereco_ip]" type="text" class="inputBrancoAzulEscuro11_c" style="width:53px" size="3">
         </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;MAC:</strong></td>
        <td><input name="mac" id="mac" type="text" class="inputBrancoAzulEscuro11" style="width:177px" size="17" maxlength="17" onKeyPress="return formataMAC(this, event);" onblur="verificaMacExistente(this, 0, this.form.interface.value); ocultarMAC();"> [capturar]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Velocidade:</strong></td>
        <td><span id="velocidade">[velocidade]</span></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:100%" rows="3" cols="52"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>

<div id="lista_macs" style="width:170px;background:#FFFFFF;border-width:0px;position:absolute;top:159px;left:112px;display:none;padding:0px"><table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%"><tr><td id="lista_macs_conteudo" style="border-width:1px;border-color:#000000;border-style:solid;padding-right:3px;padding-left:3px;padding-top:2px;padding-bottom:2px"></td></tr></table></div>

<script type="text/javascript">
  var cliente_id = '[cliente_id]';
  var interface_id = '[interface_id]';
  var ponto_id = '';
  if (document.getElementById('assinatura').value) {
  	document.getElementById('mac').focus();
  } else {
  	document.getElementById('assinatura').focus();
  }
  if (!window.opener.closed && window.opener.buscaPontos) {
    window.opener.buscaPontos(cliente_id);
  }
</script>
