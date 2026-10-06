<script type="text/javascript" src="rds.js.php?m=tarefas"></script>
<form name="ativar" method="POST" action="[action]">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><p>
			Você deseja realmente <b>ativar</b> este Ajuste Programado?</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="25%"><strong>&nbsp;Data Início:</strong></td>
    <td width="75%"><input name="inicio" id="inicio" type="text" class="inputBrancoAzulEscuro11" style="width:150px;cursor:pointer" onKeyPress="return formataDataHora(this, event)" onBlur="return formataDataHora(this, event)" onClick="carregaTarefaCalendarioNavegacao(this.name, 1, this.value)" value="[inicio]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><strong>&nbsp;Comentário:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="7" cols="105"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center">[botoes]</td>
  </tr>
</table>
</form>
