<script type="text/javascript" src="rds.js.php?m=contratos"></script>
<form name="publicar" method="POST" action="[action]" onSubmit="return validaContratoPublicar(this)" enctype="multipart/form-data">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Publicar&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="100"><strong>&nbsp;ID Cliente:</strong></td>
        <td><input name="cliente_id" id="cliente_id" type="text" class="inputBrancoAzulEscuro11" maxlength="20" style="width:150px" onblur="return formataNumero(this, event)" onkeypress="return formataNumero(this, event)">
          <input type="button" name="buscar_contratos" class="buttonAzulEscuroBranco11" value="Buscar" onclick="buscarContratos()">
          <input type="button" name="limpar_contratos" class="buttonAzulEscuroBranco11" value="Limpar" onclick="limparContratos()">
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Código:</strong></td>
        <td id="campo_codigo"><input name="codigo" id="codigo" type="text" class="inputBrancoAzulEscuro11" maxlength="20" style="width:300px" onblur="return formataContrato(this, event)" onkeypress="return formataContrato(this, event)" /></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Cliente:</strong></td>
        <td id="cliente"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Arquivo:</strong></td>
        <td><input name="arquivo" id="arquivo" type="file" size="80" /></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="40" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
<script type="text/javascript">
  var mensagem = '[mensagem]';
  if (mensagem) {
    alert(mensagem);
    window.location = "[action]";
  }
</script>
