<script type="text/javascript" src="rds.js.php?m=servicos_ajustes"></script>
<script type="text/javascript" src="rds.js.php?m=tarefas"></script>
<form name="adicionar" method="post" onSubmit="return validaServicosAjustesBloqueioTemporario(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Adicionar Bloqueio Temporário&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
   <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="15%"><strong>&nbsp;Cliente:</strong></td>
        <td width="85%">[cliente]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Período:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Início:</strong></td>
        
		<td><input name="inicio" id="inicio" type="text" class="inputBrancoAzulEscuro11" style="width:120px" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"></td>
              
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Término:</strong></td>
        
		<td><input name="termino" id="termino" type="text" class="inputBrancoAzulEscuro11" style="width:120px" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"></td>
        
        </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Serviços:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11">[servicos_ajustes]</td>
            </tr>
          </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:100%" rows="3" cols="52"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<input type="hidden" name="lista_regras" id="lista_regras">
</form>

<script type="text/javascript">
	if (!window.opener.closed) {
		if (window.opener.buscaAjustesCliente) {
			window.opener.buscaAjustesCliente('[cliente_id]');
		}
	}
</script>
