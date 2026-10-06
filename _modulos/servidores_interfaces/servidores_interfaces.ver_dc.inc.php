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
        <td><input name="nome" maxlength="15" id="nome" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="8" value="[nome]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td>[tipo]</td>
      </tr>
      [servidor_cliente_carp]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Interfaces CARP:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="listagem_carp">[interfaces_carp]</td>
      </tr>
      [/servidor_cliente_carp]
      [nao_servidor_cliente_carp]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Endereço IP:</strong></td>
        <td>[endereco_ip]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Máscara de Rede:</strong></td>
        <td>[mascara_rede]</td>
      </tr>
      [/nao_servidor_cliente_carp]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Reserva de IP's:</strong></td>
        <td>[reserva_ips]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Padrão:</strong></td>
        <td>[padrao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; ESSID:</strong></td>
        <td>[essid]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Chave Criptografia:</strong></td>
        <td>[chave_criptografia]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Canal:</strong></td>
        <td>[canal]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Comentários:</strong></td>
        <td>[comentarios]</td>
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

<script type="text/javascript">
  var interface_id = '[id]';
  var servidor_id = '[servidor_id]';
  if (window.opener && window.opener.buscaServidoresInterfaces) {
    window.opener.buscaServidoresInterfaces(servidor_id);
  }
</script>
