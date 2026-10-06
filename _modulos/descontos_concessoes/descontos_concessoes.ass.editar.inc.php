<form name="adicionar" method="POST" onSubmit="return validaDescontoConcessao(this)" action="[action]">
<script type="text/javascript" src="rds.js.php?m=tarefas"></script>
<script type="text/javascript" src="rds.js.php?m=descontos_concessoes"></script>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Cliente:</strong></td>
        <td width="80%" id="cliente">[cliente]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Assinatura:</strong></td>
        <td>[assinatura]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Desconto:</strong></td>
        <td>[desconto]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Instalação:</strong></td>
        <td><select name="instalacao_tipo" id="instalacao_tipo" class="inputBrancoAzulEscuro11" style="width:127px" onChange="mudaDescontoConcessaoValorTipo(this)" [instalacao_fixa]>[instalacao_tipo]</select> <input name="instalacao_valor" id="instalacao_valor" type="text" class="inputBrancoAzulEscuro11" size="30" style="width:157px" onKeyPress="return formataDesconto(this,event,this.form.instalacao_tipo.value);" onBlur="return formataDesconto(this,event,this.form.instalacao_tipo.value);" value="[instalacao_valor]" [instalacao_fixa]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Mensalidade:</strong></td>
        <td><select name="mensalidade_tipo" id="mensalidade_tipo" class="inputBrancoAzulEscuro11" style="width:127px" onChange="mudaDescontoConcessaoValorTipo(this)" [mensalidade_fixa]>[mensalidade_tipo]</select> <input name="mensalidade_valor" id="mensalidade_valor" type="text" class="inputBrancoAzulEscuro11" size="30" style="width:157px" onKeyPress="return formataDesconto(this,event,this.form.mensalidade_tipo.value);" onBlur="return formataDesconto(this,event,this.form.mensalidade_tipo.value);" value="[mensalidade_valor]" [mensalidade_fixa]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display:[validade_visivel]">
        <td><strong>&nbsp;Validade:</strong></td>
        <td>
          <select name="validade_1" id="validade_1" class="inputBrancoAzulEscuro11" style="width:127px;display:[exibe_validade_1]" [validade_fixa]>[validade_1]</select>
          <input type="text" name="validade_2" id="validade_2" class="inputBrancoAzulEscuro11" style="width:127px;display:[exibe_validade_2]" value="[validade_2]" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)" [validade_fixa]>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display:[inicio_visivel]">
        <td><strong>&nbsp;Início:</strong></td>
        <td><input name="data_inicio" id="data_inicio" type="text" class="inputBrancoAzulEscuro11" style="width:120px" size="50" maxlength="10" value="[data_inicio]" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display:[termino_visivel]">
        <td><strong>&nbsp;Término:</strong></td>
        <td><input name="data_termino" id="data_termino" type="text" class="inputBrancoAzulEscuro11" style="width:120px" size="50" maxlength="10" value="[data_termino]" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)" [data_termino_fixa]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:400" rows="3" cols="52"></textarea></td>
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

  if (window.opener && window.opener.document.adicionar) {
    document.getElementById("cliente").innerHTML = window.opener.document.getElementById("nome").value;
  }

  if (window.opener && window.opener.buscaAssinaturasCliente) {
    window.opener.buscaAssinaturasCliente([cliente_id]);
  }

  if (window.opener && window.opener.buscaAssinaturaDescontos) {
    window.opener.buscaAssinaturaDescontos([assinatura_id]);
  }

</script>
