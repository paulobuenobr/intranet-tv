<form name="editar_dm" id="editar_dm" method="POST" onSubmit="return validaClienteDadosComerciais(this);" action="[action]">
<script type="text/javascript" src="rds.js.php?m=assinaturas" id="assinaturas.js"></script>
<script type="text/javascript" src="rds.js.php?m=descontos_concessoes" id="descontos_concessoes.js"></script>
<script type="text/javascript" src="rds.js.php?m=clientes_retornos" id="clientes_retornos.js"></script>
<script type="text/javascript" src="rds.js.php?m=clientes_responsabilidades" id="clientes_responsabilidades.js"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.alertas" id="clientes.alertas.js"></script>
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
        <td height="30" colspan="3"><b>&nbsp;Dados Cobrança:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Vencimento:</b></td>
        <td colspan="2"><select name="cobranca_vencimento" id="cobranca_vencimento" style="width:250px" class="inputBrancoAzulEscuro11" [cobranca_vencimento_disabled]><option></option>[cobranca_vencimento]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Formato:</b></td>
        <td colspan="2"><select name="cobranca_formato" id="cobranca_formato" style="width:250px" class="inputBrancoAzulEscuro11" onChange="mudaClienteCobrancaFormato(this)" [cobranca_formato_disabled]><option></option>[cobranca_formato]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Débito em Conta &raquo; Operação:</strong></td>
        <td colspan="2"><input name="cobranca_debito_operacao" id="cobranca_debito_operacao" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="6" onKeyPress="return formataAgencia(this,event,this.form.cobranca_formato.value)" onBlur="return formataAgencia(this,event,this.form.cobranca_formato.value)" value="[cobranca_debito_operacao]" [cobranca_debito_operacao_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Débito em Conta &raquo; Agência:</b></td>
        <td colspan="2"><input name="cobranca_debito_agencia" id="cobranca_debito_agencia" style="width:250px" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="6" onChange="return formataAgencia(this,event,this.form.cobranca_formato.value)" onKeyPress="return formataAgencia(this,event,this.form.cobranca_formato.value)" value="[cobranca_debito_agencia]" [cobranca_debito_agencia_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Débito em Conta &raquo; Conta:</b></td>
        <td colspan="2"><input name="cobranca_debito_conta" id="cobranca_debito_conta" style="width:250px" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="11" onChange="return formataConta(this,event,this.form.cobranca_formato.value)" onKeyPress="return formataConta(this,event,this.form.cobranca_formato.value)" value="[cobranca_debito_conta]" [cobranca_debito_conta_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><b>&nbsp;Retornos:</b> [retornos_adicionar]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="retornos">[retornos]</td>
            </tr>
          </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><b>&nbsp;Assinaturas:</b> [assinaturas_adicionar]
          <input type="checkbox" id="status_1" onclick="buscaAssinaturasCliente([assinaturas_parametros])" checked><label for="status_1">Prospects</label>&nbsp;
          <input type="checkbox" id="status_2" onclick="buscaAssinaturasCliente([assinaturas_parametros])" checked><label for="status_2">Ativas</label>&nbsp;
          <input type="checkbox" id="status_3" onclick="buscaAssinaturasCliente([assinaturas_parametros])" checked><label for="status_3">Inativas (Canceladas)</label>&nbsp;
          <input type="checkbox" id="status_4" onclick="buscaAssinaturasCliente([assinaturas_parametros])"><label for="status_4">Inativas (Migradas)</label>&nbsp;
          <input type="checkbox" id="status_5" onclick="buscaAssinaturasCliente([assinaturas_parametros])"><label for="status_5">Inativos (Anulados)</label>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="assinaturas">[assinaturas]</td>
            </tr>
          </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><b>&nbsp;Responsabilidades:</b> [responsabilidades_adicionar]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="responsabilidades">[responsabilidades]</td>
            </tr>
          </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><b>&nbsp;Operações Contatos:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11">[operacoes]</td>
            </tr>
          </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Responsável:</b></td>
        <td colspan="2" id="cliente_responsavel">[responsavel]</td>
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
<input name="campos_desabilitados" id="campos_desabilitados" type="hidden" value="[campos_desabilitados]">
</form>

<script type="text/javascript">
  var pfpj = '[pfpj]';
  var ccsc = '[ccsc]';
  var condominio = '[condominio_id]';
  var cliente_id = '[cliente_id]';
  var pendencias = [pendencias];
  //verificaPendencias(true);
</script>
