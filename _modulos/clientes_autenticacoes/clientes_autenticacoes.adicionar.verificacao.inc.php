<script type="text/javascript" src="rds.js.php?m=clientes_autenticacoes"></script>
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
        <td height="30" colspan="2"><strong>&nbsp;POP:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; SSID:</strong></td>
        <td>[ssid]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Clientes:</strong></td>
        <td id="clientes_count">[clientes_count]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; IP da AP:</strong></td>
        <td id="ssid_ip">[ssid_ip]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; IP:</strong></td>
        <td id="ip">[ip]</td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">
        	[botoes]
        </td>
      </tr>
      
		  <tbody id="equipamento" style="display:none">
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><strong>&nbsp;Dados do Equipamento:</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Modelo:</strong></td>
          <td id="equipamento_modelo"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Nome:</strong></td>
          <td id="equipamento_nome"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; MAC:</strong></td>
          <td id="equipamento_mac"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; SSID:</strong></td>
          <td id="equipamento_ssid"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><strong>&nbsp;Qualidade do Sinal &raquo; Cliente:</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Signal:</strong></td>
          <td id="equipamento_signal"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; SNR:</strong></td>
          <td id="equipamento_snr"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; CCQ:</strong></td>
          <td id="equipamento_ccq"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><strong>&nbsp;Qualidade do Sinal &raquo; AP:</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Signal:</strong></td>
          <td id="ap_signal"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; SNR:</strong></td>
          <td id="ap_snr"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; CCQ:</strong></td>
          <td id="ap_ccq"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="40" colspan="2" align="center">
        	  <input type="button" name="continuar" id="continuar" class="buttonAzulEscuroBranco11" onclick="continuarAdicionar()" value="Continuar">
          </td>
      </tr>
      </tbody>
    </table></td>
  </tr>
</table>
<script type="text/javascript">
	var device = '';
	var cliente_id = [cliente_id];
	var autenticacao_id = [autenticacao_id];
</script>
