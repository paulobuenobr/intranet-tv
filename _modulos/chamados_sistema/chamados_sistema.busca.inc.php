<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td>
      <table border="0" cellspacing="1" cellpadding="3" width="100%">
        <tr align="center" class="txtBranco11">
          [acoes]
        </tr>
      </table>
    </td>
  </tr>

  <tbody id="busca_chamados_formulario" style="[exibir_busca_chamados]">
	  <tr>
	    <td>
		    <table width="100%" border="0" cellspacing="1" cellpadding="3">
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28" width="26%"><b>&nbsp;Código:</b></td>
		        <td width="74%"><input id="chamados_codigo" value="[chamados_codigo]" type="text" class="inputBrancoAzulEscuro11" maxlength="80" style="width:150" onkeypress="executarComando(event, 'buscarChamadosIr')">
		          <input type="button" id="chamados_codigo_ir" class="buttonAzulEscuroBranco11" value="Ir" style="width:30px" onClick="buscarChamadosIr()">
		        </td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><b>&nbsp;Tipo:</b></td>
		        <td height="27"><select id="chamados_tipo" class="inputBrancoAzulEscuro11" style="width:150" onchange="mudaChamadoTipo(this.value)" onkeypress="executarComando(event, 'buscarChamados')"><option></option>[chamados_tipo]</select></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><b>&nbsp;Conteúdo:</b></td>
		        <td><input id="chamados_conteudo" value="[chamados_conteudo]" type="text" class="inputBrancoAzulEscuro11" maxlength="80" style="width:100%" onkeypress="executarComando(event, 'buscarChamados')"></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><b>&nbsp;Cliente:</b></td>
		        <td><input id="chamados_cliente" value="[chamados_cliente]" type="text" class="inputBrancoAzulEscuro11" maxlength="80" style="width:100%" onkeypress="executarComando(event, 'buscarChamados')"></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><b>&nbsp;Condomínio:</b></td>
		        <td><select id="chamados_condominio" class="inputBrancoAzulEscuro11" style="width:100%" onkeypress="executarComando(event, 'buscarChamados')"><option></option>[chamados_condominio]</select></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><b>&nbsp;Categoria:</b></td>
		        <td><select id="chamados_categoria" class="inputBrancoAzulEscuro11" style="width:100%" onkeypress="executarComando(event, 'buscarChamados')"><option></option>[chamados_categoria]</select></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><select id="chamados_responsavel_tipo" class="inputBrancoAzulEscuro11" style="width:100%" onkeypress="executarComando(event, 'buscarChamados')">[chamados_responsavel_tipo]</select></td>
		        <td><select id="chamados_responsavel" class="inputBrancoAzulEscuro11" style="width:100%" onkeypress="executarComando(event, 'buscarChamados')"><option></option>[chamados_responsavel]</select></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><select id="chamados_data_tipo" class="inputBrancoAzulEscuro11" style="width:100%" onkeypress="executarComando(event, 'buscarChamados')">[chamados_data_tipo]</select></td>
		        <td><input id="chamados_data_inicio" value="[chamados_data_inicio]" style="width:100" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" onkeypress="executarComando(event, 'buscarChamados'); return formataData(this, event)" onblur="return formataData(this, event)"> até <input id="chamados_data_termino" value="[chamados_data_termino]" type="text" style="width:100" class="inputBrancoAzulEscuro11" size="10" maxlength="10" onkeypress="executarComando(event, 'buscarChamados'); return formataData(this, event)" onblur="return formataData(this, event)"> (dd/mm/aaaa)</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><b>&nbsp;Status:</b></td>
		        <td height="27"><select id="chamados_status" class="inputBrancoAzulEscuro11" style="width:150" onkeypress="executarComando(event, 'buscarChamados')"><option></option>[chamados_status]</select></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="30" colspan="2" align="center">
		          <input type="button" id="buscar_chamados" class="buttonAzulEscuroBranco11" value="Buscar" onClick="buscarChamados()">
		          <input type="button" id="limpar_chamados" class="buttonAzulEscuroBranco11" value="Limpar" onClick="limparFormulario('buscarChamados')">
		          <!-- <input type="button" id="imprimir_chamados" class="buttonAzulEscuroBranco11" value="Imprimir" onClick="imprimeChamados()"> -->
		        </td>
		      </tr>
		    </table>
	    </td>
	  </tr>
  </tbody>

  <tbody id="busca_clientes_formulario" style="[exibir_busca_clientes]">
	  <tr>
	    <td>
		    <table width="100%" border="0" cellspacing="1" cellpadding="3">
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28" width="26%"><b>&nbsp;ID:</b></td>
		        <td width="74%"><input id="clientes_id" value="[clientes_id]" type="text" class="inputBrancoAzulEscuro11" maxlength="80" style="width:150" onkeypress="executarComando(event, 'buscarClientesIr')">
		          <input type="button" id="clientes_id_ir" class="buttonAzulEscuroBranco11" value="Ir" style="width:30px" onClick="buscarClientesIr()">
		        </td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28" width="26%"><b>&nbsp;Nome:</b></td>
		        <td width="74%"><input id="clientes_nome" value="[clientes_nome]" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="80" style="width:100%" onkeypress="executarComando(event, 'buscarClientes')"></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><b>&nbsp;Condomínio:</b></td>
		        <td><select id="clientes_condominio" class="inputBrancoAzulEscuro11" onChange="mudaClienteCondominio(this)" style="width:100%" onkeypress="executarComando(event, 'buscarClientes')"><option></option>[clientes_condominio]</select></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><b>&nbsp;Bloco/Complemento:</b></td>
		        <td><select id="clientes_bloco" class="inputBrancoAzulEscuro11" style="width:150" onkeypress="executarComando(event, 'buscarClientes')" [clientes_blocos_editavel]><option>[clientes_blocos]</option></select>
		          <input id="clientes_complemento" value="[clientes_complemento]" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="40" style="width:147" onkeypress="executarComando(event, 'buscarClientes')"></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><b>&nbsp;Tipo:</b></td>
		        <td><select id="clientes_tipo" class="inputBrancoAzulEscuro11" onChange="mudaChamadoClienteTipo(this)" style="width:300" onkeypress="executarComando(event, 'buscarClientes')"><option></option>[clientes_tipos]</select></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><span style="font-weight:bold" id="cpf_cnpj_label">&nbsp;CPF:</span></td>
		        <td><input id="clientes_cpf_cnpj" value="[clientes_cpf_cnpj]" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="14" onkeypress="executarComando(event, 'buscarClientes'); return formataClienteCPFCNPJ(this, event)" onblur="return formataClienteCPFCNPJ(this, event)" style="width:300"></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><b>&nbsp;Telefone:</b></td>
		        <td><input id="clientes_telefone" value="[clientes_telefone]" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="14" onkeypress="executarComando(event, 'buscarClientes'); return formataTelefone(this, event)" onblur="return formataTelefone(this, event)" style="width:300"></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><b>&nbsp;E-Mail:</b></td>
		        <td><input id="clientes_email" value="[clientes_email]" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="80" style="width:300" onkeypress="executarComando(event, 'buscarClientes')"></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><b>&nbsp;Status:</b></td>
		        <td><select id="clientes_status" class="inputBrancoAzulEscuro11" style="width:150" onkeypress="executarComando(event, 'buscarClientes')"><option></option>[clientes_status]</select></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="30" colspan="2" align="center">
		          <input type="button" id="buscar_clientes" class="buttonAzulEscuroBranco11" value="Buscar" onClick="buscarClientes()">
		          <input type="button" id="limpar_clientes" class="buttonAzulEscuroBranco11" value="Limpar" onClick="limparFormulario('buscarClientes')">
		        </td>
		      </tr>
		    </table>
	    </td>
	  </tr>
  </tbody>

  <tbody id="busca_condominios_formulario" style="[exibir_busca_condominios]">
	  <tr>
	    <td>
		    <table width="100%" border="0" cellspacing="1" cellpadding="3">
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28" width="26%"><strong>&nbsp;Nome:</strong></td>
		        <td width="74%"><input id="condominios_nome" value="[condominios_nome]" type="text" class="inputBrancoAzulEscuro11" maxlength="80" style="width:100%" onkeypress="executarComando(event, 'buscarCondominios')"></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><strong>&nbsp;Cidade:</strong></td>
		        <td><select id="condominios_cidade" class="inputBrancoAzulEscuro11" onChange="atualizaCondominiosBairros(this.value)" style="width:100%" onkeypress="executarComando(event, 'buscarCondominios')"><option></option>[condominios_cidades]</select></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><strong>&nbsp;Bairro:</strong></td>
		        <td><select id="condominios_bairro" class="inputBrancoAzulEscuro11" style="width:100%" onkeypress="executarComando(event, 'buscarCondominios')" [condominios_bairros_editavel]><option></option>[condominios_bairros]</select></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><strong>&nbsp;Logradouro:</strong></td>
		        <td><input id="condominios_logradouro" value="[condominios_logradouro]" type="text" class="inputBrancoAzulEscuro11" maxlength="80" style="width:100%" onkeypress="executarComando(event, 'buscarCondominios')"></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><strong>&nbsp;Responsável:</strong></td>
		        <td><select id="condominios_responsavel" class="inputBrancoAzulEscuro11" style="width:100%" onkeypress="executarComando(event, 'buscarCondominios')"><option></option>[condominios_responsavel]</select></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><strong>&nbsp;&raquo; Data Cadastro:</strong></td>
		        <td><input id="condominios_data_cadastro_inicio" value="[condominios_data_cadastro_inicio]" type="text" class="inputBrancoAzulEscuro11" maxlength="10" onkeypress="executarComando(event, 'buscarCondominios'); return formataData(this, event)" onblur="return formataData(this, event)" size="10"> até <input id="condominios_data_cadastro_termino" value="[condominios_data_cadastro_termino]" type="text" class="inputBrancoAzulEscuro11" maxlength="10" onkeypress="executarComando(event, 'buscarCondominios'); return formataData(this, event)" onblur="return formataData(this, event)" size="10"> (dd/mm/aaaa)</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><strong>&nbsp;&raquo; Data Ativação:</strong></td>
		        <td><input id="condominios_data_ativacao_inicio" value="[condominios_data_ativacao_inicio]" type="text" class="inputBrancoAzulEscuro11" maxlength="10" onkeypress="executarComando(event, 'buscarCondominios'); return formataData(this, event)" onblur="return formataData(this, event)" size="10"> até <input id="condominios_data_ativacao_termino" value="[condominios_data_ativacao_termino]" type="text" class="inputBrancoAzulEscuro11" maxlength="10" onkeypress="executarComando(event, 'buscarCondominios'); return formataData(this, event)" onblur="return formataData(this, event)" size="10"> (dd/mm/aaaa)</td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><strong>&nbsp;Tipo:</strong></td>
		        <td><select id="condominios_tipo" class="inputBrancoAzulEscuro11" style="width:150px" onkeypress="executarComando(event, 'buscarCondominios')"><option></option>[condominios_tipo]</select></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="28"><strong>&nbsp;Status:</strong></td>
		        <td><select id="condominios_status" class="inputBrancoAzulEscuro11" style="width:150px" onkeypress="executarComando(event, 'buscarCondominios')"><option></option>[condominios_status]</select></td>
		      </tr>
		      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		        <td height="30" colspan="2" align="center">
		          <input type="button" id="buscar_condominios" class="buttonAzulEscuroBranco11" value="Buscar" onClick="buscarCondominios()">
		          <input type="button" id="limpar_condominios" class="buttonAzulEscuroBranco11" value="Limpar" onClick="limparFormulario('buscarCondominios')">
		        </td>
		      </tr>
		    </table>
	    </td>
	  </tr>
  </tbody>

  <tbody id="busca_resultado" style="[exibir_busca_resultado]">
    <tr>
      <td>
        <div id="busca_resultado_listagem" style="width:100%;height:100%;overflow-y:scroll">[busca_resultado_listagem]</div>
      </td>
    </tr>
    <tr align="center" class="txtBranco11">
      <td>
        <table border="0" cellspacing="1" cellpadding="2" width="100%">
          <tr>
            <td bgcolor="#505050" id="busca_status">
              [busca_status]
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </tbody>

</table>

<script type="text/javascript">
  try {
	  document.getElementById('chamados_codigo').focus();
  } catch(err) {}
</script>
