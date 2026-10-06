<script type="text/javascript" src="rds.js.php?m=clientes_comodatos"></script>
<form name="remover" method="POST" action="[action]" >
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Remover &nbsp;</b></td></tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center">
      <p>Você deseja realmente <b>Remover</b> este equipamento?</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center">[botoes]</td>
  </tr>
</table>
</form>
<script type="text/javascript">
if (!window.opener.closed) {
	if (window.opener.atualizaEquipamentosComodatos) {
		window.opener.atualizaEquipamentosComodatos('[cliente_id]');
	}
}
</script>
