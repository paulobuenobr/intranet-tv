<table border="0" cellpadding="0" cellspacing="0" width="100%">
<script type="text/javascript" src="rds.js.php?m=clientes.contatos" id="clientes.contatos.js"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.alertas" id="clientes.alertas.js"></script>
<script type="text/javascript" src="rds.js.php?m=chamados" id="chamados.js"></script>
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
        <td width="25%" align="center" bgcolor="#D7D7D7">[situacao_financeira]</td>
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
        <td height="30" colspan="3"><b>&nbsp;Chamados:</b> [chamados_adicionar]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="chamados">[chamados]</td>
            </tr>
          </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Responsável:</b></td>
        <td colspan="2">[responsavel]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Status:</b></td>
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
  //verificaPendencias(true);
</script>
