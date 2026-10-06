<script type="text/javascript" src="rds.js.php?m=chamados"></script>
<script type="text/javascript" src="_modulos/chamados_sistema/chamados_sistema.js"></script>
<form name="acompanhar" method="POST" action="[action]">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Acompanhamento (Iniciar)&nbsp;</b></td></tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3" onkeydown="return pressionaTecla(event)">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2">
			<p>Você deseja realmente <b>[acao]</b> este chamado?</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Nova Etapa:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="margin:0px;padding:0px">
    <td colspan="2">[nova_etapa]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" align="center" colspan="2">[botoes]</td>
  </tr>
</table>
</form>

<script type="text/javascript">
  atualizaCaracteresRestantes();
</script>
