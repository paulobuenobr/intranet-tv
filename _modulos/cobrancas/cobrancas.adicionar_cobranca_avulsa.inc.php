<script type="text/javascript" src="rds.js.php?m=cobrancas"></script>
<form name="adicionar" method="POST" action="[action]" onSubmit="return validaAdicionarCobranca(this)">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Adicionar Cobrança Avulsa&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="30%"><strong>&nbsp;Cliente:</strong></td>
        <td width="70%">[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Formato:</strong></td>
        <td>[formato]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Vencimento:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Início:</strong></td>
        <td><input name="vencimento_inicio" id="vencimento_inicio" style="width:150px" type="text" class="inputBrancoAzulEscuro11" size="30" maxlength="10" onKeyPress="return formataCompetencia(this, event)" onBlur="return formataCompetencia(this, event)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Término:</strong></td>
        <td><input name="vencimento_termino" id="vencimento_termino" style="width:150px" type="text" class="inputBrancoAzulEscuro11" size="30" maxlength="10" onKeyPress="return formataCompetencia(this, event)" onBlur="return formataCompetencia(this, event)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Data Vencimento:</strong></td>
        <td><input name="data_vencimento" id="data_vencimento" style="width:150px" type="text" class="inputBrancoAzulEscuro11" size="30" maxlength="10" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)"></td>
      </tr>
      <tbody style="display:[adicionar_plus]">
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><strong>&nbsp;Valores:</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Acréscimo:</strong></td>
          <td><input name="valor_acrescimo" id="valor_acrescimo" style="width:150px" type="text" class="inputBrancoAzulEscuro11" size="30" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Decréscimo:</strong></td>
          <td><input name="valor_decrescimo" id="valor_decrescimo" style="width:150px" type="text" class="inputBrancoAzulEscuro11" size="30" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2">
  	       <input type="checkbox" name="nao_gerar_juros" id="nao_gerar_juros" value="true"><label for="nao_gerar_juros"> Não gerar juros</label>
          </td>
        </tr>
      </tbody>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentário:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td align="center" colspan="2"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="3" cols="70"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>

<script type="text/javascript">
  var dia_vencimento = '[dia_vencimento]';
  var vencimento_maximo = '[vencimento_maximo]';
  if (window.opener != null && window.opener.document.getElementById("cobrancas") != null) {
    window.opener.buscarCobrancas([cliente_id]);
  }
</script>
