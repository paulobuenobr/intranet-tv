<script type="text/javascript" src="rds.js.php?m=mensagens"></script>
<form name="buscar" method="POST" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Buscar&nbsp;</b></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Lote:</strong></td>
        <td width="80%"><select name="lote" id="lote" class="inputBrancoAzulEscuro11" style="width:100%"><option></option>[lotes]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td><select name="tipo" id="tipo" class="inputBrancoAzulEscuro11" style="width:100%"><option></option>[tipos]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Modelo:</strong></td>
        <td><select name="modelo" id="modelo" class="inputBrancoAzulEscuro11" style="width:100%"><option></option>[modelos]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Nome do Cliente:</strong></td>
        <td><input name="cliente_nome" id="cliente_nome" type="text" value="[cliente_nome]" class="inputBrancoAzulEscuro11" style="width:100%"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Condomínio:</strong></td>
        <td><input name="condominio1" id="condominio1" type="text"  value="[condominio1]" class="inputBrancoAzulEscuro11" style="width:49%" [condominio_disabled]> <select name="condominio2" id="condominio2" class="inputBrancoAzulEscuro11"  style="width:50%" onChange="atualizaCondominiosCampo(this.value)"><option></option>[condominio2]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Enviado Data:</strong></td>
        <td><input name="data1" id="data1" type="text" value="[data1]" class="inputBrancoAzulEscuro11" size="10" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> até <input name="data2" id="data2" type="text" value="[data2]" class="inputBrancoAzulEscuro11" size="10" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> (dd/mm/aaaa)</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td><select name="status" id="status" class="inputBrancoAzulEscuro11" style="width:100%"><option></option>[status]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Resultado:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" align="center">[resultado]</td>
      </tr>
    </table></td>
</tr>
</table>
<input name="m" id="m" type="hidden" value="[modulo]">
<input name="a" id="a" type="hidden" value="buscar">
<input name="g" id="g" type="hidden" value="1">
</form>
