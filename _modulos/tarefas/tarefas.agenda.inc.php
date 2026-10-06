<script type="text/javascript" src="rds.js.php?m=tarefas"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td height="5"></td>
  </tr>
  <tr align="center" class="txtBranco11">
    <td bgcolor="#828282"><b>Filtro</b></td>
  </tr>
</table>

<table border="0" cellspacing="1" cellpadding="5" width="100%">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="75"><b>Referência:</b></td>
    <td colspan="3"><select name="listagem_referencia" id="listagem_referencia" class="inputBrancoAzulEscuro11" style="width: 100%" onChange="buscarTarefaListagem(tarefa_listagem_dia, tarefa_listagem_mes, tarefa_listagem_ano, this.value, tarefa_listagem_formato, tarefa_listagem_status)">[listagem_referencia]</select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="75"><b>Formato:</b></td>
    <td width="40%"><select name="listagem_formato" id="listagem_formato" class="inputBrancoAzulEscuro11" style="width: 100%" onChange="buscarTarefaListagem(tarefa_listagem_dia, tarefa_listagem_mes, tarefa_listagem_ano, tarefa_listagem_referencia, this.value, tarefa_listagem_status)">[listagem_formato]</select></td>
    <td width="75"><b>Status:</b></td>
    <td width="40%"><select name="listagem_status" id="listagem_status" class="inputBrancoAzulEscuro11" style="width: 100%" onChange="buscarTarefaListagem(tarefa_listagem_dia, tarefa_listagem_mes, tarefa_listagem_ano, tarefa_listagem_referencia, tarefa_listagem_formato, this.value)">[listagem_status]</select></td>
  </tr>
</table>

<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td height="5"></td>
  </tr>
  <tr align="center" class="txtBranco11">
    <td bgcolor="#828282"><b>Agenda</b></td>
  </tr>
</table>

<span class="txtAzulEscuro11" id="tarefas_agenda">[tarefas_agenda]</span>

<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td height="5"></td>
  </tr>
  <tr align="center" class="txtBranco11">
    <td bgcolor="#828282"><b>Aguardando Ciência</b></td>
  </tr>
</table>

<span class="txtAzulEscuro11" id="tarefas_aguardando_ciencia">[tarefas_aguardando_ciencia]</span>

<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td height="5"></td>
  </tr>
  <tr align="center" class="txtBranco11">
    <td bgcolor="#828282"><b>Sem Prazo</b></td>
  </tr>
</table>

<span class="txtAzulEscuro11" id="tarefas_sem_prazo">[tarefas_sem_prazo]</span>

<script type="text/javascript">
  var tarefa_listagem_dia = '[dia]';
  var tarefa_listagem_mes = '[mes]';
  var tarefa_listagem_ano = '[ano]';
  var tarefa_listagem_referencia = '[referencia]';
  var tarefa_listagem_formato = '[formato]';
  var tarefa_listagem_status = '[status]';
  var tarefa_exibicao = '[exibicao]';
</script>
