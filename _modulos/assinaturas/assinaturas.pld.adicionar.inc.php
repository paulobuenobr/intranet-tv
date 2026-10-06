<script type="text/javascript" src="rds.js.php?m=assinaturas"></script>
<script type="text/javascript" src="rds.js.php?m=descontos_concessoes"></script>
<form name="adicionar" method="POST" onSubmit="return validaAssinatura(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><b>&nbsp;Cliente:</b></td>
        <td width="75%" id="cliente_nome">[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Assinatura:</b></td>
        <td>[assinatura_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Adicional:</b></td>
        <td>[planos_adicionais]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Sistema:</b></td>
        <td>[sistema]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Valores:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Adesão:</b></td>
        <td><input name="instalacao" id="instalacao" type="text" class="inputBrancoAzulEscuro11" style="width:200px" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Mensalidade:</b></td>
        <td><input name="mensalidade" id="mensalidade" type="text" class="inputBrancoAzulEscuro11" style="width:200px" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Desconto(s):</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td id="descontos" colspan="2">[descontos]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>

<script type="text/javascript">

  var pfpj = opener.pfpj;
  var ccsc = opener.ccsc;
  var cliente = '[cliente_id]';
  var origem = '[origem_id]';
  var condominio = (window.opener && window.opener.document.adicionar && window.opener.document.adicionar.condominio) ? window.opener.document.adicionar.condominio.value : '[condominio]';

  if (window.opener && window.opener.document.adicionar && window.opener.document.getElementById("nome")) {
    document.getElementById("cliente_nome").innerHTML = window.opener.document.getElementById("nome").value;
  }

  //buscaAssinaturaAdicionais([assinatura_id]);

</script>
