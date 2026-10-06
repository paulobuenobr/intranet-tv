<form name="encaminhar" method="POST" onSubmit="return validaTarefaEncaminhar(this);" action="[action]">
<script type="text/javascript" src="rds.js.php?m=tarefas"></script>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center" colspan="2"><p>Você deseja realmente <b>convidar</b> um usuário para participar desta tarefa?</p></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="15%"><strong>&nbsp;Origem:</strong></td>
    <td>[origem_nome]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp;Destino:</strong></td>
    <td><select name="destino" id="destino" class="inputBrancoAzulEscuro11" style="width: 100%"></select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><strong>&nbsp;Comentário:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="7" cols="75" style="width: 100%"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" colspan="2" align="center">[botoes]</td>
  </tr>
</table>
</form>

<script type="text/javascript">buscarTarefaDestinos('[origem_id]');</script>
