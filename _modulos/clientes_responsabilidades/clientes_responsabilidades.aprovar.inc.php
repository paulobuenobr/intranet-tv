<form name="aprovar" method="POST" action="[action]" onSubmit="return validaClienteResponsabilidadeAprovar(this);">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center">
			<p>Você deseja realmente <b>aprovar</b> esta responsabilidade?</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30"><strong>&nbsp;Comentário:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="7" cols="75" style="width: 100%"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" align="center">[botoes]</td>
  </tr>
</table>
<input type="hidden" id="conflito" value="[conflito]">
<input type="hidden" id="permissao" value="[permissao]">
</form>
