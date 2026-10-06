<script type="text/javascript" src="rds.js.php?m=clientes_iptv"></script>
<form name="editar" method="POST" onSubmit="return validaAlterarUsuario(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Editar&nbsp;</b></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
			<td width="20%"><b>&nbsp;Cliente:</b></td>
			<td width="80%" id="cliente">[cliente_nome]</td>
		</tr>
		<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
			<td><b>&nbsp;Contrato:</b></td>
			<td>[contrato_codigo]</td>
		</tr>
		<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
			<td><b>&nbsp;Usuário (E-mail):</b></td>
			<td><input name="usuario" id="usuario" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="255" style="width:100%" value="[usuario]"></td>
		</tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="3" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
