<script type="text/javascript" src="rds.js.php?m=servicos_ajustes"></script>
<script type="text/javascript" src="rds.js.php?m=tarefas"></script>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Ver&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
   <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
         <td width="50"><strong>&nbsp;Código:</strong></td>
        <td width="280">[codigo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
         <td width="50"><strong>&nbsp;Cliente:</strong></td>
        <td>[cliente]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
         <td width="80"><strong>&nbsp;Categoria:</strong></td>
        <td>[categoria]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Regras:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[listagem_regras]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Duração:</b></td>
        <td id="duracao">[duracao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Valor:</b></td>
        <td id="valor">[valor]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Período:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Início:</strong></td>
        <td>[inicio]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Término:</strong></td>
        <td>[termino]</td>
      </tr>

      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Assinaturas/Serviços:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11">[servicos_ajustes]</td>
            </tr>
          </table></td>
      </tr>

      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Suspensões Temporárias:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[lista_suspensoes]</td>
      </tr>

      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[lista_comentarios]</td>
      </tr>

      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
         <td width="50"><strong>&nbsp;Status:</strong></td>
        <td>[status]</td>
      </tr>

      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<script type="text/javascript">
	if (!window.opener.closed) {
		if (window.opener.buscaAjustesCliente) {
			window.opener.buscaAjustesCliente('[cliente_id]');
		}
	}
</script>
