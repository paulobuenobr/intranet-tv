<script type="text/javascript" src="rds.js.php?m=cancelamentos"></script>
<form name="finalizar" method="POST" action="[action]" onsubmit="return validaCancelamentoFinalizar(this)">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Finalizar&nbsp;</b></td></tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="color:#FF0000">
    <td colspan="2" align="center" colspan="2">
      <b>Atenção!</b><br>
      Este é um contrato com plano de fidelidade.<br>
      Vigência: [vigencia].
    </td>
  </tr>
  <tbody style="display:[exibir_mensagem_comodato]">
	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="color:#FF0000">
	    <td colspan="2" align="center" colspan="2">
	      <b>Atenção!</b><br>
	      Este contrato tem o(s) seguinte(s) esquipamento(s) em comodato:<br>
	      [equipamentos_comodato].<br>
	      Se cancelado, será aberto um chamado para retirada do(s) equipamento(s).
	    </td>
	  </tr>
  </tbody>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2"><p>Você deseja realmente <b>cancelar</b> este contrato com data de [data_cancelamento], com consequente geração de <b>multa rescisória</b>?</p></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" align="center" colspan="2">[botoes]</td>
  </tr>
</table>
<input type="hidden" name="status" value="[status]">
<input type="hidden" name="data_cancelamento" value="[data_cancelamento]">
<input type="hidden" name="comentario" value="[comentario]">
<input type="hidden" name="confirmacao" value="confirmacao">
</form>
