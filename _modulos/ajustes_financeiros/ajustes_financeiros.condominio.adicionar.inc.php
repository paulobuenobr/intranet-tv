<form name="adicionar" method="POST" onSubmit="return validaAjusteFinanceiro(this)" action="[action]">
<script type="text/javascript" src="rds.js.php?m=ajustes_financeiros"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Adicionar&nbsp;</b></td></tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><strong>&nbsp;Condomínio:</strong></td>
        <td width="75%">[condominio]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Categoria:</strong></td>
        <td>[categoria]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Valor:</strong></td>
        <td>[valor_tipo] <input name="valor" id="valor" type="text" class="inputBrancoAzulEscuro11" size="50"  onKeyPress="return formataAjusteValor(this, this.form.valor_tipo.value, event)" onblur="return formataAjusteValor(this, this.form.valor_tipo.value, event)" style="width:200px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Período:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Início:</strong></td>
        <td><input name="competencia_inicio" id="competencia_inicio" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="7" onKeyPress="return formataCompetencia(this, event)" onBlur="return formataCompetencia(this, event)" style="width:135px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Término:</strong></td>
        <td><input name="competencia_termino" id="competencia_termino" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="7" onKeyPress="return formataCompetencia(this, event)" onBlur="return formataCompetencia(this, event)" style="width:135px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" rows="3" cols="75" style="width:100%"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>