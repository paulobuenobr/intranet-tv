<form name="editar_dc" id="editar_dc" method="POST" onSubmit="return validaClienteDadosCadastrais(this);" action="[action]">
<script type="text/javascript" src="rds.js.php?m=clientes"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.contatos"></script>
<script type="text/javascript" src="rds.js.php?m=enderecos"></script>
<script type="text/javascript" src="rds.js.php?m=condominios_blocos"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.alertas"></script>
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
        <td width="25%"><strong>&nbsp;<span id="nome_label">[nome_label]</span>:</strong></td>
        <td width="50%"><input name="nome" id="nome" type="text" style="width:400px" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[nome]" [nome_disabled]></td>
        <td width="25%" align="center" bgcolor="#D7D7D7">[situacao_financeira]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display:[nome_fantasia_visivel]" id="nome_fantasia_linha">
        <td width="25%"><strong>&nbsp;Nome Fantasia:</strong></td>
        <td colspan="2"><input name="nome_fantasia" id="nome_fantasia" type="text" style="width:100%" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[nome_fantasia]" [nome_fantasia_disabled]></td>
      </tr>
      [alerta_pendencias]
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Alerta(s):</strong></td>
        <td colspan="2" id="alertas">[alertas]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td colspan="2"><select name="tipo" id="tipo" style="width:250px" class="inputBrancoAzulEscuro11" onChange="mudaClienteTipo(this)" [tipo_disabled]>[tipo]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong id="cpf_cnpj_label">&nbsp;[cpf_cnpj_label][pendencia_cpf_cnpj]:</strong></td>
        <td colspan="2"><input name="cpf_cnpj" id="cpf_cnpj" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="[cpf_cnpj_maxlength]" onKeyPress="return formataCPFCNPJ(this, event)" onBlur="return formataCPFCNPJ(this, event)" value="[cpf_cnpj]" [cpf_cnpj_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Inscrição Estadual:</b></td>
        <td colspan="2">
          <table cellpadding="0" cellspacing="0" border="0">
            <tr class="txtAzulEscuro11">
              <td><input name="inscricao_estadual" id="inscricao_estadual" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="18" onKeyPress="return formataInscricaoEstadual(this, event)" onBlur="return formataInscricaoEstadual(this, event)" value="[inscricao_estadual]" [inscricao_disabled]></td>
              <td><input type="checkbox" id="isento_inscricao_estadual" name="isento_inscricao_estadual" value="1" onChange="mudaClienteTipo(this.form.tipo)" [inscricao_disabled] [isento_inscricao_estadual_checked]></td>
              <td><label for="isento_inscricao_estadual">Isento</label>&nbsp;</td>
              <td><input type="button" class="buttonAzulEscuroBranco11" name="busca_incricao_estadual" id="busca_incricao_estadual" value="Buscar" onClick="buscarInscricaoEstadual(this.form.cpf_cnpj.value, this.form.tipo.value)" [inscricao_disabled]></td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Nascimento[pendencia_nascimento]:</strong></td>
        <td colspan="2"><input name="nascimento" id="nascimento" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)" value="[nascimento]" [nascimento_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="3">
        	<table border="0" cellspacing="0" cellpadding="0" width="100%">
        		<tr>
        			<td class="txtAzulEscuro11"><strong>&nbsp;Endere&ccedil;o:</strong></td>
        			<td style="text-align:right"><a href="?m=clientes&a=editar_dc&i=[id]&imprimir_endereco=1" title="Imprimir endereço de cobrança"><img src="imagens/icone_endereco.png" width="20" height="20" style="margin: 2px" /></a></td>
        		</tr>
        	</table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Condomínio:</strong></td>
        <td colspan="2"><select name="condominio" id="condominio" style="width:400px" class="inputBrancoAzulEscuro11" onChange="mudaClienteCondominio(this)" [condominio_disabled]><option></option>[condominio]</select> <span id="panorama_condominio">[panorama_condominio]</span></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bloco[pendencia_bloco]:</strong></td>
        <td colspan="2"><select name="bloco" id="bloco" style="width:250px" class="inputBrancoAzulEscuro11" onChange="mudaClienteBloco(this)" [bloco_disabled]><option></option>[bloco]</select> <span id="panorama_bloco">[panorama_bloco]</span></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; CEP: </strong></td>
        <td colspan="2"><input name="cep" id="cep" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="9" onKeyPress="return formataCEP(this, event)" onBlur="return formataCEP(this, event)" value="[cep]" [cep_disabled]> [adicionar_cep] [pesquisar_cep]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Estado:</strong></td>
        <td colspan="2"><input name="estado" id="estado" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" value="[estado]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Cidade:</strong></td>
        <td colspan="2"><input name="cidade" id="cidade" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" value="[cidade]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bairro:</strong></td>
        <td colspan="2"><input name="bairro" id="bairro" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" value="[bairro]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Logradouro:</strong></td>
        <td colspan="2"><input name="logradouro" id="logradouro" type="text" style="width:400px" class="inputBrancoAzulEscuro11" size="50" value="[logradouro]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Número:</strong></td>
        <td colspan="2"><input name="numero" id="numero" type="text" style="width:400px" class="inputBrancoAzulEscuro11" size="50" maxlength="10" value="[numero]" [numero_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Complemento[pendencia_complemento]:</strong></td>
        <td colspan="2"><select name="complemento_tipo" id="complemento_tipo" style="width:160px" class="inputBrancoAzulEscuro11" [complemento_tipo_disabled]>[complemento_tipo]</select> &nbsp;<input name="complemento_valor" id="complemento_valor" type="text" style="width:234px" class="inputBrancoAzulEscuro11" size="31" maxlength="100" value="[complemento_valor]" onChange="mudaClienteComplemento(this)" [complemento_valor_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo;&nbsp;Ponto de Refer&ecirc;ncia:</strong></td>
        <td colspan="2"><input name="ponto_referencia" id="ponto_referencia" type="text" style="width:400px" class="inputBrancoAzulEscuro11" size="50" maxlength="105" value="[ponto_referencia]" ></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Contato:</strong></td>
        <td colspan="2"><input name="contato" id="contato" type="text" style="width:400px" class="inputBrancoAzulEscuro11" size="50" maxlength="60" value="[contato]" [contato_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30"><b>&nbsp;Contato(s)[pendencia_contatos]:</b></td>
        <td colspan="2">
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
              <td width="50%" valign="top" id="listagem_telefones">[listagem_telefones]</td>
              <td width="50%" valign="top" id="listagem_emails">[listagem_emails]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Comentários:</strong></td>
        <td colspan="2"><textarea name="comentarios" id="comentarios" style="width:400px" class="textBrancoAzulEscuro11" rows="3" cols="51" [comentarios_disabled]>[comentarios]</textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Data de Cadastro:</strong></td>
        <td colspan="2">[data_cadastro]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Indicação:</strong></td>
        <td colspan="2"><select name="indicacao" id="indicacao" style="width:250px" class="inputBrancoAzulEscuro11" [indicacao_disabled]>[indicacao]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Operadora Origem:</strong></td>
        <td colspan="2"><select name="operadora_origem" id="operadora_origem" style="width:250px" class="inputBrancoAzulEscuro11" [operadora_origem_disabled]>[operadora_origem]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Situação:</strong></td>
        <td colspan="2"><select name="situacao" id="situacao" style="width:250px" class="inputBrancoAzulEscuro11" [situacao_disabled]>[situacao]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Origem:</strong></td>
        <td colspan="2"><select name="origem" id="origem" style="width:250px" class="inputBrancoAzulEscuro11" [origem_disabled]>[origem]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Responsável:</strong></td>
        <td colspan="2">[responsavel]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Consultor:</strong></td>
        <td colspan="2"><select name="consultor" id="consultor" style="width:250px" class="inputBrancoAzulEscuro11" [consultor_disabled]>[consultor]</select></td>
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
<input name="campos_desabilitados" id="campos_desabilitados" type="hidden" value="[campos_desabilitados]">
<input type="hidden" id="atualizacao_cadastro" value="[atualizacao_cadastro]">
<input type="hidden" id="duplicado_cpf_cnpj" value="[duplicado_cpf_cnpj]">
<input type="hidden" id="duplicado_endereco" value="[duplicado_endereco]">
<input type="hidden" id="duplicado_bloqueio" value="0">
<input type="hidden" id="duplicar_cpf_cnpj" value="[duplicar_cpf_cnpj]">
<input type="hidden" id="duplicar_endereco" value="[duplicar_endereco]">
<input type="hidden" id="bloco_original" value="[bloco_original]">
<input type="hidden" name="cliente_id" id="cliente_id" value='[id]'>
<input type="hidden" name="lista_emails" id="lista_emails" value='[lista_emails]'>
<input type="hidden" name="lista_telefones" id="lista_telefones" value='[lista_telefones]'>
</form>

<script type="text/javascript">
  var cliente_id = '[id]';
  var pendencias = [pendencias];
  //verificaPendencias();
</script>
