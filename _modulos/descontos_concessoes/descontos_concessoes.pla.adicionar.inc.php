<script type="text/javascript" src="rds.js.php?m=descontos_concessoes"></script>
<form name="adicionar" method="POST" onSubmit="return validaDescontoConcessao(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><b>&nbsp;Plano:</b></td>
        <td width="75%">[plano]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="29" colspan="2"><b>&nbsp;Dados Desconto:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Desconto:</b></td>
        <td><select name="desconto" id="desconto" class="inputBrancoAzulEscuro11" style="width:100%" onChange="mudaDescontoConcessaoDesconto(this)">[descontos]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Instalação:</b></td>
        <td><select name="instalacao_tipo" id="instalacao_tipo" class="inputBrancoAzulEscuro11" style="width:148px" onChange="mudaDescontoConcessaoValorTipo(this)" disabled>[instalacao_tipo]</select> <input name="instalacao_valor" id="instalacao_valor" type="text" class="inputBrancoAzulEscuro11" style="width:149px" size="30" onKeyPress="return formataDesconto(this,event,this.form.instalacao_tipo.value);" onBlur="return formataDesconto(this, event, this.form.instalacao_tipo.value)" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Mensalidade:</b></td>
        <td><select name="mensalidade_tipo" id="mensalidade_tipo" class="inputBrancoAzulEscuro11" style="width:148px" onChange="mudaDescontoConcessaoValorTipo(this)" disabled>[mensalidade_tipo]</select> <input name="mensalidade_valor" id="mensalidade_valor" type="text" class="inputBrancoAzulEscuro11" style="width:149px" size="30" onKeyPress="return formataDesconto(this,event,this.form.mensalidade_tipo.value);" onBlur="return formataDesconto(this, event, this.form.mensalidade_tipo.value)" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Validade:</b></td>
        <td><select name="validade" id="validade" class="inputBrancoAzulEscuro11" style="width:148px" disabled>[validade]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Tipo:</b></td>
        <td id="tipo"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="29" colspan="2"><b>&nbsp;Período:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Início:</b></td>
        <td><input name="data_inicio" id="data_inicio" type="text" class="inputBrancoAzulEscuro11" style="width:148px" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this, event)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Término:</b></td>
        <td><input name="data_termino" id="data_termino" type="text" class="inputBrancoAzulEscuro11" style="width:148px" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this, event)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Comentários:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:100%" rows="3" cols="52"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
