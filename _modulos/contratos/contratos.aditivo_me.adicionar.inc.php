<script type="text/javascript" src="rds.js.php?m=contratos"></script>
<script type="text/javascript" src="rds.js.php?m=enderecos"></script>
<script type="text/javascript" src="rds.js.php?m=condominios_blocos"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.alertas"></script>
<form name="adicionar" method="POST" onsubmit="return validaContratoAditivoME(this)">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11">[acoes]</tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><b>&nbsp;Cliente:</b></td>
          <td>[nome]</td>
        </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><b>&nbsp;Código:</b></td>
	        <td>[codigo]</td>
	      </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b>&nbsp;Alerta(s):</b></td>
          <td id="alertas"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><b>&nbsp;Endere&ccedil;o de Instalação:</b></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b>&nbsp;&raquo; Condomínio:</b></td>
          <td><select name="condominio" id="condominio" style="width:300px" class="inputBrancoAzulEscuro11" onChange="mudaClienteCondominio(this); atualizaCondominioCobranca();" [condominio_disabled]><option></option>[condominio]</select> <span id="panorama_condominio">[panorama_condominio]</span></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b>&nbsp;&raquo; Bloco:</b></td>
          <td><select name="bloco" id="bloco" style="width:150px" class="inputBrancoAzulEscuro11" onChange="mudaClienteBloco(this); atualizaBlocoCobranca();" [bloco_disabled]><option></option>[bloco]</select></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b>&nbsp;&raquo; CEP:</b></td>
          <td><input name="cep" id="cep" type="text" style="width:150px" class="inputBrancoAzulEscuro11" size="50" maxlength="9" onKeyPress="return formataCEP(this,event);" onBlur="return formataCEP(this,event);" onchange="if (this.form.mesmo_endereco.checked) {this.form.cobranca_cep.value = this.form.cep.value;}" value="[cep]" [cep_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b>&nbsp;&raquo; Estado:</b></td>
          <td><input name="estado" id="estado" type="text" style="width:300px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b>&nbsp;&raquo; Cidade:</b></td>
          <td><input name="cidade" id="cidade" type="text" style="width:300px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b>&nbsp;&raquo; Bairro:</b></td>
          <td><input name="bairro" id="bairro" type="text" style="width:300px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b>&nbsp;&raquo; Logradouro:</b></td>
          <td><input name="logradouro" id="logradouro" type="text" style="width:300px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b>&nbsp;&raquo; Número:</b></td>
          <td><input name="numero" id="numero" type="text" style="width:150px" class="inputBrancoAzulEscuro11" size="50" maxlength="10" value="[numero]" onchange="atualizaNumeroCobranca()" onkeyup="atualizaNumeroCobranca()" [numero_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><b>&nbsp;&raquo; Complemento:</b></td>
          <td><select name="complemento_tipo" id="complemento_tipo" style="width:150px" class="inputBrancoAzulEscuro11" onchange="atualizaComplementoTipoCobranca()" onkeypress="atualizaComplementoTipoCobranca()" [complemento_tipo_disabled]>[complemento_tipo]</select><span style="width:5px">&nbsp;</span><input name="complemento_valor" id="complemento_valor" type="text" style="width:145px" class="inputBrancoAzulEscuro11" size="31" maxlength="40" value="[complemento_valor]" onChange="mudaClienteComplemento(this); atualizaComplementoValorCobranca()" onkeyup="atualizaComplementoValorCobranca()" [complemento_valor_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><b>&nbsp;Endere&ccedil;o de Cobrança / Correspondência:</b> <input type="checkbox" name="mesmo_endereco" id="mesmo_endereco" value="1" [mesmo_endereco_checked] onclick="mudaMesmoEndereco(this)"><label for="mesmo_endereco">Mesmo endereço de instalação</label></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Condomínio:</strong></td>
          <td><select name="cobranca_condominio" id="cobranca_condominio" style="width:300px" class="inputBrancoAzulEscuro11" onChange="mudaClienteCondominio(this, 'cobranca_')" [cobranca_condominio_disabled]><option></option>[cobranca_condominio]</select> <span id="cobranca_panorama_condominio">[cobranca_panorama_condominio]</span></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Bloco:</strong></td>
          <td><select name="cobranca_bloco" id="cobranca_bloco" style="width:150px" class="inputBrancoAzulEscuro11" onChange="mudaClienteBloco(this, 'cobranca_')" [cobranca_bloco_disabled]><option></option>[cobranca_bloco]</select></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; CEP:</strong></td>
          <td><input name="cobranca_cep" id="cobranca_cep" type="text" style="width:150px" class="inputBrancoAzulEscuro11" size="50" maxlength="9" onKeyPress="return formataCEP(this, event, 'cobranca_');" onBlur="return formataCEP(this, event, 'cobranca_');" value="[cobranca_cep]" [cobranca_cep_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Estado:</strong></td>
          <td><input name="cobranca_estado" id="cobranca_estado" type="text" style="width:300px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Cidade:</strong></td>
          <td><input name="cobranca_cidade" id="cobranca_cidade" type="text" style="width:300px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Bairro:</strong></td>
          <td><input name="cobranca_bairro" id="cobranca_bairro" type="text" style="width:300px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Logradouro:</strong></td>
          <td><input name="cobranca_logradouro" id="cobranca_logradouro" type="text" style="width:300px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Número:</strong></td>
          <td><input name="cobranca_numero" id="cobranca_numero" type="text" style="width:150px" class="inputBrancoAzulEscuro11" size="50" maxlength="10" value="[cobranca_numero]" [cobranca_numero_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Complemento:</strong></td>
          <td><select name="cobranca_complemento_tipo" id="cobranca_complemento_tipo" style="width:150px" class="inputBrancoAzulEscuro11" [cobranca_complemento_tipo_disabled]>[cobranca_complemento_tipo]</select><span style="width:5px">&nbsp;</span><input name="cobranca_complemento_valor" id="cobranca_complemento_valor" type="text" style="width:145px" class="inputBrancoAzulEscuro11" size="31" maxlength="40" value="[cobranca_complemento_valor]" [cobranca_complemento_valor_disabled]></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
           <td height="30" colspan="2"><strong>&nbsp;Formas de Pagamento Instalação:</strong></td>
        </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" id="formas_pagamento">[formas_pagamento]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><b>&nbsp;Comentários:</b></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="3" cols="75" style="width: 100%"></textarea></td>
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
</form>

<script type="text/javascript">var cliente_id = '[cliente_id]'</script>
<script type="text/javascript">buscaEnderecoCEP('[cep]');</script>
<script type="text/javascript">buscaEnderecoCEP('[cobranca_cep]', 'cobranca_');</script>
