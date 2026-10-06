<script type="text/javascript" src="rds.js.php?m=contratos" id="contratos.js"></script>
<form name="editar" method="POST" onSubmit="return validaContratoPrincipalEditar(this);" action="[action]">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11">[acoes]</tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Contrato:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Código:</strong></td>
        <td>[codigo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Tipo:</strong></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Serviço:</strong></td>
        <td>[servico]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Cliente:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;&raquo; Nome:</strong></td>
        <td>[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Telefone(s):</strong></td>
        <td>[telefones]</td>
      </tr>
      <tbody style="display:[exibir_mensagem_vigencia]">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="color:#FF0000">
        <td colspan="2" align="center" colspan="2">
          <b>Atenção!</b><br>
          Este é um contrato com plano de fidelidade.<br>
          Vigência: [vigencia].
        </td>
      </tr>
      </tbody>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Assinaturas:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="assinaturas">[assinaturas]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Descontos:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="descontos">[descontos]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><strong>&nbsp;Valores:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp; » Instalação:</strong></td>
        <td id="instalacao_valor">[instalacao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp; » Mensalidade:</strong></td>
        <td id="mensalidade_valor">[mensalidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Aditivos:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[aditivos]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Datas:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Geração:</strong></td>
        <td><input name="data_geracao" id="data_geracao" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" style="width:150px" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_geracao]" [data_geracao_disabled]></td>
      </tr>
     <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Ativação:</strong></td>
        <td><input name="data_ativacao" id="data_ativacao" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" style="width:150px" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_ativacao]" [data_ativacao_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Anulação:</strong></td>
        <td><input name="data_anulacao" id="data_anulacao" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" style="width:150px" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_anulacao]" [data_anulacao_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Cancelamento:</strong></td>
        <td><input name="data_cancelamento" id="data_cancelamento" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="10" style="width:150px" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_cancelamento]" [data_cancelamento_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Vigência:</strong></td>
        <td><select name="vigencia" id="vigencia" style="width:150px" class="inputBrancoAzulEscuro11" onchange="mudaFidelizacao(this)" [vigencia_disabled]>[vigencias]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Valor Multa:</strong></td>
        <td><input name="valor_multa" id="valor_multa" type="text" style="width:150px" value="[valor_multa]" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" [valor_multa_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Instalador:</strong></td>
        <td>[instalador]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Situação:</strong></td>
        <td>[situacao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="3" cols="75" style="width: 100%"></textarea></td>
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

  var cliente_id = [cliente_id];
  var servico_id = [servico_id];
  var contrato_id = [contrato_id];

  if (window.opener  && window.opener.buscaContratosCliente) {
    window.opener.buscaContratosCliente(cliente_id);
  }

  if (window.opener  && window.opener.buscaAjustesCliente) {
    window.opener.buscaAjustesCliente(cliente_id);
  }

  if (window.opener  && window.opener.buscaCancelamentosCliente) {
    window.opener.buscaCancelamentosCliente(cliente_id);
  }

</script>
