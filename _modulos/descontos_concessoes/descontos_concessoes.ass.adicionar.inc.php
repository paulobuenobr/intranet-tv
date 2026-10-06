<form name="adicionar" method="POST" onSubmit="return validaDescontoConcessao(this)" action="[action]">
<script type="text/javascript" src="rds.js.php?m=assinaturas"></script>
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
        <td>
          [assinatura_nome]<br>
          <span id="instalacao">&nbsp;&raquo; Instalação: [assinatura_instalacao]</span><br>
          <span id="mensalidade">&nbsp;&raquo; Mensalidade: [assinatura_mensalidade]</span>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="100"><strong>&nbsp;Desconto:</strong></td>
        <td><select name="desconto" id="desconto" class="inputBrancoAzulEscuro11" style="width:100%" onChange="mudaDescontoConcessaoDesconto(this,[assinatura_id])" disabled><option></option></select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Instalação:</strong></td>
        <td><select name="instalacao_tipo" id="instalacao_tipo" class="inputBrancoAzulEscuro11" style="width:127px" onChange="mudaDescontoConcessaoValorTipo(this)" disabled>[instalacao_tipo]</select> <input name="instalacao_valor" id="instalacao_valor" type="text" class="inputBrancoAzulEscuro11" style="width:170px" size="30" onKeyPress="return formataDesconto(this,event,this.form.instalacao_tipo.value);" onBlur="return formataDesconto(this,event,this.form.instalacao_tipo.value);" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Mensalidade:</strong></td>
        <td><select name="mensalidade_tipo" id="mensalidade_tipo" class="inputBrancoAzulEscuro11" style="width:127px" onChange="mudaDescontoConcessaoValorTipo(this)" disabled>[mensalidade_tipo]</select> <input name="mensalidade_valor" id="mensalidade_valor" type="text" class="inputBrancoAzulEscuro11" style="width:170px" size="30" onKeyPress="return formataDesconto(this,event,this.form.mensalidade_tipo.value);" onBlur="return formataDesconto(this,event,this.form.mensalidade_tipo.value);" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Validade:</strong></td>
        <td>
          <select name="validade_1" id="validade_1" class="inputBrancoAzulEscuro11" style="width:127px" disabled>[validade]</select>
          <input type="text" name="validade_2" id="validade_2" class="inputBrancoAzulEscuro11" style="width:127px;display:none" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)" disabled>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td id="tipo"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
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

<script type="text/javascript">
  if (window.opener && window.opener.document.adicionar) {
    document.getElementById("cliente").innerHTML = window.opener.document.getElementById("nome").value;
  }
  buscaDescontosServico([descontos_parametros], [assinatura_id], [plano_id]);
</script>
