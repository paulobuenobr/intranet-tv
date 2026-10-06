<script type="text/javascript" src="rds.js.php?m=cancelamentos"></script>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Cliente:</strong></td>
        <td width="80%">[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Endereço:</b></td>
        <td>[endereco]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Contrato:</b></td>
        <td>[contrato]</td>
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
		  <tbody style="display:[exibir_mensagem_comodato]">
			  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="color:#FF0000">
			    <td colspan="2" align="center" colspan="2">
			      <b>Atenção!</b><br>
			      Este contrato tem o(s) seguinte(s) esquipamento(s) em comodato:<br>
			      [equipamentos_comodato].
			    </td>
			  </tr>
		  </tbody>
      <tbody style="display:[mensagem_inadimplente]">
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" style="color:#FF0000;text-align:center"><br>
            Atenção!<br>
            Este cliente possui [cobrancas] cobrança(s) em atraso e o cancelamento / alteração<br>
            de seus contratos só será possível após a quitação destes débitos.<br>
            <br>
          </td>
        </tr>
      </tbody>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Origem:</b></td>
        <td>[origem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Motivo:</b></td>
        <td>[motivo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Datas:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Abertura:</strong></td>
        <td>[inicio]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Fechamento:</strong></td>
        <td>[termino]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Etapas:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[etapas]</td>
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

<script type="text/javascript">

  if (window.opener != null && window.opener.document.getElementById("cancelamentos")) {
    window.opener.buscaCancelamentosCliente([cliente_id]);
  }

  if (window.opener && window.opener.buscaContratosCliente) {
    window.opener.buscaContratosCliente([cliente_id]);
  }

  if (window.opener && window.opener.buscaAjustesCliente) {
    window.opener.buscaAjustesCliente([cliente_id]);
  }

</script>
