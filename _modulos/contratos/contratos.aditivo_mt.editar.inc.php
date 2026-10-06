<form name="adicionar" method="POST" onsubmit="return validaContratoAditivoMT(this)">
<script type="text/javascript" src="rds.js.php?m=contratos"></script>
<script type="text/javascript" src="rds.js.php?m=enderecos"></script>
<script type="text/javascript" src="rds.js.php?m=condominios_blocos"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.contatos"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.alertas"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11">[acoes]</tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td height="30" colspan="2"><strong>&nbsp;Contrato:</strong></td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Código:</strong></td>
	        <td>[codigo]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Tipo:</strong></td>
	        <td>[tipo]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;&raquo; Serviço:</strong></td>
	        <td>[servico]</td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td height="30" colspan="2"><strong>&nbsp;Cliente:</strong></td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td width="20%"><strong>&nbsp;&raquo; Nome:</strong></td>
	        <td>[nome_antigo]</td>
	      </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Tipo:</strong></td>
          <td>[pessoa_tipo_antigo]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b>&nbsp;&raquo; [cpf_cnpj_label_antigo]:</b></td>
          <td>[cpf_cnpj_antigo]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Nascimento:</strong></td>
          <td>[nascimento_antigo]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="30"><b>&nbsp;Novo Titular:</b></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><b>&nbsp;&raquo; Nome:</b></td>
          <td><input name="nome" id="nome" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[nome]" [nome_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b>&nbsp;&raquo; Tipo:</b></td>
          <td><select name="tipo" id="tipo" class="inputBrancoAzulEscuro11" onChange="mudaClienteTipo(this)" [tipo_disabled]>[tipo]</select></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b id="cpf_cnpj_label">&nbsp;&raquo; [cpf_cnpj_label]:</b></td>
          <td><input name="cpf_cnpj" id="cpf_cnpj" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="[cpf_cnpj_maxlength]" onKeyPress="return formataCPFCNPJ(this,event);" onBlur="return formataCPFCNPJ(this,event);" value="[cpf_cnpj]" [cpf_cnpj_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b>&nbsp;&raquo; Nascimento:</b></td>
          <td><input name="nascimento" id="nascimento" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[nascimento]" [nascimento_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Contato:</strong></td>
          <td><input name="contato" id="contato" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="60" value="[contato]" [contato_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b>&nbsp;Alerta(s):</b></td>
          <td id="alertas"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><b>&nbsp;Contato(s):</b></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                <td width="50%" valign="top" id="listagem_telefones">[listagem_telefones]</td>
                <td width="50%" valign="top" id="listagem_emails">[listagem_emails]</td>
              </tr>
            </table>
          </td>
        </tr>
        <tbody style="display:none">
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><b>&nbsp;&raquo; Condomínio:</b></td>
            <td><select name="condominio" id="condominio" class="inputBrancoAzulEscuro11" onChange="mudaClienteCondominio(this); atualizaCondominioCobranca();" [condominio_disabled]><option></option>[condominio]</select> <span id="panorama_condominio"></span></td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><b>&nbsp;&raquo; Bloco:</b></td>
            <td><select name="bloco" id="bloco" class="inputBrancoAzulEscuro11" onChange="mudaClienteBloco(this); atualizaBlocoCobranca();" [bloco_disabled]><option></option>[bloco]</select></td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><b>&nbsp;&raquo; CEP:</b></td>
            <td><input name="cep" id="cep" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="9" onKeyPress="return formataCEP(this,event);" onBlur="return formataCEP(this,event);" onchange="if (this.form.mesmo_endereco.checked) {this.form.cobranca_cep.value = this.form.cep.value;}" value="[cep]" [cep_disabled]></td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><b>&nbsp;&raquo; Estado:</b></td>
            <td><input name="estado" id="estado" type="text" class="inputBrancoAzulEscuro11" size="50" disabled></td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><b>&nbsp;&raquo; Cidade:</b></td>
            <td><input name="cidade" id="cidade" type="text" class="inputBrancoAzulEscuro11" size="50" disabled></td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><b>&nbsp;&raquo; Bairro:</b></td>
            <td><input name="bairro" id="bairro" type="text" class="inputBrancoAzulEscuro11" size="50" disabled></td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><b>&nbsp;&raquo; Logradouro:</b></td>
            <td><input name="logradouro" id="logradouro" type="text" class="inputBrancoAzulEscuro11" size="50" disabled></td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><b>&nbsp;&raquo; Número:</b></td>
            <td><input name="numero" id="numero" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" value="[numero]" onchange="atualizaNumeroCobranca()" onkeyup="atualizaNumeroCobranca()" [numero_disabled]></td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><b>&nbsp;&raquo; Complemento:</b></td>
            <td><select name="complemento_tipo" id="complemento_tipo" class="inputBrancoAzulEscuro11" onchange="atualizaComplementoTipoCobranca()" onkeypress="atualizaComplementoTipoCobranca()" [complemento_tipo_disabled]>[complemento_tipo]</select> &nbsp;<input name="complemento_valor" id="complemento_valor" type="text" class="inputBrancoAzulEscuro11" size="31" maxlength="40" value="[complemento_valor]" onChange="mudaClienteComplemento(this); atualizaComplementoValorCobranca()" onkeyup="atualizaComplementoValorCobranca()" [complemento_valor_disabled]></td>
          </tr>
        </tbody>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><b>&nbsp;Endere&ccedil;o de Cobrança / Correspondência:</b> <input type="checkbox" name="mesmo_endereco" id="mesmo_endereco" value="1" [mesmo_endereco_checked] onclick="mudaMesmoEndereco(this)" [mesmo_endereco_disabled]><label for="mesmo_endereco">Mesmo endereço de instalação</label></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Condomínio:</strong></td>
          <td><select name="cobranca_condominio" id="cobranca_condominio" class="inputBrancoAzulEscuro11" onChange="mudaClienteCondominio(this, 'cobranca_')" [cobranca_condominio_disabled]><option></option>[cobranca_condominio]</select></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Bloco:</strong></td>
          <td><select name="cobranca_bloco" id="cobranca_bloco" class="inputBrancoAzulEscuro11" onChange="mudaClienteBloco(this, 'cobranca_')" [cobranca_bloco_disabled]><option></option>[cobranca_bloco]</select></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; CEP:</strong></td>
          <td><input name="cobranca_cep" id="cobranca_cep" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="9" onKeyPress="return formataCEP(this, event, 'cobranca_');" onBlur="return formataCEP(this, event, 'cobranca_');" value="[cobranca_cep]" [cobranca_cep_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Estado:</strong></td>
          <td><input name="cobranca_estado" id="cobranca_estado" type="text" class="inputBrancoAzulEscuro11" size="50" disabled></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Cidade:</strong></td>
          <td><input name="cobranca_cidade" id="cobranca_cidade" type="text" class="inputBrancoAzulEscuro11" size="50" disabled></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Bairro:</strong></td>
          <td><input name="cobranca_bairro" id="cobranca_bairro" type="text" class="inputBrancoAzulEscuro11" size="50" disabled></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Logradouro:</strong></td>
          <td><input name="cobranca_logradouro" id="cobranca_logradouro" type="text" class="inputBrancoAzulEscuro11" size="50" disabled></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Número:</strong></td>
          <td><input name="cobranca_numero" id="cobranca_numero" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" value="[cobranca_numero]" [cobranca_numero_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Complemento:</strong></td>
          <td><select name="cobranca_complemento_tipo" id="cobranca_complemento_tipo" class="inputBrancoAzulEscuro11" [cobranca_complemento_tipo_disabled]>[cobranca_complemento_tipo]</select> &nbsp;<input name="cobranca_complemento_valor" id="cobranca_complemento_valor" type="text" class="inputBrancoAzulEscuro11" size="31" maxlength="40" value="[cobranca_complemento_valor]" [cobranca_complemento_valor_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="3" cols="75" style="width: 100%"></textarea></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2">[comentarios]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><strong>&nbsp;Datas:</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Geração:</strong></td>
          <td><input name="data_geracao" id="data_geracao" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_geracao]" [data_geracao_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Ativação:</strong></td>
          <td><input name="data_ativacao" id="data_ativacao" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_ativacao]" [data_ativacao_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Cancelamento:</strong></td>
          <td><input name="data_cancelamento" id="data_cancelamento" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_cancelamento]" [data_cancelamento_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Situação:</strong></td>
          <td>[situacao]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Status:</strong></td>
          <td>[status]</td>
        </tr>
      	<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="40" colspan="2" align="center" >[botoes]</td>
        </tr>
      </table>
    </td>
</tr>
</table>
<input name="campos_desabilitados" id="campos_desabilitados" type="hidden" value="[campos_desabilitados]">
<input type="hidden" id="duplicado_cpf_cnpj" value="[duplicado_cpf_cnpj]">
<input type="hidden" id="duplicado_endereco" value="[duplicado_endereco]">
<input type="hidden" id="duplicar_cpf_cnpj" value="[duplicar_cpf_cnpj]">
<input type="hidden" id="duplicar_endereco" value="[duplicar_endereco]">
<input type="hidden" name="lista_telefones" id="lista_telefones" value='[lista_telefones]'>
<input type="hidden" name="lista_emails" id="lista_emails" value='[lista_emails]'>
<input type="hidden" name="cliente_id" id="cliente_id" value='[cliente_id]'>
</form>

<script type="text/javascript">var cliente_id = '[cliente_id]'</script>
<script type="text/javascript">buscaEnderecoCEP('[cep]');</script>
<script type="text/javascript">buscaEnderecoCEP('[cobranca_cep]', 'cobranca_');</script>
