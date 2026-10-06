<form name="ativar" method="POST" action="[action]" onSubmit="return validaContratoAtivar(this)">
<script type="text/javascript" src="rds.js.php?m=contratos"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11">[acoes]</tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><strong>&nbsp;Dados do Contrato:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Código:</strong></td>
    <td>[contrato]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Tipo:</strong></td>
    <td>[tipo]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Serviço:</strong></td>
    <td>[servico]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><strong>&nbsp;&raquo; Cliente:</strong></td>
    <td>[cliente]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><strong>&nbsp;Ativação:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><b>&nbsp;&raquo; Data:</b></td>
    <td width="80%"><input name="data_ativacao" id="data_ativacao" type="text" class="inputBrancoAzulEscuro11" style="width:150px" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_ativacao]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;&raquo; Instalador:</b></td>
    <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr class="txtAzulEscuro11">
          <td width="80%"><select id="instalador" name="instalador" class="inputBrancoAzulEscuro11" style="width:100%"><option></option>[instalador]</select></td>
          <td width="5%"><input type="checkbox" name="sem_instalador" id="sem_instalador" onclick="if(this.checked){document.getElementById('instalador').value = ''; document.getElementById('instalador').disabled = true;} else {document.getElementById('instalador').disabled = false;}" [sem_visita_disabled]></td>
          <td width="15%"><label for="sem_instalador">Sem Visita</label></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><b>&nbsp;Nota Fiscal:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Número:</strong></td>
    <td>[nf_numero]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Emissão:</strong></td>
    <td>[nf_emissao]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;&raquo; Valor:</strong></td>
    <td>[nf_valor]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><b>&nbsp;Comentário:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="5" cols="105"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center" colspan="2">[botoes]</td>
  </tr>
</table>
</form>
