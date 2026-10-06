<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
  <tr align="center" class="txtBranco11">
    <td height="20">
      <table border="0" cellspacing="1" cellpadding="3" width="100%">
        <tr class="txtBranco11">
          <td bgcolor="#505050">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr class="txtBranco11">
                <td width="30%"></td>
                <td width="30%" align="center"><b>Dados do Cliente</b> <span id="contato_registro"></span></td>
                <td width="30%" align="right"><b>&nbsp;Duração</b>: <span id="contato_duracao">00:00:00</span>&nbsp;</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr valign="top">
    <td>
      <div style="width:100%;height:148px;overflow-y:scroll">
        <table border="0" cellspacing="1" cellpadding="3" width="100%" height="100%">
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td width="80"><b>&nbsp;Nome</b>:</td>
            <td id="contato_nome">&nbsp;</td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><b>&nbsp;Endereço</b>:</td>
            <td id="contato_endereco">&nbsp;</td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><b>&nbsp;Telefone(s)</b>:</td>
            <td id="contato_telefone" colspan="2">&nbsp;</td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td style="height:100%" valign="top"><b>&nbsp;Cobrança(s)</b>:</td>
            <td id="contato_cobrancas_em_aberto" valign="top">&nbsp;</td>
          </tr>
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td><b>&nbsp;Status</b>:</td>
            <td id="contato_status">&nbsp;</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
  <tr class="txtBranco11">
    <td valign="bottom" align="center">
      <input type="button" class="buttonAzulEscuroBranco11" id="prev_cliente" name="prev_cliente" value="Anterior" onClick="buscaClienteAnterior();">
      [botoes]
      <input type="button" class="buttonAzulEscuroBranco11" id="prox_cliente" name="prox_cliente" value="Próximo" onClick="buscaClientePosterior();">
      <span id="contato_links"></span>
    </td>
  </tr>
</table>
<input type="hidden" id="contato_inicio" name="contato_inicio">
<input type="hidden" id="contato_status" name="contato_status">
