<form name="remover_baixa" method="POST" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Regerar&nbsp;</strong></td></tr>
    </table></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center">
			<p>Você deseja realmente <b>regerar</b> esta cobrança?</p>
    </td>
  </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="27%"><strong>&nbsp;Data Vencimento:</strong></td>
    <td><input name="data_vencimento" id="data_vencimento" value="[data_vencimento]" style="width:150px" type="text" class="inputBrancoAzulEscuro11" size="30" maxlength="10" onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)"></td>
  </tr>
  <tr id="permissao_especial" bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30">
     <input type="checkbox" name="nao_gerar_juros" id="nao_gerar_juros" value="true"><label for="nao_gerar_juros"> Não gerar juros</label>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><strong>&nbsp;Comentário:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="7" cols="85" style="width:100%"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center">[botoes]</td>
  </tr>
</table>
</form>
