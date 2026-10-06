<script type="text/javascript" src="rds.js.php?m=assinaturas"></script>
<script type="text/javascript" src="rds.js.php?m=descontos_concessoes"></script>
<form name="editar" method="POST" onSubmit="return validaAssinatura(this);" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Cliente:</strong></td>
        <td width="80%" id="cliente_nome">[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Assinatura:</strong></td>
        <td>[assinatura_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Adicional:</strong></td>
        <td>[planos]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Sistema:</strong></td>
        <td>[sistemas]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Valores:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Adesão:</strong></td>
        <td><input name="instalacao" id="instalacao" value="[instalacao]" type="text" class="inputBrancoAzulEscuro11" size="50" style="width:200px" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Mensalidade:</strong></td>
        <td><input name="mensalidade" id="mensalidade" value="[mensalidade]" type="text" class="inputBrancoAzulEscuro11" size="50" style="width:200px" disabled></td>
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

  var pfpj = [pfpj];
  var ccsc = [ccsc];
  var cliente = '[cliente_id]';
  var origem = '[origem_id]';

  if (window.opener.document.adicionar) {
    document.getElementById("cliente_nome").innerHTML = window.opener.document.getElementById("nome").value;
  }

  if (window.opener && window.opener.document.adicionar && window.opener.document.getElementById("condominio")) {
    condominio = window.opener.document.getElementById("condominio").value;
  } else {
    condominio = '[condominio]';
  }

  if (window.opener && window.opener.atualizaAssinaturas) {
    window.opener.atualizaAssinaturas();
  }

</script>
