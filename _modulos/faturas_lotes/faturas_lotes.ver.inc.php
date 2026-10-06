<form method="POST" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td>[acoes]</td>
  </tr>
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><strong>&nbsp;Ciclo:</strong></td>
          <td>[ciclo]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><strong>&nbsp;Tipo:</strong></td>
          <td>[tipo_nome]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="28"><strong>&nbsp;Dados</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><strong>&nbsp;&raquo; Clientes:</strong></td>
          <td>[clientes]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><strong>&nbsp;&raquo; Faturas:</strong></td>
          <td>[faturas]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><strong>&nbsp;&raquo; Total:</strong></td>
          <td>[total]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><strong>&nbsp;&raquo; ICMS:</strong></td>
          <td>[total_icms]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="28"><strong>&nbsp;Datas</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><strong>&nbsp;&raquo; Geração:</strong></td>
          <td>[data_geracao]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><strong>&nbsp;&raquo; Processamento:</strong></td>
          <td>[data_processamento]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><strong>&nbsp;&raquo; Aprovação:</strong></td>
          <td>[data_aprovacao]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="28"><strong>&nbsp;Filtros</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" id="filtros_listagem">[filtros]</td>
        </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td height="30" colspan="2"><b>&nbsp;Comentários:</b></td>
	      </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:100%" rows="3" cols="52"></textarea></td>
	      </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><strong>&nbsp;Status:</strong></td>
          <td>[status_nome]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="40" colspan="2" align="center">[botoes]</td>
        </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td colspan="2">[lista_comentarios]</td>
	      </tr>
      </table>
    </td>
</tr>
</table>
</form>
