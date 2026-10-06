<form name="ativar" method="POST" action="[action]" onSubmit="return validaDominiosAtivar(this)">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Ativar&nbsp;</b></td></tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center" colspan="2"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center" colspan="2"><p>Você deseja realmente <b>ativar</b> este domínio?</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="25%"><strong>&nbsp;Data &raquo; Ativação:</strong></td>
    <td width="75%"><input name="data_ativacao" id="data_ativacao" type="text" class="inputBrancoAzulEscuro11" style="width:150px" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_ativacao]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><strong>&nbsp;Comentário:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="7" cols="105"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center" colspan="2">[botoes]</td>
  </tr>
</table>
</form>

<script type="text/javascript">
  function validaDominiosAtivar(formulario) {
    var erro_vazio = new Array();
    var erro_invalido = new Array();

    if (!formulario.data_ativacao.value) {
      erro_vazio[erro_vazio.length] = "Data » Ativação";
    } else if (!validaData(formulario.data_ativacao.value)) {
      erro_invalido[erro_invalido.length] = "Data » Ativação";
    }

    return apresentaErros(erro_vazio,erro_invalido);
  }
</script>
