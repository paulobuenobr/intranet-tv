<script type="text/javascript" src="rds.js.php?m=operacoes_lotes"></script>
<form name="editar" method="POST" onSubmit="return validaOperacaoLote(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td>
      <table border="0" cellspacing="1" cellpadding="3" width="100%">
        <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Editar&nbsp;</strong></td></tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="10%"><strong>&nbsp;Nome:</strong></td>
          <td width="90%"><input name="nome" id="nome" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="80" value="[nome]"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Sistema:</strong></td>
          <td>[sistema]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="28" colspan="2"><strong>&nbsp;Regras:</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="28"><strong>&nbsp;&raquo; Endereço</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2" id="enderecos_listagem">[enderecos]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="28"><strong>&nbsp;&raquo; Filtro</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2" id="filtros_listagem">[filtros]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="28"><strong>&nbsp;&raquo; Últimos Contatos</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" id="ultimos_contatos">[ultimos_contatos]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="28"><strong>&nbsp;&raquo; Validade</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2" id="validades_listagem">[validades]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Status:</strong></td>
          <td>[status]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><strong>&nbsp;Operações:</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td id="operacoes" colspan="2">[operacoes]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="40" colspan="2" align="center">[botoes]</td>
        </tr>
      </table>
    </td>
</tr>
</table>
<input type="hidden" name="enderecos_lista" id="enderecos_lista" value='[enderecos_lista]'>
<input type="hidden" name="filtros_lista" id="filtros_lista" value='[filtros_lista]'>
<input type="hidden" name="validades_lista" id="validades_lista" value='[validades_lista]'>
<input type="hidden" name="filtros_lista_backup" id="filtros_lista_backup">
</form>
<script type="text/javascript">
  var lote_id = [lote_id];
</script>
