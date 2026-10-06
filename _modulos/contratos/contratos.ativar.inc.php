<script type="text/javascript" src="rds.js.php?m=contratos"></script>
<form name="ativar" method="POST" action="[action]" onSubmit="return validaContratoAtivar(this)">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Ativar&nbsp;</b></td></tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center" colspan="2"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center" colspan="2"><p>Você deseja realmente <b>ativar</b> o contrato "<b>[contrato]</b>" do cliente<br>"<b>[cliente]</b>"?</p>
    </td>
  </tr>
  <tbody style="display:[exibir_mensagem_impressao]">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="color:#FF0000">
    <td colspan="2" align="center" colspan="2">
      <b>Caro Usuário</b><br>
      Este tipo de contrato requer a assinatura do cliente.<br>
      Certifique-se de que a Olé Telecom possui este contrato<br>
      impresso e assinado antes de prosseguir.
    </td>
  </tr>
  </tbody>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="25%"><strong>&nbsp;Data de Ativação:</strong></td>
    <td width="75%"><input name="data_ativacao" id="data_ativacao" type="text" class="inputBrancoAzulEscuro11" style="width:150px" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_ativacao]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><b>&nbsp;Equipamentos em Comodato:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2">[equipamentos]</td>
  </tr>
  
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><strong>&nbsp;Comentário:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="7" cols="105"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="left">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr class="txtAzulEscuro11">
          <td width="5%"><input type="checkbox" name="com_pendencias" id="com_pendencias" value="true"></td>
          <td width="95%"><label for="com_pendencias">Ativar com pendências</label></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center" colspan="2">[botoes]</td>
  </tr>
</table>
<input type="hidden" name="contrato_tipo" id="contrato_tipo" value="[contrato_tipo]">
</form>
