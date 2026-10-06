<form name="buscar" method="POST" onSubmit="return validaClienteBusca(this);" action="[action]">
<script type="text/javascript" src="rds.js.php?m=condominios_blocos" id="condominios_blocos.js"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.buscar" id="clientes.buscar.js"></script>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11">[acoes]</tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="18%"><strong>&nbsp;Nome:</strong></td>
        <td width="82%"><input name="nome" id="nome" type="text" style="width:300px" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[nome]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Condomínio:</strong></td>
        <td><select name="condominio2" id="condominio2" style="width:300px" class="inputBrancoAzulEscuro11" onChange="mudaClienteCondominio(this, '', 1); atualizaCondominiosCampo(this.value)"><option></option>[condominio2]</select>
          <input name="condominio1" id="condominio1" style="width:298px" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[condominio1]" [condominio_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Bloco:</strong></td>
        <td><select name="bloco" id="bloco" style="width:150px" class="inputBrancoAzulEscuro11" onChange="mudaClienteBloco(this)" [bloco_disabled]><option></option>[bloco]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Complemento:</strong></td>
        <td><input name="complemento" id="complemento" style="width:150px" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="40" value="[complemento]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Cidade:</strong></td>
        <td><select name="cidade2" id="cidade2" style="width:300px" class="inputBrancoAzulEscuro11" onChange="atualizaBairros(this.value)"><option></option>[cidade2]</select>
          <input name="cidade1" id="cidade1" style="width:298px" type="text" class="inputBrancoAzulEscuro11" maxlength="80" value="[cidade1]" [cidade_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Bairro:</strong></td>
        <td><select name="bairro2" id="bairro2" style="width:300px" class="inputBrancoAzulEscuro11" onChange="atualizaBairrosCampo(this.value)" [bairro2_disabled]><option></option>[bairro2]</select>
          <input name="bairro1" id="bairro1" style="width:298px" type="text" class="inputBrancoAzulEscuro11" maxlength="80" value="[bairro1]" [bairro_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="18%"><strong>&nbsp;Logradouro:</strong></td>
        <td width="82%"><input name="logradouro" id="logradouro" type="text" style="width:300px" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[logradouro]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;CEP:</strong></td>
        <td><input name="cep" id="cep" style="width:150px" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="9" maxlength="9" onKeyPress="return formataCEP(this, event)" onBlur="return formataCEP(this, event)" value="[cep]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Número:</strong></td>
        <td><input name="numero" id="numero" style="width:150px" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="40" value="[numero]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td><select name="tipo" id="tipo" style="width:150px" class="inputBrancoAzulEscuro11" onChange="mudaClienteTipo(this)"><option></option>[tipo]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong id="cpf_cnpj_label">&nbsp;CPF:</strong></td>
        <td><input name="cpf_cnpj" id="cpf_cnpj" style="width:150px" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="14" onKeyPress="return formataCPFCNPJ(this,event);" onBlur="return formataCPFCNPJ(this,event);" value="[cpf_cnpj]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Telefone:</strong></td>
        <td><input name="telefone" id="telefone" style="width:150px" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="14" onKeyPress="return formataTelefone(this,event);" onBlur="return formataTelefone(this,event);" value="[telefone]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;E-Mail:</strong></td>
        <td><input name="email" id="email" type="text" style="width:300px" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[email]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Responsável:</strong></td>
        <td><select name="responsavel" id="responsavel" style="width:300px" class="inputBrancoAzulEscuro11" [responsavel_disabled]><option></option>[responsavel]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Data Cadastro:</strong></td>
        <td><input name="data_cadastro_inicio" id="data_cadastro_inicio" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_cadastro_inicio]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> até <input name="data_cadastro_termino" id="data_cadastro_termino" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_cadastro_termino]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> (dd/mm/aaaa)</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Situação:</strong></td>
        <td><select name="situacao" id="situacao" style="width:150px" class="inputBrancoAzulEscuro11"><option></option>[situacao]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td><select name="status" id="status" style="width:150px" class="inputBrancoAzulEscuro11"><option></option>[status]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Origem:</strong></td>
        <td><select name="origem" id="origem" style="width:150px" class="inputBrancoAzulEscuro11"><option></option>[origem]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><strong>&nbsp;Assinatura:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Serviço / Plano:</strong></td>
        <td><select name="servico" id="servico" class="inputBrancoAzulEscuro11" style="width:300px" onChange="atualizaPlanos(this.value)"><option></option>[servico]</select>
          <select name="plano" id="plano" class="inputBrancoAzulEscuro11" style="width:298px" [plano_disabled]><option></option>[plano]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Data Cadastro:</strong></td>
        <td><input name="data_ass_cadastro_inicio" id="data_ass_cadastro_inicio" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_ass_cadastro_inicio]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> até <input name="data_ass_cadastro_termino" id="data_ass_cadastro_termino" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_ass_cadastro_termino]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> (dd/mm/aaaa)</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Data Fechamento:</strong></td>
        <td><input name="data_ass_fechamento_inicio" id="data_ass_fechamento_inicio" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_ass_fechamento_inicio]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> até <input name="data_ass_fechamento_termino" id="data_ass_fechamento_termino" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_ass_fechamento_termino]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> (dd/mm/aaaa)</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Data Ativação:</strong></td>
        <td><input name="data_ass_ativacao_inicio" id="data_ass_ativacao_inicio" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_ass_ativacao_inicio]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> até <input name="data_ass_ativacao_termino" id="data_ass_ativacao_termino" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_ass_ativacao_termino]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> (dd/mm/aaaa)</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Data Cancelamento:</strong></td>
        <td><input name="data_ass_cancelamento_inicio" id="data_ass_cancelamento_inicio" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_ass_cancelamento_inicio]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> até <input name="data_ass_cancelamento_termino" id="data_ass_cancelamento_termino" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_ass_cancelamento_termino]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> (dd/mm/aaaa)</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
      [resultado]
    </table></td>
  </tr>
</table>
<input name="m" id="m" type="hidden" value="[modulo]">
<input name="a" id="a" type="hidden" value="buscar">
<input name="g" id="g" type="hidden" value="1">
</form>
