<script type="text/javascript" src="rds.js.php?m=clientes"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.contatos"></script>
<script type="text/javascript" src="rds.js.php?m=enderecos"></script>
<script type="text/javascript" src="rds.js.php?m=condominios_blocos"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.alertas"></script>
<script type="text/javascript" src="rds.js.php?m=assinaturas"></script>
<script type="text/javascript" src="rds.js.php?m=ceps"></script>
<form name="adicionar" id="adicionar" method="POST" onSubmit="return validaClienteDadosCadastraisProspects(this);" action="[action]">
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
        <td width="75%"><input name="nome" id="nome" type="text" style="width:400px" class="inputBrancoAzulEscuro11" size="50" maxlength="80"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Endere&ccedil;o:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Condomínio:</b></td>
        <td><select name="condominio" id="condominio" style="width:400px" class="inputBrancoAzulEscuro11" onChange="mudaClienteCondominio(this)"><option></option>[condominio]</select> <span id="panorama_condominio"></span></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Bloco:</b></td>
        <td><select name="bloco" id="bloco" style="width:250px" class="inputBrancoAzulEscuro11" onChange="mudaClienteBloco(this)" disabled><option></option></select> <span id="panorama_bloco"></span></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; CEP:</b></td>
        <td><input name="cep" id="cep" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="9" onKeyPress="return formataCEP(this, event)" onBlur="return formataCEP(this, event)"> [adicionar_cep] [pesquisar_cep]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Estado:</b></td>
        <td><input name="estado" id="estado" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Cidade:</b></td>
        <td><input name="cidade" id="cidade" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Bairro:</b></td>
        <td><input name="bairro" id="bairro" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Logradouro:</b></td>
        <td><input name="logradouro" id="logradouro" type="text" style="width:400px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Número:</b></td>
        <td><input name="numero" id="numero" type="text" style="width:400px" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onchange="buscaAlertas(this.form)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Complemento:</b></td>
        <td><select name="complemento_tipo" id="complemento_tipo" style="width:160px" class="inputBrancoAzulEscuro11"><option></option>[complemento_tipo]</select> &nbsp;<input name="complemento_valor" id="complemento_valor" type="text" style="width:234px" class="inputBrancoAzulEscuro11" size="31" maxlength="40" onChange="mudaClienteComplemento(this)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Ponto de Refer&ecirc;ncia:</b></td>
        <td><input name="ponto_referencia" id="ponto_referencia" type="text" style="width:400px" class="inputBrancoAzulEscuro11" size="50" maxlength="105" onchange="buscaAlertas(this.form)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Alerta(s):</b></td>
        <td id="alertas"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Assinaturas:</b> [assinaturas_adicionar]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="assinaturas">[assinaturas]</td>
            </tr>
          </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Tipo:</b></td>
        <td><select name="tipo" id="tipo" style="width:250px" class="inputBrancoAzulEscuro11" onChange="mudaClienteTipo(this)">[tipo]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b id="cpf_cnpj_label">&nbsp;CPF:</b></td>
        <td><input name="cpf_cnpj" id="cpf_cnpj" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="14" onKeyPress="return formataCPFCNPJ(this, event)" onBlur="return formataCPFCNPJ(this, event)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Inscrição Estadual:</b></td>
        <td colspan="2">
          <table cellpadding="0" cellspacing="0" border="0">
            <tr class="txtAzulEscuro11">
              <td><input name="inscricao_estadual" id="inscricao_estadual" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="18" onKeyPress="return formataInscricaoEstadual(this, event)" onBlur="return formataInscricaoEstadual(this, event)" disabled></td>
              <td><input type="checkbox" id="isento_inscricao_estadual" name="isento_inscricao_estadual" value="1" onChange="mudaClienteTipo(this.form.tipo)" disabled></td>
              <td><label for="isento_inscricao_estadual">Isento</label>&nbsp;</td>
              <td><input type="button" class="buttonAzulEscuroBranco11" name="busca_incricao_estadual" id="busca_incricao_estadual" value="Buscar" onClick="buscarInscricaoEstadual(this.form.cpf_cnpj.value, this.form.tipo.value)" disabled></td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Nascimento:</b></td>
        <td><input name="nascimento" id="nascimento" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Contato:</b></td>
        <td><input name="contato" id="contato" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" maxlength="60"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30"><b>&nbsp;Contato(s):</b></td>
        <td>
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
              <td width="50%" valign="top" id="listagem_telefones">[listagem_telefones]</td>
              <td width="50%" valign="top" id="listagem_emails">[listagem_emails]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Dados Cobrança:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Vencimento:</b></td>
        <td><select name="cobranca_vencimento" style="width:250px" id="cobranca_vencimento" class="inputBrancoAzulEscuro11">[cobranca_vencimento]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Formato:</b></td>
        <td><select name="cobranca_formato" style="width:250px" id="cobranca_formato" class="inputBrancoAzulEscuro11" onChange="mudaClienteCobrancaFormato(this)">[cobranca_formato]</select></td>
      </tr>
	  <!--
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Indicação:</b></td>
        <td><select name="indicacao" id="indicacao" style="width:250px" class="inputBrancoAzulEscuro11"><option></option>[indicacao]</select></td>
      </tr>
       <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Operadora Origem:</b></td>
        <td><select name="operadora_origem" id="operadora_origem" style="width:250px" class="inputBrancoAzulEscuro11"><option></option>[operadora_origem]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Situação:</b></td>
        <td><select name="situacao" id="situacao" style="width:250px" class="inputBrancoAzulEscuro11" onchange="mudaSituacao(this)"><option></option>[situacao]</select></td>
      </tr>
	  -->
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Responsável:</b></td>
        <td><select name="responsavel" id="responsavel" style="width:250px" class="inputBrancoAzulEscuro11">[responsavel]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Consultor:</b></td>
        <td><select name="consultor" id="consultor" style="width:250px" class="inputBrancoAzulEscuro11"><option></option>[consultor]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Retorno:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Data / Período:</b></td>
        <td><input name="retorno_data" id="retorno_data" style="width:160px" type="text" class="inputBrancoAzulEscuro11" size="37" maxlength="10" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)">&nbsp;<select name="retorno_periodo" id="retorno_periodo" style="width:87px" class="inputBrancoAzulEscuro11"><option></option>[retorno_periodo]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Comentário:</b></td>
        <td><textarea name="retorno_comentario" style="width:250px" id="retorno_comentario" class="textBrancoAzulEscuro11" rows="2" cols="51"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Comentários:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="comentarios" id="comentarios" style="width:100%" class="textBrancoAzulEscuro11" rows="2" cols="51"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<input type="hidden" id="duplicado_cpf_cnpj" value="0">
<input type="hidden" id="duplicado_endereco" value="0">
<input type="hidden" id="duplicado_bloqueio" value="0">
<input type="hidden" id="duplicar_cpf_cnpj" value="[duplicar_cpf_cnpj]">
<input type="hidden" id="duplicar_endereco" value="[duplicar_endereco]">
<input type="hidden" name="cliente_id" id="cliente_id" value="[id]">
<input type="hidden" name="responsavel_duplicado" id="responsavel_duplicado">
<input type="hidden" name="lista_emails" id="lista_emails" value="[lista_emails]">
<input type="hidden" name="lista_telefones" id="lista_telefones" value="[lista_telefones]">
</form>

<script type="text/javascript">
  var cliente_id = '[id]';
  var telefone_quantidade = 0;
  var email_quantidade = 0;
  var pfpj = 1;
  var ccsc = 0;
</script>
