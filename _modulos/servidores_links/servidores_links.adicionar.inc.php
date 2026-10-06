<script type="text/javascript" src="_modulos/servidores_links/servidores_links.js"></script>
<form name="adicionar" method="POST" onSubmit="return validaServidorLink(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Adicionar&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
   <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Nome:</strong></td>
        <td><input name="nome" id="nome" type="text" class="inputBrancoAzulEscuro11" style="width:280" size="50" maxlength="80" ></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Interface:</strong></td>
        <td>[servidor_interface]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Velocidade:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Download:</strong></td>
        <td><input name="download" id="download" type="text" class="inputBrancoAzulEscuro11_r" size="10" maxlength="15" onKeyPress="return formataNumeroReal(this, event)"> [unidade_download]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Upload:</strong></td>
        <td><input name="upload" id="upload" type="text" class="inputBrancoAzulEscuro11_r" size="10" maxlength="15" onKeyPress="return formataNumeroReal(this, event)"> [unidade_upload]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>

<script type="text/javascript">
  var link_id = '[id]';
  document.adicionar.nome.focus();
</script>
