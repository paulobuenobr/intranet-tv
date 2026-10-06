<form name="adicionar" method="POST" onSubmit="return validaDescontoConcessao(this)" action="[action]">
<script type="text/javascript" src="rds.js.php?m=visitas"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.visitas"></script>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
 	  	<td><strong>&nbsp;&raquo; Data/Periodo</strong></td>
        <td>[data_visita]</td>
 	  </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><strong>&nbsp;Numero de Visitas</strong></td>
      </tr>
 	  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
 	  	<td><strong>&nbsp;&raquo; Residenciais</strong></td>
        <td id="n_visitas_residencial">[n_visitas_residencial]</td>
 	  </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
 	  	<td><strong>&nbsp;&raquo; Condominiais</strong></td>
        <td id="n_visitas_condominial">[n_visitas_condominiais]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
