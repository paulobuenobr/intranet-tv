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
        <td>[instalacao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Mensalidade:</strong></td>
        <td>[mensalidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display:[validade_visivel]">
        <td><strong>&nbsp;Validade:</strong></td>
        <td>[validade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display:[inicio_termino_visivel]">
        <td><strong>&nbsp;Início:</strong></td>
        <td>[data_inicio]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display:[inicio_termino_visivel]">
        <td><strong>&nbsp;Término:</strong></td>
        <td>[data_termino]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
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
