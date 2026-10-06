<form name="editar" method="POST" onSubmit="return validaAjusteFinanceiro(this)" action="[action]">
<script type="text/javascript" src="rds.js.php?m=ajustes_financeiros"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Editar&nbsp;</b></td></tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><strong>&nbsp;Cliente:</strong></td>
        <td width="75%">[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Ajuste:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Tipo:</strong></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Categoria:</strong></td>
        <td>[categoria]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Descri&ccedil;&atilde;o customizada do servi&ccedil;o:</strong></td>
        <td><input name="descricao_custom_servico" id="descricao_custom_servico" type="text" value="[descricao_custom_servico]" class="inputBrancoAzulEscuro11" style="width:100%" maxlength="250" [descricao_custom_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Assinatura:</strong></td>
        <td>[assinatura]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Competência:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Início:</strong></td>
        <td><input name="competencia_inicio" id="competencia_inicio" type="text" value="[competencia_inicio]" class="inputBrancoAzulEscuro11" size="50" maxlength="7" onKeyPress="return mudaCompetencia(this, event)" onKeyUp="return mudaCompetencia(this, event)" onBlur="return mudaCompetencia(this, event)" style="width: 135px" [competencia_inicio_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Término:</strong></td>
        <td><input name="competencia_termino" id="competencia_termino" type="text" value="[competencia_termino]" class="inputBrancoAzulEscuro11" size="50" maxlength="7" onKeyPress="return mudaCompetencia(this, event)" onKeyUp="return mudaCompetencia(this, event)" onBlur="return mudaCompetencia(this, event)" style="width: 135px" [competencia_termino_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Parcelas:</strong></td>
        <td><input name="parcelas" id="parcelas" type="text" value="[parcelas]" class="inputBrancoAzulEscuro11" size="50" maxlength="2" onKeyPress="return mudaCompetenciaParcelas(this, event)" onKeyUp="return mudaCompetenciaParcelas(this, event)" onBlur="return mudaCompetenciaParcelas(this, event)" style="width: 135px" [parcelas_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Valor:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Mensal:</strong></td>
        <td>
          <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
              <td width="135">[valor_tipo]</td>
              <td>&nbsp;</td>
              <td width="100%"><input name="valor_mensal" id="valor_mensal" type="text" value="[valor_mensal]" class="inputBrancoAzulEscuro11" size="50" onKeyPress="return mudaValorMensal(this, event)" onKeyUp="return mudaValorMensal(this, event)" onblur="return mudaValorMensal(this, event)" style="width:100%" [valor_mensal_disabled]></td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Total:</strong></td>
        <td><input name="valor_total" id="valor_total" type="text" value="[valor_total]" class="inputBrancoAzulEscuro11" size="50" onChange="mudaValorTotal(this)" onKeyPress="return mudaValorTotal(this, event)" onKeyUp="return mudaValorTotal(this, event)" onBlur="return mudaValorTotal(this, event)" style="width: 135px" [valor_total_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Nota Fiscal:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Número:</strong></td>
        <td>[nf_numero]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Emissão:</strong></td>
        <td>[nf_emissao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Valor:</strong></td>
        <td>[nf_valor]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" rows="3" cols="75" style="width:100%"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[comentarios]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td>[status]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>

<script type="text/javascript">
  if (window.opener && window.opener.buscarAjustesFinanceiros) {
    window.opener.buscarAjustesFinanceiros([cliente_id]);
  }
</script>