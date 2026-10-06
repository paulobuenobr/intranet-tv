<script type="text/javascript" src="rds.js.php?m=tarefas"></script>
<form name="adicionar" id="adicionar" method="POST" onSubmit="return validaTarefaAdicionar(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Adicionar&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="15%"><strong>&nbsp;Origem:</strong></td>
        <td width="85%"><select name="origem" id="origem" class="inputBrancoAzulEscuro11" onChange="mudaTarefaOrigem(this);" style="width:500" disabled></select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Destino:</strong></td>
        <td><select name="destino" id="destino" class="inputBrancoAzulEscuro11" onChange="mudaTarefaDestino(this);" style="width:500" disabled></select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td><select name="tipo" id="tipo" class="inputBrancoAzulEscuro11" style="width:500" disabled></select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Título:</strong></td>
        <td><input name="titulo" id="titulo" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="200" style="width:500"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Prazo:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Início:</strong></td>
        <td><input name="prazo_inicio" id="prazo_inicio" value="[prazo_inicio]" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="21" style="width:500" onmouseover="if (this.form.destino.value) this.style.cursor='pointer'" onmouseout="this.style.cursor='default'" onClick="carregaTarefaCalendarioNavegacao(this.name,this.form.destino.value,this.value);" readonly></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Término:</strong></td>
        <td><input name="prazo_termino" id="prazo_termino" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="21" style="width:500" onmouseover="if (this.form.destino.value) this.style.cursor='pointer'" onmouseout="this.style.cursor='default'" onClick="carregaTarefaCalendarioNavegacao(this.name,this.form.destino.value,this.value);" readonly></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Descrição:</strong></td>
        <td><textarea name="descricao" id="descricao" class="textBrancoAzulEscuro11" rows="4" cols="75" style="width:500"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>

<script type="text/javascript">
  buscarTarefaOrigens();
</script>
