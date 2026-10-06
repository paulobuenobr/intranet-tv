<script type="text/javascript" src="rds.js.php?m=tarefas"></script>
<script type="text/javascript" src="rds.js.php?m=clientes_responsabilidades"></script>
<form name="adicionar" method="POST" onSubmit="return validaClienteResponsabilidade(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Cliente:</strong></td>
        <td width="80%">[cliente]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Categoria:</strong></td>
        <td>[categoria]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Responsável:</strong></td>
        <td>[responsavel]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Comentário:</strong></td>
        <td><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="7" cols="51"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Início:</strong></td>
        <td><input name="inicio" id="inicio" type="text" class="inputBrancoAzulEscuro11" style="width:50%;cursor:pointer" size="8" onClick="carregaTarefaCalendarioNavegacao(this.name, 1, this.value)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
      </table></td>
  </tr>
</table>
</form>
