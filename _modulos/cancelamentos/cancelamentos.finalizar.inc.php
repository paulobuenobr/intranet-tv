<script type="text/javascript" src="rds.js.php?m=cancelamentos"></script>
<form name="finalizar" method="POST" action="[action]" onsubmit="return validaCancelamentoFinalizar(this)">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Finalizar&nbsp;</b></td></tr>
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
    <td align="center" colspan="2" ><p>Você deseja realmente <b>finalizar</b> esta solicitação de cancelamento?</p></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2">
      <input type="radio" name="status" id="status_3" value="3" onclick="mudaStatus()" checked><label for="status_3"> Cliente Retido</label>
      [perdido]<label for="status_4"> Cliente Perdido</label>
    </td>
  </tr>
  <tbody id="cancelamento_contrato" style="display:none">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="25%"><strong>&nbsp;Data &raquo; Cancelamento:</strong></td>
    <td width="75%">[data_cancelamento]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><strong>&nbsp;Comentário:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="7" cols="105"></textarea></td>
  </tr>
  </tbody>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" align="center" colspan="2">[botoes]</td>
  </tr>
</table>
</form>
