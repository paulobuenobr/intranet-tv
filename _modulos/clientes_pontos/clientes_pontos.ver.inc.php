<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Ver&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><strong>&nbsp;Cliente:</strong></td>
        <td width="75%">[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Assinatura:</strong></td>
        <td>[assinatura]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Endereço IP:</strong></td>
        <td>[endereco_ip]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;MAC:</strong></td>
        <td>[mac]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Dados Técnicos:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Interface:</strong></td>
        <td>[servidor]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Velocidade:</strong></td>
        <td>[velocidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Ajustes Programados:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">
        	<table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11">[ajustes_programados]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td>[status_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[lista_comentarios]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<script type="text/javascript">
	if (!window.opener.closed) {
		if (window.opener.buscaPontos) {
			window.opener.buscaPontos([id]);
		} else if (window.opener.buscaCondominiosPontos) {
			window.opener.buscaCondominiosPontos([id_condominio]);
		}
	}
</script>
