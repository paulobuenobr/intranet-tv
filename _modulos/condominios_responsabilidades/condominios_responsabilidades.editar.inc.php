<script type="text/javascript" src="rds.js.php?m=tarefas"></script>
<form name="editar" method="POST" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td>[acoes]</td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Condomínio:</strong></td>
        <td>[condominio_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Responsável:</strong></td>
        <td>[responsavel]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Categoria:</strong></td>
        <td>[categoria]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Atividades:</strong></td>
        <td>[atividades]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Período:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Início:</strong></td>
        <td><input name="inicio" id="inicio" type="text" class="inputBrancoAzulEscuro11" style="width:120px" size="8" onClick="carregaTarefaCalendarioNavegacao(this.name, 1, this.value)" value="[inicio]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Término:</strong></td>
        <td>[termino]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="5" cols="75" style="width: 100%"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[comentarios]</td>
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
</form>

<script type="text/javascript">
  if (window.opener != null && window.opener.document.getElementById("responsabilidades")) {
    window.opener.buscarCondominioResponsabilidades([condominio_id]);
  }
</script>
