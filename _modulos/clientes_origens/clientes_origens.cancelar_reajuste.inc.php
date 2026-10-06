<script type="text/javascript" src="rds.js.php?m=clientes_origens"></script>
<form name="remover" method="POST" action="[action]">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2">
		<p>Você deseja realmente <b>cancelar o reajuste</b> dos clientes desta origem?</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2">
		<div style="display:inline">
			<div style="width: 150px; text-align: left"><label><input type="radio" name="contratos" value="vigentes" checked> Com menos de 1 ano ([clientes_vigentes])</label></div>
			<div style="width: 150px; text-align: left"><label><input type="radio" name="contratos" value="todos"> Todos ([clientes_todos])</label></div>
		<div>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" align="center" colspan="2">[botoes]</td>
  </tr>
</table>
</form>
