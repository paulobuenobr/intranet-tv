<form name="editar_df" method="POST" onSubmit="return validaClienteDadosFinanceiros(this)" action="[action]">
<script type="text/javascript" src="rds.js.php?m=clientes"></script>
<script type="text/javascript" src="rds.js.php?m=enderecos"></script>
<script type="text/javascript" src="rds.js.php?m=condominios_blocos"></script>
<script type="text/javascript" src="rds.js.php?m=ajustes_financeiros"></script>
<script type="text/javascript" src="rds.js.php?m=cobrancas"></script>
<script type="text/javascript" src="rds.js.php?m=clientes_consultas_credito"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.alertas"></script>
<script type="text/javascript" src="rds.js.php?m=clientes_negativacoes"></script>
<script type="text/javascript" src="rds.js.php?m=ceps"></script>
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
        <td width="50%" valign="middle">[nome]</td>
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
        <td colspan="2"><select name="condominial" id="condominial" class="inputBrancoAzulEscuro11" style="width:400px" onchange="mudaCondominial(this)" [condominial_disabled]><option></option>[condominial]</select> <input type="button" id="ver_cliente" name="ver_cliente" value="Ver" class="buttonAzulEscuroBranco11" onclick="if (this.form.condominial.value != '') window.location = './?m=clientes&a=ver_df&i=' + this.form.condominial.value;"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Vencimento:</strong></td>
        <td colspan="2"><select name="cobranca_vencimento" id="cobranca_vencimento" style="width:250px" class="inputBrancoAzulEscuro11" [cobranca_vencimento_disabled]><option></option>[cobranca_vencimento]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bloqueio:</strong></td>
        <td colspan="2"><input name="bloqueio" id="bloqueio" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="6" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" value="[bloqueio]" [bloqueio_disabled]> dia(s)</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Formato:</strong></td>
        <td colspan="2"><select name="cobranca_formato" id="cobranca_formato" style="width:250px" class="inputBrancoAzulEscuro11" onChange="mudaClienteCobrancaFormato(this)" [cobranca_formato_disabled]><option></option>[cobranca_formato]</select>[cobranca_formato_copia_email]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Débito em Conta &raquo; Operação:</strong></td>
        <td colspan="2"><input name="cobranca_debito_operacao" id="cobranca_debito_operacao" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="6" onKeyPress="return formataOperacao(this,event)" onBlur="return formataOperacao(this,event)" value="[cobranca_debito_operacao]" [cobranca_debito_operacao_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Débito em Conta &raquo; Agência:</strong></td>
        <td colspan="2"><input name="cobranca_debito_agencia" id="cobranca_debito_agencia" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="6" onKeyPress="return formataAgencia(this,event,this.form.cobranca_formato.value)" onBlur="return formataAgencia(this,event,this.form.cobranca_formato.value)" value="[cobranca_debito_agencia]" [cobranca_debito_agencia_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Débito em Conta &raquo; Conta:</strong></td>
        <td colspan="2"><input name="cobranca_debito_conta" id="cobranca_debito_conta" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="11" onKeyPress="return formataConta(this,event,this.form.cobranca_formato.value)" onBlur="return formataConta(this,event,this.form.cobranca_formato.value)" value="[cobranca_debito_conta]" [cobranca_debito_conta_disabled]></td>
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
        <td colspan="2"><select name="condominio" id="condominio" class="inputBrancoAzulEscuro11" style="width:400px" onChange="mudaClienteCondominio(this,false)" [condominio_disabled]><option></option>[condominio]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bloco:</strong></td>
        <td colspan="2"><select name="bloco" id="bloco" class="inputBrancoAzulEscuro11" style="width:250px" onChange="mudaClienteBloco(this)" [bloco_disabled]><option></option>[bloco]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; CEP:</strong></td>
        <td colspan="2"><input name="cep" id="cep" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" maxlength="9" onKeyPress="return formataCEP(this,event);" onBlur="return formataCEP(this,event);" value="[cep]" [cep_disabled]> [adicionar_cep] [pesquisar_cep]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Estado:</strong></td>
        <td colspan="2"><input name="estado" id="estado" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" value="[estado]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Cidade:</strong></td>
        <td colspan="2"><input name="cidade" id="cidade" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" value="[cidade]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bairro:</strong></td>
        <td colspan="2"><input name="bairro" id="bairro" type="text" class="inputBrancoAzulEscuro11" style="width:250px" size="50" value="[bairro]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Logradouro:</strong></td>
        <td colspan="2"><input name="logradouro" id="logradouro" type="text" class="inputBrancoAzulEscuro11" style="width:400px" size="50" value="[logradouro]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Número:</strong></td>
        <td colspan="2"><input name="numero" id="numero" type="text" class="inputBrancoAzulEscuro11" size="50" style="width:400px" maxlength="10" value="[numero]" [numero_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Complemento:</strong></td>
        <td colspan="2"><select name="complemento_tipo" id="complemento_tipo" class="inputBrancoAzulEscuro11" style="width:160px" [complemento_tipo_disabled]>[complemento_tipo]</select> &nbsp;<input name="complemento_valor" id="complemento_valor" type="text" class="inputBrancoAzulEscuro11" size="31" maxlength="100" style="width:234px" value="[complemento_valor]" [complemento_valor_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="3" align="center">[botoes]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3"><strong>&nbsp;Negativações:</strong> [clientes_negativacoes_adicionar]</td>
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
        <td height="30" colspan="3"><strong>&nbsp;Ajustes Financeiros:</strong> [ajustes_financeiros_adicionar]</td>
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
    </table></td>
  </tr>
</table>
<input name="campos_desabilitados" id="campos_desabilitados" type="hidden" value="[campos_desabilitados]">
</form>

<script type="text/javascript">
  var cliente_id = '[cliente_id]';
  var pendencias = [pendencias];
  var cobrancas_formatos_debito = [cobrancas_formatos_debito];
  //verificaPendencias(true);
</script>
