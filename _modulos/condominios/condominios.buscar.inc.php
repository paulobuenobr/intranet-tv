<script type="text/javascript" src="rds.js.php?m=condominios.buscar"></script>
<form name="buscar" method="POST" action="[action]" onSubmit="return validaCondominioBusca(this)">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="18%"><strong>&nbsp;Nome:</strong></td>
        <td width="82%"><input name="nome" id="nome" type="text" class="inputBrancoAzulEscuro11" maxlength="80" value="[nome]" style="width:300px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Cidade:</strong></td>
        <td>[cidade2]&nbsp;[cidade1]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Bairro:</strong></td>
        <td>[bairro2]&nbsp;[bairro1]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Logradouro:</strong></td>
        <td><input name="logradouro" id="logradouro" type="text" class="inputBrancoAzulEscuro11" maxlength="80" value="[logradouro]" style="width:300px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;CEP:</strong></td>
        <td><input name="cep" id="cep" style="width:150px" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="9" maxlength="9" onKeyPress="return formataCEP(this, event)" onBlur="return formataCEP(this, event)" value="[cep]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Número:</strong></td>
        <td><input name="numero" id="numero" type="text" class="inputBrancoAzulEscuro11" maxlength="80" value="[numero]" style="width:300px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Responsável:</strong></td>
        <td>[responsavel]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td>[status]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Situação:</strong></td>
        <td>[situacao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><strong>&nbsp;Período:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Data Cadastro:</strong></td>
        <td><input name="data_cadastro_inicio" id="data_cadastro_inicio" type="text" class="inputBrancoAzulEscuro11" maxlength="10" value="[data_cadastro_inicio]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" size="10"> até <input name="data_cadastro_termino" id="data_cadastro_termino" type="text" class="inputBrancoAzulEscuro11" maxlength="10" value="[data_cadastro_termino]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" size="10"> (dd/mm/aaaa)</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Data Ativação:</strong></td>
        <td><input name="data_ativacao_inicio" id="data_ativacao_inicio" type="text" class="inputBrancoAzulEscuro11" maxlength="10" value="[data_ativacao_inicio]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" size="10"> até <input name="data_ativacao_termino" id="data_ativacao_termino" type="text" class="inputBrancoAzulEscuro11" maxlength="10" value="[data_ativacao_termino]" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" size="10"> (dd/mm/aaaa)</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" align="center" height="30">[resultado]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
