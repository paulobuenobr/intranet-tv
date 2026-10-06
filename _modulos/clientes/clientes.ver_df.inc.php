<script type="text/javascript" src="rds.js.php?m=ajustes_financeiros"></script>
<script type="text/javascript" src="rds.js.php?m=clientes"></script>
<script type="text/javascript" src="rds.js.php?m=cobrancas"></script>
<script type="text/javascript" src="rds.js.php?m=clientes_consultas_credito"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.alertas"></script>
<script type="text/javascript" src="rds.js.php?m=clientes_negativacoes"></script>
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
        <td height="30" colspan="3"><strong>&nbsp;Dados Cobrança:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display:[exibir_condominial]">
        <td><strong>&nbsp;&raquo; Condominial:</strong></td>
        <td colspan="2">[condominial]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Vencimento:</strong></td>
        <td colspan="2">[cobranca_vencimento] [bloqueio_dias]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Formato:</strong></td>
        <td colspan="2">[cobranca_formato]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Débito em Conta &raquo; Operação:</strong></td>
        <td colspan="2">[cobranca_debito_operacao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Débito em Conta &raquo; Agência:</strong></td>
        <td colspan="2">[cobranca_debito_agencia]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Débito em Conta &raquo; Conta:</strong></td>
        <td colspan="2">[cobranca_debito_conta]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display:[exibir_debito_codigo]">
        <td><strong>&nbsp;&raquo; Débito em Conta &raquo; Código:</strong></td>
        <td colspan="2">[codigo_debito_automatico]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><strong>&nbsp;Endereço de Cobrança / Correspondência:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Condomínio:</strong></td>
        <td colspan="2">[condominio]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bloco:</strong></td>
        <td colspan="2">[bloco]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; CEP:</strong></td>
        <td colspan="2">[cep]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Estado:</strong></td>
        <td colspan="2">[estado]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Cidade:</strong></td>
        <td colspan="2">[cidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bairro:</strong></td>
        <td colspan="2">[bairro]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Logradouro:</strong></td>
        <td colspan="2">[logradouro]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Número:</strong></td>
        <td colspan="2">[numero]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Complemento:</strong></td>
        <td colspan="2">[complemento]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><strong>&nbsp;Negativações:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3">
          <table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="clientes_negativacoes">[clientes_negativacoes]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><strong>&nbsp;Ajustes Financeiros:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3">
          <table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="ajustes_financeiros">[ajustes_financeiros]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><strong>&nbsp;Cobranças:</strong> [cobrancas_adicionar] [relatorio_financeiro]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3">
          <table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="cobrancas">[cobrancas]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display:[exibir_clientes_condominiais]">
        <td height="30" colspan="3"><strong>&nbsp;Clientes Condominiais:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display:[exibir_clientes_condominiais]">
        <td colspan="3">
          <table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="clientes_condominiais">[clientes_condominiais]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><strong>&nbsp;Consultas de Crédito:</strong> [consultas_credito_efetuar]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3">
          <table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="consultas_credito">[consultas_credito]</td>
            </tr>
          </table>
        </td>
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
  //verificaPendencias(true);
</script>
