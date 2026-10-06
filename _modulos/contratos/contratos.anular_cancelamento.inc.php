<form name="cancelar" method="POST" action="[action]">
<script type="text/javascript" src="rds.js.php?m=contratos" id="contratos.js"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11">[acoes]</tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
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
    <td colspan="2" align="center">
      <p>Você deseja realmente <b>anular</b> o cancelamento este contrato?</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><strong>&nbsp;Comentário:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="7" cols="105"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center">[botoes]</td>
  </tr>
</table>
</form>
