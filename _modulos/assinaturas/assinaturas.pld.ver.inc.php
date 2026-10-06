<script type="text/javascript" src="rds.js.php?m=assinaturas"></script>
<script type="text/javascript" src="rds.js.php?m=descontos_concessoes"></script>
<script type="text/javascript" src="rds.js.php?m=dominios"></script>
<script type="text/javascript" src="rds.js.php?m=vendas_comissoes"></script>
<script type="text/javascript" src="rds.js.php?m=vendas"></script>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Cliente:</strong></td>
        <td width="80%" id="cliente_nome">[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Assinatura:</strong></td>
        <td>[assinatura]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Adicional:</strong></td>
        <td>[adicional]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Sistema:</strong></td>
        <td>[sistema]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Valores:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Adesão:</strong></td>
        <td id="instalacao">[instalacao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Mensalidade:</strong></td>
        <td id="mensalidade">[mensalidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Desconto(s):</b> [descontos_adicionar]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td id="descontos" colspan="2">[descontos]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Datas:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Cadastro:</strong></td>
        <td>[data_cadastro]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Fechamento:</strong></td>
        <td>[data_fechamento]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Ativação:</strong></td>
        <td>[data_ativacao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Cancelamento:</strong></td>
        <td>[data_cancelamento]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><strong>&nbsp;Comissões:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="comissoes_listagem">[comissoes]</td>
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

  pfpj = opener.pfpj;
  ccsc = opener.ccsc;
  cliente = '[cliente_id]';

  if (window.opener && window.opener.document.adicionar && window.opener.document.getElementById("nome")) {
    document.getElementById("cliente_nome").innerHTML = window.opener.document.getElementById("nome").value;
  }

  if (window.opener && window.opener.document.adicionar && window.opener.document.getElementById("condominio")) {
    condominio = window.opener.document.getElementById("condominio").value;
  } else {
    condominio = '[condominio]';
  }

  if (window.opener && window.opener.alertaDominioAdicionado) {
    window.opener.alertaDominioAdicionado('[dominio_id]')
  }

  if (window.opener && window.opener.buscaAssinaturasCliente) {
    window.opener.buscaAssinaturasCliente(cliente);
  }

  if (window.opener && window.opener.atualizaAssinaturas) {
    window.opener.atualizaAssinaturas();
  }

</script>
