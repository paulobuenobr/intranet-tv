<script type="text/javascript" src="rds.js.php?m=descontos_concessoes"></script>
<form name="editar" method="POST" onSubmit="return validaDescontoConcessao(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><strong>&nbsp;Plano:</strong></td>
        <td width="75%">[plano]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Dados Desconto:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Desconto:</strong></td>
        <td>[desconto]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Instalação:</strong></td>
        <td><select name="instalacao_tipo" id="instalacao_tipo" class="inputBrancoAzulEscuro11" style="width:148px" onChange="mudaDescontoConcessaoValorTipo(this)" [instalacao_fixa]>[instalacao_tipo]</select> <input name="instalacao_valor" id="instalacao_valor" type="text" class="inputBrancoAzulEscuro11" style="width:149px" size="30" onKeyPress="return formataDesconto(this,event,this.form.instalacao_tipo.value);" onBlur="return formataDesconto(this,event,this.form.instalacao_tipo.value);" value="[instalacao_valor]" [instalacao_fixa]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Mensalidade:</strong></td>
        <td><select name="mensalidade_tipo" id="mensalidade_tipo" class="inputBrancoAzulEscuro11" style="width:148px" onChange="mudaDescontoConcessaoValorTipo(this)" [mensalidade_fixa]>[mensalidade_tipo]</select> <input name="mensalidade_valor" id="mensalidade_valor" type="text" class="inputBrancoAzulEscuro11" style="width:149px" size="30" onKeyPress="return formataDesconto(this,event,this.form.mensalidade_tipo.value);" onBlur="return formataDesconto(this,event,this.form.mensalidade_tipo.value);" value="[mensalidade_valor]" [mensalidade_fixa]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Validade:</strong></td>
        <td><select name="validade" id="validade" class="inputBrancoAzulEscuro11" style="width:148px" [validade_fixa]>[validade]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Tipo:</strong></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Período:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Início:</strong></td>
        <td><input name="data_inicio" id="data_inicio" type="text" class="inputBrancoAzulEscuro11" style="width:148px" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_inicio]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Término:</strong></td>
        <td><input name="data_termino" id="data_termino" type="text" class="inputBrancoAzulEscuro11" style="width:148px" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_termino]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:100%" rows="3" cols="52"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[lista_comentarios]</td>
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
  if (window.opener && window.opener.buscaDescontosConcessoesPlanos) {
    window.opener.buscaDescontosConcessoesPlanos([plano_id]);
  }
</script>
