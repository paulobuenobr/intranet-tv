<script type="text/javascript" src="rds.js.php?m=contratos"></script>
<form name="buscar" method="POST" action="[action]" onSubmit="return validaContratoBusca(this)">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11">[acoes]</tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Código:</strong></td>
        <td><input name="codigo" id="codigo" type="text" class="inputBrancoAzulEscuro11" maxlength="80" value="[codigo]" style="width:300px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Cliente:</strong></td>
        <td><input name="cliente" id="cliente" type="text" class="inputBrancoAzulEscuro11" maxlength="80" value="[cliente]" style="width:450px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Condomínio:</strong></td>
        <td><select name="condominio" id="condominio" class="inputBrancoAzulEscuro11" style="width:450px"><option></option>[condominio]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><strong>&nbsp;Período:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Data Geração:</strong></td>
        <td><input name="data_geracao_inicio" id="data_geracao_inicio" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_geracao_inicio]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> até <input name="data_geracao_termino" id="data_geracao_termino" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_geracao_termino]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> (dd/mm/aaaa)</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Data Ativação:</strong></td>
        <td><input name="data_ativacao_inicio" id="data_ativacao_inicio" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_ativacao_inicio]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> até <input name="data_ativacao_termino" id="data_ativacao_termino" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_ativacao_termino]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> (dd/mm/aaaa)</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Data Cancelamento:</strong></td>
        <td><input name="data_cancelamento_inicio" id="data_cancelamento_inicio" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_cancelamento_inicio]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> até <input name="data_cancelamento_termino" id="data_cancelamento_termino" type="text" class="inputBrancoAzulEscuro11" size="10" maxlength="10" value="[data_cancelamento_termino]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"> (dd/mm/aaaa)</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Serviço:</strong></td>
        <td><select name="servico" id="servico" class="inputBrancoAzulEscuro11" style="width:300px"><option></option>[servico]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td><select name="tipo" id="tipo" class="inputBrancoAzulEscuro11" style="width:300px"><option></option>[tipo]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Responsável:</strong></td>
        <td><select name="responsavel" id="responsavel" style="width:300px" class="inputBrancoAzulEscuro11" [responsavel_disabled]><option></option>[responsavel]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td><select name="status" id="status" class="inputBrancoAzulEscuro11" style="width:300px"><option></option>[status]</select></td>
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
</form>
