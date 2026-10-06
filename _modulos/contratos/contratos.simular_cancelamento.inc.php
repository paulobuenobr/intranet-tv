<form name="cancelar" method="POST" action="[action]" >
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Simular Cancelamento&nbsp;</b></td></tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; C&ocirc;digo:</strong></td>
    <td>[codigo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Servi&ccedil;o:</strong></td>
    <td>[servico]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><strong>&nbsp;Cliente:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><strong>&nbsp;&raquo; Nome:</strong></td>
    <td>[cliente_nome]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Telefone(s):</strong></td>
    <td>[telefones]</td>
  </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="color:#FF0000">
    <td colspan="2" align="center" colspan="2">
      <b>Aten&ccedil;&atilde;o!</b><br>
      Este &eacute; um contrato com plano de fidelidade.<br>
      Vig&ecirc;ncia: [vigencia].
    </td>
  </tr>
 <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Simula&ccedil;&atilde;o:</strong></td>
      </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="25%"><strong>&nbsp;&raquo; Data de Cancelamento:</strong></td>
    <td width="75%"><input name="data_cancelamento" id="data_cancelamento" type="text" class="inputBrancoAzulEscuro11" style="width:150px" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_cancelamento]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="25%"><strong>&nbsp;&raquo; Multa:</strong></td>
    <td width="75%">[multa]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center">[botoes]</td>
  </tr>
</table>
</form>
