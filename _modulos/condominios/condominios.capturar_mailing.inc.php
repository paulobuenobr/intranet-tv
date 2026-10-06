<form name="capturar_mailing" method="POST" action="[action]" onsubmit="this.confirmar.disabled = true; this.confirmar.value = 'Aguarde...'">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center">
			<p>Você realmente deseja capturar mailings para o condomínio "<b>[nome]</b>"?</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" align="center">[botoes]</td>
  </tr>
</table>
<input type="hidden" name="capturar" value="capturar">
</form>
