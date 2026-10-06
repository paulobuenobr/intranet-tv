<script type="text/javascript" src="rds.js.php?m=clientes_comodatos"></script>
<script type="text/javascript" src="rds.js.php?m=clientes_iptv"></script>
<script type="text/javascript" src="rds.js.php?m=servicos_ajustes"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.visitas"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.alertas"></script>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11">[acoes]</tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><strong>&nbsp;Nome:</strong></td>
        <td width="50%">[nome]</td>
        <td width="25%" align="center" bgcolor="#D7D7D7">[situacao_financeira]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;CPF/CNPJ:</b></td>
        <td colspan="2">[cpf_cnpj]</td>
      </tr>
      [alerta_pendencias]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Endere&ccedil;o:</strong></td>
        <td colspan="2">[endereco]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Telefone(s):</strong></td>
        <td colspan="2">[telefone]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Origem:</strong></td>
        <td colspan="2">[origem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><strong>&nbsp;IPTV:</strong><div style="float:right"><input type="checkbox" id="exibir_iptv_inativos" onclick="buscaIptv([cliente_id], this.checked)"><label for="exibir_iptv_inativos">Exibir inativos</label></div></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="clientes_iptv_listagem">[iptv_listagem]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><b>&nbsp;Equipamentos:</b><div style="float:right;margin-right:8px;height:15px"><input type="checkbox" id="exibir_comodatos_inativos" style="vertical-align:middle" onclick="atualizaEquipamentosComodatos([cliente_id], this.checked)" checked><label style="vertical-align:middle" for="exibir_comodatos_inativos">Exibir inativos</label></div></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      	<td colspan="3"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="equipamentos_comodatos">[equipamentos_comodatos]</td>
            </tr>
          </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><b>&nbsp;Ajustes Programados:</b> <span id="ajustes_adicionar">[ajustes_adicionar]</span> <span id="ajustes_bloqueio_temporario_adicionar">[ajustes_bloqueio_temporario_adicionar]</span></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="ajustes">[ajustes]</td>
            </tr>
          </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><strong>&nbsp;Visitas:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="visitas_listagem">[visitas_listagem]</td>
            </tr>
          </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Responsável:</strong></td>
        <td colspan="2">[responsavel]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td colspan="2">[status]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="3" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>

<script type="text/javascript">
  var cliente_id = '[cliente_id]';
  var pendencias = [pendencias];
</script>
