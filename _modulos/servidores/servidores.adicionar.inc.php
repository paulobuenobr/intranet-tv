<form name="adicionar" method="POST" onSubmit="return validaServidor(this)" action="[action]">
<script type="text/javascript" src="rds.js.php?m=servidores"></script>
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
        <td><input name="nome" id="nome" type="text" class="inputBrancoAzulEscuro11" style="width:291" size="50" maxlength="80" ></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Interface:</strong></td>
        <td>[servidor_interface]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Endereço IP:</strong></td>
        <td><input name="prefixo_ip_1" id="prefixo_ip_1" type="text" class="inputBrancoAzulEscuro11_c" style="width:43" size="3" maxlength="3" disabled> .
        <input name="prefixo_ip_2" id="prefixo_ip_2" type="text" class="inputBrancoAzulEscuro11_c" style="width:43" size="3" maxlength="3" disabled> .
        <input name="prefixo_ip_3" id="prefixo_ip_3" type="text" class="inputBrancoAzulEscuro11_c" style="width:43" size="3" maxlength="3" disabled> .
        <input name="endereco_ip" id="endereco_ip" type="text" class="inputBrancoAzulEscuro11_c" style="width:43" size="3" maxlength="3" onKeyPress="return formataNumeroIP(this, event)" onChange="verificaEnderecoIPExistenteServidor(this.form.endereco_ip, 0, this.form.servidor_interface)" onBlur="verificaEnderecoIPExistenteServidor(this.form.endereco_ip, 0, this.form.servidor_interface)" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Configurações:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Sistema Operacional/Versão:</strong></td>
        <td>[sistema_operacional]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Processador (Modelo/Clock):</strong></td>
        <td>[processador_modelo]&nbsp;
          <input name="processador_clock" id="processador_clock" type="text" class="inputBrancoAzulEscuro11_r" style="width:63px" size="3" maxlength="15" onKeyPress="return formataNumeroReal(this, event)">
          [processador_unidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; HD (Tipo/Tamanho):</strong></td>
        <td>[hd_tipo]&nbsp;
          <input name="hd_tamanho" id="hd_tamanho" type="text" class="inputBrancoAzulEscuro11_r" style="width:63px" size="3" maxlength="15" onKeyPress="return formataNumeroReal(this, event)">
          [hd_unidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Memória (Tipo/Tamanho):</strong></td>
        <td>[memoria_tipo]&nbsp;
          <input name="memoria_tamanho" id="memoria_tamanho" type="text" class="inputBrancoAzulEscuro11_r" style="width:63px" size="3" maxlength="15" onKeyPress="return formataNumeroReal(this, event)">
          [memoria_unidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Comentários:</strong></td>
        <td><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:291" rows="4" cols="52"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>

<script type="text/javascript">
  document.adicionar.nome.focus();
</script>
