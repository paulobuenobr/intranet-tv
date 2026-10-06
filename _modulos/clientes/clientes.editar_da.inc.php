<script type="text/javascript" src="rds.js.php?m=contratos" ></script>
<script type="text/javascript" src="rds.js.php?m=cancelamentos"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.alertas"></script>
<script type="text/javascript" src="rds.js.php?m=pesquisas_aplicacoes"></script>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11">[acoes]</tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><b>&nbsp;Nome:</b></td>
        <td width="50%">[nome]</td>
        <td width="25%" align="center" bgcolor="#D7D7D7" id="cliente_situacao">[situacao_financeira]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;CPF/CNPJ:</b></td>
        <td colspan="2">[cpf_cnpj]</td>
      </tr>
      [alerta_pendencias]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Endere&ccedil;o:</b></td>
        <td colspan="2">[endereco]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Telefone(s):</b></td>
        <td colspan="2">[telefone]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Origem:</strong></td>
        <td colspan="2">[origem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><b>&nbsp;Contratos:</b> <span id="contratos_adicionar">[contratos_adicionar]</span>
          <input type="checkbox" id="status_1" onclick="buscaContratosCliente('[cliente_id]')" checked><label for="status_1">Em Aberto</label>&nbsp;
          <input type="checkbox" id="status_2" onclick="buscaContratosCliente('[cliente_id]')" checked><label for="status_2">Ativos</label>&nbsp;
          <input type="checkbox" id="status_3" onclick="buscaContratosCliente('[cliente_id]')" checked><label for="status_3">Inativos (Cancelados)</label>&nbsp;
          <input type="checkbox" id="status_4" onclick="buscaContratosCliente('[cliente_id]')"><label for="status_4">Inativos (Migrados)</label>&nbsp;
          <input type="checkbox" id="status_5" onclick="buscaContratosCliente('[cliente_id]')"><label for="status_5">Inativos (Anulados)</label>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="contratos">[contratos]</td>
            </tr>
          </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><b>&nbsp;Solicitações de Cancelamento:</b> <span id="cancelamentos_adicionar">[cancelamentos_adicionar]</span></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3">
          <table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="cancelamentos">[cancelamentos]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><b>&nbsp;Pesquisas (Aplicações):</b> [pesquisas_adicionar]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="pesquisas">[pesquisas]</td>
            </tr>
          </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Responsável:</b></td>
        <td colspan="2">[responsavel]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Status:</b></td>
        <td colspan="2" id="cliente_status">[status]</td>
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
  //verificaPendencias(true);
</script>
