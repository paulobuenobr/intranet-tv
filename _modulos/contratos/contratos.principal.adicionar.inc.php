<script type="text/javascript" src="rds.js.php?m=contratos" id="contratos.js"></script>
<form name="adicionar" method="POST" onSubmit="return validaContratoPrincipalAdicionar(this)" action="[action]">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11">[acoes]</tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><strong>&nbsp;Nome:</strong></td>
          <td>[nome]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Serviço:</strong></td>
          <td><select id="servico" name="servico" class="inputBrancoAzulEscuro11" onchange="mudaContratoServico(this.value, [cliente_id])" style="width:50%">[servicos]</select></td>
        </tr>
        <tbody id="informacoes_assinaturas" style="display:[exibe_assinaturas]">
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td colspan="2" height="30"><strong>&nbsp;Assinaturas:</strong></td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td colspan="2" id="assinaturas">[assinaturas]</td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td colspan="2" height="30"><strong>&nbsp;Descontos:</strong></td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td colspan="2" id="descontos">[descontos]</td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td height="30" colspan="2"><strong>&nbsp;Valores:</strong></td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><strong>&nbsp; » Instalação:</strong></td>
            <td id="instalacao_valor">[instalacao_valor]</td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><strong>&nbsp; » Mensalidade:</strong></td>
            <td id="mensalidade_valor">[mensalidade_valor]</td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><strong>&nbsp; » Parcelamento:</strong></td>
            <td id="parcelamento">[parcelamento]</td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><strong>&nbsp; » Multa:</strong></td>
            <td id="multa">[multa]</td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><strong>&nbsp; » Vigência:</strong></td>
            <td id="vigencia">[vigencia]</td>
          </tr>
        </tbody>
      	<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="40" colspan="2" align="center" >[botoes]</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>

<script type="text/javascript">
  var cliente_id = [cliente_id];
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
