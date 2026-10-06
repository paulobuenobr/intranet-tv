<script type="text/javascript" src="rds.js.php?m=servidores_interfaces"></script>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Ver&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
   <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Servidor:</strong></td>
        <td>[servidor_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
         <td><strong>&nbsp;Serviço:</strong></td>
        <td height="25">[servico]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" valign="top" height="232"></td>
            </tr>
          </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td>[status]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>

<script type="text/javascript">

  var interface_id = '[id]';
  var servidor_id = '[servidor_id]';

  if (!window.opener.closed && window.opener.buscaServidoresInterfaces) {
    window.opener.buscaServidoresInterfaces(servidor_id);
  }

</script>
